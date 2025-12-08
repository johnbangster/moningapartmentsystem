<?php
session_start();
require('config/dbcon.php');
date_default_timezone_set('Asia/Manila');
// require('send_sms.php');


 //Utility: Generate Unique Bill Reference

function generateReferenceID() {
    return 'BILL-' . strtoupper(uniqid());
}

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

    
    // Normalize Phone Number
   
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

   
    // Initialize CurlHandle (PHP 8+)
    
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

    
    // Execute request
    
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




 //STEP 1: Validate and Fetch Unit Info

$unit_id = 0;
if (isset($_GET['unit_id'])) {
    $unit_id = intval($_GET['unit_id']);
} elseif (isset($_POST['unit_id'])) {
    $unit_id = intval($_POST['unit_id']);
}

if ($unit_id <= 0) {
    echo "<div style='padding:10px;background:#f8d7da;color:#721c24;border-radius:5px;'>
            <strong>Error:</strong> Missing or invalid <code>unit_id</code> parameter.
          </div>";
    exit;
}

// Fetch unit info
$query = "
    SELECT u.id AS unit_id, u.name, u.price, ut.type_name
    FROM units u
    JOIN unit_type ut ON u.unit_type_id = ut.id
    WHERE u.id = ?
";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $unit_id);
$stmt->execute();
$unit = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$unit) {
    echo "<div style='padding:10px;background:#f8d7da;color:#721c24;border-radius:5px;'>
            <strong>Error:</strong> No unit found for ID {$unit_id}.
          </div>";
    exit;
}

$unit_type  = $unit['type_name'];
$unit_name  = $unit['name'];
$unit_price = $unit['price'];

// Prevent auto generation for Room type
if (strcasecmp($unit_type, 'Room') === 0) {
    echo "<script>
            alert('Unit type \"Room\" is not allowed for full auto generation. Redirecting...');
            window.location.href = 'room_unit_bill.php?unit_id={$unit_id}';
          </script>";
    exit;
}

 //STEP 2: Fetch Active Renters + Latest Agreement

$sql = "
    SELECT 
        r.id AS renter_id,
        r.first_name,
        r.last_name,
        r.move_in_date,
        r.lease_term,
        r.contacts,
        a.status AS agreement_status
    FROM renters r
    LEFT JOIN (
        SELECT renter_id, unit_id, status
        FROM rental_agreements
        WHERE status IN ('pending', 'accepted')
        ORDER BY id DESC
        LIMIT 1
    ) a ON a.renter_id = r.id AND a.unit_id = r.unit_id
    WHERE r.unit_id = ?
      AND r.status IN ('Active', 'Occupied')
    LIMIT 1
";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $unit_id);
$stmt->execute();
$renter = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$renter) {
    echo "<div style='padding:10px;background:#f8d7da;color:#721c24;border-radius:5px;'>
            <strong>No active renter found for unit <code>{$unit_name}</code>.</strong>
          </div>
          <script>
             setTimeout(function() { 
                 window.location.href = 'billing.php'; 
             }, 2000); // 2 seconds
          </script>";
    exit;
}

// Validate agreement status
if (empty($renter['agreement_status'])) {
    echo "<div style='padding:10px;background:#fff3cd;color:#856404;border-radius:5px;'>
            <strong>This renter has no rental agreement.</strong><br>
            Please create or accept the rental agreement before generating a bill.
          </div>
          <script>
             setTimeout(function() { 
                 window.location.href = 'billing.php'; 
             }, 3000);
          </script>";
    exit;
}

if ($renter['agreement_status'] === 'pending') {
    echo "<div style='padding:10px;background:#fff3cd;color:#856404;border-radius:5px;'>
            <strong>The agreement for this renter is still pending.</strong><br>
            Please wait until the renter accepts the agreement before generating a bill.
          </div>
          <script>
             setTimeout(function() { 
                 window.location.href = 'billing.php'; 
             }, 3000);
          </script>";
    exit;
}


 //STEP 3: Generate Monthly Bills

$count = 0;
$generated_by   = $_SESSION['auth_user']['username'] ?? 'System';
$generated_role = $_SESSION['auth_role'] ?? 'admin';

