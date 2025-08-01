<?php
//update 05.02.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare traineri";
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/elearning/login.php?message=MLF");
}
include '../dashboard/header.php';
?>
      <div class="grid-x grid-padding-x">
	  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM elearning_trainers WHERE trainer_id=" .$_GET['tID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitetrainers.php\"
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
$myString = $_POST["trainer_name"];
$myarray = explode(',', $myString);
$trainer_name=$myarray[1];
$trainer_utilizator_ID=$myarray[0];

	$mSQL = "INSERT INTO elearning_trainers(";
	$mSQL = $mSQL . "trainer_name,";
	$mSQL = $mSQL . "trainer_utilizator_ID,";
	$mSQL = $mSQL . "trainer_presentation_short,";
	$mSQL = $mSQL . "trainer_picture,";
	$mSQL = $mSQL . "trainer_keywords,";
	$mSQL = $mSQL . "trainer_email,";
	$mSQL = $mSQL . "trainer_password,";
	$mSQL = $mSQL . "trainer_phone,";
	$mSQL = $mSQL . "trainer_metadescription,";
	$mSQL = $mSQL . "trainer_url)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$trainer_name) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$trainer_utilizator_ID) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["trainer_presentation_short"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["trainer_picture"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["trainer_keywords"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["trainer_email"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["trainer_password"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["trainer_phone"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["trainer_metadescription"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["trainer_url"]) ."')";
				
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
    window.location = \"sitetrainers.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{// edit
	$myString = $_POST["trainer_name"];
$myarray = explode(',', $myString);
$trainer_name=$myarray[1];
$trainer_utilizator_ID=$myarray[0];

$strWhereClause = " WHERE elearning_trainers.trainer_id=" . $_GET["tID"] . ";";
$query= "UPDATE elearning_trainers SET elearning_trainers.trainer_name='" .str_replace("'","&#39;",$trainer_name) . "' ," ;
$query= $query . " elearning_trainers.trainer_picture='". $trainer_utilizator_ID. "', "; 
$query= $query . " elearning_trainers.trainer_picture='" .str_replace("'","&#39;",$_POST["trainer_picture"]) . "', "; 
$query= $query . " elearning_trainers.trainer_keywords='" .str_replace("'","&#39;",$_POST["trainer_keywords"]) . "', "; 
$query= $query . " elearning_trainers.trainer_password='" .str_replace("'","&#39;",$_POST["trainer_password"]) . "', "; 
$query= $query . " elearning_trainers.trainer_email='" .str_replace("'","&#39;",$_POST["trainer_email"]) . "', "; 
$query= $query . " elearning_trainers.trainer_phone='" .str_replace("'","&#39;",$_POST["trainer_phone"]) . "', "; 
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
echo "<div class=\"callout success\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitetrainers.php\"
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
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
	echo "<a href=\"sitetrainers.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward\"></i></a>";
?>

<form Method="post" id="users" Action="sitetrainers.php?mode=new" >
<div class="grid-x grid-padding-x">
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strName?></label>
	  <select name="trainer_name" required>
						<option value="0"><?php echo $strPick?></option>
						<?php $sql = "Select utilizator_ID, utilizator_Prenume, utilizator_Nume from date_utilizatori WHERE utilizator_Function='TRAINER' ORDER BY utilizator_Nume ASC";
							$result = ezpub_query($conn,$sql);
							while ($rss=ezpub_fetch_array($result)){
							?>
						<option  value="<?php echo $rss["utilizator_ID"].",".$rss["utilizator_Prenume"]." ".$rss["utilizator_Nume"]?>"><?php echo $rss["utilizator_Prenume"]." ".$rss["utilizator_Nume"]?></option>
							<?php } ?>
		</select>
</div>
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strURL?></label></TD>
	  <input name="trainer_url" Type="text" class="required" />
</div>
<div class="large-3 medium-3 small-3 cell">
<label><?php echo $strKeyWords?></label></TD>
	  <input name="trainer_keywords" Type="text" class="required" />
</div>
<div class="large-3 medium-3 small-3 cell">
<label><?php echo $strPicture?></label></TD>
	  <input name="trainer_picture" id="image" Type="text" required readonly="readonly" />
					<!-- Trigger/Open The Modal -->
									<div class="full reveal" id="myModal" data-reveal>
					<!-- Modal content -->
					<iframe src="<?php echo $strSiteURL?>/common/image.php?directory=traineri&field=image" frameborder="0" style="border:0" Width="100%" height="750"></iframe>
										  <button class="close-button" data-close aria-label="Close reveal" type="button">
    <span aria-hidden="true">&times;</span>
  </button>
						</div>
						<a data-open="myModal" class="button"><?php echo $strImage?></a>
