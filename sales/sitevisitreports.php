<?php
//update 08.01.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';

$strPageTitle="Administrare vizite prospecți";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$clearence=$_SESSION['function'];

$month= date('m');
$year=date('Y');
$day = date('d');

if ((isset( $_GET['aloc'])) && !empty( $_GET['aloc'])){
$aloc=$_GET['aloc'];}
Else{
$aloc=0;}
if ((isset( $_GET['cl'])) && !empty( $_GET['cl'])){
$cl=$_GET['cl'];}
Else{
$cl=0;}
if ((isset( $_GET['yr'])) && !empty( $_GET['yr'])){
$fyear=$_GET['yr'];
$year=$fyear;
}
Else{
$fyear=0;}
if ((isset( $_GET['fmonth'])) && !empty( $_GET['fmonth'])){
$fmonth=$_GET['fmonth'];}
Else{
$fmonth=0;}
?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM prospecti_vizite WHERE ID_vizita=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitevisitreports.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){
//insert new user
$client=trim($_POST["client_vizita"],"editprospects.php?mode=edit&cID=");

	$mSQL = "INSERT INTO prospecti_vizite(";
	$mSQL = $mSQL . "client_vizita,";
	$mSQL = $mSQL . "tip_vizita,";
	$mSQL = $mSQL . "data_vizita,";
	$mSQL = $mSQL . "scop_vizita,";
	$mSQL = $mSQL . "alocat,";
	$mSQL = $mSQL . "urmatoarea_vizita,";
	$mSQL = $mSQL . "observatii_vizita)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$client . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["tip_vizita"]) . "', ";
	$mSQL = $mSQL . "'" .$_POST["data_vizita"] . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["scop_vizita"]) . "', ";
	$mSQL = $mSQL . "'" .$uid . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["urmatoarea_vizita"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["observatii_vizita"]) . "') ";

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
    window.location = \"sitevisitreports.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{// edit
$client=trim($_POST["client_vizita"],"editprospects.php?mode=edit&cID=");
$strWhereClause = " WHERE prospecti_vizite.ID_vizita=" . $_GET["cID"] . ";";
$query= "UPDATE prospecti_vizite SET prospecti_vizite.client_vizita='" .$client . "' ," ;
$query= $query . "prospecti_vizite.scop_vizita='" .str_replace("'","&#39;",$_POST["scop_vizita"]) . "' ," ;
$query= $query . "prospecti_vizite.tip_vizita='" .str_replace("'","&#39;",$_POST["tip_vizita"]) . "' ," ;
$query= $query . "prospecti_vizite.data_vizita='" .$_POST["data_vizita"] . "' ," ;
$query= $query . "prospecti_vizite.urmatoarea_vizita='" .str_replace("'","&#39;",$_POST["urmatoarea_vizita"]) . "' ," ;
$query= $query . " prospecti_vizite.observatii_vizita='" .str_replace("'","&#39;",$_POST["observatii_vizita"]) . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"sitevisitreports.php\"
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
<script language="JavaScript" type="text/JavaScript">
$(document).ready(function() {
	$("#users").validate();
});
</script>
    <script src="<?php echo $strSiteURL ?>js/foundation/jquery.js"></script>

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
<script>
function removeIFrame(){
document.getElementById( 'editframe' ).style.display = 'block';
}
</script>
<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitevisitreports.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form Method="post" id="users" Action="sitevisitreports.php?mode=new" >
   			    <div class="grid-x grid-margin-x">
			  <div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strClient?></label>
	  <select name="client_vizita" class="required" ONCHANGE="document.getElementById('editprospect').src = this.options[this.selectedIndex].value">
           <option value=""><?php echo $strClient?></option>
          <?php 
		  if ($clearence=="MANAGER")
		  {		  $sql="SELECT prospect_ID, prospect_denumire from prospecti";}
	  else
		  {		  $sql="SELECT prospect_ID, prospect_denumire from prospecti where prospect_aloc='$code'";}
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="editprospects.php?mode=edit&cID=<?php echo $rss["prospect_ID"]?>"><?php echo $rss["prospect_denumire"]?></option>
          <?php
}?>
        </select>
		<input class="button" name="Close" type="button" value="<?php echo $strEdit?>" onClick="removeIFrame()" tabindex="10" /> 
