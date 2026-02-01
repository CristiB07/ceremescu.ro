<?php
//update 9.01.2026 - Security: prepared statements, simple-editor

include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strKeywords=" ";
$strDescription="Administrează produsele.";
$strPageTitle="Administrează produsele";
$url="siteproducts.php";
include '../dashboard/header.php';
?>
<link rel="stylesheet" href="../js/simple-editor/simple-editor.css">
<script src="../js/simple-editor/simple-editor.js"></script>

<?php
echo "      <div class=\"grid-x grid-padding-x\">
        <div class=\"large-12 cell\">
<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

if (!isset($_GET['pID']) || !is_numeric($_GET['pID'])) {
    echo "<div class=\"callout alert\">" . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . "</div>";
    include '../bottom.php';
    die;
}

$pID = (int)$_GET['pID'];
$stmt = mysqli_prepare($conn, "DELETE FROM magazin_produse WHERE produs_id=?");
mysqli_stmt_bind_param($stmt, 'i', $pID);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo "<div class=\"callout success\">" . htmlspecialchars($strRecordDeleted, ENT_QUOTES, 'UTF-8') . "</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteproducts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();

// Validare input
$required_fields = ['produs_nume', 'produs_pret', 'produs_imagine', 'produs_categorie', 
                    'produs_fcategorie', 'produs_url', 'produs_limba', 'produs_tva'];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        echo "<div class=\"callout alert\">" . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . "</div></div></div>";
        include '../bottom.php';
        die;
    }
}

// Validare preț
if (!is_numeric($_POST['produs_pret']) || $_POST['produs_pret'] < 0) {
    echo "<div class=\"callout alert\">Preț invalid!</div></div></div>";
    include '../bottom.php';
    die;
}

// Validare imagine (basename pentru path traversal)
$produs_imagine = basename($_POST['produs_imagine']);
$produs_thumb = isset($_POST['produs_thumb']) ? basename($_POST['produs_thumb']) : '';

// Validare extensii imagine
$allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$ext = strtolower(pathinfo($produs_imagine, PATHINFO_EXTENSION));
if (!in_array($ext, $allowed_ext, true)) {
    echo "<div class=\"callout alert\">Extensie imagine invalidă!</div></div></div>";
    include '../bottom.php';
    die;
}

