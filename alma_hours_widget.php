<?php

/* CONFIGURATION */

// set your Alma Hours API Key
define("ALMA_HOURS_API_KEY","XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX");

// set the Caching Frequency - Daily, Hourly or None (default: Daily)
define("CACHE_FREQUENCY","Daily");

// if this file is not on the same host as the widget JavaScript file, cross-site scripting (XSS) access needs to be allowed
$allowed_domains = array();

/* END OF CONFIGURATION */


if(in_array($_SERVER['HTTP_ORIGIN'],$allowed_domains))
	header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
else
	header('Access-Control-Allow-Origin: none');

$hours = array();

// REQUIRED PARAMETERS
if(isset($_GET['library']))
	$library = urlencode($_GET['library']);
else
{
	// ERROR: required parameter missing
	print("ERROR: required 'library' parameter missing<br>\n");
	exit();
}

// OPTIONAL PARAMETERS
if(isset($_GET['date_format']))
	$date_format = $_GET['date_format'];
else
	$date_format = "m/d/Y";

if(isset($_GET['time_format']))
	$time_format = $_GET['time_format'];
else
	$time_format = "g:ia";

if(isset($_GET['from']))
	$from = urlencode($_GET['from']);
else
	$from = "";

if(isset($_GET['to']))
	$to = urlencode(date("Y-m-d",strtotime("+1 day",strtotime($_GET['to']))));	// need to increase by one day, because Alma hours API is not inclusive of range
else
	$to = "";




// BUILD REST REQUEST URL
$url = "https://api-ap.hosted.exlibrisgroup.com/almaws/v1/conf/libraries/".$library."/open-hours?apikey=".ALMA_HOURS_API_KEY;
if(strcmp($from,''))
	$url .= "&from=".$from;
if(strcmp($to,''))
	$url .= "&to=".$to;

if(isset($_GET['debug']))
	print("URL: $url<br>\n");


$xml_result = false;

if(strcmp(CACHE_FREQUENCY,"None"))
{
	// check cache for hours
	if(file_exists("cache/$library-$from-$to.xml"))
	{
		// check last modified datestamp
		$cache_expired = false;
		switch(CACHE_FREQUENCY)
		{
			case 'Hourly': if(filemtime("cache/$library-$from-$to.xml") < strtotime(date("Y-m-d H:00:00",strtotime("now")))) $cache_expired = true;
			default: if(filemtime("cache/$library-$from-$to.xml") < strtotime(date("Y-m-d 00:00:00",strtotime("now")))) $cache_expired = true;
		}
		if(!$cache_expired)
			$xml_result = simplexml_load_file("cache/$library-$from-$to.xml");
	}
}

// if no cache data available, query the Alma API
if(!$xml_result)
{
	// use curl to make the API request
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);
	
	// parse the response xml
	$xml_result = simplexml_load_string($result);
	
	// save result to cache
	if(strcmp(CACHE_FREQUENCY,"None") && is_writable("cache/$library-$from-$to.xml"))
	{
		file_put_contents("cache/$library-$from-$to.xml",$result);
	}
}

// CREATE JSON OUTPUT
if(strcmp($from,''))
	$expected_start_date = gmdate("Y-m-d",strtotime("yesterday",strtotime($from)));
else
	$expected_start_date = gmdate("Y-m-d",strtotime("yesterday",strtotime("now")));
if(isset($_GET['to']))
	$expected_end_date = gmdate("Y-m-d",strtotime($_GET['to']));
else
	$expected_end_date = gmdate("Y-m-d",strtotime("+6 days"));
$expected_current_date = $expected_start_date;
foreach($xml_result->day as $day)
{
	if(strtotime($day->date) > 0)
	{
		// work around for missing date info for full day closures
		$breakout_count = 0;
		while(strcmp(gmdate("Y-m-d",strtotime($day->date)),$expected_current_date))
		{
			$formatted_date = gmdate($date_format,strtotime($expected_current_date));
			$hours[$expected_current_date] = array("date"=>$formatted_date,"closed"=>true);
			$expected_current_date = gmdate("Y-m-d",strtotime("tomorrow",strtotime($expected_current_date)));
			
			if(strtotime($expected_current_date) > strtotime($expected_end_date))
				break;
			
			$breakout_count++;
			if($breakout_count > 30)
			{
				break;
			}
		}
		
		$formatted_date = gmdate($date_format,strtotime($day->date));
		
		if(strtotime($day->date.$day->hours->hour->from) > 0 && strtotime($day->date.$day->hours->hour->to) > 0 )
		{
			// Midnight Fix
			if(!strcmp(gmdate("g:ia",strtotime($day->date.$day->hours->hour->from)),"11:59pm"))
				$formatted_open_time = "Midnight";
			else
				$formatted_open_time = gmdate($time_format,strtotime($day->date.$day->hours->hour->from));
			if(!strcmp(gmdate("g:ia",strtotime($day->date.$day->hours->hour->to)),"11:59pm"))
				$formatted_close_time = "Midnight";
			else
				$formatted_close_time = gmdate($time_format,strtotime($day->date.$day->hours->hour->to));
			
			$hours[gmdate("Y-m-d",strtotime($day->date))] = array("date"=>$formatted_date,"open"=>$formatted_open_time,"close"=>$formatted_close_time);
		}
		else
		{
			// Full Day Closure
			$hours[gmdate("Y-m-d",strtotime($day->date))] = array("date"=>$formatted_date,"closed"=>true);
		}
		$expected_current_date = gmdate("Y-m-d",strtotime("tomorrow",strtotime($expected_current_date)));
	}
}

// workaround to include missing full day closures at the end of the result set
$breakout_count = 0;
while(strtotime($expected_current_date) <= strtotime($expected_end_date))
{
	$formatted_date = gmdate($date_format,strtotime($expected_current_date));
	$hours[$expected_current_date] = array("date"=>$formatted_date,"closed"=>true);
	$expected_current_date = gmdate("Y-m-d",strtotime("tomorrow",strtotime($expected_current_date)));
	
	$breakout_count++;
	if($breakout_count > 30)
	{
		print("ERROR: breakout!!<br>\n");
		break;
	}
}


print(json_encode($hours));

if(isset($_GET['debug']))
{
	print("<pre>\n");
	print_r($hours);
	print_r($xml_result);
	print("</pre>\n");
}




?>