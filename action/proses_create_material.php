<?php
session_start();
ob_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/config/koneksi.php';

/** @var mysqli $koneksi */
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/queue_helper.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/db_helper.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/email_helper.php';

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$request_id = (int)$_POST['request_id'];
$action     = $_POST['action'];
$user_id    = (int)$_SESSION['user_id'];
$role       = $_SESSION['role'];

// Ambil info request
$q = mysqli_query($koneksi, "SELECT * FROM request_header WHERE id = '$request_id'");
$header = mysqli_fetch_assoc($q);
$request_type = $header['request_type'];
$current_step = $header['current_step'];

// Guard: blokir jika sudah COMPLETED
if($header['status'] == 'COMPLETED'){
    header("Location: MDM_Global_Review.php?id=$request_id&msg=locked");
    exit();
}

// ================= CREATE MATERIAL =================
if($action == 'create_material' && $request_type == 'MATERIAL'){

    // Ambil semua item APPROVED yang is_forwarded = 1
    $items = mysqli_query($koneksi, "
        SELECT * FROM request_detail_material
        WHERE request_id = '$request_id'
        AND status = 'APPROVED'
        AND is_forwarded = 1
    ");

    // Fallback: jika tidak ada data dengan is_forwarded=1, ambil semua yang APPROVED
    if(mysqli_num_rows($items) == 0){
        $items = mysqli_query($koneksi, "
            SELECT * FROM request_detail_material
            WHERE request_id = '$request_id'
            AND status = 'APPROVED'
        ");
    }

    $created = 0;
    $skipped = 0;
    $material_rows = [];

    while($item = mysqli_fetch_assoc($items)){

        // Cek duplikat
        $cekDup = mysqli_fetch_assoc(mysqli_query($koneksi, "
            SELECT material_number FROM material
            WHERE material_number = '" . mysqli_real_escape_string($koneksi, $item['material_number']) . "'
        "));
        if($cekDup){ $skipped++; continue; }

        $mn  = mysqli_real_escape_string($koneksi, $item['material_number'] ?? '');
        $ns  = mysqli_real_escape_string($koneksi, $item['numbering_scheme'] ?? '');
        $des = mysqli_real_escape_string($koneksi, $item['description'] ?? '');
        $da  = mysqli_real_escape_string($koneksi, $item['description_alt'] ?? '');
        $um  = mysqli_real_escape_string($koneksi, $item['uom'] ?? '');
        $emg = mysqli_real_escape_string($koneksi, $item['ext_material_group'] ?? '');
        $mg  = mysqli_real_escape_string($koneksi, $item['material_group'] ?? '');
        $mt  = mysqli_real_escape_string($koneksi, $item['material_type'] ?? '');
        $vh  = mysqli_real_escape_string($koneksi, $item['vhs'] ?? '');
        $loc = mysqli_real_escape_string($koneksi, $item['location'] ?? '');
        $vc  = mysqli_real_escape_string($koneksi, $item['val_class'] ?? '');
        $vca = mysqli_real_escape_string($koneksi, $item['val_category'] ?? '');
        $pg  = mysqli_real_escape_string($koneksi, $item['purchasing_group'] ?? '');
        $mrp = mysqli_real_escape_string($koneksi, $item['mrp_controller'] ?? '');
        $pc  = mysqli_real_escape_string($koneksi, $item['price_control'] ?? '');
        $pm  = mysqli_real_escape_string($koneksi, $item['profile_make'] ?? '');
        $pp  = mysqli_real_escape_string($koneksi, $item['profile_plan'] ?? '');
        $omn = mysqli_real_escape_string($koneksi, $item['old_material_number'] ?? '');
        $eg  = mysqli_real_escape_string($koneksi, $item['egi'] ?? '');
        $cg  = mysqli_real_escape_string($koneksi, $item['cgi'] ?? '');
        $et  = mysqli_real_escape_string($koneksi, $item['engine_type'] ?? '');
        $ou  = mysqli_real_escape_string($koneksi, $item['order_unit'] ?? '');
        $nw  = !empty($item['net_weight'])      ? (float)$item['net_weight']      : 'NULL';
        $wu  = mysqli_real_escape_string($koneksi, $item['weight_unit'] ?? '');
        $msl = !empty($item['max_stock_level']) ? (float)$item['max_stock_level'] : 'NULL';
        $mnl = !empty($item['min_lot_size'])    ? (float)$item['min_lot_size']    : 'NULL';
        $mxl = !empty($item['max_lot_size'])    ? (float)$item['max_lot_size']    : 'NULL';
        $fl  = !empty($item['fix_lot_size'])    ? (float)$item['fix_lot_size']    : 'NULL';
        $sp  = !empty($item['standard_price'])  ? (float)$item['standard_price']  : 'NULL';
        $mp  = !empty($item['moving_price'])    ? (float)$item['moving_price']    : 'NULL';
        $rem = mysqli_real_escape_string($koneksi, $item['remarks'] ?? '');

        $sql = "
            INSERT INTO material
            (material_number, numbering_scheme, description, description_alt,
            uom, ext_material_group, material_group, material_type,
            vhs, location, val_class, val_category,
            purchasing_group, mrp_controller, price_control,
            profile_make, profile_plan, old_material_number,
            egi, cgi, engine_type, order_unit,
            net_weight, weight_unit, max_stock_level,
            min_lot_size, max_lot_size, fix_lot_size,
            standard_price, moving_price, remarks, source_request_id)
            VALUES
            ('$mn','$ns','$des','$da',
            '$um','$emg','$mg','$mt',
            '$vh','$loc','$vc','$vca',
            '$pg','$mrp','$pc',
            '$pm','$pp','$omn',
            '$eg','$cg','$et','$ou',
            $nw,'$wu',$msl,
            $mnl,$mxl,$fl,
            $sp,$mp,'$rem','$request_id')
        ";

        if(mysqli_query($koneksi, $sql)){
            $created++;
            $material_rows[] = $item;
        }
    }

    // Insert approval record
    mysqli_query($koneksi, "
        INSERT INTO approval (request_id, approved_by, role, step, status, approved_at)
        VALUES ('$request_id', '$user_id', '$role', '$current_step', 'GENERAL_APPROVED', NOW())
    ");

    

    // Update header ke COMPLETED
    mysqli_query($koneksi, "
        UPDATE request_header
        SET status = 'COMPLETED', current_step = 'COMPLETED'
        WHERE id = '$request_id'
    ");

    publishQueue('email_queue', [
    'type'       => 'FINAL_EMAIL',
    'request_id' => $request_id
]);


    $header = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT rh.*, u.nama 
    FROM request_header rh
    JOIN users u ON rh.requestor_id = u.id
    WHERE rh.id = '$request_id'
"));


header('Content-Type: application/vnd.ms-excel');
header(
    'Content-Disposition: attachment; filename="sap_material_' .
    $request_id . '_' . date('Ymd_His') . '.xml"'
);
header('Cache-Control: no-cache, no-store, must-revalidate');

$total_rows = 8 + count($material_rows);

//munculin hasil nya ke xml meski dupplkt 
$detailQuery = mysqli_query($koneksi, "
    SELECT *
    FROM request_detail_material
    WHERE request_id = '$request_id'
    AND status = 'APPROVED'
");

$details = mysqli_fetch_all($detailQuery, MYSQLI_ASSOC);

if(!$details){
    $details = [];
}


include $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/workbook_start.php';

include $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/sheets/introduction.php';
include $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/sheets/Field_List.php';
include $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/sheets/Classification.php';
include $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/sheets/request_data.php';
include $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/sheets/req_locP.php';
include $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/sheets/req_shortext.php';
include $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/sheets/additional_fields.php';

include $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/workbook_end.php';
exit();

}

// ================= CREATE SERVICE =================
if($action == 'create_service' && $request_type == 'SERVICE'){

    $items = mysqli_query($koneksi, "
        SELECT *
        FROM request_detail_service
        WHERE request_id = '$request_id'
        AND status = 'APPROVED'
    ");

    $service_rows = [];

    while($item = mysqli_fetch_assoc($items)){

        // skip duplicate
        $cekDup = mysqli_fetch_assoc(mysqli_query($koneksi, "
            SELECT service_number
            FROM service
            WHERE service_number = '" .
            mysqli_real_escape_string($koneksi, $item['service_number']) .
            "'
        "));

        if($cekDup){
            continue;
        }

        $sn  = mysqli_real_escape_string($koneksi, $item['service_number'] ?? '');
        $des = mysqli_real_escape_string($koneksi, $item['description'] ?? '');
        $um  = mysqli_real_escape_string($koneksi, $item['uom'] ?? '');
        $sg  = mysqli_real_escape_string($koneksi, $item['service_group'] ?? '');
        $sc  = mysqli_real_escape_string($koneksi, $item['service_category'] ?? '');
        $vc  = mysqli_real_escape_string($koneksi, $item['valuation_class'] ?? '');
        $rem = mysqli_real_escape_string($koneksi, $item['remarks'] ?? '');

        $sql = "
            INSERT INTO service
            (
                service_number,
                description,
                uom,
                service_group,
                service_category,
                valuation_class,
                remarks,
                source_request_id
            )
            VALUES
            (
                '$sn',
                '$des',
                '$um',
                '$sg',
                '$sc',
                '$vc',
                '$rem',
                '$request_id'
            )
        ";

        if(mysqli_query($koneksi, $sql)){
            $service_rows[] = $item;
        }
    }

    // approval
    mysqli_query($koneksi, "
        INSERT INTO approval
        (request_id, approved_by, role, step, status, approved_at)
        VALUES
        (
            '$request_id',
            '$user_id',
            '$role',
            '$current_step',
            'GENERAL_APPROVED',
            NOW()
        )
    ");

    // completed
    mysqli_query($koneksi, "
        UPDATE request_header
        SET status = 'COMPLETED',
            current_step = 'COMPLETED'
        WHERE id = '$request_id'
    ");

    publishQueue('email_queue', [
    'type'       => 'FINAL_EMAIL',
    'request_id' => $request_id
]);

    // reload detail
    $detailQuery = mysqli_query($koneksi, "
        SELECT *
        FROM request_detail_service
        WHERE request_id = '$request_id'
        AND status = 'APPROVED'
    ");

    $details = mysqli_fetch_all($detailQuery, MYSQLI_ASSOC);

    if(!$details){
        $details = [];
    }

    ob_clean();

    header('Content-Type: application/vnd.ms-excel');

    header(
        'Content-Disposition: attachment; filename="sap_service_' .
        $request_id . '_' .
        date('Ymd_His') .
        '.xml"'
    );

    require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate_service/helpers.php';

include $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate_service/workbook_start.php';

include $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate_service/sheets/Introduction.php';
include $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate_service/sheets/Field_List.php';
include $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate_service/sheets/itmds_Request_Data.php';
include $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate_service/sheets/Additional_Fields.php';

include $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate_service/workbook_end.php';

    exit();
}


// ================= REJECT PER ITEM =================
if($action == 'reject_item'){
    $detail_id = (int)$_POST['detail_id'];
    $remarks   = mysqli_real_escape_string($koneksi, $_POST['remarks'] ?? '');

    $table = $request_type == 'MATERIAL'
        ? 'request_detail_material'
        : 'request_detail_service';

    // Update status item jadi REJECTED
    mysqli_query($koneksi, "
        UPDATE $table
        SET status = 'REJECTED', remarks = '$remarks'
        WHERE id = $detail_id AND request_id = $request_id
    ");

    // Cek sisa item yang masih APPROVED
    $cekApproved = mysqli_fetch_assoc(mysqli_query($koneksi, "
        SELECT COUNT(*) as total FROM $table
        WHERE request_id = $request_id AND status = 'APPROVED'
    "));

    if($cekApproved['total'] > 0){
        mysqli_query($koneksi, "
            UPDATE request_header SET status = 'PARTIAL'
            WHERE id = $request_id
        ");
    } else {
        mysqli_query($koneksi, "
            UPDATE request_header SET status = 'REJECTED'
            WHERE id = $request_id
        ");
    }

    header("Location: /atri/pages/MDM_Global_Review.php?id=$request_id");
    exit();
}

// ================= REJECT ALL =================
if($action == 'general_reject'){
    $remarks = mysqli_real_escape_string($koneksi, $_POST['remarks'] ?? '');

    mysqli_query($koneksi, "
        INSERT INTO approval (request_id, approved_by, role, step, status, remarks, approved_at)
        VALUES ('$request_id', '$user_id', '$role', '$current_step', 'REJECTED', '$remarks', NOW())
    ");

    mysqli_query($koneksi, "
        UPDATE request_header
        SET status = 'REJECTED', current_step = 'MDM Global'
        WHERE id = $request_id
    ");

    header("Location: /atri/pages/MDM_Global_Review.php?id=$request_id");
    exit();
}

?>