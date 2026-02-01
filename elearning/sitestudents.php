<?php
//update 05.02.2025
include '../settings.php';
include '../classes/paginator.class.php';
include '../classes/common.php';
$strDescription="Administreaza cursanții";
$strPageTitle="Administreaza cursanții";

if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/elearning/login.php?message=MLF");
}
include '../dashboard/header.php';
?>
<div class="grid-x grid-padding-x">
    <div class="large-12 medium-12 small-12 cell">

        <?php

echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="view"){
If (IsSet($_GET['sID']) AND is_numeric($_GET['sID'])){

// Sanitize input
$sID = (int)$_GET['sID'];
if ($sID <= 0) {
    header("location:sitestudents.php?message=ER");
    die;
}

// Prepared statement pentru SELECT
$stmt = mysqli_prepare($conn, "SELECT * FROM site_accounts WHERE account_id=?");
mysqli_stmt_bind_param($stmt, "i", $sID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row=ezpub_fetch_array($result);
echo "<a href=\"sitestudents.php\" class=\"button\"><i class=\"fas fa-users fa-xl\">$strUsers</i>></a>";
echo "<table id=\"rounded-corner\" summary=\"$strUsers\" width=\"100%\">";
echo"<tr><td>ID</td><td>$row[account_id]</td></tr>
	<tr><td>$strFirstName</td><td>$row[account_first_name]</td></tr>
	<tr><td>$strName</td><td>$row[account_last_name]</td></tr>
	<tr><td>$strEmail</td><td>$row[account_email]</td></tr>
	<tr><td>$strPhone</td><td>$row[account_phone]</td></tr>
	<tr><td>$strAddress</td><td>$row[account_address]</td></tr>
	<tr><td>$strCity</td><td>$row[account_city]</td></tr>
		</table>";
		if ($row["account_company"]==0){
$account_id_safe = (int)$row['account_id'];
// Prepared statement pentru SELECT
$stmt2 = mysqli_prepare($conn, "SELECT * FROM site_companies WHERE company_siteaccount=?");
mysqli_stmt_bind_param($stmt2, "i", $account_id_safe);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
$numar=mysqli_num_rows($result2);
if ($numar==0)
{
echo "<div class=\"callout alert\">$strNoRecordsFound</div>";
}
else {
	While ($row=ezpub_fetch_array($result)){

echo "<table id=\"rounded-corner\" summary=\"$strUsers\" width=\"100%\">";
echo"	<tr><td>$strCompanyName</td><td>$row2[company_name]</td></tr>
	<tr><td>$strCompanyVAT</td><td>$row2[company_VAT]</td></tr>
	<tr><td>$strCompanyRC</td><td>$row2[company_reg]</td></tr>
	<tr><td>$strCompanyAddress</td><td>$row2[company_address]</td></tr>
	<tr><td>$strCompanyBank</td><td>$row2[company_bank]</td></tr>
	<tr><td>$strCompanyIBAN</td><td>$row2[company_IBAN]</td></tr>
</table>";		
}}
	include '../bottom.php';
die;
}
else
{
echo "<div class=\"callout alert\">$strThereWasAnError</div></div></div>"; ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitestudents.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";

include '../bottom.php';
die;}}
}
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

// Sanitize input
$sID = (int)$_GET['sID'];
if ($sID <= 0) {
    header("location:sitestudents.php?message=ER");
    die;
}

// Prepared statement pentru DELETE
$stmt = mysqli_prepare($conn, "DELETE FROM site_accounts WHERE account_id=?");
mysqli_stmt_bind_param($stmt, "i", $sID);
mysqli_stmt_execute($stmt);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitestudents.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include 'bottom.php';
die;}

