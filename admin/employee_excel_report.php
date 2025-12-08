<?php
session_start();
require 'config/dbcon.php';
require 'vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// Check admin access
if (!isset($_SESSION['auth']) || $_SESSION['auth_role'] !== 'admin') {
    die("Access denied. Admins only.");
}

// Fetch cash reports + employee + renter + bill info
$sql = "SELECT 
            cr.id AS cash_id,
            cr.amount_paid,
            cr.payment_date,
            cr.receipt_path,
            cr.notes,
            cr.verified,
            u.first_name AS emp_first, 
            u.last_name AS emp_last,
            r.first_name AS renter_first, 
            r.last_name AS renter_last,
            b.billing_month,
            b.reference_id
        FROM cash_reports cr
        INNER JOIN users u ON cr.employee_id = u.id
        INNER JOIN renters r ON cr.renter_id = r.id
        LEFT JOIN bills b ON cr.bill_id = b.id
        ORDER BY cr.created_at DESC";
$res = mysqli_query($con, $sql);

// Create spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Cash Reports');

//company logo
if(file_exists('images/logo.png')){
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setPath('images/logo.png');
    $drawing->setHeight(70);
    $drawing->setCoordinates('A1');
    $drawing->setOffsetX(10);
    $drawing->setWorksheet($sheet);
}

// Company info
$sheet->mergeCells('B1:G1');
$sheet->setCellValue('B1','Your Company Name');
$sheet->mergeCells('B2:G2');
$sheet->setCellValue('B2','123 Main Street, City');
$sheet->mergeCells('B3:G3');
$sheet->setCellValue('B3','TIN: 123-456-789');

// Header row
$header = ['Cash ID','Employee','Renter','Amount Paid (PHP)','Payment Date','Billing Month','Bill Reference','Verified','Notes','Receipt'];
$sheet->fromArray($header,NULL,'A5');

// Add data rows
$rowNum = 6;
while($row = mysqli_fetch_assoc($res)){
    $employee = $row['emp_first'].' '.$row['emp_last'];
    $renter   = $row['renter_first'].' '.$row['renter_last'];
    $amount   = number_format($row['amount_paid'],2);
    $verified = $row['verified'] == 1 ? 'Yes' : 'No';
    $billing  = $row['billing_month'] ?: 'N/A';
    $ref      = $row['reference_id'] ?: 'N/A';
    $notes    = $row['notes'];

    $sheet->setCellValue("A{$rowNum}", $row['cash_id']);
    $sheet->setCellValue("B{$rowNum}", $employee);
    $sheet->setCellValue("C{$rowNum}", $renter);
    $sheet->setCellValue("D{$rowNum}", $amount);
    $sheet->setCellValue("E{$rowNum}", $row['payment_date']);
    $sheet->setCellValue("F{$rowNum}", $billing);
    $sheet->setCellValue("G{$rowNum}", $ref);
    $sheet->setCellValue("H{$rowNum}", $verified);
    $sheet->setCellValue("I{$rowNum}", $notes);

    // Add receipt image if exists
    if(!empty($row['receipt_path']) && file_exists($row['receipt_path'])){
        $ext = strtolower(pathinfo($row['receipt_path'], PATHINFO_EXTENSION));
        if(in_array($ext,['jpg','jpeg','png'])){
            $drawing = new Drawing();
            $drawing->setName('Receipt');
            $drawing->setPath($row['receipt_path']);
            $drawing->setHeight(50);
            $drawing->setCoordinates("J{$rowNum}");
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);
        } else {
            $sheet->setCellValue("J{$rowNum}", 'PDF/File');
        }
    } else {
        $sheet->setCellValue("J{$rowNum}", 'N/A');
    }

    $rowNum++;
}

// Auto size columns (except image column)
foreach(range('A','I') as $col){
    $sheet->getColumnDimension($col)->setAutoSize(true);
}
$sheet->getColumnDimension('J')->setWidth(15); // fixed width for images

// Output Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="cash_reports.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
