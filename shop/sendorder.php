<?php
//create 8.01.2025
include '../settings.php';
include '../classes/common.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/autoload.php';
$url="/shop/";
$strKeywords="Comandă proceduri ";
$strDescription="Pagina finalizare a comenzii cu cont " . $strSiteName;

// Verificare sesiune utilizator
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['userlogedin']) || $_SESSION['userlogedin'] != "Yes") {
    header("Location: $strSiteURL/login/index.php?redirect=" . urlencode('shop/sendorder.php?oID=' . (isset($_GET['oID']) ? $_GET['oID'] : '')));
    exit;
}

// Validare și sanitizare oID
if (!isset($_GET['oID']) || !is_numeric($_GET['oID'])) {
    header("Location: $strSiteURL/404.php");
    exit;
}
$oID = (int)$_GET['oID'];

$strPageTitle = "Finalizare comandă numărul " . $oID;
include '../header.php';

// Validare sesiune buyer
if (!isset($_SESSION['buyer']) || empty($_SESSION['buyer'])) {
    header("Location: $strSiteURL/404.php");
    exit;
}

$uid = (int)$_SESSION['uid'];
$buyer = $_SESSION['buyer'];

// Query date utilizator autentificat
$stmt_user = mysqli_prepare($conn, "SELECT account_first_name, account_last_name, account_email, account_phone, account_address, account_city, account_county FROM site_accounts WHERE account_id=?");
mysqli_stmt_bind_param($stmt_user, 'i', $uid);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user_data = mysqli_fetch_array($result_user, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_user);

if (!$user_data) {
    echo '<div class="row"><div class="large-12 columns">';
    echo '<div class="callout alert">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . '</div>';
    echo '</div></div>';
    include '../bottom.php';
    exit;
}

// Verificare comandă activă
$stmt_order = mysqli_prepare($conn, "SELECT * FROM magazin_comenzi WHERE comanda_utilizator=? AND comanda_status=0 AND comanda_ID=?");
mysqli_stmt_bind_param($stmt_order, 'si', $buyer, $oID);
mysqli_stmt_execute($stmt_order);
$result_order = mysqli_stmt_get_result($stmt_order);
$order = mysqli_fetch_array($result_order, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_order);

if (!$order) {
    echo '<div class="row"><div class="large-12 columns">';
    echo '<div class="callout alert">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . '</div>';
    echo '</div></div>';
    include '../bottom.php';
    exit;
}

