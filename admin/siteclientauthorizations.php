<?php
//update 29.07.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare autorizații";
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
$code=$_SESSION['$code'];
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM date_autorizatii_clienti WHERE ID_Autorizatie_Client=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteclientauthorizations.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){
//insert new user
$dataexpirarii = $_POST["strEData3"] ."-". $_POST["strEData2"] ."-". $_POST["strEData1"] ."";
$dataemiterii = $_POST["strIData3"] ."-". $_POST["strIData2"] ."-". $_POST["strIData1"] ."";
$datavizei = $_POST["strVData3"] ."-". $_POST["strVData2"] ."-". $_POST["strVData1"] ."";


	$mSQL = "INSERT INTO date_autorizatii_clienti(";
	$mSQL = $mSQL . "ID_Client,";
	$mSQL = $mSQL . "ID_Autorizatie,";
	$mSQL = $mSQL . "ID_User,";
	$mSQL = $mSQL . "Autorizatie_Client_Numar,";
	$mSQL = $mSQL . "Autorizatie_Client_Tip,";
	$mSQL = $mSQL . "Autorizatie_Client_Expirare,";
	$mSQL = $mSQL . "Autorizatie_Client_Viza,";
	$mSQL = $mSQL . "Autorizatie_Client_Emitere)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$_POST["ID_Client"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["ID_Autorizatie"] . "', ";
	$mSQL = $mSQL . "'" .$uid . "', ";
	$mSQL = $mSQL . "'" .$_POST["Autorizatie_Client_Numar"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Autorizatie_Client_Tip"] . "', ";
	$mSQL = $mSQL . "'" .$dataexpirarii . "', ";
	$mSQL = $mSQL . "'" .$datavizei . "', ";
	$mSQL = $mSQL . "'" .$dataemiterii . "') ";
			
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteclientauthorizations.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{// edit
$dataexpirarii = $_POST["strEData3"] ."-". $_POST["strEData2"] ."-". $_POST["strEData1"] ."";
$dataemiterii = $_POST["strIData3"] ."-". $_POST["strIData2"] ."-". $_POST["strIData1"] ."";
$datavizei = $_POST["strVData3"] ."-". $_POST["strVData2"] ."-". $_POST["strVData1"] ."";
$strWhereClause = " WHERE date_autorizatii_clienti.ID_Autorizatie_Client=" . $_GET["cID"] . ";";
$query= "UPDATE date_autorizatii_clienti SET date_autorizatii_clienti.ID_Client='" .$_POST["ID_Client"] . "' ," ;
$query= $query . " date_autorizatii_clienti.Autorizatie_Client_Numar='" .$_POST["Autorizatie_Client_Numar"] . "' ," ;
$query= $query . " date_autorizatii_clienti.Autorizatie_Client_Tip='" .$_POST["Autorizatie_Client_Tip"] . "' ," ;
$query= $query . " date_autorizatii_clienti.ID_Autorizatie='" .$_POST["ID_Autorizatie"] . "' ," ;
$query= $query . " date_autorizatii_clienti.ID_User='" .$uid . "' ," ;
$query= $query . " date_autorizatii_clienti.Autorizatie_Client_Emitere='" .$dataemiterii . "' ," ;
$query= $query . " date_autorizatii_clienti.Autorizatie_Client_Viza='" .$datavizei . "' ," ;
$query= $query . " date_autorizatii_clienti.Autorizatie_Client_Expirare='" .$dataexpirarii . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteclientauthorizations.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}
}
}
Else {
?>
<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="siteclientauthorizations.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form Method="post" Action="siteclientauthorizations.php?mode=new" >
			    <div class="grid-x grid-margin-x">
			  <div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strTitle?></label>
	  <select name="ID_Client" class="required">
           <option value=""><?php echo $strClient?></option>
          <?php 
		  $sql="SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, Contract_Alocat, Client_Denumire FROM clienti_date, clienti_contracte 
			WHERE Contract_Alocat='$code' AND  clienti_date.ID_Client=clienti_contracte.ID_Client
			ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
          <?php
}?>
        </select>
		</div>
<div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strAuthorizations?></label>
	  <select name="ID_Autorizatie" class="required">
           <option value=""><?php echo $strAuthorizations?></option>
          <?php $sql = "Select * FROM date_autorizatii ORDER BY Autorizatie ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["ID_autorizatii"]?>"><?php echo $rss["Autorizatie"]?></option>
          <?php
}?>
        </select>
	</div>
	</div>
			    <div class="grid-x grid-margin-x">
			  <div class="large-6 medium-6 small-6 cell">
			  <label><?php echo $strNumber?></label>
	  <input name="Autorizatie_Client_Numar" Type="text" size="30" value="" />
		</div>
