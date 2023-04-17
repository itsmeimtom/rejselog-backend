<?php
	// Permission to automating adding journeys to RailMiles.me
	// given by Olivia on Fri 14th Apr, 2023:
	// "We have no problem with you creating a programme or script - however, we cannot guarantee it will always work, or guarantee support for it. You may be interested to know that we do have an API for RailMiles currently in development."
	
	// It is likely that this will become obsolete in the future
	
	// PLEASE DO NOT USE THIS TO ADD JOURNEYS THAT YOU DID NOT ACTUALLY TAKE
	// PLEASE ENSURE REQUESTS ARE NOT MADE TOO OFTEN

	// This is created in good faith, but I am not responsible for any misuse of this script

	require "db.php";
	require "auth.php";

	
	$RMcookie = $_POST["cookie"];

	// show all post data
	// var_dump($_POST);
	// die();

	header("Content-Type: application/json");
	
	if(!$RMcookie) {
		die('{"error": "Missing cookie"}');
	}


	// check POST data

	// if missing origin or destination name
	if(!$_POST["origin_name"] || !$_POST["destination_name"]) {
		die('{"error": "Missing origin or destination name"}');
	}

	// if origin and destination are too long
	if(strlen($_POST["origin_name"]) > 255 || strlen($_POST["destination_name"]) > 255) {
		die('{"error": "Origin or destination name too long (255 max)"}');
	}

	// if platforms are set
	if($_POST["origin_platform"] || $_POST["destination_platform"]) {
		// if platforms are too long
		if(strlen($_POST["origin_platform"]) > 5 || strlen($_POST["destination_platform"]) > 5) {
			die('{"error": "Origin or destination platform too long (5 max)"}');
		}
	}

	// if missing actual dates/times
	if(!$_POST["act_dep_date"] || !$_POST["act_dep_time"] || !$_POST["act_arr_date"] || !$_POST["act_arr_time"]) {
		die('{"error": "Missing actual dates/times"}');
	}

	// if route description is too long
	if(strlen($_POST["route_description"]) > 512) {
		die('{"error": "Route description too long (512 max)"}');
	}

	// if operator name set
	if($_POST["operator_name"]) {
		// if operator name is too long
		if(strlen($_POST["operator_name"]) > 36) {
			die('{"error": "Operator name too long (36 max)"}');
		}
	}

	// if operator code set
	if($_POST["operator_code"]) {
		// if operator code is too long
		if(strlen($_POST["operator_code"]) > 4) {
			die('{"error": "Operator code too long (4 max)"}');
		}
	}

	// if identity set
	if($_POST["identity"]) {
		// if identity is too long
		if(strlen($_POST["identity"]) > 8) {
			die('{"error": "Identity too long (8 max)"}');
		}
	}

	// if vehicle type not 0, 1, 2, or 3
	// (0 = train, 1 = bus, 2 = ferry, 3 = tram/metro)
	if($_POST["vehicle_type"] != 0 && $_POST["vehicle_type"] != 1 && $_POST["vehicle_type"] != 2 && $_POST["vehicle_type"] != 3) {
		die('{"error": "Vehicle type must be 0, 1, 2, or 3"}');
	} else {
		$type = strval($_POST["vehicle_type"]);
	}

	// die($type);

	// if notes set
	if($_POST["notes"]) {
		// if notes are too long
		if(strlen($_POST["notes"]) > 512) {
			die('{"error": "Notes too long (512 max)"}');
		}
	}


	$ua = "Rejselog (PHP " . phpversion() . " cURL) (http://TomR.me)";

	// fetch CSRF token from RM form
	$curlCSRF = curl_init();
	curl_setopt($curlCSRF, CURLOPT_URL, "https://my.railmiles.me/journeys/rail/new");
	curl_setopt($curlCSRF, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curlCSRF, CURLOPT_HEADER, 1);
	// curl_setopt($curlCSRF, CURLOPT_VERBOSE, 1);

	curl_setopt($curlCSRF, CURLOPT_USERAGENT, $ua);

	// set session cookie
	curl_setopt($curlCSRF, CURLOPT_HTTPHEADER, array("Cookie: railmiles_sessionid=\"$RMcookie\""));
	
	$output = curl_exec($curlCSRF);

	curl_close($curlCSRF);

	// the following from
	// https://stackoverflow.com/a/41979193
	$headers = [];
	$output = rtrim($output);
	$data = explode("\n",$output);
	$headers['status'] = $data[0];
	array_shift($data);

	foreach($data as $part){

		//some headers will contain ":" character (Location for example), and the part after ":" will be lost, Thanks to @Emanuele
		$middle = explode(":",$part,2);

		//Supress warning message if $middle[1] does not exist, Thanks to @crayons
		if ( !isset($middle[1]) ) { $middle[1] = null; }

		$headers[trim($middle[0])] = trim($middle[1]);
	}
	// end from stackoverflow

	// if location header set
	if(isset($headers["Location"])) {
		die('{"error":"Looks like your session cookie is incorrect. It may have expired."}');
	}

	// if set-cookie set
	if(isset($headers["Set-Cookie"])) {
		$csrfToken = extract_csrf_token($headers["Set-Cookie"]);
	}


	// if we've gotten this far, we might have a CSRF token
	if(!isset($csrfToken)) {
		die('{"error":"Missing CSRF token"}');
	}

	$curlAddJourney = curl_init();

    $fields = array(
		"csrfmiddlewaretoken" => "$csrfToken",
		"origin_name" => $_POST["origin_name"] ? $_POST["origin_name"] : "Unset Origin",
		"origin" => "",
		"origin_platform" => $_POST["origin_platform"] ? $_POST["origin_platform"] : "",
		"act_dep_date" => $_POST["act_dep_date"] ? $_POST["act_dep_date"] : "",
		"act_dep_time" => $_POST["act_dep_time"] ? $_POST["act_dep_time"] : "",
		"destination_name" => $_POST["destination_name"] ? $_POST["destination_name"] : "Unset Destination",
		"destination" => "",
		"destination_platform" => $_POST["destination_platform"] ? $_POST["destination_platform"] : "",
		"act_arr_date" => $_POST["act_arr_date"] ? $_POST["act_arr_date"] : "",
		"act_arr_time" => $_POST["act_arr_time"] ? $_POST["act_arr_time"] : "",
		"route_description" => $_POST["route_description"] ? $_POST["route_description"] : "",
		"distance_miles" => $_POST["distance_miles"] ? $_POST["distance_miles"] : "",
		"distance_chains" => $_POST["distance_chains"] ? $_POST["distance_chains"] : "",
		"operator_name" => $_POST["operator_name"] ? $_POST["operator_name"] : "",
		"operator_code" => $_POST["operator_code"] ? $_POST["operator_code"] : "",
		"identity" => $_POST["identity"] ? $_POST["identity"] : "",
		"vehicle_type" => $type,
		"ids-TOTAL_FORMS" => "2",
		"ids-INITIAL_FORMS" => "0",
		"ids-MIN_NUM_FORMS" => "2",
		"ids-MAX_NUM_FORMS" => "1000",
		"ids-0-identity" => "",
		"ids-1-identity" => "",
		"plan_dep_date" => $_POST["plan_dep_date"] ? $_POST["plan_dep_date"] : "",
		"plan_dep_time" => $_POST["plan_dep_time"] ? $_POST["plan_dep_time"] : "",
		"origin_tz" => $_POST["origin_tz"] ? $_POST["origin_tz"] : "Europe/Copenhagen",
		"plan_arr_date" => $_POST["plan_arr_date"] ? $_POST["plan_arr_date"] : "",
		"plan_arr_time" => $_POST["plan_arr_time"] ? $_POST["plan_arr_time"] : "",
		"destination_tz" => $_POST["destination_tz"] ? $_POST["destination_tz"] : "Europe/Copenhagen",
		"hidden" => "10",
		"notes" => ""
    );

	// if vehicles set
	if($_POST["vehicles"]) {
		// split at comma
		$vehicles = explode(",", $_POST["vehicles"]);

		// ids-TOTAL_FORMS = number of vehicles, minimum 2
		$fields["ids-TOTAL_FORMS"] = count($vehicles) > 2 ? count($vehicles) : 2;

		// add new element for each vehicle, formatted as ids-X-identity
		for($i = 0; $i < count($vehicles); $i++) {
			$fields["ids-" . $i . "-identity"] = $vehicles[$i];
		}
	}

	// if notes set
	if($_POST["notes"]) {
		// remove newlines and replace with fullstops
		$fields["notes"] = str_replace("\n", ". ", $_POST["notes"]);
	} else {
		$fields["notes"] = "This journey was added by go.TomR.me/rejselog. No notes were set.";
	}

	// var_dump($fields);
	// die();

    // build the urlencoded data
    $postvars = http_build_query($fields);

    curl_setopt($curlAddJourney, CURLOPT_URL, "https://my.railmiles.me/journeys/rail/new");
	curl_setopt($curlAddJourney, CURLOPT_POST, 1);
	curl_setopt($curlAddJourney, CURLOPT_POSTFIELDS, $postvars);
    
	// set referer header
	curl_setopt($curlAddJourney, CURLOPT_REFERER, "https://my.railmiles.me/journeys/rail/new");

	// set session cookie and csrf cookie
	curl_setopt($curlAddJourney, CURLOPT_HTTPHEADER, array("Cookie: railmiles_sessionid=\"$RMcookie\"; csrftoken=\"$csrfToken\""));

	// return response instead of outputting
	curl_setopt($curlAddJourney, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($curlAddJourney, CURLOPT_USERAGENT, $ua);

	// set verbose output
	// curl_setopt($curlAddJourney, CURLOPT_VERBOSE, 1);

    // execute post
    $result = curl_exec($curlAddJourney);
	
	// if result contains "sorry"
	if(strpos($result, "Sorry") !== false) {
		die('{"error":"Sorry, something went wrong. Please try again."}');
	}

	// if result contains "added"
	if(strpos($result, "successfully added") !== false) {
		die('{"success":"Journey added!"}');
	}

	// close connection
    curl_close($curlAddJourney);


	// chatgpt!
	// "from this cookie, create a PHP function that will extract the csrftoken on its own"
	function extract_csrf_token($cookie_string) {
		$parts = explode(';', $cookie_string);
		foreach ($parts as $part) {
			$part = trim($part);
			if (strpos($part, 'csrftoken=') === 0) {
				return substr($part, 10);
			}
		}
    	return null;
	}
?>
