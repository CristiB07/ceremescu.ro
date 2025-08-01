<?php
//update 30.07.2025
$uid=$_SESSION['uid'];
$code=$_SESSION['$code'];
$month= date('m');
$year=date('Y');
$byear=$year - 1;
$day = date('d');
?>
	    <div class="grid-x grid-margin-x">
					  <div class="large-12 medium-12 small-12 cell">
					  <h1><?php echo $strFinancials?></h1>
					  </div>
					  </div>
	    <div class="grid-x grid-margin-x">
					  <div class="large-12 medium-12 small-12 cell">
		<?php
			 	$querybank="SELECT * FROM date_banca";
	$resultbank=ezpub_query($conn,$querybank);
	$rsb=ezpub_fetch_array($resultbank);
	$totalinbanca=$rsb["date_banca_ING"]+$rsb["date_banca_unicredit"]+$rsb["date_banca_transilvania"];
	
	?><form method="Post" action="updatebank.php">
			              <div class="grid-x grid-padding-x ">
              <div class="large-2 medium-2 cell">
                <label>Transilvania</label>
                <input type="text"  name="date_banca_transilvania" value="<?php echo romanize($rsb["date_banca_transilvania"])?>"/>
				</div>           
				<div class="large-2 medium-2 cell">
                <label>ING</label>
                <input type="text"  name="date_banca_ING" value="<?php echo romanize($rsb["date_banca_ING"])?>"/>
				</div>
				              <div class="large-2 medium-2 cell">
                <label>Trezorerie</label>
                <input type="text"  name="date_banca_unicredit" value="<?php echo romanize($rsb["date_banca_unicredit"])?>"/>
				</div>
				<div class="large-3 medium-3 cell">
				<label>&nbsp;</label>
	 <input type="submit" value="<?php echo $strModify?>" class="button" name="Submit" class="button success"> 
	 </div>
	 <div class="large-3 medium-3 cell">
	 <label>&nbsp;</label>
	 <p><?php echo $strTotal?>: <strong><?php echo romanize($totalinbanca)?></strong></p>
	 	 </div>
	 	 </div>
		 <?php //some math
	$query4="SELECT AVG(curs_valutar_valoare) AS cursvalutar FROM date_curs_valutar WHERE YEAR(curs_valutar_﻿zi)='$year';";
	$result4=ezpub_query($conn,$query4);
	$rs4=ezpub_fetch_array($result4);
	$cursmediu=$rs4["cursvalutar"]; 
	if (!$cursmediu) {$cursmediu=5;}
	$query1="SELECT SUM(factura_client_valoare_totala) AS valoaretotala FROM date_clienti_facturi WHERE YEAR(factura_data_emiterii)='$year' AND factura_tip=0";
	$result1=ezpub_query($conn,$query1);
	$rs1=ezpub_fetch_array($result1);
	$totalfacturi=$rs1["valoaretotala"]; 
	if (!$totalfacturi) {$totalfacturieuro=0;}
	else
	{$totalfacturieuro=$totalfacturi/$cursmediu;}
	$query2="SELECT SUM(factura_client_valoare_totala) AS neachitata FROM date_clienti_facturi WHERE factura_client_achitat='0' AND factura_tip=0 AND factura_client_anulat=0";
	$result2=ezpub_query($conn,$query2);
	$rs2=ezpub_fetch_array($result2);
	$totalneachitate=$rs2["neachitata"]; 
		if (!$totalneachitate) {$totalneachitateeuro=0;
	$totalneachitate=0;
	}
	else {$totalneachitateeuro=$totalneachitate/$cursmediu;}
	$query3="SELECT SUM(factura_client_valoare_totala) AS achitata FROM date_clienti_facturi WHERE factura_client_achitat='1' AND YEAR(factura_client_data_achitat)='$year'";
	$result3=ezpub_query($conn,$query3);
	$rs3=ezpub_fetch_array($result3);
	$totalachitate=$rs3["achitata"]; 

	if (!$totalachitate) {$totalachitate=0;
	$totalachitateeuro=0;}
	Else
	{	$totalachitateeuro=$totalachitate/$cursmediu;} 	
	$query4="SELECT SUM(factura_client_valoare_totala) AS mentenanta FROM date_clienti_facturi WHERE factura_client_tip_activitate='M' AND YEAR(factura_data_emiterii)='$year'";
	$result4=ezpub_query($conn,$query4);
	$rs4=ezpub_fetch_array($result4);
	$totalmentenanta=$rs4["mentenanta"]; 	
	if (!$totalmentenanta) {
	$totalmentenanta=0; 
	$totalmentenantaeeuro=0;}
	Else
	{$totalmentenantaeeuro=$totalmentenanta/$cursmediu;}	
	$query5="SELECT SUM(factura_client_valoare_totala) AS onetime FROM date_clienti_facturi WHERE factura_client_tip_activitate='O' AND YEAR(factura_data_emiterii)='$year'";
	$result5=ezpub_query($conn,$query5);
	$rs5=ezpub_fetch_array($result5);
	$totalonetime=$rs5["onetime"]; 	
		if (!$totalonetime) {
	$totalonetime=0; 
	$totalonetimeeuro=0;}
	Else
	{$totalonetimeeuro=$totalonetime/$cursmediu;}
