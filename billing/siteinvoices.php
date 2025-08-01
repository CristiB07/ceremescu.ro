<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle="Administrare facturi";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$month= date('m');
$year=date('Y');
$day = date('d');

if ((isset( $_GET['aloc'])) && !empty( $_GET['aloc'])){
$aloc=$_GET['aloc'];}
Else{
$aloc=0;}
if ((isset( $_GET['cl'])) && !empty( $_GET['cl'])){
$cl=$_GET['cl'];}
Else{
$cl=0;}
if ((isset( $_GET['act'])) && !empty( $_GET['act'])){
$act=$_GET['act'];}
Else{
$act=0;}
if ((isset( $_GET['paid']))){
$paid=$_GET['paid'];}
Else{
$paid=3;}
if ((isset( $_GET['yr'])) && !empty( $_GET['yr'])){
$fyear=$_GET['yr'];
$year=$fyear;
}
Else{
$fyear=0;}
if ((isset( $_GET['fmonth'])) && !empty( $_GET['fmonth'])){
$fmonth=$_GET['fmonth'];}
Else{
$fmonth=0;}

?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<script language="JavaScript" type="text/JavaScript">
  function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
  }
</script>
	<script src="<?php echo $strSiteURL ?>js/vendor/jquery.js"></script>
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.6.min.js"></script>
<script type="text/javascript" src="../js/foundation/jquery.reveal.js"></script>
<?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM facturare_facturi WHERE factura_ID=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteinvoices.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){
	If ($_POST["existent"]==1)
	{
		$query="SELECT ID_Client FROM clienti_date  WHERE Client_Denumire='$_POST[factura_client_denumire]'";
		$result=ezpub_query($conn,$query);
		$row=ezpub_fetch_array($result);
		$Client_ID=$row["ID_Client"];
		if (!ezpub_query($conn,$query))
  {
  die('Error: ' . ezpub_error($conn,$query));
  }
	}
	Else
	{
		$clientcui=$_POST["factura_client_RO"]." ".$_POST["factura_client_CIF"];
		
	$mSQL = "INSERT INTO clienti_date(";
	$mSQL = $mSQL . "Client_Denumire,";
	$mSQL = $mSQL . "Client_Adresa,";
	$mSQL = $mSQL . "Client_CUI,";
	$mSQL = $mSQL . "Client_RC,";
	$mSQL = $mSQL . "Client_Banca,";
	$mSQL = $mSQL . "Client_IBAN,";
	$mSQL = $mSQL . "Client_RO,";
	$mSQL = $mSQL . "Client_CIF,";
	$mSQL = $mSQL . "Client_Localitate,";
	$mSQL = $mSQL . "Client_Tip,";
	$mSQL = $mSQL . "Client_Nr_Contract,";
	$mSQL = $mSQL . "Client_Judet)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["factura_client_denumire"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["factura_client_adresa"]) . "', ";
	$mSQL = $mSQL . "'" .$clientcui . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["factura_client_RC"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["factura_client_banca"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["factura_client_IBAN"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["factura_client_RO"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["factura_client_CIF"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["factura_client_localitate"]) . "', ";
	$mSQL = $mSQL . "'1', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["factura_client_contract"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["factura_client_judet"]) . "') ";

//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn,$query));
  }
Else{
		$Client_ID=ezpub_inserted_id($conn);
	}
	}
//insert new user
$dataemiterii = $_POST["strEData3"] ."-". $_POST["strEData2"] ."-". $_POST["strEData1"] ."";
$termenfactura = date('Y-m-d', strtotime($dataemiterii . ' +'.$_POST["factura_client_termen"].' day'));
$query1="SELECT SUM(articol_valoare) AS valoare_factura FROM facturare_articole_facturi WHERE factura_ID=$_GET[cID]";
$result1=ezpub_query($conn,$query1);
$row1=ezpub_fetch_array($result1);
$valoareproduse=$row1["valoare_factura"];
$query2="SELECT SUM(articol_TVA) AS valoare_tva FROM facturare_articole_facturi WHERE factura_ID=$_GET[cID]";
$result2=ezpub_query($conn,$query2);
$row2=ezpub_fetch_array($result2);
$valoareTVA=$row2["valoare_tva"];
$query3="SELECT SUM(articol_total) AS grandtotal FROM facturare_articole_facturi WHERE factura_ID=$_GET[cID]";
$result3=ezpub_query($conn,$query3);
$row3=ezpub_fetch_array($result3);
$grandtotal=$row3["grandtotal"];
$closed=1;
$anulat=0;
$data = date('Y-m-d', strtotime($dataemiterii));
    $sql="SELECT * FROM curs_valutar WHERE curs_valutar_﻿zi='$data'";

	$curs=ezpub_query($conn,$sql);
	$rss=ezpub_fetch_array($curs);
	If (!isSet($rss["curs_valutar_valoare"])){
	
 $curs=new CursBNR("https://www.bnr.ro/nbrfxrates.xml");
$cursvalutar=$curs->getExchangeRate("EUR");
	
	
	$mSQL = "INSERT INTO curs_valutar(";
	$mSQL = $mSQL . "curs_valutar_﻿zi,";
	$mSQL = $mSQL . "curs_valutar_valoare)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$data . "', ";
	$mSQL = $mSQL . "'" .$cursvalutar . "') ";

//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn,$query));
  }
	}
	else
	{
		 $cursvalutar=$rss["curs_valutar_valoare"];
	}	

		$clientcui=$_POST["factura_client_RO"]." ".$_POST["factura_client_CIF"];

$strWhereClause = " WHERE facturare_facturi.factura_ID=" . $_GET["cID"] . ";";
$query= "UPDATE facturare_facturi SET facturare_facturi.factura_client_ID='" .$Client_ID . "' ," ;
$query= $query . "facturare_facturi.factura_data_emiterii='" .$dataemiterii . "' ," ;
$query= $query . "facturare_facturi.factura_client_denumire='" .str_replace("'","&#39;",$_POST["factura_client_denumire"]) . "' ," ;
$query= $query . "facturare_facturi.factura_client_CUI='" .$clientcui . "' ," ;
$query= $query . "facturare_facturi.factura_client_RC='" .$_POST["factura_client_RC"] . "' ," ;
$query= $query . "facturare_facturi.factura_client_RO='" .$_POST["factura_client_RO"] . "' ," ;
$query= $query . "facturare_facturi.factura_cod_factura='" .$_POST["factura_cod_factura"] . "' ," ;
$query= $query . "facturare_facturi.factura_client_CIF='" .$_POST["factura_client_CIF"] . "' ," ;
$query= $query . "facturare_facturi.factura_client_adresa='" .$_POST["factura_client_adresa"] . "' ," ;
$query= $query . "facturare_facturi.factura_client_judet='" .$_POST["factura_client_judet"] . "' ," ;
$query= $query . "facturare_facturi.factura_client_localitate='" .$_POST["factura_client_localitate"] . "' ," ;
$query= $query . "facturare_facturi.factura_client_IBAN='" .$_POST["factura_client_IBAN"] . "' ," ;
$query= $query . "facturare_facturi.factura_client_banca='" .$_POST["factura_client_banca"] . "' ," ;
$query= $query . "facturare_facturi.factura_client_alocat='" .$_POST["factura_client_alocat"] . "' ," ;
$query= $query . "facturare_facturi.factura_client_contract='" .$_POST["factura_client_contract"] . "' ," ;
$query= $query . "facturare_facturi.factura_client_BU='" .$_POST["factura_client_BU"] . "' ," ;
$query= $query . "facturare_facturi.factura_client_sales='" .$_POST["factura_client_sales"] . "' ," ;
$query= $query . "facturare_facturi.factura_client_an='" .$_POST["factura_client_an"] . "' ," ;
$query= $query . "facturare_facturi.factura_client_termen='" .$termenfactura . "' ," ;
$query= $query . "facturare_facturi.factura_client_valoare='" .$valoareproduse . "' ," ;
$query= $query . "facturare_facturi.factura_client_valoare_tva='" .$valoareTVA . "' ," ;
$query= $query . "facturare_facturi.factura_client_curs_valutar='" .$cursvalutar . "' ," ;
$query= $query . "facturare_facturi.factura_client_valoare_totala='" .$grandtotal . "' ," ;
$query= $query . "facturare_facturi.factura_client_achitat='" .$_POST["factura_client_achitat"] . "' ," ;
$query= $query . "facturare_facturi.factura_client_inchisa='" .$closed . "' ," ;
$query= $query . "facturare_facturi.factura_stornata='" .$_POST["factura_stornata"] . "' ," ;
if ($_POST["factura_stornata_data"]!='')
{$query= $query . "facturare_facturi.factura_client_data_stornata='" .$_POST["factura_stornata_data"] . "' ," ;}
$query= $query . "facturare_facturi.factura_client_anulat='" .$anulat . "' ," ;
$query= $query . " facturare_facturi.factura_client_tip_activitate='" .$_POST["factura_client_tip_activitate"] . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
Else{
$nsql="DELETE FROM facturare_facturi WHERE factura_client_inchisa IS NULL;";
ezpub_query($conn,$nsql);

If (IsSet($_POST['factura_client_achitat']) AND $_POST['factura_client_achitat']=="1"){

$query1="Select chitanta_numar FROM facturare_chitante WHERE chitanta_inchisa='1' ORDER BY chitanta_numar DESC";
$result1=ezpub_query($conn,$query1);
$row1=ezpub_fetch_array($result1);
If (!isSet($row1["chitanta_numar"]))
{$numarchitanta=1;}
Else
{$numarchitanta=(int)$row1["chitanta_numar"]+1;}
$qsql2="SELECT factura_numar, factura_data_emiterii, factura_client_valoare_totala FROM facturare_facturi WHERE factura_ID=" . $_GET["cID"] . ";";
$result11=ezpub_query($conn,$qsql2);
$row11=ezpub_fetch_array($result11);
$datachitantei=$row11["factura_data_emiterii"];
$sumaincasata=$row11["factura_client_valoare_totala"];
$facturaincasata=$row11["factura_numar"];
$descriere = "Contravaloare factură ". $siteInvoicingCode ." 0000".$facturaincasata ."/".date("d.m.Y", strtotime($datachitantei));

$usql="UPDATE facturare_facturi SET factura_client_data_achitat='$datachitantei', factura_client_zile_achitat='0', factura_client_achitat_prin='0' WHERE factura_ID=$_GET[cID];";
ezpub_query($conn,$usql);

$mSQL = "INSERT INTO facturare_chitante(";
	$mSQL = $mSQL . "chitanta_data_incasarii,";
	$mSQL = $mSQL . "chitanta_factura_ID,";
	$mSQL = $mSQL . "chitanta_suma_incasata,";
	$mSQL = $mSQL . "chitanta_inchisa,";
	$mSQL = $mSQL . "chitanta_descriere,";
	$mSQL = $mSQL . "chitanta_numar)";
	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" . $datachitantei . "', ";
	$mSQL = $mSQL . "'" . $_GET["cID"]	. "', ";
	$mSQL = $mSQL . "'" . $sumaincasata . "', ";
	$mSQL = $mSQL . "'1', ";
	$mSQL = $mSQL . "'" . $descriere . "', ";
	$mSQL = $mSQL . "'" .$numarchitanta . "') ";		
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
}}
echo "<div class=\"callout success\"><p>$strRecordAdded</p>";
echo"			    <div class=\"grid-x grid-margin-x\">
			  <div class=\"large-12 medium-12 small-12 cell\">
			  <p>
			  <a href=\"invoice.php?cID=$_GET[cID]\" class=\"button\"><i class=\"fas fa-file-pdf\"></i>&nbsp;$strPrint</a>
			  <a href=\"emailinvoice.php?cID=$_GET[cID]\" class=\"button\"><i class=\"far fa-envelope\"></i>&nbsp;$strEmail</a>
			  </p>
</div>
</div>";

echo"</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteinvoices.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{
	// edit
$dataemiterii = $_POST["strEData3"] ."-". $_POST["strEData2"] ."-". $_POST["strEData1"] ."";

$query1="SELECT SUM(articol_valoare) AS valoare_factura FROM facturare_articole_facturi WHERE factura_ID=$_GET[cID]";
$result1=ezpub_query($conn,$query1);
$row1=ezpub_fetch_array($result1);
$valoareproduse=$row1["valoare_factura"];
$query2="SELECT SUM(articol_TVA) AS valoare_tva FROM facturare_articole_facturi WHERE factura_ID=$_GET[cID]";
$result2=ezpub_query($conn,$query2);
$row2=ezpub_fetch_array($result2);
$valoareTVA=$row2["valoare_tva"];
$query3="SELECT SUM(articol_total) AS grandtotal FROM facturare_articole_facturi WHERE factura_ID=$_GET[cID]";
$result3=ezpub_query($conn,$query3);
$row3=ezpub_fetch_array($result3);
$grandtotal=$row3["grandtotal"];

$query="SELECT ID_Client FROM clienti_date  WHERE Client_Denumire='$_POST[factura_client_denumire]'";
		$result=ezpub_query($conn,$query);
		$row=ezpub_fetch_array($result);
		$Client_ID=$row["ID_Client"];
		$clientcui=$_POST["factura_client_RO"]." ".$_POST["factura_client_CIF"];	
$strWhereClause = " WHERE facturare_facturi.factura_ID=" . $_GET["cID"] . ";";
$uquery= "UPDATE facturare_facturi SET facturare_facturi.factura_client_denumire='" .str_replace("'","&#39;",$_POST["factura_client_denumire"]) . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_data_emiterii='" .$dataemiterii . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_ID='" .$Client_ID . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_CUI='" .$clientcui . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_RO='" .$_POST["factura_client_RO"] . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_CIF='" .$_POST["factura_client_CIF"] . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_cod_factura='" .$_POST["factura_code_factura"] . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_RC='" .$_POST["factura_client_RC"] . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_adresa='" .$_POST["factura_client_adresa"] . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_judet='" .$_POST["factura_client_judet"] . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_localitate='" .$_POST["factura_client_localitate"] . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_IBAN='" .$_POST["factura_client_IBAN"] . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_banca='" .$_POST["factura_client_banca"] . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_contract='" .$_POST["factura_client_contract"] . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_alocat='" .$_POST["factura_client_alocat"] . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_an='" .$_POST["factura_client_an"] . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_termen='" .$_POST["factura_client_termen"] . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_BU='" .$_POST["factura_client_BU"] . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_sales='" .$_POST["factura_client_sales"] . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_stornata='" .$_POST["factura_stornata"] . "' ," ;
if ($_POST["factura_stornata_data"]!='')
{$query= $query . "facturare_facturi.factura_client_data_stornata='" .$_POST["factura_stornata_data"] . "' ," ;}
$uquery= $uquery . "facturare_facturi.factura_client_valoare='" .$valoareproduse . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_valoare_tva='" .$valoareTVA . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_valoare_totala='" .$grandtotal . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_pdf=NULL," ;
$uquery= $uquery . "facturare_facturi.factura_client_pdf_generat=Null," ;
$uquery= $uquery . "facturare_facturi.factura_client_efactura_generata=Null," ;
$uquery= $uquery . " facturare_facturi.factura_client_tip_activitate='" .$_POST["factura_client_tip_activitate"] . "' "; 
$uquery= $uquery . $strWhereClause;
if (!ezpub_query($conn,$uquery))
  {
  echo $uquery;
  die('Error: ' . ezpub_error($conn));
  }
Else{
	$nsql="DELETE FROM efactura WHERE factura_ID=" . $_GET["cID"] . ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\"><p>$strRecordModified</p>";
echo"			    <div class=\"grid-x grid-margin-x\">
			  <div class=\"large-12 medium-12 small-12 cell\">
			  <p>
			  <a href=\"invoice.php?cID=$_GET[cID]\" class=\"button\"><i class=\"fas fa-file-pdf\"></i>&nbsp;$strPrint</a>
			  <a href=\"emailinvoice.php?cID=$_GET[cID]\" class=\"button\"><i class=\"far fa-envelope\"></i>&nbsp;$strEmail</a>
			  </p>
</div>
</div>";

echo"</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteinvoices.php\"
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
	If (IsSet($_GET['proforma']) AND $_GET['proforma']=="0")
	{
		$tipfactura='0';
	}
	else
		{
			$tipfactura='1';
	}	
$query="Select factura_numar FROM facturare_facturi WHERE factura_client_inchisa='1' AND factura_tip='0' ORDER BY CAST(factura_numar AS unsigned) DESC";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
If (!isSet($row["factura_numar"]))
{$numarfactura=1;}
Else
{$numarfactura=(int)$row["factura_numar"]+1;}

$mSQL = "INSERT INTO facturare_facturi(";
	$mSQL = $mSQL . "factura_tip,";
	$mSQL = $mSQL . "factura_numar)";
	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" . $tipfactura . "', ";
	$mSQL = $mSQL . "'" .$numarfactura . "') ";		
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
	$invoiceID=ezpub_inserted_id($conn);
}
?>
<script src="<?php echo $strSiteURL ?>js/foundation/jquery.js"></script>
<script language="JavaScript" type="text/JavaScript">
$(document).ready(function() {
    $("#btn1").click(function() {  
	jQuery.ajax({
	url: "cui.php",
	dataType: "json",
	data:'Cui='+$("#Cui").val(),
	type: "POST",
	  success: function(data) {
		  try {
           $('#factura_client_denumire').val((data["denumire"] || "").toUpperCase());
           $("#factura_client_CIF").val(data["cif"]);
           $("#factura_client_RO").val(data["tva"]);
           $("#factura_client_adresa").val(data["adresa"]);
           $("#factura_client_judet").val((data["judet"]).toUpperCase());
	     $("#factura_client_localitate").val((data["oras"]).toUpperCase());
           $("#factura_client_RC").val(data["numar_reg_com"]);
		   document.getElementById('nou').checked=true;
		   $("#loaderIcon").hide();   
		   }
catch(err) {
  document.getElementById("response").innerHTML = err.message;
}
        },
        error : function(){
           alert('Nu se poate face legătura cu serverul ANAF!');
        }
    });
});
});
</script>

<script language="JavaScript" type="text/JavaScript">
$(document).ready(function() {
    $('select[name="factura_client_ID"]').change(function() {  
	jQuery.ajax({
	url: "invoice_client.php",
	dataType: "json",
	data:'Client_ID='+$(this).val(),
	type: "POST",
	  success: function(data) {
		  try {
           $('#factura_client_denumire').val((data["denumire"] || "").toUpperCase());
           $("#factura_client_CIF").val(data["cif"]);
           $("#factura_client_RO").val(data["tva"]);
           $("#factura_client_adresa").val(data["adresa"]);
           $("#factura_client_judet").val((data["judet"]).toUpperCase());
           $("#factura_client_localitate").val((data["localitate"]).toUpperCase());
           $("#factura_client_banca").val(data["banca"]);
           $("#factura_client_IBAN").val(data["iban"]);
           $("#factura_client_RC").val(data["numar_reg_com"]);  
		   }
catch(err) {
  document.getElementById("response").innerHTML = err.message;
}
        },
        error : function(){
           alert('Clientul nu a fost găsit!');
        }
    });
});
});
</script>
   <div class="grid-x grid-padding-x ">
              <div class="large-6 medium-6 cell">
<label><?php echo $strClient?></label>
<select name="factura_client_ID" onClick="document.getElementById('existent').checked=true;">
           <option value=""><?php echo $strClient?></option>
          <?php $sql = "Select * FROM clienti_date ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
          <?php
}?>
        </select>
			  </div>
<div class="large-6 medium-6 cell">
<label><?php echo $strCompanyVAT?></label>
<div id="response"></div>
<div class="input-group">
  <span class="input-group-label"><?php echo $strCompanyVAT?></span>
  <input class="input-group-field" type="text" name="Cui" id="Cui" placeholder="<?php echo $strEnterVATNumber?>">
  <div class="input-group-button">
    <button id="btn1" class="button success" ><i class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
  </div>
</div>			  
			  </div>
			  </div>
<form Method="post" id="users" Action="siteinvoices.php?mode=new&cID=<?php echo $invoiceID?>" >
	   <div class="grid-x grid-padding-x ">
              <div class="large-3 medium-3 cell">
                <label><?php echo $strType?></label>
                <input type="radio" name="existent" value="1" id="existent"><label for="existent"><?php echo $strExistingClient?></label>
                <input type="radio" name="existent" value="0" id="nou"><label for="nou"><?php echo $strNewClient?></label>
              </div>  
	<div class="large-3 medium-3 cell">
                <label><?php echo $strContractType?></label>
                <input type="radio" name="factura_client_tip_activitate" value="M" id="lunar"><label for="lunar"><?php echo $strSubscribtion?></label>
                <input type="radio" name="factura_client_tip_activitate" value="O" id="onetime" checked><label for="onetime"><?php echo $strOneTimeJob?></label>
                </div>
				  <div class="large-3 medium-3 small-3 cell"> 
	      <label><?php echo $strContract?></label>
			<input name="factura_client_contract" Type="text"  value="" />
	</div>
					  <div class="large-3 medium-3 small-3 cell"> 
	      <label><?php echo $strCode?></label>
			<input name="factura_cod_factura" Type="text"  value="380" />
	</div>
            </div>
  <div class="grid-x grid-padding-x ">
              <div class="large-2 medium-2 small-2 cell">
			  <label><?php echo $strReceipt?></label>
			  <input type="radio" name="factura_client_achitat" value="0" checked id="chitanta"><label for="chitanta"><?php echo $strNo?></label><input name="factura_client_achitat" Type="radio" value="1" id="banca"><label for="banca"><?php echo $strYes?></label>
			  </div>
			   <div class="large-2 medium-2 small-2 cell">
			   <label><?php echo $strNumber?></label>
			  <input name="factura_numar" Type="text"  value="<?php echo $siteInvoicingCode . "0000".$numarfactura?>" />
			  </div>
			     <div class="large-2 medium-2 cell">
			   <label><?php echo $strDeadline?></label>
			  <input name="factura_client_termen" Type="text"  value="10" />
			  </div>	
			  <div class="large-2 medium-2 cell">
			   <label><?php echo $strStornedInvoice?></label>
			  <input name="factura_stornata" Type="text"  />
			  </div>	
			  <div class="large-4 medium-4 cell">
			   <label><?php echo $strStornedInvoiceDate?></label>
			  <input name="factura_stornata_data" Type="date"   />
			  </div>
		   </div>
              <div class="grid-x grid-padding-x ">
              <div class="large-1 medium-1 small-1 cell ">
			 <label> <?php echo $strDay?></label>
      <select name="strEData1">
	  <option value="00" selected>--</option>
<?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
    		
    		// create option With numeric value of day
    		if ($day==$d){
    		echo "<OPTION selected value=\"$d\">$d</OPTION>";}
			else {echo "<OPTION value=\"$d\">$d</OPTION>";}
			} 
?>
        </select> 
		</div>
		  <div class="large-2 medium-2 small-2 cell ">
		 <label> <?php echo $strMonth?></label>
		<select name="strEData2">
	<option value="00" selected>--</option>
         <?php for ( $m = 1; $m <= 12; $m ++) {
    		
   		//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
    			if ($month==$m){
    			echo "<OPTION selected value=\"$m\">$monthname</OPTION>";}
				Else
				{echo "<OPTION value=\"$m\">$monthname</OPTION>";}
				} 
			?>
        </select> 
		</div>
		  <div class="large-1 medium-1 small-1 cell ">
		 <label> <?php echo $strYear?></label>
		<select name="strEData3">
		<option value="0000" selected>--</option>
		<?php
		$cy=date("Y");
		$fy=$cy+1;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
    			if ($year==$y){
    	echo "<OPTION selected value=\"$y\">$y</OPTION>";}
		Else{
		echo "<OPTION value=\"$y\">$y</OPTION>";
		}
		} 
			?>
        </select>
		</div>
              <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strClient?></label>
                <input type="text"  name="factura_client_denumire" id="factura_client_denumire" value=""/>
				</div>
				<div class="large-1 medium-1 small-1 cell">
                <label><?php echo $strCompanyFA?></label>
                <input type="text"  name="factura_client_RO" id="factura_client_RO" value=""/>
				</div>				
				<div class="large-2 medium-2 small2 cell">
                <label><?php echo $strCompanyVAT?></label>
                <input type="text"  name="factura_client_CIF" id="factura_client_CIF" value=""/>
				</div>
				<div class="large-2 medium-2  small-2 cell">
                <label><?php echo $strCompanyRC?></label>
                <input type="text"  name="factura_client_RC" id="factura_client_RC" value=""/>
				</div>
				</div>
			  <div class="grid-x grid-padding-x ">
              <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strSeenBy?></label>
                <select name="factura_client_alocat" class="required">
           <option value=""><?php echo $strUser?></option>
          <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["utilizator_Code"]?>"><?php echo $rss["utilizator_Prenume"]?> <?php echo $rss["utilizator_Nume"]?></option>
          <?php
}?>
        </select>
				</div>          
				<div class="large-3 medium-3 small-2 cell">
                <label><?php echo $strSales?></label>
                <select name="factura_client_sales" class="required">
           <option value=""><?php echo $strUser?></option>
          <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["utilizator_Code"]?>"><?php echo $rss["utilizator_Prenume"]?> <?php echo $rss["utilizator_Nume"]?></option>
          <?php
}?>
        </select>
				</div>
										  					<div class="large-3 medium-3 small-3 cell">
