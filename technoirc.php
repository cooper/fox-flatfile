<?
	$config->network = "technoirc";
	$config->server = "irc.technoirc.net";
	$config->serv_port = "6667";
	$config->serv_nick = "fox-".rand(1,99);
	$config->serv_ident = "fox";
	$config->serv_realname = "fox";
	$config->serv_nickpass = "lolpass";
	$config->ownerhost = array(
		strtolower('SecureHost-8beaf600.in.comcast.net') => true,
		strtolower('cooper.on.fgtb.us') => true,
		strtolower('FBB9DD.E5A659.90273C.E125CB') => true
	);
	$config->storage = "db/";
	$config->debug = false;
		include("core.php");
?>
