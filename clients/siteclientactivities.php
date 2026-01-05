<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle="Administrare activități";
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes")
{
	header("location:$strSiteURL/login/login.php?message=MLF");
	die;
}

// Validare parametri GET
if (isset($_GET['mode']) && !in_array($_GET['mode'], ['new', 'edit', 'delete'])) {
    header("location:$strSiteURL/clients/siteclientactivities.php");
    die;
}

if (isset($_GET['cID'])) {
    if (!is_numeric($_GET['cID'])) {
        header("location:$strSiteURL/clients/siteclientactivities.php");
        die;
    }
    $cID = (int)$_GET['cID'];
}

include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, "DELETE FROM clienti_activitati_clienti WHERE ID_activitati_client=?");
mysqli_stmt_bind_param($stmt, 'i', $cID);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
echo "<div class=\"success callout\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteclientactivities.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){
// Validare și sanitizare input
$ID_Client = isset($_POST["ID_Client"]) && is_numeric($_POST["ID_Client"]) ? (int)$_POST["ID_Client"] : null;
$ID_Activitate = isset($_POST["ID_Activitate"]) && is_numeric($_POST["ID_Activitate"]) ? (int)$_POST["ID_Activitate"] : null;
$Frecventa = isset($_POST["Activitate_Client_Frecventa"]) ? (int)$_POST["Activitate_Client_Frecventa"] : 0;
$Coleg = isset($_POST["Activitate_Client_Coleg"]) && is_numeric($_POST["Activitate_Client_Coleg"]) ? (int)$_POST["Activitate_Client_Coleg"] : null;
$Contract = isset($_POST["Activitate_Client_Contract"]) && is_numeric($_POST["Activitate_Client_Contract"]) ? (int)$_POST["Activitate_Client_Contract"] : null;

$strData1 = isset($_POST["strData1"]) ? (int)$_POST["strData1"] : 0;
$strData2 = isset($_POST["strData2"]) ? (int)$_POST["strData2"] : 0;
$strData3 = isset($_POST["strData3"]) ? (int)$_POST["strData3"] : 0;
$termenlimita = sprintf("%04d-%02d-%02d", $strData3, $strData2, $strData1);

if (!$ID_Client || !$ID_Activitate) {
    echo "<div class=\"alert callout\">Date invalide</div>";
    die;
}

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, "INSERT INTO clienti_activitati_clienti(ID_Client, ID_Activitate, ID_user, Activitate_Client_Frecventa, Activitate_Client_Coleg, Activitate_Client_Contract, Activitate_Client_Termen) VALUES (?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'iiiiiis', $ID_Client, $ID_Activitate, $uid, $Frecventa, $Coleg, $Contract, $termenlimita);
			
//It executes the SQL
if (!mysqli_stmt_execute($stmt))
  {
  mysqli_stmt_close($stmt);
  die('Error: ' . mysqli_error($conn));
  }
