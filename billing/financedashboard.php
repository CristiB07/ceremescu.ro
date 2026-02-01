<?php
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$month= date('m');
$year=date('Y');
$byear=$year - 1;
$day = date('d');
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes")
{
	header("location:$strSiteURL/login/index.php?message=MLF");
	die;
}

?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <h2><?php echo $strFinancials?></h2>
    </div>
</div>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
			 	$querybank="SELECT * FROM cash_banca";
	$resultbank=ezpub_query($conn,$querybank);
	$rsb=ezpub_fetch_array($resultbank);
	$totalinbanca=$rsb["cash_banca_ING"]+$rsb["cash_banca_trezorerie"]+$rsb["cash_banca_transilvania"];
	
	?><form method="Post" action="/billing/updatebank.php">
            <div class="grid-x grid-padding-x ">
                <div class="large-2 medium-2 cell">
                    <label>Transilvania
                        <input type="text" name="cash_banca_transilvania"
                            value="<?php echo romanize($rsb["cash_banca_transilvania"])?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 cell">
                    <label>ING
                        <input type="text" name="cash_banca_ING"
                            value="<?php echo romanize($rsb["cash_banca_ING"])?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 cell">
                    <label>Trezorerie
                        <input type="text" name="cash_banca_trezorerie"
                            value="<?php echo romanize($rsb["cash_banca_trezorerie"])?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 cell">
                    <label><br />
                        <input type="submit" value="<?php echo $strModify?>" class="button" name="Submit"
                            class="button success">
                    </label>
                </div>
                <div class="large-2 medium-2 cell">
                    <label><br />
                        <p style='color:green;'><?php echo $strTotalinBank?>:
                            <strong><?php echo romanize($totalinbanca)?></strong>
                        </p>
                    </label>
                </div>
                <div class="large-2 medium-2 cell">
                    <?php
                    $tquery="SELECT SUM(fp_valoare_totala) AS facturineplatite FROM facturare_facturi_primite WHERE fp_achitat='0'";
$tresult=ezpub_query($conn,$tquery);
$trow=ezpub_fetch_array($tresult);
$TotalInvoices=$trow["facturineplatite"];?>
                    <label><br />
                        <p style='color:red;'><?php echo $strTotalToPay?>:
                            <strong><?php echo romanize($TotalInvoices)?></strong>
                        </p>
                    </label>
                </div>
            </div>
            <?php //some math
	$query4="SELECT AVG(curs_valutar_valoare) AS cursvalutar FROM curs_valutar WHERE YEAR(curs_valutar_zi)='$year';";
	$result4=ezpub_query($conn,$query4);
	$rs4=ezpub_fetch_array($result4);
	$cursmediu=$rs4["cursvalutar"]; 
	if (!$cursmediu) {$cursmediu=5;}
	$query1="SELECT SUM(factura_client_valoare_totala) AS valoaretotala FROM facturare_facturi WHERE YEAR(factura_data_emiterii)='$year' AND factura_tip=0";
	$result1=ezpub_query($conn,$query1);
	$rs1=ezpub_fetch_array($result1);
	$totalfacturi=$rs1["valoaretotala"]; 
	if (!$totalfacturi) {$totalfacturieuro=0;}
	else
	{$totalfacturieuro=$totalfacturi/$cursmediu;}
	$query2="SELECT SUM(factura_client_valoare_totala) AS neachitata FROM facturare_facturi WHERE factura_client_achitat='0' AND factura_tip=0 AND factura_client_anulat=0";
	$result2=ezpub_query($conn,$query2);
	$rs2=ezpub_fetch_array($result2);
	$totalneachitate=$rs2["neachitata"]; 
		if (!$totalneachitate) {$totalneachitateeuro=0;
	$totalneachitate=0;
	}
	else {$totalneachitateeuro=$totalneachitate/$cursmediu;}
	$query3="SELECT SUM(factura_client_valoare_totala) AS achitata FROM facturare_facturi WHERE factura_client_achitat='1' AND YEAR(factura_client_data_achitat)='$year'";
	$result3=ezpub_query($conn,$query3);
	$rs3=ezpub_fetch_array($result3);
	$totalachitate=$rs3["achitata"]; 

	if (!$totalachitate) {$totalachitate=0;
	$totalachitateeuro=0;}
	else
	{	$totalachitateeuro=$totalachitate/$cursmediu;} 	
	$query4="SELECT SUM(factura_client_valoare_totala) AS mentenanta FROM facturare_facturi WHERE factura_client_tip_activitate='M' AND YEAR(factura_data_emiterii)='$year'";
	$result4=ezpub_query($conn,$query4);
	$rs4=ezpub_fetch_array($result4);
	$totalmentenanta=$rs4["mentenanta"]; 	
	if (!$totalmentenanta) {
	$totalmentenanta=0; 
	$totalmentenantaeeuro=0;}
	else
	{$totalmentenantaeeuro=$totalmentenanta/$cursmediu;}	
	$query5="SELECT SUM(factura_client_valoare_totala) AS onetime FROM facturare_facturi WHERE factura_client_tip_activitate='O' AND YEAR(factura_data_emiterii)='$year'";
	$result5=ezpub_query($conn,$query5);
	$rs5=ezpub_fetch_array($result5);
	$totalonetime=$rs5["onetime"]; 	
		if (!$totalonetime) {
	$totalonetime=0; 
	$totalonetimeeuro=0;}
	else
	{$totalonetimeeuro=$totalonetime/$cursmediu;}
If ($totalmentenanta==0 OR $totalfacturi==0)
{$procentmentenanta=0;}
else
{	$procentmentenanta=($totalmentenanta/$totalfacturi)*100;}
If ($totalonetime==0 OR $totalfacturi==0)
{$procentonetime=0;}
else
{
$procentonetime=($totalonetime/$totalfacturi)*100;}
	$query6="SELECT AVG(factura_client_zile_achitat) AS mediezile FROM facturare_facturi WHERE YEAR(factura_data_emiterii)='$year'";
	$result6=ezpub_query($conn,$query6);
	$rs6=ezpub_fetch_array($result6);
	$mediezile=$rs6["mediezile"]; 	
	if (!$mediezile)
	{$mediezile=0;}
		 ?>
            <div class="grid-x grid-padding-x ">
                <div class="large-2 medium-2 cell">
                    <label>Total facturat
                        <p><span class="green"><?php echo romanize($totalfacturi)?> lei <br />
                                <?php echo romanize($totalfacturieuro)?> €</span></p>
                    </label>
                </div>
                <div class="large-2 medium-2 cell">
                    <label>Total încasat
                        <p><span class="green"><?php echo romanize($totalachitate)?>lei <br />
                                <?php echo romanize($totalachitateeuro)?> €</span></p>
                    </label>
                </div>
                <div class="large-2 medium-2 cell">
                    <label>Total neîncasat
                        <p class="red"><span class="red"><?php echo romanize($totalneachitate)?>lei <br />
                                <?php echo romanize($totalneachitateeuro)?> €</span></p>
                    </label>
                </div>
                <div class="large-2 medium-2 cell">
                    <label>Total mentenanță
                        <p><span class="green"><?php echo romanize($totalmentenanta)?>lei <br />
                                <?php echo romanize($totalmentenantaeeuro)?> € <br />
                                <?php echo romanize($procentmentenanta)?> %</span></p>
                    </label>
                </div>
                <div class="large-2 medium-2 cell">
                    <label>Total onetime
                        <p><span class="green"><?php echo romanize($totalonetime)?>lei <br />
                                <?php echo romanize($totalonetimeeuro)?> € <br /> <?php echo romanize($procentonetime)?>
                                %</span></p>
                    </label>
                </div>
                <div class="large-2 medium-2 cell">
                    <label>Curs mediu
                        <p><strong><?php echo romanize($cursmediu)?></strong></p>
                    </label>
                    <label>Medie zile încasare
                        <p><strong><?php echo romanize($mediezile)?></strong></p>
                    </label>
                </div>
            </div>
               <!-- facturi neplatite -->
            <div class="grid-x grid-padding-x ">
                <div class="large-12 medium-12 small-12 cell">
                    <?php 
