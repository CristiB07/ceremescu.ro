<?php
include '../settings.php';

include '../classes/paginator.class.php';
include '../classes/common.php';
$strPageTitle="Administrare deconturi";
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

$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
  $mindate = strtotime("-1 year", time());
  $mindate = date("Y-m-d", $mindate);
    $maxdate = strtotime("+1 year", time());
  $maxdate = date("Y-m-d", $maxdate);


// Validate message parameter
$allowed_messages = ['Error', 'Success'];
$message = isset($_GET['message']) && in_array($_GET['message'], $allowed_messages, true) ? $_GET['message'] : '';

if ($message === "Error") {
    echo "<div class=\"callout alert\">" . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . "</div>";
}
if ($message === "Success") {
    echo "<div class=\"callout success\">" . htmlspecialchars($strMessageSent, ENT_QUOTES, 'UTF-8') . "</div>";
}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>" . htmlspecialchars($strPageTitle, ENT_QUOTES, 'UTF-8') . "</h1>";

// Validate mode parameter
$allowed_modes = ['delete', 'new', 'edit'];
$mode = isset($_GET['mode']) && in_array($_GET['mode'], $allowed_modes, true) ? $_GET['mode'] : '';

if ($mode === "delete") {
    // Validate cID
    if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
        header("location: personalexpenses.php?message=Error");
        exit();
    }
    $cID = intval($_GET['cID']);
    
    // Check authorization - user can only delete their own records
    $stmt_check = $conn->prepare("SELECT decont_ID FROM administrative_deconturi WHERE decont_ID=? AND decont_user=?");
    $stmt_check->bind_param("is", $cID, $code);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows === 0) {
        $stmt_check->close();
        header("location: personalexpenses.php?message=Error");
        exit();
    }
    $stmt_check->close();
    
    // Perform deletion
    $stmt_del = $conn->prepare("DELETE FROM administrative_deconturi WHERE decont_ID=?");
    $stmt_del->bind_param("i", $cID);
    $stmt_del->execute();
    $stmt_del->close();
    
    echo "<div class=\"callout success\">" . htmlspecialchars($strRecordDeleted, ENT_QUOTES, 'UTF-8') . "</div></div></div>";
    echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-1);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
    include '../bottom.php';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if ($mode === "new") {
        // Validate inputs
        if (!isset($_POST["decont_descriere"]) || trim($_POST["decont_descriere"]) === '') {
            header("location: personalexpenses.php?mode=new&message=Error");
            exit();
        }
        
        if (!isset($_POST["decont_suma"]) || !is_numeric(str_replace(",", ".", $_POST["decont_suma"]))) {
            header("location: personalexpenses.php?mode=new&message=Error");
            exit();
        }
        
        if (!isset($_POST["decont_data"]) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST["decont_data"])) {
            header("location: personalexpenses.php?mode=new&message=Error");
            exit();
        }
        
        if (!isset($_POST["decont_achitat_card"]) || !in_array($_POST["decont_achitat_card"], ['0', '1'], true)) {
            header("location: personalexpenses.php?mode=new&message=Error");
            exit();
        }
        
        $suma = str_replace(",", ".", $_POST["decont_suma"]);
        $decont_descriere = trim($_POST["decont_descriere"]);
        $decont_data = $_POST["decont_data"];
        $decont_achitat_card = intval($_POST["decont_achitat_card"]);
        $decont_document = isset($_POST["decont_document"]) ? trim($_POST["decont_document"]) : '';
        
        // Insert with prepared statement
        $stmt = $conn->prepare("INSERT INTO administrative_deconturi(decont_descriere, decont_suma, decont_luna, decont_data, decont_achitat_card, decont_user, decont_document) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdssdss", $decont_descriere, $suma, $decont_data, $decont_data, $decont_achitat_card, $code, $decont_document);
        
        if (!$stmt->execute()) {
            $stmt->close();
            header("location: personalexpenses.php?mode=new&message=Error");
            exit();
        }
        $stmt->close();
        
        echo "<div class=\"callout success\">" . htmlspecialchars($strRecordAdded, ENT_QUOTES, 'UTF-8') . "</div></div></div>";
        echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-2);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
        include '../bottom.php';
        exit();
    }
