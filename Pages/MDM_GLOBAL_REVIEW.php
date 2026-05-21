<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/config/koneksi.php';

/** @var mysqli $koneksi */

require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/db_helper.php';


if(!isset($_SESSION['user_id'])){
    header("Location: /atri/Pages/index.php");
    exit();
}

if (empty($_SESSION['submit_token'])) {
    $_SESSION['submit_token'] = bin2hex(random_bytes(32));
}

if(!isset($_GET['id'])){
    echo "Request tidak ditemukan";
    exit();
}

$company_id = $_SESSION['company_id'];
$request_id = (int)$_GET['id'];
$user_id    = (int)$_SESSION['user_id'];
$role       = $_SESSION['role'];

// THEME
$queryTheme = executeOrFail($koneksi, "SELECT fungsi, warna FROM theme WHERE company_id='$company_id'");
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
    'Direct Manager'    => 'Direct Manager',
    'BPO Local'         => 'BPO Local',
    'MDM Global'        => 'MDM Global'
];

// APPROVAL DATA
$qApproval = executeOrFail($koneksi, "
    SELECT *
    FROM approval
    WHERE request_id = '$request_id'
    AND detail_material_id IS NULL
    AND detail_service_id IS NULL
");

$approvals = [];
while($a = mysqli_fetch_assoc($qApproval)){
    $approvals[$a['step']] = $a;
}

$request_type = $header['request_type'];

// ✅ FIX: ganti is_forwarded=1 → status='APPROVED'
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
        AND status = 'APPROVED'
    ");
}

$rows = [];
while($row = mysqli_fetch_assoc($queryDetail)){
    $rows[] = $row;
}

