<?php
// Stream ZIP files for efactura
include '../settings.php';
include '../classes/common.php';
if (!isset($_SESSION)) { session_start(); }
if (!isset($_SESSION['userlogedin']) || $_SESSION['userlogedin']!="Yes") {
    header("location:$strSiteURL/login/index.php?message=MLF");
    exit;
}

$tip = isset($_GET['tip']) ? strtoupper(trim($_GET['tip'])) : '';
$cid = isset($_GET['cid']) ? trim($_GET['cid']) : '';

// Validate cid (should be integer-like)
if ($cid === '' || !ctype_digit($cid)) {
    http_response_code(400);
    echo 'Invalid cid';
    exit;
}

switch ($tip) {
    case 'ER':
        $folder = $error_folder;
        break;
    case 'FP':
        $folder = $efacturareceived_folder;
        break;
    case 'FT':
        $folder = $efacturadownload_folder;
        break;
    default:
        http_response_code(400);
        echo 'Invalid tip';
        exit;
}

// Build path safely
$folder = trim($folder, "\/ ");
$filepath = rtrim($hddpath, "\/ ") . '/' . $folder . '/' . $cid . '.zip';

if (!is_file($filepath) || !is_readable($filepath)) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

// Stream file with headers (ensure no extra output corrupts the ZIP)
// Clean (end) any active output buffering
if (ob_get_level()) {
    while (ob_get_level()) {
        ob_end_clean();
    }
}
// Send headers
header('Content-Description: File Transfer');
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));

// Stream in binary-safe chunks
$fp = fopen($filepath, 'rb');
if ($fp) {
    // Avoid timeouts for large files
    @set_time_limit(0);
    while (!feof($fp)) {
        echo fread($fp, 8192);
        flush();
        if (connection_aborted()) break;
    }
    fclose($fp);
}
exit;

?>
