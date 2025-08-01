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
			$query3= $query3 . " $pages->limit" ;
			$result3=ezpub_query($conn,$query3);
	if ($numar==0)
{
echo "<div class=\"callout alert\"><p align=\"left\">$strNoRecordsFound</p></div>";
}
Else {?>
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
					Else
					{
						$pprice=romanize($row["course_price"]*$vatprc);
					}
	echo "<div class=\"large-3 medium-3 small-3 cell \">
	<div class=\"column\" data-equalizer-watch>";
	if (strlen($row['course_name'])>80)
	{$productname=substr($row['course_name'], 0, 80)."&hellip;";}
else
{$productname=$row['course_name'];}
		echo "<h5>$productname</h5></div>
		<div class=\"column align-self-bottom\"><a href=\"$strSiteURL$cursuritrail$row[course_url]\"><img src=\"$strSiteURL/img/cursuri/".$row["course_picture"]."\" alt=\"$row[course_name]\"></a></div>
		<div class=\"column align-self-bottom\">$strTrainer : $row[trainer_name] </div>
		</p>
<div class=\"column align-self-bottom\"><h6><strong>$strPrice:" . " $pprice " ." lei</strong></h6></div>
<div class=\"column align-self-bottom\"><p><a href=\"$strSiteURL/elearning/inscriere.php\" title=\"$strEnroll $row[course_name]\" class=\"expanded button\"><i class=\"fas fa-cart-plus\"></i>&nbsp;$strEnroll</a></p></div>

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
			$query3="SELECT course_name, course_picture, course_url, course_author, 
			schedule_start_date, schedule_end_date, schedule_exam_date, schedule_details, 
			trainer_name, trainer_url
			FROM elearning_courseschedules, elearning_courses, elearning_trainers 
			WHERE schedule_course_ID=elearning_courses.Course_ID AND 
			course_author=trainer_utilizator_id AND
			schedule_end_date >= '$sdata' ORDER BY schedule_start_date ASC ";
			
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
echo "<div class=\"callout alert\"><p align=\"left\">$strNoRecordsFound</p></div></div></div>";
}

Else {
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

	echo "<div class=\"large-3 medium-3 small-3 cell \">
	<div class=\"column\" data-equalizer-watch>
	 <h4>$row[course_name]</h4>
		<a href=\"$strSiteURL$cursuritrail$row[course_url]\"><img src=\"$strSiteURL/img/cursuri/".$row["course_picture"]."\" alt=\"$row[course_name]\"></a><br />
        $strPartner : <a href=\"$strSiteURL$locatiitrail$row[location_url]\"> $row[location_name] </a>
		<p>$strData : $startdate-$enddate<br />
		$strTrainer : $row[trainer_name] <br />
		$strDetails :  $row[schedule_details] <br />
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