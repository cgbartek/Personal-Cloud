<?php
// Plugin: Home Automation (X10 Active Home Pro)
// Author: Chris Bartek, Jr.
// Description: This plugin controls the CM15A USB X10 interface.
// In order to make this plugin work, you must have a CM15A module plugged into the wall and a USB port, and
// install the ActiveHome Pro software as well as the SDK. If you have problems with the AHP installation,
// the Marmitek version is available on the web for free.

class HomeAutomation {
	
	// About
	public static function about() {
		return("Home Automation Plugin (X10) (C) 2014 Chris Bartek, Jr.");
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
		
		$devices = array("lamp"=>"a2","light"=>"a1","bedroom light"=>"a1");
		
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
			$cmd = "sendplc A1 all$which$intent";
			Core::execInBackground('plugins/HomeAutomation/','runahcmd.exe',$cmd);
			$result['text'] = "Turned $which $intent.";
			return($result);
		}
		if(array_search("all", $command) && array_search("off", $command)) {
			$cmd = "sendplc A1 AllLightsOff";
			Core::execInBackground('plugins/HomeAutomation/','runahcmd.exe',$cmd);
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
				$cmd = "sendplc $selectedDevice $intent";
				Core::execInBackground('plugins/HomeAutomation/','runahcmd.exe',$cmd);
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