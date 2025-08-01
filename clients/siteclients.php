<?php
//update 08.01.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';

$strPageTitle="Administrare clienți";
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
    'searchseenlace visualblocks code fullscreen preview',
    'insertdatetime media table contextmenu paste code pagebreak'
  ],
  toolbar: 'insertfile undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link preview code pagebreak',
content_css: [
    '//fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i',
    '//www.tiny.cloud/css/codepen.min.css'],
	 image_title: true, 
  // enable automatic uploads of images seenresented by blob or data URIs
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

$nsql="DELETE FROM clienti_date WHERE ID_Client=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteclients.php\"
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
$clientcui=$_POST["Client_RO"]." ".$_POST["Client_CIF"];
	$mSQL = "INSERT INTO clienti_date(";
	$mSQL = $mSQL . "Client_Denumire,";
	$mSQL = $mSQL . "Client_Adresa,";
	$mSQL = $mSQL . "Client_Telefon,";
	$mSQL = $mSQL . "Client_CUI,";
	$mSQL = $mSQL . "Client_RC,";
	$mSQL = $mSQL . "Client_Banca,";
	$mSQL = $mSQL . "Client_IBAN,";
	$mSQL = $mSQL . "Client_Localitate,";
	$mSQL = $mSQL . "Client_Judet,";
	$mSQL = $mSQL . "Client_Cod_CAEN,";
	$mSQL = $mSQL . "Client_Numar_Angajati,";
	$mSQL = $mSQL . "Client_Descriere_Activitate,";
	$mSQL = $mSQL . "Client_Web,";
	$mSQL = $mSQL . "Client_Email,";
	$mSQL = $mSQL . "Client_CIF,";
	$mSQL = $mSQL . "Client_RO,";
	$mSQL = $mSQL . "Client_Codpostal,";
	$mSQL = $mSQL . "Client_Tip,";
	$mSQL = $mSQL . "Client_Caracterizare)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Client_Denumire"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Client_Adresa"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Client_Telefon"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$clientcui) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Client_RC"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Client_Banca"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Client_IBAN"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Client_Localitate"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Client_Judet"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Client_Cod_CAEN"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Client_Numar_Angajati"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Client_Descriere_Activitate"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Client_Web"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Client_Email"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Client_CIF"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Client_RO"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Client_Codpostal"]) . "', ";
	$mSQL = $mSQL . "'" .$_POST["Client_Tip"] . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["Client_Caracterizare"]) . "') ";
				
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
$clientcui=$_POST["Client_RO"]." ".$_POST["Client_CIF"];
$strWhereClause = " WHERE clienti_date.ID_Client=" . $_GET["cID"] . ";";
$query= "UPDATE clienti_date SET clienti_date.Client_Denumire='" .str_replace("'","&#39;",$_POST["Client_Denumire"]) . "' ," ;
$query= $query . "clienti_date.Client_Adresa='" .str_replace("'","&#39;",$_POST["Client_Adresa"]) . "' ," ;
$query= $query . "clienti_date.Client_Telefon='" .str_replace("'","&#39;",$_POST["Client_Telefon"]) . "' ," ;
$query= $query . "clienti_date.Client_CUI='" .str_replace("'","&#39;",$clientcui) . "' ," ;
$query= $query . "clienti_date.Client_RC='" .str_replace("'","&#39;",$_POST["Client_RC"]) . "' ," ;
$query= $query . "clienti_date.Client_Banca='" .str_replace("'","&#39;",$_POST["Client_Banca"]) . "' ," ;
$query= $query . "clienti_date.Client_IBAN='" .str_replace("'","&#39;",$_POST["Client_IBAN"]) . "' ," ;
$query= $query . "clienti_date.Client_Localitate='" .str_replace("'","&#39;",$_POST["Client_Localitate"]) . "' ," ;
$query= $query . "clienti_date.Client_Judet='" .str_replace("'","&#39;",$_POST["Client_Judet"]) . "' ," ;
$query= $query . "clienti_date.Client_Cod_CAEN='" .str_replace("'","&#39;",$_POST["Client_Cod_CAEN"]) . "' ," ;
$query= $query . "clienti_date.Client_Tip='" .$_POST["Client_Tip"] . "' ," ;
$query= $query . "clienti_date.Client_RO='" .$_POST["Client_RO"] . "' ," ;
$query= $query . "clienti_date.Client_CIF='" .$_POST["Client_CIF"] . "' ," ;
$query= $query . "clienti_date.Client_Email='" .$_POST["Client_Email"] . "' ," ;
$query= $query . "clienti_date.Client_Codpostal='" .$_POST["Client_Codpostal"] . "' ," ;
$query= $query . "clienti_date.Client_Numar_Angajati='" .str_replace("'","&#39;",$_POST["Client_Numar_Angajati"]) . "' ," ;
$query= $query . "clienti_date.Client_Descriere_Activitate='" .str_replace("'","&#39;",$_POST["Client_Descriere_Activitate"]) . "' ," ;
$query= $query . "clienti_date.Client_Web='" .str_replace("'","&#39;",$_POST["Client_Web"]) . "' ," ;
$query= $query . " clienti_date.Client_Caracterizare='" .str_replace("'","&#39;",$_POST["Client_Caracterizare"]) . "' "; 
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
<script language="JavaScript" type="text/JavaScript">
$(document).ready(function(){
	$("#Cui").keyup(function(){
		$.ajax({
		type: "POST",
		url: "../common/check_client.php",
		data:'keyword='+$(this).val(),
		beforeSend: function(){
			$("#Cui").css("background","#FFF url(../img/LoaderIcon.gif) no-seeneat 165px");
		},
		success: function(data){
			$("#suggesstion-box").show();
			$("#suggesstion-box").html(data);
			$("#search-box").css("background","#FFF");
		}
		});
	});
});

