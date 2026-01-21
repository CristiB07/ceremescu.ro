<?php
include '../settings.php';
include '../classes/common.php';
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
if (isset($_GET['mode']) && !in_array($_GET['mode'], ['new', 'edit', 'delete', 'view'])) {
    header("location:$strSiteURL/clients/siteclientaspects.php");
    die;
}

if (isset($_GET['fID'])) {
    if (!is_numeric($_GET['fID'])) {
        header("location:$strSiteURL/clients/siteclientaspects.php");
        die;
    }
    $fID = (int)$_GET['fID'];
}

include '../classes/paginator.class.php';
$strPageTitle="Administrare fișe clienți";
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
$stmt = mysqli_prepare($conn, "DELETE FROM clienti_fisa WHERE fisa_ID=?");
mysqli_stmt_bind_param($stmt, 'i', $fID);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
echo "<div class=\"success callout\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteclientaspects.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}
If (IsSet($_GET['mode']) AND $_GET['mode']=="view")	{
	// Prepared statement pentru SQL injection prevention
	$stmt = mysqli_prepare($conn, "SELECT * FROM clienti_fisa WHERE fisa_ID=?");
mysqli_stmt_bind_param($stmt, 'i', $fID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

if (!$row) {
    echo "<div class=\"alert callout\">Fișa nu a fost găsită</div>";
    die;
}

$stmt2 = mysqli_prepare($conn, "SELECT clienti_date.ID_Client, Client_Denumire FROM clienti_date WHERE clienti_date.ID_Client=?");
mysqli_stmt_bind_param($stmt2, 'i', $row['ID_Client']);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
$rss = mysqli_fetch_array($result2, MYSQLI_ASSOC);
mysqli_stmt_close($stmt2);
 ?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="siteclientaspects.php" class="button"><?php echo $strBack?></a></p>
            </div>
        </div>
        <div class="grid-x grid-margin-x">
            <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strClient?></label>
                <?php echo $rss["Client_Denumire"]?>
            </div>
            <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strWasteManagement?></label>
                <?php If ($row["fisa_GD"]==0) echo $strYes; else echo $strNo; ?>
                <label><?php echo $strDSPReporting?></label>
                <?php If ($row["fisa_raportare_DSP"]==0) echo $strYes; else echo $strNo; ?>

            </div>
            <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strPackageManagement?></label>
                <?php If ($row["fisa_GA"]==0) echo $strYes; else echo $strNo; ?>
                <label><?php echo $strOTRName?></label>
                <?php echo $row["fisa_OTR"]?>
            </div>
            <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strEEEManagement?></label>
                <?php If ($row["fisa_DEE"]==0) echo $strYes; else echo $strNo; ?>
                <label><?php echo $strOTRName?></label>
                <?php echo $row["fisa_OTR_EE"]?>
            </div>
        </div>
        <div class="grid-x grid-margin-x">
            <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strBateryManagement?></label>
                <?php If ($row["fisa_baterii"]==0) echo $strYes; else echo $strNo; ?>
                <label><?php echo $strOTRName?></label>
                <?php echo $row["fisa_OTR_BAT"]?>
            </div>
            <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strTiresManagement?></label>
                <?php If ($row["fisa_anvelope"]==0) echo $strYes; else echo $strNo; ?>
                <label><?php echo $strOTRName?></label>
                <?php echo $row["fisa_OTR_Anvelope"]?>
            </div>
            <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strOilManagement?></label>
                <?php If ($row["fisa_uleiuri"]==0) echo $strYes; else echo $strNo; ?>
            </div>
            <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strChemicalsManagement?></label>
                <?php If ($row["fisa_substante"]==0) echo $strYes; else echo $strNo; ?>
            </div>
        </div>
        <div class="grid-x grid-margin-x">
            <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strCOVManagement?></label>
                <?php If ($row["fisa_COV"]==0) echo $strYes; else echo $strNo; ?>
            </div>
            <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strEmmissionsManagement?></label>
                <?php If ($row["fisa_emisii_stationare"]==0) echo $strYes; else echo $strNo; ?>
            </div>
            <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strWaterMonitoring?></label>
                <?php If ($row["fisa_monitorizari_apa"]==0) echo $strYes; else echo $strNo;?>
            </div>
            <div class="large-3 medium-3 small-3 cell">
                <TD colspan="2"><?php echo $strDetails?><br />
                    <?php echo $row["fisa_detalii_monitorizari_apa"]?>
            </div>
        </div>
        <div class="grid-x grid-margin-x">
            <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strAirMonitoring?></label>
                <?php If ($row["fisa_monitorizari_aer"]==0) echo $strYes; else echo $strNo;?>
            </div>
            <div class="large-3 medium-3 small-3 cell">
                <TD colspan="2"><?php echo $strDetails?><br />
                    <?php echo $row["fisa_detalii_monitorizari_aer"]?>
            </div>
            <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strSoilMonitoring?></label>
                <?php If ($row["fisa_monitorizari_sol"]==0) echo $strYes; else echo $strNo;?>
            </div>
            <div class="large-3 medium-3 small-3 cell">
                <label><?php echo $strDetails?><label>
                        <?php echo $row["fisa_detalii_monitorizari_sol"]?>
            </div>
        </div>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <label><?php echo $strOther?><label>
                        <?php echo $row["fisa_alte_detalii"]?>
            </div>
        </div>
    </div>
</div>

<?php	
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){
//insert new user
$dateclient = $_POST['client_ID'];
$result_explode = explode('|', $dateclient);
$clientID=(int)$result_explode[0];
$idcontract=(int)$result_explode[1];

$fisa_GD = (int)$_POST["fisa_GD"];
$fisa_GA = (int)$_POST["fisa_GA"];
$fisa_OTR = (int)$_POST["fisa_OTR"];
$fisa_DEE = (int)$_POST["fisa_DEE"];
$fisa_OTR_EE = (int)$_POST["fisa_OTR_EE"];
$fisa_baterii = (int)$_POST["fisa_baterii"];
$fisa_OTR_BAT = (int)$_POST["fisa_OTR_BAT"];
$fisa_anvelope = (int)$_POST["fisa_anvelope"];
$fisa_OTR_Anvelope = (int)$_POST["fisa_OTR_Anvelope"];
$fisa_uleiuri = (int)$_POST["fisa_uleiuri"];
$fisa_substante = (int)$_POST["fisa_substante"];
$fisa_COV = (int)$_POST["fisa_COV"];
$fisa_emisii_stationare = (int)$_POST["fisa_emisii_stationare"];
$fisa_monitorizari_apa = (int)$_POST["fisa_monitorizari_apa"];
$fisa_detalii_monitorizari_apa = (int)$_POST["fisa_detalii_monitorizari_apa"];
$fisa_monitorizari_aer = (int)$_POST["fisa_monitorizari_aer"];
$fisa_detalii_monitorizari_aer = (int)$_POST["fisa_detalii_monitorizari_aer"];
$fisa_monitorizari_sol = (int)$_POST["fisa_monitorizari_sol"];
$fisa_detalii_monitorizari_sol = (int)$_POST["fisa_detalii_monitorizari_sol"];
$fisa_raportare_DSP = (int)$_POST["fisa_raportare_DSP"];
$fisa_alte_detalii = (int)$_POST["fisa_alte_detalii"];

$stmt = mysqli_prepare($conn, "INSERT INTO clienti_fisa(ID_Client, fisa_GD, fisa_GA, fisa_OTR, fisa_DEE, fisa_OTR_EE, fisa_baterii, fisa_OTR_BAT, fisa_anvelope, fisa_OTR_Anvelope, fisa_uleiuri, fisa_substante, fisa_COV, fisa_emisii_stationare, fisa_monitorizari_apa, fisa_detalii_monitorizari_apa, fisa_monitorizari_aer, fisa_detalii_monitorizari_aer, fisa_monitorizari_sol, fisa_detalii_monitorizari_sol, fisa_raportare_DSP, fisa_alte_detalii) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "iiiiiiiiiiiiiiiiiiiiii", $clientID, $fisa_GD, $fisa_GA, $fisa_OTR, $fisa_DEE, $fisa_OTR_EE, $fisa_baterii, $fisa_OTR_BAT, $fisa_anvelope, $fisa_OTR_Anvelope, $fisa_uleiuri, $fisa_substante, $fisa_COV, $fisa_emisii_stationare, $fisa_monitorizari_apa, $fisa_detalii_monitorizari_apa, $fisa_monitorizari_aer, $fisa_detalii_monitorizari_aer, $fisa_monitorizari_sol, $fisa_detalii_monitorizari_sol, $fisa_raportare_DSP, $fisa_alte_detalii);
	
if (!mysqli_stmt_execute($stmt)) {
    die('Error: ' . mysqli_stmt_error($stmt));
}
mysqli_stmt_close($stmt);

echo "am creat fișa<br />";

// Funcție helper pentru INSERT-uri repetitive în date_activitati_clienti
function insert_activitate_client($conn, $clientID, $activitateID, $userID, $frecventa, $contract, $termen) {
    $stmt = mysqli_prepare($conn, "INSERT INTO date_activitati_clienti(ID_Client, ID_Activitate, ID_user, Activitate_Client_Frecventa, Activitate_Client_Contract, Activitate_Client_Termen) VALUES(?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iiiiis", $clientID, $activitateID, $userID, $frecventa, $contract, $termen);
    if (!mysqli_stmt_execute($stmt)) {
        die('Error: ' . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
    return true;
}

//insert tasks
//1 - GD
If ($_POST["fisa_GD"]==0) {
    insert_activitate_client($conn, $clientID, 1, $uid, 1, $idcontract, "2000-01-31");
    echo "am adăugat gd <br />";
}
//2 - GD anual
If ($_POST["fisa_GD"]==0) {
    insert_activitate_client($conn, $clientID, 1, $uid, 12, $idcontract, "2000-03-31");
    echo "am adăugat gd anual<br />";
}
//2 - GD Plan minimizare
If ($_POST["fisa_GD"]==0) {
        insert_activitate_client($conn, $clientID, 22, $uid, 12, $idcontract, "2000-05-31");
        echo "am adăugat plan minimizare deșeuri. <br />";
	}

	//3 - GA 
	If ($_POST["fisa_GA"]==0) {
        insert_activitate_client($conn, $clientID, 2, $uid, 1, $idcontract, "2000-01-25");
        echo "am adăugat gestiunea ambalajelor<br />";
	}
	//3 - GA Anual
	If (($_POST["fisa_GA"]==0) AND ($_POST["fisa_OTR"]=='')) {
        insert_activitate_client($conn, $clientID, 2, $uid, 12, $idcontract, "2000-02-25");
        echo "am adăugat gestiunea ambalajelor anual<br />";
	}
	//4 - DEE 
	If ($_POST["fisa_DEE"]==0) {
        insert_activitate_client($conn, $clientID, 3, $uid, 1, $idcontract, "2000-01-25");
        echo "am adăugat gestiunea DEE<br />";
	}
	
	//5 - Baterii
	If ($_POST["fisa_baterii"]==0) {
        insert_activitate_client($conn, $clientID, 13, $uid, 1, $idcontract, "2000-01-25");
        echo "am adăugat gestiunea bateriilor<br />";
	}
	//6 - Anvelope
	If ($_POST["fisa_anvelope"]==0) {
        insert_activitate_client($conn, $clientID, 16, $uid, 1, $idcontract, "2000-01-25");
        echo "am adăugat gestiunea anvelopelor<br />";
	}
	
	//7 - Uleiuri
	If ($_POST["fisa_uleiuri"]==0) {
        insert_activitate_client($conn, $clientID, 5, $uid, 12, $idcontract, "2000-01-31");
        echo "am adăugat gestiunea uleiurilor<br />";
	}
	//8 - Substanțe periculoase
	If ($_POST["fisa_substante"]==0) {
        insert_activitate_client($conn, $clientID, 16, $uid, 1, $idcontract, "2000-01-25");
        echo "am adăugat gestiunea substanțelor periculoase<br />";
	}
	//9 - COV
	If ($_POST["fisa_COV"]==0) {
        insert_activitate_client($conn, $clientID, 12, $uid, 12, $idcontract, "2000-01-31");
        echo "am adăugat gestiunea COV<br />";
	}
	//10 - Emisii
	If ($_POST["fisa_emisii_stationare"]==0) {
        insert_activitate_client($conn, $clientID, 11, $uid, 1, $idcontract, "2000-01-25");
        echo "am adăugat gestiunea emisiilor din surse staționare<br />";
	}
	//11 - Monitorizare ape
	If ($_POST["fisa_monitorizari_apa"]==0) {
        $termenlimita = ($_POST["frecventa_monitorizare_apa"]==1) ? "2000-01-25" : "2000-01-01";
        $frecventa_apa = (int)$_POST["frecventa_monitorizare_apa"];
        insert_activitate_client($conn, $clientID, 17, $uid, $frecventa_apa, $idcontract, $termenlimita);
        echo "am adăugat gestiunea monitorizare ape<br />";
	}
	
	//12 - Monitorizare aer
	If ($_POST["fisa_monitorizari_aer"]==0) {
        $termenlimita = ($_POST["frecventa_monitorizare_aer"]==1) ? "2000-01-25" : "2000-01-01";
        $frecventa_aer = (int)$_POST["frecventa_monitorizare_aer"];
        insert_activitate_client($conn, $clientID, 20, $uid, $frecventa_aer, $idcontract, $termenlimita);
        echo "am adăugat monitorizare aer<br />";
	}
	//12 - Monitorizare sol
	If ($_POST["fisa_monitorizari_sol"]==0) {
        $termenlimita = ($_POST["frecventa_monitorizare_sol"]==1) ? "2000-01-25" : "2000-01-01";
        $frecventa_sol = (int)$_POST["frecventa_monitorizare_sol"];
        insert_activitate_client($conn, $clientID, 21, $uid, $frecventa_sol, $idcontract, $termenlimita);
        echo "am adăugat monitorizare sol<br />";
	}
	//13 - Emisii
	If ($_POST["fisa_raportare_DSP"]==0) {
        insert_activitate_client($conn, $clientID, 23, $uid, 1, $idcontract, "2000-01-15");
        echo "am adăugat raportarea DSP<br />";
	}
	//finaly, end, show success
echo "<div class=\"success callout\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteclientaspects.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
Else
{// edit

$cID = (int)$_GET["cID"];
$dateclient = $_POST['client_ID'];
$result_explode = explode('|', $dateclient);
$client_ID = (int)$result_explode[0];

$fisa_GD = (int)$_POST["fisa_GD"];
$fisa_GA = (int)$_POST["fisa_GA"];
$fisa_OTR = (int)$_POST["fisa_OTR"];
$fisa_DEE = (int)$_POST["fisa_DEE"];
$fisa_OTR_EE = (int)$_POST["fisa_OTR_EE"];
$fisa_baterii = (int)$_POST["fisa_baterii"];
$fisa_OTR_BAT = (int)$_POST["fisa_OTR_BAT"];
$fisa_anvelope = (int)$_POST["fisa_anvelope"];
$fisa_OTR_Anvelope = (int)$_POST["fisa_OTR_Anvelope"];
$fisa_uleiuri = (int)$_POST["fisa_uleiuri"];
$fisa_substante = (int)$_POST["fisa_substante"];
$fisa_COV = (int)$_POST["fisa_COV"];
$fisa_emisii_stationare = (int)$_POST["fisa_emisii_stationare"];
$fisa_raportare_DSP = (int)$_POST["fisa_raportare_DSP"];
$fisa_monitorizari_apa = (int)$_POST["fisa_monitorizari_apa"];
$fisa_detalii_monitorizari_apa = (int)$_POST["fisa_detalii_monitorizari_aer"];
$fisa_monitorizari_aer = (int)$_POST["fisa_monitorizari_aer"];
$fisa_detalii_monitorizari_sol = (int)$_POST["fisa_detalii_monitorizari_sol"];
$fisa_alte_detalii = (int)$_POST["fisa_alte_detalii"];

$stmt = mysqli_prepare($conn, "UPDATE clienti_fisa SET ID_Client=?, fisa_GD=?, fisa_GA=?, fisa_OTR=?, fisa_DEE=?, fisa_OTR_EE=?, fisa_baterii=?, fisa_OTR_BAT=?, fisa_anvelope=?, fisa_OTR_Anvelope=?, fisa_uleiuri=?, fisa_substante=?, fisa_COV=?, fisa_emisii_stationare=?, fisa_raportare_DSP=?, fisa_monitorizari_apa=?, fisa_detalii_monitorizari_apa=?, fisa_monitorizari_aer=?, fisa_detalii_monitorizari_sol=?, fisa_alte_detalii=? WHERE fisa_ID=?");
mysqli_stmt_bind_param($stmt, "iiiiiiiiiiiiiiiiiiiii", $client_ID, $fisa_GD, $fisa_GA, $fisa_OTR, $fisa_DEE, $fisa_OTR_EE, $fisa_baterii, $fisa_OTR_BAT, $fisa_anvelope, $fisa_OTR_Anvelope, $fisa_uleiuri, $fisa_substante, $fisa_COV, $fisa_emisii_stationare, $fisa_raportare_DSP, $fisa_monitorizari_apa, $fisa_detalii_monitorizari_apa, $fisa_monitorizari_aer, $fisa_detalii_monitorizari_sol, $fisa_alte_detalii, $cID);

if (!mysqli_stmt_execute($stmt)) {
    die('Error: ' . mysqli_stmt_error($stmt));
}
mysqli_stmt_close($stmt);

echo "<div class=\"success callout\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteclientaspects.php\"
}
//-->
</script>

<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
}
Else {
?>
<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <p><a href="siteclientaspects.php" class="button"><?php echo $strBack?></a></p>
    </div>
</div>
<form Method="post" id="users" Action="siteclientaspects.php?mode=new">
    <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strClient?></label>
            <select name="client_ID" class="required">
                <option value=""><?php echo $strClient?></option>
                <?php 
		  $sql="SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, Contract_Alocat, Client_Denumire, Client_CUI, Client_Localitate, ID_Contract  FROM clienti_date, clienti_contracte 
WHERE Contract_Alocat='$code' AND  clienti_date.ID_Client=clienti_contracte.ID_Client AND Contract_Activ=0
ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                <option value="<?php echo $rss["ID_Client"]."|".$rss["ID_Contract"] ?>">
                    <?php echo $rss["Client_Denumire"]?></option>
                <?php
}?>
            </select>
        </div>
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strWasteManagement?></label>
            <input name="fisa_GD" Type="radio" value="0" checked /> <?php echo $strYes?><input name="fisa_GD"
                Type="radio" value="1" /><?php echo $strNo?>
        </div>
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strDSPReporting?></label>
            <input name="fisa_raportare_DSP" Type="radio" value="0" /> <?php echo $strYes?><input
                name="fisa_raportare_DSP" Type="radio" value="1" checked /><?php echo $strNo?>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strPackageManagement?></label>
            <input name="fisa_GA" Type="radio" value="0" checked /> <?php echo $strYes?><input name="fisa_GA"
                Type="radio" value="1" /><?php echo $strNo?>
        </div>
        <div class="large-8 medium-8 small-8 cell">
            <label><?php echo $strOTRName?></label>
            <input name="fisa_OTR" Type="text" />
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strEEEManagement?></label>
            <input name="fisa_DEE" Type="radio" value="0" /> <?php echo $strYes?><input name="fisa_DEE" Type="radio"
                value="1" checked /><?php echo $strNo?>
        </div>
        <div class="large-8 medium-8 small-8 cell">
            <label><?php echo $strOTRName?></label>
            <input name="fisa_OTR_EE" Type="text" size="30" />
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strBateryManagement?></label>
            <input name="fisa_baterii" Type="radio" value="0" /> <?php echo $strYes?><input name="fisa_baterii"
                Type="radio" value="1" checked /><?php echo $strNo?>
        </div>
        <div class="large-8 medium-8 small-8 cell">
            <label><?php echo $strOTRName?></label>
            <input name="fisa_OTR_BAT" Type="text" size="30" />
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strTiresManagement?></label>
            <input name="fisa_anvelope" Type="radio" value="0" /> <?php echo $strYes?><input name="fisa_anvelope"
                Type="radio" value="1" checked /><?php echo $strNo?>
        </div>
        <div class="large-8 medium-8 small-8 cell">
            <label><?php echo $strOTRName?></label>
            <input name="fisa_OTR_Anvelope" Type="text" size="30" />
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strOilManagement?></label>
            <input name="fisa_uleiuri" Type="radio" value="0" /> <?php echo $strYes?><input name="fisa_uleiuri"
                Type="radio" value="1" checked /><?php echo $strNo?>
        </div>
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strChemicalsManagement?></label>
            <input name="fisa_substante" Type="radio" value="0" /> <?php echo $strYes?><input name="fisa_substante"
                Type="radio" value="1" checked /><?php echo $strNo?>
        </div>
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strCOVManagement?></label>
            <input name="fisa_COV" Type="radio" value="0" /> <?php echo $strYes?><input name="fisa_COV" Type="radio"
                value="1" checked /><?php echo $strNo?>
        </div>
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strEmmissionsManagement?></label>
            <input name="fisa_emisii_stationare" Type="radio" value="0" /> <?php echo $strYes?><input
                name="fisa_emisii_stationare" Type="radio" value="1" checked /><?php echo $strNo?>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strWaterMonitoring?></label>
            <input name="fisa_monitorizari_apa" Type="radio" value="0"
                onclick='document.getElementById("monitorizareapa").style.display="block"' ; /> <?php echo $strYes?>
            <input name="fisa_monitorizari_apa" Type="radio" value="1" checked
                onclick='document.getElementById("monitorizareapa").style.display="none"' ; /><?php echo $strNo?>
        </div>
        <div class="large-4 medium-4 small-4 cell">
            <div id="monitorizareapa" style="display:none" width="100%">
                <label><?php echo $strFrequency?></label>
                <input name="frecventa_monitorizare_apa" Type="radio" value="1" /> <?php echo $strMonthly?>
                <input name="frecventa_monitorizare_apa" Type="radio" value="3" /> <?php echo $strQuaterly?>
                <input name="frecventa_monitorizare_apa" Type="radio" value="6" /> <?php echo $strSemestrial?>
                <input name="frecventa_monitorizare_apa" Type="radio" value="12" /> <?php echo $strYearly?>
            </div>
        </div>
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strDetails?></label>
            <textarea name="fisa_detalii_monitorizari_apa" style="width:100%;"></textarea>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strAirMonitoring?></label>
            <input name="fisa_monitorizari_aer" Type="radio" value="0"
                onclick='document.getElementById("monitorizareaer").style.display="block"' ; /> <?php echo $strYes?>
            <input name="fisa_monitorizari_aer" Type="radio" value="1" checked
                onclick='document.getElementById("monitorizareaer").style.display="none"' ; /><?php echo $strNo?>
        </div>
        <div class="large-4 medium-4 small-4 cell">
            <div id="monitorizareaer" style="display:none" width="100%">
                <label><?php echo $strFrequency?></label>
                <input name="frecventa_monitorizare_aer" Type="radio" value="1" /> <?php echo $strMonthly?>
                <input name="frecventa_monitorizare_aer" Type="radio" value="3" /> <?php echo $strQuaterly?>
                <input name="frecventa_monitorizare_aer" Type="radio" value="6" /> <?php echo $strSemestrial?>
                <input name="frecventa_monitorizare_aer" Type="radio" value="12" /> <?php echo $strYearly?>
            </div>
        </div>
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strDetails?></label>
            <textarea name="fisa_detalii_monitorizari_aer" style="width:100%;"></textarea>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strSoilMonitoring?></label>
            <input name="fisa_monitorizari_sol" Type="radio" value="0"
                onclick='document.getElementById("monitorizaresol").style.display="block"' ; /><?php echo $strYes?>
            <input name="fisa_monitorizari_sol" Type="radio" value="1" checked
                onclick='document.getElementById("monitorizaresol").style.display="none"' ; /><?php echo $strNo?>
        </div>
        <div class="large-4 medium-4 small-4 cell">
            <div id="monitorizaresol" style="display:none">
                <label><?php echo $strFrequency?></label>
                <input name="frecventa_monitorizare_sol" Type="radio" value="1" /> <?php echo $strMonthly?>
                <input name="frecventa_monitorizare_sol" Type="radio" value="3" /> <?php echo $strQuaterly?>
                <input name="frecventa_monitorizare_sol" Type="radio" value="6" /> <?php echo $strSemestrial?>
                <input name="frecventa_monitorizare_sol" Type="radio" value="12" /> <?php echo $strYearly?>
            </div>
        </div>
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strDetails?></label>
            <textarea name="fisa_detalii_monitorizari_sol" style="width:100%;"></textarea>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <label><?php echo $strOther?></label>
            <textarea name="fisa_alte_detalii" style="width:100%;"></textarea>
        </div>
    </div>

    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell text-center"> <input Type="submit" Value="<?php echo $strAdd?>" name="Submit"
                class="button success">
        </div>
    </div>
</form>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$fID = (int)$_GET['fID'];
$stmt = mysqli_prepare($conn, "SELECT * FROM clienti_fisa WHERE fisa_ID=?");
mysqli_stmt_bind_param($stmt, "i", $fID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <p><a href="siteclientaspects.php" class="button"><?php echo $strBack?></a></p>
    </div>
</div>
<form Method="post" id="users" Action="siteclientaspects.php?mode=edit&cID=<?php echo $row['fisa_ID']?>">

    <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strClient?></label>
            <select name="client_ID" class="required">
                <?php 
		  $sql="SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, Contract_Alocat, Client_Denumire, Client_CUI, Client_Localitate, ID_Contract  FROM clienti_date, clienti_contracte 
WHERE Contract_Alocat='$code' AND  clienti_date.ID_Client=clienti_contracte.ID_Client AND Contract_Activ=0
ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                <option <?php if ($row["ID_Client"]==$rss["ID_Client"]) echo "selected"; ?>
                    value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
                <?php
}?>
            </select>
        </div>
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strWasteManagement?></label>
            <input name="fisa_GD" Type="radio" value="0" <?php If ($row["fisa_GD"]==0) echo "checked"?> />
            <?php echo $strYes?><input name="fisa_GD" Type="radio" value="1"
                <?php If ($row["fisa_GD"]==1) echo "checked"?> /><?php echo $strNo?>
        </div>
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strDSPReporting?></label>
            <input name="fisa_raportare_DSP" Type="radio" value="0"
                <?php If ($row["fisa_raportare_DSP"]==0) echo "checked"?> /> <?php echo $strYes?><input
                name="fisa_raportare_DSP" Type="radio" value="1"
                <?php If ($row["fisa_raportare_DSP"]==1) echo "checked"?> /><?php echo $strNo?>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strPackageManagement?></label>
            <input name="fisa_GA" Type="radio" value="0" <?php If ($row["fisa_GA"]==0) echo "checked"?> />
            <?php echo $strYes?><input name="fisa_GA" Type="radio" value="1"
                <?php If ($row["fisa_GA"]==1) echo "checked"?> /><?php echo $strNo?>
        </div>
        <div class="large-8 medium-8 small-8 cell">
            <label><?php echo $strOTRName?></label>
            <input name="fisa_OTR" Type="text" size="30" value="<?php echo $row["fisa_OTR"]?>" />
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strEEEManagement?></label>
            <input name="fisa_DEE" Type="radio" value="0" <?php If ($row["fisa_DEE"]==0) echo "checked"?> />
            <?php echo $strYes?><input name="fisa_DEE" Type="radio" value="1"
                <?php If ($row["fisa_DEE"]==1) echo "checked"?> /><?php echo $strNo?>
        </div>
        <div class="large-8 medium-8 small-8 cell">
            <label><?php echo $strOTRName?></label>
            <input name="fisa_OTR_EE" Type="text" size="30" value="<?php echo $row["fisa_OTR_EE"]?>" />
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strBateryManagement?></label>
            <input name="fisa_baterii" Type="radio" value="0" <?php If ($row["fisa_baterii"]==0) echo "checked"?> />
            <?php echo $strYes?><input name="fisa_baterii" Type="radio" value="1"
                <?php If ($row["fisa_baterii"]==1) echo "checked"?> /><?php echo $strNo?>
        </div>
        <div class="large-8 medium-8 small-8 cell">
            <label><?php echo $strOTRName?></label>
            <input name="fisa_OTR_BAT" Type="text" size="30" value="<?php echo $row["fisa_OTR_BAT"]?>" />
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strTiresManagement?></label>
            <input name="fisa_anvelope" Type="radio" value="0" <?php If ($row["fisa_anvelope"]==0) echo "checked"?> />
            <?php echo $strYes?><input name="fisa_anvelope" Type="radio" value="1"
                <?php If ($row["fisa_anvelope"]==1) echo "checked"?> /><?php echo $strNo?>
        </div>
        <div class="large-8 medium-8 small-8 cell">
            <label><?php echo $strOTRName?></label>
            <input name="fisa_OTR_Anvelope" Type="text" size="30" value="<?php echo $row["fisa_OTR_Anvelope"]?>" />
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strOilManagement?></label>
            <input name="fisa_uleiuri" Type="radio" value="0" <?php If ($row["fisa_uleiuri"]==0) echo "checked"?> />
            <?php echo $strYes?><input name="fisa_uleiuri" Type="radio" value="1"
                <?php If ($row["fisa_uleiuri"]==1) echo "checked"?> /><?php echo $strNo?>
        </div>
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strChemicalsManagement?></label>
            <input name="fisa_substante" Type="radio" value="0" <?php If ($row["fisa_substante"]==0) echo "checked"?> />
            <?php echo $strYes?><input name="fisa_substante" Type="radio" value="1"
                <?php If ($row["fisa_substante"]==1) echo "checked"?> /><?php echo $strNo?>
        </div>
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strCOVManagement?></label>
            <input name="fisa_COV" Type="radio" value="0" <?php If ($row["fisa_COV"]==0) echo "checked"?> />
            <?php echo $strYes?><input name="fisa_COV" Type="radio" value="1"
                <?php If ($row["fisa_COV"]==1) echo "checked"?> /><?php echo $strNo?>
        </div>
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strEmmissionsManagement?></label>
            <input name="fisa_emisii_stationare" Type="radio" value="0"
                <?php If ($row["fisa_emisii_stationare"]==0) echo "checked"?> /> <?php echo $strYes?><input
                name="fisa_emisii_stationare" Type="radio" value="1"
                <?php If ($row["fisa_emisii_stationare"]==1) echo "checked"?> /><?php echo $strNo?>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strWaterMonitoring?></label>
            <input name="fisa_monitorizari_apa" Type="radio" value="0"
                <?php If ($row["fisa_monitorizari_apa"]==0) echo "checked"?> /> <?php echo $strYes?><input
                name="fisa_monitorizari_apa" Type="radio" value="1"
                <?php If ($row["fisa_monitorizari_apa"]==1) echo "checked"?> /><?php echo $strNo?>
        </div>
        <div class="large-8 medium-8 small-8 cell">
            <label><?php echo $strDetails?><br />
                <textarea name="fisa_detalii_monitorizari_apa" style="width:100%;"
                    value="<?php echo $row["fisa_detalii_monitorizari_apa"]?>"></textarea>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strAirMonitoring?></label>
            <input name="fisa_monitorizari_aer" Type="radio" value="0"
                <?php If ($row["fisa_monitorizari_aer"]==0) echo "checked"?> /> <?php echo $strYes?><input
                name="fisa_monitorizari_aer" Type="radio" value="1"
                <?php If ($row["fisa_monitorizari_aer"]==1) echo "checked"?> /><?php echo $strNo?>
        </div>
        <div class="large-8 medium-8 small-8 cell">
            <label><?php echo $strDetails?></label>
            <textarea name="fisa_detalii_monitorizari_aer" style="width:100%;"
                value="<?php echo $row["fisa_detalii_monitorizari_aer"]?>"></textarea>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strSoilMonitoring?></label>
            <input name="fisa_monitorizari_sol" Type="radio" value="0"
                <?php If ($row["fisa_monitorizari_sol"]==0) echo "checked"?> /> <?php echo $strYes?><input
                name="fisa_monitorizari_sol" Type="radio" value="1"
                <?php If ($row["fisa_monitorizari_sol"]==1) echo "checked"?> /><?php echo $strNo?>
        </div>
        <div class="large-8 medium-8 small-8 cell">
            <label><?php echo $strDetails?></label>
            <textarea name="fisa_detalii_monitorizari_sol" style="width:100%;"
                value="<?php echo $row["fisa_detalii_monitorizari_sol"]?>"></textarea>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <TD colspan="2"><?php echo $strOther?><br />
                <textarea name="fisa_alte_detalii" style="width:100%;"
                    value="<?php echo $row["fisa_alte_detalii"]?>"></textarea>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell text-center">
            <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success">
        </div>
    </div>
</form>
<?php
}
else
		{
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"siteclientaspects.php?mode=new\" class=\"button\"><i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i>&nbsp;$strAdd</a></div></div>";
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_contracte.Contract_Alocat, clienti_contracte.ID_Client, 
clienti_fisa.ID_Client, clienti_fisa.fisa_ID
FROM clienti_fisa, clienti_date, clienti_contracte
WHERE clienti_date.ID_Client=clienti_contracte.ID_Client AND clienti_date.ID_Client=clienti_fisa.ID_Client ";
if ($_SESSION['clearence']=='USER')
{
$query.=" AND clienti_contracte.Contract_Alocat='$code'";
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
echo $strTotal . " " .$numar." ".$strClientsAspects ;
echo " <br /><br />";
echo $pages->display_pages();
echo " <br /><br /></div>";

?>
    <table width="100%">
        <thead>
            <tr>
                <th width="80%"><?php echo $strClient?></th>
                <th width="5%"><?php echo $strView?></th>
                <th width="5%"><?php echo $strEdit?></th>
                <th width="5%"><?php echo $strDelete?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			  <td><a href=\"siteclientaspects.php?mode=view&fID=$row[fisa_ID]\"><i class=\"fa fa-search-plus fa-xl\" title=\"$strView\"></i></a></td>
			  <td><a href=\"siteclientaspects.php?mode=edit&fID=$row[fisa_ID]\" ><i class=\"fas fa-pencil-alt fa-xl\" title=\"$strEdit\"></i></a></td>
              <td><a href=\"siteclientaspects.php?mode=delete&fID=$row[fisa_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>";
        echo "</tr>";
}
echo "</tbody><tfoot><tr><td colspan=\"4\">&nbsp;</td></tr></tfoot></table>";
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