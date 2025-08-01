<?php
// update 29.07.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle="Export fișier Facturi Excel";
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

if ((isset( $_POST['month'])) && !empty( $_POST['month'])){
$month=$_POST['month'];
if ($month <10)
{$month="0".$month;}
Else
{$month=$month;}
}
Else
{echo "<div class=\"callout alert\">$strThereWasAnError</div>";
die;
}
if ((isset( $_POST['year'])) && !empty( $_POST['year'])){
$year=$_POST['year'];}
Else
{echo "<div class=\"callout alert\">$strThereWasAnError</div>";
die;}

$sqlu=" SELECT * from date_utilizatori Where utilizator_ID='$uid'";
$resultu=ezpub_query($conn,$sqlu);
$rowu = ezpub_fetch_array($resultu);
$Nume=$rowu["utilizator_Prenume"] ." ". $rowu["utilizator_Nume"];

	
$fp = fopen($hddpath .'/' . $exports_folder .'/Export_facturi_'.$siteInvoicingCode.'_'.$month.'.xml', "w");
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
<Worksheet ss:Name=\"Export facturi ".$siteInvoicingCode. " " . $month ." \">
<Table>
<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\"/>
<Row>
<Cell><Data ss:Type=\"String\">Linie</Data></Cell>
<Cell><Data ss:Type=\"String\">Serie</Data></Cell>
<Cell><Data ss:Type=\"String\">Număr factură</Data></Cell>
<Cell><Data ss:Type=\"String\">Data emiterii</Data></Cell>
<Cell><Data ss:Type=\"String\">Data scadentă</Data></Cell>
<Cell><Data ss:Type=\"String\">Tip factură</Data></Cell>
<Cell><Data ss:Type=\"String\">Nume partener</Data></Cell>
<Cell><Data ss:Type=\"String\">Atribut fiscal</Data></Cell>
<Cell><Data ss:Type=\"String\">Cod fiscal fiscal</Data></Cell>
<Cell><Data ss:Type=\"String\">Nr.reg.Comert</Data></Cell>
<Cell><Data ss:Type=\"String\">Rezidența</Data></Cell>
<Cell><Data ss:Type=\"String\">Țara</Data></Cell>
<Cell><Data ss:Type=\"String\">Județ</Data></Cell>
<Cell><Data ss:Type=\"String\">Localitate</Data></Cell>
<Cell><Data ss:Type=\"String\">Strada</Data></Cell>
<Cell><Data ss:Type=\"String\">Număr</Data></Cell>
<Cell><Data ss:Type=\"String\">Bloc</Data></Cell>
<Cell><Data ss:Type=\"String\">Scară</Data></Cell>
<Cell><Data ss:Type=\"String\">Etaj</Data></Cell>
<Cell><Data ss:Type=\"String\">Apartament</Data></Cell>
<Cell><Data ss:Type=\"String\">Cod Poștal</Data></Cell>
<Cell><Data ss:Type=\"String\">Moneda</Data></Cell>
<Cell><Data ss:Type=\"String\">Curs valutar</Data></Cell>
<Cell><Data ss:Type=\"String\">TVA la încasare</Data></Cell>
<Cell><Data ss:Type=\"String\">Taxare inversă</Data></Cell>
<Cell><Data ss:Type=\"String\">Factură transport</Data></Cell>
<Cell><Data ss:Type=\"String\">Cod agent</Data></Cell>
<Cell><Data ss:Type=\"String\">Valoare netă toală</Data></Cell>
<Cell><Data ss:Type=\"String\">Valoare TVA</Data></Cell>
<Cell><Data ss:Type=\"String\">Total document</Data></Cell>
<Cell><Data ss:Type=\"String\">Articol</Data></Cell>
<Cell><Data ss:Type=\"String\">Cantitate</Data></Cell>
<Cell><Data ss:Type=\"String\">Tip mișcare stoc</Data></Cell>
<Cell><Data ss:Type=\"String\">Cont</Data></Cell>
<Cell><Data ss:Type=\"String\">Preț de listă</Data></Cell>
<Cell><Data ss:Type=\"String\">Valoare fără TVA</Data></Cell>
<Cell><Data ss:Type=\"String\">Valoare TVA</Data></Cell>
<Cell><Data ss:Type=\"String\">Valoare cu TVA</Data></Cell>
<Cell><Data ss:Type=\"String\">Opțiune TVA</Data></Cell>
<Cell><Data ss:Type=\"String\">Cota TVA</Data></Cell>
<Cell><Data ss:Type=\"String\">Cod TVA SAFT</Data></Cell>
<Cell><Data ss:Type=\"String\">Observație</Data></Cell>
<Cell><Data ss:Type=\"String\">Centre de cost</Data></Cell>
</Row>
";
fwrite($fp, $header);
//adaugă alimentări

