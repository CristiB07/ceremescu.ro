<?php
// Export Excel OpenXML (Excel 2007+) cu sheet-uri pentru fiecare tab
date_default_timezone_set('Europe/Bucharest');
include '../settings.php';
include '../classes/common.php';
$year = $_GET["year"];
$client = $_GET["client"];
$strwastetoreport = $_GET["cod_id"];
$wid = $_GET["wID"];
$wastequery = "SELECT * FROM deseuri_coduri WHERE cd_id='$strwastetoreport'";
$wasteresult = ezpub_query($conn, $wastequery);
$wasterow = ezpub_fetch_array($wasteresult);
$wastecode = $wasterow["cd_01"] . $wasterow["cd_02"] . $wasterow["cd_03"];
$wastedescription = $wasterow["cd_description"];

$cquery = "SELECT * FROM clienti_date WHERE ID_Client=$client";
$cresult = ezpub_query($conn, $cquery);
$clientRow = ezpub_fetch_array($cresult);

$wrquery = "SELECT * FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_cod_deseu='$wid' AND raportare_an_raportare='$year' ORDER BY raportare_id ASC";
$wrresult = ezpub_query($conn, $wrquery);
$wrrow=ezpub_fetch_array($wrresult);
$wasteunit=$wrrow['raportare_um'];
// Tabs
$tabs = [
    1 => 'Generare',
    2 => 'Stocare',
    3 => 'Valorificare',
    4 => 'Eliminare',
];

// Explanatory text for tab 2
// Text explicativ pentru tab 2 (Stocare)
$texttab2 = '<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">Notă</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">*1) Tipul de stocare:</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">RM - recipient metalic</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">RP - recipient de plastic</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">BZ - bazin decantor</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">CT - container transportabil</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">CF - container fix</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">S - saci</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">PD - platforma de deshidratare</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">VN - în vrac, neacoperit</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">VA - în vrac, incinta acoperită</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">RL - recipient din lemn</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">A - altele</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">*2) Modul de tratare:</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">TM - tratare mecanică</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">TC - tratare chimică</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">TMC - tratare mecano-chimică</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">TB - tratare biochimică</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D - deshidratare</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">TT - tratare termică</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">A - altele</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">*3) Scopul tratării:</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">V - pentru valorificare</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">E - în vederea eliminării</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">*4) Mijlocul de transport:</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">AS - autospeciale</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">AN - auto nespecial</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">H - transport hidraulic</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">CF - cale ferată</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">A - altele</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">*5) Destinaţia:</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">DO - depozitul de gunoi al oraşului/comunei</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">HP - halda proprie</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">HC - halda industrială comună</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">I - incinerarea în scopul eliminării</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">Vr - valorificare prin agenţi economici autorizaţi</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">P - utilizare materială sau energetică în propria întreprindere</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">Ve - valorificare energetică prin agenţi economici autorizaţi</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">A - altele</Data></Cell></Row>';

