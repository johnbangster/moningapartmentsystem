=<?php
session_start();
require('fpdf/fpdf.php');
require('config/dbcon.php');

$id = intval($_GET['id']);
$q = mysqli_query($con, "SELECT p.*, r.first_name, r.last_name, b.bill_no 
                         FROM payments p 
                         JOIN renters r ON p.renter_id = r.id 
                         JOIN bills b ON p.bill_id = b.id 
                         WHERE p.id = '$id'");
$receipt = mysqli_fetch_assoc($q);

$cashier = $_SESSION['auth_user']['username'] ?? '';
$logoPath = 'images/logo.png'; //logo here
$companyName = "Moning’s Rental";
$companyAddr = "1438-B M.J. Cuenco, Mabolo Cebu City";
$tin = "TIN: 123-456-789";

$pdf = new FPDF('P', 'mm', array(90, 150)); // small receipt size
$pdf->AddPage();
$pdf->SetMargins(5, 5, 5);

// Logo + Header
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 25, 5, 30); 
    $pdf->Ln(25);
} else {
    $pdf->Ln(5);
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 5, $companyName, 0, 1, 'C');
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 4, $companyAddr, 0, 1, 'C');
$pdf->Cell(0, 4, $tin, 0, 1, 'C');
$pdf->Ln(2);
$pdf->Cell(0, 4, '--- OFFICIAL RECEIPT ---', 0, 1, 'C');
$pdf->Ln(3);

$pdf->SetFont('Arial', '', 9);
$pdf->Cell(35, 5, 'Receipt No:', 0, 0);
$pdf->Cell(0, 5, $receipt['id'], 0, 1);
$pdf->Cell(35, 5, 'Date:', 0, 0);
$pdf->Cell(0, 5, date('M d, Y', strtotime($receipt['payment_date'])), 0, 1);
$pdf->Cell(35, 5, 'Received From:', 0, 0);
$pdf->Cell(0, 5, $receipt['first_name'].' '.$receipt['last_name'], 0, 1);
$pdf->Cell(35, 5, 'Bill Ref:', 0, 0);
$pdf->Cell(0, 5, $receipt['bill_no'], 0, 1);
$pdf->Cell(35, 5, 'Payment Method:', 0, 0);
$pdf->Cell(0, 5, ucfirst($receipt['payment_type']), 0, 1);
$pdf->Cell(35, 5, 'Amount:', 0, 0);
$pdf->Cell(0, 5, '₱'.number_format($receipt['amount'], 2), 0, 1);
$pdf->Ln(5);

// Divider
$pdf->Cell(0, 0, str_repeat('-', 40), 0, 1, 'C');
$pdf->Ln(3);

// Footer with Cashier Info
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 5, 'Cashier:', 0, 1, 'L');
$pdf->Ln(5);
$pdf->Cell(0, 5, $cashier ? strtoupper($cashier) : '_________________', 0, 1, 'C');
$pdf->SetFont('Arial', 'I', 7);
$pdf->Cell(0, 4, 'Signature over printed name', 0, 1, 'C');
$pdf->Ln(8);

$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 4, 'Thank you for your payment!', 0, 1, 'C');
$pdf->Cell(0, 4, 'Please keep this receipt for reference.', 0, 1, 'C');
$pdf->Ln(3);
$pdf->SetFont('Arial', 'I', 6);
$pdf->Cell(0, 4, 'Generated on '.date('M d, Y h:i A'), 0, 1, 'C');

$pdf->Output('I', 'receipt_'.$receipt['id'].'.pdf'); // I = open in browser
?>
