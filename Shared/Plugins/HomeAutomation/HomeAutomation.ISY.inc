<?php
// Plugin: Home Automation (ISY-994i)
// Author: Chris Bartek, Jr.
// Description: This plugin controls the Universal Devices ISY series Insteon/X10/Zwave/Zigbee hubs. It was.
// tested with an ISY-994i but should also work on other models. In order for this plugin to work, you should
// set a static IP address in the device settings to avoid it changing on its own.

class HomeAutomation {
	
	// About
	public static function about() {
		return("Home Automation Plugin (ISY) (C) 2014 Chris Bartek, Jr.");
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
		
		// Automatically grab list of devices from ISY
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url."/rest/nodes/");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec ($ch);
		curl_close ($ch);
		$xml = json_decode(json_encode((array) simplexml_load_string($output)), 1);
		foreach($xml['node'] as $k => $v) {
			$devices[strtolower($v['name'])] = $v['address'];
		}
		
		// Send Command to Hub
		function sendToHub($url,$device,$cmd) {
			if($cmd == "off"){$cmd = "DFOF";$cmdX = "11";}
			else{$cmd = "DFON";$cmdX = "3";}
			$deviceId = str_replace(" ","%20",$device);
			$mode = "Insteon";
			if($deviceId <= 3){$mode = "X10";}
			$ch = curl_init();
			if($mode == "Insteon") {
				curl_setopt($ch, CURLOPT_URL,$url."/rest/nodes/$deviceId/cmd/$cmd");
			} else { // X10
				$deviceId = strtoupper($deviceId);
				curl_setopt($ch, CURLOPT_URL,$url."/rest/X10/$deviceId/$cmdX");
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