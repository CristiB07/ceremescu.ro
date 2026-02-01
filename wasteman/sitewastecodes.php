<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';

$strPageTitle="Administrare coduri deșeuri";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$clearence=$_SESSION['function'];


?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM deseuri_coduri WHERE cd_id=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitewastecodes.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){
//insert new user


	$mSQL = "INSERT INTO deseuri_coduri(";
	$mSQL = $mSQL . "cd_01,";
	$mSQL = $mSQL . "cd_02,";
	$mSQL = $mSQL . "cd_03,";
	$mSQL = $mSQL . "cd_description)";

	$mSQL = $mSQL . "values(";
	$mSQL = $mSQL . "'" . $_POST["cd_01"] . "', ";
	$mSQL = $mSQL . "'" . $_POST["cd_02"] . "', ";
	$mSQL = $mSQL . "'" . $_POST["cd_03"] . "', ";
	$mSQL = $mSQL . "'" . $_POST["cd_description"] . "')";

//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
else{
echo "<div class=\"callout success\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitewastecodes.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}}
else
{// edit
$strWhereClause = " WHERE deseuri_coduri.cd_id=" . $_GET["cID"] . ";";
$query= "UPDATE deseuri_coduri SET deseuri_coduri.cd_01='" . $_POST["cd_01"] . "' ," ;
$query= $query . "deseuri_coduri.cd_02='" . $_POST["cd_02"] . "' ," ;
$query= $query . "deseuri_coduri.cd_03='" . $_POST["cd_03"] . "' " ;
$query= $query . " deseuri_coduri.cd_description='" .str_replace("'","&#39;",$_POST["cd_description"]) . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
else{
echo "<div class=\"callout success\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitewastecodes.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
}
}
else {
?>
        <?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="sitewastecodes.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post"  Action="sitewastecodes.php?mode=new">
            <div class="grid-x grid-margin-x">
                
                <div class="large-1 medium-1 small-4 cell">
                    <label><?php echo $strClassCode?>
                        <input name="cd_01" type="text" class="required" value="" />
                    </label>
                </div>
                <div class="large-1 medium-1 small-4 cell">
                    <label><?php echo $strCategoryCode?>
                        <input name="cd_02" type="text" class="required" value="" />
                    </label>
                </div>
                <div class="large-1 medium-1 small-4 cell">
                    <label><?php echo $strWasteCode?>
                        <input name="cd_03" type="text" class="required" value="" />
                    </label>
            </div>

                <div class="large-9 medium-9 small-8 cell">
                    <label><?php echo $strDetails?>
                        <textarea name="cd_description" rows="5"></textarea>
                    </label>
                </div>
            </div>
                        <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"> 
                    <input type="submit" value="<?php echo $strAdd?>" name="Submit" class="button success" />
                </div>
            </div>
        </form>

        <?php
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM deseuri_coduri WHERE cd_id=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="sitewastecodes.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
                   <div class="grid-x grid-margin-x">
                
                <div class="large-1 medium-1 small-4 cell">
                    <label><?php echo $strClassCode?>
                        <input name="cd_01" type="text" class="required" value="<?php echo htmlspecialchars($row['cd_01']); ?>" />
                    </label>
                </div>
                <div class="large-1 medium-1 small-4 cell">
                    <label><?php echo $strCategoryCode?>
                        <input name="cd_02" type="text" class="required" value="<?php echo htmlspecialchars($row['cd_02']); ?>" />
                    </label>
                </div>
                <div class="large-1 medium-1 small-4 cell">
                    <label><?php echo $strWasteCode?>
                        <input name="cd_03" type="text" class="required" value="<?php echo htmlspecialchars($row['cd_03']); ?>" />
                    </label>
            </div>

                <div class="large-9 medium-9 small-8 cell">
                    <label><?php echo $strDetails?>
                        <textarea name="cd_description" rows="5"><?php echo htmlspecialchars($row['cd_description']); ?></textarea>
                    </label>
                </div>
            </div>
                        <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"> 
                    <input type="submit" value="<?php echo $strModify?>" name="Submit" class="button success" />
                </div>
            </div>
        </form>
        <?php
}
else
{
	?>
      

        <?php
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"sitewastecodes.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";

$query_base = "SELECT * FROM deseuri_coduri";
$result=ezpub_query($conn, $query_base);
$numar=ezpub_num_rows($result,$query_base);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query = $query_base . " ORDER BY cd_01 ASC $pages->limit";
$result=ezpub_query($conn,$query);

echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>
<div class=\"paginate\"><a href=\"sitewastecodes.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;</div>";
}
else {
?>
        <div class="paginate">
            <?php
echo $strTotal . " " .$numar." ".$strCodes;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"sitewastecodes.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
        </div>
        <table width="100%">
            <thead>
                <tr>
                    <th><?php echo $strClassCode?></th>
                    <th><?php echo $strCategoryCode?></th>
                    <th><?php echo $strWasteCode?></th>
                    <th><?php echo $strWasteCodeComplete?></th>
                    <th><?php echo $strDetails?></th>
                    <th><?php echo $strEdit?></th>
                    <th><?php echo $strDelete?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[cd_01]</td>
			<td>$row[cd_02]</td>
			<td>$row[cd_03]</td>
			<td>$row[cd_01] $row[cd_02] $row[cd_03]</td>
			<td>$row[cd_description]</td>
			 <td><a href=\"sitewastecodes.php?mode=edit&cID=$row[cd_id]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			 <td><a href=\"sitewastecodes.php?mode=delete&cID=$row[cd_id]\ OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td colspan=\"7\">&nbsp;</td></tr></tfoot></table>";
?>
                <div class="paginate">
                    <?php
echo $pages->display_pages() . " <a href=\"sitewastecodes.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
                </div>
                <?php
}
}
}

?>
    </div>
</div>
<?php
include '../bottom.php';
?>