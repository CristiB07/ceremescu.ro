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

// Validare format și valoare dată (YYYY-MM-DD) — fără PCRE
$dataincasarii = $_POST['data_incasarii'];
$dt = DateTime::createFromFormat('Y-m-d', $dataincasarii);
$dtErrors = DateTime::getLastErrors();
if (!$dt || $dt->format('Y-m-d') !== $dataincasarii || $dtErrors['warning_count'] > 0 || $dtErrors['error_count'] > 0) {
    die("<div class=\"callout alert\">Format dată invalid</div>");
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

// Parsare suma totală din formular (user poate modifica valoarea sugerată)
if (!isset($_POST['chitanta_suma_incasata'])) {
    die("Suma încasată este necesară");
}
$suma_norm = parseRomanianNumber($_POST['chitanta_suma_incasata']);
if (!is_numeric($suma_norm)) {
    die("Suma introdusă este invalidă");
}
$payment_remaining = (float)$suma_norm;
$suma_totala_incasata = 0.0;

// Pregătim SELECT pentru toate facturile selectate ordonate după vechime (data emiterii)
$placeholders = implode(',', array_fill(0, count($facturi_IDs), '?'));
$types = str_repeat('i', count($facturi_IDs));
$stmt_sel = mysqli_prepare($conn, "SELECT factura_ID, factura_data_emiterii, factura_client_valoare_totala, IFNULL(factura_suma_partiala,0) AS suma_partiala, factura_numar FROM facturare_facturi WHERE factura_ID IN ($placeholders) ORDER BY factura_data_emiterii ASC");
// bind dynamic params
$bind_names[] = $stmt_sel; // dummy to keep structure
$refs = [];
for ($i = 0; $i < count($facturi_IDs); $i++) {
    $refs[$i] = &$facturi_IDs[$i];
}
call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt_sel, $types], $refs));
mysqli_stmt_execute($stmt_sel);
$result_sel = mysqli_stmt_get_result($stmt_sel);