<label><?php echo $strBusinessUnit?></label>					
	<select name="factura_client_BU">
				 <?php
			 			$query7="SELECT DISTINCT factura_client_BU FROM facturare_facturi ORDER By factura_client_BU ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
echo"<option value=\"$seenby[factura_client_BU]\">". $seenby['factura_client_BU']."</option>";
			}
		?></select>
		</div>
				<div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strYear?></label>
                <input type="text"  name="factura_client_an" id="factura_client_an" value=""/>
				</div>
				
				</div>

			  <div class="grid-x grid-padding-x ">
               <div class="large-2 medium-2 small-2 cell">
			   <label><?php echo $strAddress?></label>
			  <input type="text"  name="factura_client_adresa" id="factura_client_adresa" value=""/>
</div>	
               <div class="large-2 medium-2 small-2 cell">
			   <label><?php echo $strCity?></label>
			  <input type="text"  name="factura_client_localitate" id="factura_client_localitate" value=""/>
</div>            
				<div class="large-2 medium-2 small-2 cell">
			   <label><?php echo $strCounty?></label>
			  <input type="text"  name="factura_client_judet" id="factura_client_judet" value=""/>
</div>			  

               <div class="large-3 medium-3 small-3 cell">
			   <label><?php echo $strBank?></label>
			  <input type="text"  name="factura_client_banca" id="factura_client_banca" value=""/>
