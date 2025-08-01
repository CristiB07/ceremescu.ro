<?php
//updated 15.05.2025
session_start(); 
include '../settings.php';
include '../classes/common.php';


check_inject();

// username and password sent from form
$myusername=$_POST['username'];
$mypassword=$_POST['password'];


$sql="SELECT * FROM elearning_students WHERE student_email='$myusername'";
$result=ezpub_query($conn,$sql);
echo $sql;
// ezpub_num_row is counting table row
$count=ezpub_num_rows($result,$sql);
echo $count;
// If result matched $myusername and $mypassword, table row must be 1 row
if($count==1){
$row = ezpub_fetch_array($result);
if (password_verify($mypassword, $row['student_password'])) {

$_SESSION['uid'] = $row['student_id']; // store session data
$_SESSION['userlogedin']="Yes";
$_SESSION['clearence'] = 'ELEARNING'; // store session data
$_SESSION['function']='STUDENT';
$_SESSION['code'] = $row['student_id']; // store session data

header("location:$strSiteURL". "/dashboard/dashboard.php");
}

else {
    echo "No user match";
header("location:$strSiteURL". "/elearning/login.php?message=WP");
}}
else {
    echo "No user match";
header("location:$strSiteURL". "/elearning/login.php?message=WP");
}
?>