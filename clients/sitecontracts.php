<?php
//update 03.01.2025
include '../settings.php';
include '../classes/common.php';

include '../classes/paginator.class.php';
$strPageTitle="Administrare contracte";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM clienti_contracte WHERE ID_Contract=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
     window.location = \"sitecontracts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

If ($_GET['mode']=="new"){
//insert new user

 $lunifacturare= implode(';',$_POST['Contract_lunifacturare']);

	$mSQL = "INSERT INTO clienti_contracte(";
	$mSQL = $mSQL . "ID_Client,";
	$mSQL = $mSQL . "Contract_Alocat,";
	$mSQL = $mSQL . "Contract_Tip,";
	$mSQL = $mSQL . "Contract_Obiect,";
	$mSQL = $mSQL . "Contract_Numar,";
	$mSQL = $mSQL . "Contract_Data,";
	$mSQL = $mSQL . "Contract_Activ,";
	$mSQL = $mSQL . "Contract_Suma,";
	$mSQL = $mSQL . "Contract_Termen,";
	$mSQL = $mSQL . "Contract_Zifacturare,";
	$mSQL = $mSQL . "Contract_lunifacturare,";
	$mSQL = $mSQL . "Contract_Responsabil,";
	$mSQL = $mSQL . "Contract_Email_Facturare,";
	$mSQL = $mSQL . "Contract_An,";
	$mSQL = $mSQL . "Contract_abonament,";
	$mSQL = $mSQL . "Contract_BU,";
	$mSQL = $mSQL . "Contract_Sales,";
	$mSQL = $mSQL . "Contract_Valuta)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$_POST["ID_Client"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Alocat"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Tip"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Obiect"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Numar"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Data"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Activ"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Suma"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Termen"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Zifacturare"] . "', ";
	$mSQL = $mSQL . "'" .$lunifacturare	. "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Responsabil"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Email_Facturare"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_An"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_abonament"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_BU"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Sales"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Valuta"] . "') ";
			
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
	If ($_POST["Contract_abonament"]==1)
	{
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
	$mSQL = $mSQL . "abonament_client_lunafacturare,";
	$mSQL = $mSQL . "abonament_client_activ,";
	$mSQL = $mSQL . "abonament_client_email,";
	$mSQL = $mSQL . "abonament_client_an,";
	$mSQL = $mSQL . "abonament_client_BU,";
	$mSQL = $mSQL . "abonament_client_sales,";
	$mSQL = $mSQL . "abonament_client_contract)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$_POST["ID_Client"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Suma"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Valuta"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Tip"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Alocat"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Obiect"] . "', ";
	$mSQL = $mSQL . "'Luni', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Termen"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Zifacturare"] . "', ";
	$mSQL = $mSQL . "'" .$lunifacturare . "', ";
	$mSQL = $mSQL . "'0', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Email_Facturare"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_An"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_BU"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Sales"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["Contract_Numar"] ."/". $_POST["Contract_Data"]."') ";
			
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
	echo "Abonament adăugat.";
	}
	}

echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
     window.location = \"sitecontracts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{// edit