</div>	
               <div class="large-3 medium-3 small-3 cell">
			   <label><?php echo $strCompanyIBAN?></label>
			  <input type="text"  name="factura_client_IBAN" id="factura_client_IBAN" value=""/>
</div>			  
</div>	
	  <div class="grid-x grid-padding-x">
              <div class="large-12 cell">	
		<iframe width="100%" height="600" src="siteinvoiceitems.php?valuta=lei&cID=<?php echo $invoiceID?>" frameBorder="0" scrolling="no" onload="resizeIframe(this)" id="lei" ></iframe>
	</div>
	</div>
		             <div class="grid-x grid-padding-x ">
              <div class="large-12 text-center cell">
			  <input type="submit" value="<?php echo $strAdd?>" class="button" name="Submit"> 
		</div>
		</div>
	
  </form>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM facturare_facturi WHERE factura_ID=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>

<script src="<?php echo $strSiteURL ?>js/foundation/jquery.js"></script>
<script language="JavaScript" type="text/JavaScript">
$(document).ready(function() {
    $("#btn1").click(function() {  
	jQuery.ajax({
	url: "cui.php",
	dataType: "json",
	data:'Cui='+$("#Cui").val(),
	type: "POST",
	  success: function(data) {
		  try {
           $('#factura_client_denumire').val((data["denumire"] || "").toUpperCase());
           $("#factura_client_CIF").val(data["cif"]);
           $("#factura_client_RO").val(data["tva"]);
           $("#factura_client_adresa").val(data["adresa"]);
           $("#factura_client_judet").val((data["judet"]).toUpperCase());
           $("#factura_client_RC").val(data["numar_reg_com"]);
		   $("#loaderIcon").hide();   
		   }
catch(err) {
  document.getElementById("response").innerHTML = err.message;
}
        },
        error : function(){
           alert('Nu se poate face legătura cu serverul ANAF!');
        }
    });
});
});
</script>

