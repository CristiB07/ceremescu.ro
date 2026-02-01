<?php
include '../settings.php';
    if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 
include '../classes/common.php';
$strKeywords="Înscriere curs ". $strSiteName; ;
$strPageTitle="Înscriere curs" . $strSiteName;
$strDescription="Pagina de înscriere la curs ". $strSiteName;
include '../dashboard/header.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/autoload.php';
$uid= $_SESSION['uid'];
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$d = date("Y-m-d H:i:s");
?>
<div class="grid-x grid-padding-x">
    <div class="large-12 medium-12 small-12 cell">
        <h1>Înscriere curs</h1>

        <?php
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
if ($_POST["account_company"]==0) {

// Prepared statement pentru INSERT companies
$stmt = mysqli_prepare($conn, "INSERT INTO site_companies(
    company_name, company_VAT, company_reg, company_address, 
    company_bank, company_siteaccount, company_IBAN
) VALUES (?, ?, ?, ?, ?, ?, ?)");

$company_name = mysqli_real_escape_string($conn, $_POST["company_name"]);
$company_VAT = mysqli_real_escape_string($conn, $_POST["company_VAT"]);
$company_reg = mysqli_real_escape_string($conn, $_POST["company_reg"]);
$company_address = mysqli_real_escape_string($conn, $_POST["company_address"]);
$company_bank = mysqli_real_escape_string($conn, $_POST["company_bank"]);
$company_IBAN = mysqli_real_escape_string($conn, $_POST["company_IBAN"]);

mysqli_stmt_bind_param($stmt, "sssssss", 
    $company_name, $company_VAT, $company_reg, $company_address,
    $company_bank, $uid, $company_IBAN
);
				
//It executes the SQL
if (!mysqli_stmt_execute($stmt))
  {
  die('Error: ' . mysqli_error($conn));
  }}
