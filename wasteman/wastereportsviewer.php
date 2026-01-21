<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Raportare deșeuri";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$year=$_GET["year"];
$client=$_GET["client"];
$strwastetoreport=$_GET["cod_id"];
$wid=$_GET["wID"];
$wastequery="SELECT * FROM deseuri_coduri WHERE cd_id='$strwastetoreport'";
$wasteresult=ezpub_query($conn,$wastequery);
$wasterow=ezpub_fetch_array($wasteresult);
$wastecode=$wasterow["cd_01"] . $wasterow["cd_02"] . $wasterow["cd_03"];
$wastedescription=$wasterow["cd_description"];
?>
<div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			    <p><a href="wastereportselector.php" class="button"><i class="fa fa-backward"></i> <?php echo $strBack?></a>
			    <a href="wastereportsviewer.export.php?mode=show&year=<?php echo $year ?>&client=<?php echo $client ?>&cod_id=<?php echo $strwastetoreport ?>&wID=<?php echo $wid ?>" class="button"><i class="fa fa-file-excel-o"></i> <?php echo $strExport?></a></p>
			    <a href="wastereportsviewer.export.openxml.php?mode=show&year=<?php echo $year ?>&client=<?php echo $client ?>&cod_id=<?php echo $strwastetoreport ?>&wID=<?php echo $wid ?>" class="button"><i class="fa fa-file-excel"></i> <?php echo $strExport?></a></p>
<table width="100%">
    <thead>
        <th><td></td><td></td></th>
        </thead>
    <tbody>
        <?php
        $cquery="SELECT * FROM clienti_date WHERE ID_Client=$client";
        $cresult=ezpub_query($conn,$cquery);
        $crow=ezpub_fetch_array($cresult);
        ?>
<tr>
    <td><?php echo $strName?></td>
    <td><?php echo $crow["Client_Denumire"]?></td>
 </tr>
 <tr>
     <td><?php echo $strVAT?></td>
    <td><?php echo $crow["Client_CUI"]?></td>
 </tr>
 <tr>
     <td><?php echo $strCompanyRC?></td>
    <td><?php echo $crow["Client_RC"]?></td>
</tr>
 <tr>
    <td><?php echo $strAddress?></td>
    <td><?php echo $crow["Client_Adresa"]?></td>
 </tr>
 <tr>
     <td><?php echo $strCity?></td>
    <td><?php echo $crow["Client_Localitate"]?></td>
 </tr>
 <tr>
    <td><?php echo $strCounty?></td>
    <td><?php echo $crow["Client_Judet"]?></td>
 </tr>
 <tr>
    <td><?php echo $strCode?></td>
    <td><?php echo $crow["Client_Cod_CAEN"]?></td>
</tr>  
    <tr>
        <td><?php echo $strWasteCodeComplete?></td>
        <td><?php echo $wastecode?></td>    
    </tr>
    <tr>
        <td><?php echo $strDetails?></td>
        <td><?php echo $wastedescription?></td> 
    </tr>
</tbody>
</table>
</div>  
</div>
<ul class="tabs" data-tabs id="waste-management-tabs">
  <li class="tabs-title is-active"><a href="#panel1" aria-selected="true">Generare</a></li>
  <li class="tabs-title"><a data-tabs-target="panel2" href="#panel2">Stocare</a></li>
  <li class="tabs-title"><a data-tabs-target="panel3" href="#panel3">Valorificare</a></li>
  <li class="tabs-title"><a data-tabs-target="panel4" href="#panel4">Eliminare</a></li>
