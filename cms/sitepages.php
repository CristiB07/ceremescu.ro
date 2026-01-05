<?php
//update 29.12.2025
if(!isset($_SESSION)) { 
    session_start(); 
}
include '../settings.php';
include '../classes/common.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$strPageTitle="Administrează paginile!";
include '../dashboard/header.php';
?>
<link rel="stylesheet" href="../js/simple-editor/simple-editor.css">
<script src="../js/simple-editor/simple-editor.js"></script>
<div class="grid-x grid-padding-x">
    <div class="large-12 cell">
        <?php

echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

// Validate CSRF token
if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    die('<div class="callout alert">Invalid CSRF token</div>');
}

// Validate pID parameter
if (!isset($_GET['pID']) || !is_numeric($_GET['pID'])) {
    die('<div class="callout alert">Invalid page ID</div>');
}

$pID = intval($_GET['pID']);

// Use prepared statement
$stmt = $conn->prepare("DELETE FROM cms_pagini WHERE pagina_id=?");
$stmt->bind_param("i", $pID);
$stmt->execute();
$stmt->close();

echo "<div class=\"callout success\">$strRecordDeleted</div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitepages.php\"
}
setTimeout('delayer()', 1500);
//-->
</script>
</div>";
include '../bottom.php';
exit();
} // end delete record

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('<div class="callout alert">Invalid CSRF token</div>');
}

// Validate required fields
if (!isset($_POST['pagina_titlu'], $_POST['pagina_continut'], $_POST['pagina_url'], 
    $_POST['pagina_descriere'], $_POST['pagina_keywords'])) {
    die('<div class="callout alert">All required fields must be filled!</div>');
}

