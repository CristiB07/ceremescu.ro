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
$query="SELECT * FROM elearning_students WHERE student_id=$_GET[sID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
echo "<a href=\"sitestudents.php\"><img src=\"images/users.png\" alt=\"$strUsers\" title=\"$strUsers\" border=\"0\" /></a>";
echo "<table id=\"rounded-corner\" summary=\"$strUsers\" width=\"100%\">";
echo"<tr><td>ID</td><td>$row[student_id]</td></tr>
	<tr><td>$strFirstName</td><td>$row[student_first_name]</td></tr>
	<tr><td>$strName</td><td>$row[student_last_name]</td></tr>
	<tr><td>$strEmail</td><td>$row[student_email]</td></tr>
	<tr><td>$strPhone</td><td>$row[student_phone]</td></tr>
	<tr><td>$strAddress</td><td>$row[student_adresa]</td></tr>
	<tr><td>$strCity</td><td>$row[student_oras]</td></tr>
		</table>";
		if ($row["student_company"]==0){
$query="SELECT * FROM elearning_companies WHERE company_student='$row[student_id]'";
$result2=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result2,$query);
if ($numar==0)
{
echo $strNoRecordsFound;
}
Else {
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
Else
{
echo "<div class=\"error callout\">$strThereWasAnError</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitestudents.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";

include '../bottom.php';
die;}}
}
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM elearning_students WHERE student_id=" .$_GET['sID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitestudents.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include 'bottom.php';
die;}

If (IsSet($_GET['mode']) AND $_GET['mode']=="activate"){

$nsql="UPDATE elearning_students SET student_active=1 WHERE student_id=" .$_GET['sID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordUpdated</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitestudents.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}

If (IsSet($_GET['mode']) AND $_GET['mode']=="deactivate"){

$nsql="UPDATE elearning_students SET student_active=0 WHERE student_id=" .$_GET['sID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordUpdated</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitestudents.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include 'bottom.php';
die;}
Else
{
$query="SELECT * FROM elearning_students";
$result=ezpub_query($conn,$query);
$nume=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $nume;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query2= $query . " ORDER BY student_last_name ASC $pages->limit" ;
$result2=ezpub_query($conn,$query2);

if ($nume==0)
{
echo $strNorecordsFound;
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
<table  width="100%">
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
    		echo"<tr>
			<td>$row[student_id]</td>
			<td>$row[student_first_name]</td>
			<td>$row[student_last_name]</td>
			<td>$row[student_email]</td>
			<td>$row[student_phone]</td>
			<td><a href=\"sitestudents.php?mode=view&sID=$row[student_id]\" ><i class=\"fas fa-info\"></i></td>
			<td><a href=\"sitestudents.php?mode=delete&sID=$row[student_id]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>";
if ($row["student_active"]=='1'){			
echo		"<td><a href=\"sitestudents.php?mode=deactivate&sID=$row[student_id]\"  OnClick=\"return confirm('$strConfirmdeActivate');\"><i class=\"fas fa-user\" title=\"$strdeActivate\"></i></a></td>";}
else{
echo		"<td><a href=\"sitestudents.php?mode=activate&sID=$row[student_id]\"  OnClick=\"return confirm('$strConfirmActivate');\"><i class=\"fas fa-user-slash\" title=\"$strActivate\"></i></a></td>";}	
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