function selectCountry(val) {
	split_str=val.split(" - ");
$("#Cui").val(split_str[0]);
$("#judet").val(split_str[1]);
$("#suggesstion-box").hide();
}
</script>

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
           $("#codpostal").val(data["codpostal"]);
		   $("#loaderIcon").hide();   
		   }
catch(err) {
  document.getElementById("response").innerHTML = err.message;
}
        },
        error : function(){
           alert('Nu se poate face legătura la serverul ANAF!');
        }
    });
});
});
</script>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="siteclients.php" class="button"><?php echo $strBack?> <i class="fas fa-backward"></i></a></p>
</div>
</div>
			    <div class="grid-x grid-margin-x">
			  <div class="large-6 medium-6 small-6 cell">
<div id="response"></div>
<div class="input-group">
  <span class="input-group-label"><?php echo $strCompanyVAT?></span>
  <input class="input-group-field" type="text" name="Cui" id="Cui" placeholder="<?php echo $strEnterVATNumber?>">
  <div class="input-group-button">
    <button id="btn1" class="button" ><i class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
  </div>
</div>
  <div id="suggesstion-box"></div>
	</div>	
	</div>	
<form Method="post" Action="siteclients.php?mode=new" >
			    <div class="grid-x grid-margin-x">
			  <div class="large-8 medium-8 small-8 cell">
	  <label><?php echo $strTitle?></label>
	  <input name="Client_Denumire" Type="text" id="denumire" value="" />
	</div>
	 <div class="large-4 medium-4 small-4 cell">
      <label><?php echo $strBranch?></label>
      <input name="Client_Tip" Type="radio" value="0" /> <?php echo $strYes?>&nbsp;&nbsp;<input name="Client_Tip" Type="radio" value="1" checked><?php echo $strNo?>
    </div>	 
    </div>	
			    <div class="grid-x grid-margin-x">	
				<div class="large-1 medium-1 small-1 cell">
	  <label><?php echo $strCompanyFA?></label>
	  <input name="Client_RO" Type="text" id="tva" value="" size="3"/> 
	  </div>
	  	 <div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strCompanyVAT?></label>
	  <input name="Client_CIF" Type="text" id="cif" value="" />
	</div>
	 <div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strCompanyRC?></label>
	  <input name="Client_RC" id="numar_reg_com" Type="text" value=""/>
	</div>
	 <div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strAddress?></label>
	  <textarea name="Client_Adresa" id="adresa" style="width:100%;"></textarea>
	</div>			 
	</div>	
	<div class="grid-x grid-margin-x">	
				<div class="large-2 medium-2 small-2 cell">	
	  <label><?php echo $strCity?></label>
	  <input name="Client_Localitate" id="oras" Type="text"  />
	</div>
