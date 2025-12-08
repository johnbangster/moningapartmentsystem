<?php
session_start();
require 'config/dbcon.php';
require ('includes/header.php');

// Check if logged in
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    die("Access denied. Please log in.");
}

// Check role
if ($_SESSION['auth_role'] !== 'employee') {
    die("Access denied. Only employees can create cash reports.");
}

// Employee ID from logged-in user
$employee_id = (int) $_SESSION['auth_user']['user_id'];

// Fetch renters
$res = mysqli_query($con, "SELECT id,CONCAT(first_name, ' ', last_name) AS full_name FROM renters ORDER BY full_name ASC");
$renters = [];
while ($row = mysqli_fetch_assoc($res)) {
    $renters[] = $row;
}
?>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <title>Create Cash Report</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
 <div class="card">
  <div class="card-body">
   <h4>Create Cash Payment Report</h4>
   <form id="reportForm" action="save_report.php" method="post" enctype="multipart/form-data">

     <div class="mb-3">
       <label class="form-label">Select Renter</label>
       <select name="renter_id" id="renter_id" class="form-select" required>
         <option value="">-- choose renter --</option>
         <?php foreach($renters as $r): ?>
           <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['full_name']) ?></option>
         <?php endforeach; ?>
       </select>
     </div>

     <div id="renterInfo" class="mb-3" style="display:none">
       <h6>Renter information</h6>
       <p id="renterName"></p>
       <p id="renterContact"></p>
     </div>

     <div class="mb-3">
       <label class="form-label">Select Bill (month)</label>
       <select name="bill_id" id="bill_id" class="form-select" required>
         <option value="">-- select bill after renter --</option>
       </select>
     </div>

     <div id="billRef" class="mb-3" style="display:none">
       <div class="alert alert-secondary">
         <strong>Reference Bill:</strong>
         <div id="billDesc"></div>
         <div id="billAmount"></div>
         <div id="billMonth"></div>
         <div id="billRefId"></div>

       </div>
     </div>

     <div class="mb-3">
       <label class="form-label">Amount received (PHP)</label>
       <input type="number" step="0.01" name="amount_paid" id="amount_paid" class="form-control" required>
     </div>

     <div class="mb-3">
       <label class="form-label">Payment date</label>
       <input type="datetime-local" name="payment_date" class="form-control" required>
     </div>

     <div class="mb-3">
       <label class="form-label">Upload receipt (photo or PDF, max 2MB)</label>
       <input type="file" name="receipt_file" id="receipt_file" class="form-control" accept="image/*,.pdf" required>
     </div>

     <div class="mb-3">
       <label class="form-label">Notes (optional)</label>
       <textarea name="notes" class="form-control" rows="3"></textarea>
     </div>

     <button type="submit" class="btn btn-primary">Create Report</button>
   </form>
  </div>
 </div>
 <div id="messageArea" class="mt-3"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){

  // When renter is selected
  $('#renter_id').on('change', function(){
    const renterId = $(this).val();
    if (!renterId) {
      $('#renterInfo').hide();
      $('#bill_id').html('<option value="">-- select bill after renter --</option>');
      $('#billRef').hide();
      return;
    }

    // fetch renter bills and info
    $.ajax({
      url: 'get_renter_bills.php',
      method: 'GET',
      data: { renter_id: renterId },
      dataType: 'json'
    }).done(function(resp){
      if (resp.success) {
        // populate renter info
        // populate renter info
        $('#renterName').text('Name: ' + resp.renter.full_name);
        $('#renterContact').text('Contact: ' + (resp.renter.contacts || 'N/A'));
        $('#renterInfo').show();


        // populate bills
        const bills = resp.bills;
        let opts = '<option value="">-- choose bill --</option>';
        if (bills.length===0) opts += '<option value="">No open bills found</option>';
        bills.forEach(function(b){
          opts += `
                <option value="${b.id}" 
                        data-amount="${b.amount}" 
                        data-desc="${b.description}" 
                        data-month="${b.month_year}"
                        data-ref="${b.reference_id}">
                    ${b.month_year} — ₱${b.amount} (Ref: ${b.reference_id})
                </option>`;

        });
        $('#bill_id').html(opts);
        $('#billRef').hide();
      } else {
        alert(resp.message || 'Failed to fetch bills');
      }
    }).fail(function(){ alert('Request error'); });
  });

  // When bill is selected
  $('#bill_id').on('change', function(){
    const $opt = $(this).find('option:selected');
    const amount = $opt.data('amount');
    const desc = $opt.data('desc');
    const month = $opt.data('month');
    const ref = $opt.data('ref');

    if (!$(this).val()) {
      $('#billRef').hide();
      return;
    }
    $('#billDesc').text('Description: ' + (desc || '—'));
    $('#billAmount').text('Amount: ₱' + parseFloat(amount).toFixed(2));
    $('#billMonth').text('Month: ' + month);
    $('#amount_paid').val(parseFloat(amount).toFixed(2));
    $('#billRefId').text('Reference ID: ' + ref);
    $('#billRef').show();
  });

  // Optional client-side validation for file size
  $('#reportForm').on('submit', function(e){
    const file = $('#receipt_file')[0].files[0];
    if (file && file.size > 2 * 1024 * 1024) {
      e.preventDefault();
      alert('Receipt file exceeds 2MB limit.');
    }
  });

});
</script>
</body>
</html>
