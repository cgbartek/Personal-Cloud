<?php
// Plugin: Home Automation
// Author: Chris Bartek, Jr.
// Description: This plugin controls X10 and Insteon automation equipment. Compatible models: 
// - Universal Devices ISY Series (tested with ISY-994i)
// - Insteon Hub (should also work with SmartLinc 2414N)
// - X10 ActiveHome Pro (tested with CM15A)

//$automationDevice = "ISY";
//$automationDevice = "Insteon";
//$automationDevice = "X10";

$automationDevice = Core::get("automation.system");

if ($automationDevice) {
	include("HomeAutomation.$automationDevice.inc");
}

?>