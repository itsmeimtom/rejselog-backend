<?php
	require "auth.php";
	require "db.php";

	header("Content-Type: application/json");

	// open the file
	$db = new SQLite3("_data.sqlite");

	// fetch from db
	$result = $db->query('SELECT * FROM saves WHERE user = "' . $user . '"');
	$data = $result->fetchArray();

	// if there was data
	if($data) {
		// return data
		header("HTTP/1.0 200 OK");
		die('{"message": "here is your data, good sir!", "data": "' . $data[0] . '", "error": false}');
	} else {
		// return error
		header("HTTP/1.0 404 Not Found");
		die('{"message": "no entry for this user", "error": true}');
	}

	// close the file
	$db->close();
?>