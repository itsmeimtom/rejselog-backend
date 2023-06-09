<?php
	require "db.php";
	require "auth.php";

	header("Content-Type: application/json");

	$post = file_get_contents("php://input");
	// die(var_dump($post));

	if($post == "") {
		header("HTTP/1.0 400 Bad Request");
		die('{"message": "no data provided", "error": true}');
	}

	// check if data is base64 formatted
	// this doesn't really work, but it's good enough
	if(!base64_decode($post, true)) {
		header("HTTP/1.0 400 Bad Request");
		die('{"message": "data is not base64 encoded", "error": true}');
	}

	// if user exists
	$result = $db->query('SELECT * FROM saves WHERE user = "' . $user . '"');

	$new = false; // if a new user 

	// check if user exists
	if($result->fetchArray()) {
		// update
		$new = false;
		$r = $db->exec('UPDATE saves SET data = "' . $post . '" WHERE user = "' . $user . '"');
	} else {
		// insert
		header("HTTP/1.0 201 Created");
		$new = true;
		$r = $db->exec('INSERT INTO saves (user, data) VALUES ("' . $user . '", "' . $post . '")');
	}

	// close the file
	$db->close();

	// return success
	echo '{"message": "tried to add your data!", "error":' . ($r ? "false" : "true") . ', "new":' . ($new ? "true" : "false") . '}';
?>