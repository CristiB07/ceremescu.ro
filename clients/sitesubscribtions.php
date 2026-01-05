<?php
include '../settings.php';
include '../classes/common.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes")
{
	header("location:$strSiteURL/login/login.php?message=MLF");
	die;
}

// Validare parametri GET
if (isset($_GET['mode']) && !in_array($_GET['mode'], ['new', 'edit', 'delete'])) {
    header("location:$strSiteURL/clients/sitesubscribtions.php");
    die;
}

if (isset($_GET['cID'])) {
    if (!is_numeric($_GET['cID'])) {
        header("location:$strSiteURL/clients/sitesubscribtions.php");
        die;
    }
    $cID = (int)$_GET['cID'];
}

include '../classes/paginator.class.php';
$strPageTitle="Administrare abonamente";
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
$stmt = mysqli_prepare($conn, "DELETE FROM clienti_abonamente WHERE abonament_ID=?");
mysqli_stmt_bind_param($stmt, 'i', $cID);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-2);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
If ($_GET['mode']=="new"){
// Handle lunafacturare array
if (isset($_POST['abonament_client_lunafacturare']) && is_array($_POST['abonament_client_lunafacturare'])) {
    $lunifacturare = implode(';', $_POST['abonament_client_lunafacturare']);
} else {
    $lunifacturare = '';
}

$abonament_client_id = isset($_POST["abonament_client_ID"]) && is_numeric($_POST["abonament_client_ID"]) ? (int)$_POST["abonament_client_ID"] : 0;

// Sanitize all POST variables
$abonament_client_valoare = mysqli_real_escape_string($conn, $_POST["abonament_client_valoare"] ?? '');
$abonament_client_valuta = mysqli_real_escape_string($conn, $_POST["abonament_client_valuta"] ?? '');
$abonament_client_frecventa = mysqli_real_escape_string($conn, $_POST["abonament_client_frecventa"] ?? '');
$abonament_client_aloc = mysqli_real_escape_string($conn, $_POST["abonament_client_aloc"] ?? '');
$abonament_client_detalii = mysqli_real_escape_string($conn, $_POST["abonament_client_detalii"] ?? '');
$abonament_client_unitate = mysqli_real_escape_string($conn, $_POST["abonament_client_unitate"] ?? '');
$abonament_client_termen = mysqli_real_escape_string($conn, $_POST["abonament_client_termen"] ?? '');
$abonament_client_zifacturare = mysqli_real_escape_string($conn, $_POST["abonament_client_zifacturare"] ?? '');
$abonament_client_activ = mysqli_real_escape_string($conn, $_POST["abonament_client_activ"] ?? '');
$abonament_client_email = mysqli_real_escape_string($conn, $_POST["abonament_client_email"] ?? '');
$abonament_client_an = mysqli_real_escape_string($conn, $_POST["abonament_client_an"] ?? '');
$abonament_client_BU = mysqli_real_escape_string($conn, $_POST["abonament_client_BU"] ?? '');
$abonament_client_sales = mysqli_real_escape_string($conn, $_POST["abonament_client_sales"] ?? '');
$abonament_client_anexa = mysqli_real_escape_string($conn, $_POST["abonament_client_anexa"] ?? '');
$abonament_client_pdf = mysqli_real_escape_string($conn, $_POST["abonament_client_pdf"] ?? '');
$abonament_client_contract = mysqli_real_escape_string($conn, $_POST["abonament_client_contract"] ?? '');

$stmt = mysqli_prepare($conn, 
    "INSERT INTO clienti_abonamente(abonament_client_ID, abonament_client_valoare, abonament_client_valuta, 
    abonament_client_frecventa, abonament_client_aloc, abonament_client_detalii, abonament_client_unitate, 
    abonament_client_termen, abonament_client_zifacturare, abonament_client_activ, abonament_client_email, 
    abonament_client_an, abonament_client_BU, abonament_client_sales, abonament_client_anexa, abonament_client_pdf, 
    abonament_client_lunafacturare, abonament_client_contract) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param($stmt, 'isssssssssssssssss', 
    $abonament_client_id, 
    $abonament_client_valoare, 
    $abonament_client_valuta, 
    $abonament_client_frecventa, 
    $abonament_client_aloc, 
    $abonament_client_detalii, 
    $abonament_client_unitate, 
    $abonament_client_termen, 
    $abonament_client_zifacturare, 
    $abonament_client_activ, 
    $abonament_client_email, 
    $abonament_client_an, 
    $abonament_client_BU, 
    $abonament_client_sales, 
    $abonament_client_anexa,
    $abonament_client_pdf, 
    $lunifacturare, 
    $abonament_client_contract
);

if (!mysqli_stmt_execute($stmt)) {
    die('Error: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt);
echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-2);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
else
{// edit
// Handle lunafacturare array
if (isset($_POST['abonament_client_lunafacturare']) && is_array($_POST['abonament_client_lunafacturare'])) {
    $lunifacturare = implode(';', $_POST['abonament_client_lunafacturare']);
} else {
    $lunifacturare = '';
}

$abonament_client_id = isset($_POST["abonament_client_ID"]) && is_numeric($_POST["abonament_client_ID"]) ? (int)$_POST["abonament_client_ID"] : 0;

// Sanitize all POST variables
$abonament_client_valoare = mysqli_real_escape_string($conn, $_POST["abonament_client_valoare"] ?? '');
$abonament_client_valuta = mysqli_real_escape_string($conn, $_POST["abonament_client_valuta"] ?? '');
$abonament_client_frecventa = mysqli_real_escape_string($conn, $_POST["abonament_client_frecventa"] ?? '');
$abonament_client_aloc = mysqli_real_escape_string($conn, $_POST["abonament_client_aloc"] ?? '');
$abonament_client_detalii = mysqli_real_escape_string($conn, $_POST["abonament_client_detalii"] ?? '');
$abonament_client_activ = mysqli_real_escape_string($conn, $_POST["abonament_client_activ"] ?? '');
$abonament_client_email = mysqli_real_escape_string($conn, $_POST["abonament_client_email"] ?? '');
$abonament_client_unitate = mysqli_real_escape_string($conn, $_POST["abonament_client_unitate"] ?? '');
$abonament_client_termen = mysqli_real_escape_string($conn, $_POST["abonament_client_termen"] ?? '');
$abonament_client_zifacturare = mysqli_real_escape_string($conn, $_POST["abonament_client_zifacturare"] ?? '');
$abonament_client_BU = mysqli_real_escape_string($conn, $_POST["abonament_client_BU"] ?? '');
$abonament_client_sales = mysqli_real_escape_string($conn, $_POST["abonament_client_sales"] ?? '');
$abonament_client_anexa = mysqli_real_escape_string($conn, $_POST["abonament_client_anexa"] ?? '');
$abonament_client_pdf = mysqli_real_escape_string($conn, $_POST["abonament_client_pdf"] ?? '');
$abonament_client_an = mysqli_real_escape_string($conn, $_POST["abonament_client_an"] ?? '');
$abonament_client_contract = mysqli_real_escape_string($conn, $_POST["abonament_client_contract"] ?? '');

$stmt_update = mysqli_prepare($conn, 
    "UPDATE clienti_abonamente SET abonament_client_ID=?, abonament_client_valoare=?, abonament_client_valuta=?, 
    abonament_client_frecventa=?, abonament_client_aloc=?, abonament_client_detalii=?, abonament_client_activ=?, 
    abonament_client_email=?, abonament_client_unitate=?, abonament_client_termen=?, abonament_client_zifacturare=?, 
    abonament_client_lunafacturare=?, abonament_client_BU=?, abonament_client_sales=?, abonament_client_anexa=?, 
    abonament_client_pdf=?, abonament_client_an=?, abonament_client_contract=? 
    WHERE abonament_ID=?"
);

mysqli_stmt_bind_param($stmt_update, 'isssssssssssssssssi', 
    $abonament_client_id, 
    $abonament_client_valoare, 
    $abonament_client_valuta, 
    $abonament_client_frecventa, 
    $abonament_client_aloc, 
    $abonament_client_detalii, 
    $abonament_client_activ, 
    $abonament_client_email, 
    $abonament_client_unitate, 
    $abonament_client_termen, 
    $abonament_client_zifacturare, 
    $lunifacturare,
    $abonament_client_BU, 
    $abonament_client_sales, 
    $abonament_client_anexa,
    $abonament_client_pdf, 
    $abonament_client_an, 
    $abonament_client_contract,
    $cID
);

if (!mysqli_stmt_execute($stmt_update)) {
    die('Error: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt_update);

echo "<div class=\"callout success\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-2);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
}
?>
        <?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="sitesubscribtions.php" class="button"><?php echo $strBack?> <i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post" id="users" Action="sitesubscribtions.php?mode=new">
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strClient?>
                        <select name="abonament_client_ID" class="required">
                            <option value=""><?php echo $strClient?></option>
                            <?php $sql = "Select Client_Denumire, clienti_date.ID_Client, Contract_Data, Contract_Numar, Contract_Obiect FROM clienti_date, clienti_contracte WHERE clienti_date.ID_Client=clienti_contracte.ID_Client ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                            <option value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?> -
                                <?php echo $rss["Contract_Obiect"]?> -
                                <?php echo $rss["Contract_Numar"]?>/<?php echo $rss["Contract_Data"]?> </option>
                            <?php
}?>
                        </select></label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strSeenBy?>
                        <select name="abonament_client_aloc" class="required">
                            <option value=""><?php echo $strSeenBy?></option>
                            <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                            <option value="<?php echo $rss["utilizator_Code"]?>">
                                <?php echo $rss["utilizator_Nume"]?>&nbsp;<?php echo $rss["utilizator_Prenume"]?>
                            </option>
                            <?php
}?>
                        </select></label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strSales?>
                        <select name="abonament_client_sales" class="required">
                            <option value=""><?php echo $strSeenBy?></option>
                            <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                            <option value="<?php echo $rss["utilizator_Code"]?>">
                                <?php echo $rss["utilizator_Nume"]?>&nbsp;<?php echo $rss["utilizator_Prenume"]?>
                            </option>
                            <?php
}?>
                        </select></label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <?php echo $strBusinessUnit?>
                    <select name="abonament_client_BU">
                        <?php
			 			$query7="SELECT * FROM clienti_activitati_contracte ORDER By activitate_contracte_denumire ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			echo"<option value=\"$seenby[activitate_contracte_cod]\">". $seenby['activitate_contracte_denumire']."</option>";
			}
		?></select></label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strContractType?>
                        <input name="abonament_client_frecventa" type="radio"
                            value="1"><?php echo $strMonthly?>&nbsp;&nbsp;
                        <input name="abonament_client_frecventa" type="radio"
                            value="2"><?php echo $strQuaterly?>&nbsp;&nbsp;
                        <input name="abonament_client_frecventa" type="radio"
                            value="0"><?php echo $strSemestrial?>&nbsp;&nbsp;
                        <input name="abonament_client_frecventa" type="radio" value="3"><?php echo $strYearly?>
                    </label>
                </div>

                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strActive?>
                        <input name="abonament_client_activ" type="radio"
                            value="0"><?php echo $strYes?>&nbsp;&nbsp;<input name="abonament_client_active" type="radio"
                            value="1"><?php echo $strNo?>
                    </label>
                </div>

                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strObject?>
                        <textarea name="abonament_client_detalii"></textarea>
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strUnit?>
                        <input name="abonament_client_unitate" type="text" id="numar" class="required" value="" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strContract?></label>
                    <input name="abonament_client_contract" type="text" id="numar" class="required" value="" />
                </div>
                <div class="large-3 medium-3 small-3 cell">

                    <label><?php echo $strDeadline?></label>
                    <input name="abonament_client_termen" type="text" id="numar" class="required" value="10" />
                </div>

                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strDay?></label>
                    <input name="abonament_client_zifacturare" type="text" id="numar" class="required" value="1" />
                </div>
                <div class="large-3 medium-3 small-3 cell">

                    <label><?php echo $strEmail?></label>
                    <input name="abonament_client_email" type="text" id="numar" class="required" />
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">

                    <label><?php echo $strYear?></label>
                    <input name="abonament_client_an" type="text" id="numar" class="required" />
                </div>
                <div class="large-3 medium-3 small-3 cell">

                    <label><?php echo $strValue?>
                        <input name="abonament_client_valoare" type="text" id="numar" class="required" value="" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strCurrency?>
                        <input name="abonament_client_valuta" type="radio" value="0" checked />
                        <?php echo $strLei?>&nbsp;&nbsp;<input name="abonament_client_valuta" type="radio"
                            value="1"><?php echo $strEuro?>
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strInvoiceMonth?>
                        <select name="abonament_client_lunafacturare[]" multiple size=5>
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
    		echo "<option value=\"$m\">$monthname</option>";
				} 
			?>
                        </select></label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strAnnex?>
                        <input name="abonament_client_anexa" type="radio" value="0"
                            checked><?php echo $strNo?>&nbsp;&nbsp;<input name="abonament_client_anexa" type="radio"
                            value="1"><?php echo $strYes?>
                    </label>
                </div>
                <div class="large-9 medium-9 small-9 cell">
                    <label><?php echo $strFile?>
                        <input name="abonament_client_pdf" type="text" value="" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"><input type="submit"
                        Value="<?php echo $strAdd?>" name="Submit" class="button"></div>
            </div>
        </form>
        <?php
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT clienti_date.ID_Client, clienti_date.Client_CIF, clienti_date.Client_Denumire, clienti_date.Client_Aloc, 
clienti_abonamente.abonament_client_ID, clienti_abonamente.abonament_client_aloc, clienti_abonamente.abonament_client_contract, clienti_abonamente.abonament_client_sales, clienti_abonamente.abonament_client_termen, clienti_abonamente.abonament_client_activ, clienti_abonamente.abonament_client_email, 
clienti_abonamente.abonament_client_frecventa, clienti_abonamente.abonament_client_detalii, clienti_abonamente.abonament_client_unitate, clienti_abonamente.abonament_client_an, clienti_abonamente.abonament_client_zifacturare,
clienti_abonamente.abonament_client_valoare, clienti_abonamente.abonament_client_valuta, clienti_abonamente.abonament_client_anexa, clienti_abonamente.abonament_client_pdf, clienti_abonamente.abonament_client_lunafacturare, clienti_abonamente.abonament_client_BU, utilizator_Nume, utilizator_Prenume
FROM clienti_abonamente, clienti_date, date_utilizatori
WHERE abonament_ID=$_GET[cID] AND clienti_date.ID_Client=clienti_abonamente.abonament_client_ID AND date_utilizatori.utilizator_Code=clienti_abonamente.abonament_client_aloc";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);

