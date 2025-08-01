<?php
//update 8.01.2025

include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrează paginile!";
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
        <div class="large-12 cell">
<?php

echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM cms_pagini WHERE pagina_id=" .$_GET['pID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitepages.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">
</div>";
include '../bottom.php';
die;} // end delete record

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){
//insert new user
	$mSQL = "INSERT INTO cms_pagini(";
	$mSQL = $mSQL . "pagina_titlu,";
	$mSQL = $mSQL . "pagina_continut,";
	$mSQL = $mSQL . "pagina_url,";
	$mSQL = $mSQL . "pagina_categorie,";
	$mSQL = $mSQL . "pagina_descriere,";
	$mSQL = $mSQL . "pagina_numar,";
	$mSQL = $mSQL . "pagina_status,";
	$mSQL = $mSQL . "pagina_tip,";
	$mSQL = $mSQL . "pagina_master,";
	$mSQL = $mSQL . "pagina_limba,";
	$mSQL = $mSQL . "pagina_imaginetitlu,";
	$mSQL = $mSQL . "pagina_keywords)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$_POST["pagina_titlu"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["pagina_continut"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["pagina_url"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["pagina_categorie"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["pagina_descriere"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["pagina_numar"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["pagina_status"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["pagina_tip"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["pagina_master"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["pagina_limba"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["pagina_imaginetitlu"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["pagina_keywords"] ."')";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
	  echo "<div class=\"callout alert\">$strThereWasAnError</ br>Error: " . ezpub_error() . "</div></div></div><hr/>";
 include '../bottom.php';
die;
  }
Else{
echo "<div class=\"callout success\">$strRecordAdded</div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitepages.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\"></div><hr />";
include '../bottom.php';
die;
}}
//ends if post new
Else
{// edit
$strWhereClause = " WHERE cms_pagini.pagina_id=" . $_GET["pID"] . ";";
$query= "UPDATE cms_pagini SET cms_pagini.pagina_titlu='" . $_POST["pagina_titlu"] . "' ," ;
$query= $query . " cms_pagini.pagina_continut='" . $_POST["pagina_continut"] . "', "; 
$query= $query . " cms_pagini.pagina_url='" . $_POST["pagina_url"] . "', "; 
$query= $query . " cms_pagini.pagina_categorie='" . $_POST["pagina_categorie"] . "', "; 
$query= $query . " cms_pagini.pagina_descriere='" . $_POST["pagina_descriere"] . "', "; 
$query= $query . " cms_pagini.pagina_numar='" . $_POST["pagina_numar"] . "', "; 
$query= $query . " cms_pagini.pagina_status='" . $_POST["pagina_status"] . "', "; 
$query= $query . " cms_pagini.pagina_tip='" . $_POST["pagina_tip"] . "', "; 
$query= $query . " cms_pagini.pagina_limba='" . $_POST["pagina_limba"] . "', "; 
$query= $query . " cms_pagini.pagina_master='" . $_POST["pagina_master"] . "', "; 
$query= $query . " cms_pagini.pagina_imaginetitlu='" . $_POST["pagina_imaginetitlu"] . "', "; 
$query= $query . " cms_pagini.pagina_keywords='" . $_POST["pagina_keywords"] . "' "; 
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
    window.location = \"sitepages.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\"></div></div><hr />";
include '../bottom.php';
die;
}
}
}// ends post if
Else { // starts entering data

If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){ // we have new page
echo "<a href=\"sitepages.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward\"></i></a>";
?>
<form Method="post" id="users" Action="sitepages.php?mode=new" >
            <div class="grid-x grid-padding-x">
              <div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strType?></label>
					<select name="pagina_tip" required>
						<option value="0"><?php echo $strMaster?></option>
						<option value="1"><?php echo $strSlave?></option>
					</select>
              </div>
              <div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strMaster?></label>
					<select name="pagina_master" required>
						<option value="0"><?php echo $strMaster?></option>
							<?php $sql = "Select pagina_id, pagina_titlu FROM cms_pagini WHERE pagina_tip='0' ORDER BY pagina_titlu ASC";
							$result = ezpub_query($conn,$sql);
							while ($rss=ezpub_fetch_array($result)){
							?>
						<option  value="<?php echo $rss["pagina_id"]?>"><?php echo $rss["pagina_titlu"]?></option>
							<?php } ?>
					</select>
              </div>
              <div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strTitle?></label>
                <input type="text" name="pagina_titlu" Type="text" size="30" placeholder="<?php echo $strTitle?>" required/>
              </div>
              <div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strURL?></label>
                <input type="text" name="pagina_url" Type="text" size="30" placeholder="<?php echo $strURL?>" required/>
              </div>
            </div>
			 <div class="grid-x grid-padding-x">
		<div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strMainPicture?></label>
				<input name="pagina_imaginetitlu" id="image" Type="text" required readonly="readonly" />
					<!-- Trigger/Open The Modal -->
									<div class="full reveal" id="myModal" data-reveal>
					<!-- Modal content -->
					<iframe src="<?php echo $strSiteURL?>/common/image.php?directory=pages&field=image" frameborder="0" style="border:0" Width="100%" height="750"></iframe>
										  <button class="close-button" data-close aria-label="Close reveal" type="button">
    <span aria-hidden="true">&times;</span>
  </button>
						</div>
						<a data-open="myModal" class="button"><?php echo $strImage?></a>
		</div>
            <div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strCategory?></label>
                <input type="text" name="pagina_categorie" Type="text" size="30" placeholder="<?php echo $strCategory?>" />
              </div>
              <div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strPageDescription?></label>
                <textarea name="pagina_descriere" placeholder="<?php echo $strPageDescription?>" required rows="6"></textarea>
              </div>
            </div>
			<div class="grid-x grid-padding-x">
              <div class="large-6 medium-6 small-6 cell">
                <label><?php echo $strPageKeywords?></label>
                <input type="text" name="pagina_keywords" Type="text" size="30" placeholder="<?php echo $strPageKeywords?>" required/>
              </div>
              <div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strPageNumber?></label>
                <input type="text" name="pagina_numar" Type="text" size="30" placeholder="<?php echo $strPageNumber?>" required/>
              </div>
             <div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strPageLanguage?></label>
                <input type="text" name="pagina_limba" Type="text" size="30" placeholder="<?php echo $strPageLanguage?>" required/>
              </div>
			    <div class="large-2 medium-2 small-2 cell">
                <legend><?php echo $strActive?></legend>
				<input name="pagina_status" type="radio" value="0" checked /><label> <?php echo $strYes?></label><input name="pagina_status" Type="radio" value="1"><label><?php echo $strNo?></label>
            </div>
            </div>
            <div class="grid-x grid-padding-x">
              <div class="large-12 medium-12 small-12 cell"><textarea name="pagina_continut" id="myTextEditor" class="myTextEditor" rows="10"  ></textarea></div>
			  </div>
            <div class="grid-x grid-padding-x">
              <div class="large-12 cell"><p align="center"><br /><input type="submit" Value="<?php echo $strAdd?>" name="Submit" class="submit button"> </p> </div>
			  </div>
    </form>
<?php
} // ends if new page
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
	echo "<a href=\"sitepages.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward\"></i></a>";
