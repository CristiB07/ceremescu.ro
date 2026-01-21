<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
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
    header("location:$strSiteURL/clients/siteclients.php");
    die;
}

if (isset($_GET['cID'])) {
    if (!is_numeric($_GET['cID'])) {
        header("location:$strSiteURL/clients/siteclients.php");
        die;
    }
    $cID = (int)$_GET['cID'];
}

$strPageTitle="Administrare clienți";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <link rel="stylesheet" href="../js/simple-editor/simple-editor.css">
        <script src="../js/simple-editor/simple-editor.js"></script>
        <?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, "DELETE FROM clienti_date WHERE ID_Client=?");
mysqli_stmt_bind_param($stmt, 'i', $cID);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteclients.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
include_once '../anaf/balancesgetlib.php';



check_inject();
If ($_GET['mode']=="new"){

// Validare și sanitizare input
$client_denumire = trim($_POST["Client_Denumire"] ?? '');
$client_adresa = trim($_POST["Client_Adresa"] ?? '');
$client_telefon = trim($_POST["Client_Telefon"] ?? '');
$client_cif = trim($_POST["Client_CIF"] ?? '');
$client_ro = trim($_POST["Client_RO"] ?? '');
$clientcui = $client_ro . " " . $client_cif;
$client_rc = trim($_POST["Client_RC"] ?? '');
$client_banca = trim($_POST["Client_Banca"] ?? '');
$client_iban = trim($_POST["Client_IBAN"] ?? '');
$client_localitate = trim($_POST["Client_Localitate"] ?? '');
$client_judet = trim($_POST["Client_Judet"] ?? '');
$client_cod_caen = trim($_POST["Client_Cod_CAEN"] ?? '');
$client_numar_angajati = trim($_POST["Client_Numar_Angajati"] ?? '');
$client_descriere = trim($_POST["Client_Descriere_Activitate"] ?? '');
$client_web = trim($_POST["Client_Web"] ?? '');
$client_email = trim($_POST["Client_Email"] ?? '');
$client_codpostal = trim($_POST["Client_Codpostal"] ?? '');
$client_tip = trim($_POST["Client_Tip"] ?? '');
$client_caracterizare = trim($_POST["Client_Caracterizare"] ?? '');

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, 
    "INSERT INTO clienti_date(Client_Denumire, Client_Adresa, Client_Telefon, Client_CUI, Client_RC, 
    Client_Banca, Client_IBAN, Client_Localitate, Client_Judet, Client_Cod_CAEN, Client_Numar_Angajati, 
    Client_Descriere_Activitate, Client_Web, Client_Email, Client_CIF, Client_RO, Client_Codpostal, 
    Client_Tip, Client_Caracterizare) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param($stmt, 'sssssssssssssssssss', 
    $client_denumire, $client_adresa, $client_telefon, $clientcui, $client_rc,
    $client_banca, $client_iban, $client_localitate, $client_judet, $client_cod_caen,
    $client_numar_angajati, $client_descriere, $client_web, $client_email,
    $client_cif, $client_ro, $client_codpostal, $client_tip, $client_caracterizare
);

