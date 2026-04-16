<?php
$strPageTitle = "Company Info";
if(!isset($_SESSION)) { session_start(); }
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
    header("Location: ../login/index.php?message=MLF");
    die;
}

// Ensure settings are loaded (DB credentials) and common utilities are available
include_once  __DIR__ .'/../settings.php';
include_once  __DIR__ . '/../classes/common.php';

$relative_header = __DIR__ . '/../dashboard/header.php';
include $relative_header;

$cui = isset($_GET['cui']) ? trim($_GET['cui']) : '';
?>
<div class="grid-container">
    <div class="grid-x grid-margin-x">
        <div class="large-12 cell">
            <h1><?php echo $strPageTitle?></h1>
        </div>
    </div>

    <div class="grid-x grid-margin-x">
        <div class="large-6 cell">
            <form method="get">
                <label>CUI:</label>
                <input type="text" name="cui" value="<?php echo htmlspecialchars($cui)?>">
                <input type="submit" class="button" value="Caută">
            </form>
        </div>
    </div>

    <?php if ($cui!=''):
        // CUI este exclusiv numeric în od_firme_master; normalizăm la cifre
        $cui_numeric = preg_replace('/\D/', '', $cui);
        // Prepare and fetch from od_firme_master – all records ordered by DATA_INMATRICULARE DESC
        $stmt = mysqli_prepare($conn, "SELECT * FROM od_firme_master WHERE CUI = ? ORDER BY DATA_INMATRICULARE DESC");
        mysqli_stmt_bind_param($stmt, 's', $cui_numeric);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $od_all = [];
        while ($row_od = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
            $od_all[] = $row_od;
        }
        mysqli_stmt_close($stmt);
        $od = !empty($od_all) ? $od_all[0] : null; // cea mai recentă înregistrare

        // ANAF fiscal data - try direct lookup, then fallback to normalized CUI lookup
        $anaf = null;
        $stmt2 = mysqli_prepare($conn, "SELECT * FROM clienti_date_fiscale WHERE cui = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt2, 's', $cui);
        mysqli_stmt_execute($stmt2);
        $res2 = mysqli_stmt_get_result($stmt2);
        $anaf = mysqli_fetch_array($res2, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt2);

        if (empty($anaf)) {
            // Normalize CUI by removing non-digit characters and try matching a normalized column
            $cui_digits = preg_replace('/\D/', '', $cui);
            if ($cui_digits !== '') {
                $stmt2b = mysqli_prepare($conn, "SELECT * FROM clienti_date_fiscale WHERE REPLACE(REPLACE(REPLACE(cui, '.', ''), '-', ''), ' ', '') = ? LIMIT 1");
                mysqli_stmt_bind_param($stmt2b, 's', $cui_digits);
                mysqli_stmt_execute($stmt2b);
                $res2b = mysqli_stmt_get_result($stmt2b);
                $anaf = mysqli_fetch_array($res2b, MYSQLI_ASSOC);
                mysqli_stmt_close($stmt2b);
            }
        }

        // If still empty or if data is older than 3 months, try to fetch from ANAF via business helper
        $need_update = false;
        if (empty($anaf)) {
            $need_update = true;
        } else {
            // check data field age
            if (!empty($anaf['data']) && $d = DateTime::createFromFormat('Y-m-d', $anaf['data'])) {
                $now = new DateTime();
                $interval = $now->diff($d);
                if (!($interval->y == 0 && $interval->m < 3 && $interval->invert == 0)) {
                    $need_update = true;
                }
            } else {
                $need_update = true;
            }
        }
        if ($need_update) {
            // include business-specific fetcher (writes to clienti_date_fiscale)
            include_once __DIR__ . '/getfiscaldata.lib.php';
            if (function_exists('getFiscalDataByCUI_business')) {
                $res_fetch = getFiscalDataByCUI_business($cui, $conn);
                if ($res_fetch) {
                    // re-query the table to load the newly imported data
                    $stmt2c = mysqli_prepare($conn, "SELECT * FROM clienti_date_fiscale WHERE cui = ? LIMIT 1");
                    mysqli_stmt_bind_param($stmt2c, 's', $cui);
                    mysqli_stmt_execute($stmt2c);
                    $res2c = mysqli_stmt_get_result($stmt2c);
                    $anaf = mysqli_fetch_array($res2c, MYSQLI_ASSOC);
                    mysqli_stmt_close($stmt2c);
                }
            }
        }

        // Bilanțuri
        $stmt3 = mysqli_prepare($conn, "SELECT * FROM bilanturi WHERE cui = ? ORDER BY an DESC LIMIT 50");
        mysqli_stmt_bind_param($stmt3, 's', $cui);
        mysqli_stmt_execute($stmt3);
        $res3 = mysqli_stmt_get_result($stmt3);
        mysqli_stmt_close($stmt3);
    ?>

    <ul class="tabs" data-tabs id="company-tabs">
        <li class="tabs-title is-active"><a href="#panel1" aria-selected="true">Date registrul comerțului</a></li>
        <li class="tabs-title"><a href="#panel2">Date ANAF</a></li>
        <li class="tabs-title"><a href="#panel3">Date bilanț</a></li>
        <li class="tabs-title"><a href="#panel4">Date JUST</a></li>
        <li class="tabs-title"><a href="#panel5">Analiză financiară</a></li>
        <?php if (count($od_all) > 1): ?>
        <li class="tabs-title"><a href="#panel6">Istoric firmă</a></li>
        <?php endif; ?>
    </ul>

    <div class="tabs-content" data-tabs-content="company-tabs">
        <div class="tabs-panel is-active" id="panel1">
            <?php if (!$od) { echo '<div class="callout alert">Nu s-au găsit date în registrul comerțului</div>'; } else { ?>
            <table class="stack">
                <tbody>
                    <tr><td>Denumire</td><td><?php echo htmlspecialchars($od['DENUMIRE'] ?? '')?></td></tr>
                    <tr><td>CUI</td><td><?php echo htmlspecialchars($od['CUI'] ?? '')?></td></tr>
                    <tr><td>Cod înmatriculare</td><td><?php echo htmlspecialchars($od['COD_INMATRICULARE'] ?? '')?></td></tr>
                    <tr><td>Data înmatriculare</td><td><?php
                        $data_inm = isset($od['DATA_INMATRICULARE']) ? $od['DATA_INMATRICULARE'] : '';
                        if (!empty($data_inm) && $d = DateTime::createFromFormat('Y-m-d', $data_inm)) {
                            echo $d ? $d->format('d.m.Y') : htmlspecialchars((string)$data_inm);
                        } else {
                            echo htmlspecialchars((string)$data_inm);
                        }
                        ?></td></tr>
                    <tr><td>Administratori</td><td><?php
                        $cod_inmatr = $od['COD_INMATRICULARE'] ?? '';
                        $adm_persons = []; // ['name' => ..., 'birthdate' => ''] pentru verificare identitate
                        if (!empty($cod_inmatr)) {
                            $stmt_adm = mysqli_prepare($conn, "SELECT PERSOANA_IMPUTERNICITA, CALITATE, DATA_NASTERE, LOCALITATE_NASTERE FROM od_reprezentanti_legali WHERE COD_INMATRICULARE = ?");
                            if ($stmt_adm) {
                                mysqli_stmt_bind_param($stmt_adm, 's', $cod_inmatr);
                                mysqli_stmt_execute($stmt_adm);
                                $res_adm = mysqli_stmt_get_result($stmt_adm);
                                $adm_parts = [];
                                while ($row_adm = mysqli_fetch_array($res_adm, MYSQLI_ASSOC)) {
                                    $adm_persons[] = [
                                        'name'      => $row_adm['PERSOANA_IMPUTERNICITA'],
                                        'birthdate' => !empty($row_adm['DATA_NASTERE']) ? substr($row_adm['DATA_NASTERE'], 0, 10) : null,
                                    ];
                                    $adm_label = htmlspecialchars($row_adm['PERSOANA_IMPUTERNICITA']);
                                    if (!empty($row_adm['CALITATE'])) {
                                        $adm_label .= ' (' . htmlspecialchars($row_adm['CALITATE']) . ')';
                                    }
                                    $adm_parts[] = $adm_label;
                                }
                                mysqli_stmt_close($stmt_adm);
                                echo implode('<br>', $adm_parts);
                            }
                        }
                    ?></td></tr>
                    <tr><td>Alte firme cu același administrator</td><td><?php
                        if (!empty($cod_inmatr) && !empty($adm_persons)) {
                            $alte_firme = [];
                            $alte_firme_seen = [];
                            foreach ($adm_persons as $adm_person) {
                                $adm_name      = $adm_person['name'];
                                $adm_birthdate = $adm_person['birthdate']; // null dacă lipsește
                                // Dacă avem dată naștere, verificăm și după ea pentru a confirma identitatea
                                if ($adm_birthdate !== null) {
                                    $stmt_af = mysqli_prepare($conn, "SELECT DISTINCT rl.COD_INMATRICULARE FROM od_reprezentanti_legali rl WHERE rl.PERSOANA_IMPUTERNICITA = ? AND SUBSTR(rl.DATA_NASTERE,1,10) = ? AND rl.COD_INMATRICULARE != ? LIMIT 20");
                                    if ($stmt_af) {
                                        mysqli_stmt_bind_param($stmt_af, 'sss', $adm_name, $adm_birthdate, $cod_inmatr);
                                    }
                                } else {
                                    $stmt_af = mysqli_prepare($conn, "SELECT DISTINCT rl.COD_INMATRICULARE FROM od_reprezentanti_legali rl WHERE rl.PERSOANA_IMPUTERNICITA = ? AND rl.COD_INMATRICULARE != ? LIMIT 20");
                                    if ($stmt_af) {
                                        mysqli_stmt_bind_param($stmt_af, 'ss', $adm_name, $cod_inmatr);
                                    }
                                }
                                if ($stmt_af) {
                                    mysqli_stmt_execute($stmt_af);
                                    $res_af = mysqli_stmt_get_result($stmt_af);
                                    while ($row_af = mysqli_fetch_array($res_af, MYSQLI_ASSOC)) {
                                        $inm = $row_af['COD_INMATRICULARE'];
                                        if (isset($alte_firme_seen[$inm])) continue;
                                        $alte_firme_seen[$inm] = true;
                                        $stmt_fm2 = mysqli_prepare($conn, "SELECT DENUMIRE, CUI FROM od_firme_master WHERE COD_INMATRICULARE = ? LIMIT 1");
                                        if ($stmt_fm2) {
                                            mysqli_stmt_bind_param($stmt_fm2, 's', $inm);
                                            mysqli_stmt_execute($stmt_fm2);
                                            $res_fm2 = mysqli_stmt_get_result($stmt_fm2);
                                            $row_fm2 = mysqli_fetch_array($res_fm2, MYSQLI_ASSOC);
                                            if ($row_fm2 && !empty($row_fm2['CUI'])) {
                                                $alte_firme[] = '<a href="?cui=' . urlencode($row_fm2['CUI']) . '">' . htmlspecialchars($row_fm2['DENUMIRE']) . '</a>';
                                            }
                                            mysqli_stmt_close($stmt_fm2);
                                        }
                                    }
                                    mysqli_stmt_close($stmt_af);
                                }
                            }
                            echo implode('<br>', $alte_firme);
                        }
                    ?></td></tr>
                    <tr><td>Coduri CAEN autorizate</td><td><?php
                        if (!empty($cod_inmatr)) {
                            $stmt_caen = mysqli_prepare($conn, "SELECT COD_CAEN_AUTORIZAT, VER_CAEN_AUTORIZAT FROM od_caen_autorizat WHERE COD_INMATRICULARE = ?");
                            if ($stmt_caen) {
                                mysqli_stmt_bind_param($stmt_caen, 's', $cod_inmatr);
                                mysqli_stmt_execute($stmt_caen);
                                $res_caen = mysqli_stmt_get_result($stmt_caen);
                                $caen_parts = [];
                                while ($row_caen = mysqli_fetch_array($res_caen, MYSQLI_ASSOC)) {
                                    $caen_cod = $row_caen['COD_CAEN_AUTORIZAT'];
                                    $caen_ver = $row_caen['VER_CAEN_AUTORIZAT'];
                                    $caen_den = '';
                                    if ($caen_ver == 1) {
                                        // Versiunea 1 CAEN - nu avem tabel cu denumiri
                                        $caen_den = '';
                                    } elseif ($caen_ver == 3) {
                                        $stmt_cdn = mysqli_prepare($conn, "SELECT Denumire FROM generale_coduri_caen_v3 WHERE Clasa = ? LIMIT 1");
                                        if ($stmt_cdn) {
                                            mysqli_stmt_bind_param($stmt_cdn, 's', $caen_cod);
                                            mysqli_stmt_execute($stmt_cdn);
                                            $res_cdn = mysqli_stmt_get_result($stmt_cdn);
                                            $row_cdn = mysqli_fetch_array($res_cdn, MYSQLI_ASSOC);
                                            $caen_den = $row_cdn ? $row_cdn['Denumire'] : '';
                                            mysqli_stmt_close($stmt_cdn);
                                        }
                                    } else {
                                        $stmt_cdn = mysqli_prepare($conn, "SELECT Denumire FROM generale_coduri_caen_v2 WHERE Clasa = ? LIMIT 1");
                                        if ($stmt_cdn) {
                                            mysqli_stmt_bind_param($stmt_cdn, 's', $caen_cod);
                                            mysqli_stmt_execute($stmt_cdn);
                                            $res_cdn = mysqli_stmt_get_result($stmt_cdn);
                                            $row_cdn = mysqli_fetch_array($res_cdn, MYSQLI_ASSOC);
                                            $caen_den = $row_cdn ? $row_cdn['Denumire'] : '';
                                            mysqli_stmt_close($stmt_cdn);
                                        }
                                    }
                                    $caen_parts[] = htmlspecialchars($caen_cod) . ($caen_den !== '' ? ' - ' . htmlspecialchars($caen_den) : '');
                                }
                                mysqli_stmt_close($stmt_caen);
                                echo implode('<br>', $caen_parts);
                            }
                        }
                    ?></td></tr>
                    <tr><td>Adresă județ</td><td><?php echo htmlspecialchars($od['ADR_JUDET'] ?? '')?></td></tr>
                    <tr><td>Adresă localitate</td><td><?php echo htmlspecialchars($od['ADR_LOCALITATE'] ?? '')?></td></tr>
                    <tr><td>Adresă stradă</td><td><?php echo htmlspecialchars($od['ADR_DEN_STRADA'] ?? '')?></td></tr>
                    <tr><td>Adresă număr</td><td><?php echo htmlspecialchars($od['ADR_NR_STRADA'] ?? '')?></td></tr>
                    <tr><td>Cod status firmă</td><td><?php
                        $cod_status = isset($od['COD_STATUS']) ? $od['COD_STATUS'] : '';
                        if ($cod_status !== '') {
                            $status_label = '';
                            $stmt_status = mysqli_prepare($conn, "SELECT DENUMIRE FROM od_stare_firma WHERE COD = ? LIMIT 1");
                            if ($stmt_status) {
                                mysqli_stmt_bind_param($stmt_status, 's', $cod_status);
                                mysqli_stmt_execute($stmt_status);
                                $res_status = mysqli_stmt_get_result($stmt_status);
                                $row_status = mysqli_fetch_array($res_status, MYSQLI_ASSOC);
                                if ($row_status && !empty($row_status['DENUMIRE'])) {
                                    $status_label = $row_status['DENUMIRE'];
                                }
                                mysqli_stmt_close($stmt_status);
                            }
                            if ($status_label === '') { $status_label = $cod_status; }
                            echo htmlspecialchars($status_label);
                        } else {
                            echo '';
                        }
                        ?></td></tr>
                    <tr><td>Web</td><td><?php echo htmlspecialchars($od['WEB'] ?? '')?></td></tr>
                </tbody>
            </table>
            <?php
            if (!empty($od['DATA_PRELUCRARII']) && $dp = DateTime::createFromFormat('Y-m-d', $od['DATA_PRELUCRARII'])) {
                echo '<p><em>Notă: Datele au fost actualizate la ' . $dp->format('d.m.Y') . '.</em></p>';
            }
            ?>
            <?php } ?>
        </div>

        <div class="tabs-panel" id="panel2">
            <?php
            // helper to retrieve first non-empty value from multiple possible column names
            function _b_get_val($row, $candidates) {
                foreach ($candidates as $k) {
                    if (isset($row[$k]) && $row[$k] !== null && $row[$k] !== '') return $row[$k];
                }
                return null;
            }
            // Present ANAF data using the same mapping and formatting as anaf/fiscalview.php
            if (empty($anaf)) {
                echo '<div class="callout alert">Nu s-au găsit date fiscale ANAF</div>';
            } else {
                $field_labels = [
                    'id' => 'ID intern',
                    'cui' => 'CUI',
                    'data' => 'Data actualizare',
                    'denumire' => 'Denumire firmă',
                    'adresa' => 'Adresă',
                    'nrRegCom' => 'Nr. Reg. Com.',
                    'telefon' => 'Telefon',
                    'fax' => 'Fax',
                    'codPostal' => 'Cod poștal',
                    'act' => 'Act',
                    'stare_inregistrare' => 'Stare înregistrare',
                    'data_inregistrare' => 'Data înregistrare',
                    'cod_CAEN' => 'Cod CAEN',
                    'iban' => 'IBAN',
                    'statusRO_e_Factura' => 'e-Factura activ',
                    'organFiscalCompetent' => 'Organ fiscal',
                    'forma_de_proprietate' => 'Forma de proprietate',
                    'forma_organizare' => 'Forma organizare',
                    'forma_juridica' => 'Forma juridică',
                    'scpTVA' => 'Înregistrare TVA',
                    'data_inceput_ScpTVA' => 'Data început TVA',
                    'data_sfarsit_ScpTVA' => 'Data sfârșit TVA',
                    'data_anul_imp_ScpTVA' => 'Data anulare TVA',
                    'mesaj_ScpTVA' => 'Mesaj TVA',
                    'dataInceputTvaInc' => 'Data început TVA incasare',
                    'dataSfarsitTvaInc' => 'Data sfârșit TVA incasare',
                    'dataActualizareTvaInc' => 'Data actualizare TVA incasare',
                    'dataPublicareTvaInc' => 'Data publicare TVA incasare',
                    'tipActTvaInc' => 'Tip act TVA incasare',
                    'statusTvaIncasare' => 'Status TVA incasare',
                    'dataInactivare' => 'Data inactivare',
                    'dataReactivare' => 'Data reactivare',
                    'dataPublicareInactiv' => 'Data publicare inactiv',
                    'dataRadiere' => 'Data radiere',
                    'statusInactivi' => 'Status inactivi',
                    'domiciliuFiscal' => 'Domiciliu fiscal',
                ];

                $date_fields = [
                    'data', 'data_inregistrare', 'data_inceput_ScpTVA', 'data_sfarsit_ScpTVA', 'data_anul_imp_ScpTVA',
                    'dataInceputTvaInc', 'dataSfarsitTvaInc', 'dataActualizareTvaInc', 'dataPublicareTvaInc',
                    'dataInactivare', 'dataReactivare', 'dataPublicareInactiv', 'dataRadiere'
                ];
                $bool_fields = [
                    'statusRO_e_Factura', 'scpTVA', 'statusTvaIncasare', 'statusInactivi'
                ];

                echo '<table class="stack"><thead><tr><th>Indicator</th><th>Valoare</th></tr></thead><tbody>';
                foreach ($anaf as $field => $value) {
                    // skip internal id and data actualizare (afișat ca notiță sub tabel)
                    if ($field === 'id' || $field === 'data') continue;
                    echo '<tr><td>'.(isset($field_labels[$field])?htmlspecialchars($field_labels[$field]):htmlspecialchars($field)).'</td><td>';
                    if (in_array($field, $date_fields) && !empty($value) && $value !== '0000-00-00') {
                        $d = DateTime::createFromFormat('Y-m-d', $value);
                        echo $d ? $d->format('d.m.Y') : htmlspecialchars((string)$value);
                    } elseif (in_array($field, $bool_fields)) {
                        if ($value === '1' || $value === 1) { echo 'Da'; }
                        elseif ($value === '0' || $value === 0) { echo 'Nu'; }
                        else { echo htmlspecialchars((string)$value); }
                    } elseif ($field === 'cod_CAEN' && !empty($value)) {
                        $caen_den_anaf = '';
                        $caen_ver_anaf = null;
                        $nr_reg_com = $anaf['nrRegCom'] ?? '';
                        if (!empty($nr_reg_com)) {
                            $stmt_cv = mysqli_prepare($conn, "SELECT VER_CAEN_AUTORIZAT FROM od_caen_autorizat WHERE COD_INMATRICULARE = ? AND COD_CAEN_AUTORIZAT = ? LIMIT 1");
                            if ($stmt_cv) {
                                mysqli_stmt_bind_param($stmt_cv, 'ss', $nr_reg_com, $value);
                                mysqli_stmt_execute($stmt_cv);
                                $res_cv = mysqli_stmt_get_result($stmt_cv);
                                $row_cv = mysqli_fetch_array($res_cv, MYSQLI_ASSOC);
                                $caen_ver_anaf = $row_cv ? trim($row_cv['VER_CAEN_AUTORIZAT']) : null;
                                mysqli_stmt_close($stmt_cv);
                            }
                        }
                        if ($caen_ver_anaf == 3) {
                            $stmt_cdn_a = mysqli_prepare($conn, "SELECT Denumire FROM generale_coduri_caen_v3 WHERE Clasa = ? LIMIT 1");
                            if ($stmt_cdn_a) {
                                mysqli_stmt_bind_param($stmt_cdn_a, 's', $value);
                                mysqli_stmt_execute($stmt_cdn_a);
                                $res_cdn_a = mysqli_stmt_get_result($stmt_cdn_a);
                                $row_cdn_a = mysqli_fetch_array($res_cdn_a, MYSQLI_ASSOC);
                                $caen_den_anaf = $row_cdn_a ? $row_cdn_a['Denumire'] : '';
                                mysqli_stmt_close($stmt_cdn_a);
                            }
                        } elseif ($caen_ver_anaf == 2 || $caen_ver_anaf === null || ($caen_ver_anaf !== null && $caen_ver_anaf != 1)) {
                            $stmt_cdn_a = mysqli_prepare($conn, "SELECT Denumire FROM generale_coduri_caen_v2 WHERE Clasa = ? LIMIT 1");
                            if ($stmt_cdn_a) {
                                mysqli_stmt_bind_param($stmt_cdn_a, 's', $value);
                                mysqli_stmt_execute($stmt_cdn_a);
                                $res_cdn_a = mysqli_stmt_get_result($stmt_cdn_a);
                                $row_cdn_a = mysqli_fetch_array($res_cdn_a, MYSQLI_ASSOC);
                                $caen_den_anaf = $row_cdn_a ? $row_cdn_a['Denumire'] : '';
                                mysqli_stmt_close($stmt_cdn_a);
                            }
                        }
                        echo htmlspecialchars($value);
                        if ($caen_den_anaf !== '') {
                            echo ' - ' . htmlspecialchars($caen_den_anaf);
                        }
                    } else {
                        echo htmlspecialchars((string)$value);
                    }
                    echo '</td></tr>';
                }
                echo '</tbody></table>';
                if (!empty($anaf['data']) && $anaf['data'] !== '0000-00-00') {
                    $d_anaf = DateTime::createFromFormat('Y-m-d', $anaf['data']);
                    if ($d_anaf) {
                        echo '<p><em>Notă: Datele au fost actualizate la ' . $d_anaf->format('d.m.Y') . '.</em></p>';
                    }
                }
            }
            ?>
        </div>

        <div class="tabs-panel" id="panel3">
            <?php
            // Collect bilanțuri into array so we can both render table and chart
            $bilanturi = [];
            while ($b = ezpub_fetch_array($res3)) {
                $bilanturi[] = $b;
            }
            if (count($bilanturi) == 0) {
                echo '<div class="callout alert">Nu s-au găsit bilanțuri</div>';
            } else {
                // Render table (most recent first)
                echo '<table class="stack"><thead><tr><th>An</th><th style="text-align:right">Cifră afaceri</th><th style="text-align:right">Cheltuieli totale</th><th style="text-align:right">Active circulante</th><th style="text-align:right">Active imobilizate</th><th style="text-align:right">Capitaluri total</th><th style="text-align:right">Datorii</th><th style="text-align:right">Profit/Pierdere</th><th style="text-align:right">Angajați</th></tr></thead><tbody>';
                foreach ($bilanturi as $b) {
                    $val = 0;
                    if (!empty($b['profit_net']) && $b['profit_net']!=0) { $val = $b['profit_net']; }
                    else { $val = -1 * intval($b['pierdere_neta'] ?? 0); }
                    echo '<tr>';
                    echo '<td>'.htmlspecialchars($b['an']).'</td>';
                    // try multiple candidate keys and fall back to raw value if formatting yields empty
                    $v_ca = _b_get_val($b, ['cifra_afaceri_net','cifra_afaceri']);
                    $fmt_ca = romanize_int($v_ca);
                    $neg_ca = ($v_ca !== null && $v_ca !== '' && ((float)$v_ca) < 0);
                    echo '<td style="text-align:right"'.($neg_ca? ' class="loss"':'').'>'.($fmt_ca !== '' ? $fmt_ca : htmlspecialchars((string)($v_ca ?? '-'))).'</td>';

                    $v_ch = _b_get_val($b, ['cheltuieli_totale','cheltuieli_total','cheltuieli']);
                    $fmt_ch = romanize_int($v_ch);
                    $neg_ch = ($v_ch !== null && $v_ch !== '' && ((float)$v_ch) < 0);
                    echo '<td style="text-align:right"'.($neg_ch? ' class="loss"':'').'>'.($fmt_ch !== '' ? $fmt_ch : htmlspecialchars((string)($v_ch ?? '-'))).'</td>';

                    $v_ac = _b_get_val($b, ['active_circulante','active_circulante_total','active_circ']);
                    $fmt_ac = romanize_int($v_ac);
                    $neg_ac = ($v_ac !== null && $v_ac !== '' && ((float)$v_ac) < 0);
                    echo '<td style="text-align:right"'.($neg_ac? ' class="loss"':'').'>'.($fmt_ac !== '' ? $fmt_ac : htmlspecialchars((string)($v_ac ?? '-'))).'</td>';

                    $v_ai = _b_get_val($b, ['active_imobilizate','active_imobilizate_total','active_imob']);
                    $fmt_ai = romanize_int($v_ai);
                    $neg_ai = ($v_ai !== null && $v_ai !== '' && ((float)$v_ai) < 0);
                    echo '<td style="text-align:right"'.($neg_ai? ' class="loss"':'').'>'.($fmt_ai !== '' ? $fmt_ai : htmlspecialchars((string)($v_ai ?? '-'))).'</td>';

                    $v_cap = _b_get_val($b, ['capitaluri_total','capitaluri','capital_propriu']);
                    $fmt_cap = romanize_int($v_cap);
                    $neg_cap = ($v_cap !== null && $v_cap !== '' && ((float)$v_cap) < 0);
                    echo '<td style="text-align:right"'.($neg_cap? ' class="loss"':'').'>'.($fmt_cap !== '' ? $fmt_cap : htmlspecialchars((string)($v_cap ?? '-'))).'</td>';

                    $v_d = _b_get_val($b, ['datorii','datorii_total']);
                    $fmt_d = romanize_int($v_d);
                    $neg_d = ($v_d !== null && $v_d !== '' && ((float)$v_d) < 0);
                    echo '<td style="text-align:right"'.($neg_d? ' class="loss"':'').'>'.($fmt_d !== '' ? $fmt_d : htmlspecialchars((string)($v_d ?? '-'))).'</td>';

                    $fmt_val = romanize_int($val);
                    $neg_val = ($val !== null && $val !== '' && ((float)$val) < 0);
                    echo '<td style="text-align:right"'.($neg_val? ' class="loss"':'').'>'.($fmt_val !== '' ? $fmt_val : htmlspecialchars((string)($val ?? '-'))).'</td>';
                    echo '<td style="text-align:right">'.htmlspecialchars($b['numar_salariati']).'</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';

                // Prepare data for chart (order ascending by year)
                $bilanturi_cresc = array_reverse($bilanturi);
                $ani = array_map(function($r){ return $r['an']; }, $bilanturi_cresc);
                $comparatii = [
                    'cifra_afaceri_net' => 'Cifră afaceri',
                    'cheltuieli_totale' => 'Cheltuieli totale',
                    'active_circulante' => 'Active circulante',
                    'active_imobilizate' => 'Active imobilizate',
                    'capitaluri_total' => 'Capitaluri total',
                    'datorii' => 'Datorii',
                    'profit_pierdere_net' => 'Profit/Pierdere netă',
                    'numar_salariati' => 'Număr angajați'
                ];
                $chart_data = [];
                foreach ($comparatii as $col => $den) {
                    $vals = [];
                    foreach ($bilanturi_cresc as $row) {
                        if ($col == 'profit_pierdere_net') {
                            $v = (!empty($row['profit_net']) && $row['profit_net']!=0) ? $row['profit_net'] : -1 * intval($row['pierdere_neta'] ?? 0);
                        } else {
                            $v = isset($row[$col]) ? $row[$col] : 0;
                        }
                        $vals[] = (is_numeric($v) ? (int)$v : 0);
                    }
                    $chart_data[] = ['label' => $den, 'data' => $vals];
                }

                // Output chart controls, canvas and JS (Chart.js) with dynamic selection and localStorage
                echo '<h3>Grafic evoluție indicatori cheie</h3>';
                // Checkbox controls
                echo '<div id="bilant-controls" style="margin-bottom:0.5em;">';
                $default_keys = array_column($chart_data, 'label'); // not used directly, we'll map keys below
                // Build mapping of keys -> labels and data for JS
                $js_chart_objs = [];
                foreach ($comparatii as $col => $den) {
                    // find corresponding dataset in $chart_data
                    $found = null;
                    foreach ($chart_data as $ds) {
                        if ($ds['label'] === $den || stripos($ds['label'], $den) !== false) { $found = $ds; break; }
                    }
                    $id = $col;
                    $checked = in_array($col, array_keys($comparatii)) ? 'checked' : '';
                    echo '<label style="margin-right:1em;"><input type="checkbox" class="bilant-chk" data-key="'.htmlspecialchars($id).'" '.$checked.'> '.htmlspecialchars($den).'</label>';
                }
                echo '</div>';
                echo '<canvas id="bilantChart" width="800" height="300"></canvas>';
                // Prepare JS data structure
                $ani_json = json_encode($ani);
                $datasets_js = [];
                $colors = ["#007bff","#28a745","#dc3545","#ffc107","#6610f2"];
                $i = 0;
                foreach ($comparatii as $col => $den) {
                    $vals = [];
                    foreach ($bilanturi_cresc as $row) {
                        if ($col == 'profit_pierdere_net') {
                            $v = (!empty($row['profit_net']) && $row['profit_net']!=0) ? $row['profit_net'] : -1 * intval($row['pierdere_neta'] ?? 0);
                        } else {
                            $v = isset($row[$col]) ? $row[$col] : 0;
                        }
                        $vals[] = (is_numeric($v) ? (int)$v : 0);
                    }
                    $datasets_js[] = [
                        'key' => $col,
                        'label' => $den,
                        'data' => $vals,
                        'color' => $colors[$i % count($colors)]
                    ];
                    $i++;
                }
                $datasets_json = json_encode($datasets_js);
                echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
                echo '<script>';
                echo 'const bilantAni = '.$ani_json.';';
                echo 'const bilantDatasetsAll = '.$datasets_json.';';
                echo 'function getSavedKeys(){ try { const s = localStorage.getItem("bilant_selected"); return s?JSON.parse(s):["cifra_afaceri_net","cheltuieli_total","active_circulante","active_imobilizate","capitaluri_total","datorii"]; } catch(e){ return ["cifra_afaceri_net","cheltuieli_total","active_circulante","active_imobilizate","capitaluri_total","datorii"]; }}';
                echo 'function saveKeys(keys){ try{ localStorage.setItem("bilant_selected", JSON.stringify(keys)); }catch(e){} }';
                echo 'function buildDatasets(keys){ return bilantDatasetsAll.filter(d=>keys.indexOf(d.key)!==-1).map(d=>({ label:d.label, data:d.data, borderColor:d.color, backgroundColor:d.color, fill:false, tension:0.2 })); }';
                echo 'let bilantChart=null; function renderChart(){ const keys = Array.from(document.querySelectorAll(".bilant-chk:checked")).map(i=>i.getAttribute("data-key")); saveKeys(keys); const data = { labels: bilantAni, datasets: buildDatasets(keys) }; if(bilantChart){ bilantChart.data = data; bilantChart.update(); } else { const ctx = document.getElementById("bilantChart").getContext("2d"); bilantChart = new Chart(ctx,{ type:"line", data:data, options:{ responsive:true, plugins:{ legend:{ position:"top" } }, scales:{ y:{ beginAtZero:false, ticks:{ callback:function(value){ return value.toLocaleString(); } } } } } }); } }';
                echo 'document.addEventListener("DOMContentLoaded", function(){ const saved = getSavedKeys(); document.querySelectorAll(".bilant-chk").forEach(function(cb){ const key = cb.getAttribute("data-key"); if(saved.indexOf(key)!==-1){ cb.checked = true; } else { cb.checked = false; } cb.addEventListener("change", renderChart); }); renderChart(); });';
                echo '</script>';
            }
            ?>
        </div>
      <!-- analiză financiară -->
        <div class="tabs-panel" id="panel5">
        <?php include __DIR__ . '/../common/panel_analiza_financiara.php'; ?>
        </div>
       <!-- istoric firmă --> 
        <?php if (count($od_all) > 1): ?>
        <div class="tabs-panel" id="panel6">
            <p>Această secțiune cuprinde istoricul firmei înregistrate în baza de date, adică toate înregistrările găsite la Registrul Comerțului care au același cod CUI. 
                Acest lucru poate fi util pentru a vedea evoluția firmei în timp, eventuale schimbări de denumire, sediu social în alt județ.</p>
            <table class="stack">
                <thead>
                    <tr>
                        <th>Denumire</th>
                        <th>Cod înmatriculare</th>
                        <th>CUI</th>
                        <th>Județ</th>
                        <th>Localitate</th>
                        <th>Stradă nr.</th>
                        <th>Cod status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($od_all as $od_row):
                    $cod_status_row = isset($od_row['COD_STATUS']) ? $od_row['COD_STATUS'] : '';
                    $status_label_row = $cod_status_row;
                    if ($cod_status_row !== '') {
                        $stmt_sr = mysqli_prepare($conn, "SELECT DENUMIRE FROM od_stare_firma WHERE COD = ? LIMIT 1");
                        if ($stmt_sr) {
                            mysqli_stmt_bind_param($stmt_sr, 's', $cod_status_row);
                            mysqli_stmt_execute($stmt_sr);
                            $res_sr = mysqli_stmt_get_result($stmt_sr);
                            $row_sr = mysqli_fetch_array($res_sr, MYSQLI_ASSOC);
                            if ($row_sr && !empty($row_sr['DENUMIRE'])) { $status_label_row = $row_sr['DENUMIRE']; }
                            mysqli_stmt_close($stmt_sr);
                        }
                    }
                    $strada_nr = trim(($od_row['ADR_DEN_STRADA'] ?? '') . ' ' . ($od_row['ADR_NR_STRADA'] ?? ''));
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($od_row['DENUMIRE'] ?? '') ?></td>
                        <td><?php echo htmlspecialchars($od_row['COD_INMATRICULARE'] ?? '') ?></td>
                        <td><?php echo htmlspecialchars($od_row['CUI'] ?? '') ?></td>
                        <td><?php echo htmlspecialchars($od_row['ADR_JUDET'] ?? '') ?></td>
                        <td><?php echo htmlspecialchars($od_row['ADR_LOCALITATE'] ?? '') ?></td>
                        <td><?php echo htmlspecialchars($strada_nr) ?></td>
                        <td><?php echo htmlspecialchars($status_label_row) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div class="tabs-panel" id="panel4">
            <?php
                // Include ministerul justitiei query helper
                // Prefer the ANAF/fiscal name (clienti_date_fiscale.denumire) as it's more consistent
                $Client_Denumire = '';
                if (!empty($anaf['denumire'])) {
                    $Client_Denumire = $anaf['denumire'];
                } elseif (!empty($od['DENUMIRE'])) {
                    $Client_Denumire = $od['DENUMIRE'];
                }
                // Debug removed: production behavior uses minimal normalization inside just_query.php
                include dirname(__FILE__) . '/../common/just_query.php';
                // If included, just_query will run a search using $Client_Denumire and render part of UI
            ?>
        </div>
    </div>

    <?php endif; // end if cui provided ?>

</div>

<?php
include '../bottom.php';
?>
