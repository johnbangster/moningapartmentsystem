<?php
session_start();
// Set timezone to Philippines (Asia/Manila)
date_default_timezone_set('Asia/Manila');
require('config/dbcon.php'); 
require __DIR__ . '/fpdf/fpdf.php';

// Company info
$company_name = "Moning's Rental Services";
$report_title = "Monthly Billing Report";
$logoPath = __DIR__ . '/images/logo.png'; //path logo image

// Fetch billing summary
$query = "
    SELECT 
        DATE_FORMAT(COALESCE(due_date, created_at), '%Y-%m') AS month,
        SUM(CASE WHEN LOWER(status) = 'paid' THEN COALESCE(total_amount, (COALESCE(unit_price,0)+COALESCE(addon_total,0))) ELSE 0 END) AS total_paid,
        SUM(CASE WHEN LOWER(status) = 'open' THEN COALESCE(total_amount, (COALESCE(unit_price,0)+COALESCE(addon_total,0))) ELSE 0 END) AS total_open
    FROM bills
    GROUP BY month
    ORDER BY month ASC
";
$res = mysqli_query($con, $query);

// Initialize PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// --- HEADER WITH LOGO ---
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 10, 8, 30); // X=10mm, Y=8mm, width=30mm
}
$pdf->SetXY(45, 10); // move right to leave space for logo
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, $company_name, 0, 1, 'L');
$pdf->SetX(45);
$pdf->SetFont('Arial', '', 13);
$pdf->Cell(0, 8, $report_title, 0, 1, 'L');

$pdf->Ln(12); // space after header

// --- TABLE HEADER ---
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(52, 152, 219);
$pdf->Cell(60, 8, 'Month', 1, 0, 'C', true);
$pdf->Cell(65, 8, 'Total Paid Bill (₱)', 1, 0, 'C', true);
$pdf->Cell(65, 8, 'Total Unpaid Bill ₱', 1, 1, 'C', true);

//  TABLE DATA 
$pdf->SetFont('Arial', '', 11);
$totalPaidSum = 0;
$totalOpenSum = 0;

if (mysqli_num_rows($res) > 0) {
    while ($r = mysqli_fetch_assoc($res)) {
        $month = date('M Y', strtotime($r['month'] . '-01'));
        $paid = number_format($r['total_paid'], 2);
        $open = number_format($r['total_open'], 2);

        $pdf->Cell(60, 8, $month, 1, 0, 'C');
        $pdf->Cell(65, 8, $paid, 1, 0, 'R');
        $pdf->Cell(65, 8, $open, 1, 1, 'R');

        $totalPaidSum += $r['total_paid'];
        $totalOpenSum += $r['total_open'];
    }

    //  TOTAL ROW 
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetFillColor(41, 128, 185);
    $pdf->Cell(60, 8, 'TOTAL', 1, 0, 'C', true);
    $pdf->Cell(65, 8, number_format($totalPaidSum, 2), 1, 0, 'R', true);
    $pdf->Cell(65, 8, number_format($totalOpenSum, 2), 1, 1, 'R', true);
} else {
    $pdf->Cell(190, 8, 'No billing data available.', 1, 1, 'C');
}

//  FOOTER 
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 9);
$pdf->Cell(0, 10, 'Generated on: ' . date('F d, Y h:i A'), 0, 0, 'R');

//  OUTPUT PDF 
$pdf->Output('D', 'Monthly_Billing_Report_' . date('Ymd_His') . '.pdf');
exit;
?>
