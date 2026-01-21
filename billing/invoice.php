<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/convertor.class.php';

// Validare și sanitizare parametri
if (!isset($_REQUEST['cID']) || !is_numeric($_REQUEST['cID'])) {
    die('Invalid invoice ID');
}
$cID = (int)$_REQUEST['cID'];

if (!isset($_REQUEST['type'])) {
    $_REQUEST['type'] = '';
}
$type = preg_replace('/[^a-zA-Z0-9_-]/', '', $_REQUEST['type']);

if (!isset($_REQUEST['option'])) {
    $_REQUEST['option'] = '';
}
$option = preg_replace('/[^a-zA-Z0-9_-]/', '', $_REQUEST['option']);

// Setare pentru compatibilitate cu invoicetemplate.php
$_REQUEST['cID'] = $cID;
$_REQUEST['type'] = $type;
$_REQUEST['option'] = $option;

include './invoicetemplate.php';
$strPageTitle="Tipărire facturi";
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes")
{
	header("location:$strSiteURL/login/index.php?message=MLF");
	die;
}

include '../dashboard/header.php';
require_once __DIR__ . '/../vendor/autoload.php';

$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new \Mpdf\Mpdf([
    'fontDir' => array_merge($fontDirs, [
        __DIR__ . '/fonts',
    ]),
    'fontdata' => $fontData + [
        'OpenSans' => [
            'R' => 'OpenSans-Regular.ttf',
            'B' => 'OpenSans-Bold.ttf',
            'I' => 'OpenSans-Italic.ttf',
            'BI' => 'OpenSans-BoldItalic.ttf',
        ]
    ],
    'default_font' => 'OpenSans'
]);

$d = date("d-m-Y ");
$data = date("Y-m-d ");

// Query securizat cu prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM facturare_facturi WHERE factura_ID=?");
mysqli_stmt_bind_param($stmt, "i", $cID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = ezpub_fetch_array($result);

if (!$row) {
    die("Factură negăsită");
}

If ($row["factura_tip"]=='0')
{
$codenumarfactura=str_pad($row["factura_numar"], 8, '0', STR_PAD_LEFT);
}
else
{
	$codenumarfactura=str_pad($row["factura_numar"], 8, '1', STR_PAD_LEFT);
}
If ($row["factura_client_pdf"]=='' OR isset($_GET["action"])&&$_GET["action"]=='cancel')
{

$mpdf = new \Mpdf\Mpdf([
	'margin_left' => 20,
	'margin_right' => 10,
	'margin_top' => 80,
	'margin_bottom' => 60,
	'margin_header' => 10,
	'margin_footer' => 50,
	'showBarcodeNumbers' => true
]);

$mpdf->SetTitle($strInvoice . " ". $siteInvoicingCode . " Nr. ".$codenumarfactura);
$mpdf->SetAuthor($siteCompanyLegalName);
$mpdf->SetSubject('Facturarea serviciilor ' . $siteCompanyLegalName );
$mpdf->SetKeywords('factură, factura, invoice');

// Validare action parameter
$action = isset($_GET["action"]) ? preg_replace('/[^a-z]/', '', $_GET["action"]) : '';

If ($action == 'cancel')
{
$mpdf->SetWatermarkText('ANULATĂ');
$mpdf->showWatermarkText = true;

// UPDATE securizat cu prepared statement
$stmt_update = mysqli_prepare($conn, 
    "UPDATE facturare_facturi SET 
        factura_client_pdf='1',
        factura_client_anulat='1',
        factura_client_valoare='0',
        factura_client_valoare_totala='0',
        factura_client_valoare_tva='0',
        factura_client_pdf_generat=?
    WHERE factura_ID=?");
mysqli_stmt_bind_param($stmt_update, "si", $data, $cID);

if (!mysqli_stmt_execute($stmt_update)) {
    die('Error updating invoice: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt_update);

// UPDATE articole securizat
$stmt_articole = mysqli_prepare($conn,
    "UPDATE facturare_articole_facturi SET 
        articol_bucati='0',
        articol_valoare='0',
        articol_TVA='0',
        articol_pret='0',
        articol_total='0'
    WHERE factura_ID=?");
mysqli_stmt_bind_param($stmt_articole, "i", $cID);

if (!mysqli_stmt_execute($stmt_articole)) {
    die('Error updating articles: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt_articole);
}

$mpdf->WriteHTML($invoice);

// Sanitizare nume fișier pentru securitate (protecție path traversal)
$safe_invoice_filename = 'Factura_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $siteInvoicingCode) . $codenumarfactura . '.pdf';
$mpdf->Output($hddpath ."/" . $invoice_folder ."/" . $safe_invoice_filename, 'F');

// UPDATE securizat cu prepared statement pentru marcare PDF generat
$stmt_pdf = mysqli_prepare($conn,
    "UPDATE facturare_facturi SET 
        factura_client_pdf='1',
        factura_client_pdf_generat=?
    WHERE factura_ID=?");
mysqli_stmt_bind_param($stmt_pdf, "si", $data, $cID);

if (!mysqli_stmt_execute($stmt_pdf)) {
    die('Error: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt_pdf);

// XSS protection pentru afișare nume fișier
$display_filename = htmlspecialchars($safe_invoice_filename, ENT_QUOTES, 'UTF-8');

echo "<div class=\"callout success\">" . $display_filename . " a fost generată. <a href=\"../common/opendoc.php?type=1&docID=" . urlencode($safe_invoice_filename) . "\" target=\"_blank\" rel=\"noopener noreferrer\"><i class=\"far fa-file-pdf\"></i></a></div>";
}
else
{
	// Sanitizare și XSS protection pentru PDF existent
	$safe_invoice_filename = 'Factura_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $siteInvoicingCode) . $codenumarfactura . '.pdf';
	$display_filename = htmlspecialchars($safe_invoice_filename, ENT_QUOTES, 'UTF-8');
	
	echo "<div class=\"callout success\">" . $display_filename . " există. <a href=\"../common/opendoc.php?type=1&docID=" . urlencode($safe_invoice_filename) . "\" target=\"_blank\" rel=\"noopener noreferrer\"><i class=\"far fa-file-pdf\"></i></a></div>";
}
include '../bottom.php';
?>