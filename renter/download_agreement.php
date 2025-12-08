<?php
session_start();
require('../admin/config/dbcon.php');
require('../admin/fpdf/fpdf.php');

if (!isset($_SESSION['auth_user']['renter_id'])) {
    header("Location: ../login.php");
    exit();
}

$renter_id = intval($_SESSION['auth_user']['renter_id']);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Agreement ID is required.");
}
$agreement_id = intval($_GET['id']);

$sql = "
    SELECT  
        a.*,  
        u.name AS unit_name,
        CONCAT(r.first_name, ' ', r.last_name) AS renter_fullname,
        r.address AS renter_address,
        b.name AS branch_name,
        b.address AS branch_address,
        bl.due_date AS next_due_date
    FROM rental_agreements a
    JOIN units u ON u.id = a.unit_id
    JOIN renters r ON r.id = a.renter_id
    LEFT JOIN branch b ON b.id = u.branch_id
    LEFT JOIN bills bl 
           ON bl.renter_id = a.renter_id 
          AND bl.unit_id = a.unit_id
          AND bl.status = 'open'
    WHERE a.id = ? AND a.renter_id = ?
    ORDER BY bl.due_date ASC
    LIMIT 1
";

$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $agreement_id, $renter_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Agreement not found.");
}

$agreement = $result->fetch_assoc();
$stmt->close();

// Initialize FPDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

// HEADER (logo + company name/address) only on first page
$pdf->Image('../images/logo.png', 90, 10, 25, 25); // centered logo
$pdf->SetFont('Arial','B',14);
$pdf->Ln(25); // move below logo
$pdf->Cell(0,6,"Moning's Rental Services",0,1,'C');
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,6,"1438-B M.J.Cuenco Ave, Brgy Mabolo, Cebu City",0,1,'C');
$pdf->Ln(5);

// Agreement title
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,6,"CONTRACT OF LEASE",0,1,'C');
$pdf->Ln(5);

// Content in typewriter font
$pdf->SetFont('Courier','',11);
$pdf->MultiCell(0,6,"This Contract of Lease is made and executed by and between:\n",0,'L');

$pdf->MultiCell(0,6,"LESSOR: PAUL LAURENTE, of legal age, Filipino, with address at 1438-B M.J.Cuenco Avenue Cebu City (hereinafter referred to as the 'Lessor');\n",0,'L');

$pdf->MultiCell(0,6,"LESSEE: ".$agreement['renter_fullname'].", of legal age, Filipino, with address at ".$agreement['renter_address']." (hereinafter referred to as the 'Lessee');\n",0,'L');

$pdf->MultiCell(0,6,"WITNESSETH: That the Lessor hereby agrees to lease unto the Lessee and the Lessee hereby agrees to lease from the Lessor the premises ".$agreement['unit_name']." / ".$agreement['branch_name']." located at ".$agreement['branch_address']." (the 'Leased Premises') under the following terms and conditions:\n",0,'L');

$pdf->Ln(2);
$pdf->MultiCell(0,6,"1. Lease Term: The term of this lease shall be for period of ".$agreement['term_months']." months commencing on ".$agreement['start_date']." and ending on ".$agreement['end_date'].", unless earlier terminated for cause.\n",0,'L');

$pdf->MultiCell(0,6,"2. Monthly Rent: The monthly rental shall be ₱".number_format($agreement['monthly_rent'],2)." payable every ".date('j', strtotime($agreement['next_due_date']))." of the month.\n",0,'L');

$pdf->MultiCell(0,6,"3. Advance Payment: The Lessee shall pay advance rent equivalent to 1 month upon signing this contract, which shall be applied to the last month/s of the lease.\n",0,'L');

$pdf->MultiCell(0,6,"4. Deposit: The Lessee shall pay deposit the amount of ₱".number_format($agreement['monthly_rent'],2)." upon signing this contract, which shall answer for unpaid rentals, damages, or breach of contract.\n",0,'L');

$pdf->Ln(3);
$pdf->SetFont('Courier','B',12);
$pdf->Cell(0,6,"Terms & Conditions",0,1,'L');
$pdf->SetFont('Courier','',11);
$pdf->MultiCell(0,6,htmlspecialchars_decode($agreement['term_conditions']),0,'L');

$pdf->Ln(10);
// SIGNATURE AREA in one line
$pdf->Ln(20); // space before signature

$signature_width = 85; // width of each signature block

// Draw signature lines
$pdf->Cell($signature_width,6,"__________________________",0,0,'C'); // Lessor line
$pdf->Cell(20,6,"",0,0); // space between
$pdf->Cell($signature_width,6,"__________________________",0,1,'C'); // Lessee line

// Names below signature
$pdf->Cell($signature_width,6,"PAUL LAURENTE",0,0,'C'); // Lessor name
$pdf->Cell(20,6,"",0,0); // space
$pdf->Cell($signature_width,6,$agreement['renter_fullname'],0,1,'C'); // Lessee name

// Dates below names
// $pdf->Cell($signature_width,6,date('Y-m-d'),0,0,'C'); // Lessor date
$pdf->Cell(20,6,"",0,0); // space
// $pdf->Cell($signature_width,6,date('Y-m-d'),0,1,'C'); // Lessee date


// OUTPUT PDF
$pdf->Output("I","Agreement-".$agreement_id.".pdf");
exit;
?>