If ($_GET['mode']=="new"){
//insert new product
	$stmt = mysqli_prepare($conn, 
        "INSERT INTO magazin_produse (produs_nume, produs_pret, produs_imagine, produs_categorie, 
         produs_fcategorie, produs_descriere, produs_url, produs_keywords, produs_meta, 
         produs_thumb, produs_limba, produs_tva, produs_dpret) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $produs_dpret = isset($_POST['produs_dpret']) && is_numeric($_POST['produs_dpret']) ? 
                    $_POST['produs_dpret'] : '0.0000';
    
    mysqli_stmt_bind_param($stmt, 'sssssssssssss',
        $_POST['produs_nume'],
        $_POST['produs_pret'],
        $produs_imagine,
        $_POST['produs_categorie'],
        $_POST['produs_fcategorie'],
        $_POST['produs_descriere'],
        $_POST['produs_url'],
        $_POST['produs_keywords'],
        $_POST['produs_meta'],
        $produs_thumb,
        $_POST['produs_limba'],
        $_POST['produs_tva'],
        $produs_dpret
    );
				
//It executes the SQL
if (!mysqli_stmt_execute($stmt))
  {
	  mysqli_stmt_close($stmt);
	  echo "<div class=\"callout alert\">" . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . "</div></div></div><hr/>";
 include '../bottom.php';
die;
  }
else{
mysqli_stmt_close($stmt);
echo "<div class=\"callout success\">" . htmlspecialchars($strRecordAdded, ENT_QUOTES, 'UTF-8') . "</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteproducts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\"></div></div>";
include '../bottom.php';
die;
}}
else
{// edit
if (!isset($_GET['pID']) || !is_numeric($_GET['pID'])) {
    echo "<div class=\"callout alert\">" . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . "</div></div></div>";
    include '../bottom.php';
    die;
}

$pID = (int)$_GET['pID'];
$produs_dpret = isset($_POST['produs_dpret']) && is_numeric($_POST['produs_dpret']) ? 
                $_POST['produs_dpret'] : '0.0000';

$stmt = mysqli_prepare($conn,
    "UPDATE magazin_produse SET 
     produs_nume=?, produs_pret=?, produs_imagine=?, produs_categorie=?, 
     produs_fcategorie=?, produs_descriere=?, produs_keywords=?, produs_meta=?, 
     produs_thumb=?, produs_limba=?, produs_tva=?, produs_url=?, produs_dpret=? 
     WHERE produs_id=?");

mysqli_stmt_bind_param($stmt, 'sssssssssssssi',
    $_POST['produs_nume'],
    $_POST['produs_pret'],
    $produs_imagine,
    $_POST['produs_categorie'],
    $_POST['produs_fcategorie'],
    $_POST['produs_descriere'],
    $_POST['produs_keywords'],
    $_POST['produs_meta'],
    $produs_thumb,
    $_POST['produs_limba'],
    $_POST['produs_tva'],
    $_POST['produs_url'],
    $produs_dpret,
    $pID
);

if (!mysqli_stmt_execute($stmt))
  {
	  mysqli_stmt_close($stmt);
	  echo "<div class=\"callout alert\">" . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . "</div></div></div><hr/>";
 include '../bottom.php';
die;
  }
else{
mysqli_stmt_close($stmt);
echo "<div class=\"callout success\">" . htmlspecialchars($strRecordModified, ENT_QUOTES, 'UTF-8') . "</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteproducts.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\"></div></div><hr />";
include '../bottom.php';
die;
}
}
}
else {
?>
<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
	echo "<a href=\"siteproducts.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>";
?>
<form method="post" action="siteproducts.php?mode=new">
    <div class="grid-x grid-padding-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strTitle?></label></label>
            <input name="produs_nume" type="text" size="30" placeholder="<?php echo $strTitle?>" required />
        </div>
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strMainPicture?></label>
            <input name="produs_imagine" id="image" type="text" required readonly="readonly" />
            <!-- Trigger/Open The Modal -->
            <a data-open="myModal-1" class="button"><?php echo $strImage?></a>
            <div class="large reveal" id="myModal-1" data-reveal>
                <!-- Modal content -->
                <button class="close-button" data-close aria-label="Close reveal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
                <iframe src="<?php echo $strSiteURL?>/common/image.php?directory=products&field=image" frameborder="0"
                    style="border:0" Width="100%" height="750"></iframe>
            </div>
        </div>
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strProductThumb?></label>
            <input name="produs_thumb" id="thumb" type="text" required readonly="readonly" />
            <!-- Trigger/Open The Modal -->
            <a data-open="myModal-2" class="button"><?php echo $strImage?></a>
            <div class="large reveal" id="myModal-2" data-reveal>
                <!-- Modal content -->
                <button class="close-button" data-close aria-label="Close reveal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
                <iframe src="<?php echo $strSiteURL?>/common/image.php?directory=products&field=thumb" frameborder="0"
                    style="border:0" Width="100%" height="750"></iframe>
            </div>
        </div>
    </div>
    <div class="grid-x grid-padding-x">
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strCategory?></label>
            <input name="produs_categorie" type="text" placeholder="<?php echo $strCategory?>" required />
        </div>
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strFriendlyCategory?></label>
            <input name="produs_fcategorie" type="text" placeholder="<?php echo $strCategory?>" required />
        </div>
        <div class="large-2 medium-2 small-2 cell">
            <label><?php echo $strLanguage?></label>
            <input name="produs_limba" type="text" placeholder="<?php echo $strLanguage?>" required />
        </div>
        <div class="large-2 medium-2 small-2 cell">
            <label><?php echo $strPrice?></label>
            <input name="produs_pret" type="text" placeholder="<?php echo $strPrice?>" number required />
        </div>
        <div class="large-2 medium-2 small-2 cell">
            <label><?php echo $strDiscountedPrice?></label>
            <input name="produs_dpret" type="text" placeholder="<?php echo $strDiscountedPrice?>" number required
                value="0.0000" />
        </div>
    </div>
    <div class="grid-x grid-padding-x">
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strURL?></label>
            <input name="produs_url" type="text" placeholder="<?php echo $strURL?>" required />
        </div>
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strKeyWords?></label>
            <input name="produs_keywords" type="text" placeholder="<?php echo $strKeyWords?>" required />
        </div>
        <div class="large-2 medium-2 small-2 cell">
            <label><?php echo $strVAT?></label>
            <input name="produs_tva" type="text" placeholder="<?php echo $strVAT?>" required />
        </div>

        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strMetaDescription?></label>
            <input name="produs_meta" type="text" required />
        </div>
    </div>
    <div class="grid-x grid-padding-x">
        <div class="large-12 medium-12 small-12 cell">
            <label><?php echo $strProductShort?></label>
            <textarea name="produs_descriere" rows="5" class="simple-html-editor" data-upload-dir="shop"></textarea>
        </div>
    </div>
    <div class="grid-x grid-padding-x">
        <div class="large-12 medium-12 small-12 cell text-center">
            <input type="submit" value="<?php echo $strAdd?>" name="Submit" class="submit button">
        </div>
    </div>
</form>
<?php
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
if (!isset($_GET['pID']) || !is_numeric($_GET['pID'])) {
    echo "<div class=\"callout alert\">" . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . "</div>";
    include '../bottom.php';
    die;
}

$pID = (int)$_GET['pID'];
$stmt = mysqli_prepare($conn, "SELECT * FROM magazin_produse WHERE produs_id=?");
mysqli_stmt_bind_param($stmt, 'i', $pID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    echo "<div class=\"callout alert\">" . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . "</div>";
    include '../bottom.php';
    die;
}

echo "<a href=\"siteproducts.php\" class=\"button\">" . htmlspecialchars($strBack, ENT_QUOTES, 'UTF-8') . " &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>";
?>
<form method="post" action="siteproducts.php?mode=edit&pID=<?php echo (int)$row['produs_id']?>">
    <div class="grid-x grid-padding-x">
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strTitle?></label>
            <input name="produs_nume" type="text" size="30" required value="<?php echo $row['produs_nume']?>" />
        </div>
        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strMainPicture?></label>
            <input name="produs_imagine" id="image" type="text" class="required"
                value="<?php echo $row['produs_imagine'] ?>" readonly="readonly" />
            <!-- Trigger/Open The Modal -->
            <a data-open="myModal-1" class="button"><?php echo $strImage?></a>
            <div class="large reveal" id="myModal-1" data-reveal>
                <!-- Modal content -->
                <button class="close-button" data-close aria-label="Close reveal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
                <iframe src="<?php echo $strSiteURL?>/common/image.php?directory=products&field=image" frameborder="0"
                    style="border:0" Width="100%" height="750"></iframe>
            </div>
        </div>

        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo $strProductThumb?></label>
            <input name="produs_thumb" id="thumb" type="text" class="required"
                value="<?php echo $row['produs_thumb'] ?>" readonly="readonly" /> <!-- Trigger/Open The Modal -->
            <a data-open="myModal-2" class="button"><?php echo $strImage?></a>
            <div class="large reveal" id="myModal-2" data-reveal>
                <!-- Modal content -->
                <button class="close-button" data-close aria-label="Close reveal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
                <iframe src="<?php echo $strSiteURL?>/common/image.php?directory=products&field=thumb" frameborder="0"
                    style="border:0" Width="100%" height="750"></iframe>
            </div>
        </div>
    </div>
    <div class="grid-x grid-padding-x">
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strCategory?></label>
            <input name="produs_categorie" type="text" required value="<?php echo $row['produs_categorie']?>" />
        </div>
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strFriendlyCategory?></label>
            <input name="produs_fcategorie" type="text" required value="<?php echo $row['produs_fcategorie']?>" />
        </div>
        <div class="large-2 medium-2 small-2 cell">
            <label><?php echo $strLanguage?></label>
            <input name="produs_limba" type="text" required value="<?php echo $row['produs_limba']?>" />
        </div>
        <div class="large-2 medium-2 small-2 cell">
            <label><?php echo $strPrice?></label>
            <input name="produs_pret" type="text" required value="<?php echo $row['produs_pret']?>" />
        </div>
        <div class="large-2 medium-2 small-2 cell">
            <label><?php echo $strDiscountedPrice?></label>
            <input name="produs_dpret" type="text" required value="<?php echo $row['produs_dpret']?>" />
        </div>
    </div>
    <div class="grid-x grid-padding-x">
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strURL?></label>
            <input name="produs_url" type="text" required value="<?php echo $row['produs_url']?>" />
        </div>
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strKeyWords?></label>
            <input name="produs_keywords" type="text" required value="<?php echo $row['produs_keywords']?>" />
        </div>
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strVAT?></label>
            <input name="produs_tva" type="text" required value="<?php echo $row['produs_tva']?>" />
        </div>
        <div class="large-3 medium-3 small-3 cell">
            <label><?php echo $strMetaDescription?></label>
            <input name="produs_meta" type="text" required value="<?php echo $row['produs_meta']?>" />
        </div>
    </div>
    <div class="grid-x grid-padding-x">
        <div class="large-12 medium-12 small-12 cell">
            <label><?php echo $strProductShort?></label>
            <textarea name="produs_descriere" class="simple-html-editor"
                data-upload-dir="shop" rows="5"><?php echo $row['produs_descriere']?></textarea>
        </div>
    </div>
    <div class="grid-x grid-padding-x">
        <div class="large-12 medium-12 small-12 cell text-center">
            <input type="submit" value="<?php echo $strModify?>" name="Submit" class="submit button">
        </div>
    </div>
</form>
<?php
}
else
{
echo "<a href=\"siteproducts.php?mode=new\" class=\"button\">" . htmlspecialchars($strAddNew, ENT_QUOTES, 'UTF-8') . " &nbsp;<i class=\"fas fa-plus\"></i></a><br />";

// Category filter
$filter_category = '';
if (isSet($_GET['cat']) AND $_GET['cat']!="") {
    $filter_category = $_GET['cat'];
}

// Count total products
if ($filter_category) {
    $stmt_count = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM magazin_produse WHERE produs_categorie=?");
    mysqli_stmt_bind_param($stmt_count, 's', $filter_category);
} else {
    $stmt_count = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM magazin_produse");
}
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$row_count = mysqli_fetch_assoc($result_count);
$numar = $row_count['total'];
mysqli_stmt_close($stmt_count);

$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 

// Get products
if ($filter_category) {
    $query = "SELECT * FROM magazin_produse WHERE produs_categorie=? ORDER BY produs_categorie ASC, produs_nume ASC " . $pages->limit;
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $filter_category);
} else {
    $query = "SELECT * FROM magazin_produse ORDER BY produs_categorie ASC, produs_nume ASC " . $pages->limit;
    $stmt = mysqli_prepare($conn, $query);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($numar==0)
{
echo "<div class=\"callout alert\">\" . htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8') . \"</div>";
}
else {
	
?>
<div class="paginate">
    <?php
echo htmlspecialchars($strTotal, ENT_QUOTES, 'UTF-8') . " " .$numar." ". htmlspecialchars($strProducts, ENT_QUOTES, 'UTF-8') ;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"siteproducts.php\" title=\"strClearAllFilters\">" . htmlspecialchars($strShowAll, ENT_QUOTES, 'UTF-8') . "</a>&nbsp;";
echo " <br /><br />";

$stmt_cat = mysqli_prepare($conn, "SELECT DISTINCT produs_categorie, produs_fcategorie FROM magazin_produse ORDER BY produs_categorie ASC");
mysqli_stmt_execute($stmt_cat);
$result_cat = mysqli_stmt_get_result($stmt_cat);

while ($row1 = mysqli_fetch_assoc($result_cat)){
	$categ = htmlspecialchars($row1["produs_categorie"], ENT_QUOTES, 'UTF-8');
	$catn = htmlspecialchars($row1["produs_fcategorie"], ENT_QUOTES, 'UTF-8');
    echo "<a href=\"siteproducts.php?cat=" . urlencode($row1["produs_categorie"]) . "\">$catn</a>&nbsp;";
}
mysqli_stmt_close($stmt_cat);
?>
</div>
<table>
    <thead>
        <tr>
            <th><?php echo $strID?></th>
            <th><?php echo $strTitle?></th>
            <th><?php echo $strCategory?></th>
            <th><?php echo $strMetaDescription?></th>
            <th><?php echo $strEdit?></th>
            <th><?php echo $strDelete?></th>
        </tr>
    </thead>
    <tbody>
        <?php 
while ($row = mysqli_fetch_assoc($result)){
    $produs_id = htmlspecialchars($row['produs_id'] ?? '', ENT_QUOTES, 'UTF-8');
    $produs_nume = htmlspecialchars($row['produs_nume'] ?? '', ENT_QUOTES, 'UTF-8');
    $produs_fcategorie = htmlspecialchars($row['produs_fcategorie'] ?? '', ENT_QUOTES, 'UTF-8');
    $produs_meta = htmlspecialchars($row['produs_meta'] ?? '', ENT_QUOTES, 'UTF-8');
    $confirm_msg = htmlspecialchars($strConfirmDelete, ENT_QUOTES, 'UTF-8');
    $delete_title = htmlspecialchars($strDelete, ENT_QUOTES, 'UTF-8');
    
    echo "<tr> 
        <td>$produs_id</td>
        <td>$produs_nume</td>
        <td>$produs_fcategorie</td>
        <td>$produs_meta</td>
        <td><a href=\"siteproducts.php?mode=edit&pID=$produs_id\"><i class=\"far fa-edit fa-xl\"></i></a></td>
        <td><a href=\"siteproducts.php?mode=delete&pID=$produs_id\" OnClick=\"return confirm('$confirm_msg');\"><i class=\"fa fa-eraser fa-xl\" title=\"$delete_title\"></i></a></td>
    </tr>";
}
mysqli_stmt_close($stmt);
echo "</tbody></table>";
}
}
}
?>
        </div>
        </div>
        <hr />
        <?php
include '../bottom.php';
?>