<?php
// update 29.07.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle="Export fișier clienți Excel";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
}
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$month= date('m');
$year=date('Y');
$day = date('d');
?>
 <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
if ((isset( $_POST['cid'])) && !empty( $_POST['cid'])){
$cid=$_POST['cid'];
}
Else
{echo $strThereWasAnError;
die;
}
$sqlu=" SELECT * from date_utilizatori Where utilizator_ID='$uid'";
$resultu=ezpub_query($conn,$sqlu);
$rowu = ezpub_fetch_array($resultu);
$Nume=$rowu["utilizator_Prenume"] ." ". $rowu["utilizator_Nume"];

	
$fp = fopen($hddpath .'/' . $exports_folder .'/Export_Clienți_'.$siteInvoicingCode.'_'.$month.'_'.$cid.'.xml', "w");
$header="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<?mso-application progid=\"Excel.Sheet\"?>
<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"https://www.w3.org/TR/html401/\">
	<ss:Styles>
		<ss:Style ss:ID=\"A\">
			<ss:Font ss:FontName=\"Open Sans\" ss:Size=\"12\" ss:Color=\"Red\"  /> 
		</ss:Style>
	</ss:Styles>
	<DocumentProperties xmlns=\"urn:schemas-microsoft-com:office:office\">
        <Author>". $Nume . "</Author>
        <LastAuthor>". $Nume. "</LastAuthor>
        <Created>". date("d-m-Y H:i:s")."</Created>
        <Version>15.00</Version>
    </DocumentProperties>
<Worksheet ss:Name=\"Export clienți ".$siteInvoicingCode. " " . $month ." \">
<Table>
<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\"/>
<Row>
<Cell><Data ss:Type=\"String\">ID Client</Data></Cell>
<Cell><Data ss:Type=\"String\">Denumire client</Data></Cell>
<Cell><Data ss:Type=\"String\">Client blocat</Data></Cell>
<Cell><Data ss:Type=\"String\">Client RO</Data></Cell>
<Cell><Data ss:Type=\"String\">Client CIF</Data></Cell>
<Cell><Data ss:Type=\"String\">Client RC</Data></Cell>
<Cell><Data ss:Type=\"String\">Client Adresa</Data></Cell>
<Cell><Data ss:Type=\"String\">Localtatea</Data></Cell>
<Cell><Data ss:Type=\"String\">Județ</Data></Cell>
<Cell><Data ss:Type=\"String\">Țara</Data></Cell>
<Cell><Data ss:Type=\"String\">Rezidența</Data></Cell>
<Cell><Data ss:Type=\"String\">Categoria</Data></Cell>
<Cell><Data ss:Type=\"String\">Banca</Data></Cell>
<Cell><Data ss:Type=\"String\">IBAN</Data></Cell>
</Row>
";
fwrite($fp, $header);
//adaugă alimentări

$query="SELECT ID_Client, Client_Denumire, Client_RO, Client_CIF, Client_RC, Client_Adresa,Client_Localitate,Client_Judet,Client_Banca,Client_IBAN 
FROM clienti_date WHERE ID_Client >$cid";
$result=ezpub_query($conn,$query);
while($row = ezpub_fetch_array($result))
{
$client_insert = "";
$client_insert .= "<Row>";	
$client_insert.="<Cell><Data ss:Type=\"String\">". $row["ID_Client"] . "</Data></Cell>";
$client_insert.="<Cell><Data ss:Type=\"String\">". $row["Client_Denumire"] . "</Data></Cell>";
$client_insert.="<Cell><Data ss:Type=\"String\"></Data></Cell>";
$client_insert.="<Cell><Data ss:Type=\"String\">". $row["Client_RO"] . "</Data></Cell>";
$client_insert.="<Cell><Data ss:Type=\"String\">". $row["Client_CIF"] . "</Data></Cell>";
$client_insert.="<Cell><Data ss:Type=\"String\">". $row["Client_RC"] . "</Data></Cell>";
$client_insert.="<Cell><Data ss:Type=\"String\">". $row["Client_Adresa"] . "</Data></Cell>";
$client_insert.="<Cell><Data ss:Type=\"String\">". $row["Client_Localitate"] . "</Data></Cell>";
$client_insert.="<Cell><Data ss:Type=\"String\">". $row["Client_Judet"] . "</Data></Cell>";
$client_insert.="<Cell><Data ss:Type=\"String\">România</Data></Cell>";
$client_insert.="<Cell><Data ss:Type=\"String\">Romania</Data></Cell>";
$client_insert.="<Cell><Data ss:Type=\"String\">Intern</Data></Cell>";
$client_insert.="<Cell><Data ss:Type=\"String\">". $row["Client_Banca"] . "</Data></Cell>";
$client_insert.="<Cell><Data ss:Type=\"String\">". $row["Client_IBAN"] . "</Data></Cell>";
$client_insert.="</Row>";
fwrite($fp, $client_insert);
}
$client_close = "";
$client_close.="</Table>
</Worksheet>
</Workbook>";

fwrite($fp, $client_close);

fclose($fp);

echo "<div class=\"callout success\">$strFileGenerated</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"exportclients.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}

// get the ID
Else
{
?>
	<form Method="post" id="users" Action="exportclients.php" >
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <label><?php echo $strLastClientID?></label></TD>
	  <input name="cid" Type="text"  class="required" />
	  </div>
	  </div>
	  <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> <p align="center">
		<input Type="submit" Value="<?php echo $strExport?>" name="Submit" class="button success" /> 
</p>
	</div>
	</div>
  </form>
<?php
}
?>

</div>
</div>
<?php
include '../bottom.php';
?>