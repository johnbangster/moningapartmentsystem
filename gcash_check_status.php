<?php
// gcash_check_status.php
header('Content-Type: application/json');

if (!isset($_GET['payment_id'])) {
    echo json_encode(["paid" => false, "message" => "Missing payment_id"]);
    exit;
}

$secret_key = 'sk_test_your_api_key_here';
$payment_id = $_GET['payment_id'];

$ch = curl_init("https://api.paymongo.com/v1/checkout_sessions/" . $payment_id);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Basic " . base64_encode($secret_key . ":"),
        "Content-Type: application/json"
    ]
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$response) {
    echo json_encode(["paid" => false, "message" => "No response from PayMongo"]);
    exit;
}

$result = json_decode($response, true);

if ($http_code >= 400) {
    echo json_encode(["paid" => false, "message" => "API Error", "error" => $result]);
    exit;
}

// ✅ Check status
$status = $result['data']['attributes']['status'] ?? 'unpaid';
$is_paid = ($status === 'paid');

echo json_encode(["paid" => $is_paid, "status" => $status]);
?>
