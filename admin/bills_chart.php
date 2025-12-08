<?php
session_start();
require('config/dbcon.php');
require __DIR__ . '/fpdf/fpdf.php';

// --- Set timezone to Philippines ---
date_default_timezone_set('Asia/Manila');

// --- Company & Report Info ---
$company_name = "Moning's Rental Services";
$report_title = "Monthly Billing Report";
$logoPath = __DIR__ . '/images/logo.png'; // <-- logo path

// --- Fetch billing summary ---
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

// --- Initialize PDF ---
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// --- HEADER WITH LOGO ---
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 160, 10, 35); // logo on right
}
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 8, utf8_decode($company_name), 0, 1, 'L');
$pdf->SetFont('Arial', '', 13);
$pdf->Cell(0, 8, utf8_decode($report_title), 0, 1, 'L');
$pdf->Ln(10);

// --- TABLE HEADER ---
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(52, 152, 219); // blue header
$pdf->SetTextColor(255, 255, 255); // white text
$pdf->Cell(60, 10, 'Month', 1, 0, 'C', true);
$pdf->Cell(65, 10, utf8_decode('Total Paid (₱)'), 1, 0, 'C', true);
$pdf->Cell(65, 10, utf8_decode('Total Open (₱)'), 1, 1, 'C', true);

// --- TABLE DATA ---
$pdf->SetFont('Arial', '', 11);
$pdf->SetFillColor(240, 240, 240); // alternating row color
$fill = false;
$totalPaidSum = 0;
$totalOpenSum = 0;

if (mysqli_num_rows($res) > 0) {
    while ($r = mysqli_fetch_assoc($res)) {
        $month = date('M Y', strtotime($r['month'] . '-01'));
        $paid = number_format($r['total_paid'], 2);
        $open = number_format($r['total_open'], 2);

        $pdf->Cell(60, 8, $month, 1, 0, 'C', $fill);
        $pdf->Cell(65, 8, utf8_decode("₱$paid"), 1, 0, 'R', $fill);
        $pdf->Cell(65, 8, utf8_decode("₱$open"), 1, 1, 'R', $fill);

        $totalPaidSum += $r['total_paid'];
        $totalOpenSum += $r['total_open'];
        $fill = !$fill; // alternate row color
    }

    // --- TOTAL ROW ---
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetFillColor(41, 128, 185); // darker blue for total
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(60, 10, 'TOTAL', 1, 0, 'C', true);
    $pdf->Cell(65, 10, utf8_decode("₱" . number_format($totalPaidSum, 2)), 1, 0, 'R', true);
    $pdf->Cell(65, 10, utf8_decode("₱" . number_format($totalOpenSum, 2)), 1, 1, 'R', true);
} else {
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(190, 8, 'No billing data available.', 1, 1, 'C', true);
}

// --- FOOTER ---
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 9);
$pdf->Cell(0, 10, 'Generated on: ' . date('F d, Y h:i A'), 0, 0, 'R');

// --- OUTPUT PDF ---
$pdf->Output('D', 'Monthly_Billing_Report_' . date('Ymd_His') . '.pdf');
exit;
?>
