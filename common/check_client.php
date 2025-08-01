<?php
//update 8.01.2025
include '../settings.php';
include '../lang/language_RO.php';
include '../classes/common.php';
    $sql = "Select ID_Client, Client_Denumire, Client_CUI FROM clienti_date WHERE Client_CUI LIKE '%$_POST[keyword]%' OR Client_Denumire LIKE '%$_POST[keyword]%' ORDER BY Client_Denumire ASC";
        $result = ezpub_query($conn,$sql);
	?>	
<?php
		$nume=ezpub_num_rows($result,$sql);
		if ($nume==0)
{
echo $strNoRecordsFound;
}
Else {
	$result2=ezpub_query($conn,$sql);
While ($row=ezpub_fetch_array($result)){
?>
<a href="siteclients.php?mode=edit&cID=<?php echo $row["ID_Client"]; ?>"> <?php echo $row["Client_CUI"] ." ". $row["Client_Denumire"]; ?> - <i class="far fa-edit fa-xl" title="<?php echo $strEdit?>"></i></a><br />
<?php }} ?>