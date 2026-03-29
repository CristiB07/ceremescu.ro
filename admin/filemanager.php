<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle = "File Manager";
include '../dashboard/header.php';

if(!isset($_SESSION)) { session_start(); }
if (!isset($_SESSION['userlogedin']) || $_SESSION['userlogedin']!="Yes" || !isset($_SESSION['clearence']) || $_SESSION['clearence']!='ADMIN'){
    header("location:$strSiteURL/login/index.php?message=MLF");
    die;
}

function human_filesize($bytes, $decimals = 2) {
    $sz = array('B','KB','MB','GB','TB');
    $factor = ($bytes > 0) ? floor((strlen((string)$bytes) - 1) / 3) : 0;
    if ($factor == 0) return $bytes . ' ' . $sz[$factor];
    return sprintf("%.{$decimals}f %s", $bytes / pow(1024, $factor), $sz[$factor]);
}

function folder_size($dir) {
    $size = 0;
    if (!is_dir($dir)) return 0;
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($files as $file) {
        if ($file->isFile()) $size += $file->getSize();
    }
    return $size;
}

$base = rtrim($hddpath, "/\\") . '/';
if (!is_dir($base)) {
    echo "<div class=\"callout alert\">Base folder " . htmlspecialchars($base) . " not found.</div>";
    include '../bottom.php';
    exit;
}

$entries = scandir($base);
$folders = array();
foreach ($entries as $e) {
    if ($e === '.' || $e === '..') continue;
    $p = $base . $e;
    if (is_dir($p)) $folders[] = $e;
}

$cache_file = $base . '.filemanager_cache.json';
$cache_ttl = 300; // seconds
$cache = array();
if (is_file($cache_file)) {
    $c = @file_get_contents($cache_file);
    $json = @json_decode($c, true);
    if (is_array($json)) $cache = $json;
}

function get_cached_folder_size($path, &$cache, $cache_file, $cache_ttl) {
    $now = time();
    $key = md5($path);
    if (isset($cache[$key]) && isset($cache[$key]['ts']) && ($now - $cache[$key]['ts'] <= $cache_ttl)) {
        return $cache[$key]['size'];
    }
    $size = folder_size($path);
    $cache[$key] = array('size' => $size, 'ts' => $now);
    @file_put_contents($cache_file, json_encode($cache), LOCK_EX);
    return $size;
}