Else{
mysqli_stmt_close($stmt);
echo "<div class=\"success callout\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteclientactivities.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}}
Else
{// edit
// Validare și sanitizare input
$ID_Client = isset($_POST["ID_Client"]) && is_numeric($_POST["ID_Client"]) ? (int)$_POST["ID_Client"] : null;
$ID_Activitate = isset($_POST["ID_Activitate"]) && is_numeric($_POST["ID_Activitate"]) ? (int)$_POST["ID_Activitate"] : null;
$Frecventa = isset($_POST["Activitate_Client_Frecventa"]) ? (int)$_POST["Activitate_Client_Frecventa"] : 0;
$Coleg = isset($_POST["Activitate_Client_Coleg"]) && is_numeric($_POST["Activitate_Client_Coleg"]) ? (int)$_POST["Activitate_Client_Coleg"] : null;
$Contract = isset($_POST["Activitate_Client_Contract"]) && is_numeric($_POST["Activitate_Client_Contract"]) ? (int)$_POST["Activitate_Client_Contract"] : null;

$strData1 = isset($_POST["strData1"]) ? (int)$_POST["strData1"] : 0;
$strData2 = isset($_POST["strData2"]) ? (int)$_POST["strData2"] : 0;
$strData3 = isset($_POST["strData3"]) ? (int)$_POST["strData3"] : 0;
$termenlimita = sprintf("%04d-%02d-%02d", $strData3, $strData2, $strData1);

if (!$ID_Client || !$ID_Activitate || !isset($cID)) {
    echo "<div class=\"alert callout\">Date invalide</div>";
    die;
}

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, "UPDATE clienti_activitati_clienti SET ID_Client=?, ID_Activitate=?, Activitate_Client_Frecventa=?, Activitate_Client_Coleg=?, Activitate_Client_Contract=?, Activitate_Client_Termen=? WHERE ID_activitati_client=?");
mysqli_stmt_bind_param($stmt, 'iiiiiis', $ID_Client, $ID_Activitate, $Frecventa, $Coleg, $Contract, $termenlimita, $cID);

if (!mysqli_stmt_execute($stmt))
  {
  mysqli_stmt_close($stmt);
  die('Error: ' . mysqli_error($conn));
  }
Else{
mysqli_stmt_close($stmt);
echo "<div class=\"success callout\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteclientactivities.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
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
                <p><a href="siteclientactivities.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post" id="users" Action="siteclientactivities.php?mode=new">

            <div class="grid-x grid-padding-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strClient?>
                        <select name="ID_Client" class="required">
                            <option value=""><?php echo $strClient?></option>
                            <?php 
		  // Prepared statement pentru SQL injection prevention
		  $stmt_clients = mysqli_prepare($conn, "SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, Contract_Alocat, Client_Denumire FROM clienti_date, clienti_contracte WHERE Contract_Alocat=? AND clienti_date.ID_Client=clienti_contracte.ID_Client ORDER BY Client_Denumire ASC");
        mysqli_stmt_bind_param($stmt_clients, 's', $code);
        mysqli_stmt_execute($stmt_clients);
        $result=mysqli_stmt_get_result($stmt_clients);
	    while ($rss=mysqli_fetch_array($result, MYSQLI_ASSOC)){
	?>
                            <option value="<?php echo htmlspecialchars($rss["ID_Client"], ENT_QUOTES, 'UTF-8')?>"><?php echo $rss["Client_Denumire"]?></option>
                            <?php
}?>
                        </select></label>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strContract?>
                        <TD><select name="Activitate_Client_Contract" class="required">
                                <option value=""><?php echo $strContract?></option>
                                <?php $sql = "SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, ID_Contract, Contract_Alocat, Contract_Obiect, Client_Denumire FROM clienti_date, clienti_contracte 
WHERE Contract_Alocat='$code' AND  clienti_date.ID_Client=clienti_contracte.ID_Client
ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                                <option value="<?php echo $rss["ID_Contract"]?>"><?php echo $rss["Client_Denumire"]?> -
                                    <?php echo $rss["Contract_Obiect"]?></option>
                                <?php
}?>
                            </select>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strJointWorkWith?>
                        <select name="Activitate_Client_Coleg" class="required">
                            <option value=""><?php echo $strUser?></option>
                            <?php $sql = "Select * FROM date_utilizatori WHERE utilizator_Role='USER' ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                            <option value="<?php echo $rss["utilizator_ID"]?>"><?php echo $rss["utilizator_Prenume"]?>
                                <?php echo $rss["utilizator_Nume"]?></option>
                            <?php
}?>
                        </select></label>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strActivities?>
                        <TD><select name="ID_Activitate" class="required">
                                <option value=""><?php echo $strActivities?></option>
                                <?php $sql = "Select * FROM clienti_activitati_lista ORDER BY Activitate_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                                <option value="<?php echo $rss["ID_Activitate"]?>">
                                    <?php echo $rss["Activitate_Nume"]?></option>
                                <?php
}?>
                            </select>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strSetDate." ".$strDay?>
                        <select name="strData1">
                            <option value="00" selected>--</option>
                            <?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
    		
    		// create option With numeric value of day
    		echo "<option value=\"$d\">$d</option>";} 
?>
                        </select>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strSetDate." ".$strMonth?> </label>
                    <select name="strData2">
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
    			echo "<option value=\"$m\">$monthname</option>";} 
			?>
                    </select> </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strSetDate." ".$strYear?>
                        <select name="strData3">
                            <option value="0000" selected>--</option>
                            <?php
		$cy=date("Y");
		$fy=$cy+10;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
    	echo "<option value=\"$y\">$y</option>";} 
			?>
                        </select></label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strFrequency?> (<?php echo $strMonths?>)
                        <select name="Activitate_Client_Frecventa">
                            <option value="00" selected>--</option>
                            <?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 12; $d ++) {
    		
    		// create option With numeric value of day
    		echo "<option value=\"$d\">$d</option>";} 
