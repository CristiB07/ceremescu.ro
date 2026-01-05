<?php // Last Modified Time: Friday, August 29, 2025 at 3:17:59 PM Eastern European Summer Time ?>
<?php // Last Modified Time: Tuesday, August 26, 2025 at 10:04:26 PM Eastern European Summer Time ?>
<?php // Last Modified Time: Friday, August 22, 2025 at 9:20:41 PM Eastern European Summer Time ?>
<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare plăți";
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
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
	header("location:$strSiteURL/login/login.php?message=MLF");
}
?>
<div class="grid-x grid-padding-x ">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";
if ($_SERVER['REQUEST_METHOD'] == 'POST'){

// Validare și sanitizare input pentru facturi multiple ÎNAINTE de check_inject
if (!isset($_POST['chitanta_fp_id']) || !is_array($_POST['chitanta_fp_id']) || empty($_POST['chitanta_fp_id'])) {
    die("<div class=\"callout alert\">Trebuie să selectați cel puțin o factură</div>");
}

// Validare fiecare ID din array și salvare în variabilă separată
$facturi_IDs = [];
foreach ($_POST['chitanta_fp_id'] as $id) {
    $validated_id = filter_var($id, FILTER_VALIDATE_INT);
    if ($validated_id === false || $validated_id <= 0) {
        die("<div class=\"callout alert\">ID factură invalid</div>");
    }
    $facturi_IDs[] = $validated_id;
}

// Ștergem array-ul din POST pentru a preveni eroarea în check_inject
unset($_POST['chitanta_fp_id']);

// Acum putem apela check_inject fără probleme
check_inject();

// Validare bancă
$banciPermise = ['Transilvania', 'ING', 'Trezorerie', 'paid'];
if (!isset($_POST['factura_client_banca_achitat']) || !in_array($_POST['factura_client_banca_achitat'], $banciPermise)) {
    die("<div class=\"callout alert\">Bancă invalidă</div>");
}
$banca = $_POST['factura_client_banca_achitat'];

// Validare dată
if (!isset($_POST['data_plata']) || empty($_POST['data_plata'])) {
    die("<div class=\"callout alert\">Data plății este obligatorie</div>");
}

// Validare format dată (YYYY-MM-DD)
$dataplata = $_POST['data_plata'];
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataplata)) {
    die("<div class=\"callout alert\">Format dată invalid</div>");
}

// Validare că data este validă
$date_parts = explode('-', $dataplata);
if (!checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
    die("<div class=\"callout alert\">Dată invalidă</div>");
}

// SELECT cash_banca la început
$sql2 = "SELECT * FROM cash_banca";
$result2 = ezpub_query($conn,$sql2);
$row2 = ezpub_fetch_array($result2);
$transilvania = (float)$row2["cash_banca_transilvania"];
$ing = (float)$row2["cash_banca_ING"];
$Trezorerie = (float)$row2["cash_banca_trezorerie"];

$suma_totala_platita = 0;
$facturi_procesate = [];

// Procesare fiecare factură selectată
foreach ($facturi_IDs as $fp_id) {
    // SELECT cu prepared statement
    $stmt1 = mysqli_prepare($conn, "SELECT fp_data_emiterii, fp_valoare_totala, fp_numar_factura FROM facturare_facturi_primite WHERE fp_id=?");
    mysqli_stmt_bind_param($stmt1, "i", $fp_id);
    mysqli_stmt_execute($stmt1);
    $result1 = mysqli_stmt_get_result($stmt1);
    $row1 = ezpub_fetch_array($result1);
    mysqli_stmt_close($stmt1);
    
    if ($row1) {
        $dataemiterii = strtotime($row1["fp_data_emiterii"]);
        $sumaplata = (float)$row1["fp_valoare_totala"];
        $plata = strtotime($dataplata);
        $datediff = $plata - $dataemiterii;
        $zile = round($datediff / (60 * 60 * 24));

        // UPDATE facturare_facturi_primite cu prepared statement
        $stmt_update1 = mysqli_prepare($conn, "UPDATE facturare_facturi_primite SET fp_achitat='1', fp_data_achitat=?, fp_zile_achitat=? WHERE fp_id=?");
        mysqli_stmt_bind_param($stmt_update1, "sii", $dataplata, $zile, $fp_id);
        mysqli_stmt_execute($stmt_update1);
        mysqli_stmt_close($stmt_update1);
        
        // Adăugare la suma totală doar dacă nu e "Achitat deja"
        if ($banca != 'paid') {
            $suma_totala_platita += $sumaplata;
        }
        $facturi_procesate[] = $row1["fp_numar_factura"];
    }
}