<script language="JavaScript" type="text/JavaScript">
$(document).ready(function() {
    $('select[name="factura_client_ID"]').change(function() {  
	jQuery.ajax({
	url: "invoice_client.php",
	dataType: "json",
	data:'Client_ID='+$(this).val(),
	type: "POST",
	  success: function(data) {
		  try {
           $('#factura_client_denumire').val((data["denumire"] || "").toUpperCase());
           $("#factura_client_CIF").val(data["cif"]);
           $("#factura_client_RO").val(data["tva"]);
           $("#factura_client_adresa").val(data["adresa"]);
           $("#factura_client_judet").val((data["judet"]).toUpperCase());
           $("#factura_client_localitate").val((data["localitate"]).toUpperCase());
           $("#factura_client_banca").val(data["banca"]);
           $("#factura_client_IBAN").val(data["iban"]);
           $("#factura_client_RC").val(data["numar_reg_com"]);  
		   }
catch(err) {
  document.getElementById("response").innerHTML = err.message;
}
        },
        error : function(){
           alert('Clientul nu a fost găsit!');
        }
    });
});
});
</script>
  <div class="grid-x grid-padding-x "><div class="large-12 medium-12 cell"><h2><?php echo $strInvoice . " ".$siteInvoicingCode ."0000" .$row["factura_numar"]?></h2></div></div>
