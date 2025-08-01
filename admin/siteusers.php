<?php
// update 03.01.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare utilizatori";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
}
?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM date_utilizatori WHERE utilizator_ID=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteusers.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){
//insert new user

	$mSQL = "INSERT INTO date_utilizatori(";
	$mSQL = $mSQL . "utilizator_Nume,";
	$mSQL = $mSQL . "utilizator_Prenume,";
	$mSQL = $mSQL . "utilizator_Email,";
	$mSQL = $mSQL . "utilizator_Parola,";
	$mSQL = $mSQL . "utilizator_Role,";
	$mSQL = $mSQL . "utilizator_Phone,";
	$mSQL = $mSQL . "utilizator_Carplate,";
	$mSQL = $mSQL . "utilizator_Function,";
	$mSQL = $mSQL . "utilizator_CRM,";
	$mSQL = $mSQL . "utilizator_Billing,";
	$mSQL = $mSQL . "utilizator_Sales,";
	$mSQL = $mSQL . "utilizator_Clients,";
	$mSQL = $mSQL . "utilizator_CMS,";
	$mSQL = $mSQL . "utilizator_Projects,";
	$mSQL = $mSQL . "utilizator_Shop,";
	$mSQL = $mSQL . "utilizator_Administrative,";
	$mSQL = $mSQL . "utilizator_Lab,";
	$mSQL = $mSQL . "utilizator_Elearning,";
	$mSQL = $mSQL . "utilizator_Team,";
	$mSQL = $mSQL . "utilizator_Code)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Nume"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Prenume"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Email"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Parola"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Role"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Phone"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Carplate"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Function"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_CRM"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Billing"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Sales"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Clients"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_CMS"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Projects"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Shop"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Administrative"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Lab"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Elearning"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Team"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["utilizator_Code"]) . "') ";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteusers.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{// edit
$strWhereClause = " WHERE date_utilizatori.utilizator_ID=" . $_GET["cID"] . ";";
$query= "UPDATE date_utilizatori SET date_utilizatori.utilizator_Nume='" .str_replace("'","&#39;",$_POST["utilizator_Nume"]) . "' ," ;
$query= $query . "date_utilizatori.utilizator_Prenume='" .str_replace("'","&#39;",$_POST["utilizator_Prenume"]) . "' ," ;
$query= $query . "date_utilizatori.utilizator_Email='" .str_replace("'","&#39;",$_POST["utilizator_Email"]) . "' ," ;
$query= $query . "date_utilizatori.utilizator_Parola='" .str_replace("'","&#39;",$_POST["utilizator_Parola"]) . "' ," ;
$query= $query . "date_utilizatori.utilizator_Role='" .str_replace("'","&#39;",$_POST["utilizator_Role"]) . "' ," ;
$query= $query . "date_utilizatori.utilizator_Carplate='" .str_replace("'","&#39;",$_POST["utilizator_Carplate"]) . "' ," ;
$query= $query . "date_utilizatori.utilizator_Function='" .str_replace("'","&#39;",$_POST["utilizator_Function"]) . "' ," ;
$query= $query . "date_utilizatori.utilizator_CRM='" .str_replace("'","&#39;",$_POST["utilizator_CRM"]) . "' ," ;
$query= $query . "date_utilizatori.utilizator_Billing='" .str_replace("'","&#39;",$_POST["utilizator_Billing"]) . "' ," ;
$query= $query . "date_utilizatori.utilizator_Sales='" .str_replace("'","&#39;",$_POST["utilizator_Sales"]) . "' ," ;
$query= $query . "date_utilizatori.utilizator_CMS='" .str_replace("'","&#39;",$_POST["utilizator_CMS"]) . "' ," ;
$query= $query . "date_utilizatori.utilizator_Projects='" .str_replace("'","&#39;",$_POST["utilizator_Projects"]) . "' ," ;
$query= $query . "date_utilizatori.utilizator_Administrative='" .str_replace("'","&#39;",$_POST["utilizator_Administrative"]) . "' ," ;
$query= $query . "date_utilizatori.utilizator_Lab='" .str_replace("'","&#39;",$_POST["utilizator_Lab"]) . "' ," ;
$query= $query . "date_utilizatori.utilizator_Elearning='" .str_replace("'","&#39;",$_POST["utilizator_Elearning"]) . "' ," ;
$query= $query . "date_utilizatori.utilizator_Team='" .str_replace("'","&#39;",$_POST["utilizator_Team"]) . "' ," ;
$query= $query . " date_utilizatori.utilizator_Code='" .str_replace("'","&#39;",$_POST["utilizator_Code"]) . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteusers.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}
}
}
Else {
?>
<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="siteusers.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form Method="post" id="users" Action="siteusers.php?mode=new" >


    <div class="grid-x grid-margin-x">
	  <div class="large-3 medium-3 small-3 cell">
			  <label><?php echo $strName?></label>
	  <input name="utilizator_Nume" Type="text"  class="required" />
	  </div>			  
	  <div class="large-3 medium-3 small-3 cell">
			  <label><?php echo $strFirstName?></label>
	  <input name="utilizator_Prenume" Type="text"  class="required" />
	  </div>			  <div class="large-3 medium-3 small-3 cell">
			  <label><?php echo $strEmail?></label>
	  <input name="utilizator_Email" Type="text"  class="email required"  />
	  </div>			  <div class="large-3 medium-3 small-3 cell">
			  <label><?php echo $strPassword?></label>
	  <input name="utilizator_Parola" Type="text"  class="required" />
	  </div>
	  </div>
			    <div class="grid-x grid-margin-x">
	<div class="large-2 medium-2 small-2 cell">
			  <label><?php echo $strRole?></label>
	  <input name="utilizator_Role" Type="text"  class="required" />
	  </div>	
	  <div class="large-2 medium-2 small-2 cell">
			  <label><?php echo $strTeam?></label>
	  <input name="utilizator_Team" Type="text"  class="required" />
	  </div>			  
	  <div class="large-1 medium-1 small-1 cell">
			  <label><?php echo $strCode?></label>
	  <input name="utilizator_Code" Type="text"  class="required" />
	  </div>			  
	  <div class="large-2 medium-2 small-2 cell">
			  <label><?php echo $strPhone?></label>
	  <input name="utilizator_Phone" Type="text"  class="required"  />
	  </div>			  
	  <div class="large-3 medium-3 small-3 cell">
			  <label><?php echo $strCarPlate?></label>
	  <input name="utilizator_Carplate" Type="text"  class="required" />
	  </div>	  
	  <div class="large-2 medium-2 small-2 cell">
			  <label><?php echo $strFunction?></label>
	  <input name="utilizator_Function" Type="text"  class="required" />
	  </div>
	  </div>
	    <div class="grid-x grid-margin-x">
	  	 <div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppCRM?></label>
      <input name="utilizator_CRM" Type="radio" value="0" /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_CRM" Type="radio" value="1" checked><?php echo $strNo?>
    </div>		  
	<div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppCMS?></label>
      <input name="utilizator_CMS" Type="radio" value="0" /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_CMS" Type="radio" value="1" checked><?php echo $strNo?>
    </div>		
	<div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppAdministrative?></label>
      <input name="utilizator_Administrative" Type="radio" value="0" /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Administrative" Type="radio" value="1" checked><?php echo $strNo?>
    </div>	
	  	 <div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppBilling?></label>
      <input name="utilizator_Billing" Type="radio" value="0" /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Billing" Type="radio" value="1" checked><?php echo $strNo?>
    </div>		  	
	  	 <div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppSales?></label>
      <input name="utilizator_Sales" Type="radio" value="0" /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Sales" Type="radio" value="1" checked><?php echo $strNo?>
    </div>	
	<div class="large-1 medium-1 small-! cell">
      <label><?php echo $strAppClients?></label>
      <input name="utilizator_Clients" Type="radio" value="0" /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Clients" Type="radio" value="1" checked><?php echo $strNo?>
    </div>	
      	 <div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppProjects?></label>
      <input name="utilizator_Projects" Type="radio" value="0" /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Projects" Type="radio" value="1" checked><?php echo $strNo?>
    </div>
		  	 <div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppShop?></label>
      <input name="utilizator_Shop" Type="radio" value="0" /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Shop" Type="radio" value="1" checked><?php echo $strNo?>
    </div>
		  	 <div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppLab?></label>
      <input name="utilizator_Lab" Type="radio" value="0" /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Lab" Type="radio" value="1" checked><?php echo $strNo?>
    </div>
		  	 <div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppElearning?></label>
      <input name="utilizator_Elearning" Type="radio" value="0" /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Elearning" Type="radio" value="1" checked><?php echo $strNo?>
    </div>
    </div>
	
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell text-center"> <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM date_utilizatori WHERE utilizator_ID=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="siteusers.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form Method="post" id="users" Action="siteusers.php?mode=edit&cID=<?php echo $row['utilizator_ID']?>" >
			  
					    <div class="grid-x grid-margin-x">
			  <div class="large-3 medium-3 small-3 cell">
			  <label><?php echo $strName?></label>
	  <input name="utilizator_Nume" Type="text"  value="<?php echo $row["utilizator_Nume"]?>" class="required" />
	  </div>			  
	  <div class="large-3 medium-3 small-3 cell">
			  <label><?php echo $strFirstName?></label>
	  <input name="utilizator_Prenume" Type="text"   value="<?php echo $row["utilizator_Prenume"]?>" class="required" />
	  </div>			  <div class="large-3 medium-3 small-3 cell">
			  <label><?php echo $strEmail?></label>
	  <input name="utilizator_Email" Type="text"   value="<?php echo $row["utilizator_Email"]?>" class="email required"  />
	  </div>			  <div class="large-3 medium-3 small-3 cell">
			  <label><?php echo $strPassword?></label>
	  <input name="utilizator_Parola" Type="text"   value="<?php echo $row["utilizator_Parola"]?>" class="required" />
	  </div>
	  </div>
			    <div class="grid-x grid-margin-x">
			  <div class="large-2 medium-2 small-2 cell">
			  <label><?php echo $strRole?></label>
	  <input name="utilizator_Role" Type="text"   value="<?php echo $row["utilizator_Role"]?>" class="required" />
	  </div>		
	  <div class="large-2 medium-2 small-2 cell">
			  <label><?php echo $strTeam?></label>
	  <input name="utilizator_Team" Type="text"   value="<?php echo $row["utilizator_Team"]?>" class="required" />
	  </div>			  
	  <div class="large-1 medium-1 small-1 cell">
			  <label><?php echo $strCode?></label>
	  <input name="utilizator_Code" Type="text"  value="<?php echo $row["utilizator_Code"]?>"  class="required" />
	  </div>			  
	  <div class="large-2 medium-2 small-2 cell">
			  <label><?php echo $strPhone?></label>
	  <input name="utilizator_Phone" Type="text"   value="<?php echo $row["utilizator_Phone"]?>" class="required"  />
	  </div>			 
	  <div class="large-2 medium-2 small-2 cell">
			  <label><?php echo $strCarPlate?></label>
	  <input name="utilizator_Carplate" Type="text"   value="<?php echo $row["utilizator_Carplate"]?>" class="required" />
	  </div>	  
	  <div class="large-3 medium-3 small-3 cell">
			  <label><?php echo $strFunction?></label>
	  <input name="utilizator_Function" Type="text"  value="<?php echo $row["utilizator_Function"]?>" class="required" />
	  </div>
	  </div>
  <div class="grid-x grid-margin-x">
	  	 <div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppCRM?></label>
      <input name="utilizator_CRM" Type="radio" value="0" <?php If ($row["utilizator_CRM"]==0) echo "checked"?> /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_CRM" Type="radio" value="1" <?php If ($row["utilizator_CRM"]==1) echo "checked"?>><?php echo $strNo?>
    </div>		  
	<div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppCMS?></label>
      <input name="utilizator_CMS" Type="radio" value="0" <?php If ($row["utilizator_CMS"]==0) echo "checked"?> /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_CMS" Type="radio" value="1" <?php If ($row["utilizator_CMS"]==1) echo "checked"?> ><?php echo $strNo?>
    </div>		
	<div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppAdministrative?></label>
      <input name="utilizator_Administrative" Type="radio" value="0" <?php If ($row["utilizator_Administrative"]==0) echo "checked"?> /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Administrative" Type="radio" value="1" <?php If ($row["utilizator_Administrative"]==1) echo "checked"?>><?php echo $strNo?>
    </div>	
    	  	 <div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppBilling?></label>
      <input name="utilizator_Billing" Type="radio" value="0" <?php If ($row["utilizator_Billing"]==0) echo "checked"?> /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Billing" Type="radio" value="1" <?php If ($row["utilizator_Billing"]==1) echo "checked"?>><?php echo $strNo?>
    </div>		  	
	  	 <div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppSales?></label>
      <input name="utilizator_Sales" Type="radio" value="0" <?php If ($row["utilizator_Sales"]==0) echo "checked"?> /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Sales" Type="radio" value="1" <?php If ($row["utilizator_Sales"]==1) echo "checked"?>><?php echo $strNo?>
    </div>	
	<div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppClients?></label>
      <input name="utilizator_Clients" Type="radio" value="0" <?php If ($row["utilizator_Clients"]==0) echo "checked"?> /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Clients" Type="radio" value="1" <?php If ($row["utilizator_Clients"]==1) echo "checked"?>><?php echo $strNo?>
    </div>	
	  	 <div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppProjects?></label>
      <input name="utilizator_Projects" Type="radio" value="0" <?php If ($row["utilizator_Projects"]==0) echo "checked"?> /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Projects" Type="radio" value="1" <?php If ($row["utilizator_Projects"]==1) echo "checked"?>><?php echo $strNo?>
    </div>
		  	 <div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppShop?></label>
      <input name="utilizator_Shop" Type="radio" value="0" <?php If ($row["utilizator_Shop"]==0) echo "checked"?> /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Shop" Type="radio" value="1" <?php If ($row["utilizator_Shop"]==1) echo "checked"?>><?php echo $strNo?>
    </div>
		  	 <div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppLab?></label>
      <input name="utilizator_Lab" Type="radio" value="0" <?php If ($row["utilizator_Lab"]==0) echo "checked"?>/> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Lab" Type="radio" value="1" <?php If ($row["utilizator_Lab"]==1) echo "checked"?>><?php echo $strNo?>
    </div>
		  	 <div class="large-1 medium-1 small-1 cell">
      <label><?php echo $strAppElearning?></label>
      <input name="utilizator_Elearning" Type="radio" value="0" <?php If ($row["utilizator_Elearning"]==0) echo "checked"?> /> <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Elearning" Type="radio" value="1" <?php If ($row["utilizator_Elearning"]==1) echo "checked"?>><?php echo $strNo?>
    </div>
    </div>
		  
			  
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell text-center"> <input Type="submit" Value="<?php echo $strModify?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
<?php
}
Else
{
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"siteusers.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";
$query="SELECT * FROM date_utilizatori";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> 
<table width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strID?></th>
			<th><?php echo $strFirstName?></th>
			<th><?php echo $strName?></th>
			<th><?php echo $strFunction?></th>
			<th><?php echo $strEmail?></th>
			<th><?php echo $strCarPlate?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[utilizator_ID]
			<td>$row[utilizator_Prenume]
			<td>$row[utilizator_Nume]
			<td>$row[utilizator_Function]
			<td>$row[utilizator_Email]
			<td>$row[utilizator_Carplate]
			  <td><a href=\"siteusers.php?mode=edit&cID=$row[utilizator_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a>
			<td><a href=\"siteusers.php?mode=delete&cID=$row[utilizator_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a>
        </tr>";
}
echo "</tbody><tfoot><tr><td><td  colspan=\"6\"><em></em><td>&nbsp;</tr></tfoot></table></div></div>";
}
}
}
?>
</div>
</div>
<?php
include '../bottom.php';
?>