<?php
//update 29.07.2025
//work in progress under review
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare activități";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
}
$uid=$_SESSION['uid'];
$code=$_SESSION['$code'];
$day = date('d');
$year = date('Y');
$today = date("Y-m-d");

If (!IsSet($_GET["month"]))
{$month = date('m');}
Else
{$month = $_GET["month"];}



if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){
//insert new user
If ($_POST["task_status"]==1)
{
	$taskended=$today;
	$lastupdate=$today;
}
Else
{
	$taskended="0000-00-00";
	$lastupdate=$today;
}
$query="SELECT * FROM date_activitati_clienti WHERE ID_activitati_client=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
If ($row["Activitate_Client_Frecventa"]==1)
{
	$timestamp = strtotime($row["Activitate_Client_Termen"]);
	$d=date("d", $timestamp);
	$deadline="$year-$month-$d";
}
If ($row["Activitate_Client_Frecventa"]==3)
{
	$timestamp = strtotime($row["Activitate_Client_Termen"]);
	$d=date("d", $timestamp);
	$month=(int)$month;
	if($month>1 && $month <=3) {$qendmonth=4;}
	if($month>4 && $month<=6) {$qendmonth=7;}
	if($month>7 && $month<=9) {$qendmonth=10;}
	if($month>10 && $month<=12) {$qendmonth=1;}
	if($month==1 || $month==4  || $month==7  || $month==10 ) {$qendmonth=$month;}
	$deadline="$year-$qendmonth-$d";
}
If ($row["Activitate_Client_Frecventa"]==6)
{
	$timestamp = strtotime($row["Activitate_Client_Termen"]);
	$d=date("d", $timestamp);
	$month=(int)$month;
	if($month>1 && $month<=6) {$sendmonth=7;}
	if($month>6 && $month<=12) {$sendmonth=1;}
	if($month==1 || $month==7 ) {$sendmonth=$month;}
	$deadline="$year-$sendmonth-$d";
}
If ($row["Activitate_Client_Frecventa"]==12)
{
	$timestamp = strtotime($row["Activitate_Client_Termen"]);
	$d=date("d", $timestamp);
	$monthd=date("m", $timestamp);
	$deadline="$year-$monthd-$d";
}
If ($row["Activitate_Client_Frecventa"]==00)
{
	$timestamp = strtotime($row["Activitate_Client_Termen"]);
	$deadline=$row["Activitate_Client_Termen"];
}


	$mSQL = "INSERT INTO date_activitati_clienti_finalizate(";
	$mSQL = $mSQL . "activitate_client_ID,";
	$mSQL = $mSQL . "task_ended,";
	$mSQL = $mSQL . "task_last_update,";
	$mSQL = $mSQL . "task_deadline,";
	$mSQL = $mSQL . "task_status,";
	$mSQL = $mSQL . "task_details)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$_GET["cID"] . "', ";
	$mSQL = $mSQL . "'" .$taskended . "', ";
	$mSQL = $mSQL . "'" .$lastupdate . "', ";
	$mSQL = $mSQL . "'" .$deadline . "', ";
	$mSQL = $mSQL . "'" .$_POST["task_status"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["task_details"] . "') ";
			
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
If ($_POST["task_status"]==1)
{
	$taskended=$today;
	$lastupdate=$today;
}
Else
{
	$taskended="0000-00-00";
	$lastupdate=$today;
}
$strWhereClause = " WHERE date_activitati_clienti_finalizate.task_ID=" . $_GET["cID"] . ";";
$query= "UPDATE date_activitati_clienti_finalizate SET date_activitati_clienti_finalizate.task_ended='" .$taskended . "' ," ;
$query= $query . " date_activitati_clienti_finalizate.task_last_update='" .$lastupdate . "' ," ;
$query= $query . " date_activitati_clienti_finalizate.task_status='" .$_POST["task_status"] . "' ," ;
$query= $query . " date_activitati_clienti_finalizate.task_details='" .$_POST["task_details"] . "' "; 
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
     		//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<script language="JavaScript" type="text/JavaScript">
  function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
  }
</script>
<?php 	echo "<h1>$strPageTitle". " - ". $monthname ."</h1>";?>
<script src="<?php echo $strSiteURL ?>/js/foundation/jquery.js"></script>
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

<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitetasks.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form Method="post" id="users" Action="sitetasks.php?mode=new&cID=<?php echo $_GET["cID"]?>&month=<?php echo $month?>" >
	    <div class="grid-x grid-margin-x">
			  <div class="large-3 medium-3 small-3 cell">
      <label><?php echo $strActive?></label>
      <input name="task_status" Type="radio" value="0" checked /> <?php echo $strYes?><input name="task_status" Type="radio" value="1"><?php echo $strNo?>
	  </div>
	 <div class="large-9 medium-9 small-9 cell">
	  <label><?php echo $strDetails?><label>
	  <textarea name="task_details" id="myTextEditor" class="myTextEditor" rows="5"></textarea>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> 
		<input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM date_activitati_clienti_finalizate WHERE task_ID=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitetasks.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form Method="post" id="users" Action="sitetasks.php?mode=edit&cID=<?php echo $row['task_ID']?>" >
	    <div class="grid-x grid-margin-x">
			  <div class="large-3 medium-3 small-3 cell">
      <label><?php echo $strActive?></label>
      <input name="task_status" Type="radio" value="0" <?php If ($row["task_status"]==0) echo "checked"?> /> <?php echo $strYes?><input name="task_status" Type="radio" value="1" <?php If ($row["task_status"]==1) echo "checked"?>><?php echo $strNo?>
 	 </div>
	 <div class="large-9 medium-9 small-9 cell">
	  <label><?php echo $strDetails?><label>
	  <textarea name="task_details" id="myTextEditor" class="myTextEditor" rows="5"><?php echo $row["task_details"]?></textarea>
	  </div>
	  </div>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
<?php
echo "<h2>$strUpdates</h2>";
$query33="SELECT * FROM date_activitati_clienti_follow_up WHERE ID_activitate=$_GET[cID]";
$result33=ezpub_query($conn,$query33);
$numar33=ezpub_num_rows($result33,$query33);
echo ezpub_error($conn);
if ($numar33==0)
{
echo "<a href=\"sitetasksfollowups.php?mode=new&tID=$_GET[cID]\"><i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a><br />";
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<table id="rounded-corner" summary="<?php echo $strdate_activitati_clienti?>" width="100%">
	      <thead>
    	<tr>
			<th><?php echo $strDetails?></th>
			<th><?php echo $strDate?></th>
			<th><?php echo $strEdit?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row33=ezpub_fetch_array($result33)){
    		echo"<tr>
			<td>$row33[followup_detail]</td>
			<td>$row33[followup_date]</td>
			<td><a href=\"sitetasksfollowups.php?mode=edit&tID=$_GET[cID]&fID=$row33[followup_ID]\"  target=\"followups\"><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>";
			echo "</tr>";
}
echo "</tbody><tfoot><tr><td></td><td></td><td>&nbsp;</td></tr></tfoot></table>";

}
?>

