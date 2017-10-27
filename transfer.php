<?php

// NOT IN USE
exit;

$path = $_GET['path'];
$path_parts = explode('/', $path);
if (count($path_parts) > 2 || in_array('..', $path_parts)) {
	exit;
}

chdir("../../../../../../..");
require_once("./Services/Init/classes/class.ilIniFile.php");
$ilIliasIniFile = new ilIniFile("./ilias.ini.php");
$ilIliasIniFile->read();

$ilias_data_dir = $ilIliasIniFile->readVariable("clients", "datadir");
$client_id = $_COOKIE["ilClientId"];
$file = $ilias_data_dir . '/' . $client_id . '/vimp_upload/' . $path;
$filename = end($path_parts);

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Description: ' . $filename);
header('Accept-Ranges: bytes');
header("Content-Length: " . (string)filesize($file));
header("Connection: close");
$file = fopen($file, "rb");
fpassthru($file);
exit;