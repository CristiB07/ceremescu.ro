<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle = "Generare document Word Anexa 1B";
 if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 


$uid = $_SESSION['uid'];
$code = $_SESSION['code'];
$uquery = "SELECT * FROM date_utilizatori WHERE utilizator_ID='" . intval($uid) . "'";
$uresult = ezpub_query($conn, $uquery);
$userRow = ezpub_fetch_array($uresult); 
$username = $userRow['utilizator_Nume'] . ' ' . $userRow['utilizator_Prenume'];
$phone=$userRow['utilizator_Phone'];

// Preluare clienți pentru select
$clienti = [];
$sql = "SELECT clienti_date.ID_Client, Client_Denumire FROM clienti_date, clienti_contracte WHERE Contract_Alocat='$code' AND clienti_date.ID_Client=clienti_contracte.ID_Client AND Contract_Activ=0 ORDER BY Client_Denumire ASC";
$res = ezpub_query($conn, $sql);
while ($row = ezpub_fetch_array($res)) {
    $clienti[] = $row;
}


// Generare ani de la anul curent la 2020
$ani = [];
$an_curent = date('Y');
for ($an = $an_curent; $an >= 2020; $an--) {
  $ani[] = $an;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id = (int)$_POST['client_id'];
    $an = (int)$_POST['an_raportare'];
    $rows = [];
    for ($i=1; $i<=9; $i++) {
      // Preluăm denumirea ambalajului din tabelul ambalaje
      $nume = '';
      $ambalajRes = ezpub_query($conn, "SELECT ambalaj_nume FROM ambalaje WHERE ambalaj_id='$i'");
      if ($ambalajRow = ezpub_fetch_array($ambalajRes)) {
        $nume = htmlspecialchars($ambalajRow['ambalaj_nume']);
      } else {
        $nume = $i;
      }
      // Preluăm datele de gestiune dacă există
      $ambquery = "SELECT * FROM ambalaje_gestionate WHERE gestiune_a_client_id=$client_id AND gestiune_a_an_raportare=$an AND gestiune_a_ambalaj_id = '$i'";
      $ambresult = ezpub_query($conn, $ambquery);
      $ambrow = ezpub_fetch_array($ambresult);
      $rows[$i] = [
        'material' => $nume,
        'ambalaje_desfacere' => (float)($ambrow['gestiune_a_ambalaje_desfacere'] ?? 0),
        'total_puse_piata' => (float)($ambrow['gestiune_a_ambalaje_primare'] ?? 0) + (float)($ambrow['gestiune_a_ambalaje_secundare'] ?? 0),
        'ambalaje_primare_total' => (float)($ambrow['gestiune_a_ambalaje_primare'] ?? 0),
        'ambalaje_primare_refolosibile' => (float)($ambrow['gestiune_a_ambalaje_primare_re'] ?? 0),
        'ambalaje_secundare_total' => (float)($ambrow['gestiune_a_ambalaje_secundare'] ?? 0),
        'ambalaje_secundare_refolosibile' => (float)($ambrow['gestiune_a_ambalaje_secundare_re'] ?? 0),
        'ambalaje_periculoase' => (float)($ambrow['gestiune_a_ambalaje_periculoase'] ?? 0)
      ];
    }

    $tableheader = '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse;width:100%;">
    <thead>
      <tr>
        <th rowspan="3">Material</th>
        <th rowspan="3">Ambalaje de desfacere fabricate/importate</th>
        <th colspan="6">Ambalaje de desfacere puse pe piață în anul de raportare<sup>1)</sup></th>
      </tr>
      <tr>
        <th rowspan="2">Total<br>(col. 3+5)</th>
        <th colspan="2">Ambalaje primare</th>
        <th colspan="2">Ambalaje secundare</th>
        <th rowspan="2">Ambalaje cu conținut periculos<sup>3)</sup> din coloana 3</th>
      </tr>
      <tr>
        <th>Total</th>
        <th>din care ambalaj reutilizabil</th>
        <th>Total</th>
        <th>din care ambalaj reutilizabil</th>
      </tr>
    </thead>';


$tablerows = '';

