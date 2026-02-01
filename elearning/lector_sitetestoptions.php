<?php
//updated 17.07.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare opțiuni";
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
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){
$optionsql="SELECT quizoption_question FROM elearning_quizoptions WHERE quizoption_ID=$_GET[oID]";
$result=ezpub_query($conn,$optionsql);
$row=ezpub_fetch_array($result);
$qID=$row["quizoption_question"];
echo $optionsql;
$elearning_testsql="SELECT question_test FROM elearning_questions WHERE question_ID=$qID";
$result=ezpub_query($conn,$elearning_testsql);
$row=ezpub_fetch_array($result);
$tID=$row["question_test"];
echo $elearning_testsql;

$nsql="DELETE FROM elearning_quizoptions WHERE quizoption_ID=" .$_GET['oID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout succes\">$strRecordDeleted</div></div></div>"; ;

echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"lector_sitetestquestions.php?tID=$tID\"
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

	$mSQL = "INSERT INTO elearning_quizoptions(";
	$mSQL = $mSQL . "quizoption_question,";
	$mSQL = $mSQL . "quizoption_option,";
	$mSQL = $mSQL . "quizoption_score,";
	$mSQL = $mSQL . "quizoption_comment)";

	$mSQL = $mSQL . "values(";
	$mSQL = $mSQL . "'" .$_GET["qID"] . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["quizoption_option"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["quizoption_score"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["quizoption_comment"]) . "') ";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
else{

$qID=$_GET['qID'];
echo "<div class=\"callout success\">$strRecordAdded</div>";
$elearning_testsql="SELECT question_test FROM elearning_questions WHERE question_ID=$qID";
$result=ezpub_query($conn,$elearning_testsql);
$row=ezpub_fetch_array($result);
$tID=$row["question_test"];

echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"lector_sitetestsquestions.php?tID=$tID\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;

}}
else
{// edit
$strWhereClause = " WHERE elearning_quizoptions.quizoption_ID=" . $_GET["oID"] . ";";
$query= "UPDATE elearning_quizoptions SET elearning_quizoptions.quizoption_option='" .str_replace("'","&#39;",$_POST["quizoption_option"]) . "' ," ;
$query= $query . "elearning_quizoptions.quizoption_score='" .str_replace("'","&#39;",$_POST["quizoption_score"]) . "' ," ;
$query= $query . "elearning_quizoptions.quizoption_comment='" .str_replace("'","&#39;",$_POST["quizoption_comment"]) . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
else{
	$optionsql="SELECT quizoption_question FROM elearning_quizoptions WHERE quizoption_ID=$_GET[oID]";
$result=ezpub_query($conn,$optionsql);
$row=ezpub_fetch_array($result);
$oID=$row["quizoption_question"];
$elearning_testsql="SELECT question_test FROM elearning_questions WHERE question_ID=$oID";
$result=ezpub_query($conn,$elearning_testsql);
$row=ezpub_fetch_array($result);
$tID=$row["question_test"];
echo "<div class=\"callout success\">$strRecordModified</div>" ;

echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"lector_sitetestsquestions.php?tID=$tID\"
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
        <script src='../js/tinymce/tinymce.min.js'></script>
        <script>
        tinymce.init({
            selector: "textarea.myTextEditor",
            menubar: false,
            image_advtab: true,
            plugins: [
                'advlist autolink lists link image imagetools charmap print preview anchor',
                'searchreplace visualblocks code fullscreen preview',
                'insertdatetime media table contextmenu paste code'
            ],
            toolbar: 'insertfile undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image preview code',
            content_css: [
                '//fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i',
                '//www.tiny.cloud/css/codepen.min.css'
            ],
            image_title: true,
            // enable automatic uploads of images represented by blob or data URIs
            paste_data_images: true,
            automatic_uploads: true,
            // URL of our upload handler (for more details check: https://www.tinymce.com/docs/configure/file-image-upload/#images_upload_url)
            images_upload_url: '../postAcceptor.php',
            images_upload_base_path: '..',
            images_upload_credentials: true,
            file_picker_types: 'file image media',

            file_picker_callback: function(cb, value, meta) {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');

                // Note: In modern browsers input[type="file"] is functional without 
                // even adding it to the DOM, but that might not be the case in some older
                // or quirky browsers like IE, so you might want to add it to the DOM
                // just in case, and visually hide it. And do not forget do remove it
                // once you do not need it anymore.

                input.onchange = function() {
                    var file = this.files[0];

                    var reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onload = function() {
                        // Note: Now we need to register the blob in TinyMCEs image blob
                        // registry. In the next release this part hopefully won't be
                        // necessary, as we are looking to handle it internally.
                        var id = 'blobid' + (new Date()).getTime();
                        var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                        var base64 = reader.result.split(',')[1];
                        var blobInfo = blobCache.create(id, file, base64);
                        blobCache.add(blobInfo);

                        // call the callback and populate the Title field with the file name
                        cb(blobInfo.blobUri(), {
                            title: file.name
                        });
                    };
                };

                input.click();
            }
        });
        </script>
        <?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
        <form method="post"  action="lector_sitetestoptions.php?mode=new&qID=<?php echo $_GET['qID']?>">
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strOption?>
                        <textarea name="quizoption_option" style="width:100%;   height: 200;" id="myTextEditor"
                            class="myTextEditor"></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strScore?>
                        <input name="quizoption_score" type="text" class="required" />
                    </label>
                </div>
                <div class="large-10 medium-10 small-10 cell">
                    <label><?php echo $strComment?>
                        <input name="quizoption_comment" type="text" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" class="button" value="<?php echo $strAdd?>" name="Submit">
                </div>
            </div>
        </form>
        <?php
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM elearning_quizoptions WHERE quizoption_ID=$_GET[oID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
        <form method="post" 
            action="lector_sitetestoptions.php?mode=edit&oID=<?php echo $row['quizoption_ID']?>">
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">

                    <label><?php echo $strOption?>
                        <textarea name="quizoption_option" style="width:100%;   height: 200;" id="myTextEditor"
                            class="myTextEditor"><?php echo $row["quizoption_option"]?></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strScore?>
                        <input name="quizoption_score" type="text" value="<?php echo $row["quizoption_score"]?>"
                            class="required" />
                    </label>
                </div>
                <div class="large-10 medium-10 small-10 cell">
                    <label><?php echo $strComment?>
                        <input name="quizoption_comment" type="text" value="<?php echo $row["quizoption_comment"]?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 text-center cell">
                    <input type="submit" class="button" value="<?php echo $strModify?>" name="Submit">
                </div>
            </div>
        </form>
        <?php
}
else
{
echo "<a href=\"lector_sitetestoptions.php?mode=new&qID=$_GET[qID]\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a><br/>";
$query="SELECT * FROM elearning_quizoptions WHERE quizoption_question=$_GET[qID]";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
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
                    <th><?php echo $strOption?></th>
                    <th><?php echo $strComment?></th>
                    <th><?php echo $strScore?></th>
                    <th><?php echo $strEdit?></th>
                    <th><?php echo $strDelete?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[quizoption_ID]</td>
			<td>$row[quizoption_option]</td>
			<td>$row[quizoption_comment]</td>
			<td>$row[quizoption_score]</td>
			  <td><a href=\"lector_sitetestoptions.php?mode=edit&oID=$row[quizoption_ID]\"><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"lector_sitetestoptions.php?mode=delete&oID=$row[quizoption_ID]\" OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"4\"><em></em></td><td >&nbsp;</td></tr></tfoot></table>";
}
}
}
?>
    </div>
</div>
<?php
include '../bottom.php';
?>