<?php
// Plugin: Test Plugin
// Author: Chris Bartek, Jr.
// Description: Plugin for testing purposes. Works great as a plugin template.

class Test {
	
	// About
	public static function about() {
		return("Test Plugin (C) 2014 Chris Bartek, Jr.");
	}
	
	// Initialize Plugin
	public static function init() {
		// Register Base Commands (the individual commands recognized by this plugin)
		Core::$commands['test'] = get_class($this);
		Core::$commands['testing'] = get_class($this);
		
		// Register Rewrite Commands (commands that are likely misunderstood by voice control)
		Core::$rewrites['best'] = 'test';
	}
	
	// Process Commands
	public static function command($command) {
		if($command[0] == "test") {
			$result['text'] = 'It works!';
			return($result);
		}
		if($command[0] == "testing") {
			$result['text'] = 'This also works!';
			return($result);
		}
	}
	
}

// Initialization (make sure you change these
Test::init();
?>