<div id="iframe1">
<iframe src="sitetasksfollowups.php?mode=new&tID=<?php echo $row['task_ID']?>" width="100%" height="600" name="followups"></iframe>
</div>
<?php
}
Else
{

//monthly
$query1="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_contracte.Contract_Alocat, clienti_contracte.ID_Client,
date_activitati_clienti.ID_Client, date_activitati_clienti.ID_Activitate, date_activitati_clienti.Activitate_Client_Frecventa, date_activitati_clienti.Activitate_Client_Termen,
date_activitati.ID_Activitate, date_activitati.Activitate_Nume, ID_activitati_client
FROM date_activitati_clienti, clienti_date, date_activitati, clienti_contracte
WHERE  clienti_date.ID_Client=date_activitati_clienti.ID_Client AND clienti_contracte.ID_Client=clienti_date.ID_Client
AND date_activitati_clienti.Activitate_Client_Frecventa=1 AND date_activitati_clienti.ID_Activitate=date_activitati.ID_Activitate
ORDER By Activitate_Nume ASC";
$result1=ezpub_query($conn,$query1);
$numar1=ezpub_num_rows($result1,$query1);
//quaterly
$query2="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_contracte.Contract_Alocat, clienti_contracte.ID_Client,
date_activitati_clienti.ID_Client, date_activitati_clienti.ID_Activitate, date_activitati_clienti.Activitate_Client_Frecventa, date_activitati_clienti.Activitate_Client_Termen,
date_activitati.ID_Activitate, date_activitati.Activitate_Nume, ID_activitati_client
FROM date_activitati_clienti, clienti_date, date_activitati, clienti_contracte
WHERE  clienti_date.ID_Client=date_activitati_clienti.ID_Client AND clienti_contracte.ID_Client=clienti_date.ID_Client 
AND date_activitati_clienti.Activitate_Client_Frecventa=3 AND date_activitati_clienti.ID_Activitate=date_activitati.ID_Activitate
ORDER By Activitate_Nume ASC";
$result2=ezpub_query($conn,$query2);
$numar2=ezpub_num_rows($result2,$query2);
//semestrial
$query3="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_contracte.Contract_Alocat, clienti_contracte.ID_Client,
date_activitati_clienti.ID_Client, date_activitati_clienti.ID_Activitate, date_activitati_clienti.Activitate_Client_Frecventa, date_activitati_clienti.Activitate_Client_Termen,
date_activitati.ID_Activitate, date_activitati.Activitate_Nume, ID_activitati_client
FROM date_activitati_clienti, clienti_date, date_activitati, clienti_contracte
WHERE  clienti_date.ID_Client=date_activitati_clienti.ID_Client AND clienti_contracte.ID_Client=clienti_date.ID_Client
AND date_activitati_clienti.Activitate_Client_Frecventa=6 AND date_activitati_clienti.ID_Activitate=date_activitati.ID_Activitate
ORDER By Activitate_Nume ASC";
$result3=ezpub_query($conn,$query3);
$numar3=ezpub_num_rows($result3,$query3);	
//yearly
$query4="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_contracte.Contract_Alocat, clienti_contracte.ID_Client,
date_activitati_clienti.ID_Client, date_activitati_clienti.ID_Activitate, date_activitati_clienti.Activitate_Client_Frecventa, date_activitati_clienti.Activitate_Client_Termen,
date_activitati.ID_Activitate, date_activitati.Activitate_Nume, ID_activitati_client
FROM date_activitati_clienti, clienti_date, date_activitati, clienti_contracte
WHERE  clienti_date.ID_Client=date_activitati_clienti.ID_Client AND clienti_contracte.ID_Client=clienti_date.ID_Client
AND date_activitati_clienti.Activitate_Client_Frecventa=12 AND date_activitati_clienti.ID_Activitate=date_activitati.ID_Activitate
ORDER By Activitate_Nume ASC";
$result4=ezpub_query($conn,$query4);
$numar4=ezpub_num_rows($result4,$query4);
//other
$query5="SELECT DISTINCT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_contracte.Contract_Alocat, clienti_contracte.ID_Client,
date_activitati_clienti.ID_Client, date_activitati_clienti.ID_Activitate, date_activitati_clienti.Activitate_Client_Frecventa, date_activitati_clienti.Activitate_Client_Termen,
date_activitati.ID_Activitate, date_activitati.Activitate_Nume, ID_activitati_client
FROM date_activitati_clienti, clienti_date, date_activitati, clienti_contracte
WHERE  clienti_date.ID_Client=date_activitati_clienti.ID_Client 
AND date_activitati_clienti.Activitate_Client_Frecventa=00 AND date_activitati_clienti.ID_Activitate=date_activitati.ID_Activitate 
AND clienti_date.ID_Client=date_activitati_clienti.ID_Client AND clienti_contracte.ID_Client=clienti_date.ID_Client
ORDER By Activitate_Nume ASC";
$result5=ezpub_query($conn,$query5);
$numar5=ezpub_num_rows($result5,$query5);
$query6="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire,  
date_autorizatii_clienti.ID_Client, date_autorizatii_clienti.ID_Autorizatie, date_autorizatii_clienti.Autorizatie_Client_Emitere, 
date_autorizatii_clienti.Autorizatie_Client_Expirare, date_autorizatii.ID_autorizatii, Autorizatie, date_autorizatii_clienti.Autorizatie_Client_Tip, 
ID_Autorizatie_Client, date_utilizatori.utilizator_ID, date_utilizatori.utilizator_Prenume
FROM date_autorizatii_clienti, clienti_date, date_autorizatii, date_utilizatori
WHERE clienti_date.ID_Client=date_autorizatii_clienti.ID_Client 
AND date_autorizatii_clienti.ID_Autorizatie=date_autorizatii.ID_autorizatii 
AND date_utilizatori.utilizator_ID=date_autorizatii_clienti.ID_User
AND (CASE
    WHEN date_autorizatii_clienti.Autorizatie_Client_Tip='1' THEN (DATEDIFF(Now(), Autorizatie_Client_Emitere))>275
    ELSE Autorizatie_Client_Expirare BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 90 DAY) END)";
