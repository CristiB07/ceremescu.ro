<?php
include '../settings.php';
include '../classes/common.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes")
{
	header("location:$strSiteURL/login/index.php?message=MLF");
	die;
}
include '../classes/paginator.class.php';

$strPageTitle="Administrare vizite clienți";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];

// Validare parametri
if (isset($_GET['cID']) && !is_numeric($_GET['cID'])) {
    header("location:$strSiteURL/clients/sitevisitreports.php?message=ER");
    die;
}
if (isset($_GET['mode']) && !in_array($_GET['mode'], ['new', 'edit', 'delete'])) {
    header("location:$strSiteURL/clients/sitevisitreports.php?message=ER");
    die;
}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$cID = (int)$_GET['cID'];
$stmt = mysqli_prepare($conn, "DELETE FROM clienti_vizite WHERE ID_vizita=?");
mysqli_stmt_bind_param($stmt, "i", $cID);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo "<div class=\"success callout\">$strRecordDeleted</div><></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitevisitreports.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){

$year = (int)$_POST["strData3"];
$month = (int)$_POST["strData2"];
$day = (int)$_POST["strData1"];
$datavizita = sprintf("%04d-%02d-%02d", $year, $month, $day);

$client_vizita = (int)$_POST["client_vizita"];
$tip_vizita = trim($_POST["tip_vizita"]);
$scop_vizita = trim($_POST["scop_vizita"]);
$urmatoarea_vizita = trim($_POST["urmatoarea_vizita"]);
$observatii_vizita = trim($_POST["observatii_vizita"]);

$stmt = mysqli_prepare($conn, "INSERT INTO clienti_vizite(client_vizita, tip_vizita, data_vizita, scop_vizita, alocat, urmatoarea_vizita, observatii_vizita) VALUES(?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "issssss", $client_vizita, $tip_vizita, $datavizita, $scop_vizita, $code, $urmatoarea_vizita, $observatii_vizita);

if (!mysqli_stmt_execute($stmt)) {
    die('Error: ' . mysqli_stmt_error($stmt));
}
mysqli_stmt_close($stmt);

echo "<div class=\"success callout\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitevisitreports.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
Else
{// edit
$year = (int)$_POST["strData3"];
$month = (int)$_POST["strData2"];
$day = (int)$_POST["strData1"];
$datavizita = sprintf("%04d-%02d-%02d", $year, $month, $day);

$cID = (int)$_GET["cID"];
$client_vizita = (int)$_POST["client_vizita"];
$scop_vizita = trim($_POST["scop_vizita"]);
$tip_vizita = trim($_POST["tip_vizita"]);
$urmatoarea_vizita = trim($_POST["urmatoarea_vizita"]);
$observatii_vizita = trim($_POST["observatii_vizita"]);

$stmt = mysqli_prepare($conn, "UPDATE clienti_vizite SET client_vizita=?, scop_vizita=?, tip_vizita=?, data_vizita=?, urmatoarea_vizita=?, observatii_vizita=? WHERE ID_vizita=?");
mysqli_stmt_bind_param($stmt, "isssssi", $client_vizita, $scop_vizita, $tip_vizita, $datavizita, $urmatoarea_vizita, $observatii_vizita, $cID);

if (!mysqli_stmt_execute($stmt)) {
    die('Error: ' . mysqli_stmt_error($stmt));
}
mysqli_stmt_close($stmt);

echo "<div class=\"success callout\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitevisitreports.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
}
Else {
?>
        <script language="JavaScript" type="text/JavaScript">
            $(document).ready(function() {
	$("#users").validate();
});
</script>
        <script src="<?php echo $strSiteURL ?>/js/foundation/jquery.js"></script>

        <script language="JavaScript" type="text/JavaScript">
            $(document).ready(function() {
	$("#users").validate();
});
</script>
        <link rel="stylesheet" href="../js/simple-editor/simple-editor.css">
        <script src="../js/simple-editor/simple-editor.js"></script>

        <?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="sitevisitreports.php" class="button"><?php echo $strBack?></a></p>
            </div>
        </div>
        <form method="post" id="users" action="sitevisitreports.php?mode=new">
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strClient?></label>
                    <select name="client_vizita" class="required">
                        <option value=""><?php echo $strClient?></option>
                        <?php 
		  $sql="SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, clienti_contracte.Contract_Numar, Contract_Alocat, Client_Denumire FROM clienti_date, clienti_contracte 
			WHERE Contract_Alocat='$code' AND  clienti_date.ID_Client=clienti_contracte.ID_Client
			ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                        <option value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
                        <?php
}?>
                    </select>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strType?></label>
                    <input name="tip_vizita" Type="text" size="30" class="required" />
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strDate?></label>
                    <select name="strData1">
                        <option value="00" selected>--</option>
                        <?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
    		
    		// create option With numeric value of day
    		echo "<OPTION value=\"$d\">$d</OPTION>";} 
