<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
if ((isset( $_GET['fileID'])) && (isset( $_GET['type'])) && !empty( $_GET['fileID']) && !empty( $_GET['type']))
{
$fileID=$_GET['docID'];
$filename='fiat.mp4'; //default file name

	if ($_GET['type']==1)
	{$filefolder=$video_folder;}
	Elseif ($_GET['type']==2)
	{$filefolder=$receipts_folder;}
		Elseif ($_GET['type']==3)
	{$filefolder=$receivedeinvoices;}
	Else
	{header("Location: " . $_SERVER["HTTP_REFERER"]);}

$file=$hddpath ."/". $filefolder."/". $filename;
echo $file;
header("Content-Description: File Transfer"); 
header("Content-Type: application/octet-stream"); 
header("Content-Disposition: attachment; filename=\"". basename($file) ."\""); 

readfile ($file);
exit(); 
}
Else
{header("Location: " . $_SERVER["HTTP_REFERER"]);}
?>