$totalSize = get_cached_folder_size($base, $cache, $cache_file, $cache_ttl);
?>
<div class="grid-container">
    <div class="grid-x grid-margin-x">
        <div class="cell large-12">
            <h3><?php echo $strCurrentFiles ?> — <?php echo human_filesize($totalSize) ?></h3>
            <table class="stack">
                <thead>
                    <tr>
                        <th>Folder</th>
                        <th>Size</th>
                        <th>Files</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($folders as $f):
                    $full = $base . $f;
                    $s = get_cached_folder_size($full, $cache, $cache_file, $cache_ttl);
                    $files = array();
                    $it = new DirectoryIterator($full);
                    foreach ($it as $fileinfo) {
                        if ($fileinfo->isFile()) $files[] = $fileinfo->getFilename();
                    }
                ?>
                <tr>
                    <td>
                        <a href="#" onclick="(function(){var el=document.getElementById('list-<?php echo md5($f)?>'); if(!el) return false; el.style.display=(el.style.display==='none'?'block':'none'); return false;})();" title="Arată/Ascunde conținut">
                            <i class="fas fa-folder-open" style="margin-right:8px;"></i>
                            <?php echo htmlspecialchars($f) ?>
                        </a>
                    </td>
                    <td><?php echo human_filesize($s) ?></td>
                    <td><?php echo count($files) ?></td>
                </tr>
                <?php $openStyle = (isset($_GET['folder']) && $_GET['folder']==$f) ? 'block' : 'none'; ?>
                <tr id="list-<?php echo md5($f)?>" style="display:<?php echo $openStyle?>;">
                    <td colspan="3">
                        <?php
                        // sorting settings (applies to all folders)
                        $sort = isset($_GET['sort']) && in_array($_GET['sort'], ['name','date']) ? $_GET['sort'] : 'date';
                        $order = isset($_GET['order']) && in_array($_GET['order'], ['asc','desc']) ? $_GET['order'] : ($sort=='date' ? 'desc' : 'asc');
                        $self = htmlspecialchars($_SERVER['PHP_SELF']);
                        $toggleOrder = function($col) use ($sort, $order) {
                            if ($sort === $col) return $order === 'asc' ? 'desc' : 'asc';
                            return $col === 'date' ? 'desc' : 'asc';
                        };
                        // pagination settings
                        $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 100;
                        if ($per_page <= 0) $per_page = 100;
                        if ($per_page > 1000) $per_page = 1000;
                        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                        ?>
                        <table class="stack">
                            <thead>
                                <tr>
                                    <th><?php
                                        $q = $_GET; $q['sort']='name'; $q['order']=$toggleOrder('name'); $q['page']=1; $q['folder']=$f;
                                        echo '<a href="' . $self . '?' . http_build_query($q) . '">File</a>';
                                    ?></th>
                                    <th style="width:120px;"><?php
                                        $q = $_GET; unset($q['sort']); unset($q['order']); $q['page']=1; $q['folder']=$f;
                                        echo '<a href="' . $self . '?' . http_build_query($q) . '">Size</a>';
                                    ?></th>
                                    <th style="width:180px;"><?php
                                        $q = $_GET; $q['sort']='date'; $q['order']=$toggleOrder('date'); $q['page']=1; $q['folder']=$f;
                                        echo '<a href="' . $self . '?' . http_build_query($q) . '">Modified</a>';
                                    ?></th>
                                </tr>
                            </thead>
                            <tbody>
                        <?php
                        $fileInfos = array();
                        $it = new DirectoryIterator($full);
                        foreach ($it as $fileinfo) {
                            if ($fileinfo->isFile()) {
                                $name = $fileinfo->getFilename();
                                $rel = $f . '/' . $name;
                                $fileInfos[] = array(
                                    'name' => $name,
                                    'rel' => $rel,
                                    'size' => $fileinfo->getSize(),
                                    'mtime' => $fileinfo->getMTime(),
                                    'icon' => function_exists('getFileIcon') ? getFileIcon($name) : 'far fa-file'
                                );
                            }
                        }

                        usort($fileInfos, function($a, $b) use ($sort, $order) {
                            if ($sort === 'name') {
                                $cmp = strcasecmp($a['name'], $b['name']);
                            } else { // date
                                $cmp = $a['mtime'] <=> $b['mtime'];
                            }
                            return $order === 'asc' ? $cmp : -$cmp;
                        });

                        // total before pagination
                        $total_files = count($fileInfos);
                        $total_pages = max(1, ceil($total_files / $per_page));
                        if ($page > $total_pages) $page = $total_pages;
                        $start = ($page - 1) * $per_page;
                        $fileInfos = array_slice($fileInfos, $start, $per_page);

                        // render pagination controls
                        if ($total_files > $per_page) {
                            $qbase = $_GET;
                            $qbase['folder'] = $f;
                            echo '<div style="margin-bottom:8px;">';
                            if ($page > 1) {
                                $qbase['page'] = $page - 1; $qbase['per_page'] = $per_page;
                                echo '<a href="' . $self . '?' . http_build_query($qbase) . '#list-' . md5($f) . '">&laquo; Prev</a> ';
                            }
                            echo " Page $page of $total_pages ";
                            if ($page < $total_pages) {
                                $qbase['page'] = $page + 1; $qbase['per_page'] = $per_page;
                                echo ' <a href="' . $self . '?' . http_build_query($qbase) . '#list-' . md5($f) . '">Next &raquo;</a>';
                            }
                            echo '</div>';
                        }

                        foreach ($fileInfos as $fi):
                            $token = base64_encode($fi['rel']);
                            $download = $strSiteURL . "/admin/filedownload.php?f=" . rawurlencode($token);
                        ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo $download ?>" title="<?php echo htmlspecialchars($fi['name']) ?>">
                                            <i class="<?php echo htmlspecialchars($fi['icon']) ?>" style="margin-right:8px;"></i>
                                            <?php echo htmlspecialchars($fi['name']) ?>
                                        </a>
                                    </td>
                                    <td><?php echo human_filesize($fi['size']) ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', $fi['mtime']) ?></td>
                                </tr>
                        <?php endforeach; ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../bottom.php';
?>
