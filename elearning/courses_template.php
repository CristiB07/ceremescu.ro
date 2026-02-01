<?php

// Sanitize URL input
$url_safe = mysqli_real_escape_string($conn, $url);

// Prepared statement pentru SELECT course
$stmt = mysqli_prepare($conn, "SELECT * FROM elearning_courses WHERE course_url=?");
mysqli_stmt_bind_param($stmt, "s", $url_safe);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row=ezpub_fetch_array($result)) {

$strKeywords=htmlspecialchars($row['course_keywords'], ENT_QUOTES, 'UTF-8');
$strDescription=htmlspecialchars($row['course_metadescription'], ENT_QUOTES, 'UTF-8');
$strPageTitle=htmlspecialchars($row['course_name'], ENT_QUOTES, 'UTF-8');
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
        <h2><?php echo htmlspecialchars($row["course_name"], ENT_QUOTES, 'UTF-8')?></h2>
        <img src="<?php echo $strSiteURL ?>/img/cursuri/<?php echo htmlspecialchars($row["course_picture"], ENT_QUOTES, 'UTF-8')?>"
            alt="<?php echo htmlspecialchars($row["course_name"], ENT_QUOTES, 'UTF-8')?>" style="width:100%">
        <h3>Prezentare</h3>
        <p><?php echo htmlspecialchars($row["course_description"], ENT_QUOTES, 'UTF-8')?></p>
        <h3>Obiective</h3>
        <p><?php echo htmlspecialchars($row["course_objective"], ENT_QUOTES, 'UTF-8')?></p>
        <h3>Public țintă</h3>
        <p><?php echo htmlspecialchars($row["course_target"], ENT_QUOTES, 'UTF-8')?></p>
        <?php If ($row["course_discount"]!=='0.0000')
					{
					$pprice=romanize($row["course_discount"]*$vatprc);
					}
					else
					{
						$pprice=romanize($row["course_price"]*$vatprc);
					} ?>
        <h3>Preț: <?php echo htmlspecialchars($pprice, ENT_QUOTES, 'UTF-8')?> lei</h3>
        <h3>Ce primiți</h3>
        <p><?php echo htmlspecialchars($row["course_whatyouget"], ENT_QUOTES, 'UTF-8')?></p>
    </div>

    <!-- END row blog -->
    <div class="large-4 medium-4 small-4 cell">
        <h3><?php echo $strTrainer?></h3>
        <?php 
		  // Sanitize input
		  $course_author_safe = (int)$row['course_author'];
		  
		  // Prepared statement pentru SELECT trainer
		  $stmt3 = mysqli_prepare($conn, "SELECT * FROM elearning_trainers WHERE trainer_utilizator_ID=?");
		  mysqli_stmt_bind_param($stmt3, "i", $course_author_safe);
		  mysqli_stmt_execute($stmt3);
		  $result3 = mysqli_stmt_get_result($stmt3);
			
			if ($row3=ezpub_fetch_array($result3)) {
		  ?>
        <div class="callout" style="padding:3px"><img
                src="<?php echo $strSiteURL ?>/img/traineri/<?php echo htmlspecialchars($row3["trainer_picture"], ENT_QUOTES, 'UTF-8')?>"
                alt="<?php echo htmlspecialchars($row3["trainer_name"], ENT_QUOTES, 'UTF-8')?>"></div>

        <h3><?php echo htmlspecialchars($row3["trainer_name"], ENT_QUOTES, 'UTF-8')?></h3>
        <p><?php echo htmlspecialchars($row3["trainer_presentation_short"], ENT_QUOTES, 'UTF-8')?></p>
        <?php
}
else
{echo "<p>$strNoRecordsFound</p>";}

If ($row["course_delivery"]==0 OR $row["course_delivery"]==2) {

?>
        <h3>Când este următorul curs live</h3>
        <?php
		  // Sanitize input
		  $course_id_safe = (int)$row['Course_id'];
		  $sdata_safe = mysqli_real_escape_string($conn, $sdata);
		  
		  // Prepared statement pentru SELECT schedule
		  $stmt4 = mysqli_prepare($conn, "SELECT schedule_start_date, schedule_end_date, schedule_exam_date, schedule_start_hour, schedule_details
		  FROM elearning_courseschedules 
		  WHERE schedule_course_ID=? AND schedule_start_date >= ?");
		  mysqli_stmt_bind_param($stmt4, "is", $course_id_safe, $sdata_safe);
		  mysqli_stmt_execute($stmt4);
		  $result4 = mysqli_stmt_get_result($stmt4);
			$numar2 = mysqli_num_rows($result4);
			
		if ($numar2!=0)
		{
			While ($row4=ezpub_fetch_array($result4)){
		$startdate=date('d M Y', strtotime($row4['schedule_start_date']));
		$enddate=date('d M Y', strtotime($row4['schedule_end_date']));
		$examdate=date('d M Y', strtotime($row4['schedule_exam_date']));
		
		echo "<p><strong>". $strData.":</strong> ". htmlspecialchars($startdate, ENT_QUOTES, 'UTF-8')."-". htmlspecialchars($enddate, ENT_QUOTES, 'UTF-8');
		echo "<br />";
		echo "<strong>". $strHours.":</strong> ". htmlspecialchars($row4['schedule_start_hour'], ENT_QUOTES, 'UTF-8');
		echo "<br />";
		echo "<strong>". $strExamDate.":</strong> ". htmlspecialchars($examdate, ENT_QUOTES, 'UTF-8'); 
		echo "<br />";		
		echo "<strong>". $strDetails.":</strong> ". htmlspecialchars($row4["schedule_details"], ENT_QUOTES, 'UTF-8'); 
		echo "<br />";
		echo "<strong>". $strPartner.":</strong> <a href=\"".htmlspecialchars($strSiteURL, ENT_QUOTES, 'UTF-8')."parteneri/" . htmlspecialchars($row4["location_url"], ENT_QUOTES, 'UTF-8')."\">".htmlspecialchars($row4["location_name"], ENT_QUOTES, 'UTF-8')."</a><br />"; 
		echo "<hr /></p>";
		 
}}
	else{echo "<p>$strNoRecordsFound</p>
		<h3>$strOnlineCourse</h3>
		<p>Acest curs poate fi urmat în regim elearning, vă puteți înscrie oricând, fără a aștepta formarea unei grupe și urma modulele în ritmul propriu. Conținutul cursului este același, având acces și la prezentarea înregistrată video.</p>
		";}}
else {
	echo "<h3>$strOnlineCourse</h3>
		<p>Acest curs poate fi urmat în regim elearning, vă puteți înscrie oricând, fără a aștepta formarea unei grupe și urma modulele în ritmul propriu. Conținutul cursului este același, având acces și la prezentarea înregistrată video.</p>
		";
}		

?>
        <div>
            <h3>Înscriere</h3>
        </div>
        <p>Pentru a vă înscrie la acest curs trebuie să vă <a
                href="<?php echo $strSiteURL ?>/account/createaccount.php"><strong>creați cont pe acest
                    site</strong></a>. Dacă aveți deja unul, intrați în contul dumneavoastră și efectuați înscrierea de
            acolo.
            <p />
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
    http_response_code(404);
    $strKeywords="Pagina nu a fost găsită";
$strDescription="Pagina nu a fost găsită";
$strPageTitle="Pagina nu a fost găsită";
$pageurl='404.php';
include 'header.php';
$previous = "javascript:history.go(-1)";
if(isset($_SERVER['HTTP_REFERER'])) {
    $previous = $_SERVER['HTTP_REFERER'];
}

echo "<div class=\"grid-x grid-padding-x\" >
<div class=\"large-12 medium-12 small-12 columns\">
<div class=\"callout alert\">";
echo "<h1><i class=\"fas fa-exclamation-triangle fa-xl\"></i>&nbsp;Pagina nu a fost găsită</h1>";
echo "<p>Adresa pe care o căutați nu a fost găsită. Linkul care v-a adus aici poate fi depășit sau,
 dacă ați introdus manual adresa, este posibil să o fi scris greșit.</p>
 <p>Dacă problema persistă, vă rugăm să ne contactați la <a href=\"mailto:" . $siteCompanyEmail . "\">" . $siteCompanyEmailMasked . "</a> pentru asistență.</p>
 <p align=\"center\">
 <a href=\"" . $strSiteURL . "\" class=\"button\"><i class=\"fas fa-home fa-xl\"></i>&nbsp;Înapoi la pagina principală</a> 
 <a href=\"" . $previous . "\" class=\"button\"><i class=\"fas fa-backward fa-xl\"></i>&nbsp;Înapoi la pagina anterioară</a> 
 </p>";
echo "</div></div></div>";
}