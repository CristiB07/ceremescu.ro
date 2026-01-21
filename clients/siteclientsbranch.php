<?php
include '../settings.php';
include '../classes/common.php';

$strPageTitle="Administrare puncte de lucru clienți";
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes")
{
	header("location:$strSiteURL/login/index.php?message=MLF");
	die;
}

// Validare parametru cID
if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
    header("location:$strSiteURL/clients/siteclients.php");
    die;
}
$cID = (int)$_GET['cID'];

include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
// Prepared statement pentru SELECT
$stmt = mysqli_prepare($conn, "SELECT * FROM clienti_date WHERE ID_Client=?");
mysqli_stmt_bind_param($stmt, 'i', $cID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Validare și sanitizare date
$type = 0;
If ($row["Client_Numar_Angajati"] == "") {$numar_angajati = 0;}
else {$numar_angajati = (int)$row["Client_Numar_Angajati"];}

$client_denumire = $row["Client_Denumire"] . " - punct de lucru";
$client_adresa = $row["Client_Adresa"] ?? '';
$client_telefon = $row["Client_Telefon"] ?? '';
$client_cui = $row["Client_CUI"] ?? '';
$client_rc = $row["Client_RC"] ?? '';
$client_banca = $row["Client_Banca"] ?? '';
$client_iban = $row["Client_IBAN"] ?? '';
$client_localitate = $row["Client_Localitate"] ?? '';
$client_judet = $row["Client_Judet"] ?? '';
$client_cod_caen = $row["Client_Cod_CAEN"] ?? '';
$client_descriere = $row["Client_Descriere_Activitate"] ?? '';
$client_web = $row["Client_Web"] ?? '';
$client_caracterizare = $row["Client_Caracterizare"] ?? '';

// Prepared statement pentru INSERT
$stmt2 = mysqli_prepare($conn, 
	"INSERT INTO clienti_date(Client_Denumire, Client_Adresa, Client_Telefon, Client_CUI, Client_RC, 
	Client_Banca, Client_IBAN, Client_Localitate, Client_Judet, Client_Cod_CAEN, Client_Numar_Angajati, 
	Client_Descriere_Activitate, Client_Web, Client_Tip, Client_HQ, Client_Caracterizare) 
	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param($stmt2, 'ssssssssssisssis', 
	$client_denumire, $client_adresa, $client_telefon, $client_cui, $client_rc,
	$client_banca, $client_iban, $client_localitate, $client_judet, $client_cod_caen,
	$numar_angajati, $client_descriere, $client_web, $type, $cID, $client_caracterizare
);
				
if (!mysqli_stmt_execute($stmt2)) {
	die('Error: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt2);

echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-2);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;