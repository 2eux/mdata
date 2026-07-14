<?php
/**
 * email_helper.php
 * ─────────────────────────────────────────────────────────────
 * Notifikasi email untuk sistem approval MDM.
 * Warna email dinamis mengikuti tabel theme di DB.
 *
 * Struktur DB yang dipakai:
 *   users        : id, nama, email, company_id, role_id
 *   role         : id, nama_role   (nama tabel: "role" bukan "roles")
 *   approval     : id, request_id, detail_material_id,
 *                  detail_service_id, approved_by, role,
 *                  step, status, note, approved_at
 *   theme  : id, company_id, fungsi, warna
 *
 * Fungsi warna yang dipakai di email (kolom fungsi):
 *   navbar, btn_approve, btn_reject, btn_primary,
 *   status_pending, status_active, table_header, alert
 *
 * MODE EMAIL:
 *   'gmail'    → Gmail SMTP + App Password (recommended)
 *   'mailtrap' → Testing gratis via mailtrap.io
 *   'php_mail' → php mail() bawaan XAMPP
 * ─────────────────────────────────────────────────────────────
 */

// ============================================================
//  PILIH MODE
// ============================================================
define('EMAIL_MODE', 'gmail');   // 'gmail' | 'mailtrap' | 'php_mail'

// ── Gmail ─────────────────────────────────────────────────────
define('GMAIL_USER', 'sydel016@gmail.com');
define('GMAIL_PASS', 'urnl vhtz gmrq fhbb');
define('GMAIL_NAME', 'MDM Approval System');

// ── Mailtrap (testing) ────────────────────────────────────────
define('MAILTRAP_HOST', 'sandbox.smtp.mailtrap.io');
define('MAILTRAP_USER', 'isi_user_mailtrap');
define('MAILTRAP_PASS', 'isi_pass_mailtrap');
define('MAILTRAP_PORT', 2525);

// ── php mail() ────────────────────────────────────────────────
define('PHP_MAIL_FROM',      'noreply@perusahaan.com');
define('PHP_MAIL_FROM_NAME', 'MDM Approval System');


// ============================================================
//  Load PHPMailer
// ============================================================
$_pm = dirname(__DIR__) . '/PHPMailer/';

if (is_dir($_pm)) {
    require_once $_pm . 'Exception.php';
    require_once $_pm . 'PHPMailer.php';
    require_once $_pm . 'SMTP.php';
} elseif (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/*=============================================================
  THEME LOADER — Ambil semua warna dari theme per company
=============================================================*/

/**
 * Kembalikan array ['fungsi' => '#hexcolor', ...] untuk company_id tertentu.
 * Kalau fungsi tidak ada di DB, pakai warna fallback hardcoded.
 */
function loadTheme(mysqli $koneksi, int $company_id): array {
    $fallback = [
        'navbar'         => '#1a3c6b',
        'btn_approve'    => '#13848C',
        'btn_reject'     => '#B91F1F',
        'btn_primary'    => '#13848C',
        'btn_edit'       => '#E89646',
        'btn_extend'     => '#2B7598',
        'status_pending' => '#F2F2A3',
        'status_active'  => '#ACF2A3',
        'table_header'   => '#E6F0F5',
        'alert'          => '#D30000',
        'logo_material'  => '#2B7598',
        'logo_service'   => '#E89646',
        'logo_vendor'    => '#9E8652',
        'request_type'   => '#7D8863',
    ];

    $ci = (int)$company_id;
    $rs = mysqli_query($koneksi, "
        SELECT fungsi, warna
        FROM theme
        WHERE company_id = $ci
    ");

    $theme = $fallback;
    if ($rs) {
        while ($row = mysqli_fetch_assoc($rs)) {
            if (!empty($row['fungsi']) && !empty($row['warna'])) {
                $theme[$row['fungsi']] = $row['warna'];
            }
        }
    }

    return $theme;
}

/**
 * Helper: ambil satu warna dari theme array, fallback ke default.
 */
function tc(array $theme, string $key, string $default = '#555555'): string {
    return $theme[$key] ?? $default;
}

/**
 * Menghasilkan warna teks kontras (hitam/putih) berdasarkan background hex.
 */
function contrastColor(string $hex): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    // Luminance relatif
    $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
    return ($luminance > 0.55) ? '#1a1a1a' : '#ffffff';
}

/**
 * Buat versi muted (terang 85%) dari hex color untuk background badge/row.
 */
function lightenHex(string $hex, float $factor = 0.85): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $r = (int)(hexdec(substr($hex, 0, 2)) + (255 - hexdec(substr($hex, 0, 2))) * $factor);
    $g = (int)(hexdec(substr($hex, 2, 2)) + (255 - hexdec(substr($hex, 2, 2))) * $factor);
    $b = (int)(hexdec(substr($hex, 4, 2)) + (255 - hexdec(substr($hex, 4, 2))) * $factor);
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}


