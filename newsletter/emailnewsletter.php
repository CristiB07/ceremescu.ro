<?php
//update 29.01.2023
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$strPageTitle="Trimitere email";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$day = date('d');
$year = date('Y');
$month = date('m');
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php


if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	//
	
	$uploaddir = $hddpath ."/" . $newsletter_folder;
 
        $filename = $_FILES['file']['name'];
	        // Upload file
        move_uploaded_file($_FILES['file']['tmp_name'],$uploaddir."/".$filename);
		$attachement = $filename;
 
	$data_newsletter=date("Y-m-d");
	$mSQL = "INSERT INTO date_newsletter(";
	$mSQL = $mSQL . "newsletter_data_trimiterii,";
	$mSQL = $mSQL . "newsletter_atasament,";
	$mSQL = $mSQL . "newsletter_content)";

	$mSQL = $mSQL . "values(";
	$mSQL = $mSQL . "'" .$data_newsletter . "', ";
	$mSQL = $mSQL . "'" .$attachement	. "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["email_body"]) . "') ";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
else{
	
//prepare the mail

$emailbody="<html>";
$emailbody=$emailbody . "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$emailbody=$emailbody . "<link href='".$siteCompanyWebsite."/fonts/open-sans-condensed-v21-latin-ext_latin-300.woff' rel='stylesheet' type='text/css'>";
$emailbody=$emailbody . "<link href='".$siteCompanyWebsite."/fonts/open-sans-v27-latin-ext_latin-regular.woff' rel='stylesheet' type='text/css'>";
$emailbody=$emailbody . "<style>body {margin-top: 10px; margin-bottom: 10px; margin-left: 10px; margin-right: 10px; font-size: 1.1em; font-family: 'Open Sans',sans-serif; padding: 0px; COLOR: " . $color ."}";
$emailbody=$emailbody . "h1,h2,h3,h4,h5 {font-family:'Open Sans',sans-serif; font-weight: bold; color: " . $color .";}";
$emailbody=$emailbody . "td {font-size: 1em; font-family: 'Open Sans',sans-serif; COLOR: " . $color ."; padding: 3px;  font-weight: normal; border-collapse:collapse; border: 1px solid " . $color .";}";
$emailbody=$emailbody . "th {font-size: 1.1em; font-family: 'Open Sans',sans-serif; COLOR: #ffffff; background-color: " . $color .";  font-weight: normal;}";
$emailbody=$emailbody . "table {border-collapse:collapse; border: 1px solid " . $color .";}";
$emailbody=$emailbody . "</style>";
$emailbody=$emailbody . "</head><body>";
$emailbody=$emailbody . "<a href=\"$siteCompanyWebsite\"><img src=\"".$siteCompanyWebsite."/img/logo.png\" title=\"$strSiteOwner\" width=\"150\" height=\"auto\"/></a>";
$emailbody=$emailbody . $_POST["email_body"];
$emailbody=$emailbody . "<p>Primiți acest mesaj deoarece sunteți client al firmei noastre. Dacă nu mai doriți să primiți acest buletin informativ pe viitor, este suficient să răspundeți la acest mesaj cu „Nu”.<p/>";
$emailbody=$emailbody . "<p>Mulțumim,<br />
$strSiteOwner<br />
$iconFacebook &nbsp;&nbsp;&nbsp;
$iconLinkedin 
</p>";
$emailbody=$emailbody . "</body>";
$emailbody=$emailbody . "</html>";

//send the email

$query="SELECT * FROM newsletter_abonati where newsletter_email_active='1'";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
While ($row=ezpub_fetch_array($result)){

require '../vendor/autoload.php';

//Create a new PHPMailer instance
$mail = new PHPMailer();
//Set PHPMailer to use the sendmail transport
$mail->CharSet = 'UTF-8';
$mail->isSMTP();
//Enable SMTP debugging
//SMTP::DEBUG_OFF = off (for production use)
//SMTP::DEBUG_CLIENT = client messages
//SMTP::DEBUG_SERVER = client and server messages
$mail->SMTPDebug = 0;
//Set the hostname of the mail server
$mail->Host = $SmtpServer;
//Set the SMTP port number - likely to be 25, 465 or 587
$mail->Port = $SmtpPort;
//Whether to use SMTP authentication
$mail->SMTPAuth = true;
//Username to use for SMTP authentication
$mail->Username = $SmtpNewsletterUser;
//Password to use for SMTP authentication
$mail->Password = $SmtpNewsletterPass;
//Set who the message is to be sent from
$mail->setFrom($siteNewsletterEmail, $strNewsletterOwner);
//Set an alternative reply-to address
$mail->addReplyTo($siteNewsletterEmail, $strNewsletterOwner);
//Set who the message is to be sent to
$mail->ConfirmReadingTo = $siteCompanyEmail;
$mail->SMTPOptions = array(
    'ssl' => array(
    'verify_peer' => false,
    'verify_peer_name' => false,
    'allow_self_signed' => true
    )
);

$emailto=str_replace(' ', '', $row["newsletter_email"]);
$array = explode(';', $emailto); //
foreach($array as $value) //loop over values
{
$mail->addAddress($value);
}
//var_dump(PHPMailer::validateAddress('$emailto'));
//Set the subject line
$mail->Subject = 'Newsletter ' . $strSiteOwner;
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Body    = $emailbody;
    $mail->AltBody = 'Acest mail conține newsletter'. $strSiteOwner .'. Mulțumim,'. $strSiteOwner;
//Attach an image file
$mail->addAttachment($hddpath ."/" . $newsletter_folder ."/". $attachement);


//send the message, check for errors
if (!$mail->send()) {
    echo '<div class=\"callout alert\">Mailer Error: ' . $mail->ErrorInfo . '</div>';
} else {
    echo "<div class=\"callout success\">" . $strMessageSent ." ". $strTo ." ". $emailto . "</div>";
}
}
}
}
else
{
 ?>
        <script src='../js/tinymce/tinymce.min.js'></script>
        <script language="JavaScript" type="text/JavaScript">
            tinymce.init({
  selector: "textarea.myTextEditor",
  menubar: false,
  image_advtab: false,
   plugins: [
    'advlist autolink lists link imagetools charmap print preview anchor',
    'searchreplace visualblocks code fullscreen preview',
    'insertdatetime media table contextmenu paste code pagebreak'
  ],
  toolbar: 'insertfile undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link preview code pagebreak',
  content_css: [
        '//fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i',
        '//www.tiny.cloud/css/codepen.min.css'],
	 image_title: true, 
  // enable automatic uploads of images represented by blob or data URIs
  paste_data_images: false,
  automatic_uploads: false,
  // URL of our upload handler (for more details check: https://www.tinymce.com/docs/configure/file-image-upload/#images_upload_url)
  images_upload_url: 'postAcceptor.php',
    images_upload_base_path: '',
  images_upload_credentials: true,
  file_picker_types: 'file image media',
  
 file_picker_callback: function(cb, value, meta) {
    var input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image*');
    
    // Note: In modern browsers input[type="file"] is functional without 
    // even adding it to the DOM, but that might not be the case in some older
    // or quirky browsers like IE, so you might want to add it to the DOM
    // just in case, and visually hide it. And do not forget do remove it
    // once you do not need it anymore.

    input.onchange = function() {
      var file = this.files[0];
      
      var reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = function () {
        // Note: Now we need to register the blob in TinyMCEs image blob
        // registry. In the next release this part hopefully won't be
        // necessary, as we are looking to handle it internally.
        var id = 'blobid' + (new Date()).getTime();
        var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
        var base64 = reader.result.split(',')[1];
        var blobInfo = blobCache.create(id, file, base64);
        blobCache.add(blobInfo);

        // call the callback and populate the Title field with the file name
        cb(blobInfo.blobUri(), { title: file.name });
      };
    };
    
    input.click();
  }
});

</script>

        <h1><?php echo $strSendNewsletter ?></h1>
        <form method="POST" action="emailnewsletter.php" enctype="multipart/form-data">
            <div class="grid-x grid-padding-x ">
                <div class="medium-12 cell">
                    <label><?php echo $strAttachement ?>
                        <input type="file" name="file" id="file">
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strMessage?></label>
                    <textarea name="email_body" id="myTextEditor" class="myTextEditor" rows="5">
		  </textarea>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 cell"><input Type="submit" value="<?php echo $strSend ?>" name="Submit"
                        class="button success" />
                </div>
            </div>
    </div>
    </form>
</div>
</div>
<?php 
}
include '../bottom.php';
 ?>