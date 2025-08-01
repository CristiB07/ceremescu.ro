<?php
// update 26.12.2024
include '../settings.php';
include '../classes/common.php';
include '../lang/language_RO.php';
$strPageTitle="Update feeduri";
$url='updatefeed.php';
?>
<link rel="stylesheet" href="<?php echo $strSiteURL ?>css/all.css"/>
<link rel="stylesheet" href="<?php echo $strSiteURL ?>css/foundation.css"/>
<link rel="stylesheet" href="<?php echo $strSiteURL ?>css/<?php echo $cssname?>.css"/>
<?php

If (IsSet($_GET['mode']) AND $_GET['mode']=='single' ){
			If (IsSet($_GET['cID'])){

					//Feed URLs
					$fsql="SELECT* FROM readerrss_feeds WHERE feed_ID=" .$_GET['cID']. ";";
					$result=ezpub_query($conn,$fsql);
					$row=ezpub_fetch_array($result);	
					$feed = $row["feed_url"];
					$xml = simplexml_load_file($feed);
					$entries = array();
					$entries = array_merge($entries, $xml->xpath("//item"));
					//Sort feed entries by pubDate
					usort($entries, function ($feed1, $feed2) {
						return strtotime($feed2->pubDate) - strtotime($feed1->pubDate);
					});	
						  if (! empty($xml)) {
				$i = 0;

				foreach($entries as $entry) {
					//array_multisort(array_column($item, 'pubDate'), SORT_DESC, $channel);
					if ($i >= 100)
						break;
					$article_url=$entry->link;
					$article_title=str_replace("'","&#39;",$entry->title);
					if (isset($entry->author))
					{$article_author=$entry->author;}
					Else
					{$article_author='';}
					$article_pubDate = strtotime($entry->pubDate);
					$date=date("Y/m/d H:i:s", $article_pubDate);
					$article_content=$entry->description;
					$query="SELECT * from readerrss_articole WHERE articol_titlu='$article_title'";
					$result=ezpub_query($conn,$query);
					$numar=ezpub_num_rows($result,$query);
			if ($numar==0)
					{		//insert new article

				$mSQL = "INSERT INTO readerrss_articole(";
				$mSQL = $mSQL . "articol_feed_ID,";
				$mSQL = $mSQL . "articol_link,";
				$mSQL = $mSQL . "articol_titlu,";
				$mSQL = $mSQL . "articol_autor,";
				$mSQL = $mSQL . "articol_data_publicarii,";
				$mSQL = $mSQL . "articol_descriere)";

				$mSQL = $mSQL . "Values(";
				$mSQL = $mSQL . "'" .$row['feed_ID'] . "', ";
				$mSQL = $mSQL . "'" .$article_url . "', ";
				$mSQL = $mSQL . "'" .str_replace("'","&#39;",$article_title) . "', ";
				$mSQL = $mSQL . "'" .str_replace("'","&#39;",$article_author) . "', ";
				$mSQL = $mSQL . "'" .$date . "', ";
				$mSQL = $mSQL . "'" .str_replace("'","&#39;",$article_content) . "') ";
										
			//It executes the SQL
			if (!ezpub_query($conn,$mSQL))
			  {
			  die('Error: ' . ezpub_error($conn));
			  }
					}
					
					   $i ++;
				}
			}
					
					$date = date('Y-m-d H:i:s');
					$query= "UPDATE readerrss_feeds SET feed_lastupdated='" .$date . "' WHERE feed_ID='" .$_GET['cID'].  "';" ;
					ezpub_query($conn,$query);
					header('Location: ' . $_SERVER['HTTP_REFERER']);
					exit;
			}
			Else
			{ echo "Nui nimic<div class=\"callout alert\">$strThereWasAnError</div>" ;}}
		
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=='all' )	
{
$query1="SELECT feed_ID, feed_url FROM readerrss_feeds order by feed_ID DESC";
$result1=ezpub_query($conn,$query1);
	While ($row1=ezpub_fetch_array($result1)){
			$feed = $row1['feed_url'];
echo $feed;	
echo "<br>";		
            $xml = simplexml_load_file($feed);
		$entries = array();
		$entries = array_merge($entries, $xml->xpath("//item"));
		//Sort feed entries by pubDate
        usort($entries, function ($feed1, $feed2) {
            return strtotime($feed2->pubDate) - strtotime($feed1->pubDate);
        });	
		//print_R ($entries);
              if (! empty($xml)) {
    $i = 0;

    foreach($entries as $entry) {
		//array_multisort(array_column($item, 'pubDate'), SORT_DESC, $channel);
        if ($i >= 100)
            break;
		$article_url=$entry->link;
		$article_title=str_replace("'","&#39;",$entry->title);
		if (isset($entry->author))
		{$article_author=$entry->author;}
		Else
		{$article_author='';}
		$article_pubDate = strtotime($entry->pubDate);
		$date=date("Y/m/d H:i:s", $article_pubDate);
		$article_content=$entry->description;
		$query="SELECT * from readerrss_articole WHERE articol_titlu='$article_title'";
		$result=ezpub_query($conn,$query);
		$numar=ezpub_num_rows($result,$query);
if ($numar==0)
		{		//insert new article

	$mSQL = "INSERT INTO readerrss_articole(";
	$mSQL = $mSQL . "articol_feed_ID,";
	$mSQL = $mSQL . "articol_link,";
	$mSQL = $mSQL . "articol_titlu,";
	$mSQL = $mSQL . "articol_autor,";
	$mSQL = $mSQL . "articol_data_publicarii,";
	$mSQL = $mSQL . "articol_descriere)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$row1['feed_ID'] . "', ";
	$mSQL = $mSQL . "'" .$article_url . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$article_title) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$article_author) . "', ";
	$mSQL = $mSQL . "'" .$date . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$article_content) . "') ";

//echo $msql;
//echo "</br>";				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
		}
		
           $i ++;
    }
}
        
   		$date = date('Y-m-d H:i:s');
		$query= "UPDATE readerrss_feeds SET feed_lastupdated='" .$date . "' WHERE feed_ID='" .$row1['feed_ID'].  "';" ;
		ezpub_query($conn,$query);
							}
}
Else
{ echo "<div class=\"callout alert\">$strThereWasAnError</div>" ;}
header('Location: ' . $_SERVER['HTTP_REFERER']);
			exit;
        ?>