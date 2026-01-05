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
    header("location:$strSiteURL/clients/sitecontracts.php");
    die;
}

if (isset($_GET['cID'])) {
    if (!is_numeric($_GET['cID'])) {
        header("location:$strSiteURL/clients/sitecontracts.php");
        die;
    }
    $cID = (int)$_GET['cID'];
}

include '../classes/paginator.class.php';
$strPageTitle="Administrare contracte";
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
$stmt = mysqli_prepare($conn, "DELETE FROM clienti_contracte WHERE ID_Contract=?");
mysqli_stmt_bind_param($stmt, 'i', $cID);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
     window.location = \"sitecontracts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

If ($_GET['mode']=="new"){

// Validare și sanitizare input
$id_client = isset($_POST["ID_Client"]) && is_numeric($_POST["ID_Client"]) ? (int)$_POST["ID_Client"] : 0;
$contract_alocat = trim($_POST["Contract_Alocat"] ?? '');
$contract_tip = trim($_POST["Contract_Tip"] ?? '');
$contract_obiect = trim($_POST["Contract_Obiect"] ?? '');
$contract_numar = trim($_POST["Contract_Numar"] ?? '');
$contract_data = trim($_POST["Contract_Data"] ?? '');
$contract_activ = trim($_POST["Contract_Activ"] ?? '');
$contract_suma = trim($_POST["Contract_Suma"] ?? '');
$contract_termen = trim($_POST["Contract_Termen"] ?? '');
$contract_zifacturare = trim($_POST["Contract_Zifacturare"] ?? '');
$contract_responsabil = trim($_POST["Contract_Responsabil"] ?? '');
$contract_email = trim($_POST["Contract_Email_Facturare"] ?? '');
$contract_an = trim($_POST["Contract_An"] ?? '');
$contract_abonament = isset($_POST["Contract_abonament"]) && is_numeric($_POST["Contract_abonament"]) ? (int)$_POST["Contract_abonament"] : 0;
$contract_bu = trim($_POST["Contract_BU"] ?? '');
$contract_sales = trim($_POST["Contract_Sales"] ?? '');
$contract_valuta = trim($_POST["Contract_Valuta"] ?? '');

// File upload
$uploaddir = $hddpath ."/" . $contracts_folder;
$contractfiles= '';
$countfiles = count($_FILES['file']['name']);
for($i=0;$i<$countfiles;$i++){
    $filename = $_FILES['file']['name'][$i];
    move_uploaded_file($_FILES['file']['tmp_name'][$i],$uploaddir."/".$filename);
    $contractfiles=$contractfiles.";".$filename;
}

$lunifacturare= implode(';',$_POST['Contract_lunifacturare']);

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, 
    "INSERT INTO clienti_contracte(ID_Client, Contract_Alocat, Contract_Tip, Contract_Obiect, Contract_Numar, 
    Contract_Data, Contract_Activ, Contract_Suma, Contract_Termen, Contract_Zifacturare, Contract_lunifacturare, 
    Contract_Responsabil, Contract_Email_Facturare, Contract_An, Contract_File, Contract_abonament, Contract_BU, 
    Contract_Sales, Contract_Valuta) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param($stmt, 'issssssssssssssssss', 
    $id_client, $contract_alocat, $contract_tip, $contract_obiect, $contract_numar,
    $contract_data, $contract_activ, $contract_suma, $contract_termen, $contract_zifacturare,
    $lunifacturare, $contract_responsabil, $contract_email, $contract_an, $contractfiles,
    $contract_abonament, $contract_bu, $contract_sales, $contract_valuta
);
			
