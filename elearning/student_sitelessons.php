<?php
//updated 17.07.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle="Acces module";
?>
<script src="<?php $strSiteURL?>/js/plyr/plyr.polyfilled.js"></script>
<script>
  const player = new Plyr('#player');
  controlsList="nodownload";
</script>
<link rel="stylesheet" href="<?php echo $strSiteURL?>/css/plyr.css" />
<?php
include '../dashboard/header.php';

if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/elearning/login.php?message=MLF");
}
$uid=$_SESSION['uid'];
$query1="SELECT * FROM elearning_lessons WHERE lesson_ID=$_GET[lID]";
$result1=ezpub_query($conn, $query1);
$row1=ezpub_fetch_array($result1);
?>
    <div class="grid-x grid-margin-x">
			  <div class="large-8 medium-8 small-8 cell">
   	<iframe width="100%" height="600" src="student_slideshow.php?lID=<?php echo $_GET["lID"]?>" frameBorder="0" scrolling="no" onload="resizeIframe(this)" id="lei" ></iframe>


</div>
    <div class="large-4 medium-4 small-4 cell">
		<h1><?php echo $row1["lesson_title"]?></h1>
<video
    id="my-player"
    class="video-js"
    controls
	controlsList="nodownload"
    preload="auto"
    poster="//vjs.zencdn.net/v/oceans.png"
    data-setup='{}'>
  <source src="../videofiles/fiat.mp4" type="video/mp4"></source>
  <p class="vjs-no-js">
    To view this video please enable JavaScript, and consider upgrading to a
    web browser that
  </p>
</video>
   </div>
   </div>
    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
	<iframe width="100%" height="600" src="student_questions.php?lID=<?php echo $_GET["lID"]?>" frameBorder="0" scrolling="no" onload="resizeIframe(this)" id="lei" ></iframe>
</div>
</div>
<?php
include '../bottom.php';
?>