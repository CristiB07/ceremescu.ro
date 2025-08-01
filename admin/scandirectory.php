<?php
//work in progress
include '../settings.php';
include_once '../classes/common.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
}
$foldertoscan = $_GET['app'];
$post_dir = $hddpath . $admin_folder; 
$hashedfile= $post_dir . "/last_hash.txt";
// Check if a hash file exists
$filename = $post_dir . "/". $foldertoscan ."_md5file.txt";

if (file_exists($filename)) {
    echo "The file $filename exists";

if ($md5file != $last_hash) {
  // save the new hash
 echo "The file has changed since last check";
  // some file may have been added, removed or rename
  // do something..
} else {
  // no files has been added, removed or renamed, since last change.
  echo "The file has not changed since last check";
  // do something.. 
} }
else 
    {
    echo "The file $filename does not exist";

$filelist=scanDirectories("../$foldertoscan");
 
//creating hash of files in a directory
$allnames = implode(",", $filelist);

file_put_contents($post_dir . "/". $foldertoscan ."_md5file.txt",$allnames);

$md5file = md5_file($post_dir . "/". $foldertoscan ."_md5file.txt");

echo $md5file;
}

?>