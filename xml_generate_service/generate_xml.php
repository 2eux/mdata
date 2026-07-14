<?php
error_reporting(0);
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/config/koneksi.php';

/** @var mysqli $koneksi */

if (!isset($_GET['id'])) {
    die('Request ID tidak ditemukan');
}

$request_id = (int) $_GET['id'];


// ================= HEADER =================
$headerQuery = mysqli_query($koneksi, "
    SELECT rh.*, u.nama AS requestor_name
    FROM request_header rh
    LEFT JOIN users u 
        ON rh.requestor_id = u.id
    WHERE rh.id = '$request_id'
");

$header = mysqli_fetch_assoc($headerQuery);

if (!$header) {
    die('Request tidak ditemukan');
}


// ================= DETAIL SERVICE =================
$detailQuery = mysqli_query($koneksi, "
    SELECT *
    FROM request_detail_service
    WHERE request_id = '$request_id'
    AND status = 'APPROVED'
");

$details = mysqli_fetch_all($detailQuery, MYSQLI_ASSOC);

if (!$details) {
    $details = [];
}


// ================= COMPANY CODE =================
$company_id = $header['company_id'];

$qCompany = mysqli_query($koneksi, "
    SELECT company_code
    FROM company
    WHERE id = '$company_id'
");

$company = mysqli_fetch_assoc($qCompany);

$companyCode = strtoupper($company['company_code'] ?? 'COMP');


// ================= MIG COUNTER =================
$prefixDate = date('YmdHis');

for ($i = 0; $i < count($details); $i++) {

    $details[$i]['mig_counter'] =
        $prefixDate . '-' .
        $companyCode . '-' .
        str_pad(($i + 1), 3, '0', STR_PAD_LEFT);
}


// ================= HELPER =================
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate_service/helpers.php';


// ================= OUTPUT XML =================
ob_end_clean();

header('Content-Type: application/vnd.ms-excel');

header(
    'Content-Disposition: attachment; filename="sap_service_' .
    $request_id . '_' .
    date('Ymd_His') .
    '.xml"'
);

header('Cache-Control: no-cache, no-store, must-revalidate');


// ================= GENERATE XML =================
require $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate_service/workbook_start.php';

require $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate_service/sheets/Introduction.php';

require $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate_service/sheets/Field_List.php';

require $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate_service/sheets/Create_Overwrite.php';

require $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate_service/sheets/itmds_Request_Data.php';

require $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate_service/sheets/Additional_Fields.php';

require $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate_service/workbook_end.php';

exit();
?>