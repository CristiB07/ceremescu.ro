<?php
//update 08.01.2025
include '../settings.php';
include '../classes/common.php';

$strPageTitle="Administrare puncte de lucru clienți";
include 'header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['$code'];
$query="SELECT * FROM clienti_date where ID_Client=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);

//insert new 
$type=0;
If ($row["Client_Numar_Angajati"]=="") {$numar_angajati=0;}
Else {$numar_angajati=$row["Client_Numar_Angajati"];}

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
	$mSQL = $mSQL . "Client_Tip,";
	$mSQL = $mSQL . "Client_HQ,";
	$mSQL = $mSQL . "Client_Caracterizare)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$row["Client_Denumire"]  . " - punct de lucru" . "', ";
	$mSQL = $mSQL . "'" .$row["Client_Adresa"] . "', ";
	$mSQL = $mSQL . "'" .$row["Client_Telefon"] . "', ";
	$mSQL = $mSQL . "'" .$row["Client_CUI"] . "', ";
	$mSQL = $mSQL . "'" .$row["Client_RC"] . "', ";
	$mSQL = $mSQL . "'" .$row["Client_Banca"] . "', ";
	$mSQL = $mSQL . "'" .$row["Client_IBAN"] . "', ";
	$mSQL = $mSQL . "'" .$row["Client_Localitate"] . "', ";
	$mSQL = $mSQL . "'" .$row["Client_Judet"] . "', ";
	$mSQL = $mSQL . "'" .$row["Client_Cod_CAEN"] . "', ";
	$mSQL = $mSQL . "'" .$numar_angajati . "', ";
	$mSQL = $mSQL . "'" .$row["Client_Descriere_Activitate"] . "', ";
	$mSQL = $mSQL . "'" .$row["Client_Web"] . "', ";
	$mSQL = $mSQL . "'" .$type. "', ";
	$mSQL = $mSQL . "'" .$_GET['cID']. "', ";
	$mSQL = $mSQL . "'" .$row["Client_Caracterizare"] . "') ";
				
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
    window.history.go(-2);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}