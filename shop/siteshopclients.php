<?php
//update 8.01.2025

include '../settings.php';
include '../classes/paginator.class.php';
include '../classes/common.php';
$strDescription="Administreaza cumpărătorii";
$strPageTitle="Administreaza cumpărătorii";
$url="siteshopclients.php";
?>

<?php
include '../dashboard/header.php';
echo "      <div class=\"grid-x grid-padding-x\">
        <div class=\"large-12 medium-12 small-12 cell\">
<h1>$strPageTitle</h1>";

If (IsSet($_GET['mode']) AND $_GET['mode']=="view"){
if (!isset($_GET['bID']) || !is_numeric($_GET['bID'])) {
    header("Location: siteshopclients.php");
    exit();
}
$bID = (int)$_GET['bID'];

$stmt = mysqli_prepare($conn, "SELECT * FROM magazin_cumparatori WHERE cumparator_id=?");
mysqli_stmt_bind_param($stmt, 'i', $bID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    echo "<div class=\"callout alert\">" . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . "</div></div></div>";
    echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteshopclients.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
    include '../bottom.php';
    exit();
}

$stmt2 = mysqli_prepare($conn, "SELECT * FROM magazin_firme WHERE firma_cumparatorID=?");
mysqli_stmt_bind_param($stmt2, 'i', $bID);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
$nume = mysqli_num_rows($result2);

echo "<h3>" . htmlspecialchars($strUser, ENT_QUOTES, 'UTF-8') . "</h3><table>";
echo "<tr><td width=\"30%\">" . htmlspecialchars($strName, ENT_QUOTES, 'UTF-8') . "</td><td width=\"70%\">" . htmlspecialchars(($row['cumparator_prenume'] ?? '') . ' ' . ($row['cumparator_nume'] ?? ''), ENT_QUOTES, 'UTF-8') . "</td></tr>
	<tr><td>" . htmlspecialchars($strEmail, ENT_QUOTES, 'UTF-8') . "</td><td>" . htmlspecialchars($row['cumparator_email'] ?? '', ENT_QUOTES, 'UTF-8') . "</td></tr>
	<tr><td>" . htmlspecialchars($strPhone, ENT_QUOTES, 'UTF-8') . "</td><td>" . htmlspecialchars($row['cumparator_telefon'] ?? '', ENT_QUOTES, 'UTF-8') . "</td></tr>
	<tr><td>" . htmlspecialchars($strAddress, ENT_QUOTES, 'UTF-8') . "</td><td>" . htmlspecialchars($row['cumparator_adresa'] ?? '', ENT_QUOTES, 'UTF-8') . "</td></tr>
	<tr><td>" . htmlspecialchars($strCity, ENT_QUOTES, 'UTF-8') . "</td><td>" . htmlspecialchars($row['cumparator_oras'] ?? '', ENT_QUOTES, 'UTF-8') . "</td></tr>
	<tr><td>" . htmlspecialchars($strCounty, ENT_QUOTES, 'UTF-8') . "</td><td>" . htmlspecialchars($row['cumparator_judet'] ?? '', ENT_QUOTES, 'UTF-8') . "</td></tr>
	
</table>
<h3>" . htmlspecialchars($strCompany, ENT_QUOTES, 'UTF-8') . "</h3>";
if ($nume==0)
{
echo htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8');
}
else {		
while ($row2 = mysqli_fetch_assoc($result2)){
echo "<table>";
echo "<tr><td width=\"30%\">" . htmlspecialchars($strCompanyName, ENT_QUOTES, 'UTF-8') . "</td><td width=\"70%\">" . htmlspecialchars($row2['firma_nume'] ?? '', ENT_QUOTES, 'UTF-8') . "</td></tr>
	<tr><td>" . htmlspecialchars($strCompanyVAT, ENT_QUOTES, 'UTF-8') . "</td><td>" . htmlspecialchars(($row2['firma_RO'] ?? '') . ' ' . ($row2['firma_CIF'] ?? ''), ENT_QUOTES, 'UTF-8') . "</td></tr>
	<tr><td>" . htmlspecialchars($strCompanyRC, ENT_QUOTES, 'UTF-8') . "</td><td>" . htmlspecialchars($row2['firma_reg'] ?? '', ENT_QUOTES, 'UTF-8') . "</td></tr>
	<tr><td>" . htmlspecialchars($strCompanyAddress, ENT_QUOTES, 'UTF-8') . "</td><td>" . htmlspecialchars($row2['firma_adresa'] ?? '', ENT_QUOTES, 'UTF-8') . "</td></tr>
	<tr><td>" . htmlspecialchars($strCompanyBank, ENT_QUOTES, 'UTF-8') . "</td><td>" . htmlspecialchars($row2['firma_banca'] ?? '', ENT_QUOTES, 'UTF-8') . "</td></tr>
	<tr><td>" . htmlspecialchars($strCompanyIBAN, ENT_QUOTES, 'UTF-8') . "</td><td>" . htmlspecialchars($row2['firma_IBAN'] ?? '', ENT_QUOTES, 'UTF-8') . "</td></tr>
</table><br /><br />";		
}
mysqli_stmt_close($stmt2);
}
	echo "<br /><br /><a href=\"siteshopclients.php\" class=\"button right\">Înapoi</a></div></div>";
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

if (!isset($_GET['bID']) || !is_numeric($_GET['bID'])) {
    header("Location: siteshopclients.php");
    exit();
}
$bID = (int)$_GET['bID'];

$stmt = mysqli_prepare($conn, "DELETE FROM magazin_cumparatori WHERE cumparator_id=?");
mysqli_stmt_bind_param($stmt, 'i', $bID);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo "<div class=\"callout success\">" . htmlspecialchars($strRecordDeleted, ENT_QUOTES, 'UTF-8') . "</div></div></div><hr/>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteshopclients.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
exit();}

