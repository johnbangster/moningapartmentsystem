<?php
session_start();
require('../admin/config/dbcon.php');
require ('includes/header.php');

// Check if the user is logged in by verifying the session variable
if (!isset($_SESSION['auth_user']['user_id'])) {
    // Redirect to login page if the user is not logged in
    header("Location: login.php");
    exit;
}

// Get the renter's user_id from session
$renter_id = $_SESSION['auth_user']['user_id'];

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// SQL to join bills and renters and filter by lease_term (6-12 months) and renter_id
$sql = "SELECT 
            bills.*,
            renters.first_name,
            renters.last_name,
            renters.lease_term,
            renters.move_in_date
        FROM bills
        INNER JOIN renters ON bills.renter_id = renters.id
        WHERE renters.lease_term BETWEEN 6 AND 12
        AND bills.renter_id = $renter_id";  // Filter by logged-in renter's id

$result = mysqli_query($con, $sql);

// Check for results
if (mysqli_num_rows($result) > 0) {
    echo "<h2>Your Bills (Lease Term 6-12 Months)</h2>";
    echo "<table border='1' cellpadding='10'>
            <tr>
                <th>Bill ID</th>
                <th>Billing Month</th>
                <th>Due Date</th>
                <th>Unit Price</th>
                <th>Addons</th>
                <th>Total</th>
                <th>Late Fee</th>
                <th>Status</th>
            </tr>";

    // Output the data for each bill
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['billing_month']}</td>
                <td>{$row['due_date']}</td>
                <td>{$row['unit_price']}</td>
                <td>{$row['addon_total']}</td>
                <td>{$row['total_amount']}</td>
                <td>{$row['late_fee']}</td>
                <td>{$row['status']}</td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "No bills found for your lease term between 6 and 12 months.";
}

// Close connection
mysqli_close($con);

include('includes/footer.php');
include('includes/scripts.php');
?>




