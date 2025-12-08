<?php
session_start();
include('config/dbcon.php');
require('fpdf/fpdf.php');
date_default_timezone_set('Asia/Manila');


if(!isset($_GET['search']) || empty($_GET['search'])){
    die("No search keyword provided.");
}
$search = mysqli_real_escape_string($con, $_GET['search']);

// Fetch search results
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
if(!$results || mysqli_num_rows($results)==0){
    die("No results found for '$search'.");
}

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();

// Logo and Address
$pdf->Image('images/logo.png',10,10,30);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Monings Rental Services',0,1,'C');
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,5,'Address: 1438-B M.J.Cuenco Ave, Brgy Mabolo, Cebu City',0,1,'C');
$pdf->Cell(0,5,'Generated on: '.date(format: "F d, Y"),0,1,'C');
$pdf->Ln(10);

// Table Header
$pdf->SetFont('Arial','B',10);
$pdf->Cell(15,6,'Unit ID',1);
$pdf->Cell(35,6,'Unit Name',1);
$pdf->Cell(25,6,'Unit Type',1);
$pdf->Cell(20,6,'Status',1);
$pdf->Cell(35,6,'Branch',1);
$pdf->Cell(35,6,'Renter Name',1);
$pdf->Cell(25,6,'Email',1);
$pdf->Ln();

// Table Body
$pdf->SetFont('Arial','',9);
while($row = mysqli_fetch_assoc($results)){
    $pdf->Cell(15,6,$row['unit_id'],1);
    $pdf->Cell(35,6,$row['unit_name'],1);
    $pdf->Cell(25,6,$row['unit_type'] ?: '-',1);
    $pdf->Cell(20,6,$row['unit_status'] ?: '-',1);
    $pdf->Cell(35,6,$row['branch_name'] ?: '-',1);
    $renterName = $row['first_name'] ? $row['first_name'].' '.$row['middle_name'].' '.$row['last_name'] : '-';
    $pdf->Cell(35,6,$renterName,1);
    $pdf->Cell(25,6,$row['email'] ?: '-',1);
    $pdf->Ln();
}

// Output PDF
$pdf->Output('D','SearchResults_'.$search.'.pdf');
exit;