echo '<div class="row"><div class="large-12 columns">';
echo '<h1>' . htmlspecialchars($strPageTitle, ENT_QUOTES, 'UTF-8') . '</h1>';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    check_inject();
    
    // Procesare comandă pentru utilizator cu cont
    // Date cumpărător din formular (pot fi editate)
    $cumparator_prenume = isset($_POST["cumparator_prenume"]) ? trim($_POST["cumparator_prenume"]) : $user_data['account_first_name'];
    $cumparator_nume = isset($_POST["cumparator_nume"]) ? trim($_POST["cumparator_nume"]) : $user_data['account_last_name'];
    $cumparator_email = isset($_POST["cumparator_email"]) ? trim($_POST["cumparator_email"]) : $user_data['account_email'];
    $cumparator_telefon = isset($_POST["cumparator_telefon"]) ? trim($_POST["cumparator_telefon"]) : $user_data['account_phone'];
    $cumparator_oras = isset($_POST["cumparator_oras"]) ? trim($_POST["cumparator_oras"]) : $user_data['account_city'];
    $cumparator_judet = isset($_POST["cumparator_judet"]) ? trim($_POST["cumparator_judet"]) : $user_data['account_county'];
    $cumparator_adresa = isset($_POST["cumparator_adresa"]) ? trim($_POST["cumparator_adresa"]) : $user_data['account_address'];
    
    // Date facturare (opțional)
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
    
    $companyID = 0;
    $studentinvoice = '0';
    
    // Verificăm ce opțiune de facturare a fost selectată
    $company_option = isset($_POST["company_option"]) ? trim($_POST["company_option"]) : 'none';
    
    if ($company_option == 'existing' && isset($_POST["saved_company"])) {
        // Firmă existentă selectată din lista salvată
        $companyID = (int)$_POST["saved_company"];
        
        // Query datele firmei pentru email
        $stmt_comp = mysqli_prepare($conn, "SELECT company_VAT FROM site_companies WHERE company_id=? AND company_siteaccount=?");
        mysqli_stmt_bind_param($stmt_comp, 'ii', $companyID, $uid);
        mysqli_stmt_execute($stmt_comp);
        $result_comp = mysqli_stmt_get_result($stmt_comp);
        if ($row_comp = mysqli_fetch_array($result_comp, MYSQLI_ASSOC)) {
            $studentinvoice = $row_comp['company_VAT'];
        }
        mysqli_stmt_close($stmt_comp);
        
    } elseif ($company_option == 'new' && !empty($factura_client_CIF)) {
        // Firmă nouă - salvăm în site_companies
        $stmt_firma = mysqli_prepare($conn, "INSERT INTO site_companies(company_name, company_ro, company_VAT, company_reg, company_address, company_city, company_county, company_bank, company_iban, company_siteaccount) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt_firma) {
            mysqli_stmt_bind_param($stmt_firma, 'sssssssssi', $factura_client_denumire, $factura_client_RO, $factura_client_CIF, $factura_client_RC, $factura_client_adresa, $factura_client_localitate, $factura_client_judet, $factura_client_banca, $factura_client_IBAN, $uid);
            
            if (mysqli_stmt_execute($stmt_firma)) {
                $companyID = mysqli_insert_id($conn);
                $studentinvoice = $factura_client_CIF;
            }
            mysqli_stmt_close($stmt_firma);
        }
    }
    // else: company_option == 'none' -> companyID rămâne 0
    
    // UPDATE magazin_comenzi - setăm statusul pe 1, user_id, comanda_cont și company_id
    $status_one = 1;
    $data = date('Y-m-d H:i:s');
    $comanda_cont = 1; // Utilizator autentificat
    $comanda_cont_id = $uid; // ID-ul utilizatorului autentificat
    $stmt_upd_com = mysqli_prepare($conn, "UPDATE magazin_comenzi SET comanda_status=?, comanda_utilizator=?, comanda_inchisa=?, comanda_cont=?, comanda_cont_id=?, company_id=? WHERE comanda_ID=?");
    mysqli_stmt_bind_param($stmt_upd_com, 'iisiiii', $status_one, $uid, $data, $comanda_cont, $comanda_cont_id, $companyID, $oID);
    mysqli_stmt_execute($stmt_upd_com);
    mysqli_stmt_close($stmt_upd_com);
    
    // Construcție mesaj email
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

    $HTMLBody = "<html>";
    $HTMLBody .= "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
    $HTMLBody .= "<link href='".$siteCompanyWebsite."/fonts/open-sans-condensed-v21-latin-ext_latin-300.woff' rel='stylesheet' type='text/css'>";
    $HTMLBody .= "<link href='".$siteCompanyWebsite."/fonts/open-sans-v27-latin-ext_latin-regular.woff' rel='stylesheet' type='text/css'>";
    $HTMLBody .= "<style>body {margin-top: 10px; margin-bottom: 10px; margin-left: 10px; margin-right: 10px; font-size: 1.1em; font-family: 'Open Sans',sans-serif; padding: 0px; color: " . $color ."}";
    $HTMLBody .= "h1,h2,h3,h4,h5 {font-family:'Open Sans',sans-serif; font-weight: bold; color: " . $color .";}";
    $HTMLBody .= "td {font-size: 1em; font-family: 'Open Sans',sans-serif; color: " . $color ."; padding: 3px;  font-weight: normal; border-collapse:collapse; border: 1px solid " . $color .";}";
    $HTMLBody .= "th {font-size: 1.1em; font-family: 'Open Sans',sans-serif; color: #ffffff; background-color: " . $color .";  font-weight: normal;}";
    $HTMLBody .= "table {border-collapse:collapse; border: 1px solid " . $color .";}";
    $HTMLBody .= "</style>";
    $HTMLBody .= "</head><body>";
    $HTMLBody .= "<a href=\"$siteCompanyWebsite\"><img src=\"".$siteCompanyWebsite."/img/logo.png\" title=\"$strSiteOwner\" width=\"150\" height=\"auto\"/></a>";
    $HTMLBody .= "<p>Stimate " . htmlspecialchars($cumparator_prenume, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($cumparator_nume, ENT_QUOTES, 'UTF-8') . ",<br>";
    $HTMLBody .= "Acesta este un mesaj de confirmare a comenzii făcute de dumneavoastră pe site-ul " . htmlspecialchars($strSiteName, ENT_QUOTES, 'UTF-8') . ". Mai jos aveți factura proforma. </p>";
    $HTMLBody .= "<H2>Factura proforma " . htmlspecialchars($siteInvoicingCode, ENT_QUOTES, 'UTF-8') . htmlspecialchars($oID, ENT_QUOTES, 'UTF-8') . "</H2>";
    $HTMLBody .= "<table border=\"0\" align=\"center\" width=\"100%\">";
    $HTMLBody .= "<tr valign=\"top\"><td width=\"50%\" valign=\"top\"><strong>Furnizor</strong>";
    $HTMLBody .= "<h4>$siteCompanyLegalName</h4>CUI: $siteVATNumber; $siteCompanyRegistrationNr; Capital social $siteCompanySocialCapital.<br />
$siteCompanyLegalAddress<br />
Tel.: $siteCompanyPhones; Email: $siteCompanyEmail $siteCompanyShortSite<br />";
    foreach ($siteBankAccounts as $account) {
        $HTMLBody .= "<font color=\"" . $color ."\">$account</font><br />";
    }
    $HTMLBody .= "<h5>$siteVATStatus</h5> ";
    $HTMLBody .= "</td><td width=\"50%\" valign=\"top\"><strong>Cumpărător</strong>";
    $HTMLBody .= $cumparator;
    $HTMLBody .= "</td>";
    $HTMLBody .= "</tr>";
    $HTMLBody .= "</table>";
    $HTMLBody .= "<br/><br/><br/>";
    $HTMLBody .= "<table align=\"center\" width=\"100%\">";
    $HTMLBody .= "<thead><tr>";
    $HTMLBody .= "<th>Produs</th>";
    $HTMLBody .= "<th>Valoare</th>";
    $HTMLBody .= "<th>Cantitate</th>";
    $HTMLBody .= "<th>Total</th>";
    $HTMLBody .= "<th>TVA</th>";
    $HTMLBody .= "</tr></thead>";

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
    $vatrat = 0;
    $vatprc = 1;
    
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
    $HTMLBody .= "</table>";
    $HTMLBody .= "<p>Produsele comandate vor fi livrate după confirmarea plății.</p> 
Vă mulțumim,<br />
<strong>$siteCompanyLegalName</strong><br />
$siteCompanyLegalAddress <br />
$siteCompanyPhones<br />
$siteCompanyEmail<br />
$siteCompanyWebsite <br />
";
    $HTMLBody .= "</body></html>";
    $body = $HTMLBody;

    // PHPMailer
    $mail = new PHPMailer();
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = $SmtpServer;
    $mail->Port = $SmtpPort;
    $mail->SMTPAuth = true;
    $mail->Username = $SmtpUser;
    $mail->Password = $SmtpPass;
    $mail->setFrom($SmtpUser, $strSiteOwner);
    $mail->addReplyTo($SmtpUser, $strSiteOwner);
    $mail->addAddress($emailto, $emailtoname);
    $mail->addAddress($SmtpUser, $strSiteOwner);
    $mail->Subject = 'Factura proforma ' . $siteInvoicingCode . $oID;
    $mail->isHTML(true);
    $mail->Body = $body;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    if (!$mail->send()) {
        echo '<div class="callout alert">Mailer Error: ' . htmlspecialchars($mail->ErrorInfo, ENT_QUOTES, 'UTF-8') . '</div>';
    } else {
        echo '<div class="callout success">' . htmlspecialchars($strOrderSentSuccessfully, ENT_QUOTES, 'UTF-8') . '</div>';
    }

    // UPDATE comandă cu prepared statement
    $stmt_final = mysqli_prepare($conn, "UPDATE magazin_comenzi SET comanda_total=?, comanda_status=?, comanda_inchisa=?, company_id=? WHERE comanda_ID=?");
    mysqli_stmt_bind_param($stmt_final, 'disii', $ordertotal, $status_one, $data, $companyID, $oID);
    mysqli_stmt_execute($stmt_final);
    mysqli_stmt_close($stmt_final);

    include '../bottom.php';
} else {
    // Afișare formular cu date pre-completate
    ?>
    <p>Datele tale vor fi completate automat din cont. Poți modifica datele înainte de finalizarea comenzii.</p>
    
    <form method="post" action="sendorder.php?oID=<?php echo $oID ?>">
        <h3><?php echo $strCompanyRepresentative?></h3>
        <div class="grid-x grid-padding-x">
            <div class="large-3 cell">
                <label><?php echo $strFirstName?></label>
                <input name="cumparator_prenume" type="text" value="<?php echo htmlspecialchars($user_data['account_first_name'], ENT_QUOTES, 'UTF-8'); ?>" required />
            </div>
            <div class="large-3 cell">
                <label><?php echo $strLastName?></label>
                <input name="cumparator_nume" type="text" value="<?php echo htmlspecialchars($user_data['account_last_name'], ENT_QUOTES, 'UTF-8'); ?>" required />
            </div>
            <div class="large-3 cell">
                <label><?php echo $strEmail?></label>
                <input name="cumparator_email" type="email" value="<?php echo htmlspecialchars($user_data['account_email'], ENT_QUOTES, 'UTF-8'); ?>" required />
            </div>
            <div class="large-3 cell">
                <label><?php echo $strPhone?></label>
                <input name="cumparator_telefon" type="text" value="<?php echo htmlspecialchars($user_data['account_phone'], ENT_QUOTES, 'UTF-8'); ?>" required />
            </div>
        </div>
        <div class="grid-x grid-padding-x">
            <div class="large-4 cell">
                <label><?php echo $strAddress?></label>
                <input name="cumparator_adresa" type="text" value="<?php echo htmlspecialchars($user_data['account_address'], ENT_QUOTES, 'UTF-8'); ?>" />
            </div>
            <div class="large-4 cell">
                <label><?php echo $strCity?></label>
                <input name="cumparator_oras" type="text" value="<?php echo htmlspecialchars($user_data['account_city'], ENT_QUOTES, 'UTF-8'); ?>" />
            </div>
            <div class="large-4 cell">
                <label><?php echo $strCounty?></label>
                <input name="cumparator_judet" type="text" value="<?php echo htmlspecialchars($user_data['account_county'], ENT_QUOTES, 'UTF-8'); ?>" />
            </div>
        </div>

        <h3><?php echo $strInvoiceData?> (opțional)</h3>
        <div class="grid-x grid-padding-x">
            <div class="large-12 cell">
                <div class="callout alert"><?php echo $strDisclaimerCompanies?></div>
            </div>
        </div>
        
        <div class="grid-x grid-padding-x">
            <div class="large-12 cell">
                <h4>Opțiuni facturare:</h4>
                <label>
                    <input type="radio" name="company_option" value="none" checked>
                    Fără facturare pe firmă (persoană fizică)
                </label><br />
                <label>
                    <input type="radio" name="company_option" value="existing">
                    Folosește o firmă salvată
                </label><br />
                <label>
                    <input type="radio" name="company_option" value="new">
                    Adaugă firmă nouă (se va salva în contul meu)
                </label>
            </div>
        </div>
        <hr />
        
        <?php
        // Query firme salvate ale utilizatorului
        $stmt_companies = mysqli_prepare($conn, "SELECT * FROM site_companies WHERE company_siteaccount=? ORDER BY company_name ASC");
        mysqli_stmt_bind_param($stmt_companies, 'i', $uid);
        mysqli_stmt_execute($stmt_companies);
        $result_companies = mysqli_stmt_get_result($stmt_companies);
        $companies_count = mysqli_num_rows($result_companies);
        
        if ($companies_count > 0) {
            echo '<div class="grid-x grid-padding-x">';
            echo '<div class="large-12 cell">';
            echo '<h4>Firmele mele salvate:</h4>';
            echo '<p>Selectează o firmă salvată pentru a completa automat datele de facturare:</p>';
            
            while ($company = mysqli_fetch_array($result_companies, MYSQLI_ASSOC)) {
                $company_id = (int)$company['company_id'];
                echo '<label>';
                echo '<input type="radio" name="saved_company" value="' . $company_id . '" ';
                echo 'data-denumire="' . htmlspecialchars($company['company_name'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
                echo 'data-ro="' . htmlspecialchars($company['company_ro'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
                echo 'data-cif="' . htmlspecialchars($company['company_VAT'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
                echo 'data-rc="' . htmlspecialchars($company['company_reg'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
                echo 'data-adresa="' . htmlspecialchars($company['company_address'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
                echo 'data-localitate="' . htmlspecialchars($company['company_city'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
                echo 'data-judet="' . htmlspecialchars($company['company_county'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
                echo 'data-banca="' . htmlspecialchars($company['company_bank'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
                echo 'data-iban="' . htmlspecialchars($company['company_iban'] ?? '', ENT_QUOTES, 'UTF-8') . '" ';
                echo 'onclick="populateCompanyData(this)">';
                echo ' ' . htmlspecialchars($company['company_name'] ?? '', ENT_QUOTES, 'UTF-8');
                echo ' (CUI: ' . htmlspecialchars($company['company_VAT'] ?? '', ENT_QUOTES, 'UTF-8') . ')';
                echo '</label><br />';
            }
            
            echo '</div>';
            echo '</div>';
            echo '<hr />';
        }
        mysqli_stmt_close($stmt_companies);
        ?>
        
        <script type="text/javascript">
        // Script pentru populare date firmă salvată
        function populateCompanyData(radio) {
            const denumireInput = document.getElementById('factura_client_denumire');
            const roInput = document.getElementById('factura_client_RO');
            const cifInput = document.getElementById('factura_client_CIF');
            const rcInput = document.getElementById('factura_client_RC');
            const adresaInput = document.getElementById('factura_client_adresa');
            const localitateInput = document.getElementById('factura_client_localitate');
            const judetInput = document.getElementById('factura_client_judet');
            const bancaInput = document.getElementById('factura_client_banca');
            const ibanInput = document.getElementById('factura_client_IBAN');
            
            if (denumireInput) denumireInput.value = radio.dataset.denumire || '';
            if (roInput) roInput.value = radio.dataset.ro || '';
            if (cifInput) cifInput.value = radio.dataset.cif || '';
            if (rcInput) rcInput.value = radio.dataset.rc || '';
            if (adresaInput) adresaInput.value = radio.dataset.adresa || '';
            if (localitateInput) localitateInput.value = radio.dataset.localitate || '';
            if (judetInput) judetInput.value = radio.dataset.judet || '';
            if (bancaInput) bancaInput.value = radio.dataset.banca || '';
            if (ibanInput) ibanInput.value = radio.dataset.iban || '';
        }
        </script>
        
        <script type="text/javascript">
        // Script CUI Lookup (Vanilla JavaScript)
        document.addEventListener('DOMContentLoaded', function() {
            const btn1 = document.getElementById('btn1');
            
            if (btn1) {
                btn1.addEventListener('click', function(e) {
                    e.preventDefault();
                    const cuiInput = document.getElementById('Cui');
                    const cuiValue = cuiInput ? cuiInput.value : '';
                    
                    fetch('../common/cui.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'Cui=' + encodeURIComponent(cuiValue)
                    })
                    .then(response => response.json())
                    .then(data => {
                        try {
                            const denumireInput = document.getElementById('factura_client_denumire');
                            const cifInput = document.getElementById('factura_client_CIF');
                            const roInput = document.getElementById('factura_client_RO');
                            const adresaInput = document.getElementById('factura_client_adresa');
                            const judetInput = document.getElementById('factura_client_judet');
                            const localitateInput = document.getElementById('factura_client_localitate');
                            const rcInput = document.getElementById('factura_client_RC');
                            const loaderIcon = document.getElementById('loaderIcon');
                            
                            if (denumireInput) denumireInput.value = (data.denumire || '').toUpperCase();
                            if (cifInput) cifInput.value = data.cif || '';
                            if (roInput) roInput.value = data.tva || '';
                            if (adresaInput) adresaInput.value = data.adresa || '';
                            if (judetInput) judetInput.value = (data.judet || '').toUpperCase();
                            if (localitateInput) localitateInput.value = (data.oras || '').toUpperCase();
                            if (rcInput) rcInput.value = data.numar_reg_com || '';
                            if (loaderIcon) loaderIcon.style.display = 'none';
                        } catch (err) {
                            const responseEl = document.getElementById('response');
                            if (responseEl) responseEl.innerHTML = err.message;
                        }
                    })
                    .catch(error => {
                        alert('Some error occurred!');
                        console.error('Error:', error);
                    });
                });
            }
        });
        </script>
        
        <div class="grid-x grid-padding-x">
            <div class="large-6 cell">
                <div id="response"></div>
                <div class="input-group">
                    <span class="input-group-label"><?php echo $strCompanyVAT?></span>
                    <input class="input-group-field" type="text" name="Cui" id="Cui"
                        placeholder="<?php echo $strEnterVATNumber?>">
                    <div class="input-group-button">
                        <button id="btn1" type="button" class="button success"><i
                                class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="grid-x grid-padding-x ">
            <div class="large-4 medium-4 cell">
                <label><?php echo $strClient?></label>
                <input type="text" name="factura_client_denumire" id="factura_client_denumire" value="" />
            </div>
            <div class="large-1 medium-1 cell">
                <label><?php echo $strCompanyFA?></label>
                <input type="text" name="factura_client_RO" id="factura_client_RO" value="" />
            </div>
            <div class="large-3 medium-3 cell">
                <label><?php echo $strCompanyVAT?></label>
                <input type="text" name="factura_client_CIF" id="factura_client_CIF" value="" />
            </div>
            <div class="large-4 medium-4 cell">
                <label><?php echo $strCompanyRC?></label>
                <input type="text" name="factura_client_RC" id="factura_client_RC" value="" />
            </div>
        </div>
        <div class="grid-x grid-padding-x ">
            <div class="large-4 medium-4 cell">
                <label><?php echo $strAddress?></label>
                <input type="text" name="factura_client_adresa" id="factura_client_adresa" value="" />
            </div>
            <div class="large-4 medium-4 cell">
                <label><?php echo $strCity?></label>
                <input type="text" name="factura_client_localitate" id="factura_client_localitate" value="" />
            </div>
            <div class="large-4 medium-4 cell">
                <label><?php echo $strCounty?></label>
                <input type="text" name="factura_client_judet" id="factura_client_judet" value="" />
            </div>
        </div>
        <div class="grid-x grid-padding-x ">
            <div class="large-6 medium-6 cell">
                <label><?php echo $strBank?></label>
                <input type="text" name="factura_client_banca" id="factura_client_banca" value="" />
            </div>
            <div class="large-6 medium-6 cell">
                <label><?php echo $strCompanyIBAN?></label>
                <input type="text" name="factura_client_IBAN" id="factura_client_IBAN" value="" />
            </div>
        </div>

        <div class="grid-x grid-padding-x">
            <div class="large-12 cell">
                Am citit şi sunt de acord cu <a href="../termeni.php" title="Termeni şi condiţii de utilizare">Termenii şi condiţiile de utilizare</a>
                <input type="checkbox" id="strAcord" onclick="javascript:document.getElementById('btn_submit').style.display='block'; javascript:document.getElementById('strAcord').style.display='none';" />
            </div>
        </div>
        <div class="grid-x grid-padding-x">
            <div class="large-12 medium-12 small-12 cell text-center">
                <input type="submit" id="btn_submit" class="button success large" style="display:none;" value="<?php echo $strSubmit?>" />
            </div>
        </div>
    </form>
    <?php
}

echo '</div></div>';
include '../bottom.php';
?>
