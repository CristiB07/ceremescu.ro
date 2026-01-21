<?php // Last Modified Time: Thursday, August 21, 2025 at 4:44:51 PM Eastern European Summer Time ?>
<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';

$strPageTitle="Administrare facturi primite";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$month= date('m');
$year=date('Y');
$day = date('d');

if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/index.php?message=MLF");
}

if ((isset( $_GET['aloc'])) && !empty( $_GET['aloc'])){
    if (!filter_var($_GET['aloc'], FILTER_VALIDATE_INT)) {
        $aloc = 0;
    } else {
        $aloc = (int)$_GET['aloc'];
    }
} else {
    $aloc = 0;
}

if ((isset( $_GET['cl'])) && !empty( $_GET['cl'])){
    if (!preg_match('/^[0-9]+$/', $_GET['cl'])) {
        $cl = 0;
    } else {
        $cl = $_GET['cl'];
    }
} else {
    $cl = 0;
}

if ((isset( $_GET['act'])) && !empty( $_GET['act'])){
    if (!filter_var($_GET['act'], FILTER_VALIDATE_INT)) {
        $act = 0;
    } else {
        $act = (int)$_GET['act'];
    }
} else {
    $act = 0;
}

if ((isset( $_GET['paid']))){
    if (!in_array($_GET['paid'], ['0', '1', '3'], true)) {
        $paid = 3;
    } else {
        $paid = $_GET['paid'];
    }
} else {
    $paid = 3;
}

if ((isset( $_GET['yr'])) && !empty( $_GET['yr'])){
    if (!filter_var($_GET['yr'], FILTER_VALIDATE_INT) || $_GET['yr'] < 2000 || $_GET['yr'] > 2100) {
        $fyear = 0;
        $year = date('Y');
    } else {
        $fyear = (int)$_GET['yr'];
        $year = $fyear;
    }
} else {
    $fyear = 0;
}

if ((isset( $_GET['fmonth'])) && !empty( $_GET['fmonth'])){
    if (!filter_var($_GET['fmonth'], FILTER_VALIDATE_INT) || $_GET['fmonth'] < 1 || $_GET['fmonth'] > 12) {
        $fmonth = 0;
    } else {
        $fmonth = (int)$_GET['fmonth'];
    }
} else {
    $fmonth = 0;
}

?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <script language="JavaScript" type="text/JavaScript">
            function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
  }
</script>

        <?php
