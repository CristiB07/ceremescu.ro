<?php
include '../settings.php';
    if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 
include '../classes/common.php';
$strKeywords="Înscriere curs CertPlus.ro";
$strPageTitle="Înscriere curs CertPlus.ro";
$strDescription="Pagina de înscriere la curs CertPlus.ro";
include '../dashboard/header.php';
$uid= $_SESSION['uid'];
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$d = date("Y-m-d H:i:s");
?>
 <div class="row">
      <div class="twelve columns">
        <div class="blog_post">
          <!-- Begin Blog Post -->
          <div class="heading_dots_grey">
            <h3><span class="heading_bg">Înscriere curs</span></h3>
          </div>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
if ($_POST["student_company"]==0) {
	$mSQL = "INSERT INTO elearning_companies(";
	$mSQL = $mSQL . "company_name,";
	$mSQL = $mSQL . "company_VAT,";
	$mSQL = $mSQL . "company_reg,";
	$mSQL = $mSQL . "company_address,";
	$mSQL = $mSQL . "company_bank,";
	$mSQL = $mSQL . "company_student,";
	$mSQL = $mSQL . "company_IBAN)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_name"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_VAT"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_reg"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_address"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_bank"]) . "', ";
	$mSQL = $mSQL . "'" .$uid . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_IBAN"]) ."')";
				
//It executes the SQL
if (!ezpub_query($mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }}
//insert new user

	$sql="SELECT schedule_course_ID FROM elearning_courseschedules WHERE schedule_ID='".$_POST['strCourseAndSchedule']."'";
		$result=ezpub_query($conn, $sql);
		$course=ezpub_fetch_array($result);
		$course_ID=$course["schedule_course_ID"];
		if ($_POST["student_company"]==0) {
		$studentinvoice=$_POST["company_VAT"];}
		Else {
		$studentinvoice=0;
		}
	

	$mSQL = "INSERT INTO elearning_enrollments(";
	$mSQL = $mSQL . "elearning_enrollments_stud_id,";
	$mSQL = $mSQL . "elearning_enrollments_course_id,";
	$mSQL = $mSQL . "elearning_enrollments_elearning_courseschedule_id,";
	$mSQL = $mSQL . "elearning_enrollments_date,";
	$mSQL = $mSQL . "elearning_enrollments_active)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$uid . "', ";
	$mSQL = $mSQL . "'" .$course_ID . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["strCourseAndSchedule"]) . "', ";
	$mSQL = $mSQL . "'" .$d . "', ";
	$mSQL = $mSQL . "'" . 0 . "') ";

$query="SELECT * FROM elearning_enrollments where elearning_enrollments_course_id=$course_ID";
	$result=ezpub_query($conn,$query);
$nume=ezpub_num_rows($result,$query);
$enrollmentID=$nume;
				//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{

echo "<div class=\"callout success\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"user/dashboard.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include 'bottom.php';
/// write and send email
//coursedetails
$query="SELECT * FROM elearning_courses WHERE Course_id=$course_ID";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
$numecurs=$row["course_name"];
$locationID=$row["course_location"];
If ($row["course_discount"]==""){
$pretcurs=$row["course_price"];}
Else {
$pretcurs=$row["course_discount"];}
//schedule details
$query="SELECT * FROM elearning_courseschedules WHERE schedule_ID=$_POST[strCourseAndSchedule]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
		$startdate=date('d M', strtotime($row['schedule_start_date']));
		$enddate=date('d M', strtotime($row['schedule_end_date']));
//Cumpărător
$query="SELECT * FROM elearning_students WHERE student_id=$uid";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
$studentemail=$row["student_email"];
$student_first_name=$row["student_first_name"];
$student_last_name=$row["student_last_name"];

If ($_POST["student_company"]==1) {
$cumparator="Cumpărător: ". $row["student_last_name"] . " ". $row["student_first_name"] . "<br />";
$cumparator=$cumparator . "CNP: ". $row["student_cnp"] . "<br />";
$cumparator=$cumparator . "Adresa: ". $row["student_adresa"] . "<br />";
}
ElseIf ($_POST["student_company"]==0) {
$cumparator="Cumpărător: ". $_POST["company_name"] . "<br />";
$cumparator=$cumparator . "CUI: ".$_POST["company_VAT"] . "<br />";
$cumparator=$cumparator . "Reg. Comert.: ".$_POST["company_reg"] . "<br />";
$cumparator=$cumparator . "Adresa: ".$_POST["company_address"] . "<br />";
$cumparator=$cumparator . "Banca: ".$_POST["company_bank"] . "<br />";
$cumparator=$cumparator . "IBAN: ".$_POST["company_IBAN"] . "<br />";
}
Else  {
$query="SELECT * FROM elearning_companies WHERE company_student=$uid and company_id=$_POST[student_company]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);

$cumparator="Cumpărător: ". $row["company_name"] . "<br />";
$cumparator=$cumparator . "CUI: ". $row["company_VAT"] . "<br />";
$cumparator=$cumparator . "Reg. Comert.: ". $row["company_reg"] . "<br />";
$cumparator=$cumparator . "Adresa: ". $row["company_address"] . "<br />";
$cumparator=$cumparator . "Banca: ". $row["company_bank"] . "<br />";
$cumparator=$cumparator . "IBAN: ". $row["company_IBAN"] . "<br />";
}
//Furnizor

