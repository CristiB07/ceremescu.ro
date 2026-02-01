<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle="Toate datele cursului meu";
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
if (isset($_GET['cID'])) {
$cID= (int)$_GET['cID'];
if ($cID <= 0) {
	header("location:$strSiteURL/dashboard/dashboard.php");
	die();
}
} else {
header("location:$strSiteURL/dashboard/dashboard.php");
die();
}
?>

<?php
// Use prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM elearning_courses WHERE course_ID=?");
mysqli_stmt_bind_param($stmt, "i", $cID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row=ezpub_fetch_array($result)) {
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <h1><?php  echo $row['course_name']?></h1>
    </div>
</div>

<ul class="tabs" data-deep-link="true" data-update-history="true" data-deep-link-smudge="true"
    data-deep-link-smudge-delay="500" data-tabs id="deeplinked-tabs">
    <li class="tabs-title is-active"><a
            href="<?php echo $strSiteURL?>/elearning/student_mycourses.php?cID=<?php echo (int)$cID?>#panel1d"
            aria-selected="true"><?php echo $strCourseDescription ?></a></li>
    <li class="tabs-title"><a
            href="<?php echo $strSiteURL?>/elearning/student_mycourses.php?cID=<?php echo (int)$cID?>#panel2d"><?php echo $strLessons?>
            & <?php echo $strTests?></a></li>
    <li class="tabs-title"><a
            href="<?php echo $strSiteURL?>/elearning/student_mycourses.php?cID=<?php echo (int)$cID?>#panel3d"><?php echo $strQuestions?></a>
    </li>
    <li class="tabs-title"><a
            href="<?php echo $strSiteURL?>/elearning/student_mycourses.php?cID=<?php echo (int)$cID?>#panel4d"><?php echo $strFiles?></a>
    </li>
</ul>

<div class="tabs-content" data-tabs-content="deeplinked-tabs">
    <div class="tabs-panel is-active" id="panel1d">
        <div class="grid-x grid-margin-x">
            <div class="large-4 medium-4 small-4 cell">            
        <img src="<?php echo $strSiteURL ?>/img/cursuri/<?php echo $row["course_picture"]?>"
            alt="<?php echo $row["course_name"]?>" style="width:100%">
            </div>
            <div class="large-8 medium-8 small-8 cell">
        <h3><?php echo $strCourseDescription ?></h3>
        <p><?php echo $row["course_description"]?></p>
        <h3><?php echo $strObjectives ?></h3>
        <p><?php echo $row["course_objective"]?></p>
        <?php }?>
    </div>
    </div>
    </div>
    <div class="tabs-panel" id="panel2d">
        <?php
	//elearning_lessons
echo "<h3>$strLessons</h3>";
$stmt_lessons = mysqli_prepare($conn, "SELECT * FROM elearning_lessons where lesson_course=? ORDER BY lesson_level ASC");
mysqli_stmt_bind_param($stmt_lessons, "i", $row['Course_id']);
mysqli_stmt_execute($stmt_lessons);
$result2 = mysqli_stmt_get_result($stmt_lessons);
$numar2=mysqli_num_rows($result2);
if ($numar2==0)
{
echo $strNoRecordsFound;
}
else {
		echo		"<table width=\"100%\">
	      <thead>
    	<tr>
        	<th>$strID</th>
			<th>$strTitle</th>
			<th>$strView</th>
        </tr>
		</thead>
<tbody>";
While ($row2=ezpub_fetch_array($result2)){
    		echo"
			<tr>
			<td>$row2[lesson_level]</td>
			<td>$row2[lesson_title]</td>
			<td><a href=\"student_sitelessons.php?lID=" . (int)$row2['lesson_ID'] . "\"><i class=\"fa-xl fa fa-eye\" title=\"$strView\"></i></a></td>
        </tr>";

	}//ends elearning_lessons while
	echo "</tbody><tfoot><tr><td></td><td><em></em></td><td>&nbsp;</td></tr></tfoot></table>";

}//ends elearning_lessons else
//
//tests
	echo "<h3>$strTests</h3>";
