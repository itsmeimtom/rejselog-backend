<?php

	$db = new SQLite3("_data.sqlite");
	$db->exec("CREATE TABLE IF NOT EXISTS saves (user TEXT, data BLOB, PRIMARY KEY (user))");

?>