echo "<h1>$strPageTitle</h1>";
?>
        <script language="JavaScript" type="text/JavaScript">
            <!-- jump menu
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
        </script>
        <div class="grid-x grid-padding-x ">
            <div class="large-3 medium-3 small-3 cell">
                <label> <?php echo $strSupplier?>
                    <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                        <option
                            value="sitereceivedinvoices.php?act=<?php echo $act?>&cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&paid=<?php echo $paid?>&aloc=<?php echo $aloc ?>"
                            selected><?php echo $strPick?></option>
                        <?php
			$query7="SELECT DISTINCT fp_nume_furnizor, fp_CUI_furnizor FROM facturare_facturi_primite ORDER By fp_nume_furnizor ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			if ((isset($_GET['cl'])) && !empty($_GET['cl'])){
			If ($seenby['fp_CUI_furnizor']==$_GET['cl']) {
			echo"<option selected value=\"sitereceivedinvoices.php?act=$act&fmonth=$fmonth&yr=$year&paid=$paid&cl=".$seenby['fp_CUI_furnizor']."\">". $seenby['fp_nume_furnizor']."</option>";}
			else{echo"<option value=\"sitereceivedinvoices.php?act=$act&fmonth=$fmonth&yr=$year&paid=$paid&cl=".$seenby['fp_CUI_furnizor']."\">". $seenby['fp_nume_furnizor']."</option>";}}
			else {echo"<option value=\"sitereceivedinvoices.php?act=$act&fmonth=$fmonth&yr=$year&paid=$paid&cl=".$seenby['fp_CUI_furnizor']."\">". $seenby['fp_nume_furnizor']."</option>";}
			}
			?>
                    </select></label>
            </div>
            <div class="large-3 medium-3 small-3 cell">
                <label> <?php echo $strMonth?>
                    <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                        <option value="00" selected>--</option>
                        <?php for ( $m = 1; $m <= 12; $m ++) {

     		//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
		$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
				echo "<option value=\"sitereceivedinvoices.php?act=".urlencode($act)."&cl=".urlencode($cl)."&yr=".urlencode($year)."&paid=".urlencode($paid)."&fmonth=".$m."\">".htmlspecialchars($monthname, ENT_QUOTES, 'UTF-8')."</option>";}
				 
			?>
                    </select> </label>
            </div>
            <div class="large-3 medium-3 small-3 cell">
                <label> <?php echo $strYear?>
                    <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                        <option
                            value="sitereceivedinvoices.php?act=<?php echo $act?>&cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&paid=<?php echo $paid?>&aloc=<?php echo $aloc ?>"
                            selected><?php echo $strPick?></option>
                        <?php
			 			$query7="SELECT DISTINCT YEAR(fp_data_emiterii) as iyear FROM facturare_facturi_primite ORDER By YEAR(fp_data_emiterii) DESC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
						if ((isset($_GET['yr'])) && !empty($_GET['yr'])){
			If ($seenby['iyear']==$_GET['yr']) {
			echo"<option selected value=\"sitereceivedinvoices.php?act=$act&cl$cl&fmonth=$fmonth&paid=$paid&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}
			else{echo"<option value=\"sitereceivedinvoices.php?act=$act&cl=$cl&paid=$paid&fmonth=$fmonth&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}}
			else {
			if ($year==$seenby['iyear']) 
			{echo "<option value=\"sitereceivedinvoices.php?act=$act&cl=$cl&paid=$paid&fmonth=$fmonth&yr=".$seenby['iyear']." \" selected >". $seenby['iyear']."</option>";}
			else {echo"<option value=\"sitereceivedinvoices.php?act=$act&cl=$cl&paid=$paid&fmonth=$fmonth&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}
			}
			}
			 ?>
                    </select></label>
            </div>
            <div class="large-3 medium-3 small-3 cell">
                <label> <?php echo $strPaid?>
                    <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                        <option
                            value="sitereceivedinvoices.php?act=<?php echo urlencode($act)?>&cl=<?php echo urlencode($cl)?>&fmonth=<?php echo urlencode($fmonth)?>&yr=<?php echo urlencode($year)?>&paid=<?php echo urlencode($paid)?>"
                            selected><?php echo htmlspecialchars($strPick, ENT_QUOTES, 'UTF-8')?></option>
                        <?php
							echo"<option value=\"sitereceivedinvoices.php?act=".urlencode($act)."&cl=".urlencode($cl)."&fmonth=".urlencode($fmonth)."&yr=".urlencode($year)."&paid=1\">".htmlspecialchars($strYes, ENT_QUOTES, 'UTF-8')."</option>";
							echo"<option value=\"sitereceivedinvoices.php?act=".urlencode($act)."&cl=".urlencode($cl)."&fmonth=".urlencode($fmonth)."&yr=".urlencode($year)."&paid=0\">".htmlspecialchars($strNo, ENT_QUOTES, 'UTF-8')."</option>";
							
							
		?>
                    </select></label>
            </div>
        </div>

        <?php
echo "<div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">";
	 ?>
        <?php
// Build query with prepared statement
$sql = "SELECT * FROM facturare_facturi_primite WHERE YEAR(fp_data_emiterii) = ?";
$params = [$year];
$types = "i";

if ($cl != '0' && $cl != 0){
    $sql .= " AND fp_CUI_furnizor = ?";
    $params[] = $cl;
    $types .= "s";
}

if ($fmonth != '0' && $fmonth != 0){
    $sql .= " AND MONTH(fp_data_emiterii) = ?";
    $params[] = $fmonth;
    $types .= "i";
}

if ($paid != '3'){
    $sql .= " AND fp_achitat = ?";
    $params[] = $paid;
    $types .= "s";
}

// Count total records
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$numar = mysqli_num_rows($result);
mysqli_stmt_close($stmt);

$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 

// Execute main query with pagination
$sql .= " ORDER BY fp_data_emiterii DESC $pages->limit";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
?>
        <div class="paginate">
            <?php
$stmt = mysqli_prepare($conn, "SELECT COUNT(fp_achitat) AS neplatite FROM facturare_facturi_primite WHERE fp_achitat = 0");
mysqli_stmt_execute($stmt);
$resultp = mysqli_stmt_get_result($stmt);
$rowp = mysqli_fetch_assoc($resultp);
mysqli_stmt_close($stmt);
$unpaid=$rowp["neplatite"];
echo $strTotal . " " .$numar." ".$strInvoices ." / ". $unpaid ." ". $strUnpayed;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"sitereceivedinvoices.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
        </div>
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
                    <th><?php echo $strPaymentDate?></th>
                    <th><?php echo $strDays?></th>
                    <th><?php echo $strView?></th>
                    <th><?php echo $strPayout?></th>
                </tr>
            </thead>
            <?php 
While ($row=mysqli_fetch_assoc($result)){

    		echo"<tr><td>".htmlspecialchars($row['fp_numar_factura'], ENT_QUOTES, 'UTF-8')."</td>
			<td>" . date("d.m.Y",strtotime($row["fp_data_emiterii"]))."</td>
			<td>" . date("d.m.Y",strtotime($row["fp_data_scadenta"]))."</td>
			<td width=\"15%\">".htmlspecialchars($row['fp_nume_furnizor'], ENT_QUOTES, 'UTF-8')."</td>
			<td align=\"right\">". romanize($row["fp_valoare_totala"])."</td>
			<td align=\"right\">". romanize($row["fp_valoare_neta"])."</td>
			<td align=\"right\">". romanize($row["fp_valoare_TVA"])."</td>";
If ($row["fp_achitat"]=="1") {
     $dataemiterii=strtotime($row["fp_data_emiterii"]);
		$incasare=strtotime($row["fp_data_achitat"]);
		$datediff=$incasare-$dataemiterii;
		$zile=round($datediff / (60 * 60 * 24));
	echo "<td>". date("d.m.Y", strtotime($row["fp_data_achitat"]))."</td>
    <td>$zile</td>";
}	
else 	{
	echo "<td>&nbsp;</td>
        <td>&nbsp;</td>";
}
             ?>
            <div class="full reveal" id="exampleModal1_<?php echo htmlspecialchars($row["fp_index_download"], ENT_QUOTES, 'UTF-8')?>" data-reveal>
                <iframe src="viewinvoice.php?type=0&option=show&cID=<?php echo urlencode($row["fp_index_download"])?>"
                    frameborder="0" style="border:0" Width="100%" height="1000"></iframe>
                <button class="close-button" data-close aria-label="Close modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <td><i class="fa-xl fas fa-search" title="<?php echo htmlspecialchars($strView, ENT_QUOTES, 'UTF-8')?>"
                    data-open="exampleModal1_<?php echo htmlspecialchars($row["fp_index_download"], ENT_QUOTES, 'UTF-8')?>"></i></td>
            <?php 
                    if ($row["fp_achitat"]==0)
			 {
		 echo "<td><a href=\"sitepayout.php?cID=".urlencode($row['fp_id'])."\"><i class=\"fas fa-money-bill-alt fa-xl\" title=\"".htmlspecialchars($strPayout, ENT_QUOTES, 'UTF-8')."\"></i></a></td>";
			 }
		 else {
		 echo "<td color=\"green\"><i class=\"fas fa-money-bill fa-xl\" title=\"".htmlspecialchars($strPayout, ENT_QUOTES, 'UTF-8')."\"></i></td>";
		 }
echo		"</tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"9\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
?>
            <div class="paginate">
                <?php
echo $pages->display_pages() . " <a href=\"sitereceivedinvoices.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
            </div>
    </div>
</div>
<?php
include '../bottom.php';
?>