$query="SELECT * FROM locations WHERE location_id=$locationID";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
$invoicecode=$row["location_code"];
$furnizor="Furnizor: ". $row["location_name"] . "<br />";
$furnizor=$furnizor . "CUI: ". $row["location_VAT_number"] . "<br />";
$furnizor=$furnizor . "Reg. Comert.: ". $row["location_registration_number"] . "<br />";
$furnizor=$furnizor . "Adresa: ". $row["location_address"] . "<br />";
$furnizor=$furnizor . "Banca: ". $row["location_bank"] . "<br />";
$furnizor=$furnizor . "IBAN: ". $row["location_IBAN_number"] . "<br />";
$furnizor=$furnizor . "Telefon: ". $row["location_phone"] . "<br />";
$furnizor=$furnizor . "Email: ". $row["location_email"] . "<br />";
$furnizor=$furnizor . "Persoană de contact: ". $row["location_contact_person"] . "<br />";
$supplyeremail=$row["location_email"];

$emailto=$studentemail;
$emailfrom="CertPlus.ro <office@medreport.ro>";
$to = $emailto;
$from = $emailfrom;
$cc = $supplyeremail;
$bcc="info@cursurispecializare.ro";
$subject="Înscriere curs " . $numecurs;

$HTMLBody="<html>";
$HTMLBody=$HTMLBody . "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$HTMLBody=$HTMLBody . "<style>BODY {MARGIN-TOP: 10px; MARGIN-BOTTOM: 10px; MARGIN-LEFT: 10px;MARGIN-RIGHT: 10px;FONT-SIZE: 12px;FONT-FAMILY: arial,helvetica,sans-serif; PADDING: 0px;}";
$HTMLBody=$HTMLBody . "TD {FONT-SIZE: 12px; FONT-FAMILY: arial,helvetica,sans-serif; COLOR: #000000;}";
$HTMLBody=$HTMLBody . "TH {FONT-SIZE: 8px; FONT-FAMILY: arial,helvetica,sans-serif }";
$HTMLBody=$HTMLBody . "H1 {FONT-SIZE: 20px}";
$HTMLBody=$HTMLBody . "TABLE,IMG,A {BORDER: 0px;}";
$HTMLBody=$HTMLBody . "</style>";
$HTMLBody=$HTMLBody . "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$HTMLBody=$HTMLBody . "</head><body>";

$HTMLBody=$HTMLBody . "<a href=\"http://www.medreport.ro\"><img src=\"http://www.medreport.ro/images/logo.png\" title=\"CertPlus.ro\" /></a>";
$HTMLBody=$HTMLBody . "<p>Stimate " . $student_first_name. " ". $student_last_name. ",<br>";
$HTMLBody=$HTMLBody . "Acesta este un mesaj de confirmare a înscrierii făcute de dumneavoastră la cursul ". $numecurs .", în perioada ". $startdate ."-".$enddate.". Mai jos aveți factura proforma. </p>";
$HTMLBody=$HTMLBody . "<H2>Factura proforma CNST00" . $enrollmentID . "</H2>";
$HTMLBody=$HTMLBody . "<table border=\"1\" align=\"center\" width=\"75%\">";
$HTMLBody=$HTMLBody . "<tr>";
$HTMLBody=$HTMLBody . "<td colspan=\"2\">";
$HTMLBody=$HTMLBody . $furnizor;
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "<td colspan=\"2\">";
$HTMLBody=$HTMLBody .  $cumparator;
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "</tr>";

$HTMLBody=$HTMLBody . "<tr>";
$HTMLBody=$HTMLBody . "<td>";
$HTMLBody=$HTMLBody . "Produs";
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "<td>";
$HTMLBody=$HTMLBody . "Cantitate";
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "<td>";
$HTMLBody=$HTMLBody . "Valoare";
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "<td>";
$HTMLBody=$HTMLBody . "TVA";
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "</tr>";
$HTMLBody=$HTMLBody . "<tr>";
$HTMLBody=$HTMLBody . "<td>";
$HTMLBody=$HTMLBody . $numecurs .", perioada ". $startdate ."-".$enddate;
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "<td>";
$HTMLBody=$HTMLBody . "1";
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "<td>";
$HTMLBody=$HTMLBody . $pretcurs;
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "<td>";
$HTMLBody=$HTMLBody . "0";
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "</tr>";
$HTMLBody=$HTMLBody . "<tr>";
$HTMLBody=$HTMLBody . "<td colspan=\"2\">";
$HTMLBody=$HTMLBody . "TOTAL ";
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "<td colspan=\"2\">";
$HTMLBody=$HTMLBody . $pretcurs;
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "</tr>";
$HTMLBody=$HTMLBody . "</table>";
$HTMLBody=$HTMLBody . "<p>Înscrierea și locul dumneavoastră la curs vor fi confirmate după ce veți plăti această factura proforma către furnizor. Pentru orice detalii legate de plată, vă rugăm să contactați furnizorul folosind datele de mai sus.
</p> Vă mulțumim,<br />
<strong>Consaltis Consultanță și Audit</strong><br />
Ion Câmpineanu nr. 11, Sector 1, București <br />
Tel./Fax: 031 432 7883;<br />
GSM: 0722 111 703<br />
office@consaltis.ro<br />
www.consaltis.ro <br />
";

