<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare încasări";
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/index.php?message=MLF");
}
include '../dashboard/header.php';
$day = date('d');
$year = date('Y');
$month = date('m');
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/index.php?message=MLF");
}
?>
<div class="grid-x grid-padding-x ">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";
if ($_SERVER['REQUEST_METHOD'] == 'POST'){

// Validare și sanitizare input pentru facturi multiple ÎNAINTE de check_inject
if (!isset($_POST['chitanta_factura_ID']) || !is_array($_POST['chitanta_factura_ID']) || empty($_POST['chitanta_factura_ID'])) {
    die("<div class=\"callout alert\">Trebuie să selectați cel puțin o factură</div>");
}

// Validare fiecare ID din array și salvare în variabilă separată
$facturi_IDs = [];
foreach ($_POST['chitanta_factura_ID'] as $id) {
    $validated_id = filter_var($id, FILTER_VALIDATE_INT);
    if ($validated_id === false || $validated_id <= 0) {
        die("<div class=\"callout alert\">ID factură invalid</div>");
    }
    $facturi_IDs[] = $validated_id;
}

// Ștergem array-ul din POST pentru a preveni eroarea în check_inject
$temp_facturi = $_POST['chitanta_factura_ID'];
unset($_POST['chitanta_factura_ID']);

// Acum putem apela check_inject fără probleme
check_inject();

// Validare bancă
$banciPermise = ['Transilvania', 'ING', 'Unicredit'];
if (!isset($_POST['factura_client_banca_achitat']) || !in_array($_POST['factura_client_banca_achitat'], $banciPermise)) {
    die("<div class=\"callout alert\">Bancă invalidă</div>");
}
$banca = $_POST['factura_client_banca_achitat'];

// Validare dată
if (!isset($_POST['data_incasarii']) || empty($_POST['data_incasarii'])) {
    die("<div class=\"callout alert\">Data încasării este obligatorie</div>");
}

// Validare format dată (YYYY-MM-DD)
$dataincasarii = $_POST['data_incasarii'];
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataincasarii)) {
    die("<div class=\"callout alert\">Format dată invalid</div>");
}

// Validare că data este validă
$date_parts = explode('-', $dataincasarii);
if (!checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
    die("<div class=\"callout alert\">Dată invalidă</div>");
}

// SELECT cash_banca la început
$sql2 = "SELECT * FROM cash_banca";
$result2 = ezpub_query($conn,$sql2);
$row2=ezpub_fetch_array($result2);
$transilvania=(float)$row2["cash_banca_transilvania"];
$ing=(float)$row2["cash_banca_ING"];
$unicredit=(float)$row2["cash_banca_trezorerie"];

$suma_totala_incasata = 0;
$facturi_procesate = [];

// Procesare fiecare factură selectată
foreach ($facturi_IDs as $factura_ID) {
    // SELECT cu prepared statement
    $stmt1 = mysqli_prepare($conn, "SELECT factura_data_emiterii, factura_client_valoare_totala, factura_numar FROM facturare_facturi WHERE factura_ID=?");
    mysqli_stmt_bind_param($stmt1, "i", $factura_ID);
    mysqli_stmt_execute($stmt1);
    $result1 = mysqli_stmt_get_result($stmt1);
    $row1=ezpub_fetch_array($result1);
    mysqli_stmt_close($stmt1);
    
    if ($row1) {
        $dataemiterii=strtotime($row1["factura_data_emiterii"]);
        $sumaincasata=(float)$row1["factura_client_valoare_totala"];
        $incasare=strtotime($dataincasarii);
        $datediff=$incasare-$dataemiterii;
        $zile=round($datediff / (60 * 60 * 24));	

        // UPDATE facturare_facturi cu prepared statement
        $stmt_update1 = mysqli_prepare($conn, "UPDATE facturare_facturi SET factura_client_achitat='1', factura_client_data_achitat=?, factura_client_banca_achitat=?, factura_client_zile_achitat=?, factura_client_achitat_prin='1' WHERE factura_ID=?");
        mysqli_stmt_bind_param($stmt_update1, "ssii", $dataincasarii, $banca, $zile, $factura_ID);
        mysqli_stmt_execute($stmt_update1);
        mysqli_stmt_close($stmt_update1);
        
        // Adăugare la suma totală
        $suma_totala_incasata += $sumaincasata;
        $facturi_procesate[] = $siteInvoicingCode.str_pad($row1["factura_numar"], 8, '0', STR_PAD_LEFT);
    }
}

// Actualizare solduri bancă cu suma totală
if ($banca == "ING") {
    $ing = $ing + $suma_totala_incasata;
} elseif ($banca == "Transilvania") {
    $transilvania = $transilvania + $suma_totala_incasata;
} else {
    $unicredit = $unicredit + $suma_totala_incasata;
}

