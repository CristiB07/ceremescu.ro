<?php
    include '../settings.php';
    include '../classes/common.php';

if (!isset($_GET['file'])) {
    die('No file specified');
}

$file = $_GET['file'];
$folder = $_GET['folder'];

// Prevent directory traversal
if (strpos($file, '..') !== false || strpos($file, '/') === 0 || strpos($folder, '..') !== false || strpos($folder, '/') === 0) {
    die('Invalid file path');
}

$full_path = $hddpath . '/' . $folder . '/' . $file;

if (!file_exists($full_path)) {
    die('File not found');
}
echo $full_path;
// Serve the file
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($full_path) . '"');
header('Content-Length: ' . filesize($full_path));
readfile($full_path);
exit;
?>