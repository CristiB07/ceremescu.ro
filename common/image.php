<?php
//update 05.02.2025
namespace Verot\Upload;

include '../settings.php';
if (!isSet($_SESSION['$lang'])) {
	$_SESSION['$lang']="RO";
	$lang=$_SESSION['$lang'];
}
Else
{
	$lang=$_SESSION['$lang'];
}
if ($lang=="RO") {
include '../lang/language_RO.php';
}
Else
{
	include '../lang/language_EN.php';
}
include '../classes/common.php';
include '../classes/upload.class.php';

If (IsSet($_GET['directory']) AND IsSet($_GET['field'])) 
{
$directory=$_GET['directory'];

$field=$_GET['field'];

}
Else{
echo "<div class=\"callout alert\">$strThereWasAnError</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteproducts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
die;}

// set variables
// Simple way to get back to server path minus the javascript directorys
$_cur_dir = getcwd(); if ($_cur_dir == FALSE) { $_cur_dir = dirname($_SERVER['SCRIPT_FILENAME']); }
// minus the amout of directorys back to root directory from current run script e.g. /js/tinymce/plugins/phpimage
// The default language for errors is english to change to another type add lang to the lang folder e.g. fr_FR (french) to get language packs please download the class zip from the above authors link
$language						= 'en_EN';
// server file directory to store images - IMPORTANT CHANGE PATH TO SUIT YOUR NEEDS!!!
$server_image_directory		= '../img/'.$directory;  //e.g. '/home/user/public_html/uploads'; 
// URL directory to stored images (relative or absoulte) - IMPORTANT CHANGE PATH TO SUIT YOUR NEEDS!!!
$url_image_directory			= $strSiteURL.'/img/'.$directory; 
   
//$dir_dest = (isset($_GET['dir']) ? $_GET['dir'] : 'tmp');
$dir_pics = (isset($_GET['pics']) ? $_GET['pics'] : $server_image_directory);
$newpath = realpath($_SERVER["DOCUMENT_ROOT"] . "/../");

$log = '';
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- Google fonts -->
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/foundation.css"/>
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/consaltis.css"/>
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/all.css"/>
<!-- Start scripts-->
    <script src="<?php echo $strSiteURL ?>/js/foundation/jquery.js"></script>
    <script src="<?php echo $strSiteURL ?>/js/foundation/what-input.js"></script>
    <script src="<?php echo $strSiteURL ?>/js/foundation/foundation.js"></script>
    <script src="<?php echo $strSiteURL ?>/js/foundation/app.js"></script>
	    <script>
      $(document).foundation();
    </script>
	<!-- Ends scrips-->

<!-- IE Fix for HTML5 Tags -->
<?php


if (isset($_POST['action']) && $_POST['action'] == 'image') 
{
   // ---------- IMAGE UPLOAD ----------

    // we create an instance of the class, giving as argument the PHP object
    // corresponding to the file field from the form
    // All the uploads are accessible from the PHP object $_FILES
    $handle = new Upload($_FILES['my_field']);

    // then we check if the file has been uploaded properly
    // in its *temporary* location in the server (often, it is /tmp)
    if ($handle->uploaded) {

        // yes, the file is on the server
        // below are some example settings which can be used if the uploaded file is an image.
        //$handle->image_resize            = true;
        //$handle->image_ratio_y           = true;
        //$handle->image_x                 = 300;

        // now, we start the upload 'process'. That is, to copy the uploaded file
        // from its temporary location to the wanted location
        // It could be something like $handle->process('/home/www/my_uploads/');
        $handle->process($server_image_directory);

        // we check if everything went OK
        if ($handle->processed) {
			echo "<script>setTimeout(\"document.getElementById('src').value='".$url_image_directory."/".$handle->file_dst_name."'\", 200)</script>";
			echo "<script>setTimeout(\"document.getElementById('img').src='".$url_image_directory."/".$handle->file_dst_name."'\", 200)</script>";
			echo "<script>setTimeout(\"parent.document.getElementById('$field').value='".$handle->file_dst_name."'\", 200)</script>";
            // everything was fine !
           // echo '<p class="result">';
            //echo '  <b>File uploaded with success</b><br />';
            //echo '  <img src="'.$url_image_directory.'/' . $handle->file_dst_name . '" />';
            //$info = getimagesize($handle->file_dst_pathname);
            //echo '  File: <a href="'.$url_image_directory.'/' . $handle->file_dst_name . '">' . $handle->file_dst_name . '</a><br/>';
            //echo '   (' . $info['mime'] . ' - ' . $info[0] . ' x ' . $info[1] .' -  ' . round(filesize($handle->file_dst_pathname)/256)/4 . 'KB)';
            //echo '</p>';
        } else {
            // one error occured
            echo '<p class="result">';
            echo '  <b>File not uploaded to the wanted location</b><br />';
            echo '  Error: ' . $handle->error . '';
            echo '</p>';
        }

        // we delete the temporary files
        $handle-> clean();

    } else {
        // if we're here, the upload file failed for some reasons
        // i.e. the server didn't receive the file
        echo '<p class="result">';
        echo '  <b>File not uploaded on the server</b><br />';
        echo '  Error: ' . $handle->error . '';
        echo '</p>';
    }

    $log .= $handle->log . '<br />';
	}