If ($totalmentenanta==0 OR $totalfacturi==0)
{$procentmentenanta=0;}
else
{	$procentmentenanta=($totalmentenanta/$totalfacturi)*100;}
If ($totalonetime==0 OR $totalfacturi==0)
{$procentonetime=0;}
else
{
$procentonetime=($totalonetime/$totalfacturi)*100;}
	$query6="SELECT AVG(factura_client_zile_achitat) AS mediezile FROM date_clienti_facturi WHERE YEAR(factura_data_emiterii)='$year'";
	$result6=ezpub_query($conn,$query6);
	$rs6=ezpub_fetch_array($result6);
	$mediezile=$rs6["mediezile"]; 	
	if (!$mediezile)
	{$mediezile=0;}
		 ?>
		<div class="grid-x grid-padding-x ">
               <div class="large-2 medium-2 cell">
	 <label>Total facturat</label>
	 <p><span class="green"><?php echo romanize($totalfacturi)?> lei <br /> <?php echo romanize($totalfacturieuro)?> €</span></p>
	 	 </div>               
		 <div class="large-2 medium-2 cell">
	 <label>Total încasat</label>
	 <p><span class="green"><?php echo romanize($totalachitate)?>lei  <br />  <?php echo romanize($totalachitateeuro)?> €</span></p>
	 	 </div>
		                <div class="large-2 medium-2 cell">
	 <label>Total neîncasat</label>
	 <p class="red"><span class="red"><?php echo romanize($totalneachitate)?>lei  <br />  <?php echo romanize($totalneachitateeuro)?> €</span></p>
	 	 </div>
		 		                <div class="large-2 medium-2 cell">
	 <label>Total mentenanță</label>
	 <p><span class="green"><?php echo romanize($totalmentenanta)?>lei  <br />  <?php echo romanize($totalmentenantaeeuro)?> €  <br />  <?php echo romanize($procentmentenanta)?> %</span></p>
	 	 </div>
		 		                <div class="large-2 medium-2 cell">
	 <label>Total onetime</label>
	 <p><span class="green"><?php echo romanize($totalonetime)?>lei  <br />  <?php echo romanize($totalonetimeeuro)?> €  <br />  <?php echo romanize($procentonetime)?> %</span></p>
	 	 </div>
		 <div class="large-2 medium-2 cell">
	 	 <label>Curs mediu</label>
	 <p><strong><?php echo romanize($cursmediu)?></strong></p>
	 <label>Medie zile încasare</label>
	 <p><strong><?php echo romanize($mediezile)?></strong></p>
	 	 </div>
	 	 </div>
		 <div class="grid-x grid-padding-x ">
               <div class="large-6 medium-6 small-6 cell">
			   <fieldset class="fieldset">
			   <h1><?php echo $year?></h1>
<table width="100%" class="small-font-table">
   <thead>
        <tr>
