<?php
// Secure file download for admin — accepts base64-encoded relative path in `f`
include '../settings.php';
include '../classes/common.php';

if(!isset($_SESSION)) { session_start(); }
if (!isset($_SESSION['userlogedin']) || $_SESSION['userlogedin']!="Yes" || !isset($_SESSION['clearence']) || $_SESSION['clearence']!='ADMIN'){
    header("location:$strSiteURL/login/index.php?message=MLF");
    die;
}

if (!isset($_GET['f']) || empty($_GET['f'])) {
    http_response_code(400);
    echo "Missing file parameter.";
    exit;
}

$rel = base64_decode($_GET['f']);
if ($rel === false) { http_response_code(400); echo "Invalid file token."; exit; }

$base = rtrim($hddpath, "/\\") . '/';
$path = realpath($base . ltrim($rel, '/\\'));

if ($path === false || strpos($path, realpath($base)) !== 0 || !is_file($path)) {
    http_response_code(404);
    echo "File not found.";
    exit;
}

$filename = basename($path);
$fsize = filesize($path);

// Clear output buffers
while (ob_get_level()) ob_end_clean();
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . $fsize);

$fp = fopen($path, 'rb');
if ($fp === false) { http_response_code(500); echo "Unable to open file."; exit; }

set_time_limit(0);
// stream in 8MB chunks
$chunkSize = 8 * 1024 * 1024;
while (!feof($fp)) {
    echo fread($fp, $chunkSize);
    if (connection_aborted()) break;
}
fclose($fp);
exit;
