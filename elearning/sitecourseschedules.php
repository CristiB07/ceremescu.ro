<?php
//update 05.02.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare programări";
include '../dashboard/header.php';
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/elearning/login.php?message=MLF");
}
?>
	    <div class="grid-x grid-margin-x">
		<div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM elearning_courseschedules WHERE schedule_ID=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"succes callout\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecourseschedules.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();

If ($_GET['mode']=="new"){
//insert new user

	$mSQL = "INSERT INTO elearning_courseschedules(";
	$mSQL = $mSQL . "schedule_course_ID,";
	$mSQL = $mSQL . "schedule_start_date,";
	$mSQL = $mSQL . "schedule_end_date,";
	$mSQL = $mSQL . "schedule_enrolments,";
	$mSQL = $mSQL . "schedule_max_enrolments,";
	$mSQL = $mSQL . "schedule_start_hour,";
	$mSQL = $mSQL . "schedule_exam_date,";
	$mSQL = $mSQL . "schedule_exam_hour,";
	$mSQL = $mSQL . "schedule_details)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$_POST["schedule_course_ID"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["schedule_start_date"]. "', ";
	$mSQL = $mSQL . "'" .$_POST["schedule_end_date"]. "', ";
	$mSQL = $mSQL . "'" .$_POST["schedule_enrolments"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["schedule_max_enrolments"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["schedule_start_hour"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["schedule_exam_date"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["schedule_exam_hour"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["schedule_details"] ."')";
				
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
    window.location = \"sitecourseschedules.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{// edit
$strWhereClause = " WHERE schedule_ID=" . $_GET["cID"] . ";";
$query= "UPDATE elearning_courseschedules SET schedule_course_ID='" .$_POST["schedule_course_ID"] . "', "; 
$query= $query . " schedule_start_date='" . $_POST["schedule_start_date"] . "', "; 
$query= $query . " schedule_end_date='" . $_POST["schedule_end_date"] . "', "; 
$query= $query . " schedule_enrolments='" .$_POST["schedule_enrolments"] . "', "; 
$query= $query . " schedule_max_enrolments='" .$_POST["schedule_max_enrolments"] . "', "; 
$query= $query . " schedule_start_hour='" .$_POST["schedule_start_hour"] . "', "; 
$query= $query . " schedule_exam_date='" . $_POST["schedule_exam_date"]. "', "; 
$query= $query . " schedule_exam_hour='" .$_POST["schedule_exam_hour"] . "', "; 
$query= $query . " schedule_details='" .$_POST["schedule_details"] . "' "; 
$query= $query . $strWhereClause;

if (!ezpub_query($conn,$query))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecourseschedules.php\"
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
<script src='../js/tinymce/tinymce.min.js'></script>
<script>
tinymce.init({
  selector: "textarea.myTextEditor",
  menubar: false,
  image_advtab: true,
   plugins: [
    'advlist autolink lists link image imagetools charmap print preview anchor',
    'searchreplace visualblocks code fullscreen preview',
    'insertdatetime media table contextmenu paste code'
  ],
  toolbar: 'insertfile undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image preview code',
  content_css: [
    '//fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i',
    '//www.tiny.cloud/css/codepen.min.css'],
	 image_title: true, 
  // enable automatic uploads of images represented by blob or data URIs
  paste_data_images: true,
  automatic_uploads: true,
  // URL of our upload handler (for more details check: https://www.tinymce.com/docs/configure/file-image-upload/#images_upload_url)
  images_upload_url: '../common/postAcceptor.php',
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
<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
	echo "<a href=\"sitecourseschedules.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward\"></i></a>";
	?>
<form Method="post" id="users" Action="sitecourseschedules.php?mode=new" >
	 <div class="grid-x grid-padding-x">
<<<<<<< HEAD
	 <div class="large-5 medium-3 small-5 cell">
	 <label><?php echo $strCourse ."<br />&nbsp;" ?></label>
=======
	 <div class="large-4 medium-4 small-4 cell">
	 <label><?php echo $strCourse?></label>
>>>>>>> 0d7c282a4f899deaded420111dbd467d73ea8dc1
	<select size="1"  name="schedule_course_ID" class="required">
          <option value=""><?php echo $strPick?></option>
          <?php $sql = "Select * FROM elearning_courses ORDER BY course_name ASC";
        $result = ezpub_query($conn,$sql);
	    While ($rs1=ezpub_fetch_array($result)){
	?>
          <option value="<?php echo $rs1["Course_id"]?>"><?php echo $rs1["course_name"]?></option>
          <?php
}?>
        </select>
</div>
<<<<<<< HEAD
<div class="large-1 medium-1 small-1 cell">
	  <label><?php echo $strStartDate."<br />&nbsp;"?></label>
	  <input name="schedule_start_date" Type="date" class="required" />
</div>
<div class="large-1 medium-1 small-1 cell">
	  <label><?php echo $strEndDate."<br />&nbsp;"?></label>
	  <input name="schedule_end_date" Type="date" class="required" />
</div>
<div class="large-1 medium-1 small-1 cell">
	  <label><?php echo $strEnrolments?></label></TD>
	  <input name="schedule_enrolments" Type="text" class="required" />
</div>
<div class="large-1 medium-1 small-1 cell">
	  <label><?php echo $strMaxEnrolments?></label></TD>
	  <input name="schedule_max_enrolments" Type="text" class="required" />
</div>
<div class="large-1 medium-1 small-1 cell">
	  <label><?php echo $strStartHour."<br />&nbsp;"?></label>
	  <input name="schedule_start_hour" Type="text" class="required" />
</div>
<div class="large-1 medium-1 small-1 cell">
	  <label><?php echo $strExamDate?></label>
	  <input name="schedule_exam_date" Type="date" class="required" />
</div>
<div class="large-1 medium-1 small-1 cell">
=======
<div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strStartDate?></label>
	  <input name="schedule_start_date" Type="date" class="required" />
</div>
<div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strEndDate?></label>
	  <input name="schedule_end_date" Type="date" class="required" />
</div>
<div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strEnrolments?></label></TD>
	  <input name="schedule_enrolments" Type="text" class="required" />
</div>
<div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strMaxEnrolments?></label></TD>
	  <input name="schedule_max_enrolments" Type="text" class="required" />
</div>
</div>
<div class="grid-x grid-padding-x">
<div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strStartHour?></label>
	  <input name="schedule_start_hour" Type="text" class="required" />
</div>
<div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strExamDate?></label>
	  <input name="schedule_exam_date" Type="date" class="required" />
</div>
<div class="large-4 medium-4 small-4 cell">
>>>>>>> 0d7c282a4f899deaded420111dbd467d73ea8dc1
	  <label><?php echo $strExamHour?></label></TD>
	  <input name="schedule_exam_hour" Type="text" class="required" />
</div>
</div>
<div class="grid-x grid-padding-x">
<div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strDetails?></label>
	  <textarea name="schedule_details" class="myTextEditor" style="width:100%;"></textarea>
</div>
</div>
<div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell text-center">
	  <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success"> 
		 </div>
		 </div>
</form>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
	echo "<a href=\"sitecourseschedules.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward\"></i></a>";
$query="SELECT * FROM elearning_courseschedules WHERE schedule_ID=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
<form Method="post" id="users" Action="sitecourseschedules.php?mode=edit&cID=<?php echo $row['schedule_ID']?>" >
	 <div class="grid-x grid-padding-x">
<<<<<<< HEAD
	 <div class="large-5 medium-5 small-5 cell">
	 <label><?php echo $strCourse."<br />&nbsp;"?></label>
=======
	 <div class="large-4 medium-4 small-4 cell">
	 <label><?php echo $strCourse?></label>
>>>>>>> 0d7c282a4f899deaded420111dbd467d73ea8dc1
	<select size="1"  name="schedule_course_ID" class="required">
          <option value=""><?php echo $strPick?></option>
          <?php $sql = "Select * FROM elearning_courses ORDER BY course_name ASC";
        $result = ezpub_query($conn,$sql);
	    While ($rs1=ezpub_fetch_array($result)){
	?>
          <option value="<?php echo $rs1["Course_id"]?>" <?php if ($rs1["Course_id"]==$row["schedule_course_ID"]) echo "selected"?>><?php echo $rs1["course_name"]?></option>
          <?php
}?>
        </select>
</div>
<<<<<<< HEAD
<div class="large-1 medium-1 small-1 cell">
	  <label><?php echo $strStartDate."<br />&nbsp;"?></label>
	  <input name="schedule_start_date" Type="date" value="<?php echo $row["schedule_start_date"]?>" class="required" />
</div>
<div class="large-1 medium-1 small-1 cell">
	  <label><?php echo $strEndDate."<br />&nbsp;"?></label>
	  <input name="schedule_end_date" Type="date" value="<?php echo $row["schedule_end_date"]?>" class="required" />
</div>
<div class="large-1 medium-1 small-1 cell">
	  <label><?php echo $strEnrolments?></label>
	  <input name="schedule_enrolments" Type="text" value="<?php echo $row["schedule_enrolments"]?>" class="required" />
</div>
<div class="large-1 medium-1 small-1 cell">
	  <label><?php echo $strMaxEnrolments?></label></TD>
	  <input name="schedule_max_enrolments" Type="text" value="<?php echo $row["schedule_max_enrolments"]?>" class="required" />
</div>
<div class="large-1 medium-1 small-1 cell">
	  <label><?php echo $strStartHour."<br />&nbsp;"?></label>
	  <input name="schedule_start_hour" Type="text" value="<?php echo $row["schedule_start_hour"]?>" class="required" />
</div>
<div class="large-1 medium-1 small-1 cell">
	  <label><?php echo $strExamDate?></label>
	  <input name="schedule_exam_date" Type="date" value="<?php echo $row["schedule_exam_date"]?>" class="required" />
</div>
<div class="large-1 medium-1 small-1 cell">
=======
<div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strStartDate?></label>
	  <input name="schedule_start_date" Type="date" value="<?php echo $row["schedule_start_date"]?>" class="required" />
</div>
<div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strEndDate?></label>
	  <input name="schedule_end_date" Type="date" value="<?php echo $row["schedule_end_date"]?>" class="required" />
</div>
<div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strEnrolments?></label>
	  <input name="schedule_enrolments" Type="text" value="<?php echo $row["schedule_enrolments"]?>" class="required" />
</div>
<div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strMaxEnrolments?></label></TD>
	  <input name="schedule_max_enrolments" Type="text" value="<?php echo $row["schedule_max_enrolments"]?>" class="required" />
</div>
</div>
<div class="grid-x grid-padding-x">
<div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strStartHour?></label>
	  <input name="schedule_start_hour" Type="text" value="<?php echo $row["schedule_start_hour"]?>" class="required" />
</div>
<div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strExamDate?></label>
	  <input name="schedule_exam_date" Type="date" value="<?php echo $row["schedule_exam_date"]?>" class="required" />
</div>
<div class="large-4 medium-4 small-4 cell">
>>>>>>> 0d7c282a4f899deaded420111dbd467d73ea8dc1
	  <label><?php echo $strExamHour?></label></TD>
	  <input name="schedule_exam_hour" Type="text" value="<?php echo $row["schedule_exam_hour"]?>" class="required" />
</div>
</div>
<div class="grid-x grid-padding-x">
<div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strDetails?></label>
	  <textarea name="schedule_details" class="myTextEditor" style="width:100%;"><?php echo $row["schedule_details"]?></textarea>
</div>
</div>
<div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell text-center">
	  <input Type="submit" Value="<?php echo $strModify?>" name="Submit" class="button success"> 
		 </div>
		 </div> 
		 </form>
<?php
}
Else
{
echo "<a href=\"sitecourseschedules.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a><br /><br />";
 $query="SELECT course_name, course_author, schedule_ID, schedule_start_date, schedule_end_date FROM elearning_courseschedules, elearning_courses 
 WHERE schedule_course_ID=elearning_courses.Course_ID AND schedule_end_date >= '$sdata'";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
if ($numar==0)
{
echo $strNoRecordsFound;
}
Else {
?>
<table width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strID?></th>
			<th><?php echo $strTitle?></th>
			<th><?php echo $strStartDate?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[schedule_ID]</td>
			<td>$row[course_name]</td>
			<td>$row[schedule_start_date]</td>
			  <td><a href=\"sitecourseschedules.php?mode=edit&cID=$row[schedule_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"sitecourseschedules.php?mode=delete&cID=$row[schedule_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
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