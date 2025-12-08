<?php
session_start();
include('config/dbcon.php');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// Get search term
if(!isset($_GET['search']) || empty($_GET['search'])){
    die("No search keyword provided.");
}
$search = mysqli_real_escape_string($con, $_GET['search']);

// Fetch matching units/renters
$query = "
    SELECT 
        r.id AS renter_id, r.first_name, r.middle_name, r.last_name, r.email, r.contacts,
        a.id AS agreement_id, a.term_months, a.monthly_rent, a.start_date, a.end_date,
        u.id AS unit_id, u.name AS unit_name, u.area, u.price, u.status AS unit_status, u.address AS unit_address,
        t.type_name AS unit_type, t.description AS type_description,
        b.name AS branch_name, b.address AS branch_address
    FROM units u
    LEFT JOIN rental_agreements a ON a.unit_id = u.id
    LEFT JOIN renters r ON a.renter_id = r.id
    LEFT JOIN unit_type t ON u.unit_type_id = t.id
    LEFT JOIN branch b ON u.branch_id = b.id
    WHERE 
        r.first_name LIKE '%$search%' 
        OR r.last_name LIKE '%$search%'
        OR r.email LIKE '%$search%'
        OR r.contacts LIKE '%$search%'
        OR u.name LIKE '%$search%'
        OR t.type_name LIKE '%$search%'
        OR u.status LIKE '%$search%'
        OR b.name LIKE '%$search%'
        OR u.address LIKE '%$search%'
    ORDER BY u.id ASC
";

$results = mysqli_query($con, $query);
if(!$results || mysqli_num_rows($results) == 0){
    die("No results found for '$search'.");
}

// Initialize Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

//Add Logo 
$logo = new Drawing();
$logo->setName('Logo');
$logo->setPath('images/logo.png'); // Path to your logo
$logo->setHeight(60); // height in pixels
$logo->setCoordinates('A1'); // start at cell A1
$logo->setOffsetX(5); // small horizontal offset
$logo->setOffsetY(5); // small vertical offset
$logo->setWorksheet($sheet);

// Merge cells for company name & address next to logo 
$sheet->mergeCells('B1:L1');
$sheet->mergeCells('B2:L2');

// Add Company Name & Address 
$sheet->setCellValue('B1', 'Monings Rental Services');
$sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
                                     ->setVertical(Alignment::VERTICAL_CENTER);

$sheet->setCellValue('B2', 'Address: 1438-B M.J.Cuenco Ave, Brgy Mabolo, Cebu City');
$sheet->getStyle('B2')->getFont()->setItalic(true)->setSize(12);
$sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
                                     ->setVertical(Alignment::VERTICAL_CENTER);

// Optional: increase row height so logo & text align properly
$sheet->getRowDimension(1)->setRowHeight(60);
$sheet->getRowDimension(2)->setRowHeight(30);


// Header Row 
$headerRow = 4; // leave some space after logo and address
$sheet->fromArray([
    ['Unit ID','Unit Name','Unit Type','Unit Status','Branch','Renter Name','Email','Phone','Agreement Term','Monthly Rent','Lease Start','Lease End']
], NULL, 'A'.$headerRow);

// Add Data 
$rowNum = $headerRow + 1;

while($row = mysqli_fetch_assoc($results)){
    $sheet->setCellValue("A{$rowNum}", $row['unit_id']);
    $sheet->setCellValue("B{$rowNum}", $row['unit_name']);
    $sheet->setCellValue("C{$rowNum}", $row['unit_type']);
    $sheet->setCellValue("D{$rowNum}", $row['unit_status']);
    $sheet->setCellValue("E{$rowNum}", $row['branch_name']);
    $sheet->setCellValue("F{$rowNum}", $row['first_name'].' '.$row['middle_name'].' '.$row['last_name']);
    $sheet->setCellValue("G{$rowNum}", $row['email']);
    $sheet->setCellValue("H{$rowNum}", $row['contacts']);
    $sheet->setCellValue("I{$rowNum}", $row['agreement_id'] ? $row['term_months'].' months' : 'N/A');
    $sheet->setCellValue("J{$rowNum}", $row['agreement_id'] ? '₱'.number_format($row['monthly_rent'],2) : 'N/A');
    $sheet->setCellValue("K{$rowNum}", $row['agreement_id'] ? date('F d, Y', strtotime($row['start_date'])) : 'N/A');
    $sheet->setCellValue("L{$rowNum}", $row['agreement_id'] ? date('F d, Y', strtotime($row['end_date'])) : 'N/A');

    // Borders
    $sheet->getStyle("A{$rowNum}:L{$rowNum}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    $rowNum++;
}

// Auto-size columns
foreach(range('A','L') as $col){
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Export
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="SearchResults_'.$search.'.xlsx"');
$writer->save('php://output');
exit;