$total = [
  'ambalaje_desfacere' => 0,
  'total_puse_piata' => 0,
  'ambalaje_primare_total' => 0,
  'ambalaje_primare_refolosibile' => 0,
  'ambalaje_secundare_total' => 0,
  'ambalaje_secundare_refolosibile' => 0,
  'ambalaje_periculoase' => 0
];
$tablerows = '';
for ($i=1; $i<=9; $i++) {
  $r = $rows[$i];
  $tablerows .= "<tr>";
  $tablerows .= "<td>{$r['material']}</td>";
  $tablerows .= "<td style=\"text-align:right;\">{$r['ambalaje_desfacere']}</td>";
  $tablerows .= "<td style=\"text-align:right;\">{$r['total_puse_piata']}</td>";
  $tablerows .= "<td style=\"text-align:right;\">{$r['ambalaje_primare_total']}</td>";
  $tablerows .= "<td style=\"text-align:right;\">{$r['ambalaje_primare_refolosibile']}</td>";
  $tablerows .= "<td style=\"text-align:right;\">{$r['ambalaje_secundare_total']}</td>";
  $tablerows .= "<td style=\"text-align:right;\">{$r['ambalaje_secundare_refolosibile']}</td>";
  $tablerows .= "<td style=\"text-align:right;\">{$r['ambalaje_periculoase']}</td>";
  $tablerows .= "</tr>";

  $total['ambalaje_desfacere'] += $r['ambalaje_desfacere'];
  $total['total_puse_piata'] += $r['total_puse_piata'];
  $total['ambalaje_primare_total'] += $r['ambalaje_primare_total'];
  $total['ambalaje_primare_refolosibile'] += $r['ambalaje_primare_refolosibile'];
  $total['ambalaje_secundare_total'] += $r['ambalaje_secundare_total'];
  $total['ambalaje_secundare_refolosibile'] += $r['ambalaje_secundare_refolosibile'];
  $total['ambalaje_periculoase'] += $r['ambalaje_periculoase'];

  // După rândul 3: total plastic (2+3)
  if ($i == 3) {
    $tp = [
      'ambalaje_desfacere' => $rows[2]['ambalaje_desfacere'] + $rows[3]['ambalaje_desfacere'],
      'total_puse_piata' => $rows[2]['total_puse_piata'] + $rows[3]['total_puse_piata'],
      'ambalaje_primare_total' => $rows[2]['ambalaje_primare_total'] + $rows[3]['ambalaje_primare_total'],
      'ambalaje_primare_refolosibile' => $rows[2]['ambalaje_primare_refolosibile'] + $rows[3]['ambalaje_primare_refolosibile'],
      'ambalaje_secundare_total' => $rows[2]['ambalaje_secundare_total'] + $rows[3]['ambalaje_secundare_total'],
      'ambalaje_secundare_refolosibile' => $rows[2]['ambalaje_secundare_refolosibile'] + $rows[3]['ambalaje_secundare_refolosibile'],
      'ambalaje_periculoase' => $rows[2]['ambalaje_periculoase'] + $rows[3]['ambalaje_periculoase']
    ];
    $tablerows .= "<tr style=\"font-weight:bold;background:#f0f0f0;\"><td>Total Plastic</td>";
    $tablerows .= "<td style=\"text-align:right;\">{$tp['ambalaje_desfacere']}</td>";
    $tablerows .= "<td style=\"text-align:right;\">{$tp['total_puse_piata']}</td>";
    $tablerows .= "<td style=\"text-align:right;\">{$tp['ambalaje_primare_total']}</td>";
    $tablerows .= "<td style=\"text-align:right;\">{$tp['ambalaje_primare_refolosibile']}</td>";
    $tablerows .= "<td style=\"text-align:right;\">{$tp['ambalaje_secundare_total']}</td>";
    $tablerows .= "<td style=\"text-align:right;\">{$tp['ambalaje_secundare_refolosibile']}</td>";
    $tablerows .= "<td style=\"text-align:right;\">{$tp['ambalaje_periculoase']}</td></tr>";
  }
  // După rândul 5: total metal (4+5)
  if ($i == 6) {
    $tm = [
      'ambalaje_desfacere' => $rows[5]['ambalaje_desfacere'] + $rows[6]['ambalaje_desfacere'],
      'total_puse_piata' => $rows[5]['total_puse_piata'] + $rows[6]['total_puse_piata'],
      'ambalaje_primare_total' => $rows[5]['ambalaje_primare_total'] + $rows[6]['ambalaje_primare_total'],
      'ambalaje_primare_refolosibile' => $rows[5]['ambalaje_primare_refolosibile'] + $rows[6]['ambalaje_primare_refolosibile'],
      'ambalaje_secundare_total' => $rows[5]['ambalaje_secundare_total'] + $rows[6]['ambalaje_secundare_total'],
      'ambalaje_secundare_refolosibile' => $rows[5]['ambalaje_secundare_refolosibile'] + $rows[6]['ambalaje_secundare_refolosibile'],
      'ambalaje_periculoase' => $rows[5]['ambalaje_periculoase'] + $rows[6]['ambalaje_periculoase']
    ];
    $tablerows .= "<tr style=\"font-weight:bold;background:#f0f0f0;\"><td>Total Metal</td>";
    $tablerows .= "<td style=\"text-align:right;\">{$tm['ambalaje_desfacere']}</td>";
    $tablerows .= "<td style=\"text-align:right;\">{$tm['total_puse_piata']}</td>";
    $tablerows .= "<td style=\"text-align:right;\">{$tm['ambalaje_primare_total']}</td>";
    $tablerows .= "<td style=\"text-align:right;\">{$tm['ambalaje_primare_refolosibile']}</td>";
    $tablerows .= "<td style=\"text-align:right;\">{$tm['ambalaje_secundare_total']}</td>";
    $tablerows .= "<td style=\"text-align:right;\">{$tm['ambalaje_secundare_refolosibile']}</td>";
    $tablerows .= "<td style=\"text-align:right;\">{$tm['ambalaje_periculoase']}</td></tr>";
  }
}
$tablerows .= "<tr style=\"font-weight:bold;background:#e0e0e0;\"><td>Total general</td>";
$tablerows .= "<td style=\"text-align:right;\">{$total['ambalaje_desfacere']}</td>";
$tablerows .= "<td style=\"text-align:right;\">{$total['total_puse_piata']}</td>";
$tablerows .= "<td style=\"text-align:right;\">{$total['ambalaje_primare_total']}</td>";
$tablerows .= "<td style=\"text-align:right;\">{$total['ambalaje_primare_refolosibile']}</td>";
$tablerows .= "<td style=\"text-align:right;\">{$total['ambalaje_secundare_total']}</td>";
$tablerows .= "<td style=\"text-align:right;\">{$total['ambalaje_secundare_refolosibile']}</td>";
$tablerows .= "<td style=\"text-align:right;\">{$total['ambalaje_periculoase']}</td></tr>";

