<script>
function calcTotalAmbalaje() {
    let d = parseFloat(document.getElementsByName('gestiune_a_ambalaje_desfacere')[0]?.value.replace(',', '.') || 0);
    let p = parseFloat(document.getElementsByName('gestiune_a_ambalaje_primare')[0]?.value.replace(',', '.') || 0);
    let s = parseFloat(document.getElementsByName('gestiune_a_ambalaje_secundare')[0]?.value.replace(',', '.') || 0);
    let total = d + p + s;
    if (document.getElementsByName('gestiune_a_total')[0]) {
        document.getElementsByName('gestiune_a_total')[0].value = total.toFixed(2);
    }
}
document.addEventListener('DOMContentLoaded', function() {
    ['gestiune_a_ambalaje_desfacere','gestiune_a_ambalaje_primare','gestiune_a_ambalaje_secundare'].forEach(function(name) {
        let el = document.getElementsByName(name)[0];
        if (el) {
            el.addEventListener('input', calcTotalAmbalaje);
        }
    });
    calcTotalAmbalaje();
});
</script>
<?php
// update 30.12.2022
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle="Administrare Anexa 1 - ambalaje gestionate";
include '../dashboard/header.php';

$uid=$_SESSION['uid'];
$code=$_SESSION['code'];

$month= date('m');
$year=date('Y');
$day = date('d');

if ((isset( $_GET['aloc'])) && !empty( $_GET['aloc'])){
$aloc=$_GET['aloc'];}
else{
$aloc=$uid;}
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