$lunifacturare= implode(';',$_POST['Contract_lunifacturare']);
$strWhereClause = " WHERE clienti_contracte.ID_Contract=" . $_GET["cID"] . ";";
$query= "UPDATE clienti_contracte SET clienti_contracte.ID_Client='" .$_POST["ID_Client"] . "' ," ;
$query= $query . " clienti_contracte.Contract_Alocat='" .$_POST["Contract_Alocat"] . "' ," ;
$query= $query ." clienti_contracte.Contract_Activ='" .$_POST["Contract_Activ"] . "' ," ;
$query= $query ." clienti_contracte.Contract_Obiect='" .$_POST["Contract_Obiect"] . "' ," ;
$query= $query ." clienti_contracte.Contract_Tip='" .$_POST["Contract_Tip"] . "' ," ;
$query= $query ." clienti_contracte.Contract_Numar='" .$_POST["Contract_Numar"] . "' ," ;
$query= $query ." clienti_contracte.Contract_Suma='" .$_POST["Contract_Suma"] . "' ," ;
$query= $query ." clienti_contracte.Contract_Termen='" .$_POST["Contract_Termen"] . "' ," ;
$query= $query ." clienti_contracte.Contract_Zifacturare='" .$_POST["Contract_Zifacturare"] . "' ," ;
$query= $query ." clienti_contracte.Contract_lunifacturare='" .$lunifacturare . "' ," ;
$query= $query ." clienti_contracte.Contract_Responsabil='" .$_POST["Contract_Responsabil"] . "' ," ;
$query= $query ." clienti_contracte.Contract_Email_Facturare='" .$_POST["Contract_Email_Facturare"] . "' ," ;
$query= $query ." clienti_contracte.Contract_abonament='" .$_POST["Contract_abonament"] . "' ," ;
$query= $query ." clienti_contracte.Contract_Valuta='" .$_POST["Contract_Valuta"] . "' ," ;
$query= $query ." clienti_contracte.Contract_BU='" .$_POST["Contract_BU"] . "' ," ;
$query= $query ." clienti_contracte.Contract_Sales='" .$_POST["Contract_Sales"] . "' ," ;
$query= $query ." clienti_contracte.Contract_An='" .$_POST["Contract_An"] . "' ," ;
$query= $query . " clienti_contracte.Contract_Data='" .$_POST["Contract_Data"] . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
	if (!empty($_POST["abonament_ID"])) {
$strWhereClause = " WHERE clienti_abonamente.abonament_ID=" . $_POST["abonament_ID"] . ";";
$query2= "UPDATE clienti_abonamente SET clienti_abonamente.abonament_client_ID='" .$_POST["ID_Client"] . "' ," ;
$query2 =$query2 . " clienti_abonamente.abonament_client_aloc='" .$_POST["Contract_Alocat"] . "' ," ;
$query2= $query2 . " clienti_abonamente.abonament_client_valoare='" .$_POST["Contract_Suma"] . "' ," ;
$query2= $query2 . " clienti_abonamente.abonament_client_valuta='" .$_POST["Contract_Valuta"] . "' ," ;
$query2= $query2 . " clienti_abonamente.abonament_client_detalii='" .$_POST["Contract_Obiect"] . "' ," ;
$query2= $query2 . " clienti_abonamente.abonament_client_email='" .$_POST["Contract_Email_Facturare"] . "' ," ;
$query2= $query2 . " clienti_abonamente.abonament_client_zifacturare='" .$_POST["Contract_Zifacturare"] . "' ," ;
$query2= $query2 . " clienti_abonamente.abonament_client_an='" .$_POST["Contract_An"] . "' ," ;
$query2= $query2 . " clienti_abonamente.abonament_client_BU='" .$_POST["Contract_BU"] . "' ," ;
$query2= $query2 . " clienti_abonamente.abonament_client_sales='" .$_POST["Contract_Sales"] . "' ," ;
$query2= $query2 . " clienti_abonamente.abonament_client_contract='" .$_POST["Contract_Numar"] . "' ," ;
$query2= $query2 . " clienti_abonamente.abonament_client_activ='" .$_POST["Contract_Activ"] . "' ," ;
$query2= $query2 . " clienti_abonamente.abonament_client_lunafacturare='" .$lunifacturare . "' " ;
	$query2= $query2 . $strWhereClause;
if (!ezpub_query($conn,$query2))
  {

  die('Error: ' . ezpub_error($conn));
	}     }
