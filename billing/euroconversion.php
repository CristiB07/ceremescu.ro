<?php
include '../settings.php';
include '../classes/common.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes")
{
	header("location:$strSiteURL/login/login.php?message=MLF");
	die;
}
if (!isSet($_SESSION['$lang'])) {
	$_SESSION['$lang']="RO";
	$lang=$_SESSION['$lang'];
}
else
{
	$lang=$_SESSION['$lang'];
}
if ($lang=="RO") {
include '../lang/language_RO.php';
}
else
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
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/foundation.css" />
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/all.css" />
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/<?php echo $cssname ?>.css" />
    <!-- Start scripts-->
    <?php

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	// Validare date POST
	if (!isset($_POST["strEData1"]) || !is_numeric($_POST["strEData1"]) || 
	    !isset($_POST["strEData2"]) || !is_numeric($_POST["strEData2"]) || 
	    !isset($_POST["strEData3"]) || !is_numeric($_POST["strEData3"])) {
	    die('Date invalide');
	}
	
	$day = (int)$_POST["strEData1"];
	$month = (int)$_POST["strEData2"];
	$year = (int)$_POST["strEData3"];
	
	// Validare interval
	if ($day < 1 || $day > 31 || $month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
	    die('Date invalide');
	}
	
	$dataemiterii = sprintf("%04d-%02d-%02d", $year, $month, $day);
	$data = date('Y-m-d', strtotime($dataemiterii));
	
    $stmt = mysqli_prepare($conn, "SELECT * FROM curs_valutar WHERE curs_valutar_zi=?");
	mysqli_stmt_bind_param($stmt, "s", $data);
	mysqli_stmt_execute($stmt);
	$curs = mysqli_stmt_get_result($stmt);
	$rss = mysqli_fetch_array($curs, MYSQLI_ASSOC);
	mysqli_stmt_close($stmt);
	
	If (!IsSet($rss["curs_valutar_valoare"])){
		
	$curs=new CursBNR("https://www.bnr.ro/nbrfxrates.xml");
	 $cursvalutar=$curs->getExchangeRate("EUR");
	
	
	$stmt_ins = mysqli_prepare($conn, "INSERT INTO curs_valutar(curs_valutar_zi, curs_valutar_valoare) VALUES(?, ?)");
	mysqli_stmt_bind_param($stmt_ins, "ss", $data, $cursvalutar);

//It executes the SQL
if (!mysqli_stmt_execute($stmt_ins))
  {
  die('Error: ' . mysqli_stmt_error($stmt_ins));
  }
mysqli_stmt_close($stmt_ins);
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
                    <td><label><?php echo $strExchangeRate?><input name="articol_bucati" id="curs_valutar_0" type="text"
                                size="4" class="required" value="<?php echo $cursvalutar?>" /></label></td>
                    <td><label><?php echo $strPriceInEuro?><input name="articol_pret" id="pret_euro_0" type="text"
                                size="10" class="required" value="" oninput="calculateleifromeuro(0)" /></label></td>
                    <td><label><?php echo $strPriceInLei?><input name="articol_valoare" id="pret_lei_0" type="text"
                                size="10" class="required" value="" /></label></td>
                </tr>
            </table>
        </div>
    </div>
    <?php
}
else {
?>
    <h3><?php echo $strExchangeRate?></h3>
    <form method="post" Action="euroconversion.php">
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
    		echo "<option selected value=\"$d\">$d</option>";}
			else {echo "<option value=\"$d\">$d</option>";}
			} 
?>
                </select>
            </div>
            <div class="large-3 medium-3 cell">
                <label> <?php echo $strMonth?>
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
    			echo "<option selected value=\"$m\">$monthname</option>";}
				else
				{echo "<option value=\"$m\">$monthname</option>";}
				} 
			?>
                    </select> </label>
            </div>
            <div class="large-3 medium-3 cell">
                <label> <?php echo $strYear?>
                    <select name="strEData3">
                        <option value="0000" selected>--</option>
                        <?php
		$cy=date("Y");
		$fy=$cy+1;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
    			if ($year==$y){
    	echo "<option selected value=\"$y\">$y</option>";}
		else{
		echo "<option value=\"$y\">$y</option>";
		}
		} 
			?>
                    </select></label>
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