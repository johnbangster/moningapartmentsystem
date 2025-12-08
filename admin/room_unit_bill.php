<?php
session_start();
require 'config/dbcon.php';   // adjust path if needed
date_default_timezone_set('Asia/Manila');

// Ensure admin/employee is logged in
if (!isset($_SESSION['auth']) || !in_array($_SESSION['auth_role'], ['admin', 'employee'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['auth_user']['user_id'];
$user_role = $_SESSION['auth_role'];

//Helper: generateReferenceID

function generateReferenceID() {
    return 'BILL-' . strtoupper(uniqid());
}

/* ---------------------------
   Helper: sendSMS (Semaphore)
   --------------------------- */
function sendSMS($contact, $message)
{
    $apikey = 'ad1be2dd7b2999a0458b90c264aa4966';
    $sender = 'MONINGSRENT';
    $timestamp = date('Y-m-d H:i:s');

    // Ensure log directory exists
    if (!is_dir('logs')) {
        mkdir('logs', 0777, true);
    }
    $logFile = 'logs/sms_log.txt';

    // -------------------------
    // Normalize Phone Number
    // -------------------------
    $contact = trim($contact);

    if (preg_match('/^0\d{10}$/', $contact)) {
        $number = '63' . substr($contact, 1);
    } elseif (preg_match('/^63\d{10}$/', $contact)) {
        $number = $contact;
    } else {
        file_put_contents(
            $logFile,
            "[$timestamp] Invalid number: {$contact}\n",
            FILE_APPEND
        );
        return false;
    }

    // -------------------------
    // Initialize CurlHandle (PHP 8+)
    // -------------------------
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://api.semaphore.co/api/v4/messages',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'apikey'     => $apikey,
            'number'     => $number,
            'message'    => $message,
            'sendername' => $sender
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
    ]);

    // -------------------------
    // Execute request
    // -------------------------
    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);

    // In PHP 8.4+, CurlHandle is an object → destructor auto-closes
    unset($ch);

    
    // Logging
    
    if ($curlErr) {
        file_put_contents(
            $logFile,
            "[$timestamp] ERROR sending SMS to {$number} | CURL Error: {$curlErr}\n",
            FILE_APPEND
        );
        return false;
    }

    file_put_contents(
        $logFile,
        "[$timestamp] Sent to: {$number} | Message: {$message} | Response: {$response}\n",
        FILE_APPEND
    );

    return $response;
}

//Fetch unit_id from query string (redirect)
  
$incoming_unit_id = isset($_GET['unit_id']) ? intval($_GET['unit_id']) : 0;

//Preselect renter & unit details (if available)
 
$pre_renter = null;
$pre_unit   = null;

//STEP 2: Validate Unit & Fetch Renter
 
