<?php
include '../settings.php';
include '../classes/common.php';
/* Get username */ 
$uname = $_POST['uname']; 


$sql = "SELECT count(*) as cntUser FROM elearning_students WHERE student_email = '".$uname."'";
$select = ezpub_query($conn, $sql);
$row = ezpub_fetch_array($select);

$count = $row['cntUser']; 
echo $count; 
?>