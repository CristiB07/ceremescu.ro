<?php
//update 08.01.2025
include '../settings.php';
include '../classes/common.php';

include '../classes/paginator.class.php';
$strPageTitle="Administrare abonamente";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM clienti_abonamente WHERE abonament_ID=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-2);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
If ($_GET['mode']=="new"){
//insert new user
$lunifacturare= implode(';',$_POST['abonament_client_lunafacturare']);

	$mSQL = "INSERT INTO clienti_abonamente(";
	$mSQL = $mSQL . "abonament_client_ID,";
	$mSQL = $mSQL . "abonament_client_valoare,";
	$mSQL = $mSQL . "abonament_client_valuta,";
	$mSQL = $mSQL . "abonament_client_frecventa,";
	$mSQL = $mSQL . "abonament_client_aloc,";
	$mSQL = $mSQL . "abonament_client_detalii,";
	$mSQL = $mSQL . "abonament_client_unitate,";
	$mSQL = $mSQL . "abonament_client_termen,";
	$mSQL = $mSQL . "abonament_client_zifacturare,";
	$mSQL = $mSQL . "abonament_client_activ,";
	$mSQL = $mSQL . "abonament_client_email,";
	$mSQL = $mSQL . "abonament_client_an,";
	$mSQL = $mSQL . "abonament_client_BU,";
	$mSQL = $mSQL . "abonament_client_sales,";
	$mSQL = $mSQL . "abonament_client_anexa,";
	$mSQL = $mSQL . "abonament_client_pdf,";
	$mSQL = $mSQL . "abonament_client_lunafacturare,";
	$mSQL = $mSQL . "abonament_client_contract)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$_POST["abonament_client_ID"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["abonament_client_valoare"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["abonament_client_valuta"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["abonament_client_frecventa"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["abonament_client_aloc"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["abonament_client_detalii"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["abonament_client_unitate"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["abonament_client_termen"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["abonament_client_zifacturare"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["abonament_client_activ"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["abonament_client_email"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["abonament_client_an"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["abonament_client_BU"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["abonament_client_sales"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["abonament_client_anexa"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["abonament_client_pdf"] . "', ";
	$mSQL = $mSQL . "'" .$lunifacturare . "', ";
	$mSQL = $mSQL . "'" .$_POST["abonament_client_contract"] . "') ";
	echo $mSQL;		
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-2);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{// edit
$lunifacturare= implode(';',$_POST['abonament_client_lunafacturare']);

$strWhereClause = " WHERE clienti_abonamente.abonament_ID=" . $_GET["cID"] . ";";
$query= "UPDATE clienti_abonamente SET clienti_abonamente.abonament_client_ID='" .$_POST["abonament_client_ID"] . "' ," ;
$query= $query . " clienti_abonamente.abonament_client_valoare='" .$_POST["abonament_client_valoare"] . "' ," ;
$query= $query . " clienti_abonamente.abonament_client_valuta='" .$_POST["abonament_client_valuta"] . "' ," ;
$query= $query . " clienti_abonamente.abonament_client_frecventa='" .$_POST["abonament_client_frecventa"] . "' ," ;
$query= $query . " clienti_abonamente.abonament_client_aloc='" .$_POST["abonament_client_aloc"] . "' ," ;
$query= $query . " clienti_abonamente.abonament_client_detalii='" .$_POST["abonament_client_detalii"] . "' ," ;
$query= $query . " clienti_abonamente.abonament_client_activ='" .$_POST["abonament_client_activ"] . "' ," ;
$query= $query . " clienti_abonamente.abonament_client_email='" .$_POST["abonament_client_email"] . "' ," ;
$query= $query . " clienti_abonamente.abonament_client_unitate='" .$_POST["abonament_client_unitate"] . "' ," ;
$query= $query . " clienti_abonamente.abonament_client_termen='" .$_POST["abonament_client_termen"] . "' ," ;
$query= $query . " clienti_abonamente.abonament_client_zifacturare='" .$_POST["abonament_client_zifacturare"] . "' ," ;
$query= $query . " clienti_abonamente.abonament_client_lunafacturare='" .$lunifacturare . "' ," ;
$query= $query . " clienti_abonamente.abonament_client_BU='" .$_POST["abonament_client_BU"] . "' ," ;
$query= $query . " clienti_abonamente.abonament_client_sales='" .$_POST["abonament_client_sales"] . "' ," ;
$query= $query . " clienti_abonamente.abonament_client_anexa='" .$_POST["abonament_client_anexa"] . "' ," ;
$query= $query . " clienti_abonamente.abonament_client_pdf='" .$_POST["abonament_client_pdf"] . "' ," ;
$query= $query . " clienti_abonamente.abonament_client_an='" .$_POST["abonament_client_an"] . "' ," ;
$query= $query . " clienti_abonamente.abonament_client_contract='" .$_POST["abonament_client_contract"] . "' " ;

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
    window.history.go(-2);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}
}
}
Else {
?>
<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitesubscribtions.php" class="button"><?php echo $strBack?>  <i class="fas fa-backward"></i></a></p>
</div>
</div>
<form Method="post" id="users" Action="sitesubscribtions.php?mode=new" >
    	  <div class="grid-x grid-margin-x">
		       <div class="large-3 medium-3 small-3 cell"> 
	 <label><?php echo $strClient?></label>
	  <select name="abonament_client_ID" class="required">
           <option value=""><?php echo $strClient?></option>
          <?php $sql = "Select Client_Denumire, clienti_date.ID_Client, Contract_Data, Contract_Numar, Contract_Obiect FROM clienti_date, clienti_contracte WHERE clienti_date.ID_Client=clienti_contracte.ID_Client ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?> - <?php echo $rss["Contract_Obiect"]?> - <?php echo $rss["Contract_Numar"]?>/<?php echo $rss["Contract_Data"]?> </option>
          <?php
}?>
        </select>
		  </div>
		  <div class="large-3 medium-3 small-3 cell"> 
	 <label><?php echo $strSeenBy?></label>
	  <select name="abonament_client_aloc" class="required">
           <option value=""><?php echo $strSeenBy?></option>
          <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["utilizator_Code"]?>"><?php echo $rss["utilizator_Nume"]?>&nbsp;<?php echo $rss["utilizator_Prenume"]?> </option>
          <?php
}?>
        </select>
				   		  </div>		
						  <div class="large-3 medium-3 small-3 cell"> 
	 <label><?php echo $strSales?></label>
	  <select name="abonament_client_sales" class="required">
           <option value=""><?php echo $strSeenBy?></option>
          <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["utilizator_Code"]?>"><?php echo $rss["utilizator_Nume"]?>&nbsp;<?php echo $rss["utilizator_Prenume"]?> </option>
          <?php
}?>
        </select>
				   		  </div>
						  					<div class="large-3 medium-3 small-3 cell">
