<?php
//updated 8.01.2025
include 'settings.php';
include 'classes/common.php';
$strKeywords="Contact " . $strSiteOwner;
$strDescription="Pagina de contact " . $strSiteOwner;
$strPageTitle="Contact " . $strSiteOwner;
$pageurl='contact.php';
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
        <?php if ($strSiteOwnerId == '1') { ?>
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2849.4087761585274!2d26.11678457679874!3d44.424776471075944!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40b1ff468ca8a8e9%3A0x121d157e9ec0cd54!2zQ29uc2FsdGlzIENvbnN1bHRhbsWjxIMgxZ9pIEF1ZGl0!5e0!3m2!1sro!2sro!4v1776249631776!5m2!1sro!2sro" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        <br>
        <?php } else { ?>
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d11399.461986155045!2d26.0763991!3d44.4154057!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40b1ff88b8964adf%3A0x4d8e68334bda6fd2!2sCert%20Plus%20SRL!5e0!3m2!1sro!2sro!4v1689352479669!5m2!1sro!2sro"
            width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"></iframe>
        <br>
        <?php } ?>

    </div>
</div>
<hr />
<?php
include 'bottom.php';
?>