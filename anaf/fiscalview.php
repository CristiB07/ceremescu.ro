<?php
include_once '../settings.php';
include_once '../classes/common.php';

// Determine target table (allows usage for sales prospects)
$target_table = isset($_GET['target_table']) ? preg_replace('/[^A-Za-z0-9_]/', '', $_GET['target_table']) : 'clienti_date_fiscale';
$parent_table = isset($_GET['parent_table']) ? preg_replace('/[^A-Za-z0-9_]/', '', $_GET['parent_table']) : 'clienti_date';

// Preia toate coloanele din structura tabelului selectat
$query = "DESCRIBE {$target_table}";
$result = ezpub_query($conn, $query);
$fields = [];
while ($row = ezpub_fetch_array($result)) {
    $fields[] = $row['Field'];
}


// Preia CUI din parametru GET (setat de clientprofile.php)
$cui = isset($_GET['cui']) ? $_GET['cui'] : '';
$sample = [];
if ($cui) {
    $query2 = "SELECT * FROM {$target_table} WHERE cui = '".mysqli_real_escape_string($conn, $cui)."' LIMIT 1";
    $result2 = ezpub_query($conn, $query2);
    $sample = ezpub_fetch_array($result2);

    // If no local record found, attempt to fetch from ANAF and insert into DB
    if (empty($sample)) {
        include_once __DIR__ . '/getfiscaldata.lib.php';
        if (function_exists('getFiscalDataByCUI')) {
            // Attempt to fetch and store data; ignore return value but reload afterwards
            getFiscalDataByCUI($cui, $conn, $target_table, $parent_table);
            // Re-query after attempted import
            $result2 = ezpub_query($conn, $query2);
            $sample = ezpub_fetch_array($result2);
        }
    }
}

// Dacă există date și data este mai veche de 3 luni, actualizează automat
if (!empty($sample) && !empty($sample['data'])) {
    $dataFiscal = $sample['data'];
    $dateObj = DateTime::createFromFormat('Y-m-d', $dataFiscal);
    if ($dateObj) {
        $now = new DateTime();
        $interval = $now->diff($dateObj);
        if ($interval->m + 12 * $interval->y >= 3) {
            // Include scriptul de actualizare fiscală (doar dacă există CUI)
            if (!empty($cui)) {
                include_once __DIR__ . '/getfiscaldata.lib.php';
                if (function_exists('getFiscalDataByCUI')) {
                    // Pass the selected target and parent tables so sales side can store separately
                    getFiscalDataByCUI($cui, $conn, $target_table, $parent_table);
                    // Reîncarcă datele după actualizare
                    $query2 = "SELECT * FROM {$target_table} WHERE cui = '".mysqli_real_escape_string($conn, $cui)."' LIMIT 1";
                    $result2 = ezpub_query($conn, $query2);
                    $sample = ezpub_fetch_array($result2);
                }
            }
        }
    }
}
// Mapping pentru denumiri indicatori (modifică valorile după preferință)
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
    'forma_organizare' => 'Forma de organizare',
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

// Tabel vizualizare structură
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 cell">
        <h3>Date fiscale ANAF pentru CUI: <?php echo htmlspecialchars($cui); ?></h3>
        <table width="50%">
            <thead>
                <tr>
                    <th>Indicator</th>
                    <th>Valoare</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($sample)) {
                foreach ($sample as $field => $value): 
                    if ($field === 'id' || $field === 'statusRO_e_Factura') continue; ?>
                    <tr>
                        <td><?php echo isset($field_labels[$field]) ? htmlspecialchars($field_labels[$field]) : htmlspecialchars($field); ?></td>
                        <td>
                        <?php
                        // Listează câmpurile de tip dată din structura SQL
                        $date_fields = [
                            'data', 'data_inregistrare', 'data_inceput_ScpTVA', 'data_sfarsit_ScpTVA', 'data_anul_imp_ScpTVA',
                            'dataInceputTvaInc', 'dataSfarsitTvaInc', 'dataActualizareTvaInc', 'dataPublicareTvaInc',
                            'dataInactivare', 'dataReactivare', 'dataPublicareInactiv', 'dataRadiere'
                        ];
                        $bool_fields = [
                            'statusRO_e_Factura', 'scpTVA', 'statusTvaIncasare', 'statusInactivi'
                        ];
                        if (in_array($field, $date_fields) && !empty($value) && $value !== '0000-00-00') {
                            $d = DateTime::createFromFormat('Y-m-d', $value);
                            echo $d ? $d->format('d.m.Y') : htmlspecialchars((string)$value);
                        } elseif (in_array($field, $bool_fields)) {
                            if ($value === '1' || $value === 1) {
                                echo 'Da';
                            } elseif ($value === '0' || $value === 0) {
                                echo 'Nu';
                            } else {
                                echo htmlspecialchars((string)$value);
                            }
                        } else {
                            echo htmlspecialchars((string)$value);
                        }
                        ?>
                        </td>
                    </tr>
                <?php endforeach;
            } else { ?>
                <tr><td colspan="2">Nu există date fiscale pentru acest CUI.</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
