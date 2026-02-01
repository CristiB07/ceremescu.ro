<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/account/login.php?message=MLF");
}
$locatiitrail="/locatii/";
$cursuritrail="/cursuri/";
$trainertrail="/traineri/";
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$strKeywords="Cursuri autorizate, cursuri de pregătire, cursuri de specializare, cursuri";
$strDescription="Programarea cursurilor " . $siteOwner;
$strPageTitle="Programare cursuri " . $siteOwner;
include '../dashboard/header.php';
$uid=(int)$_SESSION['uid'];
$i= 0;
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <a href="../dashboard/dashboard.php" class="button">
            <i class="fas fa-backward"></i>&nbsp; <?php echo $strDashboard?></a>
    </div>
</div>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <h1><?php echo $strNextCourses?></h1>
        <h2><?php echo $strElearning?></h1>
            <?php
			// First query - elearning courses (no user input, safe)
			$query3="SELECT course_name, course_picture, course_url, course_author, 
			trainer_name, trainer_url
			FROM elearning_courses, elearning_trainers 
			WHERE course_author=trainer_utilizator_id ";
			
	 		$result3=ezpub_query($conn,$query3);
			$numar=ezpub_num_rows($result3,$query3);
			$pages = new Pagination;  
			$pages->items_total = $numar;  
			$pages->mid_range = 5;  
			$pages->paginate(); 
			$query3= $query3 . " $pages->limit" ;
			$result3=ezpub_query($conn,$query3);
	if ($numar==0)
{
echo "<div class=\"callout alert\"><p align=\"left\">$strNoRecordsFound</p></div>";
}
else {?>
            <div class="paginate">

                <?php
echo $pages->display_pages();
?>
            </div>
    </div>
</div>
<div class="grid-x grid-margin-x" data-equalizer data-equalize-on="medium" id="test-eq">
    <?php
		While ($row=ezpub_fetch_array($result3)){
$i++;

			$course_name = htmlspecialchars($row['course_name'], ENT_QUOTES, 'UTF-8');
			$course_url = htmlspecialchars($row['course_url'], ENT_QUOTES, 'UTF-8');
			$course_picture = htmlspecialchars($row['course_picture'], ENT_QUOTES, 'UTF-8');
			$trainer_name = htmlspecialchars($row['trainer_name'], ENT_QUOTES, 'UTF-8');
			
			echo "<div class=\"large-3 medium-3 small-3 cell \">
	<div class=\"column\" data-equalizer-watch>
 <h4>$course_name</h4><p>
		<a href=\"$strSiteURL$cursuritrail$course_url\"><img src=\"$strSiteURL/img/cursuri/$course_picture\" alt=\"$course_name\"></a><br />
		$strTrainer : $trainer_name <br />
		</p>
      </div></div>";	
	    if($i%4 == 0) {
                echo  "</div><hr />
				<div class=\"grid-x grid-padding-x\"  data-equalizer data-equalize-on=\"medium\" id=\"test-eq\">";

	}
      }
	  echo "</div>"; ?>
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <div class="paginate">
                <?php 
echo $pages->display_pages();
?>
            </div>
        </div>
    </div>
    <?php
	  }
	  ?>
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <h2><?php echo $strOnSite?></h2>
            <?php
			// Use prepared statement for date comparison
			$stmt3 = mysqli_prepare($conn, "SELECT course_name, course_picture, course_url, course_author, schedule_start_date, schedule_end_date, schedule_exam_date, schedule_details, trainer_name, trainer_url FROM elearning_courseschedules, elearning_courses, elearning_trainers WHERE schedule_course_ID=elearning_courses.Course_ID AND course_author=trainer_utilizator_id AND schedule_end_date >= ? ORDER BY schedule_start_date ASC");
			mysqli_stmt_bind_param($stmt3, "s", $sdata);
			mysqli_stmt_execute($stmt3);
			$result3 = mysqli_stmt_get_result($stmt3);
			$numar=mysqli_num_rows($result3);
			$pages = new Pagination;  
			$pages->items_total = $numar;  
			$pages->mid_range = 5;  
			$pages->paginate(); 
			$query3= $query3 . " $pages->limit" ;
			$result3=ezpub_query($conn,$query3);
	if ($numar==0)
{
echo "<div class=\"callout alert\"><p align=\"left\">$strNoRecordsFound</p></div></div></div>";
}

else {
	$i= 0;
	?>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <div class="paginate">

                        <?php
echo $pages->display_pages();
?>
                    </div>
                </div>
            </div>
            <div class="grid-x grid-margin-x" data-equalizer data-equalize-on="medium" id="test-eq">
                <?php
		While ($row=ezpub_fetch_array($result3)){
		$i++ ;
		$startdate=date('d M Y', strtotime($row['schedule_start_date']));
		$enddate=date('d M Y', strtotime($row['schedule_end_date']));
		$examdate=date('d M Y', strtotime($row['schedule_exam_date']));
		
		$course_name = htmlspecialchars($row['course_name'], ENT_QUOTES, 'UTF-8');
		$course_url = htmlspecialchars($row['course_url'], ENT_QUOTES, 'UTF-8');
		$course_picture = htmlspecialchars($row['course_picture'], ENT_QUOTES, 'UTF-8');
		$trainer_name = htmlspecialchars($row['trainer_name'], ENT_QUOTES, 'UTF-8');
		$schedule_details = htmlspecialchars($row['schedule_details'], ENT_QUOTES, 'UTF-8');

	echo "<div class=\"large-3 medium-3 small-3 cell \">
	<div class=\"column\" data-equalizer-watch>
	 <h4>$course_name</h4>
		<a href=\"$strSiteURL$cursuritrail$course_url\"><img src=\"$strSiteURL/img/cursuri/$course_picture\" alt=\"$course_name\"></a><br />
		<p>$strData : $startdate-$enddate<br />
		$strTrainer : $trainer_name <br />
		$strDetails : $schedule_details <br />
		</p>
  </div></div>";	
	    if($i%4 == 0) {
                echo  "</div><hr />
				<div class=\"grid-x grid-padding-x\"  data-equalizer data-equalize-on=\"medium\" id=\"test-eq\">";

	}
      }
	  echo "</div>"; ?>
                <div class="grid-x grid-margin-x">
                    <div class="large-12 medium-12 small-12 cell">
                        <div class="paginate">
                            <?php 
echo $pages->display_pages();
?>
                        </div>
                    </div>
                </div>><?php
	  }
	  ?>
            </div>
        </div>
        <?php
include '../bottom.php';
?>