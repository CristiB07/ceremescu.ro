<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
}
$strPageTitle="Administrare facturi";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$month= date('m');
$year=date('Y');
$day = date('d');
// Acest bloc aparține de afișarea facturilor cu filtre, ar trebui mutat mai jos

if ((isset( $_GET['aloc'])) && !empty( $_GET['aloc'])){
$aloc=$_GET['aloc'];}
else{
$aloc=0;}
if ((isset( $_GET['cl'])) && !empty( $_GET['cl'])){
$cl=$_GET['cl'];}
else{
$cl=0;}
if ((isset( $_GET['act'])) && !empty( $_GET['act'])){
$act=$_GET['act'];}
else{
$act=0;}
if ((isset( $_GET['paid']))){
$paid=$_GET['paid'];}
else{
$paid=3;}
if ((isset( $_GET['yr'])) && !empty( $_GET['yr'])){
$fyear=$_GET['yr'];
$year=$fyear;
}
else{
$fyear=0;}
if ((isset( $_GET['fmonth'])) && !empty( $_GET['fmonth'])){
$fmonth=$_GET['fmonth'];}
else{
$fmonth=0;}
// Sfârșit bloc filtre
?>
<div class="grid-x grid-margin-x">
  <div class="large-12 medium-12 small-12 cell">
    <script language="JavaScript" type="text/JavaScript">
      function resizeIframe(obj) {
  obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
 }
</script>
    <?php
