<?php

/* irclib.php
 * Libraries for IRC
 * 
 * insertPHPLog($data)
 *
 * insertChannel($channel)
 *
 * selectLogs()
 *
 * selectChannels()
 */

require_once "DBRecord.class.php";

function insertIRCLog($log) {
    $db = new DBRecord();
    return $db->insert('irclog', $log, '%s%s%s%s');
}

function insertPHPLog($channel, $nick, $message) {
    $log = array(
        "user"    => $nick,
        "type"    => "PRIVMSG",
        "channel" => $channel,
        "content" => $message,
        "said"    => "0"
    );
    $db = new DBRecord();
    return $db->insert('irclog', $log, '%s%s%s%s%d');
}

function insertIRCMemo($log) {
    $db = new DBRecord();
    return $db->insert('ircmemo', $log, '%s%s');
}

function insertPHPMemo($nick, $memo) {
    $log = array(
        "user"    => $nick,
        "content" => $memo,
    );
    $db = new DBRecord();
    return $db->insert('ircmemo', $log, '%s%s');
}

function deletePHPMemo($id) {
    $db = new DBRecord();
    return $db->delete('ircmemo', $id);
}

function selectMemos() {
    $db = new DBRecord();
    return $db->select('SELECT * FROM ircmemo');
}

function selectLogs_unsaid() {
    $db = new DBRecord();
    return $db->select('SELECT * FROM irclog WHERE said = ?', array(0), array('%d'));
}

function selectLogs($channel, $starttime, $endtime) {
    $db = new DBRecord();
    return $db->select('SELECT * FROM irclog WHERE channel = ? AND created BETWEEN ? AND ?', array($channel, $starttime, $endtime), array('%s','%s','%s'));
}

function selectLogs_heiqi($channel, $starttime, $endtime) {
    $db = new DBRecord();
    return $db->select('SELECT * FROM irclog_heiqi WHERE channel = ? AND datetime BETWEEN ? AND ?', array($channel, $starttime, $endtime), array('%s','%s','%s'));
}

function selectChannels() {
    $db = new DBRecord();
    return $db->select('SELECT name FROM channel');
}

function selectImages($startnum, $num) {
    $db = new DBRecord();
    return $db->select('SELECT * FROM images ORDER BY id DESC LIMIT ?, ?', array($startnum, $num), array('%d', '%d'));
}

function selectImagesCount() {
    $db = new DBRecord();
    return $db->select('SELECt count(id) FROM images');
}

function selectUsers($channel) {
    $db = new DBRecord();
    return $db->select('SELECT * FROM channel WHERE name = ?', array($channel), array('%s'));
}

function selectSearch($info, $channel, $starttime, $endtime) {
    $db = new DBRecord();
	$info = "%" . $info . "%";
    return $db->select('SELECT * FROM irclog WHERE (user LIKE ? OR content LIKE ?) AND channel = ? AND created BETWEEN ? AND ?',
        array($info, $info, $channel, $starttime, $endtime), array('%s','%s','%s','%s','%s'));
}

function insertChannel($ch) {
    $log = array(
        "name" => $ch,
    );
    $db = new DBRecord();
    return $db->insert('channel', $log, array('%s'));
}

function updateUnsaid2Said($id) {
    $data = array(
        "said" => 1,
    );
    $where = array(
        "said" => 0,
    );
    $db = new DBRecord();
    return $db->update('irclog', $data, array('%d'), $where, array('%d'));
}

function updateChannelData($chdata){
    if ($chdata->topic == Null) {
        $topic = "NULL";
    } else {
        $topic = $ch->topic;
    }
    $users = array();
    foreach ($chdata->users as $user) {
        $users[] = $user->nick;
    }
    $updateData = array(
        "topic"   => $topic,
        "usernum" => count($chdata->users),
        "users"   => implode(",", $users),
    );
    $where = array(
        "name" => $chdata->name,
    );
    $db = new DBRecord();
    return $db->update('channel', $updateData, array('%s','%s','%s'), $where, array('%s'));
}

function updateTopic($channel, $topic) {
    $updateData = array(
        "topic" => $topic,
    );
    $where = array(
        "name" => $channel,
    );
    $db = new DBRecord();
    return $db->update('channel', $updateData, array('%s'), $where, array('%s'));
}

function updateChannelUsers($channel, $users) {
    $user_string = implode(", ", $users);
    $updateData = array(
        "users" => $user_string,
    );
    $where = array(
        "name" => $channel,
    );
    $db = new DBRecord();
    return $db->update('channel', $updateData, array('%s'), $where, array('%s'));
}