</div>
			  <div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strType?></label>
 <input type="radio" name="tip_vizita" value="0" checked id="initiala"><label for="initiala"><?php echo $strInitial?></label>
 <input name="tip_vizita" Type="radio" value="1" id="followup"><label for="followup"><?php echo $strFollowup?></label>
</div>
			  <div class="large-4 medium-4 small-4 cell">  
      <label><?php echo $strDate?></label>
   <input name="data_vizita" Type="date" class="required" value="" />
		</div>
		</div>
			    <div class="grid-x grid-margin-x" id="editframe" style="display: none;">
			  <div class="large-12 medium-12 small-12 cell">
	  	   <iframe name="iframe" id="editprospect" src="" width="100%" border="0" frameBorder="0" scrolling="no" onload="resizeIframe(this)"></iframe>
	</div>
</div>		  
  <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strScope?></label>
	   <input name="scop_vizita" Type="text" class="required" value="" />
	</div>
</div>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell"> 
	  <label><?php echo $strDetails?></label>
	  <textarea name="observatii_vizita"  id="myTextEditor" class="myTextEditor" rows="5"></textarea>
	</div>
</div>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell"> 
	  <label><?php echo $strNextVisit?></label>
	  <textarea name="urmatoarea_vizita"  id="myTextEditor" class="myTextEditor" rows="5"></textarea>
	</div>
