<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/config/koneksi.php';

/** @var mysqli $koneksi */

$filterType   = $_GET['type'] ?? '';
$filterStatus = $_GET['status'] ?? '';


if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$user_id    = (int)$_SESSION['user_id'];

// 1. Ambil Theme
$queryTheme = mysqli_query($koneksi, "SELECT fungsi, warna FROM theme WHERE company_id='$company_id'");
$theme = [];
while($row = mysqli_fetch_assoc($queryTheme)){
    $theme[$row['fungsi']] = $row['warna'];
}

// 2. Summary Counts — include semua status yang mungkin ada
$statuses = ['DRAFT', 'PENDING', 'PARTIAL', 'REJECTED', 'COMPLETED'];
$summary  = [];
foreach($statuses as $st){
    $res           = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM request_header WHERE requestor_id = $user_id AND status = '$st'");
    $data          = mysqli_fetch_assoc($res);
    $summary[$st]  = $data['total'];
}



// 3. Query List
$where = ["rh.requestor_id = $user_id"];

if($filterType != ''){
    $where[] = "rh.request_type = '" . mysqli_real_escape_string($koneksi, $filterType) . "'";
}

if($filterStatus != ''){
    $where[] = "rh.status = '" . mysqli_real_escape_string($koneksi, $filterStatus) . "'";
}

$whereSql = implode(" AND ", $where);
$sql = "
    SELECT 
        rh.*,
        (SELECT COUNT(*) FROM request_detail_material WHERE request_id = rh.id) as total_material,
        (SELECT COUNT(*) FROM request_detail_service WHERE request_id = rh.id) as total_service
    FROM request_header rh
    WHERE $whereSql
    ORDER BY rh.request_no DESC";

$query = mysqli_query($koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Home - MDM Portal</title>
    
    <link rel="stylesheet" href="/atri/css/global.css">
    <link rel="stylesheet" href="/atri/css/navbar.css">
    <link rel="stylesheet" href="/atri/css/request_list.css">
    <style>
    :root {
        --navbar:         <?php echo $theme['navbar']; ?>;
        --logo-material:  <?php echo $theme['logo_material']; ?>;
        --logo-service:   <?php echo $theme['logo_service']; ?>;
        --logo-vendor:    <?php echo $theme['logo_vendor']; ?>;
        --btn-primary:    <?php echo $theme['btn_primary']; ?>;
        --btn-approve:    <?php echo $theme['btn_approve']; ?>;
        --btn-reject:     <?php echo $theme['btn_reject']; ?>;
        --table-header:   <?php echo $theme['table_header']; ?>;
        --status-pending: <?php echo $theme['status_pending']; ?>;
        --status-active:  <?php echo $theme['status_active']; ?>;
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
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 id="pageTitle"> My Request List</h2>
        <a href="request.php" style="background:var(--btn-primary);color:white;padding:10px 15px;border-radius:5px;text-decoration:none;">+ New Request</a>
    </div>

    <!-- Summary Cards -->
    <div class="summary-box">
        <?php
        $labelMap = [
            'DRAFT'     => 'Draft',
            'PENDING'   => 'Pending',
            'PARTIAL'   => 'In Review',
            'REJECTED'  => 'Rejected',
            'COMPLETED' => 'Completed',
        ];
        $colorMap = [
            'DRAFT'     => ['bg' => '#f0f0f0', 'color' => '#555'],
            'PENDING'   => ['bg' => '#fff3cd', 'color' => '#856404'],
            'PARTIAL'   => ['bg' => '#cce5ff', 'color' => '#004085'],
            'REJECTED'  => ['bg' => '#f8d7da', 'color' => '#721c24'],
            'COMPLETED' => ['bg' => '#d4edda', 'color' => '#155724'],
        ];
        foreach($summary as $st => $count):
            $bg    = $colorMap[$st]['bg']    ?? '#f0f0f0';
            $color = $colorMap[$st]['color'] ?? '#333';
        ?>
        <div class="summary-card" style="border-top: 4px solid <?= $color ?>">
            <div style="color:#666;font-size:13px;"><?= $labelMap[$st] ?? $st ?></div>
            <h1 style="margin:10px 0 0 0;color:<?= $color ?>"><?= $count ?></h1>
        </div>
        <?php endforeach; ?>

    </div>

<form method="GET" style="display:flex;gap:10px;margin-bottom:15px;">
    
    <select name="type">
        <option value="">All Type</option>
        <option value="MATERIAL" <?= ($_GET['type'] ?? '')=='MATERIAL' ? 'selected' : '' ?>>Material</option>
        <option value="SERVICE" <?= ($_GET['type'] ?? '')=='SERVICE' ? 'selected' : '' ?>>Service</option>
    </select>

    <select name="status">
        <option value="">All Status</option>
        <option value="DRAFT">Draft</option>
        <option value="PENDING">Pending</option>
        <option value="PARTIAL">In Review</option>
        <option value="REJECTED">Rejected</option>
        <option value="COMPLETED">Completed</option>
    </select>

    <button type="submit" class="btn-view">Filter</button>

    <a href="request_list.php" class="btn-edit">Reset</a>
</form>


    <!-- Table -->
    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>Request No</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Total Item</th>
                    <th>Status</th>
                    <th>Current Step</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($query && mysqli_num_rows($query) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['request_no']) ?></strong></td>
                            <td><?= htmlspecialchars($row['request_type']) ?></td>
                            <td><?= date('d M Y', strtotime($row['request_date'])) ?></td>
                            <td style="text-align:center;">
                                <?php
                                if($row['request_type'] == 'MATERIAL'){
                                    echo $row['total_material'] . ' Material';
                                } else {
                                    echo $row['total_service'] . ' Service';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                $s     = $row['status'];
                                $label = $labelMap[$s] ?? $s;
                                $cls   = 'status-' . strtolower($s);
                                echo '<span class="badge ' . $cls . '">' . htmlspecialchars($label) . '</span>';
                                ?>
                            </td>
                            <td>
                                <?php
                                $step = $row['current_step'] ?? '-';
                                // Kalau sudah completed, tampil tanda selesai
                                if($row['status'] == 'COMPLETED'){
                                    echo '<span style="color:green;font-weight:bold">✓ Completed</span>';
                                } elseif($row['status'] == 'REJECTED'){
                                    echo '<span style="color:red;font-weight:bold">✗ Rejected</span>';
                                } else {
                                    echo htmlspecialchars($step);
                                }
                                ?>
                            </td>
                            <td>
                                <?php if($row['status'] == 'DRAFT'): ?>
                                    <a href="request.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                                <?php else: ?>
                                    <a href="view_request.php?id=<?= $row['id'] ?>" class="btn-view">View</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center;padding:30px;color:#999;">
                            Belum ada data request untuk akun Anda.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>