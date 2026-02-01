<?php
//update 29.01.2026
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Vizualizare document";
include '../dashboard/header.php';
?>
<?php
if(!isset($_SESSION))
{
	session_start();
}
if (!isSet($_SESSION['userlogedin']))
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

$cID = intval($_GET['cID'] ?? 0);
if ($cID <= 0) {
	header("location:$strSiteURL/documents/sitedocuments.php?message=ER");
	exit();
}

// Get document
$stmt = $conn->prepare("SELECT * FROM documente WHERE document_id = ?");
$stmt->bind_param("i", $cID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
	header("location:$strSiteURL/documents/sitedocuments.php?message=ER");
	exit();
}
$row = $result->fetch_assoc();
$stmt->close();

// Check if user has signed
$document_lastupdated = $row['document_lastupdated'];
$stmt_sign = $conn->prepare("SELECT * FROM documente_semnaturi WHERE document_id = ? AND utilizator_ID = ? AND document_lastupdated = ?");
$stmt_sign->bind_param("iis", $cID, $uid, $document_lastupdated);
$stmt_sign->execute();
$result_sign = $stmt_sign->get_result();
$signed = $result_sign->num_rows > 0;
$sign_date = null;
if ($signed) {
	$sign_row = $result_sign->fetch_assoc();
	$sign_date = $sign_row['data_semnare'];
}
$stmt_sign->close();

// Handle signing
if (isset($_POST['sign_document']) && !$signed) {
	// CSRF validation
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		die('<div class="callout alert">Invalid CSRF token</div>');
	}

	// Check if agreement checkbox is checked
	if (!isset($_POST['agreement'])) {
		echo '<div class="callout alert">Trebuie să fiți de acord cu conținutul documentului pentru a-l semna.</div>';
	} else {
		$data_semnare = date("Y-m-d H:i:s");
		$stmt_insert = $conn->prepare("INSERT INTO documente_semnaturi (document_id, utilizator_ID, document_lastupdated, data_semnare) VALUES (?, ?, ?, ?)");
		$stmt_insert->bind_param("iiss", $cID, $uid, $document_lastupdated, $data_semnare);
		if ($stmt_insert->execute()) {
			echo '<div class="callout success">Documentul a fost semnat cu succes.</div>';
			$signed = true;
			$sign_date = $data_semnare;
		} else {
			echo '<div class="callout alert">Eroare la semnarea documentului.</div>';
		}
		$stmt_insert->close();
	}
}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <h2><?php echo htmlspecialchars($row['document_titlu'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <p><strong>Tip:</strong> <?php echo htmlspecialchars($row['document_tip'], ENT_QUOTES, 'UTF-8'); ?> | <strong>Categorie:</strong> <?php echo htmlspecialchars($row['document_categorie'], ENT_QUOTES, 'UTF-8'); ?> | <strong>Cod:</strong> <?php echo htmlspecialchars($row['document_cod'], ENT_QUOTES, 'UTF-8'); ?> | <strong>Versiune:</strong> <?php echo htmlspecialchars($row['document_versiune'], ENT_QUOTES, 'UTF-8'); ?></p>

        <h2>Versiuni</h2>
        <?php
        // Get all versions of this document with author names
        $stmt_versions = $conn->prepare("SELECT d.document_versiune, CONCAT(u.utilizator_Prenume, ' ', u.utilizator_Nume) as autor_nume, d.document_lastupdated FROM documente d LEFT JOIN date_utilizatori u ON d.document_autor = u.utilizator_ID WHERE d.document_cod = ? ORDER BY d.document_lastupdated DESC");
        $stmt_versions->bind_param("s", $row['document_cod']);
        $stmt_versions->execute();
        $result_versions = $stmt_versions->get_result();
        ?>
        <table>
            <thead>
                <tr>
                    <th>Versiunea</th>
                    <th>Autor</th>
                    <th>Data intrării în vigoare</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($version_row = $result_versions->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($version_row['document_versiune'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($version_row['autor_nume'] ?? 'Necunoscut', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo date("d.m.Y H:i", strtotime($version_row['document_lastupdated'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php $stmt_versions->close(); ?>

        <div class="document-content">
            <?php echo $row['document_continut']; ?>
        </div>

        <?php if ($row['document_atasamente']): 
            $attachments = unserialize($row['document_atasamente']);
            if (!empty($attachments)): ?>
        <div class="callout">
            <strong>Atașamente:</strong>
            <ul>
                <?php foreach ($attachments as $file): ?>
                <li><a href="download.php?folder=<?php echo urlencode($documents_folder); ?>&file=<?php echo urlencode($file); ?>" target="_blank"><?php echo htmlspecialchars($file, ENT_QUOTES, 'UTF-8'); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <?php if ($signed): ?>
            <div class="callout success">
                <h5>Ai semnat acest document</h5>
                <p>Data semnării: <?php echo date("d.m.Y H:i", strtotime($sign_date)); ?></p>
            </div>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="callout secondary">
                    <label>
                        <input type="checkbox" name="agreement" id="agreement_checkbox" required>
                        Am citit acest document și sunt de acord să respect conținutul său
                    </label>
                </div>
                <input type="submit" name="sign_document" id="sign_button" class="button" value="Semnează documentul" style="display: none;">
            </form>
        <?php endif; ?>

        <a href="sitedocuments.php" class="button secondary">Înapoi la listă</a>
        <?php if ($role == 'ADMIN'): ?>
        <a href="sitedocuments.php?mode=edit&cID=<?php echo $cID; ?>" class="button">Primit la editare</a>
        <?php endif; ?>
    </div>
</div>
<script>
document.getElementById('agreement_checkbox').addEventListener('change', function() {
    const signButton = document.getElementById('sign_button');
    if (this.checked) {
        signButton.style.display = 'inline-block';
    } else {
        signButton.style.display = 'none';
    }
});
</script>
<?php
include '../bottom.php';
?>