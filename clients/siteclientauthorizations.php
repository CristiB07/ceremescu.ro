<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle="Administrare autorizații";
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes")
{
	header("location:$strSiteURL/login/index.php?message=MLF");
	die;
}

// Validare parametri GET
if (isset($_GET['mode']) && !in_array($_GET['mode'], ['new', 'edit', 'delete'])) {
    header("location:$strSiteURL/clients/siteclientauthorizations.php");
    die;
}

if (isset($_GET['cID'])) {
    if (!is_numeric($_GET['cID'])) {
        header("location:$strSiteURL/clients/siteclientauthorizations.php");
        die;
    }
    $cID = (int)$_GET['cID'];
}

include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, "DELETE FROM clienti_autorizatii_clienti WHERE ID_Autorizatie_Client=?");
mysqli_stmt_bind_param($stmt, 'i', $cID);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteclientauthorizations.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){
// Validare și sanitizare input
$ID_Client = isset($_POST["ID_Client"]) && is_numeric($_POST["ID_Client"]) ? (int)$_POST["ID_Client"] : null;
$ID_Autorizatie = isset($_POST["ID_Autorizatie"]) && is_numeric($_POST["ID_Autorizatie"]) ? (int)$_POST["ID_Autorizatie"] : null;
$Numar = isset($_POST["Autorizatie_Client_Numar"]) ? trim($_POST["Autorizatie_Client_Numar"]) : '';
$Tip = isset($_POST["Autorizatie_Client_Tip"]) ? trim($_POST["Autorizatie_Client_Tip"]) : '';

$strEData1 = isset($_POST["strEData1"]) ? (int)$_POST["strEData1"] : 0;
$strEData2 = isset($_POST["strEData2"]) ? (int)$_POST["strEData2"] : 0;
$strEData3 = isset($_POST["strEData3"]) ? (int)$_POST["strEData3"] : 0;
$dataexpirarii = sprintf("%04d-%02d-%02d", $strEData3, $strEData2, $strEData1);

$strIData1 = isset($_POST["strIData1"]) ? (int)$_POST["strIData1"] : 0;
$strIData2 = isset($_POST["strIData2"]) ? (int)$_POST["strIData2"] : 0;
$strIData3 = isset($_POST["strIData3"]) ? (int)$_POST["strIData3"] : 0;
$dataemiterii = sprintf("%04d-%02d-%02d", $strIData3, $strIData2, $strIData1);

$strVData1 = isset($_POST["strVData1"]) ? (int)$_POST["strVData1"] : 0;
$strVData2 = isset($_POST["strVData2"]) ? (int)$_POST["strVData2"] : 0;
$strVData3 = isset($_POST["strVData3"]) ? (int)$_POST["strVData3"] : 0;
$datavizei = sprintf("%04d-%02d-%02d", $strVData3, $strVData2, $strVData1);

if (!$ID_Client || !$ID_Autorizatie) {
    echo "<div class=\"alert callout\">Date invalide</div>";
    die;
}

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, "INSERT INTO clienti_autorizatii_clienti(ID_Client, ID_Autorizatie, ID_User, Autorizatie_Client_Numar, Autorizatie_Client_Tip, Autorizatie_Client_Expirare, Autorizatie_Client_Viza, Autorizatie_Client_Emitere) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'iissssss', $ID_Client, $ID_Autorizatie, $code, $Numar, $Tip, $dataexpirarii, $datavizei, $dataemiterii);
			
//It executes the SQL
if (!mysqli_stmt_execute($stmt))
  {
  mysqli_stmt_close($stmt);
  die('Error: ' . mysqli_error($conn));
  }
else{
mysqli_stmt_close($stmt);
echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteclientauthorizations.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}}
else
{// edit
// Validare și sanitizare input
$ID_Client = isset($_POST["ID_Client"]) && is_numeric($_POST["ID_Client"]) ? (int)$_POST["ID_Client"] : null;
$ID_Autorizatie = isset($_POST["ID_Autorizatie"]) && is_numeric($_POST["ID_Autorizatie"]) ? (int)$_POST["ID_Autorizatie"] : null;
$Numar = isset($_POST["Autorizatie_Client_Numar"]) ? trim($_POST["Autorizatie_Client_Numar"]) : '';
$Tip = isset($_POST["Autorizatie_Client_Tip"]) ? trim($_POST["Autorizatie_Client_Tip"]) : '';

$strEData1 = isset($_POST["strEData1"]) ? (int)$_POST["strEData1"] : 0;
$strEData2 = isset($_POST["strEData2"]) ? (int)$_POST["strEData2"] : 0;
$strEData3 = isset($_POST["strEData3"]) ? (int)$_POST["strEData3"] : 0;
$dataexpirarii = sprintf("%04d-%02d-%02d", $strEData3, $strEData2, $strEData1);

$strIData1 = isset($_POST["strIData1"]) ? (int)$_POST["strIData1"] : 0;
$strIData2 = isset($_POST["strIData2"]) ? (int)$_POST["strIData2"] : 0;
$strIData3 = isset($_POST["strIData3"]) ? (int)$_POST["strIData3"] : 0;
$dataemiterii = sprintf("%04d-%02d-%02d", $strIData3, $strIData2, $strIData1);

$strVData1 = isset($_POST["strVData1"]) ? (int)$_POST["strVData1"] : 0;
$strVData2 = isset($_POST["strVData2"]) ? (int)$_POST["strVData2"] : 0;
$strVData3 = isset($_POST["strVData3"]) ? (int)$_POST["strVData3"] : 0;
$datavizei = sprintf("%04d-%02d-%02d", $strVData3, $strVData2, $strVData1);

if (!$ID_Client || !$ID_Autorizatie || !isset($cID)) {
    echo "<div class=\"alert callout\">Date invalide</div>";
    die;
}

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, "UPDATE clienti_autorizatii_clienti SET ID_Client=?, Autorizatie_Client_Numar=?, Autorizatie_Client_Tip=?, ID_Autorizatie=?, ID_User=?, Autorizatie_Client_Emitere=?, Autorizatie_Client_Viza=?, Autorizatie_Client_Expirare=? WHERE ID_Autorizatie_Client=?");
mysqli_stmt_bind_param($stmt, 'isssisssi', $ID_Client, $Numar, $Tip, $ID_Autorizatie, $code, $dataemiterii, $datavizei, $dataexpirarii, $cID);
if (!mysqli_stmt_execute($stmt))
  {
  mysqli_stmt_close($stmt);
  die('Error: ' . mysqli_error($conn));
  }
