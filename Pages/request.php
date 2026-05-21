<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/config/koneksi.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['user_id'])) {
    header("Location: /atri/Pages/Index.php");
    exit();
}

$_SESSION['submit_token'] = bin2hex(random_bytes(32));

$company_id = $_SESSION['company_id'];

$queryTheme = mysqli_query($koneksi, "SELECT fungsi, warna FROM theme WHERE company_id='$company_id'");
$theme = [];
while ($row = mysqli_fetch_assoc($queryTheme)) {
    $theme[$row['fungsi']] = $row['warna'];
}

$user_id = $_SESSION['user_id'];

// Ambil draft terakhir user
$qDraft = mysqli_query($koneksi, "
    SELECT * FROM request_header
    WHERE requestor_id = '$user_id'
    AND status = 'DRAFT'
    ORDER BY id DESC LIMIT 1
");

$draft        = mysqli_fetch_assoc($qDraft);
$draft_detail = [];

if ($draft) {
    $id      = $draft['id'];
    $qDetail = mysqli_query($koneksi, "
        SELECT * FROM request_detail_material
        WHERE request_id = '$id'
    ");
    while ($row = mysqli_fetch_assoc($qDetail)) {
        $draft_detail[] = $row;
    }
}

$queryUser = mysqli_query($koneksi, "SELECT * FROM users WHERE id='$user_id'");
$user      = mysqli_fetch_assoc($queryUser);

$email      = $user['email'];
$nama       = $user['nama'];
$today      = date("Y-m-d");
$departemen = $user['departemen'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Material Registration</title>

    <link rel="stylesheet" href="/atri/css/global.css">
    <link rel="stylesheet" href="/atri/css/navbar.css">
    <link rel="stylesheet" href="/atri/css/request.css">

    <style>
        :root {
            --navbar:         <?= $theme['navbar'] ?>;
            --logo-material:  <?= $theme['logo_material'] ?>;
            --logo-service:   <?= $theme['logo_service'] ?>;
            --logo-vendor:    <?= $theme['logo_vendor'] ?>;
            --btn-primary:    <?= $theme['btn_primary'] ?>;
            --table-header:   <?= $theme['table_header'] ?>;
            --request-type:   <?= $theme['request_type'] ?>;
            --btn-edit:       <?= $theme['btn_edit'] ?>;
            --btn-approve:    <?= $theme['btn_approve'] ?>;
            --btn-reject:     <?= $theme['btn_reject'] ?>;
            --status-pending: <?= $theme['status_pending'] ?>;
            --status-active:  <?= $theme['status_active'] ?>;
        }
    </style>
<script>
    tailwind.config = {
        corePlugins: {
            preflight: false
        }
    }
</script>
<script src="https://cdn.tailwindcss.com"></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/navbar.php'; ?>

<div class="container">

    <!-- HEADER -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <h2 id="pageTitle">MATERIAL REGISTRATION</h2>
        <select id="requestType"
                onchange="handleTypeChange(this.value)"
                style="
                    color: white;
                    background: var(--request-type);
                    border: none;
                    padding: 8px 16px;
                    border-radius: 20px;
                    font-size: 13px;
                    cursor: pointer;
                    outline: none;
                    appearance: none;
                    -webkit-appearance: none;
                    padding-right: 30px;
                    background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 12 12%22><path fill=%22white%22 d=%22M6 8L1 3h10z%22/></svg>');
                    background-repeat: no-repeat;
                    background-position: right 10px center;
                    transition: background-color 0.3s;
                ">
            <option value="MATERIAL">Material</option>
            <option value="SERVICE">Service</option>
        </select>
    </div>

    <!-- FORM -->
    <form action="/atri/action/save_request.php" method="POST" onsubmit="clearStorage(event)">
        <input type="hidden" name="submit_token"  value="<?= $_SESSION['submit_token'] ?? '' ?>">
        <input type="hidden" name="request_type"  id="hiddenType" value="MATERIAL">
        <input type="hidden" name="request_id"    value="<?= $draft['id'] ?? '' ?>">

        <!-- REQUESTOR -->
        <div class="card-req">
            <div class="card-title-req">Requestor Information</div>
            <div class="form-row-req">
                <div class="form-group-req">
                    <label>Email</label>
                    <input type="text" value="<?= $email ?>" readonly>
                </div>
                <div class="form-group-req">
                    <label>Name</label>
                    <input type="text" value="<?= $nama ?>" readonly>
                </div>
                <div class="form-group-req">
                    <label>Departemen</label>
                    <input type="text" name="departemen" value="<?= $departemen ?>" readonly>
                </div>
                <div class="form-group-req">
                    <label>Date</label>
                    <input type="date" name="request_date" value="<?= $today ?>" readonly>
                </div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="card-req">

            <div class="card-header-req">
                <div class="card-title-req" id="tableTitle">DATA MATERIAL MASTER</div>
                <div class="header-btn-req">
                    <button type="button" class="btn-req" onclick="document.getElementById('csvInput').click()">Upload CSV</button>
                    <input type="file" id="csvInput" accept=".csv" style="display:none" onchange="uploadCSV(this)">
                    <button type="button" class="btn-req" onclick="downloadTemplate()">Download Template</button>
                </div>
            </div>

            <!-- Bulk action khusus SERVICE -->
            <div id="serviceBulkAction"
                style="
                    display:none;
                    align-items:center;
                    gap:10px;
                    margin-bottom:15px;
                    padding:10px;
                    background:#f5f5f5;
                    border-radius:10px;
                ">
                <select onchange="setAllServiceCategory(this.value)"
                    style="
                        padding:8px 12px;
                        border:1px solid #ccc;
                        border-radius:8px;
                    ">
                    <option value="">Set All Service Category</option>
                    <option value="Z001-ATRI">Z001 - ATRI</option>
                    <option value="Z001-ADARO">Z001 - ADARO</option>
                </select>
                <select onchange="setAllValClass(this.value)"
                    style="
                        padding:8px 12px;
                        border:1px solid #ccc;
                        border-radius:8px;
                    ">
                    <option value="">Set All Valuation</option>
                    <option value="S000">S000</option>
                    <option value="S001">S001</option>
                    <option value="S002">S002</option>
                    <option value="S003">S003</option>
                    <option value="S004">S004</option>
                    <option value="S005">S005</option>
                    <option value="S006">S006</option>
                    <option value="S007">S007</option>
                    <option value="S008">S008</option>
                    <option value="S009">S009</option>
                </select>
            </div>

            <div class="table-box-req">
                <table class="table-req" id="materialTable">
                    <thead id="tableHead"></thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>

            <div class="add-material-req">
                <button type="button" class="btn-req" onclick="addRow()">+ Add Row</button>
            </div>

        </div>

        <div class="bottom-btn-req">
            <button type="submit" name="action" value="DRAFT"  class="btn-draft-req">SAVE DRAFT</button>
            <button type="submit" name="action" value="SUBMIT" class="btn-register-req">REGISTER</button>
        </div>

    </form>
</div>

<script>
/* ============================================================
   STATE
   ============================================================ */
let no        = 1;
let draftData = <?= json_encode($draft_detail) ?>;

/* ============================================================
   DOUBLE-SUBMIT GUARD
   ============================================================ */
document.addEventListener('DOMContentLoaded', function () {
    const form          = document.querySelector('form[action="/atri/action/save_request.php"]');
    let   isSubmitting  = false;

    form.addEventListener('submit', function (e) {
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }

        isSubmitting = true;

        const clickedButton = document.activeElement;
        form.querySelectorAll('button[type="submit"]').forEach(btn => {
            if (btn !== clickedButton) btn.disabled = true;
        });

        if (clickedButton) clickedButton.innerText = 'Memproses...';
    });
});

/* ============================================================
   TABLE HEAD
   ============================================================ */
function renderHead(type = 'MATERIAL') {
    let head = '';

    if (type === 'MATERIAL') {
        head = `
        <tr>
            <th>No</th>
            <th>Image</th>
            <th>Numbering</th>
            <th>Material Number</th>
            <th>Description</th>
            <th>UOM</th>
            <th>Ext Group</th>
            <th>Material Group</th>
            <th>Material Type</th>
            <th>Detail</th>
            <th>Delete</th>
        </tr>`;
    } else {
        head = `
        <tr>
            <th>No</th>
            <th>Image</th>
            <th>Service Number</th>
            <th>Description</th>
            <th>UOM</th>
            <th>Service Group</th>
            <th>Service Category</th>
            <th>Valuation Class</th>
            <th>Remarks</th>
            <th>Delete</th>
        </tr>`;
    }

    document.getElementById('tableHead').innerHTML = head;
}

/* ============================================================
   ADD ROW – MATERIAL
   ============================================================ */
function addRowMaterial(v = []) {
    const t   = document.getElementById('tableBody');
    const r   = t.insertRow();
    const val = i => v[i] || '';

    r.innerHTML = `
        <td>${no++}</td>

        <td style="text-align:center; vertical-align:middle;">
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px;">
                <div class="w-16 h-16 border rounded bg-gray-100 flex items-center justify-center overflow-hidden">
                    <img src="${val(0)}" class="max-w-full max-h-full object-contain" onerror="this.style.display='none'">
                </div>
                <input name="image_url[]" value="${val(0)}" oninput="updatePreview(this)" style="width:100%; text-align:center;">
            </div>
        </td>

        <td><input name="numbering_scheme[]"   value="${val(1)}"></td>
        <td><input name="material_number[]"    value="${val(2)}"></td>
        <td><input name="description[]"        value="${val(3)}"></td>
        <td><input name="uom[]"                value="${val(5)}"></td>
        <td><input name="ext_material_group[]" value="${val(6)}"></td>
        <td><input name="material_group[]"     value="${val(7)}"></td>
        <td><input name="material_type[]"      value="${val(8)}"></td>

        <td><button type="button" class="btn-req" onclick="goDetail(this)">View</button></td>
        <td><button type="button" onclick="this.closest('tr').remove()">X</button></td>

        <input type="hidden" name="description_alt[]"    value="${val(4)}">
        <input type="hidden" name="vhs[]"                value="${val(9)}">
        <input type="hidden" name="location[]"           value="${val(10)}">
        <input type="hidden" name="val_class[]"          value="${val(11)}">
        <input type="hidden" name="val_category[]"       value="${val(12)}">
        <input type="hidden" name="purchasing_group[]"   value="${val(13)}">
        <input type="hidden" name="mrp_controller[]"     value="${val(14)}">
        <input type="hidden" name="price_control[]"      value="${val(15)}">
        <input type="hidden" name="profile_make[]"       value="${val(16)}">
        <input type="hidden" name="profile_plan[]"       value="${val(17)}">
        <input type="hidden" name="old_material_number[]" value="${val(18)}">
        <input type="hidden" name="egi[]"                value="${val(19)}">
        <input type="hidden" name="cgi[]"                value="${val(20)}">
        <input type="hidden" name="engine_type[]"        value="${val(21)}">
        <input type="hidden" name="order_unit[]"         value="${val(22)}">
        <input type="hidden" name="net_weight[]"         value="${val(23)}">
        <input type="hidden" name="weight_unit[]"        value="${val(24)}">
        <input type="hidden" name="max_stock_level[]"    value="${val(25)}">
        <input type="hidden" name="min_lot_size[]"       value="${val(26)}">
        <input type="hidden" name="max_lot_size[]"       value="${val(27)}">
        <input type="hidden" name="fix_lot_size[]"       value="${val(28)}">
        <input type="hidden" name="standard_price[]"     value="${val(29)}">
        <input type="hidden" name="moving_price[]"       value="${val(30)}">
        <input type="hidden" name="remarks[]"            value="${val(31)}">
    `;
}

/* ============================================================
   ADD ROW – SERVICE
   ============================================================ */
function addRowService(v = []) {
    const t   = document.getElementById('tableBody');
    const r   = t.insertRow();
    const val = i => v[i] || '';

    r.innerHTML = `
        <td>${no++}</td>

        <td style="text-align:center; vertical-align:middle;">
            <div style="display:flex; flex-direction:column; align-items:center; gap:6px;">
                <div class="w-16 h-16 border rounded bg-gray-100 flex items-center justify-center overflow-hidden">
                    <img src="${val(0)}" class="max-w-full max-h-full object-contain" onerror="this.style.display='none'">
                </div>
                <input name="image_url[]" value="${val(0)}" oninput="updatePreview(this)" style="width:100%; text-align:center;">
            </div>
        </td>

        <td><input name="service_number[]" value="${val(1)}"></td>
        <td><input name="description[]"    value="${val(2)}"></td>
        <td><input name="uom[]"            value="${val(3)}"></td>
        <td><input name="service_group[]"  value="${val(4)}"></td>

        <td>
            <select name="service_category[]">
                <option value="">-- Select --</option>
                <option value="Z001-ATRI"  ${val(5) === 'Z001-ATRI'  ? 'selected' : ''}>Z001 - ATRI</option>
                <option value="Z001-ADARO" ${val(5) === 'Z001-ADARO' ? 'selected' : ''}>Z001 - ADARO</option>
            </select>
        </td>

        <td>
            <select name="valuation_class[]">
                <option value="">-- Select --</option>
                ${['S000','S001','S002','S003','S004','S005','S006','S007','S008','S009']
                    .map(s => `<option value="${s}" ${val(6) === s ? 'selected' : ''}>${s}</option>`)
                    .join('')}
            </select>
        </td>

        <td><input name="remarks[]" value="${val(7)}"></td>
        <td><button type="button" onclick="this.closest('tr').remove()">X</button></td>
    `;
}

/* ============================================================
   BULK SET (SERVICE)
   ============================================================ */
function setAllServiceCategory(value) {
    if (!value) return;
    document.querySelectorAll('select[name="service_category[]"]').forEach(s => s.value = value);
}

function setAllValClass(value) {
    if (!value) return;
    document.querySelectorAll('select[name="valuation_class[]"]').forEach(s => s.value = value);
}

/* ============================================================
   IMAGE PREVIEW
   ============================================================ */
function updatePreview(input) {
    const img = input.closest('td').querySelector('img');
    img.src   = input.value;
    img.style.display = input.value ? 'block' : 'none';
}

/* ============================================================
   TYPE SWITCH
   ============================================================ */
function handleTypeChange(type) {
    document.getElementById('pageTitle').innerText =
        type === 'MATERIAL' ? 'MATERIAL REGISTRATION' : 'SERVICE REGISTRATION';

    document.getElementById('tableTitle').innerText =
        type === 'MATERIAL' ? 'DATA MATERIAL MASTER' : 'DATA SERVICE MASTER';

    document.getElementById('hiddenType').value = type;
    document.getElementById('serviceBulkAction').style.display =
        type === 'SERVICE' ? 'flex' : 'none';

    updateSelectColor(type);

    document.getElementById('tableBody').innerHTML = '';
    no = 1;

    renderHead(type);
    addRow();
}

function updateSelectColor(type) {
    const select = document.getElementById('requestType');
    const prop   = type === 'MATERIAL' ? '--logo-material' : '--logo-service';
    select.style.background = getComputedStyle(document.documentElement).getPropertyValue(prop);
}

/* ============================================================
   CSV UPLOAD
   ============================================================ */
function uploadCSV(input) {
    const f = input.files[0];
    if (!f) return;

    const reader = new FileReader();
    reader.onload = e => {
        const lines = e.target.result.split('\n');

        document.getElementById('tableBody').innerHTML = '';
        no = 1;

        let start = -1;
        for (let i = 0; i < lines.length; i++) {
            if (lines[i].toLowerCase().includes('numbering scheme')) {
                start = i + 1;
                break;
            }
        }

        if (start === -1) {
            alert('Header tidak ditemukan ❌');
            return;
        }

        const type = document.getElementById('requestType').value;

        if (type !== 'MATERIAL') {
            alert('CSV hanya untuk Material ❌');
            return;
        }

        for (let i = start; i < lines.length; i++) {
            const row = lines[i].trim();
            if (!row) continue;

            const c = parseCSV(row);
            if (!c[2]) continue;

            addRowMaterial([
                '',         // image_url        (0)
                c[1],       // numbering_scheme  (1)
                c[2],       // material_number   (2)
                c[3],       // description       (3)
                c[17],      // description_alt   (4)
                c[4],       // uom               (5)
                c[5],       // ext_material_group(6)
                c[6],       // material_group    (7)
                c[7].startsWith('MM_') ? c[7] : 'MM_' + c[7], // material_type (8)
                c[8],       // vhs               (9)
                c[9],       // location          (10)
                c[10],      // val_class         (11)
                c[11],      // val_category      (12)
                c[12],      // purchasing_group  (13)
                c[13],      // mrp_controller    (14)
                c[14],      // price_control     (15)
                c[15],      // profile_make      (16)
                c[16],      // profile_plan      (17)
                c[18],      // old_material_number(18)
                c[19],      // egi               (19)
                c[20],      // cgi               (20)
                c[21],      // engine_type       (21)
                c[22],      // order_unit        (22)
                c[23],      // net_weight        (23)
                c[24],      // weight_unit       (24)
                c[25],      // max_stock_level   (25)
                c[26],      // min_lot_size      (26)
                c[27],      // max_lot_size      (27)
                c[28],      // fix_lot_size      (28)
                c[29],      // standard_price    (29)
                c[30],      // moving_price      (30)
                c[31]       // remarks           (31)
            ]);
        }

        alert('CSV berhasil terupload!');
    };

    reader.readAsText(f);
}

function parseCSV(line) {
    const res = [];
    let cur = '', q = false;
    for (let i = 0; i < line.length; i++) {
        const ch = line[i];
        if      (ch === '"')            q   = !q;
        else if (ch === ',' && !q)      { res.push(cur); cur = ''; }
        else                            cur += ch;
    }
    res.push(cur);
    return res;
}

/* ============================================================
   LOCAL STORAGE (detail modal sync)
   ============================================================ */
function saveTable() {
    const rows = document.querySelectorAll('#tableBody tr');
    const data = [];

    rows.forEach(r => {
        const obj = {};
        r.querySelectorAll('input').forEach(i => {
            obj[i.name.replace('[]', '')] = i.value;
        });
        data.push(obj);
    });

    localStorage.setItem('material_table', JSON.stringify(data));
}

function goDetail(btn) {
    saveTable();
    sessionStorage.setItem('from_detail', '1');

    const row  = btn.closest('tr');
    const data = {};
    row.querySelectorAll('input').forEach(i => {
        data[i.name.replace('[]', '')] = i.value;
    });

    localStorage.setItem('material_detail', JSON.stringify(data));
    window.location.href = '/atri/Pages/detail_material.php';
}

function clearStorage(e) {
    const action = document.activeElement ? document.activeElement.value : '';
    if (action === 'SUBMIT') {
        localStorage.removeItem('material_table');
        localStorage.removeItem('material_detail');
    }
}

/* ============================================================
   ADD ROW (dispatcher)
   ============================================================ */
function addRow() {
    const type = document.getElementById('requestType').value;
    if (type === 'MATERIAL') addRowMaterial();
    else                     addRowService();
}

/* ============================================================
   ON LOAD
   ============================================================ */
window.onload = () => {
    const type = document.getElementById('requestType').value;
    renderHead(type);
    updateSelectColor(type);

    const fromDetail = sessionStorage.getItem('from_detail');

    if (fromDetail) {
        // Pulih dari halaman detail
        const saved = JSON.parse(localStorage.getItem('material_table') || '[]');

        if (saved.length > 0) {
            saved.forEach(row => addRowMaterial([
                row.image_url,          row.numbering_scheme,    row.material_number,
                row.description,        row.description_alt,     row.uom,
                row.ext_material_group, row.material_group,      row.material_type,
                row.vhs,                row.location,            row.val_class,
                row.val_category,       row.purchasing_group,    row.mrp_controller,
                row.price_control,      row.profile_make,        row.profile_plan,
                row.old_material_number,row.egi,                 row.cgi,
                row.engine_type,        row.order_unit,          row.net_weight,
                row.weight_unit,        row.max_stock_level,     row.min_lot_size,
                row.max_lot_size,       row.fix_lot_size,        row.standard_price,
                row.moving_price,       row.remarks
            ]));
        } else {
            addRow();
        }

        sessionStorage.removeItem('from_detail');

    } else if (draftData && draftData.length > 0) {
        // Muat dari draft database
        document.getElementById('tableBody').innerHTML = '';
        no = 1;

        draftData.forEach(row => addRowMaterial([
            row.image_url,          row.numbering_scheme,    row.material_number,
            row.description,        row.description_alt,     row.uom,
            row.ext_material_group, row.material_group,      row.material_type,
            row.vhs,                row.location,            row.val_class,
            row.val_category,       row.purchasing_group,    row.mrp_controller,
            row.price_control,      row.profile_make,        row.profile_plan,
            row.old_material_number,row.egi,                 row.cgi,
            row.engine_type,        row.order_unit,          row.net_weight,
            row.weight_unit,        row.max_stock_level,     row.min_lot_size,
            row.max_lot_size,       row.fix_lot_size,        row.standard_price,
            row.moving_price,       row.remarks
        ]));

    } else {
        addRow();
    }
};

// Auto-save ke localStorage setiap ada input
document.addEventListener('input', saveTable);
</script>

</body>
</html>