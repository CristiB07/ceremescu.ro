
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
            throw new RuntimeException('Nu pot crea directorul de depozitare: ' . $dir);
        }
    }
}

function pad_ticket_id(int $id): string { return str_pad((string)$id, 6, '0', STR_PAD_LEFT); }
function base_data_dir(): string { global $hddpath; return $hddpath . 'tickete'; }


function insert_attachment(PDO $pdo, int $ticketId, ?int $replyId, array $att): int
{
    $sql = "
        INSERT INTO `tickets_attachments`
        (`ticket_id`, `reply_id`, `stored`, `name`, `mime`, `size`, `is_internal`, `uploaded_by_type`, `uploaded_by_ui`)
        VALUES (:tid, :rid, :stored, :name, :mime, :size, :is_internal, :by_type, :by_ui)
    ";

    $stmt = $pdo->prepare($sql);

    // Tipuri explicite – opțional, dar util
    $stmt->bindValue(':tid',  $ticketId, PDO::PARAM_INT);
    if ($replyId === null) {
        $stmt->bindValue(':rid', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(':rid', $replyId, PDO::PARAM_INT);
    }
    $stmt->bindValue(':stored', $att['stored']); // VARCHAR
    // name și mime pot fi NULL dacă așa vrei (altfel asigură-le string)
    $name = $att['name'] ?? null;
    $stmt->bindValue(':name', $name, $name === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

    $mime = $att['mime'] ?? null;
    $stmt->bindValue(':mime', $mime, $mime === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

    $stmt->bindValue(':size', (int)($att['size'] ?? 0), PDO::PARAM_INT);
    $stmt->bindValue(':is_internal', (int)($att['is_internal'] ?? 0), PDO::PARAM_INT);
    $stmt->bindValue(':by_type', $att['uploaded_by_type']); // ex. 'CLIENT'
    $stmt->bindValue(':by_ui',   $att['uploaded_by_ui']);   // ex. user id

    $stmt->execute();

    return (int)$pdo->lastInsertId();
}

function get_attachments_for_viewer(PDO $pdo, int $ticketId, string $role): array {
    // Client vede doar is_internal=0 și: dacă e agent reply -> trebuie approved; pentru admin/client non-internal se afișează indiferent de validare
    $sql = "SELECT a.*, r.reply_by_type, r.reply_validated
            FROM tickets_attachments a
            JOIN tickets_replies r ON r.reply_id = a.reply_id
            WHERE a.ticket_id = :tid";
    $stmt = $pdo->prepare($sql); $stmt->execute([':tid'=>$ticketId]);
    $all = $stmt->fetchAll();
    if ($role === 'admin' || $role === 'AGENT') return $all;
    // client
    return array_values(array_filter($all, function($row){
        if ((int)$row['is_internal'] === 1) return false;
        if ($row['reply_by_type'] === 'AGENT' && $row['reply_validated'] !== 'approved') return false;
        return true;
    }));
}
?>
