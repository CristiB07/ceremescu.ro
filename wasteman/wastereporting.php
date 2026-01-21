<?php
//update 30.12.2022
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Raportare deșeuri";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$year = isset($_GET["year"]) ? $_GET["year"] : null;
$client = isset($_GET["client"]) ? $_GET["client"] : null;
$strwastetoreport = isset($_GET["cod_id"]) ? $_GET["cod_id"] : null;
$wid = isset($_GET["wID"]) ? $_GET["wID"] : null;
$wastequery="SELECT * FROM deseuri_coduri WHERE cd_id='$strwastetoreport'";
$wasteresult=ezpub_query($conn,$wastequery);
$wasterow=ezpub_fetch_array($wasteresult);
$wastecode=$wasterow["cd_01"] . $wasterow["cd_02"] . $wasterow["cd_03"];
$wastedescription=$wasterow["cd_description"];
?>
   
<?php if (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM deseuri_raportari WHERE raportare_id=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"success callout\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-1);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 5000)\">";
include '../bottom.php';
die;
} // ends delete


if ($_SERVER['REQUEST_METHOD'] == 'POST')
    { // start if post

    // Helper pentru valori numerice/decimale
    if (!function_exists('sql_decimal_or_null')) {
        function sql_decimal_or_null($val) {
            return ($val === '' || $val === null) ? 'NULL' : "'" . addslashes($val) . "'";
        }
    }

if (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
//insert new data
    $mSQL = "INSERT INTO deseuri_raportari(";
    $mSQL = $mSQL . "raportare_client_id,";
    $mSQL = $mSQL . "raportare_luna_raportare,";
    $mSQL = $mSQL . "raportare_an_raportare,";
    $mSQL = $mSQL . "raportare_cod_deseu,";
    $mSQL = $mSQL . "raportare_cod_operatiune_eliminare,";
    $mSQL = $mSQL . "raportare_cod_operatiune_valorificare,";
    $mSQL = $mSQL . "raportare_cantitate_totala,";
    $mSQL = $mSQL . "raportare_cantitate_valorificata,";
    $mSQL = $mSQL . "raportare_cantitate_eliminata,";
    $mSQL = $mSQL . "raportare_stoc,";
    $mSQL = $mSQL . "raportare_um,";
    $mSQL = $mSQL . "raportare_operator,";
    $mSQL = $mSQL . "raportare_stocare,";
    $mSQL = $mSQL . "raportare_tip_stocare,";
    $mSQL = $mSQL . "raportare_tratare,";
    $mSQL = $mSQL . "raportare_tip_tratare,";
    $mSQL = $mSQL . "raportare_scop_tratare,";
    $mSQL = $mSQL . "raportare_transport,";
    $mSQL = $mSQL . "raportare_tip_transport)";

    $mSQL = $mSQL . "values(";
    $mSQL = $mSQL . "'" .$client . "', ";
    $mSQL = $mSQL . "'" .$_POST["raportare_luna_raportare"] . "', ";
    $mSQL = $mSQL . "'" .$_POST["raportare_an_raportare"] . "', ";  
    $mSQL = $mSQL . "'" .$wastecode . "', "; 
    $mSQL = $mSQL . "'" .$_POST["raportare_cod_operatiune_eliminare"] . "', ";
    $mSQL = $mSQL . "'" .$_POST["raportare_cod_operatiune_valorificare"] . "', ";
    $mSQL = $mSQL . sql_decimal_or_null($_POST["raportare_cantitate_totala"]) . ", ";
    $mSQL = $mSQL . sql_decimal_or_null($_POST["raportare_cantitate_valorificata"]) . ", ";
    $mSQL = $mSQL . sql_decimal_or_null($_POST["raportare_cantitate_eliminata"]) . ", ";
    $mSQL = $mSQL . sql_decimal_or_null($_POST["raportare_stoc"]) . ", ";
    $mSQL = $mSQL . "'" .$_POST["raportare_um"] . "', ";
    $mSQL = $mSQL . "'" .$_POST["raportare_operator"] . "', ";
    $mSQL = $mSQL . sql_decimal_or_null($_POST["raportare_stocare"]) . ", ";
    $mSQL = $mSQL . "'" .$_POST["raportare_tip_stocare"] . "', ";
    $mSQL = $mSQL . sql_decimal_or_null($_POST["raportare_tratare"]) . ", ";
    $mSQL = $mSQL . "'" .$_POST["raportare_tip_tratare"] . "', ";
    $mSQL = $mSQL . "'" .$_POST["raportare_scop_tratare"] . "', ";
    $mSQL = $mSQL . "'" .$_POST["raportare_transport"] . "', ";
    $mSQL = $mSQL . "'" .$_POST["raportare_tip_transport"] . "'); ";
			
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
else{
	
echo "<div class=\"success callout\">$strRecordAdded</div>";
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
}//ends new post
// aici se termină blocul de procesare POST (new/edit)
   
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit") {
    $strWhereClause = " WHERE deseuri_raportari.raportare_id=" . $_GET["cID"] . ";";
    $query= "UPDATE deseuri_raportari SET deseuri_raportari.raportare_luna_raportare='" .$_POST["raportare_luna_raportare"] . "' ," ;
    $query= $query . " deseuri_raportari.raportare_an_raportare='" .$_POST["raportare_an_raportare"] . "' ," ;
    $query= $query . " deseuri_raportari.raportare_cod_deseu='" .$wastecode .   "' ," ;
    $query= $query . " deseuri_raportari.raportare_cod_operatiune_eliminare='" .$_POST["raportare_cod_operatiune_eliminare"] .   "' ," ;
    $query= $query . " deseuri_raportari.raportare_cod_operatiune_valorificare='" .$_POST["raportare_cod_operatiune_valorificare"] .   "' ," ;
    $query= $query . " deseuri_raportari.raportare_cantitate_totala=" . sql_decimal_or_null($_POST["raportare_cantitate_totala"]) .   "," ;
    $query= $query . " deseuri_raportari.raportare_cantitate_valorificata=" . sql_decimal_or_null($_POST["raportare_cantitate_valorificata"]) .   "," ;
    $query= $query . " deseuri_raportari.raportare_cantitate_eliminata=" . sql_decimal_or_null($_POST["raportare_cantitate_eliminata"]) .   "," ;
    $query= $query . " deseuri_raportari.raportare_stoc=" . sql_decimal_or_null($_POST["raportare_stoc"]) .   "," ;
    $query= $query . " deseuri_raportari.raportare_um='" .$_POST["raportare_um"] .   "' ," ;
    $query= $query . " deseuri_raportari.raportare_stocare=" . sql_decimal_or_null($_POST["raportare_stocare"]) .   "," ;
    $query= $query . " deseuri_raportari.raportare_tip_stocare='" .$_POST["raportare_tip_stocare"] .   "' ," ;
    $query= $query . " deseuri_raportari.raportare_tratare=" . sql_decimal_or_null($_POST["raportare_tratare"]) .   "," ;
    $query= $query . " deseuri_raportari.raportare_tip_tratare='" .$_POST["raportare_tip_tratare"] .   "' ," ;
    $query= $query . " deseuri_raportari.raportare_scop_tratare='" .$_POST["raportare_scop_tratare"] .   "' ," ;
    $query= $query . " deseuri_raportari.raportare_tip_transport='" .$_POST["raportare_tip_transport"] .   "' ," ;
    $query= $query . " deseuri_raportari.raportare_transport='" .$_POST["raportare_transport"] . "' "; 
    $query= $query . $strWhereClause;
    if (!ezpub_query($conn,$query)) {
        echo $query;
        die('Error: ' . ezpub_error($conn));
    } else {
        echo "<div class=\"success callout\">$strRecordModified</div>" ;
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
$wastecode=$wasterow["cd_01"] . $wasterow["cd_02"] . $wasterow["cd_03"];
$wastedescription=$wasterow["cd_description"];
}// end if post

else // no posting showing form
{
	if (IsSet($_GET['mode']) AND $_GET['mode']=="fill")
{	
?>
 <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="wastereportselector.php" class="button"><i class="fa fa-backward"></i> <?php echo $strBack?></a></p>
<table width="100%">
    <thead>
        <?php
        $cquery="SELECT * FROM clienti_date WHERE ID_Client=$client";
        $cresult=ezpub_query($conn,$cquery);
        $crow=ezpub_fetch_array($cresult);
        ?>
<tr>
    <td><?php echo $strName?></td>
    <td><?php echo $crow["Client_Denumire"]?></td>
 </tr>
 <tr>
     <td><?php echo $strVAT?></td>
    <td><?php echo $crow["Client_CUI"]?></td>
 </tr>
 <tr>
     <td><?php echo $strCompanyRC?></td>
    <td><?php echo $crow["Client_RC"]?></td>
</tr>
 <tr>
    <td><?php echo $strAddress?></td>
    <td><?php echo $crow["Client_Adresa"]?></td>
 </tr>
 <tr>
     <td><?php echo $strCity?></td>
    <td><?php echo $crow["Client_Localitate"]?></td>
 </tr>
 <tr>
    <td><?php echo $strCounty?></td>
    <td><?php echo $crow["Client_Judet"]?></td>
 </tr>
 <tr>
    <td><?php echo $strCode?></td>
    <td><?php echo $crow["Client_Cod_CAEN"]?></td>
</tr>  
 <tr>
    <td><?php echo $strWasteCode?></td>
    <td><?php echo $wastecode?><br /><?php echo $wastedescription?></td>
</tr>  
</table>
</div>  
</div>
    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitewastereporting.php" class="button"><i class="fa fa-backward"></i> <?php echo $strBack?></a> | 
			  <a href="wastereporting.php?mode=show&wID=<?php echo $wid?>&client=<?php echo $client?>&year=<?php echo $year?>&cod_id=<?php echo $strwastetoreport?>" class="button"><i class="fas fa-search"></i> <?php echo $strView?></a></p>
 </div>
 </div>
         <div class="grid-x grid-margin-x">
                            <div class="large-12 medium-12 small-12 cell">
                                <!-- Form to add/modify stock -->
                                <iframe src="wastestockform.php?client=<?php echo urlencode($client); ?>&wID=<?php echo $wid?>&year=<?php echo $year?>" width="100%" height="220" frameborder="0" style="border:1px solid #ccc; background:#f9f9f9;"></iframe>
                            </div>
         </div>
<form method="post" action="wastereporting.php?mode=new&wID=<?php echo $wid?>&client=<?php echo $client?>&year=<?php echo $year?>&cod_id=<?php echo $strwastetoreport?>&year=<?php echo $year?>">
<div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<table width="100%" class="small-font-table">
    <thead>
    	<tr>
        	<th width="5%"><?php echo $strMonth?></th>
        	<th width="5%"><?php echo $strYear?></th>
			<th width="5%"><?php echo $strWasteCode?></th>
            <th width="5%"><?php echo $strUnit?></th>
            <th width="5%"><?php echo $strTotalQuantity?></th>
            <th width="5%"><?php echo $strQuantityValorified?></th>
            <th width="5%"><?php echo $strQuantityEliminated?></th>
            <th width="5%"><?php echo $strStock?></th>
			<th width="5%"><?php echo $strOperationCodeValorification?></th>
			<th width="5%"><?php echo $strOperationCodeElimination?></th>
			<th width="5%"><?php echo $strOperator?></th>
			<th width="5%"><?php echo $strStorage?></th>
			<th width="5%"><?php echo $strStorageCode?></th>
			<th width="5%"><?php echo $strTreating?></th>
			<th width="5%"><?php echo $strTreatingCode?></th>
			<th width="5%"><?php echo $strTreatingScope?></th>
            <th width="5%"><?php echo $strTransport?></th>
			<th width="5%"><?php echo $strTransportCode?></th>
			<th width="5%"><?php echo $strAdd?></th>
			<th width="5%"><?php echo $strDelete?></th>
        </tr>
		</thead>
<?php
for ( $m = 1; $m <= 12; $m ++) { 
    //loop through months
    //Create an option With the numeric value of the month
    $dateObj   = DateTime::createFromFormat('!m', $m);
    $formatter = new IntlDateFormatter("ro_RO",
        IntlDateFormatter::FULL, 
        IntlDateFormatter::FULL, 
        'Europe/Bucharest', 
        IntlDateFormatter::GREGORIAN,
        'MMMM');
    $monthname = $formatter->format($dateObj);
    $query = "SELECT * FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_luna_raportare='$m' AND raportare_an_raportare='$year'";
    $result = ezpub_query($conn, $query);
    echo ezpub_error($conn);

    // 1. Afișează toate raportările existente pentru luna $m
    while ($row = ezpub_fetch_array($result)) {
?>
    <tr> 
    <form method="post" action="wastereporting.php?mode=edit&wID=<?php echo $wid?>&cID=<?php echo $row["raportare_id"]?>&year=<?php echo $_GET["year"]?>&client=<?php echo $client?>&cod_id=<?php echo $strwastetoreport?>">
        <input name="raportare_luna_raportare" type="hidden" value="<?php echo $m?>">
        <input name="raportare_an_raportare" type="hidden" value="<?php echo $year?>">
        <input name="raportare_cod_deseu" type="hidden" value="<?php echo $wastecode?>">
        <td><?php echo $monthname?></td>
        <td><?php echo $year?></td>
        <td><?php echo $wastecode?></td>
        <td><input name="raportare_um" type="text" value="<?php echo $row["raportare_um"]?>"  /></td>
        <td><input id="cantitate_totala_<?php echo $row["raportare_id"]?>" name="raportare_cantitate_totala" type="text" value="<?php echo $row["raportare_cantitate_totala"]?>"  /></td>
        <td><input id="valorificata_<?php echo $row["raportare_id"]?>" name="raportare_cantitate_valorificata" type="text" value="<?php echo $row["raportare_cantitate_valorificata"]?>"  /></td>
        <td><input id="eliminata_<?php echo $row["raportare_id"]?>" name="raportare_cantitate_eliminata" type="text" value="<?php echo $row["raportare_cantitate_eliminata"]?>"  /></td>
        <td><input id="stoc_<?php echo $row["raportare_id"]?>" name="raportare_stoc" type="text" value="<?php echo isset($row["raportare_stoc"]) ? $row["raportare_stoc"] : '' ?>" readonly style="background:#f3f3f3;" /></td>
        <script>
        // Calculează automat raportare_stoc la modificarea oricărui câmp cantitate
        (function() {
            var id = '<?php echo $row["raportare_id"]?>';
            var total = document.getElementById('cantitate_totala_' + id);
            var valorificata = document.getElementById('valorificata_' + id);
            var eliminata = document.getElementById('eliminata_' + id);
            var stoc = document.getElementById('stoc_' + id);
            function calcStoc() {
                var t = parseFloat(total.value) || 0;
                var v = parseFloat(valorificata.value) || 0;
                var e = parseFloat(eliminata.value) || 0;
                stoc.value = (t - v - e).toFixed(2);
            }
            if (total && valorificata && eliminata && stoc) {
                total.addEventListener('input', calcStoc);
                valorificata.addEventListener('input', calcStoc);
                eliminata.addEventListener('input', calcStoc);
            }
        })();
        </script>
        <td><input name="raportare_cod_operatiune_valorificare" type="text" value="<?php echo $row["raportare_cod_operatiune_valorificare"]?>"  /></td>
        <td><input name="raportare_cod_operatiune_eliminare" type="text" value="<?php echo $row["raportare_cod_operatiune_eliminare"]?>"  /></td>
        <td><input name="raportare_operator" type="text" value="<?php echo $row["raportare_operator"]?>"  /></td>
        <td><input id="stocare_<?php echo $row["raportare_id"]?>" name="raportare_stocare" type="text" value="<?php echo $row["raportare_stocare"]?>"  /></td>
        <script>
        // Sincronizează raportare_cantitate_totala cu raportare_stocare
        (function() {
            var form = document.currentScript.closest('form');
            if (!form) return;
            var total = form.querySelector('[name="raportare_cantitate_totala"]');
            var stocare = form.querySelector('[name="raportare_stocare"]');
            if (total && stocare) {
                total.addEventListener('input', function() {
                    stocare.value = total.value;
                });
                stocare.addEventListener('input', function() {
                    total.value = stocare.value;
                });
            }
        })();
        </script>
        <td><input name="raportare_tip_stocare" type="text" value="<?php echo $row["raportare_tip_stocare"]?>"  /></td>
        <td><input name="raportare_tratare" type="text" value="<?php echo $row["raportare_tratare"]?>"  /></td>
        <td><input name="raportare_tip_tratare" type="text" value="<?php echo $row["raportare_tip_tratare"]?>"  /></td>
        <td><input name="raportare_scop_tratare" type="text" value="<?php echo $row["raportare_scop_tratare"]?>"  /></td>
        <td><input name="raportare_transport" type="text" value="<?php echo $row["raportare_transport"]?>"  /></td>
        <td><input name="raportare_tip_transport" type="text" value="<?php echo $row["raportare_tip_transport"]?>"  /></td>
        <td><input type="submit" value="<?php echo $strModify?>" class="button" name="Submit"></td>
        <td><a href="wastereporting.php?mode=delete&cID=<?php echo $row["raportare_id"]?>&year=<?php echo $year?>" class="ask button" OnClick="return confirm('<?php echo $strConfirmDelete?>');">
        <i class="large fa fa-eraser" title="<?php echo $strDelete?>"></i></a></td>
        </form>
        <script>
        // Sincronizare cantitate totală cu stocare pentru formularul de editare
        (function() {
            var total = document.getElementById('cantitate_totala_<?php echo $row["raportare_id"]?>');
            var stocare = document.getElementById('stocare_<?php echo $row["raportare_id"]?>');
            if (total && stocare) {
                total.addEventListener('input', function() {
                    stocare.value = total.value;
                });
                stocare.addEventListener('input', function() {
                    total.value = stocare.value;
                });
            }
        })();
        </script>
    </tr>
<?php
    }

    // 2. Formular gol pentru adăugare raportare nouă (mereu prezent)
?>
    <tr>
    <form method="post" action="wastereporting.php?mode=new&wID=<?php echo $wid?>&year=<?php echo $_GET["year"]?>&client=<?php echo $client?>&cod_id=<?php echo $strwastetoreport?>" >    
        <input name="raportare_luna_raportare" type="hidden" value="<?php echo $m?>">
        <input name="raportare_an_raportare" type="hidden" value="<?php echo $year?>">
        <input name="raportare_cod_deseu" type="hidden" value="<?php echo $wastecode?>">
        <td><?php echo $monthname?></td>
        <td><?php echo $year?></td>
        <td><?php echo $wastecode?></td>
        <td><input name="raportare_um" type="text" value=""  /></td>
        <td><input id="cantitate_totala_new_<?php echo $m?>" name="raportare_cantitate_totala" type="text" value=""  /></td>
        <td><input name="raportare_cantitate_valorificata" type="text" value=""  /></td>
        <td><input name="raportare_cantitate_eliminata" type="text" value=""  /></td>
        <td><input id="stoc_new_<?php echo $m?>" name="raportare_stoc" type="text" value="" readonly style="background:#f3f3f3;" /></td>
        <script>
        // Calculează automat raportare_stoc la modificarea oricărui câmp cantitate (formular nou)
        (function() {
            var total = document.getElementById('cantitate_totala_new_<?php echo $m?>');
            var valorificata = total && total.form.querySelector('[name="raportare_cantitate_valorificata"]');
            var eliminata = total && total.form.querySelector('[name="raportare_cantitate_eliminata"]');
            var stoc = document.getElementById('stoc_new_<?php echo $m?>');
            function calcStoc() {
                var t = parseFloat(total.value) || 0;
                var v = parseFloat(valorificata.value) || 0;
                var e = parseFloat(eliminata.value) || 0;
                stoc.value = (t - v - e).toFixed(2);
            }
            if (total && valorificata && eliminata && stoc) {
                total.addEventListener('input', calcStoc);
                valorificata.addEventListener('input', calcStoc);
                eliminata.addEventListener('input', calcStoc);
            }
        })();
        </script>
        <td><input name="raportare_cod_operatiune_valorificare" type="text" value=""/></td>
        <td><input name="raportare_cod_operatiune_eliminare" type="text" value=""/></td>
        <td><input name="raportare_operator" type="text" value=""/></td>
        <td><input id="stocare_new_<?php echo $m?>" name="raportare_stocare" type="text" value=""/></td>
        <script>
        // Sincronizează raportare_cantitate_totala cu raportare_stocare
        (function() {
            var form = document.currentScript.closest('form');
            if (!form) return;
            var total = form.querySelector('[name="raportare_cantitate_totala"]');
            var stocare = form.querySelector('[name="raportare_stocare"]');
            if (total && stocare) {
                total.addEventListener('input', function() {
                    stocare.value = total.value;
                });
                stocare.addEventListener('input', function() {
                    total.value = stocare.value;
                });
            }
        })();
        </script>
        <td><input name="raportare_tip_stocare" type="text" value=""/></td>
        <td><input name="raportare_tratare" type="text" value=""/></td>
        <td><input name="raportare_tip_tratare" type="text" value=""/></td>
        <td><input name="raportare_scop_tratare" type="text" value=""/></td>
        <td><input name="raportare_transport" type="text" value=""/></td>
        <td><input name="raportare_tip_transport" type="text" value=""/></td>
        <td><input type="submit" value="<?php echo $strAdd?>" class="button" name="Submit"></td>
        <td><p class="button"><i class="large fa fa-eraser" title="<?php echo $strDelete?>"></i></p></td>
        </form>
        <script>
        // Sincronizare cantitate totală cu stocare pentru formularul de adăugare
        (function() {
            var total = document.getElementById('cantitate_totala_new_<?php echo $m?>');
            var stocare = document.getElementById('stocare_new_<?php echo $m?>');
            if (total && stocare) {
                total.addEventListener('input', function() {
                    stocare.value = total.value;
                });
                stocare.addEventListener('input', function() {
                    total.value = stocare.value;
                });
            }
        })();
        </script>
    </tr>
<?php
}
?></tbody><tfoot><tr><td></td><td  colspan="17"><em></em></td><td>&nbsp;</td></tr></tfoot></table>  
 </div>
 </div>
 

 <?php
 
 } // ends fill mode
  elseif (IsSet($_GET['mode']) AND $_GET['mode']=="show")
 {
	//show reporting data
    $query="SELECT * FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_an_raportare='$year'";
		$result=ezpub_query($conn,$query);
		$row=ezpub_fetch_array($result);
    ?>
    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitewastereporting.php" class="button"><i class="fa fa-backward"></i> <?php echo $strBack?></a> | 
			  <a href="wastereporting.php?mode=fill&wID=<?php echo $wid?>&client=<?php echo $client?>&year=<?php echo $year?>&cod_id=<?php echo $strwastetoreport?>" class="button"><i class="fa fa-file-alt"></i> <?php echo $strBackToForm?></a></p>
 </div>
 </div>
    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<table width="100%">
    <thead>
        <?php
        $cquery="SELECT * FROM clienti_date WHERE ID_Client=$client";
        $cresult=ezpub_query($conn,$cquery);
        $crow=ezpub_fetch_array($cresult);
        ?>
<tr>
    <td><?php echo $strName?></td>
    <td><?php echo $crow["Client_Denumire"]?></td>
 </tr>
 <tr>
     <td><?php echo $strVAT?></td>
    <td><?php echo $crow["Client_CUI"]?></td>
 </tr>
 <tr>
     <td><?php echo $strCompanyRC?></td>
    <td><?php echo $crow["Client_RC"]?></td>
</tr>
 <tr>
    <td><?php echo $strAddress?></td>
    <td><?php echo $crow["Client_Adresa"]?></td>
 </tr>
 <tr>
     <td><?php echo $strCity?></td>
    <td><?php echo $crow["Client_Localitate"]?></td>
 </tr>
 <tr>
    <td><?php echo $strCounty?></td>
    <td><?php echo $crow["Client_Judet"]?></td>
 </tr>
 <tr>
    <td><?php echo $strCode?></td>
    <td><?php echo $crow["Client_Cod_CAEN"]?></td>
</tr>  
 <tr>
    <td><?php echo $strWasteCode?></td>
    <td><?php echo $wastecode?><br /><?php echo $wastedescription?></td>
</tr>  
 <tr>
    <td><?php echo $strUnit?></td>
    <td><?php echo $row["raportare_um"]?></td>
</tr>  
 </table>
  
            </div>
</div>
<ul class="tabs" data-tabs id="waste-management-tabs">
  <li class="tabs-title is-active"><a href="#panel1" aria-selected="true">Generare</a></li>
  <li class="tabs-title"><a data-tabs-target="panel2" href="#panel2">Stocare</a></li>
  <li class="tabs-title"><a data-tabs-target="panel3" href="#panel3">Valorificare</a></li>
  <li class="tabs-title"><a data-tabs-target="panel4" href="#panel4">Eliminare</a></li>
</ul>
<div class="tabs-content" data-tabs-content="waste-management-tabs">
    <div class="tabs-panel is-active" id="panel1">
        <table width="100%">
    <thead>
    	<tr>
        	<th><?php echo $strMonth?></th>
        	<th><?php echo $strQuantity?></th>
        	<th><?php echo $strValorization?></th>
        	<th><?php echo $strElimination?></th>
        	<th><?php echo $strStorage?></th>
            <th><?php echo $strStock?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Calcul stoc precedent pentru ianuarie
        $stocuri_lunare = [];
        $stoc_total_anual = 0;
        $stoc_precedent = 0;
        $query_stoc_prev = "SELECT stoc_cantitate FROM deseuri_stocuri WHERE stoc_client_id='$client' AND stoc_cod_deseu='$wastecode' AND stoc_an_raportare='".($year-1)."'";
        $result_stoc_prev = ezpub_query($conn, $query_stoc_prev);
        if ($row_stoc_prev = ezpub_fetch_array($result_stoc_prev)) {
            $stoc_precedent = floatval($row_stoc_prev['stoc']);
        }
        $stoc_curent = $stoc_precedent;
        for ($m = 1; $m <= 12; $m++) {
            $dateObj = DateTime::createFromFormat('!m', $m);
            $formatter = new IntlDateFormatter("ro_RO",
                IntlDateFormatter::FULL,
                IntlDateFormatter::FULL,
                'Europe/Bucharest',
                IntlDateFormatter::GREGORIAN,
                'MMMM');
            $monthname = $formatter->format($dateObj);
            $query2 = "SELECT SUM(raportare_cantitate_totala) AS suma_totala, SUM(raportare_cantitate_valorificata) AS suma_valorificata, SUM(raportare_cantitate_eliminata) AS suma_eliminata, SUM(raportare_stocare) AS suma_stocare FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_luna_raportare='$m' AND raportare_an_raportare='$year' AND raportare_cod_deseu='$wastecode'";
            $result2 = ezpub_query($conn, $query2);
            $row2 = ezpub_fetch_array($result2);
            $gen = floatval($row2["suma_totala"]);
            $val = floatval($row2["suma_valorificata"]);
            $elim = floatval($row2["suma_eliminata"]);
            // Stocul lunar = stoc precedent (sau cumul) + generat - valorificat - eliminat
            if ($m == 1) {
                $stoc_luna = $stoc_precedent + $gen - $val - $elim;
            } else {
                $stoc_luna = $stoc_curent + $gen - $val - $elim;
            }
            $stocuri_lunare[$m] = $stoc_luna;
            $stoc_curent = $stoc_luna;
            $stoc_total_anual = $stoc_curent;
            echo "<tr>";
            echo "<td>$monthname</td>";
            echo "<td>" . ($row2["suma_totala"] === null ? '-' : $row2["suma_totala"]) . "</td>";
            echo "<td>" . ($row2["suma_valorificata"] === null ? '-' : $row2["suma_valorificata"]) . "</td>";
            echo "<td>" . ($row2["suma_eliminata"] === null ? '-' : $row2["suma_eliminata"]) . "</td>";
            echo "<td>" . ($row2["suma_stocare"] === null ? '-' : $row2["suma_stocare"]) . "</td>";
            echo "<td>" . number_format($stoc_luna, 2) . "</td>";
            echo "</tr>";
        }
        //show totals
        $totalquery="SELECT SUM(raportare_cantitate_totala) AS total_generated, 
        SUM(raportare_cantitate_valorificata) AS total_valorificated, 
        SUM(raportare_cantitate_eliminata) AS total_eliminated,
        SUM(raportare_stocare) AS total_stored 
        FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_an_raportare='$year' AND raportare_cod_deseu='$wastecode'";
        $totalresult=ezpub_query($conn,$totalquery);
        $totalrow=ezpub_fetch_array($totalresult);
        echo "<tr><td><strong>$strTotal</strong></td><td><strong>" . ($totalrow["total_generated"] === null ? '-' : $totalrow["total_generated"]) . "</strong></td><td><strong>" . ($totalrow["total_valorificated"] === null ? '-' : $totalrow["total_valorificated"]) . "</strong></td><td><strong>" . ($totalrow["total_eliminated"] === null ? '-' : $totalrow["total_eliminated"]) . "</strong></td><td>" . ($totalrow["total_stored"] === null ? '-' : $totalrow["total_stored"]) . "</td><td><strong>".number_format($stoc_total_anual,2)."</strong></td></tr>";
        echo  "</tbody><tfoot><tr><td></td><td colspan='4'><em></em></td><td>&nbsp;</td></tr></tfoot></table> ";  
        ?>
        </table>
    </div>
    <div class="tabs-panel" id="panel2">
        <table width="100%">
    <thead>
        <tr>
            <th><?php echo $strMonth?></th>
            <th><?php echo $strQuantity?></th>
            <th><?php echo $strCode?></th>
            <th><?php echo $strTreated?></th>
            <th><?php echo $strTreatedCode?></th>
            <th><?php echo $strTreatedScope?></th>
            <th><?php echo $strTransport?></th>
            <th><?php echo $strTransportCode?></th>
        </tr>
    </thead>
    <tbody>
    <?php
    $stoc_cumulat_anterior = 0;
    for ($m = 1; $m <= 12; $m++) {
        $dateObj = DateTime::createFromFormat('!m', $m);
        $formatter = new IntlDateFormatter("ro_RO",
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            'Europe/Bucharest',
            IntlDateFormatter::GREGORIAN,
            'MMMM');
        $monthname = $formatter->format($dateObj);
        $query3 = "SELECT raportare_stocare, raportare_tip_stocare, raportare_tratare, raportare_tip_tratare, raportare_scop_tratare, raportare_transport, raportare_tip_transport FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_luna_raportare='$m' AND raportare_an_raportare='$year' AND raportare_cod_deseu='$wastecode'";
        $result3 = ezpub_query($conn, $query3);
        $total_stocare = 0;
        $total_tratare = 0;
        $tip_stocare_set = [];
        $tip_tratare_set = [];
        $scop_tratare_set = [];
        $transport_set = [];
        $tip_transport_set = [];
        $has_data = false;
        while ($row3 = ezpub_fetch_array($result3)) {
            if (is_numeric($row3["raportare_stocare"])) $total_stocare += $row3["raportare_stocare"];
            if (is_numeric($row3["raportare_tratare"])) $total_tratare += $row3["raportare_tratare"];
            if ($row3["raportare_tip_stocare"] !== null && $row3["raportare_tip_stocare"] !== '') $tip_stocare_set[] = $row3["raportare_tip_stocare"];
            if ($row3["raportare_tip_tratare"] !== null && $row3["raportare_tip_tratare"] !== '') $tip_tratare_set[] = $row3["raportare_tip_tratare"];
            if ($row3["raportare_scop_tratare"] !== null && $row3["raportare_scop_tratare"] !== '') $scop_tratare_set[] = $row3["raportare_scop_tratare"];
            if ($row3["raportare_transport"] !== null && $row3["raportare_transport"] !== '') $transport_set[] = $row3["raportare_transport"];
            if ($row3["raportare_tip_transport"] !== null && $row3["raportare_tip_transport"] !== '') $tip_transport_set[] = $row3["raportare_tip_transport"];
            $has_data = true;
        }
        // elimină duplicatele
        $tip_stocare_set = array_unique($tip_stocare_set);
        $tip_tratare_set = array_unique($tip_tratare_set);
        $scop_tratare_set = array_unique($scop_tratare_set);
        $transport_set = array_unique($transport_set);
        $tip_transport_set = array_unique($tip_transport_set);
        // Stocul lunar calculat anterior
        $stoc_luna = isset($stocuri_lunare[$m]) ? $stocuri_lunare[$m] : 0;
        // Pentru luna curentă, nu adăuga stocul la total_stocare, doar pentru lunile următoare
        $total_stocare_afisat = $total_stocare;
        if ($m > 1) {
            $total_stocare_afisat += $stoc_cumulat_anterior;
        }
        $stoc_cumulat_anterior = $stoc_luna;
        if (!$has_data && $total_stocare_afisat == 0) {
            echo "<tr><td>$monthname</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td></tr>";
        } else {
            echo "<tr><td>$monthname</td>";
            echo "<td>".($total_stocare_afisat === 0 ? '-' : $total_stocare_afisat)."</td>";
            echo "<td>".(count($tip_stocare_set) ? htmlspecialchars(implode(', ', $tip_stocare_set)) : '-')."</td>";
            echo "<td>".($total_tratare === 0 ? '-' : $total_tratare)."</td>";
            echo "<td>".(count($tip_tratare_set) ? htmlspecialchars(implode(', ', $tip_tratare_set)) : '-')."</td>";
            echo "<td>".(count($scop_tratare_set) ? htmlspecialchars(implode(', ', $scop_tratare_set)) : '-')."</td>";
            echo "<td>".(count($transport_set) ? htmlspecialchars(implode(', ', $transport_set)) : '-')."</td>";
            echo "<td>".(count($tip_transport_set) ? htmlspecialchars(implode(', ', $tip_transport_set)) : '-')."</td>";
            echo "</tr>";
        }
    }
    //show totals pe an
    $totalquery2 = "SELECT SUM(raportare_stocare) AS total_stored, SUM(raportare_tratare) AS total_tratare FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_an_raportare='$year'";
    $totalresult2 = ezpub_query($conn, $totalquery2);
    $totalrow2 = ezpub_fetch_array($totalresult2);
    echo "<tr><td><strong>$strTotal</strong></td><td><strong>" . ($totalrow2["total_stored"] === null ? '-' : $totalrow2["total_stored"]) . "</strong></td><td></td><td><strong>" . ($totalrow2["total_tratare"] === null ? '-' : $totalrow2["total_tratare"]) . "</strong></td><td colspan='4'></td></tr>";
    ?>
        </tbody>
        <tfoot><tr><td></td><td colspan='7'><em></em></td><td>&nbsp;</td></tr></tfoot></table>
        </table>
        <div class="callout">
                <h1>Notă</h1>
                 <ul>
    <li>*1) Tipul de stocare:</li>
    <ul>
    <li>RM - recipient metalic</li>
    <li>RP - recipient de plastic</li>
    <li>BZ - bazin decantor</li>
    <li>CT - container transportabil</li>
    <li>CF - container fix</li>
    <li>S - saci</li>
    <li>PD - platforma de deshidratare</li>
    <li>VN - în vrac, neacoperit</li>
    <li>VA - în vrac, incinta acoperită</li>
    <li>RL - recipient din lemn</li>
    <li>A - altele</li>
    </ul>
    <li>*2) Modul de tratare:</li>
    <ul>
    <li>TM - tratare mecanică</li>
    <li>TC - tratare chimica</li>
    <li>TMC - tratare mecano-chimica</li>
    <li>TB - tratare biochimica</li>
    <li>D - deshidratare</li>
    <li>TT - tratare termica</li>
    <li>A - altele</li>
    </ul>
    <li>*3) Scopul tratarii:</li>
    <ul>
    <li>V - pentru valorificare</li>
    <li>E - în vederea eliminării</li>
    </ul>
    <li>*4) Mijlocul de transport:</li>
    <ul>
    <li>AS - autospeciale</li>
    <li>AN - auto nespecial</li>
    <li>H - transport hidraulic</li>
    <li>CF - cale ferată</li>
    <li>A - altele</li>
    </ul>
    <li>*5) Destinaţia:</li>
    <ul>
    <li>DO - depozitul de gunoi al oraşului/comunei</li>
    <li>HP - halda proprie</li>
    <li>HC - halda industriala comuna</li>
    <li>I - incinerarea în scopul eliminării</li>
    <li>Vr - valorificare prin agenţi economici autorizaţi</li>
    <li>P - utilizare materială sau energetica în propria întreprindere</li>
    <li>Ve - valorificare energetica prin agenţi economici autorizaţi</li>
    <li>A - altele</li>
                 </ul>
    </div>
    </div>
    <div class="tabs-panel" id="panel3">
        <table width="100%">
