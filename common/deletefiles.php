<?php
//update 04.08.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Ștergere contracte";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
	exit();
}
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$d = date("Y-m-d H:i:s");
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
?>
<!doctype html>

<head>
    <!--Start Header-->
    <!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"/> <![endif]-->
    <!--[if IE 7]> <html class="no-js lt-ie9 lt-ie8" lang="en"/> <![endif]-->
    <!--[if IE 8]> <html class="no-js lt-ie9" lang="en"/> <![endif]-->
    <!--[if gt IE 8]><!-->
    <html class="no-js" lang="en">
    <!--<![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!--Font Awsome-->
    <link rel="stylesheet" href="<?php echo htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') ?>/css/all.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') ?>/css/foundation.css" />
    <link rel="stylesheet" href="<?php echo htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') ?>/css/<?php echo htmlspecialchars($cssname, ENT_QUOTES, 'UTF-8') ?>.css" />

    <script>
    function resizeIframe(obj) {
        obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
    }
    </script>
</head>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
if ((isset($_GET['docID'])) && (isset($_GET['type'])) && !empty($_GET['docID']) && !empty($_GET['type']))
{
    // Validate and sanitize filename - prevent path traversal
    $filename = basename($_GET['docID']);
    
    // Validate type parameter
    if (!is_numeric($_GET['type']) || $_GET['type'] < 1 || $_GET['type'] > 4) {
        header("Location: " . htmlspecialchars($_SERVER["HTTP_REFERER"] ?? 'index.php', ENT_QUOTES, 'UTF-8') . "?message=TWS");
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
        
        if ((isset($_GET['contractID'])) && !empty($_GET['contractID'])) {
            // Validate contractID is numeric
            if (!is_numeric($_GET['contractID'])) {
                header("Location: " . htmlspecialchars($_SERVER["HTTP_REFERER"] ?? 'index.php', ENT_QUOTES, 'UTF-8') . "?message=EWS");
                exit();
            }
            
            $contract = (int)$_GET['contractID'];
            
            // Use prepared statement
            $stmt = mysqli_prepare($conn, "SELECT Contract_File FROM clienti_contracte WHERE ID_Contract=?");
            mysqli_stmt_bind_param($stmt, 'i', $contract);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
            if (!$row) {
                header("Location: " . htmlspecialchars($_SERVER["HTTP_REFERER"] ?? 'index.php', ENT_QUOTES, 'UTF-8') . "?message=EWS");
                exit();
            }
            
            $string = $row["Contract_File"] ?? '';
            $newfilename = str_replace($filename, "", $string);
            $newfilenamew = str_replace(";;", "", $newfilename);
            
            // Update with prepared statement
            $stmt = mysqli_prepare($conn, "UPDATE clienti_contracte SET Contract_File=? WHERE ID_Contract=?");
            mysqli_stmt_bind_param($stmt, 'si', $newfilenamew, $contract);
            
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                echo "<div class=\"callout alert\">" . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . "</div></div></div>";
                include '../bottom.php';
                exit();
            }
            mysqli_stmt_close($stmt);
            echo "<div class=\"callout success\">" . htmlspecialchars($strFileDeletedFromDatabase, ENT_QUOTES, 'UTF-8') . "</div>";
        }
    }
    
    // Construct full file path securely
    $filepath = realpath($hddpath . "/" . $filefolder . "/" . $filename);
    $basepath = realpath($hddpath . "/" . $filefolder);
    
    // Verify file is within allowed directory (prevent path traversal)
    if ($filepath && $basepath && strpos($filepath, $basepath) === 0 && file_exists($filepath)) {
        unlink($filepath);
        echo "<div class=\"callout success\">" . htmlspecialchars($strFileDeletedFromDisk, ENT_QUOTES, 'UTF-8') . "</div></div></div>"; 
    } else {
        echo "<div class=\"callout alert\">Invalid file path</div></div></div>";
    }
    
    echo "<script type=\"text/javascript\">
<!--
function delayer(){
     window.history.go(-1);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
    include '../bottom.php';
    exit();
}
else
{
    $referer = htmlspecialchars($_SERVER["HTTP_REFERER"] ?? 'index.php', ENT_QUOTES, 'UTF-8');
    header("Location: " . $referer . "?message=EWS");
    exit();
}
?>