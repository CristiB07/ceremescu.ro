<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Actualizare solduri";
include '../dashboard/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
$query= "UPDATE cash_banca SET cash_banca_ING='" .(int)$_POST["cash_banca_ING"] . "' ,  cash_banca_transilvania='" .(int)$_POST["cash_banca_transilvania"] . "', cash_banca_unicredit='" .(int)$_POST["cash_banca_unicredit"] . "';" ;
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
    window.history.go(-1);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}
}
?>