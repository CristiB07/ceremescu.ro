<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strDescription="Administrare legislație";
$strPageTitle="Administrează legislația!";

include '../dashboard/header.php';
?>
      <div class="grid-x grid-padding-x">
        <div class="large-12 cell">
<?php

echo "<h1>$strPageTitle</h1>";
if (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM legislatie WHERE lege_ID=" .$_GET['pID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitelaws.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">
</div>";
include '../bottom.php';
die;} // end delete record

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
$lastupdated=date("Y-m-d H:i:s");
if ($_GET['mode']=="new"){
//insert new user
	$mSQL = "INSERT INTO legislatie(";
	$mSQL = $mSQL . "lege_denumire,";
	$mSQL = $mSQL . "lege_modificari,";
	$mSQL = $mSQL . "lege_link,";
	$mSQL = $mSQL . "lege_categorie,";
	$mSQL = $mSQL . "lege_data,";
	$mSQL = $mSQL . "lege_lastupdated,";
	$mSQL = $mSQL . "lege_tip)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$_POST["lege_denumire"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["lege_modificari"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["lege_link"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["lege_categorie"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["lege_data"] . "', ";
	$mSQL = $mSQL . "'" .$lastupdated . "', ";
	$mSQL = $mSQL . "'" .$_POST["lege_tip"] ."')";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
	  echo "<div class=\"callout alert\">$strThereWasAnError</ br>";
  die('Error: ' . ezpub_error() . "</div>");
  }
else{
echo "<div class=\"callout success\">$strRecordAdded</div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitelaws.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\"></div>";
include '../bottom.php';
die;
}}
//ends if post new
else
{// edit
$strWhereClause = " WHERE legislatie.lege_ID=" . $_GET["pID"] . ";";
$query= "UPDATE legislatie SET legislatie.lege_denumire='" . $_POST["lege_denumire"] . "' ," ;
$query= $query . " legislatie.lege_modificari='" . $_POST["lege_modificari"] . "', "; 
$query= $query . " legislatie.lege_link='" . $_POST["lege_link"] . "', "; 
$query= $query . " legislatie.lege_categorie='" . $_POST["lege_categorie"] . "', "; 
$query= $query . " legislatie.lege_data='" . $_POST["lege_data"] . "', "; 
$query= $query . " legislatie.lege_lastupdated='" . $lastupdated . "', "; 
$query= $query . " legislatie.lege_tip='" . $_POST["lege_tip"] . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
	  echo "<div class=\"callout alert\">$strThereWasAnError</ br>";
  die('Error: ' . ezpub_error() . "</div>");
  }