$query="SELECT *FROM facturare_facturi_primite WHERE fp_achitat=0 ORDER BY fp_data_scadenta ASC";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
    ?>
                    <h3><?php echo $strInvoicesToBePaid?></h3>
                    <table width="100%" class="unstriped">
                        <thead>
                            <tr>
                                <th><?php echo $strNumber?></th>
                                <th><?php echo $strIssuedDate?></th>
                                <th><?php echo $strDeadline?></th>
                                <th><?php echo $strSupplier?></th>
                                <th><?php echo $strTotal?></th>
                                <th><?php echo $strValue?></th>
                                <th><?php echo $strVAT?></th>
                                <th><?php echo $strView?></th>
                                <th><?php echo $strPayout?></th>
                            </tr>
                        </thead>
                        <?php 
While ($row=ezpub_fetch_array($result)){
$string = $row["fp_data_scadenta"];//string variable
$date = date('Y-m-d',time());//date variable

$time1 = strtotime($string);
$time2 = strtotime($date);
$datediff = $time1 - $time2;
$scadenta= round($datediff / (60 * 60 * 24));
if($scadenta >10 ){
   echo"<tr>";
}
else{
    echo"<tr style=\"background-color:#F87C63;color:#ffffff;\">";
}
    		
            echo "<td>$row[fp_numar_factura] $datediff</td>
			<td>". date("d.m.Y",strtotime($row["fp_data_emiterii"]))."</td>
			<td>". date("d.m.Y",strtotime($row["fp_data_scadenta"]))."</td>
			<td width=\"15%\">$row[fp_nume_furnizor]</td>
			<td align=\"right\">". romanize($row["fp_valoare_totala"])."</td>
			<td align=\"right\">". romanize($row["fp_valoare_neta"])."</td>
			<td align=\"right\">". romanize($row["fp_valoare_TVA"])."</td>";
?>
                        <div class="full reveal" id="exampleModal1_<?php echo $row["fp_index_download"]?>" data-reveal>
                            <iframe src="<?php echo $strSiteURL?>/billing/viewinvoice.php?type=0&option=show&cID=<?php echo $row["fp_index_download"]?>"
                                frameborder="0" style="border:0" Width="100%" height="1000"></iframe>
                            <button class="close-button" data-close aria-label="Close modal" type="button">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <td align="center"><i class="fa-xl fas fa-search" title="<?php echo $strView?>"
                                data-open="exampleModal1_<?php echo $row["fp_index_download"]?>"></i></td>
                        <?php 
                  
		 echo "<td align=\"center\"><a href=\"$strSiteURL/billing/sitepayout.php?cID=$row[fp_id]\"><i class=\"fas fa-money-bill-alt fa-xl\" title=\"$strPayout\"></i></a></td></tr>";
}
$tquery="SELECT SUM(fp_valoare_totala) AS facturineplatite FROM facturare_facturi_primite WHERE fp_achitat='0'";
$tresult=ezpub_query($conn,$tquery);
$trow=ezpub_fetch_array($tresult);
$TotalInvoices=$trow["facturineplatite"];
echo "<tr style=\"background-color:#F87C63;color:#ffffff;\"><td><strong>$strTotal</strong></td><td  colspan=\"7\"><em></em></td><td align=\"right\"><strong>".romanize($TotalInvoices)." lei </strong></td></tr>";
echo "</tbody><tfoot><tr><td></td><td  colspan=\"7\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
?>
                </div>
            </div>
  

      <!-- sfârșit facturi neplatite -->
      
      <!-- Taburi cu facturi neîncasate -->
      <div class="grid-x grid-margin-x" style="margin-top: 30px;">
          <div class="large-12 cell">
              <h3><?php echo $strInvoicesToBeCollected?></h3>
          </div>
      </div>
      
      <div class="grid-x grid-margin-x">
          <div class="large-12 cell">
              <ul class="tabs" data-tabs id="unpaid-invoices-tabs">
                  <li class="tabs-title is-active"><a href="#panel-mari" aria-selected="true"><i class="fas fa-coins"></i> Cele Mai Mari</a></li>
                  <li class="tabs-title"><a href="#panel-vechi"><i class="fas fa-calendar-times"></i> Cele Mai Vechi</a></li>
                  <li class="tabs-title"><a href="#panel-intarziate"><i class="fas fa-exclamation-triangle"></i> Întârziate</a></li>
                  <li class="tabs-title"><a href="#panel-toate"><i class="fas fa-list"></i> Toate Facturile</a></li>
              </ul>
              
              <div class="tabs-content" data-tabs-content="unpaid-invoices-tabs">
                  <!-- Panel 1: Cele mai mari facturi neîncasate -->
                  <div class="tabs-panel is-active" id="panel-mari">
                      <fieldset class="fieldset">
                          <legend>Top 20 - Cele Mai Mari Facturi Neîncasate</legend>
                          <?php
                          $stmt_mari = mysqli_prepare($conn, "SELECT factura_ID, factura_numar, factura_data_emiterii, 
                              factura_client_denumire, factura_client_valoare_totala, 
                              DATEDIFF(CURDATE(), factura_data_emiterii) as zile_intarziere
                              FROM facturare_facturi 
                              WHERE factura_client_achitat='0' AND factura_client_inchisa='1'
                              ORDER BY factura_client_valoare_totala DESC 
                              LIMIT 20");
                          mysqli_stmt_execute($stmt_mari);
                          $result_mari = mysqli_stmt_get_result($stmt_mari);
                          
                          if (mysqli_num_rows($result_mari) > 0) {
                              echo '<table class="hover stack">';
                              echo '<thead><tr>';
                              echo '<th>Nr. Factură</th>';
                              echo '<th>Data</th>';
                              echo '<th>Client</th>';
                              echo '<th>Valoare</th>';
                              echo '<th>Zile Întârziere</th>';
                              echo '<th>Acțiuni</th>';
                              echo '</tr></thead><tbody>';
                              
                              $total_mari = 0;
                              while ($row = mysqli_fetch_assoc($result_mari)) {
                                  $total_mari += $row['factura_client_valoare_totala'];
                                  $zile_class = $row['zile_intarziere'] > 90 ? 'background-color:#F87C63;color:#fff;' : 
                                               ($row['zile_intarziere'] > 60 ? 'background-color:#FFA500;' : '');
                                  
                                  echo '<tr style="' . $zile_class . '">';
                                  echo '<td>' . htmlspecialchars($row['factura_numar'], ENT_QUOTES, 'UTF-8') . '</td>';
                                  echo '<td>' . date('d.m.Y', strtotime($row['factura_data_emiterii'])) . '</td>';
                                  echo '<td>' . htmlspecialchars($row['factura_client_denumire'], ENT_QUOTES, 'UTF-8') . '</td>';
                                  echo '<td class="text-right"><strong>' . number_format($row['factura_client_valoare_totala'], 2, '.', ',') . ' lei</strong></td>';
                                  echo '<td class="text-center">' . $row['zile_intarziere'] . ' zile</td>';
                                  echo '<td><a href="siteinvoices.php?mode=edit&cID=' . htmlspecialchars($row['factura_ID'], ENT_QUOTES, 'UTF-8') . '" class="button tiny"><i class="fas fa-edit"></i></a></td>';
                                  echo '</tr>';
                              }
                              
                              echo '<tr style="background-color:#F87C63;color:#fff;font-weight:bold;">';
                              echo '<td colspan="3">TOTAL</td>';
                              echo '<td class="text-right">' . number_format($total_mari, 2, '.', ',') . ' lei</td>';
                              echo '<td colspan="2"></td>';
                              echo '</tr>';
                              echo '</tbody></table>';
                          } else {
                              echo '<div class="callout success">Nu există facturi neîncasate</div>';
                          }
                          mysqli_stmt_close($stmt_mari);
                          ?>
                      </fieldset>
                  </div>
                  
                  <!-- Panel 2: Cele mai vechi facturi neîncasate -->
                  <div class="tabs-panel" id="panel-vechi">
                      <fieldset class="fieldset">
                          <legend>Top 20 - Cele Mai Vechi Facturi Neîncasate</legend>
                          <?php
                          $stmt_vechi = mysqli_prepare($conn, "SELECT factura_ID, factura_numar, factura_data_emiterii, 
                              factura_client_denumire, factura_client_valoare_totala,
                              DATEDIFF(CURDATE(), factura_data_emiterii) as zile_intarziere
                              FROM facturare_facturi 
                              WHERE factura_client_achitat='0' AND factura_client_inchisa='1'
                              ORDER BY factura_data_emiterii ASC 
                              LIMIT 20");
                          mysqli_stmt_execute($stmt_vechi);
                          $result_vechi = mysqli_stmt_get_result($stmt_vechi);
                          
                          if (mysqli_num_rows($result_vechi) > 0) {
                              echo '<table class="hover stack">';
                              echo '<thead><tr>';
                              echo '<th>Nr. Factură</th>';
                              echo '<th>Data Emitere</th>';
                              echo '<th>Client</th>';
                              echo '<th>Valoare</th>';
                              echo '<th>Vechime (zile)</th>';
                              echo '<th>Acțiuni</th>';
                              echo '</tr></thead><tbody>';
                              
                              $total_vechi = 0;
                              while ($row = mysqli_fetch_assoc($result_vechi)) {
                                  $total_vechi += $row['factura_client_valoare_totala'];
                                  $zile_class = $row['zile_intarziere'] > 180 ? 'background-color:#8B0000;color:#fff;' : 
                                               ($row['zile_intarziere'] > 90 ? 'background-color:#F87C63;color:#fff;' : 
                                               ($row['zile_intarziere'] > 60 ? 'background-color:#FFA500;' : ''));
                                  
                                  echo '<tr style="' . $zile_class . '">';
                                  echo '<td>' . htmlspecialchars($row['factura_numar'], ENT_QUOTES, 'UTF-8') . '</td>';
                                  echo '<td>' . date('d.m.Y', strtotime($row['factura_data_emiterii'])) . '</td>';
                                  echo '<td>' . htmlspecialchars($row['factura_client_denumire'], ENT_QUOTES, 'UTF-8') . '</td>';
                                  echo '<td class="text-right"><strong>' . number_format($row['factura_client_valoare_totala'], 2, '.', ',') . ' lei</strong></td>';
                                  echo '<td class="text-center"><strong>' . $row['zile_intarziere'] . ' zile</strong></td>';
                                  echo '<td><a href="siteinvoices.php?mode=edit&cID=' . htmlspecialchars($row['factura_ID'], ENT_QUOTES, 'UTF-8') . '" class="button tiny"><i class="fas fa-edit"></i></a></td>';
                                  echo '</tr>';
                              }
                              
                              echo '<tr style="background-color:#F87C63;color:#fff;font-weight:bold;">';
                              echo '<td colspan="3">TOTAL</td>';
                              echo '<td class="text-right">' . number_format($total_vechi, 2, '.', ',') . ' lei</td>';
                              echo '<td colspan="2"></td>';
                              echo '</tr>';
                              echo '</tbody></table>';
                          } else {
                              echo '<div class="callout success">Nu există facturi neîncasate</div>';
                          }
                          mysqli_stmt_close($stmt_vechi);
                          ?>
                      </fieldset>
                  </div>
                  
                  <!-- Panel 3: Facturi întârziate (peste termen de plată) -->
                  <div class="tabs-panel" id="panel-intarziate">
                      <fieldset class="fieldset">
                          <legend>Top 20 - Facturi Întârziate (Depășit Termen Plată)</legend>
                          <?php
                          $stmt_intarziate = mysqli_prepare($conn, "SELECT f.factura_ID, f.factura_numar, f.factura_data_emiterii, 
                              f.factura_client_termen, f.factura_client_denumire, f.factura_client_valoare_totala, f.factura_client_ID,
                              DATEDIFF(CURDATE(), f.factura_client_termen) as zile_intarziere,
                              COUNT(n.notificare_id) as nr_notificari,
                              MAX(n.notificare_data_trimiterii) as ultima_notificare
                              FROM facturare_facturi f
                              LEFT JOIN facturare_notificari_trimise n ON f.factura_client_ID = n.notificare_client_id 
                                  AND JSON_CONTAINS(n.notificare_facturi_intarziate, CONCAT('\"', f.factura_numar, '\"'))
                              WHERE f.factura_client_achitat='0' AND f.factura_client_inchisa='1' 
                              AND f.factura_client_termen < CURDATE()
                              GROUP BY f.factura_ID
                              ORDER BY f.factura_client_termen ASC 
                              LIMIT 20");
                          mysqli_stmt_execute($stmt_intarziate);
                          $result_intarziate = mysqli_stmt_get_result($stmt_intarziate);
                          
                          if (mysqli_num_rows($result_intarziate) > 0) {
                              echo '<table class="hover stack">';
                              echo '<thead><tr>';
                              echo '<th>Nr. Factură</th>';
                              echo '<th>Data Emitere</th>';
                              echo '<th>Termen Plată</th>';
                              echo '<th>Client</th>';
                              echo '<th>Valoare</th>';
                              echo '<th>Zile Întârziere</th>';
                              echo '<th>Notificări</th>';
                              echo '<th>Ultima Notificare</th>';
                              echo '<th>Acțiuni</th>';
                              echo '</tr></thead><tbody>';
                              
                              $total_intarziate = 0;
                              while ($row = mysqli_fetch_assoc($result_intarziate)) {
                                  $total_intarziate += $row['factura_client_valoare_totala'];
                                  $zile_class = $row['zile_intarziere'] > 90 ? 'background-color:#8B0000;color:#fff;' : 
                                               ($row['zile_intarziere'] > 60 ? 'background-color:#F87C63;color:#fff;' : 
                                               ($row['zile_intarziere'] > 30 ? 'background-color:#FFA500;' : 'background-color:#FFEB3B;'));
                                  
                                  echo '<tr style="' . $zile_class . '">';
                                  echo '<td>' . htmlspecialchars($row['factura_numar'], ENT_QUOTES, 'UTF-8') . '</td>';
                                  echo '<td>' . date('d.m.Y', strtotime($row['factura_data_emiterii'])) . '</td>';
                                  echo '<td>' . date('d.m.Y', strtotime($row['factura_client_termen'])) . '</td>';
                                  echo '<td>' . htmlspecialchars($row['factura_client_denumire'], ENT_QUOTES, 'UTF-8') . '</td>';
                                  echo '<td class="text-right"><strong>' . number_format($row['factura_client_valoare_totala'], 2, '.', ',') . ' lei</strong></td>';
                                  echo '<td class="text-center"><strong>' . $row['zile_intarziere'] . ' zile</strong></td>';
                                  
                                  // Coloană notificări
                                  $nr_notif = (int)$row['nr_notificari'];
                                  if ($nr_notif > 0) {
                                      echo '<td class="text-center"><span class="badge warning">' . $nr_notif . '</span></td>';
                                  } else {
                                      echo '<td class="text-center"><span class="badge secondary">0</span></td>';
                                  }
                                  
                                  // Coloană ultima notificare
                                  if ($row['ultima_notificare']) {
                                      echo '<td class="text-center">' . date('d.m.Y H:i', strtotime($row['ultima_notificare'])) . '</td>';
                                  } else {
                                      echo '<td class="text-center">-</td>';
                                  }
                                  
                                  echo '<td><a href="siteinvoices.php?mode=edit&cID=' . htmlspecialchars($row['factura_ID'], ENT_QUOTES, 'UTF-8') . '" class="button tiny"><i class="fas fa-edit"></i></a></td>';
                                  echo '</tr>';
                              }
                              
                              echo '<tr style="background-color:#F87C63;color:#fff;font-weight:bold;">';
                              echo '<td colspan="4">TOTAL ÎNTÂRZIAT</td>';
                              echo '<td class="text-right">' . number_format($total_intarziate, 2, '.', ',') . ' lei</td>';
                              echo '<td colspan="4"></td>';
                              echo '</tr>';
                              echo '</tbody></table>';
                          } else {
                              echo '<div class="callout success">Nu există facturi întârziate! Toate plățile sunt la zi.</div>';
                          }
                          mysqli_stmt_close($stmt_intarziate);
                          ?>
                      </fieldset>
                  </div>
                  
                  <!-- Panel 4: Toate facturile neîncasate cu export Excel -->
                  <div class="tabs-panel" id="panel-toate">
                      <fieldset class="fieldset">
                          <legend>Toate Facturile Neîncasate</legend>
                          <div class="grid-x grid-margin-x">
                              <div class="large-12 cell text-right" style="margin-bottom: 15px;">
                                  <a href="<?php echo $strSiteURL; ?>/billing/export_unpaid_invoices.php" class="button success">
                                      <i class="fas fa-file-excel"></i> Export Excel
                                  </a>
                              </div>
                          </div>
                          <?php
                          $stmt_toate = mysqli_prepare($conn, "SELECT f.factura_ID, f.factura_numar, f.factura_data_emiterii, 
                              f.factura_client_termen, f.factura_client_denumire, f.factura_client_valoare_totala, f.factura_client_ID,
                              DATEDIFF(CURDATE(), f.factura_data_emiterii) as zile_vechime,
                              DATEDIFF(CURDATE(), f.factura_client_termen) as zile_intarziere,
                              COUNT(n.notificare_id) as nr_notificari,
                              MAX(n.notificare_data_trimiterii) as ultima_notificare
                              FROM facturare_facturi f
                              LEFT JOIN facturare_notificari_trimise n ON f.factura_client_ID = n.notificare_client_id 
                                  AND JSON_CONTAINS(n.notificare_facturi_intarziate, CONCAT('\"', f.factura_numar, '\"'))
                              WHERE f.factura_client_achitat='0' AND f.factura_client_inchisa='1'
                              GROUP BY f.factura_ID
                              ORDER BY f.factura_data_emiterii ASC");
                          mysqli_stmt_execute($stmt_toate);
                          $result_toate = mysqli_stmt_get_result($stmt_toate);
                          
                          if (mysqli_num_rows($result_toate) > 0) {
                              echo '<table class="hover stack" id="table-toate-facturi">';
                              echo '<thead><tr>';
                              echo '<th>Nr. Factură</th>';
                              echo '<th>Data Emitere</th>';
                              echo '<th>Termen Plată</th>';
                              echo '<th>Client</th>';
                              echo '<th>Valoare</th>';
                              echo '<th>Vechime</th>';
                              echo '<th>Status</th>';
                              echo '<th>Notificări</th>';
                              echo '<th>Ultima Notificare</th>';
                              echo '<th>Acțiuni</th>';
                              echo '</tr></thead><tbody>';
                              
                              $total_toate = 0;
                              $count = 0;
                              while ($row = mysqli_fetch_assoc($result_toate)) {
                                  $count++;
                                  $total_toate += $row['factura_client_valoare_totala'];
                                  
                                  // Determinare status și culoare
                                  $status = '';
                                  $row_style = '';
                                  if ($row['zile_intarziere'] > 0) {
                                      if ($row['zile_intarziere'] > 90) {
                                          $status = 'Critic (>' . $row['zile_intarziere'] . ' zile)';
                                          $row_style = 'background-color:#8B0000;color:#fff;';
                                      } elseif ($row['zile_intarziere'] > 60) {
                                          $status = 'Urgent (' . $row['zile_intarziere'] . ' zile)';
                                          $row_style = 'background-color:#F87C63;color:#fff;';
                                      } elseif ($row['zile_intarziere'] > 30) {
                                          $status = 'Atenție (' . $row['zile_intarziere'] . ' zile)';
                                          $row_style = 'background-color:#FFA500;';
                                      } else {
                                          $status = 'Întârziat (' . $row['zile_intarziere'] . ' zile)';
                                          $row_style = 'background-color:#FFEB3B;';
                                      }
                                  } else {
                                      $zile_ramase = abs($row['zile_intarziere']);
                                      $status = 'În termen (' . $zile_ramase . ' zile)';
                                      $row_style = '';
                                  }
                                  
                                  echo '<tr style="' . $row_style . '">';
                                  echo '<td>' . htmlspecialchars($row['factura_numar'], ENT_QUOTES, 'UTF-8') . '</td>';
                                  echo '<td>' . date('d.m.Y', strtotime($row['factura_data_emiterii'])) . '</td>';
                                  echo '<td>' . date('d.m.Y', strtotime($row['factura_client_termen'])) . '</td>';
                                  echo '<td>' . htmlspecialchars($row['factura_client_denumire'], ENT_QUOTES, 'UTF-8') . '</td>';
                                  echo '<td class="text-right"><strong>' . number_format($row['factura_client_valoare_totala'], 2, '.', ',') . ' lei</strong></td>';
                                  echo '<td class="text-center">' . $row['zile_vechime'] . ' zile</td>';
                                  echo '<td>' . $status . '</td>';
                                  
                                  // Coloană notificări
                                  $nr_notif = (int)$row['nr_notificari'];
                                  if ($nr_notif > 0) {
                                      echo '<td class="text-center"><span class="badge warning">' . $nr_notif . '</span></td>';
                                  } else {
                                      echo '<td class="text-center"><span class="badge secondary">0</span></td>';
                                  }
                                  
                                  // Coloană ultima notificare
                                  if ($row['ultima_notificare']) {
                                      echo '<td class="text-center">' . date('d.m.Y H:i', strtotime($row['ultima_notificare'])) . '</td>';
                                  } else {
                                      echo '<td class="text-center">-</td>';
                                  }
                                  
                                  echo '<td><a href="siteinvoices.php?mode=edit&cID=' . htmlspecialchars($row['factura_ID'], ENT_QUOTES, 'UTF-8') . '" class="button tiny"><i class="fas fa-edit"></i></a></td>';
                                  echo '</tr>';
                              }
                              
                              echo '<tr style="background-color:#F87C63;color:#fff;font-weight:bold;">';
                              echo '<td colspan="4">TOTAL (' . $count . ' facturi)</td>';
                              echo '<td class="text-right">' . number_format($total_toate, 2, '.', ',') . ' lei</td>';
                              echo '<td colspan="5"></td>';
                              echo '</tr>';
                              echo '</tbody></table>';
                          } else {
                              echo '<div class="callout success">Nu există facturi neîncasate! Felicitări!</div>';
                          }
                          mysqli_stmt_close($stmt_toate);
                          ?>
                      </fieldset>
                  </div>
              </div>
          </div>
      </div>
      <!-- Sfârșit taburi facturi neîncasate -->
       
            <?php
// Generare ani calendaristici: anul curent și 3 ani anteriori (indiferent de date în BD)
$current_year = date('Y');
$current_month = date('n');
$available_years = [
    $current_year,
    $current_year - 1,
    $current_year - 2,
    $current_year - 3
];

// Verificare și setare variabilă VATRegime (definită în company.php)
if (!isset($VATRegime)) {
    $VATRegime = 0; // Default: folosește fp_data_scadenta
}

// Funcție optimizată pentru date lunare
function getMonthlyData($conn, $year, $max_month, $VATRegime) {
    // Query optimizat - calculul depinde de VATRegime
    // VATRegime=1: TVA la încasare (din facturile încasate în luna respectivă)
    // VATRegime=0: TVA la facturare (din facturile emise în luna respectivă)
    
    if ($VATRegime == 1) {
        // TVA la încasare - calculăm după data achitării
        // 1. Total facturat: după data emiterii
        $query_facturat = "SELECT 
            MONTH(factura_data_emiterii) as luna,
            COUNT(DISTINCT factura_ID) as total_facturi,
            COALESCE(SUM(factura_client_valoare_totala), 0) as total_facturat
        FROM facturare_facturi 
        WHERE YEAR(factura_data_emiterii)='$year' AND factura_tip=0
        GROUP BY MONTH(factura_data_emiterii)";

        $result_facturat = ezpub_query($conn, $query_facturat);
        $data = [];
        while ($row = ezpub_fetch_array($result_facturat)) {
            $data[(int)$row['luna']] = [
                'luna' => (int)$row['luna'],
                'total_facturi' => $row['total_facturi'],
                'total_facturat' => $row['total_facturat'],
                'total_incasat' => 0 // va fi completat mai jos
            ];
        }

        // 2. Total încasat: după data încasării (corect)
        $query_incasat = "SELECT 
            MONTH(factura_client_data_achitat) as luna,
            COALESCE(SUM(factura_client_valoare_totala), 0) as total_incasat
        FROM facturare_facturi 
        WHERE factura_client_achitat='1' 
            AND YEAR(factura_client_data_achitat)='$year' 
            AND factura_tip=0
        GROUP BY MONTH(factura_client_data_achitat)";

        $result_incasat = ezpub_query($conn, $query_incasat);
        while ($row = ezpub_fetch_array($result_incasat)) {
            $luna = (int)$row['luna'];
            if (!isset($data[$luna])) {
                $data[$luna] = [
                    'luna' => $luna,
                    'total_facturi' => 0,
                    'total_facturat' => 0,
                    'total_incasat' => 0
                ];
            }
            $data[$luna]['total_incasat'] = $row['total_incasat'];
        }

        // 3. TVA încasat: după data încasării
        $query_tva = "SELECT 
            MONTH(factura_client_data_achitat) as luna,
            COALESCE(SUM(factura_client_valoare_tva), 0) as total_tva_incasat
        FROM facturare_facturi 
        WHERE factura_client_achitat='1' 
            AND YEAR(factura_client_data_achitat)='$year' 
            AND factura_tip=0
        GROUP BY MONTH(factura_client_data_achitat)";

        $result_tva = ezpub_query($conn, $query_tva);
        while ($row_tva = ezpub_fetch_array($result_tva)) {
            $luna = (int)$row_tva['luna'];
            if (!isset($data[$luna])) {
                $data[$luna] = [
                    'luna' => $luna,
                    'total_facturi' => 0,
                    'total_facturat' => 0,
                    'total_incasat' => 0
                ];
            }
            $data[$luna]['total_tva_incasat'] = $row_tva['total_tva_incasat'];
        }

    } else {
        // TVA la facturare - calculăm după data emiterii
        $query = "SELECT 
            MONTH(factura_data_emiterii) as luna,
            COUNT(DISTINCT factura_ID) as total_facturi,
            COALESCE(SUM(factura_client_valoare_totala), 0) as total_facturat,
            COALESCE(SUM(CASE WHEN factura_client_achitat='1' AND YEAR(factura_client_data_achitat)='$year' THEN factura_client_valoare_totala ELSE 0 END), 0) as total_incasat,
            COALESCE(SUM(factura_client_valoare_tva), 0) as total_tva_incasat
        FROM facturare_facturi 
        WHERE YEAR(factura_data_emiterii)='$year' AND factura_tip=0
        GROUP BY MONTH(factura_data_emiterii)";
        
        $result = ezpub_query($conn, $query);
        $data = [];
        while ($row = ezpub_fetch_array($result)) {
            $data[(int)$row['luna']] = $row;
        }
    }
    
    // Query pentru plăți primite - câmpul depinde de VATRegime
    $tva_field = ($VATRegime == 1) ? 'fp_data_achitat' : 'fp_data_scadenta';
    $query_fp = "SELECT 
        MONTH($tva_field) as luna,
        COALESCE(SUM(fp_valoare_totala), 0) as total_plati,
        COALESCE(SUM(fp_valoare_tva), 0) as total_tva_platit
    FROM facturare_facturi_primite 
    WHERE YEAR($tva_field)='$year'
    GROUP BY MONTH($tva_field)";
    
    $result_fp = ezpub_query($conn, $query_fp);
    while ($row = ezpub_fetch_array($result_fp)) {
        $luna = (int)$row['luna'];
        if (!isset($data[$luna])) {
            $data[$luna] = [
                'luna' => $luna,
                'total_facturi' => 0,
                'total_facturat' => 0,
                'total_incasat' => 0,
                'total_tva_incasat' => 0
            ];
        }
        $data[$luna]['total_plati'] = $row['total_plati'];
        $data[$luna]['total_tva_platit'] = $row['total_tva_platit'];
    }
    
    // Completare toate lunile (1 până la max_month) cu valori 0 dacă nu există date
    for ($m = 1; $m <= $max_month; $m++) {
        if (!isset($data[$m])) {
            $data[$m] = [
                'luna' => $m,
                'total_facturi' => 0,
                'total_facturat' => 0,
                'total_incasat' => 0,
                'total_tva_incasat' => 0,
                'total_plati' => 0,
                'total_tva_platit' => 0
            ];
        } else {
            if (!isset($data[$m]['total_plati'])) $data[$m]['total_plati'] = 0;
            if (!isset($data[$m]['total_tva_platit'])) $data[$m]['total_tva_platit'] = 0;
        }
        $data[$m]['tva_diferenta'] = $data[$m]['total_tva_incasat'] - $data[$m]['total_tva_platit'];
        
        // Adăugare alias-uri pentru grafice (compatibilitate)
        $data[$m]['facturat'] = $data[$m]['total_facturat'];
        $data[$m]['incasat'] = $data[$m]['total_incasat'];
        $data[$m]['platit'] = $data[$m]['total_plati'];
        $data[$m]['tva_incasat'] = $data[$m]['total_tva_incasat'];
        $data[$m]['tva_platit'] = $data[$m]['total_tva_platit'];
    }
    
    ksort($data);
    return $data;
}

// Generare date pentru toți cei 4 ani (anul curent + 3 ani anteriori)
$comparison_data = [];

// Pentru toți cei 4 ani - generăm date complete pentru JSON/grafice
foreach ($available_years as $year_val) {
    // Pentru anul curent folosim doar lunile până la luna curentă, pentru restul toate cele 12
    $max_month = ($year_val == $current_year) ? $current_month : 12;
    $comparison_data[$year_val] = getMonthlyData($conn, $year_val, $max_month, $VATRegime);
}

// Salvare date în JSON pentru grafic
$json_data = json_encode($comparison_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($hddpath . "/" . $charts_folder . "/finance_comparison_" . date('Y-m-d') . ".json", $json_data);

// Șterge fișierele mai vechi de 3 zile din folderul charts
$charts_dir = $hddpath . '/' . $charts_folder;
$three_days = 3 * 24 * 60 * 60;
if (function_exists('delete_older_than')) {
    delete_older_than($charts_dir, $three_days);
}
?>
            
            <h3><?php echo $strComparison?></h3>
            
            <ul class="tabs" data-tabs id="comparison-tabs">
                <?php 
                for ($i = 0; $i < min(3, count($available_years) - 1); $i++) {
                    $year1 = $available_years[$i];
                    $year2 = $available_years[$i + 1];
                    $active = ($i === 0) ? 'is-active' : '';
                    echo "<li class=\"tabs-title $active\"><a href=\"#panel-$i\" aria-selected=\"true\">$year1 vs $year2</a></li>";
                }
                ?>
            </ul>
            
            <div class="tabs-content" data-tabs-content="comparison-tabs">
                <?php 
                for ($tab = 0; $tab < min(3, count($available_years) - 1); $tab++) {
                    $year1 = $available_years[$tab];
                    $year2 = $available_years[$tab + 1];
                    $active = ($tab === 0) ? 'is-active' : '';
                    $data_year1 = $comparison_data[$year1] ?? [];
                    $data_year2 = $comparison_data[$year2] ?? [];
                ?>
                <div class="tabs-panel <?php echo $active?>" id="panel-<?php echo $tab?>">
                    <div class="grid-x grid-padding-x">
                        <div class="large-6 medium-6 small-12 cell">
                            <fieldset class="fieldset">
                                <h2><?php echo $year1?></h2>
                                <table width="100%" class="small-font-table hover">
                                    <thead>
                                        <tr>
                                            <th><?php echo $strNumber?></th>
                                            <th><?php echo $strMonth?></th>
                                            <th>Facturi</th>
                                            <th>Facturat</th>
                                            <th>Încasat</th>
                                            <th>TVA</th>
                                            <th>Plăți</th>
                                            <th>TVA Ded.</th>
                                            <th>TVA Plată</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Inițializare totaluri pentru year1
                                        $total_year1_facturi = 0;
                                        $total_year1_facturat = 0;
                                        $total_year1_incasat = 0;
                                        $total_year1_tva_incasat = 0;
                                        $total_year1_plati = 0;
                                        $total_year1_tva_platit = 0;
                                        $total_year1_tva_diferenta = 0;
                                        
                                        // Pentru primul tab (anul curent), afișăm doar până la luna curentă
                                        // Pentru restul taburilor (ani anteriori), afișăm toate cele 12 luni
                                        $is_current_year_tab = ($tab === 0 && $year1 == $current_year);
                                        foreach ($data_year1 as $month_num => $month_data) {
                                            $dateObj = DateTime::createFromFormat('!m', $month_num);
                                            $formatter = new IntlDateFormatter("ro_RO", IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'Europe/Bucharest', IntlDateFormatter::GREGORIAN, 'MMMM');
                                            $monthname = $formatter->format($dateObj);
                                            
                                            // Acumulare totaluri
                                            $total_year1_facturi += $month_data['total_facturi'];
                                            $total_year1_facturat += $month_data['total_facturat'];
                                            $total_year1_incasat += $month_data['total_incasat'];
                                            $total_year1_tva_incasat += $month_data['total_tva_incasat'];
                                            $total_year1_plati += $month_data['total_plati'];
                                            $total_year1_tva_platit += $month_data['total_tva_platit'];
                                            $total_year1_tva_diferenta += $month_data['tva_diferenta'];
                                            
                                            echo "<tr>";
                                            echo "<td>$month_num</td>";
                                            echo "<td>$monthname</td>";
                                            echo "<td align=\"right\">{$month_data['total_facturi']}</td>";
                                            echo "<td align=\"right\">".romanize($month_data['total_facturat'])."</td>";
                                            echo "<td align=\"right\">".romanize($month_data['total_incasat'])."</td>";
                                            echo "<td align=\"right\">".romanize($month_data['total_tva_incasat'])."</td>";
                                            echo "<td align=\"right\">".romanize($month_data['total_plati'])."</td>";
                                            echo "<td align=\"right\">".romanize($month_data['total_tva_platit'])."</td>";
                                            echo "<td align=\"right\">".romanize($month_data['tva_diferenta'])."</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                        <tr class="total-row">
                                            <td colspan="2"><strong>Total</strong></td>
                                            <td align="right"><strong><?php echo $total_year1_facturi?></strong></td>
                                            <td align="right"><strong><?php echo romanize($total_year1_facturat)?></strong></td>
                                            <td align="right"><strong><?php echo romanize($total_year1_incasat)?></strong></td>
                                            <td align="right"><strong><?php echo romanize($total_year1_tva_incasat)?></strong></td>
                                            <td align="right"><strong><?php echo romanize($total_year1_plati)?></strong></td>
                                            <td align="right"><strong><?php echo romanize($total_year1_tva_platit)?></strong></td>
                                            <td align="right"><strong><?php echo romanize($total_year1_tva_diferenta)?></strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </fieldset>
                        </div>
                        
                        <div class="large-6 medium-6 small-12 cell">
                            <fieldset class="fieldset">
                                <h2><?php echo $year2?></h2>
                                <table width="100%" class="small-font-table hover">
                                    <thead>
                                        <tr>
                                            <th><?php echo $strNumber?></th>
                                            <th><?php echo $strMonth?></th>
                                            <th>Facturi</th>
                                            <th>Facturat</th>
                                            <th>Încasat</th>
                                            <th>TVA</th>
                                            <th>Plăți</th>
                                            <th>TVA Ded.</th>
                                            <th>TVA Plată</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Inițializare totaluri pentru year2
                                        $total_year2_facturi = 0;
                                        $total_year2_facturat = 0;
                                        $total_year2_incasat = 0;
                                        $total_year2_tva_incasat = 0;
                                        $total_year2_plati = 0;
                                        $total_year2_tva_platit = 0;
                                        $total_year2_tva_diferenta = 0;
                                        
                                        foreach ($data_year2 as $month_num => $month_data) {
                                            $dateObj = DateTime::createFromFormat('!m', $month_num);
                                            $formatter = new IntlDateFormatter("ro_RO", IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'Europe/Bucharest', IntlDateFormatter::GREGORIAN, 'MMMM');
                                            $monthname = $formatter->format($dateObj);
                                            
                                            // Acumulare totaluri
                                            $total_year2_facturi += $month_data['total_facturi'];
                                            $total_year2_facturat += $month_data['total_facturat'];
                                            $total_year2_incasat += $month_data['total_incasat'];
                                            $total_year2_tva_incasat += $month_data['total_tva_incasat'];
                                            $total_year2_plati += $month_data['total_plati'];
                                            $total_year2_tva_platit += $month_data['total_tva_platit'];
                                            $total_year2_tva_diferenta += $month_data['tva_diferenta'];
                                            
                                            echo "<tr>";
                                            echo "<td>$month_num</td>";
                                            echo "<td>$monthname</td>";
                                            echo "<td align=\"right\">{$month_data['total_facturi']}</td>";
                                            echo "<td align=\"right\">".romanize($month_data['total_facturat'])."</td>";
                                            echo "<td align=\"right\">".romanize($month_data['total_incasat'])."</td>";
                                            echo "<td align=\"right\">".romanize($month_data['total_tva_incasat'])."</td>";
                                            echo "<td align=\"right\">".romanize($month_data['total_plati'])."</td>";
                                            echo "<td align=\"right\">".romanize($month_data['total_tva_platit'])."</td>";
                                            echo "<td align=\"right\">".romanize($month_data['tva_diferenta'])."</td>";
                                            echo "</tr>";
                                        }
                                        ?><tr class="total-row">
                                            <td colspan="2"><strong>Total</strong></td>
                                            <td align="right"><strong><?php echo $total_year2_facturi?></strong></td>
                                            <td align="right"><strong><?php echo romanize($total_year2_facturat)?></strong></td>
                                            <td align="right"><strong><?php echo romanize($total_year2_incasat)?></strong></td>
                                            <td align="right"><strong><?php echo romanize($total_year2_tva_incasat)?></strong></td>
                                            <td align="right"><strong><?php echo romanize($total_year2_plati)?></strong></td>
                                            <td align="right"><strong><?php echo romanize($total_year2_tva_platit)?></strong></td>
                                            <td align="right"><strong><?php echo romanize($total_year2_tva_diferenta)?></strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </fieldset>
                        </div>
                    </div>
                    
                    <div class="grid-x grid-padding-x">
                        <div class="large-12 cell">
                            <div class="callout secondary">
                                <h4>Diferențe <?php echo "$year1 - $year2"?>:</h4>
                                <table width="100%" class="small-font-table">
                                    <tr>
                                        <th align="left">Indicator</th>
                                        <th align="left"><?php echo $year1?></th>
                                        <th align="left"><?php echo $year2?></th>
                                        <th align="left">Diferență</th>
                                        <th align="left">%</th>
                                    </tr>
                                    <?php
                                    // Număr facturi
                                    $diff_facturi = $total_year1_facturi - $total_year2_facturi;
                                    $diff_facturi_percent = ($total_year2_facturi > 0) ? (($diff_facturi / $total_year2_facturi) * 100) : 0;
                                    $facturi_color = ($diff_facturi >= 0) ? 'profit' : 'loss';
                                    $facturi_icon = ($diff_facturi >= 0) ? '▲' : '▼';
                                    
                                    // Facturat
                                    $diff_facturat = $total_year1_facturat - $total_year2_facturat;
                                    $diff_facturat_percent = ($total_year2_facturat > 0) ? (($diff_facturat / $total_year2_facturat) * 100) : 0;
                                    $facturat_color = ($diff_facturat >= 0) ? 'profit' : 'loss';
                                    $facturat_icon = ($diff_facturat >= 0) ? '▲' : '▼';
                                    
                                    // Încasat
                                    $diff_incasat = $total_year1_incasat - $total_year2_incasat;
                                    $diff_incasat_percent = ($total_year2_incasat > 0) ? (($diff_incasat / $total_year2_incasat) * 100) : 0;
                                    $incasat_color = ($diff_incasat >= 0) ? 'profit' : 'loss';
                                    $incasat_icon = ($diff_incasat >= 0) ? '▲' : '▼';
                                    
                                    // Plăți
                                    $diff_plati = $total_year1_plati - $total_year2_plati;
                                    $diff_plati_percent = ($total_year2_plati > 0) ? (($diff_plati / $total_year2_plati) * 100) : 0;
                                    $plati_color = ($diff_plati <= 0) ? 'profit' : 'loss'; // Pentru plăți, mai puțin e mai bine
                                    $plati_icon = ($diff_plati >= 0) ? '▲' : '▼';
                                    
                                    // TVA diferență
                                    $diff_tva = $total_year1_tva_diferenta - $total_year2_tva_diferenta;
                                    $diff_tva_percent = ($total_year2_tva_diferenta != 0) ? (($diff_tva / abs($total_year2_tva_diferenta)) * 100) : 0;
                                    $tva_color = ($diff_tva >= 0) ? 'profit' : 'loss';
                                    $tva_icon = ($diff_tva >= 0) ? '▲' : '▼';
                                    ?>
                                    <tr>
                                        <td><strong>Număr facturi</strong></td>
                                        <td><?php echo $total_year1_facturi?></td>
                                        <td><?php echo $total_year2_facturi?></td>
                                        <td class="<?php echo $facturi_color?>"><?php echo $diff_facturi?> <?php echo $facturi_icon?></td>
                                        <td class="<?php echo $facturi_color?>"><?php echo number_format($diff_facturi_percent, 2)?>%</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total facturat</strong></td>
                                        <td><?php echo romanize($total_year1_facturat)?></td>
                                        <td><?php echo romanize($total_year2_facturat)?></td>
                                        <td class="<?php echo $facturat_color?>"><?php echo romanize($diff_facturat)?> <?php echo $facturat_icon?></td>
                                        <td class="<?php echo $facturat_color?>"><?php echo number_format($diff_facturat_percent, 2)?>%</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total încasat</strong></td>
                                        <td><?php echo romanize($total_year1_incasat)?></td>
                                        <td><?php echo romanize($total_year2_incasat)?></td>
                                        <td class="<?php echo $incasat_color?>"><?php echo romanize($diff_incasat)?> <?php echo $incasat_icon?></td>
                                        <td class="<?php echo $incasat_color?>"><?php echo number_format($diff_incasat_percent, 2)?>%</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total plăți</strong></td>
                                        <td><?php echo romanize($total_year1_plati)?></td>
                                        <td><?php echo romanize($total_year2_plati)?></td>
                                        <td class="<?php echo $plati_color?>"><?php echo romanize($diff_plati)?> <?php echo $plati_icon?></td>
                                        <td class="<?php echo $plati_color?>"><?php echo number_format($diff_plati_percent, 2)?>%</td>
                                    </tr>
                                    <tr>
                                        <td><strong>TVA de plată</strong></td>
                                        <td><?php echo romanize($total_year1_tva_diferenta)?></td>
                                        <td><?php echo romanize($total_year2_tva_diferenta)?></td>
                                        <td class="<?php echo $tva_color?>"><?php echo romanize($diff_tva)?> <?php echo $tva_icon?></td>
                                        <td class="<?php echo $tva_color?>"><?php echo number_format($diff_tva_percent, 2)?>%</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            
            <div class="grid-x grid-padding-x">
                <div class="large-12 cell">
                    <a href="<?php echo $hddpath . "/" . $charts_folder . "/finance_comparison_" . date('Y-m-d') . ".json"?>" class="button secondary" download>
                        <i class="fas fa-download"></i> Descarcă Date JSON
                    </a>
                </div>
            </div>
            
            <!-- Secțiune grafice Chart.js cu Taburi Foundation -->
            <div class="grid-x grid-margin-x" style="margin-top: 30px;">
                <div class="large-12 cell">
                    <h3>Grafice Comparative</h3>
                </div>
            </div>
            
            <!-- Taburi pentru grafice -->
            <div class="grid-x grid-margin-x">
                <div class="large-12 cell">
                    <ul class="tabs" data-tabs id="finance-charts-tabs">
                        <li class="tabs-title is-active"><a href="#panel-incasari" aria-selected="true"><i class="fas fa-chart-line"></i> Încasări</a></li>
                        <li class="tabs-title"><a href="#panel-plati"><i class="fas fa-chart-line"></i> Plăți</a></li>
                        <li class="tabs-title"><a href="#panel-facturat"><i class="fas fa-chart-bar"></i> Facturat vs Încasat</a></li>
                        <li class="tabs-title"><a href="#panel-numar"><i class="fas fa-chart-bar"></i> Număr Facturi</a></li>
                        <li class="tabs-title"><a href="#panel-tva"><i class="fas fa-chart-bar"></i> TVA Diferență</a></li>
                        <li class="tabs-title"><a href="#panel-total"><i class="fas fa-chart-line"></i> Total Facturat</a></li>
                    </ul>
                    
                    <div class="tabs-content" data-tabs-content="finance-charts-tabs">
                        <!-- Panel 1: Evoluție Încasări -->
                        <div class="tabs-panel is-active" id="panel-incasari">
                            <fieldset class="fieldset">
                                <legend>Evoluție Încasări (ultimii 3 ani)</legend>
                                <canvas id="chartIncasari" style="max-height: 400px;"></canvas>
                            </fieldset>
                        </div>
                        
                        <!-- Panel 2: Evoluție Plăți -->
                        <div class="tabs-panel" id="panel-plati">
                            <fieldset class="fieldset">
                                <legend>Evoluție Plăți (ultimii 3 ani)</legend>
                                <canvas id="chartPlati" style="max-height: 400px;"></canvas>
                            </fieldset>
                        </div>
                        
                        <!-- Panel 3: Facturat vs Încasat -->
                        <div class="tabs-panel" id="panel-facturat">
                            <fieldset class="fieldset">
                                <legend>Total Facturat vs Încasat (<?php echo date('Y')?>)</legend>
                                <canvas id="chartFacturatIncasat" style="max-height: 400px;"></canvas>
                            </fieldset>
                        </div>
                        
                        <!-- Panel 4: Număr Facturi -->
                        <div class="tabs-panel" id="panel-numar">
                            <fieldset class="fieldset">
                                <legend>Număr de Facturi (ultimii 3 ani)</legend>
                                <canvas id="chartNumarFacturi" style="max-height: 400px;"></canvas>
                            </fieldset>
                        </div>
                        
                        <!-- Panel 5: TVA Diferență -->
                        <div class="tabs-panel" id="panel-tva">
                            <fieldset class="fieldset">
                                <legend>Comparație TVA Diferență</legend>
                                <canvas id="chartTVA" style="max-height: 400px;"></canvas>
                            </fieldset>
                        </div>
                        
                        <!-- Panel 6: Total Facturat -->
                        <div class="tabs-panel" id="panel-total">
                            <fieldset class="fieldset">
                                <legend>Total Facturat pe Lună (ultimii 3 ani)</legend>
                                <canvas id="chartTotalFacturat" style="max-height: 400px;"></canvas>
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Chart.js Library -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
            
            <script>
            console.log('Script de grafice pornit...');
            
            // Verificare existență canvas-uri
            console.log('Canvas chartIncasari:', document.getElementById('chartIncasari'));
            console.log('Canvas chartPlati:', document.getElementById('chartPlati'));
            
            // Încărcare date JSON prin endpoint PHP (pentru acces la fișiere în afara public_html)
            console.log('Încep fetch la:', '<?php echo $strSiteURL?>/billing/get_finance_data.php');
            
            fetch('<?php echo $strSiteURL?>/billing/get_finance_data.php')
                .then(response => {
                    console.log('Response primit:', response);
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Date încărcate din JSON:', data);
                    console.log('Tipul datelor:', typeof data);
                    console.log('Chei disponibile:', Object.keys(data));
                    
                    // Pregătire date pentru grafice
                    const monthNames = ['Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun', 'Iul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    
                    // Ani calendaristici: anul curent + 3 ani anteriori (mereu acești 4 ani, indiferent de date în BD)
                    const currentYear = <?php echo $current_year?>;
                    const years = [currentYear, currentYear - 1, currentYear - 2, currentYear - 3];
                    console.log('Ani pentru grafice:', years);
                    const colors = [
                        'rgba(54, 162, 235, 0.8)',   // Albastru - anul curent (2026)
                        'rgba(255, 99, 132, 0.8)',   // Roșu - anul trecut (2025)
                        'rgba(75, 192, 192, 0.8)',   // Verde-turcoaz - acum 2 ani (2024)
                        'rgba(255, 206, 86, 0.8)'    // Galben - acum 3 ani (2023)
                    ];
                    
                    // Grafic 1: Evoluție Încasări (TOȚI cei 4 ani calendaristici)
                    const incasariDatasets = years.map((year, index) => {
                        return {
                            label: year,
                            data: Array.from({length: 12}, (_, i) => {
                                const month = i + 1;
                                // Returnează valoarea sau 0 dacă nu există anul sau luna
                                return (data[year] && data[year][month]) ? parseFloat(data[year][month].incasat) || 0 : 0;
                            }),
                            borderColor: colors[index],
                            backgroundColor: colors[index].replace('0.8', '0.2'),
                            tension: 0.4,
                            fill: true
                        };
                    });
                    
                    new Chart(document.getElementById('chartIncasari'), {
                        type: 'line',
                        data: {
                            labels: monthNames,
                            datasets: incasariDatasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            label += new Intl.NumberFormat('ro-RO', { 
                                                style: 'currency', 
                                                currency: 'RON' 
                                            }).format(context.parsed.y);
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return new Intl.NumberFormat('ro-RO', { 
                                                style: 'currency', 
                                                currency: 'RON',
                                                maximumFractionDigits: 0
                                            }).format(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                    
                    // Grafic 2: Evoluție Plăți (TOȚI cei 4 ani calendaristici)
                    const platiDatasets = years.map((year, index) => {
                        return {
                            label: year,
                            data: Array.from({length: 12}, (_, i) => {
                                const month = i + 1;
                                return (data[year] && data[year][month]) ? parseFloat(data[year][month].platit) || 0 : 0;
                            }),
                            borderColor: colors[index],
                            backgroundColor: colors[index].replace('0.8', '0.2'),
                            tension: 0.4,
                            fill: true
                        };
                    });
                    
                    new Chart(document.getElementById('chartPlati'), {
                        type: 'line',
                        data: {
                            labels: monthNames,
                            datasets: platiDatasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            label += new Intl.NumberFormat('ro-RO', { 
                                                style: 'currency', 
                                                currency: 'RON' 
                                            }).format(context.parsed.y);
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return new Intl.NumberFormat('ro-RO', { 
                                                style: 'currency', 
                                                currency: 'RON',
                                                maximumFractionDigits: 0
                                            }).format(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                    
                    // Grafic 3: TVA Diferență (Bar Chart) - TOȚI cei 4 ani calendaristici
                    const tvaDatasets = years.map((year, index) => {
                        return {
                            label: year,
                            data: Array.from({length: 12}, (_, i) => {
                                const month = i + 1;
                                if (data[year] && data[year][month]) {
                                    const tva_incasat = parseFloat(data[year][month].tva_incasat) || 0;
                                    const tva_platit = parseFloat(data[year][month].tva_platit) || 0;
                                    return tva_incasat - tva_platit;
                                }
                                return 0;
                            }),
                            backgroundColor: colors[index],
                            borderColor: colors[index],
                            borderWidth: 1
                        };
                    });
                    
                    new Chart(document.getElementById('chartTVA'), {
                        type: 'bar',
                        data: {
                            labels: monthNames,
                            datasets: tvaDatasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            label += new Intl.NumberFormat('ro-RO', { 
                                                style: 'currency', 
                                                currency: 'RON' 
                                            }).format(context.parsed.y);
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    ticks: {
                                        callback: function(value) {
                                            return new Intl.NumberFormat('ro-RO', { 
                                                style: 'currency', 
                                                currency: 'RON',
                                                maximumFractionDigits: 0
                                            }).format(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                    
                    // Grafic 4: Facturat vs Încasat (anul curent real) - arată toate lunile inclusiv cu 0
                    // Folosim currentYear deja definit mai sus
                    const facturatData = Array.from({length: 12}, (_, i) => {
                        const month = i + 1;
                        // Verificare dacă există date pentru anul curent
                        return (data[currentYear] && data[currentYear][month]) ? parseFloat(data[currentYear][month].facturat) || 0 : 0;
                    });
                    const incasatData = Array.from({length: 12}, (_, i) => {
                        const month = i + 1;
                        return (data[currentYear] && data[currentYear][month]) ? parseFloat(data[currentYear][month].incasat) || 0 : 0;
                    });
                    
                    new Chart(document.getElementById('chartFacturatIncasat'), {
                        type: 'bar',
                        data: {
                            labels: monthNames,
                            datasets: [
                                {
                                    label: 'Facturat',
                                    data: facturatData,
                                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1
                                },
                                {
                                    label: 'Încasat',
                                    data: incasatData,
                                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            label += new Intl.NumberFormat('ro-RO', { 
                                                style: 'currency', 
                                                currency: 'RON' 
                                            }).format(context.parsed.y);
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return new Intl.NumberFormat('ro-RO', { 
                                                style: 'currency', 
                                                currency: 'RON',
                                                maximumFractionDigits: 0
                                            }).format(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                    
                    // Grafic 5: Număr de Facturi (Bar Chart pentru cei 4 ani calendaristici)
                    const facturiDatasets = years.map((year, index) => {
                        return {
                            label: year,
                            data: Array.from({length: 12}, (_, i) => {
                                const month = i + 1;
                                return (data[year] && data[year][month]) ? parseInt(data[year][month].total_facturi) || 0 : 0;
                            }),
                            backgroundColor: colors[index],
                            borderColor: colors[index],
                            borderWidth: 1
                        };
                    });
                    
                    new Chart(document.getElementById('chartNumarFacturi'), {
                        type: 'bar',
                        data: {
                            labels: monthNames,
                            datasets: facturiDatasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            label += context.parsed.y + ' facturi';
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        callback: function(value) {
                                            return value;
                                        }
                                    }
                                }
                            }
                        }
                    });
                    
                    // Grafic 6: Total Facturat pe Lună (Line Chart pentru cei 4 ani calendaristici)
                    const totalFacturatDatasets = years.map((year, index) => {
                        return {
                            label: year,
                            data: Array.from({length: 12}, (_, i) => {
                                const month = i + 1;
                                return (data[year] && data[year][month]) ? parseFloat(data[year][month].facturat) || 0 : 0;
                            }),
                            borderColor: colors[index],
                            backgroundColor: colors[index].replace('0.8', '0.2'),
                            tension: 0.4,
                            fill: true
                        };
                    });
                    
                    new Chart(document.getElementById('chartTotalFacturat'), {
                        type: 'line',
                        data: {
                            labels: monthNames,
                            datasets: totalFacturatDatasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            label += new Intl.NumberFormat('ro-RO', { 
                                                style: 'currency', 
                                                currency: 'RON' 
                                            }).format(context.parsed.y);
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return new Intl.NumberFormat('ro-RO', { 
                                                style: 'currency', 
                                                currency: 'RON',
                                                maximumFractionDigits: 0
                                            }).format(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Eroare la încărcarea datelor JSON:', error);
                });
            </script>
            
            <?php
// Datele sunt salvate în JSON pentru utilizare în grafice
?>
    </div>
</div>