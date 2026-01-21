<?php
// Start session before any output
if(!isset($_SESSION)) { 
    session_start(); 
}

include '../settings.php';
if (!isSet($_SESSION['$lang'])) {
	$_SESSION['$lang']="RO";
	$lang=$_SESSION['$lang'];
}
else
{
	$lang=$_SESSION['$lang'];
}
if ($lang=="RO") {
include '../lang/language_RO.php';
}
else
{
	include '../lang/language_EN.php';
}

include '../classes/common.php';

// Set content type after session start
header('Content-Type: application/rss+xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
    xmlns:slash="http://purl.org/rss/1.0/modules/slash/">
    <?php
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$stimedate=date('m/d/Y h:i:s', time());
?>
    <channel>
        <title><?php echo $strSiteName?></title>
        <link><?php echo $strSiteURL?></link>
        <description><?php echo $strSiteDescription?></description>
        <image>
            <url><?php echo $strSiteURL?>/images/<?php echo $siteOGImage?></url>
            <title><?php echo $strSiteName?></title>
            <link><?php echo $strSiteURL?></link>
        </image>
        <language>ro-Ro</language>
        <copyright><?php echo $siteCompanyLegalName?></copyright>
        <lastBuildDate><?php echo htmlspecialchars($stimedate, ENT_XML1, 'UTF-8') ?></lastBuildDate>
        <ttl>20</ttl>
        <?php
	// Use prepared statement to prevent SQL injection
	$stmt = $conn->prepare("SELECT articol_id, articol_titlu, articol_tip, articol_imaginetitlu, articol_url, articol_continut, articol_data_publicarii 
	                        FROM blog_articole 
	                        WHERE articol_tip = '1' 
	                        ORDER BY articol_id DESC 
	                        LIMIT 10");
	$stmt->execute();
	$result3 = $stmt->get_result();
	
	While ($row = $result3->fetch_assoc()){
		// Sanitize all output
		$titlu = $row["articol_titlu"];
		$tip = htmlspecialchars($row["articol_tip"], ENT_XML1, 'UTF-8');
		$url = htmlspecialchars($row["articol_url"], ENT_XML1, 'UTF-8');
		$imagine = basename($row["articol_imaginetitlu"]); // Prevent path traversal
		$imagine = htmlspecialchars($imagine, ENT_XML1, 'UTF-8');
		$continut = htmlspecialchars(substr($row["articol_continut"], 0, 500), ENT_XML1, 'UTF-8');
		$data = htmlspecialchars($row["articol_data_publicarii"], ENT_XML1, 'UTF-8');
  ?>
        <item>
            <title><?php echo $titlu?> </title>
            <link><?php echo htmlspecialchars($strSiteURL, ENT_XML1, 'UTF-8'). "/blog/". $url?></link>
            <description>
                <![CDATA[
                    <img src="<?php echo htmlspecialchars($strSiteURL, ENT_XML1, 'UTF-8') . "/img/blog/" . $imagine ?>" alt="<?php echo $titlu ?>" /><br/>
                    <?php echo $continut ?>...
                ]]>
            </description>
            <pubDate><?php echo $data ?></pubDate>
        </item>
        <?php
	}
	$stmt->close();
?>
    </channel>
</rss>