<thead>
    <tr>
        <th><?php echo $strMonth?></th>
        <th><?php echo $strQuantity?></th>
        <th><?php echo $strOperator?></th>
        <th><?php echo $strOperationCodeValorification?></th>
    </tr>
</thead>
<tbody>
<?php
for ($m = 1; $m <= 12; $m++) {
    $dateObj = DateTime::createFromFormat('!m', $m);
    $formatter = new IntlDateFormatter("ro_RO",
        IntlDateFormatter::FULL,
        IntlDateFormatter::FULL,
        'Europe/Bucharest',
        IntlDateFormatter::GREGORIAN,
        'MMMM');
    $monthname = $formatter->format($dateObj);
    $query4 = "SELECT raportare_operator, raportare_cantitate_valorificata, raportare_cod_operatiune_valorificare FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_luna_raportare='$m' AND raportare_an_raportare='$year'";
    $result4 = ezpub_query($conn, $query4);
    $operators = [];
    $codes = [];
    $total_valorificata = 0;
    while ($row4 = ezpub_fetch_array($result4)) {
        // Afișează doar dacă există cantitate valorificată (nu și eliminată)
        if ($row4["raportare_cantitate_valorificata"] !== null && $row4["raportare_cantitate_valorificata"] !== '' && floatval($row4["raportare_cantitate_valorificata"]) > 0) {
            $q = htmlspecialchars($row4["raportare_cantitate_valorificata"]);
            $c = ($row4["raportare_cod_operatiune_valorificare"] === null || $row4["raportare_cod_operatiune_valorificare"] === '' ? '-' : htmlspecialchars($row4["raportare_cod_operatiune_valorificare"]));
            $operators[] = htmlspecialchars($row4["raportare_operator"]) . ' - ' . $q;
            $codes[] = $c;
            $total_valorificata += floatval($row4["raportare_cantitate_valorificata"]);
        }
    }
    echo "<tr><td>$monthname</td><td>".($total_valorificata === 0 ? '-' : $total_valorificata)."</td><td>".(count($operators) ? implode('<br/>', $operators) : '-')."</td><td>".(count($codes) ? implode('<br/>', $codes) : '-')."</td></tr>";
}
//show grand total
$totalquery3 = "SELECT SUM(raportare_cantitate_valorificata) AS total_valorificated FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_an_raportare='$year'";
$totalresult3 = ezpub_query($conn, $totalquery3);
$totalrow3 = ezpub_fetch_array($totalresult3);
echo "<tr><td><strong>$strTotal</strong></td><td><strong>" . ($totalrow3["total_valorificated"] === null ? '-' : $totalrow3["total_valorificated"]) . "</strong></td><td></td><td></td></tr>";
?>
                </tbody>
                <tfoot><tr><td></td><td><em></em></td><td>&nbsp;</td><td></td></tr></tfoot></table>
        </table>
        <div class="callout">
                <h1>Operațiuni de valorificare conform Anexa 3/OUG 92/2021 privind gestionarea deșeurilor</h1>
                <ul>
                    <li>R1 Întrebuinţarea în principal drept combustibil sau ca altă sursă de energie<sup>1</sup></li>
                    <li>R2 Valorificarea/Regenerarea solvenţilor</li>
                    <li>R3 Reciclarea/Recuperarea substanţelor organice utilizate ca solvenţi (inclusiv compostarea şi alte procese de transformare biologică)<sup>2</sup></li>
                    <li>R4 Reciclarea/Recuperarea metalelor şi compuşilor metalici <sup>3</sup></li>
                    <li>R5 Reciclarea/Recuperarea altor materiale anorganice<sup>4</sup></li>
                    <li>R6 Regenerarea acizilor sau a bazelor</li>
                    <li>R7 Valorificarea componenţilor utilizaţi pentru reducerea poluării</i></li>
                    <li>R8 Valorificarea componentelor catalizatorilor</li>
                    <li>R9 Rerafinarea uleiului uzat sau alte reutilizări ale uleiului uzat</li>
                    <li>R10 Tratarea terenurilor având drept rezultat beneficii pentru agricultură sau ecologie</li>
                    <li>R11 Utilizarea deşeurilor obţinute din oricare dintre operaţiunile numerotate de la R 1 la R 10</li>
                    <li>R12 Schimbul de deşeuri în vederea expunerii la oricare dintre operaţiunile numerotate de la R 1 la R 11<sup>5</sup></li>
                    <li>R13 Stocarea deşeurilor înaintea oricărei operaţiuni numerotate de la R 1 la R 12 (excluzând stocarea temporară, înaintea colectării, la situl unde a fost generat deşeul)<sup>6</sup></li>
