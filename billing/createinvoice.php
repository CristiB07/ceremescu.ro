<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Generare facturi din proforme";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
}

if ((isset( $_GET['cID'])) && !empty( $_GET['cID'])){
$query="Select factura_numar FROM facturare_facturi WHERE factura_client_inchisa='1' AND factura_tip='0' ORDER BY CAST(factura_numar AS unsigned) DESC";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
If (!isSet($row["factura_numar"]))
{$numarfactura=1;}
Else
{$numarfactura=(int)$row["factura_numar"]+1;}
$dataemiterii= date('Y-m-d');

$strWhereClause = " WHERE facturare_facturi.factura_ID=" . $_GET["cID"] . ";";
$uquery= "UPDATE facturare_facturi SET facturare_facturi.factura_tip='0' ," ;
$uquery= $uquery . "facturare_facturi.factura_data_emiterii='" .$dataemiterii . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_termen='" .$dataemiterii . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_numar='" .$numarfactura . "' ," ;
$uquery= $uquery . "facturare_facturi.factura_client_pdf=NULL," ;
$uquery= $uquery . "facturare_facturi.factura_client_pdf_generat=Null" ;
$uquery= $uquery . $strWhereClause;
if (!ezpub_query($conn,$uquery))
  {
  echo $uquery;
  die('Error: ' . ezpub_error($conn));
  }
Else{
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
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
}
Else
{
	Echo "<div class=\"callout alert\">There was an error</div></div></div>";
	include '../bottom.php';
	die;
}