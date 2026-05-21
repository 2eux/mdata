<?php
error_reporting(E_ALL & ~E_DEPRECATED);

session_start();
if (!isset($_SESSION['user_id'])) {

    header("Location: /atri/Pages/index.php");
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/config/koneksi.php';

/** @var mysqli $koneksi */

require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/queue_helper.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/db_helper.php';

// ================= CSRF TOKEN CHECK =================
$token_post = $_POST['submit_token'] ?? '';
if (
    empty($token_post) ||
    !hash_equals(
        $_SESSION['submit_token'] ?? '',
        $token_post
    )
) {
    header("Location: /atri/Pages/request_list.php");
    exit();
}
unset($_SESSION['submit_token']);

// ================= INPUT =================
$requestor_id    = $_SESSION['user_id'];
$company_id      = $_SESSION['company_id'];
$departemen      = mysqli_real_escape_string($koneksi, $_POST['departemen']    ?? '');
$request_date    = mysqli_real_escape_string($koneksi, $_POST['request_date']  ?? '');
$action          = mysqli_real_escape_string($koneksi, $_POST['action']        ?? '');
$request_type    = mysqli_real_escape_string($koneksi, $_POST['request_type']  ?? '');

mysqli_begin_transaction($koneksi);

try {
$allowedType = ['MATERIAL', 'SERVICE'];

if (!in_array($request_type, $allowedType)) {
    throw new Exception("Invalid request type");
}

$request_id_post = mysqli_real_escape_string($koneksi, $_POST['request_id']    ?? '');


// ================= INSERT / UPDATE HEADER =================
if (!empty($request_id_post)) {
    $qCek = executeOrFail($koneksi, "
        SELECT status
        FROM request_header
        WHERE id = '$request_id_post'
    ");

    $cek = mysqli_fetch_assoc($qCek);

    if ($cek['status'] !== 'DRAFT') {
        throw new Exception("Request sudah diproses, tidak bisa diedit!");
    }

    if ($action === 'DRAFT') {
        $sqlHeader = "
            UPDATE request_header SET
                departemen   = '$departemen',
                request_type = '$request_type',
                request_date = '$request_date',
                status       = 'DRAFT',
                current_step = '-',
                submitted_at = NULL
            WHERE id = '$request_id_post'
        ";
    } else {
        $sqlHeader = "
            UPDATE request_header SET
                departemen   = '$departemen',
                request_type = '$request_type',
                request_date = '$request_date',
                status       = 'PENDING',
                current_step = 'MDM Business Unit',
                submitted_at = NOW()
            WHERE id = '$request_id_post'
        ";
    }

    executeOrFail($koneksi, $sqlHeader);

    $request_id = $request_id_post;

    // Hapus detail lama sebelum re-insert
    executeOrFail(
        $koneksi,
        "DELETE FROM request_detail_material WHERE request_id = '$request_id'"
    );

    executeOrFail(
        $koneksi,
        "DELETE FROM request_detail_service WHERE request_id = '$request_id'"
    );
} else {

    // INSERT header baru
    $status = ($action === 'DRAFT') ? 'DRAFT'   : 'PENDING';
    $step   = ($action === 'DRAFT') ? '-'        : 'MDM Business Unit';
    $subAt  = ($action === 'SUBMIT') ? 'NOW()'   : 'NULL';

    $sqlHeader = "
        INSERT INTO request_header
            (requestor_id, company_id, departemen, request_type, request_date, status, current_step, submitted_at)
        VALUES
            ('$requestor_id', '$company_id', '$departemen', '$request_type', '$request_date', '$status', '$step', $subAt)
    ";

    executeOrFail($koneksi, $sqlHeader);
    $request_id = mysqli_insert_id($koneksi);

    // Generate request_no dari $request_id yang baru di-insert
    $prefix     = ($request_type === 'SERVICE') ? 'SRV' : 'MAT';
    $year       = date("Y");
    $request_no = $prefix . '-' . $year . '-' . str_pad($request_id, 4, '0', STR_PAD_LEFT);

    executeOrFail($koneksi, "
        UPDATE request_header
        SET request_no = '$request_no'
        WHERE id = '$request_id'
    ");
}

// ================= INSERT DETAIL =================
if ($request_type === 'MATERIAL') {

    $numbering_scheme    = $_POST['numbering_scheme']    ?? [];
    $material_number     = $_POST['material_number']     ?? [];
    $description         = $_POST['description']         ?? [];
    $description_alt     = $_POST['description_alt']     ?? [];
    $uom                 = $_POST['uom']                 ?? [];
    $ext_material_group  = $_POST['ext_material_group']  ?? [];
    $material_group      = $_POST['material_group']      ?? [];
    $material_type       = $_POST['material_type']       ?? [];
    $vhs                 = $_POST['vhs']                 ?? [];
    $location            = $_POST['location']            ?? [];
    $val_class           = $_POST['val_class']           ?? [];
    $val_category        = $_POST['val_category']        ?? [];
    $purchasing_group    = $_POST['purchasing_group']    ?? [];
    $mrp_controller      = $_POST['mrp_controller']      ?? [];
    $price_control       = $_POST['price_control']       ?? [];
    $profile_make        = $_POST['profile_make']        ?? [];
    $profile_plan        = $_POST['profile_plan']        ?? [];
    $old_material_number = $_POST['old_material_number'] ?? [];
    $egi                 = $_POST['egi']                 ?? [];
    $cgi                 = $_POST['cgi']                 ?? [];
    $engine_type         = $_POST['engine_type']         ?? [];
    $order_unit          = $_POST['order_unit']          ?? [];
    $net_weight          = $_POST['net_weight']          ?? [];
    $weight_unit         = $_POST['weight_unit']         ?? [];
    $max_stock_level     = $_POST['max_stock_level']     ?? [];
    $min_lot_size        = $_POST['min_lot_size']        ?? [];
    $max_lot_size        = $_POST['max_lot_size']        ?? [];
    $fix_lot_size        = $_POST['fix_lot_size']        ?? [];
    $standard_price      = $_POST['standard_price']      ?? [];
    $moving_price        = $_POST['moving_price']        ?? [];
    $remarks             = $_POST['remarks']             ?? [];
    $image_url           = $_POST['image_url']           ?? [];

    $prefixDate = (new DateTime())->format('YmdHisv');

    $qComp = executeOrFail($koneksi, "
        SELECT company_code
        FROM company
        WHERE id = '$company_id'
    ");

    $compData = mysqli_fetch_assoc($qComp);

    $cCode    = strtoupper($compData['company_code'] ?? 'COMP');

    for ($i = 0; $i < count($description); $i++) {
        if (empty(trim($description[$i]))) continue;

        $mig_counter_val = $prefixDate . '-' . $cCode . '-' . str_pad(($i + 1), 3, '0', STR_PAD_LEFT);

        $ns  = mysqli_real_escape_string($koneksi, $numbering_scheme[$i]    ?? '');
        $mn  = mysqli_real_escape_string($koneksi, $material_number[$i]     ?? '');
        $des = mysqli_real_escape_string($koneksi, $description[$i]         ?? '');
        $da  = mysqli_real_escape_string($koneksi, $description_alt[$i]     ?? '');
        $um  = mysqli_real_escape_string($koneksi, $uom[$i]                 ?? '');
        $emg = mysqli_real_escape_string($koneksi, $ext_material_group[$i]  ?? '');
        $mg  = mysqli_real_escape_string($koneksi, $material_group[$i]      ?? '');
        $mt  = mysqli_real_escape_string($koneksi, $material_type[$i]       ?? '');
        $vh  = mysqli_real_escape_string($koneksi, $vhs[$i]                 ?? '');
        $loc = mysqli_real_escape_string($koneksi, $location[$i]            ?? '');
        $vc  = mysqli_real_escape_string($koneksi, $val_class[$i]           ?? '');
        $vca = mysqli_real_escape_string($koneksi, $val_category[$i]        ?? '');
        $pg  = mysqli_real_escape_string($koneksi, $purchasing_group[$i]    ?? '');
        $mrp = mysqli_real_escape_string($koneksi, $mrp_controller[$i]      ?? '');
        $pc  = mysqli_real_escape_string($koneksi, $price_control[$i]       ?? '');
        $pm  = mysqli_real_escape_string($koneksi, $profile_make[$i]        ?? '');
        $pp  = mysqli_real_escape_string($koneksi, $profile_plan[$i]        ?? '');
        $omn = mysqli_real_escape_string($koneksi, $old_material_number[$i] ?? '');
        $eg  = mysqli_real_escape_string($koneksi, $egi[$i]                 ?? '');
        $cg  = mysqli_real_escape_string($koneksi, $cgi[$i]                 ?? '');
        $et  = mysqli_real_escape_string($koneksi, $engine_type[$i]         ?? '');
        $ou  = mysqli_real_escape_string($koneksi, $order_unit[$i]          ?? '');
        $nw  = !empty($net_weight[$i])     ? (float) $net_weight[$i]     : 'NULL';
        $wu  = mysqli_real_escape_string($koneksi, $weight_unit[$i]         ?? '');
        $msl = !empty($max_stock_level[$i]) ? (float) $max_stock_level[$i] : 'NULL';
        $mnl = !empty($min_lot_size[$i])    ? (float) $min_lot_size[$i]    : 'NULL';
        $mxl = !empty($max_lot_size[$i])    ? (float) $max_lot_size[$i]    : 'NULL';
        $fl  = !empty($fix_lot_size[$i])    ? (float) $fix_lot_size[$i]    : 'NULL';
        $sp  = !empty($standard_price[$i])  ? (float) $standard_price[$i]  : 'NULL';
        $mp  = !empty($moving_price[$i])    ? (float) $moving_price[$i]    : 'NULL';
        $rem = mysqli_real_escape_string($koneksi, $remarks[$i]             ?? '');
        $img = !empty($image_url[$i])
            ? "'" . mysqli_real_escape_string($koneksi, $image_url[$i]) . "'"
            : 'NULL';

        $sqlDetail = "
            INSERT INTO request_detail_material
                (request_id, mig_counter, numbering_scheme, material_number, description, description_alt,
                 uom, ext_material_group, material_group, material_type,
                 vhs, location, val_class, val_category,
                 purchasing_group, mrp_controller, price_control,
                 profile_make, profile_plan, old_material_number,
                 egi, cgi, engine_type, order_unit,
                 net_weight, weight_unit, max_stock_level,
                 min_lot_size, max_lot_size, fix_lot_size,
                 standard_price, moving_price, remarks, image_url, status, is_forwarded)
            VALUES
                ('$request_id', '$mig_counter_val', '$ns', '$mn', '$des', '$da',
                 '$um', '$emg', '$mg', '$mt',
                 '$vh', '$loc', '$vc', '$vca',
                 '$pg', '$mrp', '$pc',
                 '$pm', '$pp', '$omn',
                 '$eg', '$cg', '$et', '$ou',
                 $nw, '$wu', $msl,
                 $mnl, $mxl, $fl,
                 $sp, $mp, '$rem', $img, 'PENDING', 0)
        ";

        executeOrFail($koneksi, $sqlDetail);
    }

} elseif ($request_type === 'SERVICE') {

    $service_number   = $_POST['service_number']   ?? [];
    $description      = $_POST['description']      ?? [];
    $uom              = $_POST['uom']              ?? [];
    $service_group    = $_POST['service_group']    ?? [];
    $service_category = $_POST['service_category'] ?? [];
    $valuation_class  = $_POST['valuation_class']  ?? [];   // fix: field ini ada di form tapi tidak diambil sebelumnya
    $remarks          = $_POST['remarks']          ?? [];
    $image_url        = $_POST['image_url']        ?? [];

    $prefixDate = (new DateTime())->format('YmdHisv');

    $qComp = executeOrFail($koneksi, "
        SELECT company_code
        FROM company
        WHERE id = '$company_id'
    ");

$compData = mysqli_fetch_assoc($qComp);

$cCode = strtoupper($compData['company_code'] ?? 'COMP');

    for ($i = 0; $i < count($description); $i++) {
        if (empty(trim($description[$i]))) continue;


        $mig_counter_val =
        $prefixDate . '-' .
        $cCode . '-' .
        str_pad(($i + 1), 3, '0', STR_PAD_LEFT);

        $sn  = mysqli_real_escape_string($koneksi, $service_number[$i]   ?? '');
        $des = mysqli_real_escape_string($koneksi, $description[$i]      ?? '');
        $um  = mysqli_real_escape_string($koneksi, $uom[$i]              ?? '');
        $sg  = mysqli_real_escape_string($koneksi, $service_group[$i]    ?? '');
        $sc  = mysqli_real_escape_string($koneksi, $service_category[$i] ?? '');
        $vc  = mysqli_real_escape_string($koneksi, $valuation_class[$i]  ?? '');
        $rem = mysqli_real_escape_string($koneksi, $remarks[$i]          ?? '');
        $img = !empty($image_url[$i])
            ? "'" . mysqli_real_escape_string($koneksi, $image_url[$i]) . "'"
            : 'NULL';

        // fix: kolom & value sebelumnya tidak sinkron (mig_counter terisi service_number, semua field geser)
        $sqlService = "
            INSERT INTO request_detail_service
(
    request_id,
    mig_counter,
    service_number,
    description,
    uom,
    service_group,
    service_category,
    valuation_class,
    remarks,
    image_url,
    status
)
VALUES
(
    '$request_id',
    '$mig_counter_val',
    '$sn',
    '$des',
    '$um',
    '$sg',
    '$sc',
    '$vc',
    '$rem',
    $img,
    'PENDING'
)
        ";

       executeOrFail($koneksi, $sqlService);
    }
}

// ================= APPROVAL LOG + EMAIL =================
if ($action === 'SUBMIT') {

    executeOrFail($koneksi, "
        INSERT INTO approval
        (request_id, approved_by, step, status, approved_at)
        VALUES
        ('$request_id', '$requestor_id', 'Request Submitted', 'APPROVED', NOW())
    ");

    $data = [
        'type'       => 'NEXT_STEP',
        'request_id' => $request_id,
        'next_step'  => 'MDM Business Unit',
    ];

    try {

        publishQueue('email_queue', $data);

    } catch (\Throwable $e) {

        error_log($e->getMessage());
    }
}

mysqli_commit($koneksi);

header("Location: /atri/pages/request_list.php");
exit();

} catch (\Throwable $e) {

    mysqli_rollback($koneksi);

    error_log($e->getMessage());

    $_SESSION['error_message'] = "Terjadi kesalahan sistem";

    header("Location: /atri/pages/request_list.php");
    exit();
}