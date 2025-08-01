<?php
// update 29.12.2024
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle="Administrare feeduri";
$url='feeds.php';
include '../dashboard/header.php';

?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
echo "<a href=\"updatefeed.php?mode=all\" class=\"button\">$strUpdate&nbsp;<i class=\"large fas fa-sync\" title=\"$strUpdate\"></i></a>
</div></div>";
$query="SELECT feed_titlu, feed_image_url, feed_ID, articol_feed_ID, articol_ID, articol_titlu, articol_link, articol_autor, articol_data_publicarii, articol_descriere, articol_data_citirii 
FROM readerrss_feeds, readerrss_articole
WHERE feed_ID=articol_feed_ID";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY articol_data_publicarii ASC $pages->limit";
$result=ezpub_query($conn,$query);

echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<div class="paginate">
<?php
echo $strTotal . " " .$numar." ".$strMessages ;
echo " <br /><br />";
echo $pages->display_pages();
echo " <br /><br />";
?>
</div>
</div>
</div>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> 
	 
<table width="100%">
	      <thead>
    	<tr>
        	<th></th>
        	<th><?php echo $strFeed?></th>
			<th><?php echo $strArticle?></th>
			<th><?php echo $strAuthor?></th>
			<th><?php echo $strPublished?></th>
			<th><?php echo $strDetails?></th>
			<th><?php echo $strMarkAsRead?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
	
   		echo"<tr>";
		if (!empty($row["feed_image_url"]))
		{echo	"<td><img src=\"$row[feed_image_url]\" width=\"32\" height=width=\"32\"></td>";}
	else
		{echo	"<td><img src=\"$strSiteURL/img/rssreader/rss.png\" width=\"32\" height=width=\"32\"></td>";}
echo			"<td>$row[feed_titlu]</td>";?>
			<div class="large reveal" id="mymodal-<?php echo $row["articol_ID"]?>" data-reveal>
  <h1><?php echo $row["articol_titlu"]?></h1>
 <p><?php echo $row["articol_descriere"]?></p>
  <button class="close-button" data-close aria-label="Close modal" type="button">
    <span aria-hidden="true">&times;</span>
  </button>
</div>

<?php 
echo	"			<td> $row[articol_titlu]&nbsp;<a href=\"#\" data-open=\"mymodal-$row[articol_ID]\">  &nbsp<i class=\"fab fa-readme\" title=\"$strRead\"></i></a>
</td>
			<td>$row[articol_autor]</td>";
			$dataarticol=$row["articol_data_publicarii"];
			$dateObj   = DateTime::createFromFormat('Y-m-d H:i:s', $dataarticol);
			$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'EEEE, d MMMM Y HH:mm:ss');
		$dayname = $formatter->format($dateObj);

echo			"<td>$dayname</td>
			<td><a href=\"$row[articol_link]\">
			<i class=\"large far fa-newspaper\" title=\"$strOpen\"></i>
			</a>
			  <td>";
			  if (isset($row["articol_data_citirii"]))
			  {echo $row["articol_data_citirii"];}
		  Else
		  {echo " <a href=\"markasread.php?cID=$row[articol_ID]\" ><i class=\"fas fa-check-double\" title=\"$strMarkAsRead\"></i></a>";}
echo
			        "</td></tr>";
}
echo "</tbody><tfoot><tr><td><td  colspan=\"5\"><em></em><td>&nbsp;</tr></tfoot></table></div></div>";
}
?>
    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<div class="paginate">
<?php
echo $strTotal . " " .$numar." ".$strMessages ;
echo " <br /><br />";
echo $pages->display_pages();
echo " <br /><br />";
?>
</div>
</div>
</div>
<?php
include '../bottom.php';
?>