<div class="large-2 medium-2 small-2 cell">	
	  <label><?php echo $strCounty?></label>
	  <input name="Client_Judet" id="judet" Type="text" value="" />
	</div>
	<div class="large-2 medium-2 small-2 cell">	
	  <label><?php echo $strCode?></label>
	  <input name="Client_Codpostal" id="codpostal" Type="text" value="" />
	</div>
		 <div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strCompany?></label>
	  <textarea name="datecontract" id="datecontract" style="width:100%;"></textarea>
	</div>	
	</div>
				    <div class="grid-x grid-margin-x">	
				<div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strCompanyBank?></label>
	  <input name="Client_Banca" Type="text"  />
	</div>
	 <div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strCompanyIBAN?></label>
	  <input name="Client_IBAN" Type="text"  />
	  	</div>
 	</div>
				    <div class="grid-x grid-margin-x">	
	 <div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strPhone?></label>
	  <input name="Client_Telefon" Type="text"  />
	</div>
	 <div class="large-4 medium-4 small-4 cell">			 
	  <label><?php echo $strEmail?></label>
	  <input name="Client_Email" Type="text"  />
		</div>
		 <div class="large-4 medium-4 small-4 cell">
		  <label><?php echo $strWWW?></label>
	  <input name="Client_Web" Type="text"  />
 	</div>			 
 	</div>		
				    <div class="grid-x grid-margin-x">	
				<div class="large-4 medium-4 small-4 cell">	
	  <label><?php echo $strEmployees?></label>
	  <input name="Client_Numar_Angajati" Type="text" value="0" />
	</div>
			<div class="large-8 medium-8 small-8 cell">		 
	  <label><?php echo $strCAENCode?></label>
	  <input name="Client_Cod_CAEN" Type="text"  />
		</div>			
		</div>			
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strActivities?></label>
	  <textarea name="Client_Descriere_Activitate" id="myTextEditor" class="myTextEditor" rows="5"></textarea>
		</div>
		</div>
	 			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strProfile?></label>
	  <textarea name="Client_Caracterizare" id="myTextEditor" class="myTextEditor" rows="5"></textarea>
		</div>
		</div>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"><p align="center"> <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button" /> </p></div>
	</div>
  </form>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM clienti_date WHERE ID_Client=$_GET[cID]";
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
           $("#codpostal").val(data["codpostal"]);
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
			  <p><a href="siteclients.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<div class="grid-x grid-margin-x">
			  <div class="large-6 medium-6 small-6 cell">
<div id="response"></div>
<div class="input-group">
  <span class="input-group-label"><?php echo $strCompanyVAT?></span>
  <input class="input-group-field" type="text" name="Cui" id="Cui" value="<?php echo $row['Client_CIF'] ?>">
  <div class="input-group-button">
    <button id="btn11" class="button" ><i class="fas fa-sync-alt"></i>&nbsp;<?php echo $strUpdate ?></button>
  </div>
</div>
	</div>	
	</div>	
  			  <form Method="post"  Action="siteclients.php?mode=edit&cID=<?php echo $row['ID_Client']?>" >
			    <div class="grid-x grid-margin-x">
			  <div class="large-8 medium-8 small-8 cell">
			  <label><?php echo $strTitle?></label>
