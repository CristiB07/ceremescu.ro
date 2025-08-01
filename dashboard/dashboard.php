<?php
//update 8.01.2025
$strPageTitle="Dashboard";
include '../settings.php';
include '../classes/common.php';
    if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 

if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
header("location:$strSiteURL/login/index.php?message=MLF");
die;}

include 'header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$scope=$_SESSION['function'];

$month= date('m');
$year=date('Y');
$day = date('d');
?>
	    <div class="grid-x grid-margin-x">
					  <div class="large-12 medium-12 small-12 cell">
					  <h1><?php echo $strPageTitle?></h1>
					  <div class="callout primary">			<p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam eget augue elit. Pellentesque justo tortor, ultricies vel lobortis at, vehicula gravida enim. Morbi sollicitudin pellentesque sodales. Praesent accumsan molestie quam in porta. Phasellus lobortis purus leo, vitae convallis ipsum luctus in. Nulla viverra imperdiet ante vitae fringilla. Mauris ac turpis orci. Etiam semper, ligula at ornare malesuada, erat turpis commodo risus, eget sagittis quam augue vel nibh. Maecenas volutpat maximus massa sit amet porttitor. Mauris vitae imperdiet diam. Nunc arcu neque, lacinia eu sapien eu, commodo gravida orci. Donec maximus justo neque, ac vestibulum nisi lacinia ac.</p></div>
					  <div class="callout secondary"><p>Etiam venenatis dapibus mauris non faucibus. Ut sit amet augue faucibus, malesuada augue et, mollis metus. Pellentesque egestas et leo eu tempus. Nulla commodo accumsan lorem, ut consectetur eros porta eget. Nam rhoncus efficitur ex, quis cursus mi sollicitudin id. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam laoreet id tortor nec tincidunt. Morbi tincidunt eleifend viverra. Nulla maximus sapien semper est tempor, ac maximus orci posuere. Praesent vestibulum velit et risus gravida laoreet. Quisque viverra viverra magna, non tempor magna efficitur vitae.</p></div>
					  <div class="callout primary"><p>In hac habitasse platea dictumst. Sed bibendum magna vitae ante varius porttitor. Sed at nisl dapibus, vestibulum lacus quis, blandit odio. Maecenas sed tortor mollis, cursus turpis id, mollis orci. Ut facilisis metus dui, quis ultrices nisl venenatis quis. In faucibus volutpat lectus id ultrices. Aliquam at tellus efficitur, congue augue id, maximus est. Nullam bibendum posuere dolor, et consectetur purus suscipit venenatis. Nunc ultrices sodales justo nec pretium.</p></div>
					  <div class="callout secondary"><p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Donec dignissim ipsum justo, at mollis tortor fermentum eu. Etiam ut scelerisque urna, ullamcorper vehicula nulla. Sed non sem at est aliquet eleifend nec at tellus. Aenean a mollis purus. In hac habitasse platea dictumst. Etiam diam sapien, tempus eu magna in, suscipit mollis mauris. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Ut viverra quam in eros porttitor, eget efficitur diam sodales. Aenean pharetra viverra nisl quis dictum. Ut sagittis purus ex, in malesuada metus aliquet in. Mauris ornare sem sit amet luctus sollicitudin.</p></div>
					  <div class="callout primary"><p>Etiam gravida id est vitae porttitor. Praesent facilisis viverra arcu vel convallis. Ut eget mattis odio. Praesent et tristique mi, a commodo mauris. Integer suscipit feugiat neque, et tincidunt nisl malesuada et. Donec sagittis blandit tincidunt. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis porttitor pulvinar magna et pretium. Nam nibh sem, interdum sit amet elit at, dapibus aliquam sapien. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vivamus pharetra nisi in mi bibendum rutrum eleifend ac ipsum. Aenean tempor tellus enim, non dignissim ligula tincidunt vel. Etiam eu eleifend odio.</p></div>
					  </div>
					  </div>
	    <div class="grid-x grid-margin-x">
					  <div class="large-12 medium-12 small-12 cell">
	
  </div>
  </div>
<?php include '../bottom.php'?>