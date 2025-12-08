<?php
function verifyPayPalPayment($transactionId,$expectedAmount,$expectedCurrency = "PHP") {

    //Paypal sanbox API
    $clientId = "Ab-W1WHCrsBe68cL4bNydaxSyqk4VpR88F_uZYB5J-S-CJJLGpy-3t88rYTXUco_U3NGgqplr0girCnE";
    $clientSecret = "EC2XO2GpNzF_YKPF6VhlBAcy2q9YRC7x-4xxwnzZsPOstyrPTWcO_3StAtpUZGcUreeRO9zP7AQcYLEz";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/oauth2/token");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Accept: application/json", "Accept-Language: en_US"]);
    curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $clientSecret);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    if(!$result)return false;
    $tokenInfo =json_decode($result,true);
    curl_close($ch);

    if(!isset($tokenInfo['access_token'])) return false;
    $accessToken = $tokenInfo['access_token'];

    //verify transaction
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v2/checkout/orders/" . urlencode($transactionId));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $accessToken
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    if (!$result) return false;
    $orderData = json_decode($result, true);
    curl_close($ch);

    //validate
    if(
        isset($orderData['status']) &&
        strtolower($orderData['status']) === 'completed' &&
        isset($orderData['purchase_units'][0]['amount']['value']) &&
        floatval($orderData['purchase_units'][0]['amount']['value']) == floatval($expectedAmount) &&
        $orderData['purchase_units'][0]['amount']['currency_code'] === $expectedCurrency
    ) 
    {
        return true;
    }
    return false;
}