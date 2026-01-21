<?php
// Validare și sanitizare input
if (!isset($url) || empty($url)) {
    http_response_code(404);
    $strKeywords = "Pagina nu a fost găsită";
    $strDescription = "Pagina nu a fost găsită";
    $strPageTitle = "Pagina nu a fost găsită";
    $pageurl = '404.php';
    include 'header.php';
    echo '<div class="grid-x grid-padding-x"><div class="large-12 cell"><div class="callout alert"><h1>Pagina nu a fost găsită</h1></div></div></div>';
    include 'bottom.php';
    exit;
}

// Query produs cu prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM magazin_produse WHERE produs_url=?");
mysqli_stmt_bind_param($stmt, 's', $url);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

if ($row) {
$strKeywords = htmlspecialchars($row['produs_keywords'] ?? '', ENT_QUOTES, 'UTF-8');
$strDescription = htmlspecialchars($row['produs_meta'] ?? '', ENT_QUOTES, 'UTF-8');
$strPageTitle = htmlspecialchars($row['produs_nume'] ?? 'Produs', ENT_QUOTES, 'UTF-8');
include 'header.php';
$vatrat = $row["produs_tva"] / 100;
$vatprc = $vatrat + 1;

// Validare imagine principală
$main_image = basename($row["produs_imagine"] ?? 'default.jpg');
$allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$ext = strtolower(pathinfo($main_image, PATHINFO_EXTENSION));
if (!in_array($ext, $allowed_ext, true)) {
    $main_image = 'default.jpg';
}
$main_image_safe = htmlspecialchars($main_image, ENT_QUOTES, 'UTF-8');

$produs_id = (int)$row['produs_id'];
$produs_nume_safe = htmlspecialchars($row['produs_nume'] ?? '', ENT_QUOTES, 'UTF-8');
$produs_descriere_safe = $row['produs_descriere'] ?? ''; // Presupunem că e deja HTML valid
$produs_categorie_safe = htmlspecialchars($row['produs_categorie'] ?? '', ENT_QUOTES, 'UTF-8');
$produs_fcategorie_safe = htmlspecialchars($row['produs_fcategorie'] ?? '', ENT_QUOTES, 'UTF-8');
?>
        <script>
        function changeImage(p) {
            document.getElementById("main").src = p;
            document.getElementById("modal").src = p;
            document.getElementById('a').style.backgroundImage = "url('p')";
            var abc = p;
        }
        </script>
        <nav aria-label="<?php echo htmlspecialchars($strYouAreHere, ENT_QUOTES, 'UTF-8')?>:" role="navigation">
            <ul class="breadcrumbs">
                <li><a href="<?php echo htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8')?>"><?php echo htmlspecialchars($strHome, ENT_QUOTES, 'UTF-8')?></a></li>
                <li><a href="<?php echo htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') . '/shop/' . $produs_categorie_safe?>"><?php echo $produs_fcategorie_safe?></a></li>
                <li class="disabled"><?php echo $strPageTitle?></li>
            </ul>
        </nav>

        <div class="grid-x grid-margin-x">
          <div class="large-12 medium-12 small-12 columns">
          <h1><?php echo $strPageTitle?></h1>
          </div></div>
        <div class="grid-x grid-margin-x">
          <div class="medium-6 cell">
					
        <div class="large reveal" id="Modal1" data-reveal>
            <!-- Modal content -->
            <h3><?php echo $produs_nume_safe?></h3>
            <p align="center"><img id="modal" name="modal"
                    src="<?php echo htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8')?>/img/products/<?php echo $main_image_safe?>" height="auto"
                    width="auto" alt="<?php echo $produs_nume_safe?>" /></p>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <?php 
        $strSiteURL_safe = htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8');
        echo '<a href="#" data-open="Modal1"><img id="main" name="main" class="zoom" src="' . $strSiteURL_safe . '/img/products/' . $main_image_safe . '" height="auto" width="auto" alt="' . $produs_nume_safe . '" /></a>';
        
        // Procesare thumbnails
        $pimages = [];
        if (!empty($row["produs_thumb"])) {
            $pimages_raw = explode(", ", $row["produs_thumb"]);
            foreach ($pimages_raw as $img) {
                $img_clean = basename($img);
                $ext = strtolower(pathinfo($img_clean, PATHINFO_EXTENSION));
                if (in_array($ext, $allowed_ext, true)) {
                    $pimages[] = htmlspecialchars($img_clean, ENT_QUOTES, 'UTF-8');
                }
            }
        }
        
        echo '<div class="grid-x grid-padding-x small-up-4 align-center">';
        echo '<button id="prev" class="button"><<<</button>';
        echo '<ul id="myUl" class="menu">';
        echo '<li class="myLi"><a href="#" OnClick="changeImage(\'' . $strSiteURL_safe . '/img/products/' . $main_image_safe . '\')">';
        echo '<img src="' . $strSiteURL_safe . '/img/products/' . $main_image_safe . '" alt="' . $produs_nume_safe . '" width="180px" /></a></li>';
        
        foreach ($pimages as $pimage) {
            echo '<div class="cell">';
            echo '<li class="myLi"><a href="#" OnClick="changeImage(\'' . $strSiteURL_safe . '/img/products/' . $pimage . '\')">';
            echo '<img src="' . $strSiteURL_safe . '/img/products/' . $pimage . '" alt="' . $produs_nume_safe . '" width="180px" /></a></li>';
            echo '</div>';
        }
        
        echo '</ul>';
        echo '<button id="next" class="button align-right">>>></button></div>';
        echo '</div>';
        echo '<div class="medium-6 large-5 cell large-offset-1">';
        echo $produs_descriere_safe . '<br />';
        
        $sprice = $row['produs_pret'] * $vatprc;
        $fprice = romanize($sprice);
        $pprice = $row["produs_dpret"] * $vatprc;
        $dprice = romanize($pprice);
        
        $fprice_safe = htmlspecialchars($fprice, ENT_QUOTES, 'UTF-8');
        $dprice_safe = htmlspecialchars($dprice, ENT_QUOTES, 'UTF-8');
        $strAddToCart_safe = htmlspecialchars($strAddToCart, ENT_QUOTES, 'UTF-8');
        $strPrice_safe = htmlspecialchars($strPrice, ENT_QUOTES, 'UTF-8');
        $strPromoPrice_safe = htmlspecialchars($strPromoPrice, ENT_QUOTES, 'UTF-8');
        
        echo '<p><a href="' . $strSiteURL_safe . '/shop/order.php?action=order&pID=' . $produs_id . '" title="' . $strAddToCart_safe . ' ' . $produs_nume_safe . '" class="expanded button"><i class="fas fa-cart-plus"></i>&nbsp;' . $strAddToCart_safe . '</a></p>';
        
        if ($row["produs_dpret"] !== '0.0000') {
            echo '<h3>' . $strPrice_safe . '<span style="text-decoration:line-through">: ' . $fprice_safe . ' lei</span> <span style="color: red;">' . $strPromoPrice_safe . ':&nbsp;&nbsp;' . $dprice_safe . ' lei</span></h3>';
        } else {
            echo '<h3>' . $strPrice_safe . ': ' . $fprice_safe . ' lei</h3>';
        }
        
        echo '</div></div><hr />';
        echo '<div class="grid-x grid-margin-x"><div class="large-12 medium-12 small-12 columns">';
        echo '<h3>' . htmlspecialchars($strSimilarProducts, ENT_QUOTES, 'UTF-8') . '</h3></div></div>';
        echo '<div class="grid-x grid-padding-x">';
        
        // Query produse similare cu prepared statement
        $stmt_similar = mysqli_prepare($conn, "SELECT * FROM magazin_produse ORDER BY produs_nume LIMIT 4");
        mysqli_stmt_execute($stmt_similar);
        $result2 = mysqli_stmt_get_result($stmt_similar);
        $similar_array = [];
        while ($temp_row = mysqli_fetch_array($result2, MYSQLI_ASSOC)) {
            $similar_array[] = $temp_row;
        }
        mysqli_stmt_close($stmt_similar);
        
        $numar = count($similar_array);
        
        if ($numar == 0) {
            echo '<p>' . htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8') . '</p>';
        } else {
            foreach ($similar_array as $sim_row) {
                // Validare imagine produs similar
                $sim_image = basename($sim_row['produs_imagine']);
                $ext = strtolower(pathinfo($sim_image, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed_ext, true)) {
                    $sim_image = 'default.jpg';
                }
                
                $sim_id = (int)$sim_row['produs_id'];
                $sim_url = htmlspecialchars($sim_row['produs_url'], ENT_QUOTES, 'UTF-8');
                $sim_nume = htmlspecialchars($sim_row['produs_nume'], ENT_QUOTES, 'UTF-8');
                $sim_image_safe = htmlspecialchars($sim_image, ENT_QUOTES, 'UTF-8');
                
                if ($sim_row["produs_dpret"] !== '0.0000') {
                    $pprice = romanize($sim_row["produs_dpret"] * $vatprc);
                } else {
                    $pprice = romanize($sim_row["produs_pret"] * $vatprc);
                }
                $pprice_safe = htmlspecialchars($pprice, ENT_QUOTES, 'UTF-8');
                
                $producttrail_safe = htmlspecialchars($producttrail, ENT_QUOTES, 'UTF-8');
                $thumbnailstrail_safe = htmlspecialchars($thumbnailstrail, ENT_QUOTES, 'UTF-8');
                
                echo '<div class="large-3 medium-3 small-3 cell">';
                echo '<a href="' . $strSiteURL_safe . '/' . $producttrail_safe . $sim_url . '">';
                echo '<h5>' . $sim_nume . '</h5></a>';
                echo '<h6><strong>' . $strPrice_safe . ': ' . $pprice_safe . ' lei</strong></h6>';
                echo '<img src="' . $strSiteURL_safe . '/' . $thumbnailstrail_safe . $sim_image_safe . '" class="shopim">';
                echo '<p><a href="' . $strSiteURL_safe . '/shop/order.php?action=order&pID=' . $sim_id . '" ';
                echo 'title="' . $strAddToCart_safe . ' ' . $sim_nume . '" class="expanded button">';
                echo '<i class="fas fa-cart-plus"></i>&nbsp;' . $strAddToCart_safe . '</a></p>';
                echo '</div>';
            }
        }
        echo '</div>';
}

