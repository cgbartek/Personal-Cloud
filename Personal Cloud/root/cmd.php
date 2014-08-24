<?php
// Page: Command Service
// Author: Chris Bartek, Jr.
// Description: Slawdog Server Command Module

// This page receives textual input and funnels it to the appropriate plugin, as well as handles a few things internally.

require_once('config.php');

// Use GET or POST
$input = $_REQUEST;

// No action string? Then let's use the whole querystring (don't do this unless you know the user is authenticated)
if(!$input['a'] && !$input['user']) {
	$input['a'] = $_SERVER["QUERY_STRING"];
}

// Break into an array for parsing
$command = explode(' ',$input['a']);

// Does the base command match a registered plugin?
Core::queryPlugins($command);

// Attempt to let the plugins non-explicitly try and figure it out
Core::tryPlugins($command);

// Built-in Commands
if($command[0] == "say" || $command[0] == "echo") {
	$result['text'] = '"'.substr($input['a'],strlen($command[0])+1).'"';
	Core::echoResult($result);
}

if($command[0] == "plugins") {
	foreach(Core::$commands as $k => $v) {
		$commandList[] = $k;
	}
	$commandList = implode(", ",$commandList);
	foreach(Core::$rewrites as $k => $v) {
		$rewriteList[] = $k;
	}
	$rewriteList = implode(", ",$rewriteList);
	$result['text'] = "Plugins: ".implode(", ",Core::$plugins)."<br>Commands: $commandList"."<br>Rewrites: $rewriteList";
	Core::echoResult($result);
}

// Command was blank
if(!$input['a']) {
	$result['text'] = "No command given.";
	Core::echoResult($result);
}

// Try one last time by rewriting possible voice misunderstandings
foreach(Core::$rewrites as $k => $v) {
	if($command[0] == $k) {
		$command[0] = $v;
		queryPlugins($command);
	}
}
$wompwomp = array("Huh?", "Eh?", "What?", "I don't understand.");
$result['text'] = $wompwomp[array_rand($wompwomp)];
Core::echoResult($result);
?>