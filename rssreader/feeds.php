<?php
// update 26.12.2024
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare feeduri";
$url='feeds.php';
include '../dashboard/header.php';

?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM readerrss_feeds WHERE feed_ID=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"feeds.php\"
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

	$mSQL = "INSERT INTO readerrss_feeds(";
	$mSQL = $mSQL . "feed_url,";
	$mSQL = $mSQL . "feed_titlu,";
	$mSQL = $mSQL . "feed_image_url,";
	$mSQL = $mSQL . "feed_site_url,";
	$mSQL = $mSQL . "feed_image_w,";
	$mSQL = $mSQL . "feed_image_h,";
	$mSQL = $mSQL . "feed_descriere)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["feed_url"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["feed_titlu"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["feed_site_url"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["feed_image_url"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["feed_image_w"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["feed_image_h"]) . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["feed_descriere"]) . "') ";
				
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
    window.location = \"feeds.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{// edit
$strWhereClause = " WHERE readerrss_feeds.feed_ID=" . $_GET["cID"] . ";";
$query= "UPDATE readerrss_feeds SET readerrss_feeds.feed_url='" .str_replace("'","&#39;",$_POST["feed_url"]) . "' ," ;
$query=$query . "  readerrss_feeds.feed_titlu='" .str_replace("'","&#39;",$_POST["feed_titlu"]) . "' ," ;
$query=$query . "  readerrss_feeds.feed_site_url='" .$_POST["feed_site_url"] . "' ," ;
$query=$query . "  readerrss_feeds.feed_image_url='" .$_POST["feed_image_url"] . "' ," ;
$query= $query . " readerrss_feeds.feed_image_w='" .$_POST["feed_image_w"] . "' ," ;
$query= $query . " readerrss_feeds.feed_image_h='" .str_replace("'","&#39;",$_POST["feed_image_h"]) . "' ," ;
$query= $query . " readerrss_feeds.feed_descriere='" .str_replace("'","&#39;",$_POST["feed_descriere"]) . "' "; 
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
    window.location = \"feeds.php\"
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
<script src="<?php echo $strSiteURL ?>js/foundation/jquery.js"></script>
<script language="JavaScript" type="text/JavaScript">
$(document).ready(function() {
    $("#btn1").click(function() {  
	$("#loaderIcon").show();    
	jQuery.ajax({
	url: "getfeeddata.php",
	dataType: "json",
	data:'feed='+$("#feed").val(),
	type: "POST",
	  success: function(data) {
		  try {
           $('#feed_url').val(data["feed_url"]);
           $("#feed_titlu").val(data["feed_title"]);
           $("#feed_descriere").val(data["feed_description"]);
           $("#feed_site_url").val(data["feed_site_url"]);
           $("#feed_image_url").val(data["feed_image"]);
           $("#feed_image_w").val(data["feed_image_w"]);
           $("#feed_image_h").val(data["feed_image_h"]);
           $("#loaderIcon").hide();   
		   }
catch(err) {
  document.getElementById("response").innerHTML = err.message;
}
        },
        error : function(){
           alert('Nu se poate încărca feed-ul!');
        }
    });
});
});
document.getElementById("feed").innerHTML = data;
</script>

			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="feeds.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>

		<div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			 <div id="response"></div>
<div class="input-group">
  <span class="input-group-label"><?php echo $strURL?></span>
  <input class="input-group-field" type="text" name="feed" id="feed" placeholder="<?php echo $strFeedURL?>">
  <div class="input-group-button">
    <button id="btn1" class="button" ><i class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
  </div>
