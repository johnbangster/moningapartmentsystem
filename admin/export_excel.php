<?php
session_start();
include('config/dbcon.php');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Initialize Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$rowNum = 1;

//Logo
$drawing = new Drawing();
$drawing->setName('Logo');
$drawing->setDescription('Company Logo');
$drawing->setPath('images/logo.png');
$drawing->setHeight(80);
$drawing->setCoordinates('A'.$rowNum);
$drawing->setWorksheet($sheet);
$rowNum += 7;

//Header Info
$sheet->setCellValue("A{$rowNum}", "MONINGS RENTAL SERVICES");
$sheet->getStyle("A{$rowNum}")->getFont()->setBold(true)->setSize(16);
$sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->mergeCells("A{$rowNum}:H{$rowNum}");
$rowNum++;

$sheet->setCellValue("A{$rowNum}", "Address: 1438-B M.J.Cuenco Ave, Brgy Mabolo, Cebu City");
$sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->mergeCells("A{$rowNum}:H{$rowNum}");
$rowNum++;

$sheet->setCellValue("A{$rowNum}", "Generated on: ".date("F d, Y"));
$sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->mergeCells("A{$rowNum}:H{$rowNum}");
$rowNum += 2;

//Determine Mode
$renter_id = isset($_GET['renter_id']) ? intval($_GET['renter_id']) : null;
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : null;

