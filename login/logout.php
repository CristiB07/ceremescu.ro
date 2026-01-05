<?php
//update 31.12.2025
session_start();

// Verificare IP whitelist
include __DIR__ . '/ip_check.php';

include '../settings.php';

// Unset all session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Regenerate session ID for security
session_start();
session_regenerate_id(true);

// Set a flag that user just logged out
$_SESSION['logged_out'] = true;

// Redirect to home page
header("location: " . $strSiteURL);
exit();
?>