<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Generare facturi din proforme";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes")
{
	header("location:$strSiteURL/login/index.php?message=MLF");
	die;
}

// Validare parametru cID
if ((isset($_GET['cID'])) && !empty($_GET['cID'])){
	if (!is_numeric($_GET['cID'])) {
		header("location:$strSiteURL/billing/siteinvoices.php?message=ER");
		die;
	}
	$cID = (int)$_GET['cID'];
	$query="Select factura_numar FROM facturare_facturi WHERE factura_client_inchisa='1' AND factura_tip='0' ORDER BY CAST(factura_numar AS unsigned) DESC";
	$result=ezpub_query($conn,$query);
	$row=ezpub_fetch_array($result);
	If (!isSet($row["factura_numar"]))
	{$numarfactura=1;}
	else
	{$numarfactura=(int)$row["factura_numar"]+1;}
	$dataemiterii= date('Y-m-d');

	$stmt = mysqli_prepare($conn, "UPDATE facturare_facturi SET factura_tip='0', factura_data_emiterii=?, factura_client_termen=?, factura_numar=?, factura_client_pdf=NULL, factura_client_pdf_generat=NULL WHERE factura_ID=?");
	mysqli_stmt_bind_param($stmt, "ssii", $dataemiterii, $dataemiterii, $numarfactura, $cID);

	if (!mysqli_stmt_execute($stmt))
	{
		die('Error: ' . mysqli_stmt_error($stmt));
	}
	mysqli_stmt_close($stmt);

	echo "<div class=\"callout success\"><p>$strRecordModified</p>";
	$cID_safe = htmlspecialchars($cID, ENT_QUOTES, 'UTF-8');
	echo"			    <div class=\"grid-x grid-margin-x\">
			  <div class=\"large-12 medium-12 small-12 cell\">
			  <p>
			  <a href=\"invoice.php?cID=$cID_safe\" class=\"button\"><i class=\"fas fa-file-pdf\"></i>&nbsp;$strPrint</a>
			  <a href=\"emailinvoice.php?cID=$cID_safe\" class=\"button\"><i class=\"far fa-envelope\"></i>&nbsp;$strEmail</a>
			  </p>
</div>
</div>";

echo"</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteinvoices.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
else
{
	Echo "<div class=\"callout alert\">There was an error</div></div></div>";
	include '../bottom.php';
	die;
}