// Text explicativ pentru tab 3 (Valorificare)
$texttab3 = '<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">Operațiuni de valorificare conform Anexa 3/OUG 92/2021 privind gestionarea deșeurilor</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">R1 Întrebuinţarea în principal drept combustibil sau ca altă sursă de energie</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">R2 Valorificarea/Regenerarea solvenţilor</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">R3 Reciclarea/Recuperarea substanţelor organice utilizate ca solvenţi (inclusiv compostarea şi alte procese de transformare biologică)</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">R4 Reciclarea/Recuperarea metalelor şi compuşilor metalici</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">R5 Reciclarea/Recuperarea altor materiale anorganice</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">R6 Regenerarea acizilor sau a bazelor</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">R7 Valorificarea componenţilor utilizaţi pentru reducerea poluării</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">R8 Valorificarea componentelor catalizatorilor</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">R9 Rerafinarea uleiului uzat sau alte reutilizări ale uleiului uzat</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">R10 Tratarea terenurilor având drept rezultat beneficii pentru agricultură sau ecologie</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">R11 Utilizarea deşeurilor obţinute din oricare dintre operaţiunile numerotate de la R1 la R10</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">R12 Schimbul de deşeuri în vederea expunerii la oricare dintre operaţiunile numerotate de la R1 la R11</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">R13 Stocarea deşeurilor înaintea oricărei operaţiuni numerotate de la R1 la R12 (excluzând stocarea temporară, înaintea colectării, la situl unde a fost generat deşeul)</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">Note:</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">[1] Aceasta include instalaţii de incinerare destinate în principal tratării deşeurilor municipale solide, numai în cazul în care randamentul lor energetic este egal sau mai mare decât 0,60 pentru instalaţiile care funcţionează şi sunt autorizate în conformitate cu legislaţia comunitară aplicabilă înainte de 1 ianuarie 2009; 0,65 pentru instalaţiile autorizate după 31 decembrie 2008, folosindu-se următoarea formulă: Eficienţa energetică = (Ep - (Ef + Ei))/(0,97 × (Ew + Ef)), unde: Ep reprezintă producţia anuală de energie sub formă de căldură sau electricitate. Aceasta este calculată înmulţind energia produsă sub formă de electricitate cu 2,6 şi energia produsă sub formă de căldură pentru utilizare comercială (GJ/an) cu 1,1; Ef reprezintă consumul anual de energie al sistemului, provenită din combustibili, care contribuie la producţia de aburi (GJ/an); Ew reprezintă energia anuală conţinută de deşeurile tratate, calculată pe baza valorii calorice nete inferioare a deşeurilor (GJ/an); Ei reprezintă energia anuală importată, exclusiv Ew şi Ef (GJ/an); 0,97 este un coeficient care reprezintă pierderile de energie datorate reziduurilor generate în urma incinerării şi radierii. Această formulă se aplică în conformitate cu documentul de referinţă privind cele mai bune tehnici existente pentru incinerarea deşeurilor.</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">[2] Aceasta include pregătirea pentru reutilizare, gazeificarea şi piroliza care folosesc componentele ca produse chimice şi valorificarea materialelor organice sub formă de rambleiaj.</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">[3] Aceasta include pregătirea pentru reutilizare.</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">[4] Aceasta include pregătirea pentru reutilizare, reciclarea materialelor de construcţie anorganice, valorificarea materialelor anorganice sub formă de rambleiaj şi curăţarea solului care are ca rezultat valorificarea solului.</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">[5] În cazul în care nu există niciun alt cod R corespunzător, aceasta include operaţiunile preliminare înainte de valorificare, inclusiv preprocesarea, cum ar fi, printre altele, demontarea, sortarea, sfărâmarea, compactarea, granularea, mărunţirea uscată, condiţionarea, reambalarea, separarea şi amestecarea înainte de supunerea la oricare dintre operaţiunile numerotate de la R1 la R11.</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">[6] Stocare temporară înseamnă stocare preliminară în conformitate cu anexa nr. 1 pct. 6.</Data></Cell></Row>';
$texttab4='
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">Operațiuni de eliminare conform Anexa 7/OUG 92/2021 privind gestionarea deșeurilor</Data></Cell></Row> 
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D1 Depozitarea în sau pe sol (de exemplu, depozite de deşeuri etc.)</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D2 Tratarea solului (de exemplu, biodegradarea deşeurilor lichide sau nămoloase în sol etc.)</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D3 Injectarea în adâncime (de exemplu, injectarea deşeurilor care pot fi pompate în puţuri, saline sau depozite geologice naturale etc.)</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D4 Acumulare la suprafaţă (de exemplu, depunerea de deşeuri lichide sau nămoloase în bazine, iazuri sau lagune etc.)</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D5 Depozite special construite (de exemplu, depunerea în compartimente separate etanşe care sunt acoperite şi izolate unele faţă de celelalte şi faţă de mediul înconjurător etc.)</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D6 Evacuarea într-o masă de apă, cu excepţia mărilor/oceanelor</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D7 Evacuarea în mări/oceane, inclusiv eliminarea în subsolul marin</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D8 Tratarea biologică nemenţionată în altă parte în prezenta anexă, care generează compuşi sau mixturi finale eliminate prin intermediul unuia dintre procedeele numerotate de la D1 la D12</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">R9 Rerafinarea uleiului uzat sau alte reutilizări ale uleiului uzat</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D10 Incinerarea pe sol</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D11 Incinerarea pe mare </Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">Această operaţiune este interzisă de legislaţia Uniunii Europene şi de convenţii internaţionale.</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D12 Stocarea permanentă (de exemplu, plasarea de recipiente într-o mină etc.)</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D13 Amestecarea anterioară oricărei operaţiuni numerotate de la D1 la D12</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">În cazul în care nu există niciun alt cod D corespunzător, aceasta include operaţiunile preliminare înainte de eliminare, inclusiv preprocesarea, cum ar fi, printre altele, sortarea, sfărâmarea, compactarea, granularea, uscarea, mărunţirea uscată, condiţionarea sau separarea înainte de supunerea la oricare dintre operaţiunile numerotate de la D1 la D12.</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D14 Reambalarea anterioară oricărei operaţiuni numerotate de la D1 la D13</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D15 Stocarea înaintea oricărei operaţiuni numerotate de la D1 la D14 (excluzând stocarea temporară, înaintea colectării, în zona de generare a deşeurilor)</Data></Cell></Row>
<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">Stocare temporară înseamnă stocare preliminară în conformitate cu articolul 3 punctul 10 din OUG 92/2021.</Data></Cell></Row>';
$texttab4='\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">Operațiuni de eliminare conform Anexa 7/OUG 92/2021 privind gestionarea deșeurilor</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D1 Depozitarea în sau pe sol (de exemplu, depozite de deşeuri etc.)</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D2 Tratarea solului (de exemplu, biodegradarea deşeurilor lichide sau nămoloase în sol etc.)</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D3 Injectarea în adâncime (de exemplu, injectarea deşeurilor care pot fi pompate în puţuri, saline sau depozite geologice naturale etc.)</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D4 Acumulare la suprafaţă (de exemplu, depunerea de deşeuri lichide sau nămoloase în bazine, iazuri sau lagune etc.)</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D5 Depozite special construite (de exemplu, depunerea în compartimente separate etanşe care sunt acoperite şi izolate unele faţă de celelalte şi faţă de mediul înconjurător etc.)</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D6 Evacuarea într-o masă de apă, cu excepţia mărilor/oceanelor</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D7 Evacuarea în mări/oceane, inclusiv eliminarea în subsolul marin</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D8 Tratarea biologică nemenţionată în altă parte în prezenta anexă, care generează compuşi sau mixturi finale eliminate prin intermediul unuia dintre procedeele numerotate de la D1 la D12</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D9 Tratarea fizico-chimică nemenţionată în altă parte în prezenta anexă, care generează compuşi sau mixturi finale eliminate prin intermediul unuia dintre procedeele numerotate de la D1 la D12 (de exemplu, evaporare, uscare, calcinare etc.)</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D10 Incinerarea pe sol</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D11 Incinerarea pe mare</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">Această operaţiune este interzisă de legislaţia Uniunii Europene şi de convenţii internaţionale.</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D12 Stocarea permanentă (de exemplu, plasarea de recipiente într-o mină etc.)</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D13 Amestecarea anterioară oricărei operaţiuni numerotate de la D1 la D12</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">În cazul în care nu există niciun alt cod D corespunzător, aceasta include operaţiunile preliminare înainte de eliminare, inclusiv preprocesarea, cum ar fi, printre altele, sortarea, sfărâmarea, compactarea, granularea, uscarea, mărunţirea uscată, condiţionarea sau separarea înainte de supunerea la oricare dintre operaţiunile numerotate de la D1 la D12.</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D14 Reambalarea anterioară oricărei operaţiuni numerotate de la D1 la D13</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">D15 Stocarea înaintea oricărei operaţiuni numerotate de la D1 la D14 (excluzând stocarea temporară, înaintea colectării, în zona de generare a deşeurilor)</Data></Cell></Row>\n<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">Stocare temporară înseamnă stocare preliminară în conformitate cu articolul 3 punctul 10 din OUG 92/2021.</Data></Cell></Row>';

