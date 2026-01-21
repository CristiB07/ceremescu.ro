<?php // Last Modified Time: Friday, August 29, 2025 at 3:12:48 PM Eastern European Summer Time ?>
<?php
include '../settings.php';
include '../classes/common.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/index.php?message=MLF");
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


$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$d = date("Y-m-d H:i:s");
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
?>
<!doctype html>

<head>
    <!--Start Header-->
    <!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"/> <![endif]-->
    <!--[if IE 7]> <html class="no-js lt-ie9 lt-ie8" lang="en"/> <![endif]-->
    <!--[if IE 8]> <html class="no-js lt-ie9" lang="en"/> <![endif]-->
    <!--[if gt IE 8]><!-->
    <html class="no-js" lang="en">
    <!--<![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!--Font Awsome-->
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/all.css">
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/foundation.css" />
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/<?php echo $cssname ?>.css" />

    <script>
    function resizeIframe(obj) {
        obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
    }
    </script>
</head>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){
    // Validate mode
    if (!in_array($_GET['mode'], ['delete', 'new', 'edit'])) {
        die("Invalid mode parameter");
    }
    
    // Validate aID
    if (!isset($_GET['aID']) || !filter_var($_GET['aID'], FILTER_VALIDATE_INT)) {
        die("Invalid article ID");
    }
    $aID = (int)$_GET['aID'];
    
    // Validate cID
    if (!isset($_GET['cID']) || !filter_var($_GET['cID'], FILTER_VALIDATE_INT)) {
        die("Invalid invoice ID");
    }
    $cID = (int)$_GET['cID'];
    
    // DELETE with prepared statement
    $stmt = mysqli_prepare($conn, "DELETE FROM facturare_articole_facturi WHERE articol_ID=?");
    mysqli_stmt_bind_param($stmt, "i", $aID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo "<div class=\"callout success\">$strRecordDeleted</div>";
    $cID_escaped = htmlspecialchars($cID, ENT_QUOTES, 'UTF-8');
    echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteinvoiceitems.php?cID=" . $cID_escaped . "\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
    die;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    check_inject();
    
    // Validate mode
    if (!isset($_GET['mode']) || !in_array($_GET['mode'], ['new', 'edit'])) {
        die("Invalid mode parameter");
    }
    
    If ($_GET['mode']=="new"){
        // Validate cID
        if (!isset($_GET['cID']) || !filter_var($_GET['cID'], FILTER_VALIDATE_INT)) {
            die("Invalid invoice ID");
        }
        $cID = (int)$_GET['cID'];
        
        // Validate POST parameters
        if (!isset($_POST['articol_descriere'], $_POST['articol_unitate'], $_POST['articol_bucati'], 
                    $_POST['articol_pret'], $_POST['articol_valoare'], $_POST['articol_procent_TVA'], 
                    $_POST['articol_total'], $_POST['articol_TVA'])) {
            die("Missing required parameters");
        }
        
        // Validate numeric fields
        $articol_bucati = filter_var($_POST['articol_bucati'], FILTER_VALIDATE_FLOAT);
        $articol_pret = filter_var($_POST['articol_pret'], FILTER_VALIDATE_FLOAT);
        $articol_valoare = filter_var($_POST['articol_valoare'], FILTER_VALIDATE_FLOAT);
        $articol_procent_TVA = filter_var($_POST['articol_procent_TVA'], FILTER_VALIDATE_FLOAT);
        $articol_total = filter_var($_POST['articol_total'], FILTER_VALIDATE_FLOAT);
        $articol_TVA = filter_var($_POST['articol_TVA'], FILTER_VALIDATE_FLOAT);
        
        if ($articol_bucati === false || $articol_pret === false || $articol_valoare === false || 
            $articol_procent_TVA === false || $articol_total === false || $articol_TVA === false) {
            die("Invalid numeric values");
        }
        
        $articol_descriere = $_POST['articol_descriere'];
        $articol_unitate = $_POST['articol_unitate'];
        
        // INSERT with prepared statement
        $stmt = mysqli_prepare($conn, "INSERT INTO facturare_articole_facturi(
            factura_ID, articol_descriere, articol_unitate, articol_bucati, articol_pret, 
            articol_valoare, articol_procent_TVA, articol_total, articol_TVA
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        mysqli_stmt_bind_param($stmt, "issdddddd", 
            $cID, $articol_descriere, $articol_unitate, $articol_bucati, $articol_pret, 
            $articol_valoare, $articol_procent_TVA, $articol_total, $articol_TVA
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            die('Error: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        
        echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
        $cID_escaped = htmlspecialchars($cID, ENT_QUOTES, 'UTF-8');
        echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteinvoiceitems.php?cID=" . $cID_escaped . "\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
        die;
    }
    else
    {// edit
        // Validate aID
        if (!isset($_GET['aID']) || !filter_var($_GET['aID'], FILTER_VALIDATE_INT)) {
            die("Invalid article ID");
        }
        $aID = (int)$_GET['aID'];
        
        // Validate cID
        if (!isset($_GET['cID']) || !filter_var($_GET['cID'], FILTER_VALIDATE_INT)) {
            die("Invalid invoice ID");
        }
        $cID = (int)$_GET['cID'];
        
        // Validate POST parameters
        if (!isset($_POST['articol_descriere'], $_POST['articol_unitate'], $_POST['articol_bucati'], 
                    $_POST['articol_pret'], $_POST['articol_valoare'], $_POST['articol_procent_TVA'], 
                    $_POST['articol_total'], $_POST['articol_TVA'])) {
            die("Missing required parameters");
        }
        
        // Validate numeric fields
        $articol_bucati = filter_var($_POST['articol_bucati'], FILTER_VALIDATE_FLOAT);
        $articol_pret = filter_var($_POST['articol_pret'], FILTER_VALIDATE_FLOAT);
        $articol_valoare = filter_var($_POST['articol_valoare'], FILTER_VALIDATE_FLOAT);
        $articol_procent_TVA = filter_var($_POST['articol_procent_TVA'], FILTER_VALIDATE_FLOAT);
        $articol_total = filter_var($_POST['articol_total'], FILTER_VALIDATE_FLOAT);
        $articol_TVA = filter_var($_POST['articol_TVA'], FILTER_VALIDATE_FLOAT);
        
        if ($articol_bucati === false || $articol_pret === false || $articol_valoare === false || 
            $articol_procent_TVA === false || $articol_total === false || $articol_TVA === false) {
            die("Invalid numeric values");
        }
        
        $articol_descriere = $_POST['articol_descriere'];
        $articol_unitate = $_POST['articol_unitate'];
        
        // UPDATE with prepared statement
        $stmt = mysqli_prepare($conn, "UPDATE facturare_articole_facturi SET 
            articol_descriere=?, articol_unitate=?, articol_bucati=?, articol_pret=?, 
            articol_valoare=?, articol_procent_TVA=?, articol_total=?, articol_TVA=? 
            WHERE articol_ID=?");
        
        mysqli_stmt_bind_param($stmt, "ssddddddi", 
            $articol_descriere, $articol_unitate, $articol_bucati, $articol_pret, 
            $articol_valoare, $articol_procent_TVA, $articol_total, $articol_TVA, $aID
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            die('Error: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        
        echo "<div class=\"callout success\">$strRecordModified</div>";
        $cID_escaped = htmlspecialchars($cID, ENT_QUOTES, 'UTF-8');
        echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteinvoiceitems.php?cID=" . $cID_escaped . "\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
        die;
    }
}
else {
?>
        <div class="grid-x grid-padding-x">
            <div class="large-12 medium-12 small-12 cell">
                <iframe src="euroconversion.php" frameborder="0" style="border:0" Width="100%" height="250"
                    scrolling="no" onload="resizeIframe(this)"></iframe>
            </div>
        </div>
        <script>
        function calculate(rowIndex) {
            var myBox1 = document.getElementById('articol_bucati_' + rowIndex).value;
            var myBox2 = document.getElementById('articol_pret_' + rowIndex).value;
            var articol_valoare = document.getElementById('articol_valoare_' + rowIndex);
            var myResult = myBox1 * myBox2;
            articol_valoare.value = myResult;
        }
        </script>
        <script>
        function calculateTVA(rowIndex) {
            var myBox3 = document.getElementById('articol_valoare_' + rowIndex).value;
            var articol_TVA = document.getElementById('articol_TVA_' + rowIndex);
            var myBox33 = document.getElementById('articol_procent_TVA_' + rowIndex).value;
            var myResult1 = myBox3 * myBox33 / 100;
            articol_TVA.value = myResult1;
        }
        </script>
        <script>
        function calculateTotal(rowIndex) {
            var myBox4 = document.getElementById('articol_valoare_' + rowIndex).value;
            var myBox5 = document.getElementById('articol_TVA_' + rowIndex).value;
            var articol_total = document.getElementById('articol_total_' + rowIndex);
            var myResult2 = +myBox4 + +myBox5;
            articol_total.value = myResult2;
        }
        </script>

        <table width="100%">
            <thead>
                <tr>
                    <th width="30%"><?php echo $strDetails?></th>
                    <th><?php echo $strUnit?></th>
                    <th><?php echo $strItems?></th>
                    <th><?php echo $strPrice?></th>
                    <th><?php echo $strValue?></th>
                    <th><?php echo $strVATPercent?></th>
                    <th><?php echo $strVAT?></th>
                    <th><?php echo $strTotal?></th>
                    <th><?php echo $strAdd?></th>
                    <th><?php echo $strDelete?></th>
                </tr>
            </thead>
            <?php
            // Validate cID
            if (!isset($_GET['cID']) || !filter_var($_GET['cID'], FILTER_VALIDATE_INT)) {
                die("Invalid invoice ID");
            }
            $cID = (int)$_GET['cID'];
            
            // SELECT with prepared statement
            $stmt = mysqli_prepare($conn, "SELECT * FROM facturare_articole_facturi WHERE factura_ID=?");
            mysqli_stmt_bind_param($stmt, "i", $cID);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $numar = mysqli_num_rows($result);
            mysqli_stmt_close($stmt);
            
            if ($numar==0)
            {
		?>

            <form method="post" Action="siteinvoiceitems.php?mode=new&cID=<?php echo htmlspecialchars($cID, ENT_QUOTES, 'UTF-8')?>">
                <tr>
                    <td><input name="articol_descriere" type="text" id="obiect"></td>
                    <td><input name="articol_unitate" type="text" size="4" value="" /></td>
                    <td><input name="articol_bucati" id="articol_bucati_0" type="text" size="4" value=""
                            oninput="calculate(0)" /></td>
                    <td><input name="articol_pret" id="articol_pret_0" type="text" size="10" value=""
                            oninput="calculate(0)" /></td>
                    <td><input name="articol_valoare" id="articol_valoare_0" type="text" size="10" value="" /></td>
                    <td><input name="articol_procent_TVA" id="articol_procent_TVA_0" type="text" size="2" value="" />
                    </td>
                    <td><input name="articol_TVA" type="text" id="articol_TVA_0" size="10" value=""
                            onfocus="calculateTVA(0)" /></td>
                    <td><input name="articol_total" type="text" id="articol_total_0" size="10" value=""
                            onfocus="calculateTotal(0)" /></td>
                    <td><input type="submit" Value="<?php echo $strAdd?>" class="button" name="Submit"></td>
                    <td>
                        <p class="button"><i class="fa fa-eraser fa-xl" title="<?php echo $strDelete?>"></i></p>
                    </td>
                </tr>
            </form>
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td colspan="8"><em></em></td>
                    <td>&nbsp;</td>
                </tr>
            </tfoot>
        </table>
        <?php
}
else
{
		$valoareproduse=0;
		$valoareTVA=0;
		$i=0;
	While ($row=ezpub_fetch_array($result)){
$i=$i+1;
		?>
        <form method="post" id="users"
            Action="siteinvoiceitems.php?mode=edit&cID=<?php echo htmlspecialchars($cID, ENT_QUOTES, 'UTF-8')?>&aID=<?php echo htmlspecialchars($row["articol_ID"], ENT_QUOTES, 'UTF-8')?>">
            <tr>
                <td><input name="articol_descriere" type="text" id="obiect"
                        value="<?php echo htmlspecialchars($row["articol_descriere"], ENT_QUOTES, 'UTF-8')?>"></td>
                <td><input name="articol_unitate" type="text" size="4" value="<?php echo htmlspecialchars($row["articol_unitate"], ENT_QUOTES, 'UTF-8')?>" />
                </td>
                <td><input name="articol_bucati" type="text" id="articol_bucati_<?php echo $i?>" size="4"
                        value="<?php echo htmlspecialchars($row["articol_bucati"], ENT_QUOTES, 'UTF-8')?>" oninput="calculate(<?php echo $i?>)" /></td>
                <td><input name="articol_pret" type="text" id="articol_pret_<?php echo $i?>" size="10"
                        value="<?php echo htmlspecialchars($row["articol_pret"], ENT_QUOTES, 'UTF-8')?>" oninput="calculate(<?php echo $i?>)" /></td>
                <td><input name="articol_valoare" type="text" id="articol_valoare_<?php echo $i?>" size="10"
                        value="<?php echo htmlspecialchars($row["articol_valoare"], ENT_QUOTES, 'UTF-8')?>" /></td>
                <td><input name="articol_procent_TVA" type="text" id="articol_procent_TVA_<?php echo $i?>" size="2"
                        value="<?php echo htmlspecialchars($row["articol_procent_TVA"], ENT_QUOTES, 'UTF-8')?>" /></td>
                <td><input name="articol_TVA" type="text" id="articol_TVA_<?php echo $i?>" size="10"
                        value="<?php echo htmlspecialchars($row["articol_TVA"], ENT_QUOTES, 'UTF-8')?>" onfocus="calculateTVA(<?php echo $i?>)" /></td>
                <td><input name="articol_total" type="text" id="articol_total_<?php echo $i?>" size="10"
                        value="<?php echo htmlspecialchars($row["articol_total"], ENT_QUOTES, 'UTF-8')?>" onfocus="calculateTotal(<?php echo $i?>)" /></td>
                <td><input type="submit" Value="<?php echo $strModify?>" name="Submit" class="button"></td>
                <td>
                    <a href="siteinvoiceitems.php?mode=delete&aID=<?php echo htmlspecialchars($row["articol_ID"], ENT_QUOTES, 'UTF-8')?>&cID=<?php echo htmlspecialchars($cID, ENT_QUOTES, 'UTF-8')?>"
                        class="button" OnClick="return confirm('<?php echo $strConfirmDelete?>');">
                        <i class="fa fa-eraser fa-xl" title="<?php echo $strDelete?>"></i></a>
                </td>
            </tr>
        </form>
        <?php 
	$valoareproduse=$valoareproduse+$row["articol_valoare"];
	$valoareTVA=$valoareTVA+$row["articol_TVA"];
	} ?>
        <form method="post" Action="siteinvoiceitems.php?mode=new&cID=<?php echo htmlspecialchars($cID, ENT_QUOTES, 'UTF-8')?>">
            <tr>
                <td><input name="articol_descriere" type="text" id="obiect" value=""></td>
                <td><input name="articol_unitate" type="text" size="4" value="" /></td>
                <td><input name="articol_bucati" id="articol_bucati_0" type="text" size="4" value=""
                        oninput="calculate(0)" /></td>
                <td><input name="articol_pret" id="articol_pret_0" type="text" size="10" value=""
                        oninput="calculate(0)" /></td>
                <td><input name="articol_valoare" id="articol_valoare_0" type="text" size="10" value="" /></td>
                <td><input name="articol_procent_TVA" id="articol_procent_TVA_0" type="text" size="2" value="" /></td>
                <td><input name="articol_TVA" type="text" id="articol_TVA_0" size="10" value=""
                        onfocus="calculateTVA(0)" /></td>
                <td><input name="articol_total" type="text" id="articol_total_0" size="10" value=""
                        onfocus="calculateTotal(0)" /></td>
                <td><input type="submit" Value="<?php echo $strAdd?>" class="button" name="Submit"></td>
                <td>
                    <p class="button"><i class="fa fa-eraser fa-xl" title="<?php echo $strDelete?>"></i></p>
                </td>
            </tr>
        </form>
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td colspan="8"><em></em></td>
                <td>&nbsp;</td>
            </tr>
        </tfoot>
        </table>

        <table width="100%">
            <thead>
                <tr>
                    <th><?php echo $strTotal?></th>
                    <th><?php echo $strValue?></th>
                    <th><?php echo $strVAT?></th>
                    <th><?php echo $strTotal?></th>
                </tr>
            </thead>
            <tr>
                <?php
		$grandtotal=$valoareTVA+$valoareproduse;
		echo "<td>$strTotal</td>";
		echo "<td>". romanize($valoareproduse)."</td>";
		echo "<td>". romanize($valoareTVA)."</td>";
		echo "<td>". romanize($grandtotal)."</td>";
		?>
            </tr>
            <tfoot>
                <tr></tr>
        </table>
        <?php
	
}
}
?>
    </div>
</div>