<?php

require_once "Net/SmartIRC.php";

define("DEBUGLEVEL", SMARTIRC_DEBUG_NONE);

//DB settings
define("DBUSER", "maobot");
define("DBPASS", "maobot");
define("DBHOST", "localhost");
define("DBNAME", "maobot");

//IRC settings
define("IRCHOST", "irc.ircnet.ne.jp");
define("IRCPORT", "6667");
define("IRCNAME", "maobot");

// Only supported by php7
#define("IRCCHANNELS", array(
#    "#maobot_test",
#    "#maobot_test2"
#));

//Image DL settings
define("NICOUSER", 'nico@examples.com');
define("NICOPASS", 'nicopass');
define("PIXIUSER", 'pixiuser');
define("PIXIPASS", 'pixipass');

define("IRCCHANNELS", Null);
define("IRC_ENCODING", "iso-2022-jp");
mb_internal_encoding("UTF-8");

?>

