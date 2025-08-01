<?php
//updated 17.07.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle="Administrare profil";
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
    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
 
<?php
echo "<h1>$strPageTitle</h1>";


if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();

$strWhereClause = " WHERE elearning_students.student_id=" . $uid . ";";
$query= "UPDATE elearning_students SET elearning_students.student_first_name='" .str_replace("'","&#39;",$_POST["student_first_name"]) . "' ," ;
$query= $query . " elearning_students.student_oras='" .str_replace("'","&#39;",$_POST["student_oras"]) . "', "; 
$query= $query . " elearning_students.student_judet='" .str_replace("'","&#39;",$_POST["student_judet"]) . "', "; 
$query= $query . " elearning_students.student_last_name='" .str_replace("'","&#39;",$_POST["student_last_name"]) . "', "; 
$query= $query . " elearning_students.student_adresa='" .str_replace("'","&#39;",$_POST["student_adresa"]) . "', "; 
$query= $query . " elearning_students.student_password='" .str_replace("'","&#39;",$_POST["student_password"]) . "', "; 
$query= $query . " elearning_students.student_email='" .str_replace("'","&#39;",$_POST["student_email"]) . "', "; 
$query= $query . " elearning_students.student_phone='" .str_replace("'","&#39;",$_POST["student_phone"]) . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"student_myprofile.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}
}
Else {
?>
<script src="<?php echo $strSiteURL ?>/js/foundation/jquery.js"></script>
<script language="JavaScript" type="text/JavaScript">
$(document).ready(function(){
	$("#search-box").keyup(function(){
		$.ajax({
		type: "POST",
		url: "../common/city_select.php",
		data:'keyword='+$(this).val(),
		beforeSend: function(){
			$("#search-box").css("background","#FFF url(../img/LoaderIcon.gif) no-repeat 165px");
		},
		success: function(data){
			 try {
			$("#suggesstion-box").show();
			$("#suggesstion-box").html(data);
			$("#search-box").css("background","#FFF");
		 }
catch(err) {
  document.getElementById("response").innerHTML = err.message;
}
        },
        error : function(){
           alert('Some error occurred!');
        }
		});
	});
});

function selectCity(val) {
	split_str=val.split(" - ");
$("#search-box").val(split_str[0]);
$("#judet").val(split_str[1]);
$("#suggesstion-box").hide();
}
</script>
<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM elearning_students WHERE student_id=$uid";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
<form Method="post" Action="student_myprofile.php?mode=edit&sID=<?php echo $uid?>">
    <div class="grid-x grid-margin-x">
			  <div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strFirstName?></label>
	  <input name="student_first_name" Type="text" required value="<?php echo $row["student_first_name"]?>"/>
</div>
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strLastName?></label>
	  <input name="student_last_name" Type="text" required value="<?php echo $row["student_last_name"]?>"/>
	  </div>
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strEmail?></label>
	  <input name="student_email" Type="email" required value="<?php echo $row["student_email"]?>" />
	  </div>
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strPhone?></label>
	  <input name="student_phone" Type="text" required value="<?php echo $row["student_phone"]?>"/>
	  </div>
</div>
<div class="grid-x grid-margin-x">
<div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strPassword?></label>
	  <input name="student_password" id="student_password" Type="password" minlength="10" required alue="<?php echo $row["student_password"]?>"/>
	</div>
<div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strAddress?></label>
	  <input name="student_adresa" Type="text" required value="<?php echo $row["student_adresa"]?>"/>
	</div>
<div class="large-2 cell">  	
   <label><?php echo $strCity?></label>
   <input type="text" name="student_oras" id="search-box" placeholder="<?php echo $row["student_oras"]?>" />
  	<div id="suggesstion-box" class="suggesstion-box"></div></div>
<div class="large-2 cell"> 
   <label><?php echo $strCounty?></label>
   <input type="text" name="student_judet" id="judet" placeholder="<?php echo $row["student_judet"]?>" />
   			
				</div>
	</div>		
	    <div class="grid-x grid-margin-x">	
<div class="large-12 medium-12 small-12 cell">
	  <p align="center"> <input Type="submit" class="button" Value="<?php echo $strModify?>" name="Submit"> </p>
</div>
</div>
</form>
<?php
}
Else
{
	?>
<ul class="tabs" data-deep-link="true" data-update-history="true" data-deep-link-smudge="true" data-deep-link-smudge-delay="500" data-tabs id="deeplinked-tabs">
  <li class="tabs-title is-active"><a href="student_myprofile.php#panel1" aria-selected="true"><?php echo $strMyProfile?></a></li>
  <li class="tabs-title"><a href="student_myprofile.php#panel2"><?php echo $strMyCourses?></a></li>
  <li class="tabs-title"><a href="student_myprofile.php#panel3"><?php echo $strInvoicing?></a></li>
</ul>
<div class="tabs-content" data-tabs-content="deeplinked-tabs">
<div class="tabs-panel is-active" id="panel1">

<a href="student_myprofile.php?mode=edit&sID=<?php echo $uid?>" class="button"><?php echo $strEdit?>&nbsp;<i class="fas fa-edit"></i></a><br />
<?php
$query="SELECT * FROM elearning_students where student_id=$uid";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
if ($numar==0)
{
echo $strNoRecordsFound;
}
Else {
?>
<table width="100%">
	      <thead>
    	<tr>
        	<th>&nbsp;</th>
			<th><h4><?php echo $strMyProfile?></h4></th>
			<th>&nbsp;</th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"
			<tr><td>$strName</td><td colspan=\"2\">$row[student_first_name]"." " ."$row[student_last_name]</td></tr>
			<tr><td>$strEmail</td><td colspan=\"2\">$row[student_email]</td></tr>
			<tr><td>$strPhone</td><td colspan=\"2\">$row[student_phone]</td></tr>
			<tr><td>$strAddress</td><td colspan=\"2\">$row[student_adresa]</td></tr>
			<tr><td>$strCity</td><td colspan=\"2\">$row[student_oras]</td></tr>
			";
}
echo "</tbody><tfoot><tr><td></td><td><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
?>
 </div>
 <div class="tabs-panel" id="panel2">
 <?php
 echo "<a href=\"enrolment.php\" class=\"button\">$strAdd&nbsp;<i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a><br/>";
$query="SELECT elearning_enrollments_id, elearning_enrollments_stud_id, elearning_enrollments_course_id, elearning_enrollments_courseschedule_id, elearning_enrollments_date,elearning_enrollments_active, 
student_first_name, student_last_name, student_email, student_phone,
course_ID, course_name, course_price, course_discount, course_url,
schedule_start_date, schedule_end_date, schedule_ID 
FROM elearning_enrollments, elearning_students, elearning_courses, elearning_courseschedules 
WHERE elearning_enrollments.elearning_enrollments_stud_id=elearning_students.student_id AND
elearning_enrollments.elearning_enrollments_stud_id=$uid AND
elearning_courses.course_ID=elearning_enrollments.elearning_enrollments_course_id AND 
elearning_courseschedules.schedule_ID=elearning_enrollments.elearning_enrollments_courseschedule_id";
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
echo $strTotal . " " .$nume." ".$strCourses ;
echo " <br /><br />";
echo $pages->display_pages();
?>
</div>
<table width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strID?></th>
			<th><?php echo $strCourse?></th>
			<th><?php echo $strPrice?></th>
			<th><?php echo $strEnrollmentDate?></th>
			<th><?php echo $strDetails?></th>
			<th><?php echo $strActive?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result2)){
	$formateddate=date('d-m-Y H:i', strtotime($row["elearning_enrollments_date"]));
    		echo"<tr>
			<td>$row[elearning_enrollments_stud_id]</td>
			<td>$row[course_name]</td>";
			If ($row["course_discount"]=="0") {
			$price=$row["course_price"];}
			Else
			{$price=$row["course_discount"];}
		echo "
			<td>$price</td>
			<td>$formateddate</td>
			<td><a href=\"$strSiteURL"."/cursuri/$row[course_url]\"><i class=\"fa fa-book\"  title=\"$strDetails\"></i></td>";
			
if ($row["elearning_enrollments_active"]=='1'){			
echo		"<td><a href=\"mycourse.php?cID=$row[course_ID]&schID=$row[schedule_ID]\"><i class=\"fa fa-unlock\"  title=\"$strActive\"></i></td>";}
else{
echo		"<td><i class=\"fa fa-lock\"  title=\"$strInactive\"></i></td>";}	
echo "</tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"6\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
} ?> 
  </div>
  <div class="tabs-panel" id="panel3">
<?php
   echo "<a href=\"student_companies.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a><br/>";
$query="SELECT * FROM elearning_companies where company_student=$uid";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
if ($numar==0)
{
echo $strNoRecordsFound;
}
Else {
?>
<table width="100%">
	      <thead>
    	<tr>
        	<th>&nbsp;</th>
			<th><?php echo $strCompany?></th>
			<th><?php echo $strVAT?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
			<th>&nbsp;</th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"
			<tr>
			<td>$row[company_id]</td>
			<td>$row[company_name]</td>
			<td >$row[company_VAT]</td>
			<td><a href=\"student_companies.php?mode=edit&cID=$row[company_id]\"><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"student_companies.php?mode=delete&cID=$row[company_id]\" OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
			</tr>";
}
echo "</tbody><tfoot><tr><td></td><td colspan=\"4\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
  ?> 
  </div>
  </div>
 
<?php
}
?>
</div>
</div>
<?php
include '../bottom.php';
?>