If ($_GET['mode']=="new"){
//insert new page
    $pagina_titlu = trim($_POST["pagina_titlu"]);
    $pagina_continut = $_POST["pagina_continut"]; // Keep HTML content
    $pagina_url = trim($_POST["pagina_url"]);
    $pagina_categorie = trim($_POST["pagina_categorie"]);
    $pagina_descriere = trim($_POST["pagina_descriere"]);
    $pagina_numar = isset($_POST["pagina_numar"]) ? intval($_POST["pagina_numar"]) : 0;
    $pagina_status = isset($_POST["pagina_status"]) ? intval($_POST["pagina_status"]) : 0;
    $pagina_tip = isset($_POST["pagina_tip"]) ? intval($_POST["pagina_tip"]) : 0;
    $pagina_master = isset($_POST["pagina_master"]) ? intval($_POST["pagina_master"]) : 0;
    $pagina_limba = trim($_POST["pagina_limba"]);
    $pagina_imaginetitlu = trim($_POST["pagina_imaginetitlu"]);
    $pagina_keywords = trim($_POST["pagina_keywords"]);
    
    // Use prepared statement
    $stmt = $conn->prepare("INSERT INTO cms_pagini(pagina_titlu, pagina_continut, pagina_url, pagina_categorie, pagina_descriere, pagina_numar, pagina_status, pagina_tip, pagina_master, pagina_limba, pagina_imaginetitlu, pagina_keywords) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiiiisss", $pagina_titlu, $pagina_continut, $pagina_url, $pagina_categorie, $pagina_descriere, $pagina_numar, $pagina_status, $pagina_tip, $pagina_master, $pagina_limba, $pagina_imaginetitlu, $pagina_keywords);
				
//It executes the SQL
if (!$stmt->execute())
  {
	  $stmt->close();
	  echo "<div class=\"callout alert\">$strThereWasAnError</ br>Error: " . $conn->error . "</div></div></div><hr/>";
 include '../bottom.php';
exit();
  }
else{
$stmt->close();
echo "<div class=\"callout success\">$strRecordAdded</div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitepages.php\"
}
setTimeout('delayer()', 1500);
//-->
</script></div><hr />";
include '../bottom.php';
exit();
}}
//ends if post new
else
{// edit
// Validate pID parameter
if (!isset($_GET['pID']) || !is_numeric($_GET['pID'])) {
    die('<div class="callout alert">Invalid page ID</div>');
}

$pID = intval($_GET['pID']);

// Sanitize inputs
$pagina_titlu = trim($_POST["pagina_titlu"]);
$pagina_continut = $_POST["pagina_continut"]; // Keep HTML content
$pagina_url = trim($_POST["pagina_url"]);
$pagina_categorie = trim($_POST["pagina_categorie"]);
$pagina_descriere = trim($_POST["pagina_descriere"]);
$pagina_numar = isset($_POST["pagina_numar"]) ? intval($_POST["pagina_numar"]) : 0;
$pagina_status = isset($_POST["pagina_status"]) ? intval($_POST["pagina_status"]) : 0;
$pagina_tip = isset($_POST["pagina_tip"]) ? intval($_POST["pagina_tip"]) : 0;
$pagina_limba = trim($_POST["pagina_limba"]);
$pagina_master = isset($_POST["pagina_master"]) ? intval($_POST["pagina_master"]) : 0;
$pagina_imaginetitlu = trim($_POST["pagina_imaginetitlu"]);
$pagina_keywords = trim($_POST["pagina_keywords"]);

// Use prepared statement
$stmt = $conn->prepare("UPDATE cms_pagini SET pagina_titlu=?, pagina_continut=?, pagina_url=?, pagina_categorie=?, pagina_descriere=?, pagina_numar=?, pagina_status=?, pagina_tip=?, pagina_limba=?, pagina_master=?, pagina_imaginetitlu=?, pagina_keywords=? WHERE pagina_id=?");
$stmt->bind_param("sssssiiiisssi", $pagina_titlu, $pagina_continut, $pagina_url, $pagina_categorie, $pagina_descriere, $pagina_numar, $pagina_status, $pagina_tip, $pagina_limba, $pagina_master, $pagina_imaginetitlu, $pagina_keywords, $pID);

if (!$stmt->execute())
  {
	  $stmt->close();
	  echo "<div class=\"callout alert\">$strThereWasAnError</ br>Error: " . $conn->error . "</div></div></div><hr/>";
 include '../bottom.php';
exit();
  }
else{
$stmt->close();
echo "<div class=\"callout success\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitepages.php\"
}
setTimeout('delayer()', 1500);
//-->
</script></div></div><hr />";
include '../bottom.php';
exit();
}
}
}// ends post if
else { // starts entering data

If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){ // we have new page
echo "<a href=\"sitepages.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>";
?>
        <form method="post" id="users" Action="sitepages.php?mode=new">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="grid-x grid-padding-x">
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strType?>
                        <select name="pagina_tip" required>
                            <option value="0"><?php echo $strMaster?></option>
                            <option value="1"><?php echo $strSlave?></option>
                        </select>
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strMaster?>
                        <select name="pagina_master" required>
                            <option value="0"><?php echo $strMaster?></option>
                            <?php $sql = "Select pagina_id, pagina_titlu FROM cms_pagini WHERE pagina_tip='0' ORDER BY pagina_titlu ASC";
							$result = ezpub_query($conn,$sql);
							while ($rss=ezpub_fetch_array($result)){
							?>
                            <option value="<?php echo htmlspecialchars($rss["pagina_id"], ENT_QUOTES, 'UTF-8')?>"><?php echo htmlspecialchars($rss["pagina_titlu"], ENT_QUOTES, 'UTF-8')?></option>
                            <?php } ?>
                        </select></label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strTitle?>
                        <input type="text" name="pagina_titlu" type="text" placeholder="<?php echo $strTitle?>"
                            required />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strURL?>
                        <input type="text" name="pagina_url" type="text" placeholder="<?php echo $strURL?>" required />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strMainPicture?>
                        <input name="pagina_imaginetitlu" id="image" type="text" required readonly="readonly" />
                        <!-- Trigger/Open The Modal -->
                        <div class="full reveal" id="myModal" data-reveal>
                            <!-- Modal content -->
                            <iframe src="<?php echo $strSiteURL?>/common/image.php?directory=pages&field=image"
                                frameborder="0" style="border:0" Width="100%" height="750"></iframe>
                            <button class="close-button" data-close aria-label="Close reveal" type="button">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <a data-open="myModal" class="button"><?php echo $strImage?></a>
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCategory?>
                        <input type="text" name="pagina_categorie" type="text"
                            placeholder="<?php echo $strCategory?>" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strPageDescription?>
                        <textarea name="pagina_descriere" placeholder="<?php echo $strPageDescription?>" required
                            rows="6"></textarea></label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strPageKeywords?>
                        <input type="text" name="pagina_keywords" type="text"
                            placeholder="<?php echo $strPageKeywords?>" required />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strPageNumber?></label>
                    <input type="text" name="pagina_numar" type="text" placeholder="<?php echo $strPageNumber?>"
                        required />
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strPageLanguage?>
                        <input type="text" name="pagina_limba" type="text" placeholder="<?php echo $strPageLanguage?>"
                            required />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strActive?>
                        <input name="pagina_status" type="radio" value="0" checked />
                        <?php echo $strYes?>&nbsp;<input name="pagina_status" type="radio" value="1"><?php echo $strNo?>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                
                    <div class="large-12 medium-12 small-12 cell">
                        <label><?php echo $strPage?>
                        <textarea name="pagina_continut" class="simple-html-editor" data-upload-dir="pages" rows="10"></textarea>
                        </label>
                        </div>
    </div>
    <div class="grid-x grid-padding-x">
        <div class="large-12 medium-12 small-12 cell text-center"><br /><input type="submit"
                Value="<?php echo $strAdd?>" name="Submit" class="submit button"></div>
    </div>
    </form>
    <?php
} // ends if new page
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
	echo "<a href=\"sitepages.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>";