</div>
</div>
<div class="grid-x grid-padding-x">
<div class="large-12 medium-12 small-12 cell">
<label><?php echo $strMetaDescription?></label>
<textarea name="trainer_metadescription" style="width:100%;"></textarea>
</div>
</div>
<div class="grid-x grid-padding-x">
<div class="large-12 medium-12 small-12 cell">
	<label><?php echo $strTrainerPresentationShort?></label><br />
	  <textarea name="trainer_presentation_short" class="myTextEditor" style="width:100%;"></textarea>
</div>
</div>
<div class="grid-x grid-padding-x">
              <div class="large-12 cell"><p align="center"><br /><input type="submit" Value="<?php echo $strAdd?>" name="Submit" class="submit button"> </p> </div>
			  </div>
</form>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
	echo "<a href=\"sitetrainers.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward\"></i></a>";
$query="SELECT * FROM elearning_trainers WHERE trainer_id=$_GET[tID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
<form Method="post" id="users" Action="sitetrainers.php?mode=edit&tID=<?php echo $row['trainer_id']?>" >

<div class="grid-x grid-padding-x">
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strName?></label>
	  <select name="trainer_name" required>
						<option value="0"><?php echo $strPick?></option>
						<?php $sql = "Select utilizator_ID, utilizator_Prenume, utilizator_Nume from date_utilizatori WHERE utilizator_Role='TRAINER' ORDER BY utilizator_Nume ASC";
							$result = ezpub_query($conn,$sql);
							while ($rss=ezpub_fetch_array($result)){
							?>
						<option  value="<?php echo $rss["utilizator_ID"].",".$rss["utilizator_Prenume"]." ".$rss["utilizator_Nume"]?>" <?php If ($rss["utilizator_ID"]==$row["trainer_utilizator_ID"]) echo "selected"?>><?php echo $rss["utilizator_Prenume"]." ".$rss["utilizator_Nume"]?></option>
							<?php } ?>
	</select>
</div>
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strURL?></label></TD>
	  <input name="trainer_url" Type="text" value="<?php echo $row["trainer_url"]?>" class="required" />
</div>
<div class="large-3 medium-3 small-3 cell">
<label><?php echo $strKeyWords?></label></TD>
	  <input name="trainer_keywords" Type="text" value="<?php echo $row["trainer_keywords"]?>" class="required" />
</div>
<div class="large-3 medium-3 small-3 cell">
<label><?php echo $strPicture?></label></TD>
	  <input name="trainer_picture" id="image" Type="text" required readonly="readonly" value="<?php echo $row["trainer_picture"]?>"/>
					<!-- Trigger/Open The Modal -->
									<div class="full reveal" id="myModal" data-reveal>
					<!-- Modal content -->
					<iframe src="<?php echo $strSiteURL?>/common/image.php?directory=traineri&field=image" frameborder="0" style="border:0" Width="100%" height="750"></iframe>
										  <button class="close-button" data-close aria-label="Close reveal" type="button">
    <span aria-hidden="true">&times;</span>
  </button>
						</div>
						<a data-open="myModal" class="button"><?php echo $strImage?></a>
</div>
</div>
<div class="grid-x grid-padding-x">
<div class="large-12 medium-12 small-12 cell">
<label><?php echo $strMetaDescription?></label>
<textarea name="trainer_metadescription" style="width:100%;"><?php echo $row["trainer_metadescription"]?></textarea>
</div>
</div>
<div class="grid-x grid-padding-x">
<div class="large-12 medium-12 small-12 cell">
	<label><?php echo $strTrainerPresentationShort?></label><br />
	  <textarea name="trainer_presentation_short" class="myTextEditor" style="width:100%;"><?php echo $row["trainer_presentation_short"]?></textarea>
</div>
</div>
<div class="grid-x grid-padding-x">
              <div class="large-12 cell"><p align="center"><br /><input type="submit" Value="<?php echo $strModify?>" name="Submit" class="submit button"> </p> </div>
			  </div>
</form>
<?php
}
Else
{
echo "<a href=\"sitetrainers.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a>";
$query="SELECT * FROM elearning_trainers";
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
			<td>$row[trainer_id]</td>
			<td>$row[trainer_name]</td>
			  <td><a href=\"sitetrainers.php?mode=edit&tID=$row[trainer_id]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"sitetrainers.php?mode=delete&tID=$row[trainer_id]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"2\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
}
?>
</div>
</div>
</div>
<?php
include '../bottom.php';
?>