$renter_id   = (int)$renter['renter_id'];
$lease_term  = (int)$renter['lease_term'];
$contact     = $renter['contacts'];
$renter_name = $renter['first_name'] . ' ' . $renter['last_name'];
$start_date  = new DateTime($renter['move_in_date']);
$start_date->modify('+1 month');

for ($i = 0; $i < $lease_term; $i++) {

    // $due_date = (clone $start_date)->modify("+{$i} months")->format('Y-m-d');
    $due_date = (clone $start_date)->modify("+{$i} month")->format('Y-m-d');
    $billing_month = date('F Y', strtotime($due_date));
    $reference_id = generateReferenceID();

    // Check duplicate bill for same month/year
    $check_sql = "
        SELECT id FROM bills
        WHERE renter_id = ? 
          AND DATE_FORMAT(due_date, '%Y-%m') = DATE_FORMAT(?, '%Y-%m')
        LIMIT 1
    ";
    $check_stmt = $con->prepare($check_sql);
    $check_stmt->bind_param("is", $renter_id, $due_date);
    $check_stmt->execute();
    $check_stmt->store_result();
    if ($check_stmt->num_rows > 0) {
        $check_stmt->close();
        continue;
    }
    $check_stmt->close();

    // Fetch carry balances
    $carry_adjustment = 0;
    $carry_note = '';
    $carry_balances = [];

    $carry_stmt = $con->prepare("
        SELECT * FROM create_balances 
        WHERE renter_id = ? AND applied_to_bill_id IS NULL
    ");
    $carry_stmt->bind_param("i", $renter_id);
    $carry_stmt->execute();
    $carry_result = $carry_stmt->get_result();

    while ($carry = $carry_result->fetch_assoc()) {
        $carry_balances[] = $carry;
        if ($carry['carry_type'] === 'overpaid') {
            $carry_adjustment -= $carry['amount'];
            $carry_note .= "Overpaid credit applied: ₱{$carry['amount']}\n";
        } elseif ($carry['carry_type'] === 'unpaid') {
            $carry_adjustment += $carry['amount'];
            $carry_note .= "Unpaid balance added: ₱{$carry['amount']}\n";
        }
    }
    $carry_stmt->close();

    // Compute total
    $addon_total  = 0.00;
    $late_fee     = 0.00;
    $total_amount = $unit_price + $addon_total + $late_fee + $carry_adjustment;

    // Insert bill
    $stmt = $con->prepare("
        INSERT INTO bills 
        (reference_id, renter_id, unit_id, due_date, unit_price, addon_total, total_amount, late_fee, status, note, generated_by, generated_role, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'open', ?, ?, ?, NOW())
    ");
    $stmt->bind_param(
        "siisdddssss",
        $reference_id, $renter_id, $unit_id, $due_date,
        $unit_price, $addon_total, $total_amount, $late_fee,
        $carry_note, $generated_by, $generated_role
    );

    if ($stmt->execute()) {
        $bill_id = $stmt->insert_id;
        $count++;

        // Mark carry balances as applied
        foreach ($carry_balances as $carry) {
            $update_stmt = $con->prepare("
                UPDATE create_balances SET applied_to_bill_id = ? WHERE id = ?
            ");
            $update_stmt->bind_param("ii", $bill_id, $carry['id']);
            $update_stmt->execute();
            $update_stmt->close();
        }

        // Optionally send SMS notification here
        $message = "Hello {$renter_name} your {$unit_name} ({$unit_type}) bill for " .
            date('F Y', strtotime($due_date)) .
            " has been generated. Amount: PHP {$total_amount}. Due: {$due_date}. Ref: {$reference_id}. Please avoid late payment our billing generate add-on late fee automatically- Moning Rental Services";
       sendSMS($contact, $message);
    }
    $stmt->close();
}


 //STEP 4: Success Message & Redirect

echo "<div style='padding:10px;background:#d4edda;color:#155724;border-radius:5px;'>
        <strong>{$count} bill(s) generated successfully for unit <code>{$unit_name}</code>.</strong><br>
        <small>Redirecting to billing page...</small>
      </div>";

echo "<script>
        setTimeout(function() {
            window.location.href = 'billing.php';
        }, 2000);
      </script>";
?>
