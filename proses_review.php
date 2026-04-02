<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$request_id  = (int)$_POST['request_id'];
$action      = $_POST['action'];
$user_id     = (int)$_SESSION['user_id'];
$role        = $_SESSION['role'];

// Ambil step sekarang
$q = mysqli_query($koneksi, "SELECT current_step, request_type FROM request_header WHERE id='$request_id'");
$d = mysqli_fetch_assoc($q);
$current_step = $d['current_step'];
$request_type = $d['request_type'];

// Urutan workflow
$nextStep = [
    "MDM Business Unit" => "Direct Manager",
    "Direct Manager"    => "BPO Local",
    "BPO Local"         => "MDM Global",
    "MDM Global"        => "COMPLETED"
];

// Redirect list per role
$listPage = [
    "MDM Business Unit" => "approval_list.php",
    "Direct Manager"    => "APDM.php",
    "BPO Local"         => "APBPO.php",
    "MDM Global"        => "MDM_Global_List.php"
];

$reviewPage = [
    "MDM Business Unit" => "reviwMDMBU.php",
    "Direct Manager"    => "GM_Review.php",
    "BPO Local"         => "BPO_Review.php",
    "MDM Global"        => "MDM_Global_Review.php"
];

$redirectList   = $listPage[$role]   ?? "approval_list.php";
$redirectReview = $reviewPage[$role] ?? "approval_list.php";

// Tabel detail sesuai request_type
$detail_table = ($request_type == 'MATERIAL') ? 'request_detail_material' : 'request_detail_service';
$col_detail   = ($request_type == 'MATERIAL') ? 'detail_material_id'      : 'detail_service_id';


// ================= APPROVE PER ITEM =================
if($action == "approve_item"){
    $detail_id = (int)$_POST['detail_id'];

    mysqli_query($koneksi, "
        UPDATE $detail_table
        SET status      = 'APPROVED',
            reviewed_by = '$user_id',
            reviewed_at = NOW()
        WHERE id = '$detail_id' AND request_id = '$request_id'
    ");

    mysqli_query($koneksi, "
        INSERT INTO approval (request_id, $col_detail, approver_id, role, step, status, created_at)
        VALUES ('$request_id', '$detail_id', '$user_id', '$role', '$current_step', 'APPROVED', NOW())
    ");

    // Cek apakah semua item sudah di-review
    $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) as rejected
        FROM $detail_table
        WHERE request_id = '$request_id'
    "));

    if($cek['approved'] == $cek['total']){
        // Semua approved
        $new_status = 'PARTIAL';
    } elseif($cek['rejected'] == $cek['total']){
        // Semua rejected
        $new_status = 'REJECTED';
    } else {
        // Sebagian
        $new_status = 'PARTIAL';
    }

    mysqli_query($koneksi, "
        UPDATE request_header
        SET status = '$new_status'
        WHERE id = '$request_id'
    ");

    header("Location: " . $redirectReview . "?id=" . $request_id);
    exit();
}


// ================= REJECT PER ITEM =================
elseif($action == "reject_item"){
    $detail_id = (int)$_POST['detail_id'];

    mysqli_query($koneksi, "
        UPDATE $detail_table
        SET status      = 'REJECTED',
            reviewed_by = '$user_id',
            reviewed_at = NOW()
        WHERE id = '$detail_id' AND request_id = '$request_id'
    ");

    mysqli_query($koneksi, "
        INSERT INTO approval (request_id, $col_detail, approver_id, role, step, status, created_at)
        VALUES ('$request_id', '$detail_id', '$user_id', '$role', '$current_step', 'REJECTED', NOW())
    ");

    // Cek apakah semua item sudah REJECTED
    $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) as rejected
        FROM $detail_table
        WHERE request_id = '$request_id'
    "));

    if($cek['total'] == $cek['rejected']){
        // Semua item rejected → header jadi REJECTED
        mysqli_query($koneksi, "
            UPDATE request_header
            SET status = 'REJECTED'
            WHERE id = '$request_id'
        ");
    } else {
        // Masih ada yang lain → PARTIAL
        mysqli_query($koneksi, "
            UPDATE request_header
            SET status = 'PARTIAL'
            WHERE id = '$request_id'
        ");
    }

    header("Location: " . $redirectReview . "?id=" . $request_id);
    exit();
}


// ================= GENERAL APPROVE =================
elseif($action == "general_approve"){

    // Hanya item APPROVED yang di-forward ke step berikutnya
    mysqli_query($koneksi, "
        UPDATE $detail_table
        SET is_forwarded = 1
        WHERE request_id = '$request_id' AND status = 'APPROVED'
    ");

    // Item REJECTED tetap is_forwarded = 0, tidak ikut lanjut
    // (tidak perlu query tambahan karena default is_forwarded = 0)

    // Insert approval general
    mysqli_query($koneksi, "
        INSERT INTO approval (request_id, approver_id, role, step, status, created_at)
        VALUES ('$request_id', '$user_id', '$role', '$current_step', 'GENERAL_APPROVED', NOW())
    ");

    // Pindah ke step berikutnya
    if(isset($nextStep[$current_step])){
        $next = $nextStep[$current_step];

        if($next == "COMPLETED"){
            mysqli_query($koneksi, "
                UPDATE request_header
                SET status       = 'COMPLETED',
                    current_step = 'COMPLETED'
                WHERE id = '$request_id'
            ");
        } else {
            mysqli_query($koneksi, "
                UPDATE request_header
                SET current_step = '$next',
                    status       = 'PENDING'
                WHERE id = '$request_id'
            ");
        }
    }

    header("Location: " . $redirectList);
    exit();
}


// ================= GENERAL REJECT =================
elseif($action == 'general_reject'){

    // Semua item di-reject sekaligus (termasuk yang belum di-review)
    mysqli_query($koneksi, "
        UPDATE $detail_table
        SET status      = 'REJECTED',
            reviewed_by = '$user_id',
            reviewed_at = NOW()
        WHERE request_id = '$request_id'
    ");

    // Insert approval general reject
    mysqli_query($koneksi, "
        INSERT INTO approval (request_id, approver_id, role, step, status, created_at)
        VALUES ('$request_id', '$user_id', '$role', '$current_step', 'REJECTED', NOW())
    ");

    // Update header jadi REJECTED
    mysqli_query($koneksi, "
        UPDATE request_header
        SET status = 'REJECTED'
        WHERE id = '$request_id'
    ");

    header("Location: " . $redirectList);
    exit();
}
?>