<?php
//updated 17.07.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare lecții";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/elearning/login.php?message=MLF");
}
$uid=$_SESSION['uid'];
?>
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
  images_upload_url: 'postAcceptor.php?loc=lessons',
    images_upload_base_path: '..',
  images_upload_credentials: true,
  file_picker_types: 'file image media',
  
 file_picker_callback: function(cb, value, meta) {
    var input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    
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

    <div class="grid-x grid-padding-x">
	<div class="large-12 medium-12 small-12 cell">

<?php

echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM elearning_lessons WHERE lesson_ID=" .$_GET['lID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"lector_sitelessons.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">
</div>";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){
//insert new user
	$mSQL = "INSERT INTO elearning_lessons(";
	$mSQL = $mSQL . "lesson_title,";
	$mSQL = $mSQL . "lesson_trainer,";
	$mSQL = $mSQL . "lesson_level,";
	$mSQL = $mSQL . "lesson_course,";
	$mSQL = $mSQL . "lesson_body)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["lesson_title"]) . "', ";
	$mSQL = $mSQL . "'" .$uid . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["lesson_level"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["lesson_course"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["lesson_body"]) ."')";
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
 
Else{
echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"lector_sitelessons.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{// edit
$strWhereClause = " WHERE elearning_lessons.lesson_ID=" . $_GET["lID"] . ";";
$query= "UPDATE elearning_lessons SET elearning_lessons.lesson_title='" .str_replace("'","&#39;",$_POST["lesson_title"]) . "' ," ;
$query= $query . " elearning_lessons.lesson_trainer='" . $uid . "', "; 
$query= $query . " elearning_lessons.lesson_level='" . $_POST["lesson_level"] . "', "; 
$query= $query . " elearning_lessons.lesson_course='" .str_replace("'","&#39;",$_POST["lesson_course"]) . "', "; 
$query= $query . " elearning_lessons.lesson_body='" .str_replace("'","&#39;",$_POST["lesson_body"]) . "' "; 
$query= $query . $strWhereClause;

if (!ezpub_query($conn,$query))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"lector_sitelessons.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}
}
}
Else {
?>

<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
<form Method="post"  Action="lector_sitelessons.php?mode=new" >
    <div class="grid-x grid-padding-x">
	<div class="large-5 medium-5 small-5 cell">
   <label><?php echo $strCourse?></label>
      <select name="lesson_course" class="required">
           <option value="0"><?php echo $strPick?></option>
          <?php $sql = "Select Course_ID, course_name FROM elearning_courses WHERE course_author=$uid ORDER BY course_name ASC";
        $result = ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["Course_ID"]?>"><?php echo $rss["course_name"]?></option>
          <?php
}?>
        </select>
</div>
<div class="large-5 medium-5 small-5 cell">
	  <label><?php echo $strTitle?></label>
	  <input name="lesson_title" Type="text" size="30" class="required" />
</div>
	 <div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strLevel?></label>
	  <input name="lesson_level" Type="text" size="3" class="required number"/>
</div>
</div>

	     <div class="grid-x grid-padding-x">
	<div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strBody?></label>
    <textarea name="lesson_body" id="3" style= "width:100%" class="myTextEditor" ></textarea>
		</div>	
		</div>	
	     <div class="grid-x grid-padding-x">
	<div class="large-12 medium-12 small-12 cell">
      <p align="center"
      ><input type="submit" value="<?php echo $strAdd?>" class="button success">
    </p>
</form>
</div>
</div>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM elearning_lessons WHERE lesson_ID=$_GET[lID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
<form Method="post" Action="lector_sitelessons.php?mode=edit&lID=<?php echo $row['lesson_ID']?>" >
	     <div class="grid-x grid-padding-x">
	<div class="large-5 medium-5 small-5 cell">
      <label><?php echo $strCourse?></label>
      <select name="lesson_course" class="required">
           <option value="0"><?php echo $strMaster?></option>
          <?php $sql = "Select Course_ID, course_name FROM elearning_courses WHERE course_author=$uid ORDER BY course_name ASC";
        $result = ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
		if ($row['lesson_course']==$rss['Course_ID']) {
	?>
          <option  selected value="<?php echo $rss["Course_ID"]?>"><?php echo $rss["course_name"]?></option>
		  <?php } else { ?>
          <option  value="<?php echo $rss["Course_ID"]?>"><?php echo $rss["course_name"]?></option>
          <?php
}}?>
        </select>
    </div>
  <div class="large-5 medium-5 small-5 cell">
	  <label><?php echo $strTitle?></label>
	  <input name="lesson_title" Type="text" size="30" class="required" value="<?php echo $row['lesson_title']?>"/>
	</div>
	 <div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strLevel?></label>
	  <input name="lesson_level" Type="text" size="3" class="required number" value="<?php echo $row['lesson_level']?>"/>
</div>
</div>
		       <div class="grid-x grid-padding-x">
     <div class="large-12 medium-12 small-12 cell"> 
      	  <label><?php echo $strBody?></label>
	  <textarea name="lesson_body" id="3" class="myTextEditor" style="width: 100%"><?php echo $row["lesson_body"]?></textarea>
</div>
</div>
	 
		     <div class="grid-x grid-padding-x">
	<div class="large-12 medium-12 small-12 cell">
      <p align="center"
      ><input type="submit" value="<?php echo $strModify?>" class="button success"></p>
</form>
</div>
</div>
</form>
<?php
}
Else
{
echo "<a href=\"lector_sitelessons.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"fas fa-plus\"></i></a><br />";
$query="SELECT lesson_ID, lesson_title, lesson_course, Course_ID, course_name FROM elearning_lessons, elearning_courses WHERE elearning_lessons.lesson_trainer=$uid AND elearning_courses.course_ID=elearning_lessons.lesson_course";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
if ($numar==0)
{
echo "<div class=\"callout alert\">$strNoRecordsFound</div>";
}
Else {
?>
<table width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strID?></th>
        	<th><?php echo $strCourse?></th>
			<th><?php echo $strTitle?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[lesson_ID]</td>
			<td>$row[course_name]</td>
			<td>$row[lesson_title]</td>
			<td><a href=\"lector_sitelessons.php?mode=edit&lID=$row[lesson_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"lector_sitelessons.php?mode=delete&lID=$row[lesson_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
		}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"3\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
}
?>
</div>
</div>
<?php
include '../bottom.php';
?>