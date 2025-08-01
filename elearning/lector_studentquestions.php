
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
<script src='../js/tinymce/tinymce.min.js'></script>
<script>
tinymce.init({
  selector: "textarea.myTextEditor",
  height: 500,
  menubar: false,
  image_advtab: true,
   plugins: [
    'advlist autolink lists link image imagetools charmap print preview anchor',
    'searchreplace visualblocks code fullscreen preview',
    'insertdatetime media table contextmenu paste code pagebreak'
  ],
  toolbar: 'insertfile undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image preview code pagebreak',
  content_css: [
    '//fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i',
    '//www.tiny.cloud/css/codepen.min.css'],
	 image_title: true, 
  // enable automatic uploads of images represented by blob or data URIs
  paste_data_images: true,
  automatic_uploads: true,
  // URL of our upload handler (for more details check: https://www.tinymce.com/docs/configure/file-image-upload/#images_upload_url)
  images_upload_url: 'postAcceptor.php?loc=questions',
    images_upload_base_path: '',
  images_upload_credentials: true,
  file_picker_types: 'file image media',
  
 file_picker_callback: function(cb, value, meta) {
    var input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image*');
    
    // Note: In modern browsers input[type="file"] is functional without 
    // even adding it to the DOM, but that might not be the case in some older
    // or quirky browsers like IE, so you might want to add it to the DOM
    // just in case, and visually hide it. And do not forget do remove it
    // once you do not need it anymore.

    input.onchange = function() {
      var file = this.files[0];
      
      var reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = function () {
        // Note: Now we need to register the blob in TinyMCEs image blob
        // registry. In the next release this part hopefully won't be
        // necessary, as we are looking to handle it internally.
        var id = 'blobid' + (new Date()).getTime();
        var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
        var base64 = reader.result.split(',')[1];
        var blobInfo = blobCache.create(id, file, base64);
        blobCache.add(blobInfo);

        // call the callback and populate the Title field with the file name
        cb(blobInfo.blobUri(), { title: file.name });
      };
    };
    
    input.click();
  }
});

</script>

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

	$mSQL = "INSERT INTO elearning_student_questions(";
	$mSQL = $mSQL . "student_id,";
	$mSQL = $mSQL . "lesson_id,";
    $mSQL = $mSQL . "trainer_answer,";
	$mSQL = $mSQL . "course_id)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$uid . "', ";
	$mSQL = $mSQL . "'" .$lid . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["elearning_trainer_answer"]) . "', ";
	$mSQL = $mSQL . "'" .$courseID . "') ";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"lector_sitecourses.php?lID=$courseID\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
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
<body onLoad=\"setTimeout('delayer()', 500)\">";
}
 } //ends post
Else { //show form
?>
    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
    <h1><?php echo $strQuestions ?></h1>
<?php
 $query4="SELECT elearning_student_questions.qID, elearning_student_questions.course_id, elearning_student_questions.student_question, elearning_student_questions.trainer_answer, elearning_student_questions.lesson_id, 
 elearning_lessons.lesson_title, elearning_lessons.lesson_ID
 FROM elearning_student_questions, elearning_lessons 
 WHERE elearning_student_questions.lesson_id=elearning_lessons.lesson_ID ORDER BY elearning_lessons.lesson_ID ASC";
$result4=ezpub_query($conn,$query4);
$numar4=ezpub_num_rows($result4,$query4);
if ($numar4==0)
{
echo $strNoRecordsFound;
}
Else { 
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
<form method="POST" action="lector_studentquestions.php?qID=<?php echo $_GET['qID']?>">
    <label>
        <?php echo $strQuestion?>
</label>
        <textarea name="elearning_trainer_answer" id="myTextEditor" class="myTextEditor"></textarea>
</div>
</div>
<div class="grid-x grid-margin-x">
              <div class="large-12 medium-12 small-12 cell">
    <p align="center"><input type="submit" value="<?php echo $strSubmit?>" class="button success"></p>
</div>
</div>
<?php
}
?>