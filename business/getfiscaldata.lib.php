<?php
/**
 * Fetch fiscal data from ANAF and store into the local `clienti_date_fiscale` table.
 * This variant is simplified for business/ usage and does NOT update any parent table.
 *
 * @param string $cui
 * @param mysqli $conn
 * @return bool|null  true on success / already up-to-date, null on failure
 */
function getFiscalDataByCUI_business($cui, $conn) {
    $target_table = 'clienti_date_fiscale';

    // Normalize CUI to digits
    $raw_cui = $cui;
    $cui = preg_replace('/[^0-9]/', '', $cui);
    if (empty($cui) || !is_numeric($cui)) return null;

    // Check existing data date and skip if newer than 3 months
    $stmt_check_sql = "SELECT `data` FROM {$target_table} WHERE cui = ? LIMIT 1";
    $stmt_check = mysqli_prepare($conn, $stmt_check_sql);
    if ($stmt_check) {
        mysqli_stmt_bind_param($stmt_check, "s", $raw_cui);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_bind_result($stmt_check, $data_existenta);
        $exista = mysqli_stmt_fetch($stmt_check);
        mysqli_stmt_close($stmt_check);
        if ($exista && $data_existenta) {
            $data_existenta_date = DateTime::createFromFormat('Y-m-d', $data_existenta);
            $acum = new DateTime();
            if ($data_existenta_date) {
                $interval = $acum->diff($data_existenta_date);
                if ($interval->y == 0 && $interval->m < 3 && $interval->invert == 0) {
                    // Up-to-date
                    return true;
                }
            }
        }
    }

    $postfields = [];
    $url = 'https://webservicesp.anaf.ro/api/PlatitorTvaRest/v9/tva';
    $header = ["Content-Type: application/json"];
    $postfields[] = ['cui' => $cui, 'data' => date('Y-m-d')];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 130);
    curl_setopt($ch, CURLOPT_TIMEOUT, 130);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) return null;
    $json = json_decode($response, true);
    if (!$json || !isset($json['found'][0]['date_generale'])) return null;

    $dg = $json['found'][0]['date_generale'];
    $tva = $json['found'][0]['inregistrare_scop_Tva'] ?? [];
    $adresa_domiciliu = $json['found'][0]['adresa_domiciliu_fiscal'] ?? [];
    $domiciliuFiscal = '';
    if (!empty($adresa_domiciliu) && is_array($adresa_domiciliu)) {
        $domiciliuFiscal = implode(', ', array_filter([
            $adresa_domiciliu['ddenumire_Strada'] ?? '',
            $adresa_domiciliu['dnumar_Strada'] ?? '',
            $adresa_domiciliu['ddetalii_Adresa'] ?? '',
            $adresa_domiciliu['ddenumire_Localitate'] ?? '',
            $adresa_domiciliu['ddenumire_Judet'] ?? '',
            $adresa_domiciliu['dcod_Postal'] ?? '',
            $adresa_domiciliu['dtara'] ?? ''
        ]));
    }

    $perioada = [];
    if (isset($tva['perioade_TVA']) && is_array($tva['perioade_TVA']) && count($tva['perioade_TVA']) > 0) {
        $perioada = $tva['perioade_TVA'][0];
    }
    $rtvai = [
        'dataInceputTvaInc' => null,
        'dataSfarsitTvaInc' => null,
        'tipActTvaInc' => '',
        'statusTvaIncasare' => 0,
        'dataActualizareTvaInc' => null,
        'dataPublicareTvaInc' => null
    ];
    if (isset($json['found'][0]['inregistrare_RTVAI'])) {
        $rtvai_json = $json['found'][0]['inregistrare_RTVAI'];
        if (is_array($rtvai_json) && isset($rtvai_json[0])) {
            $rtvai = array_merge($rtvai, $rtvai_json[0]);
        } else {
            $rtvai = array_merge($rtvai, $rtvai_json);
        }
    }

    $stare_inactiv = $tva['stare_inactiv'] ?? [];
    $codPostal = $dg['codPostal'] ?? '';
    if (preg_match('/^\d{5}$/', $codPostal)) {
        $codPostal = '0' . $codPostal;
    }

    $data = [
        'cui' => $dg['cui'] ?? '',
        'data' => $dg['data'] ?? null,
        'denumire' => $dg['denumire'] ?? '',
        'adresa' => $dg['adresa'] ?? '',
        'nrRegCom' => $dg['nrRegCom'] ?? '',
        'telefon' => $dg['telefon'] ?? '',
        'fax' => $dg['fax'] ?? '',
        'codPostal' => $codPostal,
        'act' => $dg['act'] ?? '',
        'stare_inregistrare' => $dg['stare_inregistrare'] ?? '',
        'data_inregistrare' => $dg['data_inregistrare'] ?? null,
        'cod_CAEN' => $dg['cod_CAEN'] ?? '',
        'iban' => $dg['iban'] ?? '',
        'statusRO_e_Factura' => ($dg['statusRO_e_Factura'] ?? false) ? 1 : 0,
        'organFiscalCompetent' => $dg['organFiscalCompetent'] ?? '',
        'forma_de_proprietate' => $dg['forma_de_proprietate'] ?? '',
        'forma_organizare' => $dg['forma_organizare'] ?? '',
        'forma_juridica' => $dg['forma_juridica'] ?? '',
        'scpTVA' => ($tva['scpTVA'] ?? false) ? 1 : 0,
        'data_inceput_ScpTVA' => (!empty($perioada['data_inceput_ScpTVA'])) ? $perioada['data_inceput_ScpTVA'] : null,
        'data_sfarsit_ScpTVA' => (!empty($perioada['data_sfarsit_ScpTVA'])) ? $perioada['data_sfarsit_ScpTVA'] : null,
        'data_anul_imp_ScpTVA' => (!empty($perioada['data_anul_imp_ScpTVA'])) ? $perioada['data_anul_imp_ScpTVA'] : null,
        'mesaj_ScpTVA' => $perioada['mesaj_ScpTVA'] ?? '',
        'dataInceputTvaInc' => (!empty($rtvai['dataInceputTvaInc'])) ? $rtvai['dataInceputTvaInc'] : null,
        'dataSfarsitTvaInc' => (!empty($rtvai['dataSfarsitTvaInc'])) ? $rtvai['dataSfarsitTvaInc'] : null,
        'tipActTvaInc' => isset($rtvai['tipActTvaInc']) ? $rtvai['tipActTvaInc'] : '',
        'statusTvaIncasare' => (isset($rtvai['statusTvaIncasare']) && $rtvai['statusTvaIncasare']) ? 1 : 0,
        'dataActualizareTvaInc' => (!empty($rtvai['dataActualizareTvaInc'])) ? $rtvai['dataActualizareTvaInc'] : null,
        'dataPublicareTvaInc' => (!empty($rtvai['dataPublicareTvaInc'])) ? $rtvai['dataPublicareTvaInc'] : null,
        'dataInactivare' => $stare_inactiv['dataInactivare'] ?? null,
        'dataReactivare' => $stare_inactiv['dataReactivare'] ?? null,
        'dataPublicareInactiv' => $stare_inactiv['dataPublicare'] ?? null,
        'dataRadiere' => $stare_inactiv['dataRadiere'] ?? null,
        'statusInactivi' => ($stare_inactiv['statusInactivi'] ?? false) ? 1 : 0,
        'domiciliuFiscal' => $domiciliuFiscal,
    ];

    // Insert/update into target table
    $cols = array_keys($data);
    $placeholders = implode(',', array_fill(0, count($cols), '?'));
    $sql = 'INSERT INTO ' . $target_table . ' (' . implode(',', $cols) . ') VALUES (' . $placeholders . ') ON DUPLICATE KEY UPDATE ' . implode(',', array_map(function($c){return "$c=VALUES($c)";}, $cols));
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return null;
    $types = '';
    foreach ($cols as $col) {
        $types .= (strpos($col, 'data') !== false) ? 's' : (in_array($col, ['statusRO_e_Factura','scpTVA','statusTvaIncasare','statusInactivi']) ? 'i' : 's');
    }
    $values = array_values($data);
    mysqli_stmt_bind_param($stmt, $types, ...$values);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return true;
}
