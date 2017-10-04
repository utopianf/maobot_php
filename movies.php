<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset=utf-8 />
<meta name="description" content="irc images" />
<title>irc videos</title>
<!--link rel="stylesheet" href="css/screen.css">-->
<link rel="stylesheet" href="css/lightbox.css">
<script src="js/jquery-1.11.0.min.js"></script>
<script src="js/lightbox.js"></script>
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/irc.css">
<!-- <link rel="stylesheet" href="common/base.css" /> -->
<!-- <link rel="shortcut icon" href="" -->
<!--[if IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
</head>
<body>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
 <script src="js/bootstrap.min.js"></script>
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="../">maobot</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li><a href="irclog.php#bottom">IRCLOG</a></li>
            <li><a href="images.php">IRCIMAGES</a></li>
            <li><a href="../wiki/">IRCWIKI</a></li>
            <li><a href="../anime/?C=M;O=D">ANIME</a></li>
            <li><a href="../upload.cgi">UPLOADER</a></li>
            <li class="active"><a href="#">IRCVIDEOS</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

<br /><br /><br />
<?php
include_once 'NicoSearch.php';
//page settings
foreach(glob('mov/{*.mp4}', GLOB_BRACE) as $file) {
    if(is_file($file)){
        $keywords = preg_split("/[_.]/", $file);
        $up_date = $keywords[0];
        $mov_id = $keywords[1];
        $xml = simplexml_load_file("http://ext.nicovideo.jp/api/getthumbinfo/$mov_id");
        $title = (string)$xml->thumb->title;
        $thumbnail = (string)$xml->thumb->thumbnail_url;
        echo "<td>";
        echo "<a href='$file'><img src='$thumbnail'/></a>";
        echo "<td>\n";
        echo "<td>$title<td><br />";
    }
}
?>