// UPDATE cash_banca cu prepared statement
$stmt_update2 = mysqli_prepare($conn, "UPDATE cash_banca SET cash_banca_ING=?, cash_banca_transilvania=?, cash_banca_trezorerie=?");
mysqli_stmt_bind_param($stmt_update2, "ddd", $ing, $transilvania, $unicredit);
mysqli_stmt_execute($stmt_update2);
mysqli_stmt_close($stmt_update2);

$numar_facturi = count($facturi_procesate);
$lista_facturi = implode(", ", $facturi_procesate);
echo "<div class=\"callout success\">Au fost încasate $numar_facturi facturi: $lista_facturi<br>Suma totală: ".number_format($suma_totala_incasata, 2, '.', ',')." lei</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecashin.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php'; 
die;
}

else {
?>

        <form method="post" id="users" Action="sitecashin.php">
            <div class="grid-x grid-padding-x ">
                <div class="large-6 medium-6 small-12 cell">
                    <label> <?php echo $strInvoice?>
                        <select name="chitanta_factura_ID[]" multiple required size="10" style="height: auto;">
                            <option value="" selected>--</option>
                            <?php 
		// Validare $_GET['cID']
		if (isset($_GET['cID']) && !empty($_GET['cID'])) {
			$cid = filter_var($_GET['cID'], FILTER_VALIDATE_INT);
			if ($cid === false || $cid <= 0) {
				$cid = 0;
			}
		} else {
			$cid = 0;
		}
		
		if ($cid > 0) {
			// SELECT cu prepared statement pentru ID specific
			$stmt_select = mysqli_prepare($conn, "SELECT factura_ID, factura_numar, factura_data_emiterii, factura_client_denumire FROM facturare_facturi WHERE factura_ID=?");
			mysqli_stmt_bind_param($stmt_select, "i", $cid);
			mysqli_stmt_execute($stmt_select);
			$result = mysqli_stmt_get_result($stmt_select);
		} else {
			// SELECT fără parametri pentru toate facturile neachitate
			$query = "SELECT factura_ID, factura_numar, factura_data_emiterii, factura_client_denumire FROM facturare_facturi WHERE factura_client_achitat=0 AND factura_tip=0 ORDER BY factura_client_denumire ASC";
			$result = ezpub_query($conn, $query);
		}

  while ($rss=ezpub_fetch_array($result)){
		// Escape output pentru XSS protection
		$factura_ID_safe = htmlspecialchars($rss["factura_ID"], ENT_QUOTES, 'UTF-8');
		$client_denumire_safe = htmlspecialchars($rss["factura_client_denumire"], ENT_QUOTES, 'UTF-8');
		$factura_numar_safe = htmlspecialchars($rss["factura_numar"], ENT_QUOTES, 'UTF-8');
        $codenumarfactura=str_pad($factura_numar_safe, 8, '0', STR_PAD_LEFT);
		$factura_data_safe = htmlspecialchars($rss["factura_data_emiterii"], ENT_QUOTES, 'UTF-8');
	?>
                            <option value="<?php echo $factura_ID_safe?>"
                                <?php if ($rss["factura_ID"]==$cid) echo 'selected'?>>
                                <?php echo $client_denumire_safe." - ".$siteInvoicingCode.$codenumarfactura." - ". $factura_data_safe?>
                            </option>
                            <?php }
		// Închidere statement dacă există
		if (isset($stmt_select)) {
			mysqli_stmt_close($stmt_select);
		}
		?>
                        </select>
                        <small>Țineți apăsat Ctrl (sau Cmd pe Mac) pentru a selecta mai multe facturi</small>
                    </label>
                </div>
                <div class="large-2 medium-3 small-12 cell">
                    <label> <?php echo $strDate?>
                        <input type="date" name="data_incasarii" value="<?php echo date('Y-m-d')?>" required>
                    </label>
                </div>
                <div class="large-4 medium-3 small-12 cell">
                    <label> <?php echo $strBank?></label>
                        <input name="factura_client_banca_achitat" type="radio" value="Transilvania" checked />
                        <?php echo "Transilvania"?>&nbsp;&nbsp;
                        <input name="factura_client_banca_achitat" type="radio" value="ING" />
                        <?php echo "ING"?>&nbsp;&nbsp;
                        <input name="factura_client_banca_achitat" type="radio"
                            value="Unicredit"><?php echo "Trezorerie"?>
                    
                </div>
            </div>
            <div class="grid-x grid-padding-x ">
                <div class="large-12 medium-12 small-12 text-center cell">
                    <input type="submit" value="<?php echo $strAdd?>" class="button success" name="Submit">
                </div>
            </div>
        </form>
        <?php
}
?>
    </div>
</div>
<?php
include '../bottom.php';
?>    <div class="large-12 medium-12 small-12