<th align="center"><?php echo $strNumber?></th>
<th align="center"><?php echo $strMonth?></th>
<th align="center">Total facturi</th>
<th align="center">Total facturat</th>
<th align="center">Total încasat</th>
<th align="center">TVA Total</th>
<th align="center">Total plăți</th>
<th align="center">TVA Dedus</th>
<th align="center">TVA de plată</th>
</tr>
</thead>
<tbody>
<?php
$i=date('n');
for ( $m = 1; $m <= $i; $m ++) {

$queryi="SELECT SUM(factura_client_valoare_totala) AS valoarelunara FROM date_clienti_facturi WHERE MONTH(factura_data_emiterii)='$m' AND YEAR(factura_data_emiterii)='$year' AND factura_tip=0";
$resultm=ezpub_query($conn,$queryi);
    $rsm=ezpub_fetch_array($resultm);
    $totalfacturilunare=$rsm["valoarelunara"];
    if (!$totalfacturilunare) {$totalfacturilunare=0;}

$queryip="SELECT SUM(fp_valoare_totala) AS fplunara FROM facturare_facturi_primite WHERE MONTH(fp_data_scadenta)='$m' AND YEAR(fp_data_scadenta)='$year'";
$resultp=ezpub_query($conn,$queryip);
    $rsp=ezpub_fetch_array($resultp);
    $totalplatilunare=$rsp["fplunara"];
    if (!$totalplatilunare) {$totalplatilunare=0;}

$queryf="SELECT COUNT(factura_ID) AS facturipeluna FROM date_clienti_facturi WHERE MONTH(factura_data_emiterii)='$m' AND YEAR(factura_data_emiterii)='$year' AND factura_tip=0";
$resultf=ezpub_query($conn,$queryf);
    $rsf=ezpub_fetch_array($resultf);
    $totalfacturiemiselunar=$rsf["facturipeluna"];
    if (!$totalfacturiemiselunar) {$totalfacturiemiselunar=0;}
	
$queryc="SELECT SUM(factura_client_valoare_totala) AS valoareincasata FROM date_clienti_facturi WHERE MONTH(factura_client_data_achitat)=$m AND YEAR(factura_client_data_achitat)='$year' AND  factura_tip=0";
$resultc=ezpub_query($conn,$queryc);
    $rsc=ezpub_fetch_array($resultc);
    $totalincasarilunare=$rsc["valoareincasata"];
    if (!$totalincasarilunare) {$totalincasarilunare=0;}
	
$querytvai="SELECT SUM(factura_client_valoare_tva) AS totaltvaincasat FROM date_clienti_facturi WHERE MONTH(factura_client_data_achitat)=$m AND YEAR(factura_client_data_achitat)='$year' AND  factura_tip=0";
$resultvai=ezpub_query($conn,$querytvai);
    $rstvai=ezpub_fetch_array($resultvai);
    $totaltvaincasat=$rstvai["totaltvaincasat"];
    if (!$totaltvaincasat) {$totaltvaincasat=0;}

$querytvad="SELECT SUM(fp_valoare_tva) AS totaltvaplatit FROM facturare_facturi_primite WHERE MONTH(fp_data_scadenta)=$m AND YEAR(fp_data_scadenta)='$year'";
$resultvad=ezpub_query($conn,$querytvad);
	$rstvad=ezpub_fetch_array($resultvad);
	$totaltvaplatit=$rstvad["totaltvaplatit"];
	if (!$totaltvaplatit) {$totaltvaplatit=0;}
	
	$diferentatva=$totaltvaincasat - $totaltvaplatit;
	
	//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
Echo "<tr>
<td>$m</td>
<td>$monthname</td>
<td align=\"right\">$totalfacturiemiselunar</td>
<td align=\"right\">".romanize($totalfacturilunare)."</td>
<td align=\"right\">".romanize($totalincasarilunare)."</td>
<td align=\"right\">".romanize($totaltvaincasat)."</td>
<td align=\"right\">".romanize($totalplatilunare)."</td>
<td align=\"right\">".romanize($totaltvaplatit)."</td>
<td align=\"right\">".romanize($diferentatva)."</td>
</tr>";
}
$queryt="SELECT SUM(factura_client_valoare_totala) AS valoaretotala FROM date_clienti_facturi WHERE MONTH(factura_data_emiterii)<='$i' AND YEAR(factura_data_emiterii)='$year' AND factura_tip=0";
$resultt=ezpub_query($conn,$queryt);
    $rst=ezpub_fetch_array($resultt);
    $totalfacturianuale=$rst["valoaretotala"];
    if (!$totalfacturianuale) {$totalfacturianuale=0;}
echo "<tr><td colspan=\"8\">Total</td><td align=\"right\">".romanize($totalfacturianuale)."</td>";
echo "</tbody></table>";
?>
</fieldset>
  </div>
  <div class="large-6 medium-6 small-6 cell">
  <fieldset class="fieldset">
   <h1><?php echo $byear?></h1>
 <table width="100%" class="small-font-table">
   <thead>
        <tr>