echo "<h1>$strPageTitle</h1>";
// ștergem o factură
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){
    // Validate mode
    if (!in_array($_GET['mode'], ['delete', 'new', 'edit'])) {
        die("Invalid mode parameter");
    }
    
    // Validate cID
    if (!isset($_GET['cID']) || !filter_var($_GET['cID'], FILTER_VALIDATE_INT)) {
        die("Invalid invoice ID");
    }
    $cID = (int)$_GET['cID'];
    
    // DELETE with prepared statement
    $stmt = mysqli_prepare($conn, "DELETE FROM facturare_facturi WHERE factura_ID=?");
    mysqli_stmt_bind_param($stmt, "i", $cID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
  window.location = \"siteinvoices.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}
// s-a șters factura
//începem if POST
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();

// Validate mode
if (!isset($_GET['mode']) || !in_array($_GET['mode'], ['new', 'edit'])) {
    die("Invalid mode parameter");
}

// Validate cID
if (!isset($_GET['cID']) || !filter_var($_GET['cID'], FILTER_VALIDATE_INT)) {
    die("Invalid invoice ID");
}
$cID = (int)$_GET['cID'];

If ($_GET['mode']=="new"){
  //verificăm ce client este. dacă există preluăm din lista de clienți, dacă nu, interogăm ANAF pentru datele clientului
	If ($_POST["existent"]==1)
	{ // clientul există
		// SELECT with prepared statement
		if (!isset($_POST['factura_client_denumire'])) {
		    die("Missing client name");
		}
		$stmt = mysqli_prepare($conn, "SELECT ID_Client FROM clienti_date WHERE Client_Denumire=?");
		mysqli_stmt_bind_param($stmt, "s", $_POST['factura_client_denumire']);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$row = ezpub_fetch_array($result);
		$Client_ID = $row["ID_Client"];
		mysqli_stmt_close($stmt);
	}
	else
	{ // clientul nu există - îl inserăm în baza de date
		$clientcui=$_POST["factura_client_RO"]." ".$_POST["factura_client_CIF"];
		
		// INSERT client with prepared statement
		$stmt_client = mysqli_prepare($conn, "INSERT INTO clienti_date(
			Client_Denumire, Client_Adresa, Client_CUI, Client_RC, Client_Banca, Client_IBAN,
			Client_RO, Client_CIF, Client_Localitate, Client_Tip, Client_Nr_Contract, Client_Judet
		) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		
		mysqli_stmt_bind_param($stmt_client, "ssssssssssss",
			$_POST["factura_client_denumire"],
			$_POST["factura_client_adresa"],
			$clientcui,
			$_POST["factura_client_RC"],
			$_POST["factura_client_banca"],
			$_POST["factura_client_IBAN"],
			$_POST["factura_client_RO"],
			$_POST["factura_client_CIF"],
			$_POST["factura_client_localitate"],
			$client_tip = '1',
			$_POST["factura_client_contract"],
			$_POST["factura_client_judet"]
		);
		
		if (!mysqli_stmt_execute($stmt_client)) {
			die('Error: ' . mysqli_stmt_error($stmt_client));
		}
		$Client_ID = mysqli_insert_id($conn);
		mysqli_stmt_close($stmt_client);
	}
//insert new invoice
$dataemiterii = $_POST["data_emiterii"];
$termenfactura = date('Y-m-d', strtotime($dataemiterii . ' +'.$_POST["factura_client_termen"].' day'));
//pregătim totalurile
$stmt1 = mysqli_prepare($conn, "SELECT SUM(articol_valoare) AS valoare_factura FROM facturare_articole_facturi WHERE factura_ID=?");
mysqli_stmt_bind_param($stmt1, "i", $cID);
mysqli_stmt_execute($stmt1);
$result1 = mysqli_stmt_get_result($stmt1);
$row1 = ezpub_fetch_array($result1);
$valoareproduse = $row1["valoare_factura"];
mysqli_stmt_close($stmt1);

$stmt2 = mysqli_prepare($conn, "SELECT SUM(articol_TVA) AS valoare_tva FROM facturare_articole_facturi WHERE factura_ID=?");
mysqli_stmt_bind_param($stmt2, "i", $cID);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
$row2 = ezpub_fetch_array($result2);
$valoareTVA = $row2["valoare_tva"];
mysqli_stmt_close($stmt2);

$stmt3 = mysqli_prepare($conn, "SELECT SUM(articol_total) AS grandtotal FROM facturare_articole_facturi WHERE factura_ID=?");
mysqli_stmt_bind_param($stmt3, "i", $cID);
mysqli_stmt_execute($stmt3);
$result3 = mysqli_stmt_get_result($stmt3);
$row3 = ezpub_fetch_array($result3);
$grandtotal = $row3["grandtotal"];
mysqli_stmt_close($stmt3);
// alți parametri inițiali
$closed=1; //momentan e factura deschisă
$anulat=0; //momentan nu e anulată

$data = date('Y-m-d', strtotime($dataemiterii));
//verificăm cursul valutar pentru data facturii
  $sql="SELECT * FROM curs_valutar WHERE curs_valutar_zi='$data'";

	$curs=ezpub_query($conn,$sql);
	$rss=ezpub_fetch_array($curs);
	If (!isSet($rss["curs_valutar_valoare"])){
	
 $curs=new CursBNR("https://www.bnr.ro/nbrfxrates.xml");
$cursvalutar=$curs->getExchangeRate("EUR");
	
	// INSERT curs valutar with prepared statement
	$stmt_curs = mysqli_prepare($conn, "INSERT INTO curs_valutar(curs_valutar_zi, curs_valutar_valoare) VALUES (?, ?)");
	mysqli_stmt_bind_param($stmt_curs, "sd", $data, $cursvalutar);
	
	if (!mysqli_stmt_execute($stmt_curs)) {
		die('Error: ' . mysqli_stmt_error($stmt_curs));
	}
	mysqli_stmt_close($stmt_curs);
	}
	else
	{
		 $cursvalutar=$rss["curs_valutar_valoare"];
	}	
// pregătim și executăm query-ul de update factură nouă pentru a adăuga datele
		$clientcui=$_POST["factura_client_RO"]." ".$_POST["factura_client_CIF"];

// UPDATE factura with prepared statement
$stmt_upd_factura = mysqli_prepare($conn, "UPDATE facturare_facturi SET 
	factura_client_ID=?, factura_data_emiterii=?, factura_client_denumire=?, factura_client_CUI=?,
	factura_client_RC=?, factura_client_RO=?, factura_cod_factura=?, factura_client_CIF=?,
	factura_client_adresa=?, factura_client_judet=?, factura_client_localitate=?, factura_client_IBAN=?,
	factura_client_banca=?, factura_client_alocat=?, factura_client_contract=?, factura_client_BU=?,
	factura_client_sales=?, factura_client_an=?, factura_client_termen=?, factura_client_valoare=?,
	factura_client_valoare_tva=?, factura_client_curs_valutar=?, factura_client_valoare_totala=?,
	factura_client_achitat=?, factura_client_inchisa=?, factura_client_anulat=?, factura_client_tip_activitate=?
	WHERE factura_ID=?");

mysqli_stmt_bind_param($stmt_upd_factura, "issssssssssssssssssddddsiisi",
	$Client_ID,
	$dataemiterii,
	$_POST["factura_client_denumire"],
	$clientcui,
	$_POST["factura_client_RC"],
	$_POST["factura_client_RO"],
	$_POST["factura_cod_factura"],
	$_POST["factura_client_CIF"],
	$_POST["factura_client_adresa"],
	$_POST["factura_client_judet"],
	$_POST["factura_client_localitate"],
	$_POST["factura_client_IBAN"],
	$_POST["factura_client_banca"],
	$_POST["factura_client_alocat"],
	$_POST["factura_client_contract"],
	$_POST["factura_client_BU"],
	$_POST["factura_client_sales"],
	$_POST["factura_client_an"],
	$termenfactura,
	$valoareproduse,
	$valoareTVA,
	$cursvalutar,
	$grandtotal,
	$_POST["factura_client_achitat"],
	$closed,
	$anulat,
	$_POST["factura_client_tip_activitate"],
	$cID
);

if (!mysqli_stmt_execute($stmt_upd_factura)) {
	die('Error: ' . mysqli_stmt_error($stmt_upd_factura));
}
mysqli_stmt_close($stmt_upd_factura);

// Continuă cu restul logicii
  //ștergem facturile nefinalizate
$nsql="DELETE FROM facturare_facturi WHERE factura_client_inchisa IS NULL;";
ezpub_query($conn,$nsql);
// verificăm dacă trebuie și chitanță
If (IsSet($_POST['factura_client_achitat']) AND $_POST['factura_client_achitat']=="1"){
//verificăm ultimul număr de chitanță
$query1="Select chitanta_numar FROM facturare_chitante WHERE chitanta_inchisa='1' ORDER BY chitanta_numar DESC";
$result1=ezpub_query($conn,$query1);
$row1=ezpub_fetch_array($result1);
If (!isSet($row1["chitanta_numar"]))
{$numarchitanta=1;}
else
{$numarchitanta=(int)$row1["chitanta_numar"]+1;}
//avem numărul de chitanță
//preluăm datele necesare pentru chitanță și pregătim chitanța
$stmt_fact = mysqli_prepare($conn, "SELECT factura_numar, factura_data_emiterii, factura_client_valoare_totala FROM facturare_facturi WHERE factura_ID=?");
mysqli_stmt_bind_param($stmt_fact, "i", $cID);
mysqli_stmt_execute($stmt_fact);
$result11 = mysqli_stmt_get_result($stmt_fact);
$row11 = ezpub_fetch_array($result11);
mysqli_stmt_close($stmt_fact);
$datachitantei=$row11["factura_data_emiterii"];
$sumaincasata=$row11["factura_client_valoare_totala"];
$facturaincasata=$row11["factura_numar"];
$codenumarfactura=str_pad($facturaincasata, 8, '1', STR_PAD_LEFT);

$descriere = "Contravaloare factură ". $siteInvoicingCode .$codenumarfactura ."/".date("d.m.Y", strtotime($datachitantei));

$stmt_upd = mysqli_prepare($conn, "UPDATE facturare_facturi SET factura_client_data_achitat=?, factura_client_zile_achitat='0', factura_client_achitat_prin='0' WHERE factura_ID=?");
mysqli_stmt_bind_param($stmt_upd, "si", $datachitantei, $cID);
mysqli_stmt_execute($stmt_upd);
mysqli_stmt_close($stmt_upd);
//marcăm factura ca achitată și generăm chitanța
$stmt_chit = mysqli_prepare($conn, "INSERT INTO facturare_chitante(
	chitanta_data_incasarii, chitanta_factura_ID, chitanta_suma_incasata,
	chitanta_inchisa, chitanta_descriere, chitanta_numar
) VALUES (?, ?, ?, ?, ?, ?)");

mysqli_stmt_bind_param($stmt_chit, "sidssi",
	$datachitantei,
	$cID,
	$sumaincasata,
	$chitanta_inchisa = '1',
	$descriere,
	$numarchitanta
);

if (!mysqli_stmt_execute($stmt_chit)) {
	die('Error: ' . mysqli_stmt_error($stmt_chit));
}
mysqli_stmt_close($stmt_chit);
} // sfârșit if factură achitată
echo "<div class=\"callout success\"><p>$strRecordAdded</p>";
$cID_escaped = htmlspecialchars($cID, ENT_QUOTES, 'UTF-8');
echo"			  <div class=\"grid-x grid-margin-x\">
			 <div class=\"large-12 medium-12 small-12 cell\">
			 <p>
			 <a href=\"invoice.php?option=print&type=1&cID={$cID_escaped}\" class=\"button\"><i class=\"fas fa-file-pdf\"></i>&nbsp;$strPrint</a>
			 <a href=\"emailinvoice.php?option=print&type=1&cID={$cID_escaped}\" class=\"button\"><i class=\"far fa-envelope\"></i>&nbsp;$strEmail</a>
			 <a href=\"einvoice.php?cID={$cID_escaped}\" class=\"button\"><i class=\"fas fa-upload\"></i>&nbsp;$strUpload</a>
			 </p>
</div>
</div>";
echo"</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
  window.location = \"siteinvoices.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
} //aici am terminat factura nouă If ($_GET['mode']=="new")
else
{
	// edităm o factură existentă
$dataemiterii = $_POST["data_emiterii"];

// SELECT SUM queries with prepared statements
$stmt1 = mysqli_prepare($conn, "SELECT SUM(articol_valoare) AS valoare_factura FROM facturare_articole_facturi WHERE factura_ID=?");
mysqli_stmt_bind_param($stmt1, "i", $cID);
mysqli_stmt_execute($stmt1);
$result1 = mysqli_stmt_get_result($stmt1);
$row1 = ezpub_fetch_array($result1);
$valoareproduse = $row1["valoare_factura"];
mysqli_stmt_close($stmt1);

$stmt2 = mysqli_prepare($conn, "SELECT SUM(articol_TVA) AS valoare_tva FROM facturare_articole_facturi WHERE factura_ID=?");
mysqli_stmt_bind_param($stmt2, "i", $cID);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
$row2 = ezpub_fetch_array($result2);
$valoareTVA = $row2["valoare_tva"];
mysqli_stmt_close($stmt2);

$stmt3 = mysqli_prepare($conn, "SELECT SUM(articol_total) AS grandtotal FROM facturare_articole_facturi WHERE factura_ID=?");
mysqli_stmt_bind_param($stmt3, "i", $cID);
mysqli_stmt_execute($stmt3);
$result3 = mysqli_stmt_get_result($stmt3);
$row3 = ezpub_fetch_array($result3);
$grandtotal = $row3["grandtotal"];
mysqli_stmt_close($stmt3);

// SELECT Client with prepared statement
if (!isset($_POST['factura_client_denumire'])) {
    die("Missing client name");
}
$stmt_cl = mysqli_prepare($conn, "SELECT ID_Client FROM clienti_date WHERE Client_Denumire=?");
mysqli_stmt_bind_param($stmt_cl, "s", $_POST['factura_client_denumire']);
mysqli_stmt_execute($stmt_cl);
$result = mysqli_stmt_get_result($stmt_cl);
$row = ezpub_fetch_array($result);
$Client_ID = $row["ID_Client"];
mysqli_stmt_close($stmt_cl);
		$clientcui=$_POST["factura_client_RO"]." ".$_POST["factura_client_CIF"];	

// UPDATE factura edit with prepared statement (23 câmpuri)
$stmt_upd_edit = mysqli_prepare($conn, "UPDATE facturare_facturi SET 
	factura_client_denumire=?, factura_data_emiterii=?, factura_client_ID=?, factura_client_CUI=?,
	factura_client_RO=?, factura_client_CIF=?, factura_cod_factura=?, factura_client_RC=?,
	factura_client_adresa=?, factura_client_judet=?, factura_client_localitate=?, factura_client_IBAN=?,
	factura_client_banca=?, factura_client_contract=?, factura_client_alocat=?, factura_client_an=?,
	factura_client_termen=?, factura_client_BU=?, factura_client_sales=?, factura_client_valoare=?,
	factura_client_valoare_tva=?, factura_client_valoare_totala=?, factura_client_pdf=NULL,
	factura_client_pdf_generat=NULL, factura_client_efactura_generata=NULL, factura_client_tip_activitate=?
	WHERE factura_ID=?");

mysqli_stmt_bind_param($stmt_upd_edit, "ssisssssssssssssssdddssi",
	$_POST["factura_client_denumire"],
	$dataemiterii,
	$Client_ID,
	$clientcui,
	$_POST["factura_client_RO"],
	$_POST["factura_client_CIF"],
	$_POST["factura_cod_factura"],
	$_POST["factura_client_RC"],
	$_POST["factura_client_adresa"],
	$_POST["factura_client_judet"],
	$_POST["factura_client_localitate"],
	$_POST["factura_client_IBAN"],
	$_POST["factura_client_banca"],
	$_POST["factura_client_contract"],
	$_POST["factura_client_alocat"],
	$_POST["factura_client_an"],
	$_POST["factura_client_termen"],
	$_POST["factura_client_BU"],
	$_POST["factura_client_sales"],
	$valoareproduse,
	$valoareTVA,
	$grandtotal,
	$_POST["factura_client_tip_activitate"],
	$cID
);

if (!mysqli_stmt_execute($stmt_upd_edit)) {
	die('Error: ' . mysqli_stmt_error($stmt_upd_edit));
}
mysqli_stmt_close($stmt_upd_edit);

  //ștergem efactura creată dacă există pentru că vom genera alta
	$stmt_del = mysqli_prepare($conn, "DELETE FROM efactura WHERE factura_ID=?");
	mysqli_stmt_bind_param($stmt_del, "i", $cID);
	mysqli_stmt_execute($stmt_del);
	mysqli_stmt_close($stmt_del);
echo "<div class=\"callout success\"><p>$strRecordModified</p>";
$cID_escaped = htmlspecialchars($cID, ENT_QUOTES, 'UTF-8');
echo"			  <div class=\"grid-x grid-margin-x\">
			 <div class=\"large-12 medium-12 small-12 cell\">
			 <p>
			 <a href=\"invoice.php?option=print&type=1&cID={$cID_escaped}\" class=\"button\"><i class=\"fas fa-file-pdf\"></i>&nbsp;$strPrint</a>
			 <a href=\"emailinvoice.php?option=print&type=1&cID={$cID_escaped}\" class=\"button\"><i class=\"far fa-envelope\"></i>&nbsp;$strEmail</a>
			  <a href=\"einvoice.php?cID={$cID_escaped}\" class=\"button\"><i class=\"fas fa-upload\"></i>&nbsp;$strUpload</a>
			 </p>
</div>
</div>";

echo"</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
  window.location = \"siteinvoices.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
} // sfârșit editare factură existentă
} // sfârșit if POST
else {
// avem o factură nou-nouță
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
	If (IsSet($_GET['proforma']) AND $_GET['proforma']=="0")
	{
		$tipfactura='0';
	}
	else
		{
			$tipfactura='1';
	}	
  //verificăm ultimul număr de factură
$query="Select factura_numar FROM facturare_facturi WHERE factura_client_inchisa='1' AND factura_tip='0' ORDER BY CAST(factura_numar AS unsigned) DESC";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
If (!isSet($row["factura_numar"]))
{$numarfactura=1;}
else
{$numarfactura=(int)$row["factura_numar"]+1;}
//inserăm factura nouă cu date minime
$mSQL = "INSERT INTO facturare_facturi(";
	$mSQL = $mSQL . "factura_tip,";
	$mSQL = $mSQL . "factura_numar)";
	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" . $tipfactura . "', ";
	$mSQL = $mSQL . "'" .$numarfactura . "') ";		
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
 {
 die('Error: ' . ezpub_error($conn));
 }
else{
	$invoiceID=ezpub_inserted_id($conn);
}
//avem ID-ul noii facturi, continuăm cu formularul pentru completare date
?>
    <script type="text/javascript">
      document.addEventListener('DOMContentLoaded', function() {
        // AJAX pentru CUI (ANAF)
        const btn1 = document.getElementById('btn1');
        if (btn1) {
          btn1.addEventListener('click', function() {
            const cuiInput = document.getElementById('Cui');
            const cuiValue = cuiInput ? cuiInput.value : '';
            
            fetch('../common/cui.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: 'Cui=' + encodeURIComponent(cuiValue)
            })
            .then(response => {
              if (!response.ok) {
                throw new Error('Network response was not ok');
              }
              return response.json();
            })
            .then(data => {
              try {
                const denumireEl = document.getElementById('factura_client_denumire');
                const cifEl = document.getElementById('factura_client_CIF');
                const roEl = document.getElementById('factura_client_RO');
                const adresaEl = document.getElementById('factura_client_adresa');
                const judetEl = document.getElementById('factura_client_judet');
                const localitateEl = document.getElementById('factura_client_localitate');
                const rcEl = document.getElementById('factura_client_RC');
                const nouEl = document.getElementById('nou');
                const loaderIcon = document.getElementById('loaderIcon');
                
                if (denumireEl) denumireEl.value = (data.denumire || "").toUpperCase();
                if (cifEl) cifEl.value = data.cif || '';
                if (roEl) roEl.value = data.tva || '';
                if (adresaEl) adresaEl.value = data.adresa || '';
                if (judetEl) judetEl.value = (data.judet || '').toUpperCase();
                if (localitateEl) localitateEl.value = (data.oras || '').toUpperCase();
                if (rcEl) rcEl.value = data.numar_reg_com || '';
                if (nouEl) nouEl.checked = true;
                if (loaderIcon) loaderIcon.style.display = 'none';
              } catch(err) {
                const responseEl = document.getElementById('response');
                if (responseEl) {
                  responseEl.innerHTML = err.message;
                }
              }
            })
            .catch(error => {
              alert('Nu se poate face legătura cu serverul ANAF!');
            });
          });
        }
        
        // AJAX pentru selectare client
        const clientSelect = document.querySelector('select[name="factura_client_ID"]');
        if (clientSelect) {
          clientSelect.addEventListener('change', function() {
            const clientID = this.value;
            
            fetch('invoiceclient.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: 'Client_ID=' + encodeURIComponent(clientID)
            })
            .then(response => {
              if (!response.ok) {
                throw new Error('Network response was not ok');
              }
              return response.json();
            })
            .then(data => {
              try {
                const denumireEl = document.getElementById('factura_client_denumire');
                const cifEl = document.getElementById('factura_client_CIF');
                const roEl = document.getElementById('factura_client_RO');
                const adresaEl = document.getElementById('factura_client_adresa');
                const judetEl = document.getElementById('factura_client_judet');
                const localitateEl = document.getElementById('factura_client_localitate');
                const bancaEl = document.getElementById('factura_client_banca');
                const ibanEl = document.getElementById('factura_client_IBAN');
                const rcEl = document.getElementById('factura_client_RC');
                
                if (denumireEl) denumireEl.value = (data.denumire || "").toUpperCase();
                if (cifEl) cifEl.value = data.cif || '';
                if (roEl) roEl.value = data.tva || '';
                if (adresaEl) adresaEl.value = data.adresa || '';
                if (judetEl) judetEl.value = (data.judet || '').toUpperCase();
                if (localitateEl) localitateEl.value = (data.localitate || '').toUpperCase();
                if (bancaEl) bancaEl.value = data.banca || '';
                if (ibanEl) ibanEl.value = data.iban || '';
                if (rcEl) rcEl.value = data.numar_reg_com || '';
              } catch(err) {
                const responseEl = document.getElementById('response');
                if (responseEl) {
                  responseEl.innerHTML = err.message;
                }
              }
            })
            .catch(error => {
              alert('Clientul nu a fost găsit!');
            });
          });
        }
      });
    </script>
    <div class="grid-x grid-padding-x ">
      <div class="large-4 medium-4 cell">
        <label><?php echo $strClient?></label>
          <select name="factura_client_ID" onClick="document.getElementById('existent').checked=true;">
            <option value=""><?php echo $strClient?></option>
            <?php $sql = "Select * FROM clienti_date ORDER BY Client_Denumire ASC";
    $result=ezpub_query($conn,$sql);
	  while ($rss=ezpub_fetch_array($result)){
	?>
            <option value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
            <?php
}?>
          </select>
      </div>
      <div class="large-3 medium-3 cell">
        <label><?php echo $strCompanyVAT?></label>
          <div id="response"></div>
          <div class="input-group">
            <span class="input-group-label"><?php echo $strCompanyVAT?></span>
            <input class="input-group-field" type="text" name="Cui" id="Cui"
              placeholder="<?php echo $strEnterVATNumber?>">
            <div class="input-group-button">
              <button id="btn1" class="button success"><i
                  class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
            </div>
          </div>
      </div>
      <form method="post" id="users" Action="siteinvoices.php?mode=new&cID=<?php echo $invoiceID?>">
      <div class="large-3 medium-3 cell">
          <label><?php echo $strType?></label>
            <input type="radio" name="existent" value="1" id="existent">
            <label for="existent"><?php echo $strExistingClient?></label>
            <input type="radio" name="existent" value="0" id="nou">
            <label for="nou"><?php echo $strNewClient?></label>
        </div>
   <form method="post" id="users" Action="siteinvoices.php?mode=new&cID=<?php echo $invoiceID?>">
        <div class="large-2 medium-2 cell">
          <label><?php echo $strReceipt?></label>
            <input type="radio" name="factura_client_achitat" value="0" checked id="chitanta"><label
              for="chitanta"><?php echo $strNo?></label><input name="factura_client_achitat" type="radio"
              value="1" id="banca"><label for="banca"><?php echo $strYes?></label>
       </div>
    </div>
      <div class="grid-x grid-padding-x ">
         <div class="large-2 medium-2 cell">
          <label><?php echo $strNumber?></label>
            <input name="factura_numar" type="text" value="<?php echo $siteInvoicingCode . "0000".$numarfactura?>" />
        </div>
        <div class="large-2 medium-2 cell"></label>
          <label><?php echo $strDate?>
            <input type="date" name="data_emiterii" value="<?php echo date('Y-m-d')?>" required />
        </div>
        <div class="large-1 medium-1 cell">
          <label><?php echo $strCode?></label>
            <input name="factura_cod_factura" type="text" value="380" />
        </div>
         <div class="large-2 medium-2 cell">
          <label><?php echo $strClient?></label>
            <input type="text" name="factura_client_denumire" id="factura_client_denumire" value="" />
        </div>
        <div class="large-1 medium-1 small-1 cell">
          <label><?php echo $strCompanyFA?></label>
            <input type="text" name="factura_client_RO" id="factura_client_RO" value="" />
        </div>
        <div class="large-2 medium-2 cell">
          <label><?php echo $strCompanyVAT?></label>
            <input type="text" name="factura_client_CIF" id="factura_client_CIF" value="" />
        </div>
        <div class="large-2 medium-2  cell">
          <label><?php echo $strCompanyRC?></label>
            <input type="text" name="factura_client_RC" id="factura_client_RC" value="" />
        </div>
      </div>
       <div class="grid-x grid-padding-x ">
        <div class="large-4 medium-4 cell">
          <label><?php echo $strAddress?></label>
            <input type="text" name="factura_client_adresa" id="factura_client_adresa" value="" />
        </div>
        <div class="large-2 medium-2 cell">
          <label><?php echo $strCity?></label>
            <input type="text" name="factura_client_localitate" id="factura_client_localitate" value="" />
        </div>
        <div class="large-2 medium-2 cell">
          <label><?php echo $strCounty?></label>
            <input type="text" name="factura_client_judet" id="factura_client_judet" value="" />
        </div>
        <div class="large-2 medium-2 cell">
          <label><?php echo $strBank?></label>
            <input type="text" name="factura_client_banca" id="factura_client_banca" value="" />
        </div>
        <div class="large-2 medium-2 cell">
          <label><?php echo $strCompanyIBAN?></label>
            <input type="text" name="factura_client_IBAN" id="factura_client_IBAN" value="" />
        </div>
      </div>
      <div class="grid-x grid-padding-x ">
        <div class="large-3 medium-3 cell">
          <label><?php echo $strContractType?></label>
            <input type="radio" name="factura_client_tip_activitate" value="M" id="lunar">
            <label for="lunar"><?php echo $strSubscribtion?></label>
            <input type="radio" name="factura_client_tip_activitate" value="O" id="onetime" checked>
            <label for="onetime"><?php echo $strOneTimeJob?></label>
        </div>
        <div class="large-1 medium-1 cell">
          <label><?php echo $strContract?></label>
            <input name="factura_client_contract" type="text" value="" />
        </div>
        <div class="large-1 medium-1 small-1 cell">
          <label><?php echo $strYear?></label>
            <input type="text" name="factura_client_an" id="factura_client_an" value="" />
        </div>
        <div class="large-1 medium-1 cell">
          <label><?php echo $strDeadline?></label>
            <input name="factura_client_termen" type="text" value="10" />
        </div>
  <div class="large-2 medium-2 cell">
          <label><?php echo $strSeenBy?></label>
            <select name="factura_client_alocat" class="required">
              <option value=""><?php echo $strUser?></option>
              <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
    $result=ezpub_query($conn,$sql);
	  while ($rss=ezpub_fetch_array($result)){
	?>
              <option value="<?php echo $rss["utilizator_Code"]?>"><?php echo $rss["utilizator_Prenume"]?>
                <?php echo $rss["utilizator_Nume"]?></option>
              <?php
}?>
            </select>
        </div>
        <div class="large-2 medium-2 cell">
          <label><?php echo $strSales?></label>
            <select name="factura_client_sales" class="required">
              <option value=""><?php echo $strUser?></option>
              <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
    $result=ezpub_query($conn,$sql);
	  while ($rss=ezpub_fetch_array($result)){
	?>
              <option value="<?php echo $rss["utilizator_Code"]?>"><?php echo $rss["utilizator_Prenume"]?>
                <?php echo $rss["utilizator_Nume"]?></option>
              <?php
}?>
            </select>
        </div>
          <div class="large-2 medium-2 cell">
          <label><?php echo $strBusinessUnit?></label>
            <select name="factura_client_BU">
              <?php
			 			$query7="SELECT DISTINCT factura_client_BU FROM facturare_facturi ORDER By factura_client_BU ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
echo"<option value=\"$seenby[factura_client_BU]\">". $seenby['factura_client_BU']."</option>";
			}
		?></select>
        </div>
         </div>      
      <div class="grid-x grid-padding-x">
        <div class="large-12 cell">
          <iframe width="100%" height="600" src="siteinvoiceitems.php?valuta=lei&cID=<?php echo $invoiceID?>"
            frameBorder="0" scrolling="no" onload="resizeIframe(this)" id="lei"></iframe>
        </div>
      </div>
      <div class="grid-x grid-padding-x ">
        <div class="large-12 text-center cell">
          <input type="submit" value="<?php echo $strAdd?>" class="button" name="Submit">
        </div>
      </div>

    </form>
    <?php
} // we edit an invoice
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
    // Validate mode
    if (!in_array($_GET['mode'], ['delete', 'new', 'edit'])) {
        die("Invalid mode parameter");
    }
    
    // Validate cID
    if (!isset($_GET['cID']) || !filter_var($_GET['cID'], FILTER_VALIDATE_INT)) {
        die("Invalid invoice ID");
    }
    $cID = (int)$_GET['cID'];
    
    // SELECT with prepared statement
    $stmt = mysqli_prepare($conn, "SELECT * FROM facturare_facturi WHERE factura_ID=?");
    mysqli_stmt_bind_param($stmt, "i", $cID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = ezpub_fetch_array($result);
    mysqli_stmt_close($stmt);
?>

    <script type="text/javascript">
      document.addEventListener('DOMContentLoaded', function() {
        // AJAX pentru CUI (ANAF)
        const btn1 = document.getElementById('btn1');
        if (btn1) {
          btn1.addEventListener('click', function() {
            const cuiInput = document.getElementById('Cui');
            const cuiValue = cuiInput ? cuiInput.value : '';
            
            fetch('../common/cui.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: 'Cui=' + encodeURIComponent(cuiValue)
            })
            .then(response => {
              if (!response.ok) {
                throw new Error('Network response was not ok');
              }
              return response.json();
            })
            .then(data => {
              try {
                const denumireEl = document.getElementById('factura_client_denumire');
                const cifEl = document.getElementById('factura_client_CIF');
                const roEl = document.getElementById('factura_client_RO');
                const adresaEl = document.getElementById('factura_client_adresa');
                const judetEl = document.getElementById('factura_client_judet');
                const rcEl = document.getElementById('factura_client_RC');
                const loaderIcon = document.getElementById('loaderIcon');
                
                if (denumireEl) denumireEl.value = (data.denumire || "").toUpperCase();
                if (cifEl) cifEl.value = data.cif || '';
                if (roEl) roEl.value = data.tva || '';
                if (adresaEl) adresaEl.value = data.adresa || '';
                if (judetEl) judetEl.value = (data.judet || '').toUpperCase();
                if (rcEl) rcEl.value = data.numar_reg_com || '';
                if (loaderIcon) loaderIcon.style.display = 'none';
              } catch(err) {
                const responseEl = document.getElementById('response');
                if (responseEl) {
                  responseEl.innerHTML = err.message;
                }
              }
            })
            .catch(error => {
              alert('Nu se poate face legătura cu serverul ANAF!');
            });
          });
        }
        
        // AJAX pentru selectare client
        const clientSelect = document.querySelector('select[name="factura_client_ID"]');
        if (clientSelect) {
          clientSelect.addEventListener('change', function() {
            const clientID = this.value;
            
            fetch('invoiceclient.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: 'Client_ID=' + encodeURIComponent(clientID)
            })
            .then(response => {
              if (!response.ok) {
                throw new Error('Network response was not ok');
              }
              return response.json();
            })
            .then(data => {
              try {
                const denumireEl = document.getElementById('factura_client_denumire');
                const cifEl = document.getElementById('factura_client_CIF');
                const roEl = document.getElementById('factura_client_RO');
                const adresaEl = document.getElementById('factura_client_adresa');
                const judetEl = document.getElementById('factura_client_judet');
                const localitateEl = document.getElementById('factura_client_localitate');
                const bancaEl = document.getElementById('factura_client_banca');
                const ibanEl = document.getElementById('factura_client_IBAN');
                const rcEl = document.getElementById('factura_client_RC');
                
                if (denumireEl) denumireEl.value = (data.denumire || "").toUpperCase();
                if (cifEl) cifEl.value = data.cif || '';
                if (roEl) roEl.value = data.tva || '';
                if (adresaEl) adresaEl.value = data.adresa || '';
                if (judetEl) judetEl.value = (data.judet || '').toUpperCase();
                if (localitateEl) localitateEl.value = (data.localitate || '').toUpperCase();
                if (bancaEl) bancaEl.value = data.banca || '';
                if (ibanEl) ibanEl.value = data.iban || '';
                if (rcEl) rcEl.value = data.numar_reg_com || '';
              } catch(err) {
                const responseEl = document.getElementById('response');
                if (responseEl) {
                  responseEl.innerHTML = err.message;
                }
              }
            })
            .catch(error => {
              alert('Clientul nu a fost găsit!');
            });
          });
        }
      });
    </script>
