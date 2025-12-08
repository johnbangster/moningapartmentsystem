<?php
require '../vendor/autoload.php';
use Semaphore\SemaphoreClient;

function sendSMS($number, $message) {
    $apiKey = 'YOUR_SEMAPHORE_API_KEY'; // ← replace with your real Semaphore API key
    $client = new SemaphoreClient($apiKey);

    try {
        $response = $client->send($number, $message);
        return $response;
    } catch (Exception $e) {
        error_log('SMS Error: ' . $e->getMessage());
        return false;
    }
}
?>
