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
if (isset($_GET['mode']) && !in_array($_GET['mode'], ['edit'])) {
    header("location:$strSiteURL/clients/siteuserclients.php");
    die;
}

if (isset($_GET['cID'])) {
    if (!is_numeric($_GET['cID'])) {
        header("location:$strSiteURL/clients/siteuserclients.php");
        die;
    }
    $cID = (int)$_GET['cID'];
}

include '../classes/paginator.class.php';
$strPageTitle="Administrare clienți";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();

// Validare input
$client_cod_caen = trim($_POST["Client_Cod_CAEN"] ?? '');
$client_adresa = trim($_POST["Client_Adresa"] ?? '');
$client_telefon = trim($_POST["Client_Telefon"] ?? '');
$client_banca = trim($_POST["Client_Banca"] ?? '');
$client_iban = trim($_POST["Client_IBAN"] ?? '');
$client_localitate = trim($_POST["Client_Localitate"] ?? '');
$client_judet = trim($_POST["Client_Judet"] ?? '');
$client_email = trim($_POST["Client_Email"] ?? '');
$client_numar_angajati = trim($_POST["Client_Numar_Angajati"] ?? '');
$client_descriere = trim($_POST["Client_Descriere_Activitate"] ?? '');
$client_web = trim($_POST["Client_Web"] ?? '');
$client_caracterizare = trim($_POST["Client_Caracterizare"] ?? '');

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, 
    "UPDATE clienti_date SET Client_Cod_CAEN=?, Client_Adresa=?, Client_Telefon=?, Client_Banca=?, 
    Client_IBAN=?, Client_Localitate=?, Client_Judet=?, Client_Email=?, Client_Numar_Angajati=?, 
    Client_Descriere_Activitate=?, Client_Web=?, Client_Caracterizare=? 
    WHERE ID_Client=?"
);

mysqli_stmt_bind_param($stmt, 'ssssssssssssi', 
    $client_cod_caen, $client_adresa, $client_telefon, $client_banca, $client_iban,
    $client_localitate, $client_judet, $client_email, $client_numar_angajati,
    $client_descriere, $client_web, $client_caracterizare, $cID
);

