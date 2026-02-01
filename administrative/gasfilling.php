<?php
include '../settings.php';
include '../classes/paginator.class.php';
include '../classes/common.php';
$strPageTitle="Administrare alimentări";
include '../dashboard/header.php';

if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/index.php?message=MLF");
}

$uid=$_SESSION['uid'];
$code=$_SESSION['code'];

  $mindate = strtotime("-1 year", time());
  $mindate = date("Y-m-d", $mindate);
    $maxdate = strtotime("+1 year", time());
  $maxdate = date("Y-m-d", $maxdate);
  
If ((isSet($_GET['message'])) AND $_GET['message']=="Error"){
echo "<div class=\"callout alert\">$strThereWasAnError</div>" ;
}
If ((isSet($_GET['message'])) AND $_GET['message']=="Success"){
echo "<div class=\"callout success\">$strMessageSent</div>" ;
}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

// Validate cID parameter
if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
    die('<div class="callout alert">Invalid record ID</div>');
}

$cID = intval($_GET['cID']);

// Authorization check: verify record belongs to current user
$stmt = $conn->prepare("SELECT alimentare_aloc FROM administrative_alimentari WHERE alimentare_ID=?");
$stmt->bind_param("i", $cID);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();
$stmt->close();

if (!$record || $record['alimentare_aloc'] !== $code) {
    die('<div class="callout alert">Unauthorized access</div>');
}
// Delete record using prepared statement
$stmt = $conn->prepare("DELETE FROM administrative_alimentari WHERE alimentare_ID=?");
$stmt->bind_param("i", $cID);
$stmt->execute();
$stmt->close();

echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-1);
}
setTimeout('delayer()', 1500);
//-->
</script>";
include '../bottom.php';
exit();
} // ends delete

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

If ($_GET['mode']=="new"){
//insert new record

// Validate required fields
if (!isset($_POST['alimentare_valoare'], $_POST['alimentare_litri'], $_POST['alimentare_data'], 
    $_POST['alimentare_platit'], $_POST['alimentare_auto'], $_POST['alimentare_km'], $_POST['alimentare_bf'])) {
    die('<div class="callout alert">All fields are required</div>');
}

// Sanitize and validate inputs
$suma = floatval(str_replace(",",".",$_POST["alimentare_valoare"]));
$litri = floatval(str_replace(",",".",$_POST["alimentare_litri"]));
$alimentare_data = trim($_POST["alimentare_data"]);
$alimentare_platit = intval($_POST["alimentare_platit"]);
$alimentare_auto = trim($_POST["alimentare_auto"]);
$alimentare_km = floatval($_POST["alimentare_km"]);
$alimentare_bf = trim($_POST["alimentare_bf"]);

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $alimentare_data)) {
    die('<div class="callout alert">Invalid date format</div>');
}

// Use prepared statement
$stmt = $conn->prepare("INSERT INTO administrative_alimentari(alimentare_litri, alimentare_valoare, alimentare_data, alimentare_platit, alimentare_auto, alimentare_aloc, alimentare_km, alimentare_bf) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ddsissds", $litri, $suma, $alimentare_data, $alimentare_platit, $alimentare_auto, $code, $alimentare_km, $alimentare_bf);
			
//It executes the SQL
	if (!$stmt->execute())
	{
  $stmt->close();
  die('Error: ' . $conn->error);
	}
  else{ //continue with post new
  $stmt->close();

$alimentare="Alimentare auto";

// Use prepared statement
$stmt = $conn->prepare("INSERT INTO administrative_deconturi(decont_descriere, decont_suma, decont_luna, decont_data, decont_achitat_card, decont_user, decont_document) VALUES(?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sdsssss", $alimentare, $suma, $alimentare_data, $alimentare_data, $alimentare_platit, $code, $alimentare_bf);
			
//It executes the SQL
if (!$stmt->execute())
  {
  $stmt->close();
  die('Error: ' . $conn->error);
	  }
	  else {
$stmt->close();
echo "<div class=\"callout success\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-2);
}
setTimeout('delayer()', 1500);
//-->
</script>";
include '../bottom.php';
exit();
}}}
else
{// edit
// Validate cID parameter
if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
    die('<div class="callout alert">Invalid record ID</div>');
}

$cID = intval($_GET['cID']);

