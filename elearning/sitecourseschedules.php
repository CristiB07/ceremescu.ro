<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare programări";
include '../dashboard/header.php';
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/elearning/login.php?message=MLF");
}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

// Sanitize input
$cID = (int)$_GET['cID'];
if ($cID <= 0) {
    header("location:sitecourseschedules.php?message=ER");
    die;
}

// Prepared statement pentru DELETE
$stmt = mysqli_prepare($conn, "DELETE FROM elearning_courseschedules WHERE schedule_ID=?");
mysqli_stmt_bind_param($stmt, "i", $cID);
mysqli_stmt_execute($stmt);
echo "<div class=\"succes callout\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecourseschedules.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();

If ($_GET['mode']=="new"){
//insert new user

// Prepared statement pentru INSERT
$stmt = mysqli_prepare($conn, "INSERT INTO elearning_courseschedules(
    schedule_course_ID, schedule_start_date, schedule_end_date, 
    schedule_enrolments, schedule_max_enrolments, schedule_start_hour, 
    schedule_exam_date, schedule_exam_hour, schedule_details
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

$schedule_course_ID = (int)$_POST["schedule_course_ID"];
$schedule_start_date = mysqli_real_escape_string($conn, $_POST["schedule_start_date"]);
$schedule_end_date = mysqli_real_escape_string($conn, $_POST["schedule_end_date"]);
$schedule_enrolments = (int)$_POST["schedule_enrolments"];
$schedule_max_enrolments = (int)$_POST["schedule_max_enrolments"];
$schedule_start_hour = mysqli_real_escape_string($conn, $_POST["schedule_start_hour"]);
$schedule_exam_date = mysqli_real_escape_string($conn, $_POST["schedule_exam_date"]);
$schedule_exam_hour = mysqli_real_escape_string($conn, $_POST["schedule_exam_hour"]);
$schedule_details = mysqli_real_escape_string($conn, $_POST["schedule_details"]);

mysqli_stmt_bind_param($stmt, "issiiisss", 
    $schedule_course_ID, $schedule_start_date, $schedule_end_date,
    $schedule_enrolments, $schedule_max_enrolments, $schedule_start_hour,
    $schedule_exam_date, $schedule_exam_hour, $schedule_details
);
				
//It executes the SQL
if (!mysqli_stmt_execute($stmt))
  {
  die('Error: ' . mysqli_error($conn));
  }
else{
echo "<div class=\"callout success\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecourseschedules.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}}
else
{// edit
// Sanitize cID
$cID = (int)$_GET['cID'];
if ($cID <= 0) {
    header("location:sitecourseschedules.php?message=ER");
    die;
}

// Prepared statement pentru UPDATE
$stmt = mysqli_prepare($conn, "UPDATE elearning_courseschedules SET 
    schedule_course_ID=?, schedule_start_date=?, schedule_end_date=?, 
    schedule_enrolments=?, schedule_max_enrolments=?, schedule_start_hour=?, 
    schedule_exam_date=?, schedule_exam_hour=?, schedule_details=? 
    WHERE schedule_ID=?");

$schedule_course_ID = (int)$_POST["schedule_course_ID"];
$schedule_start_date = mysqli_real_escape_string($conn, $_POST["schedule_start_date"]);
$schedule_end_date = mysqli_real_escape_string($conn, $_POST["schedule_end_date"]);
$schedule_enrolments = (int)$_POST["schedule_enrolments"];
$schedule_max_enrolments = (int)$_POST["schedule_max_enrolments"];
$schedule_start_hour = mysqli_real_escape_string($conn, $_POST["schedule_start_hour"]);
$schedule_exam_date = mysqli_real_escape_string($conn, $_POST["schedule_exam_date"]);
$schedule_exam_hour = mysqli_real_escape_string($conn, $_POST["schedule_exam_hour"]);
$schedule_details = mysqli_real_escape_string($conn, $_POST["schedule_details"]);

mysqli_stmt_bind_param($stmt, "issiiisssi", 
    $schedule_course_ID, $schedule_start_date, $schedule_end_date,
    $schedule_enrolments, $schedule_max_enrolments, $schedule_start_hour,
    $schedule_exam_date, $schedule_exam_hour, $schedule_details, $cID
);

if (!mysqli_stmt_execute($stmt))
  {
  die('Error: ' . mysqli_error($conn));
  }
else{
echo "<div class=\"callout success\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecourseschedules.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
}
}
else {
?>
          <script src='../js/simple-editor/simple-editor.js'></script>
    <link rel="stylesheet" href='../js/simple-editor/simple-editor.css'>
        <?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
	echo "<a href=\"sitecourseschedules.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>";
	?>
        <form method="post"  action="sitecourseschedules.php?mode=new">
            <div class="grid-x grid-padding-x">
                <div class="large-5 medium-3 small-5 cell">
                    <label><?php echo $strCourse ."<br />&nbsp;" ?></label>
                    <div class="large-4 medium-4 small-4 cell">
                        <label><?php echo $strCourse?></label>
                        >>>>>>> 0d7c282a4f899deaded420111dbd467d73ea8dc1
                        <select size="1" name="schedule_course_ID" class="required">
                            <option value=""><?php echo $strPick?></option>
                            <?php $sql = "Select * FROM elearning_courses ORDER BY course_name ASC";
        $result = ezpub_query($conn,$sql);
	    While ($rs1=ezpub_fetch_array($result)){
	?>
                            <option value="<?php echo $rs1["Course_id"]?>"><?php echo $rs1["course_name"]?></option>
                            <?php
}?>
                        </select>
                    </div>
                  <div class="large-1 medium-1 small-1 cell">
                        <label><?php echo $strStartDate."<br />&nbsp;"?></label>
                        <input name="schedule_start_date" type="date" class="required" />
            </div>
            <div class="large-1 medium-1 small-1 cell">
                <label><?php echo $strEndDate."<br />&nbsp;"?></label>
                <input name="schedule_end_date" type="date" class="required" />
            </div>
            <div class="large-1 medium-1 small-1 cell">
                <label><?php echo $strEnrolments?></label></TD>
                <input name="schedule_enrolments" type="text" class="required" />
            </div>
            <div class="large-1 medium-1 small-1 cell">
                <label><?php echo $strMaxEnrolments?></label></TD>
                <input name="schedule_max_enrolments" type="text" class="required" />
            </div>
            <div class="large-1 medium-1 small-1 cell">
                <label><?php echo $strStartHour."<br />&nbsp;"?></label>
                <input name="schedule_start_hour" type="text" class="required" />
            </div>
            <div class="large-1 medium-1 small-1 cell">
                <label><?php echo $strExamDate?></label>
                <input name="schedule_exam_date" type="date" class="required" />
            </div>
            <div class="large-1 medium-1 small-1 cell">
                =======
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strStartDate?></label>
                    <input name="schedule_start_date" type="date" class="required" />
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strEndDate?></label>
                    <input name="schedule_end_date" type="date" class="required" />
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strEnrolments?></label></TD>
                    <input name="schedule_enrolments" type="text" class="required" />
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strMaxEnrolments?></label></TD>
                    <input name="schedule_max_enrolments" type="text" class="required" />
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strStartHour?></label>
                    <input name="schedule_start_hour" type="text" class="required" />
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strExamDate?></label>
                    <input name="schedule_exam_date" type="date" class="required" />
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strExamHour?></label></TD>
                    <input name="schedule_exam_hour" type="text" class="required" />
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strDetails?></label>
                    <textarea name="schedule_details" class="simple-html-editor" data-upload-dir="elearning" style="width:100%;"></textarea>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" value="<?php echo $strAdd?>" name="Submit" class="button success">
                </div>
            </div>
        </form>
        <?php
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
	echo "<a href=\"sitecourseschedules.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>";

// Sanitize cID
$cID = (int)$_GET['cID'];
if ($cID <= 0) {
    header("location:sitecourseschedules.php?message=ER");
    die;
}

// Prepared statement pentru SELECT
$stmt = mysqli_prepare($conn, "SELECT * FROM elearning_courseschedules WHERE schedule_ID=?");
mysqli_stmt_bind_param($stmt, "i", $cID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row=ezpub_fetch_array($result);
?>
        <form method="post"  action="sitecourseschedules.php?mode=edit&cID=<?php echo (int)$row['schedule_ID']?>">
            <div class="grid-x grid-padding-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strCourse?></label>
                        <select size="1" name="schedule_course_ID" class="required">
                            <option value=""><?php echo $strPick?></option>
                            <?php $sql = "Select * FROM elearning_courses ORDER BY course_name ASC";
        $result = ezpub_query($conn,$sql);
	    While ($rs1=ezpub_fetch_array($result)){
	?>
                            <option value="<?php echo $rs1["Course_id"]?>"
                                <?php if ($rs1["Course_id"]==$row["schedule_course_ID"]) echo "selected"?>>
                                <?php echo $rs1["course_name"]?></option>
                            <?php
}?>
                        </select>
                    </div>
                    <<<<<<< HEAD <div class="large-1 medium-1 small-1 cell">
                        <label><?php echo $strStartDate."<br />&nbsp;"?></label>
                        <input name="schedule_start_date" type="date" value="<?php echo $row["schedule_start_date"]?>"
                            class="required" />
            </div>
            <div class="large-1 medium-1 small-1 cell">
                <label><?php echo $strEndDate."<br />&nbsp;"?></label>
                <input name="schedule_end_date" type="date" value="<?php echo $row["schedule_end_date"]?>"
                    class="required" />
            </div>
            <div class="large-1 medium-1 small-1 cell">
                <label><?php echo $strEnrolments?></label>
                <input name="schedule_enrolments" type="text" value="<?php echo $row["schedule_enrolments"]?>"
                    class="required" />
            </div>
            <div class="large-1 medium-1 small-1 cell">
                <label><?php echo $strMaxEnrolments?></label></TD>
                <input name="schedule_max_enrolments" type="text" value="<?php echo $row["schedule_max_enrolments"]?>"
                    class="required" />
            </div>
            <div class="large-1 medium-1 small-1 cell">
                <label><?php echo $strStartHour."<br />&nbsp;"?></label>
                <input name="schedule_start_hour" type="text" value="<?php echo $row["schedule_start_hour"]?>"
                    class="required" />
            </div>
            <div class="large-1 medium-1 small-1 cell">
                <label><?php echo $strExamDate?></label>
                <input name="schedule_exam_date" type="date" value="<?php echo $row["schedule_exam_date"]?>"
                    class="required" />
            </div>
            <div class="large-1 medium-1 small-1 cell">
                =======
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strStartDate?></label>
                    <input name="schedule_start_date" type="date" value="<?php echo $row["schedule_start_date"]?>"
                        class="required" />
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strEndDate?></label>
                    <input name="schedule_end_date" type="date" value="<?php echo $row["schedule_end_date"]?>"
                        class="required" />
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strEnrolments?></label>
                    <input name="schedule_enrolments" type="text" value="<?php echo $row["schedule_enrolments"]?>"
                        class="required" />
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strMaxEnrolments?></label></TD>
                    <input name="schedule_max_enrolments" type="text"
                        value="<?php echo $row["schedule_max_enrolments"]?>" class="required" />
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strStartHour?></label>
                    <input name="schedule_start_hour" type="text" value="<?php echo $row["schedule_start_hour"]?>"
                        class="required" />
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strExamDate?></label>
                    <input name="schedule_exam_date" type="date" value="<?php echo $row["schedule_exam_date"]?>"
                        class="required" />
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strExamHour?></label></TD>
                    <input name="schedule_exam_hour" type="text" value="<?php echo $row["schedule_exam_hour"]?>"
                        class="required" />
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strDetails?></label>
                    <textarea name="schedule_details" class="simple-html-editor"
                        data-upload-dir="elearning" style="width:100%;"><?php echo $row["schedule_details"]?></textarea>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" value="<?php echo $strModify?>" name="Submit" class="button success">
                </div>
            </div>
        </form>
        <?php
}
else
{
echo "<a href=\"sitecourseschedules.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a><br /><br />";
 $query="SELECT course_name, course_author, schedule_ID, schedule_start_date, schedule_end_date FROM elearning_courseschedules, elearning_courses 
 WHERE schedule_course_ID=elearning_courses.Course_ID AND schedule_end_date >= '$sdata'";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
if ($numar==0)
{
echo $strNoRecordsFound;
}
else {
?>
        <table width="100%">
            <thead>
                <tr>
                    <th><?php echo $strID?></th>
                    <th><?php echo $strTitle?></th>
                    <th><?php echo $strStartDate?></th>
                    <th><?php echo $strEdit?></th>
                    <th><?php echo $strDelete?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=ezpub_fetch_array($result)){
    $schedule_id_safe = (int)$row['schedule_ID'];
    $course_name_safe = htmlspecialchars($row['course_name'], ENT_QUOTES, 'UTF-8');
    $start_date_safe = htmlspecialchars($row['schedule_start_date'], ENT_QUOTES, 'UTF-8');
    		echo"<tr>
			<td>$schedule_id_safe</td>
			<td>$course_name_safe</td>
			<td>$start_date_safe</td>
			  <td><a href=\"sitecourseschedules.php?mode=edit&cID=$schedule_id_safe\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"sitecourseschedules.php?mode=delete&cID=$schedule_id_safe\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"3\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
}
?>
    </div>
</div>
<?php
include '../bottom.php';
?>