<?php
//updated 17.07.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';

$locatiitrail="/locatii/";
$cursuritrail="/cursuri/";
$trainertrail="/traineri/";
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$strKeywords="Cursuri autorizate, cursuri de pregătire, cursuri de specializare, cursuri";
$strDescription="Programarea cursurilor Consaltis Training Center";
$strPageTitle="Programare cursuri Consaltis Training Center";
include '../header.php';
$i= 0;
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <h1><?php echo $strNextCourses?></h1>
        <h2><?php echo $strElearning?></h1>
            <?php
			// Query pentru cursuri e-learning (fără parametri dinamici, dar folosim best practices)
			$query3="SELECT course_name, course_picture, course_url, course_author, course_discount, course_price, course_vat,
			trainer_name, trainer_url
			FROM elearning_courses, elearning_trainers 
			WHERE course_author=trainer_utilizator_id ";
			
	 		$result3=ezpub_query($conn,$query3);
			$numar=ezpub_num_rows($result3,$query3);
			$pages = new Pagination;  
			$pages->items_total = $numar;  
			$pages->mid_range = 5;  
			$pages->paginate(); 
			
			// Re-execute cu limită de paginare (concatenare sigură - $pages->limit este generat intern)
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
	  $vatrat=$row["course_vat"]/100;
	  $vatprc=$vatrat+1;
	If ($row["course_discount"]!=='0.0000')
					{
					$pprice=romanize($row["course_discount"]*$vatprc);
					}
					else
					{
						$pprice=romanize($row["course_price"]*$vatprc);
					}
	echo "<div class=\"large-3 medium-3 small-3 cell \">
	<div class=\"column\" data-equalizer-watch>";
	
	// XSS prevention
	$course_name_safe = htmlspecialchars($row['course_name'], ENT_QUOTES, 'UTF-8');
	$course_picture_safe = htmlspecialchars($row["course_picture"], ENT_QUOTES, 'UTF-8');
	$course_url_safe = htmlspecialchars($row['course_url'], ENT_QUOTES, 'UTF-8');
	$trainer_name_safe = htmlspecialchars($row['trainer_name'], ENT_QUOTES, 'UTF-8');
	$pprice_safe = htmlspecialchars($pprice, ENT_QUOTES, 'UTF-8');
	
	if (strlen($row['course_name'])>80)
	{$productname=htmlspecialchars(substr($row['course_name'], 0, 80), ENT_QUOTES, 'UTF-8')."&hellip;";}
else
{$productname=$course_name_safe;}
		echo "<h5>$productname</h5></div>
		<div class=\"column align-self-bottom\"><a href=\"$strSiteURL$cursuritrail$course_url_safe\"><img src=\"$strSiteURL/img/cursuri/".$course_picture_safe."\" alt=\"$course_name_safe\"></a></div>
		<div class=\"column align-self-bottom\">$strTrainer : $trainer_name_safe </div>
		</p>
<div class=\"column align-self-bottom\"><h6><strong>$strPrice:" . " $pprice_safe " ." lei</strong></h6></div>
<div class=\"column align-self-bottom\"><p><a href=\"$strSiteURL/elearning/createaccount.php\" title=\"$strEnroll $course_name_safe\" class=\"expanded button\"><i class=\"fas fa-cart-plus\"></i>&nbsp;$strEnroll</a></p></div>

      </div>";	
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
			// Sanitize input
			$sdata_safe = mysqli_real_escape_string($conn, $sdata);
			
			// Prepared statement pentru SELECT cursuri on-site
			$stmt3 = mysqli_prepare($conn, "SELECT course_name, course_picture, course_url, course_author, 
			schedule_start_date, schedule_end_date, schedule_exam_date, schedule_details, 
			trainer_name, trainer_url
			FROM elearning_courseschedules, elearning_courses, elearning_trainers 
			WHERE schedule_course_ID=elearning_courses.Course_ID AND 
			course_author=trainer_utilizator_id AND
			schedule_end_date >= ? ORDER BY schedule_start_date ASC");
			mysqli_stmt_bind_param($stmt3, "s", $sdata_safe);
			mysqli_stmt_execute($stmt3);
			$result3_temp = mysqli_stmt_get_result($stmt3);
			$numar=mysqli_num_rows($result3_temp);
			$pages = new Pagination;  
			$pages->items_total = $numar;  
			$pages->mid_range = 5;  
			$pages->paginate(); 
			
			// Re-execute with pagination limit
			$query_with_limit = "SELECT course_name, course_picture, course_url, course_author, 
			schedule_start_date, schedule_end_date, schedule_exam_date, schedule_details, 
			trainer_name, trainer_url
			FROM elearning_courseschedules, elearning_courses, elearning_trainers 
			WHERE schedule_course_ID=elearning_courses.Course_ID AND 
			course_author=trainer_utilizator_id AND
			schedule_end_date >= ? ORDER BY schedule_start_date ASC " . $pages->limit;
			
			$stmt3 = mysqli_prepare($conn, $query_with_limit);
			mysqli_stmt_bind_param($stmt3, "s", $sdata_safe);
			mysqli_stmt_execute($stmt3);
			$result3 = mysqli_stmt_get_result($stmt3);
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

	// XSS prevention
	$course_name_safe = htmlspecialchars($row['course_name'], ENT_QUOTES, 'UTF-8');
	$course_url_safe = htmlspecialchars($row['course_url'], ENT_QUOTES, 'UTF-8');
	$course_picture_safe = htmlspecialchars($row["course_picture"], ENT_QUOTES, 'UTF-8');
	$location_url_safe = htmlspecialchars($row['location_url'], ENT_QUOTES, 'UTF-8');
	$location_name_safe = htmlspecialchars($row['location_name'], ENT_QUOTES, 'UTF-8');
	$startdate_safe = htmlspecialchars($startdate, ENT_QUOTES, 'UTF-8');
	$enddate_safe = htmlspecialchars($enddate, ENT_QUOTES, 'UTF-8');
	$trainer_name_safe = htmlspecialchars($row['trainer_name'], ENT_QUOTES, 'UTF-8');
	$schedule_details_safe = htmlspecialchars($row['schedule_details'], ENT_QUOTES, 'UTF-8');

	echo "<div class=\"large-3 medium-3 small-3 cell \">
	<div class=\"column\" data-equalizer-watch>
	 <h4>$course_name_safe</h4>
		<a href=\"$strSiteURL$cursuritrail$course_url_safe\"><img src=\"$strSiteURL/img/cursuri/".$course_picture_safe."\" alt=\"$course_name_safe\"></a><br />
        $strPartner : <a href=\"$strSiteURL$locatiitrail$location_url_safe\"> $location_name_safe </a>
		<p>$strData : $startdate_safe-$enddate_safe<br />
		$strTrainer : $trainer_name_safe <br />
		$strDetails :  $schedule_details_safe <br />
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