$tabData = [];
for ($tab = 1; $tab <= 4; $tab++) {
    $tabData[$tab] = [];
    if ($tab == 1) {
        // Tab 1: Generare
        $stocuri_lunare = [];
        $stoc_total_anual = 0;
        $stoc_precedent = 0;
        $query_stoc_prev = "SELECT stoc_cantitate FROM deseuri_stocuri WHERE stoc_client_id='$client' AND stoc_cod_deseu='$wastecode' AND stoc_an_raportare='".($year-1)."'";
        $result_stoc_prev = ezpub_query($conn, $query_stoc_prev);
        if ($row_stoc_prev = ezpub_fetch_array($result_stoc_prev)) {
            $stoc_precedent = floatval($row_stoc_prev['stoc_cantitate']);
        }
        $stoc_curent = $stoc_precedent;
        for ($m = 1; $m <= 12; $m++) {
            $dateObj = DateTime::createFromFormat('!m', $m);
            $formatter = new IntlDateFormatter("ro_RO", IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'Europe/Bucharest', IntlDateFormatter::GREGORIAN, 'MMMM');
            $monthname = $formatter->format($dateObj);
            $query2 = "SELECT SUM(raportare_cantitate_totala) AS suma_totala, SUM(raportare_cantitate_valorificata) AS suma_valorificata, SUM(raportare_cantitate_eliminata) AS suma_eliminata, SUM(raportare_stocare) AS suma_stocare FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_luna_raportare='$m' AND raportare_an_raportare='$year' AND raportare_cod_deseu='$wastecode'";
            $result2 = ezpub_query($conn, $query2);
            $row2 = ezpub_fetch_array($result2);
            $gen = floatval($row2["suma_totala"]);
            $val = floatval($row2["suma_valorificata"]);
            $elim = floatval($row2["suma_eliminata"]);
            if ($m == 1) {
                $stoc_luna = $stoc_precedent + $gen - $val - $elim;
            } else {
                $stoc_luna = $stoc_curent + $gen - $val - $elim;
            }
            $stocuri_lunare[$m] = $stoc_luna;
            $stoc_curent = $stoc_luna;
            $stoc_total_anual = $stoc_curent;
            $tabData[$tab][] = [
                'Luna' => $monthname,
                'Cantitate generată' => $row2["suma_totala"] === null ? '-' : $row2["suma_totala"],
                'Valorificată' => $row2["suma_valorificata"] === null ? '-' : $row2["suma_valorificata"],
                'Eliminată' => $row2["suma_eliminata"] === null ? '-' : $row2["suma_eliminata"],
                'Stocare' => $row2["suma_stocare"] === null ? '-' : $row2["suma_stocare"],
                'Stoc' => number_format($stoc_luna, 2)
            ];
        }
        // Totaluri
        $totalquery="SELECT SUM(raportare_cantitate_totala) AS total_generated, SUM(raportare_cantitate_valorificata) AS total_valorificated, SUM(raportare_cantitate_eliminata) AS total_eliminated, SUM(raportare_stocare) AS total_stored FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_an_raportare='$year' AND raportare_cod_deseu='$wastecode'";
        $totalresult=ezpub_query($conn,$totalquery);
        $totalrow=ezpub_fetch_array($totalresult);
        $tabData[$tab][] = [
            'Luna' => 'Total',
            'Cantitate generată' => $totalrow["total_generated"] === null ? '-' : $totalrow["total_generated"],
            'Valorificată' => $totalrow["total_valorificated"] === null ? '-' : $totalrow["total_valorificated"],
            'Eliminată' => $totalrow["total_eliminated"] === null ? '-' : $totalrow["total_eliminated"],
            'Stocare' => $totalrow["total_stored"] === null ? '-' : $totalrow["total_stored"],
            'Stoc' => number_format($stoc_total_anual, 2)
        ];
    } elseif ($tab == 2) {
        // Tab 2: Stocare
        $stoc_cumulat_anterior = 0;
        for ($m = 1; $m <= 12; $m++) {
            $dateObj = DateTime::createFromFormat('!m', $m);
            $formatter = new IntlDateFormatter("ro_RO", IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'Europe/Bucharest', IntlDateFormatter::GREGORIAN, 'MMMM');
            $monthname = $formatter->format($dateObj);
            $query3 = "SELECT raportare_stocare, raportare_tip_stocare, raportare_tratare, raportare_tip_tratare, raportare_scop_tratare, raportare_transport, raportare_tip_transport FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_luna_raportare='$m' AND raportare_an_raportare='$year' AND raportare_cod_deseu='$wastecode'";
            $result3 = ezpub_query($conn, $query3);
            $total_stocare = 0;
            $total_tratare = 0;
            $tip_stocare_set = [];
            $tip_tratare_set = [];
            $scop_tratare_set = [];
            $transport_set = [];
            $tip_transport_set = [];
            $has_data = false;
            while ($row3 = ezpub_fetch_array($result3)) {
                if (is_numeric($row3["raportare_stocare"])) $total_stocare += $row3["raportare_stocare"];
                if (is_numeric($row3["raportare_tratare"])) $total_tratare += $row3["raportare_tratare"];
                if ($row3["raportare_tip_stocare"] !== null && $row3["raportare_tip_stocare"] !== '') $tip_stocare_set[] = $row3["raportare_tip_stocare"];
                if ($row3["raportare_tip_tratare"] !== null && $row3["raportare_tip_tratare"] !== '') $tip_tratare_set[] = $row3["raportare_tip_tratare"];
                if ($row3["raportare_scop_tratare"] !== null && $row3["raportare_scop_tratare"] !== '') $scop_tratare_set[] = $row3["raportare_scop_tratare"];
                if ($row3["raportare_transport"] !== null && $row3["raportare_transport"] !== '') $transport_set[] = $row3["raportare_transport"];
                if ($row3["raportare_tip_transport"] !== null && $row3["raportare_tip_transport"] !== '') $tip_transport_set[] = $row3["raportare_tip_transport"];
                $has_data = true;
            }
            $tip_stocare_set = array_unique($tip_stocare_set);
            $tip_tratare_set = array_unique($tip_tratare_set);
            $scop_tratare_set = array_unique($scop_tratare_set);
            $transport_set = array_unique($transport_set);
            $tip_transport_set = array_unique($tip_transport_set);
            $stoc_luna = isset($stocuri_lunare[$m]) ? $stocuri_lunare[$m] : 0;
            $total_stocare_afisat = $total_stocare;
            if ($m > 1) {
                $total_stocare_afisat += $stoc_cumulat_anterior;
            }
            $stoc_cumulat_anterior = $stoc_luna;
            $tabData[$tab][] = [
                'Luna' => $monthname,
                'Cantitate stocată' => $total_stocare_afisat === 0 ? '-' : $total_stocare_afisat,
                'Tip stocare' => count($tip_stocare_set) ? implode(', ', $tip_stocare_set) : '-',
                'Cantitate tratată' => $total_tratare === 0 ? '-' : $total_tratare,
                'Tip tratare' => count($tip_tratare_set) ? implode(', ', $tip_tratare_set) : '-',
                'Scop tratare' => count($scop_tratare_set) ? implode(', ', $scop_tratare_set) : '-',
                'Transport' => count($transport_set) ? implode(', ', $transport_set) : '-',
                'Tip transport' => count($tip_transport_set) ? implode(', ', $tip_transport_set) : '-',
            ];
        }
        $totalquery2 = "SELECT SUM(raportare_stocare) AS total_stored, SUM(raportare_tratare) AS total_tratare FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_an_raportare='$year' AND raportare_cod_deseu='$wastecode'";
        $totalresult2 = ezpub_query($conn, $totalquery2);
        $totalrow2 = ezpub_fetch_array($totalresult2);
        $tabData[$tab][] = [
            'Luna' => 'Total',
            'Cantitate stocată' => $totalrow2["total_stored"] === null ? '-' : $totalrow2["total_stored"],
            'Tip stocare' => '',
            'Cantitate tratată' => $totalrow2["total_tratare"] === null ? '-' : $totalrow2["total_tratare"],
            'Tip tratare' => '',
            'Scop tratare' => '',
            'Transport' => '',
            'Tip transport' => '',
        ];
    } elseif ($tab == 3) {
        // Tab 3: Valorificare
        for ($m = 1; $m <= 12; $m++) {
            $dateObj = DateTime::createFromFormat('!m', $m);
            $formatter = new IntlDateFormatter("ro_RO", IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'Europe/Bucharest', IntlDateFormatter::GREGORIAN, 'MMMM');
            $monthname = $formatter->format($dateObj);
            $query4 = "SELECT raportare_operator, raportare_cantitate_valorificata, raportare_cod_operatiune_valorificare FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_luna_raportare='$m' AND raportare_an_raportare='$year' AND raportare_cod_deseu='$wastecode'";
            $result4 = ezpub_query($conn, $query4);
            $operators = [];
            $codes = [];
            $total_valorificata = 0;
            while ($row4 = ezpub_fetch_array($result4)) {
                if ($row4["raportare_cantitate_valorificata"] !== null && $row4["raportare_cantitate_valorificata"] !== '' && floatval($row4["raportare_cantitate_valorificata"]) > 0) {
                    $q = $row4["raportare_cantitate_valorificata"];
                    $c = ($row4["raportare_cod_operatiune_valorificare"] === null || $row4["raportare_cod_operatiune_valorificare"] === '' ? '-' : $row4["raportare_cod_operatiune_valorificare"]);
                    $operators[] = $row4["raportare_operator"] . ' - ' . $q;
                    $codes[] = $c;
                    $total_valorificata += floatval($row4["raportare_cantitate_valorificata"]);
                }
            }
            $tabData[$tab][] = [
                'Luna' => $monthname,
                'Cantitate valorificată' => $total_valorificata === 0 ? '-' : $total_valorificata,
                'Operator' => count($operators) ? implode("\n", $operators) : '-',
                'Cod operațiune' => count($codes) ? implode("\n", $codes) : '-',
            ];
        }
        $totalquery3 = "SELECT SUM(raportare_cantitate_valorificata) AS total_valorificated FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_an_raportare='$year' AND raportare_cod_deseu='$wastecode'";
        $totalresult3 = ezpub_query($conn, $totalquery3);
        $totalrow3 = ezpub_fetch_array($totalresult3);
        $tabData[$tab][] = [
            'Luna' => 'Total',
            'Cantitate valorificată' => $totalrow3["total_valorificated"] === null ? '-' : $totalrow3["total_valorificated"],
            'Operator' => '',
            'Cod operațiune' => '',
        ];
    } elseif ($tab == 4) {
        // Tab 4: Eliminare
        for ($m = 1; $m <= 12; $m++) {
            $dateObj = DateTime::createFromFormat('!m', $m);
            $formatter = new IntlDateFormatter("ro_RO", IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'Europe/Bucharest', IntlDateFormatter::GREGORIAN, 'MMMM');
            $monthname = $formatter->format($dateObj);
            $query5 = "SELECT raportare_operator, raportare_cantitate_eliminata, raportare_cod_operatiune_eliminare FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_luna_raportare='$m' AND raportare_an_raportare='$year' AND raportare_cod_deseu='$wastecode'";
            $result5 = ezpub_query($conn, $query5);
            $operators = [];
            $codes = [];
            $total_eliminata = 0;
            while ($row5 = ezpub_fetch_array($result5)) {
                if ($row5["raportare_cantitate_eliminata"] !== null && $row5["raportare_cantitate_eliminata"] !== '' && floatval($row5["raportare_cantitate_eliminata"]) > 0) {
                    $q = $row5["raportare_cantitate_eliminata"];
                    $c = ($row5["raportare_cod_operatiune_eliminare"] === null || $row5["raportare_cod_operatiune_eliminare"] === '' ? '-' : $row5["raportare_cod_operatiune_eliminare"]);
                    $operators[] = $row5["raportare_operator"] . ' - ' . $q;
                    $codes[] = $c;
                    $total_eliminata += floatval($row5["raportare_cantitate_eliminata"]);
                }
            }
            $tabData[$tab][] = [
                'Luna' => $monthname,
                'Cantitate eliminată' => $total_eliminata === 0 ? '-' : $total_eliminata,
                'Operator' => count($operators) ? implode("\n", $operators) : '-',
                'Cod operațiune' => count($codes) ? implode("\n", $codes) : '-',
            ];
        }
        $totalquery4 = "SELECT SUM(raportare_cantitate_eliminata) AS total_eliminated FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_an_raportare='$year' AND raportare_cod_deseu='$wastecode'";
        $totalresult4 = ezpub_query($conn, $totalquery4);
        $totalrow4 = ezpub_fetch_array($totalresult4);
        $tabData[$tab][] = [
            'Luna' => 'Total',
            'Cantitate eliminată' => $totalrow4["total_eliminated"] === null ? '-' : $totalrow4["total_eliminated"],
            'Operator' => '',
            'Cod operațiune' => '',
        ];
    }
}