$query="SELECT * FROM cms_pagini WHERE pagina_id=$_GET[pID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
<form Method="post" id="users" Action="sitepages.php?mode=edit&pID=<?php echo $row['pagina_id']?>" >
             <div class="grid-x grid-padding-x">
                  <div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strType?></label>
					<select name="pagina_tip" required>
                    <option value="0" <?php If ($row['pagina_tip']==0) echo "selected"?>><?php echo $strMaster?></option>
           <option value="1" <?php If ($row['pagina_tip']==1) echo "selected"?>><?php echo $strSlave?></option>
  	</select>
              </div>
                  <div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strMaster?></label>
					<select name="pagina_master" required>
           <option value="0"><?php echo $strMaster?></option>
          <?php $sql = "Select pagina_id, pagina_titlu FROM cms_pagini WHERE pagina_tip='0' ORDER BY pagina_titlu ASC";
        $result = ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
		if ($row['pagina_master']==$rss['pagina_id']) {
	?>
          <option  selected value="<?php echo $rss["pagina_id"]?>"><?php echo $rss["pagina_titlu"]?></option>
		  <?php } else { ?>
          <option  value="<?php echo $rss["pagina_id"]?>"><?php echo $rss["pagina_titlu"]?></option>
          <?php
}}?>
        	</select>
              </div>
    <div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strTitle?></label>
				<input name="pagina_titlu" Type="text" size="30" class="required" value="<?php echo $row['pagina_titlu']?>"/>
				</div>
    <div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strURL?></label>
				<input name="pagina_url" Type="text" size="30" class="required"value="<?php echo $row['pagina_url']?>"/>
				  </div>
            </div>
					 <div class="grid-x grid-padding-x">
		<div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strMainPicture?></label>
				<input name="pagina_imaginetitlu" id="image" Type="text" class="required" value="<?php echo $row['pagina_imaginetitlu'] ?>" readonly="readonly" /> 
					<!-- Trigger/Open The Modal -->
					<div class="full reveal" id="myModal" data-reveal>
					<!-- Modal content -->
						<iframe src="../common/image.php?directory=pages&field=image" frameborder="0" style="border:0" Width="100%" height="750"></iframe>
						  <button class="close-button" data-close aria-label="Close modal" type="button">
    <span aria-hidden="true">&times;</span>
  </button>
						</div>
						<a href="#" class="button" data-open="myModal"><?php echo $strImage?></a>
		</div>
		<div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strCategory?></label>
				<input name="pagina_categorie" Type="text" class="required"value="<?php echo $row['pagina_categorie']?>"/> 
				</div>
		<div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strPageDescription?></label>
	  <input name="pagina_descriere" Type="text" class="required" value="<?php echo $row['pagina_descriere']?>" rows ="6"/>
	  </div>
            </div>
			<div class="grid-x grid-padding-x">
              		<div class="large-6 medium-6 small-6 cell">
                <label><?php echo $strPageKeywords?></label>
	  <input name="pagina_keywords" Type="text" class="required" value="<?php echo $row['pagina_keywords']?>"/> </div>
		<div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strPageNumber?></label>
				<input name="pagina_numar" Type="text" class="required number" size="3" value="<?php echo $row['pagina_numar']?>"/>
				</div>
            		<div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strPageLanguage?></label>
