<?php

/* ircbot.php
 * Bot FILE
 *
 * updateCH doesn't run well now.
 */

include_once "Net/SmartIRC.php";
include_once "./settings.php";
include_once "./irclib.php";
include_once "./imglib.php";

if (IRCCHANNELS != Null) {
    $IRCCHANNELS = IRCCHANNELS;
} else {
    foreach (selectChannels() as $row) {
        $IRCCHANNELS[] = mb_convert_encoding(($row->name), IRC_ENCODING);
    }
}

class Net_SmartIRC_module_IRCBot {
    public $name        = "maobot_php_test";
    public $description = "IRCBot for KGB";
    public $author      = "MaO";
    public $license     = "MIT";

    private $irc;
    private $handlerids;

    public function __construct($irc) {
        $this->irc = $irc;
        $this->handlerids = array(
            $irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_NOTICE, '.*', $this, 'getLog'),
            $irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^!join', $this, 'joinCh'),
            $irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '/https?:\/\/[a-zA-Z0-9\-\.\/\?\@&=:~_#]+/', $this, 'getUrl'),
            $irc->registerActionHandler(SMARTIRC_TYPE_TOPICCHANGE, '.*', $this, 'getTopic'),
//          $irc->registerActionHandler(SMARTIRC_TYPE_TOPICCHANGE | SMARTIRC_TYPE_JOIN | SMARTIRC_TYPE_PART, '.*', $this, 'updateCh'),
            $irc->registerActionHandler(SMARTIRC_TYPE_QUIT | SMARTIRC_TYPE_JOIN | SMARTIRC_TYPE_PART, '.*', $this, 'join_partCh'),
            $irc->registerActionHandler(SMARTIRC_TYPE_JOIN, '.*', $this, 'welcome'),
            $irc->registerTimeHandler(3000, $this, 'talkFromPHP'),
            $irc->registerTimeHandler(10000, $this, 'getIrcInfo'),
            $irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^!memo', $this, 'getMemo'),
        );
    }

    public function __destruct() {
        $this->irc->unregisterActionID($this->handlerids);
    }

    private function encode($str) {
        return mb_convert_encoding($str, IRC_ENCODING);
    }

    private function decode($str) {
        return mb_convert_encoding($str, mb_internal_encoding(), IRC_ENCODING);
    }

    private function decodeData($data) {
        $class_name = get_class($data);
        $deData = new $class_name();
        $object_vars = get_object_vars($data);
        foreach ($object_vars as $name => $value) {
            if (is_string($value)) {
                $deData->$name = $this->decode($value);
            } elseif ($value != Null) {
                $daData->$name = $value;
            }
        }
        return $deData;
    }

    public function getLog($irc, $data) {
        $log = array(
            "user" => $this->decode($data->nick),
            "type" => $this->decode($data->rawmessageex[1]),
            "channel" => $this->decode($data->channel),
            "content" => $this->decode($data->message),
        );
        insertIRCLog($log);
    }

    public function getMemo($irc, $data) {
        $log = array(
            "user" => $this->decode($data->nick),
            "content" => substr($this->decode($data->message), 5),
        );
        insertIRCMemo($log);
    }

    public function join_partCh($irc, $data) {
        $nick = $this->decode($data->nick);
        $type = $this->decode($data->rawmessageex[1]);
        $log = array(
            "user" => $nick,
            "type" => $type,
            "channel" => $this->decode($data->channel),
            "content" => $nick . $type,
        );
        insertIRCLog($log);
    }

    public function getTopic($irc, $data) {
        $topic = substr($this->decode(implode(" ", array_slice($data->rawmessageex, 3))),1);
        $channel = $this->decode($data->channel);
        $nick = $this->decode($data->nick);
        $log = array(
            "user" => $nick,
            "type" => $this->decode($data->rawmessageex[1]),
            "channel" => $channel,
            "content" => $nick." changes the topic: ".$topic,
        );
        insertIRCLog($log);
        updateTopic($channel, $topic);
    }

    public function joinCh($irc, $data) {
        if (isset($data->messageex[1])) {
            $channel = $data->messageex[1];
            $irc->join(array($channel));
            insertChannel($this->decode($channel));
        } else {
            $irc->message($data->type, $data->nick, 'wrong parameter count');
            $irc->message($data->type, $data->nick, 'usage: !join $channels');
        }
    }

    public function welcome($irc, $data) {
        $irc->op($data->channel, $data->nick);
    }

    public function getUrl($irc, $data) {
        preg_match_all('/https?:\/\/[a-zA-Z0-9\-\.\/\?\@_&=:~#]+/', $data->message, $match);
        $url = $match[0][0];
        $nick = $this->decode($data->nick);
        $channel = $this->decode($data->channel);
        $this->insertUrl($irc, $url, $nick, $channel);
    }

    public function insertUrl($irc, $url, $nick, $channel) {
        $urldata = file_get_contents($url);
        $urldata = mb_convert_encoding($urldata, "UTF-8");
        preg_match( "/<title>(.*?)<\/title>/i", $urldata, $matches);
        $decode_title = html_entity_decode(str_replace("&#10;"," ",$matches[1]));
        $irc->message(SMARTIRC_TYPE_NOTICE, $this->encode($channel), $this->encode($decode_title));
        $log = array(
            "user" => "maobot",
            "type" => "NOTICE",
            "channel" => $channel,
            "content" => $decode_title,
        );
        insertIRCLog($log);
        $links = dlImg($nick, $channel, $url);
        foreach ($links as $link) {
            $log = array(
                "user"    => $nick,
                "type"    => "IMGLINK",
                "channel" => $channel,
                "content" => $link,
            );
            insertIRCLog($log);
        }
        #exec('python3 ./imgdl.py "' . $url . '"');
    }

    public function updateCh($irc, $data) {
        foreach (selectChannels() as $ch) {
            updateChannelData($this->decodeData($irc->getChannel($ch->name)));
        }
    }

    public function talkFromPHP($irc) {
        $res = selectLogs_unsaid();
        if ($res!=Null) {
            $row = $res[0];
            #foreach ($res as $row) {
                $channel = $this->encode($row->channel);
                $nick    = $this->encode($row->user);
                $content = $this->encode($row->content);
                $id      = $row->id;
                $irc->message(SMARTIRC_TYPE_CHANNEL, $channel, '('.$nick.') '.$content);
                preg_match_all('/https?:\/\/[a-zA-Z0-9\-\.\/\?\@&=:~_#]+/', $row->content, $match);
                if (isset($match[0][0])) {
                    $this->insertUrl($irc, $match[0][0], $row->user, $row->channel);
                }
                updateUnsaid2Said($id);
           #}
        }
    }

    public function getIrcInfo($irc) {
        $IRCCHANNELS = $GLOBALS['IRCCHANNELS'];
        foreach ($IRCCHANNELS as $channel) {
            $nicks = array();
            foreach ($irc->getChannel($channel)->users as $user => $info) {
                #$irc->getUser($channel, $name)->nick;
                $nicks[] = $user;
            }
            updateChannelUsers($this->decode($channel), $nicks);
        }
    }

}

$irc = new Net_SmartIRC(array(
    'DebugLevel' => DEBUGLEVEL,
    'ChannelSyncing' => true,
));

$irc->loadModule('IRCBot')
    ->connect(IRCHOST, IRCPORT)
    ->login(IRCNAME, IRCNAME, 8, IRCNAME)
    ->join($IRCCHANNELS)
    ->listen()
    ->disconnect();

?>
