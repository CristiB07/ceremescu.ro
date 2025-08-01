<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' AND IsSet($_POST['feed'])) {
	$feed=$_POST['feed'];
	
	
  $xml = simplexml_load_file($feed);
			foreach ($xml->channel as $channel) {
			$feed_title=$channel->title;
			$feed_description=$channel->description;
			$feed_url=$channel->link;
			}
				foreach ($xml->channel->image as $image) {
			$feed_image=$image->url;
			$feed_image_title=$image->title;
			$feed_image_w=$image->width;
			$feed_image_h=$image->height;
			}
	if (!isset($feed_image)) {$feed_image="";}	
	if (!isset($feed_image_w)) {$feed_image_w="";}	
	if (!isset($feed_image_h)) {$feed_image_h="";}	
	
//	echo $feed_url . "<br>" . $feed_description . "<br>" . $feed_title. "<br>";
	
			$formresult=json_encode([
				'feed_url' => strval($feed),
				'feed_site_url' => strval($feed_url),
				'feed_title' => strval($feed_title),
				'feed_description' => strval($feed_description),
				'feed_image' => strval($feed_image),
				'feed_image_w' => strval($feed_image_w),
				'feed_image_h' => strval($feed_image_h),
				]);

header('Content-Type: application/json');
echo $formresult;
}
Else
{echo 'a fost o eroare';}
?>
