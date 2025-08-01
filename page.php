<?php
//update 8.01.2024

include 'settings.php';
include 'classes/common.php';
include 'classes/paginator.class.php';

$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$producttrail="produse/";
$thumbnailstrail="img/products/";

$fullurl=$_SERVER["REQUEST_URI"];
//$page=includeTrailingBackslash($fullurl);
$fullpage=includeTrailingBackslash($fullurl);
$page=str_replace("master.ro/", "",$fullpage);
$pieces = explode("/", $page);
$cats=substr_count($page,"/");
If ($cats==4) {
$url=$pieces[3];
$category=$pieces[2];
$whereto=$pieces[1];
}
ElseIf ($cats==3) {
$url=$pieces[2];
$category=$pieces[1];
$whereto="";}
ElseIf ($cats==2) {
$url=$pieces[1];
$category=$pieces[0];
$whereto="";}
else
{
$url=$pieces[1];
$category="";
$whereto="";
;}
If ($category==$siteURLShort) {
$category="";}
//redirect to right page
//echo $url . "= url<br />";
//echo $category . "= category<br />";
//echo $whereto . "= whereto<br />";

if (strpos($fullurl,'.asp') !== false) {
    if (($pos = strpos($fullurl, "=")) !== FALSE) { 
    $whatIWant = substr($fullurl, $pos+1); 
	echo $whatIWant;
$query="SELECT * FROM magazin_produse WHERE produs_id='$whatIWant'";
$result=ezpub_query($conn,$query);
if ($row=ezpub_fetch_array($result)) {
$urlto=$row["produs_url"];
header("HTTP/1.1 301 Moved Permanently");
header("Location: $strSiteURL/" . "produse/$urlto");
exit();
}
}
}

// Regular pages

if ($url!="" AND $category=="") {
$url = strtok($url, '?');
$query="SELECT * FROM cms_pagini WHERE pagina_url='$url'";
$result=ezpub_query($conn,$query);
if ($row=ezpub_fetch_array($result)) {
$strKeywords=$row['pagina_keywords'];
$strDescription=$row['pagina_descriere'];
$strPageTitle=$row['pagina_titlu'];
$strPageContent=str_replace(array("../img/pages/","<li>", "<ul class=\"tiny\">"),array($strSiteURL."/img/pages/","<li class=\"tiny\">", "<ul>"),$row['pagina_continut']);
include 'header.php';
echo "
<nav aria-label=\"$strYouAreHere:\" role=\"navigation\">
  <ul class=\"breadcrumbs\">
    <li><a href=\"$strSiteURL\">Home</a></li>
       <li class=\"disabled\">$strPageTitle</li>
  </ul>
</nav>
";
echo "
 <div class=\"grid-x grid-margin-x\">
			  <div class=\"large-12 medium-12 small-12 cell\">";
			  if (IsSet ($row['pagina_imaginetitlu']) AND $row['pagina_imaginetitlu']!='')
			  {
				  $strPageImage=$row['pagina_imaginetitlu'];
				  echo "<img src=\"$strSiteURL/img/pages/$strPageImage\" height=\"auto\" width=\"auto\" alt=\"$strPageTitle\" />";
			  }
Echo "<h1>$strPageTitle</h1>
	$strPageContent
  </div>
</div>";
 ?>    
<?php }
else
{
$strKeywords="Pagina nu a fost găsită";
$strDescription="Pagina nu a fost găsită";
$strPageTitle="Pagina nu a fost găsită";
include 'header.php';

echo " <div class=\"grid-x grid-margin-x\">
			  <div class=\"large-12 medium-12 small-12 cell\">";
echo "<h1>Pagina nu a fost găsită</h1>";
echo "Adresa pe care o căutați nu a fost găsită.Linkul care v-a adus aici poate fi depășit sau, dacă ați introdus manual adresa, este posibil să o fi scris greșit.";
echo "</div></div>";
}
}