<div class="large-6 medium-6 small-6 cell">
      <label><?php echo $strType?></label>
      <input name="Autorizatie_Client_Tip" Type="radio" value="0" checked /> <?php echo $strLimitedTime?>&nbsp;&nbsp;<input name="Autorizatie_Client_Tip" Type="radio" value="1" ><?php echo $strNoLimit?>
	</div>
	</div>
			    		  <div class="grid-x grid-margin-x">
     <div class="large-4 medium-4 small-4 cell">
      <label><?php echo $strIssuedDate ." ". $strDay?></label>
       <select name="strIData1">
	  <option value="00" selected>--</option>
<?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
    		
    		// create option With numeric value of day
    		echo "<OPTION value=\"$d\">$d</OPTION>";} 
?>
        </select> </div>
		<div class="large-4 medium-4 small-4 cell">
      <label><?php echo $strIssuedDate ." ". $strMonth?></label>
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
    			echo "<OPTION value=\"$m\">$monthname</OPTION>";} 
			?>
        </select> 
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
    	echo "<OPTION value=\"$y\">$y</OPTION>";} 
			?>
        </select>
</div>
</div>
	  <div class="grid-x grid-margin-x">
		<div class="large-4 medium-4 small-4 cell">
	        <label><?php echo $strExpiryDate." ".$strDay?></label>
       <select name="strEData1">
	  <option value="00" selected>--</option>
<?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
    		
    		// create option With numeric value of day
    		echo "<OPTION value=\"$d\">$d</OPTION>";} 
?>
        </select> 
</select> </div>
		<div class="large-4 medium-4 small-4 cell">
	        <label><?php echo $strExpiryDate." ".$strMonth?></label>
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
   			echo "<OPTION value=\"$m\">$monthname</OPTION>";} 
			?>
        </select>  </div>
		<div class="large-4 medium-4 small-4 cell">
			        <label><?php echo $strExpiryDate." ".$strYear?></label>

		<select name="strEData3">
		<option value="0000" selected>--</option>
		<?php
		$cy=date("Y");
		$fy=$cy+10;
		$py=$cy-10;
		for ( $y = $py; $y <= $fy; $y ++) {
    	echo "<OPTION value=\"$y\">$y</OPTION>";} 
			?>
        </select>
</div>
	</div>
 	  <div class="grid-x grid-margin-x">
		<div class="large-4 medium-4 small-4 cell">
	        <label><?php echo $strVisaDeadline ." ".$strDay?></label>
       <select name="strVData1">
	  <option value="00" selected>--</option>
<?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
    		
    		// create option With numeric value of day
    		echo "<OPTION value=\"$d\">$d</OPTION>";} 
?>
        </select> 
</select> 
</div>
		<div class="large-4 medium-4 small-4 cell">
	        <label><?php echo $strVisaDeadline ." ".$strMonth?></label>
		
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
   			echo "<OPTION value=\"$m\">$monthname</OPTION>";} 
			?>
        </select>  </div>
		<div class="large-4 medium-4 small-4 cell">
			        <label><?php echo $strVisaDeadline ." ".$strYear?></label>

		<select name="strVData3">
		<option value="0000" selected>--</option>
		<?php
		$cy=date("Y");
		$fy=$cy+10;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
    	echo "<OPTION value=\"$y\">$y</OPTION>";} 
			?>
        </select>
</div>
	</div>
	  		  <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success"> 
	</div>
	</div>
  </form>

