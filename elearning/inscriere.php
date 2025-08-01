<?php
//update 16.07.2025
include '../settings.php';
include '../classes/common.php';
$strKeywords="Creare cont site CertPlus.ro";
$strDescription="Pagina de înscriere la cursuri";
$strPageTitle="Creare cont CertPlus.ro";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/autoload.php';

include '../header.php';
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$d = date("Y-m-d H:i:s");
 {
?>
  			  <div class="grid-x grid-padding-x">  
 <div class="large-12 medium-12 small-12 cell">

            <h1>Creare cont</h1>
    
<?php
$strPageTitle="Înscriere curs";

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();

//insert new user
$password=password_hash($_POST["student_password"], PASSWORD_DEFAULT);
	
	$mSQL = "INSERT INTO elearning_students(";
	$mSQL = $mSQL . "student_first_name,";
	$mSQL = $mSQL . "student_last_name,";
	$mSQL = $mSQL . "student_adresa,";
	$mSQL = $mSQL . "student_email,";
	$mSQL = $mSQL . "student_phone,";
	$mSQL = $mSQL . "student_oras,";
	$mSQL = $mSQL . "student_judet,";
	$mSQL = $mSQL . "student_password,";
	$mSQL = $mSQL . "student_enrollment)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["student_first_name"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["student_last_name"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["student_adresa"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["student_email"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["student_phone"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["student_oras"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["student_judet"]) . "', ";
	$mSQL = $mSQL . "'" .$password . "', ";
	$mSQL = $mSQL . "'" .$d . "') ";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{

//insert company if needed
	if ($_POST["student_company"]==0) {

		$companystudent=ezpub_inserted_id($conn);
		
	$mSQL = "INSERT INTO elearning_companies(";
	$mSQL = $mSQL . "company_name,";
	$mSQL = $mSQL . "company_ro,";
	$mSQL = $mSQL . "company_VAT,";
	$mSQL = $mSQL . "company_reg,";
	$mSQL = $mSQL . "company_address,";
	$mSQL = $mSQL . "company_student,";
	$mSQL = $mSQL . "company_city,";
	$mSQL = $mSQL . "company_county,";
	$mSQL = $mSQL . "company_bank,";
	$mSQL = $mSQL . "company_IBAN)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_name"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_ro"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_VAT"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_reg"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_address"]) . "', ";
	$mSQL = $mSQL . "'" .$companystudent . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_city"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_county"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_bank"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_IBAN"]) ."')";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }}


/// write and send email
//Cumpărător

$emailto=$_POST["student_email"];
$emailtoname=$_POST["student_first_name"] . " " . $_POST["student_last_name"];

If ($_POST["student_company"]==1) {
$cumparator="Date facturare: ".$_POST["student_last_name"] . " ". $_POST["student_first_name"] . "<br />";
$cumparator=$cumparator . "Adresa: ".$_POST["student_adresa"] . "<br />";
}
Else {
$cumparator="Date facturare: ".$_POST["company_name"] . "<br />";
$cumparator=$cumparator . "CUI: ".$_POST["company_VAT"] . "<br />";
$cumparator=$cumparator . "Reg. Comert.: ".$_POST["company_reg"] . "<br />";
$cumparator=$cumparator . "Adresa: ".$_POST["company_address"] . ", ".$_POST["company_city"].", ".$_POST["company_city"].".<br />";
$cumparator=$cumparator . "Banca: ".$_POST["company_bank"] . "<br />";
$cumparator=$cumparator . "IBAN: ".$_POST["company_IBAN"] . "<br />";
}
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
$emailbody=$emailbody . "<p>Stimate " .$_POST["student_first_name"]. " ".$_POST["student_last_name"]. ",<br>";
$emailbody=$emailbody . "Acesta este un mesaj de confirmare a înscrierii făcute de dumneavoastră pe site-ul ". $siteCompanyWebsite.". După activarea înscrierii, le veți putea modifica din contul dumneavoastră. </p>";
$emailbody=$emailbody . "
Vă mulțumim,<br />
<strong>$siteCompanyLegalName</strong><br />
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
    window.location = \"index.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";

