<?php
// admin page for managing saved legislation
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle = "Administrare legislație salvată";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/index.php?message=MLF");
}

$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$uid_to_use = ($userlegal == 1) ? $uid : 0;
// CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo "<h1>$strPageTitle</h1>";

// handle delete
if (isset($_GET['mode']) && $_GET['mode'] == 'delete') {
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        die('<div class="callout alert">Invalid CSRF token</div>');
    }
    if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
        die('Invalid ID');
    }
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "DELETE FROM legislatie_salvata WHERE id = ? AND uid = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $uid_to_use);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo '<div class="callout success">Înregistrare ștearsă.</div>';
    echo "<script>setTimeout(function(){ window.location='sitelegislation.php'; },1200);</script>";
    include '../bottom.php';
    exit();
}

// insert / update handling
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    check_inject();
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('<div class="callout alert">Invalid CSRF token</div>');
    }
    $tip_act = $_POST['tip_act'] ?? null;
    $numar = $_POST['numar'] ?? null;
    $titlu = $_POST['titlu'] ?? null;
    $data_vigoare = $_POST['data_vigoare'] ?? null;
    $emitent = $_POST['emitent'] ?? null;
    $publicatie = $_POST['publicatie'] ?? null;
    $link_html = $_POST['link_html'] ?? null;
    $text = $_POST['text'] ?? null;

    if (isset($_GET['mode']) && $_GET['mode'] == 'edit') {
        if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
            die('Invalid ID');
        }
        $id = (int)$_GET['id'];
        $stmt = mysqli_prepare($conn, "UPDATE legislatie_salvata SET tip_act=?, numar=?, titlu=?, data_vigoare=?, emitent=?, publicatie=?, link_html=?, text=?, last_updated=NOW() WHERE id=? AND uid=?");
        mysqli_stmt_bind_param($stmt, 'ssssssssii', $tip_act, $numar, $titlu, $data_vigoare, $emitent, $publicatie, $link_html, $text, $id, $uid_to_use);
        if (!mysqli_stmt_execute($stmt)) {
            die('Error: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        echo '<div class="callout success">Înregistrare modificată.</div>';
        echo "<script>setTimeout(function(){ window.location='sitelegislation.php'; },1200);</script>";
        include '../bottom.php';
        exit();
    } else {
        // new
        $stmt = mysqli_prepare($conn, "INSERT INTO legislatie_salvata (tip_act, numar, titlu, data_vigoare, emitent, publicatie, link_html, text, last_updated, uid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
        mysqli_stmt_bind_param($stmt, 'ssssssssi', $tip_act, $numar, $titlu, $data_vigoare, $emitent, $publicatie, $link_html, $text, $uid_to_use);
        if (!mysqli_stmt_execute($stmt)) {
            die('Error: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        echo '<div class="callout success">Înregistrare adăugată.</div>';
        echo "<script>setTimeout(function(){ window.location='sitelegislation.php'; },1200);</script>";
        include '../bottom.php';
        exit();
    }
}

// --- list with search and pagination ---
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = 'WHERE uid = ?';
$params = [$uid_to_use];
$types = 'i';
if ($search !== '') {
    // search in multiple fields incl. text
    $where .= " AND (tip_act LIKE ? OR numar LIKE ? OR titlu LIKE ? OR text LIKE ? )";
    $kw = '%' . $search . '%';
    $params[] = $kw;
    $params[] = $kw;
    $params[] = $kw;
    $params[] = $kw;
    $types .= 'ssss';
}

// count
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS cnt FROM legislatie_salvata $where");
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $cnt);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
$numar = (int)$cnt;

$pages = new Pagination;
$pages->items_total = $numar;
$pages->mid_range = 5;
$pages->paginate();
$limit = $pages->limit;

// fetch rows
$sql = "SELECT id, tip_act, numar, titlu, data_vigoare, emitent, publicatie, link_html, LEFT(text,400) AS excerpt, last_updated FROM legislatie_salvata ";
$sql .= $where . ' ';
$sql .= "ORDER BY last_updated DESC " . $limit;

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>

<form method="get" action="sitelegislation.php">
  <div class="grid-x grid-padding-x">
    <div class="cell small-8">
      <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Caută în text, titlu, număr..." />
    </div>
    <div class="cell small-4">
      <button class="button" type="submit">Caută</button>
      <a class="button" href="sitelegislation.php?">Reset</a>
      <a class="button success" href="sitelegislation.php?mode=new">Adaugă</a>
    </div>
  </div>
</form>

<table class="unstriped">
  <thead>
    <tr>
      <th>Tip</th>
      <th>Număr</th>
      <th>Titlu</th>
            <th>Intrare în vigoare</th>
      <th>Publicație</th>
      <th>Emitent</th>
      <th>Actualizat</th>
      <th>Acțiuni</th>
    </tr>
  </thead>
  <tbody>
<?php while ($row = ezpub_fetch_array($result)) { ?>
    <tr>
      <td><?php echo htmlspecialchars($row['tip_act']); ?></td>
      <td><?php echo htmlspecialchars($row['numar']); ?></td>
      <td><?php echo htmlspecialchars($row['titlu']); ?></td>
            <td>
                <?php
                    if (!empty($row['data_vigoare']) && $row['data_vigoare'] != '0000-00-00') {
                        echo htmlspecialchars(date('d.m.Y', strtotime($row['data_vigoare'])));
                    } else {
                        echo '&nbsp;';
                    }
                ?>
            </td>
            <td><?php echo htmlspecialchars($row['publicatie']); ?></td>
            <td><?php echo htmlspecialchars($row['emitent']); ?></td>
            <td><?php echo htmlspecialchars($row['last_updated']); ?></td>
            <td>
                <a href="sitelegislation.php?mode=view&id=<?php echo $row['id']; ?>" title="Vezi">
                    <i class="fa fa-eye" aria-hidden="true"></i>
                </a>
                &nbsp;
                <a href="sitelegislation.php?mode=edit&id=<?php echo $row['id']; ?>" title="Editează">
                    <i class="fa fa-edit" aria-hidden="true"></i>
                </a>
                &nbsp;
                <a href="sitelegislation.php?mode=delete&id=<?php echo $row['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" onclick="return confirm('Ștergi înregistrarea?')" title="Șterge">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </a>
            </td>
    </tr>
<?php } ?>
  </tbody>
</table>

<?php echo $pages->display_pages(); ?>

<?php
// view / new / edit forms
if (isset($_GET['mode']) && $_GET['mode'] == 'view' && isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM legislatie_salvata WHERE id=? AND uid=?");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $uid_to_use);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $item = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    if (!$item) { echo '<div class="callout alert">Înregistrare negăsită</div>'; }
    else {
        echo '<h3>' . htmlspecialchars($item['titlu']) . '</h3>';
        echo '<p><strong>Tip:</strong> ' . htmlspecialchars($item['tip_act']) . '</p>';
        echo '<p><strong>Număr:</strong> ' . htmlspecialchars($item['numar']) . '</p>';
        echo '<p><strong>Emitent:</strong> ' . htmlspecialchars($item['emitent']) . '</p>';
        echo '<p><strong>Publicație:</strong> ' . htmlspecialchars($item['publicatie']) . '</p>';
        echo '<p><strong>Link:</strong> <a href="' . htmlspecialchars($item['link_html']) . '" target="_blank">Deschide</a></p>';
        echo '<div style="white-space:pre-wrap;border:1px solid #ddd;padding:0.5rem;margin-top:1rem;">' . htmlspecialchars($item['text']) . '</div>';
    }
}

if (isset($_GET['mode']) && in_array($_GET['mode'], ['new','edit'])) {
    $mode = $_GET['mode'];
    $id = 0; $item = ['tip_act'=>'','numar'=>'','titlu'=>'','data_vigoare'=>'','emitent'=>'','publicatie'=>'','link_html'=>'','text'=>''];
    if ($mode == 'edit') {
        if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) { die('Invalid ID'); }
        $id = (int)$_GET['id'];
        $stmt = mysqli_prepare($conn, "SELECT * FROM legislatie_salvata WHERE id=? AND uid=?");
        mysqli_stmt_bind_param($stmt, 'ii', $id, $uid_to_use);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $item = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        if (!$item) die('Not found');
    }
    ?>
    <form method="post" action="sitelegislation.php?mode=<?php echo $mode; ?><?php if($mode=='edit') echo '&id=' . $id; ?>">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
      <label>Tip act<input type="text" name="tip_act" value="<?php echo htmlspecialchars($item['tip_act']); ?>" /></label>
      <label>Număr<input type="text" name="numar" value="<?php echo htmlspecialchars($item['numar']); ?>" /></label>
      <label>Titlu<textarea name="titlu"><?php echo htmlspecialchars($item['titlu']); ?></textarea></label>
      <label>Dată vigoare<input type="date" name="data_vigoare" value="<?php echo htmlspecialchars($item['data_vigoare']); ?>" /></label>
      <label>Emitent<input type="text" name="emitent" value="<?php echo htmlspecialchars($item['emitent']); ?>" /></label>
      <label>Publicație<input type="text" name="publicatie" value="<?php echo htmlspecialchars($item['publicatie']); ?>" /></label>
      <label>Link HTML<input type="text" name="link_html" value="<?php echo htmlspecialchars($item['link_html']); ?>" /></label>
      <label>Text<textarea name="text" style="min-height:200px"><?php echo htmlspecialchars($item['text']); ?></textarea></label>
      <button class="button success" type="submit"><?php echo ($mode=='edit')? 'Salvează modificările':'Adaugă înregistrare'; ?></button>
      <a class="button" href="sitelegislation.php">Renunță</a>
    </form>
    <?php
}

include '../bottom.php';