<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.ID_Aloc, 
date_autorizatii_clienti.ID_Autorizatie, date_autorizatii.ID_autorizatii, Autorizatie,
Autorizatie_Client_Emitere, Autorizatie_Client_Expirare, Autorizatie_Client_Viza, Autorizatie_Client_Numar, Autorizatie_Client_Tip
FROM 
date_autorizatii_clienti, clienti_date, date_autorizatii
WHERE ID_Autorizatie_Client=$_GET[cID] AND clienti_date.ID_Aloc='$code' AND clienti_date.ID_Client=date_autorizatii_clienti.ID_Client AND date_autorizatii_clienti.ID_Autorizatie=date_autorizatii.ID_autorizatii
ORDER By Client_Denumire ASC";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="siteclientauthorizations.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form Method="post" id="users" Action="siteclientauthorizations.php?mode=edit&cID=<?php echo $_GET["cID"]?>" >
			    <div class="grid-x grid-margin-x">
			  <div class="large-6 medium-6 small-6 cell">
			  <label><?php echo $strTitle?></label>
	  <select name="ID_Client" class="required">
          <?php 
		  $sql="SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, Contract_Alocat, Client_Denumire FROM clienti_date, clienti_contracte 
			WHERE Contract_Alocat='$code' AND  clienti_date.ID_Client=clienti_contracte.ID_Client
			ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  <?php if ($row["ID_Client"]==$rss["ID_Client"]) echo "selected"; ?> value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
          <?php
}?>
        </select></div >
	<div class="large-6 medium-6 small-6 cell">
	 
	  <label><?php echo $strAuthorizations?></label>
	  <select name="ID_Autorizatie" class="required">
          <?php $sql = "Select * FROM date_autorizatii ORDER BY Autorizatie ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  <?php if ($row["ID_Autorizatie"]==$rss["ID_autorizatii"]) echo "selected"; ?> value="<?php echo $rss["ID_autorizatii"]?>"><?php echo $rss["Autorizatie"]?></option>
          <?php
}?>
        </select>
		</div></div>
				    <div class="grid-x grid-margin-x">
			  <div class="large-6 medium-6 small-6 cell">			
	  <label><?php echo $strNumber?></label>
	  <input name="Autorizatie_Client_Numar" Type="text" size="30" value="<?php echo $row['Autorizatie_Client_Numar']?>" />
		</div>
	 <div class="large-6 medium-6 small-6 cell">	
      <label><?php echo $strType?></label>
      <input name="Autorizatie_Client_Tip" Type="radio" value="0" <?php If ($row["Autorizatie_Client_Tip"]==0) echo "checked"?> /> <?php echo $strLimitedTime?>&nbsp;&nbsp;<input name="Autorizatie_Client_Tip" Type="radio" value="1" <?php If ($row["Autorizatie_Client_Tip"]==1) echo "checked"?>><?php echo $strNoLimit?>
</div>
	</div>
			    		  <div class="grid-x grid-margin-x">
     <div class="large-4 medium-4 small-4 cell"> 
      <label><?php echo $strIssuedDate ." " .$strDay?></label>
     <select name="strIData1" class="new">
	  <option value="00" selected>--</option>
<?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
			If ($row["Autorizatie_Client_Emitere"]=="0000-00-00")
			{$day=0;}
		Else
		{$day=date("d", strtotime($row['Autorizatie_Client_Emitere']));	}
    		// create option With numeric value of day
			if ($day==$d){
    		echo "<OPTION selected value=\"$d\">$d</OPTION>";}
			else {echo "<OPTION value=\"$d\">$d</OPTION>";}
			} 
?>
        </select> 
		</div>
		  <div class="large-4 medium-4 small-4 cell"> 
		  <label><?php echo $strIssuedDate ." " .$strMonth?></label>
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
		Else
		{$month=date("m", strtotime($row['Autorizatie_Client_Emitere']));	}
    		// create option With numeric value of day
			if ($month==$m){
    			echo "<OPTION selected value=\"$m\">$monthname</OPTION>";}
				Else
				{echo "<OPTION value=\"$m\">$monthname</OPTION>";}
				} 
			?>
        </select> 
				</div>
		  <div class="large-4 medium-4 small-4 cell">
		  <label><?php echo $strIssuedDate ." " .$strYear?></label>
		<select name="strIData3">
		<option value="0000" selected>--</option>
		<?php
		$cy=date("Y");
		$fy=$cy+5;
		$py=$cy-70;
		for ( $y = $py; $y <= $fy; $y ++) {
			If ($row["Autorizatie_Client_Emitere"]=="0000-00-00")
			{$year=0000;}
		Else
		{			$year=date("Y", strtotime($row['Autorizatie_Client_Emitere']));	}
    		// create option With numeric value of day
			if ($year==$y){
    	echo "<OPTION selected value=\"$y\">$y</OPTION>";}
		Else{
		echo "<OPTION value=\"$y\">$y</OPTION>";
		} }
			?>
        </select>
</div>
</div>
 			    		  <div class="grid-x grid-margin-x">
     <div class="large-4 medium-4 small-4 cell"> 	  
      <label><?php echo $strExpiryDate." ".$strDay?></label>
        <select name="strEData1" class="new">
	  <option value="00" selected>--</option>
<?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
			If ($row["Autorizatie_Client_Expirare"]=="0000-00-00")
			{$day=0;}
		Else
		{$day=date("d", strtotime($row['Autorizatie_Client_Expirare']));}	
    		// create option With numeric value of day
			if ($day==$d){
    		echo "<OPTION selected value=\"$d\">$d</OPTION>";}
			else {echo "<OPTION value=\"$d\">$d</OPTION>";}
			} 