?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM ambalaje_gestionate WHERE gestiune_a_id=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"success callout\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitepackagesanex1a.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){
//insert new user

	$mSQL = "INSERT INTO ambalaje_gestionate(";
	$mSQL = $mSQL . "gestiune_a_client_id,";
	$mSQL = $mSQL . "gestiune_a_ambalaj_id,";
	$mSQL = $mSQL . "gestiune_a_ambalaje_desfacere,";
	$mSQL = $mSQL . "gestiune_a_ambalaje_primare,";
	$mSQL = $mSQL . "gestiune_a_ambalaje_primare_RE,";
	$mSQL = $mSQL . "gestiune_a_ambalaje_secundare,";
	$mSQL = $mSQL . "gestiune_a_ambalaje_secundare_RE,";
	$mSQL = $mSQL . "gestiune_a_ambalaje_contaminate,";
	$mSQL = $mSQL . "gestiune_a_an_raportare,";
	$mSQL = $mSQL . "gestiune_a_total)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$_POST["gestiune_a_client_id"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["gestiune_a_ambalaj_id"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["gestiune_a_ambalaje_desfacere"] . "', ";
    $mSQL = $mSQL . "'" .$_POST["gestiune_a_ambalaje_primare"] . "', ";
    $mSQL = $mSQL . "'" .$_POST["gestiune_a_ambalaje_primare_RE"] . "', ";
    $mSQL = $mSQL . "'" .$_POST["gestiune_a_ambalaje_secundare"] . "', ";
    $mSQL = $mSQL . "'" .$_POST["gestiune_a_ambalaje_secundare_RE"] . "', ";
    $mSQL = $mSQL . "'" .$_POST["gestiune_a_ambalaje_contaminate"] . "', ";
    $mSQL = $mSQL . "'" .$_POST["gestiune_a_an_raportare"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["gestiune_a_total"] . "') ";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
else{
echo "<div class=\"success callout\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitepackagesanex1a.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
else
{// edit
$strWhereClause = " WHERE ambalaje_gestionate.gestiune_a_id=" . $_GET["cID"] . ";";
$query= "UPDATE ambalaje_gestionate SET ambalaje_gestionate.gestiune_a_client_id='" .$_POST ["gestiune_a_client_id"] . "' ," ;
$query= $query . " ambalaje_gestionate.gestiune_a_ambalaj_id='" .$_POST["gestiune_a_ambalaj_id"] . "', "; 
$query= $query . " ambalaje_gestionate.gestiune_a_ambalaje_desfacere='" .$_POST["gestiune_a_ambalaje_desfacere"] . "', "; 
$query= $query . " ambalaje_gestionate.gestiune_a_ambalaje_primare='" .$_POST["gestiune_a_ambalaje_primare"] . "', "; 
$query= $query . " ambalaje_gestionate.gestiune_a_ambalaje_primare_RE='" .$_POST["gestiune_a_ambalaje_primare_RE"] . "', "; 
$query= $query . " ambalaje_gestionate.gestiune_a_ambalaje_secundare='" .$_POST["gestiune_a_ambalaje_secundare"] . "', "; 
$query= $query . " ambalaje_gestionate.gestiune_a_ambalaje_secundare_RE='" .$_POST["gestiune_a_ambalaje_secundare_RE"] . "', "; 
$query= $query . " ambalaje_gestionate.gestiune_a_ambalaje_contaminate='" .$_POST["gestiune_a_ambalaje_contaminate"] . "', "; 
$query= $query . " ambalaje_gestionate.gestiune_a_an_raportare='" .$_POST["gestiune_a_an_raportare"] . "', "; 
$query= $query . " ambalaje_gestionate.gestiune_a_total='" .$_POST["gestiune_a_total"] . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
else{
echo "<div class=\"success callout\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitepackagesanex1a.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}
}
}
else {
?>
<?php
if (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitepackagesanex1a.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form method="post" action="sitepackagesanex1a.php?mode=new" >
			    <div class="grid-x grid-margin-x">
			  <div class="large-2 medium-2 small-3 cell">
			  <label><?php echo $strClient?></label>
	  <select name="gestiune_a_client_id" class="required">
         <option value="--" selected><?php echo $strPick?></option>
        <?php
            $clientSql = "SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, Contract_Alocat, Client_Denumire, Client_CUI, Client_Localitate, Client_Judet FROM clienti_date, clienti_contracte 
                          WHERE Contract_Alocat='$code' 
                          AND  clienti_date.ID_Client=clienti_contracte.ID_Client 
                          AND Contract_Activ=0";
            $clientRes = ezpub_query($conn, $clientSql);
            while ($clientRow = ezpub_fetch_array($clientRes)) {
                echo '<option value="' . htmlspecialchars($clientRow['ID_Client']) . '">' . htmlspecialchars($clientRow['Client_Denumire']) . '</option>';
            }
        ?>
      </select>
	  </div>
			  <div class="large-2 medium-2 small-3 cell">
	  <label><?php echo $strPackage?></label>
	  	  <select name="gestiune_a_ambalaj_id" class="required">
         <option value="--" selected><?php echo $strPick?></option>
        <?php
            $ambalajSql = "SELECT ambalaj_id, ambalaj_nume, ambalaj_cod FROM ambalaje ORDER BY ambalaj_nume ASC";
            $ambalajRes = ezpub_query($conn, $ambalajSql);
            while ($ambalajRow = ezpub_fetch_array($ambalajRes)) {
                echo '<option value="' . htmlspecialchars($ambalajRow['ambalaj_id']) . '">' . htmlspecialchars($ambalajRow['ambalaj_nume']) . '</option>';
            }
        ?>
      </select>
	  </div>
      <div class="large-1 medium-1 small-3 cell">
        <label><?php echo $strCommercialPackages?></label>
	  <input name="gestiune_a_ambalaje_desfacere" type="text" class="required" /> kg
	  </div>
      <div class="large-1 medium-1 small-3 cell">
        <label><?php echo $strPrimaryPackages?></label>
        <input name="gestiune_a_ambalaje_primare" type="text" class="required" /> kg
</div>
      <div class="large-1 medium-1 small-3 cell">
        <label><?php echo $strPrimaryPackagesRE?></label>
        <input name="gestiune_a_ambalaje_primare_RE" type="text" class="required" /> kg
	  </div>
        <div class="large-1 medium-1 small-3 cell">
            <label><?php echo $strSecondaryPackages?></label>
            <input name="gestiune_a_ambalaje_secundare" type="text" class="required" /> kg  
        </div>
        <div class="large-1 medium-1 small-3 cell">
            <label><?php echo $strSecondaryPackagesRE?></label>
            <input name="gestiune_a_ambalaje_secundare_RE" type="text" class="required" /> kg
        </div>
        <div class="large-1 medium-1 small-3 cell">
            <label><?php echo $strContaminatedPackages?></label>
            <input name="gestiune_a_ambalaje_contaminate" type="text" class="required" /> kg    
        </div>
        <div class="large-1 medium-1 small-3 cell">
            <label><?php echo $strYear?></label>
            <input name="gestiune_a_an_raportare" type="text" class="required" />
        </div>
        <div class="large-1 medium-1 small-3 cell">
            <label><?php echo $strTotal?></label>
            <input name="gestiune_a_total" type="text" class="required" /> kg
        </div>
        </div>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> <input type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
<?php
}
elseif (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM ambalaje_gestionate WHERE gestiune_a_id=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitepackagesanex1a.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form method="post" action="sitepackagesanex1a.php?mode=edit&cID=<?php echo $row['gestiune_a_id']?>" >
			       <div class="grid-x grid-margin-x">
			  <div class="large-2 medium-2 small-3 cell">
			  <label><?php echo $strClient?></label>
	  <select name="gestiune_a_client_id" class="required">
         <option value="--" selected><?php echo $strPick?></option>
        <?php
            $clientSql = "SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, Contract_Alocat, Client_Denumire, Client_CUI, Client_Localitate, Client_Judet FROM clienti_date, clienti_contracte 
                          WHERE Contract_Alocat='$code' 
                          AND  clienti_date.ID_Client=clienti_contracte.ID_Client 
                          AND Contract_Activ=0";
            $clientRes = ezpub_query($conn, $clientSql);
            while ($clientRow = ezpub_fetch_array($clientRes)) {
                echo '<option value="' . htmlspecialchars($clientRow['ID_Client']) . '"';
                if ($clientRow['ID_Client'] == $row['gestiune_a_client_id']) {
                    echo ' selected';
                }
                echo '>' . htmlspecialchars($clientRow['Client_Denumire']) . '</option>';
            }
        ?>
      </select>
	  </div>
			  <div class="large-2 medium-2 small-3 cell">
	  <label><?php echo $strPackage?></label>
	  	  <select name="gestiune_a_ambalaj_id" class="required">
         <option value="--" selected><?php echo $strPick?></option>
        <?php
            $ambalajSql = "SELECT ambalaj_id, ambalaj_nume, ambalaj_cod FROM ambalaje ORDER BY ambalaj_nume ASC";
            $ambalajRes = ezpub_query($conn, $ambalajSql);
            while ($ambalajRow = ezpub_fetch_array($ambalajRes)) {
                        echo '<option value="' . htmlspecialchars($ambalajRow['ambalaj_id']) . '"';
                        if ($ambalajRow['ambalaj_id'] == $row['gestiune_a_ambalaj_id']) {
                            echo ' selected';
                        }
                        echo '>' . htmlspecialchars($ambalajRow['ambalaj_nume']) . '</option>';
            }
        ?>
      </select>
	  </div>
      <div class="large-1 medium-1 small-3 cell">
        <label><?php echo $strCommercialPackages?></label>
	  <input name="gestiune_a_ambalaje_desfacere" type="text" class="required" value="<?php echo htmlspecialchars($row['gestiune_a_ambalaje_desfacere']); ?>" /> kg
	  </div>
      <div class="large-1 medium-1 small-3 cell">
        <label><?php echo $strPrimaryPackages?></label>
        <input name="gestiune_a_ambalaje_primare" type="text" class="required" value="<?php echo htmlspecialchars($row['gestiune_a_ambalaje_primare']); ?>" /> kg
</div>
      <div class="large-1 medium-1 small-3 cell">
        <label><?php echo $strPrimaryPackagesRE?></label>
        <input name="gestiune_a_ambalaje_primare_RE" type="text" class="required" value="<?php echo htmlspecialchars($row['gestiune_a_ambalaje_primare_RE']); ?>" /> kg
	  </div>
        <div class="large-1 medium-1 small-3 cell">
            <label><?php echo $strSecondaryPackages?></label>
            <input name="gestiune_a_ambalaje_secundare" type="text" class="required" value="<?php echo htmlspecialchars($row['gestiune_a_ambalaje_secundare']); ?>" /> kg  
        </div>
        <div class="large-1 medium-1 small-3 cell">
            <label><?php echo $strSecondaryPackagesRE?></label>
            <input name="gestiune_a_ambalaje_secundare_RE" type="text" class="required" value="<?php echo htmlspecialchars($row['gestiune_a_ambalaje_secundare_RE']); ?>" /> kg
        </div>
        <div class="large-1 medium-1 small-3 cell">
            <label><?php echo $strContaminatedPackages?></label>
            <input name="gestiune_a_ambalaje_contaminate" type="text" class="required" value="<?php echo htmlspecialchars($row['gestiune_a_ambalaje_contaminate']); ?>" /> kg    
        </div>
        <div class="large-1 medium-1 small-3 cell">
            <label><?php echo $strYear?></label>
            <input name="gestiune_a_an_raportare" type="text" class="required" value="<?php echo htmlspecialchars($row['gestiune_a_an_raportare']); ?>" />
        </div>
        <div class="large-1 medium-1 small-3 cell">
            <label><?php echo $strTotal?></label>
            <input name="gestiune_a_total" type="text" class="required" value="<?php echo htmlspecialchars($row['gestiune_a_total']); ?>" /> kg
        </div>
        </div>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> <input type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
<?php
}
else
{
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
            <div class="large-4 medium-4 cell">
                <label> <?php echo $strSeenBy?>
                    <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                        <option
                            value="sitepackagesanex1a.php?cl=<?php echo $cl?>&yr=<?php echo $year?>&aloc=<?php echo $aloc ?>"
                            selected><?php echo $strPick?></option>
                        <?php
			$query7="SELECT * FROM date_utilizatori WHERE utilizator_Function='USER' ORDER By utilizator_Nume ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			if ((isset($_GET['aloc'])) && !empty($_GET['aloc'])){
			If ($seenby['strSeenBy']==$_GET['aloc']) {
			echo"<option selected value=\"sitepackagesanex1a.php?cl=$cl&yr=$year&aloc=".$seenby['utilizator_Code']."\">". $seenby['strUserName']."</option>";}
			else{echo"<option value=\"sitepackagesanex1a.php?cl=$cl&yr=$year&aloc=".$seenby['utilizator_Code']."\">". $seenby['utilizator_Nume']." ". $seenby['utilizator_Prenume']."</option>";}}
			else {echo"<option value=\"sitepackagesanex1a.php?cl=$cl&yr=$year&aloc=".$seenby['utilizator_Code']."\">". $seenby['utilizator_Nume']." ". $seenby['utilizator_Prenume']."</option>";}
			}
			?>
                    </select></label>
            </div>
            <div class="large-4 medium-4 cell">
                <label> <?php echo $strClient?>
                    <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                        <option
                            value="sitepackagesanex1a.php?cl=<?php echo $cl?>&yr=<?php echo $year?>&aloc=<?php echo $aloc ?>"
                            selected><?php echo $strPick?></option>
                        <?php
			$query7="SELECT DISTINCT gestiune_a_client_id, Client_Denumire, ID_Client, Client_Aloc FROM clienti_date, ambalaje_gestionate WHERE gestiune_a_client_id=ID_Client AND Client_Aloc='$code' ORDER By Client_Denumire ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			if ((isset($_GET['cl'])) && !empty($_GET['cl'])){
			If ($seenby['ID_Client']==$_GET['cl']) {
			echo"<option selected value=\"sitepackagesanex1a.php?aloc=$aloc&yr=$year&cl=".$seenby['ID_Client']."\">". $seenby['Client_Denumire']."</option>";}
			else{echo"<option value=\"sitepackagesanex1a.php?aloc=$aloc&yr=$year&cl=".$seenby['ID_Client']."\">". $seenby['Client_Denumire']."</option>";}}
			else {echo"<option value=\"sitepackagesanex1a.php?aloc=$aloc&yr=$year&cl=".$seenby['ID_Client']."\">". $seenby['Client_Denumire']."</option>";}
			}
			?>
                    </select></label>
            </div>
            <div class="large-4 medium-4 cell">
                <label> <?php echo $strYear?>
                    <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                        <option
                            value="sitepackagesanex1a.php?cl=<?php echo $cl?>&yr=<?php echo $year?>&aloc=<?php echo $aloc ?>"
                            selected><?php echo $strPick?></option>
                        <?php
			 			$query7="SELECT DISTINCT gestiune_a_an_raportare as iyear FROM ambalaje_gestionate ORDER By gestiune_a_an_raportare DESC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
						if ((isset($_GET['yr'])) && !empty($_GET['yr'])){
			If ($seenby['iyear']==$_GET['yr']) {
			echo"<option selected value=\"sitepackagesanex1a.php?aloc=$aloc&cl$cl&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}
			else{echo"<option value=\"sitepackagesanex1a.php?aloc=$aloc&cl=$cl&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}}
			else {
			if ($year==$seenby['iyear']) 
			{echo "<option value=\"sitepackagesanex1a.php?aloc=$aloc&cl=$cl&yr=".$seenby['iyear']." \" selected >". $seenby['iyear']."</option>";}
			else {echo"<option value=\"sitepackagesanex1a.php?aloc=$aloc&cl=$cl&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}
			}
			}
			 ?>
                    </select></label>
            </div>
        </div>

        <?php
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"sitepackagesanex1a.php?mode=new\" class=\"button\">
<i class=\"large fa fa-plus\" title=\"$strAdd\"></i>&nbsp;$strAdd</a>
</div>
</div>";
$query="SELECT gestiune_a_id, gestiune_a_client_id, gestiune_a_ambalaj_id, gestiune_a_total, 
Client_Denumire, ambalaj_nume
FROM ambalaje_gestionate, clienti_date, ambalaje
WHERE ambalaje_gestionate.gestiune_a_client_id=clienti_date.ID_Client
AND ambalaje_gestionate.gestiune_a_ambalaj_id=ambalaje.ambalaj_id";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY Client_Denumire ASC $pages->limit";
$result=ezpub_query($conn,$query);

echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
?>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell">
<div class="paginate">
<?php
echo $strTotal . " " .$numar." ".$strReports ;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"sitepackagesanex1a.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
echo " <br /><br />";
?>
</div>
</div>
</div>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> 
<table width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strID?></th>
			<th><?php echo $strClient?></th>
			<th><?php echo $strPackage?></th>
			<th><?php echo $strQuantity?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[gestiune_a_id]</td>
			<td>$row[Client_Denumire]</td>
			<td>$row[ambalaj_nume]</td>
			<td>$row[gestiune_a_total]</td>
			  <td><a href=\"sitepackagesanex1a.php?mode=edit&cID=$row[gestiune_a_id]\" class=\"ask\"><i class=\"large fas fa-pencil-alt\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"sitepackagesanex1a.php?mode=delete&cID=$row[gestiune_a_id]\" class=\"ask\" OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"large fa fa-eraser\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"4\"><em></em></td><td>&nbsp;</td></tr></tfoot></table></div></div>";
}
}
}
?>
</div>
</div>
<?php
include '../bottom.php';
?>