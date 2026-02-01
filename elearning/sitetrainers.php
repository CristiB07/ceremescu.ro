<?php
//update 05.02.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare traineri";
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/index.php?message=MLF");
}
include '../dashboard/header.php';
?>
<div class="grid-x grid-padding-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

// Sanitize input
$tID = (int)$_GET['tID'];
if ($tID <= 0) {
    header("location:sitetrainers.php?message=ER");
    die;
}

// Prepared statement pentru DELETE
$stmt = mysqli_prepare($conn, "DELETE FROM elearning_trainers WHERE trainer_id=?");
mysqli_stmt_bind_param($stmt, "i", $tID);
mysqli_stmt_execute($stmt);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitetrainers.php\"
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
$myString = mysqli_real_escape_string($conn, $_POST["trainer_name"]);
$myarray = explode(',', $myString);
$trainer_name=$myarray[1];
$trainer_utilizator_ID=(int)$myarray[0];

// Prepared statement pentru INSERT
$stmt = mysqli_prepare($conn, "INSERT INTO elearning_trainers(
    trainer_name, trainer_utilizator_ID, trainer_presentation_short, 
    trainer_picture, trainer_keywords, trainer_email, trainer_password, 
    trainer_phone, trainer_metadescription, trainer_url
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$trainer_presentation_short = mysqli_real_escape_string($conn, $_POST["trainer_presentation_short"]);
$trainer_picture = mysqli_real_escape_string($conn, $_POST["trainer_picture"]);
$trainer_keywords = mysqli_real_escape_string($conn, $_POST["trainer_keywords"]);
$trainer_email = mysqli_real_escape_string($conn, $_POST["trainer_email"]);
$trainer_password = mysqli_real_escape_string($conn, $_POST["trainer_password"]);
$trainer_phone = mysqli_real_escape_string($conn, $_POST["trainer_phone"]);
$trainer_metadescription = mysqli_real_escape_string($conn, $_POST["trainer_metadescription"]);
$trainer_url = mysqli_real_escape_string($conn, $_POST["trainer_url"]);

mysqli_stmt_bind_param($stmt, "sissssssss", 
    $trainer_name, $trainer_utilizator_ID, $trainer_presentation_short,
    $trainer_picture, $trainer_keywords, $trainer_email, $trainer_password,
    $trainer_phone, $trainer_metadescription, $trainer_url
);

if (!mysqli_stmt_execute($stmt))
  {
  die('Error: ' . mysqli_error($conn));
  }
else{
echo "<div class=\"callout success\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitetrainers.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}}
else
{// edit
// Sanitize tID
$tID = (int)$_GET['tID'];
if ($tID <= 0) {
    header("location:sitetrainers.php?message=ER");
    die;
}

$myString = mysqli_real_escape_string($conn, $_POST["trainer_name"]);
$myarray = explode(',', $myString);
$trainer_name=$myarray[1];
$trainer_utilizator_ID=(int)$myarray[0];

// Prepared statement pentru UPDATE
$stmt = mysqli_prepare($conn, "UPDATE elearning_trainers SET 
    trainer_name=?, trainer_utilizator_ID=?, trainer_picture=?, 
    trainer_keywords=?, trainer_password=?, trainer_email=?, 
    trainer_phone=?, trainer_metadescription=?, trainer_presentation_short=?, 
    trainer_url=? WHERE trainer_id=?");

$trainer_picture = mysqli_real_escape_string($conn, $_POST["trainer_picture"]);
$trainer_keywords = mysqli_real_escape_string($conn, $_POST["trainer_keywords"]);
$trainer_password = mysqli_real_escape_string($conn, $_POST["trainer_password"]);
$trainer_email = mysqli_real_escape_string($conn, $_POST["trainer_email"]);
$trainer_phone = mysqli_real_escape_string($conn, $_POST["trainer_phone"]);
$trainer_metadescription = mysqli_real_escape_string($conn, $_POST["trainer_metadescription"]);
$trainer_presentation_short = mysqli_real_escape_string($conn, $_POST["trainer_presentation_short"]);
$trainer_url = mysqli_real_escape_string($conn, $_POST["trainer_url"]);

mysqli_stmt_bind_param($stmt, "sisssssssi", 
    $trainer_name, $trainer_utilizator_ID, $trainer_picture,
    $trainer_keywords, $trainer_password, $trainer_email,
    $trainer_phone, $trainer_metadescription, $trainer_presentation_short,
    $trainer_url, $tID
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
    window.location = \"sitetrainers.php\"
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
	echo "<a href=\"sitetrainers.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>";
?>

        <form method="post"  action="sitetrainers.php?mode=new">
            <div class="grid-x grid-padding-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strName?></label>
                    <select name="trainer_name" required>
                        <option value="0"><?php echo $strPick?></option>
                        <?php $sql = "Select utilizator_ID, utilizator_Prenume, utilizator_Nume from date_utilizatori WHERE utilizator_Function='TRAINER' ORDER BY utilizator_Nume ASC";
							$result = ezpub_query($conn,$sql);
							while ($rss=ezpub_fetch_array($result)){
							?>
                        <option
                            value="<?php echo $rss["utilizator_ID"].",".$rss["utilizator_Prenume"]." ".$rss["utilizator_Nume"]?>">
                            <?php echo $rss["utilizator_Prenume"]." ".$rss["utilizator_Nume"]?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strURL?></label></TD>
                    <input name="trainer_url" type="text" class="required" />
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strKeyWords?></label></TD>
                    <input name="trainer_keywords" type="text" class="required" />
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strPicture?></label></TD>
                    <input name="trainer_picture" id="image" type="text" required readonly="readonly" />
                    <!-- Trigger/Open The Modal -->
                    <div class="full reveal" id="myModal" data-reveal>
                        <!-- Modal content -->
                        <iframe src="<?php echo $strSiteURL?>/common/image.php?directory=traineri&field=image"
                            frameborder="0" style="border:0" Width="100%" height="750"></iframe>
                        <button class="close-button" data-close aria-label="Close reveal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <a data-open="myModal" class="button"><?php echo $strImage?></a>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strMetaDescription?></label>
                    <textarea name="trainer_metadescription" style="width:100%;"></textarea>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strTrainerPresentationShort?></label><br />
                    <textarea name="trainer_presentation_short" class="simple-html-editor" data-upload-dir="elearning" style="width:100%;"></textarea>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" value="<?php echo $strAdd?>" name="Submit" class="submit button">
                </div>
            </div>
        </form>
        <?php
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
	echo "<a href=\"sitetrainers.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>";

// Sanitize tID
$tID = (int)$_GET['tID'];
if ($tID <= 0) {
    header("location:sitetrainers.php?message=ER");
    die;
}

// Prepared statement pentru SELECT
$stmt = mysqli_prepare($conn, "SELECT * FROM elearning_trainers WHERE trainer_id=?");
mysqli_stmt_bind_param($stmt, "i", $tID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row=ezpub_fetch_array($result);
?>
        <form method="post" action="sitetrainers.php?mode=edit&tID=<?php echo (int)$row['trainer_id']?>">

            <div class="grid-x grid-padding-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strName?></label>
                    <select name="trainer_name" required>
                        <option value="0"><?php echo $strPick?></option>
                        <?php $sql = "Select utilizator_ID, utilizator_Prenume, utilizator_Nume from date_utilizatori WHERE utilizator_Role='TRAINER' ORDER BY utilizator_Nume ASC";
							$result = ezpub_query($conn,$sql);
							while ($rss=ezpub_fetch_array($result)){
							?>
                        <option
                            value="<?php echo htmlspecialchars($rss["utilizator_ID"].",".$rss["utilizator_Prenume"]." ".$rss["utilizator_Nume"], ENT_QUOTES, 'UTF-8')?>"
                            <?php If ($rss["utilizator_ID"]==$row["trainer_utilizator_ID"]) echo "selected"?>>
                            <?php echo htmlspecialchars($rss["utilizator_Prenume"]." ".$rss["utilizator_Nume"], ENT_QUOTES, 'UTF-8')?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strURL?></label></TD>
                    <input name="trainer_url" type="text" value="<?php echo htmlspecialchars($row["trainer_url"], ENT_QUOTES, 'UTF-8')?>" class="required" />
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strKeyWords?></label></TD>
                    <input name="trainer_keywords" type="text" value="<?php echo htmlspecialchars($row["trainer_keywords"], ENT_QUOTES, 'UTF-8')?>"
                        class="required" />
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strPicture?></label></TD>
                    <input name="trainer_picture" id="image" type="text" required readonly="readonly"
                        value="<?php echo htmlspecialchars($row["trainer_picture"], ENT_QUOTES, 'UTF-8')?>" />
                    <!-- Trigger/Open The Modal -->
                    <div class="full reveal" id="myModal" data-reveal>
                        <!-- Modal content -->
                        <iframe src="<?php echo $strSiteURL?>/common/image.php?directory=traineri&field=image"
                            frameborder="0" style="border:0" Width="100%" height="750"></iframe>
                        <button class="close-button" data-close aria-label="Close reveal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <a data-open="myModal" class="button"><?php echo $strImage?></a>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strMetaDescription?></label>
                    <textarea name="trainer_metadescription"
                        style="width:100%;"><?php echo htmlspecialchars($row["trainer_metadescription"], ENT_QUOTES, 'UTF-8')?></textarea>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strTrainerPresentationShort?></label><br />
                    <textarea name="trainer_presentation_short" class="simple-html-editor"
                        data-upload-dir="elearning" style="width:100%;"><?php echo htmlspecialchars($row["trainer_presentation_short"], ENT_QUOTES, 'UTF-8')?></textarea>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" value="<?php echo $strModify?>" name="Submit" class="submit button">
                </div>
            </div>
        </form>
        <?php
}
else
{
echo "<a href=\"sitetrainers.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a>";
$query="SELECT * FROM elearning_trainers";
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
    $trainer_id_safe = (int)$row['trainer_id'];
    $trainer_name_safe = htmlspecialchars($row['trainer_name'], ENT_QUOTES, 'UTF-8');
    echo"<tr>
			<td>$trainer_id_safe</td>
			<td>$trainer_name_safe</td>
			  <td><a href=\"sitetrainers.php?mode=edit&tID=$trainer_id_safe\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"sitetrainers.php?mode=delete&tID=$trainer_id_safe\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"2\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
}
?>
    </div>
</div>
</div>
<?php
include '../bottom.php';
?>