</ul>
<h2>Note</h2>
<p>
    <sup>1</sup>
Aceasta include instalaţii de incinerare destinate în principal tratării deşeurilor municipale solide, numai în cazul în care randamentul lor energetic este egal sau mai mare decât:-
<ul>
    <li>0,60 pentru instalaţiile care funcţionează şi sunt autorizate în conformitate cu legislaţia comunitară aplicabilă înainte de 1 ianuarie 2009;</li>
    <li>0,65 pentru instalaţiile autorizate după 31 decembrie 2008,</li>
</ul>
folosindu-se următoarea formulă:</br>
Eficienţa energetică = (Ep - (Ef + Ei))/(0,97 × (Ew + Ef)),</br>
unde:</br>
<ul>
    <li>Ep reprezintă producţia anuală de energie sub formă de căldură sau electricitate. Aceasta este calculată înmulţind energia produsă sub formă de electricitate cu 2,6 şi energia produsă sub formă de căldură pentru utilizare comercială (GJ/an) cu 1,1;</li>
    <li>Ef reprezintă consumul anual de energie al sistemului, provenită din combustibili, care contribuie la producţia de aburi (GJ/an);</li>
    <li>Ew reprezintă energia anuală conţinută de deşeurile tratate, calculată pe baza valorii calorice nete inferioare a deşeurilor (GJ/an);</li>
    <li>Ei reprezintă energia anuală importată, exclusiv Ew şi Ef (GJ/an);</li>
    <li>0,97 este un coeficient care reprezintă pierderile de energie datorate reziduurilor generate în urma incinerării şi radierii.</li>