else{
mysqli_stmt_close($stmt);
echo "<div class=\"callout success\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteclientauthorizations.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
}
}
else {
?>
        <?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="siteclientauthorizations.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post" action="siteclientauthorizations.php?mode=new">
            <div class="grid-x grid-margin-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strTitle?>
                        <select name="ID_Client" class="required">
                            <option value=""><?php echo $strClient?></option>
                            <?php 
		  $sql="SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, Contract_Alocat, Client_Denumire FROM clienti_date, clienti_contracte 
			WHERE Contract_Alocat='$code' AND  clienti_date.ID_Client=clienti_contracte.ID_Client
			ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                            <option value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
                            <?php
}?>
                        </select></label>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strAuthorizations?>< <select name="ID_Autorizatie" class="required">
                            <option value=""><?php echo $strAuthorizations?></option>
                            <?php $sql = "Select * FROM clienti_autorizatii ORDER BY Autorizatie ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                            <option value="<?php echo $rss["ID_autorizatii"]?>"><?php echo $rss["Autorizatie"]?>
                            </option>
                            <?php
}?>
                            </select></label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strNumber?>
                        <input name="Autorizatie_Client_Numar" type="text" size="30" value="" />
                    </label>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strType?>
                        <input name="Autorizatie_Client_Tip" type="radio" value="0" checked />
                        <?php echo $strLimitedTime?>&nbsp;&nbsp;<input name="Autorizatie_Client_Tip" type="radio"
                            value="1"><?php echo $strNoLimit?>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strIssuedDate ." ". $strDay?>
                        <select name="strIData1">
                            <option value="00" selected>--</option>
                            <?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
    		
    		// create option With numeric value of day
    		echo "<option value=\"$d\">$d</option>";} 