<input name="pagina_limba" Type="text" class="required" size="2" value="<?php echo $row['pagina_limba']?>"/>
</div>
		<div class="large-2 medium-2 small-2 cell">
                <legend><?php echo $strActive?></legend>
     <input name="pagina_status" Type="radio" value="0" <?php If ($row['pagina_status']==0) echo "checked"?> /> <label><?php echo $strYes?> </label> 
	 <input name="pagina_status" Type="radio" value="1" <?php If ($row['pagina_status']==1) echo "checked"?>> <label><?php echo $strNo?></label>
	   </div>
            </div>
            <div class="grid-x grid-padding-x">
              		<div class="large-12 medium-12 small-12 cell">
			  <textarea name="pagina_continut" id="myTextEditor" class="myTextEditor" style="width: 100%"><?php echo $row["pagina_continut"]?></textarea>
			 </div>
            <div class="grid-x grid-padding-x">
              <div class="large-12 cell"><p align="center"><br /><input type="submit" Value="<?php echo $strModify?>" name="Submit" class="submit button"> </p> </div>
			  </div>
  </form>
<?php
} // ends editing
Else
{ // just lists records
echo "<a href=\"sitepages.php?mode=new\" class=\"button\">$strAdd &nbsp;<i class=\"fas fa-plus\"></i></a>";
$query="SELECT * FROM cms_pagini WHERE pagina_tip='0'";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
if ($numar==0)
{
echo $strNoRecordsFound;
}
Else {
?>
<table>
  <thead>
    <tr>
        	<th><?php echo $strID?></th>
        	<th><?php echo $strMaster?></th>
			<th><?php echo $strTitle?></th>
			<th><?php echo $strPageDescription?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[pagina_id]</td>
			<td>$row[pagina_master]</td>
			<td>$row[pagina_titlu]</td>
			<td>$row[pagina_descriere]</td>
			<td><a href=\"sitepages.php?mode=edit&pID=$row[pagina_id]\" ><i class=\"fas fa-edit\"></i></a></td>
			<td><a href=\"sitepages.php?mode=delete&pID=$row[pagina_id]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
		$subpagequery="SELECT * FROM cms_pagini WHERE pagina_master='$row[pagina_id]' AND pagina_tip='1'";
			$subpageresult=ezpub_query($conn,$subpagequery);
$subpagenumber=ezpub_num_rows($subpageresult,$subpagequery);
if ($subpagenumber!=0) {
	While ($rows=ezpub_fetch_array($subpageresult)){
    		echo"<tr>
			<td>$rows[pagina_id]</td>
			<td>$rows[pagina_master]</td>
			<td>$rows[pagina_titlu]</td>
			<td>$rows[pagina_descriere]</td>
			<td><a href=\"sitepages.php?mode=edit&pID=$rows[pagina_id]\" ><i class=\"fas fa-edit\"></i></a></td>
			<td><a href=\"sitepages.php?mode=delete&pID=$rows[pagina_id]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";	
		}}}
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