// Validate pID parameter
if (!isset($_GET['pID']) || !is_numeric($_GET['pID'])) {
    die('<div class="callout alert">Invalid page ID</div>');
}

$pID = intval($_GET['pID']);

// Use prepared statement
$stmt = $conn->prepare("SELECT * FROM cms_pagini WHERE pagina_id=?");
$stmt->bind_param("i", $pID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
?>
    <form method="post" id="users" Action="sitepages.php?mode=edit&pID=<?php echo htmlspecialchars($pID, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="grid-x grid-padding-x">
            <div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strType?>
                    <select name="pagina_tip" required>
                        <option value="0" <?php If ($row['pagina_tip']==0) echo "selected"?>><?php echo $strMaster?>
                        </option>
                        <option value="1" <?php If ($row['pagina_tip']==1) echo "selected"?>><?php echo $strSlave?>
                        </option>
                    </select></label>
            </div>
            <div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strMaster?>
                    <select name="pagina_master" required>
                        <option value="0"><?php echo $strMaster?></option>
                        <?php $sql = "Select pagina_id, pagina_titlu FROM cms_pagini WHERE pagina_tip='0' ORDER BY pagina_titlu ASC";
        $result = ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
		if ($row['pagina_master']==$rss['pagina_id']) {
	?>
                        <option selected value="<?php echo htmlspecialchars($rss["pagina_id"], ENT_QUOTES, 'UTF-8')?>"><?php echo htmlspecialchars($rss["pagina_titlu"], ENT_QUOTES, 'UTF-8')?>
                        </option>
                        <?php } else { ?>
                        <option value="<?php echo htmlspecialchars($rss["pagina_id"], ENT_QUOTES, 'UTF-8')?>"><?php echo htmlspecialchars($rss["pagina_titlu"], ENT_QUOTES, 'UTF-8')?></option>
                        <?php
}}?>
                    </select></label>
            </div>
            <div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strTitle?>
                    <input name="pagina_titlu" type="text" class="required" value="<?php echo htmlspecialchars($row['pagina_titlu'], ENT_QUOTES, 'UTF-8'); ?>" />
                </label>
            </div>
            <div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strURL?>
                    <input name="pagina_url" type="text" class="required" value="<?php echo htmlspecialchars($row['pagina_url'], ENT_QUOTES, 'UTF-8'); ?>" />
                </label>
            </div>
        </div>
        <div class="grid-x grid-padding-x">
            <div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strMainPicture?>
                    <input name="pagina_imaginetitlu" id="image" type="text" class="required"
                        value="<?php echo htmlspecialchars($row['pagina_imaginetitlu'], ENT_QUOTES, 'UTF-8'); ?>" readonly="readonly" />
                    <!-- Trigger/Open The Modal -->
                    <div class="full reveal" id="myModal" data-reveal>
                        <!-- Modal content -->
                        <iframe src="../common/image.php?directory=pages&field=image" frameborder="0" style="border:0"
                            Width="100%" height="750"></iframe>
                        <button class="close-button" data-close aria-label="Close modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <a href="#" class="button" data-open="myModal"><?php echo $strImage?></a>
                </label>
            </div>
            <div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strCategory?>
                    <input name="pagina_categorie" type="text" class="required"
                        value="<?php echo htmlspecialchars($row['pagina_categorie'], ENT_QUOTES, 'UTF-8'); ?>" />
                </label>
            </div>
            <div class="large-4 medium-4 small-4 cell">
                <label><?php echo $strPageDescription?>
                    <input name="pagina_descriere" type="text" class="required"
                        value="<?php echo htmlspecialchars($row['pagina_descriere'], ENT_QUOTES, 'UTF-8'); ?>" rows="6" />
                </label>
            </div>
        </div>
        <div class="grid-x grid-padding-x">
            <div class="large-6 medium-6 small-6 cell">
                <label><?php echo $strPageKeywords?>
                    <input name="pagina_keywords" type="text" class="required"
                        value="<?php echo htmlspecialchars($row['pagina_keywords'], ENT_QUOTES, 'UTF-8'); ?>" /></label>
            </div>
            <div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strPageNumber?>
                    <input name="pagina_numar" type="text" class="required number" size="3"
                        value="<?php echo htmlspecialchars($row['pagina_numar'], ENT_QUOTES, 'UTF-8'); ?>" /></label>
            </div>
            <div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strPageLanguage?>
                    <input name="pagina_limba" type="text" class="required" size="2"
                        value="<?php echo htmlspecialchars($row['pagina_limba'], ENT_QUOTES, 'UTF-8'); ?>" /></label>
            </div>
            <div class="large-2 medium-2 small-2 cell">
                <label><?php echo $strActive?>
                    <input name="pagina_status" type="radio" value="0"
                        <?php If ($row['pagina_status']==0) echo "checked"?> /> <?php echo $strYes?> &nbsp;
                    <input name="pagina_status" type="radio" value="1"
                        <?php If ($row['pagina_status']==1) echo "checked"?>> <?php echo $strNo?>
                </label>
            </div>
        </div>
        <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strPage?>
                    <textarea name="pagina_continut" class="simple-html-editor" data-upload-dir="pages" rows="10"><?php echo $row["pagina_continut"]?></textarea>
            </label>
        </div>
        </div>
        <div class="grid-x grid-padding-x">
            <div class="large-12 medium-12 small-12 cell text-center"><input type="submit" Value="<?php echo $strModify?>"
                    name="Submit" class="button"></div>
        </div>
    </form>
    <?php
} // ends editing
else
{ // just lists records
echo "<a href=\"sitepages.php?mode=new\" class=\"button\">$strAdd &nbsp;<i class=\"fas fa-plus\"></i></a>";
$stmt = $conn->prepare("SELECT * FROM cms_pagini WHERE pagina_tip='0'");
$stmt->execute();
$result = $stmt->get_result();
$numar = $result->num_rows;
if ($numar==0)
{
echo $strNoRecordsFound;
$stmt->close();
}
else {
?>
    <table>
        <thead>
            <tr>
                <th><?php echo $strID?></th>
                <th><?php echo $strMaster?></th>
                <th><?php echo $strTitle?></th>
                <th><?php echo $strPageDescription?></th>
                <th><?php echo $strEdit?></th>
                <th><?php echo $strDelete?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
While ($row=$result->fetch_assoc()){
    		$csrf_token = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
    		echo"<tr>
			<td>" . htmlspecialchars($row['pagina_id'], ENT_QUOTES, 'UTF-8') . "</td>
			<td>" . htmlspecialchars($row['pagina_master'], ENT_QUOTES, 'UTF-8') . "</td>
			<td>" . htmlspecialchars($row['pagina_titlu'], ENT_QUOTES, 'UTF-8') . "</td>
			<td>" . htmlspecialchars($row['pagina_descriere'], ENT_QUOTES, 'UTF-8') . "</td>
			<td><a href=\"sitepages.php?mode=edit&pID=" . htmlspecialchars($row['pagina_id'], ENT_QUOTES, 'UTF-8') . "\"><i class=\"fas fa-edit\"></i></a></td>
			<td><a href=\"sitepages.php?mode=delete&pID=" . htmlspecialchars($row['pagina_id'], ENT_QUOTES, 'UTF-8') . "&csrf_token=$csrf_token\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
		$subpage_master = intval($row['pagina_id']);
		$stmt_sub = $conn->prepare("SELECT * FROM cms_pagini WHERE pagina_master=? AND pagina_tip='1'");
		$stmt_sub->bind_param("i", $subpage_master);
		$stmt_sub->execute();
		$subpageresult = $stmt_sub->get_result();
$subpagenumber = $subpageresult->num_rows;
if ($subpagenumber!=0) {
	While ($rows=$subpageresult->fetch_assoc()){
    		echo"<tr>
			<td>" . htmlspecialchars($rows['pagina_id'], ENT_QUOTES, 'UTF-8') . "</td>
			<td>" . htmlspecialchars($rows['pagina_master'], ENT_QUOTES, 'UTF-8') . "</td>
			<td>" . htmlspecialchars($rows['pagina_titlu'], ENT_QUOTES, 'UTF-8') . "</td>
			<td>" . htmlspecialchars($rows['pagina_descriere'], ENT_QUOTES, 'UTF-8') . "</td>
			<td><a href=\"sitepages.php?mode=edit&pID=" . htmlspecialchars($rows['pagina_id'], ENT_QUOTES, 'UTF-8') . "\" ><i class=\"fas fa-edit\"></i></a></td>
			<td><a href=\"sitepages.php?mode=delete&pID=" . htmlspecialchars($rows['pagina_id'], ENT_QUOTES, 'UTF-8') . "&csrf_token=$csrf_token\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";	
		}}
		$stmt_sub->close();
}
$stmt->close();
echo "</tbody></table>";
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