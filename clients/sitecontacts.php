<?php
include '../settings.php';
include '../classes/common.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes")
{
	header("location:$strSiteURL/login/login.php?message=MLF");
	die;
}

// Validare parametri GET
if (isset($_GET['mode']) && !in_array($_GET['mode'], ['new', 'edit', 'delete'])) {
    header("location:$strSiteURL/clients/sitecontacts.php");
    die;
}

if (isset($_GET['cID'])) {
    if (!is_numeric($_GET['cID'])) {
        header("location:$strSiteURL/clients/sitecontacts.php");
        die;
    }
    $cID = (int)$_GET['cID'];
}

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

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, "DELETE FROM clienti_contacte WHERE contact_ID=?");
mysqli_stmt_bind_param($stmt, 'i', $cID);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecontacts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){

// Validare și sanitizare input
$client_id = isset($_POST["client_ID"]) && is_numeric($_POST["client_ID"]) ? (int)$_POST["client_ID"] : 0;
$contact_nume = trim($_POST["contact_nume"] ?? '');
$contact_prenume = trim($_POST["contact_prenume"] ?? '');
$contact_telefon = trim($_POST["contact_telefon"] ?? '');
$contact_email = trim($_POST["contact_email"] ?? '');
$contact_tip = trim($_POST["contact_tip"] ?? '');

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, 
    "INSERT INTO clienti_contacte(client_ID, contact_nume, contact_prenume, contact_telefon, contact_email, contact_tip) 
    VALUES (?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param($stmt, 'isssss', $client_id, $contact_nume, $contact_prenume, $contact_telefon, $contact_email, $contact_tip);

if (!mysqli_stmt_execute($stmt)) {
    die('Error: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt);

echo "<div class=\"callout success\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecontacts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
else
{// edit
// Validare și sanitizare input
$client_id = isset($_POST["client_ID"]) && is_numeric($_POST["client_ID"]) ? (int)$_POST["client_ID"] : 0;
$contact_nume = trim($_POST["contact_nume"] ?? '');
$contact_prenume = trim($_POST["contact_prenume"] ?? '');
$contact_telefon = trim($_POST["contact_telefon"] ?? '');
$contact_email = trim($_POST["contact_email"] ?? '');
$contact_tip = trim($_POST["contact_tip"] ?? '');

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, 
    "UPDATE clienti_contacte SET client_ID=?, contact_nume=?, contact_prenume=?, contact_telefon=?, contact_email=?, contact_tip=? 
    WHERE contact_ID=?"
);

mysqli_stmt_bind_param($stmt, 'isssssi', $client_id, $contact_nume, $contact_prenume, $contact_telefon, $contact_email, $contact_tip, $cID);

if (!mysqli_stmt_execute($stmt)) {
    die('Error: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt);

echo "<div class=\"callout success\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecontacts.php\"
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
                <p><a href="sitecontacts.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post" id="users" Action="sitecontacts.php?mode=new">
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strClient?>
                        <select name="client_ID" class="required">
                            <option value=""><?php echo $strClient?></option>
                            <?php 
		  $stmt = mysqli_prepare($conn, 
			  "SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, Contract_Alocat, Client_Denumire 
			  FROM clienti_date, clienti_contracte 
				WHERE Contract_Alocat=? AND  clienti_date.ID_Client=clienti_contracte.ID_Client
				ORDER BY Client_Denumire ASC"
		  );
		  mysqli_stmt_bind_param($stmt, 's', $code);
		  mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
	    while ($rss=mysqli_fetch_array($result, MYSQLI_ASSOC)){
	?>
                            <option value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
                            <?php
}?>
                        </select></label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strName?>
                        <input name="contact_nume" type="text" class="required" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strFirstName?>
                        <input name="contact_prenume" type="text" class="required" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strPhone?>
                        <input name="contact_telefon" type="text" class="required" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strEmail?>
                        <input name="contact_email" type="text" class="required" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strFunction?>
                        <input name="contact_tip" type="text" class="required" />
                    </label>
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
// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, 
    "SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_Aloc, 
    clienti_contacte.client_ID, clienti_contacte.contact_ID, clienti_contacte.contact_nume, clienti_contacte.contact_prenume, 
    clienti_contacte.contact_telefon, clienti_contacte.contact_email, clienti_contacte.contact_tip
    FROM clienti_contacte, clienti_date
    WHERE contact_ID=? AND clienti_date.Client_Aloc=? AND clienti_date.ID_Client=clienti_contacte.client_ID
    ORDER By Client_Denumire ASC"
);
mysqli_stmt_bind_param($stmt, 'is', $cID, $code);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="sitecontacts.php" class="button"><?php echo $strBack?></a></p>
            </div>
        </div>
        <form method="post" id="users" Action="sitecontacts.php?mode=edit&cID=<?php echo $row['contact_ID']?>">
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
                        <option <?php if ($row["ID_Client"]==$rss["ID_Client"]) echo "selected"; ?>
                            value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
                        <?php
}?>
                    </select>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strName?>
                        <input name="contact_nume" type="text" class="required"
                            value="<?php echo $row["contact_nume"]?>" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strFirstName?>
                        <input name="contact_prenume" type="text" class="required"
                            value="<?php echo $row["contact_prenume"]?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strPhone?>
                        <input name="contact_telefon" type="text" class="required"
                            value="<?php echo $row["contact_telefon"]?>" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strEmail?>
                        <input name="contact_email" type="text" class="required"
                            value="<?php echo $row["contact_email"]?>" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strFunction?>
                        <input name="contact_tip" type="text" class="required"
                            value="<?php echo $row["contact_tip"]?>" />
                    </label>
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
<a href=\"sitecontacts.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, 
    "SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_contracte.Contract_Alocat, clienti_contracte.ID_Client, 
    clienti_contacte.contact_ID, clienti_contacte.client_ID, clienti_contacte.contact_nume, clienti_contacte.contact_prenume, 
    clienti_contacte.contact_telefon, clienti_contacte.contact_email, clienti_contacte.contact_tip
    FROM clienti_contacte, clienti_date, clienti_contracte
    WHERE clienti_date.ID_Client=clienti_contracte.ID_Client 
    AND clienti_date.ID_Client=clienti_contacte.client_ID 
    AND clienti_contracte.Contract_Alocat=?
    ORDER By Client_Denumire ASC"
);
mysqli_stmt_bind_param($stmt, 's', $code);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$numar = mysqli_num_rows($result);
mysqli_stmt_close($stmt);

if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
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
While ($row=mysqli_fetch_array($result, MYSQLI_ASSOC)){
    		echo"<tr>
			<td>" . htmlspecialchars($row['Client_Denumire'], ENT_QUOTES, 'UTF-8') . "</td>
			<td>" . htmlspecialchars($row['contact_prenume'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($row['contact_nume'], ENT_QUOTES, 'UTF-8') . "</td>
			<td>" . htmlspecialchars($row['contact_tip'], ENT_QUOTES, 'UTF-8') . "</td>
			<td>" . htmlspecialchars($row['contact_telefon'], ENT_QUOTES, 'UTF-8') . "</td>
			<td>" . htmlspecialchars($row['contact_email'], ENT_QUOTES, 'UTF-8') . "</td>
			  <td><a href=\"sitecontacts.php?mode=edit&cID=" . (int)$row['contact_ID'] . "\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"sitecontacts.php?mode=delete&cID=" . (int)$row['contact_ID'] . "\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
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