<?php echo $strBusinessUnit?></label>					
	<select name="abonament_client_BU">
		 <?php
			 			$query7="SELECT * FROM activitati_contracte ORDER By activitate_contracte_denumire ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			echo"<option value=\"$seenby[activitate_contracte_cod]\">". $seenby['activitate_contracte_denumire']."</option>";
			}
		?></select>
		</div>
		</div>
		 	  <div class="grid-x grid-margin-x">
		  <div class="large-3 medium-3 small-3 cell"> 
      <label><?php echo $strContractType?></label>
      <input name="abonament_client_frecventa" Type="radio" value="1"><?php echo $strMonthly?>&nbsp;&nbsp;
	  <input name="abonament_client_frecventa" Type="radio" value="2"><?php echo $strQuaterly?>&nbsp;&nbsp;
	  <input name="abonament_client_frecventa" Type="radio" value="0"><?php echo $strSemestrial?>&nbsp;&nbsp;
	  <input name="abonament_client_frecventa" Type="radio" value="3"><?php echo $strYearly?>
	       
   		  </div>
		     
		  <div class="large-3 medium-3 small-3 cell"> 
      <label><?php echo $strActive?></label>
      <input name="abonament_client_activ" Type="radio" value="0"><?php echo $strYes?>&nbsp;&nbsp;<input name="abonament_client_active" Type="radio" value="1"><?php echo $strNo?>
     </div>

		       <div class="large-3 medium-3 small-3 cell"> 
	  <label><?php echo $strObject?></label>
	  <textarea name="abonament_client_detalii"></textarea>
		   		  </div>
		  <div class="large-3 medium-3 small-3 cell"> 
	 <label><?php echo $strUnit?></label>
	  <input name="abonament_client_unitate" Type="text" id="numar" size="30" class="required" value="" />
		   		  </div>
		   		  </div>
					      	  <div class="grid-x grid-margin-x">	  
		  <div class="large-3 medium-3 small-3 cell"> 
	 <label><?php echo $strContract?></label>
	  <input name="abonament_client_contract" Type="text" id="numar" size="30" class="required" value="" />
			  		  </div>
		  <div class="large-3 medium-3 small-3 cell"> 
   
	 <label><?php echo $strDeadline?></label>
	  <input name="abonament_client_termen" Type="text" id="numar" size="30" class="required" value="10" />
	     		  </div>

		  <div class="large-3 medium-3 small-3 cell"> 
	 <label><?php echo $strDay?></label>
	  <input name="abonament_client_zifacturare" Type="text" id="numar" size="30" class="required" value="1" />
	 			  		  </div>
		  <div class="large-3 medium-3 small-3 cell"> 
   
	 <label><?php echo $strEmail?></label>
	  <input name="abonament_client_email" Type="text" id="numar" size="30" class="required" />
	  			  		  </div>
	  			  		  </div>
						  <div class="grid-x grid-margin-x">
		  <div class="large-3 medium-3 small-3 cell"> 
   
	 <label><?php echo $strYear?></label>
	  <input name="abonament_client_an" Type="text" id="numar" size="30" class="required" />
	    			  		  </div>
		  <div class="large-3 medium-3 small-3 cell"> 
	 
	 <label><?php echo $strValue?></label>
	  <input name="abonament_client_valoare" Type="text" id="numar" size="30" class="required" value="" />
		</div>
		     <div class="large-3 medium-3 small-3 cell"> 
      <label><?php echo $strCurrency?></label>
      <input name="abonament_client_valuta" Type="radio" value="0" checked /> <?php echo $strLei?>&nbsp;&nbsp;<input name="abonament_client_valuta" Type="radio" value="1" ><?php echo $strEuro?>
	</div>
	 <div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strInvoiceMonth?></label>
	 <select name="abonament_client_lunafacturare[]" multiple size=5>
	     <?php for ( $m = 1; $m <= 12; $m ++) {
    		
   		//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
    		echo "<OPTION value=\"$m\">$monthname</OPTION>";
				} 
			?>
	 </select>
	</div>
	</div>
		  		  <div class="grid-x grid-margin-x">
			  <div class="large-3 medium-3 small-3 cell"> 
      <label><?php echo $strAnnex?></label>
      <input name="abonament_client_anexa" Type="radio" value="0" checked><?php echo $strNo?>&nbsp;&nbsp;<input name="abonament_client_anexa" Type="radio" value="1"><?php echo $strYes?>
     </div>
	 		  <div class="large-9 medium-9 small-9 cell"> 
	 <label><?php echo $strFile?></label>
	  <input name="abonament_client_pdf" Type="text" size="30" value="" />
		</div>
		</div>
	  		  <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"><p align="center"><input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button"> </p></div>
	</div>
  </form>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_Aloc, 
