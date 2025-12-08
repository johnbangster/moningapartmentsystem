<?php
require_once 'config/dbcon.php';
require 'authentication.php';
require 'includes/header.php';

// SEMAPHORE SMS Function

function sendSMS($contact, $message) {
    $apikey = 'YOUR_SEMAPHORE_API_KEY'; // Replace with real key
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.semaphore.co/api/v4/messages');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'apikey' => $apikey,
        'number' => $contact,
        'message' => $message,
        'sendername' => 'RentalSys'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}


// BILL GENERATION HANDLER

if (isset($_POST['generate_bill'])) {
    $renter_id = intval($_POST['renter_id']);
    $addons = [];

    // Sanitize add-ons
    if (isset($_POST['addons']['name'], $_POST['addons']['value'])) {
        $addon_names = $_POST['addons']['name'];
        $addon_values = $_POST['addons']['value'];

        for ($i = 0; $i < count($addon_names); $i++) {
            $name = trim(strip_tags($addon_names[$i]));
            $value = floatval($addon_values[$i]);
            if (!empty($name) && $value > 0) {
                $addons[$name] = $value;
            }
        }
    }

    // Fetch renter and unit details
    $renter_q = mysqli_query($con, "
        SELECT r.id, r.lease_term, r.move_in_date, u.unit_price, u.id AS unit_id, r.contact 
        FROM renters r 
        JOIN units u ON r.unit_id = u.id 
        WHERE r.id = '$renter_id' LIMIT 1
    ");
    $renter = mysqli_fetch_assoc($renter_q);

    if (!$renter) {
        $_SESSION['error'] = "Renter not found!";
        header("Location: billing.php");
        exit();
    }

    $lease_months = (int)$renter['lease_term'];
    $start = new DateTime($renter['move_in_date']);
    $unit_price = (float)$renter['unit_price'];
    $unit_id = $renter['unit_id'];
    $contact = $renter['contact'];

    // Prepare insert query
    $stmt = mysqli_prepare($con, "
        INSERT INTO bills (renter_id, unit_id, billing_month, due_date, total_amount, status, addons) 
        VALUES (?, ?, ?, ?, ?, 'unpaid', ?)
    ");

    for ($i = 0; $i < $lease_months; $i++) {
        $bill_date = clone $start;
        $bill_date->modify("+$i months");
        $due_date = clone $bill_date;
        $due_date->modify("+3 days");

        $billing_month = $bill_date->format('Y-m');
        $due = $due_date->format('Y-m-d');

        $addon_total = 0;
        $addon_list = [];

        foreach ($addons as $key => $amount) {
            $addon_total += $amount;
            $addon_list[] = "$key:$amount";
        }

        $total = $unit_price + $addon_total;
        $addon_string = implode(',', $addon_list);

        // Bind values and insert
        mysqli_stmt_bind_param($stmt, "iissds", $renter_id, $unit_id, $billing_month, $due, $total, $addon_string);
        mysqli_stmt_execute($stmt);

        // Send SMS
        $sms_message = "Moning's Rental: ₱$total due for $billing_month. Due: $due.";
        sendSMS($contact, $sms_message);
    }

    mysqli_stmt_close($stmt);
    $_SESSION['message'] = "Bills generated successfully.";
    header("Location: billing.php");
    exit();
}
?>

<!--HTML Form to Generate Bills-->
<div class="container mt-4">
    <h4>Create Bills</h4>
    <form action="generate_bill.php" method="POST">
        <div class="mb-3">
            <label>Select Renter</label>
            <select name="renter_id" class="form-select" required>
                <option value="">-- Choose Renter --</option>
                <?php
                $renters = mysqli_query($con, "
                    SELECT r.id, r.first_name, r.last_name, u.unit_id 
                    FROM renters r 
                    JOIN units u ON r.unit_id = u.id 
                    WHERE r.status = 'Active'
                ");
                while ($r = mysqli_fetch_assoc($renters)) {
                    echo "<option value='{$r['id']}'>
                            {$r['first_name']} {$r['last_name']} - Unit {$r['unit_id']}
                          </option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Optional Add-ons (e.g. Internet, Water)</label>
            <div id="addonsContainer">
                <div class="row mb-2">
                    <div class="col">
                        <input type="text" name="addons[name][]" class="form-control" placeholder="Add-on name (e.g. Internet)" />
                    </div>
                    <div class="col">
                        <input type="number" name="addons[value][]" class="form-control" placeholder="Amount" step="0.01" />
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-secondary" onclick="addAddon()">Add More</button>
        </div>

        <button type="submit" name="generate_bill" class="btn btn-dark">Generate Bills</button>
    </form>
</div>

<script>
function addAddon() {
    const html = `
    <div class="row mb-2">
        <div class="col"><input type="text" name="addons[name][]" class="form-control" placeholder="Add-on name" /></div>
        <div class="col"><input type="number" name="addons[value][]" class="form-control" placeholder="Amount" step="0.01" /></div>
    </div>`;
    document.getElementById('addonsContainer').insertAdjacentHTML('beforeend', html);
}
</script>