?>

</head>
<body>
     <div class="grid-x grid-padding-x">
        <div class="large-12 cell">
		<h3><?php echo $strManageImages?></h3>
</div></div>		
          <div class="grid-x grid-padding-x">
            <div class="large-6 medium-6 cell">
<h4><?php echo $strUploadImage?></h4>			
			<form action="" enctype="multipart/form-data" method="post">
									<input type="file" id="image_field" name="my_field" value="" />
									<input type="hidden" name="action" value="image" />
									<input type="submit" id="insert" name="insert" class="button round" value="<?php echo $strUploadImage?>" />
			</form>
			</div>
		<div class="large-6 medium-6 cell">
				
							<label id="srclabel" for="src"><?php echo $strURL?></label>
							<input name="src" type="text" id="src" value="" disabled class="small-4 columns" />	
							<label id="srclabel" for="image"><?php echo $strImage?></label>
							<input name="image" type="text" id="image" value="" disabled class="small-4 columns" />						
							</div>
				</div>
				</div>
     <div class="grid-x grid-padding-x">
        <div class="large-12 cell">
				<?php echo $strOr?>
				</div>
				</div>
     <div class="grid-x grid-padding-x">
        <div class="large-8 cell">
 
						<h4><?php echo $strSelect?></h4>
<?php

   $allow = array('jpg','jpeg', 'JPEG', 'JPG', 'png', 'PNG', 'gif', 'GIF');

    $i=0;
    $open = opendir($server_image_directory);
    // get each filename from directory
    while (($file=readdir($open))!==false) {
        // get extension
        $ext=str_replace('.', '', strrchr($file, '.'));
        // does it have a valid extension
        if (in_array($ext, $allow)) 
          $list[$i++]=$file; // store valid filename in array. use numerical indexing as makes it easier to display paginated images later
    }
    
    $perPage=12; // number of images to show per page
    $total=count($list); // total number of images to show
    $pages=ceil($total/$perPage); // number of pages is the number of images divided by how many per page
    $thisPage=isset($_GET['pg'])?$_GET['pg']-1:0; // did user select a specific page? Note, pages on web are counted from 1 (not zero) so must subtract 1 for correct indexing
    $start=$thisPage*$perPage; // calculate starting index into list of filenames
    
    $perRow=3; // how many images to be shown on each row
    
    // display quick index to pages. all pages except current page output as a link
    print "Page ";
    for ($i=0;$i<$pages;$i++)
      if ($i==$thisPage)
        print "&nbsp;".($i+1);
      else
        print "&nbsp;<a href='$strSiteURL\admin\image.php?directory=$directory&field=$field&pg=".($i+1)."'>".($i+1)."</a>";
        
    print "<table cellpadding=\"4\" cellspacing=\"0\" width=\"100%\"><tr>";
    $imgCnt=0; // used to count number of images displayed and hence whether to wrap page. note, could use "for" index $i but this is computationally quicker
    for ($i=$start;$i<$start+$perPage;$i++) {
      // may be too few images to fill page, so check if we have a valid array index. if we don't output empty table cell so fussy browsers
      // don't mis-display table due to missing cells
      if (isset($list[$i]))
        print "<td><a href=\"#\" class=\"imageSelector\"><img width='200px' height='auto' src='$url_image_directory/{$list[$i]}'></a></td>";
      else
        print "<td></td>";
        
      $imgCnt+=1; // increment images shown
      if ($imgCnt%$perRow==0) // if image count divided by number to show per row has no remainder than it's time to wrap
        print "</tr><tr>";
    }
    print "</tr>";

    closedir($open);
?>

</table>  
<script>
$(function() {
$('a.imageSelector').click(function() {

var param = $("img", $(this)).attr('src');
var filename = param.replace(/^.*[\\\/]/, '')
 $(this).css('border', "solid 2px red");  
 document.getElementById('src').value = param;
 document.getElementById('img').src=param;
   if (document.getElementById('image').value==""){
  document.getElementById('image').value = filename;
   }
   else
	   {
	var inputform = document.getElementById('image').value;
	if (inputform.includes(filename)) {
    var deleted = inputform.replace(filename, '');
	if (deleted.startsWith(', '))
	{
		deleted=deleted.slice(2);
	}
	document.getElementById('image').value = deleted;
  } else 	{
  document.getElementById('image').value +=', '+ filename ;
	   }}
   var imagine=document.getElementById('image').value;
parent.document.getElementById('<?php echo $field?>').value = imagine;

   });
});

</script>
  </div>
        <div class="large-4 columns">
          <div class="panel">

					<label><?php echo $strImagePreview?></label>
					<img id="img" name="img" src="" height="auto" width="200" alt="<?php echo $strImagePreview?>" />
					<script>
					
					</script>
			</div>
        </div>
       </div>			
</body>
</html>