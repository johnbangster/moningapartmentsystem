<?php
session_start();
require 'config/dbcon.php';
require('fpdf/fpdf.php');

class ReceiptPDF extends FPDF {

    public $headerLogo = '';
    public $company = '';
    public $address = '';
    public $tin = '';
    public $colWidths = [];

    function header() {

        // ====== LOGO ======
        if(file_exists($this->headerLogo)){
            $this->Image($this->headerLogo, 10, 8, 25);
        }
        $this->SetY(10);

        // ====== COMPANY HEADER ======
        $this->SetFont('Arial','B',14);
        $this->Cell(0,7, $this->company, 0, 1, 'C');

        $this->SetFont('Arial','',10);
        $this->Cell(0,5, $this->address, 0, 1, 'C');

        $this->Cell(0,5, $this->tin, 0, 1, 'C');

        // TITLE
        $this->Ln(2);
        $this->SetFont('Arial','B',13);
        $this->Cell(0,8, 'Employee Cash Payment Reports', 0, 1, 'C');

        $this->Ln(3);

        // ====== TABLE HEADER ======
        $this->SetFont('Arial','B',10);
        $headers = ['Employee', 'Renter', 'Amount', 'Payment Date', 'Billing Month', 'Bill Ref', 'Verified', 'Notes'];

        for($i = 0; $i < count($headers); $i++){
            $this->Cell($this->colWidths[$i], 9, $headers[$i], 1, 0, 'C');
        }
        $this->Ln();
    }

    // For wrapping long text into cells
    function NbLines($w, $txt){
        $cw = &$this->CurrentFont['cw'];
        if($w==0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;

        while ($i < $nb){
            $c = $s[$i];
            if($c == "\n"){
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if($c == ' ')
                $sep = $i;
            $l += $cw[$c] ?? 0;

            if ($l > $wmax){
                if($sep == -1){
                    if($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;

                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }
}

// ACCESS CHECK
if(!isset($_SESSION['auth']) || $_SESSION['auth_role'] !== 'admin'){
    die("Access denied.");
}


// DATABASE QUERY
$sql = "SELECT 
            cr.amount_paid,
            cr.payment_date,
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


// PDF SETTINGS 

$pdf = new ReceiptPDF('L','mm','A4');

$pdf->headerLogo = 'images/logo.png';
$pdf->company = 'Monings Rental Services';
$pdf->address = '1438-B M.J.Cueanco Avenue, Brgy Mabolo, Cebu City';
$pdf->tin = 'TIN: 123-456-789';

// MATCH EXACT HTML TABLE WIDTHS
// $pdf->colWidths = [35, 35, 25, 28, 28, 30, 18, 40];
$pdf->colWidths = [28, 28, 20, 24, 24, 26, 12, 28];

// $weights = [3, 3, 2, 2, 2, 3, 1, 3]; // total = 19 parts
$weights = [
    3,  // Employee
    3,  // Renter
    2,  // Amount
    2,  // Payment Date
    3,  // Billing Month (WIDER)
    3,  // Bill Ref
    1,  // Verified
    3   // Notes (SMALLER)
];

$totalWidth = 277;  // A4 landscape usable width
$part = $totalWidth / 20; // 20 weight units

$pdf->colWidths = [];
foreach ($weights as $w) {
    $pdf->colWidths[] = $w * $part;
}


// $totalWidth = 277;  // A4 landscape usable width
// $part = $totalWidth / array_sum($weights);

// $pdf->colWidths = [];
// foreach($weights as $w) {
//     $pdf->colWidths[] = $w * $part;
// }


// $totalWidth = 190; 
$totalWidth = 277;   // A4 landscape usable width
$part = $totalWidth / array_sum($weights);

$pdf->colWidths = [];
foreach($weights as $w) {
    $pdf->colWidths[] = $w * $part;
}


$pdf->AddPage();
$pdf->SetFont('Arial','',8);

$lineHeight = 5;


//PRINT ROWS 
while($row = mysqli_fetch_assoc($res)){

    $employee = $row['emp_first'].' '.$row['emp_last'];
    $renter   = $row['renter_first'].' '.$row['renter_last'];
    $amount   = number_format($row['amount_paid'],2);
    $verified = $row['verified'] ? 'YES' : 'NO';
    $billing  = $row['billing_month'] ?: 'N/A';
    $ref      = $row['reference_id'] ?: 'N/A';
    $notes    = $row['notes'];

    // Calculate row height
    $nb = max(
        $pdf->NbLines($pdf->colWidths[0], $employee),
        $pdf->NbLines($pdf->colWidths[1], $renter),
        $pdf->NbLines($pdf->colWidths[7], $notes)
    );
    $h = $lineHeight * $nb;

    // Check for page break
    if ($pdf->GetY() + $h > 190){
        $pdf->AddPage();
    }

    // Draw cells
    $x = $pdf->GetX(); $y = $pdf->GetY();

    $pdf->MultiCell($pdf->colWidths[0], $lineHeight, $employee, 1);
    $pdf->SetXY($x + $pdf->colWidths[0], $y);

    $x = $pdf->GetX(); $y = $pdf->GetY();
    $pdf->MultiCell($pdf->colWidths[1], $lineHeight, $renter, 1);
    $pdf->SetXY($x + $pdf->colWidths[1], $y);

    $pdf->Cell($pdf->colWidths[2], $h, $amount, 1);
    $pdf->Cell($pdf->colWidths[3], $h, $row['payment_date'], 1);
    $pdf->Cell($pdf->colWidths[4], $h, $billing, 1);
    $pdf->Cell($pdf->colWidths[5], $h, $ref, 1);
    $pdf->Cell($pdf->colWidths[6], $h, $verified, 1);

    $x = $pdf->GetX(); $y = $pdf->GetY();
    $pdf->MultiCell($pdf->colWidths[7], $lineHeight, $notes, 1);
    $pdf->SetXY($x + $pdf->colWidths[7], $y);

    $pdf->Ln();
}


//OUTPUT PDF 
$pdf->Output('D','cash_reports.pdf');
?>