<fieldset>
   <div class="grid-x grid-padding-x ">
              <div class="large-6 medium-6 cell">
<label><?php echo $strClient?></label>
<select name="factura_client_ID">
           <option value=""><?php echo $strClient?></option>
          <?php $sql = "Select * FROM clienti_date ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
          <?php
}?>
        </select>
			  </div>
<div class="large-6 medium-6 cell">
<label><?php echo $strCompanyVAT?></label>
<div id="response"></div>
<input type="text" name="Cui" id="Cui" class="required" /> <button id="btn1" class="button success" ><?php echo $strCheck ?></button><img src="../immg/LoaderIcon.gif" id="loaderIcon" style="display:none" />
<div id="suggesstion-box" class="suggesstion-box"></div>			  
			  </div>
			  </div>
<form Method="post" id="users" Action="siteinvoices.php?mode=edit&cID=<?php echo $_GET["cID"]?>" >
		  <div class="grid-x grid-padding-x ">
			   <div class="large-1 medium-1  small-1 cell ">
			   <label><?php echo $strNumber?></label>
			  <input name="factura_numar" Type="text"  value="<?php echo $siteInvoicingCode ."0000".$row["factura_numar"]?>" readonly />
			  </div>			   
			   <div class="large-1 medium-1  small-1 cell ">
			   <label><?php echo $strCode?></label>
			  <input name="factura_cod_factura" Type="text"  value="<?php echo $row["factura_cod_facturar"]?>" />
			  </div>			   
			  <div class="large-1 medium-1 small-1 cell">
			   <label><?php echo $strContract?></label>
			  <input name="factura_client_contract" Type="text"  value="<?php echo $row["factura_client_contract"]?>" />
			  </div>
			     <div class="large-1 medium-1 small-1 cell">
			   <label><?php echo $strDeadline?></label>
			  <input name="factura_client_termen" Type="text"  value="<?php echo $row["factura_client_termen"]?>" />
			  </div>
			   <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strContractType?></label>
                <input type="radio" name="factura_client_tip_activitate" value="M" <?php IF ($row["factura_client_tip_activitate"]=='M') echo "checked"?> id="lunar"><label for="lunar"><?php echo $strSubscribtion?></label>
                <input type="radio" name="factura_client_tip_activitate" value="O" <?php IF ($row["factura_client_tip_activitate"]=='O') echo "checked"?>id="onetime" checked><label for="onetime"><?php echo $strOneTimeJob?></label>
                </div>
							     <div class="large-2 medium-2 small-2 cell">
			   <label><?php echo $strStornedInvoice?></label>
			  <input name="factura_stornata" Type="text"  value="<?php echo $row["factura_stornata"]?>" />
			  </div>
			  							     <div class="large-3 medium-3 small-3 cell">
			   <label><?php echo $strStornedInvoiceDate?></label>
			  <input name="factura_stornata_data" Type="date"  value="<?php echo $row["factura_stornata_data"]?>" />
			  </div>
        
			  </div>
              <div class="grid-x grid-padding-x ">
              <div class="large-1 medium-1 small-1 cell">
			 <label> <?php echo $strDay?></label>
      <select name="strEData1">
	  <option value="00" selected>--</option>
<?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
    		
    		// create option With numeric value of day
			$day=date("d", strtotime($row['factura_data_emiterii']));
    		if ($day==$d){
    		echo "<OPTION selected value=\"$d\">$d</OPTION>";}
			else {echo "<OPTION value=\"$d\">$d</OPTION>";}
			} 