if (!mysqli_stmt_execute($stmt)) {
    die('Error: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt);

// INSERT abonamente dacă e cazul
If ($contract_abonament==1)
{
    $contract_numar_data = $contract_numar . "/" . $contract_data;
    $unitate = 'Luni';
    $activ_zero = 0;
    
    $stmt2 = mysqli_prepare($conn, 
        "INSERT INTO clienti_abonamente(abonament_client_ID, abonament_client_valoare, abonament_client_valuta, \n        abonament_client_frecventa, abonament_client_aloc, abonament_client_detalii, abonament_client_unitate, \n        abonament_client_termen, abonament_client_zifacturare, abonament_client_lunafacturare, abonament_client_activ, \n        abonament_client_email, abonament_client_an, abonament_client_BU, abonament_client_sales, abonament_client_contract) \n        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    
    mysqli_stmt_bind_param($stmt2, 'isssssssssssssss', 
        $id_client, $contract_suma, $contract_valuta, $contract_tip, $contract_alocat,
        $contract_obiect, $unitate, $contract_termen, $contract_zifacturare, $lunifacturare,
        $activ_zero, $contract_email, $contract_an, $contract_bu, $contract_sales, $contract_numar_data
    );
    
    if (!mysqli_stmt_execute($stmt2)) {
        die('Error: ' . mysqli_error($conn));
    }
    mysqli_stmt_close($stmt2);
    echo "<div class=\"callout success\">Abonament adăugat.</div>";
}

echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
     window.location = \"sitecontracts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
else
{// edit

// SELECT file existent cu prepared statement
$stmt_file = mysqli_prepare($conn, "SELECT Contract_File FROM clienti_contracte WHERE ID_Contract=?");
mysqli_stmt_bind_param($stmt_file, 'i', $cID);
mysqli_stmt_execute($stmt_file);
$result_file = mysqli_stmt_get_result($stmt_file);
$row = mysqli_fetch_array($result_file, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_file);

If (isSet($row['Contract_File']) AND !empty($row['Contract_File'])) {
	$contractfiles=$row['Contract_File'];
} else {
	$contractfiles='';
}

// File upload
$uploaddir = $hddpath ."/" . $contracts_folder;
$countfiles = count($_FILES['file']['name']);
for($i=0;$i<$countfiles;$i++){
    $filename = $_FILES['file']['name'][$i];
    move_uploaded_file($_FILES['file']['tmp_name'][$i],$uploaddir."/".$filename);
    $contractfiles=$contractfiles.";".$filename;
}

// Validare input pentru UPDATE
$id_client = isset($_POST["ID_Client"]) && is_numeric($_POST["ID_Client"]) ? (int)$_POST["ID_Client"] : 0;
$contract_alocat = trim($_POST["Contract_Alocat"] ?? '');
$contract_activ = trim($_POST["Contract_Activ"] ?? '');
$contract_obiect = trim($_POST["Contract_Obiect"] ?? '');
$contract_tip = trim($_POST["Contract_Tip"] ?? '');
$contract_numar = trim($_POST["Contract_Numar"] ?? '');
$contract_suma = trim($_POST["Contract_Suma"] ?? '');
$contract_termen = trim($_POST["Contract_Termen"] ?? '');
$contract_zifacturare = trim($_POST["Contract_Zifacturare"] ?? '');
$lunifacturare = implode(';',$_POST['Contract_lunifacturare']);
$contract_responsabil = trim($_POST["Contract_Responsabil"] ?? '');
$contract_email = trim($_POST["Contract_Email_Facturare"] ?? '');
$contract_abonament = isset($_POST["Contract_abonament"]) && is_numeric($_POST["Contract_abonament"]) ? (int)$_POST["Contract_abonament"] : 0;
$contract_valuta = trim($_POST["Contract_Valuta"] ?? '');
$contract_bu = trim($_POST["Contract_BU"] ?? '');
$contract_sales = trim($_POST["Contract_Sales"] ?? '');
$contract_an = trim($_POST["Contract_An"] ?? '');
$contract_data = trim($_POST["Contract_Data"] ?? '');

// Prepared statement pentru UPDATE contracte
$stmt_update = mysqli_prepare($conn, 
    "UPDATE clienti_contracte SET ID_Client=?, Contract_Alocat=?, Contract_Activ=?, Contract_Obiect=?, 
    Contract_Tip=?, Contract_Numar=?, Contract_Suma=?, Contract_Termen=?, Contract_Zifacturare=?, 
    Contract_lunifacturare=?, Contract_Responsabil=?, Contract_Email_Facturare=?, Contract_abonament=?, 
    Contract_Valuta=?, Contract_BU=?, Contract_Sales=?, Contract_An=?, Contract_File=?, Contract_Data=? 
    WHERE ID_Contract=?"
);

mysqli_stmt_bind_param($stmt_update, 'issssssssssssssssssi', 
    $id_client, $contract_alocat, $contract_activ, $contract_obiect, $contract_tip,
    $contract_numar, $contract_suma, $contract_termen, $contract_zifacturare, $lunifacturare,
    $contract_responsabil, $contract_email, $contract_abonament, $contract_valuta, $contract_bu,
    $contract_sales, $contract_an, $contractfiles, $contract_data, $cID
);

if (!mysqli_stmt_execute($stmt_update)) {
    die('Error: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt_update);

// UPDATE abonamente dacă este cazul (Contract_abonament==3)
if (!empty($_POST["abonament_ID"]) && $contract_abonament==3) {
    $position = strpos($_POST["abonament_ID"], ' - ');
    $abonamentID = substr($_POST["abonament_ID"], 0, $position);
    
    if (is_numeric($abonamentID)) {
        $abonamentID = (int)$abonamentID;
        
        $stmt_abon = mysqli_prepare($conn, 
            "UPDATE clienti_abonamente SET abonament_client_ID=?, abonament_client_aloc=?, 
            abonament_client_valoare=?, abonament_client_valuta=?, abonament_client_detalii=?, 
            abonament_client_email=?, abonament_client_zifacturare=?, abonament_client_an=?, 
            abonament_client_BU=?, abonament_client_sales=?, abonament_client_contract=?, 
            abonament_client_activ=?, abonament_client_lunafacturare=? 
            WHERE abonament_ID=?"
        );
        
        mysqli_stmt_bind_param($stmt_abon, 'issssssssssssi', 
            $id_client, $contract_alocat, $contract_suma, $contract_valuta, $contract_obiect,
            $contract_email, $contract_zifacturare, $contract_an, $contract_bu, $contract_sales,
            $contract_numar, $contract_activ, $lunifacturare, $abonamentID
        );
        
        if (!mysqli_stmt_execute($stmt_abon)) {
            die('Error: ' . mysqli_error($conn));
        }
        mysqli_stmt_close($stmt_abon);
        echo "<div class=\"callout success\">$strSubscribtionModified</div>";
    }
}
elseif ($contract_abonament==1) {
    // INSERT abonament nou
    $contract_numar_data = $contract_numar . "/" . $contract_data;
    $unitate = 'Luni';
    $activ_zero = 0;
    
    $stmt_abon2 = mysqli_prepare($conn, 
        "INSERT INTO clienti_abonamente(abonament_client_ID, abonament_client_valoare, abonament_client_valuta, 
        abonament_client_frecventa, abonament_client_aloc, abonament_client_detalii, abonament_client_unitate, 
        abonament_client_termen, abonament_client_zifacturare, abonament_client_lunafacturare, abonament_client_activ, 
        abonament_client_email, abonament_client_an, abonament_client_BU, abonament_client_sales, abonament_client_contract) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    
    mysqli_stmt_bind_param($stmt_abon2, 'isssssssssssssss', 
        $id_client, $contract_suma, $contract_valuta, $contract_tip, $contract_alocat,
        $contract_obiect, $unitate, $contract_termen, $contract_zifacturare, $lunifacturare,
        $activ_zero, $contract_email, $contract_an, $contract_bu, $contract_sales, $contract_numar_data
    );
    
    if (!mysqli_stmt_execute($stmt_abon2)) {
        die('Error: ' . mysqli_error($conn));
    }
    mysqli_stmt_close($stmt_abon2);
    echo "<div class=\"callout success\">$strSubscribtionAdded</div>";
}

echo "<div class=\"callout success\">$strRecordModified</div></div></div>";
echo "<div class=\"callout success\">$strRecordModified</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecontracts.php\"
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
                <p><a href="sitecontracts.php" class="button"><?php echo $strBack?> <i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post" Action="sitecontracts.php?mode=new" enctype="multipart/form-data">
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strClient?>
                        <select name="ID_Client" class="required">
                            <option value=""><?php echo $strClient?></option>
                            <?php $sql = "Select * FROM clienti_date ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                            <option value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
                            <?php
}?>
                        </select></label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strSeenBy?>
                        <select name="Contract_Alocat" class="required">
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
                        <select name="Contract_Sales" class="required">
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
                    <label><?php echo $strSubscribtion?>
                        <input name="Contract_abonament" type="radio" value="0" checked />
                        <?php echo $strOneTimeJob?>&nbsp;&nbsp;
                        <input name="Contract_abonament" type="radio" value="1"><?php echo $strSubscribtion?>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strObject?>
                        <textarea name="Contract_Obiect" id="obiect" style="width:100%;"></textarea>
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strNumber?>
                        <input name="Contract_Numar" type="text" id="numar" class="required" value="" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strYear?>
                        <input name="Contract_An" type="text" id="numar" class="required" value="" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strFile ?>
                        <input type="file" name="file[]" id="file" multiple>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strDay?>
                        <input name="Contract_Zifacturare" type="text" id="numar" class="required" value="" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strDeadline?></label>
                    <input name="Contract_Termen" type="text" id="numar" class="required" value="10" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strCurrency?>
                        <input name="Contract_Valuta" type="radio" value="0" checked />
                        <?php echo $strLei?>&nbsp;&nbsp;<input name="Contract_Valuta" type="radio"
                            value="1"><?php echo $strEuro?>
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strDate?>
                        <input name="Contract_Data" type="date" id="date" class="required" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strValue?>
                        <input name="Contract_Suma" type="text" id="numar" class="required" value="" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <?php echo $strBusinessUnit?>
                    <select name="Contract_BU">
                        <option value="" selected><?php echo $strPick?></option>
                        <?php
			 			$query7="SELECT * FROM clienti_activitati_contracte ORDER By activitate_contracte_denumire ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			echo"<option value=\"$seenby[activitate_contracte_cod]\">". $seenby['activitate_contracte_denumire']."</option>";
			}
		?>
                    </select></label>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strFrequency?>
                        <input name="Contract_Tip" type="radio" value="1"><?php echo $strMonthly?>&nbsp;&nbsp;
                        <input name="Contract_Tip" type="radio" value="2"><?php echo $strQuaterly?>&nbsp;&nbsp;
                        <input name="Contract_Tip" type="radio" value="0"><?php echo $strSemestrial?>&nbsp;&nbsp;
                        <input name="Contract_Tip" type="radio" value="3" checked /> <?php echo $strYearly?>
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strActive?>
                        <input name="Contract_Activ" type="radio" value="0" checked />
                        <?php echo $strYes?>&nbsp;&nbsp;<input name="Contract_Activ" type="radio"
                            value="1"><?php echo $strNo?>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strResponsible?>
                        <input name="Contract_Responsabil" type="text" id="numar" class="required" value="" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strEmail?>
                        <input name="Contract_Email_Facturare" type="text" id="numar" class="required" value="" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strInvoiceMonth?>
                        <select name="Contract_lunifacturare[]" multiple size=5>
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
    		echo "<OPTION value=\"$m\">$monthname</OPTION>";
				} 
			?>
                        </select></label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button">
                </div>
            </div>
        </form>
        <?php
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
    
// Prepared statement pentru SELECT \u00een EDIT mode
$stmt_edit = mysqli_prepare($conn, 
    "SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_Aloc,
    clienti_contracte.ID_Client, clienti_contracte.Contract_Alocat, clienti_contracte.Contract_File, clienti_contracte.Contract_Activ, 
    clienti_contracte.Contract_Sales, clienti_contracte.Contract_Tip, clienti_contracte.Contract_Responsabil, 
    clienti_contracte.Contract_Email_Facturare, clienti_contracte.Contract_Suma, clienti_contracte.Contract_Valuta, 
    clienti_contracte.Contract_Termen, clienti_contracte.Contract_Zifacturare, clienti_contracte.Contract_abonament, 
    clienti_contracte.Contract_Obiect, utilizator_Nume, utilizator_Prenume, ID_Contract, Contract_Numar, Contract_An, 
    Contract_lunifacturare, Contract_Data, Contract_BU 
    FROM clienti_contracte, clienti_date, date_utilizatori
    WHERE ID_Contract=? AND clienti_date.ID_Client=clienti_contracte.ID_Client 
    AND date_utilizatori.utilizator_Code=clienti_contracte.Contract_Alocat 
    ORDER By Client_Denumire ASC"
);
mysqli_stmt_bind_param($stmt_edit, 'i', $cID);
mysqli_stmt_execute($stmt_edit);
$result = mysqli_stmt_get_result($stmt_edit);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_edit);
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="sitecontracts.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post" enctype="multipart/form-data"
            Action="sitecontracts.php?mode=edit&cID=<?php echo $cID?>">
            <?php
// Prepared statement pentru SELECT abonamente
$stmt_abon_select = mysqli_prepare($conn, 
    "SELECT abonament_ID from clienti_abonamente WHERE abonament_client_ID=? AND abonament_client_detalii=?"
);
mysqli_stmt_bind_param($stmt_abon_select, 'is', $row['ID_Client'], $row['Contract_Obiect']);
mysqli_stmt_execute($stmt_abon_select);
$aresult = mysqli_stmt_get_result($stmt_abon_select);
$arow = mysqli_fetch_array($aresult, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_abon_select);
?>

            <input type="hidden" id="abonament_ID" name="abonament_ID" value="<?php echo $arow["abonament_ID"]?>">
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strTitle?>
                        <select name="ID_Client" class="required">
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
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strSeenBy?>
                        <select name="Contract_Alocat" class="required">
                            <option value=""><?php echo $strSeenBy?></option>
                            <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                            <option value="<?php echo $rss["utilizator_Code"]?>"
                                <?php if($rss["utilizator_Code"]==$row["Contract_Alocat"]) {echo "selected";}?>>
                                <?php echo $rss["utilizator_Nume"]?>&nbsp;<?php echo $rss["utilizator_Prenume"]?>
                            </option>
                            <?php
}?>
                        </select></label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strSales?>
                        <select name="Contract_Sales" class="required">
                            <option value=""><?php echo $strSeenBy?></option>
                            <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                            <option value="<?php echo $rss["utilizator_Code"]?>"
                                <?php if($rss["utilizator_Code"]==$row["Contract_Sales"]) {echo "selected";}?>>
                                <?php echo $rss["utilizator_Nume"]?>&nbsp;<?php echo $rss["utilizator_Prenume"]?>
                            </option>
                            <?php
}?>
                        </select></label>
                </div>

            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strObject?>
                        <textarea name="Contract_Obiect" id="obiect"
                            style="width:100%;"><?php echo $row["Contract_Obiect"]?></textarea>
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strNumber?>
                        <input name="Contract_Numar" type="text" id="numar" class="required"
                            value="<?php echo $row["Contract_Numar"]?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strYear?>
                        <input name="Contract_An" type="text" id="numar" class="required"
                            value="<?php echo $row["Contract_An"]?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strSubscribtion?>

                        <?php 
                        $client_id_temp = (int)$row['ID_Client'];
                        $contract_obiect_temp = $row['Contract_Obiect'];
                        $stmt_sub = mysqli_prepare($conn, "SELECT * FROM clienti_abonamente WHERE abonament_client_ID=? AND abonament_client_detalii=? ORDER BY abonament_client_detalii ASC");
                        mysqli_stmt_bind_param($stmt_sub, "is", $client_id_temp, $contract_obiect_temp);
                        mysqli_stmt_execute($stmt_sub);
                        $result = mysqli_stmt_get_result($stmt_sub);
		$numar= mysqli_num_rows($result);
		$arow=mysqli_fetch_array($result, MYSQLI_ASSOC);
                        mysqli_stmt_close($stmt_sub);
		if ($numar==0)
		{?>
                        <input name="Contract_abonament" type="radio" value="0" checked />
                        <?php echo $strOneTimeJob?>&nbsp;&nbsp;
                        <input name="Contract_abonament" type="radio" value="1" /><?php echo $strSubscribtion?>
                        <?php
		}
		else
	   {
		?>
                        <input name="abonament_ID" class="required" type="text"
                            value="<?php echo $arow["abonament_ID"]." - ".$arow["abonament_client_detalii"]?>" />
                        <input name="Contract_abonament" type="hidden" value="3" />
                        <?php
	}
	?></label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strDay?>
                        <input name="Contract_Zifacturare" type="text" id="numar" class="required"
                            value="<?php echo $row["Contract_Zifacturare"]?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strDeadline?>
                        <input name="Contract_Termen" type="text" id="numar" class="required"
                            value="<?php echo $row["Contract_Termen"]?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strCurrency?>
                        <input name="Contract_Valuta" type="radio" value="0"
                            <?php If ($row["Contract_Valuta"]==0) echo "checked"?> />
                        <?php echo $strLei?>&nbsp;&nbsp;<input name="Contract_Valuta" type="radio" value="1"
                            <?php If ($row["Contract_Valuta"]==1) echo "checked"?>><?php echo $strEuro?>
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strDate?>
                        <input name="Contract_Data" type="date" id="numar" class="required"
                            value="<?php echo $row["Contract_Data"]?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strValue?>
                        <input name="Contract_Suma" type="text" id="numar" class="required"
                            value="<?php echo $row["Contract_Suma"]?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <?php echo $strBusinessUnit?>
                    <select name="Contract_BU">
                        <?php
			 			$query7="SELECT * FROM clienti_activitati_contracte ORDER By activitate_contracte_denumire ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
				if($row["Contract_BU"]==$seenby["activitate_contracte_cod"])
				{			echo"<option selected value=\"$seenby[activitate_contracte_cod]\">". $seenby['activitate_contracte_denumire']."</option>";}
			else
				{			echo"<option value=\"$seenby[activitate_contracte_cod]\">". $seenby['activitate_contracte_denumire']."</option>";}
			}
		?></select></label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strFrequency?>
                        <input name="Contract_Tip" type="radio" value="1"
                            <?php If ($row["Contract_Tip"]==1) echo "checked"?>><?php echo $strMonthly?>&nbsp;&nbsp;
                        <input name="Contract_Tip" type="radio" value="2"
                            <?php If ($row["Contract_Tip"]==2) echo "checked"?>><?php echo $strQuaterly?>&nbsp;&nbsp;
                        <input name="Contract_Tip" type="radio" value="3"
                            <?php If ($row["Contract_Tip"]==3) echo "checked"?>><?php echo $strSemestrial?>&nbsp;&nbsp;
                        <input name="Contract_Tip" type="radio" value="0"
                            <?php If ($row["Contract_Tip"]==0) echo "checked"?> /> <?php echo $strYearly?>
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strActive?>
                        <input name="Contract_Activ" type="radio" value="0"
                            <?php If ($row["Contract_Activ"]==0) echo "checked"?> />
                        <?php echo $strYes?>&nbsp;&nbsp;<input name="Contract_Activ" type="radio" value="1"
                            <?php If ($row["Contract_Activ"]==1) echo "checked"?>><?php echo $strNo?>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strResponsible?>
                        <input name="Contract_Responsabil" type="text" id="numar" class="required"
                            value="<?php echo $row["Contract_Responsabil"]?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3cell">
                    <label><?php echo $strEmail?>
                        <input name="Contract_Email_Facturare" type="text" id="numar" class="required"
                            value="<?php echo $row["Contract_Email_Facturare"]?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strInvoiceMonth?>
                        <select name="Contract_lunifacturare[]" multiple size=5>
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
$lunidefacturare=explode(";",$row["Contract_lunifacturare"]);
if(in_array($m,$lunidefacturare)) $str_flag = "selected";
else $str_flag="";
    		echo "<OPTION value=\"$m\" $str_flag>$monthname</OPTION>";
				} 
			?>
                        </select></label>

                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strFile ?>
                        <input type="file" name="file[]" id="file" multiple>
                        <?php
