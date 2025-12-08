<?php
session_start();
require ('../admin/config/dbcon.php');


if (!isset($_GET['agreement_id'])) {
    die("Agreement ID missing.");
}

$agreement_id = intval($_GET['agreement_id']);
$renter_id = $_SESSION['auth_user']['user_id'] ?? 0;

$sql = "SELECT a.*, r.name AS renter_name, u.unit_name, u.address, u.price,
               a.created_at AS date_signed
        FROM rental_agreements a
        JOIN renters r ON a.renter_id = r.id
        JOIN units u ON a.unit_id = u.id
        WHERE a.id = $agreement_id AND a.renter_id = $renter_id
        LIMIT 1";

$res = mysqli_query($con, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    die("Invalid agreement or access denied.");
}

$agreement = mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View & Accept Agreement</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/lemonadejs/dist/lemonade.js"></script>
    <style>
        .agreement-box {
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .signature-container {
            margin-top: 20px;
        }
        canvas {
            border: 1px solid #000;
            background-color: #fff;
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-4">
    <h3 class="text-center mb-4">Rental Agreement</h3>
    
    <div class="agreement-box">
        <p><strong>Renter:</strong> <?= htmlspecialchars($agreement['renter_name']) ?></p>
        <p><strong>Unit:</strong> <?= htmlspecialchars($agreement['unit_name']) ?> (<?= htmlspecialchars($agreement['address']) ?>)</p>
        <p><strong>Lease Term:</strong> <?= htmlspecialchars($agreement['lease_term']) ?> months</p>
        <p><strong>Start Date:</strong> <?= htmlspecialchars($agreement['start_date']) ?></p>
        <p><strong>End Date:</strong> <?= htmlspecialchars($agreement['end_date']) ?></p>
        <p><strong>Monthly Rent:</strong> ₱<?= number_format($agreement['price'], 2) ?></p>
        <p><strong>Date Signed:</strong> <?= date("F j, Y", strtotime($agreement['date_signed'])) ?></p>
        <hr>
        <h5>Agreement Terms:</h5>
        <p><?= nl2br(htmlspecialchars($agreement['agreement_terms'])) ?></p>
    </div>

    <form action="save_signature.php" method="POST" onsubmit="return captureSignature();" class="mt-4">
        <input type="hidden" name="agreement_id" value="<?= $agreement_id ?>">
        <input type="hidden" name="signature" id="signature">

        <div class="signature-container">
            <label><strong>Sign Below:</strong></label><br>
            <canvas id="signaturePad" width="400" height="150"></canvas><br>
            <button type="button" class="btn btn-secondary mt-2" onclick="clearCanvas()">Clear</button>
        </div>

        <button type="submit" class="btn btn-success mt-3">Submit Agreement</button>
    </form>
</div>

<script>
    const canvas = document.getElementById('signaturePad');
    const ctx = canvas.getContext('2d');
    let drawing = false;

    canvas.addEventListener('mousedown', () => { drawing = true; });
    canvas.addEventListener('mouseup', () => { drawing = false; ctx.beginPath(); });
    canvas.addEventListener('mousemove', draw);

    function draw(e) {
        if (!drawing) return;
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#000';
        ctx.lineTo(e.offsetX, e.offsetY);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(e.offsetX, e.offsetY);
    }

    function clearCanvas() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    function captureSignature() {
        const dataURL = canvas.toDataURL('image/png');
        document.getElementById('signature').value = dataURL;
        if (dataURL.length < 1000) {
            alert("Please provide your signature before submitting.");
            return false;
        }
        return true;
    }
</script>

</body>
</html>







<!-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Rental Agreement</title>
  <style>
    body { font-family: Arial, sans-serif; max-width: 900px; margin: auto; padding: 20px; line-height: 1.6; }
    h2 { color: #333; }
    .section-title { font-weight: bold; margin-top: 20px; font-size: 18px; }
    ul.dos, ul.donts { margin: 10px 0 10px 25px; }
    ul.dos li::before { content: '✅ '; }
    ul.donts li::before { content: '❌ '; }
    .agreement-box { border: 1px solid #ccc; padding: 20px; background: #f9f9f9; margin-top: 20px; }
    .btn { padding: 10px 20px; background: green; color: white; border: none; cursor: pointer; margin-top: 20px; }
    .btn:hover { background: darkgreen; }
    .signature { margin-top: 30px; }
  </style>
</head>
<body>

  <h2>Rental Agreement Terms and Conditions</h2>
  <p><strong>(For <?= $lease_term ?> Months Lease)</strong></p>

  <div class="agreement-box">

    <div class="section-title">1. Agreement Overview</div>
    <p>This Rental Agreement is entered between the <strong>Lessor (Admin)</strong> and the <strong>Lessee (<?= $renter_name ?>)</strong> for the property listed below. By accepting this agreement, the Lessee agrees to all terms and conditions set herein.</p>

    <div class="section-title">2. Property Information</div>
    <p>
      <strong>Unit Name/No.:</strong> <?= $unit_name ?><br>
      <strong>Location:</strong> <?= $address ?><br>
      <strong>Rental Term:</strong> <?= ($lease_term == 6) ? "6 Months" : "12 Months" ?><br>
      <strong>Start Date:</strong> <?= $start_date ?><br>
      <strong>End Date:</strong> <?= $end_date ?><br>
      <strong>Monthly Rent:</strong> ₱<?= number_format($monthly_rent, 2) ?><br>
      <strong>Deposit:</strong> ₱<?= number_format($deposit, 2) ?>
    </p>

    <div class="section-title">3. Payment Terms</div>
    <p>
      Rent is due on or before the <strong><?= $due_date ?>th</strong> of each month.<br>
      Payment Methods: GCash, PayMaya, PayPal, Bank Transfer, or Cash.<br>
      A <strong>1% penalty per week</strong> will apply if payment is overdue.
    </p>

    <div class="section-title">4. Security Deposit</div>
    <p>
      The deposit is refundable at lease end, subject to inspection.<br>
      Deductions may apply for damage, unpaid bills, or violations.
    </p>

    <div class="section-title">5. Utilities and Fees</div>
    <p>
      Water, electricity, internet, and cable (if any) are billed separately.<br>
      Shared facility fees (if applicable) will also be billed.
    </p>

    <div class="section-title">6. Responsibilities of the Renter (Do’s)</div>
    <ul class="dos">
      <li>Maintain cleanliness and order in the unit.</li>
      <li>Dispose of garbage regularly.</li>
      <li>Report needed repairs promptly.</li>
      <li>Allow inspections with notice.</li>
      <li>Use utilities responsibly.</li>
      <li>Follow building rules and curfews.</li>
      <li>Pay rent and bills on time.</li>
      <li>Give 30-day notice before non-renewal.</li>
    </ul>

    <div class="section-title">7. Prohibited Actions (Don'ts)</div>
    <ul class="donts">
      <li>No subleasing without permission.</li>
      <li>No illegal activity or substances.</li>
      <li>No smoking unless permitted.</li>
      <li>No excessive noise or disturbances.</li>
      <li>No tampering with utilities or safety systems.</li>
      <li>No pets unless approved.</li>
      <li>No damaging installations/furniture.</li>
    </ul>

    <div class="section-title">8. Termination and Penalties</div>
    <p>
      Early termination requires 30-day notice and forfeits the deposit.<br>
      Immediate termination applies for criminal activity, repeated violations, or breach of contract.
    </p>

    <div class="section-title">9. Renewal Policy</div>
    <p>
      Renewal may be offered 15–30 days before lease end.<br>
      Non-renewing tenants must vacate by the end date.<br>
      Unpaid dues may be deducted from the deposit or pursued legally.
    </p>

    <div class="section-title">10. Agreement Acceptance</div>
    <p>
      By accepting below, both parties agree to this contract.
    </p>

    <div class="signature">
      <strong>Renter Name:</strong> <?= $renter_name ?><br>
      <strong>Admin Name:</strong> <?= $admin_name ?><br>
      <strong>Date Signed:</strong> <?= $date_signed ?>
    </div>

    <form action="backend/accept_agreement.php" method="POST">
      <input type="hidden" name="renter_id" value="<?= $_SESSION['renter_id'] ?>">
      <input type="hidden" name="unit" value="<?= $unit_name ?>">
      <input type="hidden" name="term" value="<?= $lease_term ?>">
      <input type="hidden" name="start_date" value="<?= $start_date ?>">
      <input type="hidden" name="end_date" value="<?= $end_date ?>">
      <input type="hidden" name="monthly_rent" value="<?= $monthly_rent ?>">
      <input type="hidden" name="deposit" value="<?= $deposit ?>">
      <button type="submit" class="btn">Accept Agreement</button>
    </form>

    <label>Upload Signature (scan/photo):</label><br>
    <input type="file" name="signature" accept="image/*" required><br>
    <small>Accepted formats: PNG, JPG, max 2MB</small><br>
  </div>

  <!DOCTYPE html>
<html>
<head>
  <title>View & Accept Agreement</title>
  <script src="https://cdn.jsdelivr.net/npm/lemonadejs"></script>
  <script src="https://cdn.jsdelivr.net/npm/@lemonadejs/signature"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    .signature-container {
      margin-top: 15px;
      border: 1px solid #ccc;
      padding: 10px;
    }
  </style>
</head>
<body class="bg-light">
<div class="container mt-5">
  <h3 class="mb-4">Lease Agreement</h3>

  <div class="bg-white p-4 shadow-sm rounded">
    <p><strong>Unit:</strong> <?= htmlspecialchars($agreement['unit_name']) ?></p>
    <p><strong>Lease Term:</strong> <?= $agreement['term_months'] ?> months</p>
    <p><strong>Start Date:</strong> <?= $agreement['start_date'] ?></p>
    <p><strong>End Date:</strong> <?= $agreement['end_date'] ?></p>
    <p><strong>Monthly Rent:</strong> ₱<?= number_format($agreement['monthly_rent'], 2) ?></p>
    <p><strong>Deposit:</strong> ₱<?= number_format($agreement['deposit'], 2) ?></p>
    <hr>
    <pre style="white-space: pre-wrap;"><?= htmlspecialchars($agreement['term_conditions']) ?></pre>
  </div>

  <form action="backend/accept_agreement.php" method="POST" onsubmit="return captureSignature()" enctype="multipart/form-data" class="mt-4">
    <div class="signature-container">
      <label>Sign below to accept the agreement:</label>
      <div id="signatureBox"></div>
      <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="signatureComponent.value = []">Clear</button>
      <input type="hidden" name="signature_data" id="signatureData">
    </div>

    <input type="hidden" name="agreement_id" value="<?= $agreement['id'] ?>">
    <button type="submit" class="btn btn-primary mt-3">Accept and Submit</button>
  </form>
</div>

<script>
  const signatureBox = document.getElementById("signatureBox");
  const signatureComponent = Signature(signatureBox, {
    width: 500,
    height: 100,
    instructions: "Sign inside the box"
  });

  function captureSignature() {
    if (!signatureComponent.value || signatureComponent.value.length === 0) {
      alert("Please provide your signature.");
      return false;
    }

    document.getElementById("signatureData").value = signatureComponent.getImage();
    return true;
  }
</script>
</body>
</html> -->