if (!mysqli_stmt_execute($stmt)) {
    die('Error: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt);

// Importă bilanțuri ANAF dacă CUI numeric valid
$import_bilanturi_msg = '';
$cui_numeric = preg_replace('/\D/', '', $client_cif);
if ($cui_numeric && is_numeric($cui_numeric) && (int)$cui_numeric > 0) {
    $imported = import_bilanturi_anaf((int)$cui_numeric, $conn);
    if ($imported > 0) {
        $import_bilanturi_msg = "<div class='callout success'>Importate $imported bilanțuri ANAF!</div>";
    } else {
        $import_bilanturi_msg = "<div class='callout info'>Bilanțurile ANAF erau deja importate sau nu există!</div>";
    }
}
// Importă date fiscale ANAF dacă CUI numeric valid
if ($cui_numeric && is_numeric($cui_numeric) && (int)$cui_numeric > 0) {
    // Import fiscal data by including the script directly (no output)
    $_GET['cui'] = $cui_numeric;
    ob_start();
    include_once '../anaf/getfiscaldata.php';
    ob_end_clean();
    unset($_GET['cui']);
}

echo "<div class=\"callout success\">$strRecordAdded</div>".$import_bilanturi_msg."</div></div>";
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
// Validare și sanitizare input
$client_denumire = trim($_POST["Client_Denumire"] ?? '');
$client_adresa = trim($_POST["Client_Adresa"] ?? '');
$client_telefon = trim($_POST["Client_Telefon"] ?? '');
$client_cif = trim($_POST["Client_CIF"] ?? '');
$client_ro = trim($_POST["Client_RO"] ?? '');
$clientcui = $client_ro . " " . $client_cif;
$client_rc = trim($_POST["Client_RC"] ?? '');
$client_banca = trim($_POST["Client_Banca"] ?? '');
$client_iban = trim($_POST["Client_IBAN"] ?? '');
$client_localitate = trim($_POST["Client_Localitate"] ?? '');
$client_judet = trim($_POST["Client_Judet"] ?? '');
$client_cod_caen = trim($_POST["Client_Cod_CAEN"] ?? '');
$client_numar_angajati = trim($_POST["Client_Numar_Angajati"] ?? '');
$client_descriere = trim($_POST["Client_Descriere_Activitate"] ?? '');
$client_web = trim($_POST["Client_Web"] ?? '');
$client_email = trim($_POST["Client_Email"] ?? '');
$client_codpostal = trim($_POST["Client_Codpostal"] ?? '');
$client_tip = trim($_POST["Client_Tip"] ?? '');
$client_caracterizare = trim($_POST["Client_Caracterizare"] ?? '');

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, 
    "UPDATE clienti_date SET 
    Client_Denumire=?, Client_Adresa=?, Client_Telefon=?, Client_CUI=?, Client_RC=?,
    Client_Banca=?, Client_IBAN=?, Client_Localitate=?, Client_Judet=?, Client_Cod_CAEN=?,
    Client_Tip=?, Client_RO=?, Client_CIF=?, Client_Email=?, Client_Codpostal=?,
    Client_Numar_Angajati=?, Client_Descriere_Activitate=?, Client_Web=?, Client_Caracterizare=?
    WHERE ID_Client=?"
);

mysqli_stmt_bind_param($stmt, 'sssssssssssssssssssi', 
    $client_denumire, $client_adresa, $client_telefon, $clientcui, $client_rc,
    $client_banca, $client_iban, $client_localitate, $client_judet, $client_cod_caen,
    $client_tip, $client_ro, $client_cif, $client_email, $client_codpostal,
    $client_numar_angajati, $client_descriere, $client_web, $client_caracterizare,
    $cID
);

if (!mysqli_stmt_execute($stmt)) {
    die('Error: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt);

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
else {

If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
        <script src="<?php echo $strSiteURL ?>/js/foundation/jquery.js"></script>
        <script language="JavaScript" type="text/JavaScript">
            $(document).ready(function(){
	$("#Cui").keyup(function(){
		$.ajax({
		type: "POST",
		url: "../common/check_client.php",
		data:'keyword='+$(this).val(),
		beforeSend: function(){
			$("#Cui").css("background","#FFF url(../img/LoaderIcon.gif) no-seeneat 165px");
		},
		success: function(data){
			$("#suggesstion-box").show();
			$("#suggesstion-box").html(data);
			$("#search-box").css("background","#FFF");
		}
		});
	});
});

function selectCountry(val) {
	split_str=val.split(" - ");
$("#Cui").val(split_str[0]);
$("#judet").val(split_str[1]);
$("#suggesstion-box").hide();
}
</script>

        <script>
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
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="siteclients.php" class="button"><?php echo $strBack?> <i class="fas fa-backward"></i></a>
                </p>
            </div>
        </div>
        <div class="grid-x grid-margin-x">
            <div class="large-6 medium-6 small-6 cell">
                <div id="response"></div>
                <div class="input-group">
                    <span class="input-group-label"><?php echo $strCompanyVAT?></span>
                    <input class="input-group-field" type="text" name="Cui" id="Cui"
                        placeholder="<?php echo $strEnterVATNumber?>">
                    <div class="input-group-button">
                        <button id="btn1" class="button"><i
                                class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
                    </div>
                </div>
                <div id="suggesstion-box"></div>
            </div>
        </div>
        <form method="post" Action="siteclients.php?mode=new">
            <div class="grid-x grid-margin-x">
                <div class="large-8 medium-8 small-8 cell">
                    <label><?php echo $strTitle?>
                        <input name="Client_Denumire" type="text" id="denumire" value="" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strBranch?>
                        <input name="Client_Tip" type="radio" value="0" /> <?php echo $strYes?>&nbsp;&nbsp;<input
                            name="Client_Tip" type="radio" value="1" checked><?php echo $strNo?>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strCompanyFA?>
                        <input name="Client_RO" type="text" id="tva" value="" size="3" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strCompanyVAT?>
                        <input name="Client_CIF" type="text" id="cif" value="" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strCompanyRC?>
                        <input name="Client_RC" id="numar_reg_com" type="text" value="" />
                    </label>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strAddress?>
                        <textarea name="Client_Adresa" id="adresa" style="width:100%;"></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strCity?>
                        <input name="Client_Localitate" id="oras" type="text" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strCounty?>
                        <input name="Client_Judet" id="judet" type="text" value="" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strCode?>
                        <input name="Client_Codpostal" id="codpostal" type="text" value="" />
                    </label>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strCompany?>
                        <textarea name="datecontract" id="datecontract" style="width:100%;"></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strCompanyBank?>
                        <input name="Client_Banca" type="text" /></label>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strCompanyIBAN?>
                        <input name="Client_IBAN" type="text" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strPhone?>
                        <input name="Client_Telefon" type="text" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strEmail?>
                        <input name="Client_Email" type="text" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strWWW?>
                        <input name="Client_Web" type="text" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strEmployees?>
                        <input name="Client_Numar_Angajati" type="text" value="0" />
                    </label>
                </div>
                <div class="large-8 medium-8 small-8 cell">
                    <label><?php echo $strCAENCode?>
                        <input name="Client_Cod_CAEN" type="text" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strActivities?>
                        <textarea name="Client_Descriere_Activitate" id="simple-editor-html" class="simple-editor-html"
                            rows="5"></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strProfile?>
                        <textarea name="Client_Caracterizare" id="simple-editor-html" class="simple-editor-html"
                            rows="5"></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"> <input type="submit"
                        Value="<?php echo $strAdd?>" name="Submit" class="button" /></div>
            </div>
        </form>
        <?php
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, "SELECT * FROM clienti_date WHERE ID_Client=?");
mysqli_stmt_bind_param($stmt, 'i', $cID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>
        <script src="<?php echo $strSiteURL ?>/js/foundation/jquery.js"></script>
        <script>
        $(document).ready(function() {
            $("#btn11").click(function() {
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
                        alert($("#Cui").val());
                        document.getElementById("Cui").innerHTML = data;
                    }
                });
            });
        });
        </script>

        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="siteclients.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <div class="grid-x grid-margin-x">
            <div class="large-6 medium-6 small-6 cell">
                <div id="response"></div>
                <div class="input-group">
                    <span class="input-group-label"><?php echo $strCompanyVAT?></span>
                    <input class="input-group-field" type="text" name="Cui" id="Cui"
                        value="<?php echo $row['Client_CIF'] ?>">
                    <div class="input-group-button">
                        <button id="btn11" class="button"><i
                                class="fas fa-sync-alt"></i>&nbsp;<?php echo $strUpdate ?></button>
                    </div>
                </div>
            </div>
        </div>
        <form method="post" Action="siteclients.php?mode=edit&cID=<?php echo $row['ID_Client']?>">
            <div class="grid-x grid-margin-x">
                <div class="large-8 medium-8 small-8 cell">
                    <label><?php echo $strTitle?>
                        <input name="Client_Denumire" id="denumire" type="text"
                            value="<?php echo $row['Client_Denumire'] ?>" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strBranch?>
                        <input name="Client_Tip" type="radio" value="0"
                            <?php If ($row["Client_Tip"]==0) echo "checked"?> /> <?php echo $strYes?>&nbsp;&nbsp;<input
                            name="Client_Tip" type="radio" value="1"
                            <?php If ($row["Client_Tip"]==1) echo "checked"?>><?php echo $strNo?>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strCompanyFA?>
                        <input name="Client_RO" type="text" id="tva" value="<?php echo $row['Client_RO'] ?>" size="3" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strCompanyVAT?>
                        <input name="Client_CIF" type="text" id="ro" value="<?php echo $row['Client_CIF'] ?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strCompanyRC?>
                        <input name="Client_RC" type="text" id="numar_reg_com"
                            value="<?php echo $row['Client_RC'] ?>" />
                    </label>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strAddress?>
                        <textarea name="Client_Adresa" id="adresa"><?php echo $row['Client_Adresa'] ?></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCity?>
                        <input name="Client_Localitate" id="oras" type="text"
                            value="<?php echo $row['Client_Localitate'] ?>" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCounty?>
                        <input name="Client_Judet" type="text" id="judet" value="<?php echo $row['Client_Judet'] ?>" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCode?>
                        <input name="Client_Codpostal" type="text" id="codpostal"
                            value="<?php echo $row['Client_Codpostal'] ?>" />
                    </label>
                </div>
            </div>
              <div class="grid-x grid-margin-x">
             <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strCompany?>
                        <textarea name="datecontract" id="datecontract" style="width:100%;"></textarea>
                    </label>
                </div>
                </div>
            <div class="grid-x grid-margin-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strCompanyBank?>
                        <input name="Client_Banca" type="text" value="<?php echo $row['Client_Banca'] ?>" />
                    </label>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strCompanyIBAN?>
                        <input name="Client_IBAN" type="text" value="<?php echo $row['Client_IBAN'] ?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strPhone?>
                        <input name="Client_Telefon" type="text" value="<?php echo $row['Client_Telefon'] ?>" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strEmail?>
                        <input name="Client_Email" type="text" value="<?php echo $row['Client_Email'] ?>" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strWWW?>
                        <input name="Client_Web" type="text" value="<?php echo $row['Client_Web'] ?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strEmployees?>
                        <?php
	  if (!$row['Client_Numar_Angajati'])
	  {$nrangajati=1;}
  else
  {$nrangajati=$row['Client_Numar_Angajati'];}
	  ?>
                        <input name="Client_Numar_Angajati" type="text" value="<?php echo $nrangajati ?>" />
                    </label>
                </div>
                <div class="large-8 medium-8 small-8 cell">
                    <label><?php echo $strCAENCode?>
                        <input name="Client_Cod_CAEN" type="text" value="<?php echo $row['Client_Cod_CAEN'] ?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strActivities?>
                        <textarea name="Client_Descriere_Activitate" id="simple-editor-html" class="simple-editor-html"
                            rows="5"><?php echo $row['Client_Descriere_Activitate'] ?></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strProfile?>
                        <textarea name="Client_Caracterizare" id="simple-editor-html" class="simple-editor-html"
                            rows="5"><?php echo $row['Client_Caracterizare'] ?></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" Value="<?php echo $strModify?>" name="Submit" class="button" />
                    <?php If ($row["Client_Tip"]==1) { ?>
                    <a href="siteclientsbranch.php?cID=<?php echo htmlspecialchars($cID, ENT_QUOTES, 'UTF-8')?>"
                        class="button"><?php echo $strAddBranch?></a>
                    <?php }?>
                </div>
            </div>
        </form>
        <?php
}
else
{
	?>
        <script src="<?php echo $strSiteURL ?>/js/foundation/jquery.js"></script>
        <script language="JavaScript" type="text/JavaScript">
            $(document).ready(function(){
	$("#Cui").keyup(function(){
		$.ajax({
		type: "POST",
		url: "../common/check_client.php",
		data:'keyword='+$(this).val(),
		beforeSend: function(){
			$("#Cui").css("background","#FFF url(../img/LoaderIcon.gif) no-seeneat 165px");
		},
		success: function(data){
			$("#suggesstion-box").show();
			$("#suggesstion-box").html(data);
			$("#cui").css("background","#FFF");
		}
		});
	});
});

</script>
        <div class="grid-x grid-margin-x">
            <div class="large-6 medium-6 small-6 cell">
                <div id="response"></div>
                <div class="input-group">
                    <span class="input-group-label"><?php echo $strCompanyName?></span>
                    <input class="input-group-field" type="text" name="Cui" id="Cui"
                        placeholder="<?php echo $strEnterName?>">
                    <div class="input-group-button">
                        <button id="btn1" class="button"><i
                                class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
                    </div>
                </div>
                <div id="suggesstion-box"></div>
            </div>
        </div>
        <?php
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"siteclients.php?mode=new\" class=\"button\">$strAddNew <i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";
$query="SELECT * FROM clienti_date ";
if ((isset( $_GET['seen'])) && !empty( $_GET['seen'])){
$seen=$_GET['seen'];}
else{
$seen=0;}
if ($seen!='0'){
$query= $query . " WHERE Client_Aloc='$seen'";
}

if ((isset( $_GET['start'])) && !empty( $_GET['start'])){
$start=$_GET['start'];}
else{
$start=0;}
if ($start!='0'){
$query= $query . " WHERE Client_Denumire LIKE '" . mysqli_real_escape_string($conn, $start) . "%'";
};
$result=mysqli_query($conn, $query);
$numar=mysqli_num_rows($result);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY Client_Denumire ASC $pages->limit";
$result=mysqli_query($conn, $query);

echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
?>
        <div class="paginate">
            <?php
echo $strTotal . " " .$numar." ".$strClients ;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"siteclients.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
echo " <br /><br />";
$sql="SELECT DISTINCT LEFT(clienti_date.Client_Denumire, 1) as letter 
FROM clienti_date 
Group By letter ORDER BY letter ASC;";
$result2=mysqli_query($conn, $sql);
While ($row1=mysqli_fetch_array($result2, MYSQLI_ASSOC)){
    $char=htmlspecialchars($row1["letter"] ?? '', ENT_QUOTES, 'UTF-8');
    echo "<a href=\"siteclients.php?start=$char\">$char</a>&nbsp;";
}
echo " <br /><br />";
$sql="SELECT DISTINCT (clienti_date.Client_Aloc) as seenby 
FROM clienti_date 
ORDER BY seenby ASC;";
$result2=mysqli_query($conn, $sql);
While ($row1=mysqli_fetch_array($result2, MYSQLI_ASSOC)){
    $seen=htmlspecialchars($row1["seenby"] ?? '', ENT_QUOTES, 'UTF-8');
    echo "<a href=\"siteclients.php?seen=$seen\">$seen</a>&nbsp;";
}
?>
        </div>

        <table width="100%">
            <thead>
                <tr>
                    <th><?php echo $strTitle?></th>
                    <th><?php echo $strVAT?></th>
                    <th><?php echo $strCity?></th>
                    <th><?php echo $strCounty?></th>
                    <th><?php echo $strEdit?></th>
                    <th><?php echo $strDelete?></th>
                    <th><?php echo $strDetails?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=mysqli_fetch_array($result, MYSQLI_ASSOC)){
            echo"<tr>
            <td>" . htmlspecialchars($row['Client_Denumire'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
            <td>" . htmlspecialchars($row['Client_CUI'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
            <td>" . htmlspecialchars($row['Client_Localitate'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
            <td>" . htmlspecialchars($row['Client_Judet'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
			 <td><a href=\"siteclients.php?mode=edit&cID=" . (int)$row['ID_Client'] . "\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			 <td><a href=\"siteclients.php?mode=delete&cID=" . (int)$row['ID_Client'] . "\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
			 <td><a href=\"clientprofile.php?cID=" . (int)$row['ID_Client'] . "\"><i class=\"fa fa-search-plus fa-xl\" title=\"$strEdit\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"5\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
}
?>
    </div>
</div>
<hr />
<?php
include '../bottom.php';
?>