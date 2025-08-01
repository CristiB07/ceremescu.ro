<!doctype html>
<!-- updated 8.01.2025-->
<html class="no-js" lang="" dir="ltr">
<head>
<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?php echo $strDescription ?>" />
<meta name="keywords" content="<?php echo $strKeywords ?>" />
<meta name="rating" content="General" />
<meta name="author" content="Cristian Banu"/>
<meta name="copyright" content="Cert Plus SRL. Copyright (c) <?php echo date("Y") ?>"/>
<title><?php echo $strSiteName ?>: <?php echo $strPageTitle ?></title>
<meta property="og:image" content="<?php echo $strSiteURL ?>/img/<?php echo $siteOGImage?>" />
<link rel="canonical" href="<?php echo $strSiteURL."/".$url ?>" />
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/all.css"/>
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/foundation.css"/>
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/<?php echo $cssname?>.css"/>
<link rel="shortcut icon" type="image/favicon" href="favicon.ico" />
<script language="javascript" type="text/javascript">
  function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
  }
</script>
<?php
//set the random id length 
if(session_status() !== PHP_SESSION_ACTIVE) session_start();
$rnd_id=generateRandomString(10);
if (!isSet($_SESSION['$buyer'])) {
	$_SESSION['$buyer']=$rnd_id;
}
if (!isSet($_SESSION['$lang'])) {
	$_SESSION['$lang']="RO";
	$lang=$_SESSION['$lang'];
}
Else
{
	$lang=$_SESSION['$lang'];
}
if (isSet($_SESSION['$userlogedin'])){
$userlogedin=$_SESSION['$userlogedin'];
}
if ($lang=="RO") {
include 'lang/language_RO.php';
}
Else
{
	include 'lang/language_EN.php';
}
$producttrail="produse/";
?>
</head>
<body>
<!-- Cookie alert -->
<?php
if(!isset($_COOKIE['user'])) {
echo "<div class=\"callout success\" data-closable>$strCookieAlert<button class=\"close-button\" aria-label=\"$strClose\" type=\"button\" data-close>
    <span aria-hidden=\"true\">&times;</span>
  </button>
</div>";
$cookie_name = "user";
$cookie_value = "John Doe";
setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day; 
}
?>
<!-- Start header-->
<div class="title-bar" data-responsive-toggle="responsive-menu" data-hide-for="medium">
  <button class="menu-icon" type="button" data-toggle="responsive-menu"></button>
  <div class="title-bar-title"><?php echo $strMenu?></div>
