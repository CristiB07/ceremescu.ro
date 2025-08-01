<?php
//update 8.01.2025
include '../settings.php';
session_start(); 
session_destroy();
header("location:$strSiteURL");
?>