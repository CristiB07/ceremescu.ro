<?php
require_once '../settings.php';
require_once '../classes/common.php';
// includes/mail.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/bootstrap.php';

/**
 * CONFIG SMTP — editați corespunzător
 */
$MAIL_FROM = $SmtpUser;
$MAIL_FROM_NAME = $strSiteOwner;
$SMTP_HOST = $SmtpServer;
$SMTP_PORT = $SmtpPort;
$SMTP_USER = $SmtpUser;
$SMTP_PASS = $SmtpPass;
$SMTP_SECURE = PHPMailer::ENCRYPTION_STARTTLS;

/**
 * Tabele și coloane pentru email (conform cerinței):
 * - Admin/Agent: date_utilizatori.utilizator_Email
 * - Client: site_accounts.account_email
 *
 * Presupunem cheia primară 'id' pe ambele tabele — dacă la voi se numește altfel,
 * actualizați constantul potrivit mai jos.
 */
const DU_TABLE = 'date_utilizatori';
const DU_IDCOL = 'id';
const DU_EMAILCOL = 'utilizator_Email';

const SA_TABLE = 'site_accounts';
const SA_IDCOL = 'id';
const SA_EMAILCOL = 'account_email';

/**
 * Destinatari ADMIN (de demo). Integrați cu tabelele voastre pentru o listă reală.
 */
$NOTIFY_ADMIN_EMAILS = ['eu@cristianbanu.ro'];
function notify_admin_list(): array { global $NOTIFY_ADMIN_EMAILS; return $NOTIFY_ADMIN_EMAILS; }

/**
 * Utilitar: trimite email simplu (fără atașamente).
 */
function send_mail(array $to, string $subject, string $body): bool {
    if (empty($to)) return false;
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $GLOBALS['SMTP_HOST'];
        $mail->Port       = $GLOBALS['SMTP_PORT'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $GLOBALS['SMTP_USER'];
        $mail->Password   = $GLOBALS['SMTP_PASS'];
        $mail->SMTPSecure = $GLOBALS['SMTP_SECURE'];
        $mail->SMTPDebug = 0;

        $mail->setFrom($GLOBALS['MAIL_FROM'], $GLOBALS['MAIL_FROM_NAME']);
        foreach ($to as $addr) { if ($addr) { $mail->addAddress($addr); } }
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->isHTML(false);
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mail error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Helpers pentru extragerea emailurilor reale din DB
 */

function fetch_email_by_ui(string $table, string $idCol, int $ui, string $emailCol): ?string {
    global $conn;
    $sql = "SELECT `$emailCol` FROM `$table` WHERE `$idCol` = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $ui);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_row($res);
    mysqli_stmt_close($stmt);
    $email = $row[0] ?? null;
    return (is_string($email) && strpos($email, '@') !== false) ? $email : null;
}

function notify_client_list(int $clientUi): array {
    $email = fetch_email_by_ui(SA_TABLE, SA_IDCOL, $clientUi, SA_EMAILCOL);
    return $email ? [$email] : [];
}

function notify_agent_list(int $agentUi): array {
    $email = fetch_email_by_ui(DU_TABLE, DU_IDCOL, $agentUi, DU_EMAILCOL);
    return $email ? [$email] : [];
}

/**
 * Utilitar pentru subiect + mesaj: obține titlul și UI-ul clientului.
 */
function get_ticket_title_and_client(int $ticketId): array {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT ticket_title, ticket_createdby FROM tickets WHERE ticket_id=?");
    mysqli_stmt_bind_param($stmt, "i", $ticketId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ? [$row['ticket_title'], (int)$row['ticket_createdby']] : ['', 0];
}

/**
 * ===== Notificări către ADMIN =====
 */
function notify_ticket_created(int $ticketId, int $clientUi): void {
    $to = notify_admin_list();
    [$title, ] = get_ticket_title_and_client($ticketId);
    $subject = sprintf('[Helpdesk] Ticket nou #%d', $ticketId);
    $body    = sprintf('Client UI %d a deschis un ticket nou #%d: %s', $clientUi, $ticketId, $title);
    send_mail($to, $subject, $body);
}

function notify_agent_reply_posted(int $ticketId, int $replyId, int $agentUi): void {
    $to = notify_admin_list();
    $subject = sprintf('[Helpdesk] Reply agent pe ticket #%d', $ticketId);
    $body    = sprintf('Agent UI %d a postat un reply (ID %d) pe ticket #%d. Necesită validare.', $agentUi, $replyId, $ticketId);
    send_mail($to, $subject, $body);
}

function notify_client_reply_posted(int $ticketId, int $replyId, int $agentUi): void {
    $to = notify_admin_list();
    $subject = sprintf('[Helpdesk] Reply client pe ticket #%d', $ticketId);
    $body    = sprintf('Clientul UI %d a postat un reply (ID %d) pe ticket #%d. Necesită validare.', $agentUi, $replyId, $ticketId);
    send_mail($to, $subject, $body);
}
/**
 * ===== Notificări către CLIENT =====
 */
function notify_client_ticket_created(int $ticketId, int $clientUi): void {
    $to = notify_client_list($clientUi);
    [$title, ] = get_ticket_title_and_client($ticketId);
    $subject = sprintf('[Helpdesk] Confirmare ticket #%d', $ticketId);
    $body    = sprintf('Ticketul tău #%d a fost creat: %s. Îți vom răspunde în cel mai scurt timp.', $ticketId, $title);
    send_mail($to, $subject, $body);
}

function notify_client_reply_approved(int $ticketId, int $replyId): void {
    [$title, $clientUi] = get_ticket_title_and_client($ticketId);
    $to = notify_client_list($clientUi);
    $subject = sprintf('[Helpdesk] Răspuns nou aprobat pe ticket #%d', $ticketId);
    $body    = sprintf('Un răspuns nou (ID %d) a fost aprobat pe ticketul tău #%d: %s. Te rugăm să verifici portalul.', $replyId, $ticketId, $title);
    send_mail($to, $subject, $body);
}

function notify_client_ticket_closed(int $ticketId): void {
    [$title, $clientUi] = get_ticket_title_and_client($ticketId);
    $to = notify_client_list($clientUi);
    $subject = sprintf('[Helpdesk] Ticket #%d a fost închis', $ticketId);
    $body    = sprintf('Ticketul tău #%d (%s) a fost închis de un administrator. Dacă problema persistă, poți solicita redeschiderea din portal.', $ticketId, $title);
    send_mail($to, $subject, $body);
}

function notify_client_ticket_reopened(int $ticketId): void {
    [$title, $clientUi] = get_ticket_title_and_client($ticketId);
    $to = notify_client_list($clientUi);
    $subject = sprintf('[Helpdesk] Ticket #%d a fost redeschis', $ticketId);
    $body    = sprintf('Ticketul tău #%d (%s) a fost redeschis. Vom continua să lucrăm la rezolvare.', $ticketId, $title);
    send_mail($to, $subject, $body);
}

function notify_client_ticket_assigned(int $ticketId, int $agentUi): void {
    [$title, $clientUi] = get_ticket_title_and_client($ticketId);
    $to = notify_client_list($clientUi);
    $subject = sprintf('[Helpdesk] Ticket #%d a fost alocat', $ticketId);
    $body    = sprintf('Ticketul tău #%d (%s) a fost alocat agentului UI %d. Vei primi notificări când apar răspunsuri.', $ticketId, $title, $agentUi);
    send_mail($to, $subject, $body);
}
?>
