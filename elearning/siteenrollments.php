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
$query="SELECT * FROM elearning_students WHERE student_id=$_GET[sID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
echo "<a href=\"siteenrollments.php\"><img src=\"images/users.png\" alt=\"$strUsers\" title=\"$strUsers\" border=\"0\" /></a>";
echo "<table id=\"rounded-corner\" summary=\"$strUsers\" width=\"100%\">";
echo"<tr><td>ID</td><td>$row[student_id]</td></tr>
	<tr><td>$strFirstName</td><td>$row[student_first_name]</td></tr>
	<tr><td>$strName</td><td>$row[student_last_name]</td></tr>
	<tr><td>$strEmail</td><td>$row[student_email]</td></tr>
	<tr><td>$strPhone</td><td>$row[student_phone]</td></tr>
	<tr><td>$strAddress</td><td>$row[student_adresa]</td></tr>
	<tr><td>$strCity</td><td>$row[student_oras]</td></tr>
	";
	$sql="SELECT schedule_ID, course_ID, course_name, schedule_start_date, schedule_end_date FROM elearning_courseschedules, elearning_courses 
		  WHERE schedule_ID = $row[student_schedule] AND course_ID=$row[student_course]";
        $result = ezpub_query($conn,$sql);
	 $rs1=ezpub_fetch_array($result);
		$startdate=date('d M', strtotime($rs1['schedule_start_date']));
		$enddate=date('d M', strtotime($rs1['schedule_end_date']));
Echo "	<tr><td>$strCourse</td><td>$rs1[course_name]: $startdate - $enddate </td></tr>
		</table>";
		if ($row["student_company"]==0){
$query="SELECT * FROM elearning_companies WHERE company_VAT='$row[student_invoice]'";
$result=ezpub_query($conn,$query);
$row2=ezpub_fetch_array($result);

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
Else
{
echo "<div class=\"error callout\">$strThereWasAnError</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteenrollments.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include 'bottom.php';
die;}}

If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM elearning_enrollments WHERE elearning_enrollments_id=" .$_GET['eID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteenrollments.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}

If (IsSet($_GET['mode']) AND $_GET['mode']=="activate"){

$nsql="UPDATE elearning_enrollments SET elearning_enrollments_active=1 WHERE elearning_enrollments_id=" .$_GET['eID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordUpdated</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteenrollments.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}

If (IsSet($_GET['mode']) AND $_GET['mode']=="deactivate"){

$nsql="UPDATE elearning_enrollments SET elearning_enrollments_active=0 WHERE elearning_enrollments_id=" .$_GET['eID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordUpdated</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteenrollments.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}
Else
{
$query="SELECT elearning_enrollments_id, elearning_enrollments_stud_id, elearning_enrollments_course_id, elearning_enrollments_courseschedule_id, elearning_enrollments_date,elearning_enrollments_active, 
student_first_name, student_last_name, student_email, student_phone,
course_ID, course_name, course_price, course_discount,
schedule_start_date, schedule_end_date 
FROM elearning_enrollments, elearning_students, elearning_courses, elearning_courseschedules WHERE elearning_enrollments.elearning_enrollments_stud_id=elearning_students.student_id AND elearning_courses.course_ID=elearning_enrollments.elearning_enrollments_course_id AND elearning_courseschedules.schedule_ID=elearning_enrollments.elearning_enrollments_courseschedule_id";
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
Else {
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
    		echo"<tr>
			<td>$row[elearning_enrollments_stud_id]</td>
			<td>$row[student_first_name]</td>
			<td>$row[student_last_name]</td>
			<td>$row[student_email]</td>
			<td>$row[student_phone]</td>
			<td>$row[course_name]</td>";
			If ($row["course_discount"]=="0") {
			$price=$row["course_price"];}
			Else
			{$price=$row["course_discount"];}
		echo "
			<td>$price</td>
			<td>$formateddate</td>
			<td><a href=\"siteenrollments.php?mode=view&sID=$row[elearning_enrollments_stud_id]\" ><i class=\"large fa fa-eye\" title=\"$strView\"></a></td>
			<td><a href=\"siteenrollments.php?mode=delete&eID=$row[elearning_enrollments_id]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>";
if ($row["elearning_enrollments_active"]=='1'){			
echo		"<td><a href=\"siteenrollments.php?mode=deactivate&eID=$row[elearning_enrollments_id]\"  OnClick=\"return confirm('$strConfirmdeActivate');\"><i class=\"large fa fa-unlock\" title=\"$strdeActivate\"></a></td>";}
else{
echo		"<td><a href=\"siteenrollments.php?mode=activate&eID=$row[elearning_enrollments_id]\"  OnClick=\"return confirm('$strConfirmActivate');\"><i class=\"large fa fa-lock\" title=\"$strActivate\"></a></td>";}	
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