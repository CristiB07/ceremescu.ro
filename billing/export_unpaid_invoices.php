<?php
$strPageTitle = "Export Facturi Neîncasate";
include '../settings.php';
include '../classes/common.php';

if(!isset($_SESSION)) { 
    session_start(); 
}

if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
    header("location:$strSiteURL/login/index.php?message=MLF");
    die;
}

// Interogare facturi neîncasate
$stmt = mysqli_prepare($conn, "SELECT 
    factura_numar, 
    factura_data_emiterii, 
    factura_client_termen, 
    factura_client_denumire, 
    factura_client_CUI,
    factura_client_valoare, 
    factura_client_valoare_tva, 
    factura_client_valoare_totala,
    DATEDIFF(CURDATE(), factura_data_emiterii) as zile_vechime,
    DATEDIFF(CURDATE(), factura_client_termen) as zile_intarziere
    FROM facturare_facturi 
    WHERE factura_client_achitat='0' AND factura_client_inchisa='1'
    ORDER BY factura_data_emiterii ASC");

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Setare headers pentru download Excel
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="facturi_neincasate_' . date('Y-m-d_His') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Start output
echo "\xEF\xBB\xBF"; // UTF-8 BOM pentru Excel

echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
echo '<!--[if gte mso 9]><xml>';
echo '<x:ExcelWorkbook>';
echo '<x:ExcelWorksheets>';
echo '<x:ExcelWorksheet>';
echo '<x:Name>Facturi Neîncasate</x:Name>';
echo '<x:WorksheetOptions>';
echo '<x:Print><x:ValidPrinterInfo/></x:Print>';
echo '</x:WorksheetOptions>';
echo '</x:ExcelWorksheet>';
echo '</x:ExcelWorksheets>';
echo '</x:ExcelWorkbook>';
echo '</xml><![endif]-->';
echo '</head>';
echo '<body>';

echo '<table border="1">';
echo '<thead>';
echo '<tr style="background-color:#4CAF50;color:#ffffff;font-weight:bold;">';
echo '<th>Nr. Factură</th>';
echo '<th>Data Emitere</th>';
echo '<th>Termen Plată</th>';
echo '<th>Client</th>';
echo '<th>CUI Client</th>';
echo '<th>Valoare (fără TVA)</th>';
echo '<th>TVA</th>';
echo '<th>Total (cu TVA)</th>';
echo '<th>Vechime (zile)</th>';
echo '<th>Întârziere (zile)</th>';
echo '<th>Status</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

$total_valoare = 0;
$total_tva = 0;
$total = 0;
$count = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $count++;
    $total_valoare += $row['factura_client_valoare'];
    $total_tva += $row['factura_client_valoare_tva'];
    $total += $row['factura_client_valoare_totala'];
    
    // Determinare status
    $status = '';
    $row_color = '';
    if ($row['zile_intarziere'] > 0) {
        if ($row['zile_intarziere'] > 90) {
            $status = 'CRITIC';
            $row_color = '#8B0000';
        } elseif ($row['zile_intarziere'] > 60) {
            $status = 'URGENT';
            $row_color = '#F87C63';
        } elseif ($row['zile_intarziere'] > 30) {
            $status = 'ATENȚIE';
            $row_color = '#FFA500';
        } else {
            $status = 'Întârziat';
            $row_color = '#FFEB3B';
        }
    } else {
        $status = 'În termen';
        $row_color = '#FFFFFF';
    }
    
    echo '<tr style="background-color:' . $row_color . ';">';
    echo '<td>' . htmlspecialchars($row['factura_numar'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . date('d.m.Y', strtotime($row['factura_data_emiterii'])) . '</td>';
    echo '<td>' . date('d.m.Y', strtotime($row['factura_client_termen'])) . '</td>';
    echo '<td>' . htmlspecialchars($row['factura_client_denumire'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars($row['factura_client_CUI'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td style="mso-number-format:\'\#\,\#\#0\.00\';">' . number_format($row['factura_client_valoare'], 2, '.', '') . '</td>';
    echo '<td style="mso-number-format:\'\#\,\#\#0\.00\';">' . number_format($row['factura_client_valoare_tva'], 2, '.', '') . '</td>';
    echo '<td style="mso-number-format:\'\#\,\#\#0\.00\';">' . number_format($row['factura_client_valoare_totala'], 2, '.', '') . '</td>';
    echo '<td>' . $row['zile_vechime'] . '</td>';
    echo '<td>' . ($row['zile_intarziere'] > 0 ? $row['zile_intarziere'] : 0) . '</td>';
    echo '<td>' . $status . '</td>';
    echo '</tr>';
}

// Rând total
echo '<tr style="background-color:#F87C63;color:#ffffff;font-weight:bold;">';
echo '<td colspan="5">TOTAL (' . $count . ' facturi)</td>';
echo '<td style="mso-number-format:\'\#\,\#\#0\.00\';">' . number_format($total_valoare, 2, '.', '') . '</td>';
echo '<td style="mso-number-format:\'\#\,\#\#0\.00\';">' . number_format($total_tva, 2, '.', '') . '</td>';
echo '<td style="mso-number-format:\'\#\,\#\#0\.00\';">' . number_format($total, 2, '.', '') . '</td>';
echo '<td colspan="3"></td>';
echo '</tr>';

echo '</tbody>';
echo '</table>';

echo '<br/><br/>';
echo '<p><strong>Export generat la data: ' . date('d.m.Y H:i:s') . '</strong></p>';
echo '<p><strong>Utilizator: ' . htmlspecialchars($_SESSION['username'] ?? 'N/A', ENT_QUOTES, 'UTF-8') . '</strong></p>';

echo '</body>';
echo '</html>';

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