$query6=$query6 . "ORDER By Client_Denumire ASC";
$result6=ezpub_query($conn,$query6);
$numar6=ezpub_num_rows($result6,$query6);
	$previousmonth=(int)$month-1;
	$nextmonth=(int)$month+1;
?>
	
<div class="clearfix">
<?php if ($previousmonth!=0)
{echo "<a href=\"$strSiteURL"."admin\sitetasks.php?month=$previousmonth\""." class=\"button float-left\">$strPreviousMonth</a>";}
Else
{echo "<a class=\"button float-left\">$strPreviousMonth</a>";}
if ($nextmonth!=13)
{echo "<a href=\"$strSiteURL"."admin\sitetasks.php?month=$nextmonth\""." class=\"button float-right\">$strNextMonth</a>";}
Else
{echo "<a class=\"button float-right\">$strNextMonth</a>";}
?>
</div>

<ul class="tabs" data-deep-link="true" data-update-history="true" data-deep-link-smudge="true" data-deep-link-smudge-delay="500" data-tabs id="tasks">
  <li class="tabs-title is-active"><a href="sitetasks.php#panel1" aria-selected="true"><?php echo $strMonthlyActivities?> - <?php echo $numar1?></a></li>
  <li class="tabs-title"><a href="sitetasks.php#panel2"><?php echo $strQuaterlyActivities?> - <?php echo $numar2?></a></li>
  <li class="tabs-title"><a href="sitetasks.php#panel3"><?php echo $strSemestrialActivities?> - <?php echo $numar3?></a></li>
  <li class="tabs-title"><a href="sitetasks.php#panel4"><?php echo $strYearlyActivities?> - <?php echo $numar4?></a></li>
  <li class="tabs-title"><a href="sitetasks.php#panel5"><?php echo $strOtherActivities?> - <?php echo $numar5?> </a></li>
  <li class="tabs-title"><a href="sitetasks.php#panel6"><?php echo $strAuthorizations?> - <?php echo $numar6?> </a></li>
</ul>
<div class="tabs-content" data-tabs-content="tasks">
<div class="tabs-panel is-active" id="panel1">
<?php

