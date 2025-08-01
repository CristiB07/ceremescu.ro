<?php
//update 8.01.2025

include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strKeywords=" ";
$strDescription="Administrează produsele.";
$strPageTitle="Administrează produsele";
$url="siteproducts.php";
include '../dashboard/header.php';
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
  images_upload_url: '../postAcceptor.php',
    images_upload_base_path: '..',
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
echo "      <div class=\"grid-x grid-padding-x\">
        <div class=\"large-12 cell\">
<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM magazin_produse WHERE produs_id=" .$_GET['pID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteproducts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){
//insert new product

	$mSQL = "INSERT INTO magazin_produse(";
	$mSQL = $mSQL . "produs_nume,";
	$mSQL = $mSQL . "produs_pret,";
	$mSQL = $mSQL . "produs_imagine,";
	$mSQL = $mSQL . "produs_categorie,";
	$mSQL = $mSQL . "produs_fcategorie,";
	$mSQL = $mSQL . "produs_descriere,";
	$mSQL = $mSQL . "produs_url,";
	$mSQL = $mSQL . "produs_keywords,";
	$mSQL = $mSQL . "produs_meta,";
	$mSQL = $mSQL . "produs_thumb,";
	$mSQL = $mSQL . "produs_limba,";
	$mSQL = $mSQL . "produs_tva,";
	$mSQL = $mSQL . "produs_dpret)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$_POST["produs_nume"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["produs_pret"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["produs_imagine"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["produs_categorie"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["produs_fcategorie"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["produs_descriere"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["produs_url"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["produs_keywords"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["produs_meta"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["produs_thumb"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["produs_limba"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["produs_tva"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["produs_dpret"] ."')";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
	  echo "<div class=\"callout alert\">$strThereWasAnError</ br>Error: " . ezpub_error() . "</div></div></div><hr/>";
 include '../bottom.php';
die;
  }
Else{
echo "<div class=\"callout success\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteproducts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\"></div></div>";
include '../bottom.php';
die;
}}
Else
{// edit
$strWhereClause = " WHERE magazin_produse.produs_id=" . $_GET["pID"] . ";";
$query= "UPDATE magazin_produse SET magazin_produse.produs_nume='" . $_POST["produs_nume"] . "' ," ;
$query= $query . " magazin_produse.produs_pret='" . $_POST["produs_pret"] . "', "; 
$query= $query . " magazin_produse.produs_imagine='" . $_POST["produs_imagine"] . "', "; 
$query= $query . " magazin_produse.produs_categorie='" . $_POST["produs_categorie"] . "', "; 
$query= $query . " magazin_produse.produs_fcategorie='" . $_POST["produs_fcategorie"] . "', "; 
$query= $query . " magazin_produse.produs_descriere='" . $_POST["produs_descriere"] . "', "; 
$query= $query . " magazin_produse.produs_keywords='" . $_POST["produs_keywords"] . "', "; 
$query= $query . " magazin_produse.produs_meta='" . $_POST["produs_meta"] . "', "; 
$query= $query . " magazin_produse.produs_thumb='" . $_POST["produs_thumb"] . "', "; 
$query= $query . " magazin_produse.produs_limba='" . $_POST["produs_limba"] . "', "; 
$query= $query . " magazin_produse.produs_tva='" . $_POST["produs_tva"] . "', "; 
$query= $query . " magazin_produse.produs_url='" . $_POST["produs_url"] . "', "; 
$query= $query . " magazin_produse.produs_dpret='" . $_POST["produs_dpret"] . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
	  echo "<div class=\"callout alert\">$strThereWasAnError</ br>Error: " . ezpub_error() . "</div></div></div><hr/>";
 include '../bottom.php';
die;
  }