<input name="Client_Denumire" id="denumire" Type="text" value="<?php echo $row['Client_Denumire'] ?>" />
</div>
		 <div class="large-4 medium-4 small-4 cell">
      <label><?php echo $strBranch?></label>
      <input name="Client_Tip" Type="radio" value="0" <?php If ($row["Client_Tip"]==0) echo "checked"?> /> <?php echo $strYes?>&nbsp;&nbsp;<input name="Client_Tip" Type="radio" value="1" <?php If ($row["Client_Tip"]==1) echo "checked"?>><?php echo $strNo?>
    </div>	 
    </div>	 
		<div class="grid-x grid-margin-x">
					<div class="large-1 medium-1 small-1 cell">
	  <label><?php echo $strCompanyFA?></label>
	  <input name="Client_RO" Type="text" id="tva" value="<?php echo $row['Client_RO'] ?>" size="3"/> 
	  </div>
		<div class="large-2 medium-2 small-2 cell">
	  <label><?php echo $strCompanyVAT?></label>
	  <input name="Client_CIF" Type="text" id="ro" value="<?php echo $row['Client_CIF'] ?>" />
		</div>
				<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strCompanyRC?></label>
	  <input name="Client_RC" Type="text" id="numar_reg_com" value="<?php echo $row['Client_RC'] ?>" />
		</div>
				<div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strAddress?></label>
	  <textarea name="Client_Adresa" id="adresa"><?php echo $row['Client_Adresa'] ?></textarea>
</div>
</div>
				    <div class="grid-x grid-margin-x">	
				<div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strCity?></label>
	  <input name="Client_Localitate" id="oras" Type="text" value="<?php echo $row['Client_Localitate'] ?>"  />
		</div>
			 <div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strCounty?></label>
	  <input name="Client_Judet" Type="text" id="judet" value="<?php echo $row['Client_Judet'] ?>"  />
	</div> 
	<div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strCode?></label>
	  <input name="Client_Codpostal" Type="text" id="codpostal" value="<?php echo $row['Client_Codpostal'] ?>"  />
	</div>
	</div>
				    <div class="grid-x grid-margin-x">	
				<div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strCompanyBank?></label>
	  <input name="Client_Banca" Type="text" value="<?php echo $row['Client_Banca'] ?>" />
			</div>
			 <div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strCompanyIBAN?></label>
	  <input name="Client_IBAN" Type="text" value="<?php echo $row['Client_IBAN'] ?>" />
		</div>
	</div>
 				    <div class="grid-x grid-margin-x">	
	 <div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strPhone?></label>
	  <input name="Client_Telefon" Type="text" value="<?php echo $row['Client_Telefon'] ?>"  />
		</div>
				<div class="large-4 medium-4 small-4 cell">		 
	  <label><?php echo $strEmail?></label>
	  <input name="Client_Email" Type="text" value="<?php echo $row['Client_Email'] ?>"  />
		</div>
				<div class="large-4 medium-4 small-4 cell"> 
				<label><?php echo $strWWW?></label>
	  <input name="Client_Web" Type="text" value="<?php echo $row['Client_Web'] ?>" />
	  </div>
	  </div>
	  				    <div class="grid-x grid-margin-x">	
				<div class="large-4 medium-4 small-4 cell">	
	  <label><?php echo $strEmployees?></label>
	  <?php
	  if (!$row['Client_Numar_Angajati'])
	  {$nrangajati=1;}
  Else
  {$nrangajati=$row['Client_Numar_Angajati'];}
	  ?>
	  <input name="Client_Numar_Angajati" Type="text" value="<?php echo $nrangajati ?>" />
	</div>
					  <div class="large-8 medium-8 small-8 cell">	 
	  <label><?php echo $strCAENCode?></label>
	  <input name="Client_Cod_CAEN" Type="text" value="<?php echo $row['Client_Cod_CAEN'] ?>" />
		</div>			
		</div>			
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strActivities?></label>
	  <textarea name="Client_Descriere_Activitate" id="myTextEditor" class="myTextEditor" rows="5"><?php echo $row['Client_Descriere_Activitate'] ?></textarea>
		</div>
		</div>
	 			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strProfile?></label>
	  <textarea name="Client_Caracterizare" id="myTextEditor" class="myTextEditor" rows="5"><?php echo $row['Client_Caracterizare'] ?></textarea>
		</div> 
		</div> 
	 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> 
	 <p align="center"><input Type="submit" Value="<?php echo $strModify?>" name="Submit" class="button" /> 
	 <?php If ($row["Client_Tip"]==1) { ?>
	 <a href="siteclientsbranch.php?cID=<?php echo $_GET['cID']?>" class="button"><?php echo $strAddBranch?></a>
	 <?php }?></p>
	</div>
	</div>
  </form>