?>
        </select> </div>
		     <div class="large-4 medium-4 small-4 cell"> 
			  <label><?php echo $strExpiryDate." ".$strMonth?></label>
		<select name="strEData2">
		<label><?php echo $strExpiryDate." ".$strMonth?></label>
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
		Else {
			$month=date("m", strtotime($row['Autorizatie_Client_Expirare']));	
		}
    		// create option With numeric value of day
			if ($month==$m){
    			echo "<OPTION selected value=\"$m\">$monthname</OPTION>";}
				Else
				{echo "<OPTION value=\"$m\">$monthname</OPTION>";}
				} 
			?>
        </select> 
		</div>
		     <div class="large-4 medium-4 small-4 cell">
			 <label><?php echo $strExpiryDate." ".$strYear?></label>
		<select name="strEData3">
		<option value="0000" selected>--</option>
		<?php
		$cy=date("Y");
		$fy=$cy+10;
		$py=$cy-10;
		for ( $y = $py; $y <= $fy; $y ++) {
			If ($row["Autorizatie_Client_Expirare"]=="0000-00-00")
			{$year=0000;}
		Else
		{			$year=date("Y", strtotime($row['Autorizatie_Client_Expirare']));}	
    		// create option With numeric value of day
			if ($year==$y){
    	echo "<OPTION selected value=\"$y\">$y</OPTION>";}
		Else{
		echo "<OPTION value=\"$y\">$y</OPTION>";
		} }
			?>
        </select>
</div> 
</div> 
		  <div class="grid-x grid-margin-x">
     <div class="large-4 medium-4 small-4 cell"> 	  
      <label><?php echo $strVisaDeadline ." ".$strDay?></label>
        <select name="strVData1" class="new">
	  <option value="00" selected>--</option>
<?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
			If ($row["Autorizatie_Client_Viza"]=="0000-00-00")
			{$day=0;}
		Else
		{$day=date("d", strtotime($row['Autorizatie_Client_Viza']));}	
    		// create option With numeric value of day
			if ($day==$d){
    		echo "<OPTION selected value=\"$d\">$d</OPTION>";}
			else {echo "<OPTION value=\"$d\">$d</OPTION>";}
			} 
?>
        </select> </div>
		     <div class="large-4 medium-4 small-4 cell"> 
			       <label><?php echo $strVisaDeadline ." ".$strMonth?></label>
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
		Else {
			$month=date("m", strtotime($row['Autorizatie_Client_Viza']));	
		}
    		// create option With numeric value of day
			if ($month==$m){
    			echo "<OPTION selected value=\"$m\">$monthname</OPTION>";}
				Else
				{echo "<OPTION value=\"$m\">$monthname</OPTION>";}
				} 
			?>
        </select> 
		</div>
		     <div class="large-4 medium-4 small-4 cell">
			       <label><?php echo $strVisaDeadline ." ".$strYear?></label>
		<select name="strVData3">
		<option value="0000" selected>--</option>
		<?php
		$cy=date("Y");
		$fy=$cy+10;
		$py=$cy-10;
		for ( $y = $py; $y <= $fy; $y ++) {
			If ($row["Autorizatie_Client_Viza"]=="0000-00-00")
			{$year=0000;}
		Else
		{			$year=date("Y", strtotime($row['Autorizatie_Client_Viza']));}	
    		// create option With numeric value of day
			if ($year==$y){
    	echo "<OPTION selected value=\"$y\">$y</OPTION>";}
		Else{
		echo "<OPTION value=\"$y\">$y</OPTION>";
		} }
			?>
        </select>
</div> 
</div>
	  		  <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success" /> 
	</div>
	</div>
	</form>
<?php
}
Else
{

echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"siteclientauthorizations.php?mode=new\"><i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, 
date_autorizatii_clienti.ID_Client, date_autorizatii_clienti.ID_Autorizatie, date_autorizatii_clienti.ID_User, date_autorizatii_clienti.Autorizatie_Client_Emitere, date_autorizatii_clienti.Autorizatie_Client_Expirare, 
date_autorizatii.ID_autorizatii, Autorizatie, date_autorizatii_clienti.Autorizatie_Client_Viza,
ID_Autorizatie_Client
FROM 
date_autorizatii_clienti, clienti_date, date_autorizatii
WHERE
clienti_date.ID_Client=date_autorizatii_clienti.ID_Client AND date_autorizatii_clienti.ID_Autorizatie=date_autorizatii.ID_autorizatii
ORDER By Client_Denumire ASC";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<table width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strClient?></th>
			<th><?php echo $strTitle?></th>
			<th><?php echo $strIssuedDate?></th>
			<th><?php echo $strExpiryDate?></th>
			<th><?php echo $strVisaDeadline?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
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
}
}
?>
</div>
</div>
<?php
include '../bottom.php';
?>