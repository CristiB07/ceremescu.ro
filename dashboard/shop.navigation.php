<div class="title-bar" data-responsive-toggle="responsive-menu" data-hide-for="medium">
  <button class="menu-icon" type="button" data-toggle="responsive-menu"></button>
  <div class="title-bar-title"><?php echo $strMenu?></div>
</div>
 <div class="top-bar" data-sticky data-options="marginTop:0;" style="width:100%">
	<div class="top-bar-left" id="responsive-menu">
		<ul class="dropdown menu" data-dropdown-menu>
			<li class="menu-text"><?php echo $strWelcome ?></li>
			<li><a href="<?php echo $strSiteURL ?>admin/dashboard.php">Acasă</a></li>
						<li>
		<a href="#"><?php echo $strAdministrative?></a>
					<ul class="menu">
							<li><a href="<?php echo $strSiteURL ?>administrative/personalexpenses.php"><i class="fas fa-file-invoice"></i>&nbsp;<?php echo $strPersonalExpenses?></a></li>
							<li><a href="<?php echo $strSiteURL ?>administrative/personalworkingdays.php"><i class="fas fa-calendar-alt"></i>&nbsp;<?php echo $strWorkingDays?></a></li>
							<li><a href="<?php echo $strSiteURL ?>administrative/gasfilling.php"><i class="fas fa-gas-pump"></i>&nbsp;<?php echo $strGasFillings?></a></li>
							<li><a href="<?php echo $strSiteURL ?>administrative/personalcarsheets.php"><i class="fas fa-car-side"></i>&nbsp;<?php echo $strCarSheet?></a></li>
					</ul>
		</li>
			<li>
				<a href="#"><?php echo $strAdministration?></a>
					<ul class="menu">
						<li><a href="<?php echo $strSiteURL ?>/admin/siteusers.php"><i class="fas fa-user"></i> <?php echo $strUsers?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/admin/siteactivities.php"><i class="far fa-list-alt"></i> <?php echo $strActivities?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/admin/siteauthorizations.php"><i class="far fa-id-badge"></i> <?php echo $strAuthorizations?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/admin/sitedbbackup.php"><i class="fas fa-save"></i> <?php echo $strDatabaseBackup?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/admin/exportclients.php"><i class="far fa-copy"></i> <?php echo $strExportClients?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/admin/exportinvoices.php"><i class="far fa-copy"></i> <?php echo $strExportInvoices?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/admin/managetoken.php?op=gettoken"><i class="far fa-file-code"></i> <?php echo $strGetToken?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/admin/managetoken.php"><i class="far fa-file-code"></i> <?php echo $strRefreshTheToken?></a></li>
					</ul>
			</li>
			<li>
				<a href="#"> <?php echo $strClients?></a>
					<ul class="menu">
						<li><a href="<?php echo $strSiteURL ?>/clients/siteclients.php"><i class="far fa-address-book"></i> <?php echo $strClients?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/clients/sitecontacts.php"><i class="fas fa-address-book"></i> <?php echo $strContacts?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/clients/sitecontracts.php"><i class="far fa-file-alt"></i> <?php echo $strContracts?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/clients/sitebranchcontracts.php"><i class="far fa-file-alt"></i> <?php echo $strBranchContracts?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/clients/sitesubscribtions.php"><i class="far fa-list-alt"></i> <?php echo $strSubscribtions?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/clients/siteclientfinancials.php"><i class="fas fa-file-alt"></i> <?php echo $strFinancials?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/clients/siteclientauthorizations.php"><i class="far fa-id-badge"></i><?php echo $strClientAuthorizations?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/clients/sitetasks.php"><i class="far fa-list-alt"></i> <?php echo $strTasks?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/clients/datecontract.php"><i class="far fa-copy"></i> <?php echo $strGetContractData?></a></li>
					</ul>
			</li>
			<li>
			<a href="#"><?php echo $strProspects?></a>
					<ul class="menu">		
				<li><a href="<?php echo $strSiteURL ?>/sales/siteprospects.php"><i class="large fa fa-users"></i>&nbsp;<?php echo $strProspects?></a></li>
				<li><a href="<?php echo $strSiteURL ?>/sales/sitevisitreports.php"><i class="large fas fa-newspaper"></i>&nbsp;<?php echo $strVisits?></a></li>
							</ul>
			</li>			
			<li>
			<a href="#"> <?php echo $strNewsletter?></a>
					<ul class="menu">		
				<li><a href="<?php echo $strSiteURL ?>/newsletter/emailnewsletter.php"><i class="fas fa-envelope-open-text"></i>&nbsp;<?php echo $strNewsletter?></a></li>
				<li><a href="<?php echo $strSiteURL ?>/newsletter/newsletetrclients.php"><i class="fas fa-users"></i>&nbsp;<?php echo $strSubscribers?></a></li>
							</ul>
			</li>	
			<li>
			<a href="#"> <?php echo $strRSSReader?></a>
					<ul class="menu">		
				<li><a href="<?php echo $strSiteURL ?>/rssreader/feeds.php"><i class="fas fa-rss-square"></i>&nbsp;<?php echo $strFeeds?></a></li>
				<li><a href="<?php echo $strSiteURL ?>/rssreader/reader.php"><i class="large fas fa-newspaper"></i>&nbsp;<?php echo $strReader?></a></li>
							</ul>
			</li>
				<li>
				<a href="#"><?php echo $strFinancials?></a>
					<ul class="menu">		
						<li><a href="<?php echo $strSiteURL ?>/billing/siteinvoices.php"><?php echo $strInvoices?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/billing/sitebulkinvoices.php"><?php echo $strBulkInvoices?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/billing/sitecashin.php"><?php echo $strCashIn?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/billing/sitereceipts.php"><?php echo $strReceipts?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/billing/efacturi.php"><?php echo $strEinvoices?></a></li>
						<li><a href="<?php echo $strSiteURL ?>/billing/verify_messages.php?mode=verify"><?php echo $strEinvoicesMessages?></a></li>

					</ul>
			</li>	
	<li>
				<a href="#"><?php echo $strCMS?></a>
					<ul class="menu">
						<li><a href="<?php echo $strSiteURL ?>/cms/sitepages.php"><i class="far fa-file-alt"></i><?php echo $strPages?></a></li>
					</ul>
			</li>
				<li>
				<a href="#"><?php echo $strShop?></a>
					<ul class="menu">
			<li><a href="<?php echo $strSiteURL ?>/shop/siteproducts.php"><i class="fas fa-cart-plus"></i><?php echo $strProducts?></a></li>
			<li><a href="<?php echo $strSiteURL ?>/shop/siteshopclients.php"><i class="far fa-id-card"></i><?php echo $strClients?></a></li>
			<li><a href="<?php echo $strSiteURL ?>/shop/siteorders.php"><i class="fas fa-shopping-cart"></i><?php echo $strOrders?></a></li>
					</ul>
			</li>
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
</div>
	    <div class="top-bar-right text-right">
			<ul class="menu">
			<li><a href="<?php echo $strSiteURL ?>/login/logout.php"><i class="fas fa-sign-out-alt"></i></a></li>
</ul>
	  </div>
	  </div>
