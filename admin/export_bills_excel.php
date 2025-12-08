<?php
// admin/export_bills_excel.php
session_start();
require('config/dbcon.php');

// PhpSpreadsheet autoload (requires composer install in project root)
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Query same aggregation
$query = "
    SELECT 
        DATE_FORMAT(COALESCE(due_date, created_at), '%Y-%m') AS month,
        SUM(CASE WHEN LOWER(status) = 'paid' THEN COALESCE(total_amount, (COALESCE(unit_price,0)+COALESCE(addon_total,0))) ELSE 0 END) AS total_paid,
        SUM(CASE WHEN LOWER(status) = 'open' THEN COALESCE(total_amount, (COALESCE(unit_price,0)+COALESCE(addon_total,0))) ELSE 0 END) AS total_open
    FROM bills
    GROUP BY month
    ORDER BY month ASC
";
$res = mysqli_query($con, $query);

// Build spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Monthly Billing');

// Header
$sheet->setCellValue('A1', 'Month');
$sheet->setCellValue('B1', 'Total Paid (₱)');
$sheet->setCellValue('C1', 'Total Open (₱)');

$row = 2;
while ($r = mysqli_fetch_assoc($res)) {
    $sheet->setCellValue("A{$row}", date('M Y', strtotime($r['month'] . '-01')));
    $sheet->setCellValue("B{$row}", (float)$r['total_paid']);
    $sheet->setCellValue("C{$row}", (float)$r['total_open']);
    $row++;
}

// Formatting: auto-size
foreach (['A','B','C'] as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

// Output to browser
$filename = 'Monthly_Billing_Report_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