</div>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
  
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM prospecti_vizite WHERE ID_vizita=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitevisitreports.php" class="button"><?php echo $strBack?> <i class="fas fa-backward"></i></a></p>
</div>
</div>
<form Method="post" id="users" Action="sitevisitreports.php?mode=edit&cID=<?php echo $row['ID_vizita']?>" >
     			    <div class="grid-x grid-margin-x">
			  <div class="large-4 medium-4 small-4 cell">
	  <label><?php echo $strClient?></label>
	  <select name="client_vizita" class="required">
          <?php 
		  $sql="SELECT prospect_ID, prospect_denumire from prospecti where prospect_aloc='$code'";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  <?php if ($row["client_vizita"]==$rss["prospect_ID"]) echo "selected"; ?> value="<?php echo $rss["prospect_ID"]?>"><?php echo $rss["prospect_denumire"]?></option>
          <?php
}?>
        </select>
	</div>
			  <div class="large-4 medium-4 small-4 cell">
			  <label><?php echo $strType?></label>
	   <input type="radio" name="tip_vizita" value="0" <?php if ($row["tip_vizita"]==0) echo "checked"?> id="initiala"><label for="initiala"><?php echo $strInitial?></label>
 <input name="tip_vizita" Type="radio" value="1" <?php if ($row["tip_vizita"]==1 )echo "checked"?> id="followup"><label for="followup"><?php echo $strFollowup?></label>
	</div>
			  <div class="large-4 medium-4 small-4 cell">
           <label><?php echo $strDate?></label>
   <input name="data_vizita" Type="date" class="required" value="<?php echo $row["data_vizita"]?>" />
	</div>
	</div>
				    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell"> 
	  <label><?php echo $strScope?></label>
	   <input name="scop_vizita" Type="text" class="required" value="<?php echo $row["scop_vizita"]?>" />
		</div>
		</div>
	
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">  
	  <label><?php echo $strDetails?></label>
	  <textarea name="observatii_vizita"  id="myTextEditor" class="myTextEditor" rows="5"><?php echo $row["observatii_vizita"]?></textarea>
		</div>
		</div>
					    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">  
	  <label><?php echo $strNextVisit?></label>
	  <textarea name="urmatoarea_vizita"  id="myTextEditor" class="myTextEditor" rows="5"><?php echo $row["urmatoarea_vizita"]?></textarea>
		 </div>
		</div>
		 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="view")
{
	?>			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitevisitreports.php" class="button"><?php echo $strBack?> <i class="fas fa-backward"></i></a></p>
</div>
</div>
<?php

$query="SELECT ID_vizita, client_vizita, alocat, data_vizita, tip_vizita, scop_vizita, observatii_vizita, urmatoarea_vizita, consultanta_iso, consultanta_mediu, consultanta_haccp, analize, gdpr, altele,
prospect_denumire, prospect_ID
FROM prospecti_vizite, prospecti 
WHERE ID_vizita=$_GET[cID] AND prospect_ID=client_vizita";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
$row=ezpub_fetch_array($result);
	    		echo"<table id=\"rounded-corner\" summary=\"$strClients\" width=\"100%\">
				<tr><td>$strName</td><td>$row[prospect_denumire]</td></tr>
				<tr><td>$strDate</td><td>$row[data_vizita]</td></tr>
				<tr><td>$strScope</td><td>$row[scop_vizita]</td></tr>
				<tr><td>$strDetails</td><td>$row[observatii_vizita]</td></tr>
				<tr><td>$strNextVisit</td><td>$row[urmatoarea_vizita]</td></tr>
			<tr><td class=\"rounded-foot-left\"></td><td class=\"rounded-foot-right\">&nbsp;</td></tr></tfoot></table>";
}
Else
{
	?>
		 <script language="JavaScript" type="text/JavaScript">
<!-- jump menu
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
</script> 
		 		<div class="grid-x grid-padding-x ">
               <div class="large-3 medium-3 cell">
			   <label> <?php echo $strSeenBy?></label>
		 					<select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
							<option value="sitevisitreports.php?cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&aloc=<?php echo $aloc ?>" selected><?php echo $strPick?></option>
			<?php
			$query7="SELECT * FROM date_utilizatori WHERE utilizator_Function='SALES' ORDER By utilizator_Nume ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			if ((isset($_GET['aloc'])) && !empty($_GET['aloc'])){
			If ($seenby['strSeenBy']==$_GET['aloc']) {
			echo"<option selected value=\"sitevisitreports.php?cl=$cl&fmonth=$fmonth&yr=$year&aloc=".$seenby['utilizator_Code']."\">". $seenby['strUserName']."</option>";}
			Else{echo"<option value=\"sitevisitreports.php?cl=$cl&fmonth=$fmonth&yr=$year&aloc=".$seenby['utilizator_Code']."\">". $seenby['utilizator_Nume']." ". $seenby['utilizator_Prenume']."</option>";}}
			Else {echo"<option value=\"sitevisitreports.php?cl=$cl&fmonth=$fmonth&yr=$year&aloc=".$seenby['utilizator_Code']."\">". $seenby['utilizator_Nume']." ". $seenby['utilizator_Prenume']."</option>";}
			}
			?>
			</select>
			</div>
			 <div class="large-3 medium-3 cell">
			 <label> <?php echo $strClient?></label>
			 <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
