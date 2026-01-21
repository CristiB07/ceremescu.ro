<?php
//update 8.01.2025

include '../settings.php';
include '../classes/common.php';

// Handle autosave AJAX request
if (isset($_POST['autosave']) && $_POST['autosave'] === '1') {
    header('Content-Type: application/json');
    
    // Validate required fields
    if (empty($_POST['articol_titlu']) || empty($_POST['articol_url']) || 
        !isset($_POST['articol_continut']) || strlen($_POST['articol_continut']) < 50) {
        echo json_encode(['success' => false, 'message' => 'Insufficient data for autosave']);
        exit();
    }
    
    $articol_ID = isset($_POST['articol_ID']) && is_numeric($_POST['articol_ID']) ? intval($_POST['articol_ID']) : 0;
    
    // Set defaults for optional fields
    $articol_categorie = $_POST['articol_categorie'] ?? '';
    $articol_descriere = $_POST['articol_descriere'] ?? '';
    $articol_tip = '0'; // Always save as draft during autosave
    $articol_data_publicarii = $_POST['articol_data_publicarii'] ?? date("Y-m-d H:i:s");
    $articol_autor = $_POST['articol_autor'] ?? '';
    $articol_imaginetitlu = $_POST['articol_imaginetitlu'] ?? '';
    $articol_keywords = $_POST['articol_keywords'] ?? '';
    
    if ($articol_ID > 0) {
        // Update existing draft
        $stmt = $conn->prepare("UPDATE blog_articole SET articol_titlu=?, articol_continut=?, articol_url=?, articol_categorie=?, articol_descriere=?, articol_data_publicarii=?, articol_autor=?, articol_imaginetitlu=?, articol_keywords=? WHERE articol_ID=?");
        $stmt->bind_param("sssssssssi", $_POST['articol_titlu'], $_POST['articol_continut'], $_POST['articol_url'], $articol_categorie, $articol_descriere, $articol_data_publicarii, $articol_autor, $articol_imaginetitlu, $articol_keywords, $articol_ID);
        
        if ($stmt->execute()) {
            $stmt->close();
            echo json_encode(['success' => true, 'message' => 'Autosaved', 'articol_ID' => $articol_ID]);
        } else {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Error updating draft']);
        }
    } else {
        // Create new draft
        $stmt = $conn->prepare("INSERT INTO blog_articole(articol_titlu, articol_continut, articol_url, articol_categorie, articol_descriere, articol_tip, articol_data_publicarii, articol_autor, articol_imaginetitlu, articol_keywords) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $_POST['articol_titlu'], $_POST['articol_continut'], $_POST['articol_url'], $articol_categorie, $articol_descriere, $articol_tip, $articol_data_publicarii, $articol_autor, $articol_imaginetitlu, $articol_keywords);
        
        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            $stmt->close();
            echo json_encode(['success' => true, 'message' => 'Autosaved', 'articol_ID' => $new_id]);
        } else {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Error creating draft']);
        }
    }
    exit();
}

$strPageTitle="Administrează articolele!";
include '../dashboard/header.php';
?>
<link rel="stylesheet" href="../js/simple-editor/simple-editor.css">
<script src="../js/simple-editor/simple-editor.js"></script>
<div class="grid-x grid-padding-x">
    <div class="large-12 cell">
        <?php

echo "<h1>".htmlspecialchars($strPageTitle, ENT_QUOTES, 'UTF-8')."</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

if (!isset($_GET['pID']) || !is_numeric($_GET['pID'])) {
    header("Location: blogarticles.php");
    exit();
}
$pID = intval($_GET['pID']);

$stmt = $conn->prepare("DELETE FROM blog_articole WHERE articol_ID=?");
$stmt->bind_param("i", $pID);
$stmt->execute();
$stmt->close();
echo "<div class=\"callout success\">".htmlspecialchars($strRecordDeleted, ENT_QUOTES, 'UTF-8')."</div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"blogarticles.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">
</div>";
include '../bottom.php';
exit();} // end delete record

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){
//insert new article

if (!isset($_POST['articol_tip']) || !in_array($_POST['articol_tip'], ['0', '1'], true)) {
    $_POST['articol_tip'] = '0';
}

$stmt = $conn->prepare("INSERT INTO blog_articole(articol_titlu, articol_continut, articol_url, articol_categorie, articol_descriere, articol_tip, articol_data_publicarii, articol_autor, articol_imaginetitlu, articol_keywords) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssssss", $_POST["articol_titlu"], $_POST["articol_continut"], $_POST["articol_url"], $_POST["articol_categorie"], $_POST["articol_descriere"], $_POST["articol_tip"], $_POST["articol_data_publicarii"], $_POST["articol_autor"], $_POST["articol_imaginetitlu"], $_POST["articol_keywords"]);
				
//It executes the SQL
if (!$stmt->execute())
  {
	  echo "<div class=\"callout alert\">".htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8')."</ br>Error: " . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8') . "</div></div></div><hr/>";
 $stmt->close();
 include '../bottom.php';
exit();
  }
