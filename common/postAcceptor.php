<?php
//Universal image uploader for simple-editor
//updated 29.12.2025
include dirname(__DIR__) . '/settings.php';

// Disable error display and only log errors
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set JSON content type header early
header('Content-Type: application/json');

/**
 * Send JSON error response
 */
function sendError($message, $code = 500) {
    http_response_code($code);
    echo json_encode(['error' => $message], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    exit;
}

/*******************************************************
 * Only these origins will be allowed to upload images *
 ******************************************************/
$accepted_origins = array("http://localhost", "https://localhost", "http://127.0.0.1", "http://localhost:8000", "$strSiteURL");

/*********************************************
 * Determine upload folder based on directory parameter *
 *********************************************/
$allowedDirectories = array('blog', 'pages', 'elearning', 'documents', 'shop', 'admin', 'legal', 'billing', 'projects');
$directory = isset($_POST['directory']) ? $_POST['directory'] : 'blog';

// Validate directory parameter
if (!in_array($directory, $allowedDirectories)) {
    sendError('Invalid directory parameter', 400);
}

$imageFolder = dirname(__DIR__) . "/img/" . $directory . "/";

// Check if directory exists, create if not
if (!is_dir($imageFolder)) {
    if (!mkdir($imageFolder, 0755, true)) {
        sendError('Failed to create upload directory', 500);
    }
}

// Maximum file size: 5MB
$maxFileSize = 5 * 1024 * 1024;

reset($_FILES);
$temp = current($_FILES);

if (!$temp || !is_uploaded_file($temp['tmp_name'])) {
    sendError('No file uploaded or invalid upload', 400);
}

if (is_uploaded_file($temp['tmp_name'])) {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // same-origin requests won't set an origin. If the origin is set, it must be valid.
        if (in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins)) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        } else {
            sendError('Origin denied', 403);
        }
    }
    
    // Validate file size
    if ($temp['size'] > $maxFileSize) {
        sendError('File too large. Maximum size is 5MB.', 413);
    }

    // Sanitize input
    if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
        sendError('Invalid file name.', 400);
    }

    // Verify extension
    $allowedExtensions = array("gif", "jpg", "jpeg", "png");
    $extension = strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        sendError('Invalid extension. Only gif, jpg, jpeg, png allowed.', 400);
    }

    // Verify MIME type
    $allowedMimeTypes = array("image/gif", "image/jpeg", "image/png");
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $temp['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedMimeTypes)) {
        sendError('Invalid file type.', 400);
    }

    // Verify it's a valid image
    if (!getimagesize($temp['tmp_name'])) {
        sendError('Invalid image file.', 400);
    }

    // Generate unique filename to prevent overwrites
    $basename = pathinfo($temp['name'], PATHINFO_FILENAME);
    $basename = preg_replace("/[^a-zA-Z0-9_-]/", "", $basename);
    $uniqueFilename = $basename . '_' . time() . '_' . uniqid() . '.' . $extension;

    // Accept upload if there was no origin, or if it is an accepted origin
    $filetowrite = $imageFolder . $uniqueFilename;
    
    if (!move_uploaded_file($temp['tmp_name'], $filetowrite)) {
        sendError('Failed to save uploaded file.', 500);
    }

    // Respond to the successful upload with JSON.
    // Use a location key to specify the path to the saved image resource.
    $relativeLocation = str_replace(dirname(__DIR__) . "/img/", "../img/", $filetowrite);
    echo json_encode(array('location' => $relativeLocation), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}
?>
