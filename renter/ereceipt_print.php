<?php
require('../admin/config/dbcon.php');
require('includes/header.php');
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
body {
    background-color: #f4f6f9;
    font-family: 'Poppins', sans-serif;
}
.receipt-container {
    max-width: 850px;
    margin: 40px auto;
    background: #fff;
    padding: 40px 50px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    position: relative;
}
.header-area {
    border-bottom: 3px solid #0d6efd;
    padding-bottom: 20px;
    margin-bottom: 30px;
}
.header-area img {
    width: 75px;
    height: auto;
}
.header-area h4 {
    margin: 0;
    font-weight: 700;
    color: #0d6efd;
}
.header-area small {
    color: #555;
}
.receipt-title {
    text-align: center;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 20px 0 30px;
    color: #333;
}
.watermark {
    position: absolute;
    top: 40%;
    left: 20%;
    opacity: 0.07;
    font-size: 120px;
    color: #000;
    transform: rotate(-25deg);
    pointer-events: none;
    z-index: 0;
}
.table th {
    background-color: #0d6efd;
    color: #fff;
    font-weight: 600;
}
.table td {
    vertical-align: middle;
}
.total-row {
    background-color: #f8f9fa;
    font-weight: bold;
    border-top: 2px solid #000;
}
.signature-area {
    margin-top: 60px;
}
.signature-line {
    border-top: 1px solid #000;
    width: 250px;
    margin: 0 auto;
}
.signature-text {
    text-align: center;
    font-size: 14px;
    color: #333;
    margin-top: 5px;
}
.footer-note {
    border-top: 1px dashed #aaa;
    margin-top: 50px;
    padding-top: 10px;
    font-size: 13px;
    text-align: center;
    color: #555;
}
.btn-toolbar {
    margin-bottom: 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
@media print {
    body { background: none; }
    .no-print { display: none !important; }
    .receipt-container {
        box-shadow: none !important;
        border: none !important;
        margin: 0;
        padding: 0 30px;
    }
}
</style>

<div class="receipt-container">
<?php
include('message.php');

if (isset($_GET['payment_id']) && is_numeric($_GET['payment_id'])) {
    $payment_id = intval($_GET['payment_id']);
    $query = "
        SELECT p.*, 
            b.reference_id, b.billing_month, b.due_date, b.total_amount, b.addon_total, b.late_fee,
            r.first_name, r.last_name, r.email, r.contacts
        FROM payments p
        JOIN bills b ON p.bill_id = b.id
        JOIN renters r ON p.renter_id = r.id
        WHERE p.id = $payment_id
        LIMIT 1
    ";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $status = strtolower(trim($data['status']));
        $is_pending = ($status === 'pending');
        $is_confirmed = ($status === 'confirmed');

        // $status = strtolower($data['status']);
        // $is_pending = ($status === 'awaiting_confirmation');
?>

    <div class="btn-toolbar no-print">
        <button class="btn btn-success btn-sm" onclick="ereceiptPrint()" <?= $is_pending ? 'disabled' : '' ?>>
            <i class="fa-solid fa-print"></i> Print
        </button>
        <button class="btn btn-primary btn-sm" onclick="downloadReceiptPDF()" <?= $is_pending ? 'disabled' : '' ?>>
            <i class="fa-solid fa-file-pdf"></i> PDF
        </button>
        <a href="mybills.php" class="btn btn-danger btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>

    <div id="ereceiptPrint">
        <div class="header-area d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <img src="../images/logo.png" alt="Logo">
                <div class="ms-3">
                    <h4>Monings Rental Services</h4>
                    <small>1438-B M.J. Cuenco Avenue, Mabolo, Cebu City</small><br>
                    <small>Email: moningsrental@gmail.com | Phone: (032) 456-7890</small>
                </div>
            </div>
            <div>
                <h5 class="text-end text-muted mb-0">E-Receipt</h5>
                <small class="text-end d-block">TIN: 402-123-456-000</small>
            </div>
        </div>

        <div class="watermark"><?= $is_pending ? 'PENDING' : 'PAID' ?></div>
        <h5 class="receipt-title"><?= $is_pending ? 'Payment Pending Confirmation' : 'Payment Receipt' ?></h5>

        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="fw-bold text-secondary">Renter Information</h6>
                <p>
                    <strong>Name:</strong> <?= htmlspecialchars($data['first_name'] . ' ' . $data['last_name']) ?><br>
                    <strong>Contact:</strong> <?= htmlspecialchars($data['contacts']) ?><br>
                    <strong>Email:</strong> <?= htmlspecialchars($data['email']) ?>
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <h6 class="fw-bold text-secondary">Receipt Details</h6>
                <p>
                    <strong>Reference No:</strong> <?= htmlspecialchars($data['reference_id']) ?><br>
                    <strong>Billing Month:</strong> <?= htmlspecialchars($data['billing_month']) ?><br>
                    <strong>Due Date:</strong> <?= date('d M Y', strtotime($data['due_date'])) ?><br>
                    <strong>Payment Date:</strong> <?= date('d M Y', strtotime($data['payment_date'] ?? $data['created_at'])) ?><br>
                    <strong>Status:</strong> 
                    <?= $is_pending 
                        ? '<span class="badge bg-warning text-dark">Awaiting Admin Confirmation</span>' 
                        : '<span class="badge bg-success">Paid</span>' ?>
                </p>
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-end">Amount (₱)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Unit Rent</td>
                    <td class="text-end"><?= number_format($data['total_amount'], 2) ?></td>
                </tr>
                <tr>
                    <td>Add-ons</td>
                    <td class="text-end"><?= number_format($data['addon_total'], 2) ?></td>
                </tr>
                <tr>
                    <td>Late Fee</td>
                    <td class="text-end"><?= number_format($data['late_fee'], 2) ?></td>
                </tr>
                <tr class="total-row">
                    <td>Total <?= $is_pending ? 'Amount Due' : 'Amount Paid' ?></td>
                    <td class="text-end">₱<?= number_format($data['total_amount'] + $data['addon_total'] + $data['late_fee'], 2) ?></td>
                </tr>
            </tbody>
        </table>

        <?php if ($is_pending): ?>
        <div class="alert alert-warning text-center mt-4">
            <i class="fa-solid fa-clock me-2"></i>
            This payment is <strong>awaiting admin confirmation</strong>.
        </div>
    <?php elseif ($is_confirmed): ?>
        <div class="alert alert-success text-center mt-4">
            <i class="fa-solid fa-circle-check me-2"></i>
            Payment has been <strong>confirmed</strong> by admin.
        </div>
    <?php else: ?>
        <div class="alert alert-danger text-center mt-4">
            <i class="fa-solid fa-times-circle me-2"></i>
            Payment was <strong>rejected</strong>. Please contact admin.
        </div>
    <?php endif; ?>

    </div>

<?php
    } else {
        echo "<div class='text-center text-danger py-5'><h5>No receipt found with this ID.</h5></div>";
    }
} else {
    echo "<div class='text-center text-warning py-5'><h5>No valid payment ID provided!</h5></div>";
}
?>
</div>

<?php 
require('includes/footer.php');
include('includes/scripts.php');
?>

<!-- PDF Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
function ereceiptPrint() {
    const content = document.getElementById("ereceiptPrint").innerHTML;
    const win = window.open('', '', 'height=700,width=900');
    win.document.write("<html><title>e-Receipt - Monings Rental</title>");
    win.document.write('<body style="font-family:Poppins; margin:40px;">');
    win.document.write(content);
    win.document.write('</body></html>');
    win.document.close();
    win.print();
}

async function downloadReceiptPDF() {
    const { jsPDF } = window.jspdf;
    const receipt = document.getElementById("ereceiptPrint");
    const canvas = await html2canvas(receipt, { scale: 2 });
    const imgData = canvas.toDataURL("image/png");
    const pdf = new jsPDF("p", "mm", "a4");
    const width = 190;
    const height = (canvas.height * width) / canvas.width;
    pdf.addImage(imgData, "PNG", 10, 10, width, height);
    pdf.save("Monings_Official_Receipt.pdf");
}
</script>
