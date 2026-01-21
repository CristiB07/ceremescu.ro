<?php
//update 16.12.2025
include_once '../settings.php';
include_once '../classes/common.php';
include_once '../classes/paginator.class.php';
$pageurl='/blog/index.php';
$cursuritrail="blog/";
$strKeywords="Blog, articole, noutăți, știri, informații, actualizări";
$strDescription="Blogul CyberPlus.ro - cele mai recente articole și noutăți din domeniul IT și tehnologie.";
$strPageTitle="Blog Cyber Plus SRL";
include '../header.php';
?>
<div class="grid-x grid-padding-x">
    <div class="large-12 medium-12 small-12 cell">
<div class="grid-x grid-padding-x">
    <div class="large-12 medium-12 small-12 cell">
        <h1><?php echo htmlspecialchars($strBlog, ENT_QUOTES, 'UTF-8')?></h1>
    </div>
</div>
<?php
$stmt = $conn->prepare("SELECT * FROM blog_articole WHERE articol_tip=1");
$stmt->execute();
$result = $stmt->get_result();
$numar = $result->num_rows;
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$stmt = $conn->prepare("SELECT * FROM blog_articole WHERE articol_tip=1 ORDER BY articol_data_publicarii DESC $pages->limit");
$stmt->execute();
$result = $stmt->get_result();
if ($numar==0)
{
echo "<div class=\"callout alert\">".htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8')."</div>";
}
else {
if ($numar>10 && !isset($_GET['page'])){
    ?>
    <div class="orbit" role="region" aria-label="Imagini articole blog" data-orbit data-options="animInFromLeft:fade-in; animInFromRight:fade-in; animOutToLeft:fade-out; animOutToRight:fade-out;">
      <ul class="orbit-container">
        <button class="orbit-previous"><span class="show-for-sr"><?php echo htmlspecialchars($strPrevious, ENT_QUOTES, 'UTF-8')?></span>&#9664;&#xFE0E;</button>
        <button class="orbit-next"><span class="show-for-sr"><?php echo htmlspecialchars($strNext, ENT_QUOTES, 'UTF-8')?></span>&#9654;&#xFE0E;</button>
       <?php
       $stmt1 = $conn->prepare("SELECT * FROM blog_articole ORDER BY articol_data_publicarii DESC");
$stmt1->execute();
$result1 = $stmt1->get_result();
while ($row1 = $result1->fetch_assoc())
{echo  "<li class=\"is-active orbit-slide\">
          <img class=\"orbit-image\" src=\"".htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8')."/img/blog/".htmlspecialchars($row1['articol_imaginetitlu'], ENT_QUOTES, 'UTF-8')."\" alt=\"".htmlspecialchars($row1['articol_titlu'], ENT_QUOTES, 'UTF-8')."\">
          <figcaption class=\"orbit-caption\">".htmlspecialchars($row1['articol_titlu'], ENT_QUOTES, 'UTF-8')."</figcaption>
        </li>";
 }
$stmt1->close(); 
 ?>
      </ul>
    </div>
 <?php
 //end slider if more than 10 articles
}
?><div class="grid-x grid-padding-x">
    <div class="large-12 medium-12 small-12 cell">
     <div class="paginate">
      <?php
echo htmlspecialchars($strTotal, ENT_QUOTES, 'UTF-8') . " " .htmlspecialchars($numar, ENT_QUOTES, 'UTF-8')." ".htmlspecialchars($strArticles, ENT_QUOTES, 'UTF-8');
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"index.php\" title=\"strClearAllFilters\">".htmlspecialchars($strShowAll, ENT_QUOTES, 'UTF-8')."</a>&nbsp;";
?>
            </div>
            </div>
            </div>
<?php While ($row = $result->fetch_assoc()){

    		echo "
            <div class=\"grid-x grid-margin-x\"><div class=\"large-5 cell\">
            <p><img src=\"".htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8')."/img/blog/".htmlspecialchars($row['articol_imaginetitlu'], ENT_QUOTES, 'UTF-8')."\" alt=\"".htmlspecialchars($row['articol_titlu'], ENT_QUOTES, 'UTF-8')."\"></p>
          </div>
          <div class=\"large-7 cell\">
            <h3><a href=\"".htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8')."/blog/".htmlspecialchars($row['articol_url'], ENT_QUOTES, 'UTF-8')."\">" .htmlspecialchars($row['articol_titlu'], ENT_QUOTES, 'UTF-8')."</a></h5>
            <p>
              <i class=\"far fa-user xl\"></i>".htmlspecialchars($row['articol_autor'], ENT_QUOTES, 'UTF-8')."&nbsp;&nbsp;
              <i class=\"fas fa-calendar-alt xl\"></i> " . htmlspecialchars(date("d.m.Y H:i:s",strtotime($row["articol_data_publicarii"])), ENT_QUOTES, 'UTF-8') . "
            </p>
            <p>" . truncateblogarticle($row["articol_continut"]) . " <a href=\"".htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8')."/blog/".htmlspecialchars($row['articol_url'], ENT_QUOTES, 'UTF-8')."\">" .htmlspecialchars($strReadMore, ENT_QUOTES, 'UTF-8')."</a></p>
          </div>
        </div>
        <hr>";
}
$stmt->close();
?><div class="grid-x grid-padding-x">
    <div class="large-12 medium-12 small-12 cell">
     <div class="paginate">
     <?php
echo $pages->display_pages() . " <a href=\"index.php\" title=\"strClearAllFilters\">".htmlspecialchars($strShowAll, ENT_QUOTES, 'UTF-8')."</a>&nbsp;";
?>
            </div>
            </div>
            </div>
<?php
}?>
</div>
</div>
<?php
include '../bottom.php';
?>