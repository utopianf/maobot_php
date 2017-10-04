<!doctype html>
<html>
<head>
 <meta charset="utf-8">
 <meta http-equiv="X-UA-Compatible" content="IE=edge">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <title> IRC LOG </title>
 <link href="css/bootstrap.min.css" rel="stylesheet" />
 <link href="css/irc.css" rel="stylesheet" />
 <link href="css/col.less" rel="stylesheet/less" type="text/css" />
 <script src="//cdnjs.cloudflare.com/ajax/libs/less.js/2.7.1/less.min.js"></script>
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
            <li><a href="irclog.php">IRCLOG</a></li>
            <li><a href="images.php">IRCIMAGES</a></li>
            <li><a href="../wiki/">IRCWIKI</a></li>
            <li><a href="../anime/?C=M;O=D">ANIME</a></li>
            <li><a href="../upload.cgi">UPLOADER</a></li>
            <li><a href="movies.php">IRCVIDEOS</a></li>
            <li class="active"><a href="#">IRCMEMO</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
<br /><br /><br />
<?php

include_once "irclib.php";


//login check
#if(!isset($_SESSION["USERID"])) {
#    header("Location: logout.php");
#    exit;
#}


$starttime = date('Y-m-d\TH:i', time()-60*60*3);
$endtime   = date('Y-m-d\TH:i');
$channel   = "#化学部";
$image_display = '0';
$search_mode = '0';

if (isset($_COOKIE['nick'])) {
    $nick = $_COOKIE['nick'];
} else {
    $nick = "";
}

if (isset($_POST["image_display_b"])) {
    if ($_POST["image_display_b"] == 'image: off')
        $image_display = 1;
    elseif ($_POST["image_display_b"] == 'image: on')
        $image_display = 0;
} elseif (isset($_POST["image_display"])) {
    $image_display = $_POST["image_display"];
} elseif (isset($_GET["image_display"])) {
    $image_display = $_GET["image_display"];
}

if (isset($_POST["search_mode_b"])) {
    if ($_POST["search_mode_b"] == 'search: off')
        $search_mode = 1;
    elseif ($_POST["search_mode_b"] == 'search: on')
        $search_mode = 0;
} elseif (isset($_POST["search_mode"])) {
    $search_mode = $_POST["search_mode"];
} elseif (isset($_GET["search_mode"])) {
    $search_mode = $_GET["search_mode"];
}

if (isset($_POST["nick"])) {
    $nick = $_POST["nick"];
    setcookie('nick', $nick, time() + 60*60*24*14);
}

if ((!isset($_POST["now"])) && (!isset($_POST["send"]))) {
    if(isset($_POST["starttime"])) {
        $starttime = $_POST["starttime"];
    }
    if (isset($_POST["endtime"])) {
        $endtime = $_POST["endtime"];
    }
}

if (isset($_GET["starttime"]) && isset($_GET["endtime"])) {
    $starttime = $_GET["starttime"];
    $endtime = $_GET["endtime"];
}

if (isset($_GET["channel"])) {
    $channel = $_GET["channel"];
}

if (isset($_POST["previous"])) {
    $starttime = date('Y-m-d\TH:i', strtotime($starttime.'-3 hours'));
    $endtime = date('Y-m-d\TH:i', strtotime($endtime.'-3 hours'));
}

if (isset($_POST["next"])) {
    $starttime = date('Y-m-d\TH:i', strtotime($starttime.'+3 hours'));
    $endtime = date('Y-m-d\TH:i', strtotime($endtime.'+3 hours'));
}

if (isset($_POST["channel"])) {
    $channel = $_POST["channel"];
    #setcookie('channel', $channel, time() + 60*60*1);
}

if (isset($_GET["delete"])) {
    $id = $_GET["id"];
    deletePHPMemo($id);
}

echo show_memo();

//----------------------
// funcitons            
//----------------------

function show_memo() {
    require_once 'Mobile_Detect.php';
    $detect = new Mobile_Detect;

    echo "<table class='table-striped'>";
    echo "<tbody>";
    $res = selectMemos();
    foreach ($res as $row) {
        $time = $row->created;
        $id = $row->id;
        if ($detect->isMobile()) {
            $time = date('G:i:s', strtotime($time));
        }
        $user = htmlspecialchars($row->user, ENT_QUOTES);
        $c = mb_ereg_replace("https?://[\w/:%#\$(&amp)\?\(\)~\.=\+\-]+", "<a href=\"\\0\" target=\"_blank\">\\0</a>", $row->content);
        echo "   <tr><form action='./ircmemo.php' id='{$id}form' method='GET'>\n";
        echo "   <input type='hidden' name='id' value='$id' />";
        echo "   <td><input type='submit' id='{$id}delete' name='delete' value='完了' /></td>";
        echo "   <td class='irc time'>{$time}</td>";
        echo "   <td class='irc name'> [{$user}]</td>";
        echo "   <td class='irc priv'> {$c}</td></form></tr>\n";
    }
    echo "</tbody></table>";
}
?>