else {
        // edit
        // Validate cID
        if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
            header("location: personalexpenses.php?message=Error");
            exit();
        }
        $cID = intval($_GET['cID']);
        
        // Validate inputs
        if (!isset($_POST["decont_descriere"]) || trim($_POST["decont_descriere"]) === '') {
            header("location: personalexpenses.php?mode=edit&cID=$cID&message=Error");
            exit();
        }
        
        if (!isset($_POST["decont_suma"]) || !is_numeric(str_replace(",", ".", $_POST["decont_suma"]))) {
            header("location: personalexpenses.php?mode=edit&cID=$cID&message=Error");
            exit();
        }
        
        if (!isset($_POST["decont_data"]) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST["decont_data"])) {
            header("location: personalexpenses.php?mode=edit&cID=$cID&message=Error");
            exit();
        }
        
        // Check authorization - user can only edit their own records
        $stmt_check = $conn->prepare("SELECT decont_ID FROM administrative_deconturi WHERE decont_ID=? AND decont_user=?");
        $stmt_check->bind_param("is", $cID, $code);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows === 0) {
            $stmt_check->close();
            header("location: personalexpenses.php?message=Error");
            exit();
        }
        $stmt_check->close();
        
        $suma = str_replace(",", ".", $_POST["decont_suma"]);
        $decont_descriere = trim($_POST["decont_descriere"]);
        $decont_data = $_POST["decont_data"];
        $decont_document = isset($_POST["decont_document"]) ? trim($_POST["decont_document"]) : '';
        
        // Update with prepared statement
        $stmt = $conn->prepare("UPDATE administrative_deconturi SET decont_descriere=?, decont_suma=?, decont_document=?, decont_luna=?, decont_data=? WHERE decont_ID=?");
        $stmt->bind_param("sdsssi", $decont_descriere, $suma, $decont_document, $decont_data, $decont_data, $cID);
        
        if (!$stmt->execute()) {
            $stmt->close();
            header("location: personalexpenses.php?mode=edit&cID=$cID&message=Error");
            exit();
        }
        $stmt->close();
        
        echo "<div class=\"callout success\">" . htmlspecialchars($strRecordModified, ENT_QUOTES, 'UTF-8') . "</div></div></div><hr/>";
        echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"personalexpenses.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
        include '../bottom.php';
        exit();
    }
}
else {
?>
        <?php
if ($mode === "new") {
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="personalexpenses.php" class="button"><?php echo htmlspecialchars($strBack, ENT_QUOTES, 'UTF-8')?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post" action="personalexpenses.php?mode=new">

            <div class="grid-x grid-margin-x">
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo htmlspecialchars($strMonth, ENT_QUOTES, 'UTF-8')?>
                        <select name="strDData2">
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
    			echo "<option value=\"" . htmlspecialchars($m, ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($monthname, ENT_QUOTES, 'UTF-8') . "</option>";} 
			?>
                        </select> </label>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo htmlspecialchars($strYear, ENT_QUOTES, 'UTF-8')?>
                        <select name="strDData3">
                            <option value="0000" selected>--</option>
                            <?php
		$cy=date("Y");
		$fy=$cy+1;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
    	echo "<option value=\"" . htmlspecialchars($y, ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($y, ENT_QUOTES, 'UTF-8') . "</option>";} 
			?>
                        </select></label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo htmlspecialchars($strExpense, ENT_QUOTES, 'UTF-8')?>
                        <input name="decont_descriere" id="decont_descriere" type="text" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strPaidCard?>
                        <input name="decont_achitat_card" type="radio" value="0" checked /><?php echo $strYes?>&nbsp;&nbsp;
                        <input name="decont_achitat_card" type="radio" value="1"><?php echo $strNo?> 
                    </label>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo $strSum?>
                        <input name="decont_suma" type="text" value="" /></label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strDocument?>
                        <input name="decont_document" type="text" value="" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strDate?>
                        <input name="decont_data" type="date" value="<?php echo date("Y-m-d")?>" min="<?php echo $mindate?>" max="<?php echo $maxdate?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"> <input type="submit" value="<?php echo htmlspecialchars($strAdd, ENT_QUOTES, 'UTF-8')?>" name="Submit" class="button"></div>
            </div>
        </form>
        <?php
}
elseif ($mode === "edit") {
    // Validate cID
    if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
        header("location: personalexpenses.php?message=Error");
        exit();
    }
    $cID = intval($_GET['cID']);
    
    // Check authorization - user can only edit their own records
    $stmt = $conn->prepare("SELECT * FROM administrative_deconturi WHERE decont_ID=? AND decont_user=?");
    $stmt->bind_param("is", $cID, $code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        header("location: personalexpenses.php?message=Error");
        exit();
    }
    
    $row = $result->fetch_assoc();
    $stmt->close();
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="personalexpenses.php" class="button"><?php echo htmlspecialchars($strBack, ENT_QUOTES, 'UTF-8')?>&nbsp;<i class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post" action="personalexpenses.php?mode=edit&cID=<?php echo htmlspecialchars($cID, ENT_QUOTES, 'UTF-8')?>">
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo htmlspecialchars($strExpense, ENT_QUOTES, 'UTF-8')?>
                        <input name="decont_descriere" type="text" value="<?php echo htmlspecialchars($row["decont_descriere"], ENT_QUOTES, 'UTF-8')?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo htmlspecialchars($strPaidCard, ENT_QUOTES, 'UTF-8')?>
                        <input name="decont_achitat_card" type="radio" value="0" <?php if ($row["decont_achitat_card"]==0) echo "checked"?> />&nbsp;<?php echo htmlspecialchars($strYes, ENT_QUOTES, 'UTF-8')?>
                        <input name="decont_achitat_card" type="radio" value="1" <?php if ($row["decont_achitat_card"]==1) echo "checked"?> />&nbsp;<?php echo htmlspecialchars($strNo, ENT_QUOTES, 'UTF-8')?>
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo htmlspecialchars($strSum, ENT_QUOTES, 'UTF-8')?>
                        <input name="decont_suma" type="text" value="<?php echo htmlspecialchars($row["decont_suma"], ENT_QUOTES, 'UTF-8')?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo htmlspecialchars($strDocument, ENT_QUOTES, 'UTF-8')?>
                        <input name="decont_document" type="text" value="<?php echo htmlspecialchars($row["decont_document"], ENT_QUOTES, 'UTF-8')?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo htmlspecialchars($strDate, ENT_QUOTES, 'UTF-8')?>
                        <input name="decont_data" type="date" value="<?php echo htmlspecialchars($row["decont_data"], ENT_QUOTES, 'UTF-8')?>"min="<?php echo htmlspecialchars($mindate, ENT_QUOTES, 'UTF-8')?>" max="<?php echo htmlspecialchars($maxdate, ENT_QUOTES, 'UTF-8')?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"> <input type="submit" value="<?php echo htmlspecialchars($strModify, ENT_QUOTES, 'UTF-8')?>" name="Submit" class="button"></div>
            </div>
        </form>
        <?php
}
else 
{?>
        <h3><?php echo htmlspecialchars($strSendPE, ENT_QUOTES, 'UTF-8')?></h3>
        <form method="post"  action="pe2excel.php">
            <div class="grid-x grid-margin-x">
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo htmlspecialchars($strMonth, ENT_QUOTES, 'UTF-8')?>
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
    			echo "<option value=\"" . htmlspecialchars($m, ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($monthname, ENT_QUOTES, 'UTF-8') . "</option>";} 
			?>
                        </select> </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo htmlspecialchars($strYear, ENT_QUOTES, 'UTF-8')?>
                        <select name="year">
                            <option value="0000" selected>--</option>
                            <?php
		$cy=date("Y");
		$fy=$cy+1;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
    	echo "<option value=\"" . htmlspecialchars($y, ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($y, ENT_QUOTES, 'UTF-8') . "</option>";} 
			?>
                        </select></label>
                </div>
                <div class="large-2 medium-2 small-2 cell ">
                    <p align="right"><input type="submit" value="<?php echo htmlspecialchars($strSend, ENT_QUOTES, 'UTF-8')?>" name="Submit" class="button">
                    </p>
                </div>
            </div>
        </form>

        <?php
echo "<a href=\"personalexpenses.php?mode=new\" class=\"button\">" . htmlspecialchars($strAddNew, ENT_QUOTES, 'UTF-8') . " <i class=\"fa-xl fa fa-plus\" title=\"" . htmlspecialchars($strAdd, ENT_QUOTES, 'UTF-8') . "\"></i></a><br />";

// Get records with prepared statement
$stmt = $conn->prepare("SELECT * FROM administrative_deconturi WHERE decont_user=? ORDER BY decont_data DESC");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();
$numar = $result->num_rows;
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 

if ($numar==0)
{
    echo "<div class=\"callout alert\">" . htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8') . "</div>";
}
else {
?>
        <h3><?php echo htmlspecialchars($strExpenses, ENT_QUOTES, 'UTF-8')?></h3>

        <div class="paginate">
            <?php
echo htmlspecialchars($strTotal, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($numar, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($strPayments, ENT_QUOTES, 'UTF-8');
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"personalexpenses.php\" title=\"strClearAllFilters\">" . htmlspecialchars($strShowAll, ENT_QUOTES, 'UTF-8') . "</a>&nbsp;";
echo " <br /><br />";
?>
        </div>

        <table width="100%">
            <thead>
                <tr>
                    <th><?php echo htmlspecialchars($strExpense, ENT_QUOTES, 'UTF-8')?></th>
                    <th><?php echo htmlspecialchars($strValue, ENT_QUOTES, 'UTF-8')?></th>
                    <th><?php echo htmlspecialchars($strDate, ENT_QUOTES, 'UTF-8')?></th>
                    <th><?php echo htmlspecialchars($strEdit, ENT_QUOTES, 'UTF-8')?></th>
                    <th><?php echo htmlspecialchars($strDelete, ENT_QUOTES, 'UTF-8')?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['decont_descriere'], ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td>" . htmlspecialchars(romanize($row['decont_suma']), ENT_QUOTES, 'UTF-8') . "</td>";
    
    $m = date("m", strtotime($row['decont_luna']));
    $dateObj = DateTime::createFromFormat('!m', $m);
    $formatter = new IntlDateFormatter("ro_RO",
                                        IntlDateFormatter::FULL, 
                                        IntlDateFormatter::FULL, 
                                        'Europe/Bucharest', 
                                        IntlDateFormatter::GREGORIAN,
                                        'MMMM');
    $monthname = $formatter->format($dateObj);
    $year = date("Y", strtotime($row['decont_luna']));
    $lunadecont = htmlspecialchars($monthname . "." . $year, ENT_QUOTES, 'UTF-8');
    
    $decont_ID = htmlspecialchars($row['decont_ID'], ENT_QUOTES, 'UTF-8');
    $strEditEscaped = htmlspecialchars($strEdit, ENT_QUOTES, 'UTF-8');
    $strDeleteEscaped = htmlspecialchars($strDelete, ENT_QUOTES, 'UTF-8');
    $strConfirmDeleteEscaped = htmlspecialchars($strConfirmDelete, ENT_QUOTES, 'UTF-8');
    
    echo "<td>$lunadecont</td>";
    echo "<td><a href=\"personalexpenses.php?mode=edit&cID=$decont_ID\"><i class=\"far fa-edit fa-xl\" title=\"$strEditEscaped\"></i></a></td>";
    echo "<td><a href=\"personalexpenses.php?mode=delete&cID=$decont_ID\" OnClick=\"return confirm('$strConfirmDeleteEscaped');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDeleteEscaped\"></i></a></td>";
    echo "</tr>";
}
$stmt->close();
echo "</tbody><tfoot><tr><td></td><td colspan=\"3\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
}
?>
    </div>
</div>
<hr />
<?php
include '../bottom.php';
?>