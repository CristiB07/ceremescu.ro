<?php
include '../settings.php';
//update 8.01.2025
session_start(); 
session_destroy();
header("location:$strSiteURL");
?>