?>
                        </select></label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strIssuedDate ." ". $strMonth?>
                        <select name="strIData2">
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
    			echo "<option value=\"$m\">$monthname</option>";} 
			?>
                        </select> </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strIssuedDate ." ". $strYear?></label>

                    <select name="strIData3">
                        <option value="0000" selected>--</option>
                        <?php
		$cy=date("Y");
		$fy=$cy+10;
		$py=$cy-10;
		for ( $y = $py; $y <= $fy; $y ++) {
    	echo "<option value=\"$y\">$y</option>";} 
			?>
                    </select></label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strExpiryDate." ".$strDay?>
                        <select name="strEData1">
                            <option value="00" selected>--</option>
                            <?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
    		
    		// create option With numeric value of day
    		echo "<option value=\"$d\">$d</option>";} 
?>
                        </select> </label>
                    </select>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strExpiryDate." ".$strMonth?>
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
   			echo "<option value=\"$m\">$monthname</option>";} 
			?>
                        </select> </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strExpiryDate." ".$strYear?>

                        <select name="strEData3">
                            <option value="0000" selected>--</option>
                            <?php
		$cy=date("Y");
		$fy=$cy+10;
		$py=$cy-10;
		for ( $y = $py; $y <= $fy; $y ++) {
    	echo "<option value=\"$y\">$y</option>";} 
			?>
                        </select></label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strVisaDeadline ." ".$strDay?>
                        <select name="strVData1">
                            <option value="00" selected>--</option>
                            <?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
    		
    		// create option With numeric value of day
    		echo "<option value=\"$d\">$d</option>";} 
?>
                        </select> </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strVisaDeadline ." ".$strMonth?>

                        <select name="strVData2">
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
   			echo "<option value=\"$m\">$monthname</option>";} 
			?>
                        </select> </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strVisaDeadline ." ".$strYear?>

                        <select name="strVData3">
                            <option value="0000" selected>--</option>
                            <?php
		$cy=date("Y");
		$fy=$cy+10;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
    	echo "<option value=\"$y\">$y</option>";} 
			?>
                        </select></label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"> <input type="submit" value="<?php echo $strAdd?>"
                        name="Submit" class="button success">
                </div>
            </div>
        </form>

        <?php
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.ID_Aloc, 
clienti_autorizatii_clienti.ID_Autorizatie, clienti_autorizatii.ID_autorizatii, Autorizatie,
Autorizatie_Client_Emitere, Autorizatie_Client_Expirare, Autorizatie_Client_Viza, Autorizatie_Client_Numar, Autorizatie_Client_Tip
FROM 
clienti_autorizatii_clienti, clienti_date, clienti_autorizatii
WHERE ID_Autorizatie_Client=$_GET[cID] AND clienti_date.ID_Aloc='$code' AND clienti_date.ID_Client=clienti_autorizatii_clienti.ID_Client AND clienti_autorizatii_clienti.ID_Autorizatie=clienti_autorizatii.ID_autorizatii
ORDER By Client_Denumire ASC";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="siteclientauthorizations.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post"  action="siteclientauthorizations.php?mode=edit&cID=<?php echo htmlspecialchars($cID, ENT_QUOTES, 'UTF-8')?>">
            <div class="grid-x grid-margin-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strTitle?>
                        <select name="ID_Client" class="required">
                            <?php 
		  $sql="SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, Contract_Alocat, Client_Denumire FROM clienti_date, clienti_contracte 
			WHERE Contract_Alocat='$code' AND  clienti_date.ID_Client=clienti_contracte.ID_Client
			ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                            <option <?php if ($row["ID_Client"]==$rss["ID_Client"]) echo "selected"; ?>
                                value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
                            <?php
}?>
                        </select></label>
                </div>
                <div class="large-6 medium-6 small-6 cell">

                    <label><?php echo $strAuthorizations?>
                        <select name="ID_Autorizatie" class="required">
                            <?php $sql = "Select * FROM clienti_autorizatii ORDER BY Autorizatie ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                            <option <?php if ($row["ID_Autorizatie"]==$rss["ID_autorizatii"]) echo "selected"; ?>
                                value="<?php echo $rss["ID_autorizatii"]?>"><?php echo $rss["Autorizatie"]?></option>
                            <?php
}?>
                        </select></label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strNumber?>
                        <input name="Autorizatie_Client_Numar" type="text" size="30"
                            value="<?php echo $row['Autorizatie_Client_Numar']?>" />
                    </label>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strType?>
                        <input name="Autorizatie_Client_Tip" type="radio" value="0"
                            <?php If ($row["Autorizatie_Client_Tip"]==0) echo "checked"?> />
                        <?php echo $strLimitedTime?>&nbsp;&nbsp;<input name="Autorizatie_Client_Tip" type="radio"
                            value="1"
                            <?php If ($row["Autorizatie_Client_Tip"]==1) echo "checked"?>><?php echo $strNoLimit?>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strIssuedDate ." " .$strDay?>
                        <select name="strIData1" class="new">
                            <option value="00" selected>--</option>
                            <?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
			If ($row["Autorizatie_Client_Emitere"]=="0000-00-00")
			{$day=0;}
		else
		{$day=date("d", strtotime($row['Autorizatie_Client_Emitere']));	}
    		// create option With numeric value of day
			if ($day==$d){
    		echo "<option selected value=\"$d\">$d</option>";}
			else {echo "<option value=\"$d\">$d</option>";}
			} 
