<?php
//updated 17.07.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare întrebări";
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
$elearning_testsql="SELECT question_test FROM elearning_questions WHERE question_ID=$_GET[qID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
$tID=$row["question_test"];
$nsql="DELETE FROM elearning_questions WHERE question_ID=" .$_GET['qID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"lector_sitetests.php?tID=$tID\"
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

	$mSQL = "INSERT INTO elearning_questions(";
	$mSQL = $mSQL . "question_question,";
	$mSQL = $mSQL . "question_type,";
	$mSQL = $mSQL . "question_hint,";
	$mSQL = $mSQL . "question_score,";
	$mSQL = $mSQL . "question_test)";

	$mSQL = $mSQL . "values(";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["question_question"]) . "', ";
	$mSQL = $mSQL . "'" .$_POST["question_type"] . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["question_hint"]) . "', ";
	$mSQL = $mSQL . "'" .$_POST["question_score"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["question_test"] . "') ";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
else{
echo "<div class=\"callout success\">$strRecordAdded</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"lector_sitetestsquestions.php?tID=$_POST[question_test]\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}}
else
{// edit
$strWhereClause = " WHERE elearning_questions.question_ID=" . $_GET["qID"] . ";";
$query= "UPDATE elearning_questions SET elearning_questions.question_question='" .str_replace("'","&#39;",$_POST["question_question"]) . "' ," ;
$query= $query . "elearning_questions.question_hint='" .str_replace("'","&#39;",$_POST["question_hint"]) . "' ," ;
$query= $query . "elearning_questions.question_type='" .$_POST["question_type"] . "' ," ;
$query= $query . "elearning_questions.question_score='" .$_POST["question_score"] . "' ," ;
$query= $query . "elearning_questions.question_test='" .$_POST["question_test"] . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
else{
echo "<div class=\"callout success\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"lector_sitetestsquestions.php?tID=$_POST[question_test]\"
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
            images_upload_url: '../postAcceptor.php?loc=tests',
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
        <form method="post" action="lector_sitetestsquestions.php?mode=new">
            <div class="grid-x grid-padding-x">
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strTest?>
                        <select name="question_test" class="required">
                            <option value="0"><?php echo $strPick?></option>
                            <?php $sql = "Select test_ID, test_name  FROM elearning_tests where test_author=$uid";
        $result = ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
		?>
                            <option value="<?php echo $rss["test_ID"]?>"><?php echo $rss["test_name"]?></option>
                            <?php }?>
                        </select></label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strScore?>
                        <input name="question_score" type="text" size="4" class="required" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strType?>
                        <select name="question_type" class="required">
                            <option value="1000"><?php echo $strPick?></option>
                            <option value="0"><?php echo $strOpened?></option>
                            <option value="1"><?php echo $strSingleOption?></option>
                            <option value="2"><?php echo $strMultipleOption?></option>
                        </select></label>
                </div>
                <div class="large-3 medium-3 small-3 cell">

                    <label><?php echo $strHint?>
                        <input name="question_hint" type="text" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label> <?php echo $strQuestion?>
                        <textarea name="question_question" style="width:100%;   height: 200;" id="myTextEditor"
                            class="myTextEditor"></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" class="button" value="<?php echo $strAdd?>" name="Submit">
                </div>
            </div>
        </form>

        <?php
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM elearning_questions WHERE question_ID=$_GET[qID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
        <form method="post" 
            action="lector_sitetestsquestions.php?mode=edit&qID=<?php echo $row['question_ID']?>">
            <table id="rounded-corner" summary="<?php echo $strQuestion?>" width="100%">
                <div class="grid-x grid-padding-x">
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strTest?>
                            <select name="question_test" class="required">
                                <option value="0"><?php echo $strPick?></option>
                                <?php $sql = "Select test_ID, test_name  FROM elearning_tests where test_author=$uid";
        $result = ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
		if ($row["question_test"]==$rss["test_ID"]){
		?>
                                <option selected value="<?php echo $rss["test_ID"]?>"><?php echo $rss["test_name"]?>
                                </option>
                                <?php } else {?>
                                <option value="<?php echo $rss["test_ID"]?>"><?php echo $rss["test_name"]?></option>
                                <?php }}?>
                            </select> </label>
                    </div>
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strScore?></label>
                        <input name="question_score" type="text" value="<?php echo $row["question_score"]?>"
                            class="required" />
                        </label>
                    </div>
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strType?>
                            <select name="question_type" class="required">
                                <option value="1000"><?php echo $strPick?></option>
                                <option value="0" <?php If ($row["question_type"]==0) {echo "selected";}?>>
                                    <?php echo $strOpened?></option>
                                <option value="1" <?php If ($row["question_type"]==1) {echo "selected";}?>>
                                    <?php echo $strSingleOption?></option>
                                <option value="2" <?php If ($row["question_type"]==2) {echo "selected";}?>>
                                    <?php echo $strMultipleOption?></option>
                            </select></label>
                    </div>
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strHint?>
                            <input name="question_hint" type="text" value="<?php echo $row["question_hint"]?>" />
                        </label>
                    </div>
                </div>
                <div class="grid-x grid-padding-x">
                    <div class="large-12 medium-12 small-12 cell">
                        <label> <?php echo $strQuestion?>
                            <textarea name="question_question" style="width:100%;   height: 200;" id="myTextEditor"
                                class="myTextEditor"><?php echo $row["question_question"]?></textarea>
                        </label>
                    </div>
                </div>
                <div class="grid-x grid-padding-x">
                    <div class="large-12 medium-12 small-12 cell text-center">
                        <input type="submit" class="button" value="<?php echo $strModify?>" name="Submit">
                    </div>
                </div>
        </form>
        <?php
}
else
{
echo "<a href=\"lector_sitetestsquestions.php?mode=new&tID=$_GET[tID]\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a><br/>";
$query="SELECT * FROM elearning_questions WHERE question_test='$_GET[tID]'";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout success\">$strNoRecordsFound</div>";
}
else {
?>
        <?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<table width=\"100%\">
	      <thead>
    	<tr>
        	<th>$strID</th>
			<th>$strQuestion</th>
			<th>$strType</th>
			<th>$strScore</th>
			<th>$strEdit</th>
			<th>$strDelete</th>
        </tr>
		</thead>
<tbody>

			<tr>
			<td>$row[question_ID]</td>
			<td>$row[question_question]</td>
			<td>";

If			($row["question_type"]==0) {$questiontype=$strOpen;}
elseIf		($row["question_type"]==1) {$questiontype=$strSingleOption;}
elseIf		($row["question_type"]==2) {$questiontype=$strMultipleOption;}
echo $questiontype . "
</td>
			<td>$row[question_score]</td>
			  <td><a href=\"lector_sitetestsquestions.php?mode=edit&qID=$row[question_ID]\" <i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"lector_sitetestsquestions.php?mode=delete&qID=$row[question_ID]\" OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";

echo "</tbody><tfoot><tr><td></td><td  colspan=\"4\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>
<h4>$strOptions</h4>
";

$query2="SELECT * FROM elearning_quizoptions WHERE quizoption_question=$row[question_ID]";
$result2=ezpub_query($conn,$query2);
$numar2=ezpub_num_rows($result2,$query2);
echo ezpub_error($conn);
if ($numar2==0)
{
	echo "<a href=\"lector_sitetestoptions.php?mode=new&qID=$row[question_ID]\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a><br/>";
echo $strNoRecordsFound;
}
else {
		echo "<a href=\"lector_sitetestoptions.php?mode=new&qID=$row[question_ID]\"><i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a><br/>";
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
While ($row2=ezpub_fetch_array($result2)){
    		echo"<tr>
			<td>$row2[quizoption_ID]</td>
			<td>$row2[quizoption_option]</td>
			<td>$row2[quizoption_comment]</td>
			<td>$row2[quizoption_score]</td>
			  <td><a href=\"lec	tor_sitetestoptions.php?mode=edit&oID=$row2[quizoption_ID]\"><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"lector_sitetestoptions.php?mode=delete&oID=$row2[quizoption_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"4\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
echo "</div>";
}
}
}
}
?>
    </div>
</div>
<?php
include '../bottom.php';
?>