if ($incoming_unit_id > 0) {
    // find active renter assigned to this unit + check agreement
    $stmt = $con->prepare("
        SELECT 
            r.id AS renter_id, 
            r.first_name, 
            r.last_name, 
            r.contacts, 
            r.move_in_date, 
            r.lease_term,
            u.id AS unit_id, 
            u.name AS unit_name, 
            u.price AS unit_price,
            ra.status AS agreement_status
        FROM renters r
        JOIN units u 
            ON r.unit_id = u.id
        LEFT JOIN rental_agreements ra 
            ON ra.renter_id = r.id AND ra.unit_id = u.id
        WHERE 
            u.id = ? 
            AND r.status = 'Active'
        ORDER BY ra.id DESC
        LIMIT 1
    ");
    
    $stmt->bind_param("i", $incoming_unit_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();

        //Store renter info
        $pre_renter = [
            'id' => (int)$row['renter_id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'contacts' => $row['contacts'],
            'move_in_date' => $row['move_in_date'],
            'lease_term' => (int)$row['lease_term'],
            'agreement_status' => $row['agreement_status']
        ];

        //Store unit info
        $pre_unit = [
            'id' => (int)$row['unit_id'],
            'name' => $row['unit_name'],
            'price' => (float)$row['unit_price']
        ];

        //STEP 3: Agreement Validation
         
        if (empty($row['agreement_status'])) {
            //  No agreement found
            echo "<script>
                alert('Cannot generate bill. No rental agreement found for this renter.');
                window.history.back();
            </script>";
            exit();
        } elseif ($row['agreement_status'] === 'pending') {
            // Agreement still pending
            echo "<script>
                alert('Cannot generate bill. Rental agreement is still pending approval.');
                window.history.back();
            </script>";
            exit();
        }
        //  Otherwise agreement is accepted — continue
    } else {
        // No active renter found for this unit
        echo "<script>
            alert('No active renter found for this unit.');
            window.history.back();
        </script>";
        exit();
    }

    $stmt->close();
}

//STEP 4: Fetch dropdown data
$renters_res = mysqli_query($con, "
    SELECT id, first_name, last_name 
    FROM renters 
    WHERE status='Active' 
    ORDER BY first_name, last_name
");

$units_res = mysqli_query($con, "
    SELECT id, name, price 
    FROM units 
    WHERE status='Occupied' AND unit_type_id = 3 
    ORDER BY name
");

//POST: Save bill / validation
  
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_bill'])) {
    // sanitize
    $renter_id   = intval($_POST['renter_id'] ?? 0);
    $unit_id     = intval($_POST['unit_id'] ?? 0);
    $bill_month  = trim($_POST['bill_month'] ?? ''); // format YYYY-MM
    $unit_price  = floatval($_POST['unit_price'] ?? 0);
    $reference   = trim($_POST['reference_id'] ?? generateReferenceID());
    $generated_by = isset($_SESSION['auth_user']['username']) ? $_SESSION['auth_user']['username'] : 'system';
    $generated_role = isset($_SESSION['auth_role']) ? $_SESSION['auth_role'] : 'admin';

    // collect add-ons arrays
    $addon_names   = $_POST['addon_name'] ?? [];
    $addon_amounts = $_POST['addon_amount'] ?? [];

    // Basic validation
    if (!$renter_id || !$unit_id || !$bill_month) {
        $_SESSION['room_bill_msg'] = ['type' => 'error', 'text' => 'Missing required fields.'];
        header("Location: room_unit_bill.php?unit_id={$incoming_unit_id}");
        exit();
    }

    // Validate bill_month format YYYY-MM
    if (!preg_match('/^\d{4}-\d{2}$/', $bill_month)) {
        $_SESSION['room_bill_msg'] = ['type' => 'error', 'text' => 'Invalid month format.'];
        header("Location: room_unit_bill.php?unit_id={$incoming_unit_id}");
        exit();
    }

    $current_ym = date('Y-m');

    // 1) Prevent past month
    if ($bill_month < $current_ym) {
        $_SESSION['room_bill_msg'] = ['type' => 'error', 'text' => 'Past months are not allowed.'];
        header("Location: room_unit_bill.php?unit_id={$incoming_unit_id}");
        exit();
    }

    // 2) Check duplicate - ensure only one bill per unit per month
    $stmtDup = $con->prepare("SELECT COUNT(*) FROM bills WHERE unit_id = ? AND DATE_FORMAT(due_date, '%Y-%m') = ?");
    $stmtDup->bind_param("is", $unit_id, $bill_month);
    $stmtDup->execute();
    $dupCount = $stmtDup->get_result()->fetch_row()[0];
    $stmtDup->close();

    if ($dupCount > 0) {
        $_SESSION['room_bill_msg'] = ['type' => 'error', 'text' => 'A bill for this unit and month already exists.'];
        header("Location: room_unit_bill.php?unit_id={$incoming_unit_id}");
        exit();
    }

    // 3) Get renter move_in_date and lease_term for validation
    $stmtLease = $con->prepare("SELECT move_in_date, lease_term FROM renters WHERE id = ? LIMIT 1");
    $stmtLease->bind_param("i", $renter_id);
    $stmtLease->execute();
    $leaseRes = $stmtLease->get_result();
    if (!$leaseRes || $leaseRes->num_rows === 0) {
        $stmtLease->close();
        $_SESSION['room_bill_msg'] = ['type' => 'error', 'text' => 'Renter/lease info not found.'];
        header("Location: room_unit_bill.php?unit_id={$incoming_unit_id}");
        exit();
    }
    $leaseRow = $leaseRes->fetch_assoc();
    $move_in_date = $leaseRow['move_in_date'];          // e.g. 2025-06-10
    $lease_term    = intval($leaseRow['lease_term']);   // months e.g. 6 or 12
    $stmtLease->close();

    // compute allowed lease months
    $start_ym = date('Y-m', strtotime($move_in_date));
    // lease_end = move_in_date + lease_term - 1 months (inclusive)
    $dt = new DateTime($move_in_date);
    $dt->modify('+'.($lease_term).' months');
    $end_ym = $dt->format('Y-m');

    if ($bill_month < $start_ym || $bill_month > $end_ym) {
        $_SESSION['room_bill_msg'] = ['type' => 'error', 'text' => "Bill month is outside the lease period ({$start_ym} to {$end_ym})."];
        header("Location: room_unit_bill.php?unit_id={$incoming_unit_id}");
        exit();
    }

    // 4) Sum addons
    $total_addons = 0.0;
    $valid_addons = [];
    for ($i = 0; $i < count($addon_names); $i++) {
        $name = trim($addon_names[$i] ?? '');
        $amt  = isset($addon_amounts[$i]) ? floatval($addon_amounts[$i]) : 0.0;
        if ($name !== '' && $amt > 0) {
            $valid_addons[] = ['name' => $name, 'amount' => $amt];
            $total_addons += $amt;
        }
    }

    // final total
    $total_amount = $unit_price + $total_addons;

    // derive due_date (we'll set due day to 5th of the month)
    list($yy, $mm) = explode('-', $bill_month);
    $due_date = sprintf('%04d-%02d-05', intval($yy), intval($mm));

    // 5) Insert bill (prepared)
    $stmtIns = $con->prepare("
    INSERT INTO bills (reference_id, renter_id, unit_id, due_date, total_amount, status, generated_by, generated_role)
    VALUES (?, ?, ?, ?, ?, 'open', ?, ?)
    ");
    if (!$stmtIns) {
        $_SESSION['room_bill_msg'] = ['type' => 'error', 'text' => 'DB prepare error (bills): ' . $con->error];
        header("Location: room_unit_bill.php?unit_id={$incoming_unit_id}");
        exit();
    }

    // correct binding string (no spaces!)
    $stmtIns->bind_param("siisdss", $reference, $renter_id, $unit_id, $due_date, $total_amount, $generated_by, $generated_role);

    if (!$stmtIns->execute()) {
        $_SESSION['room_bill_msg'] = ['type' => 'error', 'text' => 'Failed to create bill: ' . $stmtIns->error];
        $stmtIns->close();
        header("Location: room_unit_bill.php?unit_id={$incoming_unit_id}");
        exit();
    }
    $bill_id = $stmtIns->insert_id;
    $stmtIns->close();

    // 6) Insert add-ons in bill_addon table
    if (!empty($valid_addons)) {
        $stmtAdd = $con->prepare("INSERT INTO bill_addons (bill_id, name, amount) VALUES (?, ?, ?)");
        foreach ($valid_addons as $ad) {
            $n = $ad['name'];
            $a = $ad['amount'];
            $stmtAdd->bind_param("isd", $bill_id, $n, $a);
            $stmtAdd->execute();
        }
        $stmtAdd->close();
    }

    // 7) Send SMS
    $rRow = $con->query("SELECT first_name, last_name, contacts FROM renters WHERE id = {$renter_id} LIMIT 1")->fetch_assoc();
    $rname = trim($rRow['first_name'] . ' ' . $rRow['last_name']);
    $rcontact = $rRow['contacts'] ?? '';
    if (!empty($rcontact)) {
        $message = "Hello {$rname}! Your bill for " . date('F Y', strtotime($yy.'-'.$mm.'-01')) .
                   " has been created. Amount: PHP " . number_format($total_amount,2) .
                   ". Ref: {$reference}. Due: {$due_date}. - Moning Rental Services";
        sendSMS($rcontact, $message);
    }

    $_SESSION['room_bill_msg'] = ['type' => 'success', 'text' => 'Bill created successfully (Ref: ' . $reference . ')'];
    header("Location: room_unit_bill.php?unit_id={$incoming_unit_id}");
    exit();
}

//Output HTML below
   
$msg = $_SESSION['room_bill_msg'] ?? null;
unset($_SESSION['room_bill_msg']);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Room Bill</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    .input-group-text { width:140px; background: #f8f9fa; }
    .NoPrint { }
    @media print { .NoPrint{display:none;} }
  </style>
</head>
<body class="bg-light">
  <div class="container mt-4 mb-5">
    <div class="container mt-4 mb-5">
      <div class="mb-3">
          <a href="billing.php" 
            class="btn btn-secondary btn-m shadow-none" 
            style="display: inline-flex; align-items: center;">
              <i class="fa-solid fa-arrow-left me-1"></i> Back
          </a>
      </div>
    <div class="card shadow">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Manual Room Billing</h5>
      </div>

      <div class="card-body">
        <form id="roomBillForm" method="POST" novalidate>
          <input type="hidden" name="create_bill" value="1">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Renter</label>
              <select name="renter_id" id="renterSelect" class="form-select" required>
                <option value="">-- Select Renter --</option>
                <?php
                // rewind renter result pointer if it was used earlier
                if ($renters_res) mysqli_data_seek($renters_res, 0);
                while ($r = mysqli_fetch_assoc($renters_res)):
                    $rid = (int)$r['id'];
                    $label = htmlspecialchars($r['first_name'] . ' ' . $r['last_name']);
                    $sel = ($pre_renter && $pre_renter['id'] === $rid) ? 'selected' : '';
                ?>
                  <option value="<?= $rid ?>" <?= $sel ?>><?= $label ?></option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Unit (Room)</label>
              <select name="unit_id" id="unitSelect" class="form-select" required>
                <option value="">-- Select Unit --</option>
                <?php
                if ($units_res) mysqli_data_seek($units_res, 0);
                while ($u = mysqli_fetch_assoc($units_res)):
                    $uid = (int)$u['id'];
                    $sel = ($pre_unit && $pre_unit['id'] === $uid) ? 'selected' : '';
                ?>
                  <option value="<?= $uid ?>" data-price="<?= htmlspecialchars($u['price']) ?>" <?= $sel ?>>
                    <?= htmlspecialchars($u['name']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Bill Month</label>
              <input type="month" name="bill_month" id="bill_month" class="form-control" min="<?= date('Y-m') ?>" required>
              <div class="form-text">Only current or future months within lease are allowed.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Monthly Rent (₱)</label>
              <input type="number" step="0.01" name="unit_price" id="unit_price" class="form-control" value="<?= $pre_unit ? number_format($pre_unit['price'],2,'.','') : '' ?>" required>
            </div>

            <div class="col-md-4">
              <label class="form-label">Reference No</label>
              <input type="text" name="reference_id" id="reference_id" class="form-control" readonly value="<?= generateReferenceID() ?>">
            </div>
          </div>

          <hr>
          <h6>Add-ons (for this bill month only)</h6>
          <div id="addonsContainer" class="mb-3"></div>
          <button type="button" class="btn btn-sm btn-success NoPrint mb-3" id="addAddonBtn">+ Add Add-on</button>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Total Add-ons (₱)</label>
              <input type="text" id="totalAddons" class="form-control" readonly value="0.00">
            </div>
            <div class="col-md-6">
              <label class="form-label">Total Bill Amount (₱)</label>
              <input type="text" name="total_amount" id="totalAmount" class="form-control" readonly value="<?= $pre_unit ? number_format($pre_unit['price'],2,'.','') : '0.00' ?>">
            </div>
          </div>

          <div class="d-grid gap-2">
            <button type="button" id="submitBtn" class="btn btn-primary btn-lg">Create Bill</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<script>
//Client: helpers & UI
  
function formatNumber(n){ return Number(n).toLocaleString('en-US',{minimumFractionDigits:2, maximumFractionDigits:2}); }

function recalcTotals() {
  const rent = parseFloat($('#unit_price').val() || 0);
  let addons = 0;
  $('[name="addon_amount[]"]').each(function(){ addons += parseFloat($(this).val() || 0); });
  $('#totalAddons').val(formatNumber(addons));
  $('#totalAmount').val(formatNumber(rent + addons));
}

// add addon row
function addAddonRow(name = '', amount = '') {
  const row = $('<div class="row mb-2 addon-row">\
    <div class="col-md-6"><input type="text" name="addon_name[]" class="form-control" placeholder="Add-on name" value="'+name+'" required></div>\
    <div class="col-md-4"><input type="number" step="0.01" name="addon_amount[]" class="form-control" placeholder="Amount" value="'+amount+'" required></div>\
    <div class="col-md-2"><button type="button" class="btn btn-danger btn-block removeAddon">X</button></div>\
  </div>');
  $('#addonsContainer').append(row);
}
$(document).on('click', '.removeAddon', function(){ $(this).closest('.addon-row').remove(); recalcTotals(); });

$('#addAddonBtn').on('click', function(){ addAddonRow(); });

// when unit changes populate rent
$('#unitSelect').on('change', function(){
  const price = $(this).find(':selected').data('price') || 0;
  $('#unit_price').val(Number(price).toFixed(2));
  recalcTotals();
});

// when rent or addon changed
$(document).on('input', '#unit_price, [name="addon_amount[]"]', function(){ recalcTotals(); });

// SweetAlert confirmation and submit
$('#submitBtn').on('click', function(){
  // basic client validation
  const renter = $('#renterSelect').val();
  const unit = $('#unitSelect').val();
  const month = $('#bill_month').val();
  const rent = parseFloat($('#unit_price').val() || 0);

  if (!renter || !unit || !month || rent <= 0) {
    Swal.fire('Missing data','Please select renter, unit, month and check rent amount.','warning');
    return;
  }

  // build summary for confirmation
  const addons = [];
  $('[name="addon_name[]"]').each(function(i){
    const n = $(this).val().trim();
    const a = parseFloat($('[name="addon_amount[]"]').eq(i).val() || 0);
    if (n && a) addons.push(n + ' (₱' + a.toFixed(2) + ')');
  });

  let html = `<p><strong>Renter:</strong> ${$('#renterSelect option:selected').text()}</p>`;
  html += `<p><strong>Unit:</strong> ${$('#unitSelect option:selected').text()}</p>`;
  html += `<p><strong>Month:</strong> ${month}</p>`;
  html += `<p><strong>Rent:</strong> ₱${rent.toFixed(2)}</p>`;
  if (addons.length) html += `<p><strong>Add-ons:</strong><br>${addons.join('<br>')}</p>`;
  html += `<p><strong>Total:</strong> ₱${($('#totalAmount').val()).replace(/,/g,'')}</p>`;

  Swal.fire({
    title: 'Create bill?',
    html: html,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, create bill',
    cancelButtonText: 'Cancel',
    showLoaderOnConfirm: true,
    preConfirm: () => {
      // submit form via normal POST (we'll create a temporary hidden submit)
      return new Promise((resolve) => {
        $('#submitBtn').prop('disabled', true);
        $('#roomBillForm').submit();
        resolve();
      });
    }
  });

});

// Show server msg (if any) via SweetAlert
<?php if ($msg): ?>
  document.addEventListener('DOMContentLoaded', function(){
    <?php if ($msg['type'] === 'success'): ?>
      Swal.fire('Success','<?= addslashes($msg['text']) ?>','success').then(()=>{ /* optional redirect */ });
    <?php else: ?>
      Swal.fire('Error','<?= addslashes($msg['text']) ?>','error');
    <?php endif; ?>
  });
<?php endif; ?>

// Auto-fill selected incoming unit/renter price on load
$(document).ready(function(){
  <?php if ($pre_unit): ?>
    $('#unit_price').val('<?= number_format($pre_unit['price'],2,'.','') ?>');
    $('#unitSelect').val('<?= $pre_unit['id'] ?>');
  <?php endif; ?>
  <?php if ($pre_renter): ?>
    $('#renterSelect').val('<?= $pre_renter['id'] ?>');
  <?php endif; ?>
  recalcTotals();
});
</script>

</body>
</html>
