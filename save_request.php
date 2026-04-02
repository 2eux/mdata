<?php
session_start();
include 'koneksi.php';

$requestor_id = $_SESSION['user_id'];
$departemen   = $_POST['departemen'];
$request_date = $_POST['request_date'];
$action       = $_POST['action'];      // DRAFT / SUBMIT
$request_type = $_POST['request_type']; // MATERIAL / SERVICE

// ================= GENERATE REQUEST NUMBER =================
$prefix = ($request_type == 'SERVICE') ? 'SRV' : 'MAT';
$year   = date("Y");

$q = mysqli_query($koneksi, "SELECT MAX(id) as max_id FROM request_header");
$d = mysqli_fetch_assoc($q);
$no = ($d['max_id'] ?? 0) + 1;

$request_no = $prefix . "-" . $year . "-" . str_pad($no, 4, "0", STR_PAD_LEFT);

// ================= STATUS =================
if($action == "DRAFT"){
    $status = "DRAFT";
    $step   = "-";
} else {
    $status = "PENDING";
    $step   = "MDM Business Unit";
}

// ================= INSERT HEADER =================
$sqlHeader = "
    INSERT INTO request_header 
    (request_no, requestor_id, departemen, request_type, request_date, status, current_step, submitted_at)
    VALUES 
    ('$request_no', '$requestor_id', '$departemen', '$request_type', '$request_date', '$status', '$step',
    " . ($action == 'SUBMIT' ? "NOW()" : "NULL") . ")
";

if(!mysqli_query($koneksi, $sqlHeader)){
    die("Error Header: " . mysqli_error($koneksi));
}

$request_id = mysqli_insert_id($koneksi);

