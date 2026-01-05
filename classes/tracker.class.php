<?php
/**
 * Activity & Error Tracker
 * Logs user activity and PHP/MySQL errors
 * Note: getRealIpAddr() is defined in common.php
 */

if(!isset($_SESSION)) { 
    session_start(); 
}

// Function to get or create visitor ID for non-logged users
function getVisitorId()
{
    if (!isset($_SESSION['visitor_id'])) {
        $_SESSION['visitor_id'] = 'visitor_' . bin2hex(random_bytes(8)) . '_' . time();
    }
    return $_SESSION['visitor_id'];
}

// Function to log activity
function logActivity($conn)
{
    try {
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . 
                       $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $d = date("Y-m-d H:i:s");
        $sessionID = session_id();
        $ip = getRealIpAddr();
        
        // Get user ID or visitor ID
        if (isset($_SESSION['uid']) && !empty($_SESSION['uid'])) {
            $uid = (int)$_SESSION['uid'];
            $visitor_id = null;
        } else {
            $uid = null;
            $visitor_id = getVisitorId();
        }
        
        $stmt = mysqli_prepare($conn, 
            "INSERT INTO application_logs(log_utilizator_id, log_visitor_id, log_IP_address, 
             log_utilizator_time, log_utilizator_page, log_utilizator_session) 
             VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isssss', $uid, $visitor_id, $ip, $d, $actual_link, $sessionID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } catch (Exception $e) {
        // Silent fail - don't break the page if logging fails
        error_log("Activity logging failed: " . $e->getMessage());
    }
}

// Function to log errors
function logError($conn, $error_type, $error_message, $error_file = null, $error_line = null)
{
    try {
        $d = date("Y-m-d H:i:s");
        $ip = getRealIpAddr();
        $sessionID = session_id();
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . 
                       $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
        // Get user ID, visitor ID, and user role
        if (isset($_SESSION['uid']) && !empty($_SESSION['uid'])) {
            $uid = (int)$_SESSION['uid'];
            $visitor_id = null;
            $user_role = isset($_SESSION['clearence']) ? $_SESSION['clearence'] : null;
        } else {
            $uid = null;
            $visitor_id = getVisitorId();
            $user_role = null;
        }
        
        // Sanitize error message (limit length)
        $error_message = substr($error_message, 0, 5000);
        $error_file = $error_file ? substr($error_file, 0, 500) : null;
        
        $stmt = mysqli_prepare($conn, 
            "INSERT INTO application_errors(error_utilizator_id, error_visitor_id, error_user_role, 
             error_type, error_message, error_file, error_line, error_IP_address, error_time, 
             error_page, error_session) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isssssissss', $uid, $visitor_id, $user_role, $error_type, 
                              $error_message, $error_file, $error_line, $ip, $d, 
                              $actual_link, $sessionID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } catch (Exception $e) {
        // Silent fail
        error_log("Error logging failed: " . $e->getMessage());
    }
}

// Set custom error handler for PHP errors
function customErrorHandler($errno, $errstr, $errfile, $errline)
{
    global $conn;
    
    // Don't log suppressed errors (@)
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $error_types = [
        E_ERROR => 'PHP Error',
        E_WARNING => 'PHP Warning',
        E_PARSE => 'PHP Parse Error',
        E_NOTICE => 'PHP Notice',
        E_CORE_ERROR => 'PHP Core Error',
        E_CORE_WARNING => 'PHP Core Warning',
        E_COMPILE_ERROR => 'PHP Compile Error',
        E_COMPILE_WARNING => 'PHP Compile Warning',
        E_USER_ERROR => 'PHP User Error',
        E_USER_WARNING => 'PHP User Warning',
        E_USER_NOTICE => 'PHP User Notice',
        E_STRICT => 'PHP Strict Notice',
        E_RECOVERABLE_ERROR => 'PHP Recoverable Error',
        E_DEPRECATED => 'PHP Deprecated',
        E_USER_DEPRECATED => 'PHP User Deprecated'
    ];
    
    $error_type = $error_types[$errno] ?? 'PHP Unknown Error';
    
    // Only log warnings and above (skip notices and deprecated for production)
    if ($errno >= E_WARNING) {
        logError($conn, $error_type, $errstr, $errfile, $errline);
    }
    
    // Don't execute PHP internal error handler
    return true;
}

// Set custom exception handler
function customExceptionHandler($exception)
{
    global $conn;
    logError($conn, 'PHP Exception', $exception->getMessage(), 
             $exception->getFile(), $exception->getLine());
}

// Set custom MySQL error handler wrapper
function logMySQLError($conn, $query = null)
{
    if (mysqli_error($conn)) {
        $error_message = mysqli_error($conn);
        if ($query) {
            $error_message .= " | Query: " . substr($query, 0, 500);
        }
        logError($conn, 'MySQL Error', $error_message);
    }
}

// Register error handlers
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');

// Log current page activity
logActivity($conn);
?>