Else{
 echo "Abonament actualizat";	

}
}
echo "<div class=\"callout success\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitecontracts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}
}
Else {
?>
<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitecontracts.php" class="button"><?php echo $strBack?> <i class="fas fa-backward"></i></a></p>
</div>
</div>
<form Method="post" Action="sitecontracts.php?mode=new" >
			    		  <div class="grid-x grid-margin-x">
     <div class="large-3 medium-3 small-3 cell"> 
	  <label><?php echo $strClient?></label>
	  <select name="ID_Client" class="required">
           <option value=""><?php echo $strClient?></option>
          <?php $sql = "Select * FROM clienti_date ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["ID_Client"]?>"><?php echo $rss["Client_Denumire"]?></option>
          <?php
}?>
        </select>
	</div>
     <div class="large-3 medium-3 small-3 cell"> 	  
	  <label><?php echo $strSeenBy?></label>
	  <select name="Contract_Alocat" class="required">
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
	  <select name="Contract_Sales" class="required">
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
      <label><?php echo $strSubscribtion?></label>
      <input name="Contract_abonament" Type="radio" value="0" checked /> <?php echo $strOneTimeJob?>&nbsp;&nbsp;
	  <input name="Contract_abonament" Type="radio" value="1"><?php echo $strSubscribtion?>
    </div> 
    </div> 
				    <div class="grid-x grid-margin-x">
     <div class="large-4 medium-4 small-4 cell"> 	
	  <TD colspan="2"><?php echo $strObject?><br />
	  <textarea name="Contract_Obiect" id="obiect" style="width:100%;"></textarea>
	</div>
     <div class="large-4 medium-4 small-4 cell"> 	
	  <label><?php echo $strNumber?></label>
	  <input name="Contract_Numar" Type="text" id="numar" class="required" value="" />
    </div> 
	     <div class="large-4 medium-4 small-4 cell"> 		 
	  <label><?php echo $strYear?></label>
	  <input name="Contract_An" Type="text" id="numar" class="required" value="" />
		</div>
    </div> 
				    <div class="grid-x grid-margin-x">
			  <div class="large-2 medium-2 small-2 cell">  
	<label><?php echo $strDay?></label>
	  <input name="Contract_Zifacturare" Type="text" id="numar" class="required" value="" />
		</div>
     <div class="large-2 medium-2 small-2 cell"> 		 
	  <label><?php echo $strDeadline?></label>
	  <input name="Contract_Termen" Type="text" id="numar" class="required" value="10" />
	</div>
	    <div class="large-2 medium-2 small-2 cell"> 
	      <label><?php echo $strCurrency?></label>
      <input name="Contract_Valuta" Type="radio" value="0" checked /> <?php echo $strLei?>&nbsp;&nbsp;<input name="Contract_Valuta" Type="radio" value="1" ><?php echo $strEuro?>
	</div>
			  <div class="large-3 medium-3 small-3 cell"> 
      <label><?php echo $strDate?></label>
       <input name="Contract_Data" Type="date" id="date" class="required"  />
    </div> 
			  <div class="large-3 medium-3 small-3 cell">  
	  <label><?php echo $strValue?></label>
	  <input name="Contract_Suma" Type="text" id="numar" class="required" value="" />
	</div>	
	    </div> 
				    <div class="grid-x grid-margin-x">
	<div class="large-3 medium-3 small-3 cell">  
	<?php echo $strBusinessUnit?></label>	
	<select name="Contract_BU">
	<option value="" selected><?php echo $strPick?></option>
				 <?php
			 			$query7="SELECT * FROM activitati_contracte ORDER By activitate_contracte_denumire ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			echo"<option value=\"$seenby[activitate_contracte_cod]\">". $seenby['activitate_contracte_denumire']."</option>";
			}
		?></select>
		</div>
		   <div class="large-6 medium-6 small-6 cell">  
      <label><?php echo $strFrequency?></label>
	  <input name="Contract_Tip" Type="radio" value="1"><?php echo $strMonthly?>&nbsp;&nbsp;
	  <input name="Contract_Tip" Type="radio" value="2"><?php echo $strQuaterly?>&nbsp;&nbsp;
	  <input name="Contract_Tip" Type="radio" value="0"><?php echo $strSemestrial?>&nbsp;&nbsp; 
	  <input name="Contract_Tip" Type="radio" value="3" checked /> <?php echo $strYearly?>
    </div> 
		   <div class="large-3 medium-3 small-3 cell">  
      <label><?php echo $strActive?></label>
      <input name="Contract_Activ" Type="radio" value="0" checked /> <?php echo $strYes?>&nbsp;&nbsp;<input name="Contract_Activ" Type="radio" value="1" ><?php echo $strNo?>
    </div> 
	</div>	
	 <div class="grid-x grid-margin-x">
	   <div class="large-4 medium-4 small-4 cell">  
	  <label><?php echo $strResponsible?></label>
	  <input name="Contract_Responsabil" Type="text" id="numar" class="required" value="" />
	</div>	
	   <div class="large-4 medium-4 small-4 cell">   
	  <label><?php echo $strEmail?></label>
	  <input name="Contract_Email_Facturare" Type="text" id="numar" class="required" value="" />
	</div>	
	   <div class="large-4 medium-4 small-4 cell"> 
	  <label><?php echo $strInvoiceMonth?></label>
	 <select name="Contract_lunifacturare[]" multiple size=5>
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
     <div class="large-12 medium-12 small-12 cell text-center"> <p align="center"><input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button"> </p></div>
	</div>
  </form>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_Aloc,
