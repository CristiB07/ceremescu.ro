<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';

$strPageTitle="Administrare raportări deșeuri";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$clearence=$_SESSION['function'];

$month= date('m');
$year=date('Y');
$day = date('d');

if ((isset( $_GET['aloc'])) && !empty( $_GET['aloc'])){
$aloc=$_GET['aloc'];}
else{
$aloc=0;}
if ((isset( $_GET['cl'])) && !empty( $_GET['cl'])){
$cl=$_GET['cl'];}
else{
$cl=0;}
if ((isset( $_GET['yr'])) && !empty( $_GET['yr'])){
$fyear=$_GET['yr'];
$year=$fyear;
}
else{
$fyear=0;}
if ((isset( $_GET['wID'])) && !empty( $_GET['wID'])){
$waste=$_GET['wID'];}
else{
$waste=0;}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <script language="JavaScript" type="text/JavaScript">
            <!-- jump menu
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
        </script>
        <div class="grid-x grid-padding-x ">
            <div class="large-3 medium-3 cell">
                <label> <?php echo $strSeenBy?>
                    <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                        <option value="sitewastereporting.php?cl=<?php echo $cl?>&wID=<?php echo $waste?>&yr=<?php echo $year?>&aloc=<?php echo $aloc ?>" selected><?php echo $strPick?></option>
                        <?php
                        if ($clearence!='ADMIN' || $clearence!='MANAGER'){
			$query7="SELECT * FROM date_utilizatori WHERE utilizator_ID='$uid' ORDER By utilizator_Nume ASC";
            }
            else {
            $query7="SELECT * FROM date_utilizatori WHERE utilizator_Function='USER' ORDER By utilizator_Nume ASC";
                }
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			if ((isset($_GET['aloc'])) && !empty($_GET['aloc'])){
			If ($seenby['strSeenBy']==$_GET['aloc']) {
			echo"<option selected value=\"sitewastereporting.php?cl=$cl&wID=$waste&yr=$year&aloc=".$seenby['utilizator_Code']."\">". $seenby['strUserName']."</option>";}
			else{echo"<option value=\"sitewastereporting.php?cl=$cl&wID=$waste&yr=$year&aloc=".$seenby['utilizator_Code']."\">". $seenby['utilizator_Nume']." ". $seenby['utilizator_Prenume']."</option>";}}
			else {echo"<option value=\"sitewastereporting.php?cl=$cl&wID=$waste&yr=$year&aloc=".$seenby['utilizator_Code']."\">". $seenby['utilizator_Nume']." ". $seenby['utilizator_Prenume']."</option>";}
			}
			?>
                    </select></label>
            </div>
            <div class="large-3 medium-3 cell">
                <label> <?php echo $strClient?>
                    <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                        <option
                            value="sitewastereporting.php?cl=<?php echo $cl?>&wID=<?php echo $waste?>&yr=<?php echo $year?>&aloc=<?php echo $aloc ?>"
                            selected><?php echo $strPick?></option>
                        <?php
			$query7="SELECT DISTINCT raportare_client_id, Client_Denumire, ID_Client, Client_Aloc FROM clienti_date, deseuri_raportari WHERE raportare_client_id=ID_Client ORDER By Client_Denumire ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			if ((isset($_GET['cl'])) && !empty($_GET['cl'])){
			If ($seenby['ID_Client']==$_GET['cl']) {
			echo"<option selected value=\"sitewastereporting.php?aloc=$aloc&wID=$waste&yr=$year&cl=".$seenby['ID_Client']."\">". $seenby['Client_Denumire']."</option>";}
			else{echo"<option value=\"sitewastereporting.php?aloc=$aloc&wID=$waste&yr=$year&cl=".$seenby['ID_Client']."\">". $seenby['Client_Denumire']."</option>";}}
			else {echo"<option value=\"sitewastereporting.php?aloc=$aloc&wID=$waste&yr=$year&cl=".$seenby['ID_Client']."\">". $seenby['Client_Denumire']."</option>";}
			}
			?>
                    </select></label>
            </div>
            <div class="large-3 medium-3 cell">
                <label> <?php echo $strWaste?>
                    <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                        <option value="00" selected>--</option>
                        <?php 	$query7="SELECT DISTINCT raportare_cod_deseu FROM deseuri_raportari ORDER By raportare_cod_deseu ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			if ((isset($_GET['wID'])) && !empty($_GET['wID'])){
			If ($seenby['raportare_cod_deseu']==$_GET['wID']) {
			echo"<option selected value=\"sitewastereporting.php?aloc=$aloc&wID=".$seenby['raportare_cod_deseu']."&yr=$year&cl=$cl\">". $seenby['raportare_cod_deseu']."</option>";}
			else{echo"<option value=\"sitewastereporting.php?aloc=$aloc&wID=".$seenby['raportare_cod_deseu']."&yr=$year&cl=$cl\">". $seenby['raportare_cod_deseu']."</option>";}}
			else {echo"<option value=\"sitewastereporting.php?aloc=$aloc&wID=".$seenby['raportare_cod_deseu']."&yr=$year&cl=$cl\">". $seenby['raportare_cod_deseu']."</option>";}
			}
			?>
        
                    </select></label>
            </div>
            <div class="large-3 medium-3 cell">
                <label> <?php echo $strYear?>
                    <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                        <option
                            value="sitewastereporting.php?cl=<?php echo $cl?>&wID=<?php echo $waste?>&yr=<?php echo $year?>&aloc=<?php echo $aloc ?>"
                            selected><?php echo $strPick?></option>
                        <?php
			 			$query7="SELECT DISTINCT raportare_an_raportare as iyear FROM deseuri_raportari ORDER By raportare_an_raportare DESC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
						if ((isset($_GET['yr'])) && !empty($_GET['yr'])){
			If ($seenby['iyear']==$_GET['yr']) {
			echo"<option selected value=\"sitewastereporting.php?aloc=$aloc&cl$cl&wID=$waste&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}
			else{echo"<option value=\"sitewastereporting.php?aloc=$aloc&cl=$cl&wID=$waste&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}}
			else {
			if ($year==$seenby['iyear']) 
			{echo "<option value=\"sitewastereporting.php?aloc=$aloc&cl=$cl&wID=$waste&yr=".$seenby['iyear']." \" selected >". $seenby['iyear']."</option>";}
			else {echo"<option value=\"sitewastereporting.php?aloc=$aloc&cl=$cl&wID=$waste&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}
			}
			}
			 ?>
                    </select></label>
            </div>
        </div>

        <?php
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"wastereportselector.php\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";
$query="SELECT c.ID_Client, c.Client_Denumire, c.Client_Aloc, r.raportare_an_raportare, r.raportare_cod_deseu, d.cd_id
FROM deseuri_raportari r
JOIN clienti_date c ON c.ID_Client = r.raportare_client_id
LEFT JOIN deseuri_coduri d ON d.cd_full = r.raportare_cod_deseu
WHERE r.raportare_an_raportare = '$year' 
GROUP BY c.ID_Client, c.Client_Denumire, r.raportare_an_raportare, r.raportare_cod_deseu, d.cd_id";
if ($aloc!='0'){
$query= $query . " AND c.Client_Aloc='$aloc'";
};
if ($cl!='0'){
$query= $query . " AND raportare_client_id='$cl'";
};
if ($waste!='0'){
$query= $query . " AND raportare_cod_deseu='$waste'";
};
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY raportare_cod_deseu ASC $pages->limit";
$result=ezpub_query($conn,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>
<div class=\"paginate\"><a href=\"sitewastereporting.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;</div>";
}
else {
?>
        <div class="paginate">
            <?php
echo $strTotal . " " .$numar." ".$strReports;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"sitewastereporting.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
        </div>
        <table width="100%">
            <thead>
                <tr>
                    <th><?php echo $strClient?></th>
                    <th><?php echo $strWaste?></th>
                    <th><?php echo $strYear?></th>
                    <th><?php echo $strEdit?></th>
                    <th><?php echo $strDetails?></th>
                    <th><?php echo $strExportToExcel?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[raportare_cod_deseu]</td>
			<td>$row[raportare_an_raportare]</td>
			 <td><a href=\"wastereporting.php?mode=fill&wID=$row[raportare_cod_deseu]&client=$row[ID_Client]&year=$year&cod_id=$row[cd_id]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			 <td><a href=\"wastereporting.php?mode=show&wID=$row[raportare_cod_deseu]&client=$row[ID_Client]&year=$year&cod_id=$row[cd_id]\"><i class=\"fa fa-search-plus fa-xl\" title=\"$strEdit\"></i></a></td>
			 <td><a href=\"wastereports2excel.php?wID=$row[raportare_cod_deseu]&client=$row[ID_Client]&year=$year&cod_id=$row[cd_id]\"><i class=\"fa fa-file-excel fa-xl\" title=\"$strEdit\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td colspan=\"6\">&nbsp;</td></tr></tfoot></table>";
?>
                <div class="paginate">
                    <?php
echo $pages->display_pages() . " <a href=\"sitewastereporting.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
                </div>
                <?php
}

?>
    </div>
</div>
<?php
include '../bottom.php';
?>