// ================= INSERT DETAIL =================
if($request_type == 'MATERIAL'){

    $numbering_scheme    = $_POST['numbering_scheme'] ?? [];
    $material_number     = $_POST['material_number'] ?? [];
    $description         = $_POST['description'] ?? [];
    $description_alt     = $_POST['description_alt'] ?? [];
    $uom                 = $_POST['uom'] ?? [];
    $ext_material_group  = $_POST['ext_material_group'] ?? [];
    $material_group      = $_POST['material_group'] ?? [];
    $material_type       = $_POST['material_type'] ?? [];
    $vhs                 = $_POST['vhs'] ?? [];
    $location            = $_POST['location'] ?? [];
    $val_class           = $_POST['val_class'] ?? [];
    $val_category        = $_POST['val_category'] ?? [];
    $purchasing_group    = $_POST['purchasing_group'] ?? [];
    $mrp_controller      = $_POST['mrp_controller'] ?? [];
    $price_control       = $_POST['price_control'] ?? [];
    $profile_make        = $_POST['profile_make'] ?? [];
    $profile_plan        = $_POST['profile_plan'] ?? [];
    $old_material_number = $_POST['old_material_number'] ?? [];
    $egi                 = $_POST['egi'] ?? [];
    $cgi                 = $_POST['cgi'] ?? [];
    $engine_type         = $_POST['engine_type'] ?? [];
    $order_unit          = $_POST['order_unit'] ?? [];
    $net_weight          = $_POST['net_weight'] ?? [];
    $weight_unit         = $_POST['weight_unit'] ?? [];
    $max_stock_level     = $_POST['max_stock_level'] ?? [];
    $min_lot_size        = $_POST['min_lot_size'] ?? [];
    $max_lot_size        = $_POST['max_lot_size'] ?? [];
    $fix_lot_size        = $_POST['fix_lot_size'] ?? [];
    $standard_price      = $_POST['standard_price'] ?? [];
    $moving_price        = $_POST['moving_price'] ?? [];
    $remarks             = $_POST['remarks'] ?? [];

    for($i = 0; $i < count($description); $i++){
        if(empty(trim($description[$i]))) continue;

        $ns  = mysqli_real_escape_string($koneksi, $numbering_scheme[$i] ?? '');
        $mn  = mysqli_real_escape_string($koneksi, $material_number[$i] ?? '');
        $des = mysqli_real_escape_string($koneksi, $description[$i] ?? '');
        $da  = mysqli_real_escape_string($koneksi, $description_alt[$i] ?? '');
        $um  = mysqli_real_escape_string($koneksi, $uom[$i] ?? '');
        $emg = mysqli_real_escape_string($koneksi, $ext_material_group[$i] ?? '');
        $mg  = mysqli_real_escape_string($koneksi, $material_group[$i] ?? '');
        $mt  = mysqli_real_escape_string($koneksi, $material_type[$i] ?? '');
        $vh  = mysqli_real_escape_string($koneksi, $vhs[$i] ?? '');
        $loc = mysqli_real_escape_string($koneksi, $location[$i] ?? '');
        $vc  = mysqli_real_escape_string($koneksi, $val_class[$i] ?? '');
        $vca = mysqli_real_escape_string($koneksi, $val_category[$i] ?? '');
        $pg  = mysqli_real_escape_string($koneksi, $purchasing_group[$i] ?? '');
        $mrp = mysqli_real_escape_string($koneksi, $mrp_controller[$i] ?? '');
        $pc  = mysqli_real_escape_string($koneksi, $price_control[$i] ?? '');
        $pm  = mysqli_real_escape_string($koneksi, $profile_make[$i] ?? '');
        $pp  = mysqli_real_escape_string($koneksi, $profile_plan[$i] ?? '');
        $omn = mysqli_real_escape_string($koneksi, $old_material_number[$i] ?? '');
        $eg  = mysqli_real_escape_string($koneksi, $egi[$i] ?? '');
        $cg  = mysqli_real_escape_string($koneksi, $cgi[$i] ?? '');
        $et  = mysqli_real_escape_string($koneksi, $engine_type[$i] ?? '');
        $ou  = mysqli_real_escape_string($koneksi, $order_unit[$i] ?? '');
        $nw  = !empty($net_weight[$i]) ? (float)$net_weight[$i] : 'NULL';
        $wu  = mysqli_real_escape_string($koneksi, $weight_unit[$i] ?? '');
        $msl = !empty($max_stock_level[$i]) ? (float)$max_stock_level[$i] : 'NULL';
        $mnl = !empty($min_lot_size[$i]) ? (float)$min_lot_size[$i] : 'NULL';
        $mxl = !empty($max_lot_size[$i]) ? (float)$max_lot_size[$i] : 'NULL';
        $fl  = !empty($fix_lot_size[$i]) ? (float)$fix_lot_size[$i] : 'NULL';
        $sp  = !empty($standard_price[$i]) ? (float)$standard_price[$i] : 'NULL';
        $mp  = !empty($moving_price[$i]) ? (float)$moving_price[$i] : 'NULL';
        $rem = mysqli_real_escape_string($koneksi, $remarks[$i] ?? '');

        $sqlDetail = "
            INSERT INTO request_detail_material
            (request_id, numbering_scheme, material_number, description, description_alt,
            uom, ext_material_group, material_group, material_type,
            vhs, location, val_class, val_category,
            purchasing_group, mrp_controller, price_control,
            profile_make, profile_plan, old_material_number,
            egi, cgi, engine_type, order_unit,
            net_weight, weight_unit, max_stock_level,
            min_lot_size, max_lot_size, fix_lot_size,
            standard_price, moving_price, remarks, status, is_forwarded)
            VALUES
            ('$request_id','$ns','$mn','$des','$da',
            '$um','$emg','$mg','$mt',
            '$vh','$loc','$vc','$vca',
            '$pg','$mrp','$pc',
            '$pm','$pp','$omn',
            '$eg','$cg','$et','$ou',
            $nw,'$wu',$msl,
            $mnl,$mxl,$fl,
            $sp,$mp,'$rem','PENDING',0)
        ";

        if(!mysqli_query($koneksi, $sqlDetail)){
            die("Error Detail: " . mysqli_error($koneksi));
        }
    }

} elseif($request_type == 'SERVICE'){

    $description     = $_POST['description'] ?? [];
    $uom             = $_POST['uom'] ?? [];
    $service_group   = $_POST['service_group'] ?? [];
    $service_category= $_POST['service_category'] ?? [];
    $remarks         = $_POST['remarks'] ?? [];

    for($i = 0; $i < count($description); $i++){
        if(empty(trim($description[$i]))) continue;

        $des = mysqli_real_escape_string($koneksi, $description[$i]);
        $um  = mysqli_real_escape_string($koneksi, $uom[$i]);
        $sg  = mysqli_real_escape_string($koneksi, $service_group[$i]);
        $sc  = mysqli_real_escape_string($koneksi, $service_category[$i]);
        $rem = mysqli_real_escape_string($koneksi, $remarks[$i]);

        $sqlService = "
            INSERT INTO request_detail_service
            (request_id, description, uom, service_group, service_category, remarks, status)
            VALUES
            ('$request_id', '$des', '$um', '$sg', '$sc', '$rem', 'PENDING')
        ";

        if(!mysqli_query($koneksi, $sqlService)){
            die("Error Service: " . mysqli_error($koneksi));
        }
    }
}

header("Location: request_list.php");
?>