// Actualizare solduri bancă cu suma totală (scădere) doar dacă nu e "Achitat deja"
if ($banca == "ING") {
    $ing = $ing - $suma_totala_platita;
} elseif ($banca == "Transilvania") {
    $transilvania = $transilvania - $suma_totala_platita;
} elseif ($banca == "Trezorerie") {
    $Trezorerie = $Trezorerie - $suma_totala_platita;
}
// else pentru 'paid' - nu schimbăm nimic în bancă

// UPDATE cash_banca cu prepared statement
$stmt_update2 = mysqli_prepare($conn, "UPDATE cash_banca SET cash_banca_ING=?, cash_banca_transilvania=?, cash_banca_trezorerie=?");
mysqli_stmt_bind_param($stmt_update2, "ddd", $ing, $transilvania, $Trezorerie);
mysqli_stmt_execute($stmt_update2);
mysqli_stmt_close($stmt_update2);

$numar_facturi = count($facturi_procesate);
$lista_facturi = implode(", ", $facturi_procesate);
$mesaj_suma = ($banca != 'paid') ? "<br>Suma totală: ".number_format($suma_totala_platita, 2, '.', ',')." lei" : "";
echo "<div class=\"callout success\">Au fost plătite $numar_facturi facturi: $lista_facturi$mesaj_suma</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitepayout.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php'; 
die;
}

else {
?>

        <form method="post" id="users" Action="sitepayout.php">
            <div class="grid-x grid-padding-x ">
                <div class="large-6 medium-6 small-12 cell">
                    <label> <?php echo $strInvoice?>
                        <select name="chitanta_fp_id[]" multiple required size="10" style="height: auto;">
                            <option value="" disabled>-- Selectați una sau mai multe facturi --</option>
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
			$stmt_select = mysqli_prepare($conn, "SELECT fp_id, fp_numar_factura, fp_data_emiterii, fp_nume_furnizor FROM facturare_facturi_primite WHERE fp_id=?");
			mysqli_stmt_bind_param($stmt_select, "i", $cid);
			mysqli_stmt_execute($stmt_select);
			$result = mysqli_stmt_get_result($stmt_select);
		} else {
			// SELECT fără parametri pentru toate facturile neachitate
			$query = "SELECT fp_id, fp_numar_factura, fp_data_emiterii, fp_nume_furnizor FROM facturare_facturi_primite WHERE fp_achitat=0 ORDER BY fp_nume_furnizor ASC";
			$result = ezpub_query($conn, $query);
		}

  while ($rss=ezpub_fetch_array($result)){
		// Escape output pentru XSS protection
		$fp_id_safe = htmlspecialchars($rss["fp_id"], ENT_QUOTES, 'UTF-8');
		$furnizor_safe = htmlspecialchars($rss["fp_nume_furnizor"], ENT_QUOTES, 'UTF-8');
		$numar_safe = htmlspecialchars($rss["fp_numar_factura"], ENT_QUOTES, 'UTF-8');
		$data_safe = htmlspecialchars($rss["fp_data_emiterii"], ENT_QUOTES, 'UTF-8');
	?>
                            <option value="<?php echo $fp_id_safe?>" <?php if ($rss["fp_id"]==$cid) echo 'selected'?>>
                                <?php echo $furnizor_safe." - ". $numar_safe." - ". $data_safe?>
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
                        <input type="date" name="data_plata" value="<?php echo date('Y-m-d')?>" required>
                    </label>
                </div>
                <div class="large-4 medium-3 small-12 cell">
                    <label> <?php echo $strBank?></label>
                        <input name="factura_client_banca_achitat" type="radio" value="Transilvania" />
                        <?php echo "Transilvania"?>&nbsp;&nbsp;
                        <input name="factura_client_banca_achitat" type="radio" value="ING" />
                        <?php echo "ING"?>&nbsp;&nbsp;
                        <input name="factura_client_banca_achitat" type="radio" value="Trezorerie"><?php echo "Trezorerie"?>
                        <input name="factura_client_banca_achitat" type="radio" value="paid" checked><?php echo "Achitat deja"?>

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
?>