clienti_abonamente.abonament_client_ID, clienti_abonamente.abonament_client_aloc, clienti_abonamente.abonament_client_contract, clienti_abonamente.abonament_client_sales, clienti_abonamente.abonament_client_termen, clienti_abonamente.abonament_client_activ, clienti_abonamente.abonament_client_email, 
clienti_abonamente.abonament_client_frecventa, clienti_abonamente.abonament_client_detalii, clienti_abonamente.abonament_client_unitate, clienti_abonamente.abonament_client_an, clienti_abonamente.abonament_client_zifacturare,
clienti_abonamente.abonament_client_valoare, clienti_abonamente.abonament_client_valuta, clienti_abonamente.abonament_client_anexa, clienti_abonamente.abonament_client_pdf, clienti_abonamente.abonament_client_lunafacturare, clienti_abonamente.abonament_client_BU, utilizator_Nume, utilizator_Prenume
FROM clienti_abonamente, clienti_date, date_utilizatori
WHERE abonament_ID=$_GET[cID] AND clienti_date.ID_Client=clienti_abonamente.abonament_client_ID AND date_utilizatori.utilizator_Code=clienti_abonamente.abonament_client_aloc";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitesubscribtions.php" class="button"><?php echo $strBack?>  <i class="fas fa-backward"></i></a></p>
</div>
</div>
<form Method="post" id="users" Action="sitesubscribtions.php?mode=edit&cID=<?php echo $_GET['cID']?>" >
    	  <div class="grid-x grid-margin-x">
		       <div class="large-3 medium-3 small-3 cell"> 
	 <label><?php echo $strTitle?></label>
	  <select name="abonament_client_ID" class="required">
          <?php $sql = "Select * FROM clienti_date ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  <?php if ($row["ID_Client"]==$rss["ID_Client"]) echo "selected"; ?> value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
          <?php
}?>
        </select>
			  </div>
			       <div class="large-3 medium-3 small-3 cell"> 		  
	 <label><?php echo $strSeenBy?></label>
	  <select name="abonament_client_aloc" class="required">
           <option value=""><?php echo $strSeenBy?></option>
          <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["utilizator_Code"]?>" <?php IF ($rss["utilizator_Code"]==$row["abonament_client_aloc"]) {echo "selected";}?>><?php echo $rss["utilizator_Nume"]?>&nbsp;<?php echo $rss["utilizator_Prenume"]?> </option>
          <?php
}?>
        </select>
			 </div>			    
			 <div class="large-3 medium-3 small-3 cell"> 		  
	 <label><?php echo $strSales?></label>
	  <select name="abonament_client_sales" class="required">
           <option value=""><?php echo $strSeenBy?></option>
          <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["utilizator_Code"]?>" <?php IF ($rss["utilizator_Code"]==$row["abonament_client_sales"]) {echo "selected";}?>><?php echo $rss["utilizator_Nume"]?>&nbsp;<?php echo $rss["utilizator_Prenume"]?> </option>
          <?php
}?>
        </select>
			 </div>
			 					<div class="large-3 medium-3 small-3 cell">
