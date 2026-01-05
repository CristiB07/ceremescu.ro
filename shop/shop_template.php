<?php

// Validare și sanitizare input
if (!isset($lang) || !in_array($lang, ['RO', 'EN'], true)) {
    $lang = 'RO';
}
if (!isset($url) || empty($url)) {
    header("Location: $strSiteURL/shop/");
    exit;
}
if (!isset($category) || empty($category)) {
    $category = 'Products';
}

$strPageTitle = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
include 'header.php';

// COUNT query cu prepared statement
$stmt_count = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM magazin_produse WHERE produs_limba=? AND produs_categorie=?");
mysqli_stmt_bind_param($stmt_count, 'ss', $lang, $url);
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$count_row = mysqli_fetch_array($result_count, MYSQLI_ASSOC);
$numar = (int)$count_row['total'];
mysqli_stmt_close($stmt_count);

$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate();

// SELECT query cu prepared statement - NU folosim LIMIT/OFFSET în bind_param
// Pentru mysqli prepared statements, LIMIT trebuie construit diferit
$limit_str = $pages->limit; // De ex: "LIMIT 10 OFFSET 0"
$stmt_products = mysqli_prepare($conn, "SELECT * FROM magazin_produse WHERE produs_limba=? AND produs_categorie=? ORDER BY produs_nume ASC $limit_str");
mysqli_stmt_bind_param($stmt_products, 'ss', $lang, $url);
mysqli_stmt_execute($stmt_products);
$result2 = mysqli_stmt_get_result($stmt_products);
$result2_array = [];
while ($temp_row = mysqli_fetch_array($result2, MYSQLI_ASSOC)) {
    $result2_array[] = $temp_row;
}
mysqli_stmt_close($stmt_products);

if ($numar==0) {
    echo "<p>$strNoRecordsFound</p>";
} else {
	$i = 0;
?>
<nav aria-label="<?php echo htmlspecialchars($strYouAreHere, ENT_QUOTES, 'UTF-8')?>:" role="navigation">
    <ul class="breadcrumbs">
        <li><a href="<?php echo htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8')?>"><?php echo htmlspecialchars($strHome, ENT_QUOTES, 'UTF-8')?></a></li>
        <li><a href="<?php echo htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') . '/shop/' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8')?>"><?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8')?></a></li>
        <li class="disabled"><?php echo htmlspecialchars($strPageTitle, ENT_QUOTES, 'UTF-8')?></li>
    </ul>
</nav>
<div class="grid-x grid-padding-x">
    <div class="large-4 medium-4 small-4 cell">
        <div>
            <h3><?php echo htmlspecialchars($strCategory, ENT_QUOTES, 'UTF-8')?></h3>
            <ul class="vertical menu">
                <?php 
                // Query categorii cu prepared statement
                $stmt_cat = mysqli_prepare($conn, "SELECT DISTINCT produs_categorie, produs_fcategorie FROM magazin_produse WHERE produs_limba=?");
                mysqli_stmt_bind_param($stmt_cat, 's', $lang);
                mysqli_stmt_execute($stmt_cat);
                $result_cat = mysqli_stmt_get_result($stmt_cat);
                while ($row = mysqli_fetch_array($result_cat, MYSQLI_ASSOC)) {
                    $cat_url = htmlspecialchars($row['produs_categorie'], ENT_QUOTES, 'UTF-8');
                    $cat_name = htmlspecialchars($row['produs_fcategorie'], ENT_QUOTES, 'UTF-8');
                    echo '<li><a href="' . htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') . '/shop/' . $cat_url . '/" class="vertical">' . $cat_name . '</a></li>';
                }
                mysqli_stmt_close($stmt_cat);
                ?>
            </ul>
            <hr />
        </div>
        <div class="promoted">
            <h3><?php echo htmlspecialchars($strPromotedProduct, ENT_QUOTES, 'UTF-8') ?></h3>
            <?php
            // Query produs promovat cu prepared statement
            $stmt_promo = mysqli_prepare($conn, "SELECT * FROM magazin_produse WHERE produs_limba=? ORDER BY RAND() LIMIT 1");
            mysqli_stmt_bind_param($stmt_promo, 's', $lang);
            mysqli_stmt_execute($stmt_promo);
            $result_promo = mysqli_stmt_get_result($stmt_promo);
            $row = mysqli_fetch_array($result_promo, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt_promo);
            
            if ($row) {
                $promo_image = basename($row["produs_imagine"]);
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($promo_image, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed_ext, true)) {
                    $promo_image = 'default.jpg';
                }
            ?>
            <img src="<?php echo htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') ?>/img/products/<?php echo htmlspecialchars($promo_image, ENT_QUOTES, 'UTF-8')?>" class="shopim">
            <h4><?php echo htmlspecialchars($row["produs_nume"], ENT_QUOTES, 'UTF-8')?></h4>
            <p class="smaller"><?php echo htmlspecialchars($row["produs_descriere"], ENT_QUOTES, 'UTF-8')?></p>
            <?php } ?>
        </div>
    </div>
    <div class="large-8 columns">
        <div class="grid-x grid-padding-x">
            <div class="large-12 cell">
                <div class="paginate">
                    <?php