?>
                    </select>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label>&nbsp;</label>
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
    			echo "<OPTION value=\"$m\">$monthname</OPTION>";} 
			?>
                    </select>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label>&nbsp;</label>
                    <select name="strData3">
                        <option value="0000" selected>--</option>
                        <?php
		$cy=date("Y");
		$fy=$cy+1;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
    	echo "<OPTION value=\"$y\">$y</OPTION>";} 
			?>
                    </select>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strScope?></label>
                    <input name="scop_vizita" Type="text" size="30" class="required" value="" />
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strDetails?></label>
                    <textarea name="observatii_vizita" id="simple-editor-html" class="simple-editor-html" rows="5"></textarea>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strNextVisit?></label>
                    <textarea name="urmatoarea_vizita" id="simple-editor-html" class="simple-editor-html" rows="5"></textarea>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"> <input Type="submit"
                        Value="<?php echo $strAdd?>" name="Submit" class="button success" />
                </div>
            </div>
        </form>

        <?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$cID = (int)$_GET['cID'];
$stmt = mysqli_prepare($conn, "SELECT * FROM clienti_vizite WHERE ID_vizita=?");
mysqli_stmt_bind_param($stmt, "i", $cID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell text-center">
                <p><a href="sitevisitreports.php" class="button"><?php echo $strBack?></a></p>
            </div>
        </div>
        <form method="post" id="users" action="sitevisitreports.php?mode=edit&cID=<?php echo $row['ID_vizita']?>">
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strClient?></label>
                    <select name="client_vizita" class="required">
                        <?php 
		  $sql="SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, Contract_Alocat, Client_Denumire FROM clienti_date, clienti_contracte 
			WHERE Contract_Alocat='$code' AND  clienti_date.ID_Client=clienti_contracte.ID_Client
			ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                        <option <?php if ($row["client_vizita"]==$rss["ID_Client"]) echo "selected"; ?>
                            value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
                        <?php
}?>
                    </select>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strType?></label>
                    <input name="tip_vizita" Type="text" size="30" class="required"
                        value="<?php echo $row["tip_vizita"]?>" />
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strDate?></label>
                    <select name="strData1" class="new">
                        <option value="00" selected>--</option>
                        <?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
		$day=date("d", strtotime($row['data_vizita']));	
    		// create option With numeric value of day
			if ($day==$d){
    		echo "<OPTION selected value=\"$d\">$d</OPTION>";}
			else {echo "<OPTION value=\"$d\">$d</OPTION>";}
			} 
