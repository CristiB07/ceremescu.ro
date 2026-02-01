<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes")
{
	header("location:$strSiteURL/login/index.php?message=MLF");
	die;
}


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
else {
?>
        <div class="paginate">
            <?php
echo $strTotal . " " .$numar." ".$strInvoices ;
echo " <br /><br />";
    echo $pages->display_pages() . " <a href=\"einvoices.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
    // Bulk download button
    echo " <a href=\"verifyeinvoice.php?mode=bulk\" class=\"button warning\" style=\"margin-left:10px;\">Bulk download</a>";
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
    $factura_ID = htmlspecialchars($row['factura_ID'] ?? '', ENT_QUOTES, 'UTF-8');
    $factura_xml = htmlspecialchars($row['factura_xml'] ?? '', ENT_QUOTES, 'UTF-8');
    $factura_index_incarcare = htmlspecialchars($row['factura_index_incarcare'] ?? '', ENT_QUOTES, 'UTF-8');
    $factura_status = htmlspecialchars($row['factura_status'] ?? '', ENT_QUOTES, 'UTF-8');
    $factura_index_descarcare = htmlspecialchars($row['factura_index_descarcare'] ?? '', ENT_QUOTES, 'UTF-8');
    $factura_descarcata = htmlspecialchars($row['factura_descarcata'] ?? '', ENT_QUOTES, 'UTF-8');
    $efactura_ID = (int)$row['efactura_ID'];
    $data_incarcarii = date("d.m.Y H.i.s", strtotime($row["factura_data_incarcarii"]));
    
    		echo"<tr>
			<td>$factura_ID</td>
			<td>$factura_xml</td>
			<td>$data_incarcarii</td>
			<td>$factura_index_incarcare</td>
			<td>$factura_status</td>
			<td>$factura_index_descarcare</td>	
			<td><a href=\"verifyeinvoice.php?mode=verify&cID=$efactura_ID\" ><i class=\"large fas fa-money-check\" title=\"$strCheck\"></a></td>	
			<td><a href=\"verifyeinvoice.php?mode=download&cID=$efactura_ID\" ><i class=\"large fas fa-file-download\" title=\"$strDownload\"></a></td>";	
			 ?>
                <?php 
          echo"  <td>$factura_descarcata</td>	
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td colspan=\"7\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}?>
    </div>
</div>
<?php
include '../bottom.php';
?>