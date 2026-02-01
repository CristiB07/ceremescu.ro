<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';

if ((isset($_GET['docID'])) && (isset($_GET['type'])) && !empty($_GET['docID']) && !empty($_GET['type']))
{
    // Validate and sanitize filename - prevent path traversal
    $filename = basename($_GET['docID']);
    
    // Validate type parameter
    if (!is_numeric($_GET['type']) || $_GET['type'] < 1 || $_GET['type'] > 5) {
        $referer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'index.php';
        header("Location: " . htmlspecialchars($referer, ENT_QUOTES, 'UTF-8'));
        exit();
    }
    
    $type = (int)$_GET['type'];
    
    // Determine folder based on type
    if ($type == 1) {
        $filefolder = $invoice_folder;
    } elseif ($type == 2) {
        $filefolder = $receipts_folder;
    } elseif ($type == 3) {
        $filefolder = $receivedeinvoices;
    } elseif ($type == 4) {
        $filefolder = $contracts_folder;
    } elseif ($type == 5) {
        $filefolder = $elearning_folder."/".$_GET['folder'];
    }
    
    // Construct full file path securely
    $filepath = realpath($hddpath . "/" . $filefolder . "/" . $filename);
    $basepath = realpath($hddpath . "/" . $filefolder);
    
    // Verify file is within allowed directory (prevent path traversal)
    if (!$filepath || !$basepath || strpos($filepath, $basepath) !== 0 || !file_exists($filepath)) {
        $referer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'index.php';
        header("Location: " . htmlspecialchars($referer, ENT_QUOTES, 'UTF-8') . "?message=FILENOTFOUND");
        exit();
    }
    
    // Get MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $filepath);
    finfo_close($finfo);
    
    // Security headers
    header("Content-Description: File Transfer");
    header("Content-Type: " . $mime);
    header("Content-Disposition: attachment; filename=\"" . basename($filepath) . "\"");
    header("Content-Length: " . filesize($filepath));
    header("Cache-Control: must-revalidate");
    header("Pragma: public");
    
    // Clear output buffer
    if (ob_get_level()) {
        ob_clean();
    }
    flush();
    
    readfile($filepath);
    exit(); 
}
else
{
    $referer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'index.php';
    header("Location: " . htmlspecialchars($referer, ENT_QUOTES, 'UTF-8'));
    exit();
}
?>