?>
                        </select> </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strIssuedDate ." " .$strMonth?>
                        <select name="strIData2">
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
			If ($row["Autorizatie_Client_Emitere"]=="0000-00-00")
			{$month=0;}
		else
		{$month=date("m", strtotime($row['Autorizatie_Client_Emitere']));	}
    		// create option With numeric value of day
			if ($month==$m){
    			echo "<option selected value=\"$m\">$monthname</option>";}
				else
				{echo "<option value=\"$m\">$monthname</option>";}
				} 
			?>
                        </select> </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strIssuedDate ." " .$strYear?>
                        <select name="strIData3">
                            <option value="0000" selected>--</option>
                            <?php
		$cy=date("Y");
		$fy=$cy+5;
		$py=$cy-70;
		for ( $y = $py; $y <= $fy; $y ++) {
			If ($row["Autorizatie_Client_Emitere"]=="0000-00-00")
			{$year=0000;}
		else
		{			$year=date("Y", strtotime($row['Autorizatie_Client_Emitere']));	}
    		// create option With numeric value of day
			if ($year==$y){
    	echo "<option selected value=\"$y\">$y</option>";}
		else{
		echo "<option value=\"$y\">$y</option>";
		} }
			?>
                        </select></label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strExpiryDate." ".$strDay?>
                        <select name="strEData1" class="new">
                            <option value="00" selected>--</option>
                            <?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
			If ($row["Autorizatie_Client_Expirare"]=="0000-00-00")
			{$day=0;}
		else
		{$day=date("d", strtotime($row['Autorizatie_Client_Expirare']));}	
    		// create option With numeric value of day
			if ($day==$d){
    		echo "<option selected value=\"$d\">$d</option>";}
			else {echo "<option value=\"$d\">$d</option>";}
			} 
?>
                        </select></label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strExpiryDate." ".$strMonth?>
                        <select name="strEData2">
                            <label><?php echo $strExpiryDate." ".$strMonth?>
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
			If ($row["Autorizatie_Client_Expirare"]=="0000-00-00")
			{$month=0;}
		else {
			$month=date("m", strtotime($row['Autorizatie_Client_Expirare']));	
		}
    		// create option With numeric value of day
			if ($month==$m){
    			echo "<option selected value=\"$m\">$monthname</option>";}
				else
				{echo "<option value=\"$m\">$monthname</option>";}
				} 
			?>
                        </select> </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strExpiryDate." ".$strYear?>
                        <select name="strEData3">
                            <option value="0000" selected>--</option>
                            <?php
		$cy=date("Y");
		$fy=$cy+10;
		$py=$cy-10;
		for ( $y = $py; $y <= $fy; $y ++) {
			If ($row["Autorizatie_Client_Expirare"]=="0000-00-00")
			{$year=0000;}
		else
		{			$year=date("Y", strtotime($row['Autorizatie_Client_Expirare']));}	
    		// create option With numeric value of day
			if ($year==$y){
    	echo "<option selected value=\"$y\">$y</option>";}
		else{
		echo "<option value=\"$y\">$y</option>";
		} }
			?>
                        </select></label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strVisaDeadline ." ".$strDay?>
                        <select name="strVData1" class="new">
                            <option value="00" selected>--</option>
                            <?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
			If ($row["Autorizatie_Client_Viza"]=="0000-00-00")
			{$day=0;}
		else
		{$day=date("d", strtotime($row['Autorizatie_Client_Viza']));}	
    		// create option With numeric value of day
			if ($day==$d){
    		echo "<option selected value=\"$d\">$d</option>";}
			else {echo "<option value=\"$d\">$d</option>";}
			} 