echo "<h2>$strMonthlyActivities</h2>";
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_contracte.Contract_Alocat, clienti_contracte.ID_Client,
date_activitati_clienti.ID_Client, date_activitati_clienti.ID_Activitate, date_activitati_clienti.Activitate_Client_Frecventa, date_activitati_clienti.Activitate_Client_Termen,
date_activitati.ID_Activitate, date_activitati.Activitate_Nume, ID_activitati_client, date_utilizatori.utilizator_ID, date_utilizatori.utilizator_Prenume
FROM date_activitati_clienti, clienti_date, date_activitati, clienti_contracte, date_utilizatori
WHERE  clienti_date.ID_Client=date_activitati_clienti.ID_Client AND clienti_contracte.ID_Client=clienti_date.ID_Client AND date_utilizatori.utilizator_ID=clienti_contracte.Contract_Alocat
AND date_activitati_clienti.Activitate_Client_Frecventa=1 AND date_activitati_clienti.ID_Activitate=date_activitati.ID_Activitate AND date_activitati_clienti.ID_User=date_utilizatori.utilizator_ID
ORDER By utilizator_Prenume ASC, Activitate_Nume ASC";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<table id="rounded-corner" width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strClient?></th>
        	<th><?php echo $strSeenBy?></th>
			<th><?php echo $strActivity?></th>
			<th><?php echo $strDeadline?></th>
			<th><?php echo $strUpdates?></th>
			<th><?php echo $strStatus?></th>
			<th><?php echo $strDate?></th>
			<th><?php echo $strEdit?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
	$timestamp = strtotime($row["Activitate_Client_Termen"]);
	$deadline=date("d", $timestamp);
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[utilizator_Prenume]</td>
			<td>$row[Activitate_Nume]</td>
			<td>$deadline</td>";
$query1="Select * FROM date_activitati_clienti_finalizate 
WHERE activitate_client_ID=$row[ID_activitati_client] AND MONTH(task_ended)=$month AND YEAR(task_ended)=$year
OR activitate_client_ID=$row[ID_activitati_client] AND MONTH(task_last_update)=$month AND YEAR(task_last_update)=$year
OR activitate_client_ID=$row[ID_activitati_client] AND MONTH(task_deadline)=$month AND YEAR(task_deadline)=$year

";			
$result1=ezpub_query($conn,$query1);
$numar=ezpub_num_rows($result1,$query1);
$row1=ezpub_fetch_array($result1);
if ($numar==0) 
{
echo "<td><i class=\"large fas fa-sticky-note\" title=\"$strNoUpdates\"></i></td>
<td><a href=\"sitetasks.php?mode=new&cID=$row[ID_activitati_client]&month=$month\" ><i class=\"far fa-edit fa-xl\" title=\"$strOpen\"></i></a></td>
<td><i class=\"fas fa-calendar-minus\" title=\"$strNotStarted\"></i></td>
<td><i class=\"large fa fa-lock\" title=\"$strLock\"></i></td>";
}
Else {
	If ($row1["task_status"]==1)
	{	if ($row1["task_follow_ups"]==0)
{echo "<td><i class=\"large fas fa-sticky-note\" title=\"$strNoUpdates\"></i></td>";}	
Else
	{echo "<td><i class=\"large far fa-sticky-note\" title=\"$strUpdates\"></i></td>";}	
		echo "<td><i class=\"large fa fa-lock\" title=\"$strLock\"></i></td>
<td>";
echo date("d.m.Y",strtotime($row1["task_ended"]));
echo "</td>
<td><a href=\"sitetasks.php?mode=edit&cID=$row1[task_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>";
}
Else
{	if ($row1['task_follow_ups']==0)
{echo "<td><i class=\"large fas fa-sticky-note\" title=\"$strNoUpdates\"></i></td>";}	
Else
	{echo "<td><i class=\"large far fa-sticky-note\" title=\"$strUpdates\"></i></td>";}	
	echo "<td><i class=\"large fas fa-hourglass-start\" title=\"$strActivityStarted\"></i></td>
<td>";
If ($row1["task_last_update"]!="") {echo date("d.m.Y",strtotime($row1["task_last_update"]));};
echo "</td>
<td><a href=\"sitetasks.php?mode=edit&cID=$row1[task_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>";
}
	
}			
			echo "</tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"6\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";

}//end monthly
?>
</div>
<div class="tabs-panel" id="panel2">
<?php
//Quaterly

    echo "<h2>$strQuaterlyActivities</h2>";
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_contracte.Contract_Alocat, clienti_contracte.ID_Client,
date_activitati_clienti.ID_Client, date_activitati_clienti.ID_Activitate, date_activitati_clienti.Activitate_Client_Frecventa, date_activitati_clienti.Activitate_Client_Termen,
date_activitati.ID_Activitate, date_activitati.Activitate_Nume, ID_activitati_client, date_utilizatori.utilizator_ID, date_utilizatori.utilizator_Prenume
FROM date_activitati_clienti, clienti_date, date_activitati, clienti_contracte, date_utilizatori
WHERE  clienti_date.ID_Client=date_activitati_clienti.ID_Client AND clienti_contracte.ID_Client=clienti_date.ID_Client AND date_utilizatori.utilizator_ID=clienti_contracte.Contract_Alocat
AND date_activitati_clienti.Activitate_Client_Frecventa=3 AND date_activitati_clienti.ID_Activitate=date_activitati.ID_Activitate AND date_activitati_clienti.ID_User=date_utilizatori.utilizator_ID
ORDER By utilizator_Prenume ASC, Activitate_Nume ASC";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<table id="rounded-corner" summary="<?php echo $strdate_activitati_clienti?>" width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strClient?></th>
        	<th><?php echo $strSeenBy?></th>
			<th><?php echo $strActivity?></th>
			<th><?php echo $strDeadline?></th>
			<th><?php echo $strUpdates?></th>
			<th><?php echo $strStatus?></th>
			<th><?php echo $strDate?></th>
			<th><?php echo $strEdit?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
	$timestamp = strtotime($row["Activitate_Client_Termen"]);
	$day=date("d", $timestamp);
	$month=(int)$month;
	if($month>1 && $month <=3) {$qendmonth=4;}
	if($month>4 && $month<=6) {$qendmonth=7;}
	if($month>7 && $month<=9) {$qendmonth=10;}
	if($month>10 && $month<=12) {$qendmonth=1;}
	if($month==1 || $month==4  || $month==7  || $month==10 ) {$qendmonth=$month;}
     		//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
	$deadline=$day." ".$monthname;
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[utilizator_Prenume]</td>
			<td>$row[Activitate_Nume]</td>
			<td>$deadline</td>";	
