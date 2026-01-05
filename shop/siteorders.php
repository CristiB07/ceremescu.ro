<?php
//update 8.01.2025
include '../settings.php';
include '../classes/paginator.class.php';
include '../classes/common.php';
$strDescription="Administrează comenzile";
$strPageTitle="Administrează comenzile";
$url="siteorders.php";
include '../dashboard/header.php';

// Verificare rol și funcție
$uid = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : 0;
$role = isset($_SESSION['clearence']) ? strtoupper($_SESSION['clearence']) : '';
$scope = isset($_SESSION['function']) ? strtoupper($_SESSION['function']) : '';
$is_client = ($role == 'CLIENT'); // Orice CLIENT poate vedea comenzile sale
$is_admin = ($role == 'ADMIN');

// Verificare permisiuni
if (!$is_admin && !$is_client) {
    echo '<div class="grid-x grid-padding-x"><div class="large-12 cell">';
    echo '<div class="callout alert">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . '</div>';
    echo '</div></div>';
    include '../bottom.php';
    exit;
}

echo '<div class="grid-x grid-padding-x"><div class="large-12 cell">';
echo '<h1>' . htmlspecialchars($strPageTitle, ENT_QUOTES, 'UTF-8') . '</h1><br />';
echo '<a href="siteorders.php" class="button">' . htmlspecialchars($strBack, ENT_QUOTES, 'UTF-8') . ' &nbsp;<i class="fas fa-backward fa-xl"></i></a><br />';

// Validare mode
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
if (!in_array($mode, ['view', 'delete', ''], true)) {
    echo '<div class="callout alert">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . '</div></div></div>';
    include '../bottom.php';
    exit;
}

