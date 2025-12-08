<?php
require('config/dbcon.php');
require('fpdf/fpdf.php');
date_default_timezone_set('Asia/Manila');

// Get search keyword from request
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$search_sql = '';
if (!empty($search)) {
    $search = mysqli_real_escape_string($con, $search);
    $search_sql = "
        WHERE 
            CONCAT(r.first_name, ' ', r.last_name) LIKE '%$search%' OR
            u.name LIKE '%$search%' OR
            b.status LIKE '%$search%' OR
            b.total_amount LIKE '%$search%' OR
            COALESCE(p.amount, 0) LIKE '%$search%' OR
            b.created_at LIKE '%$search%'
    ";
}

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Bill and Payment Report', 0, 1, 'C');
        $this->Ln(5);

        $this->SetFont('Arial', 'B', 10);
        $this->Cell(40, 8, 'Renter Name', 1, 0, 'C');
        $this->Cell(30, 8, 'Unit', 1, 0, 'C');
        $this->Cell(25, 8, 'Bill', 1, 0, 'C');
        $this->Cell(25, 8, 'Paid', 1, 0, 'C');
        $this->Cell(25, 8, 'Status', 1, 0, 'C');
        $this->Cell(45, 8, 'Created At', 1, 1, 'C');
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->PageNo().' | Generated: '.date('Y-m-d H:i'), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);


$query = "
    SELECT 
        CONCAT(r.first_name, ' ', r.last_name) AS renter_name,
        u.name AS unit_name, 
        b.total_amount AS bill_amount,
        IFNULL(SUM(p.amount), 0) AS amount_paid,
        b.status,
        b.created_at
    FROM bills b
    LEFT JOIN renters r ON b.renter_id = r.id
    LEFT JOIN units u ON b.unit_id = u.id
    LEFT JOIN payments p ON b.id = p.bill_id
    $search_sql
    GROUP BY b.id
    ORDER BY b.created_at DESC
";

// Debug line — helpful during testing
// echo "<pre>$query</pre>"; exit;

$result = mysqli_query($con, $query);

// Check for query error
if (!$result) {
    die("SQL Error: " . mysqli_error($con));
}

//Generate PDF
if (mysqli_num_rows($result) == 0) {
    $pdf->Cell(0, 10, 'No records found for your search.', 1, 1, 'C');
} else {
    while ($row = mysqli_fetch_assoc($result)) {
        $renter_name = $row['renter_name'] ?: '—';
        $unit_name = $row['unit_name'] ?: '—';
        $bill = number_format($row['bill_amount'], 2);
        $paid = number_format($row['amount_paid'], 2);
        $status = ucfirst($row['status']);
        $date = date('Y-m-d', strtotime($row['created_at']));

        $pdf->Cell(40, 8, $renter_name, 1, 0, 'C');
        $pdf->Cell(30, 8, $unit_name, 1, 0, 'C');
        $pdf->Cell(25, 8, $bill, 1, 0, 'C');
        $pdf->Cell(25, 8, $paid, 1, 0, 'C');
        $pdf->Cell(25, 8, $status, 1, 0, 'C');
        $pdf->Cell(45, 8, $date, 1, 1, 'C');
    }
}

$pdf->Output('D', 'bill_report.pdf');
exit;
?>
