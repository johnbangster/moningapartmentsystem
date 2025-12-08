<?php
require_once('config/dbcon.php'); 


function generateReferenceId($prefix = 'BILL') {
    return $prefix . '-' . date('Ym') . rand(100, 999);
}

function generateMonthlyBills($renter, $unit, $con) {
    $start = new DateTime($renter['move_in_date']);
    $term = (int)$renter['lease_term'];
    $renter_id = $renter['id'];
    $unit_id = $unit['id'];
    $unit_price = $unit['price'];

    for ($i = 0; $i < $term; $i++) {
        $due_date = clone $start;
        $due_date->modify("+$i months");

        $reference_id = generateReferenceId();
        $date_str = $due_date->format('Y-m-d'); //FIXED HERE

        $stmt = $con->prepare("INSERT INTO billing (reference_id, renter_id, unit_id, due_date, amount)
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siisd", $reference_id, $renter_id, $unit_id, $date_str, $unit_price);
        $stmt->execute();
    }
}


function notifyRenters($con) {
    $today = date('Y-m-d');
    $three_days = date('Y-m-d', strtotime("+3 days"));

    $sql = "SELECT b.reference_id, b.due_date, r.contacts, r.first_name 
            FROM billing b 
            JOIN renters r ON b.renter_id = r.id
            WHERE b.due_date = ? OR b.due_date = ?";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("ss", $today, $three_days);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $message = ($row['due_date'] == $today)
            ? "Hi {$row['first_name']}, your rent bill (Ref: {$row['reference_id']}) is due today."
            : "Hi {$row['first_name']}, your rent bill (Ref: {$row['reference_id']}) is due in 3 days.";

        echo "📱 Simulated SMS to {$row['contacts']}: $message<br>";
    }
}

function applyLateFees($con) {
    $sql = "UPDATE billing 
            SET late_interest = late_interest + (amount * 0.01)
            WHERE is_paid = 0 AND due_date < CURDATE()";
    $con->query($sql);
}

// ------------------ SIMULATION STARTS HERE ------------------ //

// Fetch renter and unit
$renter = $con->query("SELECT * FROM renter LIMIT 1")->fetch_assoc();
$unit = $con->query("SELECT * FROM unit LIMIT 1")->fetch_assoc();

// Simulate monthly bill generation
generateMonthlyBills($renter, $unit, $con);
echo "✅ Bills generated for renter ID {$renter['id']}<br>";

// Simulate notifications
notifyRenters($con);

// Simulate applying late fees
applyLateFees($con);
echo "💸 Late fees applied to unpaid overdue bills.<br>";
?>