include '../bottom.php';
}
Else {

?>
<script src="<?php echo $strSiteURL ?>/js/foundation/jquery.js"></script>
<script language="JavaScript" type="text/JavaScript">
$(document).ready(function() {
    $("#btn1").click(function() {  
	jQuery.ajax({
	url: "../common/cui.php",
	dataType: "json",
	data:'Cui='+$("#Cui").val(),
	type: "POST",
	  success: function(data) {
		  try {
           $('#factura_client_denumire').val((data["denumire"] || "").toUpperCase());
           $("#factura_client_CIF").val(data["cif"]);
           $("#factura_client_RO").val(data["tva"]);
           $("#factura_client_adresa").val(data["adresa"]);
           $("#factura_client_judet").val((data["judet"]).toUpperCase());
		   $("#factura_client_localitate").val((data["oras"]).toUpperCase());
           $("#factura_client_RC").val(data["numar_reg_com"]);
		   $("#loaderIcon").hide();   
		   }
catch(err) {
  document.getElementById("response").innerHTML = err.message;
}
        },
        error : function(){
           alert('Some error occurred!');
        }
    });
});
});
</script>
<script>
$(document).ready(function(){
	$("#search-box").keyup(function(){
		$.ajax({
		type: "POST",
		url: "../common/city_select.php",
		data:'keyword='+$(this).val(),
		beforeSend: function(){
			$("#search-box").css("background","#FFF url(../img/LoaderIcon.gif) no-repeat 165px");
		},
		success: function(data){
			 try {
			$("#suggesstion-box").show();
			$("#suggesstion-box").html(data);
			$("#search-box").css("background","#FFF");
		 }
catch(err) {
  document.getElementById("response").innerHTML = err.message;
}
        },
        error : function(){
           alert('Some error occurred!');
        }
		});
	});
});

function selectCity(val) {
	split_str=val.split(" - ");
$("#search-box").val(split_str[0]);
$("#judet").val(split_str[1]);
$("#suggesstion-box").hide();
}
</script>
<script>
$(document).ready(function () {
   $("#ConfirmPassword").on('keyup', function(){
    var password = $("#Password").val();
    var confirmPassword = $("#ConfirmPassword").val();
    if (password != confirmPassword)
        $("#CheckPasswordMatch").html("Parola nu se potrivește !").css("color","red");
    else
        $("#CheckPasswordMatch").html("Parola se potrivește !").css("color","green");
   });
});
</script>
<script> 
$(document).ready(function(){ 
  $("#email").change(function(){ 
    var uname = $("#email").val().trim(); 
    if(uname != ''){ 
      $("#uname_response").show(); 
      $.ajax({ 
        url: '../common/checkemail.php', 
        type: 'post', 
        data: {uname:uname}, 
        success: function(response){

           if(response > 0){ 
              $("#uname_response").html("!!!!!!Această adresă de email este deja folosită!!!!!").css("color","red"); 
           }else{ 
			alert(response); 
              $("#uname_response").html("Adresa este disponibilă pentru înregistrare.").css("color","green"); 
           } 
        },
		error: function() {
            alert('Error occured');
        }
      }); 
    }else{ 
      $("#uname_response").hide(); 
    } 
   }); 
});
</script>
<form Method="post"  id="form1" Action="inscriere.php" >

	 <div class="grid-x grid-padding-x">
		<div class="large-2 cell">  
	  <label><?php echo $strFirstName?></label>
	  <input name="student_first_name" Type="text" required />
	</div>
	  <div class="large-2 cell">  
	  <label><?php echo $strLastName?></label>
	  <input name="student_last_name" Type="text"  required />
	</div>
		<div class="large-2 cell"> 	  
	  <label><?php echo $strPhone?></label>
	  <input name="student_phone" Type="text"   required />
		</div>
				<div class="large-2 cell"> 
	  <label><?php echo $strAddress?></label>
	  <input name="student_adresa" Type="text"  required />
		</div>
 <div class="large-2 cell">  	
   <label><?php echo $strCity?></label>
   <input type="text" name="student_oras" id="search-box" placeholder="<?php echo $strCity?>" required/>
  	<div id="suggesstion-box" class="suggesstion-box"></div></div>
<div class="large-2 cell"> 
   <label><?php echo $strCounty?></label>
   <input type="text" name="student_judet" id="judet" placeholder="<?php echo $strCounty?>" required />
		</div>
	</div>
 <div class="grid-x grid-padding-x">
			<div class="large-4 cell"> 				  
	  <label><?php echo $strEmail?></label>
	  <input name="student_email" Type="email" id="email"  required/>
	  <div id="uname_response" class="response"></div> 
		</div>
			<div class="large-4 cell"> 				  
	  <label><?php echo $strPassword?></label>
	  <input name="student_password" Type="password" id="Password" required/>
		</div>
			<div class="large-4 cell"> 				  
	  <label><?php echo $strPassword?></label>
	  <input name="student_password_confirm" Type="password"  id="ConfirmPassword" required />
	  <div style="margin-top: 7px;" id="CheckPasswordMatch"></div>
		</div>
	</div>
  <div class="grid-x grid-padding-x">
		<div class="large-12 cell">

	<label><input type="radio" name="student_company" class="button1" value="1" onclick='document.getElementById("iframe1").style.display="none";'> Facturare pe persoană fizică</label>
	<label><input type="radio" name="student_company" class="button1" value="0" onclick='document.getElementById("iframe1").style.display="block";'>Facturare pe persoană juridică</label>
<div id="iframe1" style="display:none">

	 <div class="grid-x grid-padding-x">
	  	<div class="large-6 cell">
<div id="response"></div>
<div class="input-group">
  <span class="input-group-label"><?php echo $strCompanyVAT?></span>
  <input class="input-group-field" type="text" name="Cui" id="Cui" placeholder="<?php echo $strEnterVATNumber?>">
  <div class="input-group-button">
    <button id="btn1" class="button success" ><i class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
  </div>
</div>
	  </div>
	  </div>
	  	   <div class="grid-x grid-padding-x ">
              <div class="large-4 medium-4 cell">
                <label><?php echo $strCompany?></label>
                <input type="text"  name="company_name" id="company_name" value="" required/>
				</div>
				<div class="large-1 medium-1 cell">
                <label><?php echo $strCompanyFA?></label>
                <input type="text"  name="company_ro" id="company_ro" value="" required/>
				</div>				
				<div class="large-3 medium-3 cell">
                <label><?php echo $strCompanyVAT?></label>
                <input type="text"  name="company_VAT" id="company_VAT" value="" required />
				</div>
				<div class="large-4 medium-4 cell">
                <label><?php echo $strCompanyRC?></label>
                <input type="text"  name="company_reg" id="company_reg" value=""  required />
				</div>
				</div>
						  <div class="grid-x grid-padding-x ">
               <div class="large-4 medium-4 cell">
			   <label><?php echo $strAddress?></label>
			  <input type="text"  name="company_address" id="company_address" value="" required/>
</div>	
               <div class="large-4 medium-4 cell">
			   <label><?php echo $strCity?></label>
			  <input type="text"  name="company_city" id="company_city" value="" required />
</div>            
				<div class="large-4 medium-4 cell">
			   <label><?php echo $strCounty?></label>
			  <input type="text"  name="company_county" id="company_county" value="" required />
</div>			  
</div>	
			  <div class="grid-x grid-padding-x ">
               <div class="large-6 medium-6 cell">
			   <label><?php echo $strBank?></label>
			  <input type="text"  name="company_bank" id="company_bank" value=""/>
</div>	
               <div class="large-6 medium-6 cell">
			   <label><?php echo $strCompanyIBAN?></label>
			  <input type="text"  name="company_IBAN" id="company_IBAN" value=""/>
</div>			  
</div>	
</div>
       <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">

	Am citit şi sunt de acord cu <a href="termeni.php" title="Termeni şi condiţii de utilizare">Termenii şi condiţiile de utilizare</a> 
	<input type="checkbox" id="strAcord" onclick="javascript:document.getElementById('btn_submit').style.display='block'; javascript:document.getElementById('strAcord').style.display='none';" />
<p align="center"><input type="submit" class="button" id="btn_submit" style="display:none;" value="Trimite" /></p>
</div>
</div>
</form>
</div>
</div>
<?php
}
 }
include '../bottom.php';
?>