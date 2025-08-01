<?php
//updated 17.07.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$locatiitrail="locatii/";
$cursuritrail="cursuri/";
$trainertrail="traineri/";
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$strKeywords="Cursuri autorizate, cursuri de pregătire, cursuri de specializare, cursuri";
$strDescription="Programarea cursurilor Consaltis Training Center";
$strPageTitle="Programare cursuri Consaltis Training Center";
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
          <h1><?php $strOtherCourses?></h1>

		
		<?php
			$query3="SELECT course_name, course_picture, course_url, course_author, 
			schedule_start_date, schedule_end_date, schedule_exam_date, schedule_details, 
			trainer_name, trainer_url 
			FROM elearning_courseschedules, elearning_courses, elearning_trainers 
			WHERE schedule_course_ID=elearning_courses.Course_ID AND 
			course_author=trainer_utilizator_id AND
			course_author=$uid AND
			schedule_end_date >= '$sdata' ORDER BY schedule_start_date ASC ";
			
	 		$result3=ezpub_query($conn,$query3);
			$numar=ezpub_num_rows($result3,$query3);
			$pages = new Pagination;  
			$pages->items_total = $numar;  
			$pages->mid_range = 5;  
			$pages->paginate(); 
			$query3= $query3 . " $pages->limit" ;
			$result3=ezpub_query($conn,$query3);
			?>
		
  <?php 
	if ($numar==0)
{
echo "<div class=\"callout alert\">$strNoRecordsFound</div></div></div>";
}
Else {
	$i= 0;
	?>
	<div class="paginate">

		<?php
echo " <br /><br />";
echo $pages->display_pages();
?>
</div>
</div>
</div>
    <div class="grid-x grid-padding-x">
	<div class="large-12 medium-12 small-12 cell">
<div class="grid-x grid-margin-x" data-equalizer data-equalize-on="medium" id="test-eq">
<?php
		While ($row=ezpub_fetch_array($result3)){
		$i++ ;
		$startdate=date('d M Y', strtotime($row['schedule_start_date']));
		$enddate=date('d M Y', strtotime($row['schedule_end_date']));
		$examdate=date('d M Y', strtotime($row['schedule_exam_date']));

        	echo "<div class=\"large-3 medium-3 small-3 cell \">
			<div class=\"column\" data-equalizer-watch> <h4>$row[course_name]</h4>
		<a href=\"$strSiteURL$cursuritrail$row[course_url]\"><img src=\"$strSiteURL/img/cursuri/".$row["course_picture"]."\" alt=\"$row[course_name]\"></a><br />
		<p>$strData : $startdate-$enddate<br />
		$strTrainer :  $row[trainer_name] <br />
		$strDetails :  $row[schedule_details] <br />
		</p>
      </div></div>";	
	    if($i%4 == 0) {
                echo  "</div><hr />
				<div class=\"grid-x grid-padding-x\"  data-equalizer data-equalize-on=\"medium\" id=\"test-eq\">";

	}
      }
	  echo "</div></div>";?>
	      <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
	 
	  <div class="paginate">
		<?php 
echo $pages->display_pages();
?>
</div>
</div>
</div>
</div>
</div>

	  <?php
}
include '../bottom.php';
?>