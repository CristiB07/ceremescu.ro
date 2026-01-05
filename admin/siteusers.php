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
	exit();
}

// Check if user is admin
if (!isset($_SESSION['clearence']) || $_SESSION['clearence'] != 'ADMIN') {
	header("location:$strSiteURL/index.php?message=unauthorized");
	exit();
}

// Generate CSRF token if not exists
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

// Delete user with prepared statement
$stmt = $conn->prepare("DELETE FROM date_utilizatori WHERE utilizator_ID = ?");
$stmt->bind_param("i", $cID);
$stmt->execute();
$stmt->close();

// Also delete encryption key
$stmt_key = $conn->prepare("DELETE FROM date_utilizatori_chei WHERE cheie_primara = (SELECT hash('sha256', utilizator_Email) FROM date_utilizatori WHERE utilizator_ID = ?)");
$stmt_key->bind_param("i", $cID);
$stmt_key->execute();
$stmt_key->close();
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteusers.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();

// CSRF validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
	die('<div class="callout alert">Invalid CSRF token</div>');
}

If ($_GET['mode']=="new"){
//insert new user with prepared statement

	$stmt = $conn->prepare("INSERT INTO date_utilizatori (
		utilizator_Nume, utilizator_Prenume, utilizator_Email, utilizator_Parola, utilizator_Role, 
		utilizator_Phone, utilizator_Carplate, utilizator_Function, utilizator_CRM, utilizator_Billing, 
		utilizator_Sales, utilizator_Clients, utilizator_CMS, utilizator_Projects, utilizator_Shop, 
		utilizator_Administrative, utilizator_Lab, utilizator_Elearning, utilizator_Team, 
		utilizator_Upgraded, utilizator_Transfer, utilizator_Helpdesk, utilizator_Code
	) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 3, ?, ?, ?)");
	
	$stmt->bind_param("ssssssssiiiiiiiiiiisss",
		$_POST["utilizator_Nume"],
		$_POST["utilizator_Prenume"],
		$_POST["utilizator_Email"],
		$_POST["utilizator_Parola"],
		$_POST["utilizator_Role"],
		$_POST["utilizator_Phone"],
		$_POST["utilizator_Carplate"],
		$_POST["utilizator_Function"],
		$_POST["utilizator_CRM"],
		$_POST["utilizator_Billing"],
		$_POST["utilizator_Sales"],
		$_POST["utilizator_Clients"],
		$_POST["utilizator_CMS"],
		$_POST["utilizator_Projects"],
		$_POST["utilizator_Shop"],
		$_POST["utilizator_Administrative"],
		$_POST["utilizator_Lab"],
		$_POST["utilizator_Elearning"],
		$_POST["utilizator_Team"],
		$_POST["utilizator_Transfer"],
		$_POST["utilizator_Helpdesk"],
		$_POST["utilizator_Code"]
	);
	
	if (!$stmt->execute())
	{
		die('Error: ' . $stmt->error);
	}
	
	$stmt->close();
    // Create encryption key for new user
    $cheieprimara = hash('sha256', $_POST["utilizator_Email"]);
    $cheiesecundara = bin2hex(random_bytes(32));
    
    // Use prepared statement for security
    $stmt_key = $conn->prepare("INSERT INTO date_utilizatori_chei (cheie_primara, cheie_secundara) VALUES (?, ?)");
    $stmt_key->bind_param("ss", $cheieprimara, $cheiesecundara);
    $stmt_key->execute();
    $stmt_key->close();

echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteusers.php\"
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

// Get old email to check if it changed
$stmt_old = $conn->prepare("SELECT utilizator_Email FROM date_utilizatori WHERE utilizator_ID = ?");
$stmt_old->bind_param("i", $cID);
$stmt_old->execute();
$result_old = $stmt_old->get_result();
$row_old = $result_old->fetch_assoc();
$old_email = $row_old['utilizator_Email'];
$stmt_old->close();

