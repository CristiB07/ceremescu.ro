<?php
//update 08.01.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';

$strPageTitle="Administrare prospecți";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <script src='../js/tinymce/tinymce.min.js'></script>
<script language="JavaScript" type="text/JavaScript">
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
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM prospecti WHERE prospect_id=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteprospects.php\"
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
	$mSQL = "INSERT INTO prospecti(";
	$mSQL = $mSQL . "prospect_denumire,";
	$mSQL = $mSQL . "prospect_adresa,";
	$mSQL = $mSQL . "prospect_telefon,";
	$mSQL = $mSQL . "prospect_rc,";
	$mSQL = $mSQL . "prospect_contact,";
	$mSQL = $mSQL . "prospect_sector,";
	$mSQL = $mSQL . "prospect_cartier,";
	$mSQL = $mSQL . "prospect_activitate,";
	$mSQL = $mSQL . "prospect_email,";
	$mSQL = $mSQL . "prospect_cui,";
	$mSQL = $mSQL . "prospect_caracterizare)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["prospect_denumire"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["prospect_adresa"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["prospect_telefon"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["prospect_rc"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["prospect_contact"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["prospect_sector"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["prospect_cartier"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["prospect_activitate"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["prospect_email"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["prospect_cui"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["prospect_caracterizare"]) . "') ";
				
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
    window.history.go(-2);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{// edit
$strWhereClause = " WHERE prospecti.prospect_id=" . $_GET["cID"] . ";";
$query= "UPDATE prospecti SET prospecti.prospect_denumire='" .str_replace("'","&#39;",$_POST["prospect_denumire"]) . "' ," ;
$query= $query . "prospecti.prospect_adresa='" .str_replace("'","&#39;",$_POST["prospect_adresa"]) . "' ," ;
$query= $query . "prospecti.prospect_telefon='" .str_replace("'","&#39;",$_POST["prospect_telefon"]) . "' ," ;
$query= $query . "prospecti.prospect_rc='" .str_replace("'","&#39;",$_POST["prospect_rc"]) . "' ," ;
$query= $query . "prospecti.prospect_contact='" .str_replace("'","&#39;",$_POST["prospect_contact"]) . "' ," ;
$query= $query . "prospecti.prospect_sector='" .str_replace("'","&#39;",$_POST["prospect_sector"]) . "' ," ;
$query= $query . "prospecti.prospect_cartier='" .str_replace("'","&#39;",$_POST["prospect_cartier"]) . "' ," ;
$query= $query . "prospecti.prospect_cui='" .$_POST["prospect_cui"] . "' ," ;
$query= $query . "prospecti.prospect_email='" .$_POST["prospect_email"] . "' ," ;
$query= $query . "prospecti.prospect_activitate='" .str_replace("'","&#39;",$_POST["prospect_activitate"]) . "' ," ;
$query= $query . " prospecti.prospect_caracterizare='" .str_replace("'","&#39;",$_POST["prospect_caracterizare"]) . "' "; 
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
    window.history.go(-2);
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

If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
<script src="<?php echo $strSiteURL ?>js/foundation/jquery.js"></script>
	<script>
$(document).ready(function() {
    $("#btn1").click(function() {  
	$("#loaderIcon").show();    
	jQuery.ajax({
	url: "../common/cui.php",
	dataType: "json",
	data:'Cui='+$("#Cui").val(),
	type: "POST",
	  success: function(data) {
		  try {
           $('#denumire').val((data["denumire"] || "").toUpperCase());
           $("#cif").val(data["cif"]);
           $("#tva").val(data["tva"]);
           $("#adresa").val(data["adresa"]);
           $("#judet").val((data["judet"]).toUpperCase());
           $("#oras").val((data["oras"]).toUpperCase());
           $("#numar_reg_com").val(data["numar_reg_com"]);
           $("#datecontract").val(data["datecontract"]);
		   $("#loaderIcon").hide();   
		   }
catch(err) {
  document.getElementById("response").innerHTML = err.message;
}
        },
        error : function(){
           alert('Some error occurred!');
        }
    });
});
});
</script>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="siteprospects.php" class="button"><?php echo $strBack?>&nbsp;<i class="fas fa-backward"></i></a></p>
</div>
</div>
			    <div class="grid-x grid-margin-x">
			  <div class="large-6 medium-6 small-6 cell">
<div id="response"></div>
<div class="input-group">
  <span class="input-group-label"><?php echo $strCompanyVAT?></span>
  <input class="input-group-field" type="text" name="Cui" id="Cui" placeholder="<?php echo $strEnterVATNumber?>">
  <div class="input-group-button">
    <button id="btn1" class="button success" ><i class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
  </div>
</div>
  <div id="suggesstion-box"></div>
	</div>	
	</div>	
<form Method="post" Action="siteprospects.php?mode=new" >
			    <div class="grid-x grid-margin-x">
			  <div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strTitle?></label>
	  <input name="prospect_denumire" Type="text" id="denumire" value="" />
	</div>
	 <div class="large-3 medium-3 small-3 cell">
  <label><?php echo $strCompanyVAT?></label>
	  <input name="prospect_cui" Type="text" id="cif" value="" />
    </div>	
 <div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strCompanyRC?></label>
	  <input name="prospect_rc" id="numar_reg_com" Type="text" value=""/>
	</div>	
    </div>	
			    <div class="grid-x grid-margin-x">	
	 <div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strAddress?></label>
	  <textarea name="prospect_adresa" id="adresa" style="width:100%;"></textarea>
	</div>			 
				<div class="large-3 medium-3 small-3 cell">	
	  <label><?php echo $strSector?></label>
	  <input name="prospect_sector" id="oras" Type="text"  />
	</div>
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strNeighbourhood?></label>
	  <input name="prospect_cartier" id="judet" Type="text" value="" />
	</div>
	</div>
				    <div class="grid-x grid-margin-x">	
	 <div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strContact?></label>
	  <input name="prospect_contact" Type="text"  />
	  	</div>
 	 <div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strPhone?></label>
	  <input name="prospect_telefon" Type="text"  />
	</div>
	 <div class="large-3 medium-3 small-3 cell">			 
	  <label><?php echo $strEmail?></label>
	  <input name="prospect_email" Type="text"  />
		</div>
 	</div>		
				    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <label><?php echo $strActivities?></label>
	  <input name="prospect_activitate" Type="text" />
		</div>
		</div>
	 			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strProfile?></label>
	  <textarea name="prospect_caracterizare" id="myTextEditor" class="myTextEditor" rows="5"></textarea>
		</div>
		</div>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM prospecti WHERE prospect_id=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
<script src="<?php echo $strSiteURL ?>js/foundation/jquery.js"></script>
	<script>
$(document).ready(function() {
    $("#btn11").click(function() {  
	$("#loaderIcon").show();    
	jQuery.ajax({
	url: "../common/cui.php",
	dataType: "json",
	data:'Cui='+$("#Cui").val(),
	type: "POST",
	  success: function(data) {
		  try {
           $('#denumire').val((data["denumire"] || "").toUpperCase());
           $("#cif").val(data["cif"]);
           $("#tva").val(data["tva"]);
           $("#adresa").val(data["adresa"]);
           $("#judet").val((data["judet"]).toUpperCase());
           $("#oras").val((data["oras"]).toUpperCase());
           $("#numar_reg_com").val(data["numar_reg_com"]);
           $("#datecontract").val(data["datecontract"]);
		   $("#loaderIcon").hide();   
		   }
catch(err) {
  document.getElementById("response").innerHTML = err.message;
}
        },
        error : function(){
           alert($("#Cui").val());
		   document.getElementById("Cui").innerHTML = data;
        }
    });
});
});
</script>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="siteprospects.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<div class="grid-x grid-margin-x">
			  <div class="large-6 medium-6 small-6 cell">
<div id="response"></div>
<div class="input-group">
  <span class="input-group-label"><?php echo $strCompanyVAT?></span>
  <input class="input-group-field" type="text" name="Cui" id="Cui" value="<?php echo $row['prospect_cui'] ?>">
  <div class="input-group-button">
    <button id="btn11" class="button success" ><i class="fas fa-sync-alt"></i>&nbsp;<?php echo $strUpdate ?></button>
  </div>
</div>
	</div>	
	</div>	
  			  <form Method="post"  Action="siteprospects.php?mode=edit&cID=<?php echo $row['prospect_id']?>" >
				    <div class="grid-x grid-margin-x">
			  <div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strTitle?></label>
	  <input name="prospect_denumire" Type="text" id="denumire" value="<?php echo $row["prospect_denumire"]?>" />
	</div>
	 <div class="large-3 medium-3 small-3 cell">
  <label><?php echo $strCompanyVAT?></label>
	  <input name="prospect_cui" Type="text" id="cif" value="<?php echo $row["prospect_cui"]?>" />
    </div>	
 <div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strCompanyRC?></label>
	  <input name="prospect_rc" id="numar_reg_com" Type="text" value="<?php echo $row["prospect_rc"]?>"/>
	</div>	
    </div>	
			    <div class="grid-x grid-margin-x">	
	 <div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strAddress?></label>
	  <textarea name="prospect_adresa" id="adresa" style="width:100%;"><?php echo $row["prospect_adresa"]?></textarea>
	</div>			 
				<div class="large-3 medium-3 small-3 cell">	
	  <label><?php echo $strSector?></label>
	  <input name="prospect_sector" id="oras" Type="text" value="<?php echo $row["prospect_sector"]?>" />
	</div>
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strNeighbourhood?></label>
	  <input name="prospect_cartier" id="judet" Type="text" value="<?php echo $row["prospect_cartier"]?>" />
	</div>
	</div>
				    <div class="grid-x grid-margin-x">	
	 <div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strContact?></label>
	  <input name="prospect_contact" Type="text" value="<?php echo $row["prospect_contact"]?>" />
	  	</div>
 	 <div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strPhone?></label>
	  <input name="prospect_telefon" Type="text"  value="<?php echo $row["prospect_telefon"]?>"/>
	</div>
	 <div class="large-3 medium-3 small-3 cell">			 
	  <label><?php echo $strEmail?></label>
	  <input name="prospect_email" Type="text" value="<?php echo $row["prospect_email"]?>" />
		</div>
 	</div>				
			    <div class="grid-x grid-margin-x">
				
			  <div class="large-12 medium-12 small-12 cell">
			  <label><?php echo $strActivities?></label>
	  <input name="prospect_activitate" Type="text" />
		</div>
		</div>
	 			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strProfile?></label>
	  <textarea name="prospect_caracterizare" id="myTextEditor" class="myTextEditor" rows="5"></textarea>
		</div>
		</div>
	 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> 
	 <input Type="submit" Value="<?php echo $strModify?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
<?php
}
Else {?>
<script src="<?php echo $strSiteURL ?>js/foundation/jquery.js"></script>
<script language="JavaScript" type="text/JavaScript">
$(document).ready(function(){
	$("#Cui").keyup(function(){
		$.ajax({
		type: "POST",
		url: "check_prospect.php",
		data:'keyword='+$(this).val(),
		beforeSend: function(){
			$("#Cui").css("background","#FFF url(../img/LoaderIcon.gif) no-aloceat 165px");
		},
		success: function(data){
			$("#suggesstion-box").show();
			$("#suggesstion-box").html(data);
			$("#cui").css("background","#FFF");
		}
		});
	});
});

</script>

		    <div class="grid-x grid-margin-x">
			  <div class="large-6 medium-6 small-6 cell">
<div id="response"></div>
<div class="input-group">
  <span class="input-group-label"><?php echo $strCompanyName?></span>
  <input class="input-group-field" type="text" name="Cui" id="Cui" placeholder="<?php echo $strEnterName?>">
  <div class="input-group-button">
    <button id="btn1" class="button success" ><i class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
  </div>
</div>
  <div id="suggesstion-box"></div>
	</div>	
	</div>	
<?php
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"siteprospects.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";
if ((isset( $_GET['aloc'])) && !empty( $_GET['aloc'])){
$aloc=$_GET['aloc'];}
Else{
$aloc=0;}
$query="SELECT * FROM prospecti ";
if ($aloc!='0'){
$query= $query . " WHERE Client_Aloc='$aloc'";
};
if ((isset( $_GET['start'])) && !empty( $_GET['start'])){
$start=$_GET['start'];}
Else{
$start=0;}
$query="SELECT * FROM prospecti ";
if ($start!='0'){
$query= $query . " WHERE prospect_denumire LIKE'$start%'";
};

$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY prospect_denumire ASC $pages->limit";
$result=ezpub_query($conn,$query);

echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<div class="paginate">
<?php
echo $strTotal . " " .$numar." ".$strClients ;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"siteprospects.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
echo " <br /><br />";
$sql="SELECT DISTINCT LEFT(prospecti.prospect_denumire, 1) as letter 
FROM prospecti 
Group By letter ORDER BY letter ASC;";
$result2=ezpub_query($conn,$sql);
While ($row1=ezpub_fetch_array($result2)){
	$char=$row1["letter"];
    echo "<a href=\"siteprospects.php?start=$char\">$char</a>&nbsp;";
}
?>
</div>

<table width="100%">
	      <thead>
    	<tr>
			<th><?php echo $strTitle?></th>
			<th><?php echo $strVAT?></th>
			<th><?php echo $strSector?></th>
			<th><?php echo $strNeighbourhood?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
			<th><?php echo $strDetails?></th>
	      </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[prospect_denumire]</td>
			<td>$row[prospect_cui]</td>
			<td>$row[prospect_sector]</td>
			<td>$row[prospect_cartier]</td>
			 <td><a href=\"siteprospects.php?mode=edit&cID=$row[prospect_id]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			 <td><a href=\"siteprospects.php?mode=delete&cID=$row[prospect_id]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
			 <td><a href=\"prospectprofile.php?cID=$row[prospect_id]\"><i class=\"fa fa-search-plus fa-xl\" title=\"$strEdit\"></i></a></td>
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