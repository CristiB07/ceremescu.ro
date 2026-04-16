<?php
include '../settings.php';
include '../classes/common.php';
If (IsSet($_GET['cID'])){
$datacitirii=date("Y-m-d H:m:s");
$usql="UPDATE readerrss_articole SET articol_data_citirii='$datacitirii' WHERE articol_ID=$_GET[cID];";
ezpub_query($conn,$usql);
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;}
else
{header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;}
?>