clienti_contracte.ID_Client, clienti_contracte.Contract_Alocat, clienti_contracte.Contract_Activ, clienti_contracte.Contract_Sales, clienti_contracte.Contract_Tip,clienti_contracte.Contract_Responsabil, clienti_contracte.Contract_Email_Facturare,
clienti_contracte.Contract_Suma, clienti_contracte.Contract_Valuta, clienti_contracte.Contract_Termen, clienti_contracte.Contract_Zifacturare, clienti_contracte.Contract_abonament, 
clienti_contracte.Contract_Obiect, utilizator_Nume, utilizator_Prenume, Contract_Numar, Contract_An, Contract_lunifacturare, Contract_Data, Contract_BU 
FROM clienti_contracte, clienti_date, date_utilizatori, clienti_abonamente
WHERE ID_Contract=$_GET[cID] AND clienti_date.ID_Client=clienti_contracte.ID_Client AND date_utilizatori.utilizator_Code=clienti_contracte.Contract_Alocat 
ORDER By Client_Denumire ASC";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitecontracts.php" class="button"><?php echo $strBack?>  <i class="fas fa-backward"></i></a></p>
</div>
</div>
<form Method="post" id="users" Action="sitecontracts.php?mode=edit&cID=<?php echo $_GET['cID']?>" >
<?php
$aquery="SELECT abonament_ID from clienti_abonamente WHERE abonament_client_ID=$row[ID_Client] AND abonament_client_detalii='$row[Contract_Obiect]'";
$aresult=ezpub_query($conn,$aquery);
$arow=ezpub_fetch_array($aresult);
?>

 <input type="hidden" id="abonament_ID" name="abonament_ID" value="<?php echo $arow["abonament_ID"]?>">
			    		  <div class="grid-x grid-margin-x">
     <div class="large-3 medium-3 small-3 cell">   
	  <label><?php echo $strTitle?></label>
	  <select name="ID_Client" class="required">
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
	  <select name="Contract_Alocat" class="required">
           <option value=""><?php echo $strSeenBy?></option>
          <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["utilizator_Code"]?>" <?php IF ($rss["utilizator_Code"]==$row["Contract_Alocat"]) {echo "selected";}?>><?php echo $rss["utilizator_Nume"]?>&nbsp;<?php echo $rss["utilizator_Prenume"]?> </option>
          <?php
}?>
        </select>
		</div>		    
		<div class="large-3 medium-3 small-3 cell">   
	  <label><?php echo $strSales?></label>
	  <select name="Contract_Sales" class="required">
           <option value=""><?php echo $strSeenBy?></option>
          <?php $sql = "Select * FROM date_utilizatori ORDER BY utilizator_Nume ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["utilizator_Code"]?>" <?php IF ($rss["utilizator_Code"]==$row["Contract_Sales"]) {echo "selected";}?>><?php echo $rss["utilizator_Nume"]?>&nbsp;<?php echo $rss["utilizator_Prenume"]?> </option>
          <?php
}?>
        </select>
		</div>
		       <div class="large-3 medium-3 small-3 cell">  
      <label><?php echo $strContractType?></label>
      <input name="Contract_abonament" Type="radio" value="1" <?php If ($row["Contract_abonament"]==1) echo "checked"?> />&nbsp;<?php echo $strSubscribtion?> 
	  <input name="Contract_abonament" Type="radio" value="0" <?php If ($row["Contract_abonament"]==0) echo "checked"?>>&nbsp;<?php echo $strOneTimeJob?>
	  </div>
	  </div>
				    		  <div class="grid-x grid-margin-x">
     <div class="large-3 medium-3 small-3 cell"> 
	  <label><?php echo $strObject?></label>
	  <textarea name="Contract_Obiect" id="obiect" style="width:100%;"><?php echo $row["Contract_Obiect"]?></textarea>
		</div>
		       <div class="large-3 medium-3 small-3 cell">  
	  <label><?php echo $strNumber?></label>
	  <input name="Contract_Numar" Type="text" id="numar" class="required" value="<?php echo $row["Contract_Numar"]?>" />
					</div>
		       <div class="large-3 medium-3 small-3 cell"> 
				  <label><?php echo $strYear?></label>
	  <input name="Contract_An" Type="text" id="numar" class="required" value="<?php echo $row["Contract_An"]?>" />
  </div>
  		       <div class="large-3 medium-3 small-3 cell"> 
				  <label><?php echo $strSubscribtion?></label>
				  <select name="abonament_ID" class="required">
          <?php $sql = "Select * FROM clienti_abonamente WHERE abonament_client_ID=$row[ID_Client] AND abonament_client_detalii='$row[Contract_Obiect]' ORDER BY abonament_client_detalii ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["abonament_ID"]?>"><?php echo $rss["abonament_client_detalii"]?></option>
          <?php
}?>
        </select>
  </div>
	  </div>
				    		  <div class="grid-x grid-margin-x">
     <div class="large-3 medium-3 small-3 cell"> 
	 	  <label><?php echo $strDay?></label>
	  <input name="Contract_Zifacturare" Type="text" id="numar" class="required" value="<?php echo $row["Contract_Zifacturare"]?>" />
	  					</div>
		       <div class="large-3 medium-3 small-3 cell"> 
	  <label><?php echo $strDeadline?></label>
	  <input name="Contract_Termen" Type="text" id="numar" class="required" value="<?php echo $row["Contract_Termen"]?>" />
							</div>
		       <div class="large-3 medium-3 small-3 cell"> 
		 	   <label><?php echo $strCurrency?></label>
      <input name="Contract_Valuta" Type="radio" value="0" <?php If ($row["Contract_Valuta"]==0) echo "checked"?> /> <?php echo $strLei?>&nbsp;&nbsp;<input name="Contract_Valuta" Type="radio" value="1" <?php If ($row["Contract_Valuta"]==1) echo "checked"?> ><?php echo $strEuro?>
   </div> 
     <div class="large-3 medium-3 small-3 cell"> 
		  <label><?php echo $strDate?></label>
      <input name="Contract_Data" Type="date" id="numar" class="required" value="<?php echo $row["Contract_Data"]?>" />
   </div>
   </div>
    	  <div class="grid-x grid-margin-x">
		       <div class="large-3 medium-3 small-3 cell"> 
	  <label><?php echo $strValue?></label>
	  <input name="Contract_Suma" Type="text" id="numar" class="required" value="<?php echo $row["Contract_Suma"]?>" />
				</div>
					<div class="large-3 medium-3 small-3 cell">
