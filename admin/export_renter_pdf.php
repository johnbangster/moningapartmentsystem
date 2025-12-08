<?php
session_start();
include('config/dbcon.php');
require('fpdf/fpdf.php');
date_default_timezone_set(timezoneId: 'Asia/Manila');


if(!isset($_GET['renter_id'])){
    die("No renter selected.");
}
$renter_id = intval($_GET['renter_id']);

// Fetch renter info
$query = "SELECT 
            r.id AS renter_id, r.first_name, r.middle_name, r.last_name, r.email, r.contacts,
            a.id AS agreement_id, a.term_months, a.monthly_rent, a.start_date, a.end_date,
            u.name AS unit_name, u.area, u.price, u.status, u.address AS unit_address, u.description AS unit_description,
            t.type_name AS unit_type, t.description AS type_description,
            b.name AS branch_name, b.address AS branch_address
          FROM renters r
          LEFT JOIN rental_agreements a ON a.renter_id = r.id
          LEFT JOIN units u ON a.unit_id = u.id
          LEFT JOIN unit_type t ON u.unit_type_id = t.id
          LEFT JOIN branch b ON u.branch_id = b.id
          WHERE r.id='$renter_id'
          LIMIT 1";
$result = mysqli_query($con, $query);
if(!$result || mysqli_num_rows($result)==0){
    die("Renter not found.");
}
$data = mysqli_fetch_assoc($result);

// Fetch bills
$billQuery = "SELECT * FROM bills WHERE renter_id='$renter_id' ORDER BY due_date ASC";
$bill_run = mysqli_query($con, $billQuery);

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();

// Logo and Address
$pdf->Image('images/logo.png',10,10,30);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Monings Rental Services',0,1,'C');
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,5,'Address: 1438-B M.J.Cuenco Ave, Brgy Mabolo, Cebu City',0,1,'C');
$pdf->Cell(0,5,'Generated on: '.date("F d, Y"),0,1,'C');
$pdf->Ln(10);

// Renter Info
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,6,'Renter Information',0,1);
$pdf->SetFont('Arial','',11);
$pdf->Cell(50,6,'Full Name:',0,0);
$pdf->Cell(0,6,$data['first_name'].' '.$data['middle_name'].' '.$data['last_name'],0,1);
$pdf->Cell(50,6,'Email:',0,0);
$pdf->Cell(0,6,$data['email'],0,1);
$pdf->Cell(50,6,'Contact:',0,0);
$pdf->Cell(0,6,$data['contacts'],0,1);
$pdf->Ln(5);

// Lease Info
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,6,'Lease Agreement',0,1);
$pdf->SetFont('Arial','',11);
if($data['agreement_id']){
    $pdf->Cell(50,6,'Term:',0,0);
    $pdf->Cell(0,6,$data['term_months'].' months',0,1);
    $pdf->Cell(50,6,'Monthly Rent:',0,0);
    $pdf->Cell(0,6,''.number_format($data['monthly_rent'],2),0,1);
    $pdf->Cell(50,6,'Start Date:',0,0);
    $pdf->Cell(0,6,date('F d, Y', strtotime($data['start_date'])),0,1);
    $pdf->Cell(50,6,'End Date:',0,0);
    $pdf->Cell(0,6,date('F d, Y', strtotime($data['end_date'])),0,1);
}else{
    $pdf->Cell(0,6,'No lease agreement found.',0,1);
}
$pdf->Ln(5);

// Unit Info
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,6,'Unit Information',0,1);
$pdf->SetFont('Arial','',11);
if($data['unit_name']){
    $pdf->Cell(50,6,'Unit Name:',0,0);
    $pdf->Cell(0,6,$data['unit_name'],0,1);
    $pdf->Cell(50,6,'Unit Type:',0,0);
    $pdf->Cell(0,6,$data['unit_type'].' ('.$data['type_description'].')',0,1);
    $pdf->Cell(50,6,'Area:',0,0);
    $pdf->Cell(0,6,$data['area'].' sqm',0,1);
    $pdf->Cell(50,6,'Price:',0,0);
    $pdf->Cell(0,6,''.number_format($data['price'],2),0,1);
    $pdf->Cell(50,6,'Branch:',0,0);
    $pdf->Cell(0,6,$data['branch_address'],0,1);
}else{
    $pdf->Cell(0,6,'No unit assigned.',0,1);
}
$pdf->Ln(5);

// Bills Table
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'Billing',0,1); // slightly taller header
$pdf->SetFont('Arial','B',10);

// Adjusted column widths: Reference # wider
$colRef = 45;
$colMonth = 30;
$colDue = 35;
$colAmount = 30;
$colStatus = 30;

// Table Header
$pdf->Cell($colRef,8,'Reference #',1,0,'C');
$pdf->Cell($colMonth,8,'Month',1,0,'C');
$pdf->Cell($colDue,8,'Due Date',1,0,'C');
$pdf->Cell($colAmount,8,'Amount',1,0,'C');
$pdf->Cell($colStatus,8,'Status',1,1,'C'); // 1 = new line

$pdf->SetFont('Arial','',10);
if($bill_run && mysqli_num_rows($bill_run)>0){
    while($bill = mysqli_fetch_assoc($bill_run)){
        $pdf->Cell($colRef,8,$bill['reference_id'],1,0,'C'); // centered text
        $pdf->Cell($colMonth,8,date('F', strtotime($bill['due_date'])),1,0,'C');
        $pdf->Cell($colDue,8,date('M d, Y', strtotime($bill['due_date'])),1,0,'C');
        $pdf->Cell($colAmount,8,number_format($bill['total_amount'],2),1,0,'R'); // right-aligned for amounts
        $pdf->Cell($colStatus,8,strtoupper($bill['status']),1,1,'C'); // new line
    }
}else{
    $pdf->Cell(array_sum([$colRef,$colMonth,$colDue,$colAmount,$colStatus]),8,'No billing records found.',1,1,'C');
}


// Output PDF
$pdf->Output('D','Renter_'.$data['first_name'].'_'.$data['last_name'].'.pdf');
exit;
