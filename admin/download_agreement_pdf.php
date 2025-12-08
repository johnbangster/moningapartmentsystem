<?php
session_start();
require('config/dbcon.php');
require('fpdf/fpdf.php');

if (!isset($_GET['agreement_id']) || !is_numeric($_GET['agreement_id'])) {
    die("Invalid Agreement ID");
}

$agreement_id = intval($_GET['agreement_id']);

// Fetch Agreement Data
$query = "
    SELECT 
        ra.*, 
        r.first_name, r.last_name, r.middle_name, r.email, r.contacts, 
        u.name AS unit_name
    FROM rental_agreements ra
    JOIN renters r ON ra.renter_id = r.id
    JOIN units u ON ra.unit_id = u.id
    WHERE ra.id = $agreement_id
";

$result = mysqli_query($con, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Agreement not found.");
}

$agreement = mysqli_fetch_assoc($result);

// BEGIN FPDF

$pdf = new FPDF();
$pdf->AddPage();

//  HEADER WITH LOGO 
$logo_path = "admin/images/logo.png"; 
$logo_width = 28;

if (file_exists($logo_path)) {
    $pdf->Image($logo_path, 10, 10, $logo_width);
}

// Move cursor to the right of logo
$pdf->SetXY(10 + $logo_width + 5, 10);

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 8, "Moning's Rental Services", 0, 1);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 6, "Affordable and Secure Rental Units", 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, "Address: Purok 4, Brgy. Guadalupe, Baybay City, Leyte", 0, 1);
$pdf->Cell(0, 5, "Contact: +63 912 345 6789 | Email: moningsrental@gmail.com", 0, 1);

$pdf->Ln(5);

// Divider Line
$pdf->SetDrawColor(120,120,120);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(8);

// ---------- AGREEMENT TITLE ----------
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, "RENTAL AGREEMENT", 0, 1, 'C');

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 6, "Agreement ID: #" . $agreement_id, 0, 1, 'C');
$pdf->Ln(5);


// ---------- RENTER INFORMATION ----------
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, "Renter Information", 0, 1);

$pdf->SetFont('Arial', '', 11);
$fullname = trim($agreement['first_name'] . " " . $agreement['middle_name'] . " " . $agreement['last_name']);

$pdf->Cell(40, 6, "Name:");
$pdf->Cell(100, 6, $fullname, 0, 1);

$pdf->Cell(40, 6, "Contact:");
$pdf->Cell(100, 6, $agreement['contacts'], 0, 1);

$pdf->Cell(40, 6, "Email:");
$pdf->Cell(100, 6, $agreement['email'], 0, 1);


// ---------- UNIT DETAILS ----------
$pdf->Ln(4);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, "Unit & Lease Details", 0, 1);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(40, 6, "Unit:");
$pdf->Cell(100, 6, $agreement['unit_name'], 0, 1);

$pdf->Cell(40, 6, "Lease Term:");
$pdf->Cell(100, 6, $agreement['term_months'] . " months", 0, 1);

$pdf->Cell(40, 6, "Start Date:");
$pdf->Cell(100, 6, date("F j, Y", strtotime($agreement['start_date'])), 0, 1);

$pdf->Cell(40, 6, "End Date:");
$pdf->Cell(100, 6, date("F j, Y", strtotime($agreement['end_date'])), 0, 1);

$pdf->Cell(40, 6, "Monthly Rent:");
$pdf->Cell(100, 6, "₱" . number_format($agreement['monthly_rent'], 2), 0, 1);

$pdf->Cell(40, 6, "Deposit:");
$pdf->Cell(100, 6, "₱" . number_format($agreement['deposit'], 2), 0, 1);

$pdf->Cell(40, 6, "Status:");
$pdf->Cell(100, 6, ucfirst($agreement['status']), 0, 1);


// ---------- TERMS SECTION ----------
$pdf->Ln(6);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, "Agreement Terms", 0, 1);

$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 6, $agreement['term_conditions'], 0, 1);


// ---------- SIGNATURE ----------
if (!empty($agreement['signature_path'])) {
    $pdf->Ln(8);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, "Signature", 0, 1);

    if (file_exists($agreement['signature_path'])) {
        $pdf->Image($agreement['signature_path'], $pdf->GetX(), $pdf->GetY(), 60);
        $pdf->Ln(35);
    } else {
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 6, "(Signature image not found)", 0, 1);
    }
}


// ---------- FOOTER ----------
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 9);
$pdf->Cell(0, 6, "Generated on " . date("F j, Y g:i A"), 0, 1, 'C');


// OUTPUT PDF
$pdf->Output("I", "Rental_Agreement_$agreement_id.pdf");
exit;

