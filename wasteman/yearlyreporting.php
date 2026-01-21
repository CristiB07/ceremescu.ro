<?php
if (!isset($_SESSION)) {
    session_start();
}
// 1. Procesare export Excel înainte de orice output!
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    include '../settings.php';
    include '../classes/common.php';
    $selectedClient = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;
    $selectedYear = isset($_GET['an_raportare']) ? (int)$_GET['an_raportare'] : date('Y');
    if ($selectedClient <= 0) {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<div style="color:red;font-weight:bold;padding:2em;">Eroare: Nu ați selectat un client valid pentru export!</div>';
        exit;
    }
    $uid = $_SESSION['uid'];
    $code = $_SESSION['code'];
    // Date utilizator
    $uquery = "SELECT * FROM date_utilizatori WHERE utilizator_ID='" . intval($uid) . "'";
    $uresult = ezpub_query($conn, $uquery);
    $userRow = ezpub_fetch_array($uresult);
    $responsabil_mediu = $userRow['utilizator_Nume'] . ' ' . $userRow['utilizator_Prenume'];
    // Date client
    $cquery = "SELECT * FROM clienti_date WHERE ID_Client=$selectedClient";
    $cresult = ezpub_query($conn, $cquery);
    $clientRow = ezpub_fetch_array($cresult);
    // 1. Stoc precedent (final de an anterior) pentru fiecare cod deșeu
    $stocuri_precedente = [];
    $sql_stoc = "SELECT stoc_cod_deseu, stoc_cantitate FROM deseuri_stocuri WHERE stoc_client_id='$selectedClient' AND stoc_an_raportare='" . ($selectedYear - 1) . "'";
    $res_stoc = ezpub_query($conn, $sql_stoc);
    while ($row_stoc = ezpub_fetch_array($res_stoc)) {
        $stocuri_precedente[$row_stoc['stoc_cod_deseu']] = floatval($row_stoc['stoc_cantitate']);
    }
    // 2. Selectează toate codurile de deșeu raportate de client în anul selectat, pe operator și cod operațiune (valorificare/eliminare)
    $sql = "SELECT raportare_cod_deseu, raportare_operator,
        SUM(raportare_cantitate_totala) AS total_an,
        SUM(raportare_cantitate_valorificata) AS valorificata,
        SUM(raportare_cantitate_eliminata) AS eliminata,
        raportare_cod_operatiune_valorificare, raportare_cod_operatiune_eliminare
        FROM deseuri_raportari
        WHERE raportare_client_id='$selectedClient' AND raportare_an_raportare='$selectedYear'
        GROUP BY raportare_cod_deseu, raportare_operator, raportare_cod_operatiune_valorificare, raportare_cod_operatiune_eliminare
        ORDER BY raportare_cod_deseu, raportare_operator";
    $res = ezpub_query($conn, $sql);
    $rows = [];
    while ($row = ezpub_fetch_array($res)) {
        $cod = $row['raportare_cod_deseu'];
        $operator = $row['raportare_operator'];
        $total_an = floatval($row['total_an']);
        $valorificata = floatval($row['valorificata']);
        $eliminata = floatval($row['eliminata']);
        $op_val = $row['raportare_cod_operatiune_valorificare'];
        $op_elim = $row['raportare_cod_operatiune_eliminare'];
        // Stoc precedent pentru codul deșeu
        $stoc_prev = isset($stocuri_precedente[$cod]) ? $stocuri_precedente[$cod] : 0;
        // Determină tipul de operațiune și cantitatea asociată
        if ($eliminata > 0) {
            $op = $op_elim;
        } elseif ($valorificata > 0) {
            $op = $op_val;
        } else {
            $op = $op_val ? $op_val : $op_elim;
        }
        // Stoc final = stoc precedent + total an - valorificata - eliminata
        $stoc_final = $stoc_prev + $total_an - $valorificata - $eliminata;
        $rows[] = [
            'stoc_prev' => $stoc_prev,
            'total_an' => $total_an,
            'valorificata' => $valorificata,
            'eliminata' => $eliminata,
            'op' => $op,
            'operator' => $operator,
            'stoc_final' => $stoc_final
        ];
    }
    // Export Excel compatibil (HTML table)
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="raport_anual_' . date('Y-m-d_His') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM pentru Excel
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
    echo '<!--[if gte mso 9]><xml>';
    echo '<x:ExcelWorkbook>';
    echo '<x:ExcelWorksheets>';
    echo '<x:ExcelWorksheet>';
    echo '<x:Name>Raport anual deseuri</x:Name>';
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
  // Date client
    $uid = $_SESSION['uid'];
    $code = $_SESSION['code'];
      $uquery = "SELECT * FROM date_utilizatori WHERE utilizator_ID='" . intval($uid) . "'";
    $uresult = ezpub_query($conn, $uquery);
    $userRow = ezpub_fetch_array($uresult);
    $responsabil_mediu = $userRow['utilizator_Prenume'] . ' ' . $userRow['utilizator_Nume'];
    echo '<tr><td colspan="7"><strong>Client:</strong> ' . htmlspecialchars($clientRow['Client_Denumire'] ?? '') . '</td></tr>
     <tr><td colspan="7"><strong>CUI:</strong> ' . htmlspecialchars($clientRow['Client_CUI'] ?? '') . '</td></tr>
     <tr><td colspan="7"><strong>Registrul Comerțului:</strong> ' . htmlspecialchars($clientRow['Client_RC'] ?? '') . '</td></tr>
     <tr><td colspan="7"><strong>Localitate:</strong> ' . htmlspecialchars($clientRow['Client_Localitate'] ?? '') . '</td></tr>
    <tr><td colspan="7"> <strong>Județ:</strong> ' . htmlspecialchars($clientRow['Client_Judet'] ?? '') . '</td></tr>
    <tr><td colspan="7"> <strong>Telefon:</strong> ' . htmlspecialchars($clientRow['Client_Telefon'] ?? '') . '</td></tr>
    <tr><td colspan="7"> <strong>Cod CAEN:</strong> ' . htmlspecialchars($clientRow['Client_Cod_CAEN'] ?? '') . '</td></tr>
    <tr><td colspan="7"> <strong>Responsabil Mediu:</strong> ' . htmlspecialchars($responsabil_mediu ?? '') . '</td></tr>
    ';
      // Header
    echo '<tr style="background-color:#4CAF50;color:#ffffff;font-weight:bold;">';
    echo '<th>Stoc ('.($selectedYear-1).')</th>';
    echo '<th>Cantitate generată</th>';
    echo '<th>Cantitate valorificată</th>';
    echo '<th>Cantitate eliminată</th>';
    echo '<th>Cod operațiune</th>';
    echo '<th>Operator</th>';
    echo '<th>Stoc final an</th>';
    echo '</tr>';
    foreach ($rows as $r) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($r['stoc_prev']) . '</td>';
        echo '<td>' . htmlspecialchars($r['total_an']) . '</td>';
        echo '<td>' . htmlspecialchars($r['valorificata']) . '</td>';
        echo '<td>' . htmlspecialchars($r['eliminata']) . '</td>';
        echo '<td>' . htmlspecialchars($r['op']) . '</td>';
        echo '<td>' . htmlspecialchars($r['operator']) . '</td>';
        echo '<td>' . htmlspecialchars($r['stoc_final']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</body></html>';
    exit;
}

