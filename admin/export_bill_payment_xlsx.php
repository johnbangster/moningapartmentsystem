<?php
session_start();
require('config/dbcon.php');
date_default_timezone_set('Asia/Manila');

// SECURITY: only admin or employee
if (!isset($_SESSION['auth'])) {
    die("Unauthorized");
}
$allowed_roles = ['admin', 'employee'];
if (!in_array($_SESSION['auth_role'] ?? '', $allowed_roles)) {
    die("Not authorized");
}

// Use PhpSpreadsheet
require 'vendor/autoload.php';  // adjust path if needed

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Business info
$logo_path = 'images/logo.png';
$business = 'Monings Rental Services';
$address = '1438-B M.J.Cuenco Ave, Brgy Mabolo, Cebu City';
$phone = '09123456789';
$email = 'cuy@gmail.com';

// -----------------------------
// Build filters (same as report page)
// -----------------------------
$where = [];

if (!empty($_GET['search'])) {
    $s = mysqli_real_escape_string($con, trim($_GET['search']));
    $where[] = "(CONCAT(r.first_name, ' ', r.last_name) LIKE '%$s%' 
        OR r.first_name LIKE '%$s%' 
        OR r.last_name LIKE '%$s%' 
        OR u.name LIKE '%$s%' 
        OR b.reference_id LIKE '%$s%' 
        OR b.billing_month LIKE '%$s%' 
        OR b.status LIKE '%$s%' 
        OR p.payment_type LIKE '%$s%' 
        OR b.generated_by LIKE '%$s%' 
        OR b.generated_role LIKE '%$s%')";
}

if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $start = mysqli_real_escape_string($con, $_GET['start_date']);
    $end = mysqli_real_escape_string($con, $_GET['end_date']);
    $where[] = "DATE(b.created_at) BETWEEN '$start' AND '$end'";
}

if (!empty($_GET['month'])) {
    $month = intval($_GET['month']);
    $where[] = "MONTH(b.created_at) = $month";
}

if (!empty($_GET['year'])) {
    $year = intval($_GET['year']);
    $where[] = "YEAR(b.created_at) = $year";
}

if (!empty($_GET['creator_name'])) {
    $creator_name = mysqli_real_escape_string($con, $_GET['creator_name']);
    $where[] = "(b.generated_by LIKE '%$creator_name%' OR b.generated_role LIKE '%$creator_name%')";
}

// Build the WHERE clause
$whereSql = "";
if (count($where) > 0) {
    $whereSql = " AND " . implode(" AND ", $where);
}


// Query data

$sql = "
SELECT
    b.id AS bill_id,
    b.reference_id,
    CONCAT(r.first_name, ' ', r.last_name) AS renter_name,
    u.name AS unit_name,
    b.billing_month,
    b.due_date,
    b.total_amount,
    IFNULL(SUM(p.amount), 0) AS total_paid,
    GROUP_CONCAT(DISTINCT p.payment_type SEPARATOR ', ') AS payment_types,
    b.status AS bill_status,
    b.generated_role,
    b.created_at
FROM bills b
LEFT JOIN payments p ON b.id = p.bill_id
LEFT JOIN renters r ON b.renter_id = r.id
LEFT JOIN units u ON b.unit_id = u.id
WHERE 1=1
$whereSql
GROUP BY b.id
ORDER BY b.created_at DESC
";

$result = mysqli_query($con, $sql);
if (!$result) {
    die("Query error: " . mysqli_error($con));
}


// Create Spreadsheet

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();


// HEADER: logo + business info

$row = 1;

// Insert logo if exists
if (file_exists($logo_path)) {
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setPath($logo_path);
    $drawing->setHeight(60);
    $drawing->setCoordinates('A'.$row);
    $drawing->setOffsetX(5);
    $drawing->setOffsetY(5);
    $drawing->setWorksheet($sheet);
}

// Merge cells for business info
$sheet->mergeCells('B1:L1');
$sheet->mergeCells('B2:L2');
$sheet->mergeCells('B3:L3');
$sheet->mergeCells('B4:L4');

$sheet->setCellValue('B1', $business);
$sheet->setCellValue('B2', $address);
$sheet->setCellValue('B3', "Phone: $phone | Email: $email");
$sheet->setCellValue('B4', "Generated: " . date('F d, Y H:i:s'));

// Style header
$sheet->getStyle('B1:B4')->getFont()->setBold(true);
$sheet->getStyle('B1:B4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$row += 5; // leave space for header


// Table header

$headers = [
    '#','Reference ID','Renter','Unit','Billing Month','Due Date',
    'Total Amount','Amount Paid','Payment Type(s)','Status','Role','Created At'
];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col.$row, $header);
    $sheet->getStyle($col.$row)->getFont()->setBold(true);
    $sheet->getStyle($col.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('666666');
    $sheet->getStyle($col.$row)->getFont()->getColor()->setRGB('FFFFFF');
    $sheet->getStyle($col.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $col++;
}

$row++;


// Fill data

$counter = 1;
$totalBills = 0;
$sumAmount = 0;
$sumPaid = 0;

while ($r = mysqli_fetch_assoc($result)) {
    $col = 'A';
    $sheet->setCellValue($col.$row, $counter); $col++;
    $sheet->setCellValue($col.$row, $r['reference_id']); $col++;
    $sheet->setCellValue($col.$row, $r['renter_name']); $col++;
    $sheet->setCellValue($col.$row, $r['unit_name']); $col++;
    $sheet->setCellValue($col.$row, $r['billing_month']); $col++;
    $sheet->setCellValue($col.$row, $r['due_date']); $col++;
    $sheet->setCellValue($col.$row, floatval($r['total_amount'])); $col++;
    $sheet->setCellValue($col.$row, floatval($r['total_paid'])); $col++;
    $sheet->setCellValue($col.$row, $r['payment_types']); $col++;
    $sheet->setCellValue($col.$row, $r['bill_status']); $col++;
    $sheet->setCellValue($col.$row, $r['generated_role']); $col++;
    $sheet->setCellValue($col.$row, $r['created_at']); $col++;

    $counter++;
    $totalBills++;
    $sumAmount += floatval($r['total_amount']);
    $sumPaid += floatval($r['total_paid']);
    $row++;
}


// Summary rows

$row += 1;
$sheet->setCellValue('A'.$row, 'Summary Totals'); 
$sheet->getStyle('A'.$row)->getFont()->setBold(true);
$row++;
$sheet->setCellValue('A'.$row, 'Total Bills'); $sheet->setCellValue('B'.$row, $totalBills); $row++;
$sheet->setCellValue('A'.$row, 'Total Amount (All Bills)'); $sheet->setCellValue('B'.$row, $sumAmount); $row++;
$sheet->setCellValue('A'.$row, 'Total Paid (All Payments)'); $sheet->setCellValue('B'.$row, $sumPaid); $row++;
$sheet->setCellValue('A'.$row, 'Remaining Balance'); $sheet->setCellValue('B'.$row, $sumAmount - $sumPaid); $row++;

// Auto size columns
foreach (range('A','L') as $colID) {
    $sheet->getColumnDimension($colID)->setAutoSize(true);
}


// Output to browser

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="bill_report_'.date('Ymd_His').'.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
