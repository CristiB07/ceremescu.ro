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
If (IsSet($_GET['mode']) AND $_GET['mode']=="view"){
If (IsSet($_GET['sID']) AND is_numeric($_GET['sID'])){
$query="SELECT * FROM elearning_students WHERE student_id=$_GET[sID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
echo "<a href=\"lector_siteenrollments.php\"><i class=\"fa fa-users fa-3x\" aria-hidden=\"true\" title=\"$strUsers\"></i></a>";
echo "<table width=\"100%\">";
echo"<tr><td>ID</td><td>$row[student_id]</td></tr>
	<tr><td>$strFirstName</td><td>$row[student_first_name]</td></tr>
	<tr><td>$strName</td><td>$row[student_last_name]</td></tr>
	<tr><td>$strEmail</td><td>$row[student_email]</td></tr>
	<tr><td>$strPhone</td><td>$row[student_phone]</td></tr>
	<tr><td>$strAddress</td><td>$row[student_adresa]</td></tr>
	<tr><td>$strCity</td><td>$row[student_oras]</td></tr>
	<tr><td>$strPIN</td><td>$row[student_cnp]</td></tr>
	<tr><td>$strLicence</td><td>$row[student_licence]</td></tr>
	";
	$sql="SELECT schedule_ID, course_ID, course_name, schedule_start_date, schedule_end_date FROM elearning_courseschedules, elearning_courses 
		  WHERE schedule_ID = $_GET[oID] AND course_ID=$_GET[cID]";
        $result = ezpub_query($conn,$sql);
	 $rs1=ezpub_fetch_array($result);
		$startdate=date('d M', strtotime($rs1['schedule_start_date']));
		$enddate=date('d M', strtotime($rs1['schedule_end_date']));
Echo "	<tr><td>$strCourse</td><td>$rs1[course_name]: $startdate - $enddate </td></tr>
		</table>";
	include '../bottom.php';
die;
}
Else
{
echo "<div class=\"callout alert\">$strThereWasAnError</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"lector_siteenrolments.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}}

Else
{
$query="SELECT elearning_enrollments_id, elearning_enrollments_stud_id, elearning_enrollments_course_id, elearning_enrollments_courseschedule_id, elearning_enrollments_date,elearning_enrollments_active, 
student_first_name, student_last_name, student_email, student_phone,
course_ID, course_name, course_price, course_discount, course_author,
schedule_start_date, schedule_end_date 
FROM elearning_enrollments, elearning_students, elearning_courses, elearning_courseschedules 
WHERE elearning_enrollments.elearning_enrollments_stud_id=elearning_students.student_id 
AND elearning_courses.course_ID=elearning_enrollments_course_id
AND elearning_courses.course_author=$uid 
AND elearning_courseschedules.schedule_ID=elearning_enrollments_courseschedule_id";
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
echo "<div class=\"callout alert\">$strNoRecordsFound</div>";
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
			<th><?php echo $strEnrollmentDate?></th>
			<th><?php echo $strPrice?></th>
			<th><?php echo $strDetails?></th>
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
			
			<td>$formateddate</td><td>$price</td>
			<td><a href=\"lector_siteenrollments.php?mode=view&sID=$row[elearning_enrollments_stud_id]&cID=$row[course_ID]&oID=$row[elearning_enrollments_courseschedule_id]\"><i class=\"fa fa-eye\" aria-hidden=\"true\" title=\"$strView\"></a></td></tr>";
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