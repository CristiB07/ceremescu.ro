<?php
// update 29.07.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle="Export fișier clienți Excel";
include '../dashboard/header.php';
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

$uid = intval($_SESSION['uid']);
$code = $_SESSION['code'];
$month= date('m');
$year=date('Y');
$day = date('d');
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
if ((isset( $_POST['cid'])) && !empty( $_POST['cid'])){
$cid = intval($_POST['cid']);
if ($cid <= 0) {
	echo $strThereWasAnError;
	die;
}
}

$sqlu = "SELECT utilizator_Prenume, utilizator_Nume FROM date_utilizatori WHERE utilizator_ID = ?";
$stmt = $conn->prepare($sqlu);
$stmt->bind_param("i", $uid);
$stmt->execute();
$resultu = $stmt->get_result();
$rowu = $resultu->fetch_assoc();
$stmt->close();

if (!$rowu) {
	die('User not found');
}

$Nume = htmlspecialchars($rowu["utilizator_Prenume"] ." ". $rowu["utilizator_Nume"], ENT_QUOTES, 'UTF-8');
$sqlu=" SELECT * from date_utilizatori Where utilizator_ID='$uid'";
$resultu=ezpub_query($conn,$sqlu);
$rowu = ezpub_fetch_array($resultu);
$Nume=$rowu["utilizator_Prenume"] ." ". $rowu["utilizator_Nume"];

	
$fp = fopen($hddpath .'/' . $exports_folder .'/Export_Clienți_'.$siteInvoicingCode.'_'.$month.'_'.$cid.'.xml', "w");
$header="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
        <?mso-application progid=\"Excel.Sheet\"?>
        <Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
            xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
            xmlns:html=\"https://www.w3.org/TR/html401/\">
            <ss:Styles>
                <ss:Style ss:ID=\"A\">
                    <ss:Font ss:FontName=\"Open Sans\" ss:Size=\"12\" ss:Color=\"Red\" />
                </ss:Style>
            </ss:Styles>
            <DocumentProperties xmlns=\"urn:schemas-microsoft-com:office:office\">
                <Author>". $Nume . "</Author>
                <LastAuthor>". $Nume. "</LastAuthor>
                <Created>". date("d-m-Y H:i:s")."</Created>
                <Version>15.00</Version>
            </DocumentProperties>
            <Worksheet ss:Name=\"Export clienți ".$siteInvoicingCode. " " . $month ." \">
                <Table>
                    <Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\" />
                    <Row>
                        <Cell><Data ss:Type=\"String\">ID Client</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Denumire client</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Client blocat</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Client RO</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Client CIF</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Client RC</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Client Adresa</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Localtatea</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Județ</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Țara</Data></Cell>
                         <Cell><Data ss:Type=\"String\">Rezidența</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Categoria</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Banca</Data></Cell>
                        <Cell><Data ss:Type=\"String\">IBAN</Data></Cell>
                    </Row>
                    ";
                    fwrite($fp, $header);
                    //adaugă clienți
                        $query = "SELECT ID_Client, Client_Denumire, Client_RO, Client_CIF, Client_RC,
                    Client_Adresa, Client_Localitate, Client_Judet, Client_Banca, Client_IBAN
                    FROM clienti_date WHERE ID_Client > ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $cid);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    while($row = $result->fetch_assoc()) {
                        $client_insert = "<Row>";
                        $client_insert.="<Cell><Data ss:Type=\"String\">". htmlspecialchars($row["ID_Client"], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $client_insert.="<Cell><Data ss:Type=\"String\">". htmlspecialchars($row["Client_Denumire"], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $client_insert.="<Cell><Data ss:Type=\"String\"></Data></Cell>";
                        $client_insert.="<Cell><Data ss:Type=\"String\">". htmlspecialchars($row["Client_RO"], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $client_insert.="<Cell><Data ss:Type=\"String\">". htmlspecialchars($row["Client_CIF"], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $client_insert.="<Cell><Data ss:Type=\"String\">". htmlspecialchars($row["Client_RC"], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $client_insert.="<Cell><Data ss:Type=\"String\">". htmlspecialchars($row["Client_Adresa"], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $client_insert.="<Cell><Data ss:Type=\"String\">". htmlspecialchars($row["Client_Localitate"], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $client_insert.="<Cell><Data ss:Type=\"String\">". htmlspecialchars($row["Client_Judet"], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $client_insert.="<Cell><Data ss:Type=\"String\">România</Data></Cell>";
                        $client_insert.="<Cell><Data ss:Type=\"String\">Romania</Data></Cell>";
                        $client_insert.="<Cell><Data ss:Type=\"String\">Intern</Data></Cell>";
                        $client_insert.="<Cell><Data ss:Type=\"String\">". htmlspecialchars($row["Client_Banca"], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $client_insert.="<Cell><Data ss:Type=\"String\">". htmlspecialchars($row["Client_IBAN"], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $client_insert.="</Row>";
                        fwrite($fp, $client_insert);
                    }
                    
                    $stmt->close();
                    $client_close = "";
                    $client_close.="
                </Table>
            </Worksheet>
        </Workbook>";

        fwrite($fp, $client_close);

        fclose($fp);

        echo "<div class=\"callout success\">$strFileGenerated</div>
    </div>
</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer() {
    window.location = \"exportclients.php\"
}
//
-->
</script>

<body onLoad=\"setTimeout('delayer()', 1500)\">";
    include '../bottom.php';
    die;
    }

    // get the ID
    else
    {
    ?>
    <form method="post" id="users" Action="exportclients.php">
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <label><?php echo $strLastClientID?>
                    <input name="cid" type="text" class="required" />
                </label>
            </div>
        </div>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell text-center">
                <input Type="submit" Value="<?php echo $strExport?>" name="Submit" class="button success" />
            </div>
        </div>
    </form>
    <?php
}
?>

    </div>
    </div>
    <?php
include '../bottom.php';
?>