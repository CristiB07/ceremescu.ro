<?php
include '../settings.php';
include '../classes/common.php';

$strPageTitle = "Căutare Dosare JUST";

// Detect if this file is included by another script (e.g. clientprofile.php)
$is_included = (realpath(__FILE__) !== realpath($_SERVER['SCRIPT_FILENAME']));
if (!$is_included) {
    include '../header.php';
}
/*
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['userlogedin']) || $_SESSION['userlogedin'] != "Yes") {
    header("location:$strSiteURL/login/index.php?message=MLF");
    die;
}
*/

// Lista tuturor instituțiilor
$institutii = [
    'CurteadeApelBUCURESTI', 'TribunalulBUCURESTI', 'JudecatoriaSECTORUL4BUCURESTI', 'TribunalulTIMIS', 'CurteadeApelBACAU', 'CurteadeApelCLUJ', 'CurteadeApelORADEA', 'CurteadeApelCONSTANTA', 'CurteadeApelSUCEAVA', 'TribunalulBOTOSANI', 'CurteadeApelPLOIESTI', 'CurteadeApelTARGUMURES', 'CurteadeApelGALATI', 'CurteadeApelIASI', 'CurteadeApelPITESTI', 'CurteadeApelCRAIOVA', 'JudecatoriaARAD', 'CurteadeApelALBAIULIA', 'CurteadeApelTIMISOARA', 'TribunalulBRASOV', 'TribunalulDOLJ', 'CurteadeApelBRASOV', 'CurteaMilitaradeApelBUCURESTI', 'TribunalulSATUMARE', 'TribunalulSALAJ', 'TribunalulSIBIU', 'TribunalulSUCEAVA', 'TribunalulTELEORMAN', 'TribunalulTULCEA', 'TribunalulVASLUI', 'TribunalulVALCEA', 'TribunalulVRANCEA', 'TribunalulMilitarBUCURESTI', 'TribunalulILFOV', 'JudecatoriaBUFTEA', 'TribunalulGORJ', 'TribunalulHARGHITA', 'TribunalulHUNEDOARA', 'TribunalulIALOMITA', 'TribunalulIASI', 'TribunalulMARAMURES', 'TribunalulMEHEDINTI', 'TribunalulMURES', 'TribunalulNEAMT', 'TribunalulOLT', 'TribunalulPRAHOVA', 'TribunalulALBA', 'TribunalulARAD', 'TribunalulARGES', 'TribunalulBACAU', 'TribunalulBIHOR', 'TribunalulBISTRITANASAUD', 'TribunalulBRAILA', 'TribunalulBUZAU', 'TribunalulCARASSEVERIN', 'TribunalulCALARASI', 'TribunalulCLUJ', 'TribunalulCONSTANTA', 'TribunalulCOVASNA', 'TribunalulDAMBOVITA', 'TribunalulGALATI', 'TribunalulGIURGIU', 'JudecatoriaADJUD', 'JudecatoriaAGNITA', 'JudecatoriaAIUD', 'JudecatoriaALBAIULIA', 'JudecatoriaALESD', 'JudecatoriaBABADAG', 'JudecatoriaBACAU', 'JudecatoriaBAIADEARAMA', 'JudecatoriaBAIAMARE', 'JudecatoriaBAILESTI', 'JudecatoriaBALS', 'JudecatoriaBALCESTI', 'JudecatoriaBECLEAN', 'JudecatoriaBEIUS', 'JudecatoriaBICAZ', 'JudecatoriaBARLAD', 'JudecatoriaBISTRITA', 'JudecatoriaBLAJ', 'JudecatoriaBOLINTINVALE', 'JudecatoriaBOTOSANI', 'JudecatoriaBOZOVICI', 'JudecatoriaBRAD', 'JudecatoriaBRAILA', 'JudecatoriaBRASOV', 'JudecatoriaBREZOI', 'JudecatoriaBUHUSI', 'JudecatoriaBUZAU', 'JudecatoriaCALAFAT', 'JudecatoriaCALARASI', 'JudecatoriaCAMPENI', 'JudecatoriaCAMPINA', 'JudecatoriaCAMPULUNG', 'JudecatoriaCAMPULUNGMOLDOVENESC', 'JudecatoriaCARACAL', 'JudecatoriaCARANSEBES', 'JudecatoriaCHISINEUCRIS', 'JudecatoriaCLUJNAPOCA', 'JudecatoriaCONSTANTA', 'JudecatoriaCORABIA', 'JudecatoriaCOSTESTI', 'JudecatoriaCRAIOVA', 'JudecatoriaCURTEADEARGES', 'JudecatoriaDarabani', 'JudecatoriaCAREI', 'JudecatoriaDEJ', 'JudecatoriaDETA', 'JudecatoriaDEVA', 'JudecatoriaDOROHOI', 'JudecatoriaDRAGASANI', 'JudecatoriaDRAGOMIRESTI', 'JudecatoriaDROBETATURNUSEVERIN', 'JudecatoriaFAGARAS', 'JudecatoriaFALTICENI', 'JudecatoriaFAUREI', 'JudecatoriaFETESTI', 'JudecatoriaFILIASI', 'JudecatoriaFOCSANI', 'JudecatoriaGAESTI', 'JudecatoriaGALATI', 'JudecatoriaGHEORGHENI', 'JudecatoriaGHERLA', 'JudecatoriaGIURGIU', 'JudecatoriaGURAHUMORULUI', 'JudecatoriaGURAHONT', 'JudecatoriaHARLAU', 'JudecatoriaHATEG', 'JudecatoriaHOREZU', 'JudecatoriaHUEDIN', 'JudecatoriaHUNEDOARA', 'JudecatoriaHUSI', 'JudecatoriaIASI', 'JudecatoriaINEU', 'JudecatoriaINSURATEI', 'JudecatoriaINTORSURABUZAULUI', 'JudecatoriaLEHLIUGARA', 'JudecatoriaLIPOVA', 'JudecatoriaLUDUS', 'JudecatoriaLUGOJ', 'JudecatoriaMACIN', 'JudecatoriaMANGALIA', 'JudecatoriaMARGHITA', 'JudecatoriaMEDGIDIA', 'JudecatoriaMEDIAS', 'JudecatoriaMIERCUREACIUC', 'JudecatoriaMIZIL', 'JudecatoriaMOINESTI', 'JudecatoriaMOLDOVANOUA', 'JudecatoriaMORENI', 'JudecatoriaMOTRU', 'JudecatoriaMURGENI', 'JudecatoriaNASAUD', 'JudecatoriaNEGRESTIOAS', 'JudecatoriaNOVACI', 'JudecatoriaODORHEIULSECUIESC', 'JudecatoriaOLTENITA', 'JudecatoriaONESTI', 'JudecatoriaORADEA', 'JudecatoriaORASTIE', 'JudecatoriaORAVITA', 'JudecatoriaORSOVA', 'JudecatoriaPANCIU', 'JudecatoriaPATARLAGELE', 'JudecatoriaPETROSANI', 'JudecatoriaPIATRANEAMT', 'JudecatoriaPITESTI', 'JudecatoriaPLOIESTI', 'JudecatoriaPOGOANELE', 'JudecatoriaPUCIOASA', 'JudecatoriaRACARI', 'JudecatoriaRADAUTI', 'JudecatoriaRADUCANENI', 'JudecatoriaRAMNICUSARAT', 'JudecatoriaRAMNICUVALCEA', 'JudecatoriaREGHIN', 'JudecatoriaRESITA', 'JudecatoriaROMAN', 'JudecatoriaROSIORIDEVEDE', 'JudecatoriaRUPEA', 'JudecatoriaSALISTE', 'JudecatoriaSANNICOLAULMARE', 'JudecatoriaSATUMARE', 'JudecatoriaSAVENI', 'JudecatoriaSEBES', 'JudecatoriaSECTORUL1BUCURESTI', 'JudecatoriaSECTORUL2BUCURESTI', 'JudecatoriaSECTORUL3BUCURESTI', 'JudecatoriaSECTORUL5BUCURESTI', 'JudecatoriaSECTORUL6BUCURESTI', 'JudecatoriaSEGARCEA', 'JudecatoriaSFANTUGHEORGHE', 'JudecatoriaSIBIU', 'JudecatoriaSIGHETUMARMATIEI', 'JudecatoriaSIGHISOARA', 'JudecatoriaSIMLEULSILVANIEI', 'JudecatoriaSINAIA', 'JudecatoriaSLATINA', 'JudecatoriaSLOBOZIA', 'JudecatoriaSTREHAIA', 'JudecatoriaSUCEAVA', 'JudecatoriaTARGOVISTE', 'JudecatoriaTARGUBUJOR', 'JudecatoriaTARGUCARBUNESTI', 'JudecatoriaTARGUJIU', 'JudecatoriaTARGULAPUS', 'JudecatoriaTARGUMURES', 'JudecatoriaTARGUNEAMT', 'JudecatoriaTARGUSECUIESC', 'JudecatoriaTARNAVENI', 'JudecatoriaTECUCI', 'JudecatoriaTIMISOARA', 'JudecatoriaTOPLITA', 'JudecatoriaTULCEA', 'JudecatoriaTURDA', 'JudecatoriaTURNUMAGURELE', 'JudecatoriaURZICENI', 'JudecatoriaVALENIIDEMUNTE', 'JudecatoriaVANJUMARE', 'JudecatoriaVASLUI', 'JudecatoriaVATRADORNEI', 'JudecatoriaVIDELE', 'JudecatoriaVISEUDESUS', 'JudecatoriaZALAU', 'JudecatoriaZARNESTI', 'JudecatoriaZIMNICEA', 'TribunalulMilitarIASI', 'JudecatoriaALEXANDRIA', 'TribunalulMilitarTIMISOARA', 'TribunalulMilitarCLUJNAPOCA', 'TribunalulMilitarTeritorialBUCURESTI', 'JudecatoriaAVRIG', 'JudecatoriaTOPOLOVENI', 'JudecatoriaPODUTURCULUI', 'JudecatoriaFAGET', 'JudecatoriaSALONTA', 'JudecatoriaLIESTI', 'JudecatoriaHARSOVA', 'JudecatoriaSOMCUTAMARE', 'JudecatoriaPASCANI', 'TribunalulComercialARGES', 'TribunalulComercialCLUJ', 'TribunalulComercialMURES', 'TribunalulpentruminoriSifamilieBRASOV', 'JudecatoriaCORNETU', 'JudecatoriaJIBOU'
];