</div>
 <div class="top-bar" data-sticky data-options="marginTop:0;" style="width:100%">
	<div class="top-bar-left" id="responsive-menu">
		<ul class="dropdown menu" data-dropdown-menu>
			<li><a href="<?php echo $strSiteURL ?>/index.php"><i class="fas fa-home" title="<?php echo $strHome ?>"></i></a></li>
			 			 <?php  
 if(isSet($_SESSION['userlogedin'])) {?>
			<li><a href="<?php echo $strSiteURL ?>/admin/sitepages.php"><i class="far fa-file-alt"></i><?php echo $strPages?></a></li>
			<li><a href="<?php echo $strSiteURL ?>/admin/siteusers.php"><i class="fas fa-users"></i><?php echo $strUsers?></a></li>
			<li><a href="<?php echo $strSiteURL ?>/admin/siteproducts.php"><i class="fas fa-cart-plus"></i><?php echo $strProducts?></a></li>
			<li><a href="<?php echo $strSiteURL ?>/admin/siteclients.php"><i class="far fa-id-card"></i><?php echo $strClients?></a></li>
			<li><a href="<?php echo $strSiteURL ?>/admin/siteorders.php"><i class="fas fa-shopping-cart"></i><?php echo $strOrders?></a></li>
			<li><a href="<?php echo $strSiteURL ?>/admin/logout.php"><i class="fas fa-sign-out-alt"></i></a></li>
		</ul>
		<?php } Else { 
		
			 if($cartenabled==1) {?>
			 <li><a href="<?php echo $strSiteURL ?>/shop/"><?php echo $strOnlineShop?></a></li>
			 <?php }
			 
		$pagequery="SELECT * FROM cms_pagini WHERE pagina_tip=0 AND pagina_limba='$lang' AND pagina_status=0 ORDER BY pagina_numar ASC";
		$pageresult=ezpub_query($conn, $pagequery);
		While ($pagerow=ezpub_fetch_array($pageresult))
		{
			$subpagequery="SELECT * FROM cms_pagini WHERE pagina_master='$pagerow[pagina_id]' AND pagina_limba='$lang' AND pagina_status=0 ORDER BY pagina_numar ASC";
			$subpageresult=ezpub_query($conn,$subpagequery);
			$numar=ezpub_num_rows($subpageresult,$subpagequery);
		if ($numar==0)
		{ echo "<li><a href=\"$strSiteURL/$pagerow[pagina_url]\">$pagerow[pagina_titlu]</a></li>";}
		else
		{ echo "<li class=\"has-submenu\"><a href=\"$strSiteURL/$pagerow[pagina_url]\">$pagerow[pagina_titlu]</a>";		
			echo "  <ul class=\"submenu menu vertical\" data-submenu>";
		While ($subpagerow=ezpub_fetch_array($subpageresult)) {
		echo "<li><a href=\"$strSiteURL/$subpagerow[pagina_url]\">$subpagerow[pagina_titlu]</a></li>";
		}
		echo "</ul></li>";
		}
		}
			
					 if($cartenabled==1) {?>
			 <li><a href="<?php echo $strSiteURL ?>/shop/"><?php echo $strOnlineShop?></a></li>
			 <?php }
			 if($elearningenabled==1) {?>	
			 <li><a href="<?php echo $strSiteURL ?>/elearning/"><?php echo $strCourses?></a></li>
 <?php }?>
	        <li><a href="<?php echo $strSiteURL ?>/contact.php"><?php echo $strContact?></a></li>
		?>			 
	        <li><a href="<?php echo $strSiteURL ?>/contact.php"><?php echo $strContact?></a></li>
		<?php } ?>

		</ul>
	</div>
	    <div class="top-bar-right text-right">
			<ul class="menu">
				<li><a href="https://wa.me/40722575390"><i class="fab fa-whatsapp"></i></a></li>
				<li><a href="tel: 0722575390"><i class="fas fa-mobile-alt"></i> 0722575390</a></li>
				<li><a href="https://facebook.com/consaltisconsultantasiaudit"><i class="fab fa-facebook-f"></i></a></li>
				<li><a href="https://www.linkedin.com/company/consaltis-consultanta-si-audit/"><i class="fab fa-linkedin-in"></i></a></li>
				<li><a href="mailto:office@consaltis.ro?Subject=Doresc%20o%20ofertă" target="_top"><i class="far fa-envelope"></i> </a></li>
				<?php
			 if($cartenabled==1) {
$buyer=$_SESSION['$buyer'];
$query="SELECT * FROM magazin_comenzi where comanda_utilizator='$buyer' AND comanda_status=0";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result, $query);
If ($numar==0)
{$nume=0;}
	Else {
$orderr=ezpub_fetch_array($result);
$oID=$orderr['comanda_ID'];
$itemq="SELECT * FROM magazin_articole where articol_idcomanda=$oID";
$resulti=ezpub_query($conn,$itemq);
$ordertotal=0;
	$nume=ezpub_num_rows($resulti,$itemq);}
	
				?>
				<li><a href="<?php echo $strSiteURL ?>/shop/<?php If($numar!=0){echo "order.php?oID=$oID";}?>"><i class="fas fa-shopping-cart"></i><span class="badge" id="CartCount"><?php echo $nume?></span></a></li>

				 <?php }
				 if($elearningenabled==1) {
				if (isSet($_SESSION['$userlogedin'])){	?>
				<li><a href="<?php echo $strSiteURL ?>/dashboard/dashboard.php"><i class="fas fa-user-circle"></i></a></li>
				<?php
				}
				else 
				{?>
				<li><a href="<?php echo $strSiteURL ?>/elearning/login.php"><i class="far fa-user-circle"></i></a></li>
			<?php	}}?>
</ul>
	  </div>
	  </div>
	  <div class="grid-container">
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">&nbsp;
	  </div>
	  	  </div>
	    <div class="grid-x grid-margin-x">
			<div class="large-4 medium-4 small-4 cell">
				<h1><a href="<?php echo $strSiteURL ?>/index.php"><img src="<?php echo $strSiteURL ?>/img/logo.png" width="300px"></a></h1>
			</div>
        <div class="large-8 medium-8 small-8 cell">
        </div>
      </div>
	  <!-- Ends header-->