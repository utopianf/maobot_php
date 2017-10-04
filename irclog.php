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
            <li class="active"><a href="#">IRCLOG</a></li>
            <li><a href="images.php">IRCIMAGES</a></li>
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

#echo show_log($channel, $starttime, date('Y-m-d\TH:i', strtotime($endtime.'+1 minute')));
#print(strtotime($endtime.'+1 minute'));

if (isset($_POST["send"]) and isset($_POST["channel"]) and ($search_mode == '0') and
    isset($_POST["nick"]) and isset($_POST["message"]) and !empty($_POST["message"])) {
    insertPHPLog($_POST["channel"], $_POST["nick"], $_POST["message"]);
    if (preg_match('/^!memo/', $_POST["message"])) {
        insertPHPMemo($_POST["nick"], substr($_POST["message"], 6));
	}
	$search_mode = 0;
	$content = $_POST["message"];
}

elseif ((isset($_POST["search"]) and isset($_POST["channel"]) and isset($_POST["nick"]) and 
	isset($_POST["search_info"]) and !empty($_POST["search_info"]))) {
	$search_mode = 1;
	$content = $_POST["search_info"];
	$starttime = $_POST["sstarttime"];
	$endtime = $_POST["sendtime"];
}

#echo <<< EOM
#<form action='irclog.php#form' id='form1' method='POST'>
# <div class='container'>
#  <div class="col-xs-4 col-md-4">
#	<input type="submit" class="btn btn-default btn-block" id="previous"  name="previous"  value="＜前" >
#  </div>
#  <div class="col-xs-4 col-md-4">
#    <input type="submit" class="btn btn-default btn-block" id="now" name="now" value="今" />
#  </div>
#  <div class="col-xs-4 col-md-4">
#    <input type="submit" class="btn btn-default btn-block" id="next" name="next" value="次＞" />
#  </div>
# </div>
#</form>
#EOM;
echo "<div class='container'>";
echo "<a href='#bottom'>一番下</a>";
echo show_users($channel);
echo " <br />";
echo show_log($channel, $starttime, date('Y-m-d\TH:i', strtotime($endtime.'+1 minute')), $image_display, $search_mode, $content);

#echo " <form action='/mypage/maobot_php/irclog.php#bottom' method='POST'>\n";
echo "\n <form action='irclog.php#form' id='form' method='POST'>\n<div class='row row-10'>\n";
echo "<div class='form-group col-xs-4 col-md-2'>\n<label for='channel' class='sr-only'>Channel</label>\n";
echo disp_list($channel);
echo "</div>";
?>
  <div class="form-group col-xs-4 col-md-3">
   <label for="start-time" class="sr-only">Start time</label>
   <input type="datetime-local" class="form-control" id="starttime" name="starttime" value="<?php echo $starttime?>" />
  </div>
  <div class="form-group col-xs-4 col-md-3">
   <label for="end-time" class="sr-only">End time</label>
   <input type="datetime-local" class="form-control" id="endtime" name="endtime" value="<?php echo $endtime?>" />
  </div>
  <div class="col-xs-3 col-md-1">
   <input type="submit" class="btn btn-default btn-block" id="display" name="display" value="表示"/>
  </div>
  <div class="col-xs-3 col-md-1">
   <input type="submit" class="btn btn-default btn-block" id="previous"  name="previous"  value="＜前" >
  </div>
  <div class="col-xs-3 col-md-1">
   <input type="submit" class="btn btn-default btn-block" id="now" name="now" value="今" />
  </div>
  <div class="col-xs-3 col-md-1">
   <input type="submit" class="btn btn-default btn-block" id="next" name="next" value="次＞" />
  </div>
<?php
echo "<div class='col-xs-3 col-md-1'>";
if ($image_display == '0') {
    echo "<input type='submit' class='btn btn-default btn-block' id='image_display' name='image_display_b' value='image: off' style='margin-right:3px'/>";    echo "<input type='hidden' name='image_display' value='0'>";
}
elseif ($image_display == '1') {
    echo "<input type='submit' class='btn btn-default btn-block' id='image_display' name='image_display_b' value='image: on' style='margin-right:3px'/>";
    echo "<input type='hidden' name='image_display' value='1'>";
}
echo "</div>";
#echo "<div class='col-xs-1 col-md-1'>";
#if ($search_mode == '0') {
#    echo "<input type='submit' class='btn btn-default btn-block' id='search_mode' name='search_mode_b' value='search: off' style='margin-right:3px'/>";
#    echo "<input type='hidden' name='search_mode' value='0'>";
#}
#elseif ($search_mode == '1') {
#    echo "<input type='submit' class='btn btn-default btn-block' id='search_mode' name='search_mode_b' value='search: on' style='margin-right:3px'/>";
#    echo "<input type='hidden' name='search_mode' value='1'>";
#}
#echo "</div>";