<?php
}
Else
{
	?>
	<script src="<?php echo $strSiteURL ?>js/foundation/jquery.js"></script>
<script language="JavaScript" type="text/JavaScript">
$(document).ready(function(){
	$("#Cui").keyup(function(){
		$.ajax({
		type: "POST",
		url: "../common/check_client.php",
		data:'keyword='+$(this).val(),
		beforeSend: function(){
			$("#Cui").css("background","#FFF url(../img/LoaderIcon.gif) no-seeneat 165px");
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
    <button id="btn1" class="button" ><i class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
  </div>
</div>
  <div id="suggesstion-box"></div>
	</div>	
	</div>	
	<?php
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"siteclients.php?mode=new\" class=\"button\">$strAddNew <i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";
$query="SELECT * FROM clienti_date ";
if ((isset( $_GET['seen'])) && !empty( $_GET['seen'])){
$seen=$_GET['seen'];}
Else{
$seen=0;}
if ($seen!='0'){
$query= $query . " WHERE Client_Aloc='$seen'";
}

if ((isset( $_GET['start'])) && !empty( $_GET['start'])){
$start=$_GET['start'];}
Else{
$start=0;}
if ($start!='0'){
$query= $query . " WHERE Client_Denumire LIKE'$start%'";
};
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY Client_Denumire ASC $pages->limit";
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
echo $pages->display_pages() . " <a href=\"siteclients.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
echo " <br /><br />";
$sql="SELECT DISTINCT LEFT(clienti_date.Client_Denumire, 1) as letter 
FROM clienti_date 
Group By letter ORDER BY letter ASC;";
$result2=ezpub_query($conn,$sql);
While ($row1=ezpub_fetch_array($result2)){
	$char=$row1["letter"];
    echo "<a href=\"siteclients.php?start=$char\">$char</a>&nbsp;";
}
echo " <br /><br />";
$sql="SELECT DISTINCT (clienti_date.Client_Aloc) as seenby 
FROM clienti_date 
ORDER BY seenby ASC;";
$result2=ezpub_query($conn,$sql);
While ($row1=ezpub_fetch_array($result2)){
	$seen=$row1["seenby"];
    echo "<a href=\"siteclients.php?seen=$seen\">$seen</a>&nbsp;";
}
?>
</div>

<table width="100%">
	      <thead>
    	<tr>
			<th><?php echo $strTitle?></th>
			<th><?php echo $strVAT?></th>
			<th><?php echo $strCity?></th>
			<th><?php echo $strCounty?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
			<th><?php echo $strDetails?></th>
	      </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[Client_CUI]</td>
			<td>$row[Client_Localitate]</td>
			<td>$row[Client_Judet]</td>
			 <td><a href=\"siteclients.php?mode=edit&cID=$row[ID_Client]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			 <td><a href=\"siteclients.php?mode=delete&cID=$row[ID_Client]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
			 <td><a href=\"clientprofile.php?cID=$row[ID_Client]\"><i class=\"fa fa-search-plus fa-xl\" title=\"$strEdit\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"5\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
}
?>
</div>
</div>
<hr/>
<?php
include '../bottom.php';
?>