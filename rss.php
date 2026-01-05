<?php
header('Content-Type: application/rss+xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
    xmlns:slash="http://purl.org/rss/1.0/modules/slash/">
    <?php
include 'settings.php';
if (!isSet($_SESSION['$lang'])) {
	$_SESSION['$lang']="RO";
	$lang=$_SESSION['$lang'];
}
else
{
	$lang=$_SESSION['$lang'];
}
if ($lang=="RO") {
include 'lang/language_RO.php';
}
else
{
	include 'lang/language_EN.php';
}

include 'classes/common.php';
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
            <url><?php echo $strSiteURL?>/images/rss/rss.jpg</url>
            <title><?php echo $strSiteName?></title>
            <link><?php echo $strSiteURL?></link>
        </image>
        <language>ro-Ro</language>
        <copyright><?php echo $siteCompanyLegalName?></copyright>
        <lastBuildDate><?php echo $stimedate ?></lastBuildDate>
        <ttl>20</ttl>
        <?php
	$query3="SELECT * FROM magazin_produse
			ORDER BY produs_id DESC LIMIT 10";

	 		$result3=ezpub_query($conn,$query3);
			While ($row=ezpub_fetch_array($result3)){
		
  ?>
        <item>
            <title><?php echo $row["produs_nume"]?> </title>
            <link><?php echo $strSiteURL. "/produse/". $row["produs_url"]?></link>
            <description>
                <![CDATA[<img align="left" width="75" src="<?php echo $strSiteURL?>/img/products/<?php echo $row["produs_imagine"]?>" hspace="10" />
	  <?php echo $row["produs_descriere"]?><br />
	 <?php If ($row["produs_dpret"]!=='0.0000')
					{
					$pprice=romanize($row["produs_dpret"]*$vatprc);
					}
					else
					{
						$pprice=romanize($row["produs_pret"]*$vatprc);
					}
					?>
		<strong>Preț:</strong> <?php echo $pprice ?><br />]]>
            </description>
            <pubDate><?php echo $stimedate ?></pubDate>
        </item>
        <?php
}
?>
    </channel>
</rss>