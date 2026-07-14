<?php

error_reporting(E_ALL & ~E_DEPRECATED);

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /atri/Pages/index.php");
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/config/koneksi.php';
/** @var mysqli $koneksi */
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/db_helper.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/queue_helper.php';

// ================= CSRF TOKEN CHECK =================
$action = $_POST['action'] ?? '';

$csrfRequired = [
    'general_approve',
    'general_reject'
];

if (in_array($action, $csrfRequired)) {

    $token_post = $_POST['submit_token'] ?? '';

    if (
        empty($token_post) ||
        !hash_equals(
            $_SESSION['submit_token'] ?? '',
            $token_post
        )
    ) {

        header("Location: /atri/Pages/index.php");
        exit();
    }
}

$request_id = (int)$_POST['request_id'];
$action     = $_POST['action'];
$user_id    = (int)$_SESSION['user_id'];
$role       = $_SESSION['role'];

// Sanitasi remarks
$remarks = isset($_POST['remarks'])
    ? mysqli_real_escape_string($koneksi, $_POST['remarks'])
    : '';

// Ambil current step & request type
$q = executeOrFail($koneksi, "
    SELECT current_step, request_type
    FROM request_header
    WHERE id = '$request_id'
");

$d = mysqli_fetch_assoc($q);

$current_step = $d['current_step'];


// ============================================================
// UPDATE HEADER STATUS
// ============================================================
function updateHeaderStatus($koneksi, $request_id, $detail_table)
{
    $cek = mysqli_fetch_assoc(executeOrFail($koneksi, "
        SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) AS approved,
            SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) AS rejected
        FROM $detail_table
        WHERE request_id = '$request_id'
    "));

    if ($cek['total'] == 0) return;

    if ($cek['rejected'] == $cek['total']) {

        executeOrFail($koneksi, "
            UPDATE request_header
            SET status = 'REJECTED',
                current_step = 'REJECTED'
            WHERE id = '$request_id'
        ");

    } elseif ($cek['approved'] == $cek['total']) {

        executeOrFail($koneksi, "
            UPDATE request_header
            SET status = 'APPROVED'
            WHERE id = '$request_id'
        ");

    } else {

        executeOrFail($koneksi, "
            UPDATE request_header
            SET status = 'PARTIAL'
            WHERE id = '$request_id'
        ");
    }
}


mysqli_begin_transaction($koneksi);

try {

$allowedAction = [
    'approve_item',
    'reject_item',
    'general_approve',
    'general_reject'
];

if (!in_array($action, $allowedAction)) {
    throw new Exception("Invalid action");
}



if ($current_step !== $role) {
    throw new Exception("Unauthorized action");
}

$request_type = $d['request_type'];

// Workflow
$nextStep = [
    "MDM Business Unit" => "Direct Manager",
    "Direct Manager"    => "BPO Local",
    "BPO Local"         => "MDM Global",
    "MDM Global"        => "COMPLETED"
];

// Redirect page
$listPage = [
    "MDM Business Unit" => "/atri/Pages/approval_list.php",
    "Direct Manager"    => "/atri/Pages/APBPO.php",
    "BPO Local"         => "/atri/Pages/APBPO.php",
    "MDM Global"        => "/atri/Pages/APBPO.php"
];

$reviewPage = [
    "MDM Business Unit" => "/atri/Pages/reviwMDMBU.php",
    "Direct Manager"    => "/atri/Pages/GM_Review.php",
    "BPO Local"         => "/atri/Pages/BPO_Review.php",
    "MDM Global"        => "/atri/Pages/MDM_Global_Review.php"
];

$redirectList   = $listPage[$role]   ?? "/atri/Pages/approval_list.php";
$redirectReview = $reviewPage[$role] ?? "/atri/Pages/approval_list.php";

// Detail table
$detail_table = ($request_type == 'MATERIAL')
    ? 'request_detail_material'
    : 'request_detail_service';

$col_detail = ($request_type == 'MATERIAL')
    ? 'detail_material_id'
    : 'detail_service_id';

// Lock check
$cek_lock = mysqli_fetch_assoc(executeOrFail($koneksi, "
    SELECT status
    FROM request_header
    WHERE id = '$request_id'
"));

if (in_array($cek_lock['status'], ['REJECTED', 'COMPLETED'])) {
    throw new Exception("Request sudah dikunci!");
}


// ============================================================
// APPROVE ITEM
// ============================================================
if ($action == "approve_item") {

    $detail_id = (int)$_POST['detail_id'];

    executeOrFail($koneksi, "
        UPDATE $detail_table
        SET status = 'APPROVED',
            reviewed_by = '$user_id',
            reviewed_at = NOW()
        WHERE id = '$detail_id'
        AND request_id = '$request_id'
    ");

    executeOrFail($koneksi, "
        INSERT INTO approval
        (
            request_id,
            $col_detail,
            approved_by,
            role,
            step,
            status,
            approved_at
        )
        VALUES
        (
            '$request_id',
            '$detail_id',
            '$user_id',
            '$role',
            '$current_step',
            'APPROVED',
            NOW()
        )
    ");

    updateHeaderStatus($koneksi, $request_id, $detail_table);

    mysqli_commit($koneksi);

    header("Location: " . $redirectReview . "?id=" . $request_id);
    exit();
}

// ============================================================
// REJECT ITEM
// ============================================================
elseif ($action == "reject_item") {

    $detail_id = (int)$_POST['detail_id'];

    if (empty($remarks)) {
        throw new Exception("Alasan reject wajib diisi!");
    }

    executeOrFail($koneksi, "
        UPDATE $detail_table
        SET status = 'REJECTED',
            reviewed_by = '$user_id',
            reviewed_at = NOW()
        WHERE id = '$detail_id'
        AND request_id = '$request_id'
    ");

    executeOrFail($koneksi, "
        INSERT INTO approval
        (
            request_id,
            $col_detail,
            approved_by,
            role,
            step,
            status,
            note,
            approved_at
        )
        VALUES
        (
            '$request_id',
            '$detail_id',
            '$user_id',
            '$role',
            '$current_step',
            'REJECTED',
            '$remarks',
            NOW()
        )
    ");

    updateHeaderStatus($koneksi, $request_id, $detail_table);

    mysqli_commit($koneksi);

    header("Location: " . $redirectReview . "?id=" . $request_id);
    exit();
}

// ============================================================
// GENERAL APPROVE
// ============================================================
elseif ($action == "general_approve") {

    $cek = mysqli_fetch_assoc(executeOrFail($koneksi, "
        SELECT COUNT(*) AS approved
        FROM $detail_table
        WHERE request_id = '$request_id'
        AND status = 'APPROVED'
    "));

    if ($cek['approved'] == 0) {
        throw new Exception("Tidak ada item yang bisa diteruskan!");
    }

    executeOrFail($koneksi, "
        INSERT INTO approval
        (
            request_id,
            approved_by,
            role,
            step,
            status,
            approved_at
        )
        VALUES
        (
            '$request_id',
            '$user_id',
            '$role',
            '$current_step',
            'GENERAL_APPROVED',
            NOW()
        )
    ");

    $next = $nextStep[$current_step] ?? null;

    if ($next === "COMPLETED") {

        executeOrFail($koneksi, "
            UPDATE request_header
            SET status = 'COMPLETED',
                current_step = 'COMPLETED'
            WHERE id = '$request_id'
        ");

    } elseif ($next !== null) {

        executeOrFail($koneksi, "
            UPDATE request_header
            SET current_step = '$next',
                status = 'PENDING'
            WHERE id = '$request_id'
        ");
    }

    // ========================================================
    // RABBITMQ
    // ========================================================

if ($next !== null && $next !== "COMPLETED") {

        $data = [
            "type"       => "NEXT_STEP",
            "request_id" => $request_id,
            "next_step"  => $next
        ];

        try {

            publishQueue('email_queue', $data);

        } catch (\Throwable $e) {

            error_log($e->getMessage());
        }
}

    mysqli_commit($koneksi);

header("Location: " . $redirectList);
exit();

}

elseif ($action == "general_reject") {

    if (empty($remarks)) {
        throw new Exception("Alasan reject wajib diisi!");
    }

    executeOrFail($koneksi, "
        UPDATE $detail_table
        SET status = 'REJECTED',
            reviewed_by = '$user_id',
            reviewed_at = NOW()
        WHERE request_id = '$request_id'
    ");

    executeOrFail($koneksi, "
        INSERT INTO approval
        (
            request_id,
            approved_by,
            role,
            step,
            status,
            note,
            approved_at
        )
        VALUES
        (
            '$request_id',
            '$user_id',
            '$role',
            '$current_step',
            'REJECTED',
            '$remarks',
            NOW()
        )
    ");

    executeOrFail($koneksi, "
        UPDATE request_header
        SET status = 'REJECTED',
            current_step = 'REJECTED'
        WHERE id = '$request_id'
    ");

    // ========================================================
    // RABBITMQ
    // ========================================================
        $data = [
            "type"       => "REJECTED",
            "request_id" => $request_id,
            "role"       => $role,
            "remarks"    => $remarks
        ];

        try {

            publishQueue('email_queue', $data);

        } catch (\Throwable $e) {

            error_log($e->getMessage());
        }

    mysqli_commit($koneksi);

    header("Location: " . $redirectList);

    exit();

}

} catch (\Throwable $e) {

    mysqli_rollback($koneksi);

    error_log($e->getMessage());

    $_SESSION['error_message'] = "Terjadi kesalahan sistem";

    header("Location: " . $redirectList);
    exit();
}

?>