// Restul paginii normale (HTML, include-uri, interfață)
include '../settings.php';
include '../classes/common.php';
$strPageTitle = "Raportare anuală deșeuri";
include '../dashboard/header.php';
$uid = $_SESSION['uid'];
$code = $_SESSION['code'];
// Selectare client și an
$selectedClient = isset($_GET['client_id']) ? (int)$_GET['client_id'] : '';
$selectedYear = isset($_GET['an_raportare']) ? (int)$_GET['an_raportare'] : date('Y');
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <h2>Raportare anuală deșeuri</h2>
        <form method="get" action="yearlyreporting.php">
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-12 cell">
                    <label>Selectează client
                        <select id="client_id" name="client_id" class="required">
                            <option value="">Alege...</option>
                            <?php
                            $clientSql = "SELECT DISTINCT clienti_date.ID_Client, Client_Denumire, Client_Aloc, Client_CUI, deseuri_raportari.raportare_client_id FROM clienti_date, deseuri_raportari 
                                WHERE Client_Aloc='$code' 
                                AND  clienti_date.ID_Client=deseuri_raportari.raportare_client_id";
                            $clientRes = ezpub_query($conn, $clientSql);
                            while ($clientRow = ezpub_fetch_array($clientRes)) {
                                $sel = ($selectedClient == $clientRow['ID_Client']) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($clientRow['ID_Client']) . '" ' . $sel . '>' . htmlspecialchars($clientRow['Client_Denumire']) . '</option>';
                            }
                            ?>
                        </select>
                    </label>
                </div>
                <div class="large-2 medium-2 small-6 cell">
                    <label>Anul raportare:
                        <select id="an_raportare" name="an_raportare" class="required">
                            <?php
                            $currentYear = date("Y");
                            for ($year = $currentYear; $year >= $currentYear - 5; $year--) {
                                $sel = ($selectedYear == $year) ? 'selected' : '';
                                echo '<option value="' . $year . '" ' . $sel . '>' . $year . '</option>';
                            }
                            ?>
                        </select>
                    </label>
                </div>
                <div class="large-2 medium-2 small-12 cell" style="margin-top:1.8em;">
                    <button type="submit" class="button success">Generează raport</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