else{
$stmt->close();
echo "<div class=\"callout success\">".htmlspecialchars($strRecordAdded, ENT_QUOTES, 'UTF-8')."</div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"blogarticles.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\"></div><hr />";
include '../bottom.php';
exit();
}}
//ends if post new
else
{// edit
if (!isset($_GET['pID']) || !is_numeric($_GET['pID'])) {
    header("Location: blogarticles.php");
    exit();
}
$pID = intval($_GET['pID']);

if (!isset($_POST['articol_tip']) || !in_array($_POST['articol_tip'], ['0', '1'], true)) {
    $_POST['articol_tip'] = '0';
}

$stmt = $conn->prepare("UPDATE blog_articole SET articol_titlu=?, articol_continut=?, articol_url=?, articol_categorie=?, articol_descriere=?, articol_tip=?, articol_data_publicarii=?, articol_autor=?, articol_imaginetitlu=?, articol_keywords=? WHERE articol_ID=?");
$stmt->bind_param("ssssssssssi", $_POST["articol_titlu"], $_POST["articol_continut"], $_POST["articol_url"], $_POST["articol_categorie"], $_POST["articol_descriere"], $_POST["articol_tip"], $_POST["articol_data_publicarii"], $_POST["articol_autor"], $_POST["articol_imaginetitlu"], $_POST["articol_keywords"], $pID);
if (!$stmt->execute())
  {
	  echo "<div class=\"callout alert\">".htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8')."</ br>Error: " . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8') . "</div></div></div><hr/>";
 $stmt->close();
 include '../bottom.php';
exit();
  }