</script>
      <div class="grid-x grid-padding-x ">
      <div class="large-6 medium-6 cell">
        <label><?php echo $strClient?></label>
          <select name="factura_client_ID" onClick="document.getElementById('existent').checked=true;">
            <option value=""><?php echo $strClient?></option>
            <?php $sql = "Select * FROM clienti_date ORDER BY Client_Denumire ASC";
    $result=ezpub_query($conn,$sql);
	  while ($rss=ezpub_fetch_array($result)){
	?>
            <option value="<?php echo $rss["ID_Client"]?>" <?php if ($rss['ID_Client'] == $row['factura_client_ID']) echo 'selected'; ?>><?php echo $rss["Client_Denumire"]?></option>
            <?php
}?>
          </select>
      </div>
    </div>
    <form method="post" action="siteinvoices.php?mode=edit&cID=<?php echo htmlspecialchars($cID, ENT_QUOTES, 'UTF-8')?>">
      <div class="grid-x grid-padding-x ">
         <div class="large-2 medium-2 cell">
          <label><?php echo $strNumber?></label>
          <?php 
           $codenumarfactura=str_pad($row["factura_numar"], 8, '0', STR_PAD_LEFT);
          ?>
            <input name="factura_numar" type="text" value="<?php echo $siteInvoicingCode .$codenumarfactura?>" readonly/>
        </div>
        <div class="large-2 medium-2 cell"></label>
          <label><?php echo $strDate?>
            <input type="date" name="data_emiterii" value="<?php echo $row['factura_data_emiterii']?>" required />
        </div>
        <div class="large-1 medium-1 cell">
          <label><?php echo $strCode?></label>
            <input name="factura_cod_factura" type="text" value="<?php echo $row['factura_cod_factura']?>" />
        </div>
         <div class="large-2 medium-2 cell">
          <label><?php echo $strClient?></label>
            <input type="text" name="factura_client_denumire" id="factura_client_denumire" value="<?php echo $row['factura_client_denumire']?>" />
        </div>
        <div class="large-1 medium-1 small-1 cell">
          <label><?php echo $strCompanyFA?></label>
            <input type="text" name="factura_client_RO" id="factura_client_RO" value="<?php echo $row['factura_client_RO']?>" />
        </div>
        <div class="large-2 medium-2 cell">
          <label><?php echo $strCompanyVAT?></label>
            <input type="text" name="factura_client_CIF" id="factura_client_CIF" value="<?php echo $row['factura_client_CIF']?>" />
        </div>
        <div class="large-2 medium-2  cell">
          <label><?php echo $strCompanyRC?></label>
            <input type="text" name="factura_client_RC" id="factura_client_RC" value="<?php echo $row['factura_client_RC']?>" />
        </div>
      </div>
       <div class="grid-x grid-padding-x ">
        <div class="large-4 medium-4 cell">
          <label><?php echo $strAddress?></label>
            <input type="text" name="factura_client_adresa" id="factura_client_adresa" value="<?php echo $row['factura_client_adresa']?>" />
        </div>
        <div class="large-2 medium-2 cell">
          <label><?php echo $strCity?></label>
            <input type="text" name="factura_client_localitate" id="factura_client_localitate" value="<?php echo $row['factura_client_localitate']?>" />
        </div>
        <div class="large-2 medium-2 cell">
          <label><?php echo $strCounty?></label>
            <input type="text" name="factura_client_judet" id="factura_client_judet" value="<?php echo $row['factura_client_judet']?>" />
        </div>
        <div class="large-2 medium-2 cell">
          <label><?php echo $strBank?></label>
            <input type="text" name="factura_client_banca" id="factura_client_banca" value="<?php echo $row['factura_client_banca']?>" />
        </div>
        <div class="large-2 medium-2 cell">
          <label><?php echo $strCompanyIBAN?></label>
            <input type="text" name="factura_client_IBAN" id="factura_client_IBAN" value="<?php echo $row['factura_client_IBAN']?>" />
        </div>
      </div>
      <div class="grid-x grid-padding-x ">
        <div class="large-3 medium-3 cell">
          <label><?php echo $strContractType?></label>
            <input type="radio" name="factura_client_tip_activitate" value="M" id="lunar" <?php if ($row['factura_client_tip_activitate']=='M') echo 'checked';?>>
            <label for="lunar"><?php echo $strSubscribtion?></label>
            <input type="radio" name="factura_client_tip_activitate" value="O" id="onetime" <?php if ($row['factura_client_tip_activitate']=='O') echo 'checked';?>>
            <label for="onetime"><?php echo $strOneTimeJob?></label>
        </div>
        <div class="large-1 medium-1 cell">
          <label><?php echo $strContract?></label>
            <input name="factura_client_contract" type="text" value="<?php echo $row['factura_client_contract']?>" />
        </div>
        <div class="large-1 medium-1 small-1 cell">
          <label><?php echo $strYear?></label>
            <input type="text" name="factura_client_an" id="factura_client_an" value="<?php echo $row['factura_client_an']?>" />
        </div>
        <div class="large-1 medium-1 cell">
          <label><?php echo $strDeadline?></label>
            <input name="factura_client_termen" type="text" value="<?php echo $row['factura_client_termen']?>" />
        </div>
  <div class="large-2 medium-2 cell">
          <label><?php echo $strSeenBy?></label>
            <select name="factura_client_alocat" class="required">
              <option value=""><?php echo $strUser?></option>
              <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
    $result=ezpub_query($conn,$sql);
	  while ($rss=ezpub_fetch_array($result)){
	?>
              <option value="<?php echo $rss["utilizator_Code"]?>" <?php if ($row['factura_client_alocat'] == $rss["utilizator_Code"]) echo 'selected'; ?>><?php echo $rss["utilizator_Prenume"]?>
                <?php echo $rss["utilizator_Nume"]?></option>
              <?php
}?>
            </select>
        </div>
        <div class="large-2 medium-2 cell">
          <label><?php echo $strSales?></label>
            <select name="factura_client_sales" class="required">
              <option value=""><?php echo $strUser?></option>
              <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
    $result=ezpub_query($conn,$sql);
	  while ($rss=ezpub_fetch_array($result)){
	?>
              <option value="<?php echo $rss["utilizator_Code"]?>" <?php if ($row['factura_client_sales'] == $rss["utilizator_Code"]) echo 'selected'; ?>><?php echo $rss["utilizator_Prenume"]?>
                <?php echo $rss["utilizator_Nume"]?></option>
              <?php
}?>
            </select>
        </div>
          <div class="large-2 medium-2 cell">
          <label><?php echo $strBusinessUnit?></label>
            <select name="factura_client_BU">
              <?php
			 			$query7="SELECT DISTINCT factura_client_BU FROM facturare_facturi ORDER By factura_client_BU ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
