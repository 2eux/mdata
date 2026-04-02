<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$request_id = (int)$_GET['id'];

// Theme
$queryTheme = mysqli_query($koneksi, "SELECT fungsi, warna FROM theme WHERE company_id='$company_id'");
$theme = [];
while($row = mysqli_fetch_assoc($queryTheme)){
    $theme[$row['fungsi']] = $row['warna'];
}

// Request Header
$queryHeader = mysqli_query($koneksi, "
    SELECT rh.*, u.nama as requestor_name, u.email
    FROM request_header rh
    JOIN users u ON rh.requestor_id = u.id
    WHERE rh.id = $request_id
");
$header = mysqli_fetch_assoc($queryHeader);

if(!$header){
    die("Request tidak ditemukan.");
}

$request_type = $header['request_type']; // MATERIAL / SERVICE

// Request Detail — sesuai request_type
if($request_type == 'MATERIAL'){
    $queryDetail = mysqli_query($koneksi, "
        SELECT * FROM request_detail_material WHERE request_id = $request_id
    ");
} else {
    $queryDetail = mysqli_query($koneksi, "
        SELECT * FROM request_detail_service WHERE request_id = $request_id
    ");
}

// Approval steps — sesuaikan key dengan step baru
$steps = [
    'Request Submitted',
    'MDM Business Unit',
    'Direct Manager',
    'BPO Local',
    'MDM Global'
];

$stepsLabel = [
    'Request Submitted' => 'Request Submitted',
    'MDM Business Unit'            => 'MDM Business Unit',
    'Direct Manager'    => 'Direct Manager',
    'BPO Local'               => 'BPO Local',
    'MDM Global'        => 'MDM Global'
];

$queryApproval = mysqli_query($koneksi, "
    SELECT * FROM approval WHERE request_id = $request_id ORDER BY id ASC
");
$approvals = [];
while($row = mysqli_fetch_assoc($queryApproval)){
    $approvals[$row['step']] = $row;  // lama: role → baru: step
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Request</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/view_request.css">
    <style>
    :root {
        --navbar: <?php echo $theme['navbar']; ?>;
        --btn-primary: <?php echo $theme['btn_primary']; ?>;
        --table-header: <?php echo $theme['table_header']; ?>;
        --btn-approve: <?php echo $theme['btn_approve']; ?>;
        --btn-reject: <?php echo $theme['btn_reject']; ?>;
        --status-pending: <?php echo $theme['status_pending']; ?>;
        --status-active: <?php echo $theme['status_active']; ?>;
    }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">

    <h2><?= $request_type == 'MATERIAL' ? 'MATERIAL REQUEST' : 'SERVICE REQUEST' ?></h2>
    <div class="progress-label">Request Progress</div>

    <!-- PROGRESS BAR -->
    <div class="progress-wrapper">
        <?php
        $currentStep = $header['current_step'];
        $currentIndex = array_search($currentStep, $steps);

        foreach($steps as $i => $step):
            if($i == 0){
                $icon = 'done';
            } elseif($i < $currentIndex){
                $icon = 'done';
            } elseif($i == $currentIndex){
                $icon = 'pending';
            } else {
                $icon = 'inactive';
            }

            // Cek rejected di approval berdasarkan step
            if(isset($approvals[$step]) && $approvals[$step]['status'] == 'REJECTED'){
                $icon = 'rejected';
            }

            // Cek general approved
            if(isset($approvals[$step]) && $approvals[$step]['status'] == 'GENERAL_APPROVED'){
                $icon = 'done';
            }
        ?>
        <div class="step-item">
            <div class="step-circle <?= $icon ?>">
                <?php if($icon == 'done'): ?>
                    <span>&#10003;</span>
                <?php elseif($icon == 'pending'): ?>
                    <span>!</span>
                <?php elseif($icon == 'rejected'): ?>
                    <span>&#10005;</span>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
            </div>
            <div class="step-label"><?= $stepsLabel[$step] ?></div>
        </div>
        <?php if($i < count($steps)-1): ?>
            <div class="step-line"></div>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- REQUESTOR INFORMATION -->
    <div class="card-view">
        <div class="card-title-view">Requestor Information</div>
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Request ID</span>
                <span class="info-sep">:</span>
                <span class="info-val"><?= htmlspecialchars($header['request_no']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Request Type</span>
                <span class="info-sep">:</span>
                <span class="info-val"><?= htmlspecialchars($header['request_type']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Departemen</span>
                <span class="info-sep">:</span>
                <span class="info-val"><?= htmlspecialchars($header['departemen'] ?? '-') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Requester Name</span>
                <span class="info-sep">:</span>
                <span class="info-val"><?= htmlspecialchars($header['requestor_name']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Request Date</span>
                <span class="info-sep">:</span>
                <span class="info-val"><?= date('d M Y', strtotime($header['request_date'])) ?></span>
            </div>
        </div>

        <!-- STATUS -->
        <div class="status-box">
            <div class="status-title">Status</div>
            <?php
            $s = $header['status'];
            if($s == 'APPROVED')        $badge = 'badge-approved';
            elseif($s == 'REJECTED')    $badge = 'badge-rejected';
             elseif($s == 'COMPLETED')    $badge = 'badge-approved';
            elseif($s == 'PENDING')     $badge = 'badge-pending';
            elseif($s == 'PARTIAL')     $badge = 'badge-partial';
            else                        $badge = 'badge-draft';
            ?>
            <div class="status-badge <?= $badge ?>">
                <?php if($s == 'PENDING' || $s == 'PARTIAL'): ?>⚠️<?php endif; ?>
                <?= htmlspecialchars($s) ?>
            </div>
        </div>
    </div>

    <!-- DATA DETAIL -->
    <div class="card-view">
        <div class="card-title-view">
            <?= $request_type == 'MATERIAL' ? 'DATA MATERIAL MASTER' : 'DATA SERVICE MASTER' ?>
        </div>
        <div class="table-box-view">
            <table class="table-view">
                <thead>
                    <tr>
                        <th>No</th>
                        <?php if($request_type == 'MATERIAL'): ?>
                            <th>Material Number</th>
                            <th>Material Description</th>
                            <th>UoM</th>
                            <th>Material Group</th>
                            <th>Ext. Material Group</th>
                            <th>Material Type</th>
                            <th>Status Item</th>
                        <?php else: ?>
                            <th>Service Number</th>
                            <th>Service Description</th>
                            <th>UoM</th>
                            <th>Service Group</th>
                            <th>Service Category</th>
                            <th>Status Item</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if($queryDetail && mysqli_num_rows($queryDetail) > 0):
                        $no = 1;
                        while($det = mysqli_fetch_assoc($queryDetail)): ?>
                    <tr>
                        <td style="text-align:center"><?= $no++ ?></td>
                        <?php if($request_type == 'MATERIAL'): ?>
                            <td><?= htmlspecialchars($det['material_number'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($det['description'] ?? '-') ?></td>
                            <td style="text-align:center"><?= htmlspecialchars($det['uom'] ?? '-') ?></td>
                            <td style="text-align:center"><?= htmlspecialchars($det['material_group'] ?? '-') ?></td>
                            <td style="text-align:center"><?= htmlspecialchars($det['ext_material_group'] ?? '-') ?></td>
                            <td style="text-align:center"><?= htmlspecialchars($det['material_type'] ?? '-') ?></td>
                        <?php else: ?>
                            <td><?= htmlspecialchars($det['service_number'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($det['description'] ?? '-') ?></td>
                            <td style="text-align:center"><?= htmlspecialchars($det['uom'] ?? '-') ?></td>
                            <td style="text-align:center"><?= htmlspecialchars($det['service_group'] ?? '-') ?></td>
                            <td style="text-align:center"><?= htmlspecialchars($det['service_category'] ?? '-') ?></td>
                        <?php endif; ?>
                        <td style="text-align:center">
                            <?php
                            $itemStatus = $det['status'] ?? 'PENDING';
                            if($itemStatus == 'APPROVED')       echo '<span class="badge-approved">Approved</span>';
                            elseif($itemStatus == 'REJECTED')   echo '<span class="badge-rejected">Rejected</span>';
                            else                                echo '<span class="badge-pending">Pending</span>';
                            ?>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:20px;color:#999">Tidak ada data</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- BACK BUTTON -->
    <div style="text-align:right; margin-top:20px;">
        <a href="request_list.php" class="btn-back">BACK</a>
    </div>

</div>
</body>
</html>