If (IsSet($_GET['mode']) AND $_GET['mode']=="activate"){

// Sanitize input
$sID = (int)$_GET['sID'];
if ($sID <= 0) {
    header("location:sitestudents.php?message=ER");
    die;
}

// Prepared statement pentru UPDATE
$stmt = mysqli_prepare($conn, "UPDATE site_accounts SET account_active=1 WHERE account_id=?");
mysqli_stmt_bind_param($stmt, "i", $sID);
mysqli_stmt_execute($stmt);
echo "<div class=\"callout success\">$strRecordUpdated</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitestudents.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

If (IsSet($_GET['mode']) AND $_GET['mode']=="deactivate"){

// Sanitize input
$sID = (int)$_GET['sID'];
if ($sID <= 0) {
    header("location:sitestudents.php?message=ER");
    die;
}

// Prepared statement pentru UPDATE
$stmt = mysqli_prepare($conn, "UPDATE site_accounts SET account_active=0 WHERE account_id=?");
mysqli_stmt_bind_param($stmt, "i", $sID);
mysqli_stmt_execute($stmt);
echo "<div class=\"callout success\">$strRecordUpdated</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitestudents.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include 'bottom.php';
die;}
else
{
$query="SELECT * FROM site_accounts";
$result=ezpub_query($conn,$query);
$nume=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $nume;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query2= $query . " ORDER BY account_last_name ASC $pages->limit" ;
$result2=ezpub_query($conn,$query2);

if ($nume==0)
{
echo $strNorecordsFound;
}
else {
?>
        <div class="paginate">
            <?php
echo $strTotal . " " .$nume." ".$strStudents ;
echo " <br /><br />";
echo $pages->display_pages();
?>
        </div>
        <table width="100%">
            <thead>
                <tr>
                    <th scope="col" class="rounded-company"><?php echo $strID?></th>
                    <th scope="col" class="rounded"><?php echo $strFirstName?></th>
                    <th scope="col" class="rounded"><?php echo $strName?></th>
                    <th scope="col" class="rounded"><?php echo $strEmail?></th>
                    <th scope="col" class="rounded"><?php echo $strPhone?></th>
                    <th scope="col" class="rounded"><?php echo $strDetails?></th>
                    <th scope="col" class="rounded"><?php echo $strDelete?></th>
                    <th scope="col" class="rounded-q4"><?php echo $strActive?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=ezpub_fetch_array($result2)){
    $account_id_safe = (int)$row['account_id'];
    $first_name_safe = htmlspecialchars($row['account_first_name'], ENT_QUOTES, 'UTF-8');
    $last_name_safe = htmlspecialchars($row['account_last_name'], ENT_QUOTES, 'UTF-8');
    $email_safe = htmlspecialchars($row['account_email'], ENT_QUOTES, 'UTF-8');
    $phone_safe = htmlspecialchars($row['account_phone'], ENT_QUOTES, 'UTF-8');
    echo"<tr>
			<td>$account_id_safe</td>
			<td>$first_name_safe</td>
			<td>$last_name_safe</td>
			<td>$email_safe</td>
			<td>$phone_safe</td>
			<td><a href=\"sitestudents.php?mode=view&sID=$account_id_safe\" ><i class=\"fas fa-info\"></i></td>
			<td><a href=\"sitestudents.php?mode=delete&sID=$account_id_safe\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>";
if ($row["account_active"]=='1'){			
echo		"<td><a href=\"sitestudents.php?mode=deactivate&sID=$account_id_safe\"  OnClick=\"return confirm('$strConfirmdeActivate');\"><i class=\"fas fa-user\" title=\"$strdeActivate\"></i></a></td>";}
else{
echo		"<td><a href=\"sitestudents.php?mode=activate&sID=$account_id_safe\"  OnClick=\"return confirm('$strConfirmActivate');\"><i class=\"fas fa-user-slash\" title=\"$strActivate\"></i></a></td>";}	
echo "</tr>";
}
echo "</tbody><tfoot><tr><td class=\"rounded-foot-left\"></td><td  colspan=\"6\"><em></em></td><td class=\"rounded-foot-right\">&nbsp;</td></tr></tfoot></table>";
}
}
?>
                <div class="paginate">
                    <?php
echo $pages->display_pages();
?>
                </div>
    </div>
</div>
<?php
include '../bottom.php';
?>