<?php
$strPageTitle = "Notificări Întârzieri Plăți"; 
include '../settings.php';
include '../classes/common.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if(!isset($_SESSION)) { 
    session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
    header("location:$strSiteURL/login/index.php?message=MLF");
    die;
}

$uid = $_SESSION['uid'];
$role = $_SESSION['clearence'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_reminders'])) {
    // Initialize variables for email processing
    $sent_count = 0;
    $failed_count = 0;
    if (!isset($_POST['clients']) || !is_array($_POST['clients'])) {
        $message = "ER";
    } else {
        $sent_count = 0;
        $failed_count = 0;
        
        foreach ($_POST['clients'] as $client_data) {
            list($client_id, $client_email_raw) = explode('|', $client_data);
            $client_id = (int)$client_id;
            // Split multiple emails
            $email_list = array_filter(array_map('trim', preg_split('/;[ ]*/', $client_email_raw)));
            $valid_email_found = false;
            foreach ($email_list as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $valid_email_found = true;
                    break;
                }
            }
            if (!$valid_email_found) {
                $failed_count++;
                continue;
            }
            
            // Get client details
            $stmt_client = mysqli_prepare($conn, "SELECT factura_client_denumire, factura_client_CIF FROM facturare_facturi WHERE factura_client_ID=? LIMIT 1");
            mysqli_stmt_bind_param($stmt_client, "i", $client_id);
            mysqli_stmt_execute($stmt_client);
            $result_client = mysqli_stmt_get_result($stmt_client);
            $client = mysqli_fetch_array($result_client, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt_client);
            
            if (!$client) {
                $failed_count++;
                continue;
            }
            
            // Get overdue invoices for this client
            $stmt_invoices = mysqli_prepare($conn, "SELECT factura_numar, factura_client_valoare_totala, factura_data_emiterii, factura_client_termen, DATEDIFF(CURDATE(), factura_client_termen) as zile_intarziere FROM facturare_facturi WHERE factura_client_ID=? AND factura_client_achitat='0' AND factura_client_termen < CURDATE() AND DATEDIFF(CURDATE(), factura_client_termen) >  3 ORDER BY factura_client_termen ASC");
            mysqli_stmt_bind_param($stmt_invoices, "i", $client_id);
            mysqli_stmt_execute($stmt_invoices);
            $result_invoices = mysqli_stmt_get_result($stmt_invoices);
            
            $invoices = [];
            $invoice_numbers = []; // Array pentru numerele facturilor
            $total_debt = 0;
            while ($inv = mysqli_fetch_array($result_invoices, MYSQLI_ASSOC)) {
                $invoices[] = $inv;
                $invoice_numbers[] = $inv['factura_numar']; // Colectăm numerele facturilor
                $total_debt += $inv['factura_client_valoare_totala'];
            }
            mysqli_stmt_close($stmt_invoices);
            
            if (empty($invoices)) {
                $failed_count++;
                continue;
            }
            
            // Generate email body
            $email_body = generateReminderEmail($client['factura_client_denumire'], $client['factura_client_CIF'], $invoices, $total_debt, $siteCompanyLegalName, $siteCompanyPhones, $siteCompanyEmail);
            
            // Send email using PHPMailer
            require '../vendor/autoload.php';
            
            $mail = new PHPMailer(true);
            
            try {
                $mail->isSMTP();
                $mail->Host = $SmtpServer;
                $mail->SMTPAuth = true;
                $mail->Username = $SmtpUser;
                $mail->Password = $SmtpPass;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $SmtpPort;
                $mail->CharSet = 'UTF-8';
                $mail->SMTPDebug = 0;
                $mail->ConfirmReadingTo = $siteCompanyEmail;
                
                $mail->setFrom($siteCompanyEmail, $strSiteOwner);
                // Adaugă toate adresele valide
                foreach ($email_list as $email) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $mail->addAddress($email, $client['factura_client_denumire']);
                    }
                }
                
                $mail->isHTML(true);
                $mail->Subject = 'Notificare Întârziere Plată - ' . $siteCompanyLegalName;
                $mail->Body = $email_body;
                
                $mail->send();
                
                // Salvare notificare în baza de date
                $facturi_array_str = json_encode($invoice_numbers); // Convertim array-ul în JSON
                $stmt_notif = mysqli_prepare($conn, "INSERT INTO facturare_notificari_trimise (notificare_client_id, notificare_data_trimiterii, notificare_facturi_intarziate) VALUES (?, NOW(), ?)");
                mysqli_stmt_bind_param($stmt_notif, "is", $client_id, $facturi_array_str);
                mysqli_stmt_execute($stmt_notif);
                mysqli_stmt_close($stmt_notif);
                
                $sent_count++;
            } catch (Exception $e) {
                $failed_count++;
            }
        }
        
        if ($sent_count > 0) {
            $message = "OK";
            $message_text = "Au fost trimise $sent_count notificări.";
            if ($failed_count > 0) {
                $message_text .= " $failed_count notificări au eșuat.";
            }
        } else {
            $message = "ER";
        }
    }
}