</ul>
<div class="tabs-content" data-tabs-content="waste-management-tabs">
    <div class="tabs-panel is-active" id="panel1">
        <table width="100%">
    <thead>
    	<tr>
        	<th><?php echo $strMonth?></th>
        	<th><?php echo $strQuantity?></th>
        	<th><?php echo $strValorization?></th>
        	<th><?php echo $strElimination?></th>
        	<th><?php echo $strStorage?></th>
            <th><?php echo $strStock?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Calcul stoc precedent pentru ianuarie
        $stocuri_lunare = [];
        $stoc_total_anual = 0;
        $stoc_precedent = 0;
        $query_stoc_prev = "SELECT stoc_cantitate FROM deseuri_stocuri WHERE stoc_client_id='$client' AND stoc_cod_deseu='$wastecode' AND stoc_an_raportare='".($year-1)."'";
        $result_stoc_prev = ezpub_query($conn, $query_stoc_prev);
        if ($row_stoc_prev = ezpub_fetch_array($result_stoc_prev)) {
            $stoc_precedent = floatval($row_stoc_prev['stoc']);
        }
        $stoc_curent = $stoc_precedent;
        for ($m = 1; $m <= 12; $m++) {
            $dateObj = DateTime::createFromFormat('!m', $m);
            $formatter = new IntlDateFormatter("ro_RO",
                IntlDateFormatter::FULL,
                IntlDateFormatter::FULL,
                'Europe/Bucharest',
                IntlDateFormatter::GREGORIAN,
                'MMMM');
            $monthname = $formatter->format($dateObj);
            $query2 = "SELECT SUM(raportare_cantitate_totala) AS suma_totala, SUM(raportare_cantitate_valorificata) AS suma_valorificata, SUM(raportare_cantitate_eliminata) AS suma_eliminata, SUM(raportare_stocare) AS suma_stocare FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_luna_raportare='$m' AND raportare_an_raportare='$year' AND raportare_cod_deseu='$wastecode'";
            $result2 = ezpub_query($conn, $query2);
            $row2 = ezpub_fetch_array($result2);
            $gen = floatval($row2["suma_totala"]);
            $val = floatval($row2["suma_valorificata"]);
            $elim = floatval($row2["suma_eliminata"]);
            // Stocul lunar = stoc precedent (sau cumul) + generat - valorificat - eliminat
            if ($m == 1) {
                $stoc_luna = $stoc_precedent + $gen - $val - $elim;
            } else {
                $stoc_luna = $stoc_curent + $gen - $val - $elim;
            }
            $stocuri_lunare[$m] = $stoc_luna;
            $stoc_curent = $stoc_luna;
            $stoc_total_anual = $stoc_curent;
            echo "<tr>";
            echo "<td>$monthname</td>";
            echo "<td>" . ($row2["suma_totala"] === null ? '-' : $row2["suma_totala"]) . "</td>";
            echo "<td>" . ($row2["suma_valorificata"] === null ? '-' : $row2["suma_valorificata"]) . "</td>";
            echo "<td>" . ($row2["suma_eliminata"] === null ? '-' : $row2["suma_eliminata"]) . "</td>";
            echo "<td>" . ($row2["suma_stocare"] === null ? '-' : $row2["suma_stocare"]) . "</td>";
            echo "<td>" . number_format($stoc_luna, 2) . "</td>";
            echo "</tr>";
        }
        //show totals
        $totalquery="SELECT SUM(raportare_cantitate_totala) AS total_generated, 
        SUM(raportare_cantitate_valorificata) AS total_valorificated, 
        SUM(raportare_cantitate_eliminata) AS total_eliminated,
        SUM(raportare_stocare) AS total_stored 
        FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_an_raportare='$year' AND raportare_cod_deseu='$wastecode'";
        $totalresult=ezpub_query($conn,$totalquery);
        $totalrow=ezpub_fetch_array($totalresult);
        echo "<tr><td><strong>$strTotal</strong></td><td><strong>" . ($totalrow["total_generated"] === null ? '-' : $totalrow["total_generated"]) . "</strong></td><td><strong>" . ($totalrow["total_valorificated"] === null ? '-' : $totalrow["total_valorificated"]) . "</strong></td><td><strong>" . ($totalrow["total_eliminated"] === null ? '-' : $totalrow["total_eliminated"]) . "</strong></td><td>" . ($totalrow["total_stored"] === null ? '-' : $totalrow["total_stored"]) . "</td><td><strong>".number_format($stoc_total_anual,2)."</strong></td></tr>";
        echo  "</tbody><tfoot><tr><td></td><td colspan='4'><em></em></td><td>&nbsp;</td></tr></tfoot></table> ";  
        ?>
        </table>
    </div>
    <div class="tabs-panel" id="panel2">
        <table width="100%">
    <thead>
        <tr>
            <th><?php echo $strMonth?></th>
            <th><?php echo $strQuantity?></th>
            <th><?php echo $strCode?></th>
            <th><?php echo $strTreated?></th>
            <th><?php echo $strTreatedCode?></th>
            <th><?php echo $strTreatedScope?></th>
            <th><?php echo $strTransport?></th>
            <th><?php echo $strTransportCode?></th>
        </tr>
    </thead>
    <tbody>
    <?php
    $stoc_cumulat_anterior = 0;
    for ($m = 1; $m <= 12; $m++) {
        $dateObj = DateTime::createFromFormat('!m', $m);
        $formatter = new IntlDateFormatter("ro_RO",
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            'Europe/Bucharest',
            IntlDateFormatter::GREGORIAN,
            'MMMM');
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
        // elimină duplicatele
        $tip_stocare_set = array_unique($tip_stocare_set);
        $tip_tratare_set = array_unique($tip_tratare_set);
        $scop_tratare_set = array_unique($scop_tratare_set);
        $transport_set = array_unique($transport_set);
        $tip_transport_set = array_unique($tip_transport_set);
        // Stocul lunar calculat anterior
        $stoc_luna = isset($stocuri_lunare[$m]) ? $stocuri_lunare[$m] : 0;
        // Pentru luna curentă, nu adăuga stocul la total_stocare, doar pentru lunile următoare
        $total_stocare_afisat = $total_stocare;
        if ($m > 1) {
            $total_stocare_afisat += $stoc_cumulat_anterior;
        }
        $stoc_cumulat_anterior = $stoc_luna;
        if (!$has_data && $total_stocare_afisat == 0) {
            echo "<tr><td>$monthname</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td></tr>";
        } else {
            echo "<tr><td>$monthname</td>";
            echo "<td>".($total_stocare_afisat === 0 ? '-' : $total_stocare_afisat)."</td>";
            echo "<td>".(count($tip_stocare_set) ? htmlspecialchars(implode(', ', $tip_stocare_set)) : '-')."</td>";
            echo "<td>".($total_tratare === 0 ? '-' : $total_tratare)."</td>";
            echo "<td>".(count($tip_tratare_set) ? htmlspecialchars(implode(', ', $tip_tratare_set)) : '-')."</td>";
            echo "<td>".(count($scop_tratare_set) ? htmlspecialchars(implode(', ', $scop_tratare_set)) : '-')."</td>";
            echo "<td>".(count($transport_set) ? htmlspecialchars(implode(', ', $transport_set)) : '-')."</td>";
            echo "<td>".(count($tip_transport_set) ? htmlspecialchars(implode(', ', $tip_transport_set)) : '-')."</td>";
            echo "</tr>";
        }
    }
    //show totals pe an
    $totalquery2 = "SELECT SUM(raportare_stocare) AS total_stored, SUM(raportare_tratare) AS total_tratare FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_an_raportare='$year'";
    $totalresult2 = ezpub_query($conn, $totalquery2);
    $totalrow2 = ezpub_fetch_array($totalresult2);
    echo "<tr><td><strong>$strTotal</strong></td><td><strong>" . ($totalrow2["total_stored"] === null ? '-' : $totalrow2["total_stored"]) . "</strong></td><td></td><td><strong>" . ($totalrow2["total_tratare"] === null ? '-' : $totalrow2["total_tratare"]) . "</strong></td><td colspan='4'></td></tr>";
    ?>
        </tbody>
        <tfoot><tr><td></td><td colspan='7'><em></em></td><td>&nbsp;</td></tr></tfoot></table>
        </table>
    </div>
    <div class="tabs-panel" id="panel3">
        <table width="100%">