header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="raport_deseuri_' . date('Y-m-d_His') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

echo '<?xml version="1.0"?>';
echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
foreach ($tabs as $tab => $tabName) {
    echo '<Worksheet ss:Name="' . htmlspecialchars($tabName) . '"><Table>';
    // Client info rows
    echo '<Row><Cell><Data ss:Type="String">Nume client</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($clientRow['Client_Denumire'] ?? '') . '</Data></Cell></Row>';
    echo '<Row><Cell><Data ss:Type="String">Adresă client</Data></Cell><Cell><Data ss:Type="String">' . htmlspecialchars($clientRow['Client_Adresa'] ?? '') . '</Data></Cell></Row>';
    echo '<Row><Cell><Data ss:Type="String">Oraș</Data></Cell><Cell><Data ss:Type="String">' . htmlspecialchars($clientRow['Client_Localitate'] ?? '') . '</Data></Cell></Row>';
    echo '<Row><Cell><Data ss:Type="String">Județ</Data></Cell><Cell><Data ss:Type="String">' . htmlspecialchars($clientRow['Client_Judet'] ?? '') . '</Data></Cell></Row>';
    echo '<Row><Cell><Data ss:Type="String">Cod CAEN</Data></Cell><Cell><Data ss:Type="String">' . htmlspecialchars($clientRow['Client_Cod_CAEN'] ?? '') . '</Data></Cell></Row>';
    echo '<Row><Cell><Data ss:Type="String">CUI</Data></Cell><Cell><Data ss:Type="String">' . htmlspecialchars($clientRow['Client_CUI'] ?? '') . '</Data></Cell></Row>';
    echo '<Row><Cell><Data ss:Type="String">Cod deseu</Data></Cell><Cell><Data ss:Type="String">' . htmlspecialchars($wastecode) . '</Data></Cell></Row>';
    echo '<Row><Cell><Data ss:Type="String">Descriere</Data></Cell><Cell><Data ss:Type="String">' . htmlspecialchars($wastedescription) . '</Data></Cell></Row>';
    echo '<Row><Cell><Data ss:Type="String">Unitate de măsură</Data></Cell><Cell><Data ss:Type="String">' . htmlspecialchars($wasteunit) . '</Data></Cell></Row>';
    // ...existing code...
       // Table header
    if (!empty($tabData[$tab])) {
        echo '<Row>';
        foreach (array_keys($tabData[$tab][0]) as $col) {
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars((string)($col ?? '')) . '</Data></Cell>';
        }
        echo '</Row>';
        foreach ($tabData[$tab] as $row) {
            echo '<Row>';
            foreach ($row as $val) {
                // Păstrează newline (\n) pentru Excel XML cu ss:WhiteSpace="Preserve"
                $cell = str_replace(["<br />", "<br/>", "<br>"], "\n", (string)($val ?? ''));
                echo '<Cell><Data ss:Type="String" ss:WhiteSpace="Preserve">' . htmlspecialchars($cell) . '</Data></Cell>';
            }
            echo '</Row>';
        }
    } else {
        echo '<Row><Cell ss:MergeAcross="5"><Data ss:Type="String" ss:WhiteSpace="Preserve">Fără date pentru acest tab.</Data></Cell></Row>';
    }
    // Inserează text explicativ la final pentru taburile 2, 3, 4
    if ($tab == 2 && !empty($texttab2)) {
        echo $texttab2;
    }
    if ($tab == 3 && !empty($texttab3)) {
        echo $texttab3;
    }
    if ($tab == 4 && !empty($texttab4)) {
        echo $texttab4;
    }
    echo '</Table></Worksheet>';
}
echo '</Workbook>';
exit;
