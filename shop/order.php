<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$url="shop/order.php";
$strKeywords="Comandă, produse , HACCP";
$strDescription="Produsele consaltis.";
$strPageTitle="Comandă";

include '../header.php';

// Validare și sanitizare input
if (!isset($_SESSION['buyer']) || empty($_SESSION['buyer'])) {
    echo '<div class="callout alert">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . '</div>';
    include '../bottom.php';
    exit;
}
$buyer = $_SESSION['buyer'];

echo '<div class="row"><div class="large-12 columns">';
echo '<h1>' . htmlspecialchars($strPageTitle, ENT_QUOTES, 'UTF-8') . '</h1>';

// Folosește prepared statement pentru query principal
$stmt = mysqli_prepare($conn, "SELECT * FROM magazin_comenzi WHERE comanda_utilizator=? AND comanda_status=0");
mysqli_stmt_bind_param($stmt, 's', $buyer);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$result_array = [];
while ($row_temp = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    $result_array[] = $row_temp;
}
mysqli_stmt_close($stmt);
$numar = count($result_array);

// Validare action și pID
if (isset($_GET['action']) && $_GET['action'] == "order") {
    if (!isset($_GET['pID']) || !is_numeric($_GET['pID'])) {
        echo '<div class="callout alert">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . '</div>';
        include '../bottom.php';
        exit;
    }
    $pID = (int)$_GET['pID'];

    if ($numar == 0) {
        // Comandă nouă
        $IP = getRealIpAddr();
        $data = date('Y-m-d H:i:s');
        $status = 0;
        
        // Check if user is logged in and has SHOP or BOTH function
        $comanda_cont = 0;
        $comanda_cont_id = 0;
        
        if (isset($_SESSION['uid']) && !empty($_SESSION['uid'])) {
            $uid = (int)$_SESSION['uid'];
            
            // Query to check account_function from site_accounts
            $stmt_check_user = mysqli_prepare($conn, "SELECT account_function FROM site_accounts WHERE account_id=?");
            mysqli_stmt_bind_param($stmt_check_user, 'i', $uid);
            mysqli_stmt_execute($stmt_check_user);
            $result_check_user = mysqli_stmt_get_result($stmt_check_user);
            $user_data = mysqli_fetch_array($result_check_user, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt_check_user);
            
            if ($user_data && in_array($user_data['account_function'], ['SHOP', 'BOTH'])) {
                $comanda_cont = 1;
                $comanda_cont_id = $uid;
            }
        }
        
        // INSERT comandă cu prepared statement
        $stmt_order = mysqli_prepare($conn, "INSERT INTO magazin_comenzi(comanda_utilizator, comanda_deschisa, comanda_status, comanda_IP, comanda_cont, comanda_cont_id) VALUES(?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_order, 'ssissi', $buyer, $data, $status, $IP, $comanda_cont, $comanda_cont_id);
        mysqli_stmt_execute($stmt_order);
        $oID = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_order);
        
        // INSERT articol cu prepared statement
        $quantity = 1;
        $stmt_item = mysqli_prepare($conn, "INSERT INTO magazin_articole(articol_produs, articol_cantitate, articol_idcomanda) VALUES(?, ?, ?)");
        mysqli_stmt_bind_param($stmt_item, 'iii', $pID, $quantity, $oID);
        mysqli_stmt_execute($stmt_item);
        mysqli_stmt_close($stmt_item);
    } else {
        // Comandă existentă
        $order_row = $result_array[0];
        $oID = (int)$order_row['comanda_ID'];
        
        // Verifică dacă produsul există deja în comandă
        $stmt_check = mysqli_prepare($conn, "SELECT articol_cantitate FROM magazin_articole WHERE articol_idcomanda=? AND articol_produs=?");
        mysqli_stmt_bind_param($stmt_check, 'ii', $oID, $pID);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $existing_item = mysqli_fetch_array($result_check, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt_check);
        
        if (!$existing_item) {
            // Produs nou în comandă
            $quantity = 1;
            $stmt_insert = mysqli_prepare($conn, "INSERT INTO magazin_articole(articol_produs, articol_cantitate, articol_idcomanda) VALUES(?, ?, ?)");
            mysqli_stmt_bind_param($stmt_insert, 'iii', $pID, $quantity, $oID);
            mysqli_stmt_execute($stmt_insert);
            mysqli_stmt_close($stmt_insert);
        } else {
            // Produs existent - incrementează cantitatea atomic
            $stmt_update = mysqli_prepare($conn, "UPDATE magazin_articole SET articol_cantitate = articol_cantitate + 1 WHERE articol_idcomanda=? AND articol_produs=?");
            mysqli_stmt_bind_param($stmt_update, 'ii', $oID, $pID);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
        }
    }
    
    // Mesaj succes și redirect
    echo '<div class="callout success radius">' . htmlspecialchars($strRecordAdded, ENT_QUOTES, 'UTF-8') . '</div>';
    echo '<script type="text/javascript">
    function delayer(){
        window.location = "order.php"
    }
    </script>
    <body onLoad="setTimeout(\'delayer()\', 1500)">';
    include '../bottom.php';
    exit;
}