?>
                        </select></label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strVisaDeadline ." ".$strMonth?>
                        <select name="strVData2">
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
			If ($row["Autorizatie_Client_Viza"]=="0000-00-00")
			{$month=0;}
		else {
			$month=date("m", strtotime($row['Autorizatie_Client_Viza']));	
		}
    		// create option With numeric value of day
			if ($month==$m){
    			echo "<option selected value=\"$m\">$monthname</option>";}
				else
				{echo "<option value=\"$m\">$monthname</option>";}
				} 
			?>
                        </select> </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strVisaDeadline ." ".$strYear?>
                        <select name="strVData3">
                            <option value="0000" selected>--</option>
                            <?php
		$cy=date("Y");
		$fy=$cy+10;
		$py=$cy-10;
		for ( $y = $py; $y <= $fy; $y ++) {
			If ($row["Autorizatie_Client_Viza"]=="0000-00-00")
			{$year=0000;}
		else
		{			$year=date("Y", strtotime($row['Autorizatie_Client_Viza']));}	
    		// create option With numeric value of day
			if ($year==$y){
    	echo "<option selected value=\"$y\">$y</option>";}
		else{
		echo "<option value=\"$y\">$y</option>";
		} }
			?>
                        </select></label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"> <input type="submit" value="<?php echo $strModify?>"
                        name="Submit" class="button success" />
                </div>
            </div>
        </form>
        <?php
}
else
{

echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"siteclientauthorizations.php?mode=new\" class=\"button\"><i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i>&nbsp;$strAdd</a></div></div>";
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, 
clienti_autorizatii_clienti.ID_Client, clienti_autorizatii_clienti.ID_Autorizatie, clienti_autorizatii_clienti.ID_User, clienti_autorizatii_clienti.Autorizatie_Client_Emitere, clienti_autorizatii_clienti.Autorizatie_Client_Expirare, 
clienti_autorizatii.ID_autorizatii, Autorizatie, clienti_autorizatii_clienti.Autorizatie_Client_Viza,
ID_Autorizatie_Client
FROM 
clienti_autorizatii_clienti, clienti_date, clienti_autorizatii
WHERE
clienti_date.ID_Client=clienti_autorizatii_clienti.ID_Client AND clienti_autorizatii_clienti.ID_Autorizatie=clienti_autorizatii.ID_autorizatii
";
if ($_SESSION['clearence']=='USER')
{
$query.=" AND clienti_autorizatii_clienti.ID_User='$code'";
}
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY Client_Denumire ASC $pages->limit";
$result=ezpub_query($conn,$query);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
?>
        <div class="paginate">
            <?php
echo $strTotal . " " .$numar." ".$strAuthorizations ;
echo " <br /><br />";
echo $pages->display_pages();
echo " <br /><br /></div>";

?>
        <table width="100%">
            <thead>
                <tr>
                    <th width="40%"><?php echo $strClient?></th>
                    <th width="20%"><?php echo $strTitle?></th>
                    <th width="10%"><?php echo $strIssuedDate?></th>
                    <th width="10%"><?php echo $strExpiryDate?></th>
                    <th width="10%"><?php echo $strVisaDeadline?></th>
                    <th width="5%"><?php echo $strEdit?></th>
                    <th width="5%"><?php echo $strDelete?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[Autorizatie]</td><td>"; 
			If ($row["Autorizatie_Client_Emitere"]!="") {echo date("d.m.Y",strtotime($row["Autorizatie_Client_Emitere"]));};
			echo "</td><td>"; 
			If ($row["Autorizatie_Client_Expirare"]!="") {echo date("d.m.Y",strtotime($row["Autorizatie_Client_Expirare"]));};
			echo "</td><td>";
			If ($row["Autorizatie_Client_Viza"]!="") {echo date("d.m",strtotime($row["Autorizatie_Client_Viza"]));};
			echo "</td>
			  <td><a href=\"siteclientauthorizations.php?mode=edit&cID=$row[ID_Autorizatie_Client]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"siteclientauthorizations.php?mode=delete&cID=$row[ID_Autorizatie_Client]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td colspan=\"7\">&nbsp;</td></tr></tfoot></table>";
}
echo " <div class=\"paginate\">";
echo $pages->display_pages();
echo "</div>";
}
}
?>
    </div>
</div>
<?php
include '../bottom.php';
?>