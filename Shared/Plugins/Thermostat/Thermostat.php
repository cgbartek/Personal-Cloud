<?php
// Plugin: Thermostat
// Author: Chris Bartek, Jr.
// Description: Right now this plugin handles only the Radio Thermostat CT50, CT80, Filtrete 3M-50, and other clones with a Wi-Fi USNAP module.

// Please specify your device's URL below. Note: you should set your thermostat's IP to static if you have not done so already,
// as this script will not attempt to discover the device itself.

class Thermostat {
	
	// place your thermostat URL here (http:// included), without the trailing slash
	//public static $url = "http://192.168.0.3";

	// About
	public static function about() {
		return("Thermostat Plugin (C) 2014 Chris Bartek, Jr.");
	}
	
	// Initialize Plugin
	public static function init() {
		// Register Base Commands
		Core::$commands['thermostat'] = get_class($this);
		Core::$commands['temperature'] = get_class($this);
		Core::$commands['temp'] = get_class($this);
	}
	
	// Process Commands
	public static function command($command) {
		$url = Core::get("thermostat.url");
		
		// Get Thermostat Info
		function getThermostatInfo($url,$post="") {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url."/tstat");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			if ($post) {
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
			}
			$output = curl_exec ($ch);
			curl_close ($ch);
			$return = json_decode($output, true);
			return $return;
		}
		
		if($command[0] == "thermostat" || $command[0] == "temperature" || $command[0] == "temp") {
			// Get Info
			if($command[1] == "info") {
				$info = getThermostatInfo($url);
				if($info) {
					$result['text'] = "Temperature is ".$info['temp'].".";
				} else {
					$result['text'] = "No reply.";
				}
			}
			// Set Fan
			if($command[1] == "fan") {
				$fan = 0;
				$fanSet = "off";
				if($command[2] == "auto") {
					$fan = 1;
					$fanSet = "auto";
				}
				if($command[2] == "on") {
					$fan = 2;
					$fanSet = "on";
				}
				$info = getThermostatInfo($url,'{"fmode":'.$fan.'}');
				if($info) {
					$result['text'] = "Fan set to ".$fanSet.".";
				} else {
					$result['text'] = "No reply.";
				}
			}
			// Temperature Up/Down
			if($command[1] == "up" || $command[1] == "down" || $command[1] == "plus" || $command[1] == "minus") {
				// find out the current temp
				$info = getThermostatInfo($url);
				if($info['temp']) {
					$tmode = $info['tmode'];
					// adjust heat
					if($tmode == 1) {
						$mode = "heat";
						$temp = $info['t_heat'];
					} else {
					// adjust a/c
						$mode = "cool";
						$temp = $info['t_cool'];
					}
					// if user gave a number, use that, otherwise assume 1 degree
					$num = $command[2];
					if(!$num) {
						$num = 1;
					}
					// up or down?
					if($command[1] == "up" || $command[1] == "plus") {
						$newTemp = $temp + $num;
					}
					if($command[1] == "down" || $command[1] == "minus") {
						$newTemp = $temp - $num;
					}
					// set temp
					$info = getThermostatInfo($url,'{"tmode":'.$tmode.',"t_'.$mode.'":'.$newTemp.'}');
					if(isset($info['success'])) {
						$result['text'] = "Temperature set to $newTemp.";
					} else {
						$result['text'] = "Failed.";
					}
				} else {
					$result['text'] = "No reply.";
				}
			}
			// Set A/C Temperature
			if($command[1] == "cool" || $command[1] == "ac" || $command[1] == "a/c" || $command[1] == "cold") {
				// user requested to hold the temp?
				if($command[3] == "hold" || $command[4] == "hold") {
					$hold = ',"hold":1';
				}
				// set temp
				$info = getThermostatInfo($url,'{"tmode":2,"t_cool":'.$command[2].$hold.'}');
				if(isset($info['success'])) {
					$result['text'] = "Temperature set to ".$command[2].".";
				} else {
					$result['text'] = "Failed.";
				}
			}
			// Set Heat Temperature
			if($command[1] == "heat" || $command[1] == "heater" || $command[1] == "furnace") {
				if($command[3] == "hold" || $command[4] == "hold") {
					$hold = ',"hold":1';
				}
				$info = getThermostatInfo($url,'{"tmode":1,"t_heat":'.$command[2].$hold.'}');
				if(isset($info['success'])) {
					$result['text'] = "Temperature set to ".$command[2].".";
				} else {
					$result['text'] = "Failed.";
				}
			}
			return($result);
		}
	}
	
}

// Initialization
Thermostat::init();
?>