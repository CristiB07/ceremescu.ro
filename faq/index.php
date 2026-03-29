<?php
// FAQ — public list (accordion)
include_once '../settings.php';
include_once '../classes/common.php';

$strPageTitle = isset($strQuestions) ? $strQuestions : 'FAQ';
include '../header.php';
?>
<div class="grid-x grid-padding-x">
    <div class="large-12 cell">
        <h1><?php echo htmlspecialchars($strPageTitle, ENT_QUOTES, 'UTF-8')?></h1>
        <?php
        // Retrieve all FAQs ordered by category then id
        $stmt = $conn->prepare("SELECT faq_id, faq_q, faq_a, faq_cat FROM cms_faq ORDER BY COALESCE(faq_cat, ''), faq_id ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            echo '<div class="callout alert">' . htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8') . '</div>';
        } else {
            // Group by category
            $groups = [];
            while ($row = $result->fetch_assoc()) {
                $cat = trim($row['faq_cat'] ?? '');
                if ($cat === '') $cat = 'General';
                if (!isset($groups[$cat])) $groups[$cat] = [];
                $groups[$cat][] = $row;
            }

            echo '<ul class="accordion" data-accordion data-allow-all-closed="true">';
            $catIndex = 0;
            foreach ($groups as $catName => $items) {
                $catIndex++;
                $catId = 'cat-' . $catIndex;
                echo '<li class="accordion-item" data-accordion-item>';
                echo '<a href="#' . $catId . '" class="accordion-title">' . htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') . '</a>';
                echo '<div id="' . $catId . '" class="accordion-content" data-tab-content>';
                // inner accordion for questions
                echo '<ul class="accordion" data-accordion data-allow-all-closed="true">';
                foreach ($items as $row) {
                    $id = (int)$row['faq_id'];
                    $q = htmlspecialchars($row['faq_q'], ENT_QUOTES, 'UTF-8');
                    // Ensure any stored HTML entities are decoded so HTML formatting renders
                    $a = html_entity_decode($row['faq_a'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    echo '<li class="accordion-item" data-accordion-item>';
                    echo '<a href="#faq-' . $id . '" class="accordion-title">' . $q . '</a>';
                    echo '<div id="faq-' . $id . '" class="accordion-content" data-tab-content>' . $a . '</div>';
                    echo '</li>';
                }
                echo '</ul>';
                echo '</div>';
                echo '</li>';
            }
            echo '</ul>';
        }
        $stmt->close();
        ?>
    </div>
</div>
<?php include '../bottom.php'; ?>