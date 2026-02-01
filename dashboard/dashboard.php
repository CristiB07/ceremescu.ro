<?php // Last Modified Time: Tuesday, September 30, 2025 at 3:41:56 PM Eastern European Summer Time ?>
<?php
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
$scope=$_SESSION['function'];
$role=$_SESSION['clearence'];

$month= date('m');
$year=date('Y');
$day = date('d');

if ($sitefunction=='CRM'){
?>

<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <h1><?php echo $strPageTitle?></h1>
        <?php
        // Check for recent errors (last 24 hours)
        if ($role == 'ADMIN') {
            $error_query = "SELECT COUNT(*) as error_count FROM application_errors WHERE error_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $error_result = ezpub_query($conn, $error_query);
            $error_row = ezpub_fetch_array($error_result);
            $error_count = $error_row['error_count'];
            
            if ($error_count > 0) {
                echo "<div class=\"callout alert\" data-closable>
                        <h5><i class=\"fas fa-exclamation-triangle\"></i> $strErrors</h5>
                        <p>Există <strong>$error_count</strong> eroare/erori înregistrate în ultimele 24 de ore.</p>
                        <p><a href=\"$strSiteURL/admin/siteerrors.php\" class=\"button small\">Vezi erori</a></p>
                        <button class=\"close-button\" aria-label=\"Dismiss alert\" type=\"button\" data-close>
                        <span aria-hidden=\"true\">&times;</span>
                        </button>
                    </div>";
            }
        }
        ?>
        <?php 
        if (isset($_SESSION['team'])&& $_SESSION['team']=='MANAGEMENT')
        {// check token expiration
             $string = $expiration_date;//string variable
            $date = date('Y-m-d',time());//date variable

            $time1 = strtotime($string);
            $time2 = strtotime($date);
            $datediff = $time1 - $time2;
            $scadenta= round($datediff / (60 * 60 * 24));
            if($scadenta < 5 ){
                echo "<div class=\"callout alert\" data-closable>
                        <h5>$strImportant</h5>
                        <p>Tokenul de acces efactura expiră în <strong>$scadenta</strong> zile.</p>
                        <p>Accesați <a href=\"$strSiteURL/admin/managetoken.php\"><i class=\"far fa-file-code\"></i> $strRefreshTheToken</a>.</p>
                        <button class=\"close-button\" aria-label=\"Dismiss alert\" type=\"button\" data-close>
                        <span aria-hidden=\"true\">&times;</span>
                        </button>
                    </div>"; }
            $equery="SELECT * FROM facturare_facturi WHERE factura_client_efactura_generata IS NULL and YEAR(factura_data_emiterii)>'2023' AND factura_tip=0 ORDER BY factura_ID DESC";
            $eresult=ezpub_query($conn,$equery);
            $numar=ezpub_num_rows($eresult);
            if ($numar>0){
                 echo "<div class=\"callout alert\" data-closable>
                        <h5>$strImportant</h5>
                        <p>Există <strong>$numar</strong> factură/facturi neîncărcate în SPV.</p>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Data emiterii</th>
                                    <th>Valoare</th>
                                    <th>Încarcă</th>
                                </tr>
                            </thead>
                            <tbody>";
                                while ($erow=ezpub_fetch_array($eresult)) {
                                    echo "<tr>
                                            <td>$erow[factura_ID]</td>
                                            <td>$erow[factura_client_denumire]</td>
                                            <td>$erow[factura_data_emiterii]</td>
                                            <td>".romanize($erow["factura_client_valoare_totala"])."</td>
                                            <td><a href=\"$strSiteURL/billing/einvoice.php?cID=$erow[factura_ID]\"><i class=\"fas fa-upload\"></i></a></td>
                                        </tr>";
                                } 
                            echo "
                            </tbody>
                        </table>
                        <button class=\"close-button\" aria-label=\"Dismiss alert\" type=\"button\" data-close>
                        <span aria-hidden=\"true\">&times;</span>
                        </button>
                    </div>";}
            include '../billing/financedashboard.php';
           
        }
        else
        {
        ?>
<!-- verificare semnături documente -->
<?php
// Check for unsigned documents
$unsigned_query = "SELECT d.* FROM documente d WHERE NOT EXISTS (SELECT 1 FROM documente_semnaturi ds WHERE ds.document_id = d.document_id AND ds.utilizator_ID = ? AND ds.document_lastupdated = d.document_lastupdated)";
$stmt = $conn->prepare($unsigned_query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$unsigned_result = $stmt->get_result();
$unsigned_count = $unsigned_result->num_rows;

if ($unsigned_count > 0) {
    // Documents to sign
    echo "<div class=\"callout warning\" data-closable=\"false\">
            <h5><i class=\"fas fa-exclamation-triangle\"></i> Documente care necesită semnătură</h5>
            <p>Aveți <strong>$unsigned_count</strong> document(e) care trebuie semnate la versiunea curentă.</p>
            <table>
                <thead>
                    <tr>
                        <th>Titlu</th>
                        <th>Tip</th>
                        <th>Categorie</th>
                        <th>Cod</th>
                        <th>Acțiune</th>
                    </tr>
                </thead>
                <tbody>";
    while ($doc = $unsigned_result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($doc['document_titlu']) . "</td>
                <td>" . htmlspecialchars($doc['document_tip']) . "</td>
                <td>" . htmlspecialchars($doc['document_categorie']) . "</td>
                <td>" . htmlspecialchars($doc['document_cod']) . "</td>
                <td><a href=\"$strSiteURL/documents/viewdocument.php?cID=" . $doc['document_id'] . "\" class=\"button small\">Vizualizare și semnare</a></td>
            </tr>";
    }
    echo "</tbody>
            </table>
        </div>";
} else {
    // No documents to sign
    echo "<div class=\"callout success\" data-closable>
            <h5><i class=\"fas fa-check-circle\"></i> Toate documentele sunt semnate</h5>
            <p>Nu aveți documente noi de semnat. Toate documentele sunt la zi cu semnăturile.</p>
            <button class=\"close-button\" aria-label=\"Dismiss alert\" type=\"button\" data-close>
                <span aria-hidden=\"true\">&times;</span>
            </button>
        </div>";
}
$stmt->close();
?>
        <?php }
}
        else
            //site is a CMS
{?>
<?php
// Check for recent errors (last 24 hours)
if ($role == 'ADMIN') {
    $error_query = "SELECT COUNT(*) as error_count FROM application_errors WHERE error_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $error_result = ezpub_query($conn, $error_query);
    $error_row = ezpub_fetch_array($error_result);
    $error_count = $error_row['error_count'];
    
    if ($error_count > 0) {
        echo "<div class=\"callout alert\" data-closable>
                <h5><i class=\"fas fa-exclamation-triangle\"></i> $strErrors</h5>
                <p>Există <strong>$error_count</strong> eroare/erori înregistrate în ultimele 24 de ore.</p>
                <p><a href=\"$strSiteURL/admin/siteerrors.php\" class=\"button small\">Vezi erori</a></p>
                <button class=\"close-button\" aria-label=\"Dismiss alert\" type=\"button\" data-close>
                <span aria-hidden=\"true\">&times;</span>
                </button>
            </div>";
    }
}
?>
<div class="callout primary">
            <p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam eget augue elit. Pellentesque justo
                tortor, ultricies vel lobortis at, vehicula gravida enim. Morbi sollicitudin pellentesque sodales.
                Praesent accumsan molestie quam in porta. Phasellus lobortis purus leo, vitae convallis ipsum luctus in.
                Nulla viverra imperdiet ante vitae fringilla. Mauris ac turpis orci. Etiam semper, ligula at ornare
                malesuada, erat turpis commodo risus, eget sagittis quam augue vel nibh. Maecenas volutpat maximus massa
                sit amet porttitor. Mauris vitae imperdiet diam. Nunc arcu neque, lacinia eu sapien eu, commodo gravida
                orci. Donec maximus justo neque, ac vestibulum nisi lacinia ac.</p>
        </div>

<?php } ?>
    </div>
</div>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">

    </div>
</div>
<?php include '../bottom.php'?>