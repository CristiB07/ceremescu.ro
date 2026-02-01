<?php
//update 05.02.2025
include '../settings.php';
include '../classes/paginator.class.php';
include '../classes/common.php';
$strDescription="Administreaza înscrierile";
$strPageTitle="Administreaza înscrierile";
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
    <div class="large-12 cell">
        <?php

echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="view"){
If (IsSet($_GET['sID']) AND is_numeric($_GET['sID'])){

// Sanitize input
$sID = (int)$_GET['sID'];
if ($sID <= 0) {
    header("location:siteenrollments.php?message=ER");
    die;
}

// Prepared statement pentru SELECT
$stmt = mysqli_prepare($conn, "SELECT * FROM site_accounts WHERE account_id=?");
mysqli_stmt_bind_param($stmt, "i", $sID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row=ezpub_fetch_array($result);
echo "<a href=\"siteenrollments.php\"><img src=\"images/users.png\" alt=\"$strUsers\" title=\"$strUsers\" border=\"0\" /></a>";
echo "<table id=\"rounded-corner\" summary=\"$strUsers\" width=\"100%\">";
echo"<tr><td>ID</td><td>$row[account_id]</td></tr>
	<tr><td>$strFirstName</td><td>$row[account_first_name]</td></tr>
	<tr><td>$strName</td><td>$row[account_last_name]</td></tr>
	<tr><td>$strEmail</td><td>$row[account_email]</td></tr>
	<tr><td>$strPhone</td><td>$row[account_phone]</td></tr>
	<tr><td>$strAddress</td><td>$row[account_address]</td></tr>
	<tr><td>$strCity</td><td>$row[account_city]</td></tr>
	";
	$account_schedule = (int)$row['account_schedule'];
	$account_course = (int)$row['account_course'];
	// Prepared statement pentru SELECT
	$stmt2 = mysqli_prepare($conn, "SELECT schedule_ID, course_ID, course_name, schedule_start_date, schedule_end_date 
		FROM elearning_courseschedules, elearning_courses 
		WHERE schedule_ID = ? AND course_ID=?");
	mysqli_stmt_bind_param($stmt2, "ii", $account_schedule, $account_course);
	mysqli_stmt_execute($stmt2);
	$result = mysqli_stmt_get_result($stmt2);
	$rs1=ezpub_fetch_array($result);
		$startdate=date('d M', strtotime($rs1['schedule_start_date']));
		$enddate=date('d M', strtotime($rs1['schedule_end_date']));
Echo "	<tr><td>$strCourse</td><td>$rs1[course_name]: $startdate - $enddate </td></tr>
		</table>";
		if ($row["account_company"]==0){
$account_invoice = mysqli_real_escape_string($conn, $row['account_invoice']);
// Prepared statement pentru SELECT
$stmt3 = mysqli_prepare($conn, "SELECT * FROM site_companies WHERE company_VAT=?");
mysqli_stmt_bind_param($stmt3, "s", $account_invoice);
mysqli_stmt_execute($stmt3);
$result3 = mysqli_stmt_get_result($stmt3);
$row2=ezpub_fetch_array($result3);

echo "<table id=\"rounded-corner\" summary=\"$strUsers\" width=\"100%\">";
echo"	<tr><td>$strCompanyName</td><td>$row2[company_name]</td></tr>
	<tr><td>$strCompanyVAT</td><td>$row2[company_VAT]</td></tr>
	<tr><td>$strCompanyRC</td><td>$row2[company_reg]</td></tr>
	<tr><td>$strCompanyAddress</td><td>$row2[company_address]</td></tr>
	<tr><td>$strCompanyBank</td><td>$row2[company_bank]</td></tr>
	<tr><td>$strCompanyIBAN</td><td>$row2[company_IBAN]</td></tr>
</table>";		
		}
	include '../bottom.php';
die;
}
else
{
echo "<div class=\"error callout\">$strThereWasAnError</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteenrollments.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include 'bottom.php';
die;}}

If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

// Sanitize input
$eID = (int)$_GET['eID'];
if ($eID <= 0) {
    header("location:siteenrollments.php?message=ER");
    die;
}

// Prepared statement pentru DELETE
$stmt = mysqli_prepare($conn, "DELETE FROM elearning_enrollments WHERE elearning_enrollments_id=?");
mysqli_stmt_bind_param($stmt, "i", $eID);
mysqli_stmt_execute($stmt);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteenrollments.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

If (IsSet($_GET['mode']) AND $_GET['mode']=="activate"){

// Sanitize input
$eID = (int)$_GET['eID'];
if ($eID <= 0) {
    header("location:siteenrollments.php?message=ER");
    die;
}

// Prepared statement pentru UPDATE
$stmt = mysqli_prepare($conn, "UPDATE elearning_enrollments SET elearning_enrollments_active=1 WHERE elearning_enrollments_id=?");
mysqli_stmt_bind_param($stmt, "i", $eID);
mysqli_stmt_execute($stmt);
echo "<div class=\"callout success\">$strRecordUpdated</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteenrollments.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

If (IsSet($_GET['mode']) AND $_GET['mode']=="deactivate"){

// Sanitize input
$eID = (int)$_GET['eID'];
if ($eID <= 0) {
    header("location:siteenrollments.php?message=ER");
    die;
}

// Prepared statement pentru UPDATE
$stmt = mysqli_prepare($conn, "UPDATE elearning_enrollments SET elearning_enrollments_active=0 WHERE elearning_enrollments_id=?");
mysqli_stmt_bind_param($stmt, "i", $eID);
mysqli_stmt_execute($stmt);
echo "<div class=\"callout success\">$strRecordUpdated</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteenrollments.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}
else
{
$query="SELECT elearning_enrollments_id, elearning_enrollments_stud_id, elearning_enrollments_course_id, elearning_enrollments_courseschedule_id, elearning_enrollments_date,elearning_enrollments_active, 
account_first_name, account_last_name, account_email, account_phone,
course_ID, course_name, course_price, course_discount,
schedule_start_date, schedule_end_date 
FROM elearning_enrollments
INNER JOIN site_accounts ON elearning_enrollments.elearning_enrollments_stud_id=site_accounts.account_id 
INNER JOIN elearning_courses ON elearning_courses.course_ID=elearning_enrollments.elearning_enrollments_course_id 
LEFT JOIN elearning_courseschedules ON elearning_courseschedules.schedule_ID=elearning_enrollments.elearning_enrollments_courseschedule_id";
$result=ezpub_query($conn,$query);
$nume=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $nume;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query2= $query . " ORDER BY elearning_enrollments_date DESC $pages->limit" ;
$result2=ezpub_query($conn,$query2);
if ($nume==0)
{
echo $strNoRecordsFound;
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
                    <th><?php echo $strID?></th>
                    <th><?php echo $strClient?></th>
                    <th><?php echo $strEmail?></th>
                    <th><?php echo $strPhone?></th>
                    <th><?php echo $strCourse?></th>
                    <th><?php echo $strPrice?></th>
                    <th><?php echo $strEnrollmentDate?></th>
                    <th><?php echo $strDetails?></th>
                    <th><?php echo $strDelete?></th>
                    <th><?php echo $strActive?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=ezpub_fetch_array($result2)){
	$formateddate=date('d-m-Y H:i', strtotime($row["elearning_enrollments_date"]));
	$enroll_id_safe = (int)$row['elearning_enrollments_id'];
	$stud_id_safe = (int)$row['elearning_enrollments_stud_id'];
	$first_name_safe = htmlspecialchars($row['account_first_name'], ENT_QUOTES, 'UTF-8');
	$last_name_safe = htmlspecialchars($row['account_last_name'], ENT_QUOTES, 'UTF-8');
	$email_safe = htmlspecialchars($row['account_email'], ENT_QUOTES, 'UTF-8');
	$phone_safe = htmlspecialchars($row['account_phone'], ENT_QUOTES, 'UTF-8');
	$course_name_safe = htmlspecialchars($row['course_name'], ENT_QUOTES, 'UTF-8');
    		echo"<tr>
			<td>$enroll_id_safe</td>
			<td>$first_name_safe $last_name_safe</td>
			<td>$email_safe</td>
			<td>$phone_safe</td>
			<td>$course_name_safe</td>";
			If ($row["course_discount"]=="0") {
			$price=$row["course_price"];}
			else
			{$price=$row["course_discount"];}
		echo "
			<td>$price</td>
			<td>$formateddate</td>
			<td><a href=\"siteenrollments.php?mode=view&sID=$stud_id_safe\" ><i class=\"fa-xl fa fa-eye\" title=\"$strView\"></a></td>
			<td><a href=\"siteenrollments.php?mode=delete&eID=$enroll_id_safe\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>";
if ($row["elearning_enrollments_active"]=='1'){			
echo		"<td><a href=\"siteenrollments.php?mode=deactivate&eID=$enroll_id_safe\"  OnClick=\"return confirm('$strConfirmdeActivate');\"><i class=\"fa-xl fa fa-unlock\" title=\"$strdeActivate\"></a></td>";}
else{
echo		"<td><a href=\"siteenrollments.php?mode=activate&eID=$enroll_id_safe\"  OnClick=\"return confirm('$strConfirmActivate');\"><i class=\"fa-xl fa fa-lock\" title=\"$strActivate\"></a></td>";}	
echo "</tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"9\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
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