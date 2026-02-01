<?php
require_once 'settings.php';
include 'classes/common.php';
include 'classes/paginator.class.php';
$pageurl="index.php";
$producttrail="produse/";
$thumbnailstrail="img/products/";
$strPageTitle="Proceduri ISO, proceduri de lucru, proceduri operaționale";
include 'header.php';
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
?>
<!-- Start content-->
<?php
// Afișează caruselul doar pe prima pagină (fără parametri de paginare)
if (!isset($_GET['page']) || $_GET['page'] == 1) {
?>
<!-- Start carousel-->
<div class="grid-x grid-margin-x">
    <div class="large-12 cell">
        <div class="orbit" role="region" aria-label="CertPlus" data-orbit>
            <div class="orbit-wrapper">
                <div class="orbit-controls">
                    <button class="orbit-previous"><span
                            class="show-for-sr"><?php echo $strPrevious?></span>&#9664;&#xFE0E;</button>
                    <button class="orbit-next"><span
                            class="show-for-sr"><?php echo $strNext?></span>&#9654;&#xFE0E;</button>
                </div>
                <ul class="orbit-container">
                    <li class="is-active orbit-slide">
                        <figure class="orbit-figure">
                            <img class="orbit-image" src="img/slider/iso9001.jpg" alt="proceduri ISO 9001...">
                            <figcaption class="orbit-caption">
                                Proceduri și formulare pentru certificarea sistemelor de management al calității în baza
                                standardului ISO 9001:20015
                            </figcaption>
                        </figure>
                    </li>
                    <li class="orbit-slide">
                        <figure class="orbit-figure">
                            <img class="orbit-image" src="img/slider/iso14001.jpg" alt="Certificare ISO 14001">
                            <figcaption class="orbit-caption">
                                Proceduri și formulare pentru certificarea sistemelor de management al mediului în baza
                                standardului ISO 14001:2015
                            </figcaption>
                        </figure>
                    </li>
                    <li class="orbit-slide">
                        <figure class="orbit-figure">
                            <img class="orbit-image" src="img/slider/iso45001.jpg" alt="Certificare ISO 45001">
                            <figcaption class="orbit-caption">
                                Proceduri și formulare pentru certificarea sistemelor de management al sănătății și
                                securității în muncă în baza ISO 45001:2018
                            </figcaption>
                        </figure>
                    </li>
                    <li class="orbit-slide">
                        <figure class="orbit-figure">
                            <img class="orbit-image" src="img/slider/iso22000.jpg" alt="Certificare ISO 22000">
                            <figcaption class="orbit-caption">
                                Proceduri și formulare pentru certificarea sistemelor de management al siguranței
                                alimentare în baza ISO 22000:2019 sau HACCP
                            </figcaption>
                        </figure>
                    </li>
                    <li class="orbit-slide">
                        <figure class="orbit-figure">
                            <img class="orbit-image" src="img/slider/iso27001.jpg" alt="Certificare ISO 27001">
                            <figcaption class="orbit-caption">
                                Proceduri și formulare pentru certificarea sistemelor de management al securității
                                informațiilor în baza ISO 27001:2022
                            </figcaption>
                        </figure>
                    </li>
                    <li class="orbit-slide">
                        <figure class="orbit-figure">
                            <img class="orbit-image" src="img/slider/iso22301.jpg" alt="Certificare ISO 22301">
                            <figcaption class="orbit-caption">
                                Proceduri și formulare pentru certificarea sistemelor de management al continuității
                                afacerilor în baza ISO 22301:2019
                            </figcaption>
                        </figure>
                    </li>
                    <li class="orbit-slide">
                        <figure class="orbit-figure">
                            <img class="orbit-image" src="img/slider/iso37001.jpg" alt="Certificare ISO 37001">
                            <figcaption class="orbit-caption">
                                Proceduri și formulare pentru certificarea sistemelor de management anti-mită în baza
                                ISO 37001:2016
                            </figcaption>
                        </figure>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- End carousel-->
<?php
}
?>

