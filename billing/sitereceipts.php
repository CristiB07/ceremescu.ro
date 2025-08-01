<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle="Administrare chitanțe";
include '../dashboard/header.php';
$day = date('d');
$year = date('Y');
$month = date('m');

?>
<div class="grid-x grid-padding-x ">
<div class="large-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM facturare_chitante WHERE chitanta_ID=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitereceipts.php\"
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
$inchisa=1;
$dataincasarii = $_POST["strAData3"] ."-". $_POST["strAData2"] ."-". $_POST["strAData1"] ."";
$strWhereClause = " WHERE facturare_chitante.chitanta_ID=" . $_GET["cID"] . ";";
$query= "UPDATE facturare_chitante SET facturare_chitante.chitanta_factura_ID='" .$_POST["chitanta_factura_ID"] . "' ," ;
$query= $query . " facturare_chitante.chitanta_data_incasarii='" .$dataincasarii . "', "; 
$query= $query . " facturare_chitante.chitanta_suma_incasata='" .$_POST["chitanta_suma_incasata"] . "', "; 
$query= $query . " facturare_chitante.chitanta_inchisa='" .$inchisa . "', "; 
$query= $query . " facturare_chitante.chitanta_descriere='" .$_POST["chitanta_descriere"] . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
$nsql="DELETE FROM facturare_chitante WHERE chitanta_inchisa IS NULL;";
ezpub_query($conn,$nsql);
    
	$sql1 = "Select factura_data_emiterii FROM facturare_facturi WHERE factura_ID =$_POST[chitanta_factura_ID]";
        $result1 = ezpub_query($conn,$sql1);
		$row1=ezpub_fetch_array($result1);
		$dataemiterii=strtotime($row1["factura_data_emiterii"]);
		$incasare=strtotime($dataincasarii);
		$datediff=$incasare-$dataemiterii;
		$zile=round($datediff / (60 * 60 * 24));
		
$usql="UPDATE facturare_facturi SET factura_client_achitat='1', factura_client_data_achitat='$dataincasarii', factura_client_zile_achitat='$zile', factura_client_achitat_prin='0' WHERE factura_ID=$_POST[chitanta_factura_ID];";
ezpub_query($conn,$usql);


echo "<div class=\"callout success\"><p>$strRecordAdded</p>";
echo"			    <div class=\"grid-x grid-margin-x\">
			  <div class=\"large-12 medium-12 small-12 cell\">
			  <p>
			  <a href=\"receipt.php?cID=$_GET[cID]\" class=\"button\"><i class=\"fas fa-file-pdf\"></i>&nbsp;$strPrint</a>
			  </p>
</div>
</div>";