<thead>
    <tr>
        <th><?php echo $strMonth?></th>
        <th><?php echo $strQuantity?></th>
        <th><?php echo $strOperator?></th>
        <th><?php echo $strOperationCodeValorification?></th>
    </tr>
</thead>
<tbody>
<?php
for ($m = 1; $m <= 12; $m++) {
    $dateObj = DateTime::createFromFormat('!m', $m);
    $formatter = new IntlDateFormatter("ro_RO",
        IntlDateFormatter::FULL,
        IntlDateFormatter::FULL,
        'Europe/Bucharest',
        IntlDateFormatter::GREGORIAN,
        'MMMM');
    $monthname = $formatter->format($dateObj);
    $query4 = "SELECT raportare_operator, raportare_cantitate_valorificata, raportare_cod_operatiune_valorificare FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_luna_raportare='$m' AND raportare_an_raportare='$year'";
    $result4 = ezpub_query($conn, $query4);
    $operators = [];
    $codes = [];
    $total_valorificata = 0;
    while ($row4 = ezpub_fetch_array($result4)) {
        // Afișează doar dacă există cantitate valorificată (nu și eliminată)
        if ($row4["raportare_cantitate_valorificata"] !== null && $row4["raportare_cantitate_valorificata"] !== '' && floatval($row4["raportare_cantitate_valorificata"]) > 0) {
            $q = htmlspecialchars($row4["raportare_cantitate_valorificata"]);
            $c = ($row4["raportare_cod_operatiune_valorificare"] === null || $row4["raportare_cod_operatiune_valorificare"] === '' ? '-' : htmlspecialchars($row4["raportare_cod_operatiune_valorificare"]));
            $operators[] = htmlspecialchars($row4["raportare_operator"]) . ' - ' . $q;
            $codes[] = $c;
            $total_valorificata += floatval($row4["raportare_cantitate_valorificata"]);
        }
    }
    echo "<tr><td>$monthname</td><td>".($total_valorificata === 0 ? '-' : $total_valorificata)."</td><td>".(count($operators) ? implode('<br/>', $operators) : '-')."</td><td>".(count($codes) ? implode('<br/>', $codes) : '-')."</td></tr>";
}
//show grand total
$totalquery3 = "SELECT SUM(raportare_cantitate_valorificata) AS total_valorificated FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_an_raportare='$year'";
$totalresult3 = ezpub_query($conn, $totalquery3);
$totalrow3 = ezpub_fetch_array($totalresult3);
echo "<tr><td><strong>$strTotal</strong></td><td><strong>" . ($totalrow3["total_valorificated"] === null ? '-' : $totalrow3["total_valorificated"]) . "</strong></td><td></td><td></td></tr>";
?>
                </tbody>
                <tfoot><tr><td></td><td><em></em></td><td>&nbsp;</td><td></td></tr></tfoot></table>
        </table>
    </div>
    <div class="tabs-panel" id="panel4">
        <table width="100%">
