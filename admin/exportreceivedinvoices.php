<?php // Last Modified Time: Wednesday, December 3, 2025 at 10:35:23 AM Eastern European Standard Time ?>
<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle="Export fișier Facturi Primite Excel";
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

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$month= date('m');
$year=date('Y');
$day = date('d');
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

// CSRF validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
	die('<div class="callout alert">Invalid CSRF token</div>');
}

if ((isset( $_POST['month'])) && !empty( $_POST['month'])){
	$month = intval($_POST['month']);
	if ($month < 1 || $month > 12) {
		die('<div class="callout alert">Invalid month</div>');
	}
	if ($month < 10) {
		$month = "0" . $month;
	}
}
else
{
	echo "<div class=\"callout alert\">$strThereWasAnError</div>";
	die;
}
if ((isset( $_POST['year'])) && !empty( $_POST['year'])){
	$year = intval($_POST['year']);
	if ($year < 2000 || $year > 2100) {
		die('<div class="callout alert">Invalid year</div>');
	}
}
else
{
	echo "<div class=\"callout alert\">$strThereWasAnError</div>";
	die;
}

$stmt = $conn->prepare("SELECT utilizator_Prenume, utilizator_Nume FROM date_utilizatori WHERE utilizator_ID = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$resultu = $stmt->get_result();
$rowu = $resultu->fetch_assoc();
$stmt->close();
$Nume = $rowu["utilizator_Prenume"] . " " . $rowu["utilizator_Nume"];

	
$fp = fopen($hddpath .'/' . $exports_folder .'/Export_facturi_primite_'.$siteInvoicingCode.'_'.$month.'.xml', "w");
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
            <Worksheet ss:Name=\"Export facturi primite ".$siteInvoicingCode. " " . $month ." \">
                <Table>
                    <Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\" />
                    <Row>
                        <Cell><Data ss:Type=\"String\">Linie</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Serie</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Număr factură</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Data emiterii</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Data scadentă</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Tip factură</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Nume partener</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Atribut fiscal</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Cod fiscal fiscal</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Nr.reg.Comert</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Rezidența</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Țara</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Județ</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Localitate</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Strada</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Număr</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Bloc</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Scară</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Etaj</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Apartament</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Cod Poștal</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Moneda</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Curs valutar</Data></Cell>
                        <Cell><Data ss:Type=\"String\">TVA la încasare</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Taxare inversă</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Factură transport</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Cod agent</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Valoare netă toală</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Valoare TVA</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Total document</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Articol</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Cantitate</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Tip mișcare stoc</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Cont</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Preț de listă</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Valoare fără TVA</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Valoare TVA</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Valoare cu TVA</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Opțiune TVA</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Cota TVA</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Cod TVA SAFT</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Observație</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Centre de cost</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Deductibilitate</Data></Cell>
                        <Cell><Data ss:Type=\"String\">Cod SAFT Deductibilitate</Data></Cell>
                    </Row>
                    ";
                    fwrite($fp, $header);
                    //adaugă alimentări

                    $stmt = $conn->prepare("SELECT fp_numar_factura, fp_data_emiterii, fp_data_scadenta, fp_nume_furnizor,
                    fp_CUI_furnizor, fp_RC_furnizor, fp_valoare_neta, fp_valoare_TVA, fp_valoare_totala,
                    articolFP_nume, articolFP_cantitate, articolFP_pret, articolFP_valoare, articolFP_TVA, articolFP_procent_TVA
                    FROM facturare_facturi_primite
                    INNER JOIN facturare_articole_facturi_primite ON facturare_facturi_primite.fp_id = facturare_articole_facturi_primite.factura_ID
                    WHERE YEAR(fp_data_emiterii) = ? AND MONTH(fp_data_emiterii) = ?
                    ORDER BY fp_numar_factura DESC");
                    $month_int = intval($month);
                    $stmt->bind_param("ii", $year, $month_int);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while($row = $result->fetch_assoc())
                    {
                        $articol_total = $row["articolFP_valoare"] + $row["articolFP_TVA"];
                    $factura_insert = "";
                    $factura_insert .= "<Row>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">1</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['fp_numar_factura'], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">".date("Ymd",strtotime($row["fp_data_emiterii"]))."</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">".date("Ymd",strtotime($row["fp_data_scadenta"]))."</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">380</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['fp_nume_furnizor'], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $atribut_fiscal = substr($row["fp_CUI_furnizor"], 0, 2);
                        if ($atribut_fiscal == 'RO')
                        {
                            $factura_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($atribut_fiscal, ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        }
                        else
                        {
                            $factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                        }
                        $cui_clean = str_replace('RO', '', $row["fp_CUI_furnizor"]);
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($cui_clean, ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['fp_RC_furnizor'], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">România</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">România</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">RON</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">0.0000</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">$VATRegime</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">0</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['fp_valoare_neta'], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['fp_valoare_TVA'], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['fp_valoare_totala'], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['articolFP_nume'], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['articolFP_cantitate'], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">628</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['articolFP_pret'], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['articolFP_valoare'], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['articolFP_TVA'], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($articol_total, ENT_XML1, 'UTF-8') . "</Data></Cell>";
                        If ($row["articolFP_procent_TVA"]==0)
                        {
                            $factura_insert .= "<Cell><Data ss:Type=\"String\">Scutite</Data></Cell>";
                        }
                        else
                        {
                            $factura_insert .= "<Cell><Data ss:Type=\"String\">Taxabile</Data></Cell>";
                        }
                        $factura_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['articolFP_procent_TVA'], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                       if ($row["articolFP_procent_TVA"] == 19)
                       {
                           $factura_insert .= "<Cell><Data ss:Type=\"String\">301301</Data></Cell>";
                       }
                       elseif ($row["articolFP_procent_TVA"]==21)
                       {
                           $factura_insert .= "<Cell><Data ss:Type=\"String\">301305</Data></Cell>";
                       }
                       elseif ($row["articolFP_procent_TVA"]==11)
                       {
                           $factura_insert .= "<Cell><Data ss:Type=\"String\">301306</Data></Cell>";
                       }
                       elseif ($row["articolFP_procent_TVA"]==5)
                       {
                           $factura_insert .= "<Cell><Data ss:Type=\"String\">301303</Data></Cell>";
                       }
                       elseif ($row["articolFP_procent_TVA"]==9)
                       {
                           $factura_insert .= "<Cell><Data ss:Type=\"String\">301302</Data></Cell>";
                       }
                       else
                       {
                           $factura_insert .= "<Cell><Data ss:Type=\"String\">308302</Data></Cell>";
                       }
                       $factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                       $factura_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                       $factura_insert .= "<Cell><Data ss:Type=\"String\">1</Data></Cell>";
                       $factura_insert .= "<Cell><Data ss:Type=\"String\">360104</Data></Cell>";
                       $factura_insert.="</Row>";
                    fwrite($fp, $factura_insert);
                    }
                    $stmt->close();
                    $factura_close = "";
                    $factura_close.="
                </Table>
            </Worksheet>
        </Workbook>";

        fwrite($fp, $factura_close);

        fclose($fp);

        echo "<div class=\"callout success\">$strFileGenerated</div>
    </div>
</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer() {
    window.location = \"exportreceivedinvoices.php\"
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
    <form method="post" Action="exportreceivedinvoices.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="grid-x grid-margin-x">
            <div class="large-6 medium-6 small-6 cell">
                <label><?php echo $strMonth?>
                    <select name="month">
                        <option value="00" selected>--</option>
                        <?php for ( $m = 1; $m <= 12; $m ++) {
    		
     		//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
    			echo "<option value=\"$m\">$monthname</option>";} 
			?>
                    </select>
                </label>
            </div>
            <div class="large-6 medium-6 small-6 cell">
                <label><?php echo $strYear?>
                    <select name="year">
                        <option value="0000" selected>--</option>
                        <?php
		$cy=date("Y");
		$fy=$cy+1;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
    	echo "<option value=\"$y\">$y</option>";} 
			?>
                    </select>
                </label>
            </div>
        </div>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell text-center">
                <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success" />
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
