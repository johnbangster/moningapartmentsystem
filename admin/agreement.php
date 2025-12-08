<?php
session_start();
require 'config/dbcon.php';
require('includes/header.php');


//for prefill latest added renter
$renter_id = isset($_GET['renter_id']) ? intval($_GET['renter_id']) : 0;
$unit_id   = isset($_GET['unit_id']) ? intval($_GET['unit_id']) : 0;


// Fetch renters and units
// $renters = mysqli_query($con, "SELECT id, first_name, last_name FROM renters WHERE status='Active' ORDER BY first_name ASC, last_name ASC");
$renters = mysqli_query($con, "
    SELECT id, first_name, last_name, move_in_date
    FROM renters
    WHERE status='Active'
    ORDER BY first_name ASC, last_name ASC
");

$units   = mysqli_query($con, "SELECT id, name, price FROM units WHERE status='Occupied'");

// Default renter
$default_renter = null;
if (mysqli_num_rows($renters) > 0) {
  $default_renter = mysqli_fetch_assoc($renters);
  mysqli_data_seek($renters, 0); // rewind so loop works
}

// Default unit
$default_unit = null;
if (mysqli_num_rows($units) > 0) {
  $default_unit = mysqli_fetch_assoc($units);
  mysqli_data_seek($units, 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Lease Agreement</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/lemonadejs/dist/lemonade.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@lemonadejs/signature/dist/index.min.js"></script>
  <style>
    .section-title { font-weight: bold; margin-top: 20px; }
    .dos li::before { content: ' '; }
    .donts li::before { content: ' '; }
  </style>
</head>
<body class="bg-light">

<div class="container py-5">
  <h3 class="mb-4">Create New Lease Agreement</h3>
  <!-- <div class="text-end mb-4">
    <button class="btn btn-danger shadow-none btn-sm" onclick="document.location='index.php'">BACK</button>
  </div> -->

  <form action="function/process_agreement.php" method="POST" id="agreementForm" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm">
    <div class="row mb-3">
      <div class="col-md-6">
        <label>Renter</label>
        <select name="renter_id" id="renterSelect" class="form-select" required>
          <option value="">Select Renter</option>
          <?php while($r = mysqli_fetch_assoc($renters)): ?>
            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['first_name']." ".$r['last_name']) ?></option>
          <?php endwhile; ?>
        </select>
        <!-- <select name="renter_id" class="form-select" required>
          <option value="">Select Renter</option>
          <?php while($r = mysqli_fetch_assoc($renters)): ?>
            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['first_name']." ".$r['last_name']) ?></option>
            <?php endwhile; ?>
        </select> -->
      </div>
      <div class="col-md-6">
        <label>Unit</label>
        <select name="unit_id" id="unitSelect" class="form-select" required>
          <option value="">Select Unit</option>
          <?php while($u = mysqli_fetch_assoc($units)): ?>
            <option value="<?= $u['id'] ?>" data-price="<?= number_format($u['price'],0) ?>">
              <?= htmlspecialchars($u['name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <label>Lease Term</label>
        <select name="term_months" id="termSelect" class="form-select" required>
          <option value="6">6 Months</option>
          <option value="12">12 Months</option>
        </select>
      </div>
      <div class="col-md-4">
        <label>Start Date</label>
        <input type="date" name="start_date" id="startDate" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label>End Date</label>
        <input type="date" name="end_date" id="endDate" class="form-control" required readonly>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label>Monthly Rent (₱)</label>
        <input type="text" name="monthly_rent" id="monthlyRent" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label>Deposit (₱)</label>
        <input type="text" name="deposit" id="deposit" class="form-control" required>
      </div>
    </div>

    <div class="mb-3">
      
      <label class="text-center">Monings's Rental Agreement and Lease Terms</label>
      <textarea name="term_conditions" id="term_conditions" rows="30" class="form-control" style="white-space: pre-wrap; font-family: monospace;">
        
                              RESIDENTIAL LEASE AGREEMENT
     
      1. This Lease Agreement is between the Landlord and the Renter.
      2. Lease Duration: 6 or 12 months based on selection.
      3. Monthly rent is payable on or before the due date each month.
      4. A 1% weekly penalty applies for late payments.
      5. Deposit is refundable upon end of lease minus damages/unpaid bills.
      8. Early termination requires 30-day notice and forfeits deposit.
      9. Agreement takes effect on the Start Date.
      10. Both parties agree by digitally signing or accepting this form.
      11. Down payment and Advance payment is required before move-in.

      Payment Terms
          Monthly rent is payable on or before the due date each month.
          Payment Methods: PayPal, or Cash.
          A 1% penalty per week will apply if payment is overdue.

      Security Deposit
          The deposit is refundable at lease end, subject to inspection.
          Deductions may apply for damage, unpaid bills, or violations.

      Utilities and Fees
          Water, electricity, internet, and cable (if any) are billed separately.
          Shared facility fees (if applicable) will also be billed.

      Renewal Policy
          Renewal may be offered 15–30 days before lease end.
          Non-renewing renters must vacate by the end date.
          Unpaid dues may be deducted from the deposit or pursued legally.

      Responsibilities of the Renter (Do’s)
          Maintain cleanliness and order in the unit.
          Dispose of garbage regularly.
          Report needed repairs promptly.
          Allow inspections with notice.
          Use utilities responsibly.
          Follow building rules and curfews.
          Pay rent and bills on time.
          Give 30-day notice before non-renewal.

      Prohibited Actions (Don'ts)
          No subleasing is permitted.
          No illegal activity or substances.
          No smoking inside the vicinity.
          No excessive noise or disturbances.
          No tampering with utilities or safety systems.
          No pets allowed.
          No damaging installations/furniture.

      Violation of any part of this Agreement or nonpayment of rent when due shall be cause for eviction under applicable code sections.
      The prevailing party (shall/shall not) recover reasonable legal service fees involved.
      
      Tenants hereby acknowledge that they have read this Agreement, understand it, agree to it,and have been given a copy. 


      Owner/Employee Signature: ___________________________Renter Signature: __________________________Date:_______________________

      Owner/Employee: ______________________________________Renter: ___________________________________Date:________________________
      
      </textarea>
    </div>

    <div id="root"></div>
    <!-- <input type="button" value="Reset" id="resetCanvas" class="btn btn-outline-secondary btn-sm" />
    <input type="button" value="Save as image" id="getImage" class="btn btn-outline-secondary btn-sm" /> -->
    <!-- <img id="image" class="image full-width mt-2" /><br><br> -->

    <div class="d-grid">
      <button type="submit" class="btn btn-primary">Create Agreement</button>
    </div>
  </form>

  <!-- Preview Modal -->
  <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Lease Agreement Preview</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <p><strong>Renter:</strong> <span id="previewRenter"></span></p>
          <p><strong>Unit:</strong> <span id="previewUnit"></span></p>
          <p><strong>Lease Term:</strong> <span id="previewTerm"></span></p>
          <p><strong>Start Date:</strong> <span id="previewStart"></span></p>
          <p><strong>End Date:</strong> <span id="previewEnd"></span></p>
          <p><strong>Monthly Rent:</strong> ₱<span id="previewRent"></span></p>
          <p><strong>Deposit:</strong> ₱<span id="previewDeposit"></span></p>

          <div class="mb-3">
            <label class="form-label"><strong>Agreement Terms</strong></label>
            <textarea id="modal_terms" rows="12" class="form-control"></textarea>
          </div>

          <!-- <div class="mb-3">
            <label class="form-label"><strong>Signature Preview:</strong></label><br>
            <img id="previewSignature" style="max-width:100%; border:1px solid #ccc; padding:5px;" alt="signature">
          </div> -->
        </div>

        <div class="modal-footer">
          <button type="button" id="submitAgreement" class="btn btn-primary">Confirm & Submit</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>

      </div>
    </div>
  </div>

</div><!-- container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const form = document.getElementById('agreementForm');
  const startDateInput = document.getElementById('startDate');
  const endDateInput   = document.getElementById('endDate');
  const termSelect     = document.getElementById('termSelect');
  const unitSelect     = document.getElementById('unitSelect');
  const rentInput      = document.getElementById('monthlyRent');

  // Prevent past start dates
  const today = new Date().toISOString().split('T')[0];
  startDateInput.min = today;

  // Auto calculate end date
  function updateEndDate() {
    if (!startDateInput.value) return;
    const start = new Date(startDateInput.value);
    const term  = parseInt(termSelect.value, 10) || 0;
    if (!isNaN(start) && !isNaN(term)) {
      const end = new Date(start);
      end.setMonth(end.getMonth() + term);
      endDateInput.value = end.toISOString().split('T')[0];
    }
  }
  startDateInput.addEventListener('change', updateEndDate);
  termSelect.addEventListener('change', updateEndDate);

  // Auto populate rent
  unitSelect.addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];
    const price = selected ? selected.getAttribute('data-price') : '';
    if (price) rentInput.value = price;
  });

  // Safe number formatter
  function formatNumber(num) {
    return String(num).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  // Format inputs (monthlyRent & deposit)
  document.getElementById('monthlyRent').addEventListener('input', function (event) {
    let value = event.target.value.replace(/[^0-9]/g,'');
    event.target.value = value ? formatNumber(value) : '';
  });
  document.getElementById('deposit').addEventListener('input', function (event) {
    let value = event.target.value.replace(/[^0-9]/g,'');
    event.target.value = value ? formatNumber(value) : '';
  });

  // Signature component
  // const root = document.getElementById("root");
  // const component = Signature(root, { width: 500, height: 100, instructions: "Please sign in the box above" });
  // document.getElementById("resetCanvas").onclick = () => { component.value = []; };
  // document.getElementById("getImage").onclick = () => { document.getElementById("image").src = component.getImage(); };

  // Handle form submit with preview
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    // Collect values
    const renter   = form.renter_id.selectedOptions[0]?.text || '';
    const unit     = form.unit_id.selectedOptions[0]?.text || '';
    const term     = termSelect.value;
    const start    = startDateInput.value;
    const end      = endDateInput.value;
    const rent     = rentInput.value;
    const deposit  = form.deposit.value;
    const agreement= document.getElementById('term_conditions').value.trim();

    

    // Fill preview modal
    document.getElementById('previewRenter').textContent = renter;
    document.getElementById('previewUnit').textContent   = unit;
    document.getElementById('previewTerm').textContent   = term + " months";
    document.getElementById('previewStart').textContent  = start;
    document.getElementById('previewEnd').textContent    = end;
    document.getElementById('previewRent').textContent   = rent;
    document.getElementById('previewDeposit').textContent= deposit;
    document.getElementById('modal_terms').value = agreement;
    // document.getElementById('previewSignature').src = base64signature;

    // Show modal
    const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    previewModal.show();

    // Confirm submit: copy modal terms back to main textarea then submit
    document.getElementById('submitAgreement').onclick = () => {
      // copy updated terms back to main form field
      document.getElementById('term_conditions').value = document.getElementById('modal_terms').value;
      previewModal.hide();
      form.submit();
    };
  });


    // --- Auto load renter data ---
  document.getElementById('renterSelect').addEventListener('change', function () {
  const renterId = this.value;
  if (!renterId) return;

  fetch(`get_renter.php?renter_id=${renterId}`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        unitSelect.value = data.unit_id || '';
                rentInput.value = data.unit_price ? formatNumber(data.unit_price) : '';
                document.getElementById('deposit').value = data.deposit ? formatNumber(data.deposit) : '';
                if (data.term_months) termSelect.value = data.term_months;
                if (data.start_date) startDateInput.value = data.start_date;
                if (data.end_date) endDateInput.value = data.end_date;
                else updateEndDate();
                updateEndDate();
            } else {
                alert(data.message || 'No lease data found for this renter.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to load renter details.');
        });
});
//         // Autofill unit dropdown
//         const unitSelect = document.getElementById('unitSelect');
//         unitSelect.value = data.unit_id || '';

