<?php
require 'config/dbcon.php';
require 'admin/config/paymongo.php';


$session_id  = $_GET['checkout_session_id'] ?? null;
$unit_id     = $_GET['unit_id'] ?? null;
$unit_name   = $_GET['unit_name'] ?? "Unit";

// Prospect customer info
$fname = $_GET['customer_fname'] ?? null;
$lname = $_GET['customer_lname'] ?? null;
$email = $_GET['customer_email'] ?? null;
$contact = $_GET['customer_contact'] ?? null;

$move_in  = $_GET['move_in'] ?? null;
// $move_out = $_GET['move_out'] ?? null;

if (!$session_id) {
    die("Missing payment reference.");
}

//Check payment status
$ch = curl_init("https://api.paymongo.com/v1/checkout_sessions/$session_id");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Basic " . base64_encode($secret_key . ":")
    ]
]);
$response = curl_exec($ch);
curl_close($ch);

if (!$response) {
    die("Unable to contact PayMongo");
}

$data = json_decode($response, true);
$status = $data['data']['attributes']['payment_status'] ?? 'unpaid';
$total_amount = $data['data']['attributes']['payment_intent']['attributes']['amount'] / 100 ?? 0;

// Create reservation code
$reservation_code = strtoupper("RES" . substr(md5(time() . rand()), 0, 8));

?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php
if ($status === "paid") {

    // INSERT into bookings table
    $stmt = $con->prepare("
        INSERT INTO bookings 
        (unit_id, unit_name, customer_fname, customer_lname, customer_email, customer_contact, move_in, total_amount, payment_type, payment_status, booking_status, reservation_code)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'gcash', 'paid', 'confirmed', ?)
    ");

    $stmt->bind_param("issssssds", 
        $unit_id, 
        $unit_name, 
        $fname, 
        $lname, 
        $email, 
        $contact, 
        $move_in, 
        $total_amount, 
        $reservation_code
    );

    $stmt->execute();
?>
<script>
Swal.fire({
    icon: 'success',
    title: 'GCash Payment Successful!',
    text: 'Your booking has been confirmed.',
    confirmButtonText: 'OK'
}).then(() => {
    window.location.href = 'thank_you.php?reservation=<?php echo $reservation_code; ?>';
});
</script>

<?php
} else {
?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Payment Not Completed',
    text: 'GCash payment was cancelled or failed.',
    confirmButtonText: 'OK'
}).then(() => {
    window.location.href = 'booking_form.php';
});
</script>
<?php
}
?>

</body>
</html>
