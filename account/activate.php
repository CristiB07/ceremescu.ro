<?php
include '../settings.php';
require_once '../classes/common.php';

if (isSet($_GET['hash']) && $_GET['hash']!="") {
    // Validate hash parameter (should be alphanumeric)
    $hash = $_GET['hash'];
    if (!preg_match('/^[a-zA-Z0-9]+$/', $hash)) {
        header("location: $strSiteURL/index.php?message=ER");
        exit();
    }
    
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM site_accounts WHERE account_secret=? AND account_active='0'");
    $stmt->bind_param("s", $hash);
    $stmt->execute();
    $activationquery = $stmt->get_result();
    $activationcount = $activationquery->num_rows;
    
    if ($activationcount==1) {
        // Use prepared statement for update
        $stmt_update = $conn->prepare("UPDATE site_accounts SET account_active='1' WHERE account_secret=? AND account_active='0'");
        $stmt_update->bind_param("s", $hash);
        $activationresult = $stmt_update->execute();
        $stmt_update->close();
        
        if ($activationresult) {
            $stmt->close();
            header("location: $strSiteURL/account/login.php?message=AC");
            exit();
        }
        else {
            $stmt->close();
            header("location: $strSiteURL/account/login.php?message=ER");
            exit();
        }
    }
    $stmt->close();
}
else {
    //he just try to get here directly or something is wrong
    header("location: $strSiteURL/index.php?message=ER");
    exit();
}
?>