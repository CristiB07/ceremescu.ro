<?php
//update 16.07.2025
include '../settings.php';
include '../classes/common.php';
$cursuritrail="cursuri/";
$strKeywords="Cursuri de specializare, cursuri, specializare, ANC, cursuri formare, formare profesională, autorizate ANC";
$strDescription="Pagina de prezentare a Cursurilor de specializare organizate de CertPlus.ro Training Center";
$strPageTitle="Cursuri Cert Plus SRL";
include '../header.php';
?>

<div class="grid-x grid-padding-x">
    <div class="large-12 medium-12 small-12 cell">
        <h2><?php echo $strCourses?></h2>
    </div>
</div>
<div class="grid-x grid-margin-x" data-equalizer data-equalize-on="medium" id="test-eq">

    <?php
//elearning_courses
$query="SELECT * FROM elearning_coursecategory";
$result=ezpub_query($conn, $query);
$numar=ezpub_num_rows($result,$query);
if ($numar==0)
{
echo $strNoRecordsFound;
}
else {
  $i = 0;
While ($row=ezpub_fetch_array($result)){
  $i++ ;
        // Sanitize pentru XSS
        $category_id_safe = (int)$row['elearning_coursecategory_ID'];
        $category_name_safe = htmlspecialchars($row['elearning_coursecategory_name'], ENT_QUOTES, 'UTF-8');
        $category_picture_safe = htmlspecialchars($row['elearning_coursecategory_picture'], ENT_QUOTES, 'UTF-8');
        $category_description_safe = htmlspecialchars($row['elearning_coursecategory_description'], ENT_QUOTES, 'UTF-8');
        
        echo " <div class=large-4 medium-4 small-4 cell><div class=column data-equalizer-watch> 
        <h3> $category_name_safe </h3>
        <img src=\"$strSiteURL/img/categorii/$category_picture_safe\" alt=\"$category_description_safe\" >";	
        echo $category_description_safe;
        echo "<hr />";
        
// Prepared statement pentru SELECT courses
$stmt2 = mysqli_prepare($conn, "SELECT * FROM elearning_courses WHERE course_category=? ORDER BY course_name ASC");
mysqli_stmt_bind_param($stmt2, "i", $category_id_safe);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
$numar2 = mysqli_num_rows($result2);
if ($numar2==0)
{
echo $strNoRecordsFound;
}
else {
While ($row2=ezpub_fetch_array($result2)){
        // Sanitize pentru XSS
        $course_url_safe = htmlspecialchars($row2['course_url'], ENT_QUOTES, 'UTF-8');
        $course_name_safe = htmlspecialchars($row2['course_name'], ENT_QUOTES, 'UTF-8');
        $course_picture_safe = htmlspecialchars($row2['course_picture'], ENT_QUOTES, 'UTF-8');
        $course_description_safe = htmlspecialchars($row2['course_description'], ENT_QUOTES, 'UTF-8');
        
        echo "<div class=\"callout\">
        <a href=\"cursuri/$course_url_safe\"><h4>$course_name_safe</h4></a>
        <img src=\"$strSiteURL/img/cursuri/$course_picture_safe\" alt=\"$course_description_safe\" />
        $course_description_safe</div>";
        }
        }
echo "</div>";
if ($i<=3) {echo "</div><hr />";}
    elseif($i%3 == 0) {
                echo  "</div><hr />
				<div class=\"grid-x grid-padding-x\"  data-equalizer data-equalize-on=\"medium\" id=\"test-eq\">";
    }
      }
    }
 ?>
</div>

<hr />
<?php

include '../bottom.php';
?>