<?php
function sendSMS($contact, $message) {
    $apikey = 'ad1be2dd7b2999a0458b90c264aa4966'; // Replace with your Semaphore API key

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.semaphore.co/api/v4/messages');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'apikey'     => $apikey,
        'number'     => $contact,
        'message'    => $message,
        'sendername' => 'SEMAPHORE', // optional approved sender name
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if ($error = curl_error($ch)) {
        echo "CURL Error: $error";
    } else {
        echo "<pre>$response</pre>";
    }

    curl_close($ch);
}

//Test it
sendSMS('09612822106', 'This is official SMS for Moning Rental Services');