echo htmlspecialchars($strTotal, ENT_QUOTES, 'UTF-8') . " " . (int)$numar . " " . htmlspecialchars($strProducts, ENT_QUOTES, 'UTF-8');
echo " <br /><br />";
echo $pages->display_pages();
?>
                </div>
            </div>
        </div>
        <div class="grid-x grid-margin-x" data-equalizer data-equalize-on="medium" id="test-eq">
            <?php 
            $i = 0;
            foreach ($result2_array as $row) {
                $i++;
                $vatrat = $row["produs_tva"] / 100;
                $vatprc = $vatrat + 1;
                
                // Validare imagine
                $product_image = basename($row['produs_imagine']);
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($product_image, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed_ext, true)) {
                    $product_image = 'default.jpg';
                }
                
                // Truncate nume produs
                $productname = (strlen($row['produs_nume']) > 80) 
                    ? substr($row['produs_nume'], 0, 80) . "&hellip;" 
                    : $row['produs_nume'];
                
                // Calcul preț
                if ($row["produs_dpret"] !== '0.0000') {
                    $pprice = romanize($row["produs_dpret"] * $vatprc);
                } else {
                    $pprice = romanize($row["produs_pret"] * $vatprc);
                }
                
                $product_id = (int)$row['produs_id'];
                $product_url = htmlspecialchars($row['produs_url'], ENT_QUOTES, 'UTF-8');
                $product_name_safe = htmlspecialchars($row['produs_nume'], ENT_QUOTES, 'UTF-8');
                $productname_safe = htmlspecialchars($productname, ENT_QUOTES, 'UTF-8');
                $pprice_safe = htmlspecialchars($pprice, ENT_QUOTES, 'UTF-8');
                
                echo '<div class="large-3 medium-3 small-3 cell">';
                echo '<div class="column" data-equalizer-watch>';
                echo '<a href="' . htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') . '/' . htmlspecialchars($producttrail, ENT_QUOTES, 'UTF-8') . $product_url . '">';
                echo '<h5>' . $productname_safe . '</h5>';
                echo '</a></div>';
                echo '<div class="column align-self-bottom">';
                echo '<a href="' . htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') . '/' . htmlspecialchars($producttrail, ENT_QUOTES, 'UTF-8') . $product_url . '">';
                echo '<img src="' . htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') . '/img/products/' . htmlspecialchars($product_image, ENT_QUOTES, 'UTF-8') . '" class="shopim">';
                echo '</a></div>';
                echo '<div class="column align-self-bottom">';
                echo '<h6><strong>' . htmlspecialchars($strPrice, ENT_QUOTES, 'UTF-8') . ': ' . $pprice_safe . ' lei</strong></h6>';
                echo '</div>';
                echo '<div class="column align-self-bottom"><p>';
                echo '<a href="' . htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') . '/shop/order.php?action=order&pID=' . $product_id . '" ';
                echo 'title="' . htmlspecialchars($strAddToCart, ENT_QUOTES, 'UTF-8') . ' ' . $product_name_safe . '" ';
                echo 'class="expanded button">';
                echo '<i class="fas fa-cart-plus"></i>&nbsp;' . htmlspecialchars($strAddToCart, ENT_QUOTES, 'UTF-8');
                echo '</a></p></div>';
                echo '</div>';
                
                if ($i % 4 == 0) {
                    echo '</div><hr /><div class="grid-x grid-padding-x" data-equalizer data-equalize-on="medium" id="test-eq">';
                }
            }

echo "</div>";
}
?>
            <div class="grid-x grid-padding-x">
                <div class="large-12 cell">
                    <div class="paginate">
                        <?php
echo $pages->display_pages();
?>
                    </div>
                </div>
            </div>
        </div>
    </div>

