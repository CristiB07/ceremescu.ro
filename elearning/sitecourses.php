<?php
//update 9.01.2024
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare categorii";
include '../dashboard/header.php';
$strPageTitle="Administrare cursuri";
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/elearning/login.php?message=MLF");
}
?>
    <script src='../js/simple-editor/simple-editor.js'></script>
    <link rel="stylesheet" href='../js/simple-editor/simple-editor.css'>
<div class="grid-x grid-padding-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

// Sanitize input
$cID = (int)$_GET['cID'];
if ($cID <= 0) {
    header("location:sitecourses.php?message=ER");
    die;
}

// Prepared statement pentru DELETE
$stmt = mysqli_prepare($conn, "DELETE FROM elearning_courses WHERE Course_id=?");
mysqli_stmt_bind_param($stmt, "i", $cID);
mysqli_stmt_execute($stmt);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecourses.php\"
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
$stmt = mysqli_prepare($conn, "INSERT INTO elearning_courses(
    course_name, course_author, course_code, course_price, course_discount,
    course_description, course_type, course_objective, course_target, course_picture,
    course_keywords, course_metadescription, course_category, course_whatyouget, course_url
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$course_name = mysqli_real_escape_string($conn, $_POST["course_name"]);
$course_author = mysqli_real_escape_string($conn, $_POST["course_author"]);
$course_code = mysqli_real_escape_string($conn, $_POST["course_code"]);
$course_price = mysqli_real_escape_string($conn, $_POST["course_price"]);
$course_discount = mysqli_real_escape_string($conn, $_POST["course_discount"]);
$course_description = mysqli_real_escape_string($conn, $_POST["course_description"]);
$course_type = mysqli_real_escape_string($conn, $_POST["course_type"]);
$course_objective = mysqli_real_escape_string($conn, $_POST["course_objective"]);
$course_target = mysqli_real_escape_string($conn, $_POST["course_target"]);
$course_picture = mysqli_real_escape_string($conn, $_POST["course_picture"]);
$course_keywords = mysqli_real_escape_string($conn, $_POST["course_keywords"]);
$course_metadescription = mysqli_real_escape_string($conn, $_POST["course_metadescription"]);
$course_category = mysqli_real_escape_string($conn, $_POST["course_category"]);
$course_whatyouget = mysqli_real_escape_string($conn, $_POST["course_whatyouget"]);
$course_url = mysqli_real_escape_string($conn, $_POST["course_url"]);

mysqli_stmt_bind_param($stmt, "sssssssssssssss", 
    $course_name, $course_author, $course_code, $course_price, $course_discount,
    $course_description, $course_type, $course_objective, $course_target, $course_picture,
    $course_keywords, $course_metadescription, $course_category, $course_whatyouget, $course_url
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
    window.location = \"sitecourses.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}}
else
{// edit
$strWhereClause = " WHERE elearning_courses.Course_id=" . $_GET["cID"] . ";";
$query= "UPDATE elearning_courses SET elearning_courses.course_name='" .str_replace("'","&#39;",$_POST["course_name"]) . "' ," ;
$query= $query . " elearning_courses.course_price='" .str_replace("'","&#39;",$_POST["course_price"]) . "', "; 
$query= $query . " elearning_courses.course_discount='" .str_replace("'","&#39;",$_POST["course_discount"]) . "', "; 
$query= $query . " elearning_courses.course_picture='" .str_replace("'","&#39;",$_POST["course_picture"]) . "', "; 
$query= $query . " elearning_courses.course_type='" .str_replace("'","&#39;",$_POST["course_type"]) . "', "; 
$query= $query . " elearning_courses.course_description='" .str_replace("'","&#39;",$_POST["course_description"]) . "', "; 
$query= $query . " elearning_courses.course_objective='" .str_replace("'","&#39;",$_POST["course_objective"]) . "', "; 
$query= $query . " elearning_courses.course_category='" .str_replace("'","&#39;",$_POST["course_category"]) . "', "; 
$query= $query . " elearning_courses.course_code='" .str_replace("'","&#39;",$_POST["course_code"]) . "', "; 
$query= $query . " elearning_courses.course_author='" .str_replace("'","&#39;",$_POST["course_author"]) . "', "; 
$query= $query . " elearning_courses.course_target='" .str_replace("'","&#39;",$_POST["course_target"]) . "', "; 
$query= $query . " elearning_courses.course_url='" .str_replace("'","&#39;",$_POST["course_url"]) . "', "; 
$query= $query . " elearning_courses.course_whatyouget='" .str_replace("'","&#39;",$_POST["course_whatyouget"]) . "', "; 
$query= $query . " elearning_courses.course_metadescription='" .str_replace("'","&#39;",$_POST["course_metadescription"]) . "', "; 
$query= $query . " elearning_courses.course_keywords='" .str_replace("'","&#39;",$_POST["course_keywords"]) . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  die('Error: ' . ezpub_error($conn));
  }
else{
echo "<div class=\"callout success\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecourses.php\"
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
        <script language="JavaScript" type="text/JavaScript">
            $(document).ready(function() {
	$("#users").validate();
});
</script>
        <?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
        <form method="post"  action="sitecourses.php?mode=new">
            <div class="grid-x grid-padding-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strTitle?></label>
                    <input name="course_name" type="text" class="required" />
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strAuthor?></label></TD>
                    <select size="1" name="course_author" class="required">
                        <option value=""><?php echo $strPick?></option>
                        <?php $sql = "Select * FROM elearning_trainers ORDER BY trainer_name ASC";
         $result = ezpub_query($conn,$sql);
	    While ($rs1=ezpub_fetch_array($result)){
			?>
                        <option value="<?php echo $rs1["trainer_id"]?>"><?php echo $rs1["trainer_name"]?></option>
                        <?php
}?>
                    </select>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strType?></label>
                    <input name="course_type" type="text" class="required" />
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strCategory?></label>
                    <select size="1" name="course_category" class="required">
                        <option value=""><?php echo $strPick?></option>
                        <?php $sql = "Select * FROM elearning_coursecategory ORDER BY elearning_coursecategory_name";
        $result = ezpub_query($conn,$sql);
	    While ($rs1=ezpub_fetch_array($result)){
	?>
                        <option value="<?php echo $rs1["elearning_coursecategory_ID"]?>">
                            <?php echo $rs1["elearning_coursecategory_name"]?></option>
                        <?php
}?>
                    </select>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strMainPicture?></label>
                    <input name="course_picture" id="image" type="text" class="required" value="" readonly="readonly" />
                    <!-- Trigger/Open The Modal -->
                    <div class="full reveal" id="myModal" data-reveal>
                        <!-- Modal content -->
                        <iframe src="../common/image.php?directory=cursuri&field=image" frameborder="0" style="border:0"
                            Width="100%" height="750"></iframe>
                        <button class="close-button" data-close aria-label="Close modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <a href="#" class="button" data-open="myModal"><?php echo $strImage?></a>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strCode?></label>
                    <input name="course_code" type="text" class="required" />
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strPrice?></label>
                    <input name="course_price" type="text" class="required" />
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strDiscountedPrice?></label></TD>
                    <input name="course_discount" type="text" class="required" /></TD>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strCourseDescription?></label><br />
                    <textarea name="course_description" class="simple-html-editor" data-upload-dir="elearning" id="simple-html-editor"></textarea>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strObjectives?></label><br /><textarea name="course_objective"
                        class="simple-html-editor" data-upload-dir="elearning" id="simple-html-editor"></textarea>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strTarget?></label><br /><textarea name="course_target" class="simple-html-editor"
                        data-upload-dir="elearning" id="simple-html-editor"></textarea>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strWhatYouGet?></label><br /><textarea name="course_whatyouget"
                        class="simple-html-editor" data-upload-dir="elearning" id="simple-html-editor"></textarea>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strURL?></label></TD>
                    <input name="course_url" type="text" class="required" />
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strKeyWords?></label></TD>
                    <input name="course_keywords" type="text" class="required" />
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strMetaDescription?></label>
                    <textarea name="course_metadescription" id="simple-html-editor"></textarea>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" value="<?php echo $strAdd?>" name="Submit" class="button success">
                </div>
            </div>
        </FORM>
        <?php
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){

// Sanitize cID
$cID = (int)$_GET['cID'];
if ($cID <= 0) {
    header("location:sitecourses.php?message=ER");
    die;
}

// Prepared statement pentru SELECT
$stmt = mysqli_prepare($conn, "SELECT * FROM elearning_courses WHERE Course_id=?");
mysqli_stmt_bind_param($stmt, "i", $cID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row=ezpub_fetch_array($result);
?>
        <div class="grid-x grid-padding-x">
            <div class="large-3 medium-3 small-3 cell">
                <a href="sitecourses.php" class="button"><?php echo $strBack?>&nbsp;<i
                        class="fas fa-backward fa-xl"></i></a>
            </div>
        </div>
        <form method="post"  action="sitecourses.php?mode=edit&cID=<?php echo (int)$row['Course_id']?>">
            <div class="grid-x grid-padding-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strTitle?></label>
                    <input name="course_name" type="text" size="30" value="<?php echo htmlspecialchars($row['course_name'], ENT_QUOTES, 'UTF-8') ?>"
                        class="required" />
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strAuthor?></label>
                    <select size="1" name="course_author" class="required">
                        <option value=""><?php echo $strPick?></option>
                        <?php $sql = "Select * FROM elearning_trainers ORDER BY trainer_name ASC";
         $result = ezpub_query($conn,$sql);
	    While ($rs1=ezpub_fetch_array($result)){
		if ($row["course_author"]!=$rs1["trainer_id"]) {
	?>
                        <option value="<?php echo $rs1["trainer_id"]?>"><?php echo $rs1["trainer_name"]?></option>
                        <?php } else{?>
                        <option selected value="<?php echo $rs1["trainer_id"]?>"><?php echo $rs1["trainer_name"]?>
                        </option>
                        <?php
}}?>
                    </select>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strType?></label>
                    <input name="course_type" type="text" value="<?php echo $row['course_type'] ?>" class="required" />
                </div>
                <div class="large-3 medium-3 small-3 cell">

                    <label><?php echo $strCategory?></label>
                    <select size="1" name="course_category" class="required">
                        <option value=""><?php echo $strPick?></option>
                        <?php $sql = "Select * FROM elearning_coursecategory ORDER BY elearning_coursecategory_name";
       $result = ezpub_query($conn,$sql);
	    While ($rs1=ezpub_fetch_array($result)){
		if ($row['course_category']==$rs1['elearning_coursecategory_ID']){	
	?>
                        <option selected value="<?php echo $rs1["elearning_coursecategory_ID"]?>">
                            <?php echo $rs1["elearning_coursecategory_name"]?></option>
                        <?php
		  } else {
	?>
                        <option value="<?php echo $rs1["elearning_coursecategory_ID"]?>">
                            <?php echo $rs1["elearning_coursecategory_name"]?></option>
                        <?php
}}?>
                    </select>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strMainPicture?></label>
                    <input name="course_picture" id="image" type="text" class="required"
                        value="<?php echo $row["course_picture"]?>" readonly="readonly" />
                    <!-- Trigger/Open The Modal -->
                    <div class="full reveal" id="myModal" data-reveal>
                        <!-- Modal content -->
                        <iframe src="../common/image.php?directory=cursuri&field=image" frameborder="0" style="border:0"
                            Width="100%" height="750"></iframe>
                        <button class="close-button" data-close aria-label="Close modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <a href="#" class="button" data-open="myModal"><?php echo $strImage?></a>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strCode?></label>
                    <input name="course_code" type="text" value="<?php echo $row['course_code'] ?>" class="required" />
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strPrice?></label></TD>
                    <input name="course_price" type="text" value="<?php echo $row['course_price'] ?>"
                        class="required" />
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strDiscountedPrice?></label>
                    <input name="course_discount" type="text" value="<?php echo $row['course_discount'] ?>"
                        class="required" />
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strCourseDescription?></label><br />
                    <textarea name="course_description" class="simple-html-editor"
                        data-upload-dir="elearning" id="simple-html-editor"><?php echo $row['course_description'] ?></textarea>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strObjectives?></label><br /><textarea name="course_objective"
                        class="simple-html-editor" data-upload-dir="elearning" id="simple-html-editor"><?php echo $row['course_objective'] ?></textarea>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strTarget?></label><br /><textarea name="course_target" class="simple-html-editor"
                        data-upload-dir="elearning" id="simple-html-editor"><?php echo $row['course_target'] ?></textarea>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strWhatYouGet?></label><br /><textarea name="course_whatyouget"
                        class="simple-html-editor" data-upload-dir="elearning" id="simple-html-editor"><?php echo $row['course_whatyouget'] ?></textarea>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strURL?></label></TD>
                    <input name="course_url" type="text" value="<?php echo $row['course_url'] ?>" class="required" />
                    </TD>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strKeyWords?></label></TD>
                    <input name="course_keywords" type="text" value="<?php echo $row['course_keywords'] ?>"
                        class="required" /></TD>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strMetaDescription?></label><br /><textarea name="course_metadescription"
                        id="simple-html-editor"><?php echo $row['course_metadescription'] ?></textarea>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell text-center"> <input type="submit"
                        value="<?php echo $strModify?>" name="Submit" class="button success">
                </div>
            </div>
        </FORM>

        <?php
}
else
{
echo "<a href=\"sitecourses.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a>";
$query="SELECT * FROM elearning_courses";
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
                    <th><?php echo $strEdit?></th>
                    <th><?php echo $strDelete?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=ezpub_fetch_array($result)){
    $course_id_safe = (int)$row['Course_id'];
    $course_name_safe = htmlspecialchars($row['course_name'], ENT_QUOTES, 'UTF-8');
    		echo"<tr>
			<td>$course_id_safe</td>
			<td>$course_name_safe</td>
			  <td><a href=\"sitecourses.php?mode=edit&cID=$course_id_safe\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"sitecourses.php?mode=delete&cID=$course_id_safe\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td </td><td  colspan=\"2\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
}
?>
    </div>
</div>
<?php
include '../bottom.php';
?>