else
{
$stmt_count = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM magazin_cumparatori");
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$row_count = mysqli_fetch_assoc($result_count);
$nume = $row_count['total'];
mysqli_stmt_close($stmt_count);

$pages = new Pagination;  
$pages->items_total = $nume;  
$pages->mid_range = 5;  
$pages->paginate();

$query = "SELECT * FROM magazin_cumparatori ORDER BY cumparator_nume ASC " . $pages->limit;
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_execute($stmt);
$result2 = mysqli_stmt_get_result($stmt);

if ($nume==0)
{
echo htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8');
}
else {
?>
<div class="paginate">
    <?php
echo $strTotal . " " .$nume." ".$strClients ;
echo " <br /><br />";
echo $pages->display_pages();
?>
</div>
<table id="rounded-corner" summary="<?php echo $strOrders?>">
    <thead>
        <tr>
            <th><?php echo $strID?></th>
            <th><?php echo $strFirstName?></th>
            <th><?php echo $strLastName?></th>
            <th><?php echo $strEmail?></th>
            <th><?php echo $strPhone?></th>
            <th><?php echo $strDetails?></th>
            <th><?php echo $strDelete?></th>
        </tr>
    </thead>
    <tbody>
        <?php 
while ($row = mysqli_fetch_assoc($result2)){
    $cumparator_id = htmlspecialchars($row['cumparator_id'] ?? '', ENT_QUOTES, 'UTF-8');
    $cumparator_prenume = htmlspecialchars($row['cumparator_prenume'] ?? '', ENT_QUOTES, 'UTF-8');
    $cumparator_nume = htmlspecialchars($row['cumparator_nume'] ?? '', ENT_QUOTES, 'UTF-8');
    $cumparator_email = htmlspecialchars($row['cumparator_email'] ?? '', ENT_QUOTES, 'UTF-8');
    $cumparator_telefon = htmlspecialchars($row['cumparator_telefon'] ?? '', ENT_QUOTES, 'UTF-8');
    $confirm_msg = htmlspecialchars($strConfirmDelete, ENT_QUOTES, 'UTF-8');
    $delete_title = htmlspecialchars($strDelete, ENT_QUOTES, 'UTF-8');
    
    echo "<tr>
			<td>$cumparator_id</td>
			<td>$cumparator_prenume</td>
			<td>$cumparator_nume</td>
			<td>$cumparator_email</td>
			<td>$cumparator_telefon</td>
			<td><a href=\"siteshopclients.php?mode=view&bID=$cumparator_id\"><i class=\"fas fa-info\"></i></a></td>
			<td><a href=\"siteshopclients.php?mode=delete&bID=$cumparator_id\" OnClick=\"return confirm('$confirm_msg');\"><i class=\"fa fa-eraser fa-xl\" title=\"$delete_title\"></i></a></td>
        </tr>";
}
mysqli_stmt_close($stmt);
echo "</tbody></table>";
?>
        <div class="paginate">
            <?php
echo $pages->display_pages();
?>
        </div>

        <?php 
}
}
?>

        </div>
        </div>
        <hr />
        <?php
include '../bottom.php';
?>