<th align="center"><?php echo $strNumber?></th>
<th align="center"><?php echo $strMonth?></th>
<th align="center">Total facturi</th>
<th align="center">Total facturat</th>
<th align="center">Total încasat</th>
<th align="center">TVA Total</th>
<th align="center">Total plăți</th>
<th align="center">TVA dedus</th>
<th align="center">TVA de plată</th>
</tr>
</thead>
<tbody>
<?php
$i=date('n');
for ( $mm = 1; $mm <= $i; $mm ++) {

$queryib="SELECT SUM(factura_client_valoare_totala) AS valoarelunarab FROM date_clienti_facturi WHERE MONTH(factura_data_emiterii)='$mm' AND YEAR(factura_data_emiterii)='$byear' AND factura_tip=0";
$resultmb=ezpub_query($conn,$queryib);
    $rsmb=ezpub_fetch_array($resultmb);
    $totalfacturilunareb=$rsmb["valoarelunarab"];
    if (!$totalfacturilunareb) {$totalfacturilunareb=0;}

$queryipb="SELECT SUM(fp_valoare_totala) AS fplunarab FROM facturare_facturi_primite WHERE MONTH(fp_data_scadenta)='$mm' AND YEAR(fp_data_scadenta)='$byear'";
$resultpb=ezpub_query($conn,$queryipb);
    $rspb=ezpub_fetch_array($resultpb);
    $totalplatilunareb=$rsmb["fplunarab"];
    if (!$totalplatilunareb) {$totalplatilunareb=0;}

$queryfb="SELECT COUNT(factura_ID) AS facturipelunab FROM date_clienti_facturi WHERE MONTH(factura_data_emiterii)='$mm' AND YEAR(factura_data_emiterii)='$byear' AND factura_tip=0";
$resultfb=ezpub_query($conn,$queryfb);
    $rsfb=ezpub_fetch_array($resultfb);
    $totalfacturiemiselunarb=$rsfb["facturipelunab"];
    if (!$totalfacturiemiselunarb) {$totalfacturiemiselunarb=0;}

$querycb="SELECT SUM(factura_client_valoare_totala) AS valoareincasatab FROM date_clienti_facturi WHERE MONTH(factura_client_data_achitat)='$mm' AND YEAR(factura_client_data_achitat)='$byear' AND  factura_tip=0";
$resultcb=ezpub_query($conn,$querycb);
    $rscb=ezpub_fetch_array($resultcb);
    $totalincasarilunareb=$rscb["valoareincasatab"];
    if (!$totalincasarilunareb) {$totalincasarilunareb=0;}

$querytvaib="SELECT SUM(factura_client_valoare_tva) AS totaltvaincasatb FROM date_clienti_facturi WHERE MONTH(factura_client_data_achitat)='$mm' AND YEAR(factura_client_data_achitat)='$byear' AND  factura_tip=0";
$resultvaib=ezpub_query($conn,$querytvaib);
    $rstvaib=ezpub_fetch_array($resultvaib);
    $totaltvaincasatb=$rstvaib["totaltvaincasatb"];
    if (!$totaltvaincasatb) {$totaltvaincasatb=0;}

$querytvadb="SELECT SUM(fp_valoare_tva) AS totaltvaplatitb FROM facturare_facturi_primite WHERE MONTH(fp_data_scadenta)=$mm AND YEAR(fp_data_scadenta)='$byear'";
$resultvadb=ezpub_query($conn,$querytvadb);
	$rstvadb=ezpub_fetch_array($resultvadb);
	$totaltvaplatitb=$rstvadb["totaltvaplatitb"];
	if (!$totaltvaplatitb) {$totaltvaplatitb=0;}
	
	$diferentatvab=$totaltvaincasatb - $totaltvaplatitb;
	

	
	//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $mm);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
Echo "<tr>
<td>$mm</td>
<td>$monthname</td>
<td align=\"right\">$totalfacturiemiselunarb</td>
<td align=\"right\">".romanize($totalfacturilunareb)."</td>
<td align=\"right\">".romanize($totalincasarilunareb)."</td>
<td align=\"right\">".romanize($totaltvaincasatb)."</td>
<td align=\"right\">".romanize($totalplatilunareb)."</td>
<td align=\"right\">".romanize($totaltvaplatitb)."</td>
<td align=\"right\">".romanize($diferentatvab)."</td>
</tr>";
}
$querytb="SELECT SUM(factura_client_valoare_totala) AS valoaretotalab FROM date_clienti_facturi WHERE MONTH(factura_data_emiterii)<='$i' AND YEAR(factura_data_emiterii)='$byear' AND factura_tip=0";
$resulttb=ezpub_query($conn,$querytb);
    $rstb=ezpub_fetch_array($resulttb);
    $totalfacturianualeb=$rstb["valoaretotalab"];
    if (!$totalfacturianualeb) {$totalfacturianualeb=0;}
echo "<tr><td colspan=\"8\">Total</td><td align=\"right\">".romanize($totalfacturianualeb)."</td>";
echo "</tbody></table>";
?>
  
   </fieldset>
  </div>
  </div>
  </div>
  </div>
<?php include '../bottom.php'?>