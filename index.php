<!-- Start content-->
<?php
require_once 'settings.php';
If ((isSet($_GET['message'])) AND $_GET['message']=="ER")
{
echo "<div class=\"callout alert\">$strThereWasAnError</div>" ;
}
 If ($sitefunction=='Site')
                {
                    include '_site/firstpage.php';
                }
                elseif ($sitefunction=='CRM')
                {
                    include 'login/index.php';
                }
                    else 
                {
            	//he just try to get here directly or something is wrong
                header("location: $strSiteURL/index.php?message=ER");
            }
?>
<hr />
<!-- Ends content-->
<?php include 'bottom.php'?>