</div>
  <div id="suggesstion-box"></div>
  	</div>
	</div>
	<form Method="post" id="users" Action="feeds.php?mode=new" >
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <label><?php echo $strURL?></label>
	  <input name="feed_url" id="feed_url" Type="text" size="50" class="required" />
	  </div>
	  </div> 	
	  <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <label><?php echo $strFeedSiteURL?></label>
	  <input name="feed_site_url" id="feed_site_url" Type="text" size="50" class="required" />
	  </div>
	  </div> 
	  <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <label><?php echo $strTitle?></label>
	  <input name="feed_titlu" id="feed_titlu" Type="text" size="50" class="required" />
	  </div>
	  </div>
			    <div class="grid-x grid-margin-x">
			  <div class="large-8 medium-8 small-8 cell">
			  <label><?php echo $strImage?></label>
	  <input name="feed_image_url" id="feed_image_url" Type="text" size="50" class="required" />
	  </div>	
	  <div class="large-2 medium-2 small-2 cell">
			  <label><?php echo $strWidth?></label>
	  <input name="feed_image_w" id="feed_image_w" Type="text" size="50" class="required" />
	  </div>  
	  <div class="large-2 medium-2 small-2 cell">
			  <label><?php echo $strHeight?></label>
	  <input name="feed_image_h" id="feed_image_h" Type="text" size="50" class="required" />
	  </div>
	  </div>	  
	     <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strDetails?></label>
	  <textarea name="feed_descriere" id="feed_image_descriere" rows="5"></textarea>
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
$query="SELECT * FROM readerrss_feeds WHERE feed_ID=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
<script src="<?php echo $strSiteURL ?>js/foundation/jquery.js"></script>
<script language="JavaScript" type="text/JavaScript">
$(document).ready(function() {
    $("#btn1").click(function() {  
	$("#loaderIcon").show();    
	jQuery.ajax({
	url: "getfeeddata.php",
	dataType: "json",
	data:'feed='+$("#feed").val(),
	type: "POST",
	  success: function(data) {
		  try {
           $('#feed_url').val(data["feed_url"]);
           $("#feed_titlu").val(data["feed_title"]);
           $("#feed_descriere").val(data["feed_description"]);
           $("#feed_site_url").val(data["feed_site_url"]);
           $("#feed_image_url").val(data["feed_image"]);
           $("#feed_image_w").val(data["feed_image_w"]);
           $("#feed_image_h").val(data["feed_image_h"]);
           $("#loaderIcon").hide();   
		   }
catch(err) {
  document.getElementById("response").innerHTML = err.message;
}
        },
        error : function(){
           alert('Nu se poate încărca feed-ul!');
        }
    });
});
});
</script>

			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="feeds.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>

	<div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			 <div id="response"></div>
<div class="input-group">
  <span class="input-group-label"><?php echo $strURL?></span>
  <input class="input-group-field" type="text" name="feed_url" id="feed" value="<?php echo $row["feed_url"]?>">
  <div class="input-group-button">
    <button id="btn1" class="button" ><i class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
  </div>
</div>
  <div id="suggesstion-box"></div>
  	</div>
	</div>
	<form Method="post" id="users" Action="feeds.php?mode=edit&cID=<?php echo $row['feed_ID']?>" >
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <label><?php echo $strURL?></label>
	  <input name="feed_url" Type="text" id="feed_url" value="<?php echo $row['feed_url'] ?>" class="required" />
	</div>
	</div>	 
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <label><?php echo $strFeedSiteURL?></label>
	  <input name="feed_site_url" Type="text" id="feed_site_url" value="<?php echo $row['feed_site_url'] ?>" class="required" />
	</div>
	</div>	 
	<div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <label><?php echo $strTitle?></label>
	  <input name="feed_titlu" Type="text" id="feed_titlu" value="<?php echo $row['feed_titlu'] ?>" class="required" />
	</div>
	</div>		
			    <div class="grid-x grid-margin-x">
			  <div class="large-8 medium-8 small-8 cell">
			  <label><?php echo $strImage?></label>
	  <input name="feed_image_url" Type="text" id="feed_image_url" size="50" value="<?php echo $row['feed_image_url'] ?>" class="required" />
	  </div>	
	  <div class="large-2 medium-2 small-2 cell">
			  <label><?php echo $strWidth?></label>
	  <input name="feed_image_w" id="feed_image_w" Type="text" size="50" value="<?php echo $row['feed_image_w'] ?>" class="required" />
	  </div>  
	  <div class="large-2 medium-2 small-2 cell">
			  <label><?php echo $strHeight?></label>
	  <input name="feed_image_h" id="feed_image_h" Type="text" size="50" value="<?php echo $row['feed_image_h']?>" class="required" />
	  </div>
	  </div>	  
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strDetails?></label>
	  <textarea name="feed_descriere" id="feed_descriere"  rows="5"><?php echo $row['feed_descriere'] ?></textarea>
</div>
</div>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
<?php
}
Else
{
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"feeds.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";
$query="SELECT * FROM readerrss_feeds";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> 
<table width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strID?></th>
			<th><?php echo $strTitle?></th>
			<th><?php echo $strURL?></th>
			<th><?php echo $strMessages?></th>
			<th><?php echo $strUpdate?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
	
		$nquery="Select articol_ID from readerrss_articole where articol_feed_ID='$row[feed_ID]'";
	$nresult=ezpub_query($conn,$nquery);
	
	$numararticole=ezpub_num_rows($nresult,$nquery);
    		echo"<tr>
			<td>$row[feed_ID]
			<td>$row[feed_titlu]
			<td>$row[feed_url]
			<td align=\"right\">$numararticole
			<td><a href=\"updatefeed.php?mode=single&cID=$row[feed_ID]\">
			<i class=\"large fas fa-sync\" title=\"$strUpdate\"></i>
			</a>
			  <td><a href=\"feeds.php?mode=edit&cID=$row[feed_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a>
			<td><a href=\"feeds.php?mode=delete&cID=$row[feed_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a>
        </tr>";
}
echo "</tbody><tfoot><tr><td><td  colspan=\"5\"><em></em><td>&nbsp;</tr></tfoot></table></div></div>";
}
}
}
?>
</div>
</div>
<?php
include '../bottom.php';
?>