$query1="Select * FROM date_activitati_clienti_finalizate 
WHERE activitate_client_ID=$row[ID_activitati_client] AND MONTH(task_ended)=$month AND YEAR(task_ended)=$year
OR activitate_client_ID=$row[ID_activitati_client] AND MONTH(task_last_update)=$month AND YEAR(task_last_update)=$year
OR activitate_client_ID=$row[ID_activitati_client] AND MONTH(task_deadline)=$month AND YEAR(task_deadline)=$year";			
$result1=ezpub_query($conn,$query1);
$numar=ezpub_num_rows($result1,$query1);
$row1=ezpub_fetch_array($result1);
if ($numar==0) 
{
echo "<td><i class=\"large fas fa-sticky-note\" title=\"$strNoUpdates\"></i></td>
<td><a href=\"sitetasks.php?mode=new&cID=$row[ID_activitati_client]&month=$month\" ><i class=\"far fa-edit fa-xl\" title=\"$strOpen\"></i></a></td>
<td><i class=\"fas fa-calendar-minus\" title=\"$strNotStarted\"></i></td>
<td><i class=\"large fa fa-lock\" title=\"$strLock\"></i></td>";
}
Else {
	If ($row1["task_status"]==1)
	{	if ($row1['task_follow_ups']==0)
{echo "<td><i class=\"large fas fa-sticky-note\" title=\"$strNoUpdates\"></i></td>";}	
Else
	{echo "<td><i class=\"large far fa-sticky-note\" title=\"$strUpdates\"></i></td>";}	
		echo "<td><i class=\"large fa fa-lock\" title=\"$strLock\"></i></td>
<td>";
echo date("d.m.Y",strtotime($row1["task_ended"]));
echo "</td>
<td><a href=\"sitetasks.php?mode=edit&cID=$row1[task_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>";
}
Else
{	if ($row1['task_follow_ups']==0)
{echo "<td><i class=\"large fas fa-sticky-note\" title=\"$strNoUpdates\"></i></td>";}	
Else
	{echo "<td><i class=\"large far fa-sticky-note\" title=\"$strUpdates\"></i></td>";}	
	echo "<td><i class=\"large fas fa-hourglass-start\" title=\"$strActivityStarted\"></i></td>
<td>";
If ($row1["task_last_update"]!="") {echo date("d.m.Y",strtotime($row1["task_last_update"]));};
echo "</td>
<td><a href=\"sitetasks.php?mode=edit&cID=$row1[task_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>";
}
	
}			
			echo "</tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"6\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";

}


