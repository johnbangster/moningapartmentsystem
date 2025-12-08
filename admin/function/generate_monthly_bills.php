<?php
    require_once('../config/dbcon.php');

    $today = date('Y-m-d');

    $renter_q = $con->query("SELECT * FROM renters WHERE status = 'Active'");

    while ($renter = $renter_q->fetch_assoc()) 
    {
        $start = new DateTime($renter['move_in_date']);
        $end = (clone $start)->modify("+{$renter['lease_term']} months");
        $now = new DateTime();

        if ($now < $end) {
            $due_date = $now->modify('+3 days')->format('Y-m-d'); // Next due in 3 days

            // Check if a bill for this month already exists
            $exists = $con->prepare("SELECT COUNT(*) FROM billings WHERE renter_id = ? AND MONTH(due_date) = MONTH(CURDATE()) AND YEAR(due_date) = YEAR(CURDATE())");
            $exists->bind_param("i", $renter['id']);
            $exists->execute();
            $exists_count = $exists->get_result()->fetch_row()[0];

            if ($exists_count == 0) {
                $ref = 'BILL-' . strtoupper(uniqid());
                $unit_price_q = $con->query("SELECT price FROM units WHERE id = " . $renter['unit_id']);
                $unit_price = $unit_price_q->fetch_assoc()['price'];

                $insert = $con->prepare("INSERT INTO billings (renter_id, unit_id, reference_id, due_date, amount) VALUES (?, ?, ?, ?, ?)");
                $insert->bind_param("iissd", $renter['id'], $renter['unit_id'], $ref, $due_date, $unit_price);
                $insert->execute();

                // Send SMS (use your SMS gateway API here)
                file_get_contents("https://sms-api/send?to={$renter['contacts']}&msg=" . urlencode("New bill due on $due_date. Ref: $ref."));
            }
        }
    }
?>
