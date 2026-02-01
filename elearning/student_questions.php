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
$uid=(int)$_SESSION['uid'];
// Sanitize and validate input
$lID = isset($_GET['lID']) ? (int)$_GET['lID'] : 0;
if ($lID <= 0) {
    header("location:$strSiteURL/dashboard/dashboard.php");
    die();
}
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

check_inject();

// Use prepared statement to get course ID
$stmt_course = mysqli_prepare($conn, "SELECT lesson_course FROM elearning_lessons WHERE lesson_ID=?");
mysqli_stmt_bind_param($stmt_course, "i", $lID);
mysqli_stmt_execute($stmt_course);
$result = mysqli_stmt_get_result($stmt_course); 
$row=ezpub_fetch_array($result);
$courseID=(int)$row['lesson_course'];

// Use prepared statement for INSERT
$stmt_insert = mysqli_prepare($conn, "INSERT INTO elearning_student_questions(student_id, lesson_id, student_question, course_id) VALUES (?, ?, ?, ?)");
$question = mysqli_real_escape_string($conn, $_POST["elearning_student_question"]);
mysqli_stmt_bind_param($stmt_insert, "iisi", $uid, $lID, $question, $courseID);
				
//It executes the SQL
if (!mysqli_stmt_execute($stmt_insert))
  {
  die('Error: ' . mysqli_error($conn));
  }
else{
echo "<div class=\"callout success\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    // Reload parent page since this runs in an iframe
    if (window.parent && window.parent !== window) {
        window.parent.location.reload();
    } else {
        window.location.reload();
    }
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
 $stmt_questions = mysqli_prepare($conn, "SELECT elearning_student_questions.qID, elearning_student_questions.course_id, elearning_student_questions.student_question, elearning_student_questions.trainer_answer, elearning_student_questions.lesson_id, elearning_lessons.lesson_title, elearning_lessons.lesson_ID FROM elearning_student_questions, elearning_lessons WHERE elearning_student_questions.lesson_id=elearning_lessons.lesson_ID AND elearning_student_questions.lesson_id=? ORDER BY elearning_lessons.lesson_ID ASC");
mysqli_stmt_bind_param($stmt_questions, "i", $lID);
mysqli_stmt_execute($stmt_questions);
$result4 = mysqli_stmt_get_result($stmt_questions);
$numar4=mysqli_num_rows($result4);
if ($numar4==0)
{
echo "<div class=\"callout alert\">$strNoRecordsFound</div>";
}
else { 
While ($row4=ezpub_fetch_array($result4)){
	echo "<div class=\"callout\">
	<h4>$row4[lesson_title]</h4>
	<h4>$row4[student_question]</h4>
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
        <form method="POST" action="student_questions.php?lID=<?php echo (int)$lID?>">
            <label>
                <h2><?php echo $strAddQuestion?></h2>
                <textarea name="elearning_student_question" class="simple-html-editor" data-upload-dir="elearning"></textarea>
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