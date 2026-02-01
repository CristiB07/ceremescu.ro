<?php
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
else
{
	$lang=$_SESSION['$lang'];
}
if ($lang=="RO") {
include '../lang/language_RO.php';
}
else
{
	include '../lang/language_EN.php';
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/account/login.php?message=MLF");
}
$uid=$_SESSION['uid'];

// Sanitize and validate input parameters
$lID = isset($_GET['lID']) ? (int)$_GET['lID'] : 0;
if ($lID <= 0) {
    header("location:$strSiteURL/dashboard/dashboard.php");
    die();
}

if (isSet($_GET["slide"])) {
	$slide = (int)$_GET["slide"];
}
else
{
	$slide=1;
}
$back=$slide-1;
$next=$slide+1;

// Use prepared statement to prevent SQL injection
$stmt = mysqli_prepare($conn, "SELECT * FROM elearning_lessons WHERE lesson_ID = ?");
mysqli_stmt_bind_param($stmt, "i", $lID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = ezpub_fetch_array($result)) {
?>
<!doctype html>

<head>
    <!--Start Header-->
    <!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"/> <![endif]-->
    <!--[if IE 7]> <html class="no-js lt-ie9 lt-ie8" lang="en"/> <![endif]-->
    <!--[if IE 8]> <html class="no-js lt-ie9" lang="en"/> <![endif]-->
    <!--[if gt IE 8]><!-->
    <html class="no-js" lang="en">
    <!--<![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!--Font Awsome-->
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/all.css">
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/foundation.css" />
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/<?php echo $cssname ?>.css" />

    <script>
    function resizeIframe(obj) {
        obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
    }
    </script>
</head>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <h1><?php  echo $row['lesson_title']?></h1> <h2><?php echo $strSlideNumber?> <?php echo $slide?></h2>
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
else
{
	echo "style=\"display: none;\">";
}}
else{
	$slide=1;
If ($slide==$page) {
 echo "style=\"display: block;\">";}	
else
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
else
{
	echo  "<a class=\"button float-left\" id=\"button\" href=\"student_slideshow.php?lID=" . (int)$lID . "&slide=" . (int)$back . "\">$strPrevious</a>";
}
if ($next>=$maxslides){
 echo " <a class=\"button float-right\" id=\"button\" >$strNext</a>";
}
else
{
 echo " <a class=\"button float-right\" id=\"button\" href=\"student_slideshow.php?lID=" . (int)$lID . "&slide=" . (int)$next . "\">$strNext</a>";
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
function generateLink($slide, $currentSlide, $lID) {
    $lID = (int)$lID;
    $slide = (int)$slide;
    return $slide == $currentSlide ? "<a class=\"current\" href=\"#\">$slide</a>" : "<a href=\"student_slideshow.php?lID=$lID&slide=$slide\" class=\"paginate\">$slide</a>";
}

// Dacă sunt puține slide-uri (≤6), afișează-le simplu pe toate
if ($maxslides <= 6) {
    for ($i = 1; $i <= $maxslides; $i++) {
        echo generateLink($i, $slide, $lID) . " ";
    }
} else {
    // Logică complexă pentru multe slide-uri
    
    // Afișează linkurile pentru primele 3 slide-uri dacă suntem departe de început
    if ($slide >= 4) {
        for ($i = 1; $i <= 3; $i++) {
            echo generateLink($i, $slide, $lID) . " ";
        }
    }

    // Afișează `...` dacă este necesar
    if ($slide > 6) {
        echo "... ";
    }

    // Afișează 3 slide-uri înainte de slide-ul curent
    for ($i = max(4, $slide - 3); $i < $slide; $i++) {
        echo generateLink($i, $slide, $lID) . " ";
    }

    // Afișează slide-ul curent
    echo generateLink($slide, $slide, $lID) . " ";

    // Afișează 3 slide-uri după slide-ul curent
    for ($i = $slide + 1; $i <= min($maxslides - 3, $slide + 3); $i++) {
        echo generateLink($i, $slide, $lID) . " ";
    }

    // Afișează `...` dacă este necesar
    if ($slide < $maxslides - 5) {
        echo "... ";
    }

    // Afișează linkurile pentru ultimele 3 slide-uri
    for ($i = $maxslides - 2; $i <= $maxslides; $i++) {
        if ($i > 0) { // Verifică că indexul e valid
            echo generateLink($i, $slide, $lID) . " ";
        }
    }
}
?>
        </div>
    </div>
</div>
</div>
</div>
<?php }?>