<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/autoload.php';
$url="/shop/";
$strKeywords="Comandă proceduri ";
$strDescription="Pagina finalizare a comenzii documente eProceduri";

// Validare și sanitizare oID
if (!isset($_GET['oID']) || !is_numeric($_GET['oID'])) {
    header("Location: $strSiteURL/404.php");
    exit;
}
$oID = (int)$_GET['oID'];

$strPageTitle = "Trimite comanda numărul " . $oID;
include '../header.php';

// Validare sesiune
if (!isset($_SESSION['buyer']) || empty($_SESSION['buyer'])) {
    header("Location: $strSiteURL/404.php");
    exit;
}

$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata = $yn . "-" . $m . "-" . $i . " 00:00:00";

$buyer = $_SESSION['buyer'];
echo '<div class="row"><div class="large-12 columns">';
echo '<h1>' . htmlspecialchars($strPageTitle, ENT_QUOTES, 'UTF-8') . '</h1>';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    check_inject();
    
    // New client
    if (isset($_GET['action']) && $_GET['action'] == "new") {
        // Sanitizare input
        $factura_client_RO = isset($_POST["factura_client_RO"]) ? trim($_POST["factura_client_RO"]) : '';
        $factura_client_CIF = isset($_POST["factura_client_CIF"]) ? trim($_POST["factura_client_CIF"]) : '';
        $cui = $factura_client_RO . $factura_client_CIF;
        
        $factura_client_denumire = isset($_POST["factura_client_denumire"]) ? trim($_POST["factura_client_denumire"]) : '';
        $factura_client_RC = isset($_POST["factura_client_RC"]) ? trim($_POST["factura_client_RC"]) : '';
        $factura_client_adresa = isset($_POST["factura_client_adresa"]) ? trim($_POST["factura_client_adresa"]) : '';
        $factura_client_localitate = isset($_POST["factura_client_localitate"]) ? trim($_POST["factura_client_localitate"]) : '';
        $factura_client_judet = isset($_POST["factura_client_judet"]) ? trim($_POST["factura_client_judet"]) : '';
        $factura_client_banca = isset($_POST["factura_client_banca"]) ? trim($_POST["factura_client_banca"]) : '';
        $factura_client_IBAN = isset($_POST["factura_client_IBAN"]) ? trim($_POST["factura_client_IBAN"]) : '';
        
        // INSERT magazin_firme cu prepared statement
        $stmt_firma = mysqli_prepare($conn, "INSERT INTO magazin_firme(firma_nume, firma_ro, firma_CIF, firma_reg, firma_adresa, firma_oras, firma_judet, firma_banca, firma_IBAN) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt_firma) {
            echo '<div class="callout alert">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . ': ' . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8') . '</div></div></div>';
            include('../bottom.php');
            exit;
        }
        
        mysqli_stmt_bind_param($stmt_firma, 'sssssssss', $factura_client_denumire, $factura_client_RO, $factura_client_CIF, $factura_client_RC, $factura_client_adresa, $factura_client_localitate, $factura_client_judet, $factura_client_banca, $factura_client_IBAN);
        
        if (!mysqli_stmt_execute($stmt_firma)) {
            echo '<div class="callout alert">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . ': ' . htmlspecialchars(mysqli_stmt_error($stmt_firma), ENT_QUOTES, 'UTF-8') . '</div></div></div>';
            mysqli_stmt_close($stmt_firma);
            include('../bottom.php');
            exit;
        }
        
        $companyID = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_firma);

        $studentinvoice = (!empty($factura_client_CIF)) ? $factura_client_CIF : '0';
        
        // Sanitizare input cumparator
        $cumparator_prenume = isset($_POST["cumparator_prenume"]) ? trim($_POST["cumparator_prenume"]) : '';
        $cumparator_nume = isset($_POST["cumparator_nume"]) ? trim($_POST["cumparator_nume"]) : '';
        $cumparator_adresa = isset($_POST["cumparator_adresa"]) ? trim($_POST["cumparator_adresa"]) : '';
        $cumparator_email = isset($_POST["cumparator_email"]) ? trim($_POST["cumparator_email"]) : '';
        $cumparator_telefon = isset($_POST["cumparator_telefon"]) ? trim($_POST["cumparator_telefon"]) : '';
        $cumparator_oras = isset($_POST["cumparator_oras"]) ? trim($_POST["cumparator_oras"]) : '';
        $cumparator_judet = isset($_POST["cumparator_judet"]) ? trim($_POST["cumparator_judet"]) : '';
        
        // INSERT magazin_cumparatori cu prepared statement
        $stmt_cump = mysqli_prepare($conn, "INSERT INTO magazin_cumparatori(cumparator_prenume, cumparator_nume, cumparator_adresa, cumparator_email, cumparator_telefon, cumparator_oras, cumparator_judet, cumparator_factura, cumparator_firma) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt_cump) {
            echo '<div class="callout alert">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . ': ' . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8') . '</div></div></div>';
            include('../bottom.php');
            exit;
        }
        
        mysqli_stmt_bind_param($stmt_cump, 'sssssssss', $cumparator_prenume, $cumparator_nume, $cumparator_adresa, $cumparator_email, $cumparator_telefon, $cumparator_oras, $cumparator_judet, $studentinvoice, $factura_client_CIF);
        
        if (!mysqli_stmt_execute($stmt_cump)) {
            echo '<div class="callout alert">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . ': ' . htmlspecialchars(mysqli_stmt_error($stmt_cump), ENT_QUOTES, 'UTF-8') . '</div></div></div>';
            mysqli_stmt_close($stmt_cump);
            include('../bottom.php');
            exit;
        }
        
        $userID = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_cump);
        
        // UPDATE magazin_firme cu prepared statement
        $stmt_upd_firma = mysqli_prepare($conn, "UPDATE magazin_firme SET firma_cumparatorID=? WHERE firma_ID=?");
        mysqli_stmt_bind_param($stmt_upd_firma, 'ii', $userID, $companyID);
        mysqli_stmt_execute($stmt_upd_firma);
        mysqli_stmt_close($stmt_upd_firma);
        
        // UPDATE magazin_comenzi cu prepared statement
        $status_one = 1;
        $stmt_upd_com = mysqli_prepare($conn, "UPDATE magazin_comenzi SET comanda_status=?, comanda_utilizator=?, company_id=? WHERE comanda_ID=?");
        mysqli_stmt_bind_param($stmt_upd_com, 'iiii', $status_one, $userID, $companyID, $oID);
        mysqli_stmt_execute($stmt_upd_com);
        mysqli_stmt_close($stmt_upd_com);
    } //ends new client
    else {
        // Invalid action
        header("Location: $strSiteURL/404.php");
        exit;
    }
    
    // Construcție mesaj email - toate valorile escaped pentru XSS
    if (empty($factura_client_CIF)) {
        $cumparator = "<h4>" . htmlspecialchars($cumparator_nume, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($cumparator_prenume, ENT_QUOTES, 'UTF-8') . "</h4><br />";
        $cumparator .= "<strong>Adresa:</strong> " . htmlspecialchars($cumparator_adresa, ENT_QUOTES, 'UTF-8') . "<br />";
    } else {
        $cumparator = "<h4>" . htmlspecialchars($factura_client_denumire, ENT_QUOTES, 'UTF-8') . "</h4><br />";
        $cumparator .= "CUI: " . htmlspecialchars($cui, ENT_QUOTES, 'UTF-8') . "<br />";
        $cumparator .= "Reg. Comert.: " . htmlspecialchars($factura_client_RC, ENT_QUOTES, 'UTF-8') . "<br />";
        $cumparator .= "Adresa: " . htmlspecialchars($factura_client_adresa, ENT_QUOTES, 'UTF-8') . "<br />";
        $cumparator .= "Localitatea: " . htmlspecialchars($factura_client_localitate, ENT_QUOTES, 'UTF-8') . "<br />";
        $cumparator .= "Județ: " . htmlspecialchars($factura_client_judet, ENT_QUOTES, 'UTF-8') . "<br />";
        $cumparator .= "Banca: " . htmlspecialchars($factura_client_banca, ENT_QUOTES, 'UTF-8') . "<br />";
        $cumparator .= "IBAN: " . htmlspecialchars($factura_client_IBAN, ENT_QUOTES, 'UTF-8') . "<br />";
    }
    
    $emailto = $cumparator_email;
    $emailtoname = $cumparator_prenume . " " . $cumparator_nume;


$HTMLBody="<html>";
$HTMLBody=$HTMLBody . "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$HTMLBody=$HTMLBody . "<link href='".$siteCompanyWebsite."/fonts/open-sans-condensed-v21-latin-ext_latin-300.woff' rel='stylesheet' type='text/css'>";
$HTMLBody=$HTMLBody . "<link href='".$siteCompanyWebsite."/fonts/open-sans-v27-latin-ext_latin-regular.woff' rel='stylesheet' type='text/css'>";
$HTMLBody=$HTMLBody . "<style>body {margin-top: 10px; margin-bottom: 10px; margin-left: 10px; margin-right: 10px; font-size: 1.1em; font-family: 'Open Sans',sans-serif; padding: 0px; color: " . $color ."}";
$HTMLBody=$HTMLBody . "h1,h2,h3,h4,h5 {font-family:'Open Sans',sans-serif; font-weight: bold; color: " . $color .";}";
$HTMLBody=$HTMLBody . "td {font-size: 1em; font-family: 'Open Sans',sans-serif; color: " . $color ."; padding: 3px;  font-weight: normal; border-collapse:collapse; border: 1px solid " . $color .";}";
$HTMLBody=$HTMLBody . "th {font-size: 1.1em; font-family: 'Open Sans',sans-serif; color: #ffffff; background-color: " . $color .";  font-weight: normal;}";
$HTMLBody=$HTMLBody . "table {border-collapse:collapse; border: 1px solid " . $color .";}";
$HTMLBody=$HTMLBody . "</style>";
$HTMLBody=$HTMLBody . "</head><body>";
$HTMLBody=$HTMLBody . "<a href=\"$siteCompanyWebsite\"><img src=\"".$siteCompanyWebsite."/img/logo.png\" title=\"$strSiteOwner\" width=\"150\" height=\"auto\"/></a>";
$HTMLBody=$HTMLBody . "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$HTMLBody=$HTMLBody . "</head><body>";
$HTMLBody=$HTMLBody . "<p>Stimate " . htmlspecialchars($cumparator_prenume, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($cumparator_nume, ENT_QUOTES, 'UTF-8') . ",<br>";
$HTMLBody=$HTMLBody . "Acesta este un mesaj de confirmare a comenzii făcute de dumneavoastră pe site-ul " . htmlspecialchars($strSiteName, ENT_QUOTES, 'UTF-8') . ". Mai jos aveți factura proforma. </p>";
$HTMLBody=$HTMLBody . "<H2>Factura proforma " . htmlspecialchars($siteInvoicingCode, ENT_QUOTES, 'UTF-8') . htmlspecialchars($oID, ENT_QUOTES, 'UTF-8') . "</H2>";
$HTMLBody=$HTMLBody . "<table border=\"0\" align=\"center\" width=\"100%\">";
$HTMLBody=$HTMLBody . "<tr valign=\"top\"><td width=\"50%\" valign=\"top\"><strong>Furnizor</strong>";
$HTMLBody=$HTMLBody . "<h4>$siteCompanyLegalName</h4>CUI: $siteVATNumber; $siteCompanyRegistrationNr; Capital social $siteCompanySocialCapital.<br />
$siteCompanyLegalAddress<br />
Tel.: $siteCompanyPhones; Email: $siteCompanyEmail $siteCompanyShortSite<br />";
foreach ($siteBankAccounts as $account) {
  $HTMLBody=$HTMLBody . "<font color=\"" . $color ."\">$account</font><br />";
}
$HTMLBody=$HTMLBody . "<h5>$siteVATStatus</h5> ";
$HTMLBody=$HTMLBody . "</td><td width=\"50%\" valign=\"top\"><strong>Cumpărător</strong>";
$HTMLBody=$HTMLBody .  $cumparator;
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "</tr>";
$HTMLBody=$HTMLBody . "</table>";
$HTMLBody=$HTMLBody . "<br/>";
$HTMLBody=$HTMLBody . "<br/>";
$HTMLBody=$HTMLBody . "<br/>";
$HTMLBody=$HTMLBody . "<table align=\"center\" width=\"100%\">";
$HTMLBody=$HTMLBody . "<thead><tr>";
$HTMLBody=$HTMLBody . "<th>";
$HTMLBody=$HTMLBody . "Produs";
$HTMLBody=$HTMLBody . "</th>";
$HTMLBody=$HTMLBody . "<th>";
$HTMLBody=$HTMLBody . "Valoare";
$HTMLBody=$HTMLBody . "</th>";
$HTMLBody=$HTMLBody . "<th>";
$HTMLBody=$HTMLBody . "Cantitate";
$HTMLBody=$HTMLBody . "</th>";
$HTMLBody=$HTMLBody . "<th>";
$HTMLBody=$HTMLBody . "Total";
$HTMLBody=$HTMLBody . "</th>";
$HTMLBody=$HTMLBody . "<th>";
$HTMLBody=$HTMLBody . "TVA";
$HTMLBody=$HTMLBody . "</th>";
$HTMLBody=$HTMLBody . "</tr>";
$HTMLBody=$HTMLBody . "</thead>";

// Query articole cu prepared statement
$stmt_items = mysqli_prepare($conn, "SELECT * FROM magazin_articole WHERE articol_idcomanda=?");
mysqli_stmt_bind_param($stmt_items, 'i', $oID);
mysqli_stmt_execute($stmt_items);
$result_items = mysqli_stmt_get_result($stmt_items);
$items_array = [];
while ($item_temp = mysqli_fetch_array($result_items, MYSQLI_ASSOC)) {
    $items_array[] = $item_temp;
}
mysqli_stmt_close($stmt_items);

$ordertotal = 0;
foreach ($items_array as $rowi) {
    $articol_produs = (int)$rowi['articol_produs'];
    
    // Query produs cu prepared statement
    $stmt_prod = mysqli_prepare($conn, "SELECT * FROM magazin_produse WHERE produs_id=?");
    mysqli_stmt_bind_param($stmt_prod, 'i', $articol_produs);
    mysqli_stmt_execute($stmt_prod);
    $result_prod = mysqli_stmt_get_result($stmt_prod);
    $row = mysqli_fetch_array($result_prod, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt_prod);
    
    if ($row["produs_dpret"] !== '0.0000') {
        $unitprice = $row['produs_dpret'];
    } else {
        $unitprice = $row['produs_pret'];
    }
    
    $vatrat = $row["produs_tva"] / 100;
    $vatprc = $vatrat + 1;
    $quantity = (int)$rowi['articol_cantitate'];
    $totalprice = $unitprice * $quantity;
    $ordertotal = $ordertotal + $totalprice;
    $VAT = $totalprice * $vatrat;
    
    $HTMLBody .= "<tr><td>" . htmlspecialchars($row['produs_nume'], ENT_QUOTES, 'UTF-8') . "</td>";
    $HTMLBody .= "<td align=\"right\">" . htmlspecialchars(romanize($unitprice), ENT_QUOTES, 'UTF-8') . "</td>";
    $HTMLBody .= "<td align=\"right\">" . htmlspecialchars($quantity, ENT_QUOTES, 'UTF-8') . "</td>";
    $HTMLBody .= "<td align=\"right\">" . htmlspecialchars(romanize($totalprice), ENT_QUOTES, 'UTF-8') . "</td>";
    $HTMLBody .= "<td>" . htmlspecialchars(romanize($VAT), ENT_QUOTES, 'UTF-8') . "</td></tr>";
}
$totalinterim = $ordertotal * $vatprc;
$totalVAT = $ordertotal * $vatrat;
$totalorder = $ordertotal;

if ($paidtransport == "1") {
    if ($totalinterim <= $transportlimit) {
        $transportVAT = $transportprice * $transportvatrat;
        $HTMLBody .= "<tr><td colspan=\"3\">" . htmlspecialchars($strTransport, ENT_QUOTES, 'UTF-8') . "</td>";
        $HTMLBody .= "<td align=\"right\">" . htmlspecialchars(romanize($transportprice), ENT_QUOTES, 'UTF-8') . "</td>";
        $HTMLBody .= "<td>" . htmlspecialchars(romanize($transportVAT), ENT_QUOTES, 'UTF-8') . "</td></tr>";
        $totalorder = $ordertotal + $transportprice;
        $orderVAT = $ordertotal * $vatrat;
        $totalVAT = $orderVAT + $transportVAT;
    }
}

$finalprice = $totalorder + $totalVAT;
$HTMLBody .= "<tr><td colspan=\"3\">" . htmlspecialchars($strTotal, ENT_QUOTES, 'UTF-8') . "</td>";
$HTMLBody .= "<td align=\"right\">" . htmlspecialchars(romanize($totalorder), ENT_QUOTES, 'UTF-8') . "</td>";
$HTMLBody .= "<td>" . htmlspecialchars(romanize($totalVAT), ENT_QUOTES, 'UTF-8') . "</td></tr>";
$HTMLBody .= "<tr><td colspan=\"3\">" . htmlspecialchars($strTotal, ENT_QUOTES, 'UTF-8') . "</td>";
$HTMLBody .= "<td colspan=\"2\" align=\"right\">" . htmlspecialchars(romanize($finalprice), ENT_QUOTES, 'UTF-8') . "</td></tr>";
$HTMLBody=$HTMLBody . "</table>";
$HTMLBody=$HTMLBody . "<p>Produsele comandate vor fi livrate după confirmarea plății.</p> 
Vă mulțumim,<br />
<strong>$siteCompanyLegalName</strong><br />
$siteCompanyLegalAddress <br />
$siteCompanyPhones<br />
$siteCompanyEmail<br />
$siteCompanyWebsite <br />
";

$HTMLBody=$HTMLBody . "</body>";
$HTMLBody=$HTMLBody . "</html>";
$body=$HTMLBody;

//Create a new PHPMailer instance
$mail = new PHPMailer();
//Set PHPMailer to use the sendmail transport
$mail->CharSet = 'UTF-8';
$mail->isSMTP();
//Enable SMTP debugging
//SMTP::DEBUG_OFF = ON (for production use)
//SMTP::DEBUG_CLIENT = client messages
//SMTP::DEBUG_SERVER = client and server messages
$mail->SMTPDebug = 0;
//Set the hostname of the mail server
$mail->Host = $SmtpServer;
//Set the SMTP port number - likely to be 25, 465 or 587
$mail->Port = $SmtpPort;
//Whether to use SMTP authentication
$mail->SMTPAuth = true;
//Username to use for SMTP authentication
$mail->Username = $SmtpUser;
//Password to use for SMTP authentication
$mail->Password = $SmtpPass;
//Set who the message is to be sent from
//Set who the message is to be sent from
$mail->setFrom($SmtpUser, $strSiteOwner);
//Set an alternative reply-to address
$mail->addReplyTo($SmtpUser, $strSiteOwner);
//Set who the message is to be sent to
$mail->addAddress($emailto, $emailtoname);
$mail->addAddress($SmtpUser, $strSiteOwner);
//Set the subject line
$mail->Subject = 'Factura proforma '.$siteInvoicingCode . $oID ;
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Body    = $body;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
//Attach an image file

//send the message, check for errors
if (!$mail->send()) {
    echo '<div class="callout alert">Mailer Error: ' . htmlspecialchars($mail->ErrorInfo, ENT_QUOTES, 'UTF-8') . '</div>';
} else {
    echo '<div class="callout success">' . htmlspecialchars($strOrderSentSuccessfully, ENT_QUOTES, 'UTF-8') . '</div>';
}

// UPDATE comandă cu prepared statement
$data = date('Y-m-d H:i:s');
$status_one = 1;
// Determinăm company_id - dacă are date firmă, ia $companyID, altfel 0
$final_company_id = isset($companyID) && $companyID > 0 ? $companyID : 0;
$stmt_final = mysqli_prepare($conn, "UPDATE magazin_comenzi SET comanda_total=?, comanda_status=?, comanda_inchisa=?, company_id=? WHERE comanda_ID=?");
mysqli_stmt_bind_param($stmt_final, 'disii', $ordertotal, $status_one, $data, $final_company_id, $oID);
mysqli_stmt_execute($stmt_final);
mysqli_stmt_close($stmt_final);

include '../bottom.php';
} // ends if post
else {
    header("Location: $strSiteURL/404.php");
    exit;
}
?>