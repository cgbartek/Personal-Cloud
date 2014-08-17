<?php 
// Page: config.php
// Author: Chris Bartek, Jr.
// Description: Configuration settings

// Database
$db = new SQLite3('../../shared/db/slawdog.db');

// Load Classes
require_once('classes/Core.php');

// Misc.
$pagename = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
date_default_timezone_set('America/New_York');
error_reporting(E_ERROR);

// Authentication
session_start();
if(!isset($_SESSION['username']) && $_REQUEST['u'] && $_REQUEST['p']) {
	$api['a'] = "login";
	$api['u'] = $_REQUEST['u'];
	$api['p'] = $_REQUEST['p'];
	include('api.php');
	$apiReturn = json_decode($apiReturn);
	if($apiReturn['success']) {
		$success = true;
	}
}

if(!isset($_SESSION['username']) && !$success && $pagename != "api" && $pagename != "index") {
	if($_REQUEST['p']) {
		header('Location: /?redir='.$pagename.'&u='.$_REQUEST['u'].'&p='.$_REQUEST['p']);
	}
	die();
}

// Load Plugins
foreach (Core::globRecursive("../../shared/plugins/*.php") as $filename) {
    include $filename;
	$className = basename($filename, ".php");
	Core::addPlugin(basename($filename, ".php"));
}

?>