// Inițializare variabile pentru formular
$datecontract = $row["abonament_client_contract"] ?? '';
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="sitesubscribtions.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
                <script src="<?php echo $strSiteURL ?>/js/foundation/jquery.js"></script>
        <script language="JavaScript" type="text/JavaScript">
           
        $(document).ready(function() {
            $("#btn1").click(function() {
                $("#loaderIcon").show();
                jQuery.ajax({
                    url: "../common/cui.php",
                    dataType: "json",
                    data: 'Cui=' + $("#Cui").val(),
                    type: "POST",
                    success: function(data) {
                        try {
                            $('#denumire').val((data["denumire"] || "").toUpperCase());
                            $("#cif").val(data["cif"]);
                            $("#tva").val(data["tva"]);
                            $("#adresa").val(data["adresa"]);
                            $("#judet").val((data["judet"]).toUpperCase());
                            $("#oras").val((data["oras"]).toUpperCase());
                            $("#numar_reg_com").val(data["numar_reg_com"]);
                            $("#datecontract").val(data["datecontract"]);
                            $("#codpostal").val(data["codpostal"]);
                            $("#loaderIcon").hide();
                        } catch (err) {
                            document.getElementById("response").innerHTML = err.message;
                        }
                    },
                    error: function() {
                        alert('Nu se poate face legătura la serverul ANAF!');
                    }
                });
            });
        });
        </script>
 <div class="grid-x grid-margin-x">
            <div class="large-6 medium-6 small-6 cell">
                <div id="response"></div>
                <div class="input-group">
                    <span class="input-group-label"><?php echo $strCompanyVAT?></span>
                    <input class="input-group-field" type="text" name="Cui" id="Cui"
                        value="<?php echo $row["Client_CIF"]?>">
                    <div class="input-group-button">
                        <button id="btn1" class="button"><i
                                class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
                    </div>
                </div>
                <div id="suggesstion-box"></div>
            </div>
        </div><div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> 
	  <label><?php echo $strDetails?>
	  <textarea name="datecontract" id="datecontract" class="simple-editor-html" rows="5"><?php echo $datecontract ?></textarea>
