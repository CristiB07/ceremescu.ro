<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strDescription="Administreaza înscrierile";
$strPageTitle="Administreaza înscrierile";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/elearning/login.php?message=MLF");
}
$uid=$_SESSION['uid'];
?>
<div class="grid-x grid-padding-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php

echo "<h1>$strPageTitle</h1>";
if (isset($_GET['mode']) && $_GET['mode'] === "view") {
	$sID = isset($_GET['sID']) && is_numeric($_GET['sID']) ? (int)$_GET['sID'] : 0;
	$cID = isset($_GET['cID']) && is_numeric($_GET['cID']) ? (int)$_GET['cID'] : 0;
	$oID = isset($_GET['oID']) && is_numeric($_GET['oID']) ? (int)$_GET['oID'] : 0;
	if ($sID > 0) {
		$stmt = mysqli_prepare($conn, "SELECT * FROM site_accounts WHERE account_id=?");
		mysqli_stmt_bind_param($stmt, "i", $sID);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$row = ezpub_fetch_array($result);
		echo "<a href=\"lector_siteenrollments.php\"><i class=\"fa fa-users fa-xl\" aria-hidden=\"true\" title=\"".htmlspecialchars($strUsers)."\"></i></a>";
		echo "<table width=\"100%\">";
		echo "<tr><td>ID</td><td>".htmlspecialchars($row['account_id'])."</td></tr>";
		echo "<tr><td>".htmlspecialchars($strFirstName)."</td><td>".htmlspecialchars($row['account_first_name'])."</td></tr>";
		echo "<tr><td>".htmlspecialchars($strName)."</td><td>".htmlspecialchars($row['account_last_name'])."</td></tr>";
		echo "<tr><td>".htmlspecialchars($strEmail)."</td><td>".htmlspecialchars($row['account_email'])."</td></tr>";
		echo "<tr><td>".htmlspecialchars($strPhone)."</td><td>".htmlspecialchars($row['account_phone'])."</td></tr>";
		echo "<tr><td>".htmlspecialchars($strAddress)."</td><td>".htmlspecialchars($row['account_address'])."</td></tr>";
		echo "<tr><td>".htmlspecialchars($strCity)."</td><td>".htmlspecialchars($row['account_city'])."</td></tr>";
		$stmt2 = mysqli_prepare($conn, "SELECT schedule_ID, course_ID, course_name, schedule_start_date, schedule_end_date FROM elearning_courseschedules, elearning_courses WHERE schedule_ID = ? AND course_ID = ?");
		mysqli_stmt_bind_param($stmt2, "ii", $oID, $cID);
		mysqli_stmt_execute($stmt2);
		$result2 = mysqli_stmt_get_result($stmt2);
		$rs1 = ezpub_fetch_array($result2);
		$startdate = $rs1 ? date('d M', strtotime($rs1['schedule_start_date'])) : '';
		$enddate = $rs1 ? date('d M', strtotime($rs1['schedule_end_date'])) : '';
		echo "<tr><td>".htmlspecialchars($strCourse)."</td><td>".htmlspecialchars($rs1['course_name']).": $startdate - $enddate </td></tr>";
		echo "</table>";
		include '../bottom.php';
		die;
	} else {
				echo "<div class=\"callout alert\">".htmlspecialchars($strThereWasAnError)."</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"lector_siteenrollments.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">
</div>";
include '../bottom.php';
die;
			}
}
else {
	$query = "SELECT elearning_enrollments_id, elearning_enrollments_stud_id, elearning_enrollments_course_id, elearning_enrollments_courseschedule_id, elearning_enrollments_date, elearning_enrollments_active, account_first_name, account_last_name, account_email, account_phone, course_ID, course_name, course_price, course_discount, course_author, schedule_start_date, schedule_end_date FROM elearning_enrollments, site_accounts, elearning_courses, elearning_courseschedules WHERE elearning_enrollments.elearning_enrollments_stud_id=site_accounts.account_id AND elearning_courses.course_ID=elearning_enrollments_course_id AND elearning_courses.course_author=? AND elearning_courseschedules.schedule_ID=elearning_enrollments_courseschedule_id";
	$stmt = mysqli_prepare($conn, $query);
	mysqli_stmt_bind_param($stmt, "i", $uid);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	$nume = mysqli_num_rows($result);
	$pages = new Pagination;
	$pages->items_total = $nume;
	$pages->mid_range = 5;
	$pages->paginate();
	$query2 = $query . " ORDER BY elearning_enrollments_date DESC $pages->limit";
	$stmt2 = mysqli_prepare($conn, $query2);
	mysqli_stmt_bind_param($stmt2, "i", $uid);
	mysqli_stmt_execute($stmt2);
	$result2 = mysqli_stmt_get_result($stmt2);
	if ($nume == 0) {
		echo "<div class=\"callout alert\">".htmlspecialchars($strNoRecordsFound)."</div>";
	} else {
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
                    <th><?php echo $strFirstName?></th>
                    <th><?php echo $strName?></th>
                    <th><?php echo $strEmail?></th>
                    <th><?php echo $strPhone?></th>
                    <th><?php echo $strCourse?></th>
                    <th><?php echo $strEnrollmentDate?></th>
                    <th><?php echo $strPrice?></th>
                    <th><?php echo $strDetails?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                while ($row = ezpub_fetch_array($result2)) {
                    $formateddate = date('d-m-Y H:i', strtotime($row["elearning_enrollments_date"]));
                    echo "<tr>";
                    echo "<td>".htmlspecialchars($row['elearning_enrollments_stud_id'])."</td>";
                    echo "<td>".htmlspecialchars($row['account_first_name'])."</td>";
                    echo "<td>".htmlspecialchars($row['account_last_name'])."</td>";
                    echo "<td>".htmlspecialchars($row['account_email'])."</td>";
                    echo "<td>".htmlspecialchars($row['account_phone'])."</td>";
                    echo "<td>".htmlspecialchars($row['course_name'])."</td>";
					$price = (is_null($row["course_discount"]) || $row["course_discount"] == "0.0000") ? $row["course_price"] : $row["course_discount"];
                    echo "<td>".htmlspecialchars($formateddate)."</td><td>".htmlspecialchars($price)."</td>";
                    echo "<td><a href=\"lector_siteenrollments.php?mode=view&sID=".urlencode($row['elearning_enrollments_stud_id'])."&cID=".urlencode($row['course_ID'])."&oID=".urlencode($row['elearning_enrollments_courseschedule_id'])."\"><i class=\"fa fa-eye\" aria-hidden=\"true\" title=\"".htmlspecialchars($strView)."\"></i></a></td></tr>";
                }
echo "</tbody><tfoot><tr><td></td><td  colspan=\"7\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
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