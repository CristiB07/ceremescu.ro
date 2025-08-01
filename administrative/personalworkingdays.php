<?php
//update 29.07.2025
include '../settings.php';
include '../classes/paginator.class.php';
include '../classes/common.php';
$strPageTitle="Administrare pontaje";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
}
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$month= date('m');
$year=date('Y');
$lastmonth = date('m', strtotime(date('Y-m')." -1 month"));
$lastyear = date('Y', strtotime(date('Y-m')." -1 month"));
 //Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $month);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$currentmonthname = $formatter->format($dateObj);			
$dateObj   = DateTime::createFromFormat('!m', $lastmonth);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$lastmonthname = $formatter->format($dateObj);
If ((isSet($_GET['message'])) AND $_GET['message']=="Error"){
echo "<div class=\"callout alert\">$strThereWasAnError</div></div></div>" ;
}
If ((isSet($_GET['message'])) AND $_GET['message']=="Success"){
echo "<div class=\"callout success\">$strMessageSent</div><>/div></div>" ;
}
?>

	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<h1><?php echo $strPageTitle?></h1>
				  <form Method="post" id="users" Action="pwd2excel.php">
				  <div class="grid-x grid-margin-x">
			      <div class="large-4 medium-4 small-4 cell">
<h3><?php echo $strSendWD?></h3>
</div>	
<div class="large-4 medium-4 small-4 cell">
			  <h3><?php echo $strFillWD?></h3>
</div>	
<div class="large-4 medium-4 small-4 cell">
			  <h3><?php echo $strShowWD?></h3>
</div>			  
</div>				  
				  <div class="grid-x grid-margin-x">
			      <div class="large-2 medium-2 small-2 cell">
<label><?php echo $strMonth?></label>	 
		<select name="month">
	<option value="00" selected>--</option>
         <?php for ( $m = 1; $m <= 12; $m ++) {
    		
     		//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
    			echo "<OPTION value=\"$m\">$monthname</OPTION>";} 
			?>
        </select> 
				</div>
     <div class="large-1 medium-1 small-1 cell"> 
<label><?php echo $strYear?></label>			 
		<select name="year">
		<option value="0000" selected>--</option>
		<?php
		$cy=date("Y");
		$fy=$cy+1;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
    	echo "<OPTION value=\"$y\">$y</OPTION>";} 
			?>
        </select>
</div>
     <div class="large-1 medium-1 small-1 cell"> <label>&nbsp;</label><p align="right"><input Type="submit" Value="<?php echo $strSend?>" name="Submit" class="button"> </p></div>

			  </form>
			 <script language="JavaScript" type="text/JavaScript">
<!-- jump menu
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
</script> 		  

			      <div class="large-4 medium-4 small-4 cell">
<label><?php echo $strMonth?></label>	 
		 <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
			<option value="00" selected>--</option>
				<option value="workingdays.php?mode=fill&month=<?php echo $lastmonth?>&year=<?php echo $lastyear?>"><?php echo $lastmonthname." ". $lastyear?></option>
				<option value="workingdays.php?mode=fill&month=<?php echo $month?>&year=<?php echo $year?>"><?php echo $currentmonthname." ". $year?></option>
			</select> 
				</div>
     
			  			 
			      <div class="large-4 medium-4 small-4 cell">
<label><?php echo $strMonth?></label>	 
		 <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
	<option value="00" selected>--</option>
<option value="workingdays.php?mode=show&month=<?php echo $lastmonth?>&year=<?php echo $lastyear?>"><?php echo $lastmonthname." ". $lastyear?></option>
<option value="workingdays.php?mode=show&month=<?php echo $month?>&year=<?php echo $year?>"><?php echo $currentmonthname." ". $year?></option>
        </select> 
				</div>
				</div>
</div>
</div>
<hr/>
<?php
include '../bottom.php';
?>