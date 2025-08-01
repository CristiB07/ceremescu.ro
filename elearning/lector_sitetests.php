<?php
//updated 17.07.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare teste";
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
	      <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM elearning_tests WHERE elearning_tests_ID=" .$_GET['tID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>"; 
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"lector_sitetests.php\"
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

	$mSQL = "INSERT INTO elearning_tests(";
	$mSQL = $mSQL . "test_author,";
	$mSQL = $mSQL . "test_name,";
	$mSQL = $mSQL . "test_description,";
	$mSQL = $mSQL . "test_course,";
	$mSQL = $mSQL . "test_score)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$uid . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["elearning_tests_name"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["elearning_tests_description"]) . "', ";
	$mSQL = $mSQL . "'" .$_POST["elearning_tests_course"] . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["elearning_tests_score"]) . "') ";
				
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
    window.location = \"lector_sitetests.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{// edit
$strWhereClause = " WHERE elearning_tests.test_ID=" . $_GET["tID"] . ";";
$query= "UPDATE elearning_tests SET elearning_tests.test_score='" .str_replace("'","&#39;",$_POST["elearning_tests_score"]) . "' ," ;
$query= $query . "elearning_tests.test_name='" .str_replace("'","&#39;",$_POST["elearning_tests_name"]) . "' ," ;
$query= $query . "elearning_tests.test_course='" .str_replace("'","&#39;",$_POST["elearning_tests_course"]) . "' ," ;
$query= $query . " elearning_tests.test_description='" .str_replace("'","&#39;",$_POST["elearning_tests_description"]) . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordModified</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"lector_sitetests.php\"
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
<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
<form Method="post" Action="lector_sitetests.php?mode=new" >
	  	      <div class="grid-x grid-margin-x">
			  <div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strTitle?></label>
	  <input name="elearning_tests_name" Type="text" size="30" class="required" />
</div>
    <div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strCourse?></label>
	  <select name="elearning_tests_course" class="required">
           <option value="0"><?php echo $strPick?></option>
          <?php $sql = "Select course_name, Course_id, course_author FROM elearning_courses WHERE course_author=$uid ORDER BY course_name ASC";
        $result = ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
		?>
          <option  selected value="<?php echo $rss["Course_id"]?>"><?php echo $rss["course_name"]?></option>
		<?php }?>
        </select>
		</div>
<div class="large-2 medium-2 small-2 cell">
			  <label><?php echo $strScore?></label>
	  <input name="elearning_tests_score" Type="text" size="4" class="required" />
		</div>
		</div>
		<div class="grid-x grid-margin-x">
		  <div class="large-12 medium-12 small-12 cell"> 
	  <label><?php echo $strDetails?></label>
	  <textarea name="elearning_tests_description" style="width:100%;   height: 200;" id="myTextEditor" class="myTextEditor"></textarea>
		</div>
		</div>
		<div class="grid-x grid-margin-x">
		  <div class="large-12 medium-12 small-12 cell">
	  <p align="center">	 
	  <input Type="submit" class="button" Value="<?php echo $strAdd?>" name="Submit"> 	 
	</p>
		</div>
		</div>
		</form>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM elearning_tests WHERE test_ID=$_GET[tID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
<form Method="post"  Action="lector_sitetests.php?mode=edit&tID=<?php echo $row['test_ID']?>" >
<div class="grid-x grid-margin-x">
	  <div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strTitle?></label>
	  <input name="elearning_tests_name" Type="text" size="30" class="required" value="<?php echo $row['test_name'] ?>" />
		</div>
	  <div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strCourse?></label>
	  <TD><select name="elearning_tests_course">
           <option value="0"><?php echo $strPick?></option>
          <?php $sql = "Select Course_id, course_name, course_author FROM elearning_courses WHERE course_author=$uid ORDER BY course_name ASC";
        $result = ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
		if ($row["test_course"]==$rss["Course_id"]){
		?>
          <option selected value="<?php echo $rss["Course_id"]?>"><?php echo $rss["course_name"]?></option>
		<?php } else {?>
          <option  value="<?php echo $rss["Course_id"]?>"><?php echo $rss["course_name"]?></option>
		<?php }}?>
        </select>	
		</div>
		<div class="large-2 medium-2 small-2 cell">
			<label><?php echo $strScore?></label>
	  <input name="elearning_tests_score" Type="text" size="4" class="required" value="<?php echo $row['test_score'] ?>" />
		</div>
		</div>
		<div class="grid-x grid-margin-x">
		  <div class="large-12 medium-12 small-12 cell">
	  <?php echo $strDetails?><br />
	  <textarea name="elearning_tests_description" style="width:100%;" id="myTextEditor" class="myTextEditor"><?php echo $row['test_description'] ?></textarea>
		</div>
		</div>
		<div class="grid-x grid-margin-x">
		  <div class="large-12 medium-12 small-12 cell">
	  <p align="center">
 <input Type="submit" class="button" Value="<?php echo $strModify?>" name="Submit">
 </p>
		</div>
		</div>
		</form>
<?php
}
Else
{
echo "<a href=\"lector_sitetests.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a>";
$query="SELECT test_ID, test_description, test_name, test_score, Course_ID, course_name, course_author
FROM elearning_tests, elearning_courses
WHERE test_course=Course_ID AND test_author=$uid";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
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
			<th><?php echo $strTest?></th>
			<th><?php echo $strCourse?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strEdit?> <?php echo $strQuestions?> </th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[test_ID]</td>
			<td>$row[test_name]</td>
			<td>$row[course_name]</td>
			  <td><a href=\"lector_sitetests.php?mode=edit&tID=$row[test_ID]\"><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			  <td><a href=\"lector_sitetestsquestions.php?tID=$row[test_ID]\"><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"lector_sitetests.php?mode=delete&tID=$row[test_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"5\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
}
?>
</div>
</div>
<?php
include '../bottom.php';
?>