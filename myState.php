<?php
	include("config.php");
	extract($_GET);
	
	$data=mysql_fetch_object(mysql_query("SELECT * FROM billing where station LIKE $dev AND port LIKE $port"));
	echo "{".$data->status."}";
?>