// Authorization check: verify record belongs to current user
$stmt = $conn->prepare("SELECT alimentare_aloc FROM administrative_alimentari WHERE alimentare_ID=?");
$stmt->bind_param("i", $cID);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();
$stmt->close();

if (!$record || $record['alimentare_aloc'] !== $code) {
    die('<div class="callout alert">Unauthorized access</div>');
}

// Validate required fields
if (!isset($_POST['alimentare_bf'], $_POST['alimentare_valoare'], $_POST['alimentare_litri'], 
    $_POST['alimentare_km'], $_POST['alimentare_data'])) {
    die('<div class="callout alert">All fields are required</div>');
}

// Sanitize inputs
$alimentare_bf = trim($_POST["alimentare_bf"]);
$suma = floatval(str_replace(",",".",$_POST["alimentare_valoare"]));
$alimentare_litri = floatval($_POST["alimentare_litri"]);
$alimentare_km = floatval($_POST["alimentare_km"]);
$alimentare_data = trim($_POST["alimentare_data"]);

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $alimentare_data)) {
    die('<div class="callout alert">Invalid date format</div>');
}

// Use prepared statement
$stmt = $conn->prepare("UPDATE administrative_alimentari SET alimentare_bf=?, alimentare_valoare=?, alimentare_litri=?, alimentare_km=?, alimentare_data=? WHERE alimentare_ID=?");
$stmt->bind_param("sdddsi", $alimentare_bf, $suma, $alimentare_litri, $alimentare_km, $alimentare_data, $cID);

if (!$stmt->execute())
  {
  $stmt->close();
  die('Error: ' . $conn->error);
  }