<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <h2><?php echo $strProducts?></h2>
        <hr>
    </div>
</div>

<?php
$fullurl=$_SERVER["REQUEST_URI"];
$page=includeTrailingBackslash($fullurl);
$pieces = explode("/", $page);
$cats=substr_count($page,"/");
If ($cats==4) {
$url=$pieces[3];
$category=$pieces[2];}
elseIf ($cats==3) {
$url=$pieces[2];
$category=$pieces[1];}
else
{
$url=$pieces[1];
$category="";
;}
If ($category==$siteURLShort) {
$category="";}
//redirect to right page

$query="SELECT * FROM magazin_produse WHERE produs_limba='$lang' ";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result, $query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query2= $query . " ORDER BY rand () ASC $pages->limit" ;
$result2=ezpub_query($conn,$query2);

If ($numar==0)
{echo "<div class=\"callout alert\">$strNoRecordsFound</div>";}
else {
	$i = 0;
?>
<div class="grid-x grid-padding-x">
    <div class="large-4 medium-4 small-4 cell">
        <div>
            <h3><?php echo $strCategory?></h3>
            <ul class="vertical menu">
                <?php 
	  $query="SELECT Distinct produs_categorie, produs_fcategorie FROM magazin_produse WHERE produs_limba='$lang'";
$result=ezpub_query($conn,$query);
While ($row=ezpub_fetch_array($result)){
echo "<li><a href=\"$strSiteURL/shop/$row[produs_categorie]/\">$row[produs_fcategorie]</a></li>";
}  ?>
            </ul>
            <hr />
        </div>
        <div class="promoted">
            <h3><?php echo $strPromotedProduct ?></h3>
            <?php
			$query="SELECT * FROM magazin_produse WHERE produs_limba='$lang' ORDER BY rand() Limit 1";
			$result=ezpub_query($conn,$query);
			$row=ezpub_fetch_array($result)
			?>
            <img src="<?php echo $strSiteURL ?>/img/products/<?php echo $row["produs_imagine"]?>" class="shopim">
            <h4><?php echo $row["produs_nume"]?></h4>
            <p class="smaller"><?php echo $row["produs_descriere"]?></p>
        </div>
    </div>
    <div class="large-8 columns">
        <div class="grid-x grid-padding-x">
            <div class="large-12 cell">
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
While ($row=ezpub_fetch_array($result2)){
	  $i++ ;
	  $vatrat=$row["produs_tva"]/100;
	  $vatprc=$vatrat+1;
	echo "<div class=\"large-3 medium-3 small-3 cell \">
    <div class=\"column\" data-equalizer-watch>";
		if (strlen($row['produs_nume'])>80)
	{$productname=substr($row['produs_nume'], 0, 80)."&hellip;";}
else
{$productname=$row['produs_nume'];}
		echo "<a href=\"$strSiteURL/$producttrail$row[produs_url]\"><h5>$productname</h5></div>";
		
					If ($row["produs_dpret"]!=='0.0000')
					{
					$pprice=romanize($row["produs_dpret"]*$vatprc);
					}
					else
					{
						$pprice=romanize($row["produs_pret"]*$vatprc);
					}
echo                    "
                  <div class=\"column align-self-bottom\"><img src=\"$strSiteURL/img/products/$row[produs_imagine]\" class=\"shopim\"></a></div>
                   <div class=\"column align-self-bottom\"><h6><strong>$strPrice:" . " $pprice " ." lei</strong></h6></div>
					<div class=\"column align-self-bottom\"><p><a href=\"$strSiteURL/shop/order.php?action=order&pID=$row[produs_id]\" title=\"$strAddToCart $row[produs_nume]\" class=\"expanded button\"><i class=\"fas fa-cart-plus\"></i>&nbsp;$strAddToCart</a></p></div>
				</div>";
								  if($i%4 == 0) {
                echo  "</div><hr />
				<div class=\"grid-x grid-padding-x\"  data-equalizer data-equalize-on=\"medium\" id=\"test-eq\">";

	}
}
}

echo "</div>"
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
    <!-- Ends first page content-->