<?php
// update 03.01.2025
include '../settings.php';
include '../classes/common.php';

// Validare și sanitizare input
$lang = isset($_SESSION['$lang']) && in_array($_SESSION['$lang'], ['RO', 'EN']) ? $_SESSION['$lang'] : 'RO';

$pageurl="shop/index.php";
$strKeywords="Proceduri Consaltis";
$strDescription="Proceduri, manuale...";
$strPageTitle="Magazin online";
include '../header.php';
include '../classes/paginator.class.php';
$producttrail="produse/";
$thumbnailstrail="img/products/";
?>
<div class="row column text-center">
    <h2><?php echo $strProducts?></h2>
    <hr>
</div>

<?php
$fullurl=$_SERVER["REQUEST_URI"];
$page=includeTrailingBackslash($fullurl);
$pieces = explode("/", $page);
$cats=substr_count($page,"/");
if ($cats==4) {
    $url = htmlspecialchars($pieces[3], ENT_QUOTES, 'UTF-8');
    $category = htmlspecialchars($pieces[2], ENT_QUOTES, 'UTF-8');
} elseif ($cats==3) {
    $url = htmlspecialchars($pieces[2], ENT_QUOTES, 'UTF-8');
    $category = htmlspecialchars($pieces[1], ENT_QUOTES, 'UTF-8');
} else {
    $url = htmlspecialchars($pieces[1], ENT_QUOTES, 'UTF-8');
    $category = "";
}
if ($category == $siteURLShort) {
    $category = "";
}

// Folosește mysqli prepared statements pentru SQL injection prevention
$stmt = mysqli_prepare($conn, "SELECT * FROM magazin_produse WHERE produs_limba=?");
mysqli_stmt_bind_param($stmt, 's', $lang);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$result_array = [];
while ($row_temp = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    $result_array[] = $row_temp;
}
mysqli_stmt_close($stmt);
$numar = count($result_array);

$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 

// Query pentru pagină curentă
$stmt2 = mysqli_prepare($conn, "SELECT * FROM magazin_produse WHERE produs_limba=? ORDER BY RAND() ASC $pages->limit");
mysqli_stmt_bind_param($stmt2, 's', $lang);
mysqli_stmt_execute($stmt2);
$result2_temp = mysqli_stmt_get_result($stmt2);
$result2 = [];
while ($row_temp2 = mysqli_fetch_array($result2_temp, MYSQLI_ASSOC)) {
    $result2[] = $row_temp2;
}
mysqli_stmt_close($stmt2);

