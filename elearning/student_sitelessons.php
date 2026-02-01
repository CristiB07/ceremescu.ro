<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle="Acces module de curs";
?>
<script src="<?php $strSiteURL?>/js/plyr/plyr.polyfilled.js"></script>
<script>
const player = new Plyr('#player');
controlsList = "nodownload";
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
	header("location:$strSiteURL/account/login.php?message=MLF");
}
$uid=(int)$_SESSION['uid'];
// Sanitize and validate input
$lID = isset($_GET['lID']) ? (int)$_GET['lID'] : 0;
if ($lID <= 0) {
    header("location:$strSiteURL/dashboard/dashboard.php");
    die();
}

// Use prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM elearning_lessons WHERE lesson_ID=?");
mysqli_stmt_bind_param($stmt, "i", $lID);
mysqli_stmt_execute($stmt);
$result1 = mysqli_stmt_get_result($stmt);
$row1=ezpub_fetch_array($result1);
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <a href="student_mycourses.php?cID=<?php echo $row1["lesson_course"]?>#panel2d" class="button">
            <i class="fas fa-backward"></i>&nbsp; <?php echo $strBackToLessons?></a>
    </div>
</div>
<div class="grid-x grid-margin-x">
    <div class="large-8 medium-8 small-8 cell">
        <iframe width="100%" height="600" src="student_slideshow.php?lID=<?php echo (int)$lID?>" frameBorder="0"
            scrolling="no" onload="resizeIframe(this)" id="lei"></iframe>


    </div>
    <div class="large-4 medium-4 small-4 cell data-sticky-container">
        <div class="sticky" data-sticky data-margin-top="6">
        <video id="my-player" class="video-js" controls controlsList="nodownload" preload="auto"
            poster="//vjs.zencdn.net/v/oceans.png" data-setup='{}'>
            <source src="../videofiles/<?php echo $row1["lesson_video"]?>" type="video/mp4">
            </source>
            <p class="vjs-no-js">
                To view this video please enable JavaScript, and consider upgrading to a
                web browser that
            </p>
        </video>
    </div>
  
        <h2><?php echo $strFiles?></h2>
        <?php
         if (isset($row1['lesson_files']) AND !empty($row1['lesson_files']))
            {
                $folder="elearning/lesson_files/".$row1["lesson_ID"]."/";
	            $lessonfiles=explode(";",$row1['lesson_files']);
	                foreach ($lessonfiles as $file) {
		                if (!empty($file)) {
                            $file_safe = htmlspecialchars($file, ENT_QUOTES, 'UTF-8');
                            $folder_safe = htmlspecialchars($folder, ENT_QUOTES, 'UTF-8');
                            $icon_class = getFileIcon($file);
			            echo "<a href=\"../common/opendoc.php?type=5&folder=$folder_safe&docID=$file_safe\" target=\"_blank\" rel=\"noopener noreferrer\">
                        <i class=\"$icon_class\">&nbsp;$file_safe</i></a><br />";
		                                    }
	                                                }
            }
            else
            echo "<div class=\"callout warning\">Nu există fișiere atașate pentru această lecție.</div>";?>
    </div>
</div>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <iframe width="100%" height="600" src="student_questions.php?lID=<?php echo (int)$lID?>" frameBorder="0"
            scrolling="no" onload="resizeIframe(this)" id="lei"></iframe>
    </div>
</div>
<?php
include '../bottom.php';
?>