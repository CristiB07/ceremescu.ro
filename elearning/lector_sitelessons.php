<?php
//updated 17.07.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare lecții";
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
<link rel="stylesheet" href="../js/simple-editor/simple-editor.css">
<script src="../js/simple-editor/simple-editor.js"></script>
<div class="grid-x grid-padding-x">
    <div class="large-12 medium-12 small-12 cell">

        <?php

echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM elearning_lessons WHERE lesson_ID=" .$_GET['lID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"lector_sitelessons.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">
</div>";
include '../bottom.php';
die;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mode = isset($_GET['mode']) ? $_GET['mode'] : '';
    $lesson_title = isset($_POST['lesson_title']) ? trim($_POST['lesson_title']) : '';
    $lesson_level = isset($_POST['lesson_level']) ? (int)$_POST['lesson_level'] : 0;
    $lesson_course = isset($_POST['lesson_course']) ? (int)$_POST['lesson_course'] : 0;
    $lesson_body = isset($_POST['lesson_body']) ? $_POST['lesson_body'] : '';
    if ($mode == "new") {
        $stmt = mysqli_prepare($conn, "INSERT INTO elearning_lessons (lesson_title, lesson_trainer, lesson_level, lesson_course, lesson_body) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "siiss", $lesson_title, $uid, $lesson_level, $lesson_course, $lesson_body);
        if (!mysqli_stmt_execute($stmt)) {
             echo "<div class=\"callout success\">".htmlspecialchars($strThereWasAnError)."</div></div></div>";
        echo "<script type=\"text/javascript\">
        <!--
        function delayer(){
            window.location = \"lector_sitelessons.php\"
        }
        //-->
        </script>
        <body onLoad=\"setTimeout('delayer()', 1500)\">";
        include '../bottom.php';
        die;
        }
        else {
        echo "<div class=\"callout success\">".htmlspecialchars($strRecordAdded)."</div></div></div>";
        echo "<script type=\"text/javascript\">
        <!--
        function delayer(){
            window.location = \"lector_sitelessons.php\"
        }
        //-->
        </script>
        <body onLoad=\"setTimeout('delayer()', 1500)\">";
        include '../bottom.php';
        die;
    } 
    } 
    elseif ($mode == "edit") {
        $lID = isset($_GET['lID']) ? (int)$_GET['lID'] : 0;
        if ($lID > 0 && $lesson_course > 0 && $lesson_level >= 0) {
            $stmt = mysqli_prepare($conn, "UPDATE elearning_lessons SET lesson_title=?, lesson_trainer=?, lesson_level=?, lesson_course=?, lesson_body=? WHERE lesson_ID=? AND lesson_trainer=?");
            mysqli_stmt_bind_param($stmt, "siissii", $lesson_title, $uid, $lesson_level, $lesson_course, $lesson_body, $lID, $uid);
            if (!mysqli_stmt_execute($stmt)) {
                echo "<div class=\"callout alert\">".htmlspecialchars($strThereWasAnError)."</div></div></div>";
        echo "<script type=\"text/javascript\">
        <!--
        function delayer(){
            window.location = \"lector_sitelessons.php\"
        }
        //-->
        </script>
        <body onLoad=\"setTimeout('delayer()', 1500)\">";
        include '../bottom.php';
        die;
            } else {
            echo "<div class=\"callout success\">".htmlspecialchars($strRecordModified)."</div></div></div>";
        
        echo "<script type=\"text/javascript\">
        <!--
        function delayer(){
            window.location = \"lector_sitelessons.php\"
        }
        //-->
        </script>
        <body onLoad=\"setTimeout('delayer()', 1500)\">";
        include '../bottom.php';
        die;
    }
}
}
}
else {
if (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
        <form method="post" action="lector_sitelessons.php?mode=new">
            <div class="grid-x grid-padding-x">
                <div class="large-5 medium-5 small-5 cell">
                    <label><?php echo $strCourse?>
                        <select name="lesson_course" class="required">
                            <option value="0"><?php echo $strPick?></option>
                            <?php $sql = "Select Course_ID, course_name FROM elearning_courses WHERE course_author=$uid ORDER BY course_name ASC";
        $result = ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
                            <option value="<?php echo htmlspecialchars($rss["Course_ID"])?>"><?php echo htmlspecialchars($rss["course_name"]) ?></option>
                            <?php
}?>
                        </select></label>
                </div>
                <div class="large-5 medium-5 small-5 cell">
                    <label><?php echo $strTitle?>
                        <input name="lesson_title" type="text" size="30" class="required" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strLevel?>
                        <input name="lesson_level" type="text" size="3" class="required number" />
                    </label>
                </div>
            </div>

            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strBody?>
                        <textarea name="lesson_body" id="3" style="width:100%" class="simple-html-editor" data-upload-dir="elearning"></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" value="<?php echo $strAdd?>" class="button success">
        </form>
    </div>
</div>
<?php
}
elseif (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM elearning_lessons WHERE lesson_ID=$_GET[lID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
<form method="post" action="lector_sitelessons.php?mode=edit&lID=<?php echo $row['lesson_ID']?>">
    <div class="grid-x grid-padding-x">
        <div class="large-5 medium-5 small-5 cell">
            <label><?php echo $strCourse?>
                <select name="lesson_course" class="required">
                    <option value="0"><?php echo $strMaster?></option>
                    <?php $sql = "Select Course_ID, course_name FROM elearning_courses WHERE course_author=$uid ORDER BY course_name ASC";
        $result = ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
		if ($row['lesson_course']==$rss['Course_ID']) {
	?>
                    <option selected value="<?php echo htmlspecialchars($rss["Course_ID"])?>"><?php echo htmlspecialchars($rss["course_name"]) ?></option>
                    <?php } else { ?>
                    <option value="<?php echo htmlspecialchars($rss["Course_ID"])?>"><?php echo htmlspecialchars($rss["course_name"]) ?></option>
                    <?php
}}?>
                </select></label>
        </div>
        <div class="large-5 medium-5 small-5 cell">
            <label><?php echo $strTitle?>
                <input name="lesson_title" type="text" size="30" class="required"
                    value="<?php echo htmlspecialchars($row['lesson_title'])?>" />
            </label>
        </div>
        <div class="large-2 medium-2 small-2 cell">
            <label><?php echo $strLevel?>
                <input name="lesson_level" type="text" size="3" class="required number"
                    value="<?php echo htmlspecialchars($row['lesson_level'])?>" />
            </label>
        </div>
    </div>
    <div class="grid-x grid-padding-x">
        <div class="large-12 medium-12 small-12 cell">
            <label><?php echo $strBody?>
                <textarea name="lesson_body" id="3" class="simple-html-editor"
                    style="width: 100%"><?php echo htmlspecialchars($row["lesson_body"]) ?></textarea>
            </label>
        </div>
    </div>

    <div class="grid-x grid-padding-x">
        <div class="large-12 medium-12 small-12 cell text center">
            <input type="submit" value="<?php echo $strModify?>" class="button success" />
</form>
</div>
</div>
</form>
<?php
}
else
{
echo "<a href=\"lector_sitelessons.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"fas fa-plus fa-xl\"></i></a><br />";
$query="SELECT lesson_ID, lesson_title, lesson_course, Course_ID, course_name FROM elearning_lessons, elearning_courses WHERE elearning_lessons.lesson_trainer=$uid AND elearning_courses.course_ID=elearning_lessons.lesson_course";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
if ($numar==0)
{
echo "<div class=\"callout alert\">$strNoRecordsFound</div>";
}
else {
?>
<table width="100%">
    <thead>
        <tr>
            <th><?php echo $strID?></th>
            <th><?php echo $strCourse?></th>
            <th><?php echo $strTitle?></th>
            <th><?php echo $strEdit?></th>
            <th><?php echo $strDelete?></th>
        </tr>
    </thead>
    <tbody>
        <?php 
While ($row=ezpub_fetch_array($result)){
    echo "<tr>";
    echo "<td>".htmlspecialchars($row['lesson_ID'])."</td>";
    echo "<td>".htmlspecialchars($row['course_name'])."</td>";
    echo "<td>".htmlspecialchars($row['lesson_title'])."</td>";
    echo "<td><a href=\"lector_sitelessons.php?mode=edit&lID=".urlencode($row['lesson_ID'])."\"><i class=\"far fa-edit fa-xl\" title=\"".htmlspecialchars($strEdit)."\"></i></a></td>";
    echo "<td><a href=\"lector_sitelessons.php?mode=delete&lID=".urlencode($row['lesson_ID'])."\"  OnClick=\"return confirm('".htmlspecialchars($strConfirmDelete)."');\"><i class=\"fa fa-eraser fa-xl\" title=\"".htmlspecialchars($strDelete)."\"></i></a></td>";
    echo "</tr>";
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