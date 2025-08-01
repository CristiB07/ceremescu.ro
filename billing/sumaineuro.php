<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
 if(!isset($_SESSION)) 
    { 
        session_start(); 
	}
		
if (!isSet($_SESSION['$lang'])) {
	$_SESSION['$lang']="RO";
	$lang=$_SESSION['$lang'];
}
Else
{
	$lang=$_SESSION['$lang'];
}
if ($lang=="RO") {
include '../lang/language_RO.php';
}
Else
{
	include '../lang/language_EN.php';
}
$day = date('d');
$month = date('m');
$year = date('Y');

$d = date("Y-m-d H:i:s");

  if (IsSet($_SESSION['$userlogedin'])){
	  $uid=$_SESSION['uid'];

  }


$strKeywords=" ";
$strDescription="Convertește valuta";
$strPageTitle="Conversie valutară";
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- Insert this within your head tag and after foundation.css -->
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/foundation.css"/>
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/all.css"/>
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/<?php echo $cssname ?>.css"/>
<!-- Start scripts-->
<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	$dataemiterii = $_POST["strEData3"] ."-". $_POST["strEData2"] ."-". $_POST["strEData1"] ."";
$data = date('Y-m-d', strtotime($dataemiterii));
    $sql="SELECT * FROM curs_valutar WHERE curs_valutar_﻿zi='$data'";

	$curs=ezpub_query($conn,$sql);
	$rss=ezpub_fetch_array($curs);
	If (!IsSet($rss["curs_valutar_valoare"])){
		
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
	$datacurs=date('d.m.Y', strtotime($dataemiterii));
?>
<script>
function calculateleifromeuro(rowIndex) {
                        var myBox1 = document.getElementById('curs_valutar_' + rowIndex).value; 
                        var myBox2 = document.getElementById('pret_euro_' + rowIndex).value;
                        var pret_lei = document.getElementById('pret_lei_' + rowIndex);         
                        var parent_articol_pret = parent.document.getElementById('articol_pret_' + rowIndex);         
                        var myResult = myBox1 * myBox2;
                        pret_lei.value = myResult;
                        parent_articol_pret.value = myResult;
}
      </script>
 <div class="grid-x grid-margin-x">
			  <div class="large-6 medium-6 small-6 cell">
			  <?php echo $strExchangeRate?> - <?php echo $datacurs;?>
			  </div>
			  <div class="large-6 medium-6 small-6 cell">
			  <table width="100%">
			  <tr>
				<td><label><?php echo $strExchangeRate?></label><input name="articol_bucati" id="curs_valutar_0" Type="text"  size="4" class="required" value="<?php echo $cursvalutar?>" /></td>
				<td><label><?php echo $strPriceInEuro?></label><input name="articol_pret" id="pret_euro_0" Type="text"  size="10" class="required" value="" oninput="calculateleifromeuro(0)"/></td>
				<td><label><?php echo $strPriceInLei?></label><input name="articol_valoare" id="pret_lei_0" Type="text" size="10" class="required" value=""  /></td>
			  </tr>
			  </table>
</div>
</div>
<?php
}
Else {
?>
<h3><?php echo $strExchangeRate?></h3>
<form Method="post" Action="sumaineuro.php" >
           <div class="grid-x grid-padding-x ">
              <div class="large-3 medium-3 cell">
			  <h3><?php echo $strChooseDate?></h3>
			  </div>
              <div class="large-3 medium-3 cell">
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
		 <div class="large-3 medium-3 cell">
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
		 <div class="large-3 medium-3 cell">
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
		</div>
				             <div class="grid-x grid-padding-x ">
              <div class="large-12 text-center cell">
			  <input type="submit" value="<?php echo $strFind?>" class="button" name="Submit"> 
		</div>
		</div>
</form>

<?php
}
?>