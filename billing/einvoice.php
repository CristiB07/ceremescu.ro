<?php
include_once '../settings.php';
include_once '../classes/common.php';

/**
 * Procesează o factură și o încarcă în sistemul ANAF e-factura
 * 
 * @param mysqli $conn Conexiunea la baza de date
 * @param int $cID ID-ul facturii
 * @param bool $silent_mode Dacă este true, nu afișează mesaje și nu face redirect
 * @return array ['success' => bool, 'message' => string, 'index' => string|null, 'filename' => string|null]
 */
function processEInvoice($conn, $cID, $silent_mode = false) {
    global $hddpath, $efactura_folder, $siteInvoicingCode, $VATRegime, $siteCompanyLegalAddress;
    global $siteVATNumber, $siteCompanyLegalName, $siteCompanyRegistrationNr, $siteCompanySocialCapital;
    global $siteCompanyPhones, $siteCompanyEmail, $siteBankAccount, $siteTrezoAccount;
    global $upload_url, $siteCIF, $site_client_token;
    global $strWasCreated, $strThereWasAnError, $strWasUploaded, $strUploadIndexIs, $strRecordAdded;
    
    $dataincarcarii = date("Y-m-d H:i:s");
    
    $stmt = mysqli_prepare($conn, "SELECT * FROM facturare_facturi WHERE factura_ID=?");
    mysqli_stmt_bind_param($stmt, "i", $cID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    
    if (!$row) {
        return ['success' => false, 'message' => 'Factura nu a fost găsită', 'index' => null, 'filename' => null];
    }
    
    $facturaID=$row["factura_ID"];

    $facturaID=$row["factura_ID"];

    $factura_client_judet = normalizeDiacritice($row['factura_client_judet']);
    $stmt3 = mysqli_prepare($conn, "SELECT distinct county_id FROM generale_localitati WHERE UPPER(county)=UPPER(?)");
    mysqli_stmt_bind_param($stmt3, "s", $factura_client_judet);
    mysqli_stmt_execute($stmt3);
    $result3 = mysqli_stmt_get_result($stmt3);
    $row3 = mysqli_fetch_array($result3, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt3);

    $subentity='RO-'.$row3["county_id"];

    $factura_client_ID = (int)$row['factura_client_ID'];
    $stmt4 = mysqli_prepare($conn, "SELECT Client_codpostal FROM clienti_date WHERE ID_Client=?");
    mysqli_stmt_bind_param($stmt4, "i", $factura_client_ID);
    mysqli_stmt_execute($stmt4);
    $result4 = mysqli_stmt_get_result($stmt4);
    $row4 = mysqli_fetch_array($result4, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt4);
    if (!$row4["Client_codpostal"])
    {$codpostal='101010';}
    else
    {$codpostal=$row4["Client_codpostal"];}
    $codenumarfactura=str_pad($row["factura_numar"], 8, '0', STR_PAD_LEFT);

    $filename='Factura_'. $siteInvoicingCode. $codenumarfactura.'.xml';
    $filepath = $hddpath .'/' . $efactura_folder .'/' . $filename;
    $fp = fopen($filepath, "w");
$header='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
    xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
    xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
    xmlns:ns4="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2 http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-Invoice-2.1.xsd">
    <cbc:CustomizationID>urn:cen.eu:en16931:2017#compliant#urn:efactura.mfinante.ro:CIUS-RO:1.0.1</cbc:CustomizationID>
    <cbc:ID>'. $row["factura_numar"].'</cbc:ID>
    <cbc:IssueDate>'.$row["factura_data_emiterii"].'</cbc:IssueDate>
    <cbc:DueDate>'.$row["factura_client_termen"].'</cbc:DueDate>
    <cbc:InvoiceTypeCode>'.$row["factura_cod_factura"].'</cbc:InvoiceTypeCode>
    ';
    if ($VATRegime=='1')
    {
    $header = $header . ' <cbc:Note>TVA la încasare.</cbc:Note>
    <cbc:DocumentCurrencyCode>RON</cbc:DocumentCurrencyCode>
    <cac:InvoicePeriod>
        <cbc:DescriptionCode>432</cbc:DescriptionCode>
    </cac:InvoicePeriod>
    ';
    }
    else
    { $header = $header . '
    <cbc:TaxPointDate>'.$row["factura_data_emiterii"].'</cbc:TaxPointDate>
    <cbc:DocumentCurrencyCode>RON</cbc:DocumentCurrencyCode>';
    }
    $header = $header . '
    <cac:ContractDocumentReference>
        <cbc:ID>'.$row["factura_client_contract"].'</cbc:ID>
    </cac:ContractDocumentReference>';
    $header = $header . '<cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PostalAddress>
                <cbc:StreetName>'.$siteCompanyLegalAddress.'</cbc:StreetName>
                <cbc:CityName>SECTOR3</cbc:CityName>
                <cbc:PostalZone>031041</cbc:PostalZone>
                <cbc:CountrySubentity>RO-B</cbc:CountrySubentity>
                <cac:Country>
                    <cbc:IdentificationCode>RO</cbc:IdentificationCode>
                </cac:Country>
            </cac:PostalAddress>
            <cac:PartyTaxScheme>
                <cbc:CompanyID>'.$siteVATNumber.'</cbc:CompanyID>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:PartyTaxScheme>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>'.$siteCompanyLegalName.'</cbc:RegistrationName>
                <cbc:CompanyID>'.$siteCompanyRegistrationNr.'</cbc:CompanyID>
                <cbc:CompanyLegalForm>Capital social: '.$siteCompanySocialCapital.'</cbc:CompanyLegalForm>
            </cac:PartyLegalEntity>
            <cac:Contact>
                <cbc:Name>Departament financiar</cbc:Name>
                <cbc:Telephone>'.$siteCompanyPhones.'</cbc:Telephone>
                <cbc:ElectronicMail>'.$siteCompanyEmail.'</cbc:ElectronicMail>
            </cac:Contact>
        </cac:Party>
    </cac:AccountingSupplierParty>
    <cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PostalAddress>
                <cbc:StreetName>'.$row["factura_client_adresa"].'</cbc:StreetName>
                <cbc:CityName>'.$row["factura_client_localitate"].'</cbc:CityName>
                <cbc:PostalZone>'.$codpostal. '</cbc:PostalZone>
                <cbc:CountrySubentity>'.$subentity.'</cbc:CountrySubentity>
                <cac:Country>
                    <cbc:IdentificationCode>RO</cbc:IdentificationCode>
                </cac:Country>
            </cac:PostalAddress>
            <cac:PartyTaxScheme>
                <cbc:CompanyID>'.str_replace(' ', '', $row['factura_client_CUI']).'</cbc:CompanyID>';
                If ($row["factura_client_RO"]=='RO')
                {
                $header = $header .'
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>';}
                else{
                $header = $header . '
                <cac:TaxScheme>
                </cac:TaxScheme>';}

                $header = $header .'
            </cac:PartyTaxScheme>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>'.sanitarization($row["factura_client_denumire"]).'</cbc:RegistrationName>
                <cbc:CompanyID>'. $row['factura_client_CUI'].'</cbc:CompanyID>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>
    <cac:Delivery>
        <cbc:ActualDeliveryDate>'.$row["factura_data_emiterii"].'</cbc:ActualDeliveryDate>
    </cac:Delivery>
    <cac:PaymentMeans>
        <cbc:PaymentMeansCode>42</cbc:PaymentMeansCode>
        <cac:PayeeFinancialAccount>
            <cbc:ID>'.$siteBankAccount.'</cbc:ID>
        </cac:PayeeFinancialAccount>
    </cac:PaymentMeans>
    <cac:PaymentMeans>
        <cbc:PaymentMeansCode>42</cbc:PaymentMeansCode>
        <cac:PayeeFinancialAccount>
            <cbc:ID>'.$siteTrezoAccount.'</cbc:ID>
        </cac:PayeeFinancialAccount>
    </cac:PaymentMeans>
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="RON">'.number_format($row["factura_client_valoare_tva"],2, '.', '').'</cbc:TaxAmount>';
        $stmt_tva = mysqli_prepare($conn, "SELECT DISTINCT articol_procent_TVA FROM facturare_articole_facturi WHERE factura_ID=?");
        mysqli_stmt_bind_param($stmt_tva, "i", $cID);
        mysqli_stmt_execute($stmt_tva);
        $tpresult = mysqli_stmt_get_result($stmt_tva);
        While ($tprow=mysqli_fetch_array($tpresult, MYSQLI_ASSOC)){
        $articol_procent = $tprow['articol_procent_TVA'];
        $stmt_sub = mysqli_prepare($conn, "SELECT SUM(articol_TVA) AS subtotal, SUM(articol_valoare) AS valoare_articole FROM facturare_articole_facturi WHERE factura_ID=? AND articol_procent_TVA=?");
        mysqli_stmt_bind_param($stmt_sub, "is", $cID, $articol_procent);
        mysqli_stmt_execute($stmt_sub);
        $subtotalr = mysqli_stmt_get_result($stmt_sub);
        $rowsb=mysqli_fetch_array($subtotalr, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt_sub);
        $header = $header . '<cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="RON">'.number_format($rowsb["valoare_articole"],2, '.', '').'</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="RON">'.number_format($rowsb["subtotal"],2, '.', '').'</cbc:TaxAmount>
            <cac:TaxCategory>
                <cbc:ID>S</cbc:ID>
                <cbc:Percent>'.$tprow["articol_procent_TVA"].'</cbc:Percent>';
                if ($tprow["articol_procent_TVA"]=='0')
                {
                $header = $header . '<cac:TaxExemptionReason>E</cac:TaxExemptionReason>';
                }
                $header = $header . '
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>';
        }
        mysqli_stmt_close($stmt_tva);
        $header = $header . '
    </cac:TaxTotal>
    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="RON">'.number_format($row["factura_client_valoare"],2, '.', '').'</cbc:LineExtensionAmount>
        <cbc:TaxExclusiveAmount currencyID="RON">'.number_format($row["factura_client_valoare"],2, '.', '').'</cbc:TaxExclusiveAmount>
        <cbc:TaxInclusiveAmount currencyID="RON">'.number_format($row["factura_client_valoare_totala"],2, '.', '').'</cbc:TaxInclusiveAmount>
        <cbc:PayableAmount currencyID="RON">'.number_format($row["factura_client_valoare_totala"],2, '.', '').'</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>';
    fwrite($fp, $header);

    $stmt2 = mysqli_prepare($conn, "SELECT * FROM facturare_articole_facturi WHERE factura_ID=?");
    mysqli_stmt_bind_param($stmt2, "i", $cID);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);
    $count=0;
    While ($row2=ezpub_fetch_array($result2)){
    $count=$count+1;

    $efactura_insert = "";
    $efactura_insert .= '

    <cac:InvoiceLine>
        <cbc:ID>'.$count.'</cbc:ID>
        <cbc:InvoicedQuantity unitCode="H87">'.$row2["articol_bucati"].'</cbc:InvoicedQuantity>
        <cbc:LineExtensionAmount currencyID="RON">'.number_format($row2['articol_valoare'],2, '.', '').'</cbc:LineExtensionAmount>
        <cac:Item>
            <cbc:Description>'.truncateinvoiceitem($row2["articol_descriere"]).'</cbc:Description>
            <cbc:Name>'. substr($row2['articol_descriere'], 0, strpos($row2['articol_descriere'], ' - ')).'</cbc:Name>
            <cac:ClassifiedTaxCategory>
                <cbc:ID>S</cbc:ID>
                <cbc:Percent>'.$row2['articol_procent_TVA'].'</cbc:Percent>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:ClassifiedTaxCategory>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="RON">'.number_format($row2["articol_pret"],2, '.', '').'</cbc:PriceAmount>
        </cac:Price>
    </cac:InvoiceLine>
    ';
    fwrite($fp, $efactura_insert);
    }
    mysqli_stmt_close($stmt2);
        $efactura_close = "";
    $efactura_close .= '
</Invoice>';
    fwrite($fp, $efactura_close);
    fclose($fp);

    if (!$silent_mode) {
        echo "<div class=\"callout success\">" . $filename." ". $strWasCreated . ".</div>";
    }

    $upload_url_full = $upload_url.$siteCIF;
    $fullfile = $filepath;
    $file = fopen($fullfile, "r");
    $data = fread($file, filesize($fullfile));
    fclose($file);
    
    $headr = array();
    $headr[] = 'Authorization: Bearer '.$site_client_token;
    $headr[] = 'Content-Type: text/plain';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $upload_url_full);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
    $server_output = curl_exec($ch);
    if (PHP_VERSION_ID < 80500) { curl_close($ch); }
    
    $xml = json_encode(simplexml_load_string($server_output));
    $json = json_decode($xml);
    $index = null;
    
    foreach ($json as $a => $b) {
        if (isset($b->index_incarcare)) {
            $index = $b->index_incarcare;
        }
    }
    
    if (!isset($index)) {
        $error_message = $strThereWasAnError ." " . $filename.": <strong>". $xml ."</strong>";
        if (!$silent_mode) {
            echo "<div class=\"callout error\">" . $error_message . ".</div>";
        }
        return ['success' => false, 'message' => $error_message, 'index' => null, 'filename' => $filename];
    } else {
        if (!$silent_mode) {
            echo "<div class=\"callout success\">" . $filename." ". $strWasUploaded .".". $strUploadIndexIs." <strong>". $index ."</strong>.</div>";
        }

        $stmt_insert = mysqli_prepare($conn, "INSERT INTO efactura(factura_xml, factura_data_incarcarii, factura_ID, factura_CIF, factura_index_incarcare) VALUES(?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_insert, "ssiss", $filename, $dataincarcarii, $row["factura_ID"], $row["factura_client_CIF"], $index);

        if (!mysqli_stmt_execute($stmt_insert)) {
            $error_msg = 'Error: ' . mysqli_stmt_error($stmt_insert);
            if (!$silent_mode) {
                die($error_msg);
            }
            return ['success' => false, 'message' => $error_msg, 'index' => $index, 'filename' => $filename];
        } else {
            mysqli_stmt_close($stmt_insert);
            $stmt_update = mysqli_prepare($conn, "UPDATE facturare_facturi SET factura_client_efactura_generata='DA' WHERE factura_ID=?");
            mysqli_stmt_bind_param($stmt_update, "i", $cID);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);

            if (!$silent_mode) {
                echo "<div class=\"callout success\">$strRecordAdded</div>";
            }
            
            return ['success' => true, 'message' => 'Factura procesată cu succes', 'index' => $index, 'filename' => $filename];
        }
    }
}

// Verifică dacă este apelat direct (nu ca include)
if (basename($_SERVER['PHP_SELF']) == 'einvoice.php') {
    $strPageTitle="Creare efactura";
    include '../dashboard/header.php';

    if(!isset($_SESSION)) { 
        session_start(); 
    }
    if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes") {
        header("location:$strSiteURL/login/index.php?message=MLF");
        die;
    }

    // Validare parametru cID
    if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
        header("location:$strSiteURL/billing/siteinvoices.php?message=ER");
        die;
    }
    $cID = (int)$_GET['cID'];
    
    // Procesare factură
    $result = processEInvoice($conn, $cID, false);
    
    if ($result['success']) {
        echo "<script type=\"text/javascript\">
        <!--
        function delayer() {
            window.location = \"einvoices.php\"
        }
        //
        -->
        </script>
        <body onLoad=\"setTimeout('delayer()', 1500)\">";
    } else {
        echo "</div></div>";
    }
    
    include '../bottom.php';
}
?>