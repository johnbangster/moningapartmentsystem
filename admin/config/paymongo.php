<?php
// PayMongo secret key
// Loads the secret key from environment variable for better security.
// Make sure your .env file contains: PAYMONGO_SECRET_KEY=sk_test_A629QN6Ee8QgKew23g5FZ5LH

// If using vlucas/phpdotenv, load .env here. Otherwise, ensure the environment variable is set.
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    if (class_exists('Dotenv\\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
    }
}

$secret_key = getenv('PAYMONGO_SECRET_KEY');
if (!$secret_key) {
    die('PayMongo secret key not set in environment.');
}
