<?php
//updated 17.07.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle="Acces curs";
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
$cID= $_GET['cID'];
?>

<?php
$query="SELECT * FROM elearning_courses WHERE course_ID='$cID'";
$result=ezpub_query($conn, $query);
if ($row=ezpub_fetch_array($result)) {
?>
    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
    <h1><?php  echo $row['course_name']?></h1>
            </div>
</div>

<ul class="tabs" data-deep-link="true" data-update-history="true" data-deep-link-smudge="true" data-deep-link-smudge-delay="500" data-tabs id="deeplinked-tabs">
  <li class="tabs-title is-active"><a href="<?php echo $strSiteURL?>/elearning/student_mycourses.php?cID=<?php echo $cID?>#panel1d" aria-selected="true"><?php echo $strCourseDescription ?></a></li>
  <li class="tabs-title"><a href="<?php echo $strSiteURL?>/elearning/student_mycourses.php?cID=<?php echo $cID?>#panel2d"><?php echo $strLessons?> & <?php echo $strTests?></a></li>
  <li class="tabs-title"><a href="<?php echo $strSiteURL?>/elearning/student_mycourses.php?cID=<?php echo $cID?>#panel3d"><?php echo $strQuestions?></a></li>
</ul>

<div class="tabs-content" data-tabs-content="deeplinked-tabs">
  <div class="tabs-panel is-active" id="panel1d">
   <img src="<?php echo $strSiteURL ?>/img/cursuri/<?php echo $row["course_picture"]?>" alt="<?php echo $row["course_name"]?>" style="width:100%">
		  <h3><?php echo $strCourseDescription ?></h3>
		  <p><?php echo $row["course_description"]?></p>
		  <h3><?php echo $strObjectives ?></h3>
		  <p><?php echo $row["course_objective"]?></p> 
<?php }?>
		  </div>
  <div class="tabs-panel" id="panel2d">
<?php
	//elearning_lessons
echo "<h3>$strLessons</h3>";
$query2="SELECT * FROM elearning_lessons where lesson_course=$row[Course_id] ORDER BY lesson_level ASC";
$result2=ezpub_query($conn,$query2);
$numar2=ezpub_num_rows($result2,$query2);
if ($numar2==0)
{
echo $strNoRecordsFound;
}
Else {
		echo		"<table width=\"100%\">
	      <thead>
    	<tr>
        	<th>$strID</th>
			<th>$strTitle</th>
			<th>$strView</th>
        </tr>
		</thead>
<tbody>";
While ($row2=ezpub_fetch_array($result2)){
    		echo"
			<tr>
			<td>$row2[lesson_level]</td>
			<td>$row2[lesson_title]</td>
			<td><a href=\"student_sitelessons.php?lID=$row2[lesson_ID]\"><i class=\"large fa fa-eye\" title=\"$strView\"></i></a></td>
        </tr>";

	}//ends elearning_lessons while
	echo "</tbody><tfoot><tr><td></td><td><em></em></td><td>&nbsp;</td></tr></tfoot></table>";

}//ends elearning_lessons else
//
//tests
	echo "<h3>$strTests</h3>";
$query3="SELECT * FROM elearning_tests where test_course=$row[Course_id]";
$result3=ezpub_query($conn,$query3);
$numar3=ezpub_num_rows($result3,$query3);
if ($numar3==0)
{
echo $strNoRecordsFound;
}
Else {
?>
<?php 
echo"
			<table width=\"100%\">
	      <thead>
    	<tr>
        	<th width=\"10%\">$strID</th>
			<th width=\"70%\">$strTitle</th>
			<th width=\"10%\">$strView</th>
        </tr>
		</thead>
<tbody>";
While ($row3=ezpub_fetch_array($result3)){
    		echo "<tr>
			<td>$row3[test_ID]</td>
			<td>$row3[test_name]</td>
			  <td><a href=\"testwarning.php?tID=$row3[test_ID]&cID=[$cID]\"><i class=\"large fa fa-eye\" title=\"$strView\"></i></a></td>
        </tr>";
}//ends tests while	
echo "</tbody><tfoot><tr><td></td><td><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}//ends test else
?>
    </div>
  <div class="tabs-panel" id="panel3d"><h3><?php echo $strQuestions?></h3>
  <?php
 $query4="SELECT elearning_student_questions.qID, elearning_student_questions.course_id, elearning_student_questions.student_question, elearning_student_questions.trainer_answer, elearning_student_questions.lesson_id, 
 elearning_lessons.lesson_title, elearning_lessons.lesson_ID
 FROM elearning_student_questions, elearning_lessons 
 WHERE elearning_student_questions.lesson_id=elearning_lessons.lesson_ID ORDER BY elearning_lessons.lesson_ID ASC";
$result4=ezpub_query($conn,$query4);
$numar4=ezpub_num_rows($result4,$query4);
if ($numar4==0)
{
echo $strNoRecordsFound;
}
Else { 
While ($row4=ezpub_fetch_array($result4)){
	echo "<div class=\"callout\">
	<h4>$row4[lesson_title]</h4>
	<h4>$row4[student_question]</h4>
<div class=\"callout primary\">
	$row4[trainer_answer]</h4>
</div>
	</div>";
}
}
  ?>
     </div>
     </div>

  </div>
</div>
<?php
include '../bottom.php';
?>