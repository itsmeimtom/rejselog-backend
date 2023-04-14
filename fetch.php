<?php
	require "db.php";
	require "auth.php";

	header("Content-Type: application/json");

	// fetch from db
	$result = $db->query('SELECT "data" FROM saves WHERE user = "' . $user . '" LIMIT 1');
	$data = $result->fetchArray();

	// if there was data
	if($data) {
		// return data
		header("HTTP/1.0 200 OK");
		
		// echo var_dump($data);

		die('{"message": "I think I got your data, good sir!", "data": "' . $data[0] . '", "error": false}');
	} else {
		// return error
		header("HTTP/1.0 404 Not Found");
		die('{"message": "no entry for this user", "error": true}');
	}

	// close the file
	$db->close();
?>