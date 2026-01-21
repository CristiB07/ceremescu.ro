<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/index.php?message=MLF");
}


$strPageTitle="Administrare efacturi";
include '../dashboard/header.php';
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";
$query= "SELECT * FROM efactura_primite";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY efactura_primita_datap DESC $pages->limit";
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
echo " <br /><br />";
?>
        </div>

        <table width="100%">
            <thead>
                <tr>
                    <th><?php echo $strID?></th>
                    <th><?php echo $strClient?></th>
                    <th><?php echo $strDate?></th>
                    <th><?php echo $strIndex?></th>
                    <th><?php echo $strStatus?></th>
                    <th><?php echo $strRegister?></th>
                    <th><?php echo $strView?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=ezpub_fetch_array($result)){
            // Escape output pentru XSS protection
            $efactura_ID = htmlspecialchars($row['efactura_ID'], ENT_QUOTES, 'UTF-8');
            $efactura_CUI = htmlspecialchars($row['efactura_primita_CUI'], ENT_QUOTES, 'UTF-8');
            $efactura_index = htmlspecialchars($row['efactura_primita_index'], ENT_QUOTES, 'UTF-8');
            $efactura_download = htmlspecialchars($row['efactura_primita_download'], ENT_QUOTES, 'UTF-8');
            $efactura_datap = date("d.m.Y H.i.s", strtotime($row["efactura_primita_datap"]));
            
    		echo"<tr>
			<td>$efactura_ID</td>
			<td>$efactura_CUI</td>
			<td>$efactura_datap</td>
			<td>$efactura_index</td>
			<td>$efactura_download</td>
            ";
            
            // SELECT cu prepared statement pentru SQL injection protection
            $stmt = mysqli_prepare($conn, "SELECT * FROM facturare_facturi_primite WHERE fp_index_download=?");
            mysqli_stmt_bind_param($stmt, "s", $row['efactura_primita_index']);
            mysqli_stmt_execute($stmt);
            $result2 = mysqli_stmt_get_result($stmt);
			$row2=ezpub_fetch_array($result2);
            $numar2=mysqli_stmt_num_rows($stmt);
            mysqli_stmt_close($stmt);
            if ($numar2>0)
            {
                echo "<td><i class=\"fa-xl fas fa-check\" title=\"$strRegistered\"></i></td>";
            }
            else
            {
                // URL encode pentru parametrul cid
                $cid_encoded = urlencode($row['efactura_primita_index']);
                echo "<td><a href=\"einvoicereader.php?cid=$cid_encoded\" ><i class=\"fa-xl fas fa-file-import\" title=\"$strRegister\"></i></a></td>";
            }
            ?>
        <div class="full reveal" id="exampleModal1_<?php echo $efactura_ID?>" data-reveal>
            <iframe src="viewinvoice.php?type=0&option=show&cID=<?php echo urlencode($row['efactura_primita_index'])?>" frameborder="0"
                style="border:0" Width="100%" height="1000"></iframe>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <td><i class="fa-xl fas fa-search" title="<?php echo htmlspecialchars($strView, ENT_QUOTES, 'UTF-8')?>"
                data-open="exampleModal1_<?php echo $efactura_ID?>"></i></td>
                <?php 
          echo "</tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"5\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}?>
    </div>
</div>
<?php
include '../bottom.php';
?>