else
{
    http_response_code(404);
    $strKeywords="Pagina nu a fost găsită";
$strDescription="Pagina nu a fost găsită";
$strPageTitle="Pagina nu a fost găsită";
$pageurl='404.php';
include 'header.php';
$previous = "javascript:history.go(-1)";
if(isset($_SERVER['HTTP_REFERER'])) {
    $previous = $_SERVER['HTTP_REFERER'];
}

echo "<div class=\"grid-x grid-padding-x\" >
<div class=\"large-12 medium-12 small-12 columns\">
<div class=\"callout alert\">";
echo "<h1><i class=\"fas fa-exclamation-triangle fa-xl\"></i>&nbsp;Pagina nu a fost găsită</h1>";
echo "<p>Adresa pe care o căutați nu a fost găsită. Linkul care v-a adus aici poate fi depășit sau,
 dacă ați introdus manual adresa, este posibil să o fi scris greșit.</p>
 <p>Dacă problema persistă, vă rugăm să ne contactați la <a href=\"mailto:" . $siteCompanyEmail . "\">" . $siteCompanyEmailMasked . "</a> pentru asistență.</p>
 <p align=\"center\">
 <a href=\"" . $strSiteURL . "\" class=\"button\"><i class=\"fas fa-home fa-xl\"></i>&nbsp;Înapoi la pagina principală</a> 
 <a href=\"" . $previous . "\" class=\"button\"><i class=\"fas fa-backward fa-xl\"></i>&nbsp;Înapoi la pagina anterioară</a> 
 </p>";
echo "</div></div></div>";
}?>
      <script>
        const list = {
            target: document.getElementById("myUl"),
            fullList: document.getElementById("myUl").querySelectorAll(".myLi"),
            itemsToList: 3,
            index: 0,
            // remove all children, append the amout of items we want
            update: function() {
                while (this.target.firstChild) {
                    this.target.removeChild(this.target.firstChild);
                }
                for (let i = 0; i < this.itemsToList; i += 1) {
                    if (this.fullList[this.index + i]) {
                        this.target.appendChild(this.fullList[this.index + i]);
                    }
                }
            },
            prev: function() {
                // if index 1 is displayed, go to end of list
                if (this.index <= 0) {
                    this.index = this.fullList.length;
                }
                // decrement the index
                this.index -= this.itemsToList;
                // lower edge case, catch to always list the same amout of items
                if (this.index < 0) {
                    this.index = 0;
                }
            },
            next: function() {
                // increment the index
                this.index += this.itemsToList;
                // if last item is shown start from beginning
                if (this.index >= this.fullList.length) {
                    this.index = 0;
                }
                // catch upper edge case, always list the same amout of items
                else if (this.index > this.fullList.length - this.itemsToList + 1) {
                    this.index = this.fullList.length - this.itemsToList;
                }
            }
        }
        // initialize by removing list and showing from index[0]
        list.update();

        document.getElementById("prev").addEventListener('click', function() {
            list.prev();
            list.update();
        });

        document.getElementById("next").addEventListener('click', function() {
            list.next();
            list.update();
        });
        </script>