</ul>
Această formulă se aplică în conformitate cu documentul de referinţă privind cele mai bune tehnici existente pentru incinerarea deşeurilor.
</p>    
<p>
   <sup>2</sup>
Aceasta include pregătirea pentru reutilizare, gazeificarea şi piroliza care folosesc componentele ca produse chimice şi valorificarea materialelor organice sub formă de rambleiaj.
</p>
<p><sup>3</sup>
Aceasta include pregătirea pentru reutilizare.
</p>
<p><sup>4</sup>
Aceasta include pregătirea pentru reutilizare, reciclarea materialelor de construcţie anorganice, valorificarea materialelor anorganice sub formă de rambleiaj şi curăţarea solului care are ca rezultat valorificarea solului.
</p>
<p><sup>5</sup>
În cazul în care nu există niciun alt cod R corespunzător, aceasta include operaţiunile preliminare înainte de valorificare, inclusiv preprocesarea, cum ar fi, printre altele, demontarea, sortarea, sfărâmarea, compactarea, granularea, mărunţirea uscată, condiţionarea, reambalarea, separarea şi amestecarea înainte de supunerea la oricare dintre operaţiunile numerotate de la R1 la R11.
</p>
<p><sup>6</sup>
Stocare temporară înseamnă stocare preliminară în conformitate cu anexa nr. 1 pct. 6.
</p>
</div>
</div>
    <div class="tabs-panel" id="panel4">
        <table width="100%">
