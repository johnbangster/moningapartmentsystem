<?php
require('vendor/autoload.php');
require('config/dbcon.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle('Monthly Billing Report');
$sheet->setCellValue('A1','Month');
$sheet->setCellValue('B1','Total Paid (₱)');
$sheet->setCellValue('C1','Total Open (₱)');

$query = "
    SELECT 
        DATE_FORMAT(COALESCE(due_date, created_at), '%Y-%m') AS month,
        SUM(CASE WHEN LOWER(status) = 'paid' THEN (COALESCE(unit_price,0) + COALESCE(addon_total,0)) ELSE 0 END) AS total_paid,
        SUM(CASE WHEN LOWER(status) = 'open' THEN (COALESCE(unit_price,0) + COALESCE(addon_total,0)) ELSE 0 END) AS total_open
    FROM bills
    GROUP BY month
    ORDER BY month ASC
";

$result = mysqli_query($con, $query);
$rowNum = 2;

while($row = mysqli_fetch_assoc($result)){
    $month = date('M Y', strtotime($row['month'].'-01'));
    $sheet->setCellValue("A{$rowNum}", $month);
    $sheet->setCellValue("B{$rowNum}", $row['total_paid']);
    $sheet->setCellValue("C{$rowNum}", $row['total_open']);
    $rowNum++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Billing_Report.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
