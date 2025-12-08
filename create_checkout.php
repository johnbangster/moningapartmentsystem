<?php
require 'admin/config/dbcon.php';
require 'admin/config/paymongo.php';
header('Content-Type: application/json');



// 1. Collect POST data from booking form
$unit_id     = $_POST['unit_id'] ?? null;
$unit_name   = $_POST['unit_name'] ?? null;
$full_name   = $_POST['full_name'] ?? null;
$email       = $_POST['email'] ?? null;
$contact     = $_POST['contact'] ?? null;
$move_in     = $_POST['move_in'] ?? null;
// $move_out    = $_POST['move_out'] ?? null;
$total_amount = $_POST['total_amount'] ?? null;

if (!$unit_id || !$unit_name || !$full_name || !$move_in || !$total_amount) {
    echo json_encode(["success"=>false, "message"=>"Missing required fields."]);
    exit;
}

// 2. Convert amount to centavos
$amount = intval(floatval($total_amount) * 100);

// 3. Prepare payload with metadata (so webhook can read extra info)
$payload = [
    "data" => [
        "attributes" => [
            "success_url" => "https://yourdomain.com/success_page.php",
            "cancel_url" => "https://yourdomain.com/cancel_page.php",
            "line_items" => [[
                "name" => $unit_name,
                "quantity" => 1,
                "amount" => $amount,
                "currency" => "PHP",
                "description" => "Booking for $unit_name"
            ]],
            "payment_method_types" => ["gcash"],
            "metadata" => [
                "unit_id" => $unit_id,
                "move_in" => $move_in,
                // "move_out" => $move_out,
                "full_name" => $full_name,
                "email" => $email,
                "contact" => $contact
            ]
        ]
    ]
];

// 4. Call PayMongo API to create checkout session
$ch = curl_init("https://api.paymongo.com/v1/checkout_sessions");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Basic " . base64_encode($secret_key . ":"),
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode($payload)
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$response) {
    echo json_encode(["success"=>false, "message"=>"Failed to contact PayMongo."]);
    exit;
}

$result = json_decode($response, true);

if ($http_code >= 400) {
    echo json_encode([
        "success"=>false,
        "message"=>"PayMongo API error",
        "error"=>$result
    ]);
    exit;
}

$checkout_url = $result['data']['attributes']['checkout_url'] ?? null;

if ($checkout_url) {
    echo json_encode([
        "success" => true,
        "checkout_url" => $checkout_url
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Unexpected PayMongo response",
        "error" => $result
    ]);
}
?>