<thead>
    <tr>
        <th><?php echo $strMonth?></th>
        <th><?php echo $strQuantity?></th>
        <th><?php echo $strOperator?></th>
        <th><?php echo $strOperationCodeElimination?></th>
    </tr>
</thead>
<tbody>
<?php
for ($m = 1; $m <= 12; $m++) {
    $dateObj = DateTime::createFromFormat('!m', $m);
    $formatter = new IntlDateFormatter("ro_RO",
        IntlDateFormatter::FULL,
        IntlDateFormatter::FULL,
        'Europe/Bucharest',
        IntlDateFormatter::GREGORIAN,
        'MMMM');
    $monthname = $formatter->format($dateObj);
    $query5 = "SELECT raportare_operator, raportare_cantitate_eliminata, raportare_cod_operatiune_eliminare FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_luna_raportare='$m' AND raportare_an_raportare='$year'";
    $result5 = ezpub_query($conn, $query5);
    $operators = [];
    $codes = [];
    $total_eliminata = 0;
    while ($row5 = ezpub_fetch_array($result5)) {
        // Afișează doar dacă există cantitate eliminată (nu și valorificată)
        if ($row5["raportare_cantitate_eliminata"] !== null && $row5["raportare_cantitate_eliminata"] !== '' && floatval($row5["raportare_cantitate_eliminata"]) > 0) {
            $q = htmlspecialchars($row5["raportare_cantitate_eliminata"]);
            $c = ($row5["raportare_cod_operatiune_eliminare"] === null || $row5["raportare_cod_operatiune_eliminare"] === '' ? '-' : htmlspecialchars($row5["raportare_cod_operatiune_eliminare"]));
            $operators[] = htmlspecialchars($row5["raportare_operator"]) . ' - ' . $q;
            $codes[] = $c;
            $total_eliminata += floatval($row5["raportare_cantitate_eliminata"]);
        }
    }
    echo "<tr><td>$monthname</td><td>".($total_eliminata === 0 ? '-' : $total_eliminata)."</td><td>".(count($operators) ? implode('<br/>', $operators) : '-')."</td><td>".(count($codes) ? implode('<br/>', $codes) : '-')."</td></tr>";
}
//show grand total
$totalquery4 = "SELECT SUM(raportare_cantitate_eliminata) AS total_eliminated FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_an_raportare='$year'";
$totalresult4 = ezpub_query($conn, $totalquery4);
$totalrow4 = ezpub_fetch_array($totalresult4);
echo "<tr><td><strong>$strTotal</strong></td><td><strong>" . ($totalrow4["total_eliminated"] === null ? '-' : $totalrow4["total_eliminated"]) . "</strong></td><td></td><td></td></tr>";
?>
                </tbody>
                <tfoot><tr><td></td><td><em></em></td><td>&nbsp;</td><td></td></tr></tfoot></table>
        </table>
         <div class="callout">
                <h1>Operațiuni de eliminare conform Anexa 7/OUG 92/2021 privind gestionarea deșeurilor</h1>
                <ul>