//insert new user
If ($_POST["course_type"]=="live") {
    // Sanitize input
    $scheduleID = (int)$_POST['strCourseAndSchedule'];
    if ($scheduleID <= 0) {
        die('Error: Invalid schedule ID');
    }
    
    // Prepared statement pentru SELECT
    $stmt = mysqli_prepare($conn, "SELECT schedule_course_ID FROM elearning_courseschedules WHERE schedule_ID=?");
    mysqli_stmt_bind_param($stmt, "i", $scheduleID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $course=ezpub_fetch_array($result);
    $course_ID=$course["schedule_course_ID"];
    $courserschedule=$scheduleID;
}
else
{
    $course_ID=(int)$_POST["elearning"];
    $courserschedule=0;
}
		if ($_POST["account_company"]==0) {
		$studentinvoice=mysqli_real_escape_string($conn, $_POST["company_VAT"]);}
        elseIf ($_POST["account_company"]=="pf") {
            $studentinvoice=0;}
		else {
        // Sanitize input
        $companyID = (int)$_POST['account_company'];
        if ($companyID <= 0) {
            die('Error: Invalid company ID');
        }
        
        // Prepared statement pentru SELECT
        $stmt = mysqli_prepare($conn, "SELECT company_VAT FROM site_companies WHERE company_id=?");
        mysqli_stmt_bind_param($stmt, "i", $companyID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row=ezpub_fetch_array($result);
        $studentinvoice=$row["company_VAT"];
		}
	

// Prepared statement pentru INSERT enrollment
$stmt = mysqli_prepare($conn, "INSERT INTO elearning_enrollments(
    elearning_enrollments_stud_id, elearning_enrollments_course_id, 
    elearning_enrollments_courseschedule_id, elearning_enrollments_date, 
    elearning_enrollments_active
) VALUES (?, ?, ?, ?, ?)");

$enrollActive = 0;
mysqli_stmt_bind_param($stmt, "iiisi", 
    $uid, $course_ID, $courserschedule, $d, $enrollActive
);

if (!mysqli_stmt_execute($stmt))
  {
  die('Error: ' . mysqli_error($conn));
  }
else{

echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";

/// write and send email
//coursedetails
// Prepared statement pentru SELECT course
$stmt = mysqli_prepare($conn, "SELECT * FROM elearning_courses WHERE Course_id=?");
mysqli_stmt_bind_param($stmt, "i", $course_ID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row=ezpub_fetch_array($result);
$numecurs=$row["course_name"];
If ($row["course_discount"]=="0.0000"){
$pretcurs=$row["course_price"];}
else {
$pretcurs=$row["course_discount"];}
//schedule details
If ($_POST["course_type"]=="live") {
// Sanitize input
$scheduleIDEmail = (int)$_POST['strCourseAndSchedule'];

// Prepared statement pentru SELECT
$stmt = mysqli_prepare($conn, "SELECT * FROM elearning_courseschedules WHERE schedule_ID=?");
mysqli_stmt_bind_param($stmt, "i", $scheduleIDEmail);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row=ezpub_fetch_array($result);
		$startdate=date('d M', strtotime($row['schedule_start_date']));
		$enddate=date('d M', strtotime($row['schedule_end_date']));
} 
else 
{
$startdate=$startdate=date('d M'); 
$enddate= date("d M", strtotime(date("Y-m-d", strtotime($startdate)) . " + 365 day"));
}
//Cumpărător
// Prepared statement pentru SELECT account
$stmt = mysqli_prepare($conn, "SELECT * FROM site_accounts WHERE account_id=?");
mysqli_stmt_bind_param($stmt, "i", $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row=ezpub_fetch_array($result);
$studentemail=$row["account_email"];
$account_first_name=$row["account_first_name"];
$account_last_name=$row["account_last_name"];

If ($_POST["account_company"]=="pf") {
$cumparator="Cumpărător: ". htmlspecialchars($row["account_last_name"], ENT_QUOTES, 'UTF-8') . " ". htmlspecialchars($row["account_first_name"], ENT_QUOTES, 'UTF-8') . "<br />";
$cumparator=$cumparator . "Adresa: ". htmlspecialchars($row["account_address"], ENT_QUOTES, 'UTF-8') . "<br />";
}
elseIf ($_POST["account_company"]==0) {
$cumparator="Cumpărător: " . htmlspecialchars(mysqli_real_escape_string($conn, $_POST["company_name"]), ENT_QUOTES, 'UTF-8') . "<br />";
$cumparator=$cumparator . "CUI: ".htmlspecialchars(mysqli_real_escape_string($conn, $_POST["company_VAT"]), ENT_QUOTES, 'UTF-8') . "<br />";
$cumparator=$cumparator . "Reg. Comert.: ".htmlspecialchars(mysqli_real_escape_string($conn, $_POST["company_reg"]), ENT_QUOTES, 'UTF-8') . "<br />";
$cumparator=$cumparator . "Adresa: ".htmlspecialchars(mysqli_real_escape_string($conn, $_POST["company_address"]), ENT_QUOTES, 'UTF-8') . "<br />";
$cumparator=$cumparator . "Banca: ".htmlspecialchars(mysqli_real_escape_string($conn, $_POST["company_bank"]), ENT_QUOTES, 'UTF-8') . "<br />";
$cumparator=$cumparator . "IBAN: ".htmlspecialchars(mysqli_real_escape_string($conn, $_POST["company_IBAN"]), ENT_QUOTES, 'UTF-8') . "<br />";
}
else  {
// Sanitize input
$companyIDEmail = (int)$_POST['account_company'];

// Prepared statement pentru SELECT
$stmt = mysqli_prepare($conn, "SELECT * FROM site_companies WHERE company_siteaccount=? AND company_id=?");
mysqli_stmt_bind_param($stmt, "ii", $uid, $companyIDEmail);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row=ezpub_fetch_array($result);

$cumparator="Cumpărător: ". htmlspecialchars($row["company_name"], ENT_QUOTES, 'UTF-8') . "<br />";
$cumparator=$cumparator . "CUI: ". htmlspecialchars($row["company_VAT"], ENT_QUOTES, 'UTF-8') . "<br />";
$cumparator=$cumparator . "Reg. Comert.: ". htmlspecialchars($row["company_reg"], ENT_QUOTES, 'UTF-8') . "<br />";
$cumparator=$cumparator . "Adresa: ". htmlspecialchars($row["company_address"], ENT_QUOTES, 'UTF-8') . "<br />";
$cumparator=$cumparator . "Banca: ". htmlspecialchars($row["company_bank"], ENT_QUOTES, 'UTF-8') . "<br />";
$cumparator=$cumparator . "IBAN: ". htmlspecialchars($row["company_IBAN"], ENT_QUOTES, 'UTF-8') . "<br />";
}
//Furnizor


$furnizor="Furnizor: ". $siteCompanyLegalName . "<br />";
$furnizor=$furnizor . "CUI: ". $siteVATNumber . "<br />";
$furnizor=$furnizor . "Reg. Comert.: ". $siteCompanyRegistrationNr . "<br />";
$furnizor=$furnizor . "Adresa: ". $siteCompanyLegalAddress . "<br />";
$furnizor=$furnizor . "Banca: ". $siteFirstAccount . "<br />";
$furnizor=$furnizor . "Telefon: ".$siteCompanyPhones . "<br />";
$furnizor=$furnizor . "Email: ". $siteCompanyEmail . "<br />";


$emailto=$studentemail;
$emailtoname=$account_first_name . " " . $account_last_name;


$emailbody="<html>";
$emailbody=$emailbody . "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$emailbody=$emailbody . "<link href='".$siteCompanyWebsite."/fonts/open-sans-condensed-v21-latin-ext_latin-300.woff' rel='stylesheet' type='text/css'>";
$emailbody=$emailbody . "<link href='".$siteCompanyWebsite."/fonts/open-sans-v27-latin-ext_latin-regular.woff' rel='stylesheet' type='text/css'>";
$emailbody=$emailbody . "<style>body {margin-top: 10px; margin-bottom: 10px; margin-left: 10px; margin-right: 10px; font-size: 1.1em; font-family: 'Open Sans',sans-serif; padding: 0px; color: " . $color ."}";
$emailbody=$emailbody . "h1,h2,h3,h4,h5 {font-family:'Open Sans',sans-serif; font-weight: bold; color: " . $color .";}";
$emailbody=$emailbody . "td {font-size: 1em; font-family: 'Open Sans',sans-serif; color: " . $color ."; padding: 3px;  font-weight: normal; border-collapse:collapse; border: 1px solid " . $color .";}";
$emailbody=$emailbody . "th {font-size: 1.1em; font-family: 'Open Sans',sans-serif; color: #ffffff; background-color: " . $color .";  font-weight: normal;}";
$emailbody=$emailbody . "table {border-collapse:collapse; border: 1px solid " . $color .";}";
$emailbody=$emailbody . "</style>";
$emailbody=$emailbody . "</head><body>";
$emailbody=$emailbody . "<a href=\"$siteCompanyWebsite\"><img src=\"".$siteCompanyWebsite."/img/logo.png\" title=\"$strSiteOwner\" width=\"150\" height=\"auto\"/></a>";
$emailbody=$emailbody . "<p>Stimate " .$account_first_name. " ".$account_last_name. ",<br>";
$emailbody=$emailbody . "Acesta este un mesaj de confirmare a înscrierii făcute de dumneavoastră pe site-ul ". $siteCompanyWebsite." la cursul ".$numecurs.". Activarea înscrierii se face după confirmarea plății. </p>";
$emailbody=$emailbody . "<table border=\"1\" align=\"center\" width=\"75%\">";
$emailbody=$emailbody . "<tr>";
$emailbody=$emailbody . "<td colspan=\"2\">";
$emailbody=$emailbody . $furnizor;
$emailbody=$emailbody . "</td>";
$emailbody=$emailbody . "<td colspan=\"2\">";
$emailbody=$emailbody .  $cumparator;
$emailbody=$emailbody . "</td>";
$emailbody=$emailbody . "</tr>";
$emailbody=$emailbody . "<tr>";
$emailbody=$emailbody . "<td>";
$emailbody=$emailbody . "Produs";
$emailbody=$emailbody . "</td>";
$emailbody=$emailbody . "<td>";
$emailbody=$emailbody . "Cantitate";
$emailbody=$emailbody . "</td>";
$emailbody=$emailbody . "<td>";
$emailbody=$emailbody . "Valoare";
$emailbody=$emailbody . "</td>";
$emailbody=$emailbody . "<td>";
$emailbody=$emailbody . "TVA";
$emailbody=$emailbody . "</td>";
$emailbody=$emailbody . "</tr>";
$emailbody=$emailbody . "<tr>";
$emailbody=$emailbody . "<td>";
$emailbody=$emailbody . $numecurs .", perioada ". $startdate ."-".$enddate;
$emailbody=$emailbody . "</td>";
$emailbody=$emailbody . "<td>";
$emailbody=$emailbody . "1";
$emailbody=$emailbody . "</td>";
$emailbody=$emailbody . "<td>";
$emailbody=$emailbody . $pretcurs;
$emailbody=$emailbody . "</td>";
$emailbody=$emailbody . "<td>";
$emailbody=$emailbody . "0";
$emailbody=$emailbody . "</td>";
$emailbody=$emailbody . "</tr>";
$emailbody=$emailbody . "<tr>";
$emailbody=$emailbody . "<td colspan=\"2\">";
$emailbody=$emailbody . "TOTAL ";
$emailbody=$emailbody . "</td>";
$emailbody=$emailbody . "<td colspan=\"2\">";
$emailbody=$emailbody . $pretcurs;
$emailbody=$emailbody . "</td>";
$emailbody=$emailbody . "</tr>";
$emailbody=$emailbody . "</table>";
$emailbody=$emailbody . "
Vă mulțumim,<br />
<label>$siteCompanyLegalName</label><br />
$siteCompanyLegalAddress <br />
$siteCompanyPhones<br />
$siteCompanyEmail<br />
$siteCompanyWebsite <br />
";

$emailbody=$emailbody . "</body>";
$emailbody=$emailbody . "</html>";
$body=$emailbody;
//Create a new PHPMailer instance
$mail = new PHPMailer();
//Set PHPMailer to use the sendmail transport
$mail->CharSet = 'UTF-8';
$mail->isSMTP();
//Enable SMTP debugging
//SMTP::DEBUG_OFF = ON (for production use)
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
$mail->Username = $SmtpUser;
//Password to use for SMTP authentication
$mail->Password = $SmtpPass;
//Set who the message is to be sent from
//Set who the message is to be sent from
$mail->setFrom($SmtpUser, $strSiteOwner);
//Set an alternative reply-to address
$mail->addReplyTo($SmtpUser, $strSiteOwner);
//Set who the message is to be sent to
$mail->addAddress($emailto, $emailtoname);
$mail->addAddress($SmtpUser, $strSiteOwner);
//Set the subject line
$mail->Subject = 'Înscriere pe site-ul ' . $siteCompanyWebsite;
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Body    = $body;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
//Attach an image file

//send the message, check for errors
if (!$mail->send()) {
    echo "<div class=\"callout alert\">Mailer Error: " . $mail->ErrorInfo ."</div>";
} else {
    echo "<div class=\"callout success\">".$strMessageSentSuccessfully."</div>";
}
}
echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
die;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"../dashboard/dashboard.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
}
else {

?>
        <form method="post" action="enrollment.php">
            <div class="grid-x grid-padding-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strCourseType?></label>
                        <input type="radio" name="course_type" class="button1" value="live" required onclick='document.getElementById("strCourseAndSchedule").style.display="block";' />
                        <?php echo $strLiveCourses?>
                        <input type="radio" name="course_type" class="button1" value="elearning" onclick='document.getElementById("elearning").style.display="block";' required />
                    <?php echo $strElearning?>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strCourses?></label>
                        <select size="1" name="strCourseAndSchedule" id="strCourseAndSchedule" style="display:none">
                            <option value=""><?php echo $strPick?></option>
                            <?php   
// Prepared statement pentru SELECT schedules
$stmt = mysqli_prepare($conn, "SELECT schedule_ID, course_ID, course_name, schedule_start_date, schedule_end_date 
    FROM elearning_courseschedules, elearning_courses 
    WHERE schedule_end_date >= ? AND schedule_course_ID=course_ID");
mysqli_stmt_bind_param($stmt, "s", $sdata);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$numar = mysqli_num_rows($result);

If ($numar==0)
{
echo "<option value=\"0\">$strNoRecordsFound</option>";
}
else {
        While ($rs1=ezpub_fetch_array($result)){
		$startdate=date('d M', strtotime($rs1['schedule_start_date']));
		$enddate=date('d M', strtotime($rs1['schedule_end_date']));
		$schedule_id_safe = (int)$rs1["schedule_ID"];
		$course_name_safe = htmlspecialchars($rs1["course_name"], ENT_QUOTES, 'UTF-8');
		$startdate_safe = htmlspecialchars($startdate, ENT_QUOTES, 'UTF-8');
		$enddate_safe = htmlspecialchars($enddate, ENT_QUOTES, 'UTF-8');
	?>
                            <option value="<?php echo $schedule_id_safe?>"><?php echo $course_name_safe?>:
                                <?php echo $startdate_safe?> - <?php echo $enddate_safe?> </option>
                            <?php
}}
?>
                        </select>
                        <select size="1" name="elearning" id="elearning" style="display:none">
                            <option value=""><?php echo $strPick?></option>
                            <?php
$esql="Select * FROM elearning_courses WHERE course_active=0 AND course_delivery=0 OR course_delivery=1";
$result2=ezpub_query($conn,$esql);
$numar2=ezpub_num_rows($result2,$esql); 
if ($numar2==0) {
    echo "<option value=\"0\">$strNoRecordsFound</option>";
}
else {
    While ($row2=ezpub_fetch_array($result2)){
        $course_ID_safe=(int)$row2["Course_id"];
        $course_name_safe=htmlspecialchars($row2["course_name"], ENT_QUOTES, 'UTF-8');
            echo "<option value=\"$course_ID_safe\">$course_name_safe</option>";
        }
    }
                    ?>
                        </select>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strInvoiceData . " " .$strCompany?></label>
                        <?php 
// Prepared statement pentru SELECT companies
$stmt = mysqli_prepare($conn, "SELECT * FROM site_companies WHERE company_siteaccount=?");
mysqli_stmt_bind_param($stmt, "i", $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$numar = mysqli_num_rows($result);

if ($numar==0)
{
echo "<div class=\"callout alert\">$strNoRecordsFound</div>";
}
else {
While ($row=ezpub_fetch_array($result)){
    $company_id_safe = (int)$row["company_id"];
    $company_name_safe = htmlspecialchars($row["company_name"], ENT_QUOTES, 'UTF-8');
?>

                        <input type="radio" name="account_company" class="button1"
                            value="<?php echo $company_id_safe?>" required
                            onclick='document.getElementById("iframe1").style.display="none";'>
                        <?php echo $company_name_safe?></label>
                    <?php }}?>
                </div>
                <div class="large-6 medium-6 small-6 cell">
                    <label>Alte opțiuni</label>
                    <label><input type="radio" name="account_company" class="button1" value="pf" required
                            onclick='document.getElementById("iframe1").style.display="none";'> Persoană
                        fizică</label>
                    <label><input type="radio" name="account_company" class="button1" value="0" required
                            onclick='document.getElementById("iframe1").style.display="block";'>Altă companie</label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell">
                    <div id="iframe1" src="../account/mycompanies.php?mode=new" style="display:none"></div>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    Am citit şi sunt de acord cu <a href="termeni.php" title="Termeni şi condiţii de utilizare">Termenii
                        şi condiţiile de utilizare</a>
                    <input type="checkbox" id="strAcord"
                        onclick="javascript:document.getElementById('btn_submit').style.display='block'; javascript:document.getElementById('strAcord').style.display='none';" />
                    <br />
                    <p align="center"><input type="submit" id="btn_submit" class="button" style="display:none;"
                            value="Trimite" /></p>
        </form>
    </div>
</div>
</div>
</div>
<?php
}
include '../bottom.php';
?>