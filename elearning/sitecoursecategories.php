<?php
//update 9.01.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare categorii";
include '../dashboard/header.php';
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

$nsql="DELETE FROM elearning_coursecategory WHERE elearning_coursecategory_ID=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecoursecategories.php\"
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

	$mSQL = "INSERT INTO elearning_coursecategory(";
	$mSQL = $mSQL . "elearning_coursecategory_name,";
	$mSQL = $mSQL . "elearning_coursecategory_picture,";
	$mSQL = $mSQL . "elearning_coursecategory_description)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["elearning_coursecategory_name"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["elearning_coursecategory_picture"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["elearning_coursecategory_description"]) . "') ";
				
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
    window.location = \"sitecoursecategories.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{// edit
$strWhereClause = " WHERE elearning_coursecategory.elearning_coursecategory_ID=" . $_GET["cID"] . ";";
$query= "UPDATE elearning_coursecategory SET elearning_coursecategory.elearning_coursecategory_name='" .str_replace("'","&#39;",$_POST["elearning_coursecategory_name"]) . "' ," ;
$query= $query . "elearning_coursecategory.elearning_coursecategory_picture='" .str_replace("'","&#39;",$_POST["elearning_coursecategory_picture"]) . "' ," ;
$query= $query . " elearning_coursecategory.elearning_coursecategory_description='" .str_replace("'","&#39;",$_POST["elearning_coursecategory_description"]) . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecoursecategories.php\"
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
<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
<form Method="post" id="users" Action="sitecoursecategories.php?mode=new" >
<table id="rounded-corner" summary="<?php echo $strCategory?>" width="100%">
		 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strTitle?></label>
	  <input name="elearning_coursecategory_name" Type="text"  class="required" />
	</div>
	</div>
	 		 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell">
	 <label> <?php echo $strTrainerPresentationLong?></label>
	  <textarea name="elearning_coursecategory_description" id="myTextEditor" class="myTextEditor"></textarea>
		</div>
		</div>
	
		 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell">
                <label><?php echo $strMainPicture?></label>
				<input name="elearning_coursecategory_picture" id="image" Type="text" class="required" value="" readonly="readonly" /> 
					<!-- Trigger/Open The Modal -->
					<div class="full reveal" id="myModal" data-reveal>
					<!-- Modal content -->
						<iframe src="../common/image.php?directory=categorii&field=image" frameborder="0" style="border:0" Width="100%" height="750"></iframe>
						  <button class="close-button" data-close aria-label="Close modal" type="button">
    <span aria-hidden="true">&times;</span>
  </button>
						</div>
						<a href="#" class="button" data-open="myModal"><?php echo $strImage?></a>
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
$query="SELECT * FROM elearning_coursecategory WHERE elearning_coursecategory_ID=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
<form Method="post" id="users" Action="sitecoursecategories.php?mode=edit&cID=<?php echo $row['elearning_coursecategory_ID']?>" >
	  <div class="grid-x grid-padding-x">
	  <div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strTitle?></label>
	  <input name="elearning_coursecategory_name" Type="text" value="<?php echo $row['elearning_coursecategory_name'] ?>" class="required" />
	</div>
	</div>
	 	  <div class="grid-x grid-padding-x">
	  <div class="large-12 medium-12 small-12 cell">
	 <label> <?php echo $strTrainerPresentationLong?></label>
	  <textarea name="elearning_coursecategory_description" style="width:100%;" id="myTextEditor" class="myTextEditor"><?php echo $row['elearning_coursecategory_description'] ?></textarea>
		</div>
		</div>
		 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell">
                <label><?php echo $strMainPicture?></label>
				<input name="elearning_coursecategory_picture" id="image" Type="text" class="required" value="<?php echo $row['elearning_coursecategory_picture'] ?>" readonly="readonly" /> 
					<!-- Trigger/Open The Modal -->
					<div class="full reveal" id="myModal" data-reveal>
					<!-- Modal content -->
						<iframe src="../common/image.php?directory=categorii&field=image" frameborder="0" style="border:0" Width="100%" height="750"></iframe>
						  <button class="close-button" data-close aria-label="Close modal" type="button">
    <span aria-hidden="true">&times;</span>
  </button>
						</div>
						<a href="#" class="button" data-open="myModal"><?php echo $strImage?></a>
		</div>
		</div>
	 		 <div class="grid-x grid-padding-x">
		<div class="large-12 medium-12 small-12 cell text-center">
	  <input Type="submit" Value="<?php echo $strModify?>" name="Submit" class="button success"> 
		 </div>
		 </div>
	
  </FORM>
<?php
}
Else
{
echo "<a href=\"sitecoursecategories.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a>";
$query="SELECT * FROM elearning_coursecategory";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo $strNoRecordsFound;
}
Else {
?>
<table width="100%">
	      <thead>
        	<th><?php echo $strID?></th>
			<th><?php echo $strTitle?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
        
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[elearning_coursecategory_ID]
			<td>$row[elearning_coursecategory_name]
			  <td><a href=\"sitecoursecategories.php?mode=edit&cID=$row[elearning_coursecategory_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a>
			<td><a href=\"sitecoursecategories.php?mode=delete&cID=$row[elearning_coursecategory_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a>
        </tr>";
}
echo "</tbody><tfoot><td></td><td  colspan=\"2\"><em></em><td>&nbsp;</td></tfoot></table>";
}
}
}
?>
</div>
</div>
<?php
include '../bottom.php';
?>