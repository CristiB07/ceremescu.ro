<?php
if (isset($_SESSION['session_expire']) && time() > $_SESSION['session_expire']) {
    session_destroy();
    header("location: /login/");
    exit();
}
$role=$_SESSION['clearence'];
$ui=$_SESSION['uid'];
if ($role=='ADMIN')
{
?>
<?php if ($billing==1)
    {?>
<li>
    <a href="#"> <?php echo $strClients?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/clients/siteclients.php"><i class="far fa-address-book"></i>
                <?php echo $strClients?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/clients/sitecontracts.php"><i class="far fa-file-alt"></i>
                <?php echo $strContracts?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/clients/sitebranchcontracts.php"><i class="far fa-file-alt"></i>
                <?php echo $strBranchContracts?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/clients/sitesubscribtions.php"><i class="far fa-list-alt"></i>
                <?php echo $strSubscribtions?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/clients/siteclientaspects.php"><i class="far fa-file-alt"></i>
                <?php echo $strClientsAspects?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/clients/siteclientactivities.php"><i class="fa fa-hourglass-half"></i>
                <?php echo $strActivities?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/clients/sitevisitreports.php"><i class="fas fa-newspaper"></i>
                <?php echo $strReports?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/clients/sitecontacts.php"><i class="fas fa-address-book"></i>
                <?php echo $strContacts?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/clients/clientcontractdetails.php"><i class="far fa-address-book"></i>
                <?php echo $strGetContractData?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/admin/exportclients.php"><i class="fas fa-file-export"></i>
                <?php echo $strExportClients?></a></li>
    </ul>
</li>
<?php
    }
?>
<?php if ($billing==1)
    {?>
<li>
    <a href="#"><?php echo $strFinancials?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/billing/siteinvoices.php"><i class="fas fa-file-invoice"></i>
                <?php echo $strInvoices?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/billing/sitebulkinvoices.php"><i class="fas fa-clone"></i>
                <?php echo $strBulkInvoices?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/billing/bulkeinvoice.php"><i class="fas fa-file-upload"></i>
                <?php echo $strBulkEinvoice?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/billing/paymentreminders.php"><i class="fas fa-bell"></i>
                <?php echo $strPaymentReminders?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/billing/sitereceivedinvoices.php"><i class="fas fa-file-invoice-dollar"></i> <?php echo $strReceivedInvoices?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/billing/sitecashin.php"><i class="fas fa-money-check"></i>
                <?php echo $strCashIn?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/billing/sitepayout.php"><i class="fas fa-money-bill-wave"></i>
                <?php echo $strPayout?></a> </li>
        <li><a href="<?php echo $strSiteURL ?>/billing/sitereceipts.php"><i class="fas fa-receipt"></i>
                <?php echo $strReceipts?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/billing/einvoices.php"><i class="fas fa-upload"></i>
                <?php echo $strEinvoices?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/billing/receivedeinvoices.php"><i class="fas fa-inbox"></i>
                <?php echo $strReceivedEinvoices?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/billing/verifymessages.php?mode=verify"><i class="fas fa-inbox"></i>
                <?php echo $strEinvoicesMessages?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/admin/exportinvoices.php"><i class="fas fa-file-export"></i>
                <?php echo $strExportInvoices?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/admin/exportreceivedinvoices.php"><i class="fas fa-file-export"></i>
                <?php echo $strExportReceivedInvoices?></a></li>
    </ul>
</li>
<?php
}?>

<?php if ($administrative==1)
    {?>
<li>
    <a href="#"><?php echo $strAdministrative?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/administrative/personalexpenses.php"><i
                    class="fas fa-file-invoice"></i>&nbsp;<?php echo $strPersonalExpenses?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/administrative/personalworkingdays.php"><i
                    class="fas fa-calendar-alt"></i>&nbsp;<?php echo $strWorkingDays?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/administrative/gasfilling.php"><i
                    class="fas fa-gas-pump"></i>&nbsp;<?php echo $strGasFillings?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/administrative/personalcarsheets.php"><i
                    class="fas fa-car-side"></i>&nbsp;<?php echo $strCarSheet?></a></li>
    </ul>
</li>
<?php 
}
?>
<li>
    <a href="#"><?php echo $strAdministration?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/admin/siteusers.php"><i class="fas fa-user"></i>
                <?php echo $strUsers?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/admin/siteactivities.php"><i class="far fa-list-alt"></i>
                <?php echo $strActivities?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/admin/siteauthorizations.php"><i class="far fa-id-badge"></i>
                <?php echo $strAuthorizations?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/admin/siteerrors.php"><i class="fas fa-exclamation-triangle"></i>
                <?php echo $strErrors?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/admin/sitedbbackup.php"><i class="fas fa-save"></i>
                <?php echo $strDatabaseBackup?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/admin/managetoken.php?op=gettoken"><i class="far fa-file-code"></i>
                <?php echo $strGetToken?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/admin/managetoken.php"><i class="far fa-file-code"></i>
                <?php echo $strRefreshTheToken?></a></li>
    </ul>
</li>
<?php if ($sales==1)
    {?>
<li>
    <a href="#"><?php echo $strProspects?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/sales/siteprospects.php"><i
                    class="fa-xl fa fa-users"></i>&nbsp;<?php echo $strProspects?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/sales/sitevisitreports.php"><i
                    class="large fas fa-newspaper"></i>&nbsp;<?php echo $strVisits?></a></li>
    </ul>
</li>
<?php
    }
?>
<?php if ($helpdesk==1)
    {?>
<li>
    <a href="#"><?php echo $strHelpdesk?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/helpdesk/admin_tickets_all.php"><i
                    class="fa-xl fa fa-users"></i>&nbsp;<?php echo $strTickets?></a></li>
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
        <li><a href="<?php echo $strSiteURL ?>/newsletter/emailnewsletter.php"><i
                    class="fas fa-envelope-open-text"></i>&nbsp;<?php echo $strNewsletter?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/newsletter/newsletterclients.php"><i
                    class="fas fa-users"></i>&nbsp;<?php echo $strSubscribers?></a></li>
    </ul>
</li>
<?php 
}?>
<?php if ($rssreader==1)
    {?>
<li>
    <a href="#"> <?php echo $strRSSReader?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/rssreader/feeds.php"><i
                    class="fas fa-rss-square"></i>&nbsp;<?php echo $strFeeds?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/rssreader/reader.php"><i
                    class="large fas fa-newspaper"></i>&nbsp;<?php echo $strReader?></a></li>
    </ul>
</li>
<?php
}?>
<?php if ($cms==1)
    {?>
<li>
    <a href="#"><?php echo $strCMS?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/cms/sitepages.php"><i
                    class="far fa-file-alt"></i><?php echo $strPages?></a></li>
    </ul>
</li>
<?php 
    }
   
if ($blog==1)
    {?>
<li>
    <a href="#"><?php echo $strBlog?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/blog/blogarticles.php"><i
                    class="far fa-file-alt"></i><?php echo $strArticles?></a></li>
    </ul>
</li>
<?php }?>
<?php if ($shop==1)
    {
        ?>
<li>
    <a href="#"><?php echo $strShop?></a>
    <ul class="menu">
        <li><a href="<?php echo $strSiteURL ?>/shop/siteproducts.php"><i
                    class="fas fa-cart-plus"></i><?php echo $strProducts?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/shop/siteshopclients.php"><i
                    class="far fa-id-card"></i><?php echo $strClients?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/shop/siteorders.php"><i
                    class="fas fa-shopping-cart"></i><?php echo $strOrders?></a></li>
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
        <li><a href="<?php echo $strSiteURL ?>/elearning/sitecoursecategories.php"><i
                    class="fas fa-cart-plus"></i><?php echo $strCategories?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/elearning/sitecourses.php"><i
                    class="far fa-id-card"></i><?php echo $strCourses?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/elearning/sitetrainers.php"><i
                    class="far fa-id-card"></i><?php echo $strTrainers?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/elearning/sitecourseschedules.php"><i
                    class="far fa-id-card"></i><?php echo $strSchedules?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/elearning/sitestudents.php"><i
                    class="fas fa-shopping-cart"></i><?php echo $strStudents?></a></li>
        <li><a href="<?php echo $strSiteURL ?>/elearning/siteenrollments.php"><i
                    class="fas fa-shopping-cart"></i><?php echo $strEnrollments?></a></li>
    </ul>
</li>
</ul>
<?php
    }
?>
<?php
}//end of admin check, start USER
elseif ($role=='USER')
{
?>
 <li>
	        <a href="#"><?php echo $strAdministrative?></a>
	        <ul class="menu">
	            <li><a href="<?php echo $strSiteURL ?>/administrative/personalexpenses.php"><i
	                        class="fas fa-file-invoice"></i>&nbsp;<?php echo $strPersonalExpenses?></a></li>
	            <li><a href="<?php echo $strSiteURL ?>/administrative/personalworkingdays.php"><i
	                        class="fas fa-calendar-alt"></i>&nbsp;<?php echo $strWorkingDays?></a></li>
	            <li><a href="<?php echo $strSiteURL ?>/administrative/gasfilling.php"><i
	                        class="fas fa-gas-pump"></i>&nbsp;<?php echo $strGasFillings?></a></li>
	            <li><a href="<?php echo $strSiteURL ?>/administrative/personalcarsheets.php"><i
	                        class="fas fa-car-side"></i>&nbsp;<?php echo $strCarSheet?></a></li>
	        </ul>
	    </li>
 <li>
	      <a href="#"><?php echo $strClients?></a>
					<ul class="menu">		
		<li><a href="<?php echo $strSiteURL ?>/clients/siteuserclients.php"><i class="fa fa-users fa-xl"></i>&nbsp;<?php echo $strClients?></a></li>
		<li><a href="<?php echo $strSiteURL ?>/clients/siteclientaspects.php"><i class="far fa-file-alt"></i>&nbsp;<?php echo $strClientsAspects?></a></li>
		<li><a href="<?php echo $strSiteURL ?>/clients/siteclientactivities.php"><i class="fa fa-hourglass-half fa-xl"></i>&nbsp;<?php echo $strActivities?></a></li>
		<li><a href="<?php echo $strSiteURL ?>/clients/siteclientauthorizations.php"><i class="fa fa-certificate fa-xl"></i>&nbsp;<?php echo $strAuthorizations?></a></li>
		<li><a href="<?php echo $strSiteURL ?>/clients/sitecontacts.php"><i class="fa fa-address-card fa-xl"></i>&nbsp;<?php echo $strContacts?></a></li>
		<li><a href="<?php echo $strSiteURL ?>/clients/sitevisitreports.php"><i class="fas fa-newspaper fa-xl"></i>&nbsp;<?php echo $strVisits?></a></li>
							</ul>
	</li>
<?php
} //end of user check, start AGENT
elseif ($role=='AGENT')
{
?>
  <li>
	        <a href="#"><?php echo $strAdministrative?></a>
	        <ul class="menu">
	            <li><a href="<?php echo $strSiteURL ?>/administrative/personalexpenses.php"><i
	                        class="fas fa-file-invoice"></i>&nbsp;<?php echo $strPersonalExpenses?></a></li>
	            <li><a href="<?php echo $strSiteURL ?>/administrative/personalworkingdays.php"><i
	                        class="fas fa-calendar-alt"></i>&nbsp;<?php echo $strWorkingDays?></a></li>
	            <li><a href="<?php echo $strSiteURL ?>/administrative/gasfilling.php"><i
	                        class="fas fa-gas-pump"></i>&nbsp;<?php echo $strGasFillings?></a></li>
	            <li><a href="<?php echo $strSiteURL ?>/administrative/personalcarsheets.php"><i
	                        class="fas fa-car-side"></i>&nbsp;<?php echo $strCarSheet?></a></li>
	        </ul>
	    </li>

 <li>
	      <a href="#"><?php echo $strHelpdesk?></a>
					<ul class="menu">		
		<li><a href="<?php echo $strSiteURL ?>/helpdesk/agent_assigned_tickets.php"><i class="fas fa-calendar-check fa-xl"></i>&nbsp;<?php echo $strClients?></a></li>
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
