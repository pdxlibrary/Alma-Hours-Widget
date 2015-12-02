<?php

/* CONFIGURATION */

// set your Alma Hours API Key
define("ALMA_HOURS_API_KEY","XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX");

// set the Caching Frequency - Daily, Hourly or None (recommended default: Daily)
define("CACHE_FREQUENCY","Daily");

// if this file is not on the same host as the widget JavaScript file, cross-site scripting (XSS) access needs to be allowed
$allowed_domains = array();

/* END OF CONFIGURATION */


if(in_array($_SERVER['HTTP_ORIGIN'],$allowed_domains))
	header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
else
	header('Access-Control-Allow-Origin: none');

$hours = array();

$ch = curl_init();
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

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
	$from = date("Y-m-d",strtotime("+1 day",strtotime($_GET['from'])));
else
	$from = date("Y-m-d",strtotime("+1 day"));

if(isset($_GET['to']))
	$to = date("Y-m-d",strtotime("+1 day",strtotime($_GET['to'])));	// need to increase by one day, because Alma hours API is not inclusive of range
else
	$to = date("Y-m-d",strtotime("+7 days"));


// each date must be queried by itself to work-around an issue with the API returning invalid data for multi-day queries
for($date = date("Y-m-d",strtotime($from)); strtotime($date) <= strtotime($to); $date = date("Y-m-d",strtotime("+1 day",strtotime($date))))
{
	$xml_result = false;
	
	// BUILD REST REQUEST URL
	$url = "https://api-ap.hosted.exlibrisgroup.com/almaws/v1/conf/libraries/".$library."/open-hours?apikey=".ALMA_HOURS_API_KEY;
	if(strcmp($from,''))
		$url .= "&from=".$date;
	if(strcmp($to,''))
		$url .= "&to=".$date;

	if(isset($_GET['debug']))
		print("URL: $url<br>\n");
	
	if(strcmp(CACHE_FREQUENCY,"None"))
	{
		// check cache for hours
		if(file_exists("cache/".$library."_".$date.".xml"))
		{
			// check last modified datestamp
			$cache_expired = false;
			switch(CACHE_FREQUENCY)
			{
				case 'Hourly': if(filemtime("cache/".$library."_".$date.".xml") < strtotime(date("Y-m-d H:00:00",strtotime("now")))) $cache_expired = true;
				default: if(filemtime("cache/".$library."_".$date.".xml") < strtotime(date("Y-m-d 00:00:00",strtotime("now")))) $cache_expired = true;
			}
			// $cache_expired = true;
			if(!$cache_expired)
			{
				$xml_result = simplexml_load_file("cache/".$library."_".$date.".xml");
				if(isset($_GET['debug'])) print("loaded data from cache file: cache/".$library."_".$date.".xml<br>\n");
			}
		}
	}
	
	// if no cache data available, query the Alma API
	if(!$xml_result)
	{
		// use curl to make the API request
		curl_setopt($ch,CURLOPT_URL, $url);
		$result = curl_exec($ch);
		
		if(isset($_GET['debug']))
		{
			print("xml result from API<br>\n");
			print("<pre>".htmlspecialchars($result)."</pre>");
		}
				
		// save result to cache
		if(strcmp(CACHE_FREQUENCY,"None") && is_writable("cache/".$library."_".$date.".xml"))
		{
			file_put_contents("cache/".$library."_".$date.".xml",$result);
		}
		
		$xml_result = simplexml_load_string($result);
	}
	
	// PARSE RESULTS
	if(!isset($xml_result->day))
	{
		// work around for missing date info for full day closures
		$hours_obj = new stdClass();
		$actual_date = date("Y-m-d",strtotime("-1 day",strtotime($date)));
		$hours_obj->date = gmdate($date_format,strtotime($actual_date));
		$hours_obj->closed = true;
		$open_hours[$actual_date] = $hours_obj;
	}
	else
	{
		foreach($xml_result->day as $day)
		{
			if(strtotime($day->date) > 0)
			{
				$hours_obj = new stdClass();
				
				$formatted_date = gmdate($date_format,strtotime($day->date));
				$hours_obj->date = $formatted_date;
				
				// foreach open-close block for this date, collect the hours
				foreach($day->hours->hour as $hours)
				{
					if(strtotime($day->date.$hours->from) > 0 && strtotime($day->date.$hours->to) > 0 )
					{
						// Midnight Fix
						if(!strcmp(gmdate("g:ia",strtotime($day->date.$hours->from)),"12:00am"))
							$formatted_open_time = "Midnight";
						else
							$formatted_open_time = gmdate($time_format,strtotime($day->date.$hours->from));
						if(!strcmp(gmdate("g:ia",strtotime($day->date.$hours->to)),"11:59pm"))
							$formatted_close_time = "Midnight";
						else
							$formatted_close_time = gmdate($time_format,strtotime($day->date.$hours->to));
						
						if(!strcmp($formatted_open_time,'Midnight') && !strcmp($formatted_close_time,'Midnight'))
						{
							// special case - open 24 hours
							$hours_obj->open24hours = true;
						}
						else
						{
							// standard hours
							$hours_obj->hours[] = array("open"=>$formatted_open_time,"close"=>$formatted_close_time);
						}
					}
					else
					{
						// Full Day Closure
						$hours_obj->closed = true;
					}
				}
				$open_hours[gmdate("Y-m-d",strtotime($day->date))] = $hours_obj;
			}
		}
	}
	
	if(isset($_GET['debug']))
	{
		print("<pre>\n");
		print_r($xml_result);
		print("</pre>\n");
	}
}

// CREATE JSON OUTPUT
print(json_encode($open_hours));

if(isset($_GET['debug']))
{
	print("<pre>\n");
	print("calculated hours: \n");
	print_r($open_hours);
	print("</pre>\n");
}

curl_close($ch);

?>