<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
$url="selectcase.php";
$strKeywords="Comandă proceduri HACCP";
$strDescription="Pagina finalizare a comenzii consultanta-haccp.ro";
$strPageTitle="Trimite comanda";
include '../header.php';
if (!isSet($_SESSION['$buyer'])) {
header("location:$strSiteURL". "404.php");
}
Else {

$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$buyer=$_SESSION['$buyer'];
echo "<div class=\"row\">
<div class=\"large-12 columns\">";
echo "<h1>$strPageTitle</h1>";

If (isSet($_GET['oID']) AND is_numeric($_GET['oID'])) {
$oID=$_GET['oID'];
		echo "<table width=\"100%\">
  <thead>
    <th width=\"50%\">$strProduct</th><th width=\"10%\">$strProductPrice</th><th width=\"20%\">$strQuantity</th><th width=\"10%\">$strTotalPrice</th><th width=\"10%\">$strVAT</th></thead>";
	$query="SELECT * FROM magazin_comenzi where comanda_utilizator='$buyer' AND comanda_status=0";
$result=ezpub_query($conn,$query);
$orderr=ezpub_fetch_array($result);
$oID=$orderr['comanda_ID'];
$itemq="SELECT * FROM magazin_articole where articol_idcomanda=$oID";
$resulti=ezpub_query($conn,$itemq);
$ordertotal=0;
While ($rowi=ezpub_fetch_array($resulti)) {
$queryp="SELECT * FROM magazin_produse WHERE produs_id='$rowi[articol_produs]'";
$resultp=ezpub_query($conn,$queryp);
$row=ezpub_fetch_array($resultp);
If ($row["produs_dpret"]!=='0.0000')
{
$unitprice=$row['produs_dpret'];
}
Else
{
	$unitprice=$row['produs_pret'];
}
$vatrat=$row["produs_tva"]/100;
$vatprc=$vatrat+1;
$quantity=$rowi['articol_cantitate'];
$totalprice=$unitprice*$quantity;
$ordertotal=$ordertotal+$totalprice;
$VAT=$totalprice*$vatrat;
echo "<tr><td>$row[produs_nume]</td><td align=\"right\">".romanize($unitprice)."</td><td align=\"right\">$quantity &nbsp;
<a href=\"item.php?id=$rowi[articol_id]&action=add\"><i class=\"fas fa-plus\"></i></a>
<a href=\"item.php?id=$rowi[articol_id]&action=decrease\"><i class=\"fas fa-minus\"></i></a>
<a href=\"item.php?id=$rowi[articol_id]&action=delete\"><i class=\"far fa-trash-alt\"></i></a>
</td><td align=\"right\">".romanize($totalprice)."</td>
</td><td align=\"right\">".romanize($VAT)."</td></tr>";
}
$totalinterim=$ordertotal*$vatprc;
$totalVAT=$ordertotal*$vatrat;
$totalorder=$ordertotal;
if ($paidtransport=="1" )
{
If ($totalinterim<=$transportlimit){
	$transportVAT=$transportprice*$transportvatrat;
echo "<tr><td colspan=\"3\">$strTransport</td><td align=\"right\">".romanize($transportprice)."</td><td align=\"right\">".romanize($transportVAT)."</td></tr>";	
$totalorder=$ordertotal+$transportprice;
$orderVAT=$ordertotal*$vatrat;
$totalVAT=$orderVAT+$transportVAT;
}}
$finalprice=$totalorder+$totalVAT;

echo "<tr><td colspan=\"3\">$strTotals</td><td align=\"right\">".romanize($totalorder)."</td><td align=\"right\">".romanize($totalVAT)."</td></tr>";
echo "<tr><td colspan=\"4\">$strTotal</td><td align=\"right\">".romanize($finalprice)."</td></tr></table>";
		?>
		</div>
		</div>
		
		<div class="large-12 columns"  role="content">
<h3><?php echo $strInvoiceData ?></h3>
			      <div class="grid-x grid-padding-x">
 <div class="large-12 cell">
 <div class="callout alert"><?php echo $strDisclaimerCompanies?></div>  
 </div>
 </div>
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
<form Method="post" Action="sendorder.php?oID=<?php echo $oID ?>&action=new" >
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
                <label><?php echo $strClient?></label>
                <input type="text"  name="factura_client_denumire" id="factura_client_denumire" value="" />
				</div>
				<div class="large-1 medium-1 cell">
                <label><?php echo $strCompanyFA?></label>
                <input type="text"  name="factura_client_RO" id="factura_client_RO" value=""/>
				</div>				
				<div class="large-3 medium-3 cell">
                <label><?php echo $strCompanyVAT?></label>
                <input type="text"  name="factura_client_CIF" id="factura_client_CIF" value=""  />
				</div>
				<div class="large-4 medium-4 cell">
                <label><?php echo $strCompanyRC?></label>
                <input type="text"  name="factura_client_RC" id="factura_client_RC" value=""  />
				</div>
				</div>
						  <div class="grid-x grid-padding-x ">
               <div class="large-4 medium-4 cell">
			   <label><?php echo $strAddress?></label>
			  <input type="text"  name="factura_client_adresa" id="factura_client_adresa" value="" />
</div>	
               <div class="large-4 medium-4 cell">
			   <label><?php echo $strCity?></label>
			  <input type="text"  name="factura_client_localitate" id="factura_client_localitate" value="" />
</div>            
				<div class="large-4 medium-4 cell">
			   <label><?php echo $strCounty?></label>
			  <input type="text"  name="factura_client_judet" id="factura_client_judet" value=""/>
</div>			  
</div>	
			  <div class="grid-x grid-padding-x ">
               <div class="large-6 medium-6 cell">
			   <label><?php echo $strBank?></label>
			  <input type="text"  name="factura_client_banca" id="factura_client_banca" value=""/>
</div>	
               <div class="large-6 medium-6 cell">
			   <label><?php echo $strCompanyIBAN?></label>
			  <input type="text"  name="factura_client_IBAN" id="factura_client_IBAN" value=""/>
</div>			  
</div>	
			      <div class="grid-x grid-padding-x">
 <div class="large-12 cell">
 <h3><?php echo $strCompanyRepresentative?></h3>
 </div>
 </div>
      <div class="grid-x grid-padding-x">
		<div class="large-2 cell">  
	  <label><?php echo $strFirstName?></label>
	  <input name="cumparator_prenume" Type="text" size="30"  required />
	</div>
	  <div class="large-2 cell">  
	  <label><?php echo $strLastName?></label>
	  <input name="cumparator_nume" Type="text" size="30" required />
	</div>
		<div class="large-2 cell"> 
	  <label><?php echo $strEmail?></label>
	  <input name="cumparator_email" Type="email" size="30" required />
		</div>
		<div class="large-2 cell"> 	  
	  <label><?php echo $strPhone?></label>
	  <input name="cumparator_telefon" Type="text" size="30"  required />
		</div>
 <div class="large-2 cell">  	
   <label><?php echo $strCity?></label>
   <input type="text" name="cumparator_oras" id="search-box" placeholder="<?php echo $strCity?>" />
  	<div id="suggesstion-box" class="suggesstion-box"></div></div>
<div class="large-2 cell"> 
   <label><?php echo $strCounty?></label>
   <input type="text" name="cumparator_judet" id="judet" placeholder="<?php echo $strCounty?>" />
   			
				</div>
	</div>
 <div class="grid-x grid-padding-x">
			<div class="large-12 cell"> 				  
	  <label><?php echo $strAddress?></label>
	  <input name="cumparator_adresa" Type="text" size="30"  />
		</div>
	</div>

	      <div class="grid-x grid-padding-x">
		<div class="large-12 cell"> 	Am citit şi sunt de acord cu <a href="termeni.php" title="Termeni şi condiţii de utilizare">Termenii şi condiţiile de utilizare</a> 
		
		<input type="checkbox" id="strAcord" onclick="javascript:document.getElementById('btn_submit').style.display='block'; javascript:document.getElementById('strAcord').style.display='none';" />
</div>
</div>
      <div class="grid-x grid-padding-x">
		<div class="large-12 cell"> <p align="center"><input type="submit" id="btn_submit" class="button" style="display:none;" value="<?php echo $strSubmit?>" /></p>
</div>
</div>
</form>

</div>
<?php	
}
Else{
echo "<div data-alert class=\"alert-box warning round\">$strThereWasAnError<a href=\"#\" class=\"close\">&times;</a></div>"; 
include ('bottom.php');
die;}
}//ends first else
	echo "</div></div><hr />";
include '../bottom.php';
?>