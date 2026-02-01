<?php
// salesprospectprofile.php - scaffold sales prospect profile with tabs
include_once '../settings.php';
include_once '../classes/common.php';

$strPageTitle = "Profil prospect vânzări";
include '../dashboard/header.php';

if(!isset($_SESSION)) { session_start(); }
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
    header("location:$strSiteURL/login/index.php?message=MLF");
    die;
}

 // Accept either cID (used across app) or id
 $id = 0;
 if (isset($_GET['cID'])) $id = (int)$_GET['cID'];
 elseif (isset($_GET['id'])) $id = (int)$_GET['id'];
 if ($id<=0) {
     echo "<div class=\"callout alert\">ID prospect invalid</div>";
     include '../bottom.php';
     exit();
 }

 // Preia date prospect (tabel folosește coloana prospect_id)
 $stmt = mysqli_prepare($conn, "SELECT * FROM sales_prospecti WHERE prospect_id = ? LIMIT 1");
 mysqli_stmt_bind_param($stmt, "i", $id);
 mysqli_stmt_execute($stmt);
 $res = mysqli_stmt_get_result($stmt);
 $prospect = mysqli_fetch_array($res, MYSQLI_ASSOC);
 mysqli_stmt_close($stmt);

 if (!$prospect) {
     echo "<div class=\"callout alert\">Prospect negăsit</div>";
     include '../bottom.php';
     exit();
 }

// UI tabs (Foundation)
?>
<div class="grid-container">
    <h2>Profil prospect: <?php echo htmlspecialchars($prospect['prospect_denumire'] ?? ''); ?></h2>
    <ul class="tabs" data-tabs id="prospect-tabs">
        <li class="tabs-title is-active"><a href="#panel1">Date prospect</a></li>
        <li class="tabs-title"><a href="#panel2">Vizite</a></li>
        <li class="tabs-title"><a href="#panel3">Date fiscale</a></li>
        <li class="tabs-title"><a href="#panel4">Bilanțuri</a></li>
        <li class="tabs-title"><a href="#panel5">Procese</a></li>
    </ul>

    <div class="tabs-content" data-tabs-content="prospect-tabs">
        <div class="tabs-panel is-active" id="panel1">
            <p><strong>Nume:</strong> <?php echo htmlspecialchars($prospect['prospect_denumire'] ?? ''); ?></p>
            <p><strong>CUI:</strong> <?php echo htmlspecialchars($prospect['prospect_cui'] ?? ''); ?></p>
            <p><strong>Adresa:</strong> <?php echo htmlspecialchars($prospect['prospect_adresa'] ?? ''); ?></p>
        </div>
        <div class="tabs-panel" id="panel2">
            <?php
            // Include simple list of visits if table exists
            // Table name is sales_vizite_prospecti in this schema
            $stmt2 = mysqli_prepare($conn, "SELECT * FROM sales_vizite_prospecti WHERE client_vizita=? ORDER BY data_vizita DESC LIMIT 50");
            mysqli_stmt_bind_param($stmt2, "i", $id);
            mysqli_stmt_execute($stmt2);
            $res2 = mysqli_stmt_get_result($stmt2);
            echo '<ul>';
            while ($r = mysqli_fetch_array($res2, MYSQLI_ASSOC)) {
                $date = $r['data_vizita'] ?? '';
                $scop = $r['scop_vizita'] ?? $r['observatii_vizita'] ?? '';
                echo '<li>' . htmlspecialchars($date . ' - ' . $scop) . '</li>';
            }
            echo '</ul>';
            mysqli_stmt_close($stmt2);
            ?>
        </div>
        <div class="tabs-panel" id="panel3">
            <?php
            // Embed fiscalview with target_table for sales prospects
            $cui = urlencode($prospect['prospect_cui'] ?? '');
            echo '<iframe src="../anaf/fiscalview.php?cui=' . $cui . '&target_table=sales_prospecti_date_fiscale&parent_table=sales_prospecti" style="width:100%;height:600px;border:0"></iframe>';
            ?>
        </div>
        <div class="tabs-panel" id="panel4">
            <?php
            // Embed balancesview passing cui
            $cui = urlencode($prospect['prospect_cui'] ?? '');
            echo '<iframe src="../anaf/balancesview.php?cui=' . $cui . '" style="width:100%;height:600px;border:0"></iframe>';
            ?>
        </div>
        <div class="tabs-panel" id="panel5">
            <?php
            // Include clients/just_query.php directly (like clientprofile)
            // Set Client_Denumire so just_query.php auto-runs the search when included
            $Client_Denumire = $prospect['prospect_denumire'] ?? '';
            include_once __DIR__ . '/../clients/just_query.php';
            ?>
        </div>
    </div>
</div>

<?php include '../bottom.php';

?>
