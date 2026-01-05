<?php
//update 8.01.2024

include 'settings.php';
include 'classes/common.php';
include 'classes/paginator.class.php';

$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$producttrail="produse/";
$thumbnailstrail="img/products/";

$fullurl=$_SERVER["REQUEST_URI"];
//$page=includeTrailingBackslash($fullurl);
$fullpage=includeTrailingBackslash($fullurl);
$page=str_replace("masterapp.ro/", "",$fullpage);
$pieces = explode("/", $page);
$cats=substr_count($page,"/");
If ($cats==4) {
$url=$pieces[3];
$category=$pieces[2];
$whereto=$pieces[1];
}
elseIf ($cats==3) {
$url=$pieces[2];
$category=$pieces[1];
$whereto="";}
elseIf ($cats==2) {
$url=$pieces[1];
$category=$pieces[0];
$whereto="";}
else
{
$url=$pieces[1];
$category="";
$whereto="";
;}
If ($category==$siteURLShort) {
$category="";}
//redirect to right page
//echo $url . "= url<br />";
//echo $category . "= category<br />";
//echo $whereto . "= whereto<br />";


// Regular pages
if ($url!="" AND $category=="") {
    include 'cms/page_template.php';
}
// Blog

elseif ($url!="" AND $category=="blog") {
include 'blog/blog_template.php';
}

//Cursuri
elseif ($url!="" AND $category=="cursuri") {
include 'elearning/courses_template.php';
}

// Products pages
//categories
elseif ($category=="shop" AND $url!="") {
    include 'shop/shop_template.php';
}
elseif ($whereto=="shop" AND $url!="") {
    include 'shop/category_template.php';
}
//singleproduct
elseif 
($category=="produse" AND $url!="") {
 include 'shop/single_product_template.php';
}

else {
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
}
?>
        <hr />
        <?php
include 'bottom.php';
?>