else{
$stmt->close();
echo "<div class=\"callout success\">".htmlspecialchars($strRecordModified, ENT_QUOTES, 'UTF-8')."</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"blogarticles.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\"></div></div><hr />";
include '../bottom.php';
exit();
}
}
}// ends post if
else { // starts entering data

If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){ // we have new page
echo "<a href=\"blogarticles.php\" class=\"button\">".htmlspecialchars($strBack, ENT_QUOTES, 'UTF-8')." &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>";
?>
        <form method="post" id="users" Action="blogarticles.php?mode=new">
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-2 cell">
                  <label><?php echo htmlspecialchars($strTitle, ENT_QUOTES, 'UTF-8')?></label>
                        <input type="text" name="articol_titlu"  placeholder="<?php echo htmlspecialchars($strTitle, ENT_QUOTES, 'UTF-8')?>" required />
                </div>
                </div>
                 <div class="grid-x grid-padding-x">
                <div class="large-8 medium-8 small-8 cell">
                        <label><?php echo htmlspecialchars($strArticle, ENT_QUOTES, 'UTF-8')?></label>
                        <textarea name="articol_continut" class="simple-html-editor" rows="10"></textarea>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                       <label><?php echo htmlspecialchars($strURL, ENT_QUOTES, 'UTF-8')?></label>
                        <input type="text" name="articol_url"  placeholder="<?php echo htmlspecialchars($strURL, ENT_QUOTES, 'UTF-8')?>" required />
                <div class="large-4 medium-4 small-4 cell">
                       <label><?php echo htmlspecialchars($strDate, ENT_QUOTES, 'UTF-8')?>
                        <input type="text" name="articol_data_publicarii" value="<?php echo htmlspecialchars(date("Y-m-d H:i:s"), ENT_QUOTES, 'UTF-8');?>" required />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                       <label><?php echo htmlspecialchars($strStatus, ENT_QUOTES, 'UTF-8')?></label>
                       <input type="radio" name="articol_tip" value="1" id="puplished" checked>&nbsp;<?php echo htmlspecialchars($strPublished, ENT_QUOTES, 'UTF-8')?>
                        <input type="radio" name="articol_tip" value="0" id="draft">&nbsp;<?php echo htmlspecialchars($strDraft, ENT_QUOTES, 'UTF-8')?>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo htmlspecialchars($strMainPicture, ENT_QUOTES, 'UTF-8')?> </label>
                        <input name="articol_imaginetitlu" id="image" type="text" required readonly="readonly" />
                        <!-- Trigger/Open The Modal -->
                        <div class="full reveal" id="myModal" data-reveal>
                            <!-- Modal content -->
                            <iframe src="<?php echo htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8')?>/common/image.php?directory=blog&field=image"
                                frameborder="0" style="border:0" Width="100%" height="750"></iframe>
                            <button class="close-button" data-close aria-label="Close reveal" type="button">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <a data-open="myModal" class="button"><?php echo htmlspecialchars($strImage, ENT_QUOTES, 'UTF-8')?></a>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo htmlspecialchars($strCategory, ENT_QUOTES, 'UTF-8')?></label>
                        <input type="text" name="articol_categorie" placeholder="<?php echo htmlspecialchars($strCategory, ENT_QUOTES, 'UTF-8')?>" />
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo htmlspecialchars($strPageDescription, ENT_QUOTES, 'UTF-8')?>
                        <textarea name="articol_descriere" placeholder="<?php echo htmlspecialchars($strPageDescription, ENT_QUOTES, 'UTF-8')?>" required rows="6"></textarea></label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo htmlspecialchars($strPageKeywords, ENT_QUOTES, 'UTF-8')?></label>
                        <input type="text" name="articol_keywords" placeholder="<?php echo htmlspecialchars($strPageKeywords, ENT_QUOTES, 'UTF-8')?>" required />
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo htmlspecialchars($strAuthor, ENT_QUOTES, 'UTF-8')?></label>
                         <input type="text" name="articol_autor" placeholder="<?php echo htmlspecialchars($strAuthor, ENT_QUOTES, 'UTF-8')?>" required />
                </div>
            </div>
            </div>
    <div class="grid-x grid-padding-x">
        <div class="large-12 medium-12 small-12 cell text-center">
            <br />
            <span id="autosave-status" style="margin-right: 15px; font-style: italic; color: #666;"></span>
            <input type="hidden" id="autosave-id" value="">
            <input type="submit" Value="<?php echo htmlspecialchars($strAdd, ENT_QUOTES, 'UTF-8')?>" name="Submit" class="submit button">
        </div>
    </div>
    </form>
    
    <script>
    (function() {
        let autosaveTimer = null;
        let lastAutosaveData = '';
        const autosaveDelay = 3000; // 3 seconds after user stops typing
        
        function showAutosaveStatus(message, type = 'info') {
            const statusEl = document.getElementById('autosave-status');
            if (type === 'saving') {
                statusEl.textContent = '💾 ' + message;
                statusEl.style.color = '#0066cc';
            } else if (type === 'success') {
                statusEl.textContent = '✓ ' + message;
                statusEl.style.color = '#28a745';
                setTimeout(() => {
                    statusEl.textContent = '';
                }, 3000);
            } else if (type === 'error') {
                statusEl.textContent = '✗ ' + message;
                statusEl.style.color = '#dc3545';
            }
        }
        
        function canAutosave() {
            const title = document.querySelector('input[name="articol_titlu"]').value.trim();
            const url = document.querySelector('input[name="articol_url"]').value.trim();
            const content = document.querySelector('textarea[name="articol_continut"]').value.trim();
            
            return title.length > 0 && url.length > 0 && content.length >= 50;
        }
        
        function performAutosave() {
            if (!canAutosave()) {
                return;
            }
            
            const formData = new FormData(document.getElementById('users'));
            formData.append('autosave', '1');
            
            const autosaveId = document.getElementById('autosave-id').value;
            if (autosaveId) {
                formData.append('articol_ID', autosaveId);
            }
            
            // Check if data has changed
            const currentData = formData.get('articol_titlu') + formData.get('articol_url') + formData.get('articol_continut');
            if (currentData === lastAutosaveData) {
                return;
            }
            
            showAutosaveStatus('Saving...', 'saving');
            
            fetch('blogarticles.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    lastAutosaveData = currentData;
                    if (data.articol_ID) {
                        document.getElementById('autosave-id').value = data.articol_ID;
                    }
                    showAutosaveStatus('Saved at ' + new Date().toLocaleTimeString(), 'success');
                } else {
                    showAutosaveStatus('Save failed', 'error');
                }
            })
            .catch(error => {
                showAutosaveStatus('Save error', 'error');
                console.error('Autosave error:', error);
            });
        }
        
        function scheduleAutosave() {
            if (autosaveTimer) {
                clearTimeout(autosaveTimer);
            }
            autosaveTimer = setTimeout(performAutosave, autosaveDelay);
        }
        
        // Attach event listeners
        document.querySelector('input[name="articol_titlu"]').addEventListener('input', scheduleAutosave);
        document.querySelector('input[name="articol_url"]').addEventListener('input', scheduleAutosave);
        document.querySelector('textarea[name="articol_continut"]').addEventListener('input', scheduleAutosave);
        
        // Also monitor other fields for autosave
        const otherFields = ['articol_categorie', 'articol_descriere', 'articol_data_publicarii', 'articol_autor', 'articol_imaginetitlu', 'articol_keywords'];
        otherFields.forEach(fieldName => {
            const field = document.querySelector('[name="' + fieldName + '"]');
            if (field) {
                field.addEventListener('input', scheduleAutosave);
            }
        });
    })();
    </script>
    <?php
} // ends if new page
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
	echo "<a href=\"blogarticles.php\" class=\"button\">".htmlspecialchars($strBack, ENT_QUOTES, 'UTF-8')." &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>";

