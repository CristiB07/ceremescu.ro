<?php
//update 04.02.2025
session_start(); 
include '../settings.php';
require_once '../classes/common.php';

$success = false;
$myhash=$_POST['hash'];
if ($myhash != $_SESSION['_token']) {
	$csrf_error = "Invalid CSRF token";
	
}
else {
	$csrf_error = "";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($csrf_error)) {
check_inject();
// username and password sent from form
$myusername=$_POST['username'];
$mypassword=$_POST['password'];



$sql="SELECT * FROM date_utilizatori WHERE utilizator_Email='$myusername' and utilizator_Parola='$mypassword'";
$result=ezpub_query($conn,$sql);

// ezpub_num_row is counting table row
$count=ezpub_num_rows($result,$sql);

// If result matched $myusername and $mypassword, table row must be 1 row
if($count==1){
	$success = true;
		unset($_SESSION["_token"]);
		unset($_SESSION["token_expire"]);
$row = ezpub_fetch_array($result);

//create session data
$_SESSION['uid'] = $row['utilizator_ID']; // store session data
$_SESSION['code'] = $row['utilizator_Code']; // store session data
$_SESSION['clearence'] = $row['utilizator_Role']; // store session data
$_SESSION['function']=$row['utilizator_Function'];
$_SESSION['team']=$row['utilizator_Team'];
$_SESSION['shop']=$row['utilizator_Shop'];
$_SESSION['crm']=$row['utilizator_CRM'];
$_SESSION['billing']=$row['utilizator_CRM'];
$_SESSION['sales']=$row['utilizator_Billing'];
$_SESSION['cms']=$row['utilizator_CMS'];
$_SESSION['projects']=$row['utilizator_Projects'];
$_SESSION['administrative']=$row['utilizator_Administrative'];
$_SESSION['lab']=$row['utilizator_Lab'];
$_SESSION['clients']=$row['utilizator_Clients'];
$_SESSION['shop']=$row['utilizator_Shop'];
$_SESSION['elearning']=$row['utilizator_Elearning'];
$_SESSION['userlogedin']="Yes";

//redirect to userdashboard
header("location:$strSiteURL". "/dashboard/dashboard.php");
}

else {
header("location:index.php?message=WP");
}}
else {
	//he just try to get here directly or something is wrong
header("location:index.php?message=ER");
}
?>