if($renter_id){
    //Single Renter Mode
    $query = "
        SELECT 
            r.id AS renter_id, r.first_name, r.middle_name, r.last_name, r.email, r.contacts,
            a.id AS agreement_id, a.term_months, a.monthly_rent, a.start_date, a.end_date,
            u.name AS unit_name, u.area, u.price, u.status, u.address,
            t.type_name AS unit_type, t.description AS type_description,
            b.name AS branch_name, b.address AS branch_address
        FROM renters r
        LEFT JOIN rental_agreements a ON a.renter_id = r.id
        LEFT JOIN units u ON a.unit_id = u.id
        LEFT JOIN unit_type t ON u.unit_type_id = t.id
        LEFT JOIN branch b ON u.branch_id = b.id
        WHERE r.id = '$renter_id'
    ";
    $result = mysqli_query($con, $query);
    if(!$result || mysqli_num_rows($result) == 0) die("Renter not found.");
    $row = mysqli_fetch_assoc($result);

    //Renter Info
    $sheet->setCellValue("A{$rowNum}", "Renter Information");
    $sheet->getStyle("A{$rowNum}")->getFont()->setBold(true)->setSize(12);
    $rowNum++;
    $sheet->fromArray([
        ['Full Name',$row['first_name'].' '.$row['middle_name'].' '.$row['last_name']],
        ['Email',$row['email']],
        ['Contact',$row['contacts']]
    ], NULL, "A{$rowNum}");
    $rowNum +=3;

    //Lease Info
    $sheet->setCellValue("A{$rowNum}", "Lease Agreement");
    $sheet->getStyle("A{$rowNum}")->getFont()->setBold(true)->setSize(12);
    $rowNum++;

    if($row['agreement_id']){
        $sheet->fromArray([
            ['Term',$row['term_months'].' months'],
            ['Monthly Rent',"₱".number_format($row['monthly_rent'],2)],
            ['Start Date',date('F d, Y', strtotime($row['start_date']))],
            ['End Date',date('F d, Y', strtotime($row['end_date']))]
        ], NULL, "A{$rowNum}");
        $rowNum += 4;
    }else{
        $sheet->setCellValue("A{$rowNum}", "No lease agreement");
        $rowNum++;
    }

    //Unit Info
    $sheet->setCellValue("A{$rowNum}", "Unit Information");
    $sheet->getStyle("A{$rowNum}")->getFont()->setBold(true)->setSize(12);
    $rowNum++;
    $sheet->fromArray([
        ['Unit Name',$row['unit_name']],
        ['Unit Type',$row['unit_type'].' ('.$row['type_description'].')'],
        ['Area (sqm)',$row['area']],
        ['Price',"₱".number_format($row['price'],2)],
        ['Branch Name',$row['branch_name']],
        ['Branch Address',$row['branch_address']]
    ], NULL, "A{$rowNum}");
    $rowNum += 6;

    //Billing
    $sheet->setCellValue("A{$rowNum}", "Billing");
    $sheet->getStyle("A{$rowNum}")->getFont()->setBold(true)->setSize(12);
    $rowNum++;

    $headers = ['Reference #','Month','Due Date','Amount','Status'];
    $sheet->fromArray($headers, NULL, "A{$rowNum}");
    $sheet->getStyle("A{$rowNum}:E{$rowNum}")->getFont()->setBold(true);
    $sheet->getStyle("A{$rowNum}:E{$rowNum}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');
    $sheet->getStyle("A{$rowNum}:E{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A{$rowNum}:E{$rowNum}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $rowNum++;

    $bill_run = mysqli_query($con,"SELECT reference_id,due_date,total_amount,status FROM bills WHERE renter_id='$renter_id'");
    if($bill_run && mysqli_num_rows($bill_run) > 0){
        while($bill = mysqli_fetch_assoc($bill_run)){
            $sheet->setCellValue("A{$rowNum}", $bill['reference_id']);
            $sheet->setCellValue("B{$rowNum}", date('F', strtotime($bill['due_date'])));
            $sheet->setCellValue("C{$rowNum}", date('M d, Y', strtotime($bill['due_date'])));
            $sheet->setCellValue("D{$rowNum}", "₱".number_format($bill['total_amount'],2));
            $sheet->setCellValue("E{$rowNum}", strtoupper($bill['status']));

            $color = 'FF00B0F0';
            if(strtolower($bill['status'])=='paid') $color='FF92D050';
            elseif(strtolower($bill['status'])=='partial') $color='FFFFC000';

            $sheet->getStyle("E{$rowNum}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($color);
            $sheet->getStyle("A{$rowNum}:E{$rowNum}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $rowNum++;
        }
    } else {
        $sheet->setCellValue("A{$rowNum}", "No bills found");
        $sheet->getStyle("A{$rowNum}:E{$rowNum}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $rowNum++;
    }

    $filename = "Renter_{$row['first_name']}_{$row['last_name']}.xlsx";

} elseif($search){
    //Search Mode
    $searchQuery = "
        SELECT 
            r.id AS renter_id, r.first_name, r.middle_name, r.last_name, r.email, r.contacts,
            a.id AS agreement_id, a.term_months,
            u.id AS unit_id, u.name AS unit_name, u.area, u.price, u.status AS unit_status, u.address AS unit_address,
            t.type_name AS unit_type,
            b.name AS branch_name, b.address AS branch_address
        FROM units u
        LEFT JOIN rental_agreements a ON a.unit_id = u.id
        LEFT JOIN renters r ON a.renter_id = r.id
        LEFT JOIN unit_type t ON u.unit_type_id = t.id
        LEFT JOIN branch b ON u.branch_id = t.id
        WHERE 
            u.name LIKE '%$search%'
            OR t.type_name LIKE '%$search%'
            OR u.status LIKE '%$search%'
            OR u.address LIKE '%$search%'
            OR b.name LIKE '%$search%'
            OR (r.first_name LIKE '%$search%' OR r.last_name LIKE '%$search%' OR r.email LIKE '%$search%' OR r.contacts LIKE '%$search%')
        ORDER BY u.id ASC
    ";
    $results = mysqli_query($con, $searchQuery);
    if(!$results || mysqli_num_rows($results)==0) die("No results found.");

    //Table Header
    $sheet->setCellValue("A{$rowNum}", "Search Results");
    $sheet->getStyle("A{$rowNum}")->getFont()->setBold(true)->setSize(12);
    $rowNum++;

    $headers = ['Unit ID','Unit Name','Unit Type','Status','Branch','Renter Name','Email','Phone','Agreement Term'];
    $sheet->fromArray($headers, NULL, "A{$rowNum}");
    $sheet->getStyle("A{$rowNum}:I{$rowNum}")->getFont()->setBold(true);
    $sheet->getStyle("A{$rowNum}:I{$rowNum}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');
    $sheet->getStyle("A{$rowNum}:I{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A{$rowNum}:I{$rowNum}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $rowNum++;

    while($row = mysqli_fetch_assoc($results)){
        $sheet->fromArray([
            $row['unit_id'],
            $row['unit_name'],
            $row['unit_type'] ?: 'N/A',
            $row['unit_status'] ?: 'N/A',
            $row['branch_name'].' '.$row['branch_address'],
            $row['first_name'] ? $row['first_name'].' '.$row['middle_name'].' '.$row['last_name'] : 'No Renter',
            $row['email'] ?: '-',
            $row['contacts'] ?: '-',
            $row['agreement_id'] ? $row['term_months'].' mos' : 'No Agreement'
        ], NULL, "A{$rowNum}");
        $sheet->getStyle("A{$rowNum}:I{$rowNum}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $rowNum++;
    }

    $filename = "Search_Results.xlsx";

} else {
    die("No renter_id or search query provided.");
}

// Auto size columns
foreach(range('A','I') as $col){
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Output Excel
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
$writer->save('php://output');
exit;