// Înlocuiește echo-urile rândurilor individuale cu echo $tablerows;


    // Exemplu de generare Word (conținutul îl vei completa tu)
    header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
    header("Content-Disposition: attachment; filename=anexa1b_{$an}_client{$client_id}.doc");
    echo "<html><body>$header<p>Anul pentru care se realizează raportarea: <strong>$an</strong></p>";
    echo '<h2>Tabelul 1. Ambalaje introduse pe piaţa naţională [kilograme] </h2>';
    echo $tableheader;
    // Aici adaugi rândurile tabelului cu date reale
echo $tablerows;
    echo '</table>';
echo '    <p><sup>1)</sup> Se raportează numai ambalajele de desfacere destinate pieţei naţionale, definite prin Hotărârea Guvernului nr. 621/2005 privind gestionarea ambalajelor şi a deşeurilor de ambalaje, cu modificările şi completările ulterioare: Ambalaje de desfacere - obiectele proiectate şi destinate a fi umplute la punctele de vânzare, precum şi obiectele „de unică folosinţă“ vândute umplute sau destinate a fi umplute în punctele de desfacere sunt considerate ambalaje dacă îndeplinesc funcţia de ambalare. Exemple de ambalaje de desfacere (dacă sunt destinate a fi umplute la punctul de vânzare): pungi pentru cumpărături din plastic sau hârtie, farfurii şi pahare de unică folosinţă, filme aderente, pungi pentru sandvişuri, folia de aluminiu. Nu sunt ambalaje: paletele pentru amestecat, tacâmurile de unică folosinţă. </p>
<p><sup>2)</sup> Se raportează o singură dată, atunci când sunt introduse în circuitul de umplere şi livrate pentru prima dată. </p>
<p><sup>3)</sup> Se raportează numai ambalajele care au conţinut substanţe periculoase inscripţionate ca atare potrivit Hotărârii Guvernului nr. 937/2010 privind clasificarea, ambalarea şi etichetarea la introducerea pe piaţă a preparatelor periculoase. Cantităţile de ambalaje cu conţinut periculos sunt tot ambalaje primare şi se regăsesc şi în coloana 3. </p>
<p><sup>4)</sup> Se raportează numai ambalajele folosite la ambalarea produselor destinate pieţei naţionale şi se includ şi ambalajele utilizate pentru ambalarea ambalajelor de desfacere. </p>';
echo '<h2>Tabelul 2. Deşeuri de ambalaje gestionate [kilograme] </h2>';
$tableheader2 = '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse;width:100%;">
    <thead>
<thead>
  <tr>
    <th rowspan="3">Material</th>
    <th colspan="3">Deşeuri de ambalaje încredinţate unui operator economic autorizat</th>
    <th rowspan="3">Operaţiunea<sup>2)</sup> la care a supus deşeul operatorul menţionat în coloana 2</th>
  </tr>
  <tr>
    <th rowspan="2">Cantitatea</th>
    <th colspan="2">Operatorul economic<sup>1)</sup> autorizat pentru colectarea, reciclarea şi valorificarea deşeurilor de ambalaje</th>
  </tr>
  <tr>
    <th>Denumire</th>
    <th>CUI</th>
  </tr>
