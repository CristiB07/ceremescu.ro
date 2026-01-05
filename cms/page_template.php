<?php

$url = strtok($url, '?');
// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM cms_pagini WHERE pagina_url=?");
$stmt->bind_param("s", $url);
$stmt->execute();
$result = $stmt->get_result();
if ($row=$result->fetch_assoc()) {
$strKeywords=htmlspecialchars($row['pagina_keywords'], ENT_QUOTES, 'UTF-8');
$strDescription=htmlspecialchars($row['pagina_descriere'], ENT_QUOTES, 'UTF-8');
$strPageTitle=htmlspecialchars($row['pagina_titlu'], ENT_QUOTES, 'UTF-8');
// Keep HTML content but sanitize URL replacements
$strPageContent=str_replace(array("../img/pages/","<li>", "<ul class=\"tiny\">"),array(htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8')."/img/pages/","<li class=\"tiny\">", "<ul>"),$row['pagina_continut']);
$pageurl=htmlspecialchars($row['pagina_url'], ENT_QUOTES, 'UTF-8');
include 'header.php';
echo "
<nav aria-label=\"" . htmlspecialchars($strYouAreHere, ENT_QUOTES, 'UTF-8') . ":\" role=\"navigation\">
  <ul class=\"breadcrumbs\">
    <li><a href=\"" . htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') . "\">Home</a></li>
       <li class=\"disabled\">$strPageTitle</li>
  </ul>
</nav>
";
echo "
 <div class=\"grid-x grid-margin-x\">
			  <div class=\"large-12 medium-12 small-12 cell\">";
			  if (IsSet ($row['pagina_imaginetitlu']) AND $row['pagina_imaginetitlu']!='')
			  {
				  $strPageImage=htmlspecialchars($row['pagina_imaginetitlu'], ENT_QUOTES, 'UTF-8');
				  echo "<img src=\"" . htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8') . "/img/pages/$strPageImage\" height=\"auto\" width=\"auto\" alt=\"$strPageTitle\" />";
			  }
Echo "<h1>$strPageTitle</h1>
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
 <a href=\"" . htmlspecialchars($previous, ENT_QUOTES, 'UTF-8') . "\" class=\"button\"><i class=\"fas fa-backward fa-xl\"></i>&nbsp;Înapoi la pagina anterioară</a> 
 </p>";
echo "</div></div></div>";
}
?>