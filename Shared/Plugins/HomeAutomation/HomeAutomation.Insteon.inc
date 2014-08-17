<?php
// Plugin: Home Automation (Insteon Hub)
// Author: Chris Bartek, Jr.
// Description: This plugin controls Insteon Hub and SmartLinc devices. It was tested with a US 2242-222 Hub.
// In order for this plugin to work, you must use your router login to find your IP address (use the device's
// MAC address to locate it. You will also need the device's port, which should be listed on the device.
// You will also want to set up a DHCP reservation so that this address will not change in the future. 

class HomeAutomation {
	
	// About
	public static function about() {
		return("Home Automation Plugin (Insteon) (C) 2014 Chris Bartek, Jr.");
	}
	
	// Initialize Plugin
	public static function init() {
		// Register Base Commands
		Core::$commands['turn'] = get_class($this);
		Core::$commands['on'] = get_class($this);
		Core::$commands['off'] = get_class($this);
		Core::$commands['toggle'] = get_class($this);
		Core::$commands['all'] = get_class($this);

		// Register Rewrite Commands
		Core::$rewrites['turning'] = 'turn';
		Core::$rewrites['of'] = 'off';
		Core::$rewrites['yawn'] = 'on';
	}
	
	// Process Commands
	public static function command($command) {
		// URL (including port and authentication if necessary) must be defined in database settings table
		$url = Core::get("automation.url");
		
		// Load hard-coded list of devices
		$devices = json_decode(file_get_contents("../../shared/plugins/homeautomation/devices.json"),true);
		
		// Send Command to Hub
		function sendToHub($url,$device,$cmd) {
			$lookup = array("A"=>"6","B"=>"E","C"=>"2","D"=>"A","E"=>"1","F"=>"9","G"=>"5","H"=>"D","I"=>"7","J"=>"F","K"=>"3","L"=>"B","M"=>"0","N"=>"8","O"=>"4","P"=>"C",
			"1"=>"6","2"=>"E","3"=>"2","4"=>"A","5"=>"1","6"=>"9","7"=>"5","8"=>"D","9"=>"7","10"=>"F","11"=>"3","12"=>"B","13"=>"0","14"=>"8","15"=>"4","16"=>"C");
			
			if($cmd == "off"){$cmd = "14";$cmdX = "380";}
			else{$cmd = "12";$cmdX = "280";}
			$deviceId = str_replace(".","",$device);
			$mode = "Insteon";
			if($deviceId <= 3){$mode = "X10";}
			$ch = curl_init();
			if($mode == "Insteon") {
				curl_setopt($ch, CURLOPT_URL,$url."/3?0262".$deviceId."0F".$cmd."FF=I=3");
			} else { // X10
				$house = $lookup[strtoupper(substr($deviceId,0,1))];
				$unit = $lookup[substr($deviceId,1,2)];
				curl_setopt($ch, CURLOPT_URL,$url."/3?0263".$house.$unit."00=I=3");
				$output = curl_exec ($ch);
				sleep(1);
				curl_setopt($ch, CURLOPT_URL,$url."/3?0263".$house.$cmdX."=I=3");
			}
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$output = curl_exec ($ch);
			curl_close ($ch);
			$return = json_decode($output, true);
			return $return;
		}
		
		if(($key = array_search("of", $command)) !== false) {
			$command[$key] = "off";
		}
		if(($key = array_search("the", $command)) !== false) {
			unset($command[$key]);
			$command = array_values(array_filter($command));
		}
		
		if(array_search("lights", $command) || array_search("all", $command) || array_search("units", $command) || array_search("unit", $command)) {
			if(array_search("on", $command)) {
				$intent = "on";
			} else {
				$intent = "off";
			}
			if(array_search("lights", $command) || array_search("all", $command)) {
				$which = "lights";
			} else {
				$which = "units";
			}
			$result['text'] = "Turned $which $intent.";
			return($result);
		}
		if(array_search("all", $command) && array_search("off", $command)) {
			$result['text'] = "Turned all off.";
			return($result);
		}
		
		// On/Off
		if($command[0] == "turn"){
			array_shift($command);
		}
		if($command[0] == "off" || end($command) == "off") {
			$intent = "off";
			$pos = array_search($intent, $command);
			unset($command[$pos]);
		}
		if($command[0] == "on" || end($command) == "on") {
			$intent = "on";
			$pos = array_search($intent, $command);
			unset($command[$pos]);
		}
		if($intent) {
			$selectedDevice = $devices[implode(" ",$command)];
			if($selectedDevice) {
				sendToHub($url,$selectedDevice,$intent);
				$result['text'] = "Turned $intent.";
			} else {
				$result['text'] = "Device \"".implode(" ",$command)."\" not found.";
			}
			return($result);
		}
	}
	
}

// Initialization
HomeAutomation::init();
?>