<?php
$strPageTitle = "Transmitere în masă e-facturi";
include '../settings.php';
include_once '../classes/common.php';
include '../dashboard/header.php';

if(!isset($_SESSION)) { 
    session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes") {
    header("location:$strSiteURL/login/index.php?message=MLF");
    die;
}

// Verificare clearence - doar ADMIN poate transmite bulk către ANAF
$role = $_SESSION['clearence'] ?? 'USER';
if ($role !== 'ADMIN') {
    header("location:$strSiteURL/billing/siteinvoices.php?message=ER");
    die;
}

// Include funcția processEInvoice din einvoice.php
require_once 'einvoice.php';

echo '<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <h1>' . $strPageTitle . '</h1>';

// Verificare dacă este POST (procesare bulk)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    
    // Validare POST data
    if ($_POST['confirm'] !== '1') {
        echo '<div class="callout alert">Cerere invalidă.</div>';
        echo '<p><a href="siteinvoices.php" class="button">Înapoi la Facturi</a></p>';
        echo '</div></div>';
        include '../bottom.php';
        die;
    }
    
    // Selectare facturi pentru procesare
    $stmt = mysqli_prepare($conn, 
        "SELECT factura_ID, factura_numar, factura_data_emiterii, factura_client_denumire 
         FROM facturare_facturi 
         WHERE factura_client_efactura_generata IS NULL 
         AND factura_data_emiterii >= '2024-01-01'
         ORDER BY factura_data_emiterii ASC");
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $total_invoices = mysqli_num_rows($result);
    $processed = 0;
    $success_count = 0;
    $error_count = 0;
    $errors = [];
    
    echo '<div class="callout primary">
            <p>Se procesează ' . $total_invoices . ' facturi...</p>
          </div>';
    
    // Procesare fiecare factură
    while ($invoice = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        // Verificare că sesiunea este încă validă (procesare lungă)
        if (!isset($_SESSION['userlogedin']) || $_SESSION['userlogedin'] !== 'Yes') {
            echo '<div class="callout alert">Sesiune expirată. Procesare întreruptă.</div>';
            break;
        }
        
        $processed++;
        $invoice_id = (int)$invoice['factura_ID']; // Cast to int pentru securitate
        $invoice_number = $invoice['factura_numar'];
        
        echo '<div class="callout secondary">
                <p><strong>Procesare factură ' . $processed . '/' . $total_invoices . ':</strong> ' 
                . htmlspecialchars($invoice['factura_client_denumire']) . ' - Factura nr. ' . $invoice_number 
                . ' din ' . $invoice['factura_data_emiterii'] . '</p>';
        
        // Procesare factură folosind funcția din einvoice.php
        $result_process = processEInvoice($conn, $invoice_id, true);
        
        if ($result_process['success']) {
            $success_count++;
            echo '<p class="profit">✓ Succes - Index ANAF: ' . htmlspecialchars($result_process['index']) . '</p>';
        } else {
            $error_count++;
            $errors[] = [
                'invoice_number' => $invoice_number,
                'message' => $result_process['message']
            ];
            echo '<p class="loss">✗ Eroare: ' . htmlspecialchars($result_process['message']) . '</p>';
        }
        
        echo '</div>';
        
        // Flush output pentru a afișa progresul în timp real
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
        
        // Pauză între request-uri pentru a evita rate limiting ANAF
        if ($processed < $total_invoices) {
            sleep(2); // 2 secunde pauză între fiecare factură
        }
    }
    
    mysqli_stmt_close($stmt);
    
    // Rezumat final
    echo '<div class="callout ' . ($error_count > 0 ? 'warning' : 'success') . '">
            <h3>Rezumat procesare</h3>
            <p><strong>Total facturi procesate:</strong> ' . $processed . '</p>
            <p class="profit"><strong>Succes:</strong> ' . $success_count . '</p>
            <p class="loss"><strong>Erori:</strong> ' . $error_count . '</p>
          </div>';
    
    // Afișare erori detaliate dacă există
    if (!empty($errors)) {
        echo '<div class="callout alert">
                <h4>Detalii erori:</h4>
                <ul>';
        foreach ($errors as $error) {
            echo '<li><strong>Factura ' . htmlspecialchars($error['invoice_number']) . ':</strong> ' 
                . htmlspecialchars($error['message']) . '</li>';
        }
        echo '</ul>
              </div>';
    }
    
    echo '<p><a href="siteinvoices.php" class="button">Înapoi la Facturi</a> 
          <a href="einvoices.php" class="button secondary">Vezi E-Facturi</a></p>';
    
} else {
    // Afișare preview și formular de confirmare
    
    // Numărare facturi eligibile
    $stmt_count = mysqli_prepare($conn, 
        "SELECT COUNT(*) as total 
         FROM facturare_facturi 
         WHERE factura_client_efactura_generata IS NULL 
         AND factura_data_emiterii >= '2024-01-01'");
    
    mysqli_stmt_execute($stmt_count);
    $result_count = mysqli_stmt_get_result($stmt_count);
    $count_row = mysqli_fetch_array($result_count, MYSQLI_ASSOC);
    $total_count = $count_row['total'];
    mysqli_stmt_close($stmt_count);
    
    if ($total_count == 0) {
        echo '<div class="callout warning">
                <p>Nu există facturi de procesat. Toate facturile emise după 1.01.2024 au fost deja transmise la ANAF.</p>
              </div>';
        echo '<p><a href="siteinvoices.php" class="button">Înapoi la Facturi</a></p>';
    } else {
        // Preview facturi
        $stmt_preview = mysqli_prepare($conn, 
            "SELECT factura_ID, factura_numar, factura_data_emiterii, factura_client_denumire, factura_client_valoare_totala
             FROM facturare_facturi 
             WHERE factura_client_efactura_generata IS NULL 
             AND factura_data_emiterii >= '2024-01-01'
             ORDER BY factura_data_emiterii ASC
             LIMIT 20");
        
        mysqli_stmt_execute($stmt_preview);
        $result_preview = mysqli_stmt_get_result($stmt_preview);
        
        echo '<div class="callout primary">
                <h3>Transmitere în masă e-facturi către ANAF</h3>
                <p>S-au găsit <strong>' . $total_count . '</strong> facturi care nu au fost transmise către ANAF.</p>
                <p>Criteriile de selecție:</p>
                <ul>
                    <li>Facturi emise după <strong>1 ianuarie 2024</strong></li>
                    <li>Câmpul <code>factura_client_efactura_generata</code> este <strong>NULL</strong></li>
                </ul>
              </div>';
        
        echo '<div class="callout warning">
                <h4>⚠️ Atenție</h4>
                <p>Procesul poate dura câteva minute în funcție de numărul de facturi.</p>
                <p>Se va face o pauză de 2 secunde între fiecare factură pentru a evita rate limiting-ul ANAF.</p>
                <p><strong>Timp estimat:</strong> aproximativ ' . ceil($total_count * 2 / 60) . ' minute</p>
              </div>';
        
        // Tabel preview
        echo '<h4>Preview facturi (primele 20):</h4>
              <table class="hover stack">
                <thead>
                    <tr>
                        <th>Nr. Factură</th>
                        <th>Data emiterii</th>
                        <th>Client</th>
                        <th>Valoare totală</th>
                    </tr>
                </thead>
                <tbody>';
        
        while ($preview = mysqli_fetch_array($result_preview, MYSQLI_ASSOC)) {
            echo '<tr>
                    <td>' . htmlspecialchars($preview['factura_numar']) . '</td>
                    <td>' . date('d.m.Y', strtotime($preview['factura_data_emiterii'])) . '</td>
                    <td>' . htmlspecialchars($preview['factura_client_denumire']) . '</td>
                    <td>' . number_format($preview['factura_client_valoare_totala'], 2, '.', ',') . ' RON</td>
                  </tr>';
        }
        
        echo '</tbody>
              </table>';
        
        if ($total_count > 20) {
            echo '<p class="help-text">... și încă ' . ($total_count - 20) . ' facturi</p>';
        }
        
        mysqli_stmt_close($stmt_preview);
        
        // Formular confirmare
        echo '<form method="POST" action="" onsubmit="return confirm(\'Sunteți sigur că doriți să transmiteți ' . $total_count . ' facturi către ANAF? Procesul nu poate fi oprit odată început.\');">
                <input type="hidden" name="confirm" value="1">
                <button type="submit" class="button success large">
                    <i class="fas fa-cloud-upload-alt"></i> Transmite ' . $total_count . ' facturi către ANAF
                </button>
                <a href="siteinvoices.php" class="button secondary">Anulează</a>
              </form>';
    }
}

echo '    </div>
</div>';

include '../bottom.php';
?>