<?php echo $strBusinessUnit?></label>					
	<select name="Contract_BU">
				 <?php
			 			$query7="SELECT * FROM activitati_contracte ORDER By activitate_contracte_denumire ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
				if($row["Contract_BU"]==$seenby["activitate_contracte_cod"])
				{			echo"<option selected value=\"$seenby[activitate_contracte_cod]\">". $seenby['activitate_contracte_denumire']."</option>";}
			Else
				{			echo"<option value=\"$seenby[activitate_contracte_cod]\">". $seenby['activitate_contracte_denumire']."</option>";}
			}
		?></select>
		</div>
						       <div class="large-3 medium-3 small-3 cell"> 
      <label><?php echo $strFrequency?></label>
		<input name="Contract_Tip" Type="radio" value="1" <?php If ($row["Contract_Tip"]==1) echo "checked"?>><?php echo $strMonthly?>&nbsp;&nbsp;
		<input name="Contract_Tip" Type="radio" value="2" <?php If ($row["Contract_Tip"]==2) echo "checked"?>><?php echo $strQuaterly?>&nbsp;&nbsp;
		<input name="Contract_Tip" Type="radio" value="3" <?php If ($row["Contract_Tip"]==3) echo "checked"?>><?php echo $strSemestrial?>&nbsp;&nbsp;
	  	<input name="Contract_Tip" Type="radio" value="0" <?php If ($row["Contract_Tip"]==0) echo "checked"?> /> <?php echo $strYearly?>
