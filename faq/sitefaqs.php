<?php
// Admin: manage FAQ (add / edit / delete)
// Follows same pattern as other `site*` admin pages
if(!isset($_SESSION)) { session_start(); }
include '../settings.php';
include '../classes/common.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$strPageTitle = 'Administrare FAQ';
include '../dashboard/header.php';

echo "<div class=\"grid-x grid-padding-x\"><div class=\"large-12 cell\"><h1>$strPageTitle</h1>";

// Delete
if (isset($_GET['mode']) && $_GET['mode'] === 'delete') {
    // Validate CSRF token & ID
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        die('<div class="callout alert">Invalid CSRF token</div>');
    }
    if (!isset($_GET['faqID']) || !is_numeric($_GET['faqID'])) {
        die('<div class="callout alert">Invalid FAQ ID</div>');
    }
    $faqID = (int)$_GET['faqID'];

    $stmt = $conn->prepare("DELETE FROM cms_faq WHERE faq_id = ?");
    $stmt->bind_param('i', $faqID);
    $stmt->execute();
    $stmt->close();

    echo "<div class=\"callout success\">$strRecordDeleted</div>";
    echo "<script>setTimeout(function(){ window.location='sitefaqs.php'; },1500);</script></div></div>";
    include '../bottom.php';
    exit();
}

// Handle POST (insert / update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_inject();

    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('<div class="callout alert">Invalid CSRF token</div>');
    }

    $faq_q = isset($_POST['faq_q']) ? trim($_POST['faq_q']) : '';
    $faq_a = isset($_POST['faq_a']) ? $_POST['faq_a'] : '';
    $faq_cat = isset($_POST['faq_cat']) ? trim($_POST['faq_cat']) : '';

    if (isset($_GET['mode']) && $_GET['mode'] === 'new') {
        $stmt = $conn->prepare("INSERT INTO cms_faq (faq_q, faq_a, faq_cat) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $faq_q, $faq_a, $faq_cat);
        if (!$stmt->execute()) {
            $stmt->close();
            die('<div class="callout alert">' . $strThereWasAnError . '<br />Error: ' . $conn->error . '</div>');
        }
        $stmt->close();
        echo "<div class=\"callout success\">$strRecordAdded</div>";
        echo "<script>setTimeout(function(){ window.location='sitefaqs.php'; },1500);</script></div></div>";
        include '../bottom.php';
        exit();
    } else { // edit
        if (!isset($_GET['faqID']) || !is_numeric($_GET['faqID'])) {
            die('<div class="callout alert">Invalid FAQ ID</div>');
        }
        $faqID = (int)$_GET['faqID'];
        $stmt = $conn->prepare("UPDATE cms_faq SET faq_q = ?, faq_a = ?, faq_cat = ? WHERE faq_id = ?");
        $stmt->bind_param('sssi', $faq_q, $faq_a, $faq_cat, $faqID);
        if (!$stmt->execute()) {
            $stmt->close();
            die('<div class="callout alert">' . $strThereWasAnError . '<br />Error: ' . $conn->error . '</div>');
        }
        $stmt->close();
        echo "<div class=\"callout success\">$strRecordModified</div>";
        echo "<script>setTimeout(function(){ window.location='sitefaqs.php'; },1500);</script></div></div>";
        include '../bottom.php';
        exit();
    }
}

// Show forms / list
if (isset($_GET['mode']) && $_GET['mode'] === 'new') {
    echo "<a href=\"sitefaqs.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>";
    ?>
    <form method="post" action="sitefaqs.php?mode=new">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="grid-x grid-padding-x">
                    <div class="large-12 cell">
                        <label>Categoria
                            <input type="text" name="faq_cat" maxlength="50" />
                        </label>
                    </div>
                    <div class="large-12 cell">
                        <label>Întrebare
                            <input type="text" name="faq_q" required />
                        </label>
                    </div>
            <div class="large-12 cell">
                <label>Răspuns
                    <textarea name="faq_a" class="simple-html-editor" data-upload-dir="faq" rows="8"></textarea>
                </label>
            </div>
            <div class="large-12 cell text-center">
                <input type="submit" value="<?php echo $strAdd?>" class="button success" />
            </div>
        </div>
    </form>
    <?php
} elseif (isset($_GET['mode']) && $_GET['mode'] === 'edit') {
    if (!isset($_GET['faqID']) || !is_numeric($_GET['faqID'])) {
        die('<div class="callout alert">Invalid FAQ ID</div>');
    }
    $faqID = (int)$_GET['faqID'];
    $stmt = $conn->prepare("SELECT faq_id, faq_q, faq_a, faq_cat FROM cms_faq WHERE faq_id = ?");
    $stmt->bind_param('i', $faqID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    echo "<a href=\"sitefaqs.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>";
    ?>
    <form method="post" action="sitefaqs.php?mode=edit&faqID=<?php echo $faqID?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="grid-x grid-padding-x">
            <div class="large-12 cell">
                <label>Categoria
                    <input type="text" name="faq_cat" maxlength="50" value="<?php echo htmlspecialchars($row['faq_cat'] ?? '', ENT_QUOTES, 'UTF-8')?>" />
                </label>
            </div>
            <div class="large-12 cell">
                <label>Întrebare
                    <input type="text" name="faq_q" required value="<?php echo htmlspecialchars($row['faq_q'], ENT_QUOTES, 'UTF-8')?>" />
                </label>
            </div>
            <div class="large-12 cell">
                <label>Răspuns
                    <textarea name="faq_a" class="simple-html-editor" data-upload-dir="faq" rows="8"><?php echo html_entity_decode($row['faq_a'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')?></textarea>
                </label>
            </div>
            <div class="large-12 cell text-center">
                <input type="submit" value="<?php echo $strModify?>" class="button" />
            </div>
        </div>
    </form>
    <?php
} else {
    // list
    echo "<a href=\"sitefaqs.php?mode=new\" class=\"button\">$strAdd &nbsp;<i class=\"fas fa-plus\"></i></a><br /><br />";
    $stmt = $conn->prepare("SELECT faq_id, faq_q, faq_cat FROM cms_faq ORDER BY faq_cat ASC, faq_id ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        echo '<div class="callout alert">' . htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8') . '</div>';
    } else {
            echo '<table class="stack">';
        echo '<thead><tr><th>ID</th><th>Categoria</th><th>Întrebare</th><th></th></tr></thead><tbody>';
        while ($row = $result->fetch_assoc()) {
            $id = (int)$row['faq_id'];
            $q = htmlspecialchars($row['faq_q'], ENT_QUOTES, 'UTF-8');
            $cat = htmlspecialchars($row['faq_cat'] ?? '', ENT_QUOTES, 'UTF-8');
            $deleteUrl = "sitefaqs.php?mode=delete&faqID=$id&csrf_token=" . urlencode($_SESSION['csrf_token']);
            echo "<tr>";
            echo "<td>$id</td>";
            echo "<td>$cat</td>";
            echo "<td>$q</td>";
            echo "<td style=\"white-space:nowrap\">";
            echo "<a href=\"sitefaqs.php?mode=edit&faqID=$id\" class=\"button small\"><i class=\"fas fa-edit\"></i></a> ";
            echo "<a href=\"$deleteUrl\" onclick=\"return confirm('" . addslashes($strConfirmDelete) . "')\" class=\"alert button small\"><i class=\"fas fa-trash\"></i></a>";
            echo "</td>";
            echo "</tr>";
        }
        echo '</tbody></table>';
    }
    $stmt->close();
}

echo "</div></div>";
include '../bottom.php';
?>