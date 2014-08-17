<?php
// Page: remote.php
// Author: Chris Bartek, Jr.
// Description: Remote Control Interface

// This page is a customizable remote control for sending server commands.

require_once('config.php');
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<title>Slawdog Remote Interface</title>
	<link href="css/style.css" rel="stylesheet">
</head>
<body>

<div id="buttonSetup">
	<h1>Set up button</h1>
    <input id="command" type="text" value=""><br><br>
	<a href="#" class="iconPrev">&laquo;</a><div class="iconView"></div><a href="#" class="iconNext">&raquo;</a><div class="clear spacer"></div>
    <button id="buttonSetupSave">Save</button> <button id="buttonSetupDelete">Delete</button> <button id="buttonSetupClose">Close</button>
</div>

<div id="remoteBoard">
    <div id="btn-0" class="item"></div>
    <div id="btn-1" class="item"></div>
    <div id="btn-2" class="item"></div>
</div>
<div id="remoteStatus">Loading interface, please wait...</div>

<script src="js/vendor/jquery-1.10.2.min.js"></script>
<script src="js/vendor/monaca.viewport.js"></script>
<script src="js/vendor/hammer.min.js"></script>
<script src="js/vendor/packery.pkgd.min.js"></script>
<script src="js/vendor/draggabilly.pkgd.min.js"></script>
<script src="js/remote.js"></script>

</body>
</html>