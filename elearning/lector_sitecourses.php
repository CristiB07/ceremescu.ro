<?php
//updated 17.07.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare cursuri";
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
If (IsSet($_GET['cID']) AND is_numeric($_GET['cID']))
{
$query="SELECT * FROM elearning_courses where course_author=$uid AND Course_ID=$_GET[cID]";
}
Else
{
	$query="SELECT * FROM elearning_courses where course_author=$uid";
}
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
if ($numar==0)
{
echo "<div class=\"callout alert\">$strNoRecordsFound</div>";
}
Else {
?>
<h3><?php echo $strMyCourses?></h3>
<?php 
While ($row=ezpub_fetch_array($result)){
	$description=$row["course_description"];
    		echo"<div class=\"callout\">
			<h4>$row[course_name]</h4>
			$description";
//elearning_lessons
echo "<h3>$strLessons</h3>";
echo "<a href=\"lector_sitelessons.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a><br />";
$query2="SELECT * FROM elearning_lessons where lesson_course=$row[Course_id]";
$result2=ezpub_query($conn,$query2);
$numar2=ezpub_num_rows($result2,$query2);
if ($numar2==0)
{
echo "<div class=\"callout alert\">$strNoRecordsFound</div>";
}
Else {
	echo 			"<table width=\"100%\">
	      <thead>
    	<tr>
        	<th>$strID</th>
			<th>$strTitle</th>
			<th>$strEdit</th>
			<th>$strDelete</th>
        </tr>
		</thead>
<tbody>";
While ($row2=ezpub_fetch_array($result2)){
    		echo"
			<tr>
			<td>$row2[lesson_ID]</td>
			<td>$row2[lesson_title]</td>
			  <td><a href=\"lector_sitelessons.php?mode=edit&lID=$row2[lesson_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"lector_sitelessons.php?mode=delete&lID=$row2[lesson_ID]\" OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"2\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";

//elearning_tests
	echo "<h3>$strTests</h3>";
$query3="SELECT * FROM elearning_tests where test_course=$row[Course_id]";
$result3=ezpub_query($conn,$query3);
$numar3=ezpub_num_rows($result3,$query3);
if ($numar3==0)
{
	echo "<a href=\"lector_sitetests.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a><br />";
echo $strNoRecordsFound;
}
Else {
?>
<?php 
While ($row3=ezpub_fetch_array($result3)){
    		echo"
			<table width=\"100%\">
	      <thead>
    	<tr>
        	<th>$strID</th>
			<th>$strTitle</th>
			<th>$strEdit</th>
			<th>$strDelete</th>
        </tr>
		</thead>
<tbody><tr>
			<td>$row3[test_ID]</td>
			<td>$row3[test_description]</td>
			  <td><a href=\"lector_sitetests.php?mode=edit&tID=$row3[test_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"lector_sitetests.php?mode=delete&tID=$row3[test_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
echo "</tbody><tfoot><tr><td></td><td  colspan=\"2\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}//ends elearning_tests while	
}//ends test else

	}//ends elearning_lessons while
}//ends elearning_lessons else
}//ends elearning_courses while

echo "</div>";
?>
</div>
</div>
<?php
include '../bottom.php';
?>