</label>	</div>	
	</div>	

        <form method="post" id="users" Action="sitesubscribtions.php?mode=edit&cID=<?php echo htmlspecialchars($cID, ENT_QUOTES, 'UTF-8')?>">
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strTitle?>
                        <select name="abonament_client_ID" class="required">
                            <?php $sql = "Select * FROM clienti_date ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                            <option <?php if ($row["ID_Client"]==$rss["ID_Client"]) echo "selected"; ?>
                                value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
                            <?php
}?>
                        </select></label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strSeenBy?>
                        <select name="abonament_client_aloc" class="required">
                            <option value=""><?php echo $strSeenBy?></option>
                            <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                            <option value="<?php echo $rss["utilizator_Code"]?>"
                                <?php if($rss["utilizator_Code"]==$row["abonament_client_aloc"]) {echo "selected";}?>>
                                <?php echo $rss["utilizator_Nume"]?>&nbsp;<?php echo $rss["utilizator_Prenume"]?>
                            </option>
                            <?php
}?>
                        </select></label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strSales?>
                        <select name="abonament_client_sales" class="required">
                            <option value=""><?php echo $strSeenBy?></option>
                            <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                            <option value="<?php echo $rss["utilizator_Code"]?>"
                                <?php if($rss["utilizator_Code"]==$row["abonament_client_sales"]) {echo "selected";}?>>
                                <?php echo $rss["utilizator_Nume"]?>&nbsp;<?php echo $rss["utilizator_Prenume"]?>
                            </option>
                            <?php
}?>
                        </select></label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <?php echo $strBusinessUnit?>
                    <select name="abonament_client_BU">
                        <?php
			 			$query7="SELECT * FROM clienti_activitati_contracte ORDER By activitate_contracte_denumire ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
				if($row["abonament_client_BU"]==$seenby["activitate_contracte_cod"])
				{			echo"<option selected value=\"$seenby[activitate_contracte_cod]\">". $seenby['activitate_contracte_denumire']."</option>";}
			else
				{			echo"<option value=\"$seenby[activitate_contracte_cod]\">". $seenby['activitate_contracte_denumire']."</option>";}
			}
		?></select></label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strContractType?>
                        <input name="abonament_client_frecventa" type="radio" value="1"
                            <?php If ($row["abonament_client_frecventa"]==1) echo "checked"?>><?php echo $strMonthly?>&nbsp;&nbsp;
                        <input name="abonament_client_frecventa" type="radio" value="2"
                            <?php If ($row["abonament_client_frecventa"]==2) echo "checked"?>><?php echo $strQuaterly?>
                        <input name="abonament_client_frecventa" type="radio" value="0"
                            <?php If ($row["abonament_client_frecventa"]==0) echo "checked"?>><?php echo $strSemestrial?>
                        <input name="abonament_client_frecventa" type="radio" value="3"
                            <?php If ($row["abonament_client_frecventa"]==3) echo "checked"?>><?php echo $strYearly?>
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strActive?>

                        <input name="abonament_client_activ" type="radio" value="0"
                            <?php If ($row["abonament_client_activ"]==0) echo "checked"?>><?php echo $strYes?>
                        &nbsp;&nbsp;
                        <input name="abonament_client_activ" type="radio" value="1"
                            <?php If ($row["abonament_client_activ"]==1) echo "checked"?>><?php echo $strNo?>
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strObject?>
                        <textarea
                            name="abonament_client_detalii"><?php echo $row["abonament_client_detalii"]?></textarea></label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strUnit?>
                        <input name="abonament_client_unitate" type="text" id="numar" class="required"
                            value="<?php echo $row["abonament_client_unitate"]?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strContract?>
                        <input name="abonament_client_contract" type="text" id="numar" class="required"
                            value="<?php echo $row["abonament_client_contract"]?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strValue?>
                        <input name="abonament_client_valoare" type="text" id="numar" class="required"
                            value="<?php echo $row["abonament_client_valoare"]?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strEmail?>
                        <input name="abonament_client_email" type="text" id="numar" class="required"
                            value="<?php echo $row["abonament_client_email"]?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strYear?>
                        <input name="abonament_client_an" type="text" id="numar" class="required"
                            value="<?php echo $row["abonament_client_an"]?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strDeadline?>
                        <input name="abonament_client_termen" type="text" id="numar" class="required"
                            value="<?php echo $row["abonament_client_termen"]?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strDay?>
                        <input name="abonament_client_zifacturare" type="text" id="numar" class="required"
                            value="<?php echo $row["abonament_client_zifacturare"]?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strCurrency?>
                        <input name="abonament_client_valuta" type="radio" value="0"
                            <?php If ($row["abonament_client_valuta"]==0) echo "checked"?> />
                        <?php echo $strLei?>&nbsp;&nbsp;<input name="abonament_client_valuta" type="radio" value="1"
                            <?php If ($row["abonament_client_valuta"]==1) echo "checked"?>><?php echo $strEuro?>
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strInvoiceMonth?>
                        <select name="abonament_client_lunafacturare[]" multiple size=5>
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
$lunidefacturare=explode(";",$row["abonament_client_lunafacturare"]);
if(in_array($m,$lunidefacturare)) $str_flag = "selected";
else $str_flag="";
    		echo "<option value=\"$m\" $str_flag>$monthname</option>";
				} 
			?>
                        </select>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strAnnex?>
                        <input name="abonament_client_anexa" type="radio" value="0"
                            <?php If ($row["abonament_client_anexa"]==0) echo "checked"?>><?php echo $strNo?>&nbsp;&nbsp;<input
                            name="abonament_client_anexa" type="radio" value="1"
                            <?php If ($row["abonament_client_anexa"]==1) echo "checked"?>><?php echo $strYes?>
                    </label>
                </div>
                <div class="large-9 medium-9 small-9 cell">
                    <label><?php echo $strFile?>
                        <input name="abonament_client_pdf" type="text"
                            value="<?php echo $row["abonament_client_pdf"]?>" />
                    </label>
                </div>
            </div>

            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"><input type="submit"
                        Value="<?php echo $strModify?>" name="Submit" class="button"></div>
            </div>
        </form>
        <?php
}
else
{
echo "<a href=\"sitesubscribtions.php?mode=new\" class=\"button\">$strAddNew <i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a><br />";

$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_Aloc, 
clienti_abonamente.abonament_client_ID, clienti_abonamente.abonament_ID, clienti_abonamente.abonament_client_aloc, clienti_abonamente.abonament_client_contract, 
clienti_abonamente.abonament_client_frecventa, clienti_abonamente.abonament_client_detalii, clienti_abonamente.abonament_client_unitate, 
clienti_abonamente.abonament_client_valoare, clienti_abonamente.abonament_client_valuta, utilizator_Nume, utilizator_Prenume
FROM clienti_abonamente, clienti_date, date_utilizatori
WHERE clienti_date.ID_Client=clienti_abonamente.abonament_client_ID AND date_utilizatori.utilizator_Code=clienti_abonamente.abonament_client_aloc AND clienti_abonamente.abonament_client_activ='0'";

if ((isset( $_GET['start'])) && !empty( $_GET['start'])){
$start=$_GET['start'];}
else{
$start=0;}
if ($start!='0'){
$query= $query . " AND Client_Denumire LIKE'$start%'";
};

$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY Client_Denumire ASC $pages->limit";
$result=ezpub_query($conn,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
?>
        <div class="paginate">
            <?php
echo $strTotal . " " .$numar." ".$strSubscribtions ;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"sitesubscribtions.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
echo " <br /><br />";
$sql="SELECT DISTINCT LEFT(clienti_date.Client_Denumire, 1) as letter 
FROM clienti_date, clienti_abonamente 
WHERE clienti_abonamente.abonament_client_ID=clienti_date.ID_Client Group By letter ORDER BY letter ASC;";
$result2=ezpub_query($conn,$sql);
While ($row1=ezpub_fetch_array($result2)){
	$char=$row1["letter"];
    echo "<a href=\"sitesubscribtions.php?start=$char\">$char</a>&nbsp;";
}
?>
        </div>
        <table width="100%">
            <thead>
                <tr>
                    <th><?php echo $strClient?></th>
                    <th><?php echo $strObject?></th>
                    <th><?php echo $strFrequency?></th>
                    <th><?php echo $strValue?></th>
                    <th><?php echo $strSeenBy?></th>
                    <th><?php echo $strEdit?></th>
                    <th><?php echo $strDelete?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=ezpub_fetch_array($result)){
	If ($row["abonament_client_frecventa"]==1){$frecventa=$strMonthly;}
	elseIf ($row["abonament_client_frecventa"]==2){$frecventa=$strQuaterly;}
	elseIf ($row["abonament_client_frecventa"]==0){$frecventa=$strSemestrial;}
	elseif ($row["abonament_client_frecventa"]==3){$frecventa=$strYearly;}
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[abonament_client_detalii]</td>
			<td>$frecventa</td>
			<td align=\"right\">".romanize($row["abonament_client_valoare"])."</td>
			<td>$row[utilizator_Prenume]&nbsp;$row[utilizator_Nume] </td>
			  <td><a href=\"sitesubscribtions.php?mode=edit&cID=$row[abonament_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"sitesubscribtions.php?mode=delete&cID=$row[abonament_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"5\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
?>
    </div>
</div>
<hr />
<?php
include '../bottom.php';
?>