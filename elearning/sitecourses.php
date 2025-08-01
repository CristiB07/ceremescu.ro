<?php
//update 9.01.2024
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare categorii";
include '../dashboard/header.php';
$strPageTitle="Administrare cursuri";
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/elearning/login.php?message=MLF");
}
?>
<script src="<?php echo $strSiteURL ?>js/foundation/jquery.js"></script>
<script src='../js/tinymce/tinymce.min.js'></script>
<script>
tinymce.init({
  selector: "textarea.myTextEditor",
  menubar: false,
  image_advtab: false,
   plugins: [
    'advlist autolink lists link imagetools charmap print preview anchor',
    'searchreplace visualblocks code fullscreen preview',
    'insertdatetime media table contextmenu paste code pagebreak'
  ],
  toolbar: 'insertfile undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link preview code pagebreak',
  content_css: [
    '//fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i',
    '//www.tiny.cloud/css/codepen.min.css'],
	 image_title: true, 
  // enable automatic uploads of images represented by blob or data URIs
  paste_data_images: false,
  automatic_uploads: false,
  // URL of our upload handler (for more details check: https://www.tinymce.com/docs/configure/file-image-upload/#images_upload_url)
  images_upload_url: 'postAcceptor.php',
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
		 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM elearning_courses WHERE Course_id=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecourses.php\"
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

	$mSQL = "INSERT INTO elearning_courses(";
	$mSQL = $mSQL . "course_name,";
	$mSQL = $mSQL . "course_author,";
	$mSQL = $mSQL . "course_code,";
	$mSQL = $mSQL . "course_price,";
	$mSQL = $mSQL . "course_discount,";
	$mSQL = $mSQL . "course_description,";
	$mSQL = $mSQL . "course_type,";
	$mSQL = $mSQL . "course_objective,";
	$mSQL = $mSQL . "course_target,";
	$mSQL = $mSQL . "course_picture,";
	$mSQL = $mSQL . "course_keywords,";
	$mSQL = $mSQL . "course_metadescription,";
	$mSQL = $mSQL . "course_category,";
	$mSQL = $mSQL . "course_whatyouget,";
	$mSQL = $mSQL . "course_url)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["course_name"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["course_author"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["course_code"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["course_price"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["course_discount"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["course_description"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["course_type"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["course_objective"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["course_target"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["course_picture"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["course_keywords"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["course_metadescription"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["course_category"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["course_whatyouget"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["course_url"]) ."')";
				
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
    window.location = \"sitecourses.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{// edit
$strWhereClause = " WHERE elearning_courses.Course_id=" . $_GET["cID"] . ";";
$query= "UPDATE elearning_courses SET elearning_courses.course_name='" .str_replace("'","&#39;",$_POST["course_name"]) . "' ," ;
$query= $query . " elearning_courses.course_price='" .str_replace("'","&#39;",$_POST["course_price"]) . "', "; 
$query= $query . " elearning_courses.course_discount='" .str_replace("'","&#39;",$_POST["course_discount"]) . "', "; 
$query= $query . " elearning_courses.course_picture='" .str_replace("'","&#39;",$_POST["course_picture"]) . "', "; 
$query= $query . " elearning_courses.course_type='" .str_replace("'","&#39;",$_POST["course_type"]) . "', "; 
$query= $query . " elearning_courses.course_description='" .str_replace("'","&#39;",$_POST["course_description"]) . "', "; 
$query= $query . " elearning_courses.course_objective='" .str_replace("'","&#39;",$_POST["course_objective"]) . "', "; 
$query= $query . " elearning_courses.course_category='" .str_replace("'","&#39;",$_POST["course_category"]) . "', "; 
$query= $query . " elearning_courses.course_code='" .str_replace("'","&#39;",$_POST["course_code"]) . "', "; 
$query= $query . " elearning_courses.course_author='" .str_replace("'","&#39;",$_POST["course_author"]) . "', "; 
$query= $query . " elearning_courses.course_target='" .str_replace("'","&#39;",$_POST["course_target"]) . "', "; 
$query= $query . " elearning_courses.course_url='" .str_replace("'","&#39;",$_POST["course_url"]) . "', "; 
$query= $query . " elearning_courses.course_whatyouget='" .str_replace("'","&#39;",$_POST["course_whatyouget"]) . "', "; 
$query= $query . " elearning_courses.course_metadescription='" .str_replace("'","&#39;",$_POST["course_metadescription"]) . "', "; 
$query= $query . " elearning_courses.course_keywords='" .str_replace("'","&#39;",$_POST["course_keywords"]) . "' "; 
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
    window.location = \"sitecourses.php\"
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
<script language="JavaScript" type="text/JavaScript">
$(document).ready(function() {
	$("#users").validate();
});
</script>
<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
<form Method="post" id="users" Action="sitecourses.php?mode=new" >
		 <div class="grid-x grid-padding-x">
		<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strTitle?></label>
	  <input name="course_name" Type="text" class="required" />
</div>
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strAuthor?></label></TD>
	  <select size="1"  name="course_author" class="required">
          <option value=""><?php echo $strPick?></option>
          <?php $sql = "Select * FROM elearning_trainers ORDER BY trainer_name ASC";
         $result = ezpub_query($conn,$sql);
	    While ($rs1=ezpub_fetch_array($result)){
			?>
          <option value="<?php echo $rs1["trainer_id"]?>"><?php echo $rs1["trainer_name"]?></option>
                 		  <?php
}?>
        </select>
	</div>
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strType?></label>
	  <input name="course_type" Type="text" class="required"/>
</div>
      <div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strCategory?></label>
     <select size="1"  name="course_category" class="required">
          <option value=""><?php echo $strPick?></option>
          <?php $sql = "Select * FROM elearning_coursecategory ORDER BY elearning_coursecategory_name";
        $result = ezpub_query($conn,$sql);
	    While ($rs1=ezpub_fetch_array($result)){
	?>
          <option value="<?php echo $rs1["elearning_coursecategory_ID"]?>"><?php echo $rs1["elearning_coursecategory_name"]?></option>
          <?php
}?>
        </select></div>
    </div>
			 <div class="grid-x grid-padding-x">
		<div class="large-3 medium-3 small-3 cell">
	 <label><?php echo $strMainPicture?></label>
				<input name="course_picture" id="image" Type="text" class="required" value="" readonly="readonly" /> 
					<!-- Trigger/Open The Modal -->
					<div class="full reveal" id="myModal" data-reveal>
					<!-- Modal content -->
						<iframe src="../common/image.php?directory=cursuri&field=image" frameborder="0" style="border:0" Width="100%" height="750"></iframe>
						  <button class="close-button" data-close aria-label="Close modal" type="button">
    <span aria-hidden="true">&times;</span>
  </button>
						</div>
						<a href="#" class="button" data-open="myModal"><?php echo $strImage?></a> 
						</div>
						<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strCode?></label>
	  <input name="course_code" Type="text" class="required"/>
	</div>
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strPrice?></label>
	  <input name="course_price" Type="text" class="required" />
	  </div>
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strDiscountedPrice?></label></TD>
	  <input name="course_discount" Type="text" class="required" /></TD>
	</div>
	</div>
		 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strCourseDescription?></label><br />
	  <textarea name="course_description" class="myTextEditor" id="myTextEditor"></textarea>
	</div>
	</div>
	 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strObjectives?></label><br /><textarea name="course_objective" class="myTextEditor"  id="myTextEditor"></textarea>
	  </div>
	  </div>
	 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell"> 
	  <label><?php echo $strTarget?></label><br /><textarea name="course_target" class="myTextEditor"  id="myTextEditor"></textarea>
	  </div>
	  </div>
	  	 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strWhatYouGet?></label><br /><textarea name="course_whatyouget" class="myTextEditor" id="myTextEditor"></textarea>
	  </div>
	  </div>
	  <div class="grid-x grid-padding-x">
		<div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strURL?></label></TD>
	  <input name="course_url" Type="text" class="required" />
	  </div>
<div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strKeyWords?></label></TD>
	  <input name="course_keywords" Type="text" class="required" />
	  </div>
	  </div>
	 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strMetaDescription?></label>
	  <textarea name="course_metadescription" id="myTextEditor"></textarea>
</div>
</div>
				 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell text-center">
 <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success">
</div>
</div>
  </FORM>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM elearning_courses WHERE Course_id=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
<div class="grid-x grid-padding-x">
		<div class="large-3 medium-3 small-3 cell">
		<a href="sitecourses.php" class="button"><?php echo $strBack?>&nbsp;<i class="large fas fa-backward"></i></a>
		</div>
		</div>
<form Method="post" id="users" Action="sitecourses.php?mode=edit&cID=<?php echo $row['Course_id']?>" >
			 <div class="grid-x grid-padding-x">
		<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strTitle?></label>
	  <input name="course_name" Type="text" size="30" value="<?php echo $row['course_name'] ?>" class="required" />
</div>
		<div class="large-3 medium-3 small-3 cell">	  
		<label><?php echo $strAuthor?></label>
	   <select size="1"  name="course_author" class="required">
          <option value=""><?php echo $strPick?></option>
          <?php $sql = "Select * FROM elearning_trainers ORDER BY trainer_name ASC";
         $result = ezpub_query($conn,$sql);
	    While ($rs1=ezpub_fetch_array($result)){
		if ($row["course_author"]!=$rs1["trainer_id"]) {
	?>
          <option value="<?php echo $rs1["trainer_id"]?>"><?php echo $rs1["trainer_name"]?></option>
          <?php } Else{?>
		  <option selected value="<?php echo $rs1["trainer_id"]?>"><?php echo $rs1["trainer_name"]?></option>
          		  <?php
}}?>
        </select>
		</div>
		<div class="large-3 medium-3 small-3 cell">	  
		<label><?php echo $strType?></label>
	  <input name="course_type" Type="text" value="<?php echo $row['course_type'] ?>" class="required"/>
</div>
		<div class="large-3 medium-3 small-3 cell">	 

      <label><?php echo $strCategory?></label>
      <select size="1"  name="course_category" class="required">
          <option value=""><?php echo $strPick?></option>
          <?php $sql = "Select * FROM elearning_coursecategory ORDER BY elearning_coursecategory_name";
       $result = ezpub_query($conn,$sql);
	    While ($rs1=ezpub_fetch_array($result)){
		if ($row['course_category']==$rs1['elearning_coursecategory_ID']){	
	?>
          <option selected value="<?php echo $rs1["elearning_coursecategory_ID"]?>"><?php echo $rs1["elearning_coursecategory_name"]?></option>
          <?php
		  } else {
	?>
          <option value="<?php echo $rs1["elearning_coursecategory_ID"]?>"><?php echo $rs1["elearning_coursecategory_name"]?></option>
          <?php
}}?>
        </select>
	</div>
	</div>
			 <div class="grid-x grid-padding-x">
			 <div class="large-3 medium-3 small-3 cell">
	 <label><?php echo $strMainPicture?></label>
				<input name="course_picture" id="image" Type="text" class="required" value="<?php echo $row["course_picture"]?>" readonly="readonly" /> 
					<!-- Trigger/Open The Modal -->
					<div class="full reveal" id="myModal" data-reveal>
					<!-- Modal content -->
						<iframe src="../common/image.php?directory=cursuri&field=image" frameborder="0" style="border:0" Width="100%" height="750"></iframe>
						  <button class="close-button" data-close aria-label="Close modal" type="button">
    <span aria-hidden="true">&times;</span>
  </button>
						</div>
						<a href="#" class="button" data-open="myModal"><?php echo $strImage?></a> 
						</div>
		<div class="large-3 medium-3 small-3 cell">
<label><?php echo $strCode?></label>
	  <input name="course_code" Type="text" value="<?php echo $row['course_code'] ?>" class="required"/>
					</div>
		<div class="large-3 medium-3 small-3 cell">
<label><?php echo $strPrice?></label></TD>
<input name="course_price" Type="text" value="<?php echo $row['course_price'] ?>" class="required" />
</div>
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strDiscountedPrice?></label>
	  <input name="course_discount" Type="text" value="<?php echo $row['course_discount'] ?>" class="required" />
</div>
</div>
		 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strCourseDescription?></label><br />
	  <textarea name="course_description" class="myTextEditor" id="myTextEditor"><?php echo $row['course_description'] ?></textarea>
</div>
</div>
		 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strObjectives?></label><br /><textarea name="course_objective" class="myTextEditor" id="myTextEditor"><?php echo $row['course_objective'] ?></textarea>
</div>
</div>
		 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strTarget?></label><br /><textarea name="course_target" class="myTextEditor" id="myTextEditor"><?php echo $row['course_target'] ?></textarea>
 </div>
</div>
		 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strWhatYouGet?></label><br /><textarea name="course_whatyouget" class="myTextEditor" id="myTextEditor"><?php echo $row['course_whatyouget'] ?></textarea>
</div>
</div>
		 <div class="grid-x grid-padding-x">
		<div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strURL?></label></TD>
	  <input name="course_url" Type="text" value="<?php echo $row['course_url'] ?>" class="required" /></TD>
</div>
	<div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strKeyWords?></label></TD>
	  <input name="course_keywords" Type="text" value="<?php echo $row['course_keywords'] ?>" class="required" /></TD>
</div>
</div>
		 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strMetaDescription?></label><br /><textarea name="course_metadescription" id="myTextEditor"><?php echo $row['course_metadescription'] ?></textarea>
</div>
</div>
		 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell text-center">	 <input Type="submit" Value="<?php echo $strModify?>" name="Submit" class="button success"> 
</div>
</div>
  </FORM>

<?php
}
Else
{
echo "<a href=\"sitecourses.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a>";
$query="SELECT * FROM elearning_courses";
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
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[Course_id]</td>
			<td>$row[course_name]</td>
			  <td><a href=\"sitecourses.php?mode=edit&cID=$row[Course_id]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"sitecourses.php?mode=delete&cID=$row[Course_id]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td </td><td  colspan=\"2\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
}
?>
</div>
</div>
<?php
include '../bottom.php';
?>