// Funcție pentru trimiterea cererii SOAP
function searchDosare($numarDosar = '', $obiectDosar = '', $numeParte = '', $institutie = '', $dataStart = '', $dataStop = '') {
    $url = 'http://portalquery.just.ro/query.asmx';

      $xmlRequest = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <CautareDosare xmlns="portalquery.just.ro">
      <numarDosar>' . htmlspecialchars($numarDosar) . '</numarDosar>
      <obiectDosar>' . htmlspecialchars($obiectDosar) . '</obiectDosar>
      <numeParte>' . htmlspecialchars($numeParte) . '</numeParte>';
    
    if (!empty($institutie)) {
        $xmlRequest .= '<institutie>' . htmlspecialchars($institutie) . '</institutie>';
    }
    
    if (!empty($dataStart)) {
        $xmlRequest .= '<dataStart>' . htmlspecialchars($dataStart) . '</dataStart>';
    }
    if (!empty($dataStop)) {
        $xmlRequest .= '<dataStop>' . htmlspecialchars($dataStop) . '</dataStop>';
    }
    
        $xmlRequest .= '
        </CautareDosare>
    </soap:Body>
</soap:Envelope>';

        // Inițializăm cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: text/xml; charset=utf-8',
        'SOAPAction: "portalquery.just.ro/CautareDosare"',
        'Content-Length: ' . strlen($xmlRequest)
    ));

    $response = curl_exec($ch);
    $error = curl_error($ch);

    if ($error) {
        return ['error' => $error];
    }

    // Parsăm răspunsul XML
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($response);
    if ($xml === false) {
        return ['error' => 'Eroare la parsarea XML-ului'];
    }

    $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
    $xml->registerXPathNamespace('ns', 'portalquery.just.ro');

    $dosare = [];
    foreach ($xml->xpath('//ns:Dosar') as $dosar) {
        $parti = [];
        if (isset($dosar->parti) && isset($dosar->parti->DosarParte)) {
            foreach ($dosar->parti->DosarParte as $parte) {
                if (!$parte->attributes()->{'xsi:nil'}) {
                    $info = [];
                    foreach ($parte->children() as $child) {
                        $info[(string)$child->getName()] = (string)$child;
                    }
                    $parti[] = $info;
                }
            }
        }
        $sedinte = [];
        if (isset($dosar->sedinte) && isset($dosar->sedinte->DosarSedinta)) {
            foreach ($dosar->sedinte->DosarSedinta as $sedinta) {
                if (!$sedinta->attributes()->{'xsi:nil'}) {
                    $info = [];
                    foreach ($sedinta->children() as $child) {
                        $info[(string)$child->getName()] = (string)$child;
                    }
                    $sedinte[] = $info;
                }
            }
        }
        $caiAtac = [];
        if (isset($dosar->caiAtac) && isset($dosar->caiAtac->DosarCaleAtac)) {
            foreach ($dosar->caiAtac->DosarCaleAtac as $cale) {
                if (!$cale->attributes()->{'xsi:nil'}) {
                    $info = [];
                    foreach ($cale->children() as $child) {
                        $info[(string)$child->getName()] = (string)$child;
                    }
                    $caiAtac[] = $info;
                }
            }
        }
        $dosare[] = [
            'numar' => (string)$dosar->numar,
            'numarVechi' => (string)$dosar->numarVechi,
            'data' => (string)$dosar->data,
            'institutie' => (string)$dosar->institutie,
            'departament' => (string)$dosar->departament,
            'categorieCaz' => (string)$dosar->categorieCaz,
            'stadiuProcesual' => (string)$dosar->stadiuProcesual,
            'obiect' => (string)$dosar->obiect,
            'dataModificare' => (string)$dosar->dataModificare,
            'categorieCazNume' => (string)$dosar->categorieCazNume,
            'stadiuProcesualNume' => (string)$dosar->stadiuProcesualNume,
            'parti' => $parti,
            'sedinte' => $sedinte,
            'caiAtac' => $caiAtac,
        ];
    }

    return $dosare;
}

