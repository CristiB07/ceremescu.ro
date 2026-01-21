<?php
// update 30.12.2022
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle="Administrare Anexa 1 - deșeuri de ambalaje";
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

$nsql="DELETE FROM ambalaje_deseuri WHERE ad_id=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"success callout\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitepackagesanex1b.php\"
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

	$mSQL = "INSERT INTO ambalaje_deseuri(";
	$mSQL = $mSQL . "ad_client_id,";
	$mSQL = $mSQL . "ad_ambalaj_id,";
	$mSQL = $mSQL . "ad_operator_denumire,";
	$mSQL = $mSQL . "ad_operator_cui,";
	$mSQL = $mSQL . "ad_cod_operatiune,";
	$mSQL = $mSQL . "ad_an_raportare,";
	$mSQL = $mSQL . "ad_total)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$_POST["ad_client_id"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["ad_ambalaj_id"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["ad_operator_denumire"] . "', ";
    $mSQL = $mSQL . "'" .$_POST["ad_operator_cui"] . "', ";
    $mSQL = $mSQL . "'" .$_POST["ad_cod_operatiune"] . "', ";
    $mSQL = $mSQL . "'" .$_POST["ad_an_raportare"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["ad_total"] . "') ";
				
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
    window.location = \"sitepackagesanex1b.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
else
{// edit
$strWhereClause = " WHERE ambalaje_deseuri.ad_id=" . $_GET["cID"] . ";";
$query= "UPDATE ambalaje_deseuri SET ambalaje_deseuri.ad_client_id='" .$_POST ["ad_client_id"] . "' ," ;
$query= $query . " ambalaje_deseuri.ad_ambalaj_id='" .$_POST["ad_ambalaj_id"] . "', "; 
$query= $query . " ambalaje_deseuri.ad_operator_denumire='" .$_POST["ad_operator_denumire"] . "', "; 
$query= $query . " ambalaje_deseuri.ad_operator_cui='" .$_POST["ad_operator_cui"] . "', "; 
$query= $query . " ambalaje_deseuri.ad_cod_operatiune='" .$_POST["ad_cod_operatiune"] . "', "; 
$query= $query . " ambalaje_deseuri.ad_an_raportare='" .$_POST["ad_an_raportare"] . "', "; 
$query= $query . " ambalaje_deseuri.ad_total='" .$_POST["ad_total"] . "' "; 
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
    window.location = \"sitepackagesanex1b.php\"
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
			  <p><a href="sitepackagesanex1b.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form method="post" action="sitepackagesanex1b.php?mode=new" >
			    <div class="grid-x grid-margin-x">
			  <div class="large-2 medium-2 small-3 cell">
			  <label><?php echo $strClient?></label>
	  <select name="ad_client_id" class="required">
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
	  	  <select name="ad_ambalaj_id" class="required">
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
      <div class="large-2 medium-2 small-3 cell">
        <label><?php echo $strTotal?>  kg</label>
	  <input name="ad_total" type="text" class="required" />
	  </div>
      <div class="large-2 medium-2 small-3 cell">
        <label><?php echo $strOperator?></label>
        <input name="ad_operator_denumire" type="text" class="required" /> 
</div>
      <div class="large-2 medium-2 small-3 cell">
        <label><?php echo $strVAT?></label>
        <input name="ad_operator_cui" type="text" class="required" /> 
	  </div>
        <div class="large-1 medium-1 small-3 cell">
            <label><?php echo $strCode?></label>
            <input name="ad_cod_operatiune" type="text" class="required" />   
        </div>
        <div class="large-1 medium-1 small-3 cell">
            <label><?php echo $strYear?></label>
            <input name="ad_an_raportare" type="text" class="required" />
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
$query="SELECT * FROM ambalaje_deseuri WHERE ad_id=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitepackagesanex1b.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form method="post" action="sitepackagesanex1b.php?mode=edit&cID=<?php echo $row['ad_id']?>" >
			       <div class="grid-x grid-margin-x">
			  <div class="large-2 medium-2 small-3 cell">
			  <label><?php echo $strClient?></label>
	  <select name="ad_client_id" class="required">
         <option value="--" selected><?php echo $strPick?></option>
        <?php
            $clientSql = "SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, Contract_Alocat, Client_Denumire, Client_CUI, Client_Localitate, Client_Judet FROM clienti_date, clienti_contracte 
                          WHERE Contract_Alocat='$code' 
                          AND  clienti_date.ID_Client=clienti_contracte.ID_Client 
                          AND Contract_Activ=0";
            $clientRes = ezpub_query($conn, $clientSql);
            while ($clientRow = ezpub_fetch_array($clientRes)) {
                echo '<option value="' . htmlspecialchars($clientRow['ID_Client']) . '"';
                if ($clientRow['ID_Client'] == $row['ad_client_id']) {
                    echo ' selected';
                }
                echo '>' . htmlspecialchars($clientRow['Client_Denumire']) . '</option>';
            }
        ?>
      </select>
	  </div>
			  <div class="large-2 medium-2 small-3 cell">
	  <label><?php echo $strPackage?></label>
	  	  <select name="ad_ambalaj_id" class="required">
         <option value="--" selected><?php echo $strPick?></option>
        <?php
            $ambalajSql = "SELECT ambalaj_id, ambalaj_nume, ambalaj_cod FROM ambalaje ORDER BY ambalaj_nume ASC";
            $ambalajRes = ezpub_query($conn, $ambalajSql);
            while ($ambalajRow = ezpub_fetch_array($ambalajRes)) {
                        echo '<option value="' . htmlspecialchars($ambalajRow['ambalaj_id']) . '"';
                        if ($ambalajRow['ambalaj_id'] == $row['ad_ambalaj_id']) {
                            echo ' selected';
                        }
                        echo '>' . htmlspecialchars($ambalajRow['ambalaj_nume']) . '</option>';
            }
        ?>
      </select>
	  </div>
    <div class="large-2 medium-2 small-3 cell">
        <label><?php echo $strTotal?>  kg</label>
	  <input name="ad_total" type="text" class="required" value="<?php echo htmlspecialchars($row['ad_total']); ?>" />
	  </div>
      <div class="large-2 medium-2 small-3 cell">
        <label><?php echo $strOperator?></label>
        <input name="ad_operator_denumire" type="text" class="required" value="<?php echo htmlspecialchars($row['ad_operator_denumire']); ?>" /> 
</div>
      <div class="large-2 medium-2 small-3 cell">
        <label><?php echo $strVAT?></label>
        <input name="ad_operator_cui" type="text" class="required" value="<?php echo htmlspecialchars($row['ad_operator_cui']); ?>" /> 
	  </div>
        <div class="large-1 medium-1 small-3 cell">
            <label><?php echo $strCode?></label>
            <input name="ad_cod_operatiune" type="text" class="required" value="<?php echo htmlspecialchars($row['ad_cod_operatiune']); ?>" />   
        </div>
        <div class="large-1 medium-1 small-3 cell">
            <label><?php echo $strYear?></label>
            <input name="ad_an_raportare" type="text" class="required" value="<?php echo htmlspecialchars($row['ad_an_raportare']); ?>" />
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
                            value="sitepackagesanex1b.php?cl=<?php echo $cl?>&yr=<?php echo $year?>&aloc=<?php echo $aloc ?>"
                            selected><?php echo $strPick?></option>
                        <?php
			$query7="SELECT * FROM date_utilizatori WHERE utilizator_Function='USER' ORDER By utilizator_Nume ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			if ((isset($_GET['aloc'])) && !empty($_GET['aloc'])){
			If ($seenby['strSeenBy']==$_GET['aloc']) {
			echo"<option selected value=\"sitepackagesanex1b.php?cl=$cl&yr=$year&aloc=".$seenby['utilizator_Code']."\">". $seenby['strUserName']."</option>";}
			else{echo"<option value=\"sitepackagesanex1b.php?cl=$cl&yr=$year&aloc=".$seenby['utilizator_Code']."\">". $seenby['utilizator_Nume']." ". $seenby['utilizator_Prenume']."</option>";}}
			else {echo"<option value=\"sitepackagesanex1b.php?cl=$cl&yr=$year&aloc=".$seenby['utilizator_Code']."\">". $seenby['utilizator_Nume']." ". $seenby['utilizator_Prenume']."</option>";}
			}
			?>
                    </select></label>
            </div>
            <div class="large-4 medium-4 cell">
                <label> <?php echo $strClient?>
                    <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                        <option
                            value="sitepackagesanex1b.php?cl=<?php echo $cl?>&yr=<?php echo $year?>&aloc=<?php echo $aloc ?>"
                            selected><?php echo $strPick?></option>
                        <?php
			$query7="SELECT DISTINCT ad_client_id, Client_Denumire, ID_Client, Client_Aloc FROM clienti_date, ambalaje_deseuri WHERE ad_client_id=ID_Client AND Client_Aloc='$code' ORDER By Client_Denumire ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			if ((isset($_GET['cl'])) && !empty($_GET['cl'])){
			If ($seenby['ID_Client']==$_GET['cl']) {
			echo"<option selected value=\"sitepackagesanex1b.php?aloc=$aloc&yr=$year&cl=".$seenby['ID_Client']."\">". $seenby['Client_Denumire']."</option>";}
			else{echo"<option value=\"sitepackagesanex1b.php?aloc=$aloc&yr=$year&cl=".$seenby['ID_Client']."\">". $seenby['Client_Denumire']."</option>";}}
			else {echo"<option value=\"sitepackagesanex1b.php?aloc=$aloc&yr=$year&cl=".$seenby['ID_Client']."\">". $seenby['Client_Denumire']."</option>";}
			}
			?>
                    </select></label>
            </div>
            <div class="large-4 medium-4 cell">
                <label> <?php echo $strYear?>
                    <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                        <option
                            value="sitepackagesanex1b.php?cl=<?php echo $cl?>&yr=<?php echo $year?>&aloc=<?php echo $aloc ?>"
                            selected><?php echo $strPick?></option>
                        <?php
			 			$query7="SELECT DISTINCT ad_an_raportare as iyear FROM ambalaje_deseuri ORDER By ad_an_raportare DESC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
						if ((isset($_GET['yr'])) && !empty($_GET['yr'])){
			If ($seenby['iyear']==$_GET['yr']) {
			echo"<option selected value=\"sitepackagesanex1b.php?aloc=$aloc&cl$cl&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}
			else{echo"<option value=\"sitepackagesanex1b.php?aloc=$aloc&cl=$cl&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}}
			else {
			if ($year==$seenby['iyear']) 
			{echo "<option value=\"sitepackagesanex1b.php?aloc=$aloc&cl=$cl&yr=".$seenby['iyear']." \" selected >". $seenby['iyear']."</option>";}
			else {echo"<option value=\"sitepackagesanex1b.php?aloc=$aloc&cl=$cl&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}
			}
			}
			 ?>
                    </select></label>
            </div>
        </div>

        <?php
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"sitepackagesanex1b.php?mode=new\" class=\"button\">
<i class=\"large fa fa-plus\" title=\"$strAdd\"></i>&nbsp;$strAdd</a>
</div>
</div>";
$query="SELECT ad_id, ad_client_id, ad_ambalaj_id, ad_total, ad_an_raportare,
Client_Denumire, ambalaj_nume
FROM ambalaje_deseuri, clienti_date, ambalaje
WHERE ambalaje_deseuri.ad_client_id=clienti_date.ID_Client
AND ambalaje_deseuri.ad_ambalaj_id=ambalaje.ambalaj_id";
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
echo $pages->display_pages() . " <a href=\"sitepackagesanex1b.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
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
        	<th><?php echo $strYear?></th>
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
			<td>$row[ad_id]</td>
			<td>$row[ad_an_raportare]</td>
			<td>$row[Client_Denumire]</td>
			<td>$row[ambalaj_nume]</td>
			<td>$row[ad_total]</td>
			  <td><a href=\"sitepackagesanex1b.php?mode=edit&cID=$row[ad_id]\" class=\"ask\"><i class=\"large fas fa-pencil-alt\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"sitepackagesanex1b.php?mode=delete&cID=$row[ad_id]\" class=\"ask\" OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"large fa fa-eraser\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"5\"><em></em></td><td>&nbsp;</td></tr></tfoot></table></div></div>";
}
}
}
?>
</div>
</div>
<?php
include '../bottom.php';
?>