?>
                    </select>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label>&nbsp;</label>
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
			//$month=date("m", strtotime($row['data_vizita']));	
			$time=strtotime($row['data_vizita']);
			$month=date("m",$time);
    		// create option With numeric value of day
			if ($month==$m){
    			echo "<OPTION selected value=\"$m\">$monthname</OPTION>";}
				Else
				{echo "<OPTION value=\"$m\">$monthname</OPTION>";}
				} 
			?>
                    </select>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label>&nbsp;</label>
                    <select name="strData3">
                        <option value="00" selected>--</option>
                        <?php
		$cy=date("Y");
		$fy=$cy+1;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
			$year=date("Y", strtotime($row['data_vizita']));	
    		// create option With numeric value of day
			if ($year==$y){
    	echo "<OPTION selected value=\"$y\">$y</OPTION>";}
		Else{
		echo "<OPTION value=\"$y\">$y</OPTION>";
		} }
			?>
                    </select>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strScope?></label>
                    <input name="scop_vizita" Type="text" size="30" class="required"
                        value="<?php echo $row["scop_vizita"]?>" />
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strDetails?></label>
                    <textarea name="observatii_vizita" id="myTextEditor" class="myTextEditor"
                        rows="5"><?php echo $row["observatii_vizita"]?></textarea>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strNextVisit?></label>
                    <textarea name="urmatoarea_vizita" id="myTextEditor" class="myTextEditor"
                        rows="5"><?php echo $row["urmatoarea_vizita"]?></textarea>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"> <input Type="submit"
                        Value="<?php echo $strAdd?>" name="Submit" class="button success" />
                </div>
            </div>
        </form>
        <?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="view")
{
	?> <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell text-center">
                <p><a href="sitevisitreports.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <?php

$query="SELECT ID_vizita, client_vizita, alocat, data_vizita, tip_vizita, scop_vizita, observatii_vizita, urmatoarea_vizita, 
Client_Denumire, ID_Client
FROM clienti_vizite, clienti_date 
WHERE ID_vizita=$_GET[cID] AND ID_Client=client_vizita";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
$row=ezpub_fetch_array($result);
	    		echo"<table width=\"100%\">
				<tr><td>$strName</td><td>$row[Client_Denumire]</td></tr>
				<tr><td>$strDate</td><td>$row[data_vizita]</td></tr>
				<tr><td>$strScope</td><td>$row[scop_vizita]</td></tr>
				<tr><td>$strDetails</td><td>$row[observatii_vizita]</td></tr>
				<tr><td>$strNextVisit</td><td>$row[urmatoarea_vizita]</td></tr>
			<tr><td></td><td>&nbsp;</td></tr></tfoot></table>";
}
Else
{
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"sitevisitreports.php?mode=new\" class=\"button\"><i class=\"large fa fa-plus\" title=\"$strAdd\"></i>&nbsp;$strAdd</a></div></div>";
$query="SELECT ID_vizita, client_vizita, alocat, data_vizita, tip_vizita, scop_vizita, observatii_vizita, urmatoarea_vizita, 
Client_Denumire, ID_Client, alocat, utilizator_Nume, utilizator_Prenume, utilizator_Code
FROM clienti_vizite, clienti_date, date_utilizatori WHERE ID_Client=client_vizita AND utilizator_code=alocat";
if ($_SESSION['clearence']=='USER'){
$query.=" AND alocat='$code'";
}
$query.=" ORDER BY data_vizita DESC";
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
?>
        <div class="paginate">
            <?php
echo $strTotal . " " .$numar." ".$strVisits ;
echo " <br /><br />";
echo $pages->display_pages() . " <br /><br /></div>";?>
            <table width="100%">
                <thead>
                    <tr>
                        <th width="50%"><?php echo $strClient?></th>
                        <th width="10%"><?php echo $strName?></th>
                        <th width="10%"><?php echo $strDate?></th>
                        <th width="20%"><?php echo $strScope?></th>
                        <th width="5%"><?php echo $strEdit?></th>
                        <th width="5%"><?php echo $strDetails?></th>
                        <th width="5%"><?php echo $strDelete?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[utilizator_Prenume] $row[utilizator_Nume]</td>
			<td>"; echo date('d.m.Y',strtotime($row["data_vizita"]));
			echo "</td>
			<td>$row[scop_vizita]</td>
			 <td><a href=\"sitevisitreports.php?mode=edit&cID=$row[ID_vizita]\" ><i class=\"fas fa-pencil-alt fa-xl\" title=\"$strEdit\"></i></a></td>
			 <td><a href=\"sitevisitreports.php?mode=view&cID=$row[ID_vizita]\"><i class=\"fa fa-search-plus fa-xl\" title=\"$strEdit\"></i></a></td>
			 <td><a href=\"sitevisitreports.php?mode=delete&cID=$row[ID_vizita]\" OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td colspan=\"7\">&nbsp;</td></tr></tfoot></table>";
}
}
}

?>
        </div>
    </div>
    <?php
include '../bottom.php';
?>