function generateReminderEmail($client_name, $client_cif, $invoices, $total_debt, $company_name, $company_phone, $company_email) {
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #d9534f; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .invoice-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .invoice-table th { background-color: #5bc0de; color: white; padding: 10px; text-align: left; }
        .invoice-table td { padding: 10px; border-bottom: 1px solid #ddd; }
        .total { font-size: 18px; font-weight: bold; color: #d9534f; margin: 20px 0; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>NOTIFICARE ÎNTÂRZIERE PLATĂ</h2>
        </div>
        <div class="content">
            <p>Stimate/ă <strong>' . htmlspecialchars($client_name, ENT_QUOTES, 'UTF-8') . '</strong>,</p>
            <p>CIF: <strong>' . htmlspecialchars($client_cif, ENT_QUOTES, 'UTF-8') . '</strong></p>
            
            <p>Vă aducem la cunoștință că aveți facturi restante cu termene de plată depășite.</p>
            
            <div class="total">
                Sold total restant: ' . number_format($total_debt, 2, '.', ',') . ' RON
            </div>
            
            <h3>Facturi Restante:</h3>
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Nr. Factură</th>
                        <th>Data Emitere</th>
                        <th>Termen Plată</th>
                        <th>Zile Întârziere</th>
                        <th>Valoare (RON)</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($invoices as $invoice) {
        $html .= '<tr>
                        <td>' . htmlspecialchars($invoice['factura_numar'], ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . date('d.m.Y', strtotime($invoice['factura_data_emiterii'])) . '</td>
                        <td>' . date('d.m.Y', strtotime($invoice['factura_client_termen'])) . '</td>
                        <td style="color: #d9534f;"><strong>' . $invoice['zile_intarziere'] . ' zile</strong></td>
                        <td>' . number_format($invoice['factura_client_valoare_totala'], 2, '.', ',') . '</td>
                    </tr>';
    }
    
    $html .= '      </tbody>
            </table>
            
            <p>Vă rugăm să efectuați plata pentru facturile de mai sus care au depășit termenul de plată pentru a evita întreruperea activității.</p>
            
            <p>Pentru orice nelămuriri, ne puteți contacta la:</p>
            <ul>
                <li>Email: ' . htmlspecialchars($company_email, ENT_QUOTES, 'UTF-8') . '</li>
                <li>Telefon: ' . htmlspecialchars($company_phone, ENT_QUOTES, 'UTF-8') . '</li>
            </ul>
            
            <p>Cu respect,<br><strong>' . htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8') . '</strong></p>
        </div>
        <div class="footer">
            <p>Acesta este un mesaj automat. Vă rugăm să nu răspundeți la acest email.</p>
        </div>
    </div>
</body>
</html>';
    
    return $html;
}

include '../dashboard/header.php';
?>

<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <h1><i class="fas fa-exclamation-triangle"></i> <?php echo $strPageTitle?></h1>
        
        <?php
        if (isset($message) && $message == "OK") {
            echo "<div class=\"callout success\">$message_text</div>";
        } elseif (isset($message) && $message == "ER") {
            echo "<div class=\"callout alert\">A apărut o eroare la trimiterea notificărilor.</div>";
        }
        ?>
        
        <div class="callout primary">
            <p><strong>Notă:</strong> Vor fi afișați doar clienții cu facturi restante mai vechi de  3 de zile.</p>
        </div>
        
        <?php
        // Query to get clients with overdue invoices >  3 days
        // First try from clienti_abonamente
        $query = "SELECT DISTINCT 
                    f.factura_client_ID,
                    MAX(f.factura_client_denumire) as factura_client_denumire,
                    MAX(f.factura_client_CIF) as factura_client_CIF,
                    MAX(COALESCE(a.abonament_client_email, c.Client_email)) as client_email,
                    COUNT(DISTINCT f.factura_ID) as numar_facturi,
                    (
                        SELECT SUM(ff.factura_client_valoare_totala)
                        FROM facturare_facturi ff
                        WHERE ff.factura_client_ID = f.factura_client_ID
                          AND ff.factura_client_achitat = '0'
                          AND ff.factura_client_termen < CURDATE()
                          AND DATEDIFF(CURDATE(), ff.factura_client_termen) > 3
                    ) as total_restant,
                    MIN(f.factura_client_termen) as prima_scadenta,
                    MAX(DATEDIFF(CURDATE(), f.factura_client_termen)) as zile_intarziere_max
                FROM facturare_facturi f
                LEFT JOIN clienti_abonamente a ON f.factura_client_ID = a.abonament_client_ID
                LEFT JOIN clienti_contracte cc ON f.factura_client_ID = cc.ID_Client
                LEFT JOIN clienti_date c ON cc.ID_Client = c.ID_Client
                WHERE f.factura_client_achitat = '0'
                AND f.factura_client_termen < CURDATE()
                AND DATEDIFF(CURDATE(), f.factura_client_termen) >  3
                GROUP BY f.factura_client_ID
                HAVING client_email IS NOT NULL AND client_email != ''
                ORDER BY zile_intarziere_max DESC";
        
        $result = ezpub_query($conn, $query);
        $num_clients = ezpub_num_rows($result);
        
        if ($num_clients > 0) {
            ?>
            <form method="POST" action="" id="reminderForm">
                <table class="hover stack">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAll" onclick="toggleAll(this)"> 
                                <label for="selectAll">Toate</label>
                            </th>
                            <th>Client</th>
                            <th>CIF</th>
                            <th>Email</th>
                            <th>Facturi Restante</th>
                            <th>Număr(e) Factură Restantă</th>
                            <th>Reminder trimis?</th>
                            <th>Total Restant (RON)</th>
                            <th>Prima Scadență</th>
                            <th>Întârziere Max (zile)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = ezpub_fetch_array($result)) {
                            $client_id = (int)$row['factura_client_ID'];
                            $client_email = htmlspecialchars($row['client_email'], ENT_QUOTES, 'UTF-8');
                            $client_name = htmlspecialchars($row['factura_client_denumire'], ENT_QUOTES, 'UTF-8');
                            $client_cif = htmlspecialchars($row['factura_client_CIF'], ENT_QUOTES, 'UTF-8');
                            $client_value = $client_id . '|' . $row['client_email'];

                            // 1. Numerele facturilor restante (concatenate)
                            $facturi_numer = [];
                            $sql_facturi = "SELECT factura_numar FROM facturare_facturi WHERE factura_client_ID='$client_id' AND factura_client_achitat='0' AND factura_client_termen < CURDATE() AND DATEDIFF(CURDATE(), factura_client_termen) > 3";
                            $res_facturi = ezpub_query($conn, $sql_facturi);
                            while ($rf = ezpub_fetch_array($res_facturi)) {
                                $facturi_numer[] = $rf['factura_numar'];
                            }
                            $facturi_numer_str = $facturi_numer ? implode(", ", $facturi_numer) : '-';

                            // 2. Reminder trimis (data și număr factură)
                            $reminder_info = '-';
                            $sql_rem = "SELECT notificare_data_trimiterii, notificare_facturi_intarziate FROM facturare_notificari_trimise WHERE notificare_client_id='$client_id' ORDER BY notificare_data_trimiterii DESC LIMIT 1";
                            $res_rem = ezpub_query($conn, $sql_rem);
                            if ($rem = ezpub_fetch_array($res_rem)) {
                                $data = date('d.m.Y H:i', strtotime($rem['notificare_data_trimiterii']));
                                $facturi_notif = @json_decode($rem['notificare_facturi_intarziate'], true);
                                if (is_array($facturi_notif)) {
                                    $reminder_info = $data . '<br>Factura(e): ' . implode(", ", $facturi_notif);
                                } else {
                                    $reminder_info = $data;
                                }
                            }

                            echo "<tr>";
                            echo "<td><input type=\"checkbox\" name=\"clients[]\" value=\"" . htmlspecialchars($client_value, ENT_QUOTES, 'UTF-8') . "\" class=\"client-checkbox\"></td>";
                            echo "<td>$client_name</td>";
                            echo "<td>$client_cif</td>";
                            echo "<td>$client_email</td>";
                            echo "<td>" . $row['numar_facturi'] . "</td>";
                            echo "<td>" . htmlspecialchars($facturi_numer_str) . "</td>";
                            echo "<td>" . $reminder_info . "</td>";
                            echo "<td><strong>" . number_format($row['total_restant'], 2, '.', ',') . "</strong></td>";
                            echo "<td>" . date('d.m.Y', strtotime($row['prima_scadenta'])) . "</td>";
                            echo "<td><span class=\"label alert\">" . $row['zile_intarziere_max'] . " zile</span></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
                
                <div class="grid-x grid-margin-x" style="margin-top: 20px;">
                    <div class="large-12 cell">
                        <button type="submit" name="send_reminders" class="button success" onclick="return confirmSend()">
                            <i class="fas fa-paper-plane"></i> Trimite Notificări
                        </button>
                        <a href="siteinvoices.php" class="button secondary">
                            <i class="fas fa-times"></i> Anulează
                        </a>
                    </div>
                </div>
            </form>
            
            <script>
            function toggleAll(source) {
                var checkboxes = document.querySelectorAll('.client-checkbox');
                for (var i = 0; i < checkboxes.length; i++) {
                    checkboxes[i].checked = source.checked;
                }
            }
            
            function confirmSend() {
                var checkboxes = document.querySelectorAll('.client-checkbox:checked');
                if (checkboxes.length === 0) {
                    alert('Vă rugăm să selectați cel puțin un client!');
                    return false;
                }
                return confirm('Sigur doriți să trimiteți notificări către ' + checkboxes.length + ' client(i)?');
            }
            </script>
            <?php
        } else {
            echo "<div class=\"callout warning\">Nu există clienți cu facturi restante mai vechi de  3 de zile.</div>";
        }
        ?>
    </div>
</div>

<?php include '../bottom.php'; ?>
