<?php
//update 31.12.2025
session_start(); 

// Verificare IP whitelist
include __DIR__ . '/ip_check.php';

include '../settings.php';
require_once '../classes/common.php';

// Rate limiting - prevent brute force
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

// Reset attempts counter after 15 minutes
if (time() - $_SESSION['last_attempt_time'] > 900) {
    $_SESSION['login_attempts'] = 0;
}

// Block if too many attempts
if ($_SESSION['login_attempts'] >= 5) {
    $time_remaining = 900 - (time() - $_SESSION['last_attempt_time']);
    if ($time_remaining > 0) {
        header("location:index.php?message=TM&wait=" . ceil($time_remaining / 60));
        exit();
    }
}

$success = false;
$myhash = $_POST['hash'] ?? '';

if ($myhash != ($_SESSION['_token'] ?? '')) {
	$csrf_error = "Invalid CSRF token";
} else {
	$csrf_error = "";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($csrf_error)) {
    check_inject();
    
    // Validate input
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        header("location:index.php?message=WP");
        exit();
    }
    
    $myusername = trim($_POST['username']);
    $mypassword = $_POST['password'];
    
    // Validate email format
    if (!filter_var($myusername, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        header("location:index.php?message=WP");
        exit();
    }
    
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM date_utilizatori WHERE utilizator_Email=? LIMIT 1");
    $stmt->bind_param("s", $myusername);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $password_valid = false;
        $utilizator_id = intval($row['utilizator_ID']);
        $upgraded_status = intval($row['utilizator_Upgraded']);
        
        // Check if this is first login with plain text password (utilizator_Upgraded = 3)
        if ($upgraded_status === 3) {
            // First login - password is in plain text
            if ($mypassword === $row['utilizator_Parola']) {
                $password_valid = true;
                
                // Get encryption key from date_utilizatori_chei using SHA256 hash of email
                $email_hash = hash('sha256', $myusername);
                $stmt_key = $conn->prepare("SELECT cheie_secundara FROM date_utilizatori_chei WHERE cheie_primara = ?");
                $stmt_key->bind_param("s", $email_hash);
                $stmt_key->execute();
                $result_key = $stmt_key->get_result();
                
                if ($row_key = $result_key->fetch_assoc()) {
                    $encryption_key = $row_key['cheie_secundara'];
                    
                    // Encrypt the plain text password using AES-256-CBC
                    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
                    $encrypted_password = openssl_encrypt($mypassword, 'aes-256-cbc', hex2bin($encryption_key), OPENSSL_RAW_DATA, $iv);
                    $encrypted_data = base64_encode($iv . $encrypted_password);
                    
                    // Update database with encrypted password and set utilizator_Upgraded = 0
                    $stmt_update = $conn->prepare("UPDATE date_utilizatori SET utilizator_Parola = ?, utilizator_Upgraded = 0 WHERE utilizator_ID = ?");
                    $stmt_update->bind_param("si", $encrypted_data, $utilizator_id);
                    $stmt_update->execute();
                    $stmt_update->close();
                }
                $stmt_key->close();
            }
        } 
        // Standard login with encrypted password (utilizator_Upgraded = 0)
        else if ($upgraded_status === 0) {
            // Get encryption key using SHA256 hash of email
            $email_hash = hash('sha256', $myusername);
            $stmt_key = $conn->prepare("SELECT cheie_secundara FROM date_utilizatori_chei WHERE cheie_primara = ?");
            $stmt_key->bind_param("s", $email_hash);
            $stmt_key->execute();
            $result_key = $stmt_key->get_result();
            
            if ($row_key = $result_key->fetch_assoc()) {
                $encryption_key = $row_key['cheie_secundara'];
                
                try {
                    // Decrypt password from database
                    $encrypted_data = base64_decode($row['utilizator_Parola']);
                    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
                    $iv = substr($encrypted_data, 0, $iv_length);
                    $encrypted_password = substr($encrypted_data, $iv_length);
                    $decrypted_password = openssl_decrypt($encrypted_password, 'aes-256-cbc', hex2bin($encryption_key), 0, $iv);
                    
                    // Compare decrypted password with input
                    $password_valid = ($mypassword === $decrypted_password);
                } catch (Exception $e) {
                    $password_valid = false;
                }
            }
            $stmt_key->close();
        }
        // Old system - plain text (for backward compatibility)
        else {
            $password_valid = ($mypassword === $row['utilizator_Parola']);
        }
        
        if ($password_valid) {
            $success = true;
            
            // Clear old CSRF tokens
            unset($_SESSION["_token"]);
            unset($_SESSION["token_expire"]);
            
            // Reset login attempts
            $_SESSION['login_attempts'] = 0;
            
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            // Set session lifetime to 30 days (protected by VPN/IP access)
            $session_lifetime = 30 * 24 * 60 * 60; // 30 days in seconds
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                session_id(),
                time() + $session_lifetime,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
            
            // Set session expiration time in session data
            $_SESSION['session_expire'] = time() + $session_lifetime;
            
            // Generate new CSRF token for authenticated session
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            
            // Create session data with validation
            $_SESSION['uid'] = intval($row['utilizator_ID']);
            $_SESSION['code'] = htmlspecialchars($row['utilizator_Code'], ENT_QUOTES, 'UTF-8');
            $_SESSION['clearence'] = htmlspecialchars($row['utilizator_Role'], ENT_QUOTES, 'UTF-8');
            $_SESSION['function'] = htmlspecialchars($row['utilizator_Function'], ENT_QUOTES, 'UTF-8');
            $_SESSION['team'] = htmlspecialchars($row['utilizator_Team'], ENT_QUOTES, 'UTF-8');
            $_SESSION['shop'] = intval($row['utilizator_Shop']);
            $_SESSION['crm'] = intval($row['utilizator_CRM']);
            $_SESSION['billing'] = intval($row['utilizator_Billing']);
            $_SESSION['sales'] = intval($row['utilizator_Sales']);
            $_SESSION['cms'] = intval($row['utilizator_CMS']);
            $_SESSION['projects'] = intval($row['utilizator_Projects']);
            $_SESSION['administrative'] = intval($row['utilizator_Administrative']);
            $_SESSION['lab'] = intval($row['utilizator_Lab']);
            $_SESSION['clients'] = intval($row['utilizator_Clients']);
            $_SESSION['elearning'] = intval($row['utilizator_Elearning']);
            $_SESSION['newsletter'] = intval($row['utilizator_Newsletter']);
            $_SESSION['userlogedin'] = "Yes";
            $_SESSION['login_time'] = time();
            
            $stmt->close();
            
            // Update last login time in database
            $lastlogin = date('Y-m-d H:i:s');
            $stmt_update = $conn->prepare("UPDATE date_utilizatori SET utilizator_Lastlogin = ? WHERE utilizator_ID = ?");
            $stmt_update->bind_param("si", $lastlogin, $_SESSION['uid']);
            $stmt_update->execute();
            $stmt_update->close();
            
            // Redirect to user dashboard
            header("location:" . $strSiteURL . "/dashboard/dashboard.php");
            exit();
        }
    }
    
    $stmt->close();
    
    // Invalid credentials - increment attempts
    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();
    
    // Use constant time delay to prevent timing attacks
    usleep(rand(100000, 500000)); // 0.1-0.5 seconds random delay
    
    header("location:index.php?message=WP");
    exit();
    
} else {
    // CSRF error or direct access
    header("location:index.php?message=ERPP");
    exit();
}
?>