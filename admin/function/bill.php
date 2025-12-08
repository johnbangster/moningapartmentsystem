<?php
require_once('../config/dbcon.php'); // contains filteration()

// Functions
function generateReferenceId($prefix = 'BILL') {
    return $prefix . '-' . strtoupper(uniqid());
}

function generateMonthlyBills($renter, $unit, $con) {
    $start = new DateTime($renter['move_in_date']);
    $term = (int)$renter['lease_term'];
    $renter_id = $renter['id'];
    $unit_id = $unit['id'];
    $unit_price = $unit['price'];
    $due_day = (int)$start->format('d');

    for ($i = 0; $i < $term; $i++) {
        $due_date = (clone $start)->modify("+$i months");

        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $due_date->format('m'), $due_date->format('Y'));
        $day = min($due_day, $days_in_month); // Prevent Feb 30, etc.
        $due_date->setDate($due_date->format('Y'), $due_date->format('m'), $day);

        $reference_id = generateReferenceId();
        $date_str = $due_date->format('Y-m-d');

        $stmt = $con->prepare("INSERT INTO billings (reference_id, renter_id, unit_id, due_date, amount) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siisd", $reference_id, $renter_id, $unit_id, $date_str, $unit_price);
        $stmt->execute();
    }
}


function sendSMS($to, $message) {
    $api_key = 'YOUR_API_KEY';
    $sender = 'YourApp';
    $url = "https://api.semaphore.co/api/v4/messages";

    $data = [
        'apikey' => $api_key,
        'number' => $to,
        'message' => $message,
        'sendername' => $sender
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context  = stream_context_create($options);
    file_get_contents($url, false, $context);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renter_id'])) {
    $renter_id = intval($_POST['renter_id']);

    // Get renter and unit info
    $stmt = $con->prepare("
        SELECT r.id, r.move_in_date, r.lease_term, u.id as unit_id, u.name, u.price
        FROM renters r
        JOIN units u ON r.unit_id = u.id
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $renter_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        // Generate bills
        generateMonthlyBills($result, $result, $con);

        // Optionally, send SMS
        // sendSMS($phone, "Your billing schedule has been created!");

        echo "<script>alert('Bills generated successfully!');</script>";
    } else {
        echo "<script>alert('Renter not found.');</script>";
    }
}



// function generateMonthlyBills($renter, $unit, $con) {
//     $start = new DateTime($renter['move_in_date']);
//     $term = (int)$renter['lease_term'];
//     $renter_id = $renter['id'];
//     $unit_id = $unit['id'];
//     $unit_price = $unit['price'];

//     $due_day = (int)$start->format('d'); // Always due on same day

//     for ($i = 0; $i < $term; $i++) {
//         $due_date = (clone $start)->modify("+$i months");
        
//         // Align due date to same day (handles shorter months too)
//         $days_in_month = cal_days_in_month(CAL_GREGORIAN, $due_date->format('m'), $due_date->format('Y'));
//         $day = min($due_day, $days_in_month); // prevent invalid dates like Feb 30
//         $due_date->setDate($due_date->format('Y'), $due_date->format('m'), $day);

//         $reference_id = generateReferenceId();

//         $stmt = $con->prepare("INSERT INTO billings (reference_id, renter_id, unit_id, due_date, amount)
//                                VALUES (?, ?, ?, ?, ?)");
//         $date_str = $due_date->format('Y-m-d');
//         $stmt->bind_param("siisd", $reference_id, $renter_id, $unit_id, $date_str, $unit_price);
//         $stmt->execute();
//     }
// }
// function generateMonthlyBills($renter, $unit, $con) {
//     $start = new DateTime($renter['move_in_date']);
//     $term = (int)$renter['lease_term'];
//     $renter_id = (int)$renter['id'];
//     $unit_id = (int)$unit['id'];
//     $unit_price = (float)$unit['price'];

//     for ($i = 0; $i < $term; $i++) {
//         $due_date = (clone $start)->modify("+$i months");
//         $reference_id = generateReferenceId();

//         $stmt = $con->prepare("INSERT INTO billings (reference_id, renter_id, unit_id, due_date, amount)
//                                VALUES (?, ?, ?, ?, ?)");
//         $stmt->bind_param("siisd", $reference_id, $renter_id, $unit_id, $due_date->format('Y-m-d'), $unit_price);
//         $stmt->execute();
//     }
// }


// if (!function_exists('filteration')) {
//     function filteration($data) {
//         if (!is_array($data)) {
//             return htmlspecialchars(strip_tags(trim($data)));
//         }

//         foreach ($data as $key => $value) {
//             $data[$key] = htmlspecialchars(strip_tags(trim($value)));
//         }
//         return $data;
//     }
// }



// function generateReferenceId($prefix = 'BILL') {
//     return $prefix . '-' . date('Ym') . rand(100, 999); // e.g. BILL-202507123
// }

// function generateMonthlyBills($renter, $unit, $con) {
//     $start = new DateTime($renter['move_in_date']);
//     $term = (int)$renter['lease_term'];
//     $renter_id = $renter['id'];
//     $unit_id = $unit['id'];
//     $unit_price = $unit['price'];

//     for ($i = 0; $i < $term; $i++) {
//         $due_date = (clone $start)->modify("+$i months");
//         $reference_id = generateReferenceId();

//         // Insert bill
//         $stmt = $con->prepare("INSERT INTO billings (reference_id, renter_id, unit_id, due_date, amount)
//                                VALUES (?, ?, ?, ?, ?)");
//         $stmt->bind_param("siisd", $reference_id, $renter_id, $unit_id, $due_date->format('Y-m-d'), $unit_price);
//         $stmt->execute();
//     }
// }

// function notifyRenters($con) {
//     $today = date('Y-m-d');
//     $three_days = date('Y-m-d', strtotime("+3 days"));

//     $sql = "SELECT b.reference_id, b.due_date, r.contacts, r.first_name 
//             FROM billings b 
//             JOIN renters r ON b.renter_id = r.id
//             WHERE b.due_date = ? OR b.due_date = ?";

//     $stmt = $con->prepare($sql);
//     $stmt->bind_param("ss", $today, $three_days);
//     $stmt->execute();
//     $result = $stmt->get_result();

//     while ($row = $result->fetch_assoc()) {
//         $message = ($row['due_date'] == $today)
//             ? "Hi {$row['first_name']}, your rent bill (Ref: {$row['reference_id']}) is due today."
//             : "Hi {$row['first_name']}, your rent bill (Ref: {$row['reference_id']}) is due in 3 days.";

//         sendSMS($row['contacts'], $message);
//     }
// }

// function applyLateFees($con) {
//     $sql = "UPDATE billings 
//             SET late_interest = late_interest + (amount * 0.01)
//             WHERE is_paid = 0 AND due_date < CURDATE()";
//     $con->query($sql);
// }

// function sendSMS($to, $message) {
//     $api_key = 'YOUR_API_KEY';
//     $sender = 'YourApp';
//     $url = "https://api.semaphore.co/api/v4/messages";

//     $data = [
//         'apikey' => $api_key,
//         'number' => $to,
//         'message' => $message,
//         'sendername' => $sender
//     ];

//     $options = [
//         'http' => [
//             'header'  => "Content-type: application/x-www-form-urlencoded",
//             'method'  => 'POST',
//             'content' => http_build_query($data)
//         ]
//     ];

//     $context  = stream_context_create($options);
//     file_get_contents($url, false, $context);
// }

// function generateReferenceId($prefix = 'BILL') {
//     return $prefix . '-' . date('Ym') . rand(100, 999); // e.g. BILL-202507123
// }


// function generateMonthlyBills($renter, $unit, $con) {
//     $start = new DateTime($renter['move_in_date']);
//     $term = (int)$renter['lease_term'];
//     $renter_id = $renter['id'];
//     $unit_id = $unit['id'];
//     $unit_price = $unit['price'];

//     for ($i = 0; $i < $term; $i++) {
//         $due_date = (clone $start)->modify("+$i months");
//         $reference_id = "BILL-" . date("Ym") . "-" . rand(1000, 9999);

//         // Insert bill
//         $stmt = $con->prepare("INSERT INTO billings (reference_id, renter_id, unit_id, due_date, amount)
//                                VALUES (?, ?, ?, ?, ?)");
//         $stmt->bind_param("siisd", $reference_id, $renter_id, $unit_id, $due_date->format('Y-m-d'), $unit_price);
//         $stmt->execute();
//     }
// }



// function notifyRenters($con) {
//     $today = date('Y-m-d');
//     $three_days = date('Y-m-d', strtotime("+3 days"));

//     $sql = "SELECT b.reference_id, b.due_date, r.contacts, r.first_name 
//             FROM billings b 
//             JOIN renters r ON b.renter_id = r.id
//             WHERE b.due_date = ? OR b.due_date = ?";

//     $stmt = $con->prepare($sql);
//     $stmt->bind_param("ss", $today, $three_days);
//     $stmt->execute();
//     $result = $stmt->get_result();

//     while ($row = $result->fetch_assoc()) {
//         $message = ($row['due_date'] == $today)
//             ? "Hi {$row['first_name']}, your rent bill (Ref: {$row['reference_id']}) is due today."
//             : "Hi {$row['first_name']}, your rent bill (Ref: {$row['reference_id']}) is due in 3 days.";

//         sendSMS($row['contacts'], $message);
//     }
// }



// function applyLateFees($con) {
//     $sql = "UPDATE billings 
//             SET late_interest = late_interest + (amount * 0.01)
//             WHERE is_paid = 0 AND due_date < CURDATE()";
//     $con->query($sql);
// }




// function sendSMS($to, $message) {
//     $api_key = 'YOUR_API_KEY';
//     $sender = 'YourApp';
//     $url = "https://api.semaphore.co/api/v4/messages";

//     $data = [
//         'apikey' => $api_key,
//         'number' => $to,
//         'message' => $message,
//         'sendername' => $sender
//     ];

//     $options = [
//         'http' => [
//             'header'  => "Content-type: application/x-www-form-urlencoded",
//             'method'  => 'POST',
//             'content' => http_build_query($data)
//         ]
//     ];

//     $context  = stream_context_create($options);
//     file_get_contents($url, false, $context);
// } 

// function checkAndNotifyBills($con) {
//     $today = new DateTime();
//     $threeDaysAhead = (new DateTime())->modify('+3 days');

//     $sql = "SELECT b.*, r.contacts FROM billings b 
//             JOIN renters r ON b.renter_id = r.id
//             WHERE b.due_date = ? OR b.due_date = ?";

//     $stmt = $con->prepare($sql);
//     $stmt->bind_param("ss", $threeDaysAhead->format('Y-m-d'), $today->format('Y-m-d'));
//     $stmt->execute();
//     $result = $stmt->get_result();

//     while($bill = $result->fetch_assoc()) {
//         $message = ($bill['due_date'] == $today->format('Y-m-d')) ?
//             "Reminder: Your rent bill (Ref: {$bill['reference_id']}) is due today." :
//             "Notice: Your rent bill (Ref: {$bill['reference_id']}) is due in 3 days.";

//         sendSMS($bill['contact_number'], $message);
//     }
// }


?>