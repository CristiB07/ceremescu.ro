<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Creare efactura";
include '../dashboard/header.php';

$d = date("d-m-Y ");
$dataincarcarii = date("Y-m-d H:i:s");
//$s = date('d-m-Y', strtotime($d . ' +10 day'));

$query="SELECT * FROM facturare_facturi WHERE factura_ID='$_GET[cID]'";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
$facturaID=$row["factura_ID"];
$query3="SELECT distinct county_id FROM generale_localitati WHERE county='$row[factura_client_judet]'";
$result3=ezpub_query($conn,$query3);
$row3=ezpub_fetch_array($result3);
$subentity='RO-'.$row3["county_id"];

$query4="SELECT Client_codpostal FROM clienti_date WHERE ID_Client='$row[factura_client_ID]'";
$result4=ezpub_query($conn,$query4);
$row4=ezpub_fetch_array($result4);
if (!$row4["Client_codpostal"])
{$codpostal='101010';}
else
{$codpostal=$row4["Client_codpostal"];}
$codenumarfactura=str_pad($row["factura_numar"], 8, '0', STR_PAD_LEFT);

$fp = fopen($hddpath .'/' . $efactura_folder .'/Factura_'. $siteInvoicingCode. $codenumarfactura.'.xml', "w");
$filename='Factura_'. $siteInvoicingCode. $codenumarfactura.'.xml';
$header='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:ns4="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2 http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-Invoice-2.1.xsd">
    <cbc:CustomizationID>urn:cen.eu:en16931:2017#compliant#urn:efactura.mfinante.ro:CIUS-RO:1.0.1</cbc:CustomizationID>
    <cbc:ID>'. $row["factura_numar"].'</cbc:ID>
    <cbc:IssueDate>'.$row["factura_data_emiterii"].'</cbc:IssueDate>
    <cbc:DueDate>'.$row["factura_client_termen"].'</cbc:DueDate>
    <cbc:InvoiceTypeCode>'.$row["factura_cod_factura"].'</cbc:InvoiceTypeCode>
	';
	if ($VATRegime=='1')
{
	$header  = $header . ' <cbc:Note>TVA la încasare.</cbc:Note>
	<cbc:DocumentCurrencyCode>RON</cbc:DocumentCurrencyCode>
	    <cac:InvoicePeriod>
        <cbc:DescriptionCode>432</cbc:DescriptionCode>
    </cac:InvoicePeriod>
	';
}
 else
 {	 $header  = $header . '
	<cbc:TaxPointDate>'.$row["factura_data_emiterii"].'</cbc:TaxPointDate>
	<cbc:DocumentCurrencyCode>RON</cbc:DocumentCurrencyCode>
    	    <cac:InvoicePeriod>
        <cbc:DescriptionCode>3</cbc:DescriptionCode>
    </cac:InvoicePeriod>';
 }
    $header  = $header . ' 
    <cac:ContractDocumentReference>
        <cbc:ID>'.$row["factura_client_contract"].'</cbc:ID>
    </cac:ContractDocumentReference>';
 if ($row["factura_factura_stornata"]!='')
 {   $header  = $header . ' 
	<cac:BillingReference>
        <cac:InvoiceDocumentReference>
            <cbc:ID>'.$row["factura_stornata"].'</cbc:ID>
            <cbc:IssueDate>'.$row["factura_stornata_data"].'</cbc:IssueDate>
        </cac:InvoiceDocumentReference>
    </cac:BillingReference>';}
	 $header  = $header . '<cac:AccountingSupplierParty>
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
				Else{
	$header  = $header . '	
                <cac:TaxScheme>
                </cac:TaxScheme>';}
				
  	$header = $header .'</cac:PartyTaxScheme>
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
        $tpquery="SELECT DISTINCT articol_procent_TVA FROM facturare_articole_facturi WHERE factura_ID=$_GET[cID]";
        $tpresult=ezpub_query($conn,$tpquery);
      While  ($tprow=ezpub_fetch_array($tpresult)){
        $subtotalq="SELECT SUM(articol_TVA) AS subtotal, SUM(articol_valoare) AS valoare_articole FROM facturare_articole_facturi WHERE factura_ID=$_GET[cID] AND articol_procent_TVA='$tprow[articol_procent_TVA]'";
        $subtotalr=ezpub_query($conn,$subtotalq);
        $rowsb=ezpub_fetch_array($subtotalr);
      	$header = $header .  '<cac:TaxSubtotal>
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
        </cac:TaxSubtotal>';}

    $header = $header . '</cac:TaxTotal>
    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="RON">'.number_format($row["factura_client_valoare"],2, '.', '').'</cbc:LineExtensionAmount>
        <cbc:TaxExclusiveAmount currencyID="RON">'.number_format($row["factura_client_valoare"],2, '.', '').'</cbc:TaxExclusiveAmount>
        <cbc:TaxInclusiveAmount currencyID="RON">'.number_format($row["factura_client_valoare_totala"],2, '.', '').'</cbc:TaxInclusiveAmount>
        <cbc:PayableAmount currencyID="RON">'.number_format($row["factura_client_valoare_totala"],2, '.', '').'</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>';
	fwrite($fp, $header);
	
	$query2="SELECT * FROM facturare_articole_facturi WHERE factura_ID=$_GET[cID]";
$result2=ezpub_query($conn,$query2);
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
$efactura_close = "";
$efactura_close .= '
</Invoice>';
fwrite($fp, $efactura_close);
fclose($fp);

echo "<div class=\"callout success\">" . $filename." ". $strWasCreated . ".</div>";

$upload_url=$upload_url.$siteCIF;
$fname= $hddpath .'/' . $efactura_folder .'/Factura_'. $siteInvoicingCode. $codenumarfactura .'.xml';
		$fullfile=$fname;
		$file = fopen($fullfile, "r");
		$data = fread($file, filesize($fullfile));
		fclose($file);
		$url = $upload_url;
		$headr = array();
		$headr[] = 'Authorization: Bearer '.$site_client_token;
		$headr[] = 'Content-Type: text/plain';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$data); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
		$server_output = curl_exec($ch);
		curl_close ($ch);
		$xml = json_encode(simplexml_load_string($server_output));
		//echo $xml;
		//die;
		$json=json_decode($xml);
		foreach ($json as $a=>$b){
			$index=$b->index_incarcare;	
		}
		if (!isset($index))
		{ 
	echo "<div class=\"callout error\">". $strThereWasAnError ." " . $filename.": <strong>". $xml ."</strong>.</div></div></div>";
        
	include '../bottom.php';
die;
	}
	Else
	{		echo "<div class=\"callout success\">" . $filename." ". $strWasUploaded  .".". $strUploadIndexIs." <strong>". $index ."</strong>.</div>";

	$mSQL = "INSERT INTO efactura(";
	$mSQL = $mSQL . "factura_xml,";
	$mSQL = $mSQL . "factura_data_incarcarii,";
	$mSQL = $mSQL . "factura_ID,";
	$mSQL = $mSQL . "factura_CIF,";
	$mSQL = $mSQL . "factura_index_incarcare)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$filename . "', ";
	$mSQL = $mSQL . "'" .$dataincarcarii . "', ";
	$mSQL = $mSQL . "'" .$row["factura_ID"] . "', ";
	$mSQL = $mSQL . "'" .$row["factura_client_CIF"] . "', ";
	$mSQL = $mSQL . "'" .$index . "') ";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
	$usql="UPDATE facturare_facturi SET factura_client_efactura_generata='DA' WHERE factura_ID='$_GET[cID]';";
		ezpub_query($conn,$usql);

echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"efacturi.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
}
include '../bottom.php';
}
?>