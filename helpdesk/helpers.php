
<?php
require_once '../settings.php';
require_once '../classes/common.php';

require_once __DIR__ . '/bootstrap.php';

function safe_ext_from_mime(string $mime): string {
    static $map = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'text/plain' => 'txt',
        'application/zip' => 'zip',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-excel' => 'xls'
    ];
    return $map[$mime] ?? 'bin';
}

function generate_safe_name(string $ext): string {
    try { $rand = bin2hex(random_bytes(8)); } catch (Throwable $e) { $rand = bin2hex(openssl_random_pseudo_bytes(8)); }
    return $rand . '.' . $ext;
}

function ensure_dir(string $dir): void {
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0700, true)) {
            throw new RuntimeException('Nu pot crea folderul: ' . $dir);
        }
    }
}

function pad_ticket_id(int $id): string { return str_pad((string)$id, 6, '0', STR_PAD_LEFT); }
function base_data_dir(): string { global $hddpath; return $hddpath . 'tickete'; }


function insert_attachment(int $ticketId, ?int $replyId, array $att): int
{
    global $conn;
    $sql = "INSERT INTO `tickets_attachments` (`ticket_id`, `reply_id`, `stored`, `name`, `mime`, `size`, `is_internal`, `uploaded_by_type`, `uploaded_by_ui`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    $stored   = $att['stored'];
    $name     = $att['name'] ?? null;
    $mime     = $att['mime'] ?? null;
    $size     = (int)($att['size'] ?? 0);
    $internal = (int)($att['is_internal'] ?? 0);
    $byType   = $att['uploaded_by_type'];
    $byUi     = (int)$att['uploaded_by_ui'];
    mysqli_stmt_bind_param($stmt, "iisssiisi", $ticketId, $replyId, $stored, $name, $mime, $size, $internal, $byType, $byUi);
    mysqli_stmt_execute($stmt);
    $insertId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return (int)$insertId;
}

function get_attachments_for_viewer(int $ticketId, string $role): array {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT a.*, r.reply_by_type, r.reply_validated FROM tickets_attachments a JOIN tickets_replies r ON r.reply_id = a.reply_id WHERE a.ticket_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $ticketId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $all = [];
    while ($row = mysqli_fetch_assoc($res)) { $all[] = $row; }
    mysqli_stmt_close($stmt);
    if ($role === 'ADMIN' || $role === 'AGENT') return $all;
    return array_values(array_filter($all, function($row){
        if ((int)$row['is_internal'] === 1) return false;
        if ($row['reply_by_type'] === 'AGENT' && $row['reply_validated'] !== 'approved') return false;
        return true;
    }));
}
?>
