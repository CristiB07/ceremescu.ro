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
Else {
  $i = 0;
While ($row=ezpub_fetch_array($result)){
  $i++ ;
        echo " <div class=large-4 medium-4 small-4 cell><div class=column data-equalizer-watch> 
        <h3> $row[elearning_coursecategory_name] </h3>
        <img src=\"$strSiteURL/img/categorii/".$row["elearning_coursecategory_picture"]."\" alt=\"$row[elearning_coursecategory_description]\" >";	
        echo $row["elearning_coursecategory_description"];
        echo "<hr />";
$query2="SELECT * FROM elearning_courses where course_category=$row[elearning_coursecategory_ID] ORDER BY course_name ASC";
        $result2=ezpub_query($conn, $query2);
        $numar2=ezpub_num_rows($result2,$query2);
$result2=ezpub_query($conn, $query2);
$numar2=ezpub_num_rows($result2,$query2);
if ($numar2==0)
{
echo $strNoRecordsFound;
}
Else {
While ($row2=ezpub_fetch_array($result2)){
        echo "<div class=\"callout\">
        <a href=\"cursuri/$row2[course_url]\"><h4>$row2[course_name] </h4></a>
        <img src=\"$strSiteURL/img/cursuri/".$row2["course_picture"]."\" alt=\"$row2[course_description]\" />
        $row2[course_description]</div>";
        }
        }
echo "</div>";
if ($i<=3) {echo "</div><hr />";}
    Elseif($i%3 == 0) {
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
