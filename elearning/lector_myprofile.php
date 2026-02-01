<?php
//updated 27.03.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare profil";
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
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Preluare și validare input
    $trainer_name = trim($_POST["trainer_name"] ?? '');
    $trainer_picture = trim($_POST["trainer_picture"] ?? '');
    $trainer_email = trim($_POST["trainer_email"] ?? '');
    $trainer_phone = trim($_POST["trainer_phone"] ?? '');
    $trainer_presentation_short = trim($_POST["trainer_presentation_short"] ?? '');
    $trainer_metadescription = trim($_POST["trainer_metadescription"] ?? '');
    $trainer_url = trim($_POST["trainer_url"] ?? '');

    // Update cu prepared statement (fără parolă)
    $stmt = mysqli_prepare($conn, "UPDATE elearning_trainers SET trainer_name=?, trainer_picture=?, trainer_email=?, trainer_phone=?, trainer_presentation_short=?, trainer_metadescription=?, trainer_url=? WHERE trainer_id=?");
    mysqli_stmt_bind_param($stmt, "sssssssi", $trainer_name, $trainer_picture, $trainer_email, $trainer_phone, $trainer_presentation_short, $trainer_metadescription, $trainer_url, $uid);

    if (!mysqli_stmt_execute($stmt)) {
        echo "<div class=\"callout alert\">$strThereWasAnError</div>";
        die('Error: ' . htmlspecialchars(mysqli_error($conn)));
    } else {
        echo "<div class=\"callout success\">$strRecordModified</div></div></div>";
        echo "<script type=\"text/javascript\">
        function delayer(){window.location = 'lector_myprofile.php'}
        </script><body onLoad=\"setTimeout('delayer()', 1500)\">";
        include 'bottom.php';
        die;
    }
}
else {
?>


        <?php

echo "<a href=\"lector_myprofile.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM elearning_trainers WHERE trainer_id=$uid";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>

        <form method="post" action="myprofile.php?mode=edit&tID=<?php echo (int)$uid?>">
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strTitle?>
                        <input name="trainer_name" type="text" value="<?php echo htmlspecialchars($row['trainer_name'], ENT_QUOTES, 'UTF-8') ?>" class="required" />
                    </label>
                </div>
        
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strPhone?>
                        <input name="trainer_phone" type="text" value="<?php echo htmlspecialchars($row['trainer_phone'], ENT_QUOTES, 'UTF-8') ?>" class="required" />
                    </label>
                </div>
     <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strURL?>
                        <input name="trainer_url" type="text" value="<?php echo htmlspecialchars($row['trainer_url'], ENT_QUOTES, 'UTF-8') ?>" class="required" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strKeyWords?>
                        <input name="trainer_keywords" type="text" value="<?php echo htmlspecialchars($row['trainer_keywords'], ENT_QUOTES, 'UTF-8') ?>" class="required" />
                    </label>
                </div>
               
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strPhone?>
                        <input name="trainer_phone" type="text" value="<?php echo $row['trainer_phone'] ?>" class="required" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strTrainerPresentationShort?>
                        <textarea name="trainer_presentation_short" class="simple-html-editor" data-upload-dir="elearning"><?php echo htmlspecialchars($row['trainer_presentation_short'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strMetaDescription?>
                        <textarea name="trainer_metadescription"><?php echo htmlspecialchars($row['trainer_metadescription'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strMainPicture?>
                        <input name="trainer_picture" id="image" type="text" class="required" value="<?php echo htmlspecialchars($row['trainer_picture'], ENT_QUOTES, 'UTF-8') ?>" readonly="readonly" />
                        <!-- Trigger/Open The Modal -->
                        <div class="full reveal" id="myModal" data-reveal>
                            <!-- Modal content -->
                            <iframe src="../common/image.php?directory=traineri&field=image" frameborder="0"  style="border:0" width="100%" height="750"></iframe>
                            <button class="close-button" data-close aria-label="Close modal" type="button"> <span aria-hidden="true">&times;</span></button>
                        </div>
                        <a href="#" class="button" data-open="myModal"><?php echo $strImage?></a>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" class="button" value="<?php echo $strModify?>" name="Submit">
                </div>
            </div>
        </form>
        <?php
}
else
{
    $stmt = mysqli_prepare($conn, "SELECT * FROM elearning_trainers WHERE trainer_id=?");
    mysqli_stmt_bind_param($stmt, "i", $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $numar = mysqli_num_rows($result);
    if ($numar == 0) {
        echo $strNoRecordsFound;
    } else {
        ?>
        <a href="lector_myprofile.php?mode=edit&tID=<?php echo (int)$uid?>" class="button"><i class="far fa-edit fa-xl" title="<?php echo htmlspecialchars($strEdit ?? '', ENT_QUOTES, 'UTF-8'); ?>"></i></a><br />
        <table width="100%">
            <thead>
                <tr>
                    <th><h4><?php echo $strMyProfile?></h4></th>
                    <th>&nbsp;</th>
                    <th><h4><?php echo $strMyProfile?></h4></th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                while ($row = ezpub_fetch_array($result)) {
                    echo "<tr><td>$strName</td><td colspan=\"2\">" . htmlspecialchars($row['trainer_name'], ENT_QUOTES, 'UTF-8') . "</td></tr>";
                    echo "<tr><td colspan=\"3\"><img src=\"$strSiteURL" . "images/traineri/" . htmlspecialchars($row['trainer_picture'], ENT_QUOTES, 'UTF-8') . "\" width=\"auto\" title=\"" . htmlspecialchars($row['trainer_name'], ENT_QUOTES, 'UTF-8') . "\" alt=\"" . htmlspecialchars($row['trainer_name'], ENT_QUOTES, 'UTF-8') . "\"/></td></tr>";
                    echo "<tr><td>$strTrainerPresentationShort</td><td colspan=\"2\">" . htmlspecialchars($row['trainer_presentation_short'], ENT_QUOTES, 'UTF-8') . "</td></tr>";
                    echo "<tr><td>$strTrainerPresentationLong</td><td colspan=\"2\">" . htmlspecialchars($row['trainer_presentation_long'], ENT_QUOTES, 'UTF-8') . "</td></tr>";
                    echo "<tr><td>$strEmail</td><td colspan=\"2\">" . htmlspecialchars($row['trainer_email'], ENT_QUOTES, 'UTF-8') . "</td></tr>";
                    echo "<tr><td>$strPhone</td><td colspan=\"2\">" . htmlspecialchars($row['trainer_phone'], ENT_QUOTES, 'UTF-8') . "</td></tr>";
                }
                echo "</tbody><tfoot><tr><td></td></tr></tfoot></table>";
    }
}
}
?>
    </div>
</div>
<?php
include '../bottom.php';
?>