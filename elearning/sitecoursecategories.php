<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare categorii";
include '../dashboard/header.php';
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
    header("location:sitecoursecategories.php?message=ER");
    die;
}

// Prepared statement pentru DELETE
$stmt = mysqli_prepare($conn, "DELETE FROM elearning_coursecategory WHERE elearning_coursecategory_ID=?");
mysqli_stmt_bind_param($stmt, "i", $cID);
mysqli_stmt_execute($stmt);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecoursecategories.php\"
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
$stmt = mysqli_prepare($conn, "INSERT INTO elearning_coursecategory(
    elearning_coursecategory_name, 
    elearning_coursecategory_picture, 
    elearning_coursecategory_description
) VALUES (?, ?, ?)");

$category_name = mysqli_real_escape_string($conn, $_POST["elearning_coursecategory_name"]);
$category_picture = mysqli_real_escape_string($conn, $_POST["elearning_coursecategory_picture"]);
$category_description = mysqli_real_escape_string($conn, $_POST["elearning_coursecategory_description"]);

mysqli_stmt_bind_param($stmt, "sss", 
    $category_name, $category_picture, $category_description
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
    window.location = \"sitecoursecategories.php\"
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
    header("location:sitecoursecategories.php?message=ER");
    die;
}

// Prepared statement pentru UPDATE
$stmt = mysqli_prepare($conn, "UPDATE elearning_coursecategory SET 
    elearning_coursecategory_name=?, 
    elearning_coursecategory_picture=?, 
    elearning_coursecategory_description=? 
    WHERE elearning_coursecategory_ID=?");

$category_name = mysqli_real_escape_string($conn, $_POST["elearning_coursecategory_name"]);
$category_picture = mysqli_real_escape_string($conn, $_POST["elearning_coursecategory_picture"]);
$category_description = mysqli_real_escape_string($conn, $_POST["elearning_coursecategory_description"]);

mysqli_stmt_bind_param($stmt, "sssi", 
    $category_name, $category_picture, $category_description, $cID
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
    window.location = \"sitecoursecategories.php\"
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
?>
        <form method="post"  action="sitecoursecategories.php?mode=new">
            <table id="rounded-corner" summary="<?php echo $strCategory?>" width="100%">
                <div class="grid-x grid-padding-x">
                    <div class="large-12 medium-12 small-12 cell">
                        <label><?php echo $strTitle?>
                            <input name="elearning_coursecategory_name" type="text" class="required" />
                        </label>
                    </div>
                </div>
                <div class="grid-x grid-padding-x">
                    <div class="large-12 medium-12 small-12 cell">
                        <label> <?php echo $strTrainerPresentationLong?>
                            <textarea name="elearning_coursecategory_description" id="simple-html-editor"
                                class="simple-html-editor"></textarea>
                        </label>
                    </div>
                </div>

                <div class="grid-x grid-padding-x">
                    <div class="large-12 medium-12 small-12 cell">
                        <label><?php echo $strMainPicture?>
                            <input name="elearning_coursecategory_picture" id="image" type="text" class="required"
                                value="" readonly="readonly" />
                            <!-- Trigger/Open The Modal -->
                            <div class="full reveal" id="myModal" data-reveal>
                                <!-- Modal content -->
                                <iframe src="../common/image.php?directory=categorii&field=image" frameborder="0"
                                    style="border:0" Width="100%" height="750"></iframe>
                                <button class="close-button" data-close aria-label="Close modal" type="button">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <a href="#" class="button" data-open="myModal"><?php echo $strImage?></a>
                        </label>
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
    header("location:sitecoursecategories.php?message=ER");
    die;
}

// Prepared statement pentru SELECT
$stmt = mysqli_prepare($conn, "SELECT * FROM elearning_coursecategory WHERE elearning_coursecategory_ID=?");
mysqli_stmt_bind_param($stmt, "i", $cID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row=ezpub_fetch_array($result);
?>
        <form method="post" 
            action="sitecoursecategories.php?mode=edit&cID=<?php echo (int)$row['elearning_coursecategory_ID']?>">
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strTitle?>
                        <input name="elearning_coursecategory_name" type="text"
                            value="<?php echo htmlspecialchars($row['elearning_coursecategory_name'], ENT_QUOTES, 'UTF-8') ?>" class="required" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label> <?php echo $strTrainerPresentationLong?>
                        <textarea name="elearning_coursecategory_description" style="width:100%;" id="simple-html-editor"
                            class="simple-html-editor"><?php echo htmlspecialchars($row['elearning_coursecategory_description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strMainPicture?>
                        <input name="elearning_coursecategory_picture" id="image" type="text" class="required"
                            value="<?php echo htmlspecialchars($row['elearning_coursecategory_picture'], ENT_QUOTES, 'UTF-8') ?>" readonly="readonly" />
                        <!-- Trigger/Open The Modal -->
                        <div class="full reveal" id="myModal" data-reveal>
                            <!-- Modal content -->
                            <iframe src="../common/image.php?directory=categorii&field=image" frameborder="0"
                                style="border:0" Width="100%" height="750"></iframe>
                            <button class="close-button" data-close aria-label="Close modal" type="button">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <a href="#" class="button" data-open="myModal"><?php echo $strImage?></a>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" value="<?php echo $strModify?>" name="Submit" class="button success">
                </div>
            </div>

        </FORM>
        <?php
}
else
{
echo "<a href=\"sitecoursecategories.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a>";
$query="SELECT * FROM elearning_coursecategory";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo $strNoRecordsFound;
}
else {
?>
        <table width="100%">
            <thead>
                <th><?php echo $strID?></th>
                <th><?php echo $strTitle?></th>
                <th><?php echo $strEdit?></th>
                <th><?php echo $strDelete?></th>

            </thead>
            <tbody>
                <?php 
While ($row=ezpub_fetch_array($result)){
    $category_id_safe = (int)$row['elearning_coursecategory_ID'];
    $category_name_safe = htmlspecialchars($row['elearning_coursecategory_name'], ENT_QUOTES, 'UTF-8');
    echo"<tr>
			<td>$category_id_safe
			<td>$category_name_safe
			  <td><a href=\"sitecoursecategories.php?mode=edit&cID=$category_id_safe\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a>
			<td><a href=\"sitecoursecategories.php?mode=delete&cID=$category_id_safe\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a>
        </tr>";
}
echo "</tbody><tfoot><td></td><td  colspan=\"2\"><em></em><td>&nbsp;</td></tfoot></table>";
}
}
}
?>
    </div>
</div>
<?php
include '../bottom.php';
?>