<?php echo $strBusinessUnit?></label>					
	<select name="abonament_client_BU">
				 <?php
			 			$query7="SELECT * FROM activitati_contracte ORDER By activitate_contracte_denumire ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
				if($row["abonament_client_BU"]==$seenby["activitate_contracte_cod"])
				{			echo"<option selected value=\"$seenby[activitate_contracte_cod]\">". $seenby['activitate_contracte_denumire']."</option>";}
			Else
				{			echo"<option value=\"$seenby[activitate_contracte_cod]\">". $seenby['activitate_contracte_denumire']."</option>";}
			}
		?></select>
		</div>
		</div>
					      	  <div class="grid-x grid-margin-x">	  
		  <div class="large-3 medium-3 small-3 cell"> 		
      <label><?php echo $strContractType?></label>
      <input name="abonament_client_frecventa" Type="radio" value="1" <?php If ($row["abonament_client_frecventa"]==1) echo "checked"?>><?php echo $strMonthly?>&nbsp;&nbsp;
	  <input name="abonament_client_frecventa" Type="radio" value="2" <?php If ($row["abonament_client_frecventa"]==2) echo "checked"?>><?php echo $strQuaterly?>
	  <input name="abonament_client_frecventa" Type="radio" value="0" <?php If ($row["abonament_client_frecventa"]==0) echo "checked"?>><?php echo $strSemestrial?>
	  <input name="abonament_client_frecventa" Type="radio" value="3" <?php If ($row["abonament_client_frecventa"]==3) echo "checked"?>><?php echo $strYearly?>
	  </div>
       <div class="large-3 medium-3 small-3 cell">
      <label><?php echo $strActive?></label>
      
