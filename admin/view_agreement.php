<?php
session_start();
include('config/dbcon.php');
require('includes/header.php');

if (!isset($_GET['agreement_id']) || !is_numeric($_GET['agreement_id'])) {
    echo "<div class='alert alert-danger text-center mt-5'>Invalid agreement ID.</div>";
    exit;
}

$agreement_id = intval($_GET['agreement_id']);

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
    echo "<div class='alert alert-warning text-center mt-5'>Agreement not found.</div>";
    exit;
}

$agreement = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Rental Agreement - Moning's Rental Services</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background-color: #f8f9fa;
    font-family: "Courier New", Courier, monospace;
    color: #000;
    margin: 0;
}
.agreement-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 30px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
    font-family: "Courier New", Courier, monospace;
}
.header {
    text-align: center;
    margin-bottom: 30px;
}
.header img {
    width: 90px;
    display: block;
    margin: 0 auto 10px auto;
}
.header h2, .header p {
    font-family: 'Courier New', Courier, monospace;
    margin: 2px 0;
}
.agreement-title {
    text-align: center;
    font-weight: bold;
    font-size: 20px;
    margin-bottom: 20px;
    text-decoration: underline;
}
.info-table {
    width: 100%;
    margin-bottom: 25px;
    font-size: 14px;
}
.info-table td {
    padding: 5px;
    vertical-align: top;
}
.clause {
    margin-bottom: 15px;
    font-size: 14px;
    text-align: justify;
    page-break-inside: avoid;
}
.clause-number {
    font-weight: bold;
    margin-right: 8px;
}
.signature-section {
    margin-top: 60px;
    display: flex;
    justify-content: space-between;
    page-break-inside: avoid;
}
.signature-block {
    text-align: center;
    width: 45%;
}
.signature-block p {
    margin-top: 80px;
    border-top: 1px solid #000;
    padding-top: 5px;
}
.no-print {
    display: block;
}

/* ===== Print Styles ===== */
@media print {
    body, html {
        overflow: visible !important;
        background: white;
        height: auto;
        margin: 0;
        padding: 0;
    }

    .no-print, header, .sidebar, .navbar, .topbar, .scrollbar, footer {
        display: none !important;
    }

    .agreement-container {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 20px !important;
        box-shadow: none;
        border: none;
        font-family: "Courier New", Courier, monospace !important;
    }

    .header img { 
        width: 70px !important; 
    }

    .clause, .info-table td {
        font-size: 12pt !important;
    }
}
</style>
</head>
<body>

<div class="agreement-container" id="agreementContent">
    <!-- Header -->
    <div class="header">
        <img src="images/logo.png" alt="Logo">
        <h2>Moning's Rental Services</h2>
        <p>1438-B M.J.Cuenco Avenue, Brgy Mabolo, Cebu City</p>
        <p>TIN: 123-456-789</p>
    </div>

    <div class="agreement-title">RENTAL AGREEMENT</div>

    <!-- Agreement Information -->
    <table class="info-table">
        <tr>
            <td><strong>Renter:</strong> <?= htmlspecialchars($agreement['first_name'] . ' ' . $agreement['middle_name'] . ' ' . $agreement['last_name']) ?></td>
            <td><strong>Contact:</strong> <?= htmlspecialchars($agreement['contacts']) ?> | <?= htmlspecialchars($agreement['email']) ?></td>
        </tr>
        <tr>
            <td><strong>Unit:</strong> <?= htmlspecialchars($agreement['unit_name']) ?></td>
            <td><strong>Lease Term:</strong> <?= $agreement['term_months'] ?> months</td>
        </tr>
        <tr>
            <td><strong>Start Date:</strong> <?= date("F j, Y", strtotime($agreement['start_date'])) ?></td>
            <td><strong>End Date:</strong> <?= date("F j, Y", strtotime($agreement['end_date'])) ?></td>
        </tr>
        <tr>
            <td><strong>Monthly Rent:</strong> ₱<?= number_format($agreement['monthly_rent'],2) ?></td>
            <td><strong>Deposit:</strong> ₱<?= number_format($agreement['deposit'],2) ?></td>
        </tr>
        <tr>
            <td colspan="2"><strong>Status:</strong> <?= ucfirst($agreement['status']) ?></td>
        </tr>
    </table>

    <!-- Terms and Conditions -->
    <div class="clauses">
        <?php
        // Explode term_conditions by newline
        $terms = explode("\n", $agreement['term_conditions']);
        $count = 1;
        foreach($terms as $term):
            $term = trim($term);
            if($term != ''):
                // Capitalize first letter and ensure it ends with a period
                $term = ucfirst(rtrim($term, '.')) . '.';
        ?>
        <div class="clause">
            <span class="clause-number"><?= $count ?>.</span>
            <span class="clause-text"><?= htmlspecialchars($term) ?></span>
        </div>
        <?php
            $count++;
            endif;
        endforeach;
        ?>
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="signature-block">
            <p>Renter</p>
        </div>
        <div class="signature-block">
            <p>Owner / Authorized Representative</p>
        </div>
    </div>
</div>

<!-- Print Button -->
<div class="text-end my-3 no-print container">
    <button class="btn btn-success" id="printAgreementBtn">
        <i class="fa-solid fa-print me-1"></i> Print
    </button>
</div>

<script>
document.getElementById('printAgreementBtn').addEventListener('click', function() {
    const content = document.getElementById('agreementContent').outerHTML;
    const printWindow = window.open('', '', 'height=900,width=900');
    printWindow.document.write('<html><head><title>Print Agreement</title>');
    printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
    printWindow.document.write('<style>');
    printWindow.document.write('body { background-color: #f8f9fa; margin: 0; padding: 20px; font-family: "Courier New", Courier, monospace; color: #000; }');
    printWindow.document.write('.agreement-container { max-width: 800px; margin: 0 auto; padding: 30px; background-color: #fff; border-radius: 10px; box-shadow: none; }');
    printWindow.document.write('.header { text-align: center; margin-bottom: 30px; }');
    printWindow.document.write('.header img { width: 90px; display: block; margin: 0 auto 10px auto; }');
    printWindow.document.write('.header h2, .header p { font-family: "Courier New", Courier, monospace; margin: 2px 0; text-align: center; }');
    printWindow.document.write('.agreement-title { text-align: center; font-weight: bold; font-size: 20px; margin-bottom: 20px; text-decoration: underline; }');
    printWindow.document.write('.info-table { width: 100%; margin-bottom: 25px; font-size: 14px; }');
    printWindow.document.write('.info-table td { padding: 5px; vertical-align: top; }');
    printWindow.document.write('.clause { margin-bottom: 15px; font-size: 14px; text-align: justify; page-break-inside: avoid; }');
    printWindow.document.write('.clause-number { font-weight: bold; margin-right: 8px; }');
    printWindow.document.write('.signature-section { margin-top: 60px; display: flex; justify-content: space-between; page-break-inside: avoid; }');
    printWindow.document.write('.signature-block { text-align: center; width: 45%; }');
    printWindow.document.write('.signature-block p { margin-top: 80px; border-top: 1px solid #000; padding-top: 5px; }');
    printWindow.document.write('</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
});
</script>

</body>
</html>