// Afișare coș
if ($numar > 0) {
    $order_row = $result_array[0];
    $oID = (int)$order_row['comanda_ID'];
    
    // Query pentru articole din comandă cu prepared statement
    $stmt_items = mysqli_prepare($conn, "SELECT * FROM magazin_articole WHERE articol_idcomanda=?");
    mysqli_stmt_bind_param($stmt_items, 'i', $oID);
    mysqli_stmt_execute($stmt_items);
    $result_items_temp = mysqli_stmt_get_result($stmt_items);
    $items_array = [];
    while ($item_temp = mysqli_fetch_array($result_items_temp, MYSQLI_ASSOC)) {
        $items_array[] = $item_temp;
    }
    mysqli_stmt_close($stmt_items);
    
    $nume = count($items_array);
    
    if ($nume == 0) {
        echo htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8');
    } else {
        $ordertotal = 0;
        
        echo '<table width="100%">';
        echo '<thead>';
        echo '<th width="50%">' . htmlspecialchars($strProduct, ENT_QUOTES, 'UTF-8') . '</th>';
        echo '<th width="10%" align="right">' . htmlspecialchars($strProductPrice, ENT_QUOTES, 'UTF-8') . '</th>';
        echo '<th width="20%">' . htmlspecialchars($strQuantity, ENT_QUOTES, 'UTF-8') . '</th>';
        echo '<th width="10%">' . htmlspecialchars($strTotalPrice, ENT_QUOTES, 'UTF-8') . '</th>';
        echo '<th width="10%">' . htmlspecialchars($strVAT, ENT_QUOTES, 'UTF-8') . '</th>';
        echo '</thead>';
        
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
            
            $articol_id = (int)$rowi['articol_id'];
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['produs_nume'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td align="right">' . htmlspecialchars(romanize($unitprice), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td align="right">' . htmlspecialchars($quantity, ENT_QUOTES, 'UTF-8') . ' &nbsp;';
            echo '<a href="item.php?id=' . $articol_id . '&action=add"><i class="fas fa-plus"></i></a> ';
            echo '<a href="item.php?id=' . $articol_id . '&action=decrease"><i class="fas fa-minus"></i></a> ';
            echo '<a href="item.php?id=' . $articol_id . '&action=delete"><i class="far fa-trash-alt"></i></a>';
            echo '</td>';
            echo '<td align="right">' . htmlspecialchars(romanize($totalprice), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td align="right">' . htmlspecialchars(romanize($VAT), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '</tr>';
        }
        
        $totalinterim = $ordertotal * $vatprc;
        $totalVAT = $ordertotal * $vatrat;
        $totalorder = $ordertotal;
        
        if ($paidtransport == "1") {
            if ($totalinterim <= $transportlimit) {
                $transportVAT = $transportprice * $transportvatrat;
                echo '<tr><td colspan="3">' . htmlspecialchars($strTransport, ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td align="right">' . htmlspecialchars(romanize($transportprice), ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td align="right">' . htmlspecialchars(romanize($transportVAT), ENT_QUOTES, 'UTF-8') . '</td></tr>';
                $totalorder = $ordertotal + $transportprice;
                $orderVAT = $ordertotal * $vatrat;
                $totalVAT = $orderVAT + $transportVAT;
            }
        }
        $finalprice = $totalorder + $totalVAT;
        
        echo '<tr><td colspan="3">' . htmlspecialchars($strTotals, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td align="right">' . htmlspecialchars(romanize($totalorder), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td align="right">' . htmlspecialchars(romanize($totalVAT), ENT_QUOTES, 'UTF-8') . '</td></tr>';
        echo '<tr><td colspan="4">' . htmlspecialchars($strTotal, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td align="right">' . htmlspecialchars(romanize($finalprice), ENT_QUOTES, 'UTF-8') . '</td></tr></table>';
        echo '<div class="grid-x grid-padding-x">';
        echo '<div class="large-12 cell"><p class="text-right">';
        echo '<a href="selectcase.php?oID=' . (int)$oID . '" class="button"><i class="fas fa-shopping-cart"></i>&nbsp;' . htmlspecialchars($strSendOrder, ENT_QUOTES, 'UTF-8') . '</a>';
        echo '</p></div></div>';
    }
}

echo '<div class="grid-x grid-padding-x">';
echo '<div class="large-12 cell right">';
echo '<a href="' . htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') . '/shop/index.php" class="button">Înapoi la produse</a>';
echo '</div></div></div></div>';
include '../bottom.php';
?>