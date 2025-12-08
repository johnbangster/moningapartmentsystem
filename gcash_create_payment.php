<?php

require 'admin/config/paymongo.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

//Replace with your own PayMongo Test Secret Key

$unit_name = isset($_POST['unit_name']) ? trim($_POST['unit_name']) : 'Unit Reservation';


//Use the sent amount (in pesos)
if (isset($_POST['amount']) && is_numeric($_POST['amount'])) {
    $amount = floatval($_POST['amount']) * 100; // convert to centavos
} else {
    echo json_encode(["success" => false, "message" => "Invalid or missing amount"]);
    exit;
}


// Build payload
$payload = [
    "data" => [
        "attributes" => [
            "cancel_url" => "https://yourdomain.com/payment_cancel.php",
            "success_url" => "https://yourdomain.com/payment_success.php?session_id={CHECKOUT_SESSION_ID}",
            "line_items" => [[
                "amount" => intval($amount),
                "currency" => "PHP",
                "name" => $unit_name, 
                "quantity" => 1
            ]],
            "payment_method_types" => ["gcash"]
        ]
    ]
];

// Create checkout session
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
    echo json_encode(["success" => false, "message" => "Failed to contact PayMongo."]);
    exit;
}

$result = json_decode($response, true);

if ($http_code >= 400) {
    echo json_encode([
        "success" => false,
        "message" => "PayMongo API error",
        "error" => $result
    ]);
    exit;
}

$checkout_url = $result['data']['attributes']['checkout_url'] ?? null;
$session_id = $result['data']['id'] ?? null;

if ($checkout_url) {
    echo json_encode([
        "success" => true,
        "checkout_url" => $checkout_url,
        "payment_id" => $session_id
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Unexpected PayMongo response",
        "error" => $result
    ]);
}

?>