//end quaterly
?>
</div>
<div class="tabs-panel" id="panel3">
<?php
//semestrial

    echo "<h2>$strSemestrialActivities</h2>";
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_contracte.Contract_Alocat, clienti_contracte.ID_Client,
date_activitati_clienti.ID_Client, date_activitati_clienti.ID_Activitate, date_activitati_clienti.Activitate_Client_Frecventa, date_activitati_clienti.Activitate_Client_Termen,
date_activitati.ID_Activitate, date_activitati.Activitate_Nume, ID_activitati_client, date_utilizatori.utilizator_ID, date_utilizatori.utilizator_Prenume
FROM date_activitati_clienti, clienti_date, date_activitati, clienti_contracte, date_utilizatori
WHERE  clienti_date.ID_Client=date_activitati_clienti.ID_Client AND clienti_contracte.ID_Client=clienti_date.ID_Client AND date_utilizatori.utilizator_ID=clienti_contracte.Contract_Alocat
AND date_activitati_clienti.Activitate_Client_Frecventa=6 AND date_activitati_clienti.ID_Activitate=date_activitati.ID_Activitate AND date_activitati_clienti.ID_User=date_utilizatori.utilizator_ID
ORDER By utilizator_Prenume ASC, Activitate_Nume ASC";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<table id="rounded-corner" summary="<?php echo $strdate_activitati_clienti?>" width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strClient?></th>
        	<th><?php echo $strSeenBy?></th>
			<th><?php echo $strActivity?></th>
			<th><?php echo $strDeadline?></th>
			<th><?php echo $strUpdates?></th>
			<th><?php echo $strStatus?></th>
			<th><?php echo $strDate?></th>
			<th><?php echo $strEdit?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
	$timestamp = strtotime($row["Activitate_Client_Termen"]);
	$day=date("d", $timestamp);
	if($month>1 && $month<=6) {$sendmonth=7;}
	if($month>7 && $month<=12) {$sendmonth=1;}
	if($month==1 || $month==7 ) {$sendmonth=$month;}
     		//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
	$deadline=$day." ".$monthname;
    
	echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[utilizator_Prenume]</td>
			<td>$row[Activitate_Nume]</td>
			<td>$deadline</td>";	
$query1="Select * FROM date_activitati_clienti_finalizate 
WHERE activitate_client_ID=$row[ID_activitati_client] AND MONTH(task_ended)=$month AND YEAR(task_ended)=$year
OR activitate_client_ID=$row[ID_activitati_client] AND MONTH(task_last_update)=$month AND YEAR(task_last_update)=$year
OR activitate_client_ID=$row[ID_activitati_client] AND MONTH(task_deadline)=$month AND YEAR(task_deadline)=$year";	
$result1=ezpub_query($conn,$query1);
$numar=ezpub_num_rows($result1,$query1);
$row1=ezpub_fetch_array($result1);
if ($numar==0) 
{
echo "<td><i class=\"large fas fa-sticky-note\" title=\"$strNoUpdates\"></i></td>
<td><a href=\"sitetasks.php?mode=new&cID=$row[ID_activitati_client]&month=$month\" ><i class=\"far fa-edit fa-xl\" title=\"$strOpen\"></i></a></td>
<td><i class=\"fas fa-calendar-minus\" title=\"$strNotStarted\"></i></td>
<td><i class=\"large fa fa-lock\" title=\"$strLock\"></i></td>";
}
Else {
	If ($row1["task_status"]==1)
	{	if ($row1['task_follow_ups']==0)
{echo "<td><i class=\"large fas fa-sticky-note\" title=\"$strNoUpdates\"></i></td>";}	
Else
	{echo "<td><i class=\"large far fa-sticky-note\" title=\"$strUpdates\"></i></td>";}	
		echo "<td><i class=\"large fa fa-lock\" title=\"$strLock\"></i></td>
<td>";
echo date("d.m.Y",strtotime($row1["task_ended"]));
echo "</td>
<td><a href=\"sitetasks.php?mode=edit&cID=$row1[task_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>";
}
Else
{	if ($row1['task_follow_ups']==0)
{echo "<td><i class=\"large fas fa-sticky-note\" title=\"$strNoUpdates\"></i></td>";}	
Else
	{echo "<td><i class=\"large far fa-sticky-note\" title=\"$strUpdates\"></i></td>";}	
	echo "<td><i class=\"large fas fa-hourglass-start\" title=\"$strActivityStarted\"></i></td>
<td>";
If ($row1["task_last_update"]!="") {echo date("d.m.Y",strtotime($row1["task_last_update"]));};
echo "</td>
<td><a href=\"sitetasks.php?mode=edit&cID=$row1[task_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>";
}
	
}			
			echo "</tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"6\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";

}


//end semsterial
?>
</div>
<div class="tabs-panel" id="panel4">
<?php
//yearly

    echo "<h2>$strYearlyActivities</h2>";
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_contracte.Contract_Alocat, clienti_contracte.ID_Client,
date_activitati_clienti.ID_Client, date_activitati_clienti.ID_Activitate, date_activitati_clienti.Activitate_Client_Frecventa, date_activitati_clienti.Activitate_Client_Termen,
date_activitati.ID_Activitate, date_activitati.Activitate_Nume, ID_activitati_client, date_utilizatori.utilizator_ID, date_utilizatori.utilizator_Prenume
FROM date_activitati_clienti, clienti_date, date_activitati, clienti_contracte, date_utilizatori
WHERE  clienti_date.ID_Client=date_activitati_clienti.ID_Client AND clienti_contracte.ID_Client=clienti_date.ID_Client AND date_utilizatori.utilizator_ID=clienti_contracte.Contract_Alocat
AND date_activitati_clienti.Activitate_Client_Frecventa=12 AND date_activitati_clienti.ID_Activitate=date_activitati.ID_Activitate AND date_activitati_clienti.ID_User=date_utilizatori.utilizator_ID
ORDER By utilizator_Prenume ASC, Activitate_Nume ASC";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<table width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strClient?></th>
        	<th><?php echo $strSeenBy?></th>
			<th><?php echo $strActivity?></th>
			<th><?php echo $strDeadline?></th>
			<th><?php echo $strUpdates?></th>
			<th><?php echo $strStatus?></th>
			<th><?php echo $strDate?></th>
			<th><?php echo $strEdit?></th>
        </tr>
		</thead>
