<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$role = $_SESSION['role'];

// Ambil theme warna
$queryTheme = mysqli_query($koneksi, "SELECT fungsi, warna FROM theme WHERE company_id='$company_id'");
$theme = [];
while($row = mysqli_fetch_assoc($queryTheme)){
    $theme[$row['fungsi']] = $row['warna'];
}

// ================= SUMMARY =================

// Pending (yang harus dia approve sekarang)
$pending = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(*) as total 
    FROM request_header
    WHERE current_step = '$role' 
    AND status = 'Pending'
"))['total'];

// Approved (yang sudah dia approve)
$approved = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(*) as total 
    FROM approval
    WHERE role = '$role' 
    AND status = 'Approved'
"))['total'];

// Rejected (yang dia reject)
$rejected = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(*) as total 
    FROM approval
    WHERE role = '$role' 
    AND status = 'Rejected'
"))['total'];


// ================= DATA LIST =================
$query = mysqli_query($koneksi, "
    SELECT rh.*, u.nama,
    (SELECT COUNT(*) FROM request_detail WHERE request_id = rh.id) as total_material,
    a.status as approval_status
    FROM request_header rh
    JOIN users u ON rh.requestor_id = u.id
    LEFT JOIN approval a 
        ON rh.id = a.request_id 
        AND a.role = '$role'
    WHERE rh.current_step = '$role'
       OR (a.role = '$role' AND a.status IN ('Approved','Rejected'))
    ORDER BY rh.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Review Request</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/approval.css">

    <style>
    :root {
        --navbar: <?php echo $theme['navbar']; ?>;
        --btn-primary: <?php echo $theme['btn_primary']; ?>;
        --table-header: <?php echo $theme['table_header']; ?>;
        --status-pending: <?php echo $theme['status_pending']; ?>;
        --status-active: <?php echo $theme['status_active']; ?>;
        --btn-approve: <?php echo $theme['btn_approve']; ?>;
        --btn-reject: <?php echo $theme['btn_reject']; ?>;
    }
    </style>
</head>

<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h2>Review Request - <?= $role ?></h2>

    <div class="summary-box">
        <div class="summary-card">
            <div>Pending Approval</div>
            <h1><?= $pending ?></h1>
        </div>

        <div class="summary-card">
            <div>Approved by You</div>
            <h1><?= $approved ?></h1>
        </div>

        <div class="summary-card">
            <div>Rejected by You</div>
            <h1><?= $rejected ?></h1>
        </div>
    </div>

    <div class="table-box">
        <table>
            <tr>
                <th>Request ID</th>
                <th>Request Type</th>
                <th>Requester</th>
                <th>Total Material</th>
                <th>Date</th>
                <th>Your Approval Status</th>
                <th>Action</th>
            </tr>

            <?php while($row = mysqli_fetch_assoc($query)){ ?>
            <tr>
                <td><?= $row['request_no']; ?></td>
                <td>Material</td>
                <td><?= $row['nama']; ?></td>
                <td><?= $row['total_material']; ?></td>
                <td><?= $row['request_date']; ?></td>
                <td>
                    <?php if($row['approval_status'] == "Approved"){ ?>
                        <span class="status-approved">Approved</span>
                    <?php } elseif($row['approval_status'] == "Rejected"){ ?>
                        <span class="status-reject">Rejected</span>
                    <?php } else { ?>
                        <span class="status-pending">Pending</span>
                    <?php } ?>
                </td>
                <td>
                    <a href="view_request.php?id=<?= $row['id']; ?>" class="btn-review">Review</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>

</body>
</html>