<thead>
    <tr>
        <th><?php echo $strMonth?></th>
        <th><?php echo $strQuantity?></th>
        <th><?php echo $strOperator?></th>
        <th><?php echo $strOperationCodeElimination?></th>
    </tr>
</thead>
<tbody>
<?php
for ($m = 1; $m <= 12; $m++) {
    $dateObj = DateTime::createFromFormat('!m', $m);
    $formatter = new IntlDateFormatter("ro_RO",
        IntlDateFormatter::FULL,
        IntlDateFormatter::FULL,
        'Europe/Bucharest',
        IntlDateFormatter::GREGORIAN,
        'MMMM');
    $monthname = $formatter->format($dateObj);
    $query5 = "SELECT raportare_operator, raportare_cantitate_eliminata, raportare_cod_operatiune_eliminare FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_luna_raportare='$m' AND raportare_an_raportare='$year'";
    $result5 = ezpub_query($conn, $query5);
    $operators = [];
    $codes = [];
    $total_eliminata = 0;
    while ($row5 = ezpub_fetch_array($result5)) {
        // Afișează doar dacă există cantitate eliminată (nu și valorificată)
        if ($row5["raportare_cantitate_eliminata"] !== null && $row5["raportare_cantitate_eliminata"] !== '' && floatval($row5["raportare_cantitate_eliminata"]) > 0) {
            $q = htmlspecialchars($row5["raportare_cantitate_eliminata"]);
            $c = ($row5["raportare_cod_operatiune_eliminare"] === null || $row5["raportare_cod_operatiune_eliminare"] === '' ? '-' : htmlspecialchars($row5["raportare_cod_operatiune_eliminare"]));
            $operators[] = htmlspecialchars($row5["raportare_operator"]) . ' - ' . $q;
            $codes[] = $c;
            $total_eliminata += floatval($row5["raportare_cantitate_eliminata"]);
        }
    }
    echo "<tr><td>$monthname</td><td>".($total_eliminata === 0 ? '-' : $total_eliminata)."</td><td>".(count($operators) ? implode('<br/>', $operators) : '-')."</td><td>".(count($codes) ? implode('<br/>', $codes) : '-')."</td></tr>";
}
//show grand total
$totalquery4 = "SELECT SUM(raportare_cantitate_eliminata) AS total_eliminated FROM deseuri_raportari WHERE raportare_client_id='$client' AND raportare_an_raportare='$year'";
$totalresult4 = ezpub_query($conn, $totalquery4);
$totalrow4 = ezpub_fetch_array($totalresult4);
echo "<tr><td><strong>$strTotal</strong></td><td><strong>" . ($totalrow4["total_eliminated"] === null ? '-' : $totalrow4["total_eliminated"]) . "</strong></td><td></td><td></td></tr>";
?>
                </tbody>
                <tfoot><tr><td></td><td><em></em></td><td>&nbsp;</td><td></td></tr></tfoot></table>
                <div class="callout">
                <h1>Operațiuni de eliminare conform Anexa 7/OUG 92/2021 privind gestionarea deșeurilor</h1>
                <ul>