if ($mode == 'view') {
    // Validare oID
    if (!isset($_GET['oID']) || !is_numeric($_GET['oID'])) {
        echo '<div class="callout alert">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . '</div></div></div>';
        include '../bottom.php';
        exit;
    }
    $oID = (int)$_GET['oID'];
    
    // Query comandă cu prepared statement - verificare permisiuni
    if ($is_client) {
        // CLIENT vede doar comenzile sale
        $stmt_order = mysqli_prepare($conn, "SELECT * FROM magazin_comenzi WHERE comanda_ID=? AND comanda_cont=1 AND comanda_cont_id=?");
        mysqli_stmt_bind_param($stmt_order, 'ii', $oID, $uid);
    } else {
        // ADMIN vede toate comenzile
        $stmt_order = mysqli_prepare($conn, "SELECT * FROM magazin_comenzi WHERE comanda_ID=?");
        mysqli_stmt_bind_param($stmt_order, 'i', $oID);
    }
    mysqli_stmt_execute($stmt_order);
    $result = mysqli_stmt_get_result($stmt_order);
    $orderr = mysqli_fetch_array($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt_order);
    
    if (!$orderr) {
        echo '<div class="callout alert">' . htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8') . '</div></div></div>';
        include '../bottom.php';
        exit;
    }
    
    $orderuser = (int)$orderr['comanda_utilizator'];
    $comanda_cont = (int)$orderr['comanda_cont'];
    $comanda_cont_id = (int)$orderr['comanda_cont_id'];
    $company_id = (int)$orderr['company_id'];
    
    // Query articole cu prepared statement
    $stmt_items = mysqli_prepare($conn, "SELECT * FROM magazin_articole WHERE articol_idcomanda=?");
    mysqli_stmt_bind_param($stmt_items, 'i', $oID);
    mysqli_stmt_execute($stmt_items);
    $result_items = mysqli_stmt_get_result($stmt_items);
    $items_array = [];
    while ($temp_item = mysqli_fetch_array($result_items, MYSQLI_ASSOC)) {
        $items_array[] = $temp_item;
    }
    mysqli_stmt_close($stmt_items);
    
    $nume = count($items_array);
    
    if ($nume == 0) {
        echo '<div class="callout alert">' . htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8') . '</div>';
    } else {
        $ordertotal = 0;
        
        echo '<h2>' . htmlspecialchars($strOrder, ENT_QUOTES, 'UTF-8') . ' ' . (int)$oID . '</h2>';
        echo '<table><thead><tr>';
        echo '<th width="50%">' . htmlspecialchars($strProduct, ENT_QUOTES, 'UTF-8') . '</th>';
        echo '<th width="10%">' . htmlspecialchars($strProductPrice, ENT_QUOTES, 'UTF-8') . '</th>';
        echo '<th width="20%">' . htmlspecialchars($strQuantity, ENT_QUOTES, 'UTF-8') . '</th>';
        echo '<th width="10%">' . htmlspecialchars($strTotalPrice, ENT_QUOTES, 'UTF-8') . '</th>';
        echo '<th width="10%">' . htmlspecialchars($strVAT, ENT_QUOTES, 'UTF-8') . '</th>';
        echo '</tr></thead><tbody>';
        
        foreach ($items_array as $rowi) {
            $articol_produs = (int)$rowi['articol_produs'];
            
            // Query produs cu prepared statement
            $stmt_prod = mysqli_prepare($conn, "SELECT * FROM magazin_produse WHERE produs_id=?");
            mysqli_stmt_bind_param($stmt_prod, 'i', $articol_produs);
            mysqli_stmt_execute($stmt_prod);
            $result_prod = mysqli_stmt_get_result($stmt_prod);
            $row = mysqli_fetch_array($result_prod, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt_prod);
            
            if (!$row) continue;
            
            if ($row["produs_dpret"] !== '0.0000') {
                $unitprice = $row['produs_dpret'];
            } else {
                $unitprice = $row['produs_pret'];
            }
            
            $quantity = (int)$rowi['articol_cantitate'];
            $totalprice = $unitprice * $quantity;
            $ordertotal = $ordertotal + $totalprice;
            $VAT = $totalprice * $vatrat;
            
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['produs_nume'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td align="right">' . htmlspecialchars(romanize($unitprice), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td align="right">' . htmlspecialchars($quantity, ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td align="right">' . htmlspecialchars(romanize($totalprice), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td align="right">' . htmlspecialchars(romanize($VAT), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '</tr>';
        }
        
        $totalinterim = $ordertotal * $vatprc;
        $transportVAT = $transportprice * $vatrat;
        
        if ($totalinterim <= 400 && $paidtransport != "0") {
            echo '<tr>';
            echo '<td colspan="3">' . htmlspecialchars($strTransport, ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td align="right">' . htmlspecialchars(romanize($transportprice), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td align="right">' . htmlspecialchars(romanize($transportVAT), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '</tr>';
            $ordertotal = $ordertotal + $transportprice;
        }
        
        $totalVAT = $ordertotal * $vatrat;
        $finalprice = $ordertotal * $vatprc;
        
        echo '<tr><td colspan="3">' . htmlspecialchars($strTotal, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td align="right">' . htmlspecialchars(romanize($ordertotal), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td align="right">' . htmlspecialchars(romanize($totalVAT), ENT_QUOTES, 'UTF-8') . '</td></tr>';
        echo '<tr><td colspan="4">' . htmlspecialchars($strTotal, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td align="right">' . htmlspecialchars(romanize($finalprice), ENT_QUOTES, 'UTF-8') . '</td></tr>';
        echo '</tbody></table><br /><br />';
    }
    
    // Query date utilizator - diferit pentru cont vs guest
    $row1 = null;
    if ($comanda_cont == 1) {
        // Comandă cu cont - query site_accounts
        $stmt_buyer = mysqli_prepare($conn, "SELECT account_first_name as cumparator_prenume, account_last_name as cumparator_nume, account_email as cumparator_email, account_phone as cumparator_telefon, account_address as cumparator_adresa, account_city as cumparator_oras, account_county as cumparator_judet FROM site_accounts WHERE account_id=?");
        mysqli_stmt_bind_param($stmt_buyer, 'i', $comanda_cont_id);
        mysqli_stmt_execute($stmt_buyer);
        $result1 = mysqli_stmt_get_result($stmt_buyer);
        $row1 = mysqli_fetch_array($result1, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt_buyer);
    } else {
        // Comandă fără cont - query magazin_cumparatori
        $stmt_buyer = mysqli_prepare($conn, "SELECT * FROM magazin_cumparatori WHERE cumparator_id=?");
        mysqli_stmt_bind_param($stmt_buyer, 'i', $orderuser);
        mysqli_stmt_execute($stmt_buyer);
        $result1 = mysqli_stmt_get_result($stmt_buyer);
        $row1 = mysqli_fetch_array($result1, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt_buyer);
    }
    
    // Query firmă - diferit pentru cont vs guest
    $row2 = null;
    if ($company_id > 0) {
        if ($comanda_cont == 1) {
            // Comandă cu cont - query site_companies
            $stmt_company = mysqli_prepare($conn, "SELECT company_name as firma_nume, company_ro as firma_RO, company_VAT as firma_CIF, company_reg as firma_reg, company_address as firma_adresa, company_city as firma_oras, company_county as firma_judet, company_bank as firma_banca, company_iban as firma_IBAN FROM site_companies WHERE company_id=?");
            mysqli_stmt_bind_param($stmt_company, 'i', $company_id);
            mysqli_stmt_execute($stmt_company);
            $result2 = mysqli_stmt_get_result($stmt_company);
            $row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt_company);
        } else {
            // Comandă fără cont - query magazin_firme
            $stmt_company = mysqli_prepare($conn, "SELECT * FROM magazin_firme WHERE firma_ID=?");
            mysqli_stmt_bind_param($stmt_company, 'i', $company_id);
            mysqli_stmt_execute($stmt_company);
            $result2 = mysqli_stmt_get_result($stmt_company);
            $row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt_company);
        }
    }
    
    echo '<h3>' . htmlspecialchars($strUser, ENT_QUOTES, 'UTF-8') . '</h3><table>';
    if ($row1) {
        echo '<tr><td width="30%">' . htmlspecialchars($strName, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td width="70%">' . htmlspecialchars($row1['cumparator_prenume'] ?? '', ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($row1['cumparator_nume'] ?? '', ENT_QUOTES, 'UTF-8') . '</td></tr>';
        echo '<tr><td>' . htmlspecialchars($strEmail, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row1['cumparator_email'] ?? '', ENT_QUOTES, 'UTF-8') . '</td></tr>';
        echo '<tr><td>' . htmlspecialchars($strPhone, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row1['cumparator_telefon'] ?? '', ENT_QUOTES, 'UTF-8') . '</td></tr>';
        echo '<tr><td>' . htmlspecialchars($strAddress, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row1['cumparator_adresa'] ?? '', ENT_QUOTES, 'UTF-8') . '</td></tr>';
        echo '<tr><td>' . htmlspecialchars($strCity, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row1['cumparator_oras'] ?? '', ENT_QUOTES, 'UTF-8') . '</td></tr>';
        echo '<tr><td>' . htmlspecialchars($strCounty, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row1['cumparator_judet'] ?? '', ENT_QUOTES, 'UTF-8') . '</td></tr>';
    }
    echo '</table><br /><br />';
    
    echo '<h3>' . htmlspecialchars($strCompany, ENT_QUOTES, 'UTF-8') . '</h3>';
    if ($row2) {
        echo '<table>';
        echo '<tr><td width="30%">' . htmlspecialchars($strCompanyName, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td width="70%">' . htmlspecialchars($row2['firma_nume'] ?? '', ENT_QUOTES, 'UTF-8') . '</td></tr>';
        echo '<tr><td>' . htmlspecialchars($strCompanyVAT, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row2['firma_RO'] ?? '', ENT_QUOTES, 'UTF-8') . htmlspecialchars($row2['firma_CIF'] ?? '', ENT_QUOTES, 'UTF-8') . '</td></tr>';
        echo '<tr><td>' . htmlspecialchars($strCompanyRC, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row2['firma_reg'] ?? '', ENT_QUOTES, 'UTF-8') . '</td></tr>';
        echo '<tr><td>' . htmlspecialchars($strCompanyAddress, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row2['firma_adresa'] ?? '', ENT_QUOTES, 'UTF-8') . '</td></tr>';
        echo '<tr><td>' . htmlspecialchars($strCity, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row2['firma_oras'] ?? '', ENT_QUOTES, 'UTF-8') . '</td></tr>';
        echo '<tr><td>' . htmlspecialchars($strCounty, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row2['firma_judet'] ?? '', ENT_QUOTES, 'UTF-8') . '</td></tr>';
        echo '<tr><td>' . htmlspecialchars($strCompanyBank, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row2['firma_banca'] ?? '', ENT_QUOTES, 'UTF-8') . '</td></tr>';
        echo '<tr><td>' . htmlspecialchars($strCompanyIBAN, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row2['firma_IBAN'] ?? '', ENT_QUOTES, 'UTF-8') . '</td></tr>';
        echo '</table>';
    } else {
        echo '<p><em>Fără date firmă (persoană fizică)</em></p>';
    }
    echo '<br /><br />';
    echo '<a href="siteorders.php" class="button">' . htmlspecialchars($strBack, ENT_QUOTES, 'UTF-8') . ' &nbsp;<i class="fas fa-backward fa-xl"></i></a><hr>';
    include '../bottom.php';
    exit;
} elseif ($mode == 'delete') {
    // Validare oID
    if (!isset($_GET['oID']) || !is_numeric($_GET['oID'])) {
        echo '<div class="callout alert">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . '</div></div></div>';
        include '../bottom.php';
        exit;
    }
    $oID = (int)$_GET['oID'];
    
    // Verifică dacă comanda există și dacă utilizatorul are dreptul s-o șteargă
    if ($is_client) {
        $stmt_check = mysqli_prepare($conn, "SELECT comanda_status FROM magazin_comenzi WHERE comanda_ID=? AND comanda_cont=1 AND comanda_cont_id=?");
        mysqli_stmt_bind_param($stmt_check, 'ii', $oID, $uid);
    } else {
        $stmt_check = mysqli_prepare($conn, "SELECT comanda_status FROM magazin_comenzi WHERE comanda_ID=?");
        mysqli_stmt_bind_param($stmt_check, 'i', $oID);
    }
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $order_check = mysqli_fetch_array($result_check, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt_check);
    
    if (!$order_check) {
        echo '<div class="callout alert">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . ' - Comanda nu există sau nu aveți permisiunea s-o ștergeți.</div></div></div>';
        include '../bottom.php';
        exit;
    }
    
    // Verifică dacă comanda este închisă
    if ($order_check['comanda_status'] == 1) {
        echo '<div class="callout alert">Nu puteți șterge o comandă închisă.</div></div></div>';
        include '../bottom.php';
        exit;
    }
    
    // DELETE comenzi cu prepared statement
    $stmt_del_order = mysqli_prepare($conn, "DELETE FROM magazin_comenzi WHERE comanda_ID=?");
    mysqli_stmt_bind_param($stmt_del_order, 'i', $oID);
    mysqli_stmt_execute($stmt_del_order);
    mysqli_stmt_close($stmt_del_order);
    
    // DELETE articole cu prepared statement
    $stmt_del_items = mysqli_prepare($conn, "DELETE FROM magazin_articole WHERE articol_idcomanda=?");
    mysqli_stmt_bind_param($stmt_del_items, 'i', $oID);
    mysqli_stmt_execute($stmt_del_items);
    mysqli_stmt_close($stmt_del_items);
    
    echo '<div class="callout success">' . htmlspecialchars($strRecordDeleted, ENT_QUOTES, 'UTF-8') . '</div>';
    echo '<script type="text/javascript">
    function delayer(){
        window.location = "siteorders.php"
    }
    </script>
    <body onLoad="setTimeout(\'delayer()\', 1500)">';
    include '../bottom.php';
    exit;
} else {
    // Listare comenzi - COUNT pentru paginare
    if ($is_client) {
        // CLIENT vede doar comenzile sale
        $stmt_count = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM magazin_comenzi WHERE comanda_cont=1 AND comanda_cont_id=?");
        mysqli_stmt_bind_param($stmt_count, 'i', $uid);
        mysqli_stmt_execute($stmt_count);
        $count_result = mysqli_stmt_get_result($stmt_count);
        $count_row = mysqli_fetch_array($count_result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt_count);
    } else {
        // ADMIN vede toate comenzile (cu cont și fără cont)
        $count_query = "SELECT COUNT(*) as total FROM magazin_comenzi";
        $count_result = ezpub_query($conn, $count_query);
        $count_row = ezpub_fetch_array($count_result);
    }
    $nume = (int)$count_row['total'];
    
    $pages = new Pagination;  
    $pages->items_total = $nume;  
    $pages->mid_range = 5;  
    $pages->paginate();
    
    // Parse LIMIT pentru prepared statement - format MySQL: "LIMIT offset,count"
    $limit_string = trim(str_replace('LIMIT', '', $pages->limit));
    $limit_parts = explode(',', $limit_string);
    $offset_value = isset($limit_parts[0]) ? (int)trim($limit_parts[0]) : 0;
    $limit_value = isset($limit_parts[1]) ? (int)trim($limit_parts[1]) : 10;
    
    // Query principal cu JOIN
    if ($is_client) {
        // CLIENT vede doar comenzile sale cu cont - JOIN doar cu site_accounts
        $query = "SELECT mc.comanda_ID, mc.comanda_utilizator, mc.comanda_total, mc.comanda_status, mc.comanda_IP, 
                  mc.comanda_deschisa, mc.comanda_inchisa, mc.comanda_cont,
                  sa.account_first_name as cumparator_prenume,
                  sa.account_last_name as cumparator_nume,
                  sa.account_phone as cumparator_telefon,
                  sa.account_email as cumparator_email
                  FROM magazin_comenzi mc
                  INNER JOIN site_accounts sa ON mc.comanda_cont_id=sa.account_id
                  WHERE mc.comanda_cont=1 AND mc.comanda_cont_id=?
                  ORDER BY mc.comanda_deschisa DESC 
                  LIMIT ? OFFSET ?";
        
        $stmt_list = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt_list, 'iii', $uid, $limit_value, $offset_value);
    } else {
        // ADMIN vede toate comenzile (cu cont și fără cont) - LEFT JOIN pe ambele tabele
        $query = "SELECT mc.comanda_ID, mc.comanda_utilizator, mc.comanda_total, mc.comanda_status, mc.comanda_IP, 
                  mc.comanda_deschisa, mc.comanda_inchisa, mc.comanda_cont, mc.comanda_cont_id,
                  CASE WHEN mc.comanda_cont=1 THEN sa.account_first_name ELSE mcump.cumparator_prenume END as cumparator_prenume,
                  CASE WHEN mc.comanda_cont=1 THEN sa.account_last_name ELSE mcump.cumparator_nume END as cumparator_nume,
                  CASE WHEN mc.comanda_cont=1 THEN sa.account_phone ELSE mcump.cumparator_telefon END as cumparator_telefon,
                  CASE WHEN mc.comanda_cont=1 THEN sa.account_email ELSE mcump.cumparator_email END as cumparator_email
                  FROM magazin_comenzi mc
                  LEFT JOIN site_accounts sa ON mc.comanda_cont_id=sa.account_id
                  LEFT JOIN magazin_cumparatori mcump ON mc.comanda_utilizator=mcump.cumparator_id
                  ORDER BY mc.comanda_deschisa DESC 
                  LIMIT ? OFFSET ?";
        
        $stmt_list = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt_list, 'ii', $limit_value, $offset_value);
    }
    
    mysqli_stmt_execute($stmt_list);
    $result2 = mysqli_stmt_get_result($stmt_list);
    $orders_array = [];
    while ($temp_order = mysqli_fetch_array($result2, MYSQLI_ASSOC)) {
        $orders_array[] = $temp_order;
    }
    mysqli_stmt_close($stmt_list);
    
    if ($nume == 0) {
        echo htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8');
    } else {
?>
<div class="paginate">
    <?php
echo htmlspecialchars($strTotal, ENT_QUOTES, 'UTF-8') . " " . (int)$nume . " " . htmlspecialchars($strOrders, ENT_QUOTES, 'UTF-8');
echo " <br /><br />";
echo $pages->display_pages();
?>
</div>
<table id="rounded-corner" summary="<?php echo htmlspecialchars($strOrders, ENT_QUOTES, 'UTF-8')?>">
    <thead>
        <tr>
            <th><?php echo htmlspecialchars($strID, ENT_QUOTES, 'UTF-8')?></th>
            <th><?php echo htmlspecialchars($strDateCreated, ENT_QUOTES, 'UTF-8')?></th>
            <th><?php echo htmlspecialchars($strDateFinished, ENT_QUOTES, 'UTF-8')?></th>
            <th><?php echo htmlspecialchars($strClient, ENT_QUOTES, 'UTF-8')?></th>
            <th>Tip</th>
            <th><?php echo htmlspecialchars($strPhone, ENT_QUOTES, 'UTF-8')?></th>
            <th><?php echo htmlspecialchars($strEmail, ENT_QUOTES, 'UTF-8')?></th>
            <th><?php echo htmlspecialchars($strValue, ENT_QUOTES, 'UTF-8')?></th>
            <th><?php echo htmlspecialchars($strVAT, ENT_QUOTES, 'UTF-8')?></th>
            <th><?php echo htmlspecialchars($strTotal, ENT_QUOTES, 'UTF-8')?></th>
            <th><?php echo htmlspecialchars($strDetails, ENT_QUOTES, 'UTF-8')?></th>
            <th><?php echo htmlspecialchars($strDelete, ENT_QUOTES, 'UTF-8')?></th>
        </tr>
    </thead>
    <tbody>
        <?php 
foreach ($orders_array as $row) {
    $vat = $row['comanda_total'] * $vatrat;
    $ordertotal = $row['comanda_total'] * $vatprc;
    
    $comanda_ID = (int)$row['comanda_ID'];
    $comanda_deschisa = htmlspecialchars($row['comanda_deschisa'] ?? '', ENT_QUOTES, 'UTF-8');
    $comanda_inchisa = htmlspecialchars($row['comanda_inchisa'] ?? '', ENT_QUOTES, 'UTF-8');
    $cumparator_prenume = htmlspecialchars($row['cumparator_prenume'] ?? '', ENT_QUOTES, 'UTF-8');
    $cumparator_nume = htmlspecialchars($row['cumparator_nume'] ?? '', ENT_QUOTES, 'UTF-8');
    $cumparator_telefon = htmlspecialchars($row['cumparator_telefon'] ?? '', ENT_QUOTES, 'UTF-8');
    $cumparator_email = htmlspecialchars($row['cumparator_email'] ?? '', ENT_QUOTES, 'UTF-8');
    $comanda_total_safe = htmlspecialchars(romanize($row["comanda_total"]), ENT_QUOTES, 'UTF-8');
    $vat_safe = htmlspecialchars(romanize($vat), ENT_QUOTES, 'UTF-8');
    $ordertotal_safe = htmlspecialchars(romanize($ordertotal), ENT_QUOTES, 'UTF-8');
    $strConfirmDelete_safe = htmlspecialchars($strConfirmDelete, ENT_QUOTES, 'UTF-8');
    $strDelete_safe = htmlspecialchars($strDelete, ENT_QUOTES, 'UTF-8');
    
    echo '<tr>';
    echo '<td>' . $comanda_ID . '</td>';
    echo '<td>' . $comanda_deschisa . '</td>';
    echo '<td>' . $comanda_inchisa . '</td>';
    echo '<td>' . $cumparator_prenume . ' ' . $cumparator_nume . '</td>';
    if ($row['comanda_cont'] == 1) {
        echo '<td><i class="fas fa-user" title="Cu cont"></i></td>';
    } else {
        echo '<td><i class="fas fa-user-slash" title="Fără cont"></i></td>';
    }
    echo '<td>' . $cumparator_telefon . '</td>';
    echo '<td>' . $cumparator_email . '</td>';
    echo '<td>' . $comanda_total_safe . '</td>';
    echo '<td>' . $vat_safe . '</td>';
    echo '<td>' . $ordertotal_safe . '</td>';
    echo '<td><a href="siteorders.php?mode=view&oID=' . $comanda_ID . '"><i class="fas fa-info"></i></a></td>';
    
    // Buton ștergere - disabled dacă comanda este închisă (status=1)
    if ($row['comanda_status'] == 1) {
        echo '<td><i class="fa fa-eraser fa-xl" style="color: #ccc; cursor: not-allowed;" title="Comandă închisă - nu poate fi ștearsă"></i></td>';
    } else {
        echo '<td><a href="siteorders.php?mode=delete&oID=' . $comanda_ID . '" OnClick="return confirm(\'' . $strConfirmDelete_safe . '\');"><i class="fa fa-eraser fa-xl" title="' . $strDelete_safe . '"></i></a></td>';
    }
    echo '</tr>';
}
echo "</tbody></table>";
?>
        <div class="paginate">
            <?php
echo $pages->display_pages();
?>
        </div>

        <?php 
}
}
?>
        </div>
        </div>
        <hr>
        <?php
include '../bottom.php';
?>