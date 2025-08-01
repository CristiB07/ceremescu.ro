
<?php
//updated 17.07.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';


if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['$lang'])) {
	$_SESSION['$lang']="RO";
	$lang=$_SESSION['$lang'];
}
Else
{
	$lang=$_SESSION['$lang'];
}
if ($lang=="RO") {
include '../lang/language_RO.php';
}
Else
{
	include '../lang/language_EN.php';
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/elearning/login.php?message=MLF");
}
$uid=$_SESSION['uid'];
if (isSet($_GET["slide"])) {
	$slide=$_GET["slide"];
}
Else
{
	$slide=1;
}
$back=$slide-1;
$next=$slide+1;

$query="SELECT * FROM elearning_lessons WHERE lesson_ID=$_GET[lID]";
$result=ezpub_query($conn, $query);
if ($row=ezpub_fetch_array($result)) {
?>
<!doctype html>
<head>
<!--Start Header-->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"/> <![endif]-->
<!--[if IE 7]> <html class="no-js lt-ie9 lt-ie8" lang="en"/> <![endif]-->
<!--[if IE 8]> <html class="no-js lt-ie9" lang="en"/> <![endif]-->
<!--[if gt IE 8]><!--><html class="no-js" lang="en"> <!--<![endif]-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!--Font Awsome-->
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/all.css">
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/foundation.css"/>
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/<?php echo $cssname ?>.css"/>

<script>
  function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
  }
</script>
 </head>
    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
    <h1><?php  echo $row['lesson_title']?> <?php echo $strSlideNumber?> <?php echo $slide?></h1>
<?php
$mytext=$row['lesson_body'];
$pagebreak="<!-- pagebreak -->";
$slides = explode($pagebreak, $mytext);
$maxslides=count($slides);
//echo $slides;

foreach ($slides as $key => $value) {
	$page=$key+1;
    echo  "<div class=\"callout\" id=\"$page\"";
if (isSet($_GET["slide"])) {
	$slide=$_GET["slide"];
	If ($slide==$page) {
 echo "style=\"display: block;\">";}	
Else
{
	echo "style=\"display: none;\">";
}}
Else{
	$slide=1;
If ($slide==$page) {
 echo "style=\"display: block;\">";}	
Else
{
	echo "style=\"display: none;\">";
}	
}
    echo $value. "</div>";	
}
echo "<div class=\"row\"><div class=\"clearfix\">";
if ($back==0){
echo  "<a class=\"button float-left\" id=\"button\">$strPrevious</a>";
}
Else
{
	echo  "<a class=\"button float-left\" id=\"button\" href=\"student_slideshow.php?lID=$_GET[lID]&slide=$back\">$strPrevious</a>";
}
if ($next>=$maxslides){
 echo " <a class=\"button float-right\" id=\"button\" >$strNext</a>";
}
Else
{
 echo " <a class=\"button float-right\" id=\"button\" href=\"student_slideshow.php?lID=$_GET[lID]&slide=$next\">$strNext</a>";
}
echo "</div>";
  ?>
	<div class="paginate">
<?php
// Numărul maxim de slide-uri

// Slide-ul curent (poate fi preluat din URL sau altă sursă)
$slide = isset($_GET['slide']) ? (int)$_GET['slide'] : 1;

// Asigură-te că slide-ul curent este în intervalul valid
if ($slide < 1) {
    $slide = 1;
} elseif ($slide > $maxslides) {
    $slide = $maxslides;
}
// Funcție pentru generarea linkurilor
function generateLink($slide, $currentSlide) {
    return $slide == $currentSlide ? "<a class=\"current\" href=\"#\">$slide</a>" : "<a href=\"student_slideshow.php?lID=$_GET[lID]&slide=$slide\" class=\"paginate\">$slide</a>";
}
If ($slide==2){echo "<a href=\"student_slideshow.php?lID=$_GET[lID]&slide=1\" class=\"paginate\">1</a>";}
If ($slide==3){
	echo "<a href=\"student_slideshow.php?lID=$_GET[lID]&slide=1\" class=\"paginate\">1</a>";
	echo "<a href=\"student_slideshow.php?lID=$_GET[lID]&slide=2\" class=\"paginate\">2</a>";}

// Afișează linkurile pentru primele 3 slide-uri
if ($slide >= 4) {
for ($i = 1; $i <= 3; $i++) {
    if ($i == $slide) {
        echo generateLink($i, $slide) . " ";
    } else {
        echo "<a href=\"student_slideshow.php?lID=$_GET[lID]&slide=$i\" class=\"paginate\">$i</a>";
    }
}
}


// Afișează `...` dacă este necesar
if ($slide > 6) {
    echo "... ";
}

// Afișează 3 slide-uri înainte de slide-ul curent
for ($i = max(4, $slide - 3); $i < $slide; $i++) {
    echo generateLink($i, $slide) . " ";
}

// Afișează slide-ul curent
echo generateLink($slide, $slide) . " ";

// Afișează 3 slide-uri după slide-ul curent
for ($i = $slide + 1; $i <= min($maxslides - 3, $slide + 3); $i++) {
    echo generateLink($i, $slide) . " ";
}

// Afișează `...` dacă este necesar
if ($slide < $maxslides - 5) {
    echo "... ";
}

// Afișează linkurile pentru ultimele 3 slide-uri
for ($i = $maxslides - 2; $i <= $maxslides; $i++) {
    if ($i == $slide) {
        echo generateLink($i, $slide) . " ";
    } else {
        echo "<a href=\"student_slideshow.php?lID=$_GET[lID]&slide=$i\">$i</a>";
    }
}
?>
</div>
  </div>
  </div>
</div>
   </div>
<?php }?>