// Procesăm formularul
$results = [];
$error = '';
// If included from clientprofile (or any other script) and a client name is provided
// use that name (cleaned from legal forms) as default search `numeParte` and run search.
if ($is_included && isset($Client_Denumire) && !empty($Client_Denumire)) {
    // Clean common legal forms: S.R.L., SRL, S.A., SA, PFA, II, I.I., S.C., SC etc.
    $clean = trim($Client_Denumire);
    $forms = ["S.R.L.", "S.R.L", "SRL", "S C", "S.C.", "SC", "S.A.", "S A", "SA", "PFA", "II", "I.I.", "SRL.", "Srl", "s.r.l.", "srl", "s.a."];
    foreach ($forms as $f) {
        $clean = str_ireplace($f, '', $clean);
    }
    // remove extraneous punctuation and multiple spaces
    $clean = preg_replace('/[\,\.;:\(\)\"]+/', ' ', $clean);
    $clean = preg_replace('/\s+/', ' ', $clean);
    $clean = trim($clean);

    $numarDosar = '';
    $obiectDosar = '';
    $numeParte = $clean;
    $institutie = '';
    $dataStart = '';
    $dataStop = '';

    $response = searchDosare($numarDosar, $obiectDosar, $numeParte, $institutie, $dataStart, $dataStop);
    if (isset($response['error'])) {
        $error = $response['error'];
    } else {
        $results = $response;
    }

} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $numarDosar = $_POST['numarDosar'] ?? '';
    $obiectDosar = $_POST['obiectDosar'] ?? '';
    $numeParte = $_POST['numeParte'] ?? '';
    $institutie = $_POST['institutie'] ?? '';
    $dataStart = $_POST['dataStart'] ?? '';
    $dataStop = $_POST['dataStop'] ?? '';

    $response = searchDosare($numarDosar, $obiectDosar, $numeParte, $institutie, $dataStart, $dataStop);
    if (isset($response['error'])) {
        $error = $response['error'];
    } else {
        $results = $response;
    }
}
?>

