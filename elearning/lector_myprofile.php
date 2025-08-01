<?php
//updated 27.03.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare profil";
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


if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
$strWhereClause = " WHERE elearning_trainers.trainer_id=" . $uid . ";";
$query= "UPDATE elearning_trainers SET elearning_trainers.trainer_name='" .str_replace("'","&#39;",$_POST["trainer_name"]) . "' ," ;
$query= $query . " elearning_trainers.trainer_picture='" .$_POST["trainer_picture"] . "', "; 
$query= $query . " elearning_trainers.trainer_password='" .str_replace("'","&#39;",$_POST["trainer_password"]) . "', "; 
$query= $query . " elearning_trainers.trainer_email='" .str_replace("'","&#39;",$_POST["trainer_email"]) . "', "; 
$query= $query . " elearning_trainers.trainer_phone='" .str_replace("'","&#39;",$_POST["trainer_phone"]) . "', "; 
$query= $query . " elearning_trainers.trainer_presentation_short='" .str_replace("'","&#39;",$_POST["trainer_presentation_short"]) . "' ";
$query= $query . " elearning_trainers.trainer_metadescription='" .str_replace("'","&#39;",$_POST["trainer_metadescription"]) . "', "; 
$query= $query . " elearning_trainers.trainer_presentation_short='" .str_replace("'","&#39;",$_POST["trainer_presentation_short"]) . "', "; 
$query= $query . " elearning_trainers.trainer_url='" .str_replace("'","&#39;",$_POST["trainer_url"]) . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"myprofile.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include 'bottom.php';
die;
}
}
Else {
?>
<script src='../js/tinymce/tinymce.min.js'></script>
<script language="JavaScript" type="text/JavaScript">
$(document).ready(function() {
	$("#users").validate();
});
</script>
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
  images_upload_url: '../postAcceptor.php?fldr=traineri',
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

echo "<a href=\"lector_myprofile.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward\"></i></a>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM elearning_trainers WHERE trainer_id=$uid";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
	
<form Method="post" Action="myprofile.php?mode=edit&tID=<?php echo $uID?>" >
<div class="grid-x grid-margin-x">
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strTitle?></label>
	  <input name="trainer_name" Type="text" size="30" value="<?php echo $row['trainer_name'] ?>" class="required" />
</div>
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strPassword?></label>
	  <input name="trainer_password" Type="password" value="<?php echo $row['trainer_password'] ?>" class="required" />
</div>
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strEmail?></label>
	  <input name="trainer_email" Type="text" value="<?php echo $row['trainer_email'] ?>" class="required" />
</div>
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strPhone?></label>
	  <input name="trainer_phone" Type="text" value="<?php echo $row['trainer_phone'] ?>" class="required" />
</div>

<div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strTitle?></label>
	  <input name="trainer_name" Type="text" size="30" value="<?php echo $row['trainer_name'] ?>" class="required" />
</div>
</div>
<div class="grid-x grid-margin-x">
<div class="large-12 medium-12 small-12 cell">
	<label><?php echo $strTrainerPresentationShort?></label><br />
	  <textarea name="trainer_presentation_short" class="myTextEditor"><?php echo $row['trainer_presentation_short'] ?></textarea>
</div>
</div>
<div class="grid-x grid-margin-x">
<div class="large-2 medium-2 small-2 cell"> 
	  <label><?php echo $strURL?></label>
	  <input name="trainer_url" Type="text" value="<?php echo $row['trainer_url'] ?>" class="required" />
</div>
<div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strKeyWords?></label>
	  <input name="trainer_keywords" Type="text" value="<?php echo $row['trainer_keywords'] ?>" class="required" />
</div>
<div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strPassword?></label>
	  <input name="trainer_password" Type="password" value="<?php echo $row['trainer_password'] ?>" class="required" />
</div>
<div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strEmail?></label>
	  <input name="trainer_email" Type="text" value="<?php echo $row['trainer_email'] ?>" class="required" />
</div>
<div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strPhone?></label>
	  <input name="trainer_phone" Type="text" value="<?php echo $row['trainer_phone'] ?>" class="required" />
</div>
</div>
<div class="grid-x grid-margin-x">
<div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strMetaDescription?></label>
	  <textarea name="trainer_metadescription" ><?php echo $row['trainer_metadescription'] ?></textarea>
</div>
</div>
<div class="grid-x grid-margin-x">
<div class="large-12 medium-12 small-12 cell">
<label><?php echo $strMainPicture?></label>
				<input name="trainer_picture" id="image" Type="text" class="required" value="" readonly="readonly" /> 
					<!-- Trigger/Open The Modal -->
					<div class="full reveal" id="myModal" data-reveal>
					<!-- Modal content -->
						<iframe src="../common/image.php?directory=traineri&field=image" frameborder="0" style="border:0" Width="100%" height="750"></iframe>
						  <button class="close-button" data-close aria-label="Close modal" type="button">
    <span aria-hidden="true">&times;</span>
  </button>
						</div>
						<a href="#" class="button" data-open="myModal"><?php echo $strImage?></a> 
						</div>
						</div>
						<div class="grid-x grid-margin-x">
						<div class="large-12 medium-12 small-12 cell">			
	  < <input Type="submit" class="button" Value="<?php echo $strModify?>" name="Submit"> 
</div>
</div>
</form>
<?php
}
Else
{
$query="SELECT * FROM elearning_trainers where trainer_id=$uid";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
if ($numar==0)
{
echo $strNoRecordsFound;
}
Else {
?>
<a href="lector_myprofile.php?mode=edit&tID=<?php echo $uid?>" class="button"><i class="far fa-edit fa-xl" title="<?php $strEdit?>"></i></a><br />
<table width="100%">
	      <thead>
    	<tr>
			<th><h4><?php echo $strMyProfile?></h4></th>
        	<th>&nbsp;</th>
			<th><h4><?php echo $strMyProfile?></h4></th>
			<th>&nbsp;</th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"
			<tr><td>$strName</td><td colspan=\"2\">$row[trainer_name]</td></tr>
			<tr><td colspan=\"3\"><img src=\"$strSiteURL"."images/traineri/$row[trainer_picture]\" width=\"auto\" title=\"$row[trainer_name]\" alt=\"$row[trainer_name]\"/></td></tr>
			<tr><td>$strTrainerPresentationShort</td><td colspan=\"2\">$row[trainer_presentation_short]</td></tr>
			<tr><td>$strTrainerPresentationLong</td><td colspan=\"2\">$row[trainer_presentation_long]</td></tr>
			<tr><td>$strEmail</td><td colspan=\"2\">$row[trainer_email]</td></tr>
			<tr><td>$strPhone</td><td colspan=\"2\">$row[trainer_phone]</td></tr>
			";
}

echo "</tbody><tfoot><tr><td></td></tr></tfoot></table>";
}
}
}
?>
</div>
</div>
<?php
include '../bottom.php';
?>