<li>D1 Depozitarea în sau pe sol (de exemplu, depozite de deşeuri etc.)
<li>D2 Tratarea solului (de exemplu, biodegradarea deşeurilor lichide sau nămoloase în sol etc.)
<li>D3 Injectarea în adâncime (de exemplu, injectarea deşeurilor care pot fi pompate în puţuri, saline sau depozite geologice naturale etc.)
<li>D4 Acumulare la suprafaţă (de exemplu, depunerea de deşeuri lichide sau nămoloase în bazine, iazuri sau lagune etc.)
<li>D5 Depozite special construite (de exemplu, depunerea în compartimente separate etanşe care sunt acoperite şi izolate unele faţă de celelalte şi faţă de mediul înconjurător etc.)
<li>D6 Evacuarea într-o masă de apă, cu excepţia mărilor/oceanelor
<li>D7 Evacuarea în mări/oceane, inclusiv eliminarea în subsolul marin
<li>D8 Tratarea biologică nemenţionată în altă parte în prezenta anexă, care generează compuşi sau mixturi finale eliminate prin intermediul unuia dintre procedeele numerotate de la D1 la D12
<li>D9 Tratarea fizico-chimică nemenţionată în altă parte în prezenta anexă, care generează compuşi sau mixturi finale eliminate prin intermediul unuia dintre procedeele numerotate de la D1 la D12 (de exemplu, evaporare, uscare, calcinare etc.)
<li>D10 Incinerarea pe sol
<li>D11 Incinerarea pe mare <ul>
<li>Această operaţiune este interzisă de legislaţia Uniunii Europene şi de convenţii internaţionale.</li></ul></li>
<li>D12 Stocarea permanentă (de exemplu, plasarea de recipiente într-o mină etc.)
<li>D13 Amestecarea anterioară oricărei operaţiuni numerotate de la D1 la D12
    <ul>
<li>În cazul în care nu există niciun alt cod D corespunzător, aceasta include operaţiunile preliminare înainte de eliminare, inclusiv preprocesarea, cum ar fi, printre altele, sortarea, sfărâmarea, compactarea, granularea, uscarea, mărunţirea uscată, condiţionarea sau separarea înainte de supunerea la oricare dintre operaţiunile numerotate de la D1 la D12.</li></ul></li>
<li>D14 Reambalarea anterioară oricărei operaţiuni numerotate de la D1 la D13</li>
<li>D15 Stocarea înaintea oricărei operaţiuni numerotate de la D1 la D14 (excluzând stocarea temporară, înaintea colectării, în zona de generare a deşeurilor)
    <ul>
<li>Stocare temporară înseamnă stocare preliminară în conformitate cu articolul 3 punctul 10 din OUG 92/2021.</li>
</ul></li>
                </ul>
    </div>
    </div>
<?php 
	} // close 'elseif (IsSet($_GET['mode']) AND $_GET['mode']=="show")' block
} // close the main else block
?>
<!-- end tabs-content -->
<?php
include '../bottom.php';
?>