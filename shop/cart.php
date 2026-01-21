<?php
// update 6.01.2026
// Mini cart for header dropdown menu - Foundation compatible

// Prevent direct access - must be included from header
if (!defined('IN_HEADER')) {
    exit;
}

if (!isset($_SESSION['buyer']) || empty($_SESSION['buyer'])) {
    return;
}

$buyer = $_SESSION['buyer'];

// Query pentru comanda activă cu prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM magazin_comenzi WHERE comanda_utilizator=? AND comanda_status=0 LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $buyer);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order_row = mysqli_fetch_array($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

if (!$order_row) {
    return;
}

$oID = (int)$order_row['comanda_ID'];

// Query pentru articole din comandă cu prepared statement
$stmt_items = mysqli_prepare($conn, "SELECT * FROM magazin_articole WHERE articol_idcomanda=? LIMIT 5");
mysqli_stmt_bind_param($stmt_items, 'i', $oID);
mysqli_stmt_execute($stmt_items);
$result_items = mysqli_stmt_get_result($stmt_items);
$items_array = [];
while ($item = mysqli_fetch_array($result_items, MYSQLI_ASSOC)) {
    $items_array[] = $item;
}
mysqli_stmt_close($stmt_items);

$nume = count($items_array);

if ($nume == 0) {
    return;
}

$ordertotal = 0;
$totalVAT = 0;

// Afișare articole în format listă Foundation
foreach ($items_array as $rowi) {
    $articol_produs = (int)$rowi['articol_produs'];
    
    // Query produs cu prepared statement
    // Query produs cu prepared statement
    $stmt_prod = mysqli_prepare($conn, "SELECT produs_id, produs_nume, produs_pret, produs_dpret, produs_tva, produs_imagine FROM magazin_produse WHERE produs_id=?");
    mysqli_stmt_bind_param($stmt_prod, 'i', $articol_produs);
    mysqli_stmt_execute($stmt_prod);
    $result_prod = mysqli_stmt_get_result($stmt_prod);
    $product = mysqli_fetch_array($result_prod, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt_prod);
    
    if (!$product) continue;
    
    // Calculează prețul
    if ($product["produs_dpret"] !== '0.0000' && $product["produs_dpret"] > 0) {
        $unitprice = $product['produs_dpret'];
    } else {
        $unitprice = $product['produs_pret'];
    }
    
    $vatrat = $product["produs_tva"] / 100;
    $quantity = (int)$rowi['articol_cantitate'];
    $totalprice = $unitprice * $quantity;
    $ordertotal += $totalprice;
    $VAT = $totalprice * $vatrat;
    $totalVAT += $VAT;
    
    $produs_nume = htmlspecialchars($product['produs_nume'], ENT_QUOTES, 'UTF-8');
    $pret_display = htmlspecialchars(romanize($unitprice), ENT_QUOTES, 'UTF-8');
    $currency_display = htmlspecialchars($currency ?? 'RON', ENT_QUOTES, 'UTF-8');
    $produs_imagine = !empty($product['produs_imagine']) ? htmlspecialchars($product['produs_imagine'], ENT_QUOTES, 'UTF-8') : '';
    
    // Afișare cu imagine thumbnail
    echo '<li><a style="display: flex; align-items: center; gap: 10px;">';
    if ($produs_imagine) {
        echo '<img src="' . htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') . '/img/products/' . $produs_imagine . '" alt="' . $produs_nume . '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">';
    }
    echo '<div style="flex: 1;">';
    echo '<strong>' . $produs_nume . '</strong><br>';
    echo '<small>' . $quantity . ' x ' . $pret_display . ' ' . $currency_display . '</small>';
    echo '</div>';
    echo '</a></li>';
}

// Afișare total
$finalprice = $ordertotal + $totalVAT;
$total_label = htmlspecialchars($strTotal ?? 'Total', ENT_QUOTES, 'UTF-8');
$total_display = htmlspecialchars(romanize($finalprice), ENT_QUOTES, 'UTF-8');
$currency_display = htmlspecialchars($currency ?? 'RON', ENT_QUOTES, 'UTF-8');

echo '<li class="divider"></li>';
echo '<li><a><strong>' . $total_label . ': ' . $total_display . ' ' . $currency_display . '</strong></a></li>';

// Link către coș complet
$view_cart_text = htmlspecialchars($strViewCart ?? 'Vezi coșul', ENT_QUOTES, 'UTF-8');
echo '<li><a href="' . htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') . '/shop/order.php?oID=' . $oID . '" class="button small expanded">';
echo '<i class="fas fa-shopping-cart"></i> ' . $view_cart_text;
echo '</a></li>';
?>
