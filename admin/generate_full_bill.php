<?php 
session_start();
require('config/dbcon.php');

date_default_timezone_set(timezoneId: 'Asia/Manila');

//Utility: Generate Unique Bill Reference
function generateReferenceID() {
    return 'BILL-' . strtoupper(uniqid());
}

//SMS Function with Semaphore API + Logging
function sendSMS($contact, $message) {
    $apikey = 'ad1be2dd7b2999a0458b90c264aa4966';  //Semaphore key
    $sender = 'MONINGSRENT';

    //Normalize number before sending
    $contact = trim($contact);

    // Convert 09XXXXXXXXX → 639XXXXXXXXX
    if (preg_match('/^0\d{10}$/', $contact)) {
        $formatted = '63' . substr($contact, 1);
    } elseif (preg_match('/^63\d{10}$/', $contact)) {
        $formatted = $contact; // already in correct format
    } else {
        if (!is_dir('logs')) mkdir('logs', 0777, true);
        file_put_contents('logs/sms_log.txt', "[".date('Y-m-d H:i:s')."] Invalid number: {$contact}\n", FILE_APPEND);
        return false;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.semaphore.co/api/v4/messages');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'apikey' => $apikey,
        'number' => $formatted,
        'message' => $message,
        'sendername' => $sender
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if (!is_dir('logs')) mkdir('logs', 0777, true);
    $logFile = 'logs/sms_log.txt';
    $timestamp = date('Y-m-d H:i:s');

    if ($error) {
        $logMessage = "[$timestamp] ERROR sending SMS to {$formatted} | CURL Error: {$error}\n";
    } else {
        $logMessage = "[$timestamp] Sent to: {$formatted} | Message: {$message} | Response: {$response}\n";
    }

    file_put_contents($logFile, $logMessage, FILE_APPEND);
    return $response;
}

//Get All Active Renters with Units

$sql = "SELECT r.id AS renter_id, 
               r.first_name, r.last_name, r.middle_name, 
               r.move_in_date, r.lease_term, r.unit_id, r.contacts,
               u.price AS unit_price, u.name
        FROM renters r
        JOIN units u ON r.unit_id = u.id
        WHERE r.status = 'Active'";

$result = $con->query($sql);

//Bill Generation per Lease Term

if ($result && $result->num_rows > 0) {
    $count = 0;

    while ($renter = $result->fetch_assoc()) {
        $renter_id  = $renter['renter_id'];
        $unit_id    = $renter['unit_id'];
        $lease_term = (int)$renter['lease_term'];
        $unit_price = (float)$renter['unit_price'];
        $contact    = $renter['contacts'];
        $renter_name = $renter['first_name'] . ' ' . $renter['last_name'];
        $unit_name  = $renter['name'];
        $start_date = new DateTime($renter['move_in_date']);

        for ($i = 0; $i < $lease_term; $i++) {
            $due_date = (clone $start_date)->modify("+{$i} months")->format('Y-m-d');
            $month = (int)date('m', strtotime($due_date));
            $year  = (int)date('Y', strtotime($due_date));
            $reference_id = generateReferenceID();

            // Prevent duplicate bills per month
            $check = $con->prepare("SELECT COUNT(*) FROM bills WHERE renter_id = ? AND MONTH(due_date) = ? AND YEAR(due_date) = ?");
            $check->bind_param("iii", $renter_id, $month, $year);
            $check->execute();
            $exists = $check->get_result()->fetch_row()[0];
            $check->close();

            if ($exists == 0) {
                $stmt = $con->prepare("INSERT INTO bills (reference_id, renter_id, unit_id, due_date, total_amount) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("siisd", $reference_id, $renter_id, $unit_id, $due_date, $unit_price);

                if ($stmt->execute()) {
                    $count++;
                    $message = "Hello {$renter_name}! Your {$unit_name} bill for " . 
                               date('F Y', strtotime($due_date)) . 
                               " has been generated. Amount: PHP {$unit_price}. Due: {$due_date}. Ref: {$reference_id}. - Moning Rental Services";
                    sendSMS($contact, $message);
                }
                $stmt->close();
            }
        }
    }

    echo "<div style='padding:10px; background:#d4edda; color:#155724; border-radius:5px;'>
            <strong>$count bills generated successfully!</strong><br>
            SMS notifications sent to renters.<br>
            <small>Check <code>logs/sms_log.txt</code> for details.</small>
          </div>";

} else {
    echo "<div style='padding:10px; background:#f8d7da; color:#721c24; border-radius:5px;'>
            <strong>No active renters found.</strong>
          </div>";
}
?>