</div>						       
<div class="large-3 medium-3 small-3 cell"> 
      <label><?php echo $strActive?></label>
      <input name="Contract_Activ" Type="radio" value="0" <?php If ($row["Contract_Activ"]==0) echo "checked"?> /> <?php echo $strYes?>&nbsp;&nbsp;<input name="Contract_Activ" Type="radio" value="1" <?php If ($row["Contract_Activ"]==1) echo "checked"?> ><?php echo $strNo?>
</div>
</div>
	  <div class="grid-x grid-margin-x">
		       <div class="large-4 medium-4 small-4 cell">  
	  <label><?php echo $strResponsible?></label>
	  <input name="Contract_Responsabil" Type="text" id="numar" class="required" value="<?php echo $row["Contract_Responsabil"]?>" />
				</div>
		       <div class="large-4 medium-4 small-4 cell"> 
	  <label><?php echo $strEmail?></label>
	  <input name="Contract_Email_Facturare" Type="text" id="numar" class="required" value="<?php echo $row["Contract_Email_Facturare"]?>" />
				</div>
  <div class="large-4 medium-4 small-4 cell">
      <label><?php echo $strInvoiceMonth?></label>
	  <select name="Contract_lunifacturare[]" multiple size=5>
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
$lunidefacturare=explode(";",$row["Contract_lunifacturare"]);
if(in_array($m,$lunidefacturare)) $str_flag = "selected";
else $str_flag="";
    		echo "<OPTION value=\"$m\" $str_flag>$monthname</OPTION>";
				} 
			?>
	 </select>
	
     </div>
    
	</div>
	  		  <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"><p align="center"> <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button"> </p></div>
	</div>
  </form>
<?php
}
Else
{
echo "<a href=\"sitecontracts.php?mode=new\" class=\"button\">$strAddNew <i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a><br />";
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_Aloc, clienti_date.Client_Tip, 
clienti_contracte.ID_Client, clienti_contracte.Contract_Alocat, clienti_contracte.Contract_Activ, clienti_contracte.Contract_Tip,
clienti_contracte.Contract_Suma, clienti_contracte.Contract_Valuta, clienti_contracte.Contract_Obiect, utilizator_Nume, utilizator_Prenume, ID_Contract
FROM clienti_contracte, clienti_date, date_utilizatori
WHERE clienti_date.ID_Client=clienti_contracte.ID_Client AND date_utilizatori.utilizator_Code=clienti_contracte.Contract_Alocat AND clienti_contracte.Contract_Activ='0' AND Client_Tip=1 ";

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
echo $strTotal . " " .$numar." ".$strContracts ;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"sitecontracts.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
echo " <br /><br />";
$sql="SELECT DISTINCT LEFT(clienti_date.Client_Denumire, 1) as letter 
FROM clienti_date, clienti_contracte 
WHERE clienti_contracte.ID_client=clienti_date.ID_Client Group By letter ORDER BY letter ASC;";
$result2=ezpub_query($conn,$sql);
While ($row1=ezpub_fetch_array($result2)){
	$char=$row1["letter"];
    echo "<a href=\"sitecontracts.php?start=$char\">$char</a>&nbsp;";
}
?>
</div>
<table width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strClient?></th>
			<th><?php echo $strObject?></th>
			<th><?php echo $strSeenBy?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[Contract_Obiect]</td>
			<td>$row[utilizator_Prenume]&nbsp;$row[utilizator_Nume] </td>
			  <td><a href=\"sitecontracts.php?mode=edit&cID=$row[ID_Contract]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"sitecontracts.php?mode=delete&cID=$row[ID_Contract]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"3\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
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