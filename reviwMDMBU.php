<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id'])){
    echo "Request tidak ditemukan";
    exit();
}

$company_id = $_SESSION['company_id'];
$request_id = $_GET['id'];

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
    WHERE rh.id='$request_id'
");
$header = mysqli_fetch_assoc($queryHeader);

// Detail
$queryDetail = mysqli_query($koneksi, "
    SELECT * FROM request_detail
    WHERE request_id='$request_id'
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Review Request MDM BU</title>
    <link rel="stylesheet" href="css/navbar.css">

    <style>
    :root {
        --navbar: <?= $theme['navbar']; ?>;
        --btn-approve: <?= $theme['btn_approve']; ?>;
        --btn-reject: <?= $theme['btn_reject']; ?>;
        --btn-edit: <?= $theme['btn_edit']; ?>;
        --table-header: <?= $theme['table_header']; ?>;
    }

    body {
        font-family: Arial;
        background: #f4f6f9;
    }

    .wrapper {
        width: 95%;
        margin: auto;
    }

    .box {
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th {
        background: var(--table-header);
        padding: 8px;
        border: 1px solid #ccc;
    }

    .table td {
        padding: 8px;
        border: 1px solid #ccc;
    }

    .validation-container {
        display: flex;
        gap: 15px;
    }

    .validation-box {
        width: 50%;
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #ddd;
    }

    .btn-area {
        margin-top: 20px;
        text-align: center;
    }

    .btn-approve {
        background: var(--btn-approve);
        color: #fff;
        padding: 10px 25px;
        border: none;
        border-radius: 6px;
    }

    .btn-reject {
        background: var(--btn-reject);
        color: #fff;
        padding: 10px 25px;
        border: none;
        border-radius: 6px;
    }

    .btn-revision {
        background: orange;
        color: #fff;
        padding: 10px 25px;
        border: none;
        border-radius: 6px;
    }

    .duplicate-ok {
        background: #d4edda;
        padding: 10px;
        border-radius: 6px;
        color: green;
    }

    .duplicate-warning {
        background: #ffeeba;
        padding: 10px;
        border-radius: 6px;
        color: #856404;
    }

    </style>
</head>

<body>
<?php include 'navbar.php'; ?>

<div class="wrapper">

    <h2>Review Request MDM BU</h2>

    <!-- Requestor Info -->
    <div class="box">
        <b>Requestor Information</b><br><br>
        Request No : <?= $header['request_no']; ?> &nbsp;&nbsp;&nbsp;
        Departemen : <?= $header['departemen']; ?> <br>
        Requester Name : <?= $header['nama']; ?> &nbsp;&nbsp;&nbsp;
        Request Date : <?= $header['request_date']; ?> <br>
        Status : <?= $header['status']; ?>
    </div>

    <!-- Material Table -->
    <div class="box">
        <b>Material Information</b>
        <table class="table">
            <tr>
                <th>No</th>
                <th>Material Number</th>
                <th>Description</th>
                <th>UoM</th>
                <th>Material Group</th>
                <th>External Group</th>
                <th>Material Type</th>
                <th>Action</th>
            </tr>

            <?php $no=1; while($row = mysqli_fetch_assoc($queryDetail)){ ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= $row['material_number']; ?></td>
                <td><?= $row['material_desc']; ?></td>
                <td><?= $row['uom']; ?></td>
                <td><?= $row['material_group']; ?></td>
                <td><?= $row['external_group']; ?></td>
                <td><?= $row['material_type']; ?></td>
                <td>
                    <a href="edit_material.php?id=<?= $row['id']; ?>">
                        <button class="btn-revision">Edit</button>
                    </a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>

    <!-- Validation -->
    <div class="validation-container">

        <div class="validation-box">
            <b>MDM VALIDATION</b><br><br>

            <input type="checkbox"> Material number format is valid <br>
            <input type="checkbox"> No duplicate material number found <br>
            <input type="checkbox"> Material description is clear and complete <br>
            <input type="checkbox"> Unit of Measure (UoM) is appropriate <br>
            <input type="checkbox"> Material group classification is correct <br>
            <input type="checkbox"> Material type is valid <br>
            <input type="checkbox"> External material group is correct <br>
            <input type="checkbox"> Old material number reference is valid <br>
        </div>

        <div class="validation-box">
            <b>Duplicate Check</b><br><br>

            <div class="duplicate-ok">
                ✔ No duplicate material found
            </div>

            <!-- Kalau ada duplicate pakai ini -->
            <!--
            <div class="duplicate-warning">
                ⚠ Possible Duplicate Found <br><br>
                Material Number : 176394000 <br>
                Material Type : Z001 <br>
                Material Description : FLEXIBLE MOTOR MOUNTING_2PCS/SET_BY1540
            </div>
            -->
        </div>

    </div>

    <!-- Button -->
    <div class="btn-area">
        <form action="proses_review.php" method="POST">
            <input type="hidden" name="request_id" value="<?= $header['id']; ?>">

            <button type="submit" name="action" value="revision" class="btn-revision">Request Revision</button>
            <button type="submit" name="action" value="approve" class="btn-approve">Approve</button>
            <button type="submit" name="action" value="reject" class="btn-reject">Reject</button>
        </form>
    </div>

</div>
</body>
</html>