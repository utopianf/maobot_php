#!/usr/bin/python3
# -*- coding: utf-8 -*-

from sys import argv
from urllib.request import urlretrieve
from urllib.request import urlopen
from bs4 import  BeautifulSoup

dlDir = "./images/"
def main():
    try:
        html = urlopen(argv[1])
    except urllib.error.HTTPError as e:
        print(e)
    else:
        bsObj = BeautifulSoup(html)
        images = bsObj.find("div", {"class": "AdaptiveMedia-container      js-adaptive-media-container          "}).findAll("div", {"class": "AdaptiveMedia-photoContainer js-adaptive-photo "})
        for item in images:
            imageLoc = item.find("img")["src"]
            print(item.find("img")["src"])
            urlretrieve(imageLoc , dlDir + imageLoc[28:])

if __name__ == '__main__' :
    main()
