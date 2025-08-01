<?php
//update 03.01.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare contacte clienți";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM clienti_contacte WHERE contact_ID=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecontacts.php\"
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


	$mSQL = "INSERT INTO clienti_contacte(";
	$mSQL = $mSQL . "client_ID,";
	$mSQL = $mSQL . "contact_nume,";
	$mSQL = $mSQL . "contact_prenume,";
	$mSQL = $mSQL . "contact_telefon,";
	$mSQL = $mSQL . "contact_email,";
	$mSQL = $mSQL . "contact_tip)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$_POST["client_ID"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["contact_nume"] . "', ";
		$mSQL = $mSQL . "'" .$_POST["contact_prenume"] . "', ";
		$mSQL = $mSQL . "'" .$_POST["contact_telefon"] . "', ";
		$mSQL = $mSQL . "'" .$_POST["contact_email"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["contact_tip"] . "') ";
			
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecontacts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{// edit

$strWhereClause = " WHERE clienti_contacte.contact_ID=" . $_GET["cID"] . ";";
$query= "UPDATE clienti_contacte SET clienti_contacte.client_ID='" .$_POST["client_ID"] . "' ," ;
$query= $query . " clienti_contacte.contact_nume='" .$_POST["contact_nume"] . "' ," ;
$query= $query .  " clienti_contacte.contact_prenume='" .$_POST["contact_prenume"] . "' ," ;
$query= $query . "  clienti_contacte.contact_telefon='" .$_POST["contact_telefon"] . "' ," ;
$query= $query .  " clienti_contacte.contact_email='" .$_POST["contact_email"] . "' ," ;
$query= $query . " clienti_contacte.contact_tip='" .$_POST["contact_tip"] . "' "; 
$query= $query . $strWhereClause;

if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecontacts.php\"
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
			  <p><a href="sitecontacts.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form Method="post" id="users" Action="sitecontacts.php?mode=new" >
  			    <div class="grid-x grid-margin-x">
			  <div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strClient?></label>
	  <select name="client_ID" class="required">
           <option value=""><?php echo $strClient?></option>
          <?php 
		  $sql="SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, Contract_Alocat, Client_Denumire 
		  FROM clienti_date, clienti_contracte 
			WHERE Contract_Alocat='$code' AND  clienti_date.ID_Client=clienti_contracte.ID_Client
			ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
          <?php
}?>
        </select></div>
		<div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strName?></label>
	  <input name="contact_nume" Type="text" size="30" class="required" />
</div>
		<div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strFirstName?></label>
	  <input name="contact_prenume" Type="text" size="30" class="required" />
</div>
</div>
	  <div class="grid-x grid-margin-x">
		<div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strPhone?></label>
	  <input name="contact_telefon" Type="text" size="30" class="required" />
</div>
		<div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strEmail?></label>
	  <input name="contact_email" Type="text" size="30" class="required" />
</div>
		<div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strFunction?></label>
	  <input name="contact_tip" Type="text" size="30" class="required" />
</div>
</div>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_Aloc, 
clienti_contacte.client_ID, clienti_contacte.contact_ID, clienti_contacte.contact_nume, clienti_contacte.contact_prenume,  clienti_contacte.contact_telefon, clienti_contacte.contact_email, clienti_contacte.contact_tip
FROM clienti_contacte, clienti_date, clienti_contracte
WHERE contact_ID=$_GET[cID] AND clienti_date.Client_Aloc='$code' AND clienti_date.ID_Client=clienti_contacte.client_ID
ORDER By Client_Denumire ASC";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitecontacts.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form Method="post" id="users" Action="sitecontacts.php?mode=edit&cID=<?php echo $row['contact_ID']?>" >
  			    <div class="grid-x grid-margin-x">
			  <div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strClient?></label>
	  <select name="client_ID" class="required">
          <?php 
		  $sql="SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, Contract_Alocat, Client_Denumire 
			FROM clienti_date, clienti_contracte 
			WHERE Contract_Alocat='$code' AND  clienti_date.ID_Client=clienti_contracte.ID_Client
			ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  <?php if ($row["ID_Client"]==$rss["ID_Client"]) echo "selected"; ?> value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
          <?php
}?>
        </select></div>
	 <div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strName?></label>
	  <input name="contact_nume" Type="text" size="30" class="required" value="<?php echo $row["contact_nume"]?>"/>
</div>
	 <div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strFirstName?></label>
	  <input name="contact_prenume" Type="text" size="30" class="required" value="<?php echo $row["contact_prenume"]?>"/>
	</div>	
	</div>	
			  			    <div class="grid-x grid-margin-x">
			  <div class="large-4 medium-4 small-4 cell">	 
	  <label><?php echo $strPhone?></label>
	  <input name="contact_telefon" Type="text" size="30" class="required" value="<?php echo $row["contact_telefon"]?>"/>
</div>
	 <div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strEmail?></label>
	  <input name="contact_email" Type="text" size="30" class="required" value="<?php echo $row["contact_email"]?>"/>
</div>
	 <div class="large-4 medium-4 small-4 cell"> 
	  <label><?php echo $strFunction?></label>
	  <input name="contact_tip" Type="text" size="30" class="required" value="<?php echo $row["contact_tip"]?>"/>
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
<a href=\"sitecontacts.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_contracte.Contract_Alocat, clienti_contracte.ID_Client, 
clienti_contacte.contact_ID, clienti_contacte.client_ID, clienti_contacte.contact_nume, clienti_contacte.contact_prenume,  clienti_contacte.contact_telefon, 
clienti_contacte.contact_email, clienti_contacte.contact_tip
FROM clienti_contacte, clienti_date, clienti_contracte
WHERE clienti_date.ID_Client=clienti_contracte.ID_Client 
AND   clienti_date.ID_Client=clienti_contacte.client_ID 
AND clienti_contracte.Contract_Alocat='$code'
ORDER By Client_Denumire ASC";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<table id="rounded-corner" width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strClient?></th>
			<th><?php echo $strContact?></th>
			<th><?php echo $strFunction?></th>
			<th><?php echo $strPhone?></th>
			<th><?php echo $strEmail?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[contact_prenume]". " " ."$row[contact_nume]</td>
			<td>$row[contact_tip]</td>
			<td>$row[contact_telefon]</td>
			<td>$row[contact_email]</td>
			  <td><a href=\"sitecontacts.php?mode=edit&cID=$row[contact_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"sitecontacts.php?mode=delete&cID=$row[contact_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td colspan=\"7\">&nbsp;</td></tr></tfoot></table>";
}
}
}
?>
</div>
</div>
<?php
include '../bottom.php';
?>