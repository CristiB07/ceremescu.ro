<?php
//update 29.01.2026
include '../settings.php';
include '../classes/common.php';

if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes")
{
	header("location:$strSiteURL/login/index.php?message=MLF");
	exit();
}

$role = $_SESSION['clearence'];
$uid = $_SESSION['uid'];

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$is_admin = ($role == 'ADMIN');

$is_admin = ($role == 'ADMIN');

// Handle delete
if (isset($_GET['mode']) && $_GET['mode'] == "delete" && $is_admin) {
	// CSRF validation
	if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
		die('<div class="callout alert">Invalid CSRF token</div>');
	}

	$cID = intval($_GET['cID']);
	if ($cID <= 0) {
		die('<div class="callout alert">Invalid ID</div>');
	}

	$stmt = $conn->prepare("DELETE FROM documente WHERE document_id = ?");
	$stmt->bind_param("i", $cID);
	$stmt->execute();
	$stmt->close();

	echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
	echo "<script type=\"text/javascript\">
	<!--
	function delayer(){
	    window.location = \"sitedocuments.php\"
	}
	//-->
	</script>
	<body onLoad=\"setTimeout('delayer()', 1500)\">";
	include '../bottom.php';
	die;
}

// Handle delete file
if (isset($_POST['delete_file']) && $is_admin) {
	// CSRF validation
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		die('<div class="callout alert">Invalid CSRF token</div>');
	}

	$cID = intval($_POST['cID']);
	$file_to_delete = htmlspecialchars(trim($_POST['delete_file']), ENT_QUOTES, 'UTF-8');

	// Get current attachments
	$stmt = $conn->prepare("SELECT document_atasamente FROM documente WHERE document_id = ?");
	$stmt->bind_param("i", $cID);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	$stmt->close();

	$attachments = unserialize($row['document_atasamente'] ?? serialize([]));
	if (($key = array_search($file_to_delete, $attachments)) !== false) {
		unset($attachments[$key]);
		// Delete physical file
		$file_path = $hddpath . '/' . $documents_folder . '/' . $file_to_delete;
		if (file_exists($file_path)) {
			unlink($file_path);
		}
		// Update DB
		$stmt = $conn->prepare("UPDATE documente SET document_atasamente = ? WHERE document_id = ?");
		$serialized = serialize(array_values($attachments));
		$stmt->bind_param("si", $serialized, $cID);
		$stmt->execute();
		$stmt->close();
	}

	header("Location: sitedocuments.php?mode=edit&cID=$cID");
	exit();
}