?>
                        </select> </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success">
                </div>
            </div>
        </form>

        <?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, "SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_contracte.Contract_Alocat, clienti_contracte.ID_Contract,
clienti_activitati_clienti.ID_Client, clienti_activitati_clienti.ID_Activitate, clienti_activitati_clienti.Activitate_Client_Frecventa, clienti_activitati_clienti.Activitate_Client_Termen,
clienti_activitati_lista.ID_Activitate, clienti_activitati_lista.Activitate_Nume, ID_activitati_client, clienti_activitati_clienti.Activitate_Client_Coleg
FROM clienti_activitati_clienti, clienti_date, clienti_activitati_lista, clienti_contracte
WHERE ID_activitati_client=? AND clienti_contracte.Contract_Alocat=? 
AND clienti_date.ID_Client=clienti_activitati_clienti.ID_Client AND clienti_activitati_clienti.ID_Activitate=clienti_activitati_lista.ID_Activitate
AND clienti_activitati_clienti.ID_Client=clienti_contracte.ID_Client
ORDER By Client_Denumire ASC");
mysqli_stmt_bind_param($stmt, 'is', $cID, $code);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="siteclientactivities.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post" Action="siteclientactivities.php?mode=edit&cID=<?php echo $row['ID_activitati_client']?>">
            <div class="grid-x grid-padding-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strTitle?>
                        <select name="ID_Client" class="required">
                            <?php 
		  $sql="SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, Contract_Alocat, Client_Denumire FROM clienti_date, clienti_contracte 
			WHERE Contract_Alocat='$code' AND  clienti_date.ID_Client=clienti_contracte.ID_Client
			ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                            <option <?php if ($row["ID_Client"]==$rss["ID_Client"]) echo "selected"; ?>
                                value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
                            <?php
}?>
                        </select></label>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strContract?>
                        <select name="Activitate_Client_Contract" class="required">
                            <option value=""><?php echo $strContract?></option>
                            <?php $sql = "SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, ID_Contract, Contract_Alocat, Contract_Obiect, Client_Denumire FROM clienti_date, clienti_contracte 
WHERE Contract_Alocat='$code' AND  clienti_date.ID_Client=clienti_contracte.ID_Client
ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                            <option value="<?php echo $rss["ID_Contract"]?>"
                                <?php if ($row["ID_Contract"]==$rss["ID_Contract"]) echo "selected"; ?>>
                                <?php echo $rss["Client_Denumire"]?> - <?php echo $rss["Contract_Obiect"]?></option>
                            <?php
}?>
                        </select></label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strJointWorkWith?>
                        <TD><select name="Activitate_Client_Coleg" class="required">
                                <option value=""><?php echo $strUser?></option>
                                <?php $sql = "Select * FROM date_utilizatori WHERE utilizator_Role='USER' ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                                <option value="<?php echo $rss["utilizator_ID"]?>"
                                    <?php IF ($row["Activitate_Client_Coleg"]==$rss['utilizator_ID']) echo "selected";?>>
                                    <?php echo $rss["utilizator_Prenume"]?> <?php echo $rss["utilizator_Nume"]?>
                                </option>
                                <?php
}?>
                            </select>
                    </label>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strActivities?>
                        <TD><select name="ID_Activitate" class="required">
                                <?php $sql = "Select * FROM clienti_activitati_lista ORDER BY Activitate_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                                <option <?php if ($row["ID_Activitate"]==$rss["ID_Activitate"]) echo "selected"; ?>
                                    value="<?php echo $rss["ID_Activitate"]?>"><?php echo $rss["Activitate_Nume"]?>
                                </option>
                                <?php
}?>
                            </select>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strSetDate." ".$strDay?>
                        <TD> <select name="strData1">
                                <option value="00" selected>--</option>
                                <?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
		$day=date("d", strtotime($row['Activitate_Client_Termen']));	
    		// create option With numeric value of day
			if ($day==$d){
    		echo "<option selected value=\"$d\">$d</option>";}
			else {echo "<option value=\"$d\">$d</option>";}
			} 
