<?php
//update 29.01.2026
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Semnare document";
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

$uid = $_SESSION['uid'];

if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
	header("location:$strSiteURL/admin/sitedocuments.php?message=ER");
	exit();
}

$cID = intval($_GET['cID']);

// Get document details
$stmt = $conn->prepare("SELECT * FROM documente WHERE document_id = ?");
$stmt->bind_param("i", $cID);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();
$stmt->close();

if (!$document) {
	echo "<div class=\"callout alert\">Documentul nu există.</div>";
	include '../bottom.php';
	exit();
}

// Check if user has already signed the current version
$stmt = $conn->prepare("SELECT * FROM documente_semnaturi WHERE semnatura_document = ? AND semnatura_user = ? ORDER BY semnatura_data DESC LIMIT 1");
$stmt->bind_param("ii", $cID, $uid);
$stmt->execute();
$result = $stmt->get_result();
$signature = $result->fetch_assoc();
$stmt->close();

$needs_new_signature = true;
if ($signature) {
	// Compare last updated times
	if ($signature['semnatura_lastupdated'] >= $document['document_lastupdated']) {
		$needs_new_signature = false;
	}
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sign']) && $needs_new_signature) {
	// Generate CSRF token if not set
	if (!isset($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	// CSRF validation
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		die('<div class="callout alert">Invalid CSRF token</div>');
	}

	$data_semnare = date("Y-m-d H:i:s");
	$lastupdated = $document['document_lastupdated'];

	$stmt = $conn->prepare("INSERT INTO documente_semnaturi (semnatura_document, semnatura_lastupdated, semnatura_user, semnatura_data) VALUES (?, ?, ?, ?)");
	$stmt->bind_param("isis", $cID, $lastupdated, $uid, $data_semnare);
	if ($stmt->execute()) {
		echo "<div class=\"callout success\">Documentul a fost semnat cu succes.</div>";
		$needs_new_signature = false;
	} else {
		echo "<div class=\"callout alert\">Eroare la semnarea documentului: " . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8') . "</div>";
	}
	$stmt->close();
}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <h1><?php echo htmlspecialchars($document['document_titlu'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <p><strong>Tip:</strong> <?php echo htmlspecialchars($document['document_tip'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Categorie:</strong> <?php echo htmlspecialchars($document['document_categorie'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Ultima actualizare:</strong> <?php echo $document['document_lastupdated']; ?></p>
        <div><?php echo $document['document_continut']; ?></div>
        <?php if ($document['document_atasamente']): ?>
        <p><strong>Atașamente:</strong> <a href="<?php echo htmlspecialchars($document['document_atasamente'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank">Descarcă</a></p>
        <?php endif; ?>

        <?php if ($signature && !$needs_new_signature): ?>
        <div class="callout success">
            <p>Ați semnat deja această versiune a documentului pe data: <?php echo $signature['semnatura_data']; ?></p>
        </div>
        <?php elseif ($needs_new_signature): ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)); ?>">
            <p>Dacă sunteți de acord cu conținutul documentului, semnați-l apăsând butonul de mai jos.</p>
            <input type="submit" name="sign" value="Semnează documentul" class="button success">
        </form>
        <?php endif; ?>

        <a href="<?php echo $strSiteURL ?>/admin/sitedocuments.php" class="button"><?php echo $strBack; ?></a>
    </div>
</div>
<?php include '../bottom.php'; ?>