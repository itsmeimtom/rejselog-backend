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

	$journeyData = $_POST["data"];
	$RMcookie = $_POST["cookie"];

	header("Content-Type: application/json");
	
	if(!$journeyData || !$RMcookie) {
		die('{error: "Missing data"}');
	}

	$ua = "RejselogTest/0.0 (rj@TomR.me)";

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
		die('{error:"Looks like your session cookie is incorrect. It may have expired."}');
	}

	// if set-cookie set
	if(isset($headers["Set-Cookie"])) {
		$csrfToken = extract_csrf_token($headers["Set-Cookie"]);
	}


	// if we've gotten this far, we might have a CSRF token
	if(!isset($csrfToken)) {
		die('{error:"Missing CSRF token"}');
	}

	$curlAddJourney = curl_init();

    $fields = array(
		"csrfmiddlewaretoken" => "$csrfToken",
		"origin_name" => "test origin",
		"origin" => "",
		"origin_platform" => "1",
		"act_dep_date" => "16/04/2023",
		"act_dep_time" => "00:00",
		"destination_name" => "test destination",
		"destination" => "",
		"destination_platform" => "5",
		"act_arr_date" => "16/04/2023",
		"act_arr_time" => "00:01",
		"route_description" => "test route",
		"distance_miles" => "42",
		"distance_chains" => "2",
		"operator_name" => "test TOC",
		"operator_code" => "CODE",
		"identity" => "identity",
		"vehicle_type" => "0",
		"ids-TOTAL_FORMS" => "4",
		"ids-INITIAL_FORMS" => "0",
		"ids-MIN_NUM_FORMS" => "2",
		"ids-MAX_NUM_FORMS" => "1000",
		"ids-0-identity" => "123",
		"ids-1-identity" => "456",
		"ids-2-identity" => "789",
		"ids-3-identity" => "999",
		"plan_dep_date" => "16/04/2023",
		"plan_dep_time" => "00:00",
		"origin_tz" => "Europe/London",
		"plan_arr_date" => "16/04/2023",
		"plan_arr_time" => "00:01",
		"destination_tz" => "Europe/London",
		"hidden" => "10",
		"notes" => "TESTING"
    );

    
    // build the urlencoded data
    $postvars = http_build_query($fields);

	// die(var_dum	p($postvars));

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
	
	var_dump($result);

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