$HTMLBody=$HTMLBody . "</body>";
$HTMLBody=$HTMLBody . "</html>";
$body=$HTMLBody;

$SMTPMail = new SMTPClient ($SmtpServer, $SmtpPort, $SmtpUser, $SmtpPass, $from, $to, $cc, $bcc, $subject, $body);
$SMTPChat = $SMTPMail->SendMail();
die;
}}
Else {

?>
<script language="JavaScript" type="text/JavaScript">
$("#users").validate();
</script>
<form Method="post" id="users" Action="enrolment.php" >
<table summary="<?php echo $strEnrollment?>" width="100%">
 	   <TR> 
      <TD><strong><?php echo $strCourse?></strong></TD>
     <TD> <select size="1"  name="strCourseAndSchedule" required>
          <option value=""><?php echo $strPick?></option>
          <?php   $sql="SELECT schedule_ID, course_ID, course_name, schedule_start_date, schedule_end_date FROM elearning_courseschedules, elearning_courses 
		  WHERE schedule_end_date >= '$sdata' AND schedule_course_ID=course_ID";
	 
        $result = ezpub_query($conn,$sql);
	    While ($rs1=ezpub_fetch_array($result)){
		$startdate=date('d M', strtotime($rs1['schedule_start_date']));
		$enddate=date('d M', strtotime($rs1['schedule_end_date']));
	?>
          <option value="<?php echo $rs1["schedule_ID"]?>"><?php echo $rs1["course_name"]?>: <?php echo $startdate?> - <?php echo $enddate?> </option>
          <?php
}?>
        </select></TD>
    </TR>
<TR>
	<TD colspan="2" Align="Center">
	<?php 
$query="SELECT * FROM elearning_companies WHERE company_student=$uid";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo $strNoRecordsFound;
}
Else {
While ($row=ezpub_fetch_array($result)){
?>
	<label><input type="radio" name="student_company" class="button1" value="<?php echo $row["company_id"]?>" required onclick='document.getElementById("iframe1").style.display="none";'> <?php echo $row["company_name"]?></label>
<?php }}?>
	<label><input type="radio" name="student_company" class="button1" value="1" required onclick='document.getElementById("iframe1").style.display="none";'> Persoană fizică</label>
	<label><input type="radio" name="student_company" class="button1" value="0" required onclick='document.getElementById("iframe1").style.display="block";'>Alte date de facturare</label>
</TD>
</TR>
</table>
<div id="iframe1" style="display:none">
<table summary="<?php echo $strCompany?>">
<TR> 
	  <TD width="22%"><strong><?php echo $strCompanyName?></strong></TD>
	  <TD width="78%"><INPUT name="company_name" Type="text" size="30"  /></TD>
</TR>
<TR> 
	  <TD width="22%"><strong><?php echo $strCompanyVAT?></strong></TD>
	  <TD width="78%"><INPUT name="company_VAT" Type="text" size="30" /></TD>
</TR>
<TR> 
	  <TD width="22%"><strong><?php echo $strCompanyRC?></strong></TD>
	  <TD width="78%"><INPUT name="company_reg" Type="text" size="30" /></TD>
</TR>
<TR> 
	  <TD width="22%"><strong><?php echo $strCompanyAddress?></strong></TD>
	  <TD width="78%"><INPUT name="company_address" Type="text" size="30"  /></TD>
</TR>
<TR> 
	  <TD width="22%"><strong><?php echo $strCompanyBank?></strong></TD>
	  <TD width="78%"><INPUT name="company_bank" Type="text" size="30"  /></TD>
</TR>
<TR> 
	  <TD width="22%"><strong><?php echo $strCompanyIBAN?></strong></TD>
	  <TD width="78%"><INPUT name="company_IBAN" Type="text" size="30"  /></TD>
</TR>
	</table>
</div>
<table summary="<?php echo $strEnrollment?>" width="100%">
<TR>
	<TD>Am citit şi sunt de acord cu <a href="termeni.php" title="Termeni şi condiţii de utilizare">Termenii şi condiţiile de utilizare</a> </TD><TD><input type="checkbox" id="strAcord" onclick="javascript:document.getElementById('btn_submit').style.display='block'; javascript:document.getElementById('strAcord').style.display='none';" />
</TD>
</TR>
<TR>
	<TD colspan="2" Align="Center"><input type="submit" id="btn_submit" style="display:none;" value="Trimite" />
</TD>
</TR>
</TABLE>
</FORM>
</div>
</div>
</div>
<?php
}
include '../bottom.php';
?>