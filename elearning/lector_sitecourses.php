<?php
//updated 17.07.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare cursuri";
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
?>
<div class="grid-x grid-padding-x">
    <div class="large-12 medium-12 small-12 cell">

        <?php
// Determină ce query să folosească pentru cursuri
if (isset($_GET['cID']) && is_numeric($_GET['cID'])) {
	$cID = (int)$_GET['cID'];
	$stmt = mysqli_prepare($conn, "SELECT * FROM elearning_courses WHERE course_author=? AND Course_ID=?");
	mysqli_stmt_bind_param($stmt, "ii", $uid, $cID);
} else {
	$stmt = mysqli_prepare($conn, "SELECT * FROM elearning_courses WHERE course_author=?");
	mysqli_stmt_bind_param($stmt, "i", $uid);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$numar = mysqli_num_rows($result);

if ($numar == 0) {
	echo "<div class=\"callout alert\">$strNoRecordsFound</div>";
} else {
	echo "<h3>$strMyCourses</h3>";
	while ($row = ezpub_fetch_array($result)) {
		$description = htmlspecialchars($row["course_description"], ENT_QUOTES, 'UTF-8');
		echo "<div class=\"callout\"><h4>" . htmlspecialchars($row['course_name'], ENT_QUOTES, 'UTF-8') . "</h4>$description";

		// Lecții
		echo "<h3>$strLessons</h3>";
		echo "<a href=\"lector_sitelessons.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a><br />";
		$stmt2 = mysqli_prepare($conn, "SELECT * FROM elearning_lessons WHERE lesson_course=?");
		mysqli_stmt_bind_param($stmt2, "i", $row['Course_id']);
		mysqli_stmt_execute($stmt2);
		$result2 = mysqli_stmt_get_result($stmt2);
		$numar2 = mysqli_num_rows($result2);
		if ($numar2 == 0) {
			echo "<div class=\"callout alert\">$strNoRecordsFound</div>";
		} else {
			echo "<table width=\"100%\"><thead><tr><th>$strID</th><th>$strTitle</th><th>$strEdit</th><th>$strDelete</th></tr></thead><tbody>";
			while ($row2 = ezpub_fetch_array($result2)) {
				echo "<tr><td>" . (int)$row2['lesson_ID'] . "</td><td>" . htmlspecialchars($row2['lesson_title'], ENT_QUOTES, 'UTF-8') . "</td><td><a href=\"lector_sitelessons.php?mode=edit&lID=" . (int)$row2['lesson_ID'] . "\"><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td><td><a href=\"lector_sitelessons.php?mode=delete&lID=" . (int)$row2['lesson_ID'] . "\" OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td></tr>";
			}
			echo "</tbody><tfoot><tr><td></td><td  colspan=\"2\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
		}

		// Teste
		echo "<h3>$strTests</h3>";
		$stmt3 = mysqli_prepare($conn, "SELECT * FROM elearning_tests WHERE test_course=?");
		mysqli_stmt_bind_param($stmt3, "i", $row['Course_id']);
		mysqli_stmt_execute($stmt3);
		$result3 = mysqli_stmt_get_result($stmt3);
		$numar3 = mysqli_num_rows($result3);
		if ($numar3 == 0) {
			echo "<a href=\"lector_sitetests.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a><br />";
			echo "<div class=\"callout alert\">$strNoRecordsFound</div>";
		} else {
			while ($row3 = ezpub_fetch_array($result3)) {
				echo "<table width=\"100%\"><thead><tr><th>$strID</th><th>$strTitle</th><th>$strEdit</th><th>$strDelete</th></tr></thead><tbody><tr>";
				echo "<td>" . (int)$row3['test_ID'] . "</td>";
				echo "<td>" . htmlspecialchars($row3['test_description'], ENT_QUOTES, 'UTF-8') . "</td>";
				echo "<td><a href=\"lector_sitetests.php?mode=edit&tID=" . (int)$row3['test_ID'] . "\"><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>";
				echo "<td><a href=\"lector_sitetests.php?mode=delete&tID=" . (int)$row3['test_ID'] . "\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>";
				echo "</tr></tbody><tfoot><tr><td></td><td  colspan=\"2\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
			}
		}
		echo "</div>";
	}
}
?>


echo "</div>";
?>
    </div>
</div>
<?php
include '../bottom.php';
?>