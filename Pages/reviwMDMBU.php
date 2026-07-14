<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/config/koneksi.php';

/** @var mysqli $koneksi */
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/db_helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /atri/Pages/index.php");
    exit();
}

if (empty($_SESSION['submit_token'])) {

    $_SESSION['submit_token'] = bin2hex(random_bytes(32));
}

if (!isset($_GET['id'])) {

    $_SESSION['error_message'] = "Request tidak ditemukan";

    header("Location: /atri/Pages/approval_list.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$request_id = (int) $_GET['id'];


// ================= THEME =================
$queryTheme = executeOrFail($koneksi, "SELECT fungsi, warna FROM theme WHERE company_id = '$company_id'");
$theme = [];
while ($row = mysqli_fetch_assoc($queryTheme)) {
    $theme[$row['fungsi']] = $row['warna'];
}

// ================= HEADER =================
$queryHeader = executeOrFail($koneksi, "
    SELECT rh.*, u.nama
    FROM request_header rh
    JOIN users u ON rh.requestor_id = u.id
    WHERE rh.id = $request_id
");
$header = mysqli_fetch_assoc($queryHeader);

if (!$header) {

    $_SESSION['error_message'] = "Request tidak ditemukan";
    header("Location: /atri/Pages/approval_list.php");
    exit();
}

// ================= STEP FLOW =================
$steps = [
    'Request Submitted',
    'MDM Business Unit',
    'Direct Manager',
    'BPO Local',
    'MDM Global',
];

$stepsLabel = [
    'Request Submitted' => 'Request Submitted',
    'MDM Business Unit' => 'MDM Business Unit',
    'Direct Manager'    => 'Direct Manager',
    'BPO Local'         => 'BPO Local',
    'MDM Global'        => 'MDM Global',
];

// ================= APPROVAL DATA =================
$qApproval = executeOrFail($koneksi, "
    SELECT *
    FROM approval
    WHERE request_id = '$request_id'
    AND detail_material_id IS NULL
    AND detail_service_id IS NULL
    ORDER BY id ASC
");

$approvals = [];
while ($a = mysqli_fetch_assoc($qApproval)) {
    $approvals[$a['step']] = $a;
}

// Cache nama approver agar tidak query dalam loop
$approverNames = [];
foreach ($approvals as $step => $a) {
    if (!empty($a['approved_by'])) {
        $uid = (int) $a['approved_by'];
        if (!isset($approverNames[$uid])) {
            $u = mysqli_fetch_assoc(executeOrFail($koneksi, "SELECT nama FROM users WHERE id = $uid"));
            $approverNames[$uid] = $u['nama'] ?? '-';
        }
    }
}

$request_type = $header['request_type'];

// Cek sudah general approve/reject di step MDM Business Unit
$cekSudahAksi = mysqli_fetch_assoc(executeOrFail($koneksi, "
    SELECT status FROM approval
    WHERE request_id = '$request_id'
    AND step = 'MDM Business Unit'
    AND approved_by IS NOT NULL
    AND status IN ('GENERAL_APPROVED', 'REJECTED')
    AND detail_material_id IS NULL
    AND detail_service_id IS NULL
    ORDER BY id DESC LIMIT 1
"));
$sudah_general = !empty($cekSudahAksi);
$aksi_general  = $cekSudahAksi['status'] ?? '';

// ================= DETAIL =================
if ($request_type === 'MATERIAL') {
    $queryDetail = executeOrFail($koneksi, "
        SELECT * FROM request_detail_material WHERE request_id = $request_id
    ");
} else {
    $queryDetail = executeOrFail($koneksi, "
        SELECT * FROM request_detail_service WHERE request_id = $request_id
    ");
}

$rows = [];
while ($row = mysqli_fetch_assoc($queryDetail)) {
    $rows[] = $row;
}

// Hitung status item
$total          = count($rows);
$done           = array_filter($rows, fn($r) => in_array($r['status'], ['APPROVED', 'REJECTED']));
$approved_items = array_filter($rows, fn($r) => $r['status'] === 'APPROVED');
$all_reviewed   = count($done) === $total;
$has_approved   = count($approved_items) > 0;
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
            --navbar:         <?= $theme['navbar'] ?>;
            --btn-approve:    <?= $theme['btn_approve'] ?>;
            --btn-reject:     <?= $theme['btn_reject'] ?>;
            --btn-edit:       <?= $theme['btn_edit'] ?>;
            --table-header:   <?= $theme['table_header'] ?>;
            --btn-primary:    <?= $theme['btn_primary'] ?>;
            --status-pending: <?= $theme['status_pending'] ?>;
            --status-active:  <?= $theme['status_active'] ?>;
        }
    </style>
</head>
<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/navbar.php'; ?>

<div class="container">

    <h2><?= $request_type === 'MATERIAL' ? 'MATERIAL REQUEST' : 'SERVICE REQUEST' ?></h2>
    <div class="progress-label">Request Progress</div>

    <!-- ================= PROGRESS ================= -->
    <div class="progress-wrapper">
    <?php
    $currentStep  = $header['current_step'];
    $currentIndex = array_search($currentStep, $steps);
    if ($currentIndex === false) $currentIndex = 0;

    foreach ($steps as $i => $step):
        if ($i === 0)                $icon = 'done';
        elseif ($i < $currentIndex)  $icon = 'done';
        elseif ($i === $currentIndex) $icon = 'pending';
        else                          $icon = 'inactive';

        $approvalData = $approvals[$step] ?? null;
        if ($approvalData) {
            if ($approvalData['status'] === 'REJECTED')                                     $icon = 'rejected';
            elseif (in_array($approvalData['status'], ['APPROVED', 'GENERAL_APPROVED']))    $icon = 'done';
        }

        $userName   = '-';
        $approvedAt = '-';
        if ($approvalData && !empty($approvalData['approved_by'])) {
            $uid      = (int) $approvalData['approved_by'];
            $userName = $approverNames[$uid] ?? '-';
        }
        if ($approvalData && !empty($approvalData['approved_at'])) {
            $approvedAt = date('d M Y H:i', strtotime($approvalData['approved_at']));
        }
    ?>
        <div class="step-item">
            <div class="step-circle <?= $icon ?>">
                <?php if ($icon === 'done'):     ?>✓
                <?php elseif ($icon === 'pending'):  ?>!
                <?php elseif ($icon === 'rejected'): ?>✗
                <?php endif; ?>
            </div>
            <div class="step-label"><?= $stepsLabel[$step] ?></div>
            <?php if ($approvalData && $approvalData['status'] !== 'PENDING'): ?>
                <div class="step-info">
                    <small><?= $userName ?><br><?= $approvedAt ?></small>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($i < count($steps) - 1): ?>
            <div class="step-line"></div>
        <?php endif; ?>
    <?php endforeach; ?>
    </div>

    <!-- ================= REQUEST INFO ================= -->
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
                <span class="info-val"><?= htmlspecialchars($header['departemen']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Requester Name</span>
                <span class="info-sep">:</span>
                <span class="info-val"><?= htmlspecialchars($header['nama']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Request Date</span>
                <span class="info-sep">:</span>
                <span class="info-val"><?= date('d M Y', strtotime($header['request_date'])) ?></span>
            </div>
        </div>
        <div class="status-box">
            <div class="status-title">Status</div>
            <div class="status-badge badge-pending"><?= htmlspecialchars($header['status']) ?></div>
        </div>
    </div>

</div>

<!-- ================= DETAIL TABLE ================= -->
<div class="box">
    <b><?= $request_type === 'MATERIAL' ? 'Material Information' : 'Service Information' ?></b>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Image</th>
                <?php if ($request_type === 'MATERIAL'): ?>
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
        <?php $no = 1; foreach ($rows as $row): ?>
        <tr id="row-<?= $row['id'] ?>">
            <td><?= $no++ ?></td>

            <!-- Image (MATERIAL & SERVICE) -->
            <td style="text-align:center;">
                <?php if (!empty($row['image_url'])): ?>
                    <img src="<?= htmlspecialchars($row['image_url']) ?>"
                         style="width:60px;height:60px;object-fit:contain;border:1px solid #ddd;border-radius:6px;">
                <?php else: ?>
                    <span style="color:#999;font-size:12px">No Image</span>
                <?php endif; ?>
            </td>

            <?php if ($request_type === 'MATERIAL'): ?>
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
                if ($st === 'APPROVED')      echo '<span class="status-approved">Approved</span>';
                elseif ($st === 'REJECTED')  echo '<span class="status-reject">Rejected</span>';
                else                         echo '<span class="status-pending">Pending</span>';
                ?>
            </td>
            <td>
                <?php if (!$sudah_general): ?>
                    <?php if ($st === 'PENDING'): ?>
                        <form action="/atri/action/proses_review.php" method="POST" style="display:inline">
                            <input type="hidden" name="request_id" value="<?= $request_id ?>">
                            <input type="hidden" name="detail_id"  value="<?= $row['id'] ?>">
                            <button type="submit" name="action" value="approve_item" class="btn-approve">✓</button>
                        </form>
                        <button type="button" class="btn-reject"
                            onclick="openRejectItem(<?= $row['id'] ?>, '<?= htmlspecialchars($row['description'], ENT_QUOTES) ?>')">
                            ✗
                        </button>
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
</div>

<!-- ================= MDM VALIDATION ================= -->
<div class="validation-container">
    <div class="validation-box">
        <b>MDM VALIDATION</b><br><br>
        <?php
        $checklist = $request_type === 'MATERIAL' ? [
            'Material number format is valid',
            'No duplicate material number found',
            'Material description is clear and complete',
            'Unit of Measure (UoM) is appropriate',
            'Material group classification is correct',
            'Material type is valid',
            'External material group is correct',
            'Old material number reference is valid',
        ] : [
            'Service number format is valid',
            'No duplicate service number found',
            'Service description is clear and complete',
            'Unit of Measure (UoM) is appropriate',
            'Service group classification is correct',
            'Service category is valid',
        ];

        foreach ($checklist as $item):
        ?>
            <label>
                <input type="checkbox" class="mdm-check" onchange="cekChecklist()"
                    <?= $sudah_general ? 'disabled checked' : '' ?>>
                <?= htmlspecialchars($item) ?>
            </label><br>
        <?php endforeach; ?>
    </div>

    <div class="validation-box">
        <b>Duplicate Check</b><br><br>
        <?php if ($sudah_general): ?>
            <div class="duplicate-ok">✔ Sudah diproses, tidak perlu pengecekan ulang.</div>
        <?php else: ?>
            <?php
           $duplicates = [];
            $duplicate_found = false;

            if ($request_type === 'MATERIAL') {

                $numbers = [];

                foreach ($rows as $row) {

                    if (!empty($row['material_number'])) {

                        $numbers[] =
                            "'" . mysqli_real_escape_string(
                                $koneksi,
                                $row['material_number']
                            ) . "'";
                    }
                }

                if (!empty($numbers)) {

                    $in = implode(',', array_unique($numbers));

                    $qDup = executeOrFail($koneksi, "
                        SELECT
                            material_number,
                            description,
                            material_type
                        FROM material
                        WHERE material_number IN ($in)
                    ");

                    while ($d = mysqli_fetch_assoc($qDup)) {

                        $duplicates[$d['material_number']] = $d;
                    }
                }
            }

            foreach ($rows as $row) {

                $mn = $row['material_number'] ?? '';

                if (isset($duplicates[$mn])) {

                    $duplicate_found = true;

                    $cek = $duplicates[$mn];

                    echo '<div class="duplicate-warning">';
                    echo '⚠ Possible Duplicate Found<br><br>';
                    echo 'Material Number : ' . htmlspecialchars($cek['material_number']) . '<br>';
                    echo 'Material Type   : ' . htmlspecialchars($cek['material_type']) . '<br>';
                    echo 'Description     : ' . htmlspecialchars($cek['description']);
                    echo '</div>';
                }
            }

            if (!$duplicate_found) {
                echo '<div class="duplicate-ok">✔ No duplicate found</div>';
            }
            ?>
        <?php endif; ?>
    </div>
</div>

<!-- ================= GENERAL ACTION BUTTONS ================= -->
<?php if (!$sudah_general): ?>
    <div class="btn-area">
        <?php if ($all_reviewed && $has_approved): ?>
            <form action="/atri/action/proses_review.php" method="POST" style="display:inline">
                <input
                    type="hidden"
                    name="submit_token"
                    value="<?= $_SESSION['submit_token']; ?>"
                >
                <input type="hidden" name="request_id" value="<?= $request_id ?>">
                <button type="submit" name="action" value="general_approve"
                    id="btnGeneralApprove"
                    class="btn-approve"
                    disabled
                    style="opacity:0.5"
                    title="Centang semua checklist MDM Validation terlebih dahulu">
                    ✓ General Approve (<?= count($approved_items) ?> item lanjut)
                </button>
            </form>
        <?php else: ?>
            <button type="button" class="btn-approve" disabled style="opacity:0.5"
                title="Review semua item terlebih dahulu">
                ✓ General Approve
            </button>
        <?php endif; ?>

        <button type="button" class="btn-reject" onclick="openRejectAll()">
            ✗ Reject All
        </button>
    </div>

<?php else: ?>
    <div class="btn-area">
        <?php if ($aksi_general === 'GENERAL_APPROVED'): ?>
            <span style="color:green;font-weight:bold">
                ✓ Request sudah di-General Approve, diteruskan ke step berikutnya.
            </span>
        <?php elseif ($aksi_general === 'REJECTED'): ?>
            <span style="color:red;font-weight:bold">
                ✗ Request ini sudah ditolak dan tidak dapat diubah lagi.
            </span>
        <?php elseif ($header['status'] === 'COMPLETED'): ?>
            <span style="color:green;font-weight:bold">
                ✓ Request sudah selesai (Completed).
            </span>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div style="text-align:right; margin-top:20px; padding:0 20px 30px;">
    <a href="/atri/Pages/approval_list.php" class="btn-back">BACK</a>
</div>


<!-- ================= MODAL: Reject Per Item ================= -->
<div class="modal-overlay" id="modalRejectItem">
    <div class="modal-box">
        <div class="modal-title">✗ Reject Item</div>
        <div class="modal-subtitle" id="modalItemSubtitle">Masukkan alasan penolakan item ini.</div>

        <form action="/atri/action/proses_review.php" method="POST" id="formRejectItem">
            <input type="hidden" name="request_id" value="<?= $request_id ?>">
            <input type="hidden" name="detail_id"  id="rejectItemId" value="">
            <input type="hidden" name="action"     value="reject_item">

            <textarea class="modal-textarea" name="remarks" id="rejectItemNote"
                placeholder="Contoh: Material number sudah ada / deskripsi tidak lengkap..."
                maxlength="500"></textarea>
            <div class="modal-error" id="errorRejectItem">Alasan reject wajib diisi.</div>

            <div class="modal-footer">
                <button type="button" class="modal-btn-cancel" onclick="closeModal('modalRejectItem')">Batal</button>
                <button type="button" class="modal-btn-confirm" onclick="submitRejectItem()">✗ Confirm Reject</button>
            </div>
        </form>
    </div>
</div>


<!-- ================= MODAL: General Reject All ================= -->
<div class="modal-overlay" id="modalRejectAll">
    <div class="modal-box">
        <div class="modal-title">✗ Reject All Items</div>
        <div class="modal-subtitle">
            Semua item akan ditolak dan request tidak dapat dilanjutkan.<br>
            Masukkan alasan penolakan.
        </div>

        <form action="/atri/action/proses_review.php" method="POST" id="formRejectAll">
            <input
                type="hidden"
                name="submit_token"
                value="<?= $_SESSION['submit_token']; ?>"
            >
            <input type="hidden" name="request_id" value="<?= $request_id ?>">
            <input type="hidden" name="action"     value="general_reject">

            <textarea class="modal-textarea" name="remarks" id="rejectAllNote"
                placeholder="Contoh: Request tidak sesuai prosedur / data tidak lengkap..."
                maxlength="500"></textarea>
            <div class="modal-error" id="errorRejectAll">Alasan reject wajib diisi.</div>

            <div class="modal-footer">
                <button type="button" class="modal-btn-cancel" onclick="closeModal('modalRejectAll')">Batal</button>
                <button type="button" class="modal-btn-confirm" onclick="submitRejectAll()">✗ Confirm Reject All</button>
            </div>
        </form>
    </div>
</div>


<script>
// ---- Checklist ----
function cekChecklist() {
    const checks     = document.querySelectorAll('.mdm-check');
    const btnApprove = document.getElementById('btnGeneralApprove');
    if (!btnApprove) return;

    const semuaChecked       = Array.from(checks).every(c => c.checked);
    btnApprove.disabled      = !semuaChecked;
    btnApprove.style.opacity = semuaChecked ? '1' : '0.5';
    btnApprove.title         = semuaChecked ? '' : 'Centang semua checklist MDM Validation terlebih dahulu';
}

// ---- Modal helpers ----
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function (e) {
        if (e.target === this) closeModal(this.id);
    });
});

// ---- Reject per item ----
function openRejectItem(itemId, description) {
    document.getElementById('rejectItemId').value              = itemId;
    document.getElementById('rejectItemNote').value            = '';
    document.getElementById('errorRejectItem').style.display   = 'none';
    document.getElementById('modalItemSubtitle').textContent   = 'Item: ' + description;
    openModal('modalRejectItem');
    setTimeout(() => document.getElementById('rejectItemNote').focus(), 100);
}

function submitRejectItem() {
    const remarks = document.getElementById('rejectItemNote').value.trim();
    if (!remarks) {
        document.getElementById('errorRejectItem').style.display = 'block';
        return;
    }
    document.getElementById('errorRejectItem').style.display = 'none';
    document.getElementById('formRejectItem').submit();
}

// ---- General reject all ----
function openRejectAll() {
    document.getElementById('rejectAllNote').value           = '';
    document.getElementById('errorRejectAll').style.display  = 'none';
    openModal('modalRejectAll');
    setTimeout(() => document.getElementById('rejectAllNote').focus(), 100);
}

function submitRejectAll() {
    const remarks = document.getElementById('rejectAllNote').value.trim();
    if (!remarks) {
        document.getElementById('errorRejectAll').style.display = 'block';
        return;
    }
    document.getElementById('errorRejectAll').style.display = 'none';
    document.getElementById('formRejectAll').submit();
}
</script>

</body>
</html>