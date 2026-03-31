<?php
session_start();
include 'koneksi.php';

$request_id = $_POST['request_id'];
$action = $_POST['action'];
$user_id = $_SESSION['user_id'];

// Ambil step sekarang
$q = mysqli_query($koneksi, "SELECT current_step FROM request_header WHERE id='$request_id'");
$d = mysqli_fetch_assoc($q);
$current_step = $d['current_step'];

// Urutan workflow
$nextStep = [
    "MDM Business Unit" => "Direct Manager",
    "Direct Manager" => "BPO Approval",
    "BPO Approval" => "MDM GLOBAL",
    "MDM GLOBAL" => "Completed"
];

if($action == "approve"){

    mysqli_query($koneksi, "
        UPDATE approval 
        SET status='Approved', approver_id='$user_id', tanggal=NOW()
        WHERE request_id='$request_id' AND role='$current_step'
    ");

    if(isset($nextStep[$current_step])){
        $next = $nextStep[$current_step];

        mysqli_query($koneksi, "
            UPDATE request_header 
            SET current_step='$next', status='Pending'
            WHERE id='$request_id'
        ");
    }
}

elseif($action == "reject"){
    mysqli_query($koneksi, "
        UPDATE approval 
        SET status='Rejected', approver_id='$user_id', tanggal=NOW()
        WHERE request_id='$request_id' AND role='$current_step'
    ");

    mysqli_query($koneksi, "
        UPDATE request_header 
        SET status='Rejected'
        WHERE id='$request_id'
    ");
}

header("Location: view_request.php?id=".$request_id);
exit();
?>