/*=============================================================
  QUERY HELPERS
=============================================================*/

function getUsersByRole(mysqli $koneksi, string $role_name, int $company_id): array {
    $rn = mysqli_real_escape_string($koneksi, $role_name);
    $ci = (int)$company_id;
    $rs = mysqli_query($koneksi, "
        SELECT u.nama, u.email
        FROM users u
        JOIN role r ON u.role_id = r.id
        WHERE r.nama_role  = '$rn'
          AND u.company_id = '$ci'
          AND u.email IS NOT NULL
          AND u.email != ''
    ");
    $out = [];
    while ($row = mysqli_fetch_assoc($rs)) $out[] = $row;
    return $out;
}

function getRequestorEmail(mysqli $koneksi, int $request_id): array {
    return mysqli_fetch_assoc(mysqli_query($koneksi, "
        SELECT u.nama, u.email
        FROM request_header rh
        JOIN users u ON rh.requestor_id = u.id
        WHERE rh.id = $request_id
    ")) ?? [];
}

function getAllApprovers(mysqli $koneksi, int $request_id): array {
    $rs = mysqli_query($koneksi, "
        SELECT a.step, a.status, a.note, a.approved_at, u.nama, u.email
        FROM approval a
        JOIN users u ON a.approved_by = u.id
        WHERE a.request_id           = $request_id
          AND a.detail_material_id IS NULL
          AND a.detail_service_id  IS NULL
        ORDER BY a.id ASC
    ");
    $out = [];
    while ($row = mysqli_fetch_assoc($rs)) $out[] = $row;
    return $out;
}

function getDetailItems(mysqli $koneksi, int $request_id, string $request_type): array {
    $dt  = ($request_type == 'MATERIAL') ? 'request_detail_material' : 'request_detail_service';
    $col = ($request_type == 'MATERIAL') ? 'detail_material_id'      : 'detail_service_id';

    $rs = mysqli_query($koneksi, "
        SELECT
            d.id,
            d.description,
            d.status,
        GROUP_CONCAT(
            CASE WHEN a.status IN ('REJECTED')
            THEN CONCAT(
                COALESCE(a.step, '-'), ' | ',
                COALESCE(a.status, '-'),
                IF(a.note IS NOT NULL AND a.note != '',
                CONCAT(' | Alasan: \"', a.note, '\"'), ''),
                ' | ',
                IF(a.approved_at IS NOT NULL,
                DATE_FORMAT(a.approved_at, '%W %d %b %Y  %H:%i:%s'), '-')
            )
            ELSE NULL END
            ORDER BY a.id ASC
            SEPARATOR '\n'
        ) AS approval_history
        FROM $dt d
        LEFT JOIN approval a
               ON a.request_id = d.request_id
              AND a.$col       = d.id
        WHERE d.request_id = $request_id
        GROUP BY d.id
        ORDER BY d.id ASC
    ");
    $out = [];
    while ($row = mysqli_fetch_assoc($rs)) $out[] = $row;
    return $out;
}


/*=============================================================
  EMAIL 1 — Notifikasi ke approver BERIKUTNYA
=============================================================*/
function sendNotifNextStep(mysqli $koneksi, int $request_id, string $next_step, array $header): void {
    $company_id = (int)($header['company_id'] ?? 0);
    $recipients = getUsersByRole($koneksi, $next_step, $company_id);
    if (empty($recipients)) return;

    $theme = loadTheme($koneksi, $company_id);

    $rno   = htmlspecialchars($header['request_no']);
    $rtype = htmlspecialchars($header['request_type']);
    $dept  = htmlspecialchars($header['departemen']);
    $rname = htmlspecialchars($header['nama']);
    $rdate = date('d M Y', strtotime($header['request_date']));
    $now   = fmtDateTime(date('Y-m-d H:i:s'));
    $step  = htmlspecialchars($next_step);

    $navColor  = tc($theme, 'navbar');
    $navText   = contrastColor($navColor);
    $primColor = tc($theme, 'btn_primary');
    $primLight = lightenHex($primColor, 0.88);

    $subject = "[$rno] Request Menunggu Review Anda — $step";

    $body = emailWrap($navColor, $navText, 'Request Menunggu Review Anda', $now, $theme, "
        <p style='margin:0 0 14px;font-size:14px;color:#333'>
            Halo <strong>$step</strong>,
        </p>
        <p style='margin:0 0 20px;font-size:14px;color:#555;line-height:1.6'>
            Request berikut sudah disetujui oleh step sebelumnya dan
            <strong>menunggu tindakan Anda</strong> untuk dilanjutkan.
        </p>

        " . infoCard($theme, [
            'Request No'    => "<strong>$rno</strong>",
            'Tipe Request'  => rtypeBadge($rtype, $theme),
            'Departemen'    => $dept,
            'Requester'     => $rname,
            'Tanggal'       => $rdate,
            'Step Sekarang' => "<strong style='color:{$primColor}'>$step</strong>",
        ]) . "

        <div style='margin-top:24px;padding:16px 20px;background:{$primLight};
                    border-left:4px solid {$primColor};border-radius:0 6px 6px 0'>
            <p style='margin:0;font-size:13px;color:#333;line-height:1.6'>
                Silakan login ke sistem MDM dan buka halaman review untuk
                melakukan <strong>approve</strong> atau <strong>reject</strong>.
            </p>
        </div>
    ");

    _sendToList($recipients, $subject, $body);
}


/*=============================================================
  EMAIL 2 — Notifikasi ke requestor: ditolak
=============================================================*/
function sendNotifRejected(
    mysqli  $koneksi,
    int     $request_id,
    array   $header,
    string  $rejected_by_role,
    string  $reject_note,
    ?string $item_description = null
): void {
    $requestor = getRequestorEmail($koneksi, $request_id);
    if (empty($requestor['email'])) return;

    $company_id = (int)($header['company_id'] ?? 0);
    $theme      = loadTheme($koneksi, $company_id);

    $rno      = htmlspecialchars($header['request_no']);
    $rtype    = htmlspecialchars($header['request_type']);
    $dept     = htmlspecialchars($header['departemen']);
    $rname    = htmlspecialchars($header['nama']);
    $now      = fmtDateTime(date('Y-m-d H:i:s'));
    $note     = nl2br(htmlspecialchars($reject_note));
    $scope    = $item_description
        ? 'Item: <strong>' . htmlspecialchars($item_description) . '</strong>'
        : '<strong>Semua Item (General Reject)</strong>';
    $rejColor = tc($theme, 'btn_reject', '#B91F1F');
    $rejLight = lightenHex($rejColor, 0.88);
    $navColor = tc($theme, 'navbar');
    $navText  = contrastColor($navColor);

    $subject = "[$rno] Request Ditolak oleh $rejected_by_role";

    $body = emailWrap($rejColor, '#ffffff', 'Request Ditolak', $now, $theme, "
        <p style='margin:0 0 14px;font-size:14px;color:#333'>
            Halo <strong>" . htmlspecialchars($requestor['nama']) . "</strong>,
        </p>
        <p style='margin:0 0 20px;font-size:14px;color:#555;line-height:1.6'>
            Request Anda telah <strong style='color:{$rejColor}'>ditolak</strong>
            oleh <strong>" . htmlspecialchars($rejected_by_role) . "</strong>.
        </p>

        " . infoCard($theme, [
            'Request No'      => "<strong>$rno</strong>",
            'Tipe Request'    => rtypeBadge($rtype, $theme),
            'Departemen'      => $dept,
            'Requester'       => $rname,
            'Scope Penolakan' => $scope,
            'Ditolak Oleh'    => "<strong style='color:{$rejColor}'>" .
                                  htmlspecialchars($rejected_by_role) . "</strong>",
            'Waktu'           => $now,
        ]) . "

        <div style='margin-top:24px'>
            <div style='font-weight:700;font-size:12px;letter-spacing:.06em;
                        text-transform:uppercase;color:{$rejColor};
                        border-bottom:2px solid {$rejColor};
                        padding-bottom:6px;margin-bottom:12px'>
                Alasan Penolakan
            </div>
            <div style='background:{$rejLight};border-left:4px solid {$rejColor};
                        padding:14px 18px;border-radius:0 6px 6px 0;
                        font-size:13px;color:#333;line-height:1.7'>
                $note
            </div>
        </div>
    ");

    _sendToOne($requestor['email'], $requestor['nama'], $subject, $body);
}


/*=============================================================
  EMAIL 3 — FINAL EMAIL (MDM Global selesai)
=============================================================*/
function sendFinalEmail(mysqli $koneksi, int $request_id, array $header, string $final_status): void {

    $company_id = (int)($header['company_id'] ?? 0);
    $theme      = loadTheme($koneksi, $company_id);

    // ── Kumpulkan semua penerima unik ─────────────────────────
    $recipients = [];
    $requestor  = getRequestorEmail($koneksi, $request_id);
    if (!empty($requestor['email'])) {
        $recipients[$requestor['email']] = $requestor['nama'];
    }
    foreach (getAllApprovers($koneksi, $request_id) as $ap) {
        if (!empty($ap['email'])) {
            $recipients[$ap['email']] = $ap['nama'];
        }
    }
    if (empty($recipients)) return;

    $rno     = htmlspecialchars($header['request_no']);
    $rtype   = $header['request_type'];
    $dept    = htmlspecialchars($header['departemen']);
    $rname   = htmlspecialchars($header['nama']);
    $rdate   = date('d M Y', strtotime($header['request_date']));
    $now     = fmtDateTime(date('Y-m-d H:i:s'));
    $isOK    = ($final_status === 'COMPLETED');

    $approveColor = tc($theme, 'btn_approve', '#13848C');
    $rejectColor  = tc($theme, 'btn_reject',  '#B91F1F');
    $navColor     = tc($theme, 'navbar',       '#1a3c6b');
    $tableHdr     = tc($theme, 'table_header', '#E6F0F5');
    $headerColor  = $isOK ? $approveColor : $rejectColor;
    $headerText   = contrastColor($headerColor);
    $titleText    = $isOK ? 'Request Selesai (COMPLETED)' : 'Request Ditolak (REJECTED)';
    $subject      = "[$rno] $titleText — Laporan Final";

    // ── Tabel history per step ────────────────────────────────
    $stepOrder = ['MDM Business Unit', 'Direct Manager', 'BPO Local', 'MDM Global'];
    $stepData  = [];
    foreach (getAllApprovers($koneksi, $request_id) as $ap) {
        $stepData[$ap['step']] = $ap;
    }

    $historyRows = '';
    $rowBg       = ['#ffffff', lightenHex($navColor, 0.97)];
    $rowIdx      = 0;

    foreach ($stepOrder as $step) {
        $bg = $rowBg[$rowIdx % 2];
        $rowIdx++;

        if (!isset($stepData[$step])) {
            $pendingBg = lightenHex(tc($theme, 'status_pending', '#F2F2A3'), 0.5);
            $historyRows .= "
            <tr style='background:{$bg}'>
                <td style='padding:10px 12px;font-size:13px;border-bottom:1px solid #f0f0f0'>
                    <strong>$step</strong>
                </td>
                <td style='padding:10px 12px;font-size:13px;color:#999;
                           border-bottom:1px solid #f0f0f0'>—</td>
                <td style='padding:10px 12px;border-bottom:1px solid #f0f0f0'>
                    " . dynamicBadge('PENDING', $theme) . "
                </td>
                <td style='padding:10px 12px;font-size:12px;color:#999;
                           border-bottom:1px solid #f0f0f0'>—</td>
                <td style='padding:10px 12px;font-size:11px;color:#999;white-space:nowrap;
                           border-bottom:1px solid #f0f0f0'>—</td>
            </tr>";
            continue;
        }

        $ap       = $stepData[$step];
        $approver = htmlspecialchars($ap['nama']);
        $at       = fmtDateTime($ap['approved_at']);
        $noteCell = (!empty($ap['note']))
            ? "<div style='background:" . lightenHex($rejectColor, 0.9) . ";
                           border-left:3px solid {$rejectColor};
                           padding:6px 10px;font-size:11px;border-radius:0 4px 4px 0;
                           color:#333;line-height:1.5'>"
              . nl2br(htmlspecialchars($ap['note'])) . "</div>"
            : "<span style='color:#bbb;font-size:12px'>—</span>";

        // Warna baris berdasarkan status
        if ($ap['status'] === 'REJECTED') {
            $bg = lightenHex($rejectColor, 0.93);
        } elseif (in_array($ap['status'], ['APPROVED', 'GENERAL_APPROVED'])) {
            $bg = lightenHex($approveColor, 0.93);
        }

        $historyRows .= "
        <tr style='background:{$bg}'>
            <td style='padding:10px 12px;font-size:13px;border-bottom:1px solid rgba(0,0,0,.06)'>
                <strong>$step</strong>
            </td>
            <td style='padding:10px 12px;font-size:13px;color:#333;
                       border-bottom:1px solid rgba(0,0,0,.06)'>$approver</td>
            <td style='padding:10px 12px;border-bottom:1px solid rgba(0,0,0,.06)'>
                " . dynamicBadge($ap['status'], $theme) . "
            </td>
            <td style='padding:10px 12px;border-bottom:1px solid rgba(0,0,0,.06)'>$noteCell</td>
            <td style='padding:10px 12px;font-size:11px;color:#555;white-space:nowrap;
                       border-bottom:1px solid rgba(0,0,0,.06)'>$at</td>
        </tr>";
    }

    // ── Tabel detail item ─────────────────────────────────────
    $items    = getDetailItems($koneksi, $request_id, $rtype);
    $itemRows = '';
    $rowIdx2  = 0;

    foreach ($items as $item) {
        $desc    = htmlspecialchars($item['description']);
        $rowBg2  = $rowBg[$rowIdx2 % 2];
        $rowIdx2++;

        $history = '';
        if (!empty(trim($item['approval_history'] ?? ''))) {
            $history = "<pre style='margin:0;padding:10px 12px;font-family:\"Courier New\",monospace;
                                    font-size:11px;line-height:1.6;color:#444;white-space:pre-wrap;
                                    word-break:break-word;background:" . lightenHex($navColor, 0.96) . ";
                                    border-radius:5px;border:1px solid rgba(0,0,0,.07)'>"
                     . htmlspecialchars($item['approval_history'])
                     . "</pre>";
        }

        if ($item['status'] === 'REJECTED') {
            $rowBg2 = lightenHex($rejectColor, 0.93);
        } elseif ($item['status'] === 'APPROVED') {
            $rowBg2 = lightenHex($approveColor, 0.93);
        }

        $itemRows .= "
        <tr style='background:{$rowBg2}'>
            <td style='padding:10px 12px;font-size:13px;color:#333;
                       border-bottom:1px solid rgba(0,0,0,.06);vertical-align:top'>
                $desc
            </td>
            <td style='padding:10px 12px;border-bottom:1px solid rgba(0,0,0,.06);
                       vertical-align:top;white-space:nowrap'>
                " . dynamicBadge($item['status'], $theme) . "
            </td>
            <td style='padding:10px 12px;border-bottom:1px solid rgba(0,0,0,.06);
                       vertical-align:top'>$history</td>
        </tr>";
    }

    $dividerColor = lightenHex($navColor, 0.75);

    $body = emailWrap($headerColor, $headerText, $titleText, $now, $theme, "
        <p style='margin:0 0 20px;font-size:14px;color:#555;line-height:1.6'>
            Ini adalah <strong>laporan final</strong> proses approval MDM.
            Email ini dikirimkan kepada seluruh pihak yang terlibat dalam proses ini.
        </p>

        " . sectionTitle('Informasi Request', $navColor) . "
        " . infoCard($theme, [
            'Request No'   => "<strong>$rno</strong>",
            'Tipe Request' => rtypeBadge($rtype, $theme),
            'Departemen'   => $dept,
            'Requester'    => $rname,
            'Tanggal'      => $rdate,
            'Status Final' => dynamicBadge($final_status, $theme),
            'Selesai Pada' => "<strong>$now</strong>",
        ]) . "

        " . sectionTitle('History Approval Per Step', $navColor) . "
        <p style='font-size:11px;color:#888;margin:0 0 10px;font-style:italic'>
            Format waktu: Hari, DD Mmm YYYY &nbsp; HH:MM:SS (sampai detik)
        </p>

        <!-- Wrapper scroll horizontal untuk mobile -->
        <div style='overflow-x:auto;-webkit-overflow-scrolling:touch'>
        <table width='100%' cellpadding='0' cellspacing='0'
               style='border-collapse:collapse;font-size:13px;
                      min-width:560px;border:1px solid rgba(0,0,0,.08);border-radius:8px;overflow:hidden'>
            <thead>
                <tr style='background:{$navColor};color:" . contrastColor($navColor) . "'>
                    <th style='padding:11px 12px;text-align:left;font-size:12px;
                               letter-spacing:.04em;font-weight:600'>Step</th>
                    <th style='padding:11px 12px;text-align:left;font-size:12px;
                               letter-spacing:.04em;font-weight:600'>Approver</th>
                    <th style='padding:11px 12px;text-align:left;font-size:12px;
                               letter-spacing:.04em;font-weight:600'>Status</th>
                    <th style='padding:11px 12px;text-align:left;font-size:12px;
                               letter-spacing:.04em;font-weight:600'>Catatan / Alasan Tolak</th>
                    <th style='padding:11px 12px;text-align:left;font-size:12px;
                               letter-spacing:.04em;font-weight:600;white-space:nowrap'>Waktu Aksi</th>
                </tr>
            </thead>
            <tbody>$historyRows</tbody>
        </table>
        </div>

        " . sectionTitle('Detail Item — Status & Riwayat Per Item', $navColor) . "
        <p style='font-size:11px;color:#888;margin:0 0 10px;font-style:italic'>
            Format tiap baris: Step &nbsp;|&nbsp; Status &nbsp;|&nbsp;
            Alasan (jika ditolak) &nbsp;|&nbsp; Waktu sampai detik
        </p>

        <div style='overflow-x:auto;-webkit-overflow-scrolling:touch'>
        <table width='100%' cellpadding='0' cellspacing='0'
               style='border-collapse:collapse;font-size:13px;
                      min-width:460px;border:1px solid rgba(0,0,0,.08);border-radius:8px;overflow:hidden'>
            <thead>
                <tr style='background:{$navColor};color:" . contrastColor($navColor) . "'>
                    <th style='padding:11px 12px;text-align:left;font-size:12px;
                               letter-spacing:.04em;font-weight:600'>Deskripsi Item</th>
                    <th style='padding:11px 12px;text-align:left;font-size:12px;
                               letter-spacing:.04em;font-weight:600;white-space:nowrap'>Status Akhir</th>
                    <th style='padding:11px 12px;text-align:left;font-size:12px;
                               letter-spacing:.04em;font-weight:600'>Riwayat Approval</th>
                </tr>
            </thead>
            <tbody>$itemRows</tbody>
        </table>
        </div>
    ");

    $list = [];
    foreach ($recipients as $email => $nama) {
        $list[] = ['email' => $email, 'nama' => $nama];
    }
    _sendToList($list, $subject, $body);
}


/*=============================================================
  TEMPLATE HELPERS
=============================================================*/

/**
 * Wrapper HTML email utama.
 * Sepenuhnya responsive: max-width 640px, padding menyesuaikan layar HP.
 */
function emailWrap(
    string $hColor,
    string $hTextColor,
    string $title,
    string $subtitle,
    array  $theme,
    string $content
): string {
    $navColor  = tc($theme, 'navbar', $hColor);
    $footerBg  = lightenHex($navColor, 0.94);
    $footerTxt = contrastColor($footerBg);
    $accentBar = lightenHex($hColor, 0.6);

    return "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>MDM Approval — $title</title>
    <!--[if mso]>
    <style>table { border-collapse: collapse; }</style>
    <![endif]-->
    <style>
        * { box-sizing: border-box; }
        body { margin:0; padding:0; background:#eef0f4;
               font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI',
                            Helvetica, Arial, sans-serif; }
        @media only screen and (max-width: 620px) {
            .email-outer  { padding: 10px !important; }
            .email-card   { border-radius: 8px !important; }
            .email-header { padding: 18px 18px !important; }
            .email-body   { padding: 18px 18px !important; }
            .email-footer { padding: 12px 18px !important; }
        }
    </style>
</head>
<body>
<div class='email-outer' style='padding:30px 16px;background:#eef0f4'>

    <!-- Card wrapper -->
    <div class='email-card' style='max-width:640px;margin:0 auto;background:#ffffff;
              border-radius:12px;overflow:hidden;
              box-shadow:0 4px 24px rgba(0,0,0,.10),0 1px 4px rgba(0,0,0,.06)'>

        <!-- Accent top bar -->
        <div style='height:4px;background:linear-gradient(90deg, {$hColor}, {$accentBar})'></div>

        <!-- Header -->
        <div class='email-header'
             style='background:{$hColor};color:{$hTextColor};padding:24px 32px'>
            <table width='100%' cellpadding='0' cellspacing='0'>
                <tr>
                    <td>
                        <p style='margin:0 0 2px;font-size:10px;letter-spacing:.12em;
                                  text-transform:uppercase;opacity:.75;font-weight:600'>
                            MDM Approval System
                        </p>
                        <h1 style='margin:0;font-size:20px;font-weight:700;
                                   line-height:1.3;letter-spacing:-.01em'>
                            $title
                        </h1>
                        <p style='margin:6px 0 0;font-size:12px;opacity:.75'>
                            $subtitle
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Body -->
        <div class='email-body' style='padding:28px 32px'>
            $content
        </div>

        <!-- Footer -->
        <div class='email-footer'
             style='background:{$footerBg};padding:14px 32px;
                    border-top:1px solid rgba(0,0,0,.07)'>
            <table width='100%' cellpadding='0' cellspacing='0'>
                <tr>
                    <td style='font-size:11px;color:{$footerTxt};opacity:.7'>
                        MDM Approval System &bull; " . date('Y') . "
                    </td>
                    <td align='right' style='font-size:11px;color:{$footerTxt};opacity:.7'>
                        Email otomatis. Jangan dibalas.
                    </td>
                </tr>
            </table>
        </div>

    </div>
</div>
</body></html>";
}

/** Section title dengan aksen warna dari theme */
function sectionTitle(string $text, string $color): string {
    $light = lightenHex($color, 0.92);
    return "
    <div style='display:flex;align-items:center;margin:28px 0 12px'>
        <div style='width:3px;height:18px;background:{$color};
                    border-radius:2px;margin-right:10px;flex-shrink:0'></div>
        <div style='font-weight:700;font-size:13px;letter-spacing:.04em;
                    text-transform:uppercase;color:{$color}'>
            $text
        </div>
    </div>";
}

/** Kartu info 2 kolom dengan background ringan dari theme */
function infoCard(array $theme, array $rows): string {
    $navColor = tc($theme, 'navbar', '#1a3c6b');
    $bg       = lightenHex($navColor, 0.96);
    $border   = lightenHex($navColor, 0.82);

    $html = "<div style='background:{$bg};border:1px solid {$border};
                         border-radius:8px;overflow:hidden'>
             <table width='100%' cellpadding='0' cellspacing='0'>";
    $i = 0;
    foreach ($rows as $label => $val) {
        $rowBg = ($i % 2 === 0) ? 'transparent' : 'rgba(0,0,0,.025)';
        $html .= "
        <tr style='background:{$rowBg}'>
            <td style='padding:9px 14px;width:148px;font-size:12px;font-weight:600;
                       color:#666;letter-spacing:.02em;vertical-align:top;
                       border-bottom:1px solid rgba(0,0,0,.05)'>"
                       . htmlspecialchars($label) . "</td>
            <td style='padding:9px 14px;font-size:13px;color:#222;vertical-align:top;
                       border-bottom:1px solid rgba(0,0,0,.05)'>$val</td>
        </tr>";
        $i++;
    }
    return $html . "</table></div>";
}

/**
 * Badge dinamis — warna diambil dari theme DB sesuai status.
 */
function dynamicBadge(string $status, array $theme): string {
    $map = [
        'APPROVED'         => ['btn_approve',    '#13848C'],
        'GENERAL_APPROVED' => ['btn_approve',    '#13848C'],
        'COMPLETED'        => ['btn_approve',    '#13848C'],
        'REJECTED'         => ['btn_reject',     '#B91F1F'],
        'PARTIAL'          => ['btn_edit',       '#E89646'],
        'PENDING'          => ['btn_primary',    '#2B7598'],
    ];

    $upper = strtoupper($status);
    [$themeKey, $fallbackHex] = $map[$upper] ?? ['btn_primary', '#888888'];

    $baseColor = tc($theme, $themeKey, $fallbackHex);
    $bgColor   = lightenHex($baseColor, 0.86);
    $textColor = $baseColor; // pakai warna asli untuk teks agar kontras di bg muda

    return "<span style='display:inline-block;padding:3px 12px;border-radius:20px;
                         font-size:11px;font-weight:700;letter-spacing:.04em;
                         background:{$bgColor};color:{$textColor};
                         border:1px solid " . lightenHex($baseColor, 0.6) . "'>"
           . htmlspecialchars($upper) . "</span>";
}

/**
 * Badge tipe request (MATERIAL / SERVICE / VENDOR) dengan warna dari theme.
 */
function rtypeBadge(string $rtype, array $theme): string {
    $map = [
        'MATERIAL' => 'logo_material',
        'SERVICE'  => 'logo_service',
        'VENDOR'   => 'logo_vendor',
    ];
    $key       = $map[strtoupper($rtype)] ?? 'request_type';
    $baseColor = tc($theme, $key, '#7D8863');
    $bgColor   = lightenHex($baseColor, 0.86);

    return "<span style='display:inline-block;padding:3px 12px;border-radius:20px;
                         font-size:11px;font-weight:700;letter-spacing:.04em;
                         background:{$bgColor};color:{$baseColor};
                         border:1px solid " . lightenHex($baseColor, 0.6) . "'>"
           . htmlspecialchars(strtoupper($rtype)) . "</span>";
}

/**
 * Format datetime: Senin, 23 Apr 2026  14:05:37
 */
function fmtDateTime(?string $dt): string {
    if (!$dt || $dt === '0000-00-00 00:00:00') return '—';
    static $days = [
        'Sunday'   => 'Minggu', 'Monday'    => 'Senin',
        'Tuesday'  => 'Selasa', 'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',  'Friday'    => 'Jumat',
        'Saturday' => 'Sabtu',
    ];
    $ts  = strtotime($dt);
    $day = $days[date('l', $ts)] ?? date('l', $ts);
    return $day . ', ' . date('d M Y', $ts) . '&nbsp;&nbsp;' . date('H:i:s', $ts);
}


/*=============================================================
  INTERNAL: Kirim email ke 1 orang
=============================================================*/
function _sendToOne(string $email, string $nama, string $subject, string $htmlBody): void {

    if (EMAIL_MODE === 'php_mail') {
        $headers = implode("\r\n", [
            "MIME-Version: 1.0",
            "Content-type: text/html; charset=UTF-8",
            "From: =?UTF-8?B?" . base64_encode(PHP_MAIL_FROM_NAME) . "?= <" . PHP_MAIL_FROM . ">",
            "Reply-To: " . PHP_MAIL_FROM,
        ]);
        if (!mail($email, '=?UTF-8?B?' . base64_encode($subject) . '?=', $htmlBody, $headers)) {
            error_log("[EmailHelper] mail() gagal ke: $email");
        }
        return;
    }

    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("[EmailHelper] PHPMailer tidak ditemukan. " .
                  "Taruh file dari folder src/ PHPMailer ke: " .
                  $_SERVER['DOCUMENT_ROOT'] . "/atri/PHPMailer/");
        return;
    }

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->CharSet   = 'UTF-8';
        $mail->SMTPAuth  = true;
        $mail->SMTPDebug = 2;

        if (EMAIL_MODE === 'mailtrap') {
            $mail->Host       = MAILTRAP_HOST;
            $mail->Username   = MAILTRAP_USER;
            $mail->Password   = MAILTRAP_PASS;
            $mail->Port       = MAILTRAP_PORT;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->setFrom(MAILTRAP_USER, 'MDM Approval System');
        } else {
            $mail->Host       = 'smtp.gmail.com';
            $mail->Username   = GMAIL_USER;
            $mail->Password   = GMAIL_PASS;
            $mail->Port       = 587;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->setFrom(GMAIL_USER, GMAIL_NAME);
        }

        $mail->addAddress($email, $nama);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(
            ['<br>', '<br/>', '<br />', '</p>', '</div>', '</tr>', '</td>'],
            "\n", $htmlBody
        ));
        $mail->send();

    } catch (Exception $e) {
        error_log("[EmailHelper] Gagal kirim ke $email: " . $e->getMessage());
    }
}

function _sendToList(array $recipients, string $subject, string $htmlBody): void {
    foreach ($recipients as $r) {
        if (empty($r['email'])) continue;
        _sendToOne($r['email'], $r['nama'] ?? '', $subject, $htmlBody);
        usleep(300000);
    }
}