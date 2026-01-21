<?php
include '../settings.php';
include '../classes/common.php';

$strPageTitle = "Dashboard clienți";
include '../dashboard/header.php';
if(!isset($_SESSION)) { session_start(); }
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
    header("location:$strSiteURL/login/index.php?message=MLF");
    die;
}
$lang = $_SESSION['$lang'] ?? 'RO';
if ($lang=="RO") { include '../lang/language_RO.php'; } else { include '../lang/language_EN.php'; }

// Tabs
$tabs = [
    1 => 'Top 20 valoare facturi',
    2 => 'Top 20 număr facturi',
    3 => 'Top 20 buni platnici',
    4 => 'Top 20 rău platnici',
    5 => 'Top 20 venituri abonamente',
    6 => 'Analiză client',
];

?>
<div class="grid-x grid-margin-x">
  <div class="large-12 cell">
    <h1><?php echo $strPageTitle?></h1>
    <?php $activeTab = 1;
    if (!empty($_GET['client_select']) && !empty($_GET['year_select'])) $activeTab = 6;
    ?>
    <ul class="tabs" data-tabs id="clients-dashboard-tabs">
      <?php foreach ($tabs as $i => $tabName): ?>
        <li class="tabs-title<?php echo $i==$activeTab?' is-active':''?>"><a data-tabs-target="panel<?php echo $i?>" href="#panel<?php echo $i?>"><?php echo htmlspecialchars($tabName)?></a></li>
      <?php endforeach; ?>
    </ul>
    <div class="tabs-content" data-tabs-content="clients-dashboard-tabs">
      <!-- Tab 1: Top 20 valoare facturi -->
      <div class="tabs-panel<?php echo $activeTab==1?' is-active':''?>" id="panel1">
        <table class="table">
          <thead><tr><th>Client</th><th>Valoare totală facturi</th></tr></thead>
          <tbody>
          <?php
          $sql = "SELECT c.Client_Denumire, SUM(f.factura_client_valoare_totala) AS total_facturi FROM clienti_date c JOIN facturare_facturi f ON c.ID_Client = f.factura_client_ID GROUP BY c.ID_Client ORDER BY total_facturi DESC LIMIT 20";
          $res = ezpub_query($conn, $sql);
          while ($row = ezpub_fetch_array($res)) {
            echo '<tr><td>' . htmlspecialchars($row['Client_Denumire']) . '</td><td>' . ($row['total_facturi'] === null ? '-' : number_format($row['total_facturi'],2)) . '</td></tr>';
          }
          ?>
          </tbody>
        </table>
      </div>
      <!-- Tab 2: Top 20 număr facturi -->
      <div class="tabs-panel<?php echo $activeTab==2?' is-active':''?>" id="panel2">
        <table class="table">
          <thead><tr><th>Client</th><th>Număr facturi</th></tr></thead>
          <tbody>
          <?php
          $sql = "SELECT c.Client_Denumire, COUNT(f.factura_id) AS numar_facturi FROM clienti_date c JOIN facturare_facturi f ON c.ID_Client = f.factura_client_ID GROUP BY c.ID_Client ORDER BY numar_facturi DESC LIMIT 20";
          $res = ezpub_query($conn, $sql);
          while ($row = ezpub_fetch_array($res)) {
            echo '<tr><td>' . htmlspecialchars($row['Client_Denumire']) . '</td><td>' . $row['numar_facturi'] . '</td></tr>';
          }
          ?>
          </tbody>
        </table>
      </div>
      <!-- Tab 3: Top 20 buni platnici -->
      <div class="tabs-panel<?php echo $activeTab==3?' is-active':''?>" id="panel3">
        <table class="table">
          <thead><tr><th>Client</th><th>Medie zile plată</th><th>Număr facturi</th></tr></thead>
          <tbody>
          <?php
          $sql = "SELECT c.Client_Denumire, AVG(f.factura_client_zile_achitat) AS medie_zile, COUNT(f.factura_id) AS numar_facturi FROM clienti_date c JOIN facturare_facturi f ON c.ID_Client = f.factura_client_ID WHERE YEAR(f.factura_data_emiterii) = 2023 GROUP BY c.ID_Client HAVING numar_facturi >= 5 ORDER BY medie_zile ASC LIMIT 20";
          $res = ezpub_query($conn, $sql);
          while ($row = ezpub_fetch_array($res)) {
            echo '<tr><td>' . htmlspecialchars($row['Client_Denumire']) . '</td><td>' . ($row['medie_zile'] === null ? '-' : number_format($row['medie_zile'],2)) . '</td><td>' . $row['numar_facturi'] . '</td></tr>';
          }
          ?>
          </tbody>
        </table>
      </div>
      <!-- Tab 4: Top 20 rău platnici -->
      <div class="tabs-panel<?php echo $activeTab==4?' is-active':''?>" id="panel4">
        <table class="table">
          <thead><tr><th>Client</th><th>Medie zile plată</th><th>Număr facturi</th></tr></thead>
          <tbody>
          <?php
          $sql = "SELECT c.Client_Denumire, AVG(f.factura_client_zile_achitat) AS medie_zile, COUNT(f.factura_id) AS numar_facturi FROM clienti_date c JOIN facturare_facturi f ON c.ID_Client = f.factura_client_ID WHERE YEAR(f.factura_data_emiterii) = 2023 GROUP BY c.ID_Client HAVING numar_facturi >= 5 ORDER BY medie_zile DESC LIMIT 20";
          $res = ezpub_query($conn, $sql);
          while ($row = ezpub_fetch_array($res)) {
            echo '<tr><td>' . htmlspecialchars($row['Client_Denumire']) . '</td><td>' . number_format($row['medie_zile'],2) . '</td><td>' . $row['numar_facturi'] . '</td></tr>';
          }
          ?>
          </tbody>
        </table>
      </div>
      <!-- Tab 5: Top 20 venituri abonamente -->
      <div class="tabs-panel<?php echo $activeTab==5?' is-active':''?>" id="panel5">
        <table class="table">
          <thead><tr><th>Client</th><th>Venituri abonamente</th></tr></thead>
          <tbody>
          <?php
          $sql = "SELECT c.Client_Denumire, SUM(a.abonament_client_valoare) AS venit_abonamente FROM clienti_date c JOIN clienti_abonamente a ON c.ID_Client = a.abonament_client_ID GROUP BY c.ID_Client ORDER BY venit_abonamente DESC LIMIT 20";
          $res = ezpub_query($conn, $sql);
          while ($row = ezpub_fetch_array($res)) {
            echo '<tr><td>' . htmlspecialchars($row['Client_Denumire']) . '</td><td>' . number_format($row['venit_abonamente'],2) . '</td></tr>';
          }
          ?>
          </tbody>
        </table>
      </div>
      <!-- Tab 6: Analiză client -->
      <div class="tabs-panel<?php echo $activeTab==6?' is-active':''?>" id="panel6">
        <form method="get" class="grid-x grid-margin-x">
          <div class="medium-4 cell">
            <label>Selectează client
              <select name="client_select">
                <option value="">-- Selectează --</option>
                <?php
                $q = ezpub_query($conn, "SELECT ID_Client, Client_Denumire FROM clienti_date ORDER BY Client_Denumire ASC");
                while ($r = ezpub_fetch_array($q)) {
                  echo '<option value="' . $r['ID_Client'] . '"' . (isset($_GET['client_select']) && $_GET['client_select'] == $r['ID_Client'] ? ' selected' : '') . '>' . htmlspecialchars($r['Client_Denumire']) . '</option>';
                }
                ?>
              </select>
            </label>
          </div>
          <div class="medium-4 cell">
            <label>Selectează an
              <select name="year_select">
                <?php
                $currentYear = date('Y');
                for ($y = $currentYear; $y >= 2023; $y--) {
                  echo '<option value="' . $y . '"' . (isset($_GET['year_select']) && $_GET['year_select'] == $y ? ' selected' : '') . '>' . $y . '</option>';
                }
                ?>
              </select>
            </label>
          </div>
          <div class="medium-4 cell">
            <button type="submit" class="button">Vezi analiza</button>
          </div>
        </form>
        <?php
        if (!empty($_GET['client_select']) && !empty($_GET['year_select'])) {
          $cid = (int)$_GET['client_select'];
          $yr = (int)$_GET['year_select'];
          // Evoluție pe luni
          $labels = [];
          $zile = [];
          $sume = [];
          $facturi = [];
          for ($m = 1; $m <= 12; $m++) {
            $labels[] = date('M', mktime(0,0,0,$m,1));
            $q = ezpub_query($conn, "SELECT AVG(factura_client_zile_achitat) AS medie_zile, SUM(factura_client_valoare_totala) AS suma_facturi, COUNT(factura_id) AS numar_facturi FROM facturare_facturi WHERE factura_client_ID='$cid' AND YEAR(factura_data_emiterii)='$yr' AND MONTH(factura_data_emiterii)='$m'");
            $r = ezpub_fetch_array($q);
            $zile[] = $r['medie_zile'] === null ? 0 : round($r['medie_zile'],2);
            $sume[] = $r['suma_facturi'] === null ? 0 : round($r['suma_facturi'],2);
            $facturi[] = $r['numar_facturi'] === null ? 0 : (int)$r['numar_facturi'];
          }
        ?>
        <canvas id="clientChart" width="800" height="350"></canvas>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
          const ctx = document.getElementById('clientChart').getContext('2d');
          const chart = new Chart(ctx, {
            type: 'line',
            data: {
              labels: <?php echo json_encode($labels); ?>,
              datasets: [
                {
                  label: 'Zile plată',
                  data: <?php echo json_encode($zile); ?>,
                  borderColor: '#2196F3',
                  backgroundColor: 'rgba(33,150,243,0.1)',
                  yAxisID: 'y',
                  tension: 0.2
                },
                {
                  label: 'Suma facturată',
                  data: <?php echo json_encode($sume); ?>,
                  borderColor: '#4CAF50',
                  backgroundColor: 'rgba(76,175,80,0.1)',
                  yAxisID: 'y1',
                  tension: 0.2
                },
                {
                  label: 'Număr facturi',
                  data: <?php echo json_encode($facturi); ?>,
                  borderColor: '#FFC107',
                  backgroundColor: 'rgba(255,193,7,0.1)',
                  yAxisID: 'y2',
                  tension: 0.2
                }
              ]
            },
            options: {
              interaction: { mode: 'index', intersect: false },
              stacked: false,
              scales: {
                y: {
                  type: 'linear',
                  display: true,
                  position: 'left',
                  title: { display: true, text: 'Zile plată' },
                  beginAtZero: true
                },
                y1: {
                  type: 'linear',
                  display: true,
                  position: 'right',
                  title: { display: true, text: 'Suma facturată' },
                  beginAtZero: true,
                  grid: { drawOnChartArea: false }
                },
                y2: {
                  type: 'linear',
                  display: true,
                  position: 'right',
                  title: { display: true, text: 'Număr facturi' },
                  beginAtZero: true,
                  grid: { drawOnChartArea: false },
                  offset: true
                }
              }
            }
          });
        });
        </script>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
<?php include '../bottom.php'; ?>