Else{
echo "<div class=\"callout success\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitelaws.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 5000)\"></div></div>";
include '../bottom.php';
die;
}
}
}// ends post if
else { // starts entering data

if (IsSet($_GET['mode']) AND $_GET['mode']=="new"){ // we have new page
?>
<form method="post" action="sitelaws.php?mode=new" >
		    <div class="grid-x grid-padding-x">
              <div class="large-12 cell">
                <label><?php echo $strType?></label>
                <input type="text" name="lege_tip" Type="text" size="30" placeholder="<?php echo $strType?>" required/>
              </div>
            </div>		
		    <div class="grid-x grid-padding-x">
              <div class="large-12 cell">
                <label><?php echo $strTitle?></label>
                <input type="text" name="lege_denumire" Type="text" size="30" placeholder="<?php echo $strTitle?>" required/>
              </div>
            </div>		
			<div class="grid-x grid-padding-x">
              <div class="large-12 cell">
                <label><?php echo $strURL?></label>
                <input type="text" name="lege_link" Type="text" size="30" placeholder="<?php echo $strURL?>" required/>
              </div>
            </div>
					<div class="grid-x grid-padding-x">
              <div class="large-12 cell">
                <label><?php echo $strCategory?></label>
                <input type="text" name="lege_categorie" Type="text" size="30" placeholder="<?php echo $strCategory?>" />
              </div>
            </div>		
			<div class="grid-x grid-padding-x">
              <div class="large-12 cell">
                <label><?php echo $strYear?></label>
                <input type="text" name="lege_data" Type="text" size="30" placeholder="<?php echo $strYear?>" />
              </div>
            </div>
            <div class="grid-x grid-padding-x">
              <div class="large-12 cell"><textarea name="lege_modificari" id="simple-html-editor" class="simple-html-editor" data-upload-dir="legal" rows="10"  ></textarea>
			  </div>
            <div class="grid-x grid-padding-x">
              <div class="large-12 cell"><p align="center"><input type="submit" Value="<?php echo $strAdd?>" name="Submit" class="submit success button"> </p></div>
  </form>
<?php
} // ends if new page
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
	echo "<a href=\"sitelaws.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward\"></i></a>";
$query="SELECT * FROM legislatie WHERE lege_ID=$_GET[pID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
<form method="post" action="sitelaws.php?mode=edit&pID=<?php echo $row['lege_ID']?>" >
         <div class="grid-x grid-padding-x">
              <div class="large-12 cell">
                <label><?php echo $strType?></label>
                <input type="text" name="lege_tip" Type="text" size="30" value="<?php echo $row["lege_tip"]?>" required/>
              </div>
            </div>		
		    <div class="grid-x grid-padding-x">
              <div class="large-12 cell">
                <label><?php echo $strTitle?></label>
                <input type="text" name="lege_denumire" Type="text" size="30" value="<?php echo $row["lege_denumire"]?>" required/>
              </div>
            </div>		
			<div class="grid-x grid-padding-x">
              <div class="large-12 cell">
                <label><?php echo $strURL?></label>
                <input type="text" name="lege_link" Type="text" size="30" value="<?php echo $row["lege_link"]?>" required/>
              </div>
            </div>
					<div class="grid-x grid-padding-x">
              <div class="large-12 cell">
                <label><?php echo $strCategory?></label>
                <input type="text" name="lege_categorie" Type="text" size="30" value="<?php echo $row["lege_categorie"]?>"" />
              </div>
            </div>		
			<div class="grid-x grid-padding-x">
              <div class="large-12 cell">
                <label><?php echo $strYear?></label>
                <input type="text" name="lege_data" Type="text" size="30" value="<?php echo $row["lege_data"]?>"" />
              </div>
            </div>
            <div class="grid-x grid-padding-x">
              <div class="large-12 cell"><textarea name="lege_modificari" id="simple-html-editor" class="simple-html-editor" data-upload-dir="legal" rows="10"  ><?php echo $row["lege_modificari"]?></textarea>
            <div class="grid-x grid-padding-x">
              <div class="large-12 cell">
			   <input type="submit" Value="<?php echo $strModify?>" name="Submit" class="submit success button" align="center"> 
			  </div>
			  </div>
  </form>
<?php
} // ends editing
else
{ // just lists records
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"sitelaws.php?mode=new\" class=\"button\">$strAdd <i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a>
<a href=\"exportlaws.php?type=general\" class=\"button\">$strAdd <i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a>
</div></div>";

$query="SELECT lege_ID, lege_link, lege_categorie, lege_data, lege_denumire, lege_modificari FROM legislatie ";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY lege_categorie ASC, lege_denumire ASC $pages->limit";
$result=ezpub_query($conn,$query);

echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
	
?>
<div class="paginate">
<?php
echo $strTotal . " " .$numar." ".$strLaws ;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"sitelawss.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
echo " <br /><br />";
?>
</div>
<table>
  <thead>
    <tr>
      <th><?php echo $strID?></th>
      <th><?php echo $strCategory?></th>
			<th><?php echo $strTitle?></th>
			<th><?php echo $strUpdated?></th>
			<th><?php echo $strView?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
    </tr>
	</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[lege_ID]</td>
			<td>$row[lege_categorie]</td>
			<td>$row[lege_denumire]</td>
			<td>$row[lege_lastupdated]</td>
			<td><a href=\"sitelaws.php?mode=edit&pID=$row[lege_ID]\" class=\"ask\"><i class=\"fas fa-edit\"></i></a></td>
			<td><a href=\"sitelaws.php?mode=delete&pID=$row[lege_ID]\" class=\"ask\" OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"large fa fa-eraser\" title=\"$strDelete\"></i></a></td>
        </tr>";
	}
echo "</tbody></table>";
}
}
}
?>
</div>
</div>
<?php
include '../bottom.php';
?>