echo"</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitereceipts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{// edit
$dataincasarii = $_POST["strAData3"] ."-". $_POST["strAData2"] ."-". $_POST["strAData1"] ."";
$strWhereClause = " WHERE facturare_chitante.chitanta_ID=" . $_GET["cID"] . ";";
$query= "UPDATE facturare_chitante SET facturare_chitante.chitanta_factura_ID='" .$_POST["chitanta_factura_ID"] . "' ," ;
$query= $query . " facturare_chitante.chitanta_data_incasarii='" .$dataincasarii . "', "; 
$query= $query . " facturare_chitante.chitanta_suma_incasata='" .$_POST["chitanta_suma_incasata"] . "', "; 
$query= $query . " facturare_chitante.chitanta_descriere='" .$_POST["chitanta_descriere"] . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
Else{
$usql="UPDATE facturare_facturi SET factura_client_achitat='1', factura_client_data_achitat='$dataincasarii' WHERE factura_ID=$_POST[chitanta_factura_ID];";
ezpub_query($conn,$usql);
echo "<div class=\"callout success\"><p>$strRecordModified</p>";
echo"			    <div class=\"grid-x grid-margin-x\">
			  <div class=\"large-12 medium-12 small-12 cell\">
			  <p>
			  <a href=\"receipt.php?cID=$_GET[cID]\" class=\"button\"><i class=\"fas fa-file-pdf\"></i>&nbsp;$strPrint</a>
			  </p>
</div>
</div>";

echo"</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitereceipts.php\"
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
<script src="<?php echo $strSiteURL ?>/js/foundation/jquery.js"></script>
<script>
$(document).ready(function() {
    $('select[name="chitanta_factura_ID"]').change(function() {  
	jQuery.ajax({
	url: "receipt_sum.php",
	dataType: "json",
	data:'factura_ID='+$(this).val(),
	type: "POST",
	  success: function(data) {
		  try {
            $("#chitanta_suma_incasata").val(data["suma"]);  
			$("#chitanta_descriere").val(data["factura"]);  
		   }
catch(err) {
  document.getElementById("response").innerHTML = err.message;
}
        },
        error : function(){
           alert('Some error occurred!');
        }
    });
});
});
</script>
<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
$query="Select chitanta_numar FROM facturare_chitante WHERE chitanta_inchisa='1' ORDER BY chitanta_numar DESC";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
If (!isSet($row["chitanta_numar"]))
{$numarfactura=1;}
Else
{$numarfactura=(int)$row["chitanta_numar"]+1;}

$mSQL = "INSERT INTO facturare_chitante(";
	$mSQL = $mSQL . "chitanta_numar)";
	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$numarfactura . "') ";		
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
	$receiptID=ezpub_inserted_id($conn);
}
?>
<form Method="post" id="users" Action="sitereceipts.php?mode=new&cID=<?php echo $receiptID?>" >
	<div class="grid-x grid-padding-x ">
		 <div class="large-12 medium-12 cell">
		 <label> <?php echo $strInvoice?></label>
		<select name="chitanta_factura_ID">
		<option value="" selected>--</option>
		<?php
		$query="SELECT factura_ID, factura_numar, factura_data_emiterii, factura_client_denumire FROM facturare_facturi WHERE factura_client_achitat=0 ORDER BY factura_data_emiterii DESC";
$result=ezpub_query($conn,$query);
  while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["factura_ID"]?>"><?php echo $rss["factura_client_denumire"]." - CNS0000". $rss["factura_numar"]." - ". $rss["factura_data_emiterii"]?></option>
          <?php
}?>
        </select>
		</div>
		</div>
           <div class="grid-x grid-padding-x ">
              <div class="large-4 medium-4 cell">
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
		 <div class="large-4 medium-4 cell">
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
		 <div class="large-4 medium-4 cell">
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
		</div>
		<div class="grid-x grid-padding-x ">
		<div class="large-4 medium-4 cell">
		<label><?php echo $strSum?></label>
		<input name="chitanta_suma_incasata" id="chitanta_suma_incasata" type="text" size="50" class="required" value=""/>
		</div>
		<div class="large-4 medium-4 cell">
		<label><?php echo $strDetails?></label>
		<textarea name="chitanta_descriere" id="chitanta_descriere" style="width:100%;"></textarea>
		</div>
		<div class="large-4 medium-4 cell">
		<label><?php echo $strNumber?></label>
		<input name="chitanta_numar" id="chitanta_numar" type="text" size="50" class="required" value="CNS0000<?php echo $numarfactura?>"/>
		</div>
		</div>
	           <div class="grid-x grid-padding-x ">
              <div class="large-12 text-center cell">
			  <input type="submit" value="<?php echo $strAdd?>" class="button" name="Submit"> 
		</div>
		</div>
  </FORM>
</fieldset>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM facturare_chitante WHERE chitanta_ID=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitereceipts.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form Method="post" id="users" Action="sitereceipts.php?mode=edit&cID=<?php echo $row['chitanta_ID']?>" >
            <div class="grid-x grid-padding-x ">
              <div class="large-4 medium-4 cell">
			 <label> <?php echo $strDay?></label>
      <select name="strAData1">
	  <option value="00" selected>--</option>
