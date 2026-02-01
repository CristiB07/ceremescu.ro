<?php
//update 29.07.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare autorizații";
include '../dashboard/header.php';
?>
<link rel="stylesheet" href="../js/simple-editor/simple-editor.css">
<script src="../js/simple-editor/simple-editor.js"></script>
<?php
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/index.php?message=MLF");
	exit();
}

// Check if user is admin
if (!isset($_SESSION['clearence']) || $_SESSION['clearence'] != 'ADMIN') {
	header("location:$strSiteURL/index.php?message=unauthorized");
	exit();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

// CSRF validation
if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
	die('<div class="callout alert">Invalid CSRF token</div>');
}

$cID = intval($_GET['cID']);
if ($cID <= 0) {
	die('<div class="callout alert">Invalid ID</div>');
}

$stmt = $conn->prepare("DELETE FROM clienti_autorizatii WHERE ID_autorizatii = ?");
$stmt->bind_param("i", $cID);
$stmt->execute();
$stmt->close();

echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteauthorizations.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

// CSRF validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
	die('<div class="callout alert">Invalid CSRF token</div>');
}

If ($_GET['mode']=="new"){
//insert new authorization

	$autorizatie = htmlspecialchars(trim($_POST["Autorizatie"]), ENT_QUOTES, 'UTF-8');
	$descriere = htmlspecialchars(trim($_POST["Descriere"]), ENT_QUOTES, 'UTF-8');
	
	if (empty($autorizatie)) {
		die('<div class="callout alert">Title is required</div>');
	}
	
	$stmt = $conn->prepare("INSERT INTO clienti_autorizatii (Autorizatie, Descriere) VALUES (?, ?)");
	$stmt->bind_param("ss", $autorizatie, $descriere);
	
	if (!$stmt->execute())
  {
  die('Error: ' . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8'));
  }
	$stmt->close();
	
	echo "<div class=\"callout success\">$strRecordAdded</div></div></div>" ;
	echo "<script type=\"text/javascript\">
	<!--
	function delayer(){
	    window.location = \"siteauthorizations.php\"
	}
	//-->
	</script>
	<body onLoad=\"setTimeout('delayer()', 1500)\">";
	include '../bottom.php';
	die;
}
else
{// edit
$cID = intval($_GET["cID"]);
if ($cID <= 0) {
	die('<div class="callout alert">Invalid ID</div>');
}

$autorizatie = htmlspecialchars(trim($_POST["Autorizatie"]), ENT_QUOTES, 'UTF-8');
$descriere = htmlspecialchars(trim($_POST["Descriere"]), ENT_QUOTES, 'UTF-8');

if (empty($autorizatie)) {
	die('<div class="callout alert">Title is required</div>');
}

$stmt = $conn->prepare("UPDATE clienti_autorizatii SET Autorizatie = ?, Descriere = ? WHERE ID_autorizatii = ?");
$stmt->bind_param("ssi", $autorizatie, $descriere, $cID);

if (!$stmt->execute())
  {
  die('Error: ' . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8'));
  }
$stmt->close();

echo "<div class=\"callout success\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteauthorizations.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
}

else {
?>
        <?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="siteauthorizations.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post"  action="siteauthorizations.php?mode=new">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strTitle?>
                        <input name="Autorizatie" type="text" size="50" class="required" />
                    </label>
                    <div>
                        <div>
                            <div class="grid-x grid-margin-x">
                                <div class="large-12 medium-12 small-12 cell">
                                    <label><?php echo $strDetails?></label>
                                        <textarea name="Descriere" class="simple-html-editor" rows="5"></textarea>
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
$cID = intval($_GET['cID']);
if ($cID <= 0) {
	die('<div class="callout alert">Invalid ID</div>');
}

$stmt = $conn->prepare("SELECT * FROM clienti_autorizatii WHERE ID_autorizatii = ?");
$stmt->bind_param("i", $cID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
	die('<div class="callout alert">Record not found</div>');
}
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="siteauthorizations.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post" action="siteauthorizations.php?mode=edit&cID=<?php echo intval($row['ID_autorizatii']); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strTitle?>
                        <input name="Autorizatie" type="text" size="50" value="<?php echo htmlspecialchars($row['Autorizatie'], ENT_QUOTES, 'UTF-8'); ?>" class="required" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strDetails?></label>
                        <textarea name="Descriere" class="simple-html-editor" rows="5"><?php echo htmlspecialchars($row['Descriere'], ENT_QUOTES, 'UTF-8'); ?></textarea>
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
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
	 <a href=\"siteauthorizations.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";
$query="SELECT * FROM clienti_autorizatii";
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
                            <th width="5%"><?php echo $strID?></th>
                            <th width="85%"><?php echo $strTitle?></th>
                            <th width="5%"><?php echo $strEdit?></th>
                            <th width="5%"><?php echo $strDelete?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
While ($row=ezpub_fetch_array($result)){
	$safe_id = intval($row['ID_autorizatii']);
	$safe_title = htmlspecialchars($row['Autorizatie'], ENT_QUOTES, 'UTF-8');
	$csrf = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
    		echo"<tr>
			<td>$safe_id</td>
			<td>$safe_title</td>
			  <td><a href=\"siteauthorizations.php?mode=edit&cID=$safe_id\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"siteauthorizations.php?mode=delete&cID=$safe_id&csrf_token=$csrf\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"2\"><em></em></td><td>&nbsp;</td></tr></tfoot></table></div></div>";
}
}
}
?>
            </div>
        </div>
    </div>
</div>
<?php
include '../bottom.php';
?>