if ($selectedClient && $selectedYear) {
        // Export Excel dacă se cere
        if (isset($_GET['export']) && $_GET['export'] == 'excel') {
      //Date utilizator
        $uquery = "SELECT * FROM date_utilizatori WHERE utilizator_ID='" . intval($uid) . "'";
        $uresult = ezpub_query($conn, $uquery);
        $userRow = ezpub_fetch_array($uresult);
        $resposabil_mediu = $userRow['utilizator_Nume'] . ' ' . $userRow['utilizator_Prenume'];
      


           // Date client
    $cquery = "SELECT * FROM clienti_date WHERE ID_Client=$selectedClient";
    $cresult = ezpub_query($conn, $cquery);
    $clientRow = ezpub_fetch_array($cresult);
    // 1. Stoc precedent (final de an anterior) pentru fiecare cod deșeu
    $stocuri_precedente = [];
    $sql_stoc = "SELECT stoc_cod_deseu, stoc_cantitate FROM deseuri_stocuri WHERE stoc_client_id='$selectedClient' AND stoc_an_raportare='" . ($selectedYear - 1) . "'";
    $res_stoc = ezpub_query($conn, $sql_stoc);
    while ($row_stoc = ezpub_fetch_array($res_stoc)) {
        $stocuri_precedente[$row_stoc['stoc_cod_deseu']] = floatval($row_stoc['stoc_cantitate']);
    }
    // 2. Selectează toate codurile de deșeu raportate de client în anul selectat, pe operator și cod operațiune (valorificare/eliminare)
    $sql = "SELECT raportare_cod_deseu, raportare_operator,
        SUM(raportare_cantitate_totala) AS total_an,
        SUM(raportare_cantitate_valorificata) AS valorificata,
        SUM(raportare_cantitate_eliminata) AS eliminata,
        raportare_cod_operatiune_valorificare, raportare_cod_operatiune_eliminare
        FROM deseuri_raportari
        WHERE raportare_client_id='$selectedClient' AND raportare_an_raportare='$selectedYear'
        GROUP BY raportare_cod_deseu, raportare_operator, raportare_cod_operatiune_valorificare, raportare_cod_operatiune_eliminare
        ORDER BY raportare_cod_deseu, raportare_operator";
    $res = ezpub_query($conn, $sql);
    $rows = [];
    while ($row = ezpub_fetch_array($res)) {
        $cod = $row['raportare_cod_deseu'];
        $operator = $row['raportare_operator'];
        $total_an = floatval($row['total_an']);
        $valorificata = floatval($row['valorificata']);
        $eliminata = floatval($row['eliminata']);
        $op_val = $row['raportare_cod_operatiune_valorificare'];
        $op_elim = $row['raportare_cod_operatiune_eliminare'];
        // Stoc precedent pentru codul deșeu
        $stoc_prev = isset($stocuri_precedente[$cod]) ? $stocuri_precedente[$cod] : 0;
        // Determină tipul de operațiune și cantitatea asociată
        if ($eliminata > 0) {
            $op = $op_elim;
        } elseif ($valorificata > 0) {
            $op = $op_val;
        } else {
            $op = $op_val ? $op_val : $op_elim;
        }
        // Stoc final = stoc precedent + total an - valorificata - eliminata
        $stoc_final = $stoc_prev + $total_an - $valorificata - $eliminata;
        $rows[] = [
            'stoc_prev' => $stoc_prev,
            'total_an' => $total_an,
            'valorificata' => $valorificata,
            'eliminata' => $eliminata,
            'op' => $op,
            'operator' => $operator,
            'stoc_final' => $stoc_final
        ];
    }
    // Export Excel compatibil (HTML table)
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="raport_anual_' . date('Y-m-d_His') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM pentru Excel
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
    echo '<!--[if gte mso 9]><xml>';
    echo '<x:ExcelWorkbook>';
    echo '<x:ExcelWorksheets>';
    echo '<x:ExcelWorksheet>';
    echo '<x:Name>Raport anual deseuri</x:Name>';
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
    // Date client
    echo '<tr><td colspan="7"><strong>Client:</strong> ' . htmlspecialchars($clientRow['Client_Denumire'] ?? '') . '</td></tr>
     <tr><td colspan="7"><strong>CUI:</strong> ' . htmlspecialchars($clientRow['Client_CUI'] ?? '') . '</td></tr>
     <tr><td colspan="7"><strong>Registrul Comerțului:</strong> ' . htmlspecialchars($clientRow['Client_RC'] ?? '') . '</td></tr>
     <tr><td colspan="7"><strong>Localitate:</strong> ' . htmlspecialchars($clientRow['Client_Localitate'] ?? '') . '</td></tr>
    <tr><td colspan="7"> <strong>Județ:</strong> ' . htmlspecialchars($clientRow['Client_Judet'] ?? '') . '</td></tr>
    <tr><td colspan="7"> <strong>Telefon:</strong> ' . htmlspecialchars($clientRow['Client_Telefon'] ?? '') . '</td></tr>
    <tr><td colspan="7"> <strong>Cod CAEN:</strong> ' . htmlspecialchars($clientRow['Client_Cod_CAEN'] ?? '') . '</td></tr>
    <tr><td colspan="7"> <strong>Responsabil Mediu:</strong> ' . htmlspecialchars($resposabil_mediu ?? '') . '</td></tr>
    ';
     // Header
    echo '<tr style="background-color:#4CAF50;color:#ffffff;font-weight:bold;">';
    echo '<th>Stoc ('.($selectedYear-1).')</th>';
    echo '<th>Cantitate generată</th>';
    echo '<th>Cantitate valorificată</th>';
    echo '<th>Cantitate eliminată</th>';
    echo '<th>Cod operațiune</th>';
    echo '<th>Operator</th>';
    echo '<th>Stoc final an</th>';
    echo '</tr>';
    foreach ($rows as $r) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($r['stoc_prev']) . '</td>';
        echo '<td>' . htmlspecialchars($r['total_an']) . '</td>';
        echo '<td>' . htmlspecialchars($r['valorificata']) . '</td>';
        echo '<td>' . htmlspecialchars($r['eliminata']) . '</td>';
        echo '<td>' . htmlspecialchars($r['op']) . '</td>';
        echo '<td>' . htmlspecialchars($r['operator']) . '</td>';
        echo '<td>' . htmlspecialchars($r['stoc_final']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</body></html>';
        }
        // Buton export Excel
        echo '<form method="get" action="yearlyreporting.php" style="margin-bottom:20px">';
        echo '<input type="hidden" name="client_id" value="'.htmlspecialchars($selectedClient).'">';
        echo '<input type="hidden" name="an_raportare" value="'.htmlspecialchars($selectedYear).'">';
        echo '<button type="submit" name="export" value="excel" class="button secondary">Exportă Excel</button>';
        echo '</form>';
    // afișăm date client
?><div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="wastereportselector.php" class="button"><i class="fa fa-backward"></i> <?php echo $strBack?></a></p>
<table width="100%">
    <thead>
        <th><td></td><td></td></th>
        </thead>
    <tbody>
        <?php
         $cquery="SELECT * FROM clienti_date WHERE ID_Client=$selectedClient";
        $cresult=ezpub_query($conn,$cquery);
        $crow=ezpub_fetch_array($cresult);
        ?>
<tr>
    <td><?php echo $strName?></td>
    <td><?php echo $crow["Client_Denumire"]?></td>
 </tr>
 <tr>
     <td><?php echo $strVAT?></td>
    <td><?php echo $crow["Client_CUI"]?></td>
 </tr>
 <tr>
     <td><?php echo $strCompanyRC?></td>
    <td><?php echo $crow["Client_RC"]?></td>
</tr>
 <tr>
    <td><?php echo $strAddress?></td>
    <td><?php echo $crow["Client_Adresa"]?></td>
 </tr>
 <tr>
     <td><?php echo $strCity?></td>
    <td><?php echo $crow["Client_Localitate"]?></td>
 </tr>
 <tr>
    <td><?php echo $strCounty?></td>
    <td><?php echo $crow["Client_Judet"]?></td>
 </tr>
 <tr>
    <td><?php echo $strCode?></td>
    <td><?php echo $crow["Client_Cod_CAEN"]?></td>
</tr>  
</tbody>
</table>
</div>  
</div>
    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
    // 1. Stoc precedent (final de an anterior) pentru fiecare cod deșeu
    $stocuri_precedente = [];
    $sql_stoc = "SELECT stoc_cod_deseu, stoc_cantitate FROM deseuri_stocuri WHERE stoc_client_id='$selectedClient' AND stoc_an_raportare='" . ($selectedYear - 1) . "'";
    $res_stoc = ezpub_query($conn, $sql_stoc);
    while ($row_stoc = ezpub_fetch_array($res_stoc)) {
        $stocuri_precedente[$row_stoc['stoc_cod_deseu']] = floatval($row_stoc['stoc_cantitate']);
    }
    // 2. Selectează toate codurile de deșeu raportate de client în anul selectat, pe operator și cod operațiune (valorificare/eliminare)
    $sql = "SELECT raportare_cod_deseu, raportare_operator,
        SUM(raportare_cantitate_totala) AS total_an,
        SUM(raportare_cantitate_valorificata) AS total_valorificata,
        SUM(raportare_cantitate_eliminata) AS total_eliminata,
        raportare_cod_operatiune_valorificare, raportare_cod_operatiune_eliminare
        FROM deseuri_raportari
        WHERE raportare_client_id='$selectedClient' AND raportare_an_raportare='$selectedYear'
        GROUP BY raportare_cod_deseu, raportare_operator, raportare_cod_operatiune_valorificare, raportare_cod_operatiune_eliminare
        ORDER BY raportare_cod_deseu, raportare_operator";
    $res = ezpub_query($conn, $sql);
    $rows = [];
    while ($row = ezpub_fetch_array($res)) {
        $cod = $row['raportare_cod_deseu'];
        $operator = $row['raportare_operator'];
        $total_an = floatval($row['total_an']);
        $valorificata = floatval($row['total_valorificata']);
        $eliminata = floatval($row['total_eliminata']);
        $op_val = $row['raportare_cod_operatiune_valorificare'];
        $op_elim = $row['raportare_cod_operatiune_eliminare'];
        // Stoc precedent pentru codul deșeu
        $stoc_prev = isset($stocuri_precedente[$cod]) ? $stocuri_precedente[$cod] : 0;
        // Determină tipul de operațiune și cantitatea asociată
        if ($eliminata > 0) {
            $op = $op_elim;
            $cantitate = $eliminata;
        } elseif ($valorificata > 0) {
            $op = $op_val;
            $cantitate = $valorificata;
        } else {
            $op = $op_val ? $op_val : $op_elim;
            $cantitate = 0;
        }
        // Stoc final = stoc precedent + total an - valorificata - eliminata
        $stoc_final = $stoc_prev + $total_an - $valorificata - $eliminata;
        $rows[] = [
            'stoc_prev' => $stoc_prev,
            'cod' => $cod,
            'total_an' => $total_an,
            'valorificata' => $valorificata,
            'eliminata' => $eliminata,
            'op' => $op,
            'operator' => $operator,
            'stoc_final' => $stoc_final
        ];
    }
    // 3. Tabel raportare
    echo '<div class="grid-x grid-margin-x"><div class="large-12 cell">';
    echo '<h4>Raport anual pentru clientul selectat</h4>';
    echo '<table class="small-font-table" width="100%">';
    echo '<thead><tr>';
    echo '<th>Stoc ('.($selectedYear-1).')</th>';
    echo '<th>Cantitate generată</th>';
    echo '<th>Cantitate valorificată</th>';
    echo '<th>Cantitate eliminată</th>';
    echo '<th>Cod operațiune</th>';
    echo '<th>Operator</th>';
    echo '<th>Stoc final an</th>';
    echo '</tr></thead><tbody>';
    foreach ($rows as $r) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($r['stoc_prev']) . '</td>';
        echo '<td>' . htmlspecialchars($r['total_an']) . '</td>';
        echo '<td>' . htmlspecialchars($r['valorificata']) . '</td>';
        echo '<td>' . htmlspecialchars($r['eliminata']) . '</td>';
        echo '<td>' . htmlspecialchars($r['op']) . '</td>';
        echo '<td>' . htmlspecialchars($r['operator']) . '</td>';
        echo '<td>' . htmlspecialchars($r['stoc_final']) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div></div>';
}
?>
    </div>
</div>
<?php
include '../bottom.php';
?>