<?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
    		
    		// create option With numeric value of day
			$day=date("d", strtotime($row['chitanta_data_incasarii']));
    		if ($day==$d){
    		echo "<OPTION selected value=\"$d\">$d</OPTION>";}
			else {echo "<OPTION value=\"$d\">$d</OPTION>";}
			} 
?>
        </select> 
		</div>
		 <div class="large-4 medium-4 cell">
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
			If ($row["chitanta_data_incasarii"]=="0000-00-00")
			{$month=0;}
		Else
		{$month=date("m", strtotime($row['chitanta_data_incasarii']));	}
    		// create option With numeric value of day
			if ($month==$m){
    			echo "<OPTION selected value=\"$m\">$monthname</OPTION>";}
				Else
				{echo "<OPTION value=\"$m\">$monthname</OPTION>";}
				} 
			?>
        </select> 
		</div>
		 <div class="large-4 medium-4 cell">
		 <label> <?php echo $strYear?></label>
		<select name="strAData3">
		<option value="0000" selected>--</option>
		<?php
		$cy=date("Y");
		$fy=$cy+1;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
			$year=date("Y", strtotime($row['chitanta_data_incasarii']));
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
		<div class="large-6 medium-6 cell">
		<label><?php echo $strSum?></label>
		<input name="chitanta_suma_incasata" type="text" size="50" class="required" value="<?php echo $row["chitanta_suma_incasata"]?>"/>
		</div>
		<div class="large-6 medium-6 cell">
		<label><?php echo $strDetails?></label>
		<textarea name="chitanta_descriere" style="width:100%;"><?php echo $row["chitanta_descriere"]?>"</textarea>
		</div>
		</div>
		<div class="grid-x grid-padding-x ">
		 <div class="large-12 medium-12 cell">
		 <label> <?php echo $strInvoice?></label>
		<select name="chitanta_factura_ID">
		<option value="" selected>--</option>
		<?php
		$query="SELECT factura_ID, factura_numar, factura_data_emiterii, factura_client_denumire FROM facturare_facturi ORDER BY factura_data_emiterii DESC";
$result=ezpub_query($conn,$query);
  while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["factura_ID"]?>" <?php IF ($row["chitanta_factura_ID"]==$rss["factura_ID"]) echo "selected"?>><?php echo $rss["factura_client_denumire"]." - CNS0000". $rss["factura_numar"]." - ". $rss["factura_data_emiterii"]?></option>
          <?php
}?>
        </select>
		</div>
		</div>
	           <div class="grid-x grid-padding-x ">
              <div class="large-12 text-center cell">
			  <input type="submit" value="<?php echo $strAdd?>" class="button" name="Submit"> 
		</div>
		</div>
  </fieldset>

<?php
}
Else
{
	?>
	  <div class="grid-x grid-padding-x ">
              <div class="large-12 medium-12 small-12 cell">
	<?php
echo "<a href=\"sitereceipts.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a>";
?></div>
</div>
<?php $query="SELECT * FROM facturare_chitante";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY chitanta_data_incasarii DESC $pages->limit";
$result=ezpub_query($conn,$query);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<div class="paginate">
<?php
echo $strTotal . " " .$numar." ".$strReceipts;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"sitereceipts.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
</div>
<table width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strNumber?></th>
			<th><?php echo $strTitle?></th>
			<th><?php echo $strSum?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strView?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>CNS0000$row[chitanta_numar]</td>
			<td>$row[chitanta_descriere]</td>
			<td>$row[chitanta_suma_incasata]</td>
			  <td><a href=\"sitereceipts.php?mode=edit&cID=$row[chitanta_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			  <td><a href=\"receipt.php?cID=$row[chitanta_ID]\" ><i class=\"far fa-file-pdf\" title=\"$strView\"></i></a></td>
			<td><a href=\"cancelreceipt.php?cID=$row[chitanta_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"large fas fa-ban\" title=\"$strCancel\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"4\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";?>
<div class="paginate">
<?php
echo $pages->display_pages() . " <a href=\"sitereceipts.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
</div>
<?php
}
}
}
?>
</div>
</div>
<?php
include '../bottom.php';
?>