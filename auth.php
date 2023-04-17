<?php


	// development
	if($_SERVER['REMOTE_ADDR'] == "127.0.0.1" || $_SERVER['REMOTE_ADDR'] == "::1") {
		// if we're on localhost, allow all origins
		header("Access-Control-Allow-Origin: *");
	} else {
		header("Access-Control-Allow-Origin: https://github-pages.thomasr.me");
	}

	// allow credentials
	header("Access-Control-Allow-Credentials: true");

	if (!isset($_SERVER['PHP_AUTH_USER'])) {
		header('WWW-Authenticate: Basic realm="Rejselog"');
		header("HTTP/1.0 401 Unauthorized");
		authError();
	} else {
		$user = $_SERVER['PHP_AUTH_USER'];
		
		// strip to only be alphanumeric (thanks copilot)
		$user = preg_replace("/[^a-zA-Z0-9]+/", "", $user);
		

		// check if user exists
		$userResult = $db->query('SELECT user,pass FROM saves WHERE user = "' . $user . '" LIMIT 1');
		$userData = $userResult->fetchArray();

		if(!$userData) {
			authError();
			die(); // just in case
		}

		if($userData[0] !== $user) {
			authError();
			die(); // just in case
		}

		// check if password is correct

		$dbPass = $userData[1];


		// who knows how secure this is but it works so whatever
		if(!password_verify($_SERVER['PHP_AUTH_PW'], $dbPass)) {
			authError();
			die(); // just in case
		}

		// if we've gotten this far, we're good to go
	}
	
	function authError() {
			header('WWW-Authenticate: Basic realm="Rejselog"');
			header("HTTP/1.0 401 Unauthorized");
			die("401: Auth Problem");
	}
?>
