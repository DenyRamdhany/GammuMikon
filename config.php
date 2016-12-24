<?php
	$host = "localhost";
	$user = "anyone";
	$pass = "anyone";
	$db = "gammu";
	
	mysql_connect($host, $user, $pass) or die (mysql_error());
	mysql_select_db($db) or die (mysql_error());
	
	date_default_timezone_set("Asia/Bangkok");
?>