if (!mysqli_stmt_execute($stmt)) {
    die('Error: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt);
echo "<div class=\"success callout\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteuserclients.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}

ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
// Prepared statement pentru SELECT
$stmt_select = mysqli_prepare($conn, "SELECT * FROM clienti_date WHERE ID_Client=?");
mysqli_stmt_bind_param($stmt_select, 'i', $cID);
mysqli_stmt_execute($stmt_select);
$result = mysqli_stmt_get_result($stmt_select);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_select);
?>
        <script src="<?php echo $strSiteURL ?>/js/foundation/jquery.js"></script>

        <script language="JavaScript" type="text/JavaScript">
            $(document).ready(function() {
	$("#users").validate();
});
</script>
        <link rel="stylesheet" href="../js/simple-editor/simple-editor.css">
        <script src="../js/simple-editor/simple-editor.js"></script>


        <?php
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="siteuserclients.php" class="button"><?php echo $strBack?></a></p>
            </div>
        </div>
        <form Method="post" id="users" Action="siteuserclients.php?mode=edit&cID=<?php echo $row['ID_Client']?>">
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCompanyName?></label>
                    <input type="text" id="<?php echo $strTitle?>" name="<?php echo $strTitle?>"
                        value="<?php echo $row['Client_Denumire'] ?>" readonly>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCompanyVAT?></label>
                    <input type="text" id="<?php echo $row['Client_CUI'] ?>" name="<?php echo $row['Client_CUI'] ?>"
                        value="<?php echo $row['Client_CUI'] ?>" readonly>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCompanyRC?></label>
                    <input type="text" id="  <?php echo $row['Client_RC'] ?>" name="<?php echo $row['Client_RC'] ?>"
                        value="  <?php echo $row['Client_RC'] ?>" readonly>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strAddress?></label>
                    <textarea name="Client_Adresa" readonly><?php echo $row['Client_Adresa'] ?></textarea>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCity?></label>
                    <input name="Client_Localitate" Type="text" size="30"
                        value="<?php echo $row['Client_Localitate'] ?>" readonly />
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCounty?></label>
                    <input name="Client_Judet" Type="text" size="30" value="<?php echo $row['Client_Judet'] ?>"
                        readonly />
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCompanyBank?></label>
                    <input name="Client_Banca" Type="text" size="30" value="<?php echo $row['Client_Banca'] ?>"
                        class="required" />
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCompanyIBAN?></label>
                    <input name="Client_IBAN" Type="text" size="30" value="<?php echo $row['Client_IBAN'] ?>" />
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strPhone?></label>
                    <input name="Client_Telefon" Type="text" value="<?php echo $row['Client_Telefon'] ?>" size="30" />
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strEmail?></label>
                    <input name="Client_Email" Type="text" size="30" value="<?php echo $row['Client_Email'] ?>" />
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strEmployees?></label>
                    <input name="Client_Numar_Angajati" Type="text" size="30"
                        value="<?php echo $row['Client_Numar_Angajati'] ?>" />
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strCAENCode?></label>
                    <input name="Client_Cod_CAEN" Type="text" size="30" value="<?php echo $row['Client_Cod_CAEN'] ?>" />
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strWWW?></label>
                    <input name="Client_Web" Type="text" size="30" value="<?php echo $row['Client_Web'] ?>" />
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strActivities?></label>
                    <textarea name="Client_Descriere_Activitate" id="simple-editor-html" class="simple-editor-html"
                        rows="5"><?php echo $row['Client_Descriere_Activitate'] ?></textarea>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strProfile?></label>
                    <textarea name="Client_Caracterizare" id="simple-editor-html" class="simple-editor-html"
                        rows="5"><?php echo $row['Client_Caracterizare'] ?></textarea>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"> <input Type="submit"
                        Value="<?php echo $strAdd?>" name="Submit" class="button success" />
                </div>
            </div>
        </form>

        <?php
}
Else
{
$query="SELECT clienti_date.ID_Client, clienti_contracte.ID_Client, Contract_Alocat, Client_Denumire, Client_CUI, Client_Localitate, Client_Judet FROM clienti_date, clienti_contracte 
WHERE Contract_Alocat='$code' AND  clienti_date.ID_Client=clienti_contracte.ID_Client AND Contract_Activ=0";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY Client_Denumire ASC $pages->limit";
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
echo $strTotal . " " .$numar." ".$strClients ;
echo " <br /><br />";
echo $pages->display_pages();
echo " <br /><br /></div>";

?>
            <table width="100%">
                <thead>
                    <tr>
                        <th width="70%"><?php echo $strTitle?></th>
                        <th width="10%"><?php echo $strVAT?></th>
                        <th width="10%"><?php echo $strCity?></th>
                        <th width="10%"><?php echo $strCounty?></th>
                        <th width="10%"><?php echo $strEdit?></th>
                        <th width="5%"><?php echo $strDetails?></th>
                    </tr>
                </thead>
                <tbody>

                    <?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[Client_CUI]</td>
			<td>$row[Client_Localitate]</td>
			<td>$row[Client_Judet]</td>
			 <td><a href=\"siteuserclients.php?mode=edit&cID=$row[ID_Client]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>

			 <td><a href=\"clientprofile.php?cID=$row[ID_Client]\"><i class=\"large fa fa-search-plus\" title=\"$strView\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td colspan=\"7\">&nbsp;</td></tr></tfoot></table>";
}
echo " <div class=\"paginate\">";
echo $pages->display_pages();
echo "</div>";
}
?>
        </div>
    </div>
    <?php
include '../bottom.php';
?>