?>
        </select> 
		</div>
		 <div class="large-2 medium-2 small-2 cell">
		 <label> <?php echo $strMonth?></label>
		<select name="strEData2">
	<option value="00" selected>--</option>
         <?php for ( $m = 1; $m <= 12; $m ++) {
    		
     		//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
    			if ($month==$m){
    			echo "<OPTION selected value=\"$m\">$monthname</OPTION>";}
				Else
				{echo "<OPTION value=\"$m\">$monthname</OPTION>";}
				} 
			?>
        </select> 
		</div>
		 <div class="large-1 medium-1 small-1 cell">
		 <label> <?php echo $strYear?></label>
		<select name="strEData3">
		<option value="0000" selected>--</option>
		<?php
		$cy=date("Y");
		$fy=$cy+1;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
			$year=date("Y", strtotime($row['factura_data_emiterii']));
    			if ($year==$y){
    	echo "<OPTION selected value=\"$y\">$y</OPTION>";}
		Else{
		echo "<OPTION value=\"$y\">$y</OPTION>";
		}
		} 
			?>
        </select>
		</div>
              <div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strSeenBy?></label>
                <select name="factura_client_alocat" class="required">
           <option value=""><?php echo $strUser?></option>
          <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["utilizator_Code"]?>" <?php if ($row["factura_client_alocat"]==$rss["utilizator_Code"]) echo "selected"?>><?php echo $rss["utilizator_Prenume"]?> <?php echo $rss["utilizator_Nume"]?></option>
          <?php
}?>
        </select>
				</div>           
				<div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strSales?></label>
                <select name="factura_client_sales" class="required">
           <option value=""><?php echo $strUser?></option>
          <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["utilizator_Code"]?>" <?php if ($row["factura_client_sales"]==$rss["utilizator_Code"]) echo "selected"?>><?php echo $rss["utilizator_Prenume"]?> <?php echo $rss["utilizator_Nume"]?></option>
          <?php
}?>
        </select>
				</div>
				<div class="large-2 medium-2 small-2 cell">
				<label><?php echo $strBusinessUnit?></label>					
	<select name="factura_client_BU">
				 <?php
			 			$query7="SELECT DISTINCT factura_client_BU FROM facturare_facturi ORDER By factura_client_BU ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
				if($row["factura_client_BU"]==$seenby["factura_client_BU"])
				{			echo"<option selected value=\"$seenby[factura_client_BU]\">". $seenby['factura_client_BU']."</option>";}
			Else
				{			echo"<option value=\"$seenby[factura_client_BU]\">". $seenby['factura_client_BU']."</option>";}
			}
		?></select>
				</div>
				<div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strYear?></label>
                <input type="text"  name="factura_client_an" id="factura_client_an" value="<?php echo $row["factura_client_an"]?>"/>
				</div>
				
				</div>

			  <div class="grid-x grid-padding-x ">
			        <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strClient?></label>
                <input type="text"  name="factura_client_denumire" id="factura_client_denumire" value="<?php echo $row["factura_client_denumire"]?>"/>
				</div>
				<div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strCompanyFA?></label>
                <input type="text"  name="factura_client_RO" id="factura_client_RO" value="<?php echo $row["factura_client_RO"]?>"/>
				</div>			
				<div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strCompanyVAT?></label>
                <input type="text"  name="factura_client_CIF" id="factura_client_CIF" value="<?php echo $row["factura_client_CIF"]?>"/>
				</div>
				<div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strCompanyRC?></label>
                <input type="text"  name="factura_client_RC" id="factura_client_RC" value="<?php echo $row["factura_client_RC"]?>"/>
				</div>
				</div>
						  <div class="grid-x grid-padding-x ">	
               <div class="large-2 medium-2 small-2 cell">
			   <label><?php echo $strAddress?></label>
			  <input type="text"  name="factura_client_adresa" id="factura_client_adresa" value="<?php echo $row["factura_client_adresa"]?>"/>
</div>	            
			<div class="large-2 medium-2 small-2 cell">
			   <label><?php echo $strCity?></label>
			  <input type="text"  name="factura_client_localitate" id="factura_client_localitate" value="<?php echo $row["factura_client_localitate"]?>"/>
</div>	
               <div class="large-2 medium-2 small-2 cell">
			   <label><?php echo $strCounty?></label>
			  <input type="text"  name="factura_client_judet" id="factura_client_judet" value="<?php echo $row["factura_client_judet"]?>"/>
</div>			  

               <div class="large-3 medium-3 small-3 cell">
			   <label><?php echo $strBank?></label>
			  <input type="text"  name="factura_client_banca" id="factura_client_banca" value="<?php echo $row["factura_client_banca"]?>"/>
</div>	
               <div class="large-3 medium-3 small-3 cell">
			   <label><?php echo $strCompanyIBAN?></label>
			  <input type="text"  name="factura_client_IBAN" id="factura_client_IBAN" value="<?php echo $row["factura_client_IBAN"]?>"/>
</div>			  
</div>	  
			
	  <div class="grid-x grid-padding-x ">
              <div class="large-12 cell">
		<iframe name="articole" width="100%" height="400px" src="siteinvoiceitems.php?cID=<?php echo $_GET["cID"]?>" frameBorder="0" scrolling="no" onload="resizeIframe(this)"></iframe>
	</div>
	</div>
	             <div class="grid-x grid-padding-x ">
              <div class="large-12 text-center cell">
			  <input type="submit" value="<?php echo $strModify?>" class="button" name="Submit" class="button success"> 
		</div>
		</div>
  </form>
<?php
}
Else // display invoices
{
?>
		 <script language="JavaScript" type="text/JavaScript">
<!-- jump menu
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
</script> 
		 		<div class="grid-x grid-padding-x ">
               <div class="large-2 medium-2 cell">
			   <label> <?php echo $strSeenBy?></label>
		 					<select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
							<option value="siteinvoices.php?act=<?php echo $act?>&cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&paid=<?php echo $paid?>&aloc=<?php echo $aloc ?>" selected><?php echo $strPick?></option>
			<?php
			$query7="SELECT * FROM date_utilizatori ORDER By utilizator_Nume ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			if ((isset($_GET['aloc'])) && !empty($_GET['aloc'])){
			If ($seenby['strSeenBy']==$_GET['aloc']) {
			echo"<option selected value=\"siteinvoices.php?act=$act&cl=$cl&fmonth=$fmonth&yr=$year&paid=$paid&aloc=".$seenby['utilizator_Code']."\">". $seenby['strUserName']."</option>";}
			Else{echo"<option value=\"siteinvoices.php?act=$act&cl=$cl&fmonth=$fmonth&yr=$year&paid=$paid&aloc=".$seenby['utilizator_Code']."\">". $seenby['utilizator_Nume']." ". $seenby['utilizator_Prenume']."</option>";}}
			Else {echo"<option value=\"siteinvoices.php?act=$act&cl=$cl&fmonth=$fmonth&yr=$year&paid=$paid&aloc=".$seenby['utilizator_Code']."\">". $seenby['utilizator_Nume']." ". $seenby['utilizator_Prenume']."</option>";}
			}
			?>
			</select>
			</div>
			 <div class="large-2 medium-2 cell">
			 <label> <?php echo $strClient?></label>
			 <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
