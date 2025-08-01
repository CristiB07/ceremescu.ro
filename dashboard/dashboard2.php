<?php
//update 8.01.2025
$strPageTitle="Dashboard";
include '../settings.php';
include '../classes/common.php';
    if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
header("location:$strSiteURL/login/index.php?message=MLF");
die;}
include 'header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$scope=$_SESSION['function'];

$month= date('m');
$year=date('Y');
$day = date('d');
?>
<?php
$month= date('m');
$year=date('Y');
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
<table width="100%">
   <thead>
        <tr>
<th align="center"><?php echo $strNumber?></th>
<th align="center"><?php echo $strMonth?></th>
<th align="center">Total facturi</th>
<th align="center">Total facturat</th>
<th align="center">Total încasat</th>
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
</tr>";
}
echo "</tbody></table>";
?>
  </div>
  <div class="large-6 medium-6 small-6 cell">
  </div>
  </div>
	    <div class="grid-x grid-margin-x">
					  <div class="large-12 medium-12 small-12 cell">
					  <h1><?php echo $strPageTitle?></h1>
					  <div class="callout primary">			<p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam eget augue elit. Pellentesque justo tortor, ultricies vel lobortis at, vehicula gravida enim. Morbi sollicitudin pellentesque sodales. Praesent accumsan molestie quam in porta. Phasellus lobortis purus leo, vitae convallis ipsum luctus in. Nulla viverra imperdiet ante vitae fringilla. Mauris ac turpis orci. Etiam semper, ligula at ornare malesuada, erat turpis commodo risus, eget sagittis quam augue vel nibh. Maecenas volutpat maximus massa sit amet porttitor. Mauris vitae imperdiet diam. Nunc arcu neque, lacinia eu sapien eu, commodo gravida orci. Donec maximus justo neque, ac vestibulum nisi lacinia ac.</p></div>
					  <div class="callout secondary"><p>Etiam venenatis dapibus mauris non faucibus. Ut sit amet augue faucibus, malesuada augue et, mollis metus. Pellentesque egestas et leo eu tempus. Nulla commodo accumsan lorem, ut consectetur eros porta eget. Nam rhoncus efficitur ex, quis cursus mi sollicitudin id. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam laoreet id tortor nec tincidunt. Morbi tincidunt eleifend viverra. Nulla maximus sapien semper est tempor, ac maximus orci posuere. Praesent vestibulum velit et risus gravida laoreet. Quisque viverra viverra magna, non tempor magna efficitur vitae.</p></div>
					  <div class="callout primary"><p>In hac habitasse platea dictumst. Sed bibendum magna vitae ante varius porttitor. Sed at nisl dapibus, vestibulum lacus quis, blandit odio. Maecenas sed tortor mollis, cursus turpis id, mollis orci. Ut facilisis metus dui, quis ultrices nisl venenatis quis. In faucibus volutpat lectus id ultrices. Aliquam at tellus efficitur, congue augue id, maximus est. Nullam bibendum posuere dolor, et consectetur purus suscipit venenatis. Nunc ultrices sodales justo nec pretium.</p></div>
					  <div class="callout secondary"><p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Donec dignissim ipsum justo, at mollis tortor fermentum eu. Etiam ut scelerisque urna, ullamcorper vehicula nulla. Sed non sem at est aliquet eleifend nec at tellus. Aenean a mollis purus. In hac habitasse platea dictumst. Etiam diam sapien, tempus eu magna in, suscipit mollis mauris. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Ut viverra quam in eros porttitor, eget efficitur diam sodales. Aenean pharetra viverra nisl quis dictum. Ut sagittis purus ex, in malesuada metus aliquet in. Mauris ornare sem sit amet luctus sollicitudin.</p></div>
					  <div class="callout primary"><p>Etiam gravida id est vitae porttitor. Praesent facilisis viverra arcu vel convallis. Ut eget mattis odio. Praesent et tristique mi, a commodo mauris. Integer suscipit feugiat neque, et tincidunt nisl malesuada et. Donec sagittis blandit tincidunt. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis porttitor pulvinar magna et pretium. Nam nibh sem, interdum sit amet elit at, dapibus aliquam sapien. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vivamus pharetra nisi in mi bibendum rutrum eleifend ac ipsum. Aenean tempor tellus enim, non dignissim ligula tincidunt vel. Etiam eu eleifend odio.</p></div>
					  </div>
					  </div>
	    <div class="grid-x grid-margin-x">
					  <div class="large-12 medium-12 small-12 cell">
	
  </div>
  </div>
<?php include '../bottom.php'?>