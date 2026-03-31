<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['company_id'];

$queryTheme = mysqli_query($koneksi, "SELECT fungsi, warna FROM theme WHERE company_id='$company_id'");
$theme = [];
while($row = mysqli_fetch_assoc($queryTheme)){
    $theme[$row['fungsi']] = $row['warna'];
}

$user_id = $_SESSION['user_id'];
$queryUser = mysqli_query($koneksi, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($queryUser);

$email = $user['email'];
$nama  = $user['nama'];
$today = date("Y-m-d");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Material Registration</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/request.css">
    <style>
    :root {
        --navbar: <?php echo $theme['navbar']; ?>;
        --logo-material: <?php echo $theme['logo_material']; ?>;
        --logo-service: <?php echo $theme['logo_service']; ?>;
        --logo-vendor: <?php echo $theme['logo_vendor']; ?>;
        --btn-primary: <?php echo $theme['btn_primary']; ?>;
        --table-header: <?php echo $theme['table_header']; ?>;
        --request-type: <?php echo $theme['request_type']; ?>;
        --btn-edit: <?php echo $theme['btn_edit']; ?>;
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

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 id="pageTitle">MATERIAL REGISTRATION</h2>
        <select id="requestType" onchange="changeType(this.value); setRequestType(); updateSelectColor(this.value);" style="
            color: white;
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
            <option value="material">Material</option>
            <option value="service">Service</option>
        </select>
    </div>

    <form action="save_request.php" method="POST">
    <input type="hidden" name="request_type" id="hiddenType" value="material">

    <!-- REQUESTOR INFORMATION -->
    <div class="card-req">
        <div class="card-title-req">Requestor Information</div>
        <div class="form-row-req">
            <div class="form-group-req">
                <label>Requestor Email</label>
                <input type="text" value="<?= $email; ?>" readonly>
            </div>
            <div class="form-group-req">
                <label>Requestor Name</label>
                <input type="text" value="<?= $nama; ?>" readonly>
            </div>
            <div class="form-group-req">
                <label>Departemen</label>
                <input type="text" name="departemen">
            </div>
            <div class="form-group-req">
                <label>Request Date</label>
                <input type="date" name="tanggal" value="<?= $today; ?>" readonly>
            </div>
        </div>
    </div>

    <!-- DATA TABLE -->
    <div class="card-req">
        <div class="card-header-req">
            <div class="card-title-req" id="cardTitle">DATA MATERIAL MASTER</div>
            <div class="header-btn-req">
                <button type="button" class="btn-req" onclick="document.getElementById('csvInput').click()">Upload CSV</button>
                <input type="file" id="csvInput" accept=".csv" style="display:none" onchange="uploadCSV(this)">
                <button type="button" class="btn-req" onclick="downloadTemplate()">Download Template</button>
            </div>
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

    <!-- BUTTON -->
    <div class="bottom-btn-req">
        <button type="submit" name="action" value="draft" class="btn-draft-req">SAVE DRAFT</button>
        <button type="submit" name="action" value="submit" class="btn-register-req">REGIST</button>
    </div>

    </form>
</div>

<script>
let no = 1;
let currentType = 'material';

const columns = {
    material: [
        { label: 'Numbering Scheme',     name: 'numbering_scheme[]' },
        { label: 'Material Number',      name: 'material_number[]' },
        { label: 'Material Description', name: 'material_desc[]' },
        { label: 'UOM',                  name: 'uom[]' },
        { label: 'Ext. Material Group',  name: 'ext_group[]' },
        { label: 'Material Group',       name: 'material_group[]' },
        { label: 'Type',                 name: 'material_type[]' },
        { label: 'VHS',                  name: 'vhs[]' },
        { label: 'Location',             name: 'location[]' },
        { label: 'Val. Class',           name: 'val_class[]' },
        { label: 'Val. Category',        name: 'val_category[]' },
        { label: 'Purchasing Group',     name: 'purchasing_group[]' },
        { label: 'MRP Controller',       name: 'mrp_controller[]' },
        { label: 'Price Control',        name: 'price_control[]' },
        { label: 'Profile - Make',       name: 'profile_make[]' },
        { label: 'Profile - Plan',       name: 'profile_plan[]' },
    ],
    service: [
        { label: 'Service Number',      name: 'service_number[]' },
        { label: 'Service Description', name: 'service_desc[]' },
        { label: 'UoM',                 name: 'uom[]' },
        { label: 'Service Group',       name: 'service_group[]' },
        { label: 'Service Category',    name: 'service_category[]' },
    ]
};

const csvColIndex = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16];

function updateSelectColor(type) {
    const select = document.getElementById('requestType');
    const style = getComputedStyle(document.documentElement);
    if (type === 'material') {
        select.style.backgroundColor = style.getPropertyValue('--logo-material').trim();
    } else if (type === 'service') {
        select.style.backgroundColor = style.getPropertyValue('--logo-service').trim();
    }
}

function renderHead() {
    const cols = columns[currentType];
    let headHtml = '<tr><th style="min-width:40px">No</th>';
    cols.forEach(c => headHtml += `<th style="min-width:120px">${c.label}</th>`);
    headHtml += '<th style="min-width:60px">Delete</th></tr>';
    document.getElementById('tableHead').innerHTML = headHtml;
}

function changeType(type) {
    currentType = type;
    no = 1;

    document.getElementById('pageTitle').innerText =
        type === 'material' ? 'MATERIAL REGISTRATION' : 'SERVICE REGISTRATION';
    document.getElementById('cardTitle').innerText =
        type === 'material' ? 'DATA MATERIAL MASTER' : 'DATA SERVICE MASTER';

    renderHead();
    document.getElementById('tableBody').innerHTML = '';
    addRow();
}

function addRow(values = []) {
    const cols = columns[currentType];
    const tbody = document.getElementById('tableBody');
    const row = tbody.insertRow();

    let cells = `<td style="text-align:center">${no++}</td>`;
    cols.forEach((c, i) => {
        const val = values[i] ?? '';
        cells += `<td><input type="text" name="${c.name}" value="${val}"></td>`;
    });
    cells += `<td style="text-align:center">
        <button type="button" onclick="deleteRow(this)" style="
            background:#ffb3b3;border:none;border-radius:4px;
            padding:3px 8px;cursor:pointer;font-size:12px;
        ">X</button>
    </td>`;
    row.innerHTML = cells;
}

function deleteRow(btn) {
    btn.parentNode.parentNode.remove();
}

function setRequestType() {
    document.getElementById('hiddenType').value = currentType;
}

function uploadCSV(input) {
    const file = input.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const lines = e.target.result.split('\n');

        let dataStartIndex = -1;
        for (let i = 0; i < lines.length; i++) {
            if (lines[i].startsWith('No.') || lines[i].startsWith('No.,')) {
                dataStartIndex = i + 1;
                break;
            }
        }

        if (dataStartIndex === -1) {
            alert('Format CSV tidak sesuai template. Pastikan menggunakan template yang benar.');
            return;
        }

        document.getElementById('tableBody').innerHTML = '';
        no = 1;

        let rowCount = 0;
        for (let i = dataStartIndex; i < lines.length; i++) {
            const line = lines[i].trim();
            if (!line) continue;

            const cells = parseCSVLine(line);
            if (cells.length === 0) continue;

            const values = csvColIndex.map(idx => (cells[idx] ?? '').trim());
            const hasData = values.some(v => v !== '');
            if (!hasData) continue;

            addRow(values);
            rowCount++;
        }

        if (rowCount === 0) {
            alert('Tidak ada data yang ditemukan di CSV.');
            addRow();
        } else {
            alert(`Berhasil import ${rowCount} baris data.`);
        }

        input.value = '';
    };
    reader.readAsText(file);
}

function parseCSVLine(line) {
    const result = [];
    let current = '';
    let inQuotes = false;

    for (let i = 0; i < line.length; i++) {
        const char = line[i];
        if (char === '"') {
            inQuotes = !inQuotes;
        } else if (char === ',' && !inQuotes) {
            result.push(current);
            current = '';
        } else {
            current += char;
        }
    }
    result.push(current);
    return result;
}

function downloadTemplate() {
    const headers = [
        'No.', '*Numbering Scheme', '*Material Number', '*Material Description',
        '*UOM', '*External Material Group', '*Material Group', '*Type',
        '*VHS', '*Location', '*Val. Class', '*Val. Category',
        '*Purchasing Group', '*MRP Controller', '*Price Control',
        '*Profile - Make', '*Profile - Plan'
    ];

    let csv = headers.join(',') + '\n';
    for (let i = 1; i <= 5; i++) {
        csv += i + ',' + ','.repeat(headers.length - 1) + '\n';
    }

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'template_material.csv';
    a.click();
    URL.revokeObjectURL(url);
}

window.onload = function() {
    renderHead();
    addRow();
    updateSelectColor('material');
}
</script>

</body>
</html>