if (!isset($_GET['pID']) || !is_numeric($_GET['pID'])) {
    header("Location: blogarticles.php");
    exit();
}
$pID = intval($_GET['pID']);

$stmt = $conn->prepare("SELECT * FROM blog_articole WHERE articol_ID=?");
$stmt->bind_param("i", $pID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
?>
    <form method="post" action="blogarticles.php?mode=edit&pID=<?php echo htmlspecialchars($row['articol_ID'], ENT_QUOTES, 'UTF-8')?>">
          <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-2 cell">
                  <label><?php echo htmlspecialchars($strTitle, ENT_QUOTES, 'UTF-8')?></label>
                        <input type="text" name="articol_titlu" value="<?php echo htmlspecialchars($row['articol_titlu'] ?? '', ENT_QUOTES, 'UTF-8')?>" required />
                </div>
                </div>
                 <div class="grid-x grid-padding-x">
                <div class="large-8 medium-8 small-8 cell">
                        <label><?php echo htmlspecialchars($strArticle, ENT_QUOTES, 'UTF-8')?></label>
                        <textarea name="articol_continut" class="simple-html-editor" rows="10"><?php echo htmlspecialchars($row['articol_continut'] ?? '', ENT_QUOTES, 'UTF-8')?></textarea>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                       <label><?php echo htmlspecialchars($strURL, ENT_QUOTES, 'UTF-8')?></label>
                        <input type="text" name="articol_url" value="<?php echo htmlspecialchars($row['articol_url'] ?? '', ENT_QUOTES, 'UTF-8')?>" required />
    
                <div class="large-4 medium-4 small-4 cell">
                       <label><?php echo htmlspecialchars($strDate, ENT_QUOTES, 'UTF-8')?></label>
                        <input type="text" name="articol_data_publicarii" value="<?php echo htmlspecialchars($row['articol_data_publicarii'] ?? '', ENT_QUOTES, 'UTF-8')?>" required />
                </div>
                                <div class="large-4 medium-4 small-4 cell">
                       <label><?php echo htmlspecialchars($strStatus, ENT_QUOTES, 'UTF-8')?></label>
                       <input type="radio" name="articol_tip" value="1" id="puplished" <?php If ($row['articol_tip']==1){echo "checked=\"checked\"";}?>>&nbsp;<?php echo htmlspecialchars($strPublished, ENT_QUOTES, 'UTF-8')?>
                        <input type="radio" name="articol_tip" value="0" id="draft" <?php If ($row['articol_tip']==0){echo "checked=\"checked\"";}?>>&nbsp;<?php echo htmlspecialchars($strDraft, ENT_QUOTES, 'UTF-8')?>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo htmlspecialchars($strMainPicture, ENT_QUOTES, 'UTF-8')?>
                        <input name="articol_imaginetitlu" id="image" type="text" value="<?php echo htmlspecialchars($row['articol_imaginetitlu'] ?? '', ENT_QUOTES, 'UTF-8')?>" required readonly="readonly" />
                        <!-- Trigger/Open The Modal -->
                        <div class="full reveal" id="myModal" data-reveal>
                            <!-- Modal content -->
                            <iframe src="<?php echo htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8')?>/common/image.php?directory=blog&field=image"
                                frameborder="0" style="border:0" Width="100%" height="750"></iframe>
                            <button class="close-button" data-close aria-label="Close reveal" type="button">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <a data-open="myModal" class="button"><?php echo htmlspecialchars($strImage, ENT_QUOTES, 'UTF-8')?></a>
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo htmlspecialchars($strCategory, ENT_QUOTES, 'UTF-8')?></label>
                        <input type="text" name="articol_categorie" value="<?php echo htmlspecialchars($row['articol_categorie'] ?? '', ENT_QUOTES, 'UTF-8')?>" />
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo htmlspecialchars($strPageDescription, ENT_QUOTES, 'UTF-8')?></label>
                        <textarea name="articol_descriere" required rows="3"><?php echo htmlspecialchars($row['articol_descriere'] ?? '', ENT_QUOTES, 'UTF-8')?></textarea>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo htmlspecialchars($strPageKeywords, ENT_QUOTES, 'UTF-8')?></label>
                        <input type="text" name="articol_keywords" value="<?php echo htmlspecialchars($row['articol_keywords'] ?? '', ENT_QUOTES, 'UTF-8')?>" required />
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo htmlspecialchars($strAuthor, ENT_QUOTES, 'UTF-8')?></label>
                         <input type="text" name="articol_autor" value="<?php echo htmlspecialchars($row['articol_autor'] ?? '', ENT_QUOTES, 'UTF-8')?>" required />
                </div>
            </div>
            </div>
        <div class="grid-x grid-padding-x">
            <div class="large-12 medium-12 small-12 cell text-center"><input type="submit" Value="<?php echo htmlspecialchars($strModify, ENT_QUOTES, 'UTF-8')?>"
                    name="Submit" class="button"></div>
        </div>
    </form>
    <?php
} // ends editing
else
{ // just lists records
echo "<a href=\"blogarticles.php?mode=new\" class=\"button\">".htmlspecialchars($strAdd, ENT_QUOTES, 'UTF-8')." &nbsp;<i class=\"fas fa-plus\"></i></a>";
$stmt = $conn->prepare("SELECT * FROM blog_articole ORDER BY articol_ID DESC");
$stmt->execute();
$result = $stmt->get_result();
$numar = $result->num_rows;
if ($numar==0)
{
echo htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8');
}
else {
?>
    <table>
        <thead>
            <tr>
                <th><?php echo htmlspecialchars($strID, ENT_QUOTES, 'UTF-8')?></th>
                <th><?php echo htmlspecialchars($strTitle, ENT_QUOTES, 'UTF-8')?></th>
                <th><?php echo htmlspecialchars($strPageDescription, ENT_QUOTES, 'UTF-8')?></th>
                <th><?php echo htmlspecialchars($strStatus, ENT_QUOTES, 'UTF-8')?></th>
                <th><?php echo htmlspecialchars($strEdit, ENT_QUOTES, 'UTF-8')?></th>
                <th><?php echo htmlspecialchars($strDelete, ENT_QUOTES, 'UTF-8')?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
While ($row = $result->fetch_assoc()){
    		echo"<tr>
			<td>".htmlspecialchars($row['articol_ID'], ENT_QUOTES, 'UTF-8')."</td>
			<td>".htmlspecialchars($row['articol_titlu'], ENT_QUOTES, 'UTF-8')."</td>
			<td>".htmlspecialchars($row['articol_descriere'], ENT_QUOTES, 'UTF-8')."</td>
            <td>";
            if ($row['articol_status']==1){
                echo htmlspecialchars($strPublished, ENT_QUOTES, 'UTF-8');
            }
            else {
                echo htmlspecialchars($strDraft, ENT_QUOTES, 'UTF-8');
            }
            echo"</td>
			<td><a href=\"blogarticles.php?mode=edit&pID=".htmlspecialchars($row['articol_ID'], ENT_QUOTES, 'UTF-8')."\" ><i class=\"fas fa-edit\"></i></a></td>
			<td><a href=\"blogarticles.php?mode=delete&pID=".htmlspecialchars($row['articol_ID'], ENT_QUOTES, 'UTF-8')."\"  OnClick=\"return confirm('".htmlspecialchars($strConfirmDelete, ENT_QUOTES, 'UTF-8')."');\"><i class=\"fa fa-eraser fa-xl\" title=\"".htmlspecialchars($strDelete, ENT_QUOTES, 'UTF-8')."\"></i></a></td>
        </tr>";
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