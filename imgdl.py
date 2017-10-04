#!/usr/bin/python3
# -*- coding: utf-8 -*-

import sys
import re
import os
from urllib.request import urlretrieve
from urllib.request import urlopen
from urllib.request import build_opener, HTTPCookieProcessor
from urllib.parse import urlencode, quote
from http.cookiejar import CookieJar
from configparser import SafeConfigParser
from imghdr import what
from bs4 import  BeautifulSoup
from PIL import Image
import pymysql
from subprocess import Popen, PIPE

from mvdl import *
from pixivpy3 import *

dlDir = "./images/"
dlDir_mov = "./mov/"
thumbDir = "./images/thumbnail/"
thumb_lDir = "./images/thumbnail_l/"

def thumbnail(input_file, output_file):
    size = 150
    img = Image.open(input_file)
    w,h = img.size
    l,t,r,b = 0,0,size,size
    new_w, new_h = size,size

    if w>=h:
        new_w = size * w // h
        l = (new_w - size) // 2
        r = new_w - l
    else:
        new_h = size * h // w
        t = (new_h - size) // 2
        b = new_h - t

    thu = img.resize((new_w, new_h), Image.ANTIALIAS)
    thu = thu.crop((l,t,r,b))
    thu.save(thumbDir + output_file, quality=100, optimize=True)

    thu = img.resize((w*300//h, 300), Image.ANTIALIAS)
    thu.save(thumb_lDir + output_file, quality=100, optimize=True)

def regImg(loc, orig, thum, type, mov=0):
    nick = ""
    channel = ""
    if len(sys.argv) == 4:
        nick = os.fsencode(sys.argv[2]).decode('utf-8')
        channel = os.fsencode(sys.argv[3]).decode('utf-8')
    conn = pymysql.connect(host='127.0.0.1',user='maobot',
            passwd='msc3824',db='maobot',charset='utf8')
    cur = conn.cursor()
    if mov == 0:
       statement = "INSERT INTO images (user,channel,loc,orig,thum,type) VALUES(%s, %s, %s, %s, %s, %s)"
    elif mov == 1:
       statement = "INSERT INTO movies (user,channel,loc,orig,thum,type) VALUES(%s, %s, %s, %s, %s, %s)"
    data = (nick, channel, loc, orig, thum, type)
    cur.execute(statement, data)
    cur.connection.commit()
    cur.close()
    conn.close()

def readConfig():
    config = SafeConfigParser()
    if os.path.exists('imgdl.ini'):
        config.read('imgdl.ini')
    else:
        print("No Configuration File.")
        sys.exit(2)

    try:
        nicouser = config.get('nicoseiga.jp', 'user')
        nicopass = config.get('nicoseiga.jp', 'pass')
    except Exception as e:
        return "error: could not read nico configuration." + e

    try:
        pixiuser = config.get('pixiv.net', 'user')
        pixipass = config.get('pixiv.net', 'pass')
    except Exception as e:
        return "error: could not read pixiv configuration." + e

    return nicouser, nicopass, pixiuser, pixipass

def main():
    orig_url = sys.argv[1]
    html = urlopen(orig_url)
    nicouser, nicopass, pixiuser, pixipass = readConfig()
    bsObj = BeautifulSoup(html, "lxml")
    twi = re.compile('https:\/\/twitter.com\/[a-zA-Z0-9_]+\/status\/\d+')
    nic = re.compile('http:\/\/seiga.nicovideo.jp\/seiga\/[a-zA-Z0-9]+')
    pix1 = re.compile('https?:\/\/www.pixiv.net\/member_illust.php\?mode=medium\&illust_id=[0-9]+')
    pix2 = re.compile('https?:\/\/www.pixiv.net\/member_illust.php\?illust_id=[0-9]+\&mode=medium')
    pix_ = re.compile('https?:\/\/www.pixiv.net\/member_illust.php\?mode=manga_big\&illust_id=[0-9]+\&page=[0-9]+')
    nico_mov = re.compile('https?:\/\/www.nicovideo.jp\/watch\/[a-zA-Z0-9]+')
    yout_mov = re.compile('https:\/\/www.youtube.com\/watch\?v=[a-zA-Z0-9]+')

    image_format = ["jpg", "jpeg", "gif", "png"]

    if twi.match(orig_url):
        images = bsObj.find("div", {"class": "permalink-tweet-container"}).find("div", {"class": "AdaptiveMedia-container"}).findAll("div", {"class": "AdaptiveMedia-photoContainer"})
        for item in images:
            imageLoc = item.find("img")["src"]
            urlretrieve(imageLoc , dlDir + "twi" + imageLoc[28:])
            loc = dlDir+"twi"+imageLoc[28:]
            thumb = "thumb_twi" + imageLoc[28:]
            type = what(loc)
            thumbnail(loc, thumb)
            regImg(loc, orig_url, "./images/thumbnail/"+thumb, type)
            print(thumb_lDir+thumb)

    elif nic.match(orig_url):
        opener = build_opener(HTTPCookieProcessor(CookieJar()))
        post = {
            'mail_tel': nicouser,
            'password': nicopass
        }
        data = urlencode(post).encode("utf_8")
        response = opener.open('https://secure.nicovideo.jp/secure/login', data)
        response.close()

        image_id = orig_url[34:]
        with opener.open('http://seiga.nicovideo.jp/image/source?id=' + image_id) as response:
            bsObj = BeautifulSoup(response)
            imageLoc = bsObj.find("div", {"class": "illust_view_big"}).find("img")["src"]
            dlLoc = dlDir + "nic" + image_id
            urlretrieve('http://lohas.nicoseiga.jp' + imageLoc, dlLoc)
            type = what(dlLoc)
            loc = dlLoc + "." + type
            os.rename(dlLoc, loc)
            thumb = "thumb_nico"+image_id+"."+type
            print(thumb_lDir+thumb)
            thumbnail(loc, thumb)
        regImg(loc, orig_url, "./images/thumbnail/"+thumb, type)

    elif pix1.match(orig_url) or pix2.match(orig_url):
        imageLocs = []
        image_id = re.search('\d+', orig_url).group()
        api = AppPixivAPI()
        api.login(pixiuser, pixipass)
        json_result = api.illust_detail(image_id, req_auth=True)
        illust = json_result.illust
        if "original" in illust.image_urls:
            imageLocs.append(illust.image_urls.original)
        elif "meta_pages" in illust and len(illust.meta_pages)!=0:
            for i in illust.meta_pages:
                imageLocs.append(i.image_urls.original)
        elif "meta_single_page" in illust:
            imageLocs.append(illust.meta_single_page.original_image_url)
#        print(imageLocs)
        for imageLoc in imageLocs:
            api.download(imageLoc, path=dlDir, name="pix" + imageLoc.split("/")[-1])
            loc = dlDir + "pix" + imageLoc.split("/")[-1]
            type = what(loc)
            thumb = "thumb_pix"+imageLoc.split("/")[-1]
            thumbnail(loc, thumb)
            regImg(loc, orig_url, "./images/thumbnail/"+thumb, type)
            print(thumb_lDir+thumb)
    elif pix_.match(orig_url):
        imageLocs = []
        reg = re.compile("https?:\/\/www.pixiv.net\/member_illust.php\?mode=manga_big\&illust_id=(\d+)\&page=(\d+)")
        image_id = int(reg.match(orig_url).group(1))
        page = int(reg.match(orig_url).group(2))
        api = AppPixivAPI()
        api.login(pixiuser, pixipass)
        json_result = api.illust_detail(image_id, req_auth=True)
        imageLocs.append(json_result.illust.meta_pages[page].image_urls.original)
        for imageLoc in imageLocs:
            api.download(imageLoc, path=dlDir, name="pix" + imageLoc.split("/")[-1])
            loc = dlDir + "pix" + imageLoc.split("/")[-1]
            type = what(loc)
            thumb = "thumb_pix"+imageLoc.split("/")[-1]
            thumbnail(loc, thumb)
            regImg(loc, orig_url, "./images/thumbnail/"+thumb, type)
            print(thumb_lDir+thumb)

    elif nico_mov.match(orig_url):
        proc = Popen(["./mvdl.py", orig_url], stdout=PIPE, stderr=PIPE)
        retcode = proc.poll()

    elif orig_url.split(".")[-1] in image_format:
        filename = "_".join(quote(orig_url).split("/")[-2:])
        if len(filename) > 10:
            from datetime import datetime
            filename = datetime.now().strftime('%s') + filename[-10:]
        loc = dlDir + filename
        thumb = "thumb_"+filename
        urlretrieve(orig_url , loc)
        type = what(loc)
        if type == None:
            type = orig_url.split(".")[-1]
        thumbnail(loc, thumb)
        print(thumb_lDir+thumb)
        regImg(loc, orig_url, "./images/thumbnail/"+thumb, type)

if __name__ == '__main__' :
    main()
