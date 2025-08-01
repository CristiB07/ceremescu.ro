<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare încasări";
include '../dashboard/header.php';
$day = date('d');
$year = date('Y');
$month = date('m');

?>
<div class="grid-x grid-padding-x ">
<div class="large-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
$banca=$_POST["factura_client_banca_achitat"];
   $dataincasarii = $_POST["strAData3"] ."-". $_POST["strAData2"] ."-". $_POST["strAData1"] ."";
	$sql1 = "Select factura_data_emiterii, factura_client_valoare_totala FROM facturare_facturi WHERE factura_ID =$_POST[chitanta_factura_ID]";
        $result1 = ezpub_query($conn,$sql1);
		$row1=ezpub_fetch_array($result1);
		$dataemiterii=strtotime($row1["factura_data_emiterii"]);
		$sumaincasata=$row1["factura_client_valoare_totala"];
		$incasare=strtotime($dataincasarii);
		$datediff=$incasare-$dataemiterii;
		$zile=round($datediff / (60 * 60 * 24));	
$usql="UPDATE facturare_facturi SET factura_client_achitat='1', factura_client_data_achitat='$dataincasarii', factura_client_banca_achitat='$banca', factura_client_zile_achitat='$zile', factura_client_achitat_prin='1' WHERE factura_ID=$_POST[chitanta_factura_ID];";
ezpub_query($conn,$usql);

	$sql2 = "Select * FROM cash_banca";
        $result2 = ezpub_query($conn,$sql2);
		$row2=ezpub_fetch_array($result2);
		$transilvania=$row2["cash_banca_transilvania"];
		$ing=$row2["cash_banca_ING"];
		$unicredit=$row2["cash_banca_trezorerie"];
		If ($_POST["factura_client_banca_achitat"]=="ING")
		{
			$ing=$ing+$sumaincasata;
		}		
		ElseIf ($_POST["factura_client_banca_achitat"]=="Transilvania")
		{
			$transilvania=$transilvania+$sumaincasata;
		}
		Else
		{
			$unicredit=$unicredit+$sumaincasata;
		}

$usql2="UPDATE cash_banca SET cash_banca_ING='$ing', cash_banca_transilvania='$transilvania', cash_banca_trezorerie='$unicredit';";
ezpub_query($conn,$usql2);

echo "<div class=\"callout success\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecashin.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php'; 
die;
}

Else {
?>

<form Method="post" id="users" Action="sitecashin.php">
	<div class="grid-x grid-padding-x ">
		 <div class="large-12 medium-12 cell">
		 <label> <?php echo $strInvoice?></label>
		<select name="chitanta_factura_ID">
		<option value="" selected>--</option>
		<?php 
		if ((isset( $_GET['cID'])) && !empty( $_GET['cID'])){
			$cid=$_GET['cID'];
$query="SELECT factura_ID, factura_numar, factura_data_emiterii, factura_client_denumire FROM facturare_facturi WHERE factura_ID=$_GET[cID]";}
Else{
	$cid=0;
		$query="SELECT factura_ID, factura_numar, factura_data_emiterii, factura_client_denumire FROM facturare_facturi WHERE factura_client_achitat=0 AND factura_tip=0 ORDER BY factura_data_emiterii DESC";
}

$result=ezpub_query($conn,$query);
  while ($rss=ezpub_fetch_array($result)){

	?>
          <option  value="<?php echo $rss["factura_ID"]?>" <?php if ($rss["factura_ID"]==$cid) echo 'selected'?>><?php echo $rss["factura_client_denumire"]." - CNS0000". $rss["factura_numar"]." - ". $rss["factura_data_emiterii"]?></option>
  <?php }?>
        </select>
		</div>
		</div>
           <div class="grid-x grid-padding-x ">
              <div class="large-3 medium-3 cell">
			 <label> <?php echo $strDay?></label>
      <select name="strAData1">
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
		<select name="strAData2">
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
		<select name="strAData3">
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
		 <div class="large-3 medium-3 cell">
		 <label> <?php echo $strBank?></label>
		  <input name="factura_client_banca_achitat" Type="radio" value="Transilvania" checked /> <?php echo "Transilvania"?>&nbsp;&nbsp;
		  <input name="factura_client_banca_achitat" Type="radio" value="ING" /> <?php echo "ING"?>&nbsp;&nbsp;
		  <input name="factura_client_banca_achitat" Type="radio" value="Unicredit" ><?php echo "Trezorerie"?>
		</div>
		</div>
		           <div class="grid-x grid-padding-x ">
              <div class="large-12 text-center cell">
			  <input type="submit" value="<?php echo $strAdd?>" class="button success" name="Submit"> 
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