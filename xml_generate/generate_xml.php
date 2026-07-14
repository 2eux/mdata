<?php
error_reporting(0);
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/config/koneksi.php';

/** @var mysqli $koneksi */

if (!isset($_GET['id'])) {
    die('Request ID tidak ditemukan');
}

$request_id = (int)$_GET['id'];

// HEADER REQUEST
$headerQuery = mysqli_query($koneksi, "
    SELECT rh.*, u.nama AS requestor_name
    FROM request_header rh
    LEFT JOIN users u ON rh.requestor_id = u.id
    WHERE rh.id = '$request_id'
");
$header = mysqli_fetch_assoc($headerQuery);

if (!$header) {
    die('Request tidak ditemukan');
}

// DETAIL MATERIAL — hanya yang APPROVED
$detailQuery = mysqli_query($koneksi, "
    SELECT *
    FROM request_detail_material
    WHERE request_id = '$request_id'
    AND status = 'APPROVED'
");
$details = mysqli_fetch_all($detailQuery, MYSQLI_ASSOC);
if (!$details) $details = [];

// ✅ Load helper functions (xe, cellStr, cellNum, cellEmpty)
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/helpers.php';

// ✅ Bersihkan buffer, set header XML Excel
ob_end_clean();
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="sap_material_' . $request_id . '_' . date('Ymd_His') . '.xml"');
header('Cache-Control: no-cache, no-store, must-revalidate');

// GENERATE XML
require $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/workbook_start.php';
require $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/sheets/introduction.php';
require $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/sheets/Field_List.php';
require $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/sheets/Classification.php';
require $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/sheets/request_data.php';
require $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/sheets/req_shortext.php';
require $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/sheets/req_locP.php';
require $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/sheets/additional_fields.php';
require $_SERVER['DOCUMENT_ROOT'] . '/atri/xml_generate/workbook_end.php';
exit();