<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

if(!isset($_GET['id'])){
    echo "Request tidak ditemukan";
    exit();
}

$company_id = $_SESSION['company_id'];
$request_id = (int)$_GET['id'];
$user_id    = (int)$_SESSION['user_id'];
$role       = $_SESSION['role'];

// Theme
$queryTheme = mysqli_query($koneksi, "SELECT fungsi, warna FROM theme WHERE company_id='$company_id'");
$theme = [];
while($row = mysqli_fetch_assoc($queryTheme)){
    $theme[$row['fungsi']] = $row['warna'];
}

// Header
$queryHeader = mysqli_query($koneksi, "
    SELECT rh.*, u.nama
    FROM request_header rh
    JOIN users u ON rh.requestor_id = u.id
    WHERE rh.id = $request_id
    AND rh.company_id = '$company_id'
");
$header = mysqli_fetch_assoc($queryHeader);

if(!$header){
    die("Request tidak ditemukan.");
}

$request_type = $header['request_type'];

// Cek sudah general approve/reject di step ini
$cekSudahAksi = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT status FROM approval
    WHERE request_id = '$request_id'
    AND step = 'MDM Global'
    AND status IN ('GENERAL_APPROVED', 'REJECTED')
    AND detail_material_id IS NULL
    AND detail_service_id IS NULL
    ORDER BY id DESC LIMIT 1
"));
$sudah_general = !empty($cekSudahAksi);
$aksi_general  = $cekSudahAksi['status'] ?? '';

// Detail — hanya yang is_forwarded = 1 (lolos dari step sebelumnya)
if($request_type == 'MATERIAL'){
    $queryDetail = mysqli_query($koneksi, "
        SELECT * FROM request_detail_material
        WHERE request_id = $request_id AND is_forwarded = 1
    ");
} else {
    $queryDetail = mysqli_query($koneksi, "
        SELECT * FROM request_detail_service
        WHERE request_id = $request_id AND is_forwarded = 1
    ");
}

$rows = [];
while($row = mysqli_fetch_assoc($queryDetail)){
    $rows[] = $row;
}

// Hitung item status
$total          = count($rows);
$done           = array_filter($rows, fn($r) => in_array($r['status'], ['APPROVED','REJECTED']));
$approved_items = array_filter($rows, fn($r) => $r['status'] == 'APPROVED');
$all_reviewed   = count($done) == $total && $total > 0;
$has_approved   = count($approved_items) > 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Review Request MDM Global</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/reviewMDM.css">
    <style>
    :root {
        --navbar: <?= $theme['navbar']; ?>;
        --btn-approve: <?= $theme['btn_approve']; ?>;
        --btn-reject: <?= $theme['btn_reject']; ?>;
        --btn-edit: <?= $theme['btn_edit']; ?>;
        --table-header: <?= $theme['table_header']; ?>;
    }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="wrapper">

    <h2>Review Request - <?= htmlspecialchars($role) ?></h2>

    <!-- Requestor Info -->
    <div class="box">
        <b>Requestor Information</b><br><br>
        Request No : <?= htmlspecialchars($header['request_no']) ?> &nbsp;&nbsp;&nbsp;
        Request Type : <?= htmlspecialchars($header['request_type']) ?><br>
        Departemen : <?= htmlspecialchars($header['departemen']) ?> &nbsp;&nbsp;&nbsp;
        Requester Name : <?= htmlspecialchars($header['nama']) ?><br>
        Request Date : <?= date('d M Y', strtotime($header['request_date'])) ?> &nbsp;&nbsp;&nbsp;
        Status : <strong><?= htmlspecialchars($header['status']) ?></strong>
    </div>

    <!-- Detail Table -->
    <div class="box">
        <b><?= $request_type == 'MATERIAL' ? 'Material Information' : 'Service Information' ?></b>
        <?php if($total == 0): ?>
            <p style="color:#999;margin-top:10px">Tidak ada item yang diteruskan ke step ini.</p>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <?php if($request_type == 'MATERIAL'): ?>
                        <th>Material Number</th>
                        <th>Description</th>
                        <th>UoM</th>
                        <th>Material Group</th>
                        <th>Ext. Material Group</th>
                        <th>Material Type</th>
                    <?php else: ?>
                        <th>Service Number</th>
                        <th>Description</th>
                        <th>UoM</th>
                        <th>Service Group</th>
                        <th>Service Category</th>
                    <?php endif; ?>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php $no = 1; foreach($rows as $row): ?>
            <tr id="row-<?= $row['id'] ?>">
                <td><?= $no++ ?></td>
                <?php if($request_type == 'MATERIAL'): ?>
                    <td><?= htmlspecialchars($row['material_number'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= htmlspecialchars($row['uom']) ?></td>
                    <td><?= htmlspecialchars($row['material_group']) ?></td>
                    <td><?= htmlspecialchars($row['ext_material_group']) ?></td>
                    <td><?= htmlspecialchars($row['material_type']) ?></td>
                <?php else: ?>
                    <td><?= htmlspecialchars($row['service_number'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= htmlspecialchars($row['uom']) ?></td>
                    <td><?= htmlspecialchars($row['service_group']) ?></td>
                    <td><?= htmlspecialchars($row['service_category']) ?></td>
                <?php endif; ?>
                <td>
                    <?php
                    $st = $row['status'];
                    if($st == 'APPROVED')       echo '<span class="status-approved">Approved</span>';
                    elseif($st == 'REJECTED')   echo '<span class="status-reject">Rejected</span>';
                    else                        echo '<span class="status-pending">Pending</span>';
                    ?>
                </td>
                <td>
                    <?php if(!$sudah_general): ?>
                        <?php if($st == 'PENDING'): ?>
                            <form action="proses_review.php" method="POST" style="display:inline">
                                <input type="hidden" name="request_id" value="<?= $request_id ?>">
                                <input type="hidden" name="detail_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="action" value="approve_item" class="btn-approve">✓</button>
                            </form>
                            <form action="proses_review.php" method="POST" style="display:inline">
                                <input type="hidden" name="request_id" value="<?= $request_id ?>">
                                <input type="hidden" name="detail_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="action" value="reject_item" class="btn-reject">✗</button>
                            </form>
                        <?php else: ?>
                            <span style="color:#999;font-size:12px">Locked</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color:#999;font-size:12px">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Duplicate Check Only -->
    <div class="validation-container">
        <div class="validation-box" style="width:100%">
            <b>Duplicate Check</b><br><br>
            <?php
            $duplicate_found = false;
            foreach($rows as $row){
                if($request_type == 'MATERIAL' && !empty($row['material_number'])){
                    $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "
                        SELECT material_number, description, material_type FROM material
                        WHERE material_number = '" . mysqli_real_escape_string($koneksi, $row['material_number']) . "'
                    "));
                    if($cek){
                        $duplicate_found = true;
                        echo '<div class="duplicate-warning">';
                        echo '⚠ Possible Duplicate Found<br><br>';
                        echo 'Material Number : ' . htmlspecialchars($cek['material_number']) . '<br>';
                        echo 'Material Type : ' . htmlspecialchars($cek['material_type']) . '<br>';
                        echo 'Description : ' . htmlspecialchars($cek['description']);
                        echo '</div>';
                    }
                } elseif($request_type == 'SERVICE' && !empty($row['service_number'])){
                    $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "
                        SELECT service_number, description FROM service
                        WHERE service_number = '" . mysqli_real_escape_string($koneksi, $row['service_number']) . "'
                    "));
                    if($cek){
                        $duplicate_found = true;
                        echo '<div class="duplicate-warning">';
                        echo '⚠ Possible Duplicate Found<br><br>';
                        echo 'Service Number : ' . htmlspecialchars($cek['service_number']) . '<br>';
                        echo 'Description : ' . htmlspecialchars($cek['description']);
                        echo '</div>';
                    }
                }
            }
            if(!$duplicate_found){
                echo '<div class="duplicate-ok">✔ No duplicate found</div>';
            }
            ?>
        </div>
    </div>

    <!-- Button General Approve / Reject -->
    <?php if(!$sudah_general): ?>
        <div class="btn-area">
            <form action="proses_review.php" method="POST">
                <input type="hidden" name="request_id" value="<?= $request_id ?>">

                <?php if($all_reviewed && $has_approved): ?>
                    <button type="submit" name="action" value="general_approve"
                        id="btnGeneralApprove"
                        class="btn-approve">
                        ✓ General Approve (<?= count($approved_items) ?> item lanjut)
                    </button>
                <?php else: ?>
                    <button type="button" class="btn-approve" disabled style="opacity:0.5"
                        title="Review semua item terlebih dahulu">
                        ✓ General Approve
                    </button>
                <?php endif; ?>

                <button type="submit" name="action" value="general_reject" class="btn-reject">
                    ✗ Reject All
                </button>
            </form>
        </div>

    <?php else: ?>
        <div class="btn-area">
            <?php if($aksi_general == 'GENERAL_APPROVED'): ?>
                <span style="color:green;font-weight:bold">
                    ✓ Request sudah di-General Approve, diteruskan ke step berikutnya.
                </span>

            <?php elseif($aksi_general == 'REJECTED'): ?>
                <span style="color:red;font-weight:bold">
                    ✗ Request ini sudah ditolak dan tidak dapat diubah lagi.
                </span>

            <?php elseif($header['status'] == 'COMPLETED'): ?>
                <span style="color:green;font-weight:bold">
                    ✓ Request sudah selesai (Completed).
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div style="text-align:right;margin-top:20px">
        <a href="APGLOBAL.php" class="btn-back">BACK</a>
    </div>

</div>

</body>
</html>