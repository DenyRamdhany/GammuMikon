<?php
	include("config.php");
	extract($_GET);
	
	$qry=mysql_query("SELECT * FROM billing where station LIKE $dev AND port LIKE $port");
	
	while($data=mysql_fetch_object($qry))
	{ echo "{".$data->status."}";
	}
?>