<input name="abonament_client_activ" Type="radio" value="0" <?php If ($row["abonament_client_activ"]==0) echo "checked"?>><?php echo $strYes?>
	  &nbsp;&nbsp;
<input name="abonament_client_activ" Type="radio" value="1" <?php If ($row["abonament_client_activ"]==1) echo "checked"?>><?php echo $strNo?>
     
   </div>
       <div class="large-3 medium-3 small-3 cell">
	  <?php echo $strObject?><br />
	  <textarea name="abonament_client_detalii"><?php echo $row["abonament_client_detalii"]?></textarea>
		   </div>
       <div class="large-3 medium-3 small-3 cell">
	 <label><?php echo $strUnit?></label>
	  <input name="abonament_client_unitate" Type="text" id="numar" size="30" class="required" value="<?php echo $row["abonament_client_unitate"]?>" />
		   </div>
		   </div>
       					      	  <div class="grid-x grid-margin-x">	  
		  <div class="large-3 medium-3 small-3 cell"> 
	 <label><?php echo $strContract?></label>
	  <input name="abonament_client_contract" Type="text" id="numar" size="30" class="required" value="<?php echo $row["abonament_client_contract"]?>" />
		   </div>
       <div class="large-3 medium-3 small-3 cell">
	 <label><?php echo $strValue?></label>
	  <input name="abonament_client_valoare" Type="text" id="numar" size="30" class="required" value="<?php echo $row["abonament_client_valoare"]?>" />
		   </div>
       <div class="large-3 medium-3 small-3 cell">
	 <label><?php echo $strEmail?></label>
	  <input name="abonament_client_email" Type="text" id="numar" size="30" class="required" value="<?php echo $row["abonament_client_email"]?>" />
		   </div>
       <div class="large-3 medium-3 small-3 cell">
	 <label><?php echo $strYear?></label>
	  <input name="abonament_client_an" Type="text" id="numar" size="30" class="required" value="<?php echo $row["abonament_client_an"]?>" />
	 </div>
	 </div>
	     	  <div class="grid-x grid-margin-x">
		       <div class="large-3 medium-3 small-3 cell"> 
	 <label><?php echo $strDeadline?></label>
	  <input name="abonament_client_termen" Type="text" id="numar" size="30" class="required" value="<?php echo $row["abonament_client_termen"]?>" />
		 </div>
		 <div class="large-3 medium-3 small-3 cell"> 
	 <label><?php echo $strDay?></label>
	  <input name="abonament_client_zifacturare" Type="text" id="numar" size="30" class="required" value="<?php echo $row["abonament_client_zifacturare"]?>" />
					 </div>
		 <div class="large-3 medium-3 small-3 cell"> 
		   
      <label><?php echo $strCurrency?></label>
      <input name="abonament_client_valuta" Type="radio" value="0" <?php If ($row["abonament_client_valuta"]==0) echo "checked"?> /> <?php echo $strLei?>&nbsp;&nbsp;<input name="abonament_client_valuta" Type="radio" value="1" <?php If ($row["abonament_client_valuta"]==1) echo "checked"?> ><?php echo $strEuro?>
     </div>
	  <div class="large-3 medium-3 small-3 cell">
      <label><?php echo $strInvoiceMonth?></label>
	  <select name="abonament_client_lunafacturare[]" multiple size=5>
	     <?php for ( $m = 1; $m <= 12; $m ++) {
    		
   		//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
$lunidefacturare=explode(";",$row["abonament_client_lunafacturare"]);
if(in_array($m,$lunidefacturare)) $str_flag = "selected";
else $str_flag="";
    		echo "<OPTION value=\"$m\" $str_flag>$monthname</OPTION>";
				} 
			?>
	 </select>
	
     </div>
     </div>
	 		  		  <div class="grid-x grid-margin-x">
			  <div class="large-3 medium-3 small-3 cell"> 
      <label><?php echo $strAnnex?></label>
      <input name="abonament_client_anexa" Type="radio" value="0" <?php If ($row["abonament_client_anexa"]==0) echo "checked"?>><?php echo $strNo?>&nbsp;&nbsp;<input name="abonament_client_anexa" Type="radio" value="1"  <?php If ($row["abonament_client_anexa"]==1) echo "checked"?>><?php echo $strYes?>
     </div>
	 		  <div class="large-9 medium-9 small-9 cell"> 
	 <label><?php echo $strFile?></label>
	  <input name="abonament_client_pdf" Type="text" size="30" value="<?php echo $row["abonament_client_pdf"]?>" />
		</div>
		</div>
	 
	  		  <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"><p><input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button"></p></div>
	</div>
  </form>