//         // Autofill monthly rent
//         const rentInput = document.getElementById('monthlyRent');
//         rentInput.value = data.unit_price ? formatNumber(data.unit_price) : '';

//         // Autofill deposit
//         document.getElementById('deposit').value = data.deposit ? formatNumber(data.deposit) : '';

//         // Autofill lease term
//         const termSelect = document.getElementById('termSelect');
//         if (data.term_months) termSelect.value = data.term_months;

//         // Autofill start date if available
//         const startDateInput = document.getElementById('startDate');
//         const endDateInput = document.getElementById('endDate');

//         if (data.start_date) startDateInput.value = data.start_date;
//         if (data.end_date) {
//           endDateInput.value = data.end_date;
//         } else if (data.start_date && data.term_months) {
//           // Compute end date if not set
//           const start = new Date(data.start_date);
//           start.setMonth(start.getMonth() + parseInt(data.term_months));
//           endDateInput.value = start.toISOString().split('T')[0];
//         }

//         updateEndDate(); // ensure consistency
//       } else {
//         alert('No lease data found for this renter.');
//       }
//     })
//     .catch(err => {
//       console.error('Error:', err);
//       alert('Failed to load renter details.');
//     });
// });

window.addEventListener('DOMContentLoaded', () => {
    const defaultRenter = '<?= $default_renter ? $default_renter['id'] : '' ?>';
    if (defaultRenter) {
        document.getElementById('renterSelect').value = defaultRenter;
        document.getElementById('renterSelect').dispatchEvent(new Event('change'));
    }
});


</script>
</body>
</html>



