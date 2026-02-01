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
	header("location:$strSiteURL/login/index.php?message=MLF");
}
$strPageTitle="Administrare chitanțe";
include '../dashboard/header.php';
$day = date('d');
$year = date('Y');
$month = date('m');

?>
<div class="grid-x grid-padding-x ">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";

// Validate mode parameter
if (isset($_GET['mode']) && !in_array($_GET['mode'], ['delete', 'new', 'edit'])) {
    die("Invalid mode parameter");
}

if (isset($_GET['mode']) && $_GET['mode']=="delete"){
    // Validate cID parameter
    if (!isset($_GET['cID']) || !filter_var($_GET['cID'], FILTER_VALIDATE_INT)) {
        die("Invalid cID parameter");
    }
    $cID = (int)$_GET['cID'];
    
    $stmt = mysqli_prepare($conn, "DELETE FROM facturare_chitante WHERE chitanta_ID = ?");
    mysqli_stmt_bind_param($stmt, "i", $cID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitereceipts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

    // Validate cID parameter
    if (!isset($_GET['cID']) || !filter_var($_GET['cID'], FILTER_VALIDATE_INT)) {
        die("Invalid cID parameter");
    }
    $cID = (int)$_GET['cID'];

if (isset($_GET['mode']) && $_GET['mode']=="new"){
    // Validate and sanitize POST data
    if (!isset($_POST["chitanta_factura_ID"]) || !is_array($_POST["chitanta_factura_ID"])) {
        die("Invalid invoice IDs");
    }
    
    // Validate each invoice ID is numeric
    $validIDs = [];
    foreach ($_POST["chitanta_factura_ID"] as $id) {
        if (filter_var($id, FILTER_VALIDATE_INT)) {
            $validIDs[] = (int)$id;
        }
    }
    
    if (empty($validIDs)) {
        die("No valid invoice IDs provided");
    }
    
    $facturi = implode(";", $validIDs);

//insert new user
$inchisa=1;

// Validate date
if (!isset($_POST["data_incasarii"])) {
    die("Missing date parameter");
}
if (empty($_POST["data_incasarii"])) {
    die("Date cannot be empty");
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST["data_incasarii"])) {
    die("Invalid date format. Expected YYYY-MM-DD");
}
$dateParts = explode('-', $_POST["data_incasarii"]);
if (!checkdate((int)$dateParts[1], (int)$dateParts[2], (int)$dateParts[0])) {
    die("Invalid date value");
}
$dataincasarii = $_POST["data_incasarii"];

// Validate amount
if (!isset($_POST["chitanta_suma_incasata"]) || !is_numeric($_POST["chitanta_suma_incasata"])) {
    die("Invalid amount");
}
$suma = $_POST["chitanta_suma_incasata"];

// Validate description
if (!isset($_POST["chitanta_descriere"])) {
    die("Invalid description");
}
$descriere = $_POST["chitanta_descriere"];

$stmt = mysqli_prepare($conn, "UPDATE facturare_chitante SET chitanta_factura_ID = ?, chitanta_data_incasarii = ?, chitanta_suma_incasata = ?, chitanta_inchisa = ?, chitanta_descriere = ? WHERE chitanta_ID = ?");
mysqli_stmt_bind_param($stmt, "sssisi", $facturi, $dataincasarii, $suma, $inchisa, $descriere, $cID);
if (!mysqli_stmt_execute($stmt)) {
  die('Error: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt);

// Delete null records
$stmt = mysqli_prepare($conn, "DELETE FROM facturare_chitante WHERE chitanta_inchisa IS NULL");
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Update invoices
for ($i = 0; $i < sizeof($validIDs); $i++) 
{
    $value = $validIDs[$i];
    
	$stmt1 = mysqli_prepare($conn, "SELECT factura_data_emiterii FROM facturare_facturi WHERE factura_ID = ?");
	mysqli_stmt_bind_param($stmt1, "i", $value);
	mysqli_stmt_execute($stmt1);
	$result1 = mysqli_stmt_get_result($stmt1);
	$row1 = mysqli_fetch_assoc($result1);
	mysqli_stmt_close($stmt1);
	
	if ($row1) {
		$dataemiterii=strtotime($row1["factura_data_emiterii"]);
		$incasare=strtotime($dataincasarii);
		$datediff=$incasare-$dataemiterii;
		$zile=round($datediff / (60 * 60 * 24));
		
		$usql_stmt = mysqli_prepare($conn, "UPDATE facturare_facturi SET factura_client_achitat='1', factura_client_data_achitat = ?, factura_client_zile_achitat = ?, factura_client_achitat_prin='0' WHERE factura_ID = ?");
		mysqli_stmt_bind_param($usql_stmt, "sii", $dataincasarii, $zile, $value);
		mysqli_stmt_execute($usql_stmt);
		mysqli_stmt_close($usql_stmt);
	}
}

echo "<div class=\"callout success\"><p>$strRecordAdded</p>";
echo"			    <div class=\"grid-x grid-margin-x\">
			  <div class=\"large-12 medium-12 small-12 cell\">
			  <p>
			  <a href=\"receipt.php?cID=".urlencode($cID)."\" class=\"button\"><i class=\"fas fa-file-pdf\"></i>&nbsp;$strPrint</a>
			  </p>
</div>
</div>";

echo"</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitereceipts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
else
{// edit
// Validate date
if (!isset($_POST["data_incasarii"])) {
    die("Missing date parameter");
}
if (empty($_POST["data_incasarii"])) {
    die("Date cannot be empty");
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST["data_incasarii"])) {
    die("Invalid date format. Expected YYYY-MM-DD");
}
$dateParts = explode('-', $_POST["data_incasarii"]);
if (!checkdate((int)$dateParts[1], (int)$dateParts[2], (int)$dateParts[0])) {
    die("Invalid date value");
}
$dataincasarii = $_POST["data_incasarii"];

// Validate factura_ID
if (!isset($_POST["chitanta_factura_ID"]) || !filter_var($_POST["chitanta_factura_ID"], FILTER_VALIDATE_INT)) {
    die("Invalid invoice ID");
}
$facturaID = (int)$_POST["chitanta_factura_ID"];

// Validate amount
if (!isset($_POST["chitanta_suma_incasata"]) || !is_numeric($_POST["chitanta_suma_incasata"])) {
    die("Invalid amount");
}
$suma = $_POST["chitanta_suma_incasata"];

// Validate description
if (!isset($_POST["chitanta_descriere"])) {
    die("Invalid description");
}
$descriere = $_POST["chitanta_descriere"];

$stmt = mysqli_prepare($conn, "UPDATE facturare_chitante SET chitanta_factura_ID = ?, chitanta_data_incasarii = ?, chitanta_suma_incasata = ?, chitanta_descriere = ? WHERE chitanta_ID = ?");
mysqli_stmt_bind_param($stmt, "isssi", $facturaID, $dataincasarii, $suma, $descriere, $cID);
if (!mysqli_stmt_execute($stmt))
  {
  die('Error: ' . mysqli_error($conn));
  }
mysqli_stmt_close($stmt);

// Update invoice status
$usql_stmt = mysqli_prepare($conn, "UPDATE facturare_facturi SET factura_client_achitat='1', factura_client_data_achitat = ? WHERE factura_ID = ?");
mysqli_stmt_bind_param($usql_stmt, "si", $dataincasarii, $facturaID);
mysqli_stmt_execute($usql_stmt);
mysqli_stmt_close($usql_stmt);

echo "<div class=\"callout success\"><p>$strRecordModified</p>";
echo"			    <div class=\"grid-x grid-margin-x\">
			  <div class=\"large-12 medium-12 small-12 cell\">
			  <p>
			  <a href=\"receipt.php?cID=".urlencode($cID)."\" class=\"button\"><i class=\"fas fa-file-pdf\"></i>&nbsp;$strPrint</a>
			  </p>
</div>
</div>";

echo"</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitereceipts.php\"
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
        <script src="<?php echo $strSiteURL ?>/js/foundation/jquery.js"></script>
        <script>
        $(document).ready(function() {
            $('select[id="chitanta_factura_ID"]').change(function() {
                var selectedValues = $('#chitanta_factura_ID').val(); // Array cu valorile selectate

                $.ajax({
                    url: "receiptsum.php",
                    dataType: "json",
                    data: {
                        factura_ID: selectedValues
                    },
                    type: "POST",
                    success: function(data) {
                        try {
                            $("#chitanta_suma_incasata").val(data["suma"]);
                            $("#chitanta_descriere").val(data["factura"]);
                        } catch (err) {
                            document.getElementById("response").innerHTML = err.message;
                        }
                    },
                    error: function() {
                        alert('Some error occurred!');
                    }
                });
            });
        });
        </script>
        <?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
$query="Select chitanta_numar FROM facturare_chitante WHERE chitanta_inchisa='1' ORDER BY chitanta_numar DESC";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
If (!isSet($row["chitanta_numar"]))
{$numarfactura=1;}
else
{$numarfactura=(int)$row["chitanta_numar"]+1;}

$mSQL = "INSERT INTO facturare_chitante(";
	$mSQL = $mSQL . "chitanta_numar)";
	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$numarfactura . "') ";		
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
else{
	$receiptID=ezpub_inserted_id($conn);
}
?>
        <form method="post"  action="sitereceipts.php?mode=new&cID=<?php echo urlencode($receiptID)?>">
            <div class="grid-x grid-padding-x ">
                <div class="large-3 medium-3 small-12 cell">
                    <label> <?php echo $strInvoice?>
                        <select name="chitanta_factura_ID[]" id="chitanta_factura_ID" size="10" multiple required>
                            <option value="" selected>--</option>
                            <?php
		$query="SELECT factura_ID, factura_numar, factura_data_emiterii, factura_client_denumire FROM facturare_facturi WHERE factura_client_achitat=0 AND factura_tip=0 ORDER BY factura_client_denumire DESC";
$result=ezpub_query($conn,$query);
  while ($rss=ezpub_fetch_array($result)){
    $codenumarfactura=str_pad($rss["factura_numar"], 8, '0', STR_PAD_LEFT);
	?>
                            <option value="<?php echo $rss["factura_ID"]?>">
                                <?php echo $rss["factura_client_denumire"]." - ".$siteInvoicingCode. $codenumarfactura." - ". $rss["factura_data_emiterii"]?>
                            </option>
                            <?php
}?>
                        </select></label>
                </div>
                <div class="large-2 medium-2 small-12 cell">
                    <label> <?php echo $strDate?>
                        <input type="date" name="data_incasarii" required value="<?php echo date('Y-m-d')?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-12 cell">
                    <label><?php echo $strNumber?>
                        <input name="chitanta_numar" id="chitanta_numar" type="text" size="50" class="required"
                            value="CNS0000<?php echo $numarfactura?>" />
                    </label>
                </div>
                <div class="large-1 medium-1 small-12 cell">
                    <label><?php echo $strSum?>
                        <input name="chitanta_suma_incasata" id="chitanta_suma_incasata" type="text" size="50"
                            class="required" value="" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-12 cell">
                    <label><?php echo $strDetails?>
                        <textarea name="chitanta_descriere" id="chitanta_descriere" style="width:100%;"></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x ">
                <div class="large-12 medium-12 small-12 text-center cell">
                    <input type="submit" value="<?php echo $strAdd?>" class="button" name="Submit">
                </div>
            </div>
        </form>
        <?php
}
elseIf (isset($_GET['mode']) && $_GET['mode']=="edit"){
// Validate cID parameter
if (!isset($_GET['cID']) || !filter_var($_GET['cID'], FILTER_VALIDATE_INT)) {
    die("Invalid cID parameter");
}
$cID = (int)$_GET['cID'];

$stmt = mysqli_prepare($conn, "SELECT * FROM facturare_chitante WHERE chitanta_ID = ?");
mysqli_stmt_bind_param($stmt, "i", $cID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    die("Receipt not found");
}
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="sitereceipts.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post"  action="sitereceipts.php?mode=edit&cID=<?php echo urlencode($row['chitanta_ID'])?>">
            <div class="grid-x grid-padding-x ">
                <div class="large-3 medium-3 small-12 cell">
                    <label> <?php echo $strInvoice?>
                        <select name="chitanta_factura_ID" required>
                            <option value="" selected>--</option>
                            <?php
		$query="SELECT factura_ID, factura_numar, factura_data_emiterii, factura_client_denumire FROM facturare_facturi ORDER BY factura_data_emiterii DESC";
$result=ezpub_query($conn,$query);
  while ($rss=ezpub_fetch_array($result)){
    $codenumarfactura=str_pad($rss["factura_numar"], 8, '0', STR_PAD_LEFT);
	?>
                            <option value="<?php echo $rss["factura_ID"]?>"
                                <?php if($row["chitanta_factura_ID"]==$rss["factura_ID"]) echo "selected"?>>
                                <?php echo $rss["factura_client_denumire"]." - ".$siteInvoicingCode. $codenumarfactura." - ". $rss["factura_data_emiterii"]?>
                            </option>
                            <?php
}?>
                        </select></label>
                </div>
                <div class="large-2 medium-2 small-12 cell">
                    <label> <?php echo $strDate?>
                        <input type="date" name="data_incasarii" required value="<?php echo date('Y-m-d', strtotime($row['chitanta_data_incasarii']))?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-12 cell">
                    <label><?php echo $strNumber?>
                    <?php $codenumarfactura=str_pad($row["chitanta_numar"], 8, '0', STR_PAD_LEFT);
                    $receiptnumber=$siteInvoicingCode . $codenumarfactura;?>
                        <input name="chitanta_numar" type="text" size="50" class="required" value="<?php echo htmlspecialchars($receiptnumber, ENT_QUOTES, 'UTF-8')?>" readonly />
                    </label>
                </div>
                <div class="large-1 medium-1 small-12 cell">
                    <label><?php echo $strSum?>
                        <input name="chitanta_suma_incasata" type="text" size="50" class="required"
                            value="<?php echo htmlspecialchars($row["chitanta_suma_incasata"], ENT_QUOTES, 'UTF-8')?>" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-12 cell">
                    <label><?php echo $strDetails?>
                        <textarea name="chitanta_descriere"
                            style="width:100%;"><?php echo htmlspecialchars($row["chitanta_descriere"], ENT_QUOTES, 'UTF-8')?></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x ">
                <div class="large-12 medium-12 small-12 text-center cell">
                    <input type="submit" value="<?php echo $strAdd?>" class="button" name="Submit">
                </div>
            </div>

            <?php
}
else
{
	?>
            <div class="grid-x grid-padding-x ">
                <div class="large-12 medium-12 small-12 cell">
                    <?php
echo "<a href=\"sitereceipts.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a>";
?></div>
            </div>
            <?php 
// Get total count
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM facturare_chitante");
mysqli_stmt_execute($stmt);
$result_count = mysqli_stmt_get_result($stmt);
$count_row = mysqli_fetch_assoc($result_count);
$numar = $count_row['total'];
mysqli_stmt_close($stmt);

$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate();

// Get paginated results
$query = "SELECT * FROM facturare_chitante ORDER BY chitanta_ID DESC $pages->limit";
$result=ezpub_query($conn,$query);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
?>
            <div class="paginate">
                <?php
echo $strTotal . " " .$numar." ".$strReceipts;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"sitereceipts.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
            </div>
            <table width="100%">
                <thead>
                    <tr>
                        <th><?php echo $strNumber?></th>
                        <th><?php echo $strDate?></th>
                        <th><?php echo $strTitle?></th>
                        <th><?php echo $strSum?></th>
                        <th><?php echo $strEdit?></th>
                        <th><?php echo $strView?></th>
                        <th><?php echo $strDelete?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>CNS0000".htmlspecialchars($row['chitanta_numar'], ENT_QUOTES, 'UTF-8')."</td>
			<td>" . date("d.m.Y",strtotime($row["chitanta_data_incasarii"]))."</td>
			<td>".htmlspecialchars($row['chitanta_descriere'], ENT_QUOTES, 'UTF-8')."</td>
			<td>".htmlspecialchars($row['chitanta_suma_incasata'], ENT_QUOTES, 'UTF-8')."</td>
			  <td><a href=\"sitereceipts.php?mode=edit&cID=".urlencode($row['chitanta_ID'])."\" ><i class=\"far fa-edit fa-xl\" title=\"".htmlspecialchars($strEdit, ENT_QUOTES, 'UTF-8')."\"></i></a></td>
			  <td><a href=\"receipt.php?cID=".urlencode($row['chitanta_ID'])."\" ><i class=\"far fa-file-pdf\" title=\"".htmlspecialchars($strView, ENT_QUOTES, 'UTF-8')."\"></i></a></td>
			<td><a href=\"cancelreceipt.php?cID=".urlencode($row['chitanta_ID'])."\"  OnClick=\"return confirm('".htmlspecialchars($strConfirmDelete, ENT_QUOTES, 'UTF-8')."');\"><i class=\"large fas fa-ban\" title=\"".htmlspecialchars($strCancel, ENT_QUOTES, 'UTF-8')."\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"5\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";?>
                    <div class="paginate">
                        <?php
echo $pages->display_pages() . " <a href=\"sitereceipts.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
                    </div>
                    <?php
}
}
}
?>
    </div>
</div>
<?php
include '../bottom.php';
?>