<li>D1 Depozitarea în sau pe sol (de exemplu, depozite de deşeuri etc.)
<li>D2 Tratarea solului (de exemplu, biodegradarea deşeurilor lichide sau nămoloase în sol etc.)
<li>D3 Injectarea în adâncime (de exemplu, injectarea deşeurilor care pot fi pompate în puţuri, saline sau depozite geologice naturale etc.)
<li>D4 Acumulare la suprafaţă (de exemplu, depunerea de deşeuri lichide sau nămoloase în bazine, iazuri sau lagune etc.)
<li>D5 Depozite special construite (de exemplu, depunerea în compartimente separate etanşe care sunt acoperite şi izolate unele faţă de celelalte şi faţă de mediul înconjurător etc.)
<li>D6 Evacuarea într-o masă de apă, cu excepţia mărilor/oceanelor
<li>D7 Evacuarea în mări/oceane, inclusiv eliminarea în subsolul marin
<li>D8 Tratarea biologică nemenţionată în altă parte în prezenta anexă, care generează compuşi sau mixturi finale eliminate prin intermediul unuia dintre procedeele numerotate de la D1 la D12
<li>D9 Tratarea fizico-chimică nemenţionată în altă parte în prezenta anexă, care generează compuşi sau mixturi finale eliminate prin intermediul unuia dintre procedeele numerotate de la D1 la D12 (de exemplu, evaporare, uscare, calcinare etc.)
<li>D10 Incinerarea pe sol
<li>D11 Incinerarea pe mare <ul>
<li>Această operaţiune este interzisă de legislaţia Uniunii Europene şi de convenţii internaţionale.</li></ul></li>
<li>D12 Stocarea permanentă (de exemplu, plasarea de recipiente într-o mină etc.)
<li>D13 Amestecarea anterioară oricărei operaţiuni numerotate de la D1 la D12
    <ul>
<li>În cazul în care nu există niciun alt cod D corespunzător, aceasta include operaţiunile preliminare înainte de eliminare, inclusiv preprocesarea, cum ar fi, printre altele, sortarea, sfărâmarea, compactarea, granularea, uscarea, mărunţirea uscată, condiţionarea sau separarea înainte de supunerea la oricare dintre operaţiunile numerotate de la D1 la D12.</li></ul></li>
<li>D14 Reambalarea anterioară oricărei operaţiuni numerotate de la D1 la D13</li>
<li>D15 Stocarea înaintea oricărei operaţiuni numerotate de la D1 la D14 (excluzând stocarea temporară, înaintea colectării, în zona de generare a deşeurilor)
    <ul>
<li>Stocare temporară înseamnă stocare preliminară în conformitate cu articolul 3 punctul 10.</li>
</ul></li>
                </ul>
                </div>
        </table>
    </div>
<?php 
?>
<!-- end tabs-content -->
<?php
include '../bottom.php';
?>