<option value="sitevisitreports.php?cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&aloc=<?php echo $aloc ?>" selected><?php echo $strPick?></option>
			<?php
			$query7="SELECT DISTINCT client_vizita, prospect_denumire, prospect_ID	FROM prospecti, prospecti_vizite WHERE prospect_ID=client_vizita ORDER By prospect_denumire ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			if ((isset($_GET['cl'])) && !empty($_GET['cl'])){
			If ($seenby['client_vizita']==$_GET['cl']) {
			echo"<option selected value=\"sitevisitreports.php?aloc=$aloc&fmonth=$fmonth&yr=$year&cl=".$seenby['client_vizita']."\">". $seenby['prospect_denumire']."</option>";}
			Else{echo"<option value=\"sitevisitreports.php?aloc=$aloc&fmonth=$fmonth&yr=$year&cl=".$seenby['client_vizita']."\">". $seenby['prospect_denumire']."</option>";}}
			Else {echo"<option value=\"sitevisitreports.php?aloc=$aloc&fmonth=$fmonth&yr=$year&cl=".$seenby['client_vizita']."\">". $seenby['prospect_denumire']."</option>";}
			}
			?>
			</select>
			</div>
							 <div class="large-3 medium-3 cell">
			<label> <?php echo $strMonth?></label>
			 <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
	<option value="00" selected>--</option>
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
				echo "<option value=\"sitevisitreports.php?aloc=$aloc&cl=$cl&yr=$year&fmonth=".$m."\">$monthname</option>";}
				 
			?>
        </select> 
		</div>
				 <div class="large-3 medium-3 cell">
		 <label> <?php echo $strYear?></label>
			 <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
			<option value="sitevisitreports.php?cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&aloc=<?php echo $aloc ?>" selected><?php echo $strPick?></option>
			 <?php
			 			$query7="SELECT DISTINCT YEAR(data_vizita) as iyear FROM prospecti_vizite ORDER By YEAR(data_vizita) DESC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
						if ((isset($_GET['yr'])) && !empty($_GET['yr'])){
			If ($seenby['iyear']==$_GET['yr']) {
			echo"<option selected value=\"sitevisitreports.php?aloc=$aloc&cl$cl&fmonth=$fmonth&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}
			Else{echo"<option value=\"sitevisitreports.php?aloc=$aloc&cl=$cl&fmonth=$fmonth&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}}
			else {
			if ($year==$seenby['iyear']) 
			{echo "<option value=\"sitevisitreports.php?aloc=$aloc&cl=$cl&fmonth=$fmonth&yr=".$seenby['iyear']." \" selected >". $seenby['iyear']."</option>";}
			Else {echo"<option value=\"sitevisitreports.php?aloc=$aloc&cl=$cl&fmonth=$fmonth&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}
			}
			}
			 ?>
        </select>
		</div>
		</div>
			
	<?php
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"sitevisitreports.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";
$query="SELECT ID_vizita, client_vizita, alocat, data_vizita, tip_vizita, scop_vizita, observatii_vizita, urmatoarea_vizita, 
prospect_denumire, prospect_ID
FROM prospecti_vizite, prospecti WHERE 
YEAR(data_vizita)='$year' AND  
prospect_ID=client_vizita ";
if ($aloc!='0'){
$query= $query . " AND alocat='$aloc'";
};
if ($cl!='0'){
$query= $query . " AND client_vizita='$cl'";
};
if ($fmonth!='0'){
$query= $query . " AND MONTH(data_vizita)='$fmonth'";
};
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY data_vizita DESC $pages->limit";
$result=ezpub_query($conn,$query);

echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>
<div class=\"paginate\"><a href=\"sitevisitreports.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;</div>";
}
Else {
?>
<div class="paginate">
<?php
echo $strTotal . " " .$numar." ".$strVisits;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"sitevisitreports.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
</div>
<table width="100%">
	      <thead>
    	<tr>
  		<th><?php echo $strClient?></th>
			<th><?php echo $strDate?></th>
			<th><?php echo $strType?></th>
			<th><?php echo $strScope?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDetails?></th>
	      </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[prospect_denumire]</td>
			<td>"; echo date('d.m.Y',strtotime($row["data_vizita"]));
			echo "</td>
			<td>";
			If($row["tip_vizita"]==0) {echo $strInitial;}
Else {echo $strFollowup;}			
			echo "</td>
			<td>$row[scop_vizita]</td>
			 <td><a href=\"sitevisitreports.php?mode=edit&cID=$row[ID_vizita]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			 <td><a href=\"sitevisitreports.php?mode=view&cID=$row[ID_vizita]\"><i class=\"fa fa-search-plus fa-xl\" title=\"$strEdit\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td colspan=\"6\">&nbsp;</td></tr></tfoot></table>";
?>
<div class="paginate">
<?php
echo $pages->display_pages() . " <a href=\"sitevisitreports.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
</div>
<?php
}
}
}

?>
</div>
</div>
<?php
include '../bottom.php';
?>