<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset=utf-8 />
<meta name="description" content="irc images" />
<title>irc images</title>
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
            <li class="active"><a href="#">IRCIMAGES</a></li>
            <li><a href="../wiki/">IRCWIKI</a></li>
            <li><a href="../anime/?C=M;O=D">ANIME</a></li>
            <li><a href="../upload.cgi">UPLOADER</a></li>
            <li><a href="movies.php">IRCVIDEOS</a></li>
            <li><a href="ircmemo.php">IRCMEMO</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

<br /><br /><br />
<?php

include_once "irclib.php";

session_start();
session_regenerate_id(true);

#$now_page = 1;
if (isset($_POST["page"])) {
    $now_page = (int) $_POST["page"];
} else {
    $now_page = 1;
}

if (isset($_POST["mae"])) {
    if ($now_page != 1) {
        $now_page -= 1;
    }
}

if (isset($_POST["tugi"])) {
    $now_page += 1;
}

if ($now_page >= 11) {
    $ten_before = $now_page-10;
} else {
    $ten_before = 1;
}
echo "<ul>\n";
//page settings
echo "</ul>\n";

echo "<table>\n";
echo image_list($now_page);
echo "</table>\n";

echo page_list($now_page);

function image_list($page, $row=10, $column=5) {
    $res = selectImages($row*$column*($page-1), $row*$column);
    $r = 1;
    $c = 1;
    echo "<tr>\n";
    foreach ($res as $image) {
        $starttime = date('Y-m-d\TH:i', strtotime($image->created.'-1 hours'));
        $endtime = date('Y-m-d\TH:i', strtotime($image->created.'+1 hours'));
        $channel = str_replace("#", "%23", $image->channel);
        $log_url = "http://www.vert-utopia.com/mypage/maobot_php/irclog.php?channel=$channel&starttime=$starttime&endtime=$endtime&image_display=1";
        echo "<td>\n<div class='text-center'><a href={$image->loc} class='example-image-link' data-title=\"<a target='_blank' href={$image->orig}>({$image->user}) {$image->orig}</a><a target='_blank' href=$log_url>{$image->created} {$image->channel}</a>\" data-lightbox='example-set'>";
        echo "<img alt='Generic placeholder thumbnail' class='img-rounded img-responsive' src={$image->thum}></img></a></div>\n</td>\n";
        if ($r % $row == 0) {
            if ($c != $column) {
                $c++;
                $r = 0;
                echo "</tr>\n<tr>\n";
            } else {
                echo "</tr>\n";
            }
        }
        $r++;
    }
    echo "</tr>\n";
}

function page_list($now_page) {
    echo "<form accept-charset='UTF-8' action='images.php' method='POST'>\n";
    echo "<input class='btn' name='mae' type='submit' value='＜前' />";
    echo "<select id='page' name='page'>\n";
    $res = selectImagesCount();
    $count = $res[0]->{"count(id)"};
    $page_count = (int) ($count/50 + 1);
    for($page = 1; $page <= $page_count; $page++) {
        echo "<option ";
        if ($page == $now_page) {
            echo "selected ";
        }
        echo "value=".$page.">$page</option>\n";
    }
    echo "</select>\n";
    echo "<input class='btn' name='commit' type='submit' value='表示' />";
    echo "<input class='btn' name='tugi' type='submit' value='次＞' />";
    echo "</form>";
}

?>
