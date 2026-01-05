<!--Footer-->
<?php
//updated 15.05.2025
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
  echo "No content here.";
} else {
 
?>
<footer>
  <div class="grid-x grid-padding-x">
        <div class="large-12 cell">
					<p class="smaller" align="center">&copy; <?php echo date("Y") . " ".$siteCompanyLegalName?><br />
					CUI: <?php echo $siteVATNumber?>, R.C.: <?php echo $siteCompanyRegistrationNr?>, CS: <?php echo $siteCompanySocialCapital?><br />
					<?php echo $siteCompanyLegalAddress?><br />
					<?php echo $siteFirstAccount?><br />
					<a href="<?php echo $strSiteURL ?>/termeni.php"><?php echo $strTermsAndConditions ?></a> |
					<a href="<?php echo $strSiteURL ?>/politica.php"><?php echo $strPrivacyPolicy ?></a> |
          <a href="<?php echo $strSiteURL ?>/cookies.php"><?php echo $strCookiePolicy ?></a><br /></p>
                </div>
            </div>
</footer>
</div>
<!-- Start scripts-->
    <script src="<?php echo $strSiteURL ?>/js/foundation/jquery.js"></script>
    <script src="<?php echo $strSiteURL ?>/js/foundation/what-input.js"></script>
    <script src="<?php echo $strSiteURL ?>/js/foundation/foundation.js"></script>
    <script src="<?php echo $strSiteURL ?>/js/foundation/app.js"></script>
	    <script>
      $(document).foundation();
    </script>
	<!-- Ends scrips-->
	<!--end footer-->
	<?php
	// Include activity and error tracker
	include_once __DIR__ . '/classes/tracker.class.php';
	}
?>

</body>
</html>