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
	header("location:$strSiteURL/elearning/login.php?message=MLF");
}
$uid=$_SESSION['uid'];
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
    <script src='../js/simple-editor/simple-editor.js'></script>
    <link rel="stylesheet" href='../js/simple-editor/simple-editor.css'>

</head>
<?php
 if ($_SERVER['REQUEST_METHOD'] == 'POST'){

$query="SELECT lesson_course FROM elearning_lessons WHERE lesson_ID=$_GET[lID]";
$result=ezpub_query($conn, $query); 
$row=ezpub_fetch_array($result);
$courseID=$row['lesson_course'];

check_inject();
If (isset($_GET['lID'])){
//insert nlew question
    $lid=$_GET['lID'];

	$mSQL = "INSERT INTO elearning_account_questions(";
	$mSQL = $mSQL . "account_id,";
	$mSQL = $mSQL . "lesson_id,";
    $mSQL = $mSQL . "trainer_answer,";
	$mSQL = $mSQL . "course_id)";

	$mSQL = $mSQL . "values(";
	$mSQL = $mSQL . "'" .$uid . "', ";
	$mSQL = $mSQL . "'" .$lid . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["elearning_trainer_answer"]) . "', ";
	$mSQL = $mSQL . "'" .$courseID . "') ";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
else{
echo "<div class=\"callout success\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"lector_sitecourses.php?lID=$courseID\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
}}
else {
    echo "<div class=\"callout alert\">$strThereWasAnError</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"$strSiteURL/dashboard/dashboard.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
}
 } //ends post
else { //show form
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <h1><?php echo $strQuestions ?></h1>
        <?php
 $query4="SELECT elearning_account_questions.qID, elearning_account_questions.course_id, elearning_account_questions.account_question, elearning_account_questions.trainer_answer, elearning_account_questions.lesson_id, 
 elearning_lessons.lesson_title, elearning_lessons.lesson_ID
 FROM elearning_account_questions, elearning_lessons 
 WHERE elearning_account_questions.lesson_id=elearning_lessons.lesson_ID ORDER BY elearning_lessons.lesson_ID ASC";
$result4=ezpub_query($conn,$query4);
$numar4=ezpub_num_rows($result4,$query4);
if ($numar4==0)
{
echo "<div class=\"callout alert\">$strNoRecordsFound</div>";
}
else { 
While ($row4=ezpub_fetch_array($result4)){
	echo "<div class=\"callout\">
	<h4>$row4[lesson_title]</h4>
	<h4>$row4[account_question]</h4>
<div class=\"callout primary\">
	$row4[trainer_answer]</h4>
</div>
	</div>";
}
}
  ?>
    </div>
</div>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <form method="POST" action="lector_studentquestions.php?qID=<?php echo $_GET['qID']?>">
            <label>
                <?php echo $strQuestion?>
                <textarea name="elearning_trainer_answer" id="simple-html-editor" class="simple-html-editor"></textarea>
            </label>
    </div>
</div>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell text-center">
        <input type="submit" value="<?php echo $strSubmit?>" class="button success">
    </div>
</div>
<?php
}
?>