Else{
echo "<div class=\"callout success\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteproducts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\"></div></div><hr />";
include '../bottom.php';
die;
}
}
}
Else {
?>
<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
	echo "<a href=\"siteproducts.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward\"></i></a>";
?>
<form Method="post" Action="siteproducts.php?mode=new" >
   <div class="grid-x grid-padding-x">
        <div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strTitle?></label></label>
				<input name="produs_nume" Type="text" size="30" placeholder="<?php echo $strTitle?>" required />
		</div>
			<div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strMainPicture?></label>
				<input name="produs_imagine" id="image" Type="text" required readonly="readonly" />
					<!-- Trigger/Open The Modal -->
					<a data-open="myModal-1" class="button"><?php echo $strImage?></a>
						<div class="large reveal" id="myModal-1" data-reveal>
					<!-- Modal content -->
					  <button class="close-button" data-close aria-label="Close reveal" type="button">
    <span aria-hidden="true">&times;</span>
  </button>
					<iframe src="<?php echo $strSiteURL?>common/image.php?directory=products&field=image" frameborder="0" style="border:0" Width="100%" height="750"></iframe>
						</div>
		</div>
		<div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strProductThumb?></label>
				<input name="produs_thumb" id="thumb" Type="text" required readonly="readonly" /> 
		<!-- Trigger/Open The Modal -->
					<a data-open="myModal-2" class="button"><?php echo $strImage?></a>
						<div class="large reveal" id="myModal-2" data-reveal>
					<!-- Modal content -->
					  <button class="close-button" data-close aria-label="Close reveal" type="button">
    <span aria-hidden="true">&times;</span>
  </button>
					<iframe src="<?php echo $strSiteURL?>common/image.php?directory=products&field=thumb" frameborder="0" style="border:0" Width="100%" height="750"></iframe>
						</div>
		</div>
		</div>
   <div class="grid-x grid-padding-x">
		<div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strCategory?></label>
				<input name="produs_categorie" Type="text" placeholder="<?php echo $strCategory?>" required />
	</div>		
	<div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strFriendlyCategory?></label>
				<input name="produs_fcategorie" Type="text" placeholder="<?php echo $strCategory?>" required />
	</div>
		<div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strLanguage?></label>
				<input name="produs_limba" Type="text" placeholder="<?php echo $strLanguage?>" required />
	</div>
		<div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strPrice?></label>
				<input name="produs_pret" Type="text" placeholder="<?php echo $strPrice?>" number required />
	</div>
		<div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strDiscountedPrice?></label>
				<input name="produs_dpret" Type="text" placeholder="<?php echo $strDiscountedPrice?>" number required value="0.0000" />
	</div>
	</div>
   <div class="grid-x grid-padding-x">
		<div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strURL?></label>
				<input name="produs_url" Type="text"  placeholder="<?php echo $strURL?>" required />
	</div>		
		<div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strKeyWords?></label>
				<input name="produs_keywords" Type="text" placeholder="<?php echo $strKeyWords?>" required />
	</div>		
	<div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strVAT?></label>
				<input name="produs_tva" Type="text" placeholder="<?php echo $strVAT?>" required />
	</div>

			<div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strMetaDescription?></label>
	  <input name="produs_meta" Type="text" required />
	</div>
	</div>
	<div class="grid-x grid-padding-x">  
				<div class="large-12 medium-12 small-12 cell">
				<label><?php echo $strProductShort?></label>
			<textarea name="produs_descriere" rows="5" id="myTextEditor" class="myTextEditor"></textarea>
	</div>	
	</div>	
           <div class="grid-x grid-padding-x">
              <div class="large-12 cell"><p align="center"><input type="submit" Value="<?php echo $strAdd?>" name="Submit" class="submit button"> </p> </div>
			  </div>
  </form>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM magazin_produse WHERE produs_id=$_GET[pID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
echo "<a href=\"siteproducts.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward\"></i></a>";
?>
<form Method="post" Action="siteproducts.php?mode=edit&pID=<?php echo $row['produs_id']?>" >
 <div class="grid-x grid-padding-x">  
 <div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strTitle?></label>
	  <input name="produs_nume" Type="text" size="30" required value="<?php echo $row['produs_nume']?>"/>
	</div>
		<div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strMainPicture?></label>
	  <input name="produs_imagine" id="image" Type="text" class="required" value="<?php echo $row['produs_imagine'] ?>" readonly="readonly" /> 
	<!-- Trigger/Open The Modal -->
					<a data-open="myModal-1" class="button"><?php echo $strImage?></a>
						<div class="large reveal" id="myModal-1" data-reveal>
					<!-- Modal content -->
					  <button class="close-button" data-close aria-label="Close reveal" type="button">
    <span aria-hidden="true">&times;</span>
  </button>
  <iframe src="<?php echo $strSiteURL?>common/image.php?directory=products&field=image" frameborder="0" style="border:0" Width="100%" height="750"></iframe>
  </div>
  </div>

	<div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strProductThumb?></label>
	  <input name="produs_thumb" id="thumb" Type="text" class="required" value="<?php echo $row['produs_thumb'] ?>" readonly="readonly" /> 	<!-- Trigger/Open The Modal -->
					<a data-open="myModal-2" class="button"><?php echo $strImage?></a>
						<div class="large reveal" id="myModal-2" data-reveal>
					<!-- Modal content -->
					  <button class="close-button" data-close aria-label="Close reveal" type="button">
    <span aria-hidden="true">&times;</span>
  </button>
  <iframe src="<?php echo $strSiteURL?>common/image.php?directory=products&field=thumb" frameborder="0" style="border:0" Width="100%" height="750"></iframe>
</div> 
	</div>
	</div>
	<div class="grid-x grid-padding-x">
	<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strCategory?></label>
	  <input name="produs_categorie" Type="text" required value="<?php echo $row['produs_categorie']?>"/>
	</div>	
	<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strFriendlyCategory?></label>
	  <input name="produs_fcategorie" Type="text" required value="<?php echo $row['produs_fcategorie']?>"/>
	</div>
		<div class="large-2 medium-2 small-2 cell">
		<label><?php echo $strLanguage?></label>
		<input name="produs_limba" Type="text" required value="<?php echo $row['produs_limba']?>"/>
	</div>
	<div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strPrice?></label>
	  <input name="produs_pret" Type="text" required value="<?php echo $row['produs_pret']?>"/>
	</div>
	<div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strDiscountedPrice?></label>
	  <input name="produs_dpret" Type="text" required value="<?php echo $row['produs_dpret']?>"/>
	</div>
	</div>
		<div class="grid-x grid-padding-x"> 
		<div class="large-3 medium-3 small-3 cell">		
	  <label><?php echo $strURL?></label>
	  <input name="produs_url" Type="text" required value="<?php echo $row['produs_url']?>"/>
	</div>
	<div class="large-3 medium-3 small-3 cell">	
	  <label><?php echo $strKeyWords?></label>
	  <input name="produs_keywords" Type="text" required value="<?php echo $row['produs_keywords']?>"/>
	</div>		
	<div class="large-3 medium-3 small-3 cell">	
	  <label><?php echo $strVAT?></label>
	  <input name="produs_tva" Type="text" required value="<?php echo $row['produs_tva']?>"/>
	</div>
<div class="large-3 medium-3 small-3 cell">	
	  <label><?php echo $strMetaDescription?></label>
	  <input name="produs_meta" Type="text" required value="<?php echo $row['produs_meta']?>"/>
	</div>
	</div>
	<div class="grid-x grid-padding-x">  
	<div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strProductShort?></label>
	  <textarea name="produs_descriere" id="myTextEditor" class="myTextEditor" rows="5"><?php echo $row['produs_descriere']?></textarea>
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
echo "<a href=\"siteproducts.php?mode=new\" class=\"button\">$strAddNew &nbsp;<i class=\"fas fa-plus\"></i></a><br />";
$query="SELECT * FROM magazin_produse ";
if (isSet($_GET['cat']) AND $_GET['cat']!="")
{
	$query= $query . " WHERE produs_categorie='$_GET[cat]'"; 
}
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY produs_categorie ASC, produs_nume ASC $pages->limit";
$result=ezpub_query($conn,$query);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
	
?>
<div class="paginate">
<?php
echo $strTotal . " " .$numar." ".$strProducts ;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"siteproducts.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
echo " <br /><br />";
$sql="SELECT DISTINCT produs_categorie, produs_fcategorie 
FROM magazin_produse
ORDER BY produs_categorie ASC;";
$result2=ezpub_query($conn,$sql);
While ($row1=ezpub_fetch_array($result2)){
	$categ=$row1["produs_categorie"];
	$catn=$row1["produs_fcategorie"];
    echo "<a href=\"siteproducts.php?cat=$categ\">$catn</a>&nbsp;";
}
?>
</div>
<table>
	      <thead>
    	<tr> 
        	<th><?php echo $strID?></th>
			<th><?php echo $strTitle?></th>
			<th><?php echo $strCategory?></th>
			<th><?php echo $strMetaDescription?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr> 
			<td>$row[produs_id]
			<td>$row[produs_nume]
			<td>$row[produs_fcategorie]
			<td>$row[produs_meta]
			  <td><a href=\"siteproducts.php?mode=edit&pID=$row[produs_id]\" ><i class=\"far fa-edit fa-xl\"></i></a>
			<td><a href=\"siteproducts.php?mode=delete&pID=$row[produs_id]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a>
        </tr>";
}
echo "</tbody></table>";
}
}
}
?>
</div>
</div>
<hr />
<?php
include '../bottom.php';
?>