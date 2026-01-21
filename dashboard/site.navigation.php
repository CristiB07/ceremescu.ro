<?php
$role=$_SESSION['clearence'];
$function=$_SESSION['function'];
$uid=$_SESSION['uid'];
if ($role=='ADMIN')
{
?>
<li>
    <a href="#"><?php echo $strAdministration?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/admin/siteusers.php"><i class="fas fa-user"></i><?php echo $strUsers?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/admin/siteerrors.php"><i class="fas fa-exclamation-triangle"></i><?php echo $strErrors?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/admin/backupdb.php"><i class="fas fa-save"></i><?php echo $strDatabaseBackup?></a></li>
    </ul>
</li>
<?php if ($helpdesk==1)
    {?>
<li>
    <a href="#"><?php echo $strHelpdesk?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/helpdesk/admin_tickets_all.php"><i class="fa-xl fa fa-users"></i>&nbsp;<?php echo $strTickets?></a></li>
    </ul>
</li>
<?php
    }
?>
<?php if ($newsletter==1)
    {?>
<li>
    <a href="#"> <?php echo $strNewsletter?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/newsletter/emailnewsletter.php"><i class="fas fa-envelope-open-text"></i>&nbsp;<?php echo $strNewsletter?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/newsletter/newsletterclients.php"><i class="fas fa-users"></i>&nbsp;<?php echo $strSubscribers?></a></li>
    </ul>
</li>
<?php 
}?>
<?php if ($rssreader==1)
    {?>
<li>
    <a href="#"> <?php echo $strRSSReader?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/rssreader/feeds.php"><i class="fas fa-rss-square"></i>&nbsp;<?php echo $strFeeds?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/rssreader/reader.php"><i class="large fas fa-newspaper"></i>&nbsp;<?php echo $strReader?></a></li>
    </ul>
</li>
<?php
}?>
<?php if ($cms==1)
    {?>
<li>
    <a href="#"><?php echo $strCMS?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/cms/sitepages.php"><i class="far fa-file-alt"></i><?php echo $strPages?></a></li>
    </ul>
</li>
<?php 
    }
   
if ($blog==1)
    {?>
<li>
    <a href="#"><?php echo $strBlog?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/blog/blogarticles.php"><i class="far fa-file-alt"></i><?php echo $strArticles?></a></li>
    </ul>
</li>
<?php }?>
<?php if ($shop==1)
    {
        ?>
<li>
    <a href="#"><?php echo $strShop?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/shop/siteproducts.php"><i class="fas fa-cart-plus"></i><?php echo $strProducts?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/shop/siteshopclients.php"><i class="far fa-id-card"></i><?php echo $strClients?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/shop/siteorders.php"><i class="fas fa-shopping-cart"></i><?php echo $strOrders?></a></li>
    </ul>
</li>
<?php 
    }
?>
<?php if ($elearning==1)
    {?>
<li>
    <a href="#"><?php echo $strElearning?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/elearning/sitecoursecategories.php"><i class="fas fa-cart-plus"></i><?php echo $strCategories?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/elearning/sitecourses.php"><i class="far fa-id-card"></i><?php echo $strCourses?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/elearning/sitetrainers.php"><i class="far fa-id-card"></i><?php echo $strTrainers?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/elearning/sitecourseschedules.php"><i class="far fa-id-card"></i><?php echo $strSchedules?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/elearning/sitestudents.php"><i class="fas fa-shopping-cart"></i><?php echo $strStudents?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/elearning/siteenrollments.php"><i class="fas fa-shopping-cart"></i><?php echo $strEnrollments?></a></li>
    </ul>
</li>
</ul>
<?php
    }
?>
<?php
}//end of admin check, start CLIENT
elseif ($role=='CLIENT')
{
    $queryclient=ezpub_query($conn,"SELECT account_first_name, account_last_name FROM site_accounts WHERE account_id='$uid'");
    $rowclient=ezpub_fetch_array($queryclient);
    $strClientFirstName=$rowclient['account_first_name'];
    $strClientLastName=$rowclient['account_last_name'];
?>
<li>
    <a href="#"><i class="far fa-user fa-xl"></i>&nbsp;<?php echo $strHello . ' ' . $strClientFirstName. '' . $strClientLastName ?></a>
<ul class="menu">
<li><a href="<?php echo $strSiteURL ?>/account/myprofile.php"><i class="fas fa-user-circle fa-xl"></i>&nbsp;<?php echo $strMyProfile?></a></li>
<?php
if ($shop=='1') {
?>
<li><a href="<?php echo $strSiteURL ?>/shop/siteorders.php"><i class="fas fa-shopping-cart fa-xl"></i>&nbsp;<?php echo $strOrders?></a></li>
<?php }
if ($helpdesk=='1') {
?>
<li><a href="<?php echo $strSiteURL ?>/helpdesk/client_my_tickets.php"><i class="fas fa-calendar-check fa-xl"></i>&nbsp;<?php echo $strTickets?></a></li>
<?php }
if ($elearning==1){
// elearning student
			if ($_SESSION['function']=='STUDENT' || $_SESSION['function']=='BOTH'){
			?>
<li>
    <a href="#"><i class="fas fa-book fa-xl"></i>&nbsp;<?php echo $strMyCourses?></a>
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
echo "<li>
<li><a href=\"$strSiteURL/elearning/enrollment.php\"><i class=\"fas fa-plus fa-xl\"></i>&nbsp;$strEnrollInNewCourse</a></li>";
}
else
{
	?><ul class="menu">
        <?php
While ($row=ezpub_fetch_array($result2)){
	?>
        <?php If ($row["elearning_enrollments_active"]==1) {
						echo "<li><a href=\"$strSiteURL/elearning/student_mycourses.php?cID=$row[course_ID]\"> <i class=\"fa-xl fa fa-book\"></i>$row[course_name]</a></li>";
					}
					else
					{
					echo	"<li><a href=\"#\"><i class=\"fa-xl fa fa-book\"> </i>$row[course_name]</a></li>";
					}
		
 }
echo  "<li><a href=\"$strSiteURL/elearning/enrollment.php\"><i class=\"fas fa-plus fa-xl\"></i>&nbsp;$strEnrollInNewCourse</a></li>";

 echo "</ul>";
 }?>
</li>
<li><a href="<?php echo $strSiteURL ?>/elearning/student_siteschedules.php"><i class="fa-xl fa fa-calendar"></i>&nbsp;<?php echo $strOtherCourses?></a></li>
<li><a href="<?php echo $strSiteURL ?>/elearning/student_mydiplomas.php"><i class="fa-xl fa fa-graduation-cap"></i>&nbsp;<?php echo $strMyDiplomas?></a></li>
</ul>
</li>

<?php
            } //end of student check
			 if($shop==1) {?>
                <li><a href="<?php echo $strSiteURL ?>/shop/"><i class="fas fa-shopping-cart fa-xl"></i>&nbsp;<?php echo $strOnlineShop?></a></li>
                <?php }
		}
