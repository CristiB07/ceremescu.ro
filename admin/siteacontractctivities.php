<?php
// update 29.07.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare activități contracte";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
}
?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM activitati_contracte WHERE ID_Activitate=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteactivities.php\"
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

	$mSQL = "INSERT INTO activitati_contracte(";
	$mSQL = $mSQL . "Activitate_Denumire,";
	$mSQL = $mSQL . "Activitate_Descriere)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Activitate_Denumire"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Activitate_Descriere"]) . "') ";
				
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
    window.location = \"siteactivities.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{// edit
$strWhereClause = " WHERE activitati_contracte.ID_Activitate=" . $_GET["cID"] . ";";
$query= "UPDATE activitati_contracte SET activitati_contracte.Activitate_Denumire='" .str_replace("'","&#39;",$_POST["Activitate_Denumire"]) . "' ," ;
$query= $query . " activitati_contracte.Activitate_Descriere='" .str_replace("'","&#39;",$_POST["Activitate_Descriere"]) . "' "; 
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
    window.location = \"siteactivities.php\"
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
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="siteactivities.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form Method="post" id="users" Action="siteactivities.php?mode=new" >
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <label><?php echo $strTitle?></label></TD>
	  <input name="Activitate_Denumire" Type="text" size="50" class="required" />
	  </div>
	  </div>
	     <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strDetails?></label>
	  <textarea name="Activitate_Descriere" id="myTextEditor" class="myTextEditor" rows="5"></textarea>
	  </div>
	  </div>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell text-center" > <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM activitati_contracte WHERE ID_Activitate=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="siteactivities.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form Method="post" id="users" Action="siteactivities.php?mode=edit&cID=<?php echo $row['ID_Activitate']?>" >
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <label><?php echo $strTitle?></label></TD>
	  <input name="Activitate_Denumire" Type="text" value="<?php echo $row['Activitate_Denumire'] ?>" class="required" />
	</div>
	</div>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strDetails?></label>
	  <textarea name="Activitate_Descriere" id="myTextEditor" class="myTextEditor" rows="5"><?php echo $row['Activitate_Descriere'] ?></textarea>
</div>
</div>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell text-center"> <input Type="submit" Value="<?php echo $strModify?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
<?php
}
Else
{
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"siteactivities.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";
$query="SELECT * FROM activitati_contracte";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> 
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
			<td>$row[ID_Activitate]</td>
			<td>$row[Activitate_Denumire]</td>
			  <td><a href=\"siteactivities.php?mode=edit&cID=$row[ID_Activitate]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"siteactivities.php?mode=delete&cID=$row[ID_Activitate]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"2\"><em></em></td><td>&nbsp;</td></tr></tfoot></table></div></div>";
}
}
}
?>
</div>
</div>
<?php
include '../bottom.php';
?>