</thead>
<tr>
<td>0</td>
<td>1</td>
<td>2</td>
<td>3</td>
<td>4</td>
</tr>';

    echo $tableheader2;
    // Tabel 2: Deșeuri de ambalaje gestionate
    $total2 = 0;
    $ambalajeRes = ezpub_query($conn, "SELECT ambalaj_id, ambalaj_nume FROM ambalaje ORDER BY ambalaj_id ASC");
    while ($ambalaj = ezpub_fetch_array($ambalajeRes)) {
      $ambalaj_id = (int)$ambalaj['ambalaj_id'];
      $ambalaj_nume = htmlspecialchars($ambalaj['ambalaj_nume']);
      $deseuriRes = ezpub_query($conn, "SELECT * FROM ambalaje_deseuri WHERE ad_client_id=$client_id AND ad_an_raportare='$an' AND ad_ambalaj_id=$ambalaj_id");
      $found = false;
      while ($row = ezpub_fetch_array($deseuriRes)) {
        $found = true;
        $cant = (float)$row['ad_total'];
        $total2 += $cant;
        echo "<tr>";
        echo "<td>$ambalaj_nume</td>";
        echo "<td style='text-align:right;'>" . number_format($cant,2) . "</td>";
        echo "<td>" . htmlspecialchars($row['ad_operator_denumire']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ad_operator_cui']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ad_cod_operatiune']) . "</td>";
        echo "</tr>";
      }
      // Dacă nu există niciun rând pentru acest ambalaj, afișăm un rând gol cu denumirea
      if (!$found) {
        echo "<tr><td>$ambalaj_nume</td><td style='text-align:right;'>0.00</td><td></td><td></td><td></td></tr>";
      }
    }
    // Total general la final
    echo "<tr style='font-weight:bold;background:#e0e0e0;'><td>Total general</td><td style='text-align:right;'>" . number_format($total2,2) . "</td><td colspan='3'></td></tr>";
    echo '</table>';

echo '<p><sup>1)</sup> Se completează câte o rubrică distinctă pentru fiecare dintre operatorii care au preluat deşeurile de ambalaje din materialul respectiv. </p>
<p><sup>2)</sup> Se menţionează operaţiunea la care au fost supuse deşeurile potrivit anexei nr. 3 la Legea nr. 211/2011 privind regimul deşeurilor. În cazul în care operaţiunea de reciclare/valorificare se face prin export sau transfer intracomunitar, se va specifica alături de denumirea operatorului economic şi ţara de destinaţie. </p>
<p>NOTĂ: Se completează în tabel distinct în cazul deşeurilor de ambalaje periculoase. </p>
<p align=\"right\">Semnătura autorizată şi ştampila </p>

<table width="100%" border="0" cellpadding="5" cellspacing="0" style="border-collapse:collapse;">
<tr>
<td align="center" width="33%">Numele şi prenumele:</td>
<td align="center" width="33%">Funcţia:</td>
<td align="center" width="33%">Data:</td>
</tr>
</table>';

    echo "</body></html>";
    exit;
}
include '../dashboard/header.php';
?>
<div class="grid-x grid-margin-x">
  <div class="large-12 medium-12 small-12 cell">
    <h1><?php echo $strPageTitle; ?></h1>
    <form method="post">
      <div class="grid-x grid-margin-x">
        <div class="large-6 medium-6 small-12 cell">
          <label><?php echo $strClient; ?>
            <select name="client_id" required>
              <option value="">-- Selectează client --</option>
              <?php foreach ($clienti as $c) {
                echo '<option value="' . htmlspecialchars($c['ID_Client']) . '">' . htmlspecialchars($c['Client_Denumire']) . '</option>';
              } ?>
            </select>
          </label>
        </div>
        <div class="large-6 medium-6 small-12 cell">
          <label><?php echo $strYear; ?>
            <select name="an_raportare" required>
              <option value="">-- Selectează anul --</option>
              <?php foreach ($ani as $an) {
                echo '<option value="' . $an . '">' . $an . '</option>';
              } ?>
            </select>
          </label>
        </div>
      </div>
      <div class="grid-x grid-margin-x">
        <div class="large-12 cell">
          <button type="submit" class="button success"><i class="fa fa-file-word"></i> Generează document Word</button>
        </div>
      </div>
    </form>
  </div>
</div>
<?php include '../bottom.php'; ?>
