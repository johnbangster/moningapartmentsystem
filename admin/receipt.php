<?php
require('fpdf/fpdf.php');
require('config/dbcon.php');

if (!isset($_GET['ref'])) {
    die('Reference number missing.');
}

$reference_number = mysqli_real_escape_string($con, $_GET['ref']);

// Fetch payment, renter, and bill details
$query = "
    SELECT p.*, r.first_name, r.last_name, u.name AS unit_name, u.type AS unit_type,
           b.total_amount, b.id AS bill_id
    FROM payments p
    INNER JOIN renters r ON p.renter_id = r.id
    INNER JOIN bills b ON p.bill_id = b.id
    INNER JOIN units u ON b.unit_id = u.id
    WHERE p.reference_number = '$reference_number'
    LIMIT 1
";
$result = mysqli_query($con, $query);
$payment = mysqli_fetch_assoc($result);

if (!$payment) {
    die('Receipt not found.');
}

// Fetch add-ons for this bill
$addonsQuery = mysqli_query($con, "SELECT * FROM bill_addons WHERE bill_id = '{$payment['bill_id']}'");
$addons = [];
$total_addons = 0;
while ($row = mysqli_fetch_assoc($addonsQuery)) {
    $addons[] = $row;
    $total_addons += $row['addon_amount'];
}

class PDF extends FPDF
{
    function Header()
    {
        $this->Image('images/company_logo.png', 10, 8, 25);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'RENTAL PAYMENT RECEIPT', 0, 1, 'C');
        $this->Ln(3);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'This is a system-generated receipt. No signature required.', 0, 0, 'C');
    }

    function Watermark($status)
    {
        if (strtolower($status) == 'paid' || strtolower($status) == 'overpaid') {
            $this->SetFont('Arial', 'B', 60);
            $this->SetTextColor(230, 230, 230);
            $this->RotatedText(40, 160, strtoupper($status), 45);
            $this->SetTextColor(0, 0, 0);
        }
    }

    function RotatedText($x, $y, $txt, $angle)
    {
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1) $x = $this->x;
        if ($y == -1) $y = $this->y;
        if ($this->angle != 0) $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf(
                'q %.5f %.5f %.5f %.5f %.5f %.5f cm 1 0 0 1 %.5f %.5f cm',
                $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy
            ));
        }
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->Watermark($payment['status']);

// Company Info
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 6, 'Your Company Name', 0, 1, 'C');
$pdf->Cell(0, 6, 'TIN: 123-456-789', 0, 1, 'C');
$pdf->Cell(0, 6, 'Address: 123 Main Street, City, Philippines', 0, 1, 'C');
$pdf->Ln(10);

// Reference & Renter Info
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Reference No: ' . $payment['reference_number'], 0, 1, 'R');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(100, 8, 'Renter: ' . $payment['first_name'] . ' ' . $payment['last_name'], 0, 0);
$pdf->Cell(0, 8, 'Unit: ' . $payment['unit_name'], 0, 1);
$pdf->Cell(100, 8, 'Unit Type: ' . ucfirst($payment['unit_type']), 0, 0);
$pdf->Cell(0, 8, 'Method: ' . strtoupper($payment['payment_method']), 0, 1);
$pdf->Cell(100, 8, 'Payment Date: ' . date('F d, Y h:i A', strtotime($payment['payment_date'])), 0, 1);
$pdf->Ln(5);

// Payment Breakdown
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(100, 8, 'Description', 1, 0, 'C');
$pdf->Cell(40, 8, 'Amount (PHP)', 1, 0, 'C');
$pdf->Cell(50, 8, 'Remarks', 1, 1, 'C');

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(100, 8, 'Base Rent / Bill Payment', 1, 0, 'L');
$pdf->Cell(40, 8, number_format($payment['total_amount'], 2), 1, 0, 'R');
$pdf->Cell(50, 8, '-', 1, 1, 'L');

// Add-on items (if any)
if (!empty($addons)) {
    foreach ($addons as $a) {
        $pdf->Cell(100, 8, '  + ' . ucfirst($a['addon_name']), 1, 0, 'L');
        $pdf->Cell(40, 8, number_format($a['addon_amount'], 2), 1, 0, 'R');
        $pdf->Cell(50, 8, 'Included Add-on', 1, 1, 'L');
    }
}

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(100, 8, 'TOTAL DUE:', 1, 0, 'R');
$pdf->Cell(90, 8, number_format($payment['total_amount'] + $total_addons, 2), 1, 1, 'R');

// Payment Details
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(100, 8, 'AMOUNT PAID:', 1, 0, 'R');
$pdf->Cell(90, 8, number_format($payment['amount_paid'], 2), 1, 1, 'R');

// Carry Balance or Overpayment
if ($payment['carry_balance'] != 0) {
    if ($payment['carry_balance'] > 0) {
        $pdf->Cell(100, 8, 'Remaining Balance (Next Bill):', 1, 0, 'R');
        $pdf->Cell(90, 8, number_format($payment['carry_balance'], 2), 1, 1, 'R');
    } else {
        $pdf->Cell(100, 8, 'Overpayment (Deducted Next):', 1, 0, 'R');
        $pdf->Cell(90, 8, number_format(abs($payment['carry_balance']), 2), 1, 1, 'R');
    }
}

$pdf->Ln(10);

// Status
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'PAYMENT STATUS: ' . strtoupper($payment['status']), 0, 1, 'C');
$pdf->Ln(8);

// Footer note
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 8, "Thank you for your payment!\nFor inquiries, please contact the admin office or email support@yourcompany.com.", 0, 'C');

$pdf->Output('I', 'Receipt_' . $payment['reference_number'] . '.pdf');
?>
