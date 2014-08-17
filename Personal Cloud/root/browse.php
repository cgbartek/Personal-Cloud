<?php
// Page: browse.php
// Author: Chris Bartek, Jr.
// Description: File/Folder Browser and Viewer

// This page acts as both a folder browser and a file viewer/downloader.

require_once('config.php');

$file = isset($_REQUEST['file']) ? $_REQUEST['file'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$zip = isset($_REQUEST['zip']) ? $_REQUEST['zip'] : '';
$dir = isset($_REQUEST['path']) ? $_REQUEST['path'] : '';
$fso = new COM('Scripting.FileSystemObject'); 
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=450px, user-scalable=no">
    <link rel="shortcut icon" type="image/ico" href="img/favicon.ico">
    <title>Slawdog Cloud Browse</title>
	<link href="css/style.css" rel="stylesheet">
</head>
<body>
<?php  
// Load file info
if($file) {
	if(!$action) {
		$fileStats = stat($file);
		echo "<a class='selection back' href='browse.php?path=".dirname($file)."\'>..</a><br /><br />";
		echo "<strong>Name: </strong>".basename($file)."<br />";
		echo "<small><strong>Path: </strong>".dirname($file)."\<br /><br /></small>";
		echo "<strong>Size: </strong>".file_size($fileStats['size'])."<br />";
		echo "<strong>Created: </strong>".date("m-d-Y h:i:s a",$fileStats['ctime'])."<br />";
		echo "<strong>Modified: </strong>".date("m-d-Y h:i:s a",$fileStats['mtime'])."<br />";
		echo "<strong>Accessed: </strong>".date("m-d-Y h:i:s a",$fileStats['atime'])."<br /><br />";
		
		echo "<a class='btn' href='browse.php?file=$file&action=download'>Download</a> &nbsp; ";
		echo "<a class='btn' href='browse.php?file=$file&action=download&zip=1'>Download as ZIP</a><br />";
		//echo "<a class='btn' href='browse.php?file=$file&action=open'>Open on host</a>";
		die();
	}
	// Download requested
	if($action == "download") {
		if (file_exists($file)) {
			// ZIP the file if necessary
			if($zip) {
				$zipfile = new ZipArchive();
				$filename = dirname($file)."\\".basename($file).".zip";
				//echo $filename;
				
				if ($zipfile->open($filename, ZipArchive::CREATE)!==TRUE) {
					die("cannot open <$filename>\n");
				}
				
				$zipfile->addFile(dirname($file)."\\".basename($file),basename($file));
				//echo "numfiles: " . $zipfile->numFiles . "\n";
				//echo "status:" . $zipfile->status . "\n";
				$zipfile->close();
				$file = $filename;
			}
			
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.basename($file));
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			ob_clean();
			flush();
			readfile($file);
			die();
		}
	}
}

// Load drive info
if(!$dir) {
    $D = $fso->Drives;
    $type = array("Unknown","Removable","Fixed","Network","CD-ROM","RAMDisk");
    foreach($D as $d) {
		$dO = $fso->GetDrive($d);
		$s = "";
		$n = "Local Volume";
		if($dO->DriveType == 3) {
			$n = $dO->Sharename;
		} else if($dO->IsReady) {
			if($dO->VolumeName) {
				$n = $dO->VolumeName;
			}
		   //$s = " (".file_size($dO->FreeSpace) . " free of: " . file_size($dO->TotalSize).")";
		} else {
		   $n = "[Drive not ready]"; 
		}
   echo "<a class='selection drive' style='background-image:url(img/".strtolower($type[$dO->DriveType]).".png)' href='browse.php?path=".strtolower($dO->DriveLetter).":\'>".$dO->DriveLetter . ": - " . $n ."</a><br>"; //. " - " . $type[$dO->DriveType] . $s . 
    } 
    
      
} 

// Load directory info
else {

    // Open the folder 
    $dir_handle = @opendir($dir) or die("<a class='selection back' href='javascript:window.history.back();'>..</a><br /><br /> Unable to open $dir");

	if(strlen($dir) <= 3) {
		echo "<a class='selection back' href='browse.php'>..</a><br />"; 
	}

    // Loop through the folders 
    while ($file = readdir($dir_handle)) { 
		if($file == ".") {
			continue;
		}
		if($file == "..") {
			if(strlen($dir) > 3) {
				$backdir = str_replace(':\\\\',':\\',dirname($dir).'\\');
				echo "<a class='selection back' href='browse.php?path=".$backdir."'>..</a><br />"; 
			}
			continue;
		}
		
		if(!is_file($dir.$file)) {
			$backdir = str_replace('\\\\','\\',$dir.'\\');
        	echo "<a class='selection folder' href='browse.php?path=".$backdir.$file."\'>$file</a><br />";
		}
    }
	
	echo "<br />";
	
	// Loop through the files 
	$dir_handle = @opendir($dir) or die("<a class='selection back' href='javascript:window.history.back();'>..</a><br /><br /> Unable to open $dir");
    while ($file = readdir($dir_handle)) { 
		if($file == "." || $file == "..") {
			continue;
		}		
		if(is_file($dir.$file)) {
			$ext = pathinfo($file, PATHINFO_EXTENSION);
        	echo "<a class='selection file $ext' href='browse.php?file=".$dir.$file."'>$file</a><br />";
		}
    }

    // Close 
    closedir($dir_handle); 

}

function file_size($size) { 
	$filesizename = array(" Bytes"," KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB"); 
	return $size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes'; 
} 
?>
</body>
</html>