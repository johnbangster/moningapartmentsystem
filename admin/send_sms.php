<?php
// function sendSMS($contact, $message)
// {
//     $apikey = 'ad1be2dd7b2999a0458b90c264aa4966';
//     $sender = 'MONINGSRENT';
//     $timestamp = date('Y-m-d H:i:s');

//     // Ensure log directory exists
//     if (!is_dir('logs')) {
//         mkdir('logs', 0777, true);
//     }
//     $logFile = 'logs/sms_log.txt';

//     // -------------------------
//     // Normalize Phone Number
//     // -------------------------
//     $contact = trim($contact);

//     if (preg_match('/^0\d{10}$/', $contact)) {
//         $number = '63' . substr($contact, 1);
//     } elseif (preg_match('/^63\d{10}$/', $contact)) {
//         $number = $contact;
//     } else {
//         file_put_contents(
//             $logFile,
//             "[$timestamp] Invalid number: {$contact}\n",
//             FILE_APPEND
//         );
//         return false;
//     }

//     // -------------------------
//     // Initialize CurlHandle (PHP 8+)
//     // -------------------------
//     $ch = curl_init();

//     curl_setopt_array($ch, [
//         CURLOPT_URL            => 'https://api.semaphore.co/api/v4/messages',
//         CURLOPT_POST           => true,
//         CURLOPT_POSTFIELDS     => http_build_query([
//             'apikey'     => $apikey,
//             'number'     => $number,
//             'message'    => $message,
//             'sendername' => $sender
//         ]),
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_TIMEOUT        => 20,
//     ]);

//     // -------------------------
//     // Execute request
//     // -------------------------
//     $response = curl_exec($ch);
//     $curlErr  = curl_error($ch);

//     // In PHP 8.4+, CurlHandle is an object → destructor auto-closes
//     unset($ch);

//     // -------------------------
//     // Logging
//     // -------------------------
//     if ($curlErr) {
//         file_put_contents(
//             $logFile,
//             "[$timestamp] ERROR sending SMS to {$number} | CURL Error: {$curlErr}\n",
//             FILE_APPEND
//         );
//         return false;
//     }

//     file_put_contents(
//         $logFile,
//         "[$timestamp] Sent to: {$number} | Message: {$message} | Response: {$response}\n",
//         FILE_APPEND
//     );

//     return $response;
// }

function sendSms($contact, $message)
{
    $apikey = 'ad1be2dd7b2999a0458b90c264aa4966';
    $sender = 'MONINGSRENT';
    $timestamp = date('Y-m-d H:i:s');

    if (!is_dir('logs')) {
        mkdir('logs', 0777, true);
    }
    $logFile = 'logs/sms_log.txt';

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.semaphore.co/api/v4/messages',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'apikey'     => $apikey,
            'number'     => $contact,
            'message'    => $message,
            'sendername' => $sender,
        ]),
        CURLOPT_RETURNTRANSFER => true,
    ]);

    $raw = curl_exec($ch);
    $curlErr = curl_error($ch);

    unset($ch); // modern PHP

    if ($curlErr) {
        file_put_contents($logFile, "[$timestamp] ERROR sending SMS: $curlErr\n", FILE_APPEND);
        return [
            'success' => false,
            'error' => $curlErr,
            'raw' => null
        ];
    }

    file_put_contents($logFile, "[$timestamp] SMS Sent: $raw\n", FILE_APPEND);
    
    return [
        'success' => true,
        'error' => null,
        'raw' => $raw
    ];
}

