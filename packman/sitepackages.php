<?php
// update 30.12.2022
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare activități";
include '../dashboard/header.php';
?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM ambalaje WHERE ambalaj_id=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"success callout\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitepackages.php\"
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

	$mSQL = "INSERT INTO ambalaje(";
	$mSQL = $mSQL . "ambalaj_nume,";
	$mSQL = $mSQL . "ambalaj_cod)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$_POST["ambalaj_nume"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["ambalaj_cod"] . "') ";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
else{
echo "<div class=\"success callout\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitepackages.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
else
{// edit
$strWhereClause = " WHERE ambalaje.ambalaj_id=" . $_GET["cID"] . ";";
$query= "UPDATE ambalaje SET ambalaje.ambalaj_nume='" .$_POST["ambalaj_nume"] . "' ," ;
$query= $query . " ambalaje.ambalaj_cod='" .$_POST["ambalaj_cod"] . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
else{
echo "<div class=\"success callout\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitepackages.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}
}
}
else {
?>
<?php
if (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitepackages.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form method="post" id="users" action="sitepackages.php?mode=new" >
			    <div class="grid-x grid-margin-x">
			  <div class="large-8 medium-8 small-8 cell">
			  <label><?php echo $strTitle?></label>
	  <input name="ambalaj_nume" type="text" class="required" />
	  </div>
			  <div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strDetails?></label>
	  <input name="ambalaj_cod" type="text" class="required" />
	  </div>
	  </div>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> <input type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
<?php
}
elseif (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM ambalaje WHERE ambalaj_id=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitepackages.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form method="post" id="users" action="sitepackages.php?mode=edit&cID=<?php echo $row['ambalaj_id']?>" >
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <label><?php echo $strTitle?></label></TD>
	  <input name="ambalaj_nume" type="text" value="<?php echo $row['ambalaj_nume'] ?>" class="required" />
	</div>
			  <div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strDetails?></label>
	  <input name="ambalaj_cod" type="text" class="required" value="<?php echo $row['ambalaj_cod'] ?>" />
</div>
</div>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> <input type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
<?php
}
else
{
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"sitepackages.php?mode=new\"><i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";
$query="SELECT * FROM ambalaje";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
?>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> 
<table width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strID?></th>
			<th><?php echo $strTitle?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[ambalaj_id]</td>
			<td>$row[ambalaj_nume]</td>
			  <td><a href=\"sitepackages.php?mode=edit&cID=$row[ambalaj_id]\" class=\"ask\"><i class=\"large fas fa-pencil-alt\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"sitepackages.php?mode=delete&cID=$row[ambalaj_id]\" class=\"ask\" OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"large fa fa-eraser\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"2\"><em></em></td><td>&nbsp;</td></tr></tfoot></table></div></div>";
}
}
}
?>
</div>
</div>
<?php
include '../bottom.php';
?>