
			<?php
			 $uid=$_SESSION['uid'];
			if ($_SESSION['function']=='TRAINER'){
			?>
			<li><a href="<?php echo $strSiteURL ?>/elearning/lector_myprofile.php"><i class="fas fa-user"></i>&nbsp;<?php echo $strMyProfile?></a></li>
			<li><a href="<?php echo $strSiteURL ?>/elearning/lector_sitecourses.php"><i class="fa fa-book"></i>&nbsp;<?php echo $strMyCourses?></a>
			<?php
			$tquery="SELECT course_ID, course_name FROM elearning_courses WHERE course_author='$uid' ORDER BY course_name";
			$result1=ezpub_query($conn,$tquery);
			$nume=ezpub_num_rows($result1,$tquery);
if ($nume==0)
{?>
	<li><a href="<?php echo $strSiteURL ?>/elearning/lector_sitecourses.php?mode=new"><i class="fas fa-plus"></i>&nbsp;<?php echo $strAddNewCourse?></a></li>
		
<?php
}
Else {
	?>
		<ul class="menu">
			<?php
}
			While ($row1=ezpub_fetch_array($result1)){
				echo "<li><a href=\"$strSiteURL/elearning/lector_sitecourses.php?cID=$row1[course_ID]\"><i class=\"fa fa-book\"></i>$row1[course_name]</a></li>";
			}
			?>
			<li><a href="<?php echo $strSiteURL ?>/elearning/lector_sitecourses.php?mode=new"><i class="fas fa-plus"></i>&nbsp;<?php echo $strAddNewCourse?></a></li>
		</ul>
		</li>
			<li><a href="<?php echo $strSiteURL ?>/elearning/lector_studentquestions.php"><i class="fas fa-question"></i>&nbsp;<?php echo $strQuestions?></a></li>
			<li><a href="<?php echo $strSiteURL ?>/elearning/lector_siteschedules.php"><i class="fas fa-calendar"></i></i>&nbsp;<?php echo $strSchedules?></a></li>
			<li><a href="<?php echo $strSiteURL ?>/elearning/lector_sitelessons.php"><i class="fas fa-book-open"></i>&nbsp;<?php echo $strLessons?></a></li>
			<li><a href="<?php echo $strSiteURL ?>/elearning/lector_sitetests.php"><i class="fas fa-hourglass-half"></i>&nbsp;<?php echo $strTests?></a></li>
		
			<?php
		}
			ElseIf ($_SESSION['function']=='STUDENT'){
			?>
			<li>
				<a href="#"><i class="fas fa-book"></i>&nbsp;<?php echo $strMyCourses?></a>
			
      <?php 
$query="SELECT elearning_enrollments_id, elearning_enrollments_stud_id, elearning_enrollments_course_id, elearning_enrollments_courseschedule_id, elearning_enrollments_active, 
course_ID, course_name
FROM elearning_enrollments, elearning_courses 
WHERE elearning_enrollments.elearning_enrollments_stud_id='$uid' AND elearning_courses.course_ID=elearning_enrollments.elearning_enrollments_course_id 
ORDER BY elearning_enrollments_date DESC" ;
$result2=ezpub_query($conn,$query);
$nume=ezpub_num_rows($result2,$query);
if ($nume==0)
{
echo "<li><li><a href=\"$strSiteURL/elearning/enrollment.php\"><i class=\"fas fa-plus\"></i>&nbsp;$strEnrollInNewCourse</a></li>";
}
Else
{
	?><ul class="menu">
		<?php
While ($row=ezpub_fetch_array($result2)){
	?>
					<?php If ($row["elearning_enrollments_active"]==1) {
						echo "<li><a href=\"$strSiteURL/elearning/student_mycourses.php?cID=$row[course_ID]\"><i class=\"fa fa-book\"></i>$row[course_name]</a></li>";
					}
					else
					{
					echo	"<li><a href=\"#\"><i class=\" fa fa-book\"></i>$row[course_name]</a></li>";
					}
		
 }
 echo "</ul>";
 }?>		
   </li>  
		<li><a href="<?php echo $strSiteURL ?>/elearning/student_siteschedules.php"><i class="large fa fa-calendar"></i>&nbsp;<?php echo $strOtherCourses?></a></li>
		<li><a href="<?php echo $strSiteURL ?>/elearning/student_mydiplomas.php"><i class="large fa fa-graduation-cap"></i>&nbsp;<?php echo $strMyDiplomas?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/elearning/student_myprofile.php"><i class="large fa fa-user-circle"></i>&nbsp;<?php echo $strMyProfile?></a></li>
			</ul>
			</li>
			<?php
		}?>
	</ul>
</div>
	    <div class="top-bar-right text-right">
			<ul class="menu">
			<li><a href="<?php echo $strSiteURL ?>/elearning/logout.php"><i class="fas fa-sign-out-alt"></i></a></li>
</ul>
	  </div>
	  </div>
