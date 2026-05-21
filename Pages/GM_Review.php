<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/config/koneksi.php';

/** @var mysqli $koneksi */

require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/db_helper.php';

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

if (empty($_SESSION['submit_token'])) {

    $_SESSION['submit_token'] = bin2hex(random_bytes(32));
}

if(!isset($_GET['id'])){
    $_SESSION['error_message'] = "Request tidak ditemukan";
    header("Location: /atri/Pages/approval_list.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$request_id = (int)$_GET['id'];
$user_id    = (int)$_SESSION['user_id'];
$role       = $_SESSION['role'];

// THEME
$queryTheme = executeOrFail($koneksi, "SELECT fungsi, warna FROM theme WHERE company_id=$company_id");
$theme = [];
while($row = mysqli_fetch_assoc($queryTheme)){
    $theme[$row['fungsi']] = $row['warna'];
}

// HEADER
$queryHeader = executeOrFail($koneksi, "
    SELECT rh.*, u.nama
    FROM request_header rh
    JOIN users u ON rh.requestor_id = u.id
    WHERE rh.id = $request_id
");
$header = mysqli_fetch_assoc($queryHeader);

if(!$header){
    die("Request tidak ditemukan.");
}

// STEP FLOW
$steps = [
    'Request Submitted',
    'MDM Business Unit',
    'Direct Manager',
    'BPO Local',
    'MDM Global'
];

$stepsLabel = [
    'Request Submitted' => 'Request Submitted',
    'MDM Business Unit' => 'MDM Business Unit',
    'Direct Manager' => 'Direct Manager',
    'BPO Local' => 'BPO Local',
    'MDM Global' => 'MDM Global'
];

// APPROVAL DATA
$qApproval = executeOrFail($koneksi, "
    SELECT *
    FROM approval
    WHERE request_id = $request_id
    AND detail_material_id IS NULL
    AND detail_service_id IS NULL
");

$approvals = [];
while($a = mysqli_fetch_assoc($qApproval)){
    $approvals[$a['step']] = $a;
}

// Cache approver names
$approverNames = [];

foreach ($approvals as $step => $a) {

    if (!empty($a['approved_by'])) {

        $uid = (int) $a['approved_by'];

        if (!isset($approverNames[$uid])) {

            $u = mysqli_fetch_assoc(
                executeOrFail(
                    $koneksi,
                    "SELECT nama FROM users WHERE id = $uid"
                )
            );

            $approverNames[$uid] = $u['nama'] ?? '-';
        }
    }
}

$request_type = $header['request_type'];

// DETAIL
if($request_type == 'MATERIAL'){
$queryDetail = executeOrFail($koneksi, "
    SELECT * FROM request_detail_material
    WHERE request_id = $request_id
    AND status = 'APPROVED'
");
} else {
    $queryDetail = executeOrFail($koneksi, "
        SELECT * FROM request_detail_service
        WHERE request_id = $request_id
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
    <title>Review Request</title>
        <link rel="stylesheet" href="/atri/css/global.css">
    <link rel="stylesheet" href="/atri/css/navbar.css">
    <link rel="stylesheet" href="/atri/css/reviewMDM.css">
    <link rel="stylesheet" href="/atri/css/view_request.css">

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

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/navbar.php'; ?>

<div class="container">

    <h2><?= $request_type == 'MATERIAL' ? 'MATERIAL REQUEST' : 'SERVICE REQUEST' ?></h2>
    <div class="progress-label">Request Progress</div>

    <!-- PROGRESS -->
    <div class="progress-wrapper">
    <?php
    $currentStep = $header['current_step'];
    $currentIndex = array_search($currentStep, $steps);
    if($currentIndex === false) $currentIndex = 0;

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

        $approvalData = $approvals[$step] ?? null;

        if($approvalData){
            if($approvalData['status'] == 'REJECTED'){
                $icon = 'rejected';
            } elseif(in_array($approvalData['status'], ['APPROVED','GENERAL_APPROVED'])){
                $icon = 'done';
            }
        }

        $userName = '-';
        $approvedAt = '-';

        if($approvalData && !empty($approvalData['approved_by'])){
            $uid = (int) $approvalData['approved_by'];
            $userName = $approverNames[$uid] ?? '-';
        }

        if($approvalData && !empty($approvalData['approved_at'])){
            $approvedAt = date('d M Y H:i', strtotime($approvalData['approved_at']));
        }
    ?>
        <div class="step-item">
            <div class="step-circle <?= $icon ?>">
                <?php if($icon == 'done'): ?>✓
                <?php elseif($icon == 'pending'): ?>!
                <?php elseif($icon == 'rejected'): ?>✗
                <?php endif; ?>
            </div>

            <div class="step-label"><?= $stepsLabel[$step] ?></div>

            <?php if($approvalData && $approvalData['status'] != 'PENDING'): ?>
                <div class="step-info">
                    <small><?= $userName ?><br><?= $approvedAt ?></small>
                </div>
            <?php endif; ?>
        </div>

        <?php if($i < count($steps)-1): ?>
            <div class="step-line"></div>
        <?php endif; ?>

    <?php endforeach; ?>
    </div>

    <!-- REQUEST INFO -->
    <div class="card-view">
        <div class="card-title-view">Requestor Information</div>
        <div class="info-grid">

            <div class="info-row">
                <span class="info-label">Request ID</span>
                <span class="info-sep">:</span>
                <span class="info-val"><?=htmlspecialchars($header['request_no']) ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Request Type</span>
                <span class="info-sep">:</span>
                <span class="info-val"><?=htmlspecialchars($header['request_type']) ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Departemen</span>
                <span class="info-sep">:</span>
                <span class="info-val"><?=htmlspecialchars($header['departemen']) ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Requester Name</span>
                <span class="info-sep">:</span>
                <span class="info-val"><?=htmlspecialchars($header['nama']) ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Request Date</span>
                <span class="info-sep">:</span>
                <span class="info-val"><?= date('d M Y', strtotime($header['request_date'])) ?></span>
            </div>

        </div>

        <div class="status-box">
            <div class="status-title">Status</div>
            <div class="status-badge badge-pending">
               <?= htmlspecialchars($header['status']) ?>
            </div>
        </div>
    </div>

</div>
    <!-- Detail Table — hanya item yang lolos (is_forwarded = 1) -->
    <div class="box">
        <b><?= $request_type == 'MATERIAL' ? 'Material Information' : 'Service Information' ?></b>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <?php if($request_type == 'MATERIAL'): ?>
                        <th>Image</th>
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
                        <td style="text-align:center;">
                            <?php if(!empty($row['image_url'])): ?>
                                <img src="<?= htmlspecialchars($row['image_url']) ?>"
                                    style="width:60px; height:60px; object-fit:contain; border:1px solid #ddd; border-radius:6px;">
                            <?php else: ?>
                                <span style="color:#999;font-size:12px">No Image</span>
                            <?php endif; ?>
                        </td>
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
        <form action="/atri/action/proses_review.php" method="POST" id="formAction">
            <input type="hidden" name="request_id" value="<?= $request_id ?>">
            <input
                    type="hidden"
                    name="submit_token"
                    value="<?= $_SESSION['submit_token']; ?>"
                >

            <!-- Input reject reason, muncul hanya kalau klik Reject -->
            <div id="rejectBox" style="display:none; margin-bottom:10px">
                <textarea name="remarks" id="rejectReason" rows="3"
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

    <div style="text-align:right;margin-top:20px">
        <a href="/atri/Pages/APBPO.php" class="btn-back">BACK</a>
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
