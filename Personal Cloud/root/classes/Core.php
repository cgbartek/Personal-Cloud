<?php
// Class: Slawdog Personal Cloud Core
// Author: Chris Bartek, Jr.
// Description: Main Application Class

class Core {

	private function __construct() {}
	private function __clone() {}
	public function __destruct() {}

	public static $plugins = array();
	public static $commands = array();
	public static $rewrites = array();

	// Add Plugin
	public static function addPlugin($plugin) {
		self::$plugins[] = $plugin;
	}
	
	// Print the result and terminate
	public static function echoResult($result) {
		// A plugin tried to handle the command, but nothing relevant was returned.
		if($result['count'] === 0) {
			$wompwomp = array("No luck.", "No results.", "No love.", "Got nothin'.", "Sorry, no results.");
			$result['text'] = $wompwomp[array_rand($wompwomp)];
		}
		echo $result['text'];
		die();
	}
	
	// Look for plugins with specific, registered intent
	public static function queryPlugins($command,$silent=false) {
		foreach(self::$commands as $k => $v) {
			if($command[0] == $k) {
				$return = $v::command($command);
				if(!$silent){
					self::echoResult($return); // requires PHP 5.5+ to work
				}
			}
		}
	}
	
	// Let all plugins attempt to guess the intent
	public static function tryPlugins($command) {
		foreach(self::$plugins as $k => $v) {
			$try = $v::tryIt($command);
			if($try){
				self::echoResult($try);
			}
		}
	}
	
	// Launch an executable
	public static function execInBackground($path, $exe, $args = "") { 
		if (file_exists($path . $exe)) { 
			chdir($path); 
			if (substr(php_uname(), 0, 7) == "Windows") {
				pclose(popen("start \"Slawdog\" /B \"" . $exe . "\" " . escapeshellarg($args), "r"));    
			} else { 
				exec("./" . $exe . " " . escapeshellarg($args) . " > /dev/null &");    
			} 
		} 
	}
	
	// Is an executable currently running?
	public static function isAppRunning($pattern) {
		$pattern = "~($pattern)\.exe~i";
		$task_list = array();
		exec("tasklist 2>NUL", $task_list);
		foreach ($task_list AS $task_line) {
			if (preg_match($pattern, $task_line, $out)) {
				return true;
			}
		}
	}
	
	// Recursively search a folder
	public static function globRecursive($pattern, $flags = 0) {
		$files = glob($pattern, $flags);
		foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
			$files = array_merge($files, self::globRecursive($dir.'/'.basename($pattern), $flags));
		}
		return $files;
	}
	
	// Database query
	public static function query($q) {
		global $db;
		return($db->query($q));
	}
	
	// Database get val
	public static function get($q) {
		global $db;
		$results = $db->query("SELECT * FROM settings WHERE key = '$q' LIMIT 1");
		$row = $results->fetchArray(SQLITE3_ASSOC);
		$result = $row['val'];
		return($result);
	}
	
}

?>