else{
$stmt->close();
echo "<div class=\"callout success\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"gasfilling.php\"
}
setTimeout('delayer()', 1500);
//-->
</script>";
include '../bottom.php';
exit();
}
}
} // ends if post
else {
?>
        <?php
if (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="gasfilling.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post" action="gasfilling.php?mode=new">
            <div class="grid-x grid-margin-x">
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strCarPlate?>
                        <?php
	  // Use prepared statement
	  $stmt = $conn->prepare("SELECT utilizator_Carplate FROM date_utilizatori WHERE utilizator_ID=?");
	  $stmt->bind_param("i", $uid);
	  $stmt->execute();
	  $result = $stmt->get_result();
	  $row = $result->fetch_assoc();
	  $stmt->close();
	  ?>
                        <input name="alimentare_auto" id="alimentare_auto" type="text" value="<?php echo htmlspecialchars($row["utilizator_Carplate"], ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strPaidWithCard?>
                        <input name="alimentare_platit" type="radio" value="0" checked /> <?php echo $strCompanyCard?>&nbsp;&nbsp;
                        <input name="alimentare_platit" type="radio" value="3"><?php echo $strLeasingCard?>
                    </label>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strSum?>
                        <input name="alimentare_valoare" type="text" value="" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strDate?>
                        <input name="alimentare_data" type="date" value="<?php echo date("Y-m-d")?>"
                            min="<?php echo $mindate?>" max="<?php echo $maxdate?>" />
                    </label>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strLiters?>
                        <input name="alimentare_litri" id="alimentare_litri" type="text" />
                    </label>
                </div>

                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strKilometers?>
                        <input name="alimentare_km" id="alimentare_km" type="text" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strDocument?>
                        <input name="alimentare_bf" type="text" value="" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"><input type="submit"
                        value="<?php echo $strAdd?>" name="Submit" class="button"></div>
            </div>
        </form>
        <?php
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
// Validate cID parameter
if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
    die('<div class="callout alert">Invalid record ID</div>');
}

$cID = intval($_GET['cID']);

// Use prepared statement
$stmt = $conn->prepare("SELECT * FROM administrative_alimentari WHERE alimentare_ID=?");
$stmt->bind_param("i", $cID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    die('<div class="callout alert">Record not found</div>');
}

// Authorization check
if ($row['alimentare_aloc'] !== $code) {
    die('<div class="callout alert">Unauthorized access</div>');
}
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="gasfilling.php" class="button"><?php echo $strBack?>&nbsp;<i class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post"  action="gasfilling.php?mode=edit&cID=<?php echo htmlspecialchars($cID, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="grid-x grid-margin-x">
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strCarPlate?>
                        <input name="alimentare_auto" type="text" value="<?php echo htmlspecialchars($row["alimentare_auto"], ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strPaidWithCard?>
                        <input name="alimentare_platit" type="radio" value="0" <?php If ($row["alimentare_platit"]==0) echo "checked"?> />&nbsp;<?php echo $strYes?>
                        <input name="alimentare_platit" type="radio" value="1" <?php If ($row["alimentare_platit"]==1) echo "checked"?>>&nbsp;<?php echo $strNo?>
                    </label>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strSum?>
                        <input name="alimentare_valoare" type="text" value="<?php echo htmlspecialchars($row["alimentare_valoare"], ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strDate?>
                        <input name="alimentare_data" type="date" value="<?php echo htmlspecialchars($row["alimentare_data"], ENT_QUOTES, 'UTF-8'); ?>" min="<?php echo htmlspecialchars($mindate, ENT_QUOTES, 'UTF-8'); ?>" max="<?php echo htmlspecialchars($maxdate, ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strKilometers?>
                        <input name="alimentare_km" type="text" value="<?php echo htmlspecialchars($row["alimentare_km"], ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strLiters?>
                        <input name="alimentare_litri" type="text" value="<?php echo htmlspecialchars($row["alimentare_litri"], ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strDocument?>
                        <input name="alimentare_bf" type="text" value="<?php echo htmlspecialchars($row["alimentare_bf"], ENT_QUOTES, 'UTF-8'); ?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"><input type="submit"
                        value="<?php echo $strModify?>" name="Submit" class="button"></div>
            </div>
        </form>
        <?php
}
else 
{
echo "<a href=\"gasfilling.php?mode=new\" class=\"button\">$strAddNew <i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a><br />";

// Use prepared statement
$stmt = $conn->prepare("SELECT * FROM administrative_alimentari WHERE alimentare_aloc=? ORDER BY alimentare_data DESC");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();
$numar = $result->num_rows;
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate();

if ($numar==0)
{
$stmt->close();
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
?>
        <h3><?php echo $strGasFillings?></h3>

        <div class="paginate">
            <?php
echo $strTotal . " " .$numar." ".$strGasFillings ;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"gasfilling.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
echo " <br /><br />";
?>
        </div>

        <table width="100%">
            <thead>
                <tr>
                    <th><?php echo $strExpense?></th>
                    <th><?php echo $strValue?></th>
                    <th><?php echo $strDate?></th>
                    <th><?php echo $strKilometers?></th>
                    <th><?php echo $strEdit?></th>
                    <th><?php echo $strDelete?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=$result->fetch_assoc()){
    		echo"<tr>
			<td>" . htmlspecialchars($strGasFilling, ENT_QUOTES, 'UTF-8') . "</td>
<td>" . htmlspecialchars(romanize($row['alimentare_valoare']), ENT_QUOTES, 'UTF-8') . "</td>";
echo "<td>" . htmlspecialchars(date('d.m.Y', strtotime($row['alimentare_data'])), ENT_QUOTES, 'UTF-8') . "</td>";
echo "<td>" . htmlspecialchars($row['alimentare_km'], ENT_QUOTES, 'UTF-8') . "</td>";

		echo	  "<td><a href=\"gasfilling.php?mode=edit&cID=" . htmlspecialchars($row['alimentare_ID'], ENT_QUOTES, 'UTF-8') . "\" ><i class=\"far fa-edit fa-xl\" title=\"" . htmlspecialchars($strEdit, ENT_QUOTES, 'UTF-8') . "\"></i></a></td>
			<td><a href=\"gasfilling.php?mode=delete&cID=" . htmlspecialchars($row['alimentare_ID'], ENT_QUOTES, 'UTF-8') . "\"  OnClick=\"return confirm('" . htmlspecialchars($strConfirmDelete, ENT_QUOTES, 'UTF-8') . "');\"><i class=\"fa fa-eraser fa-xl\" title=\"" . htmlspecialchars($strDelete, ENT_QUOTES, 'UTF-8') . "\"></i></a></td>
        </tr>";
}
$stmt->close();
echo "</tbody><tfoot><tr><td></td><td  colspan=\"4\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
}
?>
    </div>
</div>
<hr />
<?php
include '../bottom.php';
?>