<?php 
While ($row=ezpub_fetch_array($result)){
	$timestamp = strtotime($row["Activitate_Client_Termen"]);
	$day=date("d", $timestamp);
	$yearmonth=date("m", $timestamp);
     		//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
	$deadline=$day." ".$monthname;
   
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[utilizator_Prenume]</td>
			<td>$row[Activitate_Nume]</td>
			<td>$deadline</td>";	
$query1="Select * FROM date_activitati_clienti_finalizate 
WHERE activitate_client_ID=$row[ID_activitati_client] AND MONTH(task_ended)=$month AND YEAR(task_ended)=$year
OR activitate_client_ID=$row[ID_activitati_client] AND MONTH(task_last_update)=$month AND YEAR(task_last_update)=$year
OR activitate_client_ID=$row[ID_activitati_client] AND MONTH(task_deadline)=$month AND YEAR(task_deadline)=$year";	
$result1=ezpub_query($conn,$query1);
$numar=ezpub_num_rows($result1,$query1);
$row1=ezpub_fetch_array($result1);
if ($numar==0) 
{
echo "<td><i class=\"large fas fa-sticky-note\" title=\"$strNoUpdates\"></i></td>
<td><a href=\"sitetasks.php?mode=new&cID=$row[ID_activitati_client]&month=$month\" ><i class=\"far fa-edit fa-xl\" title=\"$strOpen\"></i></a></td>
<td><i class=\"fas fa-calendar-minus\" title=\"$strNotStarted\"></i></td>
<td><i class=\"large fa fa-lock\" title=\"$strLock\"></i></td>";
}
Else {
	If ($row1["task_status"]==1)
	{	if ($row1['task_follow_ups']==0)
{echo "<td><i class=\"large fas fa-sticky-note\" title=\"$strNoUpdates\"></i></td>";}	
Else
	{echo "<td><i class=\"large far fa-sticky-note\" title=\"$strUpdates\"></i></td>";}	
		echo "<td><i class=\"large fa fa-lock\" title=\"$strLock\"></i></td>
<td>";
echo date("d.m.Y",strtotime($row1["task_ended"]));
echo "</td>
<td><a href=\"sitetasks.php?mode=edit&cID=$row1[task_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>";
}
Else
{	if ($row1['task_follow_ups']==0)
{echo "<td><i class=\"large fas fa-sticky-note\" title=\"$strNoUpdates\"></i></td>";}	
Else
	{echo "<td><i class=\"large far fa-sticky-note\" title=\"$strUpdates\"></i></td>";}	
	echo "<td><i class=\"large fas fa-hourglass-start\" title=\"$strActivityStarted\"></i></td>
<td>";
If ($row1["task_last_update"]!="") {echo date("d.m.Y",strtotime($row1["task_last_update"]));};
echo "</td>
<td><a href=\"sitetasks.php?mode=edit&cID=$row1[task_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>";
}
	
}			
			echo "</tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"6\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";

}

//end semsterial
?>
</div>
<div class="tabs-panel" id="panel5">
<?php
echo "<h2>$strOtherActivities</h2>";
$query="SELECT DISTINCT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_contracte.Contract_Alocat, clienti_contracte.ID_Client,
date_activitati_clienti.ID_Client, date_activitati_clienti.ID_Activitate, date_activitati_clienti.Activitate_Client_Frecventa, date_activitati_clienti.Activitate_Client_Termen,
date_activitati.ID_Activitate, date_activitati.Activitate_Nume, ID_activitati_client, date_utilizatori.utilizator_ID, date_utilizatori.utilizator_Prenume
FROM date_activitati_clienti, clienti_date, date_activitati, clienti_contracte, date_utilizatori
WHERE  clienti_date.ID_Client=date_activitati_clienti.ID_Client AND date_utilizatori.utilizator_ID=clienti_contracte.Contract_Alocat AND date_activitati_clienti.ID_User=date_utilizatori.utilizator_ID
AND date_activitati_clienti.Activitate_Client_Frecventa=00 AND date_activitati_clienti.ID_Activitate=date_activitati.ID_Activitate AND clienti_contracte.ID_Client=clienti_date.ID_Client
ORDER By utilizator_Prenume ASC, Activitate_Nume ASC";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<table id="rounded-corner" summary="<?php echo $strdate_activitati_clienti?>" width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strClient?></th>
        	<th><?php echo $strSeenBy?></th>
			<th><?php echo $strActivity?></th>
			<th><?php echo $strDeadline?></th>
			<th><?php echo $strUpdates?></th>
			<th><?php echo $strStatus?></th>
			<th><?php echo $strDate?></th>
			<th><?php echo $strEdit?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
	$timestamp = strtotime($row["Activitate_Client_Termen"]);
	$day=date("d", $timestamp);
	$month=date("m", $timestamp);
     		//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
	$deadline=$day." ".$monthname;
	echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[utilizator_Prenume]</td>
			<td>$row[Activitate_Nume]</td>
			<td>$deadline</td>";	