<div class="grid-container">
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <h1>Căutare Dosare JUST</h1>
        </div>
    </div>

    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <form method="post" action="" <?php if($is_included) echo 'style="display:block"';?>>
                <div class="grid-x grid-margin-x">
                    <div class="large-6 medium-6 small-12 cell">
                        <label>Număr Dosar:</label>
                        <input type="text" name="numarDosar" value="<?php echo htmlspecialchars($_POST['numarDosar'] ?? ''); ?>">
                    </div>
                    <div class="large-6 medium-6 small-12 cell">
                        <label>Obiect Dosar:</label>
                        <input type="text" name="obiectDosar" value="<?php echo htmlspecialchars($_POST['obiectDosar'] ?? ''); ?>">
                    </div>
                </div>
                <div class="grid-x grid-margin-x">
                    <div class="large-6 medium-6 small-12 cell">
                        <label>Nume Parte:</label>
                        <input type="text" name="numeParte" value="<?php echo htmlspecialchars($numeParte ?? ($_POST['numeParte'] ?? '')); ?>">
                    </div>
                    <div class="large-6 medium-6 small-12 cell">
                        <label>Instituție:</label>
                        <select name="institutie">
                            <option value="">Selectați instituția</option>
                            <?php foreach ($institutii as $inst): ?>
                                <option value="<?php echo htmlspecialchars($inst); ?>" <?php echo (($_POST['institutie'] ?? '') == $inst) ? 'selected' : ''; ?>><?php echo htmlspecialchars($inst); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid-x grid-margin-x">
                    <div class="large-6 medium-6 small-12 cell">
                        <label>Data Start (YYYY-MM-DDTHH:MM:SS):</label>
                        <input type="text" name="dataStart" value="<?php echo htmlspecialchars($_POST['dataStart'] ?? ''); ?>">
                    </div>
                    <div class="large-6 medium-6 small-12 cell">
                        <label>Data Stop (YYYY-MM-DDTHH:MM:SS):</label>
                        <input type="text" name="dataStop" value="<?php echo htmlspecialchars($_POST['dataStop'] ?? ''); ?>">
                    </div>
                </div>
                <div class="grid-x grid-margin-x">
                    <div class="large-12 medium-12 small-12 cell">
                        <input type="submit" class="button" value="Caută">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($error): ?>
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <div class="callout alert">
                <p><?php echo htmlspecialchars($error ?? ''); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($results): ?>
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <h2>Rezultate</h2>
            <table>
                <thead>
                    <tr>
                        <th>Număr</th>
                        <th>Data</th>
                        <th>Instituție</th>
                        <th>Categorie</th>
                        <th>Stadiu</th>
                        <th>Obiect</th>
                        <th>Detalii</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $index = 0; ?>
                    <?php foreach ($results as $dosar): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($dosar['numar']); ?></td>
                        <td><?php echo htmlspecialchars($dosar['data']); ?></td>
                        <td><?php echo htmlspecialchars($dosar['institutie']); ?></td>
                        <td><?php echo htmlspecialchars($dosar['categorieCazNume']); ?></td>
                        <td><?php echo htmlspecialchars($dosar['stadiuProcesualNume']); ?></td>
                        <td><?php echo htmlspecialchars($dosar['obiect']); ?></td>
                        <td><button class="button small" data-open="modal-<?php echo $index; ?>">Detalii</button></td>
                    </tr>
                    <?php $index++; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php $index = 0; ?>
            <?php foreach ($results as $dosar): ?>
            <div class="large reveal" id="modal-<?php echo $index; ?>" data-reveal>
                <h3>Detalii Dosar</h3>
                <p><strong>Număr:</strong> <?php echo htmlspecialchars($dosar['numar']); ?></p>
                <p><strong>Data:</strong> <?php echo htmlspecialchars($dosar['data']); ?></p>
                <p><strong>Instituție:</strong> <?php echo htmlspecialchars($dosar['institutie']); ?></p>
                <p><strong>Categorie:</strong> <?php echo htmlspecialchars($dosar['categorieCazNume']); ?></p>
                <p><strong>Stadiu:</strong> <?php echo htmlspecialchars($dosar['stadiuProcesualNume']); ?></p>
                <p><strong>Obiect:</strong> <?php echo htmlspecialchars($dosar['obiect']); ?></p>
                <p><strong>Părți:</strong><br/>
                <?php 
                $parti_str = '';
                foreach ($dosar['parti'] as $p) {
                    $part_str = '';
                    foreach ($p as $k => $v) {
                        if (!empty($v)) $part_str .= htmlspecialchars($v) . ' ';
                    }
                    $parti_str .= rtrim($part_str) . '<br/>';
                }
                echo $parti_str;
                ?>
                </p>
                <p><strong>Ședințe:</strong><br/>
                <?php 
                $sedinte_str = '';
                foreach ($dosar['sedinte'] as $s) {
                    $sed_str = '';
                    foreach ($s as $k => $v) {
                        if (!empty($v)) $sed_str .= htmlspecialchars($v) . ' ';
                    }
                    $sedinte_str .= rtrim($sed_str) . '<br/>';
                }
                echo $sedinte_str;
                ?>
                </p>
                <p><strong>Căi de atac:</strong><br/>
                <?php 
                $cai_str = '';
                foreach ($dosar['caiAtac'] as $c) {
                    $c_str = '';
                    foreach ($c as $k => $v) {
                        if (!empty($v)) $c_str .= htmlspecialchars($v) . ' ';
                    }
                    $cai_str .= rtrim($c_str) . '<br/>';
                }
                echo $cai_str;
                ?>
                </p>
                <button class="close-button" data-close aria-label="Close modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php $index++; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <div class="callout secondary">
                <p>Nu s-au găsit dosare care să corespundă criteriilor de căutare.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if (!$is_included) include '../bottom.php'; ?>