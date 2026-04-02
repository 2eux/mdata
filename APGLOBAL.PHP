<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$role       = $_SESSION['role'];

$queryTheme = mysqli_query($koneksi, "SELECT fungsi, warna FROM theme WHERE company_id='$company_id'");
$theme = [];
while($row = mysqli_fetch_assoc($queryTheme)){
    $theme[$row['fungsi']] = $row['warna'];
}

// ================= SUMMARY =================

$pending = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(*) as total 
    FROM request_header
    WHERE current_step = '$role' 
    AND status IN ('PENDING', 'PARTIAL')
    AND company_id = '$company_id'
"))['total'];

$approved = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(DISTINCT a.request_id) as total 
    FROM approval a
    JOIN request_header rh ON a.request_id = rh.id
    WHERE a.step             = '$role' 
    AND a.status             = 'GENERAL_APPROVED'
    AND a.detail_material_id IS NULL
    AND a.detail_service_id  IS NULL
    AND rh.company_id        = '$company_id'
"))['total'];

$rejected = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(DISTINCT a.request_id) as total 
    FROM approval a
    JOIN request_header rh ON a.request_id = rh.id
    WHERE a.step             = '$role' 
    AND a.status             = 'REJECTED'
    AND a.detail_material_id IS NULL
    AND a.detail_service_id  IS NULL
    AND rh.company_id        = '$company_id'
"))['total'];


// ================= DATA LIST =================
$query = mysqli_query($koneksi, "
    SELECT 
        rh.*,
        u.nama,
        (
            SELECT COUNT(*) FROM request_detail_material 
            WHERE request_id = rh.id AND is_forwarded = 1
        ) as total_material,
        (
            SELECT COUNT(*) FROM request_detail_service 
            WHERE request_id = rh.id AND is_forwarded = 1
        ) as total_service,
        a.status as approval_status
    FROM request_header rh
    JOIN users u ON rh.requestor_id = u.id
    LEFT JOIN approval a 
        ON rh.id                 = a.request_id 
        AND a.step               = '$role'
        AND a.detail_material_id IS NULL
        AND a.detail_service_id  IS NULL
    WHERE rh.company_id = '$company_id'
    AND (
        rh.current_step = '$role'
        OR (
            a.step             = '$role' 
            AND a.status       IN ('GENERAL_APPROVED', 'REJECTED')
            AND a.detail_material_id IS NULL
            AND a.detail_service_id  IS NULL
        )
    )
    GROUP BY rh.id
    ORDER BY rh.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>MDM Global Review Request</title>
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
    <h2>Review Request - <?= htmlspecialchars($role) ?></h2>

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
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Request Type</th>
                    <th>Requester</th>
                    <th>Total Item</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if(mysqli_num_rows($query) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($query)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['request_no']) ?></td>
                    <td><?= htmlspecialchars($row['request_type']) ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td style="text-align:center">
                        <?php
                        if($row['request_type'] == 'MATERIAL'){
                            echo $row['total_material'] . ' Material';
                        } else {
                            echo $row['total_service'] . ' Service';
                        }
                        ?>
                    </td>
                    <td><?= date('d M Y', strtotime($row['request_date'])) ?></td>
                    <td>
                        <?php
                        $approval_status = $row['approval_status'];
                        $header_status   = $row['status'];

                        if($approval_status == 'GENERAL_APPROVED'){
                            echo '<span class="status-approved">General Approved</span>';
                        } elseif($approval_status == 'REJECTED' || $header_status == 'REJECTED'){
                            echo '<span class="status-reject">Rejected</span>';
                        } elseif($header_status == 'COMPLETED'){
                            echo '<span class="status-approved">Completed</span>';
                        } elseif($header_status == 'PARTIAL'){
                            echo '<span class="status-partial">Partial</span>';
                        } else {
                            echo '<span class="status-pending">Pending</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <a href="MDM_Global_Review.php?id=<?= $row['id'] ?>" class="btn-review">Review</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:30px;color:#999">
                        Tidak ada request untuk direview.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>