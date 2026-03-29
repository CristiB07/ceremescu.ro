<?php
// Sitemap generator
// - collects URLs from: cms_pagini (pagina_url), blog_articole (articol_url), magazin_produse (produs_url), elearning_courses (course_url)
// - adds a few static pages
// - outputs XML sitemap (sitemaps.org)

include_once 'settings.php';
include_once 'classes/common.php';

header('Content-Type: application/xml; charset=utf-8');
// buffer output so we can recover on fatal errors and always produce a well-formed XML
ob_start();
$sitemap_closed = false;
register_shutdown_function(function() {
    global $sitemap_closed;
    if (!$sitemap_closed) {
        // try to close the root element to avoid "Premature end of data" errors
        echo "</urlset>";
        // flush any buffers
        while (ob_get_level()) { @ob_end_flush(); }
    }
});

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$seen = [];
function out_url($loc, $lastmod = null, $changefreq = null, $priority = null) {
    global $seen;
    $loc = rtrim($loc, '/');
    if (isset($seen[$loc])) return; // avoid duplicates
    $seen[$loc] = true;

    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n";
    if (!empty($lastmod)) {
        // format YYYY-MM-DD
        $lm = date('Y-m-d', strtotime($lastmod));
        echo "    <lastmod>$lm</lastmod>\n";
    }
    if (!empty($changefreq)) echo "    <changefreq>" . htmlspecialchars($changefreq, ENT_XML1, 'UTF-8') . "</changefreq>\n";
    if (!empty($priority)) echo "    <priority>" . htmlspecialchars($priority, ENT_XML1, 'UTF-8') . "</priority>\n";
    echo "  </url>\n";
}

// 1) Homepage + static pages
$out_home = rtrim($strSiteURL, '/');
out_url($out_home . '/', null, 'daily', '1.0');
out_url($out_home . '/contact.php', null, 'monthly', '0.4');
out_url($out_home . '/politica.php', null, 'yearly', '0.2');
out_url($out_home . '/termeni.php', null, 'yearly', '0.2');
out_url($out_home . '/rss.php', null, 'weekly', '0.1');

// 2) CMS pages (visible only)
if (isset($cms) && $cms==1) {
    $stmt = $conn->prepare("SELECT pagina_url FROM cms_pagini WHERE pagina_status=0 AND pagina_url<>''");
    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $url = $out_home . '/' . trim($r['pagina_url'], '/');
            out_url($url, null, 'monthly', '0.6');
        }
        $stmt->close();
    } else {
        echo "<!-- sitemap: failed to prepare cms_pagini: " . htmlspecialchars($conn->error, ENT_XML1, 'UTF-8') . " -->\n";
    }
} else {
    echo "<!-- sitemap: cms disabled or not configured, skipping cms_pagini -->\n";
}

// 3) Blog articles (public) — include lastmod from articol_data_publicarii when available
if (isset($blog) && $blog==1) {
    $stmt = $conn->prepare("SELECT articol_url, articol_data_publicarii FROM blog_articole WHERE articol_tip=1 AND articol_url<>''");
    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $url = $out_home . '/blog/' . trim($r['articol_url'], '/');
            out_url($url, $r['articol_data_publicarii'] ?? null, 'weekly', '0.8');
        }
        $stmt->close();
    } else {
        echo "<!-- sitemap: failed to prepare blog_articole: " . htmlspecialchars($conn->error, ENT_XML1, 'UTF-8') . " -->\n";
    }
} else {
    echo "<!-- sitemap: blog disabled or not configured, skipping blog_articole -->\n";
}

// 4) Shop products (public product URLs)
// canonical product trail used by templates = /produse/
$productTrail = 'produse';
if (isset($shop) && $shop==1) {
    $stmt = $conn->prepare("SELECT produs_url FROM magazin_produse WHERE produs_url<>''");
    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $url = $out_home . '/' . $productTrail . '/' . trim($r['produs_url'], '/');
            out_url($url, null, 'weekly', '0.7');
        }
        $stmt->close();
    } else {
        echo "<!-- sitemap: failed to prepare magazin_produse: " . htmlspecialchars($conn->error, ENT_XML1, 'UTF-8') . " -->\n";
    }
} else {
    echo "<!-- sitemap: shop disabled or not configured, skipping magazin_produse -->\n";
}

// 5) E-learning courses
// public trail used in templates = /cursuri/
if (isset($elearning) && $elearning==1) {
    $stmt = $conn->prepare("SELECT course_url FROM elearning_courses WHERE course_url<>''");
    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $url = $out_home . '/cursuri/' . trim($r['course_url'], '/');
            out_url($url, null, 'weekly', '0.7');
        }
        $stmt->close();
    } else {
        echo "<!-- sitemap: failed to prepare elearning_courses: " . htmlspecialchars($conn->error, ENT_XML1, 'UTF-8') . " -->\n";
    }
} else {
    echo "<!-- sitemap: elearning disabled or not configured, skipping elearning_courses -->\n";
}

// close
$sitemap_closed = true;
echo '</urlset>';
// flush buffered output cleanly
if (ob_get_level()) ob_end_flush();
exit;