?>
  <div class="form-group col-xs-2 col-md-2">
   <label for="nickname" class="sr-only">Nick name</label>
   <input type="text" class="form-control" id="nick" name="nick" placeholder="名前" value="<?php echo $nick;?>" />
  </div>
  <div class="form-group col-xs-7 col-md-8">
   <label for="message" class="sr-only">Message</label>
   <input type="text" class="form-control" id="message" name="message" placeholder="発言内容" value="">
  </div>
  <div class="col-xs-12 col-md-1">
   <input type="submit" class="btn btn-default btn-block" id="send" name="send" value="送信" />
  </div>
  <div class="form-group col-xs-4 col-md-3">
    <label for="sstart-time" class="sr-only">SStart time</label>
    <input type="datetime-local" class="form-control" id="sstarttime" name="sstarttime" value="<?php echo $starttime?>" />
  </div>
  <div class="form-group col-xs-4 col-md-3">
    <label for="send-time" class="sr-only">SEnd time</label>
    <input type="datetime-local" class="form-control" id="sendtime" name="sendtime" value="<?php echo $endtime?>" />
  </div>
  <div class="form-group col-xs-4 col-md-5">
   <label for="search_info" class="sr-only">search_info</label>
   <input type="text" class="form-control" id="search_info" name="search_info" placeholder="検索文字列" value="<?php echo $_POST["search_info"]?>">
  </div>
  <div class="col-xs-12 col-md-1">
   <input type="submit" class="btn btn-default btn-block" id="search" name="search" value="検索" />
  </div>
 </div>
</form>
</div>
<div name="#bottom" id="bottom"></div>
</body>
</html>

<?php
//----------------------
// funcitons            
//----------------------

function disp_list($selected_value) {
    $res = selectChannels();
    echo("  <select class=\"form-control\" id=\"channel\" name=\"channel\" onChange=\"submit()\">\n");
    foreach ($res as $row) {
        echo("   <option ");
        if ($selected_value == $row->name) {
            echo("selected ");
        }
        echo("value=\"" . $row->name . "\">" . $row->name . "</option>\n");
    }
    echo("  </select>");
}

function show_log($ch, $st, $et, $image_display, $search_mode, $search_info) {
    require_once 'Mobile_Detect.php';
    $detect = new Mobile_Detect;

    echo "<table class='table-striped'>";
    echo "<tbody>";
    if ($search_mode == '0') {
        $res = selectLogs($ch, $st, $et);
    } else {
        $res = selectSearch($search_info, $ch, $st, $et);
    }
    foreach ($res as $row) {
        $time = $row->created;
        if ($detect->isMobile()) {
            $time = date('G:i:s', strtotime($time));
        }
        if ($row->type == "IMGLINK") {
            if ($image_display == '1') {
                echo "    <tr><td><img src={$row->content} /></td></tr>";
            }
        } elseif ($row->type == "JOIN" or $row->type == "PART" or $row->type == "QUIT") {
            $user = htmlspecialchars($row->user, ENT_QUOTES);
            echo "   <tr><td class='irc time'>{$time}</td>";
            echo "   <td class='irc noti'> {$user} {$row->type}</td></tr>";
        } else {
            $user = htmlspecialchars($row->user, ENT_QUOTES);
            #$content = htmlspecialchars($row->content, ENT_QUOTES);
            $c = mb_ereg_replace("https?://[\w/:%#\$(&amp)\?\(\)~\.=\+\-]+", "<a href=\"\\0\" target=\"_blank\">\\0</a>", $row->content);
            echo "   <tr><td class='irc time'>{$time}</td>";
            if ($row->said == 1) {
                echo "   <td class='irc name'> [{$user}]</td>";
            } elseif ($row->said === 0) {
                echo "   <td class='irc name'> &lt;{$user}&gt;</td>";
            } else {
                echo "   <td class='irc name'> ({$user})</td>";
            }
            if ($row->type == "PRIVMSG") {
                echo "   <td class='irc priv'> {$c}</td></tr>\n";
            } elseif ($row->type == "NOTICE" or $row->type == "TOPIC") {
                echo "   <td class='irc noti'> {$c}</td></tr>\n";
            }
        }
    }
    echo "</tbody></table>";
}

function show_users($ch) {
    $res = selectUsers($ch);
    foreach ($res as $row) {
        echo "    <div class='irc noti'>users: {$row->users} <br />topic: {$row->topic}</div>";
    }
}

?>