while ($row1 = ezpub_fetch_array($result_sel)) {
    $factura_ID = (int)$row1['factura_ID'];
    $dataemiterii = strtotime($row1['factura_data_emiterii']);
    $total_fact = (float)$row1['factura_client_valoare_totala'];
    $already_partial = (float)$row1['suma_partiala'];
    $outstanding = $total_fact - $already_partial;
        // Handle negative total invoices (credit notes): mark them as paid immediately
        if ($total_fact < 0) {
            // mark invoice as paid (no money movement needed)
            $incasare = strtotime($dataincasarii);
            $datediff = $incasare - $dataemiterii;
            $zile = round($datediff / (60 * 60 * 24));
            $stmt_final = mysqli_prepare($conn, "UPDATE facturare_facturi SET factura_suma_partiala = NULL, factura_plata_partiala = NULL, factura_data_partiala = NULL, factura_client_achitat = 1, factura_client_data_achitat = ?, factura_client_zile_achitat = ?, factura_client_achitat_prin='1' WHERE factura_ID = ?");
            mysqli_stmt_bind_param($stmt_final, "sii", $dataincasarii, $zile, $factura_ID);
            mysqli_stmt_execute($stmt_final);
            mysqli_stmt_close($stmt_final);

            // add to processed list (no amount applied)
            $facturi_procesate[] = $siteInvoicingCode . str_pad($row1['factura_numar'], 8, '0', STR_PAD_LEFT) . " (credit)";
            // include negative invoice amount in total collected so bank balances are adjusted
            $suma_totala_incasata += $total_fact; // total_fact is negative here
            continue;
        }

        if ($outstanding < 0) $outstanding = 0.0;

        if ($payment_remaining <= 0) break; // nimic de aplicat

        $apply = min($outstanding, $payment_remaining);

        if ($apply <= 0) continue;

    // Insertăm întotdeauna un rând în plăți parțiale pentru suma aplicată (istoric)
    $stmt_pp = mysqli_prepare($conn, "INSERT INTO facturare_plati_partiale (plata_factura_id, plata_factura_suma, plata_data) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt_pp, "ids", $factura_ID, $apply, $dataincasarii);
    mysqli_stmt_execute($stmt_pp);
    mysqli_stmt_close($stmt_pp);

    // Actualizăm suma parțială în factură
    $new_partial = $already_partial + $apply;
    $is_fully_paid = ($new_partial + 0.0001) >= $total_fact; // tolerantă la float

    if ($is_fully_paid) {
        // Dacă factura devine integral achitată - curățăm flag-ul și suma parțială
        $stmt_up = mysqli_prepare($conn, "UPDATE facturare_facturi SET factura_suma_partiala = NULL, factura_plata_partiala = NULL, factura_data_partiala = NULL WHERE factura_ID = ?");
        mysqli_stmt_bind_param($stmt_up, "i", $factura_ID);
        mysqli_stmt_execute($stmt_up);
        mysqli_stmt_close($stmt_up);

        // Marcăm factura ca achitată
        $incasare = strtotime($dataincasarii);
        $datediff = $incasare - $dataemiterii;
        $zile = round($datediff / (60 * 60 * 24));
        $stmt_final = mysqli_prepare($conn, "UPDATE facturare_facturi SET factura_client_achitat = 1, factura_client_data_achitat = ?, factura_client_zile_achitat = ?, factura_client_achitat_prin='1' WHERE factura_ID = ?");
        mysqli_stmt_bind_param($stmt_final, "sii", $dataincasarii, $zile, $factura_ID);
        mysqli_stmt_execute($stmt_final);
        mysqli_stmt_close($stmt_final);
    } else {
        // Rămâne parțial plătită - actualizăm suma parțială și flag-ul
        $stmt_up = mysqli_prepare($conn, "UPDATE facturare_facturi SET factura_suma_partiala = ?, factura_plata_partiala = 1, factura_data_partiala = ? WHERE factura_ID = ?");
        mysqli_stmt_bind_param($stmt_up, "dsi", $new_partial, $dataincasarii, $factura_ID);
        mysqli_stmt_execute($stmt_up);
        mysqli_stmt_close($stmt_up);
    }

    $payment_remaining -= $apply;
    $suma_totala_incasata += $apply;
    $facturi_procesate[] = $siteInvoicingCode . str_pad($row1['factura_numar'], 8, '0', STR_PAD_LEFT) . ( $apply < $outstanding || !$is_fully_paid ? " (partial: " . number_format($apply,2,'.',',') . ")" : "" );
}

mysqli_stmt_close($stmt_sel);

// Dacă a rămas sumă nealocată (overpayment), adăugăm restul la suma totală în bancă
if ($payment_remaining > 0) {
    $suma_totala_incasata += $payment_remaining; // se lasă ca sold în bancă
}

// Actualizare solduri bancă cu suma totală (folosim suma din formular aplicată)
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

        <form method="post"  action="sitecashin.php">
            <div class="grid-x grid-padding-x ">
                <div class="large-6 medium-6 small-12 cell">
                    <label> <?php echo $strInvoice?>
                        <select name="chitanta_factura_ID[]" id="chitanta_factura_ID" multiple required size="10" style="height: auto;">
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
			// SELECT cu prepared statement pentru ID specific (adăugăm suma partială pentru JS)
			$stmt_select = mysqli_prepare($conn, "SELECT factura_ID, factura_numar, factura_data_emiterii, factura_client_denumire, factura_client_valoare_totala, IFNULL(factura_suma_partiala,0) AS suma_partiala FROM facturare_facturi WHERE factura_ID=?");
			mysqli_stmt_bind_param($stmt_select, "i", $cid);
			mysqli_stmt_execute($stmt_select);
			$result = mysqli_stmt_get_result($stmt_select);
		} else {
			// SELECT fără parametri pentru toate facturile neachitate (include partiale)
			$query = "SELECT factura_ID, factura_numar, factura_data_emiterii, factura_client_denumire, factura_client_valoare_totala, IFNULL(factura_suma_partiala,0) AS suma_partiala FROM facturare_facturi WHERE factura_client_achitat=0 AND factura_tip=0 ORDER BY factura_client_denumire ASC";
			$result = ezpub_query($conn, $query);
		}

  while ($rss=ezpub_fetch_array($result)){
		// Escape output pentru XSS protection
		$factura_ID_safe = htmlspecialchars($rss["factura_ID"], ENT_QUOTES, 'UTF-8');
		$client_denumire_safe = htmlspecialchars($rss["factura_client_denumire"], ENT_QUOTES, 'UTF-8');
		$factura_numar_safe = htmlspecialchars($rss["factura_numar"], ENT_QUOTES, 'UTF-8');
        $codenumarfactura=str_pad($factura_numar_safe, 8, '0', STR_PAD_LEFT);
		$factura_data_safe = htmlspecialchars($rss["factura_data_emiterii"], ENT_QUOTES, 'UTF-8');
		$outstanding = number_format((float)$rss["factura_client_valoare_totala"] - (float)$rss["suma_partiala"], 2, '.', '');
	?>
                            <option value="<?php echo $factura_ID_safe?>" data-outstanding="<?php echo $outstanding?>" data-date="<?php echo $factura_data_safe?>"
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
                <div class="large-1 medium-2 small-12 cell">
                    <label> <?php echo $strDate?>
                        <input type="date" name="data_incasarii" value="<?php echo date('Y-m-d')?>" required>
                    </label>
                </div>
                <div class="large-3 medium-3 small-12 cell">
                    <label> <?php echo $strBank?></label>
                        <input name="factura_client_banca_achitat" type="radio" value="Transilvania" checked />
                        <?php echo "Transilvania"?>&nbsp;&nbsp;
                        <input name="factura_client_banca_achitat" type="radio" value="ING" /> <?php echo "ING"?>&nbsp;&nbsp;
                        <input name="factura_client_banca_achitat" type="radio" value="Unicredit"><?php echo "Trezorerie"?>
                </div>
                <div class="large-2 medium-12 small-12 cell">
                    <label>Plată parțială
                        <input type="checkbox" name="plata_partial" id="plata_partial" value="1">
                    </label>
               <br/>
                    <label> Suma plătită
                        <input type="text" name="chitanta_suma_incasata" id="chitanta_suma_incasata" value="" required>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x ">
                <div class="large-12 medium-12 small-12 text-center cell">
                    <input type="submit" value="<?php echo $strAdd?>" class="button success" name="Submit">
                </div>
            </div>
        </form>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var sel = document.getElementById('chitanta_factura_ID');
            if (!sel) return;
            sel.addEventListener('change', function() {
                var selectedOptions = Array.from(sel.selectedOptions).map(function(o){ return o.value; }).filter(Boolean);
                var fd = new FormData();
                // receiptsum.php expects factura_ID as array-like POST; send as factura_ID[] entries
                selectedOptions.forEach(function(v){ fd.append('factura_ID[]', v); });

                fetch('receiptsum.php', { method: 'POST', body: fd })
                .then(function(resp){ return resp.json(); })
                .then(function(data){
                    try {
                        if (data && Object.prototype.hasOwnProperty.call(data, 'suma')) {
                            var inp = document.getElementById('chitanta_suma_incasata');
                            if (inp) inp.value = data['suma'];
                        }
                    } catch (err) {
                        if (console && console.error) console.error(err.message);
                    }
                })
                .catch(function(){ alert('Eroare la calculare sumă'); });
            });
        });
        </script>
        <?php
}
?>
    </div>
</div>
<?php
include '../bottom.php';
?>    