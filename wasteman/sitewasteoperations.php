<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';

$strPageTitle="Administrare operațiuni deșeuri";
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

$nsql="DELETE FROM deseuri_coduri_operatiuni WHERE operatiune_id=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitewasteoperations.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){
//insert new operation

	$mSQL = "INSERT INTO deseuri_coduri_operatiuni(";
	$mSQL = $mSQL . "operatiune_tip,";
	$mSQL = $mSQL . "operatiune_cod,";
	$mSQL = $mSQL . "operatiune_descriere)";

	$mSQL = $mSQL . "values(";
	$mSQL = $mSQL . "'" .$_POST["operatiune_tip"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["operatiune_cod"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["operatiune_descriere"] . "') ";
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
    window.location = \"sitewasteoperations.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}}
else
{// edit
$strWhereClause = " WHERE deseuri_coduri_operatiuni.operatiune_id=" . $_GET["cID"] . ";";
$query= "UPDATE deseuri_coduri_operatiuni SET deseuri_coduri_operatiuni.operatiune_tip='" .$_POST["operatiune_tip"] . "' ," ;
$query= $query . "deseuri_coduri_operatiuni.operatiune_cod='" .$_POST["operatiune_cod"] . "' ," ;
$query= $query . "deseuri_coduri_operatiuni.operatiune_descriere='" .$_POST["operatiune_descriere"] . "' "; 
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
    window.location = \"sitewasteoperations.php\"
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
                <p><a href="sitewasteoperations.php" class="button"><?php echo $strBack?>&nbsp;
                <i class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post" action="sitewasteoperations.php?mode=new">
            <div class="grid-x grid-margin-x">
                <div class="large-2 medium-2 small-4 cell">
                    <label><?php echo $strType?></label>
                        <input type="radio" name="operatiune_tip" value="1" id="valorificare">
                        <label for="valorificare"><?php echo $strValorization?></label>
                        <input name="operatiune_tip" type="radio" value="2" id="eliminare">
                        <label for="eliminare"><?php echo $strElimination?></label>
                    
                </div>
                <div class="large-1 medium-1 small-4 cell">
                    <label><?php echo $strCode?>
                        <input name="operatiune_cod" type="text" class="required" value="" />
                    </label>
                </div>
                <div class="large-9 medium-9 small-12 cell">
                    <label><?php echo $strDetails?>
                        <textarea name="operatiune_descriere" rows="5"></textarea>
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
$query="SELECT * FROM deseuri_coduri_operatiuni WHERE operatiune_id=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="sitewasteoperations.php" class="button"><?php echo $strBack?>&nbsp;<i class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post" id="users" action="sitewasteoperations.php?mode=edit&cID=<?php echo $row['operatiune_id']?>">
                        <div class="grid-x grid-margin-x">
                <div class="large-2 medium-2 small-4 cell">
                    <label><?php echo $strType?></label>
                        <input type="radio" name="operatiune_tip" value="1" id="valorificare" <?php if ($row['operatiune_tip']==1) {echo "checked";}?>>
                        <label for="valorificare"><?php echo $strValorization?></label>
                        <input name="operatiune_tip" type="radio" value="2" id="eliminare" <?php if ($row['operatiune_tip']==2) {echo "checked";}?>>
                        <label for="eliminare"><?php echo $strElimination?></label>                    
                </div>
                <div class="large-1 medium-1 small-4 cell">
                    <label><?php echo $strCode?>
                        <input name="operatiune_cod" type="text" class="required" value="<?php echo $row['operatiune_cod']?>" />
                    </label>
                </div>
                <div class="large-9 medium-9 small-12 cell">
                    <label><?php echo $strDetails?>
                        <textarea name="operatiune_descriere" rows="5"><?php echo $row['operatiune_descriere']?></textarea>
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
} // ends if post
else
{

echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"sitewasteoperations.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";
$query="SELECT * FROM deseuri_coduri_operatiuni";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY operatiune_cod DESC $pages->limit";
$result=ezpub_query($conn,$query);

echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>
<div class=\"paginate\"><a href=\"sitewasteoperations.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;</div>";
}
else {
?>
        <div class="paginate">
            <?php
echo $strTotal . " " .$numar." ".$strOperations;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"sitewasteoperations.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
        </div>
        <table width="100%">
            <thead>
                <tr>
                    <th><?php echo $strType?></th>
                    <th><?php echo $strCode?></th>
                    <th><?php echo $strDetails?></th>
                    <th><?php echo $strEdit?></th>
                    <th><?php echo $strDelete?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=ezpub_fetch_array($result)){
        		echo"<tr>
                <td>";
                        echo ($row['operatiune_tip'] == 1) ? $strValorization : (($row['operatiune_tip'] == 2) ? $strElimination : '');
                        echo "</td>
                <td>$row[operatiune_cod]</td>
                <td>$row[operatiune_descriere]</td>
			 <td><a href=\"sitewasteoperations.php?mode=edit&cID=$row[operatiune_id]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			 <td><a href=\"sitewasteoperations.php?mode=delete&cID=$row[operatiune_id]\" OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td colspan=\"6\">&nbsp;</td></tr></tfoot></table>";
?>
                <div class="paginate">
                    <?php
echo $pages->display_pages() . " <a href=\"sitewasteoperations.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
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