// Handle POST (insert/update)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_admin && !isset($_POST['delete_file'])) {
	$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
	$cID = isset($_POST['cID']) ? intval($_POST['cID']) : 0;
	if (empty($mode)) {
		die('Invalid mode');
	}
	// CSRF validation
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		die('<div class="callout alert">Invalid CSRF token</div>');
	}

	$tip = htmlspecialchars(trim($_POST["tip"]), ENT_QUOTES, 'UTF-8');
	$categorie = htmlspecialchars(trim($_POST["categorie"]), ENT_QUOTES, 'UTF-8');
	$titlu = htmlspecialchars(trim($_POST["titlu"]), ENT_QUOTES, 'UTF-8');
	$continut = $_POST["continut"]; // Allow HTML for content
	$cod = $_POST["document_cod"]; // Allow HTML for content
	$versiune = (int)$_POST["versiune"];
	$lastupdated = date("Y-m-d H:i:s");

	if (empty($titlu)) {
		die('<div class="callout alert">Titlul este obligatoriu</div>');
	}

	// Determine version
	if ($mode == "edit") {
		$stmt = $conn->prepare("SELECT document_versiune, document_autor FROM documente WHERE document_id = ?");
		$stmt->bind_param("i", $cID);
		$stmt->execute();
		$result = $stmt->get_result();
		$row_db = $result->fetch_assoc();
		$stmt->close();
		$current_version = $row_db['document_versiune'] ?? 0;
		$original_autor = $row_db['document_autor'];
		
		if ($versiune > $current_version) {
			$is_new_version = true;
		} else {
			$is_new_version = false;
		}
	} else {
		$is_new_version = false;
		$original_autor = null;
	}

	$autor = $mode == "edit" && $is_new_version ? $original_autor : $uid;

	// Handle file uploads
	$attachments = [];
	if ($mode == "edit") {
		$stmt = $conn->prepare("SELECT document_atasamente FROM documente WHERE document_id = ?");
		$stmt->bind_param("i", $cID);
		$stmt->execute();
		$result = $stmt->get_result();
		$row_attach = $result->fetch_assoc();
		$stmt->close();
		$attachments = unserialize($row_attach['document_atasamente'] ?? serialize([]));
	}

	// Create documents folder if not exists
	$docs_dir = $hddpath . '/' . $documents_folder;
	if (!is_dir($docs_dir)) {
		mkdir($docs_dir, 0755, true);
	}

	// Process uploaded files
	if (isset($_FILES['atasamente']) && is_array($_FILES['atasamente']['name'])) {
		foreach ($_FILES['atasamente']['name'] as $key => $name) {
			if ($_FILES['atasamente']['error'][$key] == UPLOAD_ERR_OK) {
				$tmp_name = $_FILES['atasamente']['tmp_name'][$key];
				$safe_name = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $name);
				$unique_name = time() . '_' . $safe_name;
				$destination = $docs_dir . '/' . $unique_name;
				if (move_uploaded_file($tmp_name, $destination)) {
					$attachments[] = $unique_name;
				}
			}
		}
	}

	$atasamente_serialized = serialize($attachments);

	if ($mode == "edit" && !$is_new_version) {
		$stmt = $conn->prepare("UPDATE documente SET document_tip=?, document_categorie=?, document_titlu=?, document_continut=?, document_cod=?, document_lastupdated=?, document_atasamente=?, document_versiune=? WHERE document_id=?");
		$stmt->bind_param("sssssssii", $tip, $categorie, $titlu, $continut, $cod, $lastupdated, $atasamente_serialized, $versiune, $cID);
	} else {
		$stmt = $conn->prepare("INSERT INTO documente (document_tip, document_categorie, document_titlu, document_continut, document_cod, document_lastupdated, document_atasamente, document_autor, document_versiune) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("sssssssii", $tip, $categorie, $titlu, $continut, $cod, $lastupdated, $atasamente_serialized, $autor, $versiune);
	}

	if (!$stmt->execute()) {
		die('Error: ' . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8'));
	}
	$stmt->close();

	$message = $mode == "edit" ? $strRecordModified : $strRecordAdded;
	$_SESSION['message'] = $message;
	if ($mode == "edit") {
		header("Location: sitedocuments.php?updated=1");
	} else {
		header("Location: sitedocuments.php?added=1");
	}
	exit();
}

// Display form or list
$strPageTitle="Administrare documente";
include '../dashboard/header.php';
if (isset($_SESSION['message'])) {
    echo "<div class=\"callout success\">" . htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8') . "</div>";
    unset($_SESSION['message']);
}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <h1><?php echo $strPageTitle; ?></h1>
        <?php if (isset($_GET['updated'])): ?>
        <div class="callout success">Înregistrarea a fost actualizată cu succes.</div>
        <?php elseif (isset($_GET['added'])): ?>
        <div class="callout success">Înregistrarea a fost adăugată cu succes.</div>
        <?php endif; ?>
        <?php if ($is_admin): ?>
        <a href="sitedocuments.php?mode=new" class="button"><?php echo $strAddNew; ?> <i class="fas fa-plus"></i></a>
        <?php endif; ?>
    </div>
</div>

<?php
if (isset($_GET['mode']) && ($_GET['mode'] == "new" || $_GET['mode'] == "edit") && $is_admin) {
	$row = [];
	if ($_GET['mode'] == "edit") {
		$cID = intval($_GET['cID']);
		$stmt = $conn->prepare("SELECT * FROM documente WHERE document_id = ?");
		$stmt->bind_param("i", $cID);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_assoc();
		$stmt->close();
		if (!$row) {
			echo "<div class=\"callout alert\">Document not found</div>";
			include '../bottom.php';
			die;
		}
	}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <form method="post" enctype="multipart/form-data" action="sitedocuments.php">
            <input type="hidden" name="mode" value="<?php echo $_GET['mode']; ?>">
            <?php if ($_GET['mode'] == 'edit'): ?>
            <input type="hidden" name="cID" value="<?php echo $cID; ?>">
            <?php endif; ?>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-6 cell">
                    <label>Tip document
                        <select name="tip" required>
                            <option value="">Selectați tip</option>
                            <option value="Procedură" <?php echo ($row['document_tip'] ?? '') == 'Procedură' ? 'selected' : ''; ?>>Procedură</option>
                            <option value="Instrucțiune de lucru" <?php echo ($row['document_tip'] ?? '') == 'Instrucțiune de lucru' ? 'selected' : ''; ?>>Instrucțiune de lucru</option>
                            <option value="Regulament" <?php echo ($row['document_tip'] ?? '') == 'Regulament' ? 'selected' : ''; ?>>Regulament</option>
                            <option value="Altele" <?php echo ($row['document_tip'] ?? '') == 'Altele' ? 'selected' : ''; ?>>Altele</option>
                        </select>
                    </label>
                </div>
                <div class="large-3 medium-3 small-6 cell">
                    <label>Categorie
                        <select name="categorie" required>
                            <option value="">Selectați categorie</option>
                            <option value="ISO" <?php echo ($row['document_categorie'] ?? '') == 'ISO' ? 'selected' : ''; ?>>ISO</option>
                            <option value="GDPR" <?php echo ($row['document_categorie'] ?? '') == 'GDPR' ? 'selected' : ''; ?>>GDPR</option>
                            <option value="Operaționale" <?php echo ($row['document_categorie'] ?? '') == 'Operaționale' ? 'selected' : ''; ?>>Operaționale</option>
                            <option value="SSM&SU" <?php echo ($row['document_categorie'] ?? '') == 'SSM&SU' ? 'selected' : ''; ?>>SSM&SU</option>
                        </select>
                    </label>
                </div>
                <div class="large-3 medium-3 small-6 cell">
                    <label>Cod
                        <input type="text" name="document_cod" value="<?php echo htmlspecialchars($row['document_cod'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>
                </div>
                
                <div class="large-1 medium-1 small-6 cell">
                    <label>Versiune
                        <input type="number" name="versiune" value="<?php echo htmlspecialchars($row['document_versiune'] ?? 1, ENT_QUOTES, 'UTF-8'); ?>" required min="1">
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label>Titlu
                        <input type="text" name="titlu" value="<?php echo htmlspecialchars($row['document_titlu'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label>Conținut
                        <textarea name="continut" id="continut" rows="10" class="simple-html-editor" data-upload-dir="documents"><?php echo htmlspecialchars($row['document_continut'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label>Atașamente (încarcă fișiere noi)
                        <input type="file" name="atasamente[]" multiple>
                    </label>
                    <?php if (isset($_GET['mode']) && $_GET['mode'] == "edit" && !empty($row['document_atasamente'])): 
                        $existing_attachments = unserialize($row['document_atasamente']);
                        if (!empty($existing_attachments)): ?>
                    <div class="callout">
                        <h5>Fișiere atașate:</h5>
                        <ul>
                            <?php foreach ($existing_attachments as $file): ?>
                            <li>
                                <a href="<?php echo $strSiteURL ?>/download.php?file=<?php echo urlencode($documents_folder . '/' . $file); ?>" target="_blank"><?php echo htmlspecialchars($file, ENT_QUOTES, 'UTF-8'); ?></a>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="cID" value="<?php echo $cID; ?>">
                                    <input type="hidden" name="delete_file" value="<?php echo htmlspecialchars($file, ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="button tiny alert" onclick="return confirm('Ștergeți fișierul?')"><i class="fas fa-eraser"></i></button>
                                </form>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <button type="submit" class="button"><?php echo $_GET['mode'] == 'edit' ? $strUpdate : $strAdd; ?></button>
                    <a href="sitedocuments.php" class="button secondary"><?php echo $strCancel; ?></a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
} else {
	// List documents
	$query = "SELECT d.*, u.utilizator_Prenume, u.utilizator_Nume FROM documente d LEFT JOIN date_utilizatori u ON d.document_autor = u.utilizator_ID ORDER BY d.document_lastupdated DESC";
	$result = ezpub_query($conn, $query);
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tip</th>
                    <th>Categorie</th>
                    <th>Cod</th>
                    <th>Versiune</th>
                    <th>Titlu</th>
                    <th>Ultima actualizare</th>
                    <th>Autor</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = ezpub_fetch_array($result)): ?>
                <tr>
                    <td><?php echo $row['document_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['document_tip'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['document_categorie'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['document_cod'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['document_versiune'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['document_titlu'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo $row['document_lastupdated']; ?></td>
                    <td><?php echo htmlspecialchars(($row['utilizator_Prenume'] ?? '') . ' ' . ($row['utilizator_Nume'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <a href="sitedocuments.php?mode=view&cID=<?php echo $row['document_id']; ?>" class="button small"><?php echo $strView; ?></a>
                        <?php if ($is_admin): ?>
                        <a href="sitedocuments.php?mode=edit&cID=<?php echo $row['document_id']; ?>" class="button small secondary"><?php echo $strEdit; ?></a>
                        <a href="sitedocuments.php?mode=delete&cID=<?php echo $row['document_id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="button small alert" onclick="return confirm('<?php echo $strConfirmDelete; ?>')"><?php echo $strDelete; ?></a>
                        <?php endif; ?>
                        <a href="viewdocument.php?cID=<?php echo $row['document_id']; ?>" class="button small success">Vizualizare</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
}

// Handle view mode
if (isset($_GET['mode']) && $_GET['mode'] == "view") {
	$cID = intval($_GET['cID']);
	$stmt = $conn->prepare("SELECT * FROM documente WHERE document_id = ?");
	$stmt->bind_param("i", $cID);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	$stmt->close();
	if ($row) {
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <h2><?php echo htmlspecialchars($row['document_titlu'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <p><strong>Tip:</strong> <?php echo htmlspecialchars($row['document_tip'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Categorie:</strong> <?php echo htmlspecialchars($row['document_categorie'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Cod:</strong> <?php echo htmlspecialchars($row['document_cod'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Versiune:</strong> <?php echo htmlspecialchars($row['document_versiune'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Ultima actualizare:</strong> <?php echo $row['document_lastupdated']; ?></p>
        <div><?php echo $row['document_continut']; ?></div>
        <?php if ($row['document_atasamente']): 
            $attachments = unserialize($row['document_atasamente']);
            if (!empty($attachments)): ?>
        <div>
            <strong>Atașamente:</strong>
            <ul>
                <?php foreach ($attachments as $file): ?>
                <li><a href="<?php echo $strSiteURL ?>/download.php?file=<?php echo urlencode($documents_folder . '/' . $file); ?>" target="_blank"><?php echo htmlspecialchars($file, ENT_QUOTES, 'UTF-8'); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <a href="sitedocuments.php" class="button"><?php echo $strBack; ?></a>
    </div>
</div>
<?php
	}
}
?>
<?php include '../bottom.php'; ?>