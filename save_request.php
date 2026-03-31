<?php
session_start();
include 'koneksi.php';

$requestor_id = $_SESSION['user_id'];
$departemen = $_POST['departemen'];
$tanggal = $_POST['tanggal'];
$action = $_POST['action']; // draft / submit

// Generate nomor request
$year = date("Y");
$q = mysqli_query($koneksi, "SELECT MAX(id) as max_id FROM request_header");
$d = mysqli_fetch_assoc($q);
$no = $d['max_id'] + 1;
$request_no = "REQ-" . $year . "-" . str_pad($no, 4, "0", STR_PAD_LEFT);

// Tentukan status
if($action == "draft"){
    $status = "Draft";
    $step = "-";
} else {
    $status = "Pending";
    $step = "MDM Business Unit";
}

// Insert header
mysqli_query($koneksi, "
    INSERT INTO request_header (request_no, requestor_id, departemen, request_date, status, current_step)
    VALUES ('$request_no', '$requestor_id', '$departemen', '$tanggal', '$status', '$step')
");

$request_id = mysqli_insert_id($koneksi);

// Insert detail material
$material_number = $_POST['material_number'];
$material_desc = $_POST['material_desc'];
$uom = $_POST['uom'];
$material_group = $_POST['material_group'];
$ext_group = $_POST['ext_group'];
$material_type = $_POST['material_type'];

for($i=0; $i<count($material_desc); $i++){
    mysqli_query($koneksi, "
        INSERT INTO request_detail 
        (request_id, material_number, material_desc, uom, material_group, external_group, material_type)
        VALUES 
        ('$request_id', '$material_number[$i]', '$material_desc[$i]', '$uom[$i]', '$material_group[$i]', '$ext_group[$i]', '$material_type[$i]')
    ");
}

header("Location: request_list.php");
?>