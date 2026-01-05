<?php
include 'settings.php';
include 'classes/common.php';
include 'classes/paginator.class.php';

$url = strtok($url, '?');
$stmt = $conn->prepare("SELECT * FROM blog_articole WHERE articol_url=?");
$stmt->bind_param("s", $url);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
$strKeywords=$row['articol_keywords'];
$strDescription=$row['articol_descriere'];
$strPageTitle=$row['articol_titlu'];
$strPageContent=str_replace(array("../img/blog/","<li>", "<ul class=\"tiny\">"),array($strSiteURL."/img/blog/","<li class=\"tiny\">", "<ul>"),$row['articol_continut']);
$pageurl='blog/'.$row['articol_url'].'';
include 'header.php';
echo "
<nav aria-label=\"".htmlspecialchars($strYouAreHere, ENT_QUOTES, 'UTF-8').":\" role=\"navigation\">
  <ul class=\"breadcrumbs\">
    <li><a href=\"".htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8')."\">Home</a></li>
    <li><a href=\"".htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8')."/blog/\">Blog</a></li>
       <li class=\"disabled\">".htmlspecialchars($strPageTitle, ENT_QUOTES, 'UTF-8')."</li>
  </ul>
</nav>
";

// Article navigation function
function displayArticleNavigation($conn, $current_article_id) {
    // Check total number of published articles
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM blog_articole WHERE articol_tip='1'");
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_articles = $count_row['total'];
    $count_stmt->close();
    
    if ($total_articles <= 3) {
        // Display empty paragraph to preserve layout
        echo '<div class="grid-x grid-margin-x">
              <div class="large-6 medium-6 cell">
                <p>&nbsp;</p>
              </div>
              <div class="large-6 medium-6 cell text-right">
                <p>&nbsp;</p>
              </div>
        </div>';
        return;
    }
    
    // Get previous article (older)
    $prev_stmt = $conn->prepare("SELECT articol_id, articol_titlu, articol_url FROM blog_articole WHERE articol_id < ? AND articol_tip='1' ORDER BY articol_id DESC LIMIT 1");
    $prev_stmt->bind_param("i", $current_article_id);
    $prev_stmt->execute();
    $prev_result = $prev_stmt->get_result();
    $prev_article = $prev_result->fetch_assoc();
    $prev_stmt->close();
    
    // Get next article (newer)
    $next_stmt = $conn->prepare("SELECT articol_id, articol_titlu, articol_url FROM blog_articole WHERE articol_id > ? AND articol_tip='1' ORDER BY articol_id ASC LIMIT 1");
    $next_stmt->bind_param("i", $current_article_id);
    $next_stmt->execute();
    $next_result = $next_stmt->get_result();
    $next_article = $next_result->fetch_assoc();
    $next_stmt->close();
    
    global $strSiteURL;
    
    echo '<div class="grid-x grid-margin-x">
          <div class="large-6 medium-6 cell">';
    
    if ($prev_article) {
        echo '<a href="' . htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') . '/blog/' . htmlspecialchars($prev_article['articol_url'], ENT_QUOTES, 'UTF-8') . '" class="button secondary">
              <i class="fas fa-chevron-left"></i> ' . htmlspecialchars($prev_article['articol_titlu'], ENT_QUOTES, 'UTF-8') . '
              </a>';
    } else {
        echo '<p>&nbsp;</p>';
    }
    
    echo '</div>
          <div class="large-6 medium-6 cell text-right">';
    
    if ($next_article) {
        echo '<a href="' . htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') . '/blog/' . htmlspecialchars($next_article['articol_url'], ENT_QUOTES, 'UTF-8') . '" class="button secondary">
              ' . htmlspecialchars($next_article['articol_titlu'], ENT_QUOTES, 'UTF-8') . ' <i class="fas fa-chevron-right"></i>
              </a>';
    } else {
        echo '<p>&nbsp;</p>';
    }
    
    echo '</div>
    </div>';
}

// Display navigation
displayArticleNavigation($conn, $row['articol_id']);
?>
<?php
echo "
 <div class=\"grid-x grid-margin-x\">
			  <div class=\"large-12 medium-12 small-12 cell\">";
			  if (IsSet ($row['articol_imaginetitlu']) AND $row['articol_imaginetitlu']!='')
			  {
				  $strPageImage=$row['articol_imaginetitlu'];
				  echo "<img src=\"".htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8')."/img/blog/".htmlspecialchars($strPageImage, ENT_QUOTES, 'UTF-8')."\" height=\"auto\" width=\"auto\" alt=\"".htmlspecialchars($strPageTitle, ENT_QUOTES, 'UTF-8')."\" />";
			  }
Echo "<h1>".htmlspecialchars($strPageTitle, ENT_QUOTES, 'UTF-8')."</h1>
	$strPageContent
  </div>
</div>";
 ?>
<?php
$stmt->close();
}
else
{
    http_response_code(404);
    $strKeywords="Pagina nu a fost găsită";
$strDescription="Pagina nu a fost găsită";
$strPageTitle="Pagina nu a fost găsită";
$pageurl='404.php';
include 'header.php';
$previous = "javascript:history.go(-1)";
if(isset($_SERVER['HTTP_REFERER'])) {
    $previous = htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8');
}

echo "<div class=\"grid-x grid-padding-x\" >
<div class=\"large-12 medium-12 small-12 columns\">
<div class=\"callout alert\">";
echo "<h1><i class=\"fas fa-exclamation-triangle fa-xl\"></i>&nbsp;Pagina nu a fost găsită</h1>";
echo "<p>Adresa pe care o căutați nu a fost găsită. Linkul care v-a adus aici poate fi depășit sau,
 dacă ați introdus manual adresa, este posibil să o fi scris greșit.</p>
 <p>Dacă problema persistă, vă rugăm să ne contactați la <a href=\"mailto:" . htmlspecialchars($siteCompanyEmail, ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($siteCompanyEmailMasked, ENT_QUOTES, 'UTF-8') . "</a> pentru asistență.</p>
 <p align=\"center\">
 <a href=\"" . htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') . "\" class=\"button\"><i class=\"fas fa-home fa-xl\"></i>&nbsp;Înapoi la pagina principală</a> 
 <a href=\"" . $previous . "\" class=\"button\"><i class=\"fas fa-backward fa-xl\"></i>&nbsp;Înapoi la pagina anterioară</a> 
 </p>";
echo "</div></div></div>";
}