

<?php
session_start();
require('../admin/config/dbcon.php');
require('../admin/fpdf/fpdf.php');
date_default_timezone_set(timezoneId: 'Asia/Manila');

// Get bill ID
$bill_id = intval($_GET['bill_id'] ?? 0);
if ($bill_id <= 0) { die("Invalid Bill ID"); }
$cashier = $_SESSION['auth_user']['username'] ?? '';

//Fetch bill + renter + summary
$billQuery = "
    SELECT 
        b.id AS bill_id,
        b.total_amount,
        COALESCE(b.carry_balance, 0) AS carry_balance,
        (b.total_amount - COALESCE(SUM(p.amount), 0)) AS balance,
        COALESCE(SUM(p.amount), 0) AS total_paid,
        r.first_name, r.last_name,
        b.reference_id
    FROM bills b
    JOIN renters r ON b.renter_id = r.id
    LEFT JOIN payments p ON p.bill_id = b.id
    WHERE b.id = '$bill_id'
    GROUP BY b.id
";

$billResult = mysqli_query($con, $billQuery);
if (!$billResult) { die("Error in bill query: " . mysqli_error($con)); }

$receipt = mysqli_fetch_assoc($billResult);
if (!$receipt) { die("No bill found."); }

//Fetch add-ons (check for SQL error!)
$addonsResult = mysqli_query($con, "SELECT name AS description, amount FROM bill_addons WHERE bill_id = '$bill_id'");
if ($addonsResult === false) {
    die("Error in add-ons query: " . mysqli_error($con));
}

//Fetch payment history
$paymentResult = mysqli_query($con, "
    SELECT payment_type, amount, payment_date
    FROM payments 
    WHERE bill_id='$bill_id'
    ORDER BY payment_date ASC
");
if ($paymentResult === false) {
    die("Error in payments query: " . mysqli_error($con));
}

class ReceiptPDF extends FPDF {
    function headerSection($logo, $company, $address, $tin) {
        if (file_exists($logo)) {
            $this->Image($logo, 25, 5, 25);
            $this->Ln(25);
        } else {
            $this->Ln(5);
        }
        $this->SetFont('Arial','B',10);
        $this->Cell(0,5,$company,0,1,'C');
        $this->SetFont('Arial','',8);
        $this->Cell(0,4,$address,0,1,'C');
        $this->Cell(0,4,$tin,0,1,'C');
        $this->Ln(3);
        $this->Cell(0,4,'--- ONLINE E-RECEIPT ---',0,1,'C');
        $this->Ln(3);
    }
}

$pdf = new ReceiptPDF('P','mm',array(90,200));
$pdf->AddPage();
$pdf->SetMargins(5,5,5);

$pdf->headerSection(
    '../admin/images/logo.png',
    "Monings Rental",
    "1438-B M.J. Cuenco, Mabolo Cebu City",
    "TIN: 123-456-789"
);

$pdf->SetFont('Arial','',8);
$pdf->Cell(35,5,'Reference #:',0,0);
$pdf->Cell(0,5,$receipt['reference_id'],0,1);
$pdf->Cell(35,5,'Renter:',0,0);
$pdf->Cell(0,5,$receipt['first_name'].' '.$receipt['last_name'],0,1);
$pdf->Ln(2);

// Description Table
$pdf->SetFont('Arial','B',9);
$pdf->Cell(40,5,'Description',0,0);
$pdf->Cell(0,5,'Amount',0,1,'R');
$pdf->SetFont('Arial','',9);
$pdf->Cell(40,5,'Base Rent',0,0);
$pdf->Cell(0,5,number_format($receipt['total_amount'],2),0,1,'R');

// Add-ons
if (mysqli_num_rows($addonsResult) > 0) {
    while ($addon = mysqli_fetch_assoc($addonsResult)) {
        $pdf->Cell(40,5,'- '.$addon['description'],0,0);
        $pdf->Cell(0,5,number_format($addon['amount'],2),0,1,'R');
    }
}

$pdf->Ln(2);
$pdf->SetFont('Arial','B',size: 9);
$pdf->Cell(40,5,'Total Paid',0,0);
$pdf->Cell(0,5,number_format($receipt['total_paid'],2),0,1,'R');
$pdf->Cell(40,5,'Remaining Balance',0,0);
$pdf->Cell(0,5,number_format($receipt['carry_balance'],2),0,1,'R');
$pdf->Cell(40,5,'Overpaid',0,0);
$pdf->Cell(0,5,number_format($receipt['balance'],2),0,1,'R');

$pdf->Ln(3);
$pdf->Cell(0,0,str_repeat('-',40),0,1,'C');
$pdf->Ln(3);

// Payment History
$pdf->SetFont('Arial','B',9);
$pdf->Cell(0,5,'Payment History:',0,1);
$pdf->SetFont('Arial','',8);

if (mysqli_num_rows($paymentResult) > 0) {
    while ($p = mysqli_fetch_assoc($paymentResult)) {
        $pdf->Cell(0,4,
            date('M d, Y', strtotime($p['payment_date']))." - ".
            number_format($p['amount'],2)." (".ucfirst($p['payment_type']).")",
        0,1);
    }
} else {
    $pdf->Cell(0,4,'No payment history found.',0,1);
}

// $pdf->Ln(10);

$pdf->SetFont('Arial','I',7);
$pdf->Cell(0,4,'This is a computer-generated receipt and does not require a signature.',0,1,'C');
$pdf->Cell(0, 4, 'Thank you for your payment!', 0, 1, 'C');
$pdf->Cell(0,4,'Generated on '.date('M d, Y h:i A'),0,1,'C');
// Footer with Cashier Info
$pdf->Cell(0, 5, 'Cashier:', 0, 1, 'L');
// $pdf->Ln(5);
// $pdf->Cell(0, 5, $cashier ? strtoupper($cashier) : '_________________', 0, 1, 'C');

$pdf->Output('I','receipt_bill_'.$receipt['bill_id'].'.pdf');
exit;