//Cursuri
//Courses
Elseif ($url!="" AND $category=="cursuri") {
$query="SELECT * FROM elearning_courses WHERE course_url='$url'";
$result=ezpub_query($conn, $query);
if ($row=ezpub_fetch_array($result)) {

$strKeywords=$row['course_keywords'];
$strDescription=$row['course_metadescription'];
$strPageTitle=$row['course_name'];
include 'header.php';
echo "
<nav aria-label=\"$strYouAreHere:\" role=\"navigation\">
  <ul class=\"breadcrumbs\">
    <li><a href=\"$strSiteURL\">Home</a></li>
    <li><a href=\"$strSiteURL/elearning/\">$strCourses</a></li>
       <li class=\"disabled\">$strPageTitle</li>
  </ul>
</nav>
";
?>
		<div class="grid-x grid-padding-x ">
               <div class="large-8 medium-8 small-8 cell">
		<h2><?php echo $row["course_name"]?></h2>
          <img src="<?php echo $strSiteURL ?>/img/cursuri/<?php echo $row["course_picture"]?>" alt="<?php echo $row["course_name"]?>" style="width:100%">
		  <h3>Prezentare</h3>
		  <p><?php echo $row["course_description"]?></p>
		  <h3>Obiective</h3>
		  <p><?php echo $row["course_objective"]?></p>
		  <h3>Public țintă</h3>
		  <p><?php echo $row["course_target"]?></p>
		  <?php If ($row["course_discount"]==0) {?>
		  <h3>Preț: <?php echo $row["course_price"]?> lei</h3>
		  <?php } Else {?>
		  <h3>Preț: <s><?php echo $row["course_price"]?> lei</s> <?php echo $row["course_discount"]?> lei</h3>
		  <?php }?>
		  <h3>Ce primiți</h3>
		  <p><?php echo $row["course_whatyouget"]?></p>
		  </div>

		  <!-- END row blog -->
	<div class="large-4 medium-4 small-4 cell">
          <h3><?php echo $strTrainer?></h3> 
		  	  <?php 
		  $query3="SELECT * FROM elearning_trainers WHERE trainer_utilizator_ID=$row[course_author]";
		  $result3=ezpub_query($conn, $query3);
			if ($row3=ezpub_fetch_array($result3)) {
		  ?>
          <div class="callout" style="padding:3px"><img src="<?php echo $strSiteURL ?>/img/traineri/<?php echo $row3["trainer_picture"]?>" alt="<?php echo $row3["trainer_name"]?>"></div>
        
	<h3><?php echo $row3["trainer_name"]?></h3>
		<p><?php echo $row3["trainer_presentation_short"]?></p>	
<?php
}
else
{echo "<p>$strNoRecordsFound</p>";}

If ($row["course_delivery"]==0 OR $row["course_delivery"]==2) {

?>
          <h3>Când este următorul curs live</h3>
		<?php
		  $query4="SELECT schedule_start_date, schedule_end_date, schedule_exam_date, schedule_start_hour, schedule_details
		  FROM elearning_courseschedules 
		  WHERE  schedule_course_ID=$row[Course_id] 
		  AND schedule_start_date >= '$sdata'";
			$result4=ezpub_query($conn,$query4);
			$numar2=ezpub_num_rows($result4,$query4);
		if ($numar2!=0)
		{
			While ($row4=ezpub_fetch_array($result4)){
		$startdate=date('d M Y', strtotime($row4['schedule_start_date']));
		$enddate=date('d M Y', strtotime($row4['schedule_end_date']));
		$examdate=date('d M Y', strtotime($row4['schedule_exam_date']));
		
		echo "<p><strong>". $strData.":</strong> ". $startdate."-". $enddate;
		echo "<br />";
		echo "<strong>". $strHours.":</strong> ". $row4['schedule_start_hour'];
		echo "<br />";
		echo "<strong>". $strExamDate.":</strong> ". $examdate; 
		echo "<br />";		
		echo "<strong>". $strDetails.":</strong> ". $row4["schedule_details"]; 
		echo "<br />";
		echo "<strong>". $strPartner.":</strong> <a href=\"".$strSiteURL."parteneri/" . $row4["location_url"]."\">".$row4["location_name"]."</a><br />"; 
		echo "<hr /></p>";
		 
}}
	Else{echo "<p>$strNoRecordsFound</p>
		<h3>$strOnlineCourse</h3>
		<p>Acest curs poate fi urmat în regim elearning, vă puteți înscrie oricând, fără a aștepta formarea unei grupe și urma modulele în ritmul propriu. Conținutul cursului este același, având acces și la prezentarea înregistrată video.</p>
		";}}
Else {
	echo "<h3>$strOnlineCourse</h3>
		<p>Acest curs poate fi urmat în regim elearning, vă puteți înscrie oricând, fără a aștepta formarea unei grupe și urma modulele în ritmul propriu. Conținutul cursului este același, având acces și la prezentarea înregistrată video.</p>
		";
}		

?>
 <div>
          <h3>Înscriere</h3>
        </div>
		<p>Pentru a vă înscrie la acest curs trebuie să vă <a href="<?php echo $strSiteURL ?>/elearning/inscriere.php"><strong>creați cont pe acest site</strong></a>. Dacă aveți deja unul, intrați în contul dumneavoastră și efectuați înscrierea de acolo.<p/>
<div>
          <h3>Alte informații</h3>
		  </div>
						<h3>Mobil: 0722 575 390</h3>
						<p>Email:<a class="email-link" href="#"><?php echo $siteCompanyEmail?></a></p>
						</div>
		</div>
	
<?php
}
else
{
$strKeywords="Pagina nu a fost găsită";
$strDescription="Pagina nu a fost găsită";
$strPageTitle="Pagina nu a fost găsită";
include 'header.php';

echo "<div id=\"text\">";
echo "<h1>Pagina nu a fost găsită</h1>";
echo "<h1>Pagina nu a fost găsită</h1>";
echo "Adresa pe care o căutați nu a fost găsită.Linkul care v-a adus aici poate fi depășit sau, dacă ați introdus manual adresa, este posibil să o fi scris greșit.";
echo "</div>";
}
}

// Products pages
//categories
If ($category=="shop" AND $url!="") {
$strPageTitle=$category;
include 'header.php';
$query="SELECT * FROM magazin_produse WHERE produs_limba='$lang' AND produs_categorie='$url'";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result, $query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query2= $query . " ORDER BY produs_nume ASC $pages->limit" ;
$result2=ezpub_query($conn,$query2);

If ($numar==0)
{echo "<p>$strNoRecordsFound</p>";}
Else {
	$i = 0;
?>
<nav aria-label="<?php echo $strYouAreHere?>:" role="navigation">
  <ul class="breadcrumbs">
    <li><a href="<?php echo $strSiteURL?>"><?php echo $strHome?></a></li>
    <li><a href="<?php echo $strSiteURL."/shop/".$url?>"><?php echo $category?></a></li>
    <li class="disabled"><?php echo $strPageTitle?></li>
    </ul>
</nav>
	<div class="grid-x grid-padding-x" >
	  <div class="large-4 medium-4 small-4 cell">
	  <div>
	  <h3><?php echo $strCategory?></h3>
	  <ul class="vertical menu">
	  <?php 
	  $query="SELECT Distinct produs_categorie, produs_fcategorie FROM magazin_produse WHERE produs_limba='$lang'";
$result=ezpub_query($conn,$query);
While ($row=ezpub_fetch_array($result)){
echo "<li><a href=\"$strSiteURL/shop/$row[produs_categorie]/\" class=\"vertical\">$row[produs_fcategorie]</a></li>";
}  ?>
</ul>
<hr />
</div>
	  <div class="promoted">
			<h3><?php echo $strPromotedProduct ?></h3>
			<?php
			$query="SELECT * FROM magazin_produse WHERE produs_limba='$lang' ORDER BY rand() Limit 1";
			$result=ezpub_query($conn,$query);
			$row=ezpub_fetch_array($result)
			?>
              <img src="<?php echo $strSiteURL ?>/img/products/<?php echo $row["produs_imagine"]?>" class="shopim">
                 <h4><?php echo $row["produs_nume"]?></h4>
                <p class="smaller"><?php echo $row["produs_descriere"]?></p>
				</div>
            </div>
             <div class="large-8 columns">
			  <div class="grid-x grid-padding-x">  
 <div class="large-12 cell">
<div class="paginate">
<?php
echo $strTotal . " " .$numar." ".$strProducts ;
echo " <br /><br />";
echo $pages->display_pages();
?>
</div>
</div>
</div>
	    <div class="grid-x grid-margin-x" data-equalizer data-equalize-on="medium" id="test-eq">
<?php 
While ($row=ezpub_fetch_array($result2)){
	  $i++ ;
	  $vatrat=$row["produs_tva"]/100;
	  $vatprc=$vatrat+1;
	echo "<div class=\"large-3 medium-3 small-3 cell \" >
	<div class=\"column\" data-equalizer-watch>";
		if (strlen($row['produs_nume'])>80)
	{$productname=substr($row['produs_nume'], 0, 80)."&hellip;";}
else
{$productname=$row['produs_nume'];}
		echo "<a href=\"$strSiteURL/$producttrail$row[produs_url]\"><h5>$productname</h5>";
		
					If ($row["produs_dpret"]!=='0.0000')
					{
					$pprice=romanize($row["produs_dpret"]*$vatprc);
					}
					Else
					{
						$pprice=romanize($row["produs_pret"]*$vatprc);
					}
					echo                    "
                  </div>
				  <div class=\"column align-self-bottom\"><img src=\"$strSiteURL/img/products/$row[produs_imagine]\" class=\"shopim\"></a></div>
                  <div class=\"column align-self-bottom\">  <h6><strong>$strPrice:" . " $pprice " ." lei</strong></h6></div>
					<div class=\"column align-self-bottom\"><p><a href=\"$strSiteURL/shop/order.php?action=order&pID=$row[produs_id]\" title=\"$strAddToCart $row[produs_nume]\" class=\"expanded button\"><i class=\"fas fa-cart-plus\"></i>&nbsp;$strAddToCart</a></p></div>
				</div>";
								  if($i%4 == 0) {
                echo  "</div><hr />
				<div class=\"grid-x grid-padding-x\"  data-equalizer data-equalize-on=\"medium\" id=\"test-eq\">";

	}
}
}

echo "</div>"
?>
 <div class="grid-x grid-padding-x">  
 <div class="large-12 cell">
<div class="paginate">
<?php
echo $pages->display_pages();
?>
</div>
</div>
</div>
</div>
</div>
<?php
}
//pagination categories

//categories
If ($whereto=="shop" AND $url!="") {
$strPageTitle=$category;
include 'header.php';
$query="SELECT * FROM magazin_produse WHERE produs_limba='$lang' AND produs_categorie='$category'";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result, $query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query2= $query . " ORDER BY produs_nume ASC $pages->limit" ;
$result2=ezpub_query($conn,$query2);

If ($numar==0)
{echo "<p>$strNoRecordsFound</p>";}
Else {
	$i = 0;
?>
<nav aria-label="<?php echo $strYouAreHere?>:" role="navigation">
  <ul class="breadcrumbs">
    <li><a href="<?php echo $strSiteURL?>"><?php echo $strHome?></a></li>
    <li><a href="<?php echo $strSiteURL."/shop/".$url?>"><?php echo $category?></a></li>
    <li class="disabled"><?php echo $strPageTitle?></li>
    </ul>
</nav>
	<div class="grid-x grid-padding-x" >
	  <div class="large-4 medium-4 small-4 cell">
	  <div>
	  <h3><?php echo $strCategory?></h3>
	  <ul class="vertical menu">
	  <?php 
	  $query="SELECT Distinct produs_categorie, produs_fcategorie FROM magazin_produse WHERE produs_limba='$lang'";
$result=ezpub_query($conn,$query);
While ($row=ezpub_fetch_array($result)){
echo "<li><a href=\"$strSiteURL/shop/$row[produs_categorie]/\">$row[produs_fcategorie]</a></li>";
}  ?>
</ul>
<hr />
</div>
	  <div class="promoted">
			<h3><?php echo $strPromotedProduct ?></h3>
			<?php
			$query="SELECT * FROM magazin_produse WHERE produs_limba='$lang' ORDER BY rand() Limit 1";
			$result=ezpub_query($conn,$query);
			$row=ezpub_fetch_array($result)
			?>
              <img src="<?php echo $strSiteURL ?>/img/products/<?php echo $row["produs_imagine"]?>" class="shopim">
                 <h4><?php echo $row["produs_nume"]?></h4>
                <p class="smaller"><?php echo $row["produs_descriere"]?></p>
				</div>
            </div>
             <div class="large-8 columns">
			  <div class="grid-x grid-padding-x">  
 <div class="large-12 cell">
<div class="paginate">
<?php
echo $strTotal . " " .$numar." ".$strProducts ;
echo " <br /><br />";
echo $pages->display_pages();
?>
</div>
</div>
</div>
	    <div class="grid-x grid-margin-x" data-equalizer data-equalize-on="medium" id="test-eq">
<?php 
While ($row=ezpub_fetch_array($result2)){
	  $i++ ;
	  $vatrat=$row["produs_tva"]/100;
	  $vatprc=$vatrat+1;
	echo "<div class=\"large-3 medium-3 small-3 cell \" >
	<div class=\"column\" data-equalizer-watch>";
		if (strlen($row['produs_nume'])>80)
	{$productname=substr($row['produs_nume'], 0, 80)."&hellip;";}
else
{$productname=$row['produs_nume'];}
		echo "<a href=\"$strSiteURL/$producttrail$row[produs_url]\"><h5>$productname</h5>";
		
					If ($row["produs_dpret"]!=='0.0000')
					{
					$pprice=romanize($row["produs_dpret"]*$vatprc);
					}
					Else
					{
						$pprice=romanize($row["produs_pret"]*$vatprc);
					}
echo                    "
                  </div>
				  <div class=\"column align-self-bottom\"><img src=\"$strSiteURL/img/products/$row[produs_imagine]\" class=\"shopim\"></a></div>
                  <div class=\"column align-self-bottom\">  <h6><strong>$strPrice:" . " $pprice " ." lei</strong></h6></div>
					<div class=\"column align-self-bottom\"><p><a href=\"$strSiteURL/shop/order.php?action=order&pID=$row[produs_id]\" title=\"$strAddToCart $row[produs_nume]\" class=\"expanded button\"><i class=\"fas fa-cart-plus\"></i>&nbsp;$strAddToCart</a></p></div>
				</div>";
								  if($i%4 == 0) {
                echo  "</div><hr />
				<div class=\"grid-x grid-padding-x\"  data-equalizer data-equalize-on=\"medium\" id=\"test-eq\">";

	}
}
}

echo "</div>"
?>
 <div class="grid-x grid-padding-x">  
 <div class="large-12 cell">
<div class="paginate">
<?php
echo $pages->display_pages();
?>
</div>
</div>
</div>
</div>
</div>
<?php
}

//singleproduct
Elseif 
($category=="produse" AND $url!="") {
$query="SELECT * FROM magazin_produse WHERE produs_url='$url'";
$result=ezpub_query($conn,$query);
if ($row=ezpub_fetch_array($result)) {
$strKeywords=$row['produs_keywords'];
$strDescription=$row['produs_meta'];
$strPageTitle=$row['produs_nume'];
include 'header.php';
$vatrat=$row["produs_tva"]/100;
$vatprc=$vatrat+1;
?>
<script>
function changeImage(p) {
document.getElementById("main").src = p;
document.getElementById("modal").src = p;
document.getElementById('a').style.backgroundImage="url('p')";
var abc = p;
}
</script>
<nav aria-label="<?php echo $strYouAreHere?>:" role="navigation">
  <ul class="breadcrumbs">
    <li><a href="<?php echo $strSiteURL?>"><?php echo $strHome?></a></li>
    <li><a href="<?php echo $strSiteURL."/shop/".$row["produs_categorie"]?>"><?php echo $row["produs_fcategorie"]?></a></li>
    <li class="disabled"><?php echo $strPageTitle?></li>
    </ul>
</nav>

<?php
echo "
<div class=\"grid-x grid-margin-x\">
  <div class=\"large-12 columns\">
  <h1>$strPageTitle</h1>
  </div></div>
<div class=\"grid-x grid-margin-x\">
          <div class=\"medium-6 cell\">
        <label><$strImagePreview></label>
					";?>		
<div class="large reveal" id="Modal1" data-reveal>
					<!-- Modal content -->
					<h3><?php echo $row["produs_nume"]?></h3>
					 <p align="center"><img id="modal" name="modal" src="<?php echo $strSiteURL?>/img/products/<?php echo $row["produs_imagine"]?>" height="auto" width="auto" alt="<?php echo $row["produs_nume"]?>" /></p>
  <button class="close-button" data-close aria-label="Close modal" type="button">
    <span aria-hidden="true">&times;
  </button>
						</div>
						
		<?php 
	echo "<a href=\"#\" data-open=\"Modal1\"><img id=\"main\" name=\"main\" class=\"zoom\" src=\"$strSiteURL/img/products/$row[produs_imagine]\" height=\"auto\" width=\"auto\" alt=\"$row[produs_nume]\" /></a>";	
	
	$aimage=array($row["produs_thumb"]);
		$timage=count($aimage);
	$pimages=explode(";", $row["produs_thumb"]);
	$t2image=count($pimages);
echo "<div class=\"grid-x grid-padding-x small-up-4 align-center\">
<button id=\"prev\" class=\"button\"><<<</button>  
<ul id=\"myUl\" class=\"menu\">
 <li class=\"myLi\"><a href=\"#\" OnClick=\"changeImage('$strSiteURL/img/products/$row[produs_imagine]')\"><img src='$strSiteURL/img/products/$row[produs_imagine]' alt=' $row[produs_nume]' width='180px' /></a></li>

";
$i = 0;
while($i < count($pimages)) {
	
echo              "<div class=\"cell\">
               <li class=\"myLi\"><a href=\"#\" OnClick=\"changeImage('$strSiteURL/img/products/$pimages[$i]')\"><img src='$strSiteURL/img/products/$pimages[$i]' alt='$row[produs_nume]' width='180px' /></a></li>
</div>"; 
$i++;};
    
echo"            
</ul>
<button id=\"next\" class=\"button align-right\">>>></button> </div>
          </div>
          <div class=\"medium-6 large-5 cell large-offset-1\">
	$row[produs_descriere]<br />";
	$sprice=$row['produs_pret']*$vatprc;
	$fprice=romanize($sprice);
	$pprice=$row["produs_dpret"]*$vatprc;
	$dprice=romanize($pprice);
echo "<p><a href=\"$strSiteURL/shop/order.php?action=order&pID=$row[produs_id]\" title=\"$strAddToCart $row[produs_nume]\" class=\"expanded button\"><i class=\"fas fa-cart-plus\"></i>&nbsp;$strAddToCart</a></p>";
If ($row["produs_dpret"]!=='0.0000')
{
echo	"<h3>$strPrice<span style=\"text-decoration:line-through\">: $fprice lei<span style=\"color: red;\"> $strPromoPrice:&nbsp;&nbsp;$dprice lei</h3>";
}
Else
{
echo	"<h3>$strPrice: $fprice lei";
}
echo "</div>
</div>
<hr />
<div class=\"grid-x grid-margin-x\">
  <div class=\"large-12 columns\">
  <h3>$strSimilarProducts</h3>
  </div></div>
	<div class=\"grid-x grid-padding-x\" >
";

$query2= "SELECT * FROM magazin_produse ORDER BY produs_nume Limit 4" ;
$result2=ezpub_query($conn,$query2);
$numar=ezpub_num_rows($result2, $query2);
If ($numar==0)
{echo "<p>$strNoRecordsFound</p>";}
Else {
	$i = 0;
While ($row=ezpub_fetch_array($result2)){
	  $i++ ;
	echo "<div class=\"large-3 medium-3 small-3 cell \" >
		<a href=\"$strSiteURL/$producttrail$row[produs_url]\"><h5>$row[produs_nume]</h5>";
					If ($row["produs_dpret"]!=='0.0000')
					{
					$pprice=romanize($row["produs_dpret"]*$vatprc);
					}
					Else
					{
						$pprice=romanize($row["produs_pret"]*$vatprc);
					}
echo                    "<h6><strong>$strPrice:" . " $pprice " ." lei</strong></h6>
                  <img src=\"$strSiteURL/$thumbnailstrail$row[produs_thumb]\" class=\"shopim\"></a>
                    
					<p><a href=\"$strSiteURL/shop/order.php?action=order&pID=$row[produs_id]\" title=\"$strAddToCart $row[produs_nume]\" class=\"expanded button\"><i class=\"fas fa-cart-plus\"></i>&nbsp;$strAddToCart</a></p>
				</div>";
}}
echo "</div>";
}

else
{
$strKeywords="Pagina nu a fost găsită";
$strDescription="Pagina nu a fost găsită";
$strPageTitle="Pagina nu a fost găsită";
include 'header.php';

echo "<div class=\"row\">
<div class=\"large-12 columns\">";
echo "<h1>Pagina nu a fost găsită</h1>";
echo "Adresa pe care o căutați nu a fost găsită.Linkul care v-a adus aici poate fi depășit sau, dacă ați introdus manual adresa, este posibil să o fi scris greșit.";
echo "</div></div>";
}
}
if ($url="" AND $category=="") {
	$strKeywords="Pagina nu a fost găsită";
$strDescription="Pagina nu a fost găsită";
$strPageTitle="Pagina nu a fost găsită";
include 'header.php';

echo "<div class=\"row\">
<div class=\"large-12 columns\">";
echo "<h1>Pagina nu a fost găsită</h1>";
echo "Adresa pe care o căutați nu a fost găsită.Linkul care v-a adus aici poate fi depășit sau, dacă ați introdus manual adresa, este posibil să o fi scris greșit.";
echo "</div></div>";
}
?>

<script>
const list = {
  target: document.getElementById("myUl"),
  fullList: document.getElementById("myUl").querySelectorAll(".myLi"),
  itemsToList: 3,
  index: 0,
  // remove all children, append the amout of items we want
  update: function() {
    while (this.target.firstChild) {
      this.target.removeChild(this.target.firstChild);
    }
    for (let i = 0; i < this.itemsToList; i += 1) {
      if(this.fullList[this.index + i]) {
        this.target.appendChild(this.fullList[this.index + i]);
      }
    }
  },
  prev: function() {
    // if index 1 is displayed, go to end of list
    if (this.index <= 0) { 
      this.index = this.fullList.length; 
    }
    // decrement the index
    this.index -= this.itemsToList;
    // lower edge case, catch to always list the same amout of items
    if (this.index < 0) { 
      this.index = 0; 
    }
  },
  next: function() {
    // increment the index
    this.index += this.itemsToList;
    // if last item is shown start from beginning
    if (this.index >= this.fullList.length) {
      this.index = 0;
    } 
    // catch upper edge case, always list the same amout of items
    else if ( this.index > this.fullList.length - this.itemsToList + 1) {
      this.index = this.fullList.length - this.itemsToList;
    }
  }
}
// initialize by removing list and showing from index[0]
list.update();

document.getElementById("prev").addEventListener('click', function () {
  list.prev();
  list.update();
});

document.getElementById("next").addEventListener('click', function () {
  list.next();
  list.update();
});
</script>
 <hr />
 <?php
include 'bottom.php';
?>