If ($numar==0)
{echo $strNoRecordsFound;}
else {
	$i = 0;
?>
<div class="grid-x grid-padding-x">
    <div class="large-4 medium-4 small-4 cell">
        <div>
            <h3><?php echo htmlspecialchars($strCategory, ENT_QUOTES, 'UTF-8')?></h3>
            <ul class="vertical menu">
                <?php 
	  $stmt_cat = mysqli_prepare($conn, "SELECT DISTINCT produs_categorie, produs_fcategorie FROM magazin_produse WHERE produs_limba=?");
mysqli_stmt_bind_param($stmt_cat, 's', $lang);
mysqli_stmt_execute($stmt_cat);
$result_cat = mysqli_stmt_get_result($stmt_cat);
while ($row = mysqli_fetch_array($result_cat, MYSQLI_ASSOC)){
    echo '<li><a href="' . htmlspecialchars($strSiteURL . '/shop/' . $row['produs_categorie'] . '/', ENT_QUOTES, 'UTF-8') . '">' 
         . htmlspecialchars($row['produs_fcategorie'], ENT_QUOTES, 'UTF-8') . '</a></li>';
}
mysqli_stmt_close($stmt_cat);  ?>
            </ul>
            <hr />
        </div>
        <div class="promoted">
            <h3><?php echo htmlspecialchars($strPromotedProduct, ENT_QUOTES, 'UTF-8') ?></h3>
            <?php
			$stmt_promo = mysqli_prepare($conn, "SELECT * FROM magazin_produse WHERE produs_limba=? ORDER BY RAND() LIMIT 1");
			mysqli_stmt_bind_param($stmt_promo, 's', $lang);
			mysqli_stmt_execute($stmt_promo);
			$result_promo = mysqli_stmt_get_result($stmt_promo);
			$row = mysqli_fetch_array($result_promo, MYSQLI_ASSOC);
			mysqli_stmt_close($stmt_promo);
			if ($row) {
			    // Validare imagine pentru path traversal
			    $imagine = basename($row["produs_imagine"]);
			    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
			    $ext = strtolower(pathinfo($imagine, PATHINFO_EXTENSION));
			    if (!in_array($ext, $allowed_ext)) {
			        $imagine = 'placeholder.png';
			    }
			?>
            <img src="<?php echo htmlspecialchars($strSiteURL . '/img/products/' . $imagine, ENT_QUOTES, 'UTF-8') ?>" class="shopim">
            <h4><?php echo htmlspecialchars($row["produs_nume"], ENT_QUOTES, 'UTF-8')?></h4>
            <p class="smaller"><?php echo htmlspecialchars($row["produs_descriere"], ENT_QUOTES, 'UTF-8')?></p>
            <?php } ?>
        </div>
    </div>
    <div class="large-8 columns">
        <div class="grid-x grid-padding-x">
            <div class="large-12 medium-12 small cell">
                <div class="paginate">
                    <?php
echo $strTotal . " " .$numar." ".$strProducts ;
echo " <br /><br />";
echo $pages->display_pages();
?>
                </div>
            </div>
        </div>
        <div class="grid-x grid-margin-x" data-equalizer data-equalize-on="medium" id="test-eq">
            <?php 
foreach ($result2 as $row) {
	  $i++;
	  $vatrat = $row["produs_tva"] / 100;
	  $vatprc = $vatrat + 1;
	  
	  // Validare imagine pentru path traversal
	  $imagine_prod = basename($row['produs_imagine']);
	  $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
	  $ext = strtolower(pathinfo($imagine_prod, PATHINFO_EXTENSION));
	  if (!in_array($ext, $allowed_ext)) {
	      $imagine_prod = 'placeholder.png';
	  }
	  
	  // Escape și truncare nume produs
	  $productname_raw = $row['produs_nume'];
	  if (strlen($productname_raw) > 80) {
	      $productname = htmlspecialchars(substr($productname_raw, 0, 80), ENT_QUOTES, 'UTF-8') . "&hellip;";
	  } else {
	      $productname = htmlspecialchars($productname_raw, ENT_QUOTES, 'UTF-8');
	  }
	  
	  // Calculare preț
	  if ($row["produs_dpret"] !== '0.0000') {
	      $pprice = romanize($row["produs_dpret"] * $vatprc);
	  } else {
	      $pprice = romanize($row["produs_pret"] * $vatprc);
	  }
	  
	  // Escape toate output-urile
	  $product_url = htmlspecialchars($strSiteURL . '/' . $producttrail . $row['produs_url'], ENT_QUOTES, 'UTF-8');
	  $img_src = htmlspecialchars($strSiteURL . '/img/products/' . $imagine_prod, ENT_QUOTES, 'UTF-8');
	  $order_url = htmlspecialchars($strSiteURL . '/shop/order.php?action=order&pID=' . $row['produs_id'], ENT_QUOTES, 'UTF-8');
	  $cart_title = htmlspecialchars($strAddToCart . ' ' . $row['produs_nume'], ENT_QUOTES, 'UTF-8');
	  $price_label = htmlspecialchars($strPrice, ENT_QUOTES, 'UTF-8');
	  $add_cart_text = htmlspecialchars($strAddToCart, ENT_QUOTES, 'UTF-8');
	  
	  echo '<div class="large-3 medium-3 small-3 cell">';
	  echo '<div class="column" data-equalizer-watch>';
	  echo '<a href="' . $product_url . '"><h5>' . $productname . '</h5>';
	  echo '</div>';
	  echo '<div class="column align-self-bottom"><img src="' . $img_src . '" class="shopim"></a></div>';
	  echo '<div class="column align-self-bottom"><h6><strong>' . $price_label . ': ' . htmlspecialchars($pprice, ENT_QUOTES, 'UTF-8') . ' lei</strong></h6></div>';
	  echo '<div class="column align-self-bottom"><p><a href="' . $order_url . '" title="' . $cart_title . '" class="expanded button"><i class="fas fa-cart-plus"></i>&nbsp;' . $add_cart_text . '</a></p></div>';
	  echo '</div>';
	  
	  if ($i % 4 == 0) {
	      echo '</div><hr /><div class="grid-x grid-padding-x" data-equalizer data-equalize-on="medium" id="test-eq">';
	  }
}
}

echo "</div>"
?>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <div class="paginate">
                        <?php
echo $pages->display_pages();
?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr />
    <?php
include '../bottom.php';
?>