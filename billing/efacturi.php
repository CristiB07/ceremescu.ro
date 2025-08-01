<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';

$strPageTitle="Administrare efacturi";
include '../dashboard/header.php';
?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
$query= "SELECT * FROM efactura ";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY factura_data_incarcarii DESC $pages->limit";
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
echo $strTotal . " " .$numar." ".$strInvoices ;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"efacturi.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
echo " <br /><br />";
?>
</div>

<table width="100%">
	      <thead>
    	<tr>
			<th><?php echo $strID?></th>
			<th><?php echo $strFile?></th>
			<th><?php echo $strDate?></th>
			<th><?php echo $strIndex?></th>
			<th><?php echo $strStatus?></th>
			<th><?php echo $strDownloadIndex?></th>
			<th><?php echo $strVerifyStatus?></th>
			<th><?php echo $strDownload?></th>
			<th><?php echo $strFileDownloaded?></th>
			    </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[factura_ID]</td>
			<td>$row[factura_xml]</td>
			<td>". date("d.m.Y H.i.s",strtotime($row["factura_data_incarcarii"]))."</td>
			<td>$row[factura_index_incarcare]</td>
			<td>$row[factura_status]</td>
			<td>$row[factura_index_descarcare]</td>	
			<td><a href=\"verify_efactura.php?mode=verify&cID=$row[efactura_ID]\" ><i class=\"large fas fa-money-check\" title=\"$strCheck\"></a></td>	
			<td><a href=\"verify_efactura.php?mode=download&cID=$row[efactura_ID]\" ><i class=\"large fas fa-file-download\" title=\"$strDownload\"></a></td>	
			<td>$row[factura_descarcata]</td>	
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"7\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}?>
</div>
</div>
<?php
include '../bottom.php';
?>