<?php
}
Else
{
echo "<a href=\"sitesubscribtions.php?mode=new\" class=\"button\">$strAddNew <i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a><br />";

$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_Aloc, 
clienti_abonamente.abonament_client_ID, clienti_abonamente.abonament_ID, clienti_abonamente.abonament_client_aloc, clienti_abonamente.abonament_client_contract, 
clienti_abonamente.abonament_client_frecventa, clienti_abonamente.abonament_client_detalii, clienti_abonamente.abonament_client_unitate, 
clienti_abonamente.abonament_client_valoare, clienti_abonamente.abonament_client_valuta, utilizator_Nume, utilizator_Prenume
FROM clienti_abonamente, clienti_date, date_utilizatori
WHERE clienti_date.ID_Client=clienti_abonamente.abonament_client_ID AND date_utilizatori.utilizator_Code=clienti_abonamente.abonament_client_aloc AND clienti_abonamente.abonament_client_activ='0'";

if ((isset( $_GET['start'])) && !empty( $_GET['start'])){
$start=$_GET['start'];}
Else{
$start=0;}
if ($start!='0'){
$query= $query . " AND Client_Denumire LIKE'$start%'";
};

$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY Client_Denumire ASC $pages->limit";
$result=ezpub_query($conn,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<div class="paginate">
<?php
echo $strTotal . " " .$numar." ".$strSubscribtions ;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"sitesubscribtions.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
echo " <br /><br />";
$sql="SELECT DISTINCT LEFT(clienti_date.Client_Denumire, 1) as letter 
FROM clienti_date, clienti_abonamente 
WHERE clienti_abonamente.abonament_client_ID=clienti_date.ID_Client Group By letter ORDER BY letter ASC;";
$result2=ezpub_query($conn,$sql);
While ($row1=ezpub_fetch_array($result2)){
	$char=$row1["letter"];
    echo "<a href=\"sitesubscribtions.php?start=$char\">$char</a>&nbsp;";
}
?>
</div>
<table width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strClient?></th>
			<th><?php echo $strObject?></th>
			<th><?php echo $strFrequency?></th>
			<th><?php echo $strValue?></th>
			<th><?php echo $strSeenBy?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
	If ($row["abonament_client_frecventa"]==1){$frecventa=$strMonthly;}
	ElseIf ($row["abonament_client_frecventa"]==2){$frecventa=$strQuaterly;}
	ElseIf ($row["abonament_client_frecventa"]==0){$frecventa=$strSemestrial;}
	Elseif ($row["abonament_client_frecventa"]==3){$frecventa=$strYearly;}
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[abonament_client_detalii]</td>
			<td>$frecventa</td>
			<td align=\"right\">".romanize($row["abonament_client_valoare"])."</td>
			<td>$row[utilizator_Prenume]&nbsp;$row[utilizator_Nume] </td>
			  <td><a href=\"sitesubscribtions.php?mode=edit&cID=$row[abonament_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"sitesubscribtions.php?mode=delete&cID=$row[abonament_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"5\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
}
?>
</div>
</div>
<hr/>
<?php
include '../bottom.php';
?>