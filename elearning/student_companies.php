<?php
include '../settings.php';
include '../classes/common.php';
$strDescription="Modificare date de facturare";
$strPageTitle="Creare cont MedReport";

if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/elearning/login.php?message=MLF");
}
include '../dashboard/header.php';
$strPageTitle=$strInvoiceData;
$uid=$_SESSION['uid'];
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$d = date("Y-m-d H:i:s");

If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){
$nsql="DELETE FROM elearning_companies WHERE company_id=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"student_myprofile.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}
?>
    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">

            <h3><?php echo $strInvoiceData?></h3>
    
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST'){

If ($_GET['mode']=="new"){
//insert new company
	$mSQL = "INSERT INTO elearning_companies(";
	$mSQL = $mSQL . "company_name,";
	$mSQL = $mSQL . "company_VAT,";
	$mSQL = $mSQL . "company_ro,";
	$mSQL = $mSQL . "company_reg,";
	$mSQL = $mSQL . "company_address,";
	$mSQL = $mSQL . "company_city,";
	$mSQL = $mSQL . "company_county,";
	$mSQL = $mSQL . "company_bank,";
	$mSQL = $mSQL . "company_student,";
	$mSQL = $mSQL . "company_IBAN)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_name"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_VAT"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_ro"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_reg"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_address"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_city"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_county"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_bank"]) . "', ";
	$mSQL = $mSQL . "'" .$uid . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["company_IBAN"]) ."')";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
  Else {
echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"student_myprofile.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}// ends else
}//ends new
Else
{// edit
$strWhereClause = " WHERE elearning_companies.company_ID=" . $_GET["cID"] . ";";
$query= "UPDATE elearning_companies SET elearning_companies.company_name='" .str_replace("'","&#39;",$_POST["company_name"]) . "' ," ;
$query= $query . "elearning_companies.company_VAT='" .str_replace("'","&#39;",$_POST["company_VAT"]) . "' ," ;
$query= $query . "elearning_companies.company_ro='" .str_replace("'","&#39;",$_POST["company_ro"]) . "' ," ;
$query= $query . "elearning_companies.company_reg='" .str_replace("'","&#39;",$_POST["company_reg"]) . "' ," ;
$query= $query . "elearning_companies.company_address='" .str_replace("'","&#39;",$_POST["company_address"]) . "' ," ;
$query= $query . "elearning_companies.company_city='" .str_replace("'","&#39;",$_POST["company_city"]) . "' ," ;
$query= $query . "elearning_companies.company_county='" .str_replace("'","&#39;",$_POST["company_county"]) . "' ," ;
$query= $query . "elearning_companies.company_bank='" .str_replace("'","&#39;",$_POST["company_bank"]) . "' ," ;
$query= $query . " elearning_companies.company_IBAN='" .str_replace("'","&#39;",$_POST["company_IBAN"]) . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"student_myprofile.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
} //ends edit
}// ends edit post
}// ends post
Else {
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
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
           $('#company_name').val((data["denumire"] || "").toUpperCase());
           $("#company_VAT").val(data["cif"]);
           $("#company_ro").val(data["tva"]);
           $("#company_address").val(data["adresa"]);
           $("#company_county").val((data["judet"]).toUpperCase());
		   $("#company_city").val((data["oras"]).toUpperCase());
           $("#company_reg").val(data["numar_reg_com"]);
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
<p><a href="student_myprofile.php" class="button"><?php echo $strBack?></a></p>
<form method="Post" Action="student_companies.php?mode=new" >
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
      <div class="grid-x grid-padding-x">
		<div class="large-12 cell"> <p align="center">
			<input type="submit" class="button"  value="<?php echo $strSubmit?>" /></p>
</div>
</div>
</form>
<?php
}
Else {
	?>
	<p><a href="student_myprofile.php" class="button"><?php echo $strBack?></a></p>
<form Method="post" id="users" Action="student_companies.php?mode=edit&cID=<?php echo $_GET["cID"]?>" >
<?php
$query="SELECT * FROM elearning_companies WHERE company_id=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
  <div class="grid-x grid-padding-x ">
              <div class="large-4 medium-4 cell">
                <label><?php echo $strCompany?></label>
                <input type="text"  name="company_name" id="company_name" value="<?php echo $row["company_name"]?>" required />
				</div>
				<div class="large-1 medium-1 cell">
                <label><?php echo $strCompanyFA?></label>
                <input type="text"  name="company_ro" id="company_ro" value="<?php echo $row["company_ro"]?>" required/>
				</div>				
				<div class="large-3 medium-3 cell">
                <label><?php echo $strCompanyVAT?></label>
                <input type="text"  name="company_VAT" id="company_VAT" value="<?php echo $row["company_VAT"]?>" required />
				</div>
				<div class="large-4 medium-4 cell">
                <label><?php echo $strCompanyRC?></label>
                <input type="text"  name="company_reg" id="company_reg" value="<?php echo $row["company_reg"]?>" required />
				</div>
				</div>
						  <div class="grid-x grid-padding-x ">
               <div class="large-4 medium-4 cell">
			   <label><?php echo $strAddress?></label>
			  <input type="text"  name="company_address" id="company_address" value="<?php echo $row["company_address"]?>" />
</div>	
               <div class="large-4 medium-4 cell">
			   <label><?php echo $strCity?></label>
			  <input type="text"  name="company_city" id="company_city" value="<?php echo $row["company_city"]?>" />
</div>            
				<div class="large-4 medium-4 cell">
			   <label><?php echo $strCounty?></label>
			  <input type="text"  name="company_county" id="company_county" value="<?php echo $row["company_county"]?>"/>
</div>			  
</div>	
			  <div class="grid-x grid-padding-x ">
               <div class="large-6 medium-6 cell">
			   <label><?php echo $strBank?></label>
			  <input type="text"  name="company_bank" id="company_bank" value="<?php echo $row["company_bank"]?>"/>
</div>	
               <div class="large-6 medium-6 cell">
			   <label><?php echo $strCompanyIBAN?></label>
			  <input type="text"  name="company_IBAN" id="company_IBAN" value="<?php echo $row["company_IBAN"]?>"/>
</div>			  
</div>	
      <div class="grid-x grid-padding-x">
		<div class="large-12 cell"> <p align="center"><input type="submit" class="button"  value="<?php echo $strSubmit?>" /></p>
</div>
</div>
</form>
<?php }?>
</div>
</div>

	<?php
 }
include '../bottom.php';
?>