if (isset($row['Contract_File']) AND !empty($row['Contract_File']))
{
	$contractfiles=explode(";",$row['Contract_File']);
	echo "<br />$strCurrentFiles: <br />";
	foreach ($contractfiles as $file) {
		if (!empty($file)) {
			echo "<a href=\"../common/opendoc.php?type=4&docID=$file\" target=\"_blank\" rel=\"noopener noreferrer\"><i class=\"far fa-file-pdf\">&nbsp;$file</i></a> |
			<a href=\"../common/deletefiles.php?type=4&docID=$file&contractID=$row[ID_Contract]\" OnClick=\"return confirm('$strConfirmDelete');\">
	  <i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a><br />";
		}
	}
}
?>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button">
                </div>
            </div>
        </form>
        <?php
}
else
{
echo "<a href=\"sitecontracts.php?mode=new\" class=\"button\">$strAddNew <i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a><br />";
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_Aloc, clienti_date.Client_Tip, 
clienti_contracte.ID_Client, clienti_contracte.Contract_Alocat, clienti_contracte.Contract_Activ, clienti_contracte.Contract_Tip,
clienti_contracte.Contract_Suma, clienti_contracte.Contract_Valuta, clienti_contracte.Contract_File, clienti_contracte.Contract_Obiect, utilizator_Nume, utilizator_Prenume, ID_Contract
FROM clienti_contracte, clienti_date, date_utilizatori
WHERE clienti_date.ID_Client=clienti_contracte.ID_Client AND date_utilizatori.utilizator_Code=clienti_contracte.Contract_Alocat AND Client_Tip=1 ";

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
echo $strTotal . " " .$numar." ".$strContracts ;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"sitecontracts.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
echo " <br /><br />";
$sql="SELECT DISTINCT LEFT(clienti_date.Client_Denumire, 1) as letter 
FROM clienti_date, clienti_contracte 
WHERE clienti_contracte.ID_client=clienti_date.ID_Client Group By letter ORDER BY letter ASC;";
$result2=ezpub_query($conn,$sql);
While ($row1=ezpub_fetch_array($result2)){
	$char=$row1["letter"];
    echo "<a href=\"sitecontracts.php?start=$char\">$char</a>&nbsp;";
}
?>
        </div>
        <table width="100%">
            <thead>
                <tr>
                    <th><?php echo $strClient?></th>
                    <th><?php echo $strObject?></th>
                    <th><?php echo $strSeenBy?></th>
                    <th><?php echo $strView?></th>
                    <th><?php echo $strEdit?></th>
                    <th><?php echo $strDelete?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[Contract_Obiect]</td>
			<td>$row[utilizator_Prenume]&nbsp;$row[utilizator_Nume] </td>
			<td>";
			  if (isset($row['Contract_File']) AND !empty($row['Contract_File']))
{
	$contractfiles=explode(";",$row['Contract_File']);
	foreach ($contractfiles as $file) {
		if (!empty($file)) {
			echo "<a href=\"../common/opendoc.php?type=4&docID=$file\" target=\"_blank\" rel=\"noopener noreferrer\"><i class=\"far fa-file-pdf\">&nbsp;$file</i></a> |
			<a href=\"../common/deletefiles.php?type=4&docID=$file&contractID=$row[ID_Contract]\" OnClick=\"return confirm('$strConfirmDelete');\">
	  <i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a><br />";
		}
	}
}

echo		"</td>
			  <td><a href=\"sitecontracts.php?mode=edit&cID=$row[ID_Contract]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"sitecontracts.php?mode=delete&cID=$row[ID_Contract]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"3\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
?>
    </div>
</div>
<hr />
<?php
include '../bottom.php';
?>