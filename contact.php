<?php
//updated 8.01.2025
include 'settings.php';
include 'classes/common.php';
$strKeywords="Contact Cert PLus SRL";
$strDescription="Pagina de contact Cert Plus SRL";
$strPageTitle="Contact Cert Plus SRL";
$url='contact.php';
include 'header.php';
?>
<div class="grid-x grid-margin-x">
<div class="large-6 medium-6 columns">
			<h3><?php echo $siteCompanyLegalName?>:</h3>
						    	<p><?php echo $siteCompanyLegalAddress?></p>
								<p><?php echo $siteCompanyPhones?><br />
								<?php echo $strEmail .": ".$siteCompanyEmailMasked?><br /><br />
                  </div>
<div class="large-6 medium-6 columns">
        <h3>Unde ne găsiți</h3>
<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d11399.461986155045!2d26.0763991!3d44.4154057!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40b1ff88b8964adf%3A0x4d8e68334bda6fd2!2sCert%20Plus%20SRL!5e0!3m2!1sro!2sro!4v1689352479669!5m2!1sro!2sro" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        <br>
        </div>
    </div>
	<hr />
   <?php
include 'bottom.php';
?>