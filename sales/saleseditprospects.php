<?php
include '../settings.php';
include '../classes/common.php';
//update 1.01.2023
 if(!isset($_SESSION)) 
    { 
        session_start(); 
	}
if (!isSet($_SESSION['$lang'])) {
	$_SESSION['$lang']="RO";
	$lang=$_SESSION['$lang'];
}
else
{
	$lang=$_SESSION['$lang'];
}
if ($lang=="RO") {
include '../lang/language_RO.php';
}
else
{
	include '../lang/language_EN.php';
}


$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$d = date("Y-m-d H:i:s");
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
?>
<!doctype html>
<head>
<!--Start Header-->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"/> <![endif]-->
<!--[if IE 7]> <html class="no-js lt-ie9 lt-ie8" lang="en"/> <![endif]-->
<!--[if IE 8]> <html class="no-js lt-ie9" lang="en"/> <![endif]-->
<!--[if gt IE 8]><!--><html class="no-js" lang="en"> <!--<![endif]-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!--Font Awsome-->
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/all.css">
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/foundation.css"/>
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/<?php echo $cssname ?>.css"/>
<link rel="stylesheet" href="../js/simple-editor/simple-editor.css">
<script src="../js/simple-editor/simple-editor.js"></script>

<script>
  function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
  }
</script>
 </head>
 
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <?php
if ($_SERVER['REQUEST_METHOD'] == 'POST'){

$strWhereClause = " WHERE sales_prospecti.prospect_id=" . $_GET["cID"] . ";";
$query= "UPDATE sales_prospecti SET sales_prospecti.prospect_denumire='" .str_replace("'","&#39;",$_POST["prospect_denumire"]) . "' ," ;
$query= $query . "sales_prospecti.prospect_adresa='" .str_replace("'","&#39;",$_POST["prospect_adresa"]) . "' ," ;
$query= $query . "sales_prospecti.prospect_telefon='" .str_replace("'","&#39;",$_POST["prospect_telefon"]) . "' ," ;
$query= $query . "sales_prospecti.prospect_rc='" .str_replace("'","&#39;",$_POST["prospect_rc"]) . "' ," ;
$query= $query . "sales_prospecti.prospect_contact='" .str_replace("'","&#39;",$_POST["prospect_contact"]) . "' ," ;
$query= $query . "sales_prospecti.prospect_sector='" .str_replace("'","&#39;",$_POST["prospect_sector"]) . "' ," ;
$query= $query . "sales_prospecti.prospect_cartier='" .str_replace("'","&#39;",$_POST["prospect_cartier"]) . "' ," ;
$query= $query . "sales_prospecti.prospect_cui='" .$_POST["prospect_cui"] . "' ," ;
$query= $query . "sales_prospecti.prospect_email='" .$_POST["prospect_email"] . "' ," ;
$query= $query . "sales_prospecti.prospect_activitate='" .str_replace("'","&#39;",$_POST["prospect_activitate"]) . "' ," ;
$query= $query . " sales_prospecti.prospect_caracterizare='" .str_replace("'","&#39;",$_POST["prospect_caracterizare"]) . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
else{
echo "<div class=\"success callout\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">";
die;
}
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit")
{
$query="SELECT * FROM sales_prospecti WHERE prospect_id=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);

?>
<label><?php echo $strClient?></label>
  <form method="post"  action="saleseditprospects.php?mode=edit&cID=<?php echo $row['prospect_id']?>" >
				    <div class="grid-x grid-margin-x">
			  <div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strTitle?></label>
	  <input name="prospect_denumire" Type="text" id="denumire" value="<?php echo $row["prospect_denumire"]?>" />
	</div>
	 <div class="large-3 medium-3 small-3 cell">
  <label><?php echo $strCompanyVAT?></label>
	  <input name="prospect_cui" Type="text" id="cif" value="<?php echo $row["prospect_cui"]?>" />
    </div>	
 <div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strCompanyRC?></label>
	  <input name="prospect_rc" id="numar_reg_com" Type="text" value="<?php echo $row["prospect_rc"]?>"/>
	</div>	
    </div>	
			    <div class="grid-x grid-margin-x">	
	 <div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strAddress?></label>
	  <textarea name="prospect_adresa" id="adresa" style="width:100%;"><?php echo $row["prospect_adresa"]?></textarea>
	</div>			 
				<div class="large-3 medium-3 small-3 cell">	
	  <label><?php echo $strSector?></label>
	  <input name="prospect_sector" id="oras" Type="text" value="<?php echo $row["prospect_sector"]?>" />
	</div>
<div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strNeighbourhood?></label>
	  <input name="prospect_cartier" id="judet" Type="text" value="<?php echo $row["prospect_cartier"]?>" />
	</div>
	</div>
				    <div class="grid-x grid-margin-x">	
	 <div class="large-6 medium-6 small-6 cell">
	  <label><?php echo $strContact?></label>
	  <input name="prospect_contact" Type="text" value="<?php echo $row["prospect_contact"]?>" />
	  	</div>
 	 <div class="large-3 medium-3 small-3 cell">
	  <label><?php echo $strPhone?></label>
	  <input name="prospect_telefon" Type="text"  value="<?php echo $row["prospect_telefon"]?>"/>
	</div>
	 <div class="large-3 medium-3 small-3 cell">			 
	  <label><?php echo $strEmail?></label>
	  <input name="prospect_email" Type="text" value="<?php echo $row["prospect_email"]?>" />
		</div>
 	</div>				
			    <div class="grid-x grid-margin-x">
				
			  <div class="large-12 medium-12 small-12 cell">
			  <label><?php echo $strActivities?></label>
	  <input name="prospect_activitate" Type="text" />
		</div>
		</div>
	 			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strProfile?></label>
	  <textarea name="prospect_caracterizare" id="myTextEditor" class="simple-html-editor" rows="5"></textarea>
		</div>
		</div>
	 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> 
	 <input Type="submit" value="<?php echo $strModify?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
<?php
}
?>
</div>
</div>