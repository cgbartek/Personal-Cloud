<?php
// Plugin: Media Player
// Author: Chris Bartek, Jr.
// Description: This plugin controls Slawdog Media Player.

class MediaPlayer {
	
	// About
	public static function about() {
		return("Media Player Plugin (C) 2014 Chris Bartek, Jr.");
	}
	
	// Initialize Plugin
	public static function init() {
		// Register Base Commands
		Core::$commands['stop'] = get_class($this);
		Core::$commands['unpause'] = get_class($this);
		Core::$commands['resume'] = get_class($this);
		Core::$commands['continue'] = get_class($this);
		Core::$commands['pause'] = get_class($this);
		Core::$commands['next'] = get_class($this);
		Core::$commands['skip'] = get_class($this);
		Core::$commands['previous'] = get_class($this);
		Core::$commands['play'] = get_class($this);

		// Register Rewrite Commands
		Core::$rewrites['un'] = 'unpause';
		Core::$rewrites['unties'] = 'unpause';
		Core::$rewrites['reason'] = 'unpause';
		Core::$rewrites['cause'] = 'pause';
		Core::$rewrites['POS'] = 'pause';
		Core::$rewrites['Haas'] = 'pause';
		Core::$rewrites['place'] = 'play';
		Core::$rewrites['late'] = 'play';
		Core::$rewrites['face'] = 'play';
	}
	
	// Process Commands
	public static function command($command) {
		// Launch Slawdog Media Player if not running
		if(!Core::isAppRunning("Media Player") && !Core::isAppRunning("edrt")) {
			Core::execInBackground('../../Media Player/','Media Player.exe',$input['a']);
		}
		// Stop
		if($command[0] == "stop") {
			Core::query("INSERT INTO stack (action,params) VALUES('stop','');");
			$result['text'] = "Stopping.";
			return($result);
		}
		// Resume
		if($command[0] == "unpause" || $command[0] == "resume" || $command[0] == "continue") {
			Core::query("INSERT INTO stack (action,params) VALUES('resume','');");
			$result['text'] = "Resuming.";
			return($result);
		}
		// Pause
		if($command[0] == "pause" || $command[0] == "freeze") {
			Core::query("INSERT INTO stack (action,params) VALUES('pause','');");
			$result['text'] = "Pausing.";
			return($result);
		}
		// Next Track
		if($command[0] == "next" || $command[0] == "skip") {
			Core::query("INSERT INTO stack (action,params) VALUES('next','');");
			$result['text'] = "Next song.";
			return($result);
		}
		// Previous Track
		if($command[0] == "previous" || $command[0] == "back" || $command[0] == "replay") {
			Core::query("INSERT INTO stack (action,params) VALUES('previous','');");
			$result['text'] = "Previous song.";
			return($result);
		}
		// Restart the Queue
		if(($command[0] == "play") && !$command[1]) {
			Core::query("INSERT INTO stack (action,params) VALUES('play','');");
			$result['text'] = "Playing.";
			return($result);
		}
		// Play Specific Query
		if($command[0] == "play") {
			// Play Artist
			if($command[1] == "artist" || $command[1] == "band") {
				$artist = trim("$command[2] $command[3] $command[4] $command[5]");
				$results = Core::query("SELECT * FROM media WHERE artist LIKE '%$artist%' ORDER BY RANDOM() LIMIT 20");
			}
			// Play Song
			if($command[1] == "song" || $command[1] == "track" || $command[1] == "on" || $command[1] == "some") {
				$song = trim("$command[2] $command[3] $command[4] $command[5] $command[6] $command[7] $command[8] $command[9]");
				$results = Core::query("SELECT * FROM media WHERE title LIKE '%$song%' ORDER BY RANDOM() LIMIT 1");
			}
			// Play Album
			if($command[1] == "album" || $command[1] == "record") {
				$album = trim("$command[2] $command[3] $command[4] $command[5] $command[6] $command[7] $command[8] $command[9]");
				$results = Core::query("SELECT * FROM media WHERE album LIKE '%$album%' LIMIT 20");
			}
			// Play Year
			if($command[1] == "year" || $command[1] == "decade") {
				$year = trim("$command[2]");
				$results = Core::query("SELECT * FROM media WHERE year LIKE '%$year%' ORDER BY RANDOM() LIMIT 20");
			}
			// Play File
			if($command[1] == "file" || $command[1] == "files" || $command[1] == "filename" || $command[1] == "mp3") {
				$file = trim("$command[2] $command[3] $command[4]");
				$results = Core::query("SELECT * FROM media WHERE filename LIKE '%$file%' ORDER BY RANDOM() LIMIT 20");
			}
			
			while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
				$addList[] = $row['id'];
				Core::query("INSERT INTO stack (action,params) VALUES('insertmedia','$row[filename]');");
			}
			$result['count'] = count($addList);
			if ($result['count'] > 1){ $addS = "s";}
			$result['text'] = "Playing ".count($addList)." song$addS.";
			return($result);
		}
	}
	
}

// Initialization
MediaPlayer::init();
?>