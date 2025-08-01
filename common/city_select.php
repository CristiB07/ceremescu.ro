<?php
//update 8.01.2025
include '../settings.php';
include '../lang/language_RO.php';
include '../classes/common.php';
    $sql = "Select id, name, county_id, county FROM generale_localitati WHERE name LIKE '%$_POST[keyword]%' Group by county ORDER BY name ASC";
        $result = ezpub_query($conn,$sql);
	?>	
	<div class="parent">
<ul id="country-list">
<?php
		$nume=ezpub_num_rows($result,$sql);
		if ($nume==0)
{
echo "<li>".$strNoRecordsFound."</li>";
}
Else {
	$result2=ezpub_query($conn,$sql);
While ($city=ezpub_fetch_array($result)){
?>
<li onClick="selectCity('<?php echo $city["name"]; ?> - <?php echo $city["county"]; ?>');"><?php echo $city["name"]; ?>, <?php echo $strCounty; ?> <?php echo $city["county"]; ?> </li>
<?php }} ?>
</ul>
</div>