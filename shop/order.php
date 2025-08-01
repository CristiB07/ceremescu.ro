<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$url="order.php";
$strKeywords="Comandă, produse , HACCP";
$strDescription="Produsele consaltis.";
$strPageTitle="Comandă";

include '../header.php';
$buyer=$_SESSION['$buyer'];
echo "<div class=\"row\">
<div class=\"large-12 columns\">";
echo "<h1>$strPageTitle</h1>";
$query="SELECT * FROM magazin_comenzi where comanda_utilizator='$buyer' AND comanda_status=0";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result, $query);

If (isSet($_GET['action']) AND $_GET['action']=="order") {

If ($numar==0)
{
	
$IP= getRealIpAddr();
$data= date('Y-m-d H:i:s');
	$mSQL = "INSERT INTO magazin_comenzi(";
	$mSQL = $mSQL . "comanda_utilizator,";
	$mSQL = $mSQL . "comanda_deschisa,";
	$mSQL = $mSQL . "comanda_status,";
	$mSQL = $mSQL . "comanda_IP)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" . $buyer . "', ";
	$mSQL = $mSQL . "'" . $data . "', ";
	$mSQL = $mSQL . "'" . 0 . "', ";
	$mSQL = $mSQL . "'" .$IP ."')";
				
//It executes the SQL
ezpub_query($conn,$mSQL);
$oID=ezpub_inserted_id($conn);


	$mSQL = "INSERT INTO magazin_articole(";
	$mSQL = $mSQL . "articol_produs,";
	$mSQL = $mSQL . "articol_cantitate,";
	$mSQL = $mSQL . "articol_idcomanda)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" . $_GET['pID'] . "', ";
	$mSQL = $mSQL . "'" . 1 . "', ";
	$mSQL = $mSQL . "'" .$oID ."')";
ezpub_query($conn,$mSQL);				

echo "<div class=\"callout success radius\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"order.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";

include '../bottom.php';
die;
}
else
{
$row=ezpub_fetch_array($result);
$oID=$row['comanda_ID'];
$whereclause=" WHERE articol_idcomanda=$oID and articol_produs=$_GET[pID]";
$pquery="SELECT articol_cantitate FROM magazin_articole " . $whereclause;
$presult=ezpub_query($conn,$pquery);
$numar=ezpub_num_rows($presult, $pquery);
if ($numar==0) // although it should have at least a product, maybe the user delete it.
{ $mSQL = "INSERT INTO magazin_articole(";
	$mSQL = $mSQL . "articol_produs,";
	$mSQL = $mSQL . "articol_cantitate,";
	$mSQL = $mSQL . "articol_idcomanda)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" . $_GET['pID'] . "', ";
	$mSQL = $mSQL . "'" . 1 . "', ";
	$mSQL = $mSQL . "'" .$oID ."')";
	ezpub_query($conn,$mSQL);
	echo "<div class=\"callout success radius\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"order.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";

	}
Else // just update numbers
{$prow=ezpub_fetch_array($presult);
$newquantity=$prow['articol_cantitate']+1;
$updateq="UPDATE magazin_articole SET articol_cantitate=$newquantity " . $whereclause;
ezpub_query($conn,$updateq);
echo "<div class=\"callout success radius\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"order.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
}
}
}

Else {
$orderr=ezpub_fetch_array($result);
$oID=$orderr['comanda_ID'];
$itemq="SELECT * FROM magazin_articole where articol_idcomanda=$oID";
$resulti=ezpub_query($conn,$itemq);
$ordertotal=0;
$nume=ezpub_num_rows($resulti,$itemq);
if ($nume==0)
{
echo $strNoRecordsFound;
}
Else {
echo "<table width=\"100%\">
  <thead>
    <th width=\"50%\">$strProduct</th><th width=\"10%\" align=\"right\">$strProductPrice</th><th width=\"20%\">$strQuantity</th><th width=\"10%\">$strTotalPrice</th><th width=\"10%\">$strVAT</th></thead>";

While ($rowi=ezpub_fetch_array($resulti)) {
$queryp="SELECT * FROM magazin_produse WHERE produs_id='$rowi[articol_produs]'";
$resultp=ezpub_query($conn,$queryp);
$row=ezpub_fetch_array($resultp);
If ($row["produs_dpret"]!=='0.0000')
{
$unitprice=$row['produs_dpret'];
}
Else
{
	$unitprice=$row['produs_pret'];
}
$vatrat=$row["produs_tva"]/100;
$vatprc=$vatrat+1;
$quantity=$rowi['articol_cantitate'];
$totalprice=$unitprice*$quantity;
$ordertotal=$ordertotal+$totalprice;
$VAT=$totalprice*$vatrat;
echo "<tr><td>$row[produs_nume]</td><td align=\"right\">".romanize($unitprice)."</td><td align=\"right\">$quantity &nbsp;
<a href=\"item.php?id=$rowi[articol_id]&action=add\"><i class=\"fas fa-plus\"></i></a>
<a href=\"item.php?id=$rowi[articol_id]&action=decrease\"><i class=\"fas fa-minus\"></i></a>
<a href=\"item.php?id=$rowi[articol_id]&action=delete\"><i class=\"far fa-trash-alt\"></i></a>
</td><td align=\"right\">".romanize($totalprice)."</td>
</td><td align=\"right\">".romanize($VAT)."</td></tr>";
}
$totalinterim=$ordertotal*$vatprc;
$totalVAT=$ordertotal*$vatrat;
$totalorder=$ordertotal;
if ($paidtransport=="1" )
{
If ($totalinterim<=$transportlimit){
	$transportVAT=$transportprice*$transportvatrat;
echo "<tr><td colspan=\"3\">$strTransport</td><td align=\"right\">".romanize($transportprice)."</td><td align=\"right\">".romanize($transportVAT)."</td></tr>";	
$totalorder=$ordertotal+$transportprice;
$orderVAT=$ordertotal*$vatrat;
$totalVAT=$orderVAT+$transportVAT;
}}
$finalprice=$totalorder+$totalVAT;

echo "<tr><td colspan=\"3\">$strTotals</td><td align=\"right\">".romanize($totalorder)."</td><td align=\"right\">".romanize($totalVAT)."</td></tr>";
echo "<tr><td colspan=\"4\">$strTotal</td><td align=\"right\">".romanize($finalprice)."</td></tr></table>";
echo "	<div class=\"grid-x grid-padding-x\">  
	<div class=\"large-12 cell\"><p class=\"text-right\"><a href=\"selectcase.php?oID=$oID\" class=\"button\"><i class=\"fas fa-shopping-cart\"></i>&nbsp;$strSendOrder</a></p></div></div>";
}}
//echo "<div class=\"grid-x grid-padding-x\">  
	//<div class=\"large-12 cell callout\">$strTransportAdndPayment</div></div>";
	
echo "<div class=\"grid-x grid-padding-x\">  
	<div class=\"large-12 cell right\"><a href=\"$strSiteURL" . "shop/index.php\" class=\"button\">Înapoi la produse</a>
</div></div>";
include '../bottom.php';
?>