<?php
//update 05.02.2025
include '../settings.php';
include '../classes/paginator.class.php';
include '../classes/common.php';
$strDescription="Administreaza cursanții";
$strPageTitle="Administreaza cursanții";

if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/account/login.php?message=MLF");
}
include '../dashboard/header.php';
?>
<div class="grid-x grid-padding-x">
    <div class="large-12 medium-12 small-12 cell">

        <?php

echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="view"){
If (IsSet($_GET['sID']) AND is_numeric($_GET['sID'])){
$query="SELECT * FROM newsletter_subscribers WHERE newsletter_email_ID=$_GET[sID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
echo "<a href=\"sitenweslettersubscribers.php\" class=\"button\"><i class=\"fas fa-users fa-xl\">$strSubscribers</i>></a>";
echo "<table width=\"100%\">";
echo"<tr><td>ID</td><td>$row[newsletter_email_ID]</td></tr>
	<tr><td>$strFirstName</td><td>$row[newsletter_first_name]</td></tr>
	<tr><td>$strName</td><td>$row[newsletter_last_name]</td></tr>
	<tr><td>$strEmail</td><td>$row[newsletter_email]</td></tr>
	<tr><td>$strPhone</td><td>$row[newsletter_phone]</td></tr>
	<tr><td>$strAddress</td><td>$row[newsletter_gender_reveal]</td></tr>
	<tr><td>$strCity</td><td>$row[newsletter_date_subscribed]</td></tr>
	<tr><td>$strCity</td><td>$row[newsletter_date_confirmed]</td></tr>
	<tr><td>$strCity</td><td>$row[newsletter_date_subscribed]</td></tr>
	<tr><td>$strCity</td><td>$row[newsletter_IP_subscribed]</td></tr>
	<tr><td>$strCity</td><td>$row[newsletter_email_date_unsubscribed]</td></tr>
		</table>";
		
	include '../bottom.php';
die;
}
else
{
echo "<div class=\"callout alert\">$strThereWasAnError</div></div></div>"; ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitenweslettersubscribers.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";

include '../bottom.php';
die;}}

If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM newsletter_subscribers WHERE newsletter_email_ID=" .$_GET['sID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitenweslettersubscribers.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include 'bottom.php';
die;}

If (IsSet($_GET['mode']) AND $_GET['mode']=="activate"){

$nsql="UPDATE newsletter_subscribers SET newsletter_active=1 WHERE newsletter_email_ID=" .$_GET['sID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordUpdated</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitenweslettersubscribers.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

If (IsSet($_GET['mode']) AND $_GET['mode']=="deactivate"){

$nsql="UPDATE newsletter_subscribers SET newsletter_active=0 WHERE newsletter_email_ID=" .$_GET['sID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordUpdated</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitenweslettersubscribers.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include 'bottom.php';
die;}
else
{
$query="SELECT * FROM newsletter_subscribers";
$result=ezpub_query($conn,$query);
$nume=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $nume;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query2= $query . " ORDER BY newsletter_last_name ASC $pages->limit" ;
$result2=ezpub_query($conn,$query2);

if ($nume==0)
{
echo "<div class=\"callout alert\">$strNoRecordsFound</div>";
}
else {
?>
        <div class="paginate">
            <?php
echo $strTotal . " " .$nume." ".$strStudents ;
echo " <br /><br />";
echo $pages->display_pages();
?>
        </div>
        <table width="100%">
            <thead>
                <tr>
                    <th scope="col" class="rounded-company"><?php echo $strID?></th>
                    <th scope="col" class="rounded"><?php echo $strFirstName?></th>
                    <th scope="col" class="rounded"><?php echo $strName?></th>
                    <th scope="col" class="rounded"><?php echo $strEmail?></th>
                    <th scope="col" class="rounded"><?php echo $strPhone?></th>
                    <th scope="col" class="rounded"><?php echo $strDetails?></th>
                    <th scope="col" class="rounded"><?php echo $strDelete?></th>
                    <th scope="col" class="rounded-q4"><?php echo $strActive?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=ezpub_fetch_array($result2)){
    		echo"<tr>
			<td>$row[newsletter_email_ID]</td>
			<td>$row[newsletter_first_name]</td>
			<td>$row[newsletter_last_name]</td>
			<td>$row[newsletter_email]</td>
			<td>$row[newsletter_phone]</td>
			<td><a href=\"sitenweslettersubscribers.php?mode=view&sID=$row[newsletter_email_ID]\" ><i class=\"fas fa-info\"></i></td>
			<td><a href=\"sitenweslettersubscribers.php?mode=delete&sID=$row[newsletter_email_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>";
if ($row["newsletter_active"]=='1'){			
echo		"<td><a href=\"sitenweslettersubscribers.php?mode=deactivate&sID=$row[newsletter_email_ID]\"  OnClick=\"return confirm('$strConfirmdeActivate');\"><i class=\"fas fa-user\" title=\"$strdeActivate\"></i></a></td>";}
else{
echo		"<td><a href=\"sitenweslettersubscribers.php?mode=activate&sID=$row[newsletter_email_ID]\"  OnClick=\"return confirm('$strConfirmActivate');\"><i class=\"fas fa-user-slash\" title=\"$strActivate\"></i></a></td>";}	
echo "</tr>";
}
echo "</tbody><tfoot><tr><td class=\"rounded-foot-left\"></td><td  colspan=\"6\"><em></em></td><td class=\"rounded-foot-right\">&nbsp;</td></tr></tfoot></table>";
?>
  <div class="paginate">
                    <?php
echo $pages->display_pages();
?>
             </div>
<?php
}
}
?>
    </div>
</div>
<?php
include '../bottom.php';
?>