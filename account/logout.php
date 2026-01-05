<?php
include '../settings.php';
//update 8.01.2025
session_start(); 

// Clear all session variables
$_SESSION = array();

// Destroy session cookie if it exists
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

session_destroy();
header("location:$strSiteURL");
exit();
?>