// ✅ FIX: cek sudah aksi dengan approved_by IS NOT NULL agar tidak salah baca
$cekSudahAksi = mysqli_fetch_assoc(executeOrFail($koneksi, "
    SELECT status FROM approval
    WHERE request_id = '$request_id'
    AND step = 'MDM Global'
    AND approved_by IS NOT NULL
    AND status IN ('GENERAL_APPROVED', 'REJECTED')
    AND detail_material_id IS NULL
    AND detail_service_id IS NULL
    ORDER BY id DESC LIMIT 1
"));
$sudah_general = !empty($cekSudahAksi);
$aksi_general  = $cekSudahAksi['status'] ?? '';

// Override jika COMPLETED
if($header['status'] == 'COMPLETED'){
    $sudah_general = true;
    $aksi_general  = 'GENERAL_APPROVED';
} elseif($header['current_step'] == $role && in_array($header['status'], ['PENDING', 'PARTIAL'])){
    $sudah_general = false;
    $aksi_general  = '';
} else {
    $sudah_general = true;
}

// Hitung item
$total          = count($rows);
$done           = array_filter($rows, fn($r) => in_array($r['status'], ['APPROVED','REJECTED']));
$approved_items = array_filter($rows, fn($r) => $r['status'] == 'APPROVED');
$all_reviewed   = count($done) == $total && $total > 0;
$has_approved   = count($approved_items) > 0;

// Cek duplikat
$duplicate_material_ids = [];
if(!$sudah_general){
    foreach($rows as $r){
        if($request_type == 'MATERIAL' && !empty($r['material_number'])){
            $cekDup = mysqli_fetch_assoc(executeOrFail($koneksi, "
                SELECT material_number FROM material
                WHERE material_number = '" . mysqli_real_escape_string($koneksi, $r['material_number']) . "'
            "));
            if($cekDup) $duplicate_material_ids[] = $r['id'];
        }
    }
}

$flash = $_GET['msg'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Review Request - MDM Global</title>
    <link rel="stylesheet" href="/atri/css/global.css">
    <link rel="stylesheet" href="/atri/css/navbar.css">
    <link rel="stylesheet" href="/atri/css/reviewMDM.css">
    <link rel="stylesheet" href="/atri/css/view_request.css">
    <style>
    :root {
        --navbar:         <?= $theme['navbar']; ?>;
        --btn-approve:    <?= $theme['btn_approve']; ?>;
        --btn-reject:     <?= $theme['btn_reject']; ?>;
        --btn-edit:       <?= $theme['btn_edit']; ?>;
        --table-header:   <?= $theme['table_header']; ?>;
        --status-pending: <?= $theme['status_pending']; ?>;
        --logo-material:  <?= $theme['logo_material']; ?>;
    }
    </style>
</head>
<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/navbar.php'; ?>

<div class="container">

    <h2><?= $request_type == 'MATERIAL' ? 'MATERIAL REQUEST' : 'SERVICE REQUEST' ?></h2>
    <div class="progress-label">Request Progress</div>

    <?php if($flash == 'locked'): ?>
        <div class="alert-error">
            ⚠ Request ini sudah <strong>COMPLETED</strong> dan tidak dapat diubah lagi.
        </div>
    <?php endif; ?>

    <!-- PROGRESS -->
    <div class="progress-wrapper">
    <?php
    $currentStep  = $header['current_step'];
    $currentIndex = array_search($currentStep, $steps);
    if($currentIndex === false) $currentIndex = 0;

    foreach($steps as $i => $step):
        if($i == 0)                  $icon = 'done';
        elseif($i < $currentIndex)   $icon = 'done';
        elseif($i == $currentIndex)  $icon = 'pending';
        else                         $icon = 'inactive';

        $approvalData = $approvals[$step] ?? null;
        if($approvalData){
            if($approvalData['status'] == 'REJECTED')                                    $icon = 'rejected';
            elseif(in_array($approvalData['status'], ['APPROVED','GENERAL_APPROVED']))   $icon = 'done';
        }

        $userName   = '-';
        $approvedAt = '-';
        if($approvalData && !empty($approvalData['approved_by'])){
            $u = mysqli_fetch_assoc(executeOrFail($koneksi,
                "SELECT nama FROM users WHERE id=" . (int)$approvalData['approved_by']
            ));
            if($u) $userName = $u['nama'];
        }
        if($approvalData && !empty($approvalData['approved_at'])){
            $approvedAt = date('d M Y H:i', strtotime($approvalData['approved_at']));
        }
    ?>
        <div class="step-item">
            <div class="step-circle <?= $icon ?>">
                <?php if($icon == 'done'):     ?>✓
                <?php elseif($icon == 'pending'):  ?>!
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
                <span class="info-val"><?= $header['request_no'] ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Request Type</span>
                <span class="info-sep">:</span>
                <span class="info-val"><?= $header['request_type'] ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Departemen</span>
                <span class="info-sep">:</span>
                <span class="info-val"><?= $header['departemen'] ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Requester Name</span>
                <span class="info-sep">:</span>
                <span class="info-val"><?= $header['nama'] ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Request Date</span>
                <span class="info-sep">:</span>
                <span class="info-val"><?= date('d M Y', strtotime($header['request_date'])) ?></span>
            </div>
        </div>
        <div class="status-box">
            <div class="status-title">Status</div>
            <div class="status-badge badge-<?= strtolower($header['status']) ?>">
                <?= $header['status'] ?>
            </div>
        </div>
    </div>

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
                <th>Status</th>
                <?php if(!$sudah_general): ?><th>Action</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php $no = 1; foreach($rows as $row): ?>
        <?php $is_dup = in_array($row['id'], $duplicate_material_ids); ?>
        <tr id="row-<?= $row['id'] ?>" class="<?= $is_dup ? 'row-duplicate' : '' ?>">
            <td><?= $no++ ?></td>
            <?php if($request_type == 'MATERIAL'): ?>
                <td style="text-align:center;">
                    <?php if(!empty($row['image_url'])): ?>
                        <img src="<?= htmlspecialchars($row['image_url']) ?>"
                             style="width:60px;height:60px;object-fit:contain;border:1px solid #ddd;border-radius:6px;">
                    <?php else: ?>
                        <span style="color:#999;font-size:12px">No Image</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['material_number'] ?? '-') ?>
                    <?php if($is_dup): ?>
                        <span style="color:orange;font-size:11px;display:block">⚠ Duplicate</span>
                    <?php endif; ?>
                </td>
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
                if($st == 'APPROVED')     echo '<span class="status-approved">Approved</span>';
                elseif($st == 'REJECTED') echo '<span class="status-reject">Rejected</span>';
                else                      echo '<span class="status-pending">Pending</span>';
                ?>
            </td>
            <?php if(!$sudah_general): ?>
            <td>
                <?php if($st == 'APPROVED'): ?>
                    <!-- ✅ FIX: reject per item pakai modal agar remarks bisa diisi -->
                    <button type="button" class="btn-reject"
                        onclick="openRejectItem(<?= $row['id'] ?>, '<?= htmlspecialchars($row['description'] ?? '', ENT_QUOTES) ?>')">
                        ✗ Reject
                    </button>
                <?php else: ?>
                    <span style="color:#999;font-size:12px">-</span>
                <?php endif; ?>
            </td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Duplicate Check -->
<div class="validation-container">
    <div class="validation-box" style="width:100%">
        <b>Duplicate Check</b><br><br>
        <?php if($sudah_general): ?>
            <div class="duplicate-ok">✔ Sudah diproses, tidak perlu pengecekan ulang.</div>
        <?php else: ?>
            <?php
            $duplicate_found = false;
            foreach($rows as $row){
                if($request_type == 'MATERIAL' && !empty($row['material_number'])){
                    $cek = mysqli_fetch_assoc(executeOrFail($koneksi, "
                        SELECT material_number, description, material_type FROM material
                        WHERE material_number = '" . mysqli_real_escape_string($koneksi, $row['material_number']) . "'
                    "));
                    if($cek){
                        $duplicate_found = true;
                        echo '<div class="duplicate-warning">';
                        echo '⚠ Possible Duplicate Found<br><br>';
                        echo 'Material Number : ' . htmlspecialchars($cek['material_number']) . '<br>';
                        echo 'Material Type   : ' . htmlspecialchars($cek['material_type'])   . '<br>';
                        echo 'Description     : ' . htmlspecialchars($cek['description']);
                        echo '</div>';
                    }
                } elseif($request_type == 'SERVICE' && !empty($row['service_number'])){
                    $cek = mysqli_fetch_assoc(executeOrFail($koneksi, "
                        SELECT service_number, description FROM service
                        WHERE service_number = '" . mysqli_real_escape_string($koneksi, $row['service_number']) . "'
                    "));
                    if($cek){
                        $duplicate_found = true;
                        echo '<div class="duplicate-warning">';
                        echo '⚠ Possible Duplicate Found<br><br>';
                        echo 'Service Number : ' . htmlspecialchars($cek['service_number']) . '<br>';
                        echo 'Description    : ' . htmlspecialchars($cek['description']);
                        echo '</div>';
                    }
                }
            }
            if(!$duplicate_found){
                echo '<div class="duplicate-ok">✔ No duplicate found</div>';
            }
            ?>
        <?php endif; ?>
    </div>
</div>

<!-- ✅ Button Area -->
<div class="btn-area">

<?php if(!$sudah_general): ?>

<form action="/atri/action/proses_create_material.php"
      method="POST"
      id="formCreate">

    <input type="hidden"
           name="request_id"
           value="<?= $request_id ?>">

      <input
                    type="hidden"
                    name="submit_token"
                    value="<?= $_SESSION['submit_token']; ?>"
                >


    <input type="hidden"
           name="action"
           value="<?= $request_type == 'SERVICE'
                ? 'create_service'
                : 'create_material' ?>">

    <?php if($all_reviewed && $has_approved): ?>

        <button type="submit" class="btn-create">
            ＋ Create (<?= count($approved_items) ?> item)
        </button>

    <?php else: ?>

        <button type="button"
                class="btn-create"
                disabled
                title="Review semua item terlebih dahulu">
            ＋ Create
        </button>

    <?php endif; ?>

</form>

<button type="button"
        class="btn-reject"
        onclick="openRejectAll()">
    ✗ Reject All
</button>

<?php else: ?>

    <?php if($aksi_general == 'GENERAL_APPROVED' || $header['status'] == 'COMPLETED'): ?>

        <span style="color:green;font-weight:bold">
            ✓ Request selesai.
        </span>

    <?php elseif($aksi_general == 'REJECTED'): ?>

        <span style="color:red;font-weight:bold">
            ✗ Request ditolak.
        </span>

    <?php endif; ?>

<?php endif; ?>

</div>
<!-- MODAL: Reject Per Item -->
<div class="modal-overlay" id="modalRejectItem">
    <div class="modal-box">
        <div class="modal-title">✗ Reject Item</div>
        <div class="modal-subtitle" id="modalItemSubtitle">Masukkan alasan penolakan item ini.</div>
        <form action="/atri/action/proses_review.php" method="POST" id="formRejectItem">
            <input type="hidden" name="request_id" value="<?= $request_id ?>">
            <input type="hidden" name="detail_id"  id="rejectItemId" value="">
            <input type="hidden" name="action"     value="reject_item">
            <textarea class="modal-textarea" name="remarks" id="rejectItemNote"
                placeholder="Contoh: Material tidak sesuai spesifikasi..."
                maxlength="500"></textarea>
            <div class="modal-error" id="errorRejectItem" style="display:none">Alasan reject wajib diisi.</div>
            <div class="modal-footer">
                <button type="button" class="modal-btn-cancel" onclick="closeModal('modalRejectItem')">Batal</button>
                <button type="button" class="modal-btn-confirm" onclick="submitRejectItem()">✗ Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Reject All -->
<div class="modal-overlay" id="modalRejectAll">
    <div class="modal-box">
        <div class="modal-title">✗ Reject All Items</div>
        <div class="modal-subtitle">
            Semua item akan ditolak dan request tidak dapat dilanjutkan.<br>
            Masukkan alasan penolakan.
        </div>
        <form action="/atri/action/proses_review.php" method="POST" id="formRejectAll">
            <input type="hidden" name="request_id" value="<?= $request_id ?>">
            <input type="hidden" name="action"     value="general_reject">
            <textarea class="modal-textarea" name="remarks" id="rejectAllNote"
                placeholder="Contoh: Request tidak sesuai prosedur..."
                maxlength="500"></textarea>
            <div class="modal-error" id="errorRejectAll" style="display:none">Alasan reject wajib diisi.</div>
            <div class="modal-footer">
                <button type="button" class="modal-btn-cancel" onclick="closeModal('modalRejectAll')">Batal</button>
                <button type="button" class="modal-btn-confirm" onclick="submitRejectAll()">✗ Confirm Reject All</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e){
        if(e.target === this) closeModal(this.id);
    });
});

function openRejectItem(itemId, description){
    document.getElementById('rejectItemId').value   = itemId;
    document.getElementById('rejectItemNote').value = '';
    document.getElementById('errorRejectItem').style.display = 'none';
    document.getElementById('modalItemSubtitle').textContent = 'Item: ' + description;
    openModal('modalRejectItem');
    setTimeout(() => document.getElementById('rejectItemNote').focus(), 100);
}

function submitRejectItem(){
    const remarks = document.getElementById('rejectItemNote').value.trim();
    if(!remarks){
        document.getElementById('errorRejectItem').style.display = 'block';
        return;
    }
    document.getElementById('formRejectItem').submit();
}

function openRejectAll(){
    document.getElementById('rejectAllNote').value = '';
    document.getElementById('errorRejectAll').style.display = 'none';
    openModal('modalRejectAll');
    setTimeout(() => document.getElementById('rejectAllNote').focus(), 100);
}

function submitRejectAll(){
    const remarks = document.getElementById('rejectAllNote').value.trim();
    if(!remarks){
        document.getElementById('errorRejectAll').style.display = 'block';
        return;
    }
    document.getElementById('formRejectAll').submit();
}
</script>

</body>
</html>