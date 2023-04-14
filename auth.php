<?php
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Rejselog"');
    header("HTTP/1.0 401 Unauthorized");
    die("401: Auth Problem");
    exit;
} else {
	if($_SERVER['PHP_AUTH_USER'] == "test" && $_SERVER['PHP_AUTH_PW'] == "123") {
		// all good!

		$user = $_SERVER['PHP_AUTH_USER'];
		
		// strip to only be alphanumeric (thanks copilot)
		$user = preg_replace("/[^a-zA-Z0-9]+/", "", $user);
	} else {
		header('WWW-Authenticate: Basic realm="Rejselog"');
    	header("HTTP/1.0 401 Unauthorized");
		die("401: Auth Problem");
	}
}
?>
