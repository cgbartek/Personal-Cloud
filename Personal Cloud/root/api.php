<?php 
// Page: api.php
// Author: Chris Bartek, Jr.
// Description: Internal API for AJAX calls

// By default assumes being called directly
$included = false;

header("Access-Control-Allow-Origin: *");
require_once('config.php');


if(!isset($api['a'])) {
	$api = $_REQUEST;
} else {
	// Being called via include, so output silently
	$included = true;
}

if(!$api['a']) {
	$return['error'] = 'No action requested.';
	apiReturn(json_encode($return));
}

// LOGIN
if($api['a'] == 'login') {
	$redirect = "remote";
	if($api['r']) {
		$redirect = $api['r'];
	}
	$username = $api['username'];
	if($api['u']){
		$username = $api['u'];
	}
	$password = md5($api['password']);
	if($api['p']){
		$password = $api['p'];
	}
	$results = Core::query("SELECT * FROM users WHERE username = '$username' AND password = '$password';");
	$row = $results->fetchArray(SQLITE3_ASSOC);
	if($row['username']){
		$_SESSION['username'] = strtolower($row['username']);
		$return['success'] = 1;
		if($redirect) {
			$return['redir'] = $api['redir'];
		}
	} else {
		$return['error'] = 'Username or password not found.';
	}
	apiReturn(json_encode($return));
}

// GET ICONS
if($api['a'] == 'iconList') {
	$return['success'] = 1;
	$return['list'] = glob("img/btns/*.*");
	$return['count'] = count($return['list'])-1;
	apiReturn(json_encode($return,JSON_FORCE_OBJECT));
}

// LOAD BUTTONS
if($api['a'] == 'loadButtons') {
	$return['success'] = 1;
	$results = Core::query("SELECT * FROM settings WHERE key LIKE 'remote.content' LIMIT 1");
	$row = $results->fetchArray(SQLITE3_ASSOC);
	$return['content'] = $row['val'];
	apiReturn(json_encode($return));
}

// SAVE BUTTONS
if($api['a'] == 'saveButtons') {
	$content = $api['content'];
	$return['success'] = 1;
	$results = Core::query("SELECT * FROM settings WHERE key LIKE 'remote.content' LIMIT 1");
	$row = $results->fetchArray(SQLITE3_ASSOC);
	if(isset($row['val'])) {
		$return['success'] = 2;
		Core::query("UPDATE settings SET val = '$content' WHERE key = 'remote.content';");
	} else {
		$return['success'] = 3;
		Core::query("INSERT INTO settings (key,val) VALUES('remote.content','$content');");
	}
	apiReturn(json_encode($return));
}

function apiReturn($json) {
	global $included;
	if(!$included) {
		die($json);
	} else {
		$apiReturn = $json;
	}
}
?>