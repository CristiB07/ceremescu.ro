<?php
$role=$_SESSION['clearence'];
$function=$_SESSION['function'];
$uid=$_SESSION['uid'];
if ($useraccount == 1) {
    $current_session_id = session_id();
    $stmt_sess_check = $conn->prepare("SELECT account_sessionid FROM site_accounts WHERE account_id=?");
    $stmt_sess_check->bind_param("i", $uid);
    $stmt_sess_check->execute();
    $res_sess_check = $stmt_sess_check->get_result();
    if ($res_sess_check->num_rows == 1) {
        $row_sess_check = $res_sess_check->fetch_assoc();
        if ($row_sess_check['account_sessionid'] !== $current_session_id) {
            $stmt_sess_check->close();
            session_unset();
            session_destroy();
            header("location:$strSiteURL/account/login.php?message=SES");
            exit();
        }
    }
    $stmt_sess_check->close();
}
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
<li>
    <a href="#"><?php echo $strBusinessIntelligence?></a>
    <ul class="menu">
               <li><a href="<?php echo $strSiteURL ?>/business/companiesinfo.php"><i class="fas fa-file-alt"></i><?php echo $strCompaniesInformation?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/business/searchbalances.php"><i class="fas fa-search"></i><?php echo $strSearchBalances?></a></li>
    </ul>
</li>
<?php if ($helpdesk==1)
    {?>
<li>
    <a href="#"><?php echo $strHelpdesk?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/helpdesk/admin_tickets_all.php"><i class=" fa fa-users"></i>&nbsp;<?php echo $strTickets?></a></li>
    </ul>
</li>
<?php
    }
?>
<?php
}//end of admin check, start CLIENT
elseif ($role=='CLIENT')
{
    $queryclient=ezpub_query($conn,"SELECT utilizator_Prenume, utilizator_Nume FROM date_utilizatori WHERE utilizator_id='$uid'");
    $rowclient=ezpub_fetch_array($queryclient);
    $strClientFirstName=$rowclient['utilizator_Prenume'] ?? '';
    $strClientLastName=$rowclient['utilizator_Nume'] ?? '';
?>
<li>
    <a href="#"><i class="far fa-user "></i>&nbsp;<?php echo $strHello . ' ' . $strClientFirstName. '' . $strClientLastName ?></a>
<ul class="menu">
<li><a href="<?php echo $strSiteURL ?>/account/myprofile.php"><i class="fas fa-user-circle "></i>&nbsp;<?php echo $strMyProfile?></a></li>
<?php
if ($shop=='1') {
?>
<li><a href="<?php echo $strSiteURL ?>/shop/siteorders.php"><i class="fas fa-shopping-cart "></i>&nbsp;<?php echo $strOrders?></a></li>
<?php }
if ($helpdesk=='1') {
?>
<li><a href="<?php echo $strSiteURL ?>/helpdesk/client_my_tickets.php"><i class="fas fa-calendar-check "></i>&nbsp;<?php echo $strTickets?></a></li>
<?php }
if ($elearning==1){
// elearning student
			if ($_SESSION['function']=='STUDENT' || $_SESSION['function']=='BOTH'){
			?>
<li>
    <a href="#"><i class="fas fa-book "></i>&nbsp;<?php echo $strMyCourses?></a>
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
<li><a href=\"$strSiteURL/elearning/enrollment.php\"><i class=\"fas fa-plus \"></i>&nbsp;$strEnrollInNewCourse</a></li>";
}
else
{
	?><ul class="menu">
        <?php
While ($row=ezpub_fetch_array($result2)){
	?>
        <?php If ($row["elearning_enrollments_active"]==1) {
						echo "<li><a href=\"$strSiteURL/elearning/student_mycourses.php?cID=$row[course_ID]\"> <i class=\" fa fa-book\"></i>$row[course_name]</a></li>";
					}
					else
					{
					echo	"<li><a href=\"#\"><i class=\" fa fa-book\"> </i>$row[course_name]</a></li>";
					}
		
 }
echo  "<li><a href=\"$strSiteURL/elearning/enrollment.php\"><i class=\"fas fa-plus \"></i>&nbsp;$strEnrollInNewCourse</a></li>";

 echo "</ul>";
 }?>
</li>
<li><a href="<?php echo $strSiteURL ?>/elearning/student_siteschedules.php"><i class=" fa fa-calendar"></i>&nbsp;<?php echo $strOtherCourses?></a></li>
<li><a href="<?php echo $strSiteURL ?>/elearning/student_mydiplomas.php"><i class=" fa fa-graduation-cap"></i>&nbsp;<?php echo $strMyDiplomas?></a></li>
</ul>
</li>
<?php
            } //end of student check
			 if($shop==1) {?>
                <li><a href="<?php echo $strSiteURL ?>/shop/"><i class="fas fa-shopping-cart "></i>&nbsp;<?php echo $strOnlineShop?></a></li>
                <?php }
		}
?>
</ul>
<li>
    <a href="#"><?php echo $strBusinessIntelligence?></a>
    <ul class="menu">
               <li><a href="<?php echo $strSiteURL ?>/business/companiesinfo.php"><i class="fas fa-file-alt"></i><?php echo $strCompaniesInformation?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/business/searchbalances.php"><i class="fas fa-search"></i><?php echo $strSearchBalances?></a></li>
    </ul>
</li>
<?php
} //end of client check
elseif ($role=='AGENT')
{
?>
   <li>
	      <a href="#"><?php echo $strHelpdesk?></a>
					<ul class="menu">		
		<li><a href="<?php echo $strSiteURL ?>/helpdesk/agent_assigned_tickets.php"><i class="fas fa-calendar-check "></i>&nbsp;<?php echo $strClients?></a></li>
					</ul>
	</li>
	</ul>
<?php
} //end of agent check
?>
</div>
<div class="top-bar-right text-right">
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/login/logout.php"><i class="fas fa-sign-out-alt fa-xl"></i></a></li>
    </ul>
</div>
</div>