?>
</ul>
<?php
} //end of client check
elseif ($role=='AGENT')
{
?>
   <li>
	      <a href="#"><?php echo $strHelpdesk?></a>
					<ul class="menu">		
		<li><a href="<?php echo $strSiteURL ?>/helpdesk/agent_assigned_tickets.php"><i class="fas fa-calendar-check fa-xl"></i>&nbsp;<?php echo $strClients?></a></li>
					</ul>
	</li>
	</ul>
<?php
} //end of agent check
elseif ($role=='TRAINER')
            			{
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
else {
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
<li><a href="<?php echo $strSiteURL ?>/elearning/lector_siteschedules.php"><i class="fas fa-calendar"></i>&nbsp;<?php echo $strSchedules?></a></li>
<li><a href="<?php echo $strSiteURL ?>/elearning/lector_sitelessons.php"><i class="fas fa-book-open"></i>&nbsp;<?php echo $strLessons?></a></li>
<li><a href="<?php echo $strSiteURL ?>/elearning/lector_sitetests.php"><i class="fas fa-hourglass-half"></i>&nbsp;<?php echo $strTests?></a></li>

<?php
		}
?>

</div>
<div class="top-bar-right text-right">
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/login/logout.php"><i class="fas fa-sign-out-alt fa-xl"></i></a></li>
    </ul>
</div>
</div>