$query1="Select * FROM date_activitati_clienti_finalizate 
WHERE activitate_client_ID=$row[ID_activitati_client] AND MONTH(task_ended)=$month AND YEAR(task_ended)=$year
OR activitate_client_ID=$row[ID_activitati_client] AND MONTH(task_last_update)=$month AND YEAR(task_last_update)=$year
OR activitate_client_ID=$row[ID_activitati_client] AND MONTH(task_deadline)=$month AND YEAR(task_deadline)=$year";			
$result1=ezpub_query($conn,$query1);
$numar=ezpub_num_rows($result1,$query1);
$row1=ezpub_fetch_array($result1);
if ($numar==0) 
{
echo "<td><i class=\"large fas fa-sticky-note\" title=\"$strNoUpdates\"></i></td>
<td><a href=\"sitetasks.php?mode=new&cID=$row[ID_activitati_client]&month=$month\" ><i class=\"far fa-edit fa-xl\" title=\"$strOpen\"></i></a></td>
<td><i class=\"fas fa-calendar-minus\" title=\"$strNotStarted\"></i></td>
<td><i class=\"large fa fa-lock\" title=\"$strLock\"></i></td>";
}
Else {
	If ($row1["task_status"]==1)
	{	if ($row1['task_follow_ups']==0)
{echo "<td><i class=\"large fas fa-sticky-note\" title=\"$strNoUpdates\"></i></td>";}	
Else
	{echo "<td><i class=\"large far fa-sticky-note\" title=\"$strUpdates\"></i></td>";}	
		echo "<td><i class=\"large fa fa-lock\" title=\"$strLock\"></i></td>
<td>";
echo date("d.m.Y",strtotime($row1["task_ended"]));
echo "</td>
<td><a href=\"sitetasks.php?mode=edit&cID=$row1[task_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>";
}
Else
{	if ($row1['task_follow_ups']==0)
{echo "<td><i class=\"large fas fa-sticky-note\" title=\"$strNoUpdates\"></i></td>";}	
Else
	{echo "<td><i class=\"large far fa-sticky-note\" title=\"$strUpdates\"></i></td>";}	
	echo "<td><i class=\"large fas fa-hourglass-start\" title=\"$strActivityStarted\"></i></td>
<td>";
If ($row1["task_last_update"]!="") {echo date("d.m.Y",strtotime($row1["task_last_update"]));};
echo "</td>
<td><a href=\"sitetasks.php?mode=edit&cID=$row1[task_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>";
}
	
}			
			echo "</tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"6\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";

}// ends special
?>
</div>
<div class="tabs-panel" id="panel6">
<?php
//Authorizations
echo "<h2>$strAuthorizations</h2>";
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, 
date_autorizatii_clienti.ID_Client, date_autorizatii_clienti.ID_Autorizatie, date_autorizatii_clienti.Autorizatie_Client_Emitere, date_autorizatii_clienti.Autorizatie_Client_Viza, 
date_autorizatii_clienti.Autorizatie_Client_Expirare, date_autorizatii.ID_autorizatii, Autorizatie, date_autorizatii_clienti.Autorizatie_Client_Tip, 
ID_Autorizatie_Client, date_utilizatori.utilizator_ID, date_utilizatori.utilizator_Prenume
FROM date_autorizatii_clienti, clienti_date, date_autorizatii, date_utilizatori
WHERE clienti_date.ID_Client=date_autorizatii_clienti.ID_Client 
AND date_autorizatii_clienti.ID_Autorizatie=date_autorizatii.ID_autorizatii 
AND date_utilizatori.utilizator_ID=date_autorizatii_clienti.ID_User
AND (CASE
    WHEN date_autorizatii_clienti.Autorizatie_Client_Tip='1' THEN (DATEDIFF(Now(), Autorizatie_Client_Emitere))>275
    ELSE Autorizatie_Client_Expirare BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 90 DAY) END)";
$query=$query . "ORDER By Client_Denumire ASC";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<table width="100%">
	      <thead>
    	<tr>
        	<th ><?php echo $strClient?></th>
			<th><?php echo $strAuthorizations?></th>
			<th><?php echo $strIssuedDate?></th>
			<th><?php echo $strExpiryDate?></th>
			<th><?php echo $strVisaDeadline?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[Autorizatie]</td>
			<td>";
If ($row["Autorizatie_Client_Emitere"]!="") {echo date("d.m.Y",strtotime($row["Autorizatie_Client_Emitere"]));};
echo "</td><td>";
If ($row["Autorizatie_Client_Expirare"]!="") {echo date("d.m.Y",strtotime($row["Autorizatie_Client_Expirare"]));};
echo "</td>
<td>";
If ($row["Autorizatie_Client_Viza"]!="") {echo date("d.m",strtotime($row["Autorizatie_Client_Viza"]));};
echo "</td>
	    </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"3\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
//ends authorizations
}
}
?>
</div>
</div>
</div>
</div>
<?php
include '../bottom.php';
?>