// Update user with prepared statement
$stmt = $conn->prepare("UPDATE date_utilizatori SET 
	utilizator_Nume = ?, utilizator_Prenume = ?, utilizator_Email = ?, utilizator_Parola = ?, 
	utilizator_Role = ?, utilizator_Phone = ?, utilizator_Carplate = ?, utilizator_Function = ?, 
	utilizator_CRM = ?, utilizator_Billing = ?, utilizator_Sales = ?, utilizator_Clients = ?, 
	utilizator_CMS = ?, utilizator_Projects = ?, utilizator_Shop = ?, utilizator_Administrative = ?, 
	utilizator_Lab = ?, utilizator_Elearning = ?, utilizator_Team = ?, utilizator_Code = ?, utilizator_Transfer = ?,
     utilizator_Helpdesk = ? 
	WHERE utilizator_ID = ?");

$stmt->bind_param("ssssssssiiiiiiiiiissiii",
	$_POST["utilizator_Nume"],
	$_POST["utilizator_Prenume"],
	$_POST["utilizator_Email"],
	$_POST["utilizator_Parola"],
	$_POST["utilizator_Role"],
	$_POST["utilizator_Phone"],
	$_POST["utilizator_Carplate"],
	$_POST["utilizator_Function"],
	$_POST["utilizator_CRM"],
	$_POST["utilizator_Billing"],
	$_POST["utilizator_Sales"],
	$_POST["utilizator_Clients"],
	$_POST["utilizator_CMS"],
	$_POST["utilizator_Projects"],
	$_POST["utilizator_Shop"],
	$_POST["utilizator_Administrative"],
	$_POST["utilizator_Lab"],
	$_POST["utilizator_Elearning"],
	$_POST["utilizator_Team"],
	$_POST["utilizator_Code"],
	$_POST["utilizator_Transfer"],
	$_POST["utilizator_Helpdesk"],
	$cID
);

if (!$stmt->execute())
{
	die('Error: ' . $stmt->error);
}
$stmt->close();

// If email changed, update encryption key
if ($old_email !== $_POST["utilizator_Email"]) {
	$old_hash = hash('sha256', $old_email);
	$new_hash = hash('sha256', $_POST["utilizator_Email"]);
	
	$stmt_update_key = $conn->prepare("UPDATE date_utilizatori_chei SET cheie_primara = ? WHERE cheie_primara = ?");
	$stmt_update_key->bind_param("ss", $new_hash, $old_hash);
	$stmt_update_key->execute();
	$stmt_update_key->close();
}

echo "<div class=\"callout success\">$strRecordModified</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteusers.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}}

else {
?>
        <?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="siteusers.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post" id="users" Action="siteusers.php?mode=new">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strName?>
                        <input name="utilizator_Nume" type="text" class="required" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strFirstName?>
                        <input name="utilizator_Prenume" type="text" class="required" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strEmail?>
                        <input name="utilizator_Email" type="text" class="email required" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strPassword?>
                        <input name="utilizator_Parola" type="text" class="required" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strRole?>
                        <input name="utilizator_Role" type="text" class="required" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strTeam?>
                        <input name="utilizator_Team" type="text" class="required" />
                    </label>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strCode?></label>
                    <input name="utilizator_Code" type="text" class="required" />
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strPhone?>
                        <input name="utilizator_Phone" type="text" class="required" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strCarPlate?>
                        <input name="utilizator_Carplate" type="text" class="required" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strFunction?>
                        <input name="utilizator_Function" type="text" class="required" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppCRM?> </label>
                    <input name="utilizator_CRM" type="radio" value="1" /> <?php echo $strYes?>&nbsp;&nbsp;<input
                        name="utilizator_CRM" type="radio" value="0" checked><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppCMS?> </label>
                    <input name="utilizator_CMS" type="radio" value="1" /> <?php echo $strYes?>&nbsp;&nbsp;<input
                        name="utilizator_CMS" type="radio" value="0" checked><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppAdministrative?> </label>
                    <input name="utilizator_Administrative" type="radio" value="1" />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Administrative" type="radio" value="0"
                        checked><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppBilling?> </label>
                    <input name="utilizator_Billing" type="radio" value="1" />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Billing" type="radio" value="0"
                        checked><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppSales?> </label>
                    <input name="utilizator_Sales" type="radio" value="1" /> <?php echo $strYes?>&nbsp;&nbsp;<input
                        name="utilizator_Sales" type="radio" value="0" checked><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppClients?></label>
                    <input name="utilizator_Clients" type="radio" value="1" /> <?php echo $strYes?>&nbsp;&nbsp;<input
                        name="utilizator_Clients" type="radio" value="0" checked><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppProjects?></label>
                    <input name="utilizator_Projects" type="radio" value="1" />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Projects" type="radio" value="0"
                        checked><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppShop?> </label>
                    <input name="utilizator_Shop" type="radio" value="1" /> <?php echo $strYes?>&nbsp;&nbsp;<input
                        name="utilizator_Shop" type="radio" value="0" checked><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppLab?></label>
                    <input name="utilizator_Lab" type="radio" value="1" /> <?php echo $strYes?>&nbsp;&nbsp;<input
                        name="utilizator_Lab" type="radio" value="0" checked><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppElearning?> </label>
                    <input name="utilizator_Elearning" type="radio" value="1" />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Elearning" type="radio" value="0"
                        checked><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppTransfer?> </label>
                    <input name="utilizator_Transfer" type="radio" value="1" />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Transfer" type="radio" value="0"
                        checked><?php echo $strNo?>
</div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppHelpDesk?> </label>
                    <input name="utilizator_Helpdesk" type="radio" value="1" />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Helpdesk" type="radio" value="0"
                        checked><?php echo $strNo?>
                </div>
            </div>

            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"> <input type="submit"
                        Value="<?php echo $strAdd?>" name="Submit" class="button success" />
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

$stmt = $conn->prepare("SELECT * FROM date_utilizatori WHERE utilizator_ID = ?");
$stmt->bind_param("i", $cID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
	die('<div class="callout alert">User not found</div>');
}
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="siteusers.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post" id="users" Action="siteusers.php?mode=edit&cID=<?php echo $row['utilizator_ID']?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strName?>
                        <input name="utilizator_Nume" type="text" value="<?php echo $row["utilizator_Nume"]?>"
                            class="required" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strFirstName?>
                        <input name="utilizator_Prenume" type="text" value="<?php echo $row["utilizator_Prenume"]?>"
                            class="required" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strEmail?>
                        <input name="utilizator_Email" type="text" value="<?php echo $row["utilizator_Email"]?>"
                            class="email required" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strPassword?>
                        <input name="utilizator_Parola" type="text" value="<?php echo $row["utilizator_Parola"]?>"
                            class="required" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strRole?>
                        <input name="utilizator_Role" type="text" value="<?php echo $row["utilizator_Role"]?>"
                            class="required" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strTeam?>
                        <input name="utilizator_Team" type="text" value="<?php echo $row["utilizator_Team"]?>"
                            class="required" />
                    </label>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strCode?>
                        <input name="utilizator_Code" type="text" value="<?php echo $row["utilizator_Code"]?>"
                            class="required" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strPhone?>
                        <input name="utilizator_Phone" type="text" value="<?php echo $row["utilizator_Phone"]?>"
                            class="required" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strCarPlate?></label>
                    <input name="utilizator_Carplate" type="text" value="<?php echo $row["utilizator_Carplate"]?>"
                        class="required" />
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strFunction?>
                        <input name="utilizator_Function" type="text" value="<?php echo $row["utilizator_Function"]?>"
                            class="required" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppCRM?> </label>
                    <input name="utilizator_CRM" type="radio" value="1"
                        <?php If ($row["utilizator_CRM"]==1) echo "checked"?> />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_CRM" type="radio" value="0"
                        <?php If ($row["utilizator_CRM"]==0) echo "checked"?>><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppCMS?> </label>
                    <input name="utilizator_CMS" type="radio" value="1"
                        <?php If ($row["utilizator_CMS"]==1) echo "checked"?> />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_CMS" type="radio" value="0"
                        <?php If ($row["utilizator_CMS"]==0) echo "checked"?>><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppAdministrative?> </label>
                    <input name="utilizator_Administrative" type="radio" value="1"
                        <?php If ($row["utilizator_Administrative"]==1) echo "checked"?> />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Administrative" type="radio" value="0"
                        <?php If ($row["utilizator_Administrative"]==0) echo "checked"?>><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppBilling?> </label>
                    <input name="utilizator_Billing" type="radio" value="1"
                        <?php If ($row["utilizator_Billing"]==1) echo "checked"?> />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Billing" type="radio" value="0"
                        <?php If ($row["utilizator_Billing"]==0) echo "checked"?>><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppSales?> </label>
                    <input name="utilizator_Sales" type="radio" value="1"
                        <?php If ($row["utilizator_Sales"]==1) echo "checked"?> />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Sales" type="radio" value="0"
                        <?php If ($row["utilizator_Sales"]==0) echo "checked"?>><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppClients?> </label>
                    <input name="utilizator_Clients" type="radio" value="1"
                        <?php If ($row["utilizator_Clients"]==1) echo "checked"?> />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Clients" type="radio" value="0"
                        <?php If ($row["utilizator_Clients"]==0) echo "checked"?>><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppProjects?> </label>
                    <input name="utilizator_Projects" type="radio" value="1"
                        <?php If ($row["utilizator_Projects"]==1) echo "checked"?> />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Projects" type="radio" value="0"
                        <?php If ($row["utilizator_Projects"]==0) echo "checked"?>><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppShop?> </label>
                    <input name="utilizator_Shop" type="radio" value="1"
                        <?php If ($row["utilizator_Shop"]==1) echo "checked"?> />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Shop" type="radio" value="0"
                        <?php If ($row["utilizator_Shop"]==0) echo "checked"?>><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppLab?> </label>
                    <input name="utilizator_Lab" type="radio" value="1"
                        <?php If ($row["utilizator_Lab"]==1) echo "checked"?> />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Lab" type="radio" value="0"
                        <?php If ($row["utilizator_Lab"]==0) echo "checked"?>><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppElearning?> </label>
                    <input name="utilizator_Elearning" type="radio" value="1"
                        <?php If ($row["utilizator_Elearning"]==1) echo "checked"?> />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Elearning" type="radio" value="0"
                        <?php If ($row["utilizator_Elearning"]==0) echo "checked"?>><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppTransfer?> </label>
                    <input name="utilizator_Transfer" type="radio" value="1"
                        <?php If ($row["utilizator_Transfer"]==1) echo "checked"?> />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Transfer" type="radio" value="0"
                        <?php If ($row["utilizator_Transfer"]==0) echo "checked"?>><?php echo $strNo?>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strAppHelpDesk?> </label>
                    <input name="utilizator_Helpdesk" type="radio" value="1"
                        <?php If ($row["utilizator_Helpdesk"]==1) echo "checked"?> />
                    <?php echo $strYes?>&nbsp;&nbsp;<input name="utilizator_Helpdesk" type="radio" value="0"
                        <?php If ($row["utilizator_Helpdesk"]==0) echo "checked"?>><?php echo $strNo?>
                </div>
            </div>


            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"> <input type="submit"
                        Value="<?php echo $strModify?>" name="Submit" class="button success" />
                </div>
            </div>
        </form>
        <?php
}
else
{
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"siteusers.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";
$query="SELECT * FROM date_utilizatori";
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
                            <th>ID</th>
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
	$id = intval($row['utilizator_ID']);
	$prenume = htmlspecialchars($row['utilizator_Prenume'] ?? '', ENT_QUOTES, 'UTF-8');
	$nume = htmlspecialchars($row['utilizator_Nume'] ?? '', ENT_QUOTES, 'UTF-8');
	$function = htmlspecialchars($row['utilizator_Function'] ?? '', ENT_QUOTES, 'UTF-8');
	$email = htmlspecialchars($row['utilizator_Email'] ?? '', ENT_QUOTES, 'UTF-8');
	$carplate = htmlspecialchars($row['utilizator_Carplate'] ?? '', ENT_QUOTES, 'UTF-8');
	$csrf = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
	
    		echo"<tr>
			<td>$id</td>
			<td>$prenume</td>
			<td>$nume</td>
			<td>$function</td>
			<td>$email</td>
			<td>$carplate</td>
			<td><a href=\"siteusers.php?mode=edit&cID=$id\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"siteusers.php?mode=delete&cID=$id&csrf_token=$csrf\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
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