$query="Select factura_numar, factura_data_emiterii, factura_client_termen, factura_client_denumire, factura_client_RO, factura_client_CIF, factura_client_RC, factura_client_curs_valutar, factura_client_alocat, factura_client_valoare, factura_client_valoare_tva, factura_client_valoare_totala, factura_client_BU,
articol_descriere, articol_bucati, articol_pret, articol_valoare, articol_tva, articol_total 
From facturare_facturi, facturare_articole_facturi
Where facturare_facturi.factura_ID=facturare_articole_facturi.factura_ID
And Year(factura_data_emiterii)=$year
And Month(factura_data_emiterii)=$month
AND factura_tip=0
ORDER BY factura_numar DESC;";
$result=ezpub_query($conn,$query);
while($row = ezpub_fetch_array($result))
{
$factura_insert = "";
$factura_insert .= "<Row>";	
$factura_insert .= "<Cell><Data ss:Type=\"String\">1</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">$siteInvoicingCode</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">$row[factura_numar]</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">".date("Ymd",strtotime($row["factura_data_emiterii"]))."</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">".date("Ymd",strtotime($row["factura_client_termen"]))."</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">380</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">$row[factura_client_denumire]</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">$row[factura_client_RO]</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">$row[factura_client_CIF]</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">$row[factura_client_RC]</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">România</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">România</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">RON</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">$row[factura_client_curs_valutar]</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">$VATRegime</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">0</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">$row[factura_client_alocat]</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">$row[factura_client_valoare]</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">$row[factura_client_valoare_tva]</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">$row[factura_client_valoare_totala]</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">$row[articol_descriere]</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">$row[articol_bucati]</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
$arr = explode(" - ", $row["articol_descriere"], 2);
$first = $arr[0];
If ($first=='Avans prestări servicii conform contract' OR $first=='Stornare factură')
{
	$factura_insert .= "<Cell><Data ss:Type=\"String\">472</Data></Cell>";
}
Else
{
$factura_insert .= "<Cell><Data ss:Type=\"String\">704</Data></Cell>";	
}
$factura_insert .= "<Cell><Data ss:Type=\"String\">$row[articol_pret]</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">$row[articol_valoare]</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">$row[articol_tva]</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">$row[articol_total]</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">Taxabile</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">19</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\">310309</Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
$factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
$factura_insert.="</Row>";
fwrite($fp, $factura_insert);
}
$factura_close = "";
$factura_close.="</Table>
</Worksheet>
</Workbook>";

fwrite($fp, $factura_close);

fclose($fp);

echo "<div class=\"callout success\">$strFileGenerated</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"exportinvoices.php\"
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
	<form Method="post" Action="exportinvoices.php" >

<div class="grid-x grid-margin-x">
    <div class="large-6 medium-6 small-6 cell">
<label><?php echo $strMonth?></label>	 
		<select name="month">
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
    			echo "<OPTION value=\"$m\">$monthname</OPTION>";} 
			?>
        </select> 
				</div>
     <div class="large-6 medium-6 small-6 cell"> 
<label><?php echo $strYear?></label>		 
		<select name="year">
		<option value="0000" selected>--</option>
		<?php
		$cy=date("Y");
		$fy=$cy+1;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
    	echo "<OPTION value=\"$y\">$y</OPTION>";} 
			?>
        </select>
    </div> 
    </div> 
	  <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> 
		<input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success" /> 
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