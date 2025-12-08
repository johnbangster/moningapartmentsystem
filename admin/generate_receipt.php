<?php
session_start();
require('config/dbcon.php');
require('fpdf/fpdf.php');
date_default_timezone_set('Asia/Manila');

// Get bill ID
$bill_id = intval($_GET['id'] ?? 0);
if ($bill_id <= 0) { die("Invalid Bill ID"); }

// Fetch bill + renter info
$billQuery = "
SELECT 
    b.id AS bill_id,
    b.unit_price,
    b.total_amount,
    COALESCE(b.amount_paid,0) AS amount_paid,
    r.first_name AS renter_first,
    r.last_name AS renter_last,
    b.reference_id,
    b.status
FROM bills b
JOIN renters r ON b.renter_id = r.id
WHERE b.id='$bill_id'
LIMIT 1
";
$billResult = mysqli_query($con, $billQuery);
if (!$billResult) { die("Error in bill query: " . mysqli_error($con)); }

$receipt = mysqli_fetch_assoc($billResult);
if (!$receipt) { die("Receipt data not found."); }

// Fetch add-ons and calculate total add-ons
$addonsQuery = "SELECT name AS description, amount FROM bill_addons WHERE bill_id = '$bill_id'";
$addonsResult = mysqli_query($con, $addonsQuery);
$addons = [];
$addons_total = 0;
if ($addonsResult) {
    while ($addon = mysqli_fetch_assoc($addonsResult)) {
        $addons[] = $addon;
        $addons_total += floatval($addon['amount']);
    }
}

// Total bill including add-ons
$total_bill = floatval($receipt['total_amount']) + $addons_total;

// Total paid
$paymentResult = mysqli_query($con, "SELECT id, amount, payment_type, payment_date FROM payments WHERE bill_id='$bill_id' ORDER BY payment_date ASC");
$payments = []; $total_paid = 0;
while ($p = mysqli_fetch_assoc($paymentResult)) {
    $total_paid += floatval($p['amount']);
    $payments[] = $p;
}

// Calculate remaining and overpaid
$remaining = max($total_bill - $total_paid, 0);
$overpaid = max($total_paid - $total_bill, 0);

if ($overpaid > 0) {
    $status = 'Overpaid';
} elseif ($remaining == 0) {
    $status = 'Paid';
} else {
    $status = 'Partial';
}

// Update bill
mysqli_query($con, "
    UPDATE bills 
    SET status='$status', carry_balance=0, amount_paid='$total_paid'
    WHERE id='$bill_id'
");

// Generate PDF
class ReceiptPDF extends FPDF {
    function headerSection($logo, $company, $address, $tin) {
        if (file_exists($logo)) { $this->Image($logo, 10, 5, 25); $this->Ln(20); } else { $this->Ln(5); }
        $this->SetFont('Arial','B',11); 
        $this->Cell(0,5,$company,0,1,'C');
        $this->SetFont('Arial','',8); 
        $this->Cell(0,4,$address,0,1,'C'); 
        $this->Cell(0,4,$tin,0,1,'C');
        $this->Ln(2);
    }
}

$pdf = new ReceiptPDF('L','mm',array(250,150)); // Landscape
$pdf->AddPage();
$pdf->SetMargins(8,8,8);
$pdf->headerSection('images/logo.png', "Monings Rental", "1438-B M.J. Cuenco, Mabolo Cebu City", "TIN: 123-456-789");

// Header Info
$pdf->SetFont('Arial','',9);
$pdf->Cell(35,5,'Reference #:',0,0); $pdf->Cell(0,5,$receipt['reference_id'],0,1);
$pdf->Cell(35,5,'Renter:',0,0); $pdf->Cell(0,5,$receipt['renter_first'].' '.$receipt['renter_last'],0,1);
$pdf->Cell(35,5,'Status:',0,0); $pdf->Cell(0,5,$status,0,1);

// Description Table
$pdf->Ln(2);
$pdf->SetFont('Arial','B',9);
$colDesc = 140; $colAmount = 40;
$pdf->Cell($colDesc,5,'Description',1,0,'C');
$pdf->Cell($colAmount,5,'Amount',1,1,'C');
$pdf->SetFont('Arial','',9);

// Total Bill row
$pdf->Cell($colDesc,5,'Total Bill',1,0);
$pdf->Cell($colAmount,5,number_format($receipt['total_amount'],2),1,1,'R');

// Unit Price
$pdf->Cell($colDesc,5,'Unit Price',1,0);
$pdf->Cell($colAmount,5,number_format($receipt['unit_price'],2),1,1,'R');

// Add-ons
foreach($addons as $addon) {
    $pdf->Cell($colDesc,5,'- '.$addon['description'],1,0);
    $pdf->Cell($colAmount,5,number_format($addon['amount'],2),1,1,'R');
}

// Summary
$pdf->Cell($colDesc,5,'Total Add-on',1,0); $pdf->Cell($colAmount,5,number_format($addons_total,2),1,1,'R');
$pdf->Cell($colDesc,5,'Total Paid',1,0); $pdf->Cell($colAmount,5,number_format($total_paid,2),1,1,'R');
$pdf->Cell($colDesc,5,'Remaining Balance',1,0); $pdf->Cell($colAmount,5,number_format($remaining,2),1,1,'R');
$pdf->Cell($colDesc,5,'Overpaid',1,0); $pdf->Cell($colAmount,5,number_format($overpaid,2),1,1,'R');

// Payment History (without balance)
$pdf->Ln(2);
$pdf->SetFont('Arial','B',9); $pdf->Cell(0,5,'Payment History:',0,1);
$pdf->SetFont('Arial','',8);
if (!empty($payments)) {
    foreach($payments as $p) {
        $pdf->Cell(0,4,date('M d, Y', strtotime($p['payment_date']))." - ".number_format($p['amount'],2)." (".$p['payment_type'].")",0,1);
    }
} else {
    $pdf->Cell(0,4,'No payment history found.',0,1);
}

// Footer
$pdf->Ln(2); $pdf->Cell(0,0,str_repeat('-',100),0,1,'C'); $pdf->Ln(3);
$pdf->SetFont('Arial','I',8);
$pdf->Cell(0,4,'This is a computer-generated receipt and does not require a signature.',0,1,'C');
$pdf->Cell(0,4,'Generated on '.date('M d, Y h:i A'),0,1,'C');

$pdf->Output('I','receipt_bill_'.$receipt['bill_id'].'.pdf');
exit;
?>