$stmt_tests = mysqli_prepare($conn, "SELECT * FROM elearning_tests where test_course=?");
mysqli_stmt_bind_param($stmt_tests, "i", $row['Course_id']);
mysqli_stmt_execute($stmt_tests);
$result3 = mysqli_stmt_get_result($stmt_tests);
$numar3=mysqli_num_rows($result3);
if ($numar3==0)
{
echo $strNoRecordsFound;
}
else {
?>
        <?php 
echo"
			<table width=\"100%\">
	      <thead>
    	<tr>
        	<th width=\"10%\">$strID</th>
			<th width=\"70%\">$strTitle</th>
			<th width=\"10%\">$strView</th>
        </tr>
		</thead>
<tbody>";
While ($row3=ezpub_fetch_array($result3)){
    		echo "<tr>
			<td>$row3[test_ID]</td>
			<td>$row3[test_name]</td>
			  <td><a href=\"testwarning.php?tID=" . (int)$row3['test_ID'] . "&cID=" . (int)$cID . "\"><i class=\"fa-xl fa fa-eye\" title=\"$strView\"></i></a></td>
        </tr>";
}//ends tests while	
echo "</tbody><tfoot><tr><td></td><td><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}//ends test else
?>
    </div>
    <div class="tabs-panel" id="panel3d">
        <h3><?php echo $strQuestions?></h3>
        <?php
 $stmt_questions = mysqli_prepare($conn, "SELECT elearning_student_questions.qID, elearning_student_questions.course_id, elearning_student_questions.student_question, elearning_student_questions.trainer_answer, elearning_student_questions.lesson_id, elearning_lessons.lesson_title, elearning_lessons.lesson_ID FROM elearning_student_questions, elearning_lessons WHERE elearning_student_questions.lesson_id=elearning_lessons.lesson_ID AND elearning_student_questions.course_id=? ORDER BY elearning_lessons.lesson_ID ASC");
mysqli_stmt_bind_param($stmt_questions, "i", $row['Course_id']);
mysqli_stmt_execute($stmt_questions);
$result4 = mysqli_stmt_get_result($stmt_questions);
$numar4=mysqli_num_rows($result4);
if ($numar4==0)
{
echo "<div class=\"callout alert\">$strNoRecordsFound</div>";
}
else { 
While ($row4=ezpub_fetch_array($result4)){
	echo "<div class=\"callout\">
	<h4>$row4[lesson_title]</h4>
	<h4>$row4[student_question]</h4>
<div class=\"callout primary\">
	$row4[trainer_answer]
</div>
	</div>";
}
}
  ?>
    </div>
    <div class="tabs-panel" id="panel4d">
        <h3><?php echo $strFiles?></h3>
        <?php
        // Selectăm toate lecțiile cursului pentru a afișa fișierele lor
        $stmt_files = mysqli_prepare($conn, "SELECT lesson_ID, lesson_title, lesson_files FROM elearning_lessons WHERE lesson_course=? AND lesson_files IS NOT NULL AND lesson_files != '' ORDER BY lesson_level ASC");
        mysqli_stmt_bind_param($stmt_files, "i", $row['Course_id']);
        mysqli_stmt_execute($stmt_files);
        $result_files = mysqli_stmt_get_result($stmt_files);
        $numar_files = mysqli_num_rows($result_files);
        
        if ($numar_files == 0) {
            echo "<div class=\"callout warning\">Nu există fișiere atașate pentru lecțiile acestui curs.</div>";
        } else {
            echo "<table width=\"100%\">
                <thead>
                    <tr>
                        <th width=\"30%\">" . htmlspecialchars($strLesson ?? 'Lecție', ENT_QUOTES, 'UTF-8') . "</th>
                        <th width=\"50%\">" . htmlspecialchars($strFileName ?? 'Fișier', ENT_QUOTES, 'UTF-8') . "</th>
                        <th width=\"20%\">" . htmlspecialchars($strDownload ?? 'Descarcă', ENT_QUOTES, 'UTF-8') . "</th>
                    </tr>
                </thead>
                <tbody>";
            
            while ($row_file = mysqli_fetch_array($result_files, MYSQLI_ASSOC)) {
                $lesson_id = (int)$row_file['lesson_ID'];
                $lesson_title = htmlspecialchars($row_file['lesson_title'], ENT_QUOTES, 'UTF-8');
                $lesson_files = $row_file['lesson_files'];
                
                if (!empty($lesson_files)) {
                        $folder = $lesson_id;
                    $filesArray = explode(";", $lesson_files);
                    
                    foreach ($filesArray as $file) {
                        if (!empty($file)) {
                            $file_safe = htmlspecialchars($file, ENT_QUOTES, 'UTF-8');
                                $folder_safe = htmlspecialchars($folder, ENT_QUOTES, 'UTF-8');
                            $icon_class = getFileIcon($file);
                            
                            echo "<tr>
                                <td>$lesson_title</td>
                                <td><i class=\"$icon_class\"></i> $file_safe</td>
                                    <td><a href=\"../common/opendoc.php?type=5&folder=$folder_safe&docID=$file_safe\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"button small\">
                                    <i class=\"fas fa-download\"></i>
                                </a></td>
                            </tr>";
                        }
                    }
                }
            }
            
            echo "</tbody>
                <tfoot>
                    <tr><td colspan=\"3\">&nbsp;</td></tr>
                </tfoot>
            </table>";
        }
        mysqli_stmt_close($stmt_files);
        ?>
    </div>
</div>

</div>
</div>
<?php
include '../bottom.php';
?>