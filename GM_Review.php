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
");
$header = mysqli_fetch_assoc($queryHeader);

if(!$header){
    die("Request tidak ditemukan.");
}

$request_type = $header['request_type'];

// Detail
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

// LOGIC TOMBOL APPROVE (INI YANG BENAR)
$sudah_general = true;

if($header['current_step'] == $role && $header['status'] == 'PENDING'){
    $sudah_general = false;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>GM Review Request</title>
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

    <!-- Detail Table — hanya item yang lolos (is_forwarded = 1) -->
    <div class="box">
        <b><?= $request_type == 'MATERIAL' ? 'Material Information' : 'Service Information' ?></b>
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
                </tr>
            </thead>
            <tbody>
            <?php if(count($rows) > 0): ?>
                <?php $no = 1; foreach($rows as $row): ?>
                <tr>
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
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:20px;color:#999">Tidak ada data</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Button Approve / Reject General -->
    <?php if(!$sudah_general): ?>
    <div class="btn-area">
        <form action="proses_review.php" method="POST" id="formAction">
            <input type="hidden" name="request_id" value="<?= $request_id ?>">

            <!-- Input reject reason, muncul hanya kalau klik Reject -->
            <div id="rejectBox" style="display:none; margin-bottom:10px">
                <textarea name="reject_reason" id="rejectReason" rows="3"
                    placeholder="Masukkan alasan reject..."
                    style="width:100%;padding:8px;border-radius:6px;border:1px solid #ccc;font-size:13px">
                </textarea>
            </div>

            <button type="button" onclick="submitApprove()" class="btn-approve">
                ✓ Approve
            </button>
            <button type="button" onclick="showReject()" id="btnReject" class="btn-reject">
                ✗ Reject
            </button>
            <button type="button" onclick="submitReject()" id="btnRejectConfirm" class="btn-reject"
                style="display:none">
                ✗ Confirm Reject
            </button>
            <button type="button" onclick="cancelReject()" id="btnCancelReject"
                style="display:none;padding:8px 16px;border-radius:6px;border:1px solid #ccc;cursor:pointer">
                Batal
            </button>

            <input type="hidden" name="action" id="hiddenAction" value="">
        </form>
    </div>
    <?php else: ?>
    <div class="btn-area">
       <?php if(!$sudah_general): ?>
    <!-- tombol approve reject -->
<?php else: ?>
    <div class="btn-area">
        <?php if($header['status'] == 'REJECTED'): ?>
            <span style="color:red;font-weight:bold">
                ✗ Request sudah di-Reject.
            </span>
        <?php elseif($header['status'] == 'COMPLETED'): ?>
            <span style="color:green;font-weight:bold">
                ✓ Request sudah selesai.
            </span>
        <?php else: ?>
            <span style="color:green;font-weight:bold">
                ✓ Request sudah diproses di step ini.
            </span>
        <?php endif; ?>
    </div>
<?php endif; ?>
    </div>
    <?php endif; ?>

    <div style="text-align:right;margin-top:20px">
        <a href="APDM.php" class="btn-back">BACK</a>
    </div>

</div>

<script>
function submitApprove(){
    document.getElementById('hiddenAction').value = 'general_approve';
    document.getElementById('formAction').submit();
}

function showReject(){
    document.getElementById('rejectBox').style.display   = 'block';
    document.getElementById('btnReject').style.display   = 'none';
    document.getElementById('btnRejectConfirm').style.display = 'inline-block';
    document.getElementById('btnCancelReject').style.display  = 'inline-block';
}

function cancelReject(){
    document.getElementById('rejectBox').style.display        = 'none';
    document.getElementById('btnReject').style.display        = 'inline-block';
    document.getElementById('btnRejectConfirm').style.display = 'none';
    document.getElementById('btnCancelReject').style.display  = 'none';
    document.getElementById('rejectReason').value = '';
}

function submitReject(){
    const reason = document.getElementById('rejectReason').value.trim();
    if(reason === ''){
        alert('Alasan reject wajib diisi.');
        return;
    }
    document.getElementById('hiddenAction').value = 'general_reject';
    document.getElementById('formAction').submit();
}
</script>

</body>
</html>