<option value="siteinvoices.php?act=<?php echo $act?>&cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&paid=<?php echo $paid?>&aloc=<?php echo $aloc ?>" selected><?php echo $strPick?></option>
			<?php
			$query7="SELECT DISTINCT factura_client_denumire, factura_client_ID FROM facturare_facturi ORDER By factura_client_denumire ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			if ((isset($_GET['cl'])) && !empty($_GET['cl'])){
			If ($seenby['factura_client_ID']==$_GET['cl']) {
			echo"<option selected value=\"siteinvoices.php?act=$act&aloc=$aloc&fmonth=$fmonth&yr=$year&paid=$paid&cl=".$seenby['factura_client_ID']."\">". $seenby['factura_client_denumire']."</option>";}
			Else{echo"<option value=\"siteinvoices.php?act=$act&aloc=$aloc&fmonth=$fmonth&yr=$year&paid=$paid&cl=".$seenby['factura_client_ID']."\">". $seenby['factura_client_denumire']."</option>";}}
			Else {echo"<option value=\"siteinvoices.php?act=$act&aloc=$aloc&fmonth=$fmonth&yr=$year&paid=$paid&cl=".$seenby['factura_client_ID']."\">". $seenby['factura_client_denumire']."</option>";}
			}
			?>
			</select>
			</div>
							 <div class="large-2 medium-2 cell">
			<label> <?php echo $strMonth?></label>
			 <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
	<option value="00" selected>--</option>
         <?php for ( $m = 1; $m <= 12; $m ++) {

     		//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
		$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
				echo "<option value=\"siteinvoices.php?act=$act&aloc=$aloc&cl=$cl&yr=$year&paid=$paid&fmonth=".$m."\">$monthname</option>";}
				 
			?>
        </select> 
		</div>
				 <div class="large-2 medium-2 cell">
		 <label> <?php echo $strYear?></label>
			 <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
			<option value="siteinvoices.php?act=<?php echo $act?>&cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&paid=<?php echo $paid?>&aloc=<?php echo $aloc ?>" selected><?php echo $strPick?></option>
			 <?php
			 			$query7="SELECT DISTINCT YEAR(factura_data_emiterii) as iyear FROM facturare_facturi ORDER By YEAR(factura_data_emiterii) DESC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
						if ((isset($_GET['yr'])) && !empty($_GET['yr'])){
			If ($seenby['iyear']==$_GET['yr']) {
			echo"<option selected value=\"siteinvoices.php?act=$act&aloc=$aloc&cl$cl&fmonth=$fmonth&paid=$paid&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}
			Else{echo"<option value=\"siteinvoices.php?act=$act&aloc=$aloc&cl=$cl&paid=$paid&fmonth=$fmonth&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}}
			else {
			if ($year==$seenby['iyear']) 
			{echo "<option value=\"siteinvoices.php?act=$act&aloc=$aloc&cl=$cl&paid=$paid&fmonth=$fmonth&yr=".$seenby['iyear']." \" selected >". $seenby['iyear']."</option>";}
			Else {echo"<option value=\"siteinvoices.php?act=$act&aloc=$aloc&cl=$cl&paid=$paid&fmonth=$fmonth&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}
			}
			}
			 ?>
        </select>
		</div>
               <div class="large-2 medium-2 cell">
			   <label> <?php echo $strPaid?></label>
		 					<select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
						<option value="siteinvoices.php?act=<?php echo $act?>&cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&paid=<?php echo $paid?>&aloc=<?php echo $aloc ?>" selected><?php echo $strPick?></option>
							<?php
							echo"<option value=\"siteinvoices.php?act=$act&aloc=$aloc&cl=$cl&fmonth=$fmonth&yr=$year&paid=1\">$strYes</option>";
							echo"<option value=\"siteinvoices.php?act=$act&aloc=$aloc&cl=$cl&fmonth=$fmonth&yr=$year&paid=0\">$strNo</option>";
							
							
		?></select>
		</div>
		               <div class="large-2 medium-2 cell">
			   <label> <?php echo $strBusinessUnit?></label>
		 					<select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
						<option value="siteinvoices.php?act=<?php echo $act?>&cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&paid=<?php echo $paid?>&aloc=<?php echo $aloc ?>" selected><?php echo $strPick?></option>
			 <?php
			 			$query7="SELECT DISTINCT factura_client_BU FROM facturare_facturi ORDER By factura_client_BU ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
						if ((isset($_GET['act'])) && !empty($_GET['act'])){
			If ($seenby['factura_client_BU']==$_GET['act']) {
			echo"<option selected value=\"siteinvoices.php?aloc=$aloc&cl=$cl&fmonth=$fmonth&paid=$paid&yr=$year&act=".$seenby['factura_client_BU']."\">". $seenby['factura_client_BU']."</option>";}
			Else{echo"<option value=\"siteinvoices.php?aloc=$aloc&cl=$cl&paid=$paid&fmonth=$fmonth&yr=$year&act=".$seenby['factura_client_BU']."\">". $seenby['factura_client_BU']."</option>";}}
			Else {echo"<option value=\"siteinvoices.php?aloc=$aloc&cl=$cl&paid=$paid&fmonth=$fmonth&yr=$year&act=".$seenby['factura_client_BU']."\">". $seenby['factura_client_BU']."</option>";}
			}
		?></select>
		</div>
		</div>
			
	<?php
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"siteinvoices.php?mode=new&proforma=0\" class=\"button\"><i class=\"large fa fa-plus\" title=\"$strAdd\"></i>$strNewInvoice</a>
<a href=\"siteinvoices.php?mode=new&proforma=1\" class=\"button\"><i class=\"large fa fa-plus\" title=\"$strAdd\"></i>$strNewProforma</a>
</div></div>";
echo "<div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">";
	 ?>
<ul class="tabs" data-deep-link="true" data-update-history="true" data-deep-link-smudge="true" data-deep-link-smudge-delay="500" data-tabs id="invoices">
  <li class="tabs-title is-active"><a href="siteinvoices.php#panel1" aria-selected="true"><?php echo $strInvoices?></a></li>
  <li class="tabs-title"><a href="siteinvoices.php#panel2"><?php echo $strProformas?></a></li>
</ul>
<div class="tabs-content" data-tabs-content="invoices">
<div class="tabs-panel is-active" id="panel1">
	 <?php
$query="SELECT *FROM facturare_facturi
WHERE YEAR(factura_data_emiterii)='$year' AND factura_tip='0'";
if ($aloc!='0'){
$query= $query . " AND factura_client_alocat='$aloc'";
};
if ($act!='0'){
$query= $query . " AND factura_client_BU='$act'";
};
if ($cl!='0'){
$query= $query . " AND factura_client_ID='$cl'";
};
if ($fmonth!='0'){
$query= $query . " AND MONTH(factura_data_emiterii)='$fmonth'";
};
if ($paid!='3'){
$query= $query . " AND factura_client_achitat='$paid'";
};
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY cast(factura_numar as unsigned) ASC $pages->limit";
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
$queryp="SELECT COUNT(factura_client_achitat) AS neplatite FROM facturare_facturi WHERE factura_client_achitat=0 AND factura_tip=0;"; 
$resultp=ezpub_query($conn,$queryp);
$rowp=ezpub_fetch_array($resultp);
$unpaid=$rowp["neplatite"];
echo $strTotal . " " .$numar." ".$strInvoices ." / ". $unpaid ." ". $strUnpayed;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"siteinvoices.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
</div>
<table width="100%" class="unstriped">
	      <thead>
    	<tr>
			<th><?php echo $strNumber?></th>
			<th><?php echo $strIssuedDate?></th>
			<th><?php echo $strClient?></th>
			<th><?php echo $strTotal?></th>
			<th><?php echo $strValue?></th>
			<th><?php echo $strVAT?></th>
			<th><?php echo $strPaymentDate?></th>
			<th><?php echo $strDays?></th>
			<th><?php echo $strSeenBy?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strCancel?></th>
			<th><?php echo $strDetails?></th>
			<th><?php echo $strCashin?></th>
			<th><?php echo $strEmail?></th>
			<th><?php echo $strXML?></th>
			<th><?php echo $strUploaded?></th>
	      </tr>
		</thead>
<?php 
While ($row=ezpub_fetch_array($result)){
		If ($row["factura_client_anulat"]=="1") 
	{echo "<tr class=\"canceled\">";}
Else
{
	If ($row["factura_client_achitat"]=="0") 
	{
echo "<tr class=\"notpaid\">";
} 
Else 
{
echo "<tr class=\"paid\">";}
}
    		echo"<td>$row[factura_numar]</td>
			<td>". date("d.m.Y",strtotime($row["factura_data_emiterii"]))."</td>
			<td width=\"15%\">$row[factura_client_denumire]</td>
			<td align=\"right\">". romanize($row["factura_client_valoare_totala"])."</td>
			<td align=\"right\">". romanize($row["factura_client_valoare"])."</td>
			<td align=\"right\">". romanize($row["factura_client_valoare_tva"])."</td>";
If ($row["factura_client_achitat"]=="1") {
	echo "<td>". date("d.m.Y", strtotime($row["factura_client_data_achitat"]))."</td>";
}	
else 	{
	echo "<td>&nbsp;</td>";
}	
	echo	"<td>$row[factura_client_zile_achitat]</td>
			<td>$row[factura_client_alocat]</td>";
If ($row["factura_client_achitat"]=="0") {
echo "<td><a href=\"siteinvoices.php?mode=edit&cID=$row[factura_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></a></td>";}
else {
	echo "<td><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></td>";}
If ($row["factura_client_anulat"]=="0") 
{
echo "<td><a href=\"cancelinvoice.php?cID=$row[factura_ID]\" ><i class=\"large fas fa-ban\" title=\"$strCancel\"></a></td>";
}
else {
	echo "<td><i class=\"large fas fa-ban\" title=\"$strCancel\"></td>";
	}
			  if ($row["factura_client_pdf"]=='')
			 {
			echo "<td><a href=\"invoice.php?cID=$row[factura_ID]\"><i class=\"far fa-file\" title=\"$strView\"></i></a></td>";
			 }
			 Else
			 {
				 echo "<td><a href=\"invoice.php?cID=$row[factura_ID]\"><i class=\"far fa-file-pdf\" title=\"$strView\"></i></a></td>";
			 }
			if ($row["factura_client_achitat"]==0)
			 {
		 echo "<td><a href=\"sitecashin.php?cID=$row[factura_ID]\"><i class=\"fas fa-money-bill-alt\" title=\"$strCashin\"></i></a></td>";
			 }
		 Else {
		 echo "<td color=\"green\"><i class=\"fas fa-money-bill\" title=\"$strCashin\"></i></td>";
		 }
		echo	 "<td><a href=\"emailinvoice.php?cID=$row[factura_ID]\"><i class=\"far fa-envelope\" title=\"$strEmail\"></i></a></td>";
		If ($row["factura_client_efactura_generata"]=='DA')
{   echo     "<td><i class=\"far fa-file-excel\" title=\"$strXML\"></i></td>";}
	Else
{   echo     "<td><a href=\"einvoice.php?cID=$row[factura_ID]\"><i class=\"far fa-file-excel\" title=\"$strXML\"></i></a></td>";}
      echo "<td>$row[factura_client_efactura_generata]</td>
		</tr>";
}
echo "</table>";
}
?>
<div class="paginate">
<?php
echo $pages->display_pages() . " <a href=\"siteinvoices.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
</div>
</div>
<div class="tabs-panel" id="panel2">
	 <?php
$pquery="SELECT * FROM facturare_facturi
WHERE YEAR(factura_data_emiterii)='$year' AND factura_tip='1'";
if ($cl!='0'){
$pquery= $pquery . " AND factura_client_ID='$cl'";
};
if ($fmonth!='0'){
$pquery= $pquery . " AND MONTH(factura_data_emiterii)='$fmonth'";
};
$presult=ezpub_query($conn,$pquery);
$pnumar=ezpub_num_rows($presult,$pquery);
$ppages = new Pagination;  
$ppages->items_total = $pnumar;  
$ppages->mid_range = 5;  
$ppages->paginate(); 
$pquery= $pquery . " ORDER BY cast(factura_numar as unsigned) ASC $ppages->limit";
$presult=ezpub_query($conn,$pquery);

echo ezpub_error($conn);
if ($pnumar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<div class="paginate">
<?php
$queryp="SELECT COUNT(factura_client_achitat) AS neplatite FROM facturare_facturi WHERE factura_client_achitat=0;"; 
$resultp=ezpub_query($conn,$queryp);
$rowp=ezpub_fetch_array($resultp);
$unpaid=$rowp["neplatite"];
echo $strTotal . " " .$pnumar." ".$strInvoices ." / ". $unpaid ." ". $strUnpayed;
echo " <br /><br />";
echo $ppages->display_pages() . " <a href=\"siteinvoices.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
</div>
<table width="100%" class="unstriped">
	      <thead>
    	<tr>
			<th><?php echo $strNumber?></th>
			<th><?php echo $strIssuedDate?></th>
			<th><?php echo $strClient?></th>
			<th><?php echo $strTotal?></th>
			<th><?php echo $strValue?></th>
			<th><?php echo $strVAT?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDetails?></th>
			<th><?php echo $strAddInvoice?></th>
			<th><?php echo $strEmail?></th>
	      </tr>
		</thead>
<?php 
While ($row=ezpub_fetch_array($presult)){
		
    		echo"<tr><td>$row[factura_numar]</td>
			<td>". date("d.m.Y",strtotime($row["factura_data_emiterii"]))."</td>
			<td width=\"15%\">$row[factura_client_denumire]</td>
			<td align=\"right\">". romanize($row["factura_client_valoare_totala"])."</td>
			<td align=\"right\">". romanize($row["factura_client_valoare"])."</td>
			<td align=\"right\">". romanize($row["factura_client_valoare_tva"])."</td>";
			echo "<td><a href=\"siteinvoices.php?mode=edit&cID=$row[factura_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></a></td>";
			  if ($row["factura_client_pdf"]=='')
			 {
			echo "<td><a href=\"invoice.php?cID=$row[factura_ID]\"><i class=\"far fa-file fa-xl\" title=\"$strView\"></i></a></td>";
			 }
			 Else
			 {
				 echo "<td><a href=\"invoice.php?cID=$row[factura_ID]\"><i class=\"far fa-file-pdf fa-xl\" title=\"$strView\"></i></a></td>";
			 }
		 echo "<td color=\"green\"><a href=\"createinvoice.php?cID=$row[factura_ID]\" ><i class=\"fas fa-money-bill fa-xl\" title=\"$strAddInvoice\"></i></a></td>";
		echo	 "<td><a href=\"emailinvoice.php?cID=$row[factura_ID]\"><i class=\"far fa-envelope fa-xl\" title=\"$strEmail\"></i></a></td>
        </tr>";
}
echo "</table>";
}
?>
<br />
<div class="paginate">
<?php
echo $ppages->display_pages() . " <a href=\"siteinvoices.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
</div>
</div>
</div>

</div>
</div>
<?php }}?>
</div>
</div>
<?php
include '../bottom.php';
?>