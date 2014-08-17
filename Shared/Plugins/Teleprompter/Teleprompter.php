<?php
// Plugin: Teleprompter
// Author: Chris Bartek, Jr.
// Description: This plugin was written for a very specific task, namely for controlling a homemade teleprompter.

// The way it works is you open and edit the tele.html file on a screen like an iPad or laptop, and open teleremote.html
// on a smartphone. The presenter or stagehand can then move through the screen at the necessary pace.

class Teleprompter {
	
	// About
	public static function about() {
		return("Teleprompter Plugin (C) 2014 Chris Bartek, Jr.");
	}
	
	// Initialize Plugin
	public static function init() {
		// Register Base Commands
		Core::$commands['tele'] = get_class($this);
	}
	
	// Process Commands
	public static function command($command) {
		if($command[0] == "tele") {
			if($command[1] == "push") {
				Core::query("DELETE FROM stack;");
				Core::query("INSERT INTO stack (action,params) VALUES('teleprompt','$command[2]');");
				$result['text'] = "Teleprompt command: $command[2]";
			} else {
				$results = Core::query("SELECT * FROM stack WHERE action = 'teleprompt' LIMIT 1");
				$row = $results->fetchArray(SQLITE3_ASSOC);
				$result['text'] = $row['params'];
				Core::query("DELETE FROM stack;");
			}
			$result['count'] = 1;
			return($result);
		}
	}
	
}

// Initialization
Teleprompter::init();
?>