?>
                            </select>
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strSetDate." ".$strMonth?>
                        <select name="strData2">
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
			if (strstr($row['Activitate_Client_Termen'], "-00-"))
{
    $month="00";
}
Else 		{
			//$month=date("m", strtotime($row['Activitate_Client_Termen']));	
			$time=strtotime($row['Activitate_Client_Termen']);
			$month=date("m",$time);
			}
    		// create option With numeric value of day
			if ($month==$m){
    			echo "<option selected value=\"$m\">$montNname</option>";}
				Else
				{echo "<option value=\"$m\">$monthname</option>";}
				} 
			?>
                        </select></label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strSetDate." ".$strYear?>
                        <select name="strData3">
                            <option value="0000" selected>--</option>
                            <?php
		$cy=date("Y");
		$fy=$cy+10;
		$py=$cy-10;
		for ( $y = $py; $y <= $fy; $y ++) {
			$year=date("Y", strtotime($row['Activitate_Client_Termen']));	
    		// create option With numeric value of day
			if ($year==$y){
    	echo "<option selected value=\"$y\">$y</option>";}
		Else{
		echo "<option value=\"$y\">$y</option>";
		} }
			?>
                        </select></label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strFrequency?> (<?php echo $strMonths?>)
                        <select name="Activitate_Client_Frecventa" class="new">
                            <option value="00" selected>--</option>
                            <?php
// Loop through 1 To max days In a month
    	for ( $d = 0; $d <= 12; $d ++) {
		$day=$row['Activitate_Client_Frecventa'];	
    		// create option With numeric value of day
			if ($day==$d){
    		echo "<option selected value=\"$d\">$d</option>";}
			else {echo "<option value=\"$d\">$d</option>";}
			} 
?>
                        </select> </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success">
                </div>
            </div>
        </form>
        <?php
}
else
{
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"siteclientactivities.php?mode=new\" class=\"button\"><i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i>&nbsp;$strAdd</a></div></div>";
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_Aloc, clienti_contracte.Contract_Alocat, 
clienti_activitati_clienti.ID_Client, clienti_activitati_clienti.ID_Activitate, clienti_activitati_clienti.Activitate_Client_Frecventa, 
clienti_activitati_clienti.Activitate_Client_Termen, clienti_activitati_lista.ID_Activitate, clienti_activitati_lista.Activitate_Nume, ID_activitati_client 
FROM clienti_activitati_clienti, clienti_date, clienti_activitati_lista, clienti_contracte 
WHERE clienti_date.ID_Client=clienti_activitati_clienti.ID_Client 
AND clienti_activitati_clienti.ID_Activitate=clienti_activitati_lista.ID_Activitate 
AND clienti_contracte.ID_Client=clienti_date.ID_Client  
AND clienti_activitati_clienti.Activitate_Client_Contract=clienti_contracte.ID_Contract ";
if ($_SESSION['clearence']=='USER')
{
$query.=" AND clienti_contracte.Contract_Alocat='$code'";
}
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY Client_Denumire ASC $pages->limit";
$result=ezpub_query($conn,$query);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
?>
        <div class="paginate">
            <?php
echo $strTotal . " " .$numar." ".$strActivities ;
echo " <br /><br />";
echo $pages->display_pages();
echo " <br /><br /></div>";

?>
<table width="100%">
            <thead>
                <tr>
                    <th width="50%"><?php echo $strClient?></th>
                    <th width="30%"><?php echo $strTitle?></th>
                    <th width="10%"><?php echo $strFrequency?></th>
                    <th width="5%"><?php echo $strEdit?></th>
                    <th width="5%"><?php echo $strDelete?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[Activitate_Nume]</td>";
if ($row["Activitate_Client_Frecventa"]==1) {
    $frecventa=$strMonthly;
} elseif ($row["Activitate_Client_Frecventa"]==3) {
    $frecventa=$strQuaterly;
}
elseif ($row["Activitate_Client_Frecventa"]==6) {
    $frecventa=$strSemestrial;
} elseif ($row["Activitate_Client_Frecventa"]==12) {
    $frecventa=$strYearly;
} elseif ($row["Activitate_Client_Frecventa"]==0) {
    $frecventa=$strOneTime;
} else {
    $frecventa=$row["Activitate_Client_Frecventa"];
}
echo"<td>$frecventa</td>
			  <td><a href=\"siteclientactivities.php?mode=edit&cID=$row[ID_activitati_client]\" class=\"ask\"><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"siteclientactivities.php?mode=delete&cID=$row[ID_activitati_client]\" class=\"ask\" OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td colspan=\"5\">&nbsp;</td></tr></tfoot></table>";
}
echo " <div class=\"paginate\">";
echo $pages->display_pages();
echo "</div>";
}
}
?>
    </div>
</div>
<?php
include '../bottom.php';
?>