echo"<option value=\"$seenby[factura_client_BU]\" ";
if ($row['factura_client_BU'] == $seenby['factura_client_BU']) echo 'selected';
echo ">". $seenby['factura_client_BU']."</option>";
			}
		?></select>
        </div>
         </div>  

      <div class="grid-x grid-padding-x ">
        <div class="large-12 cell">
          <iframe name="articole" width="100%" height="400px"
            src="siteinvoiceitems.php?cID=<?php echo htmlspecialchars($cID, ENT_QUOTES, 'UTF-8')?>" frameBorder="0" scrolling="no" onload="resizeIframe(this)"></iframe>
        </div>
      </div>
      <div class="grid-x grid-padding-x ">
        <div class="large-12 text-center cell">
          <input type="submit" value="<?php echo $strModify?>" class="button" name="Submit" class="button success">
        </div>
      </div>
    </form>
    <?php
}
else // display invoices
{
?>
    <script language="JavaScript" type="text/JavaScript">
      <!-- jump menu
function MM_jumpMenu(targ,selObj,restore){ //v3.0
 eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
 if (restore) selObj.selectedIndex=0;
}
//-->
    </script>
    <div class="grid-x grid-padding-x ">
      <div class="large-2 medium-2 cell">
        <label> <?php echo $strSeenBy?>
          <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
            <option
              value="siteinvoices.php?act=<?php echo $act?>&cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&paid=<?php echo $paid?>&aloc=<?php echo $aloc ?>"
              selected><?php echo $strPick?></option>
            <?php
			$query7="SELECT * FROM date_utilizatori ORDER By utilizator_Nume ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			if ((isset($_GET['aloc'])) && !empty($_GET['aloc'])){
			If ($seenby['strSeenBy']==$_GET['aloc']) {
			echo"<option selected value=\"siteinvoices.php?act=$act&cl=$cl&fmonth=$fmonth&yr=$year&paid=$paid&aloc=".$seenby['utilizator_Code']."\">". $seenby['strUserName']."</option>";}
			else{echo"<option value=\"siteinvoices.php?act=$act&cl=$cl&fmonth=$fmonth&yr=$year&paid=$paid&aloc=".$seenby['utilizator_Code']."\">". $seenby['utilizator_Nume']." ". $seenby['utilizator_Prenume']."</option>";}}
			else {echo"<option value=\"siteinvoices.php?act=$act&cl=$cl&fmonth=$fmonth&yr=$year&paid=$paid&aloc=".$seenby['utilizator_Code']."\">". $seenby['utilizator_Nume']." ". $seenby['utilizator_Prenume']."</option>";}
			}
			?>
          </select></label>
      </div>
      <div class="large-2 medium-2 cell">
        <label> <?php echo $strClient?>
          <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
            <option
              value="siteinvoices.php?act=<?php echo $act?>&cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&paid=<?php echo $paid?>&aloc=<?php echo $aloc ?>"
              selected><?php echo $strPick?></option>
            <?php
			$query7="SELECT DISTINCT factura_client_denumire, factura_client_ID FROM facturare_facturi ORDER By factura_client_denumire ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			if ((isset($_GET['cl'])) && !empty($_GET['cl'])){
			If ($seenby['factura_client_ID']==$_GET['cl']) {
			echo"<option selected value=\"siteinvoices.php?act=$act&aloc=$aloc&fmonth=$fmonth&yr=$year&paid=$paid&cl=".$seenby['factura_client_ID']."\">". $seenby['factura_client_denumire']."</option>";}
			else{echo"<option value=\"siteinvoices.php?act=$act&aloc=$aloc&fmonth=$fmonth&yr=$year&paid=$paid&cl=".$seenby['factura_client_ID']."\">". $seenby['factura_client_denumire']."</option>";}}
			else {echo"<option value=\"siteinvoices.php?act=$act&aloc=$aloc&fmonth=$fmonth&yr=$year&paid=$paid&cl=".$seenby['factura_client_ID']."\">". $seenby['factura_client_denumire']."</option>";}
			}
			?>
          </select></label>
      </div>
      <div class="large-2 medium-2 cell">
        <label> <?php echo $strMonth?>
          <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
            <option value="00" selected>--</option>
            <?php for ( $m = 1; $m <= 12; $m ++) {

   		//Create an option With the numeric value of the month
			$dateObj  = DateTime::createFromFormat('!m', $m);
		$formatter = new IntlDateFormatter("ro_RO",
                  IntlDateFormatter::FULL, 
                  IntlDateFormatter::FULL, 
                  'Europe/Bucharest', 
                  IntlDateFormatter::GREGORIAN,
                  'MMMM');
                $monthname = $formatter->format($dateObj);
				echo "<option value=\"siteinvoices.php?act=$act&aloc=$aloc&cl=$cl&yr=$year&paid=$paid&fmonth=".$m."\">$monthname</option>";}
				 
			?>
          </select> </label>
      </div>
      <div class="large-2 medium-2 cell">
        <label> <?php echo $strYear?>
          <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
            <option
              value="siteinvoices.php?act=<?php echo $act?>&cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&paid=<?php echo $paid?>&aloc=<?php echo $aloc ?>"
              selected><?php echo $strPick?></option>
            <?php
			 			$query7="SELECT DISTINCT YEAR(factura_data_emiterii) as iyear FROM facturare_facturi ORDER By YEAR(factura_data_emiterii) DESC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
						if ((isset($_GET['yr'])) && !empty($_GET['yr'])){
			If ($seenby['iyear']==$_GET['yr']) {
			echo"<option selected value=\"siteinvoices.php?act=$act&aloc=$aloc&cl$cl&fmonth=$fmonth&paid=$paid&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}
			else{echo"<option value=\"siteinvoices.php?act=$act&aloc=$aloc&cl=$cl&paid=$paid&fmonth=$fmonth&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}}
			else {
			if ($year==$seenby['iyear']) 
			{echo "<option value=\"siteinvoices.php?act=$act&aloc=$aloc&cl=$cl&paid=$paid&fmonth=$fmonth&yr=".$seenby['iyear']." \" selected >". $seenby['iyear']."</option>";}
			else {echo"<option value=\"siteinvoices.php?act=$act&aloc=$aloc&cl=$cl&paid=$paid&fmonth=$fmonth&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}
			}
			}
			 ?>
          </select></label>
      </div>
      <div class="large-2 medium-2 cell">
        <label> <?php echo $strPaid?>
          <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
            <option
              value="siteinvoices.php?act=<?php echo $act?>&cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&paid=<?php echo $paid?>&aloc=<?php echo $aloc ?>"
              selected><?php echo $strPick?></option>
            <?php
							echo"<option value=\"siteinvoices.php?act=$act&aloc=$aloc&cl=$cl&fmonth=$fmonth&yr=$year&paid=1\">$strYes</option>";
							echo"<option value=\"siteinvoices.php?act=$act&aloc=$aloc&cl=$cl&fmonth=$fmonth&yr=$year&paid=0\">$strNo</option>";
							
							
		?>
          </select></label>
      </div>
      <div class="large-2 medium-2 cell">
        <label> <?php echo $strBusinessUnit?>
          <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
            <option
              value="siteinvoices.php?act=<?php echo $act?>&cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&paid=<?php echo $paid?>&aloc=<?php echo $aloc ?>"
              selected><?php echo $strPick?></option>
            <?php
			 			$query7="SELECT DISTINCT factura_client_BU FROM facturare_facturi ORDER By factura_client_BU ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
						if ((isset($_GET['act'])) && !empty($_GET['act'])){
			If ($seenby['factura_client_BU']==$_GET['act']) {
			echo"<option selected value=\"siteinvoices.php?aloc=$aloc&cl=$cl&fmonth=$fmonth&paid=$paid&yr=$year&act=".$seenby['factura_client_BU']."\">". $seenby['factura_client_BU']."</option>";}
			else{echo"<option value=\"siteinvoices.php?aloc=$aloc&cl=$cl&paid=$paid&fmonth=$fmonth&yr=$year&act=".$seenby['factura_client_BU']."\">". $seenby['factura_client_BU']."</option>";}}
			else {echo"<option value=\"siteinvoices.php?aloc=$aloc&cl=$cl&paid=$paid&fmonth=$fmonth&yr=$year&act=".$seenby['factura_client_BU']."\">". $seenby['factura_client_BU']."</option>";}
			}
		?>
          </select></label>
      </div>
    </div>

    <?php
echo " <div class=\"grid-x grid-margin-x\">
   <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"siteinvoices.php?mode=new&proforma=0\" class=\"button\"><i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i>$strNewInvoice</a>
<a href=\"siteinvoices.php?mode=new&proforma=1\" class=\"button\"><i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i>$strNewProforma</a>
</div></div>";
echo "<div class=\"grid-x grid-margin-x\">
   <div class=\"large-12 medium-12 small-12 cell\">";
	 ?>
    <ul class="tabs" data-deep-link="true" data-update-history="true" data-deep-link-smudge="true"
      data-deep-link-smudge-delay="500" data-tabs id="invoices">
      <li class="tabs-title is-active"><a href="siteinvoices.php#panel1"
          aria-selected="true"><?php echo $strInvoices?></a></li>
      <li class="tabs-title"><a href="siteinvoices.php#panel2"><?php echo $strProformas?></a></li>
    </ul>
    <div class="tabs-content" data-tabs-content="invoices">
      <div class="tabs-panel is-active" id="panel1">
        <?php
$query="SELECT *FROM facturare_facturi
WHERE YEAR(factura_data_emiterii)='$year' AND factura_tip='0'";
if ($aloc!='0'){
$query= $query . " AND factura_client_alocat='$aloc'";
};
if ($act!='0'){
$query= $query . " AND factura_client_BU='$act'";
};
if ($cl!='0'){
$query= $query . " AND factura_client_ID='$cl'";
};
if ($fmonth!='0'){
$query= $query . " AND MONTH(factura_data_emiterii)='$fmonth'";
};
if ($paid!='3'){
$query= $query . " AND factura_client_achitat='$paid'";
};
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination; 
$pages->items_total = $numar; 
$pages->mid_range = 5; 
$pages->paginate(); 
$query= $query . " ORDER BY cast(factura_numar as unsigned) ASC $pages->limit";
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
$queryp="SELECT COUNT(factura_client_achitat) AS neplatite FROM facturare_facturi WHERE factura_client_achitat=0 AND factura_tip=0;"; 
$resultp=ezpub_query($conn,$queryp);
$rowp=ezpub_fetch_array($resultp);
$unpaid=$rowp["neplatite"];
echo $strTotal . " " .$numar." ".$strInvoices ." / ". $unpaid ." ". $strUnpayed;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"siteinvoices.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
        </div>
        <table width="100%" class="unstriped">
          <thead>
            <tr>
              <th><?php echo $strNumber?></th>
              <th><?php echo $strIssuedDate?></th>
              <th><?php echo $strClient?></th>
              <th><?php echo $strTotal?></th>
              <th><?php echo $strValue?></th>
              <th><?php echo $strVAT?></th>
              <th><?php echo $strPaymentDate?></th>
              <th><?php echo $strDays?></th>
              <th><?php echo $strSeenBy?></th>
              <th><?php echo $strEdit?></th>
              <th><?php echo $strCancel?></th>
              <th><?php echo $strDetails?></th>
              <th><?php echo $strView?></th>
              <th><?php echo $strCashin?></th>
              <th><?php echo $strEmail?></th>
              <th><?php echo $strXML?></th>
              <th><?php echo $strUploaded?></th>
            </tr>
          </thead>
          <?php 
While ($row=ezpub_fetch_array($result)){
		If ($row["factura_client_anulat"]=="1") 
	{echo "<tr class=\"canceled\">";}
else
{
	If ($row["factura_client_achitat"]=="0") 
	{
echo "<tr class=\"notpaid\">";
} 
else 
{
echo "<tr class=\"paid\">";}
}
  		echo"<td>$row[factura_numar]</td>
			<td>". date("d.m.Y",strtotime($row["factura_data_emiterii"]))."</td>
			<td width=\"15%\">$row[factura_client_denumire]</td>
			<td align=\"right\">". romanize($row["factura_client_valoare_totala"])."</td>
			<td align=\"right\">". romanize($row["factura_client_valoare"])."</td>
			<td align=\"right\">". romanize($row["factura_client_valoare_tva"])."</td>";
If ($row["factura_client_achitat"]=="1") {
	echo "<td>". date("d.m.Y", strtotime($row["factura_client_data_achitat"]))."</td>";
}	
else 	{
	echo "<td>&nbsp;</td>";
}	
	echo	"<td>$row[factura_client_zile_achitat]</td>
			<td>$row[factura_client_alocat]</td>";
If ($row["factura_client_achitat"]=="0" AND $row["factura_client_anulat"]=="0") {
echo "<td><a href=\"siteinvoices.php?mode=edit&cID=$row[factura_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></a></td>";}
else {
	echo "<td><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></td>";}
If ($row["factura_client_anulat"]=="0") 
{
echo "<td><a href=\"invoice.php?type=1&option=print&cID=$row[factura_ID]&action=cancel\" ><i class=\"large fas fa-ban fa-xl\" title=\"$strCancel\"></a></td>";
}
else {
	echo "<td><i class=\"large fas fa-ban fa-xl\" title=\"$strCancel\"></td>";
	}
			 if ($row["factura_client_pdf"]=='')
			 {
			echo "<td><a href=\"invoice.php?option=print&type=1&cID=$row[factura_ID]\"><i class=\"far fa-file fa-xl\" title=\"$strView\"></i></a></td>";
			 }
			 else
			 {
				 echo "<td><a href=\"invoice.php?option=print&type=1&cID=$row[factura_ID]\"><i class=\"far fa-file-pdf fa-xl\" title=\"$strView\"></i></a></td>";
			 }
       ?>
          <div class="full reveal" id="exampleModal1_<?php echo $row["factura_ID"]?>" data-reveal>
            <iframe src="viewinvoice.php?type=1&option=show&cID=<?php echo $row["factura_ID"]?>"
              frameborder="0" style="border:0" Width="100%" height="1000"></iframe>
            <button class="close-button" data-close aria-label="Close modal" type="button">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <td><i class="fa-xl fas fa-search" title="<?php echo $strView?>"
              data-open="exampleModal1_<?php echo $row["factura_ID"]?>"></i></td>
          <?php 
          if ($row["factura_client_achitat"]==0 AND $row["factura_client_anulat"]==0) 
			 {
		 echo "<td><a href=\"sitecashin.php?cID=$row[factura_ID]\"><i class=\"fas fa-money-bill-alt fa-xl\" title=\"$strCashin\"></i></a></td>";
			 }
		 else {
		 echo "<td color=\"green\"><i class=\"fas fa-money-bill fa-xl\" title=\"$strCashin\"></i></td>";
		 }
    
		echo	 "<td><a href=\"emailinvoice.php?option=print&type=1&cID=$row[factura_ID]\"><i class=\"far fa-envelope fa-xl\" title=\"$strEmail\"></i></a></td>";
		If ($row["factura_client_efactura_generata"]=='DA' OR $row["factura_client_anulat"]=='1')
{  echo   "<td><i class=\"far fa-file-excel fa-xl\" title=\"$strXML\"></i></td>";}
	else
{  echo   "<td><a href=\"einvoice.php?cID=$row[factura_ID]\"><i class=\"far fa-file-excel fa-xl\" title=\"$strXML\"></i></a></td>";}
   echo "<td>$row[factura_client_efactura_generata]</td>
		</tr>";
}
echo "</tbody><tfoot><tr><td></td><td colspan=\"16\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
?>
          <div class="paginate">
            <?php
echo $pages->display_pages() . " <a href=\"siteinvoices.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
          </div>
      </div>
      <div class="tabs-panel" id="panel2">
        <?php
$pquery="SELECT * FROM facturare_facturi
WHERE YEAR(factura_data_emiterii)='$year' AND factura_tip='1'";
if ($cl!='0'){
$pquery= $pquery . " AND factura_client_ID='$cl'";
};
if ($fmonth!='0'){
$pquery= $pquery . " AND MONTH(factura_data_emiterii)='$fmonth'";
};
$presult=ezpub_query($conn,$pquery);
$pnumar=ezpub_num_rows($presult,$pquery);
$ppages = new Pagination; 
$ppages->items_total = $pnumar; 
$ppages->mid_range = 5; 
$ppages->paginate(); 
$pquery= $pquery . " ORDER BY cast(factura_numar as unsigned) ASC $ppages->limit";
$presult=ezpub_query($conn,$pquery);

echo ezpub_error($conn);
if ($pnumar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
?>
        <div class="paginate">
          <?php
$queryp="SELECT COUNT(factura_client_achitat) AS neplatite FROM facturare_facturi WHERE factura_client_achitat=0;"; 
$resultp=ezpub_query($conn,$queryp);
$rowp=ezpub_fetch_array($resultp);
$unpaid=$rowp["neplatite"];
echo $strTotal . " " .$pnumar." ".$strInvoices ." / ". $unpaid ." ". $strUnpayed;
echo " <br /><br />";
echo $ppages->display_pages() . " <a href=\"siteinvoices.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
        </div>
        <table width="100%" class="unstriped">
          <thead>
            <tr>
              <th><?php echo $strNumber?></th>
              <th><?php echo $strIssuedDate?></th>
              <th><?php echo $strClient?></th>
              <th><?php echo $strTotal?></th>
              <th><?php echo $strValue?></th>
              <th><?php echo $strVAT?></th>
              <th><?php echo $strEdit?></th>
              <th><?php echo $strDetails?></th>
              <th><?php echo $strAddInvoice?></th>
              <th><?php echo $strEmail?></th>
            </tr>
          </thead>
          <?php 
While ($row=ezpub_fetch_array($presult)){
		
  		echo"<tr><td>$row[factura_numar]</td>
			<td>". date("d.m.Y",strtotime($row["factura_data_emiterii"]))."</td>
			<td width=\"15%\">$row[factura_client_denumire]</td>
			<td align=\"right\">". romanize($row["factura_client_valoare_totala"])."</td>
			<td align=\"right\">". romanize($row["factura_client_valoare"])."</td>
			<td align=\"right\">". romanize($row["factura_client_valoare_tva"])."</td>";
			echo "<td><a href=\"siteinvoices.php?mode=edit&cID=$row[factura_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></a></td>";
			 if ($row["factura_client_pdf"]=='')
			 {
			echo "<td><a href=\"invoice.php?option=print&type=1&cID=$row[factura_ID]\"><i class=\"far fa-file fa-xl\" title=\"$strView\"></i></a></td>";
			 }
			 else
			 {
				 echo "<td><a href=\"invoice.php?option=print&type=1&cID=$row[factura_ID]\"><i class=\"far fa-file-pdf fa-xl\" title=\"$strView\"></i></a></td>";
			 }
		 echo "<td color=\"green\"><a href=\"createinvoice.php?cID=$row[factura_ID]\" ><i class=\"fas fa-money-bill fa-xl\" title=\"$strAddInvoice\"></i></a></td>";
		echo	 "<td><a href=\"emailinvoice.php?option=print&type=1&cID=$row[factura_ID]\"><i class=\"far fa-envelope fa-xl\" title=\"$strEmail\"></i></a></td>
    </tr>";
}
echo "</table>";
}
?>
          <br />
          <div class="paginate">
            <?php
echo $ppages->display_pages() . " <a href=\"siteinvoices.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
          </div>
      </div>
    </div>

  </div>
</div>
<?php }}?>
</div>
</div>
<?php
include '../bottom.php';
?>