<?php
session_start();
require 'config/dbcon.php';

header('Content-Type: application/json');

// Default response
$response = ['success' => false];

// Detect AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$user_id   = intval($_SESSION['auth_user']['user_id'] ?? 0);
$auth_role = $_SESSION['auth_role'] ?? '';

if (isset($_GET['id'])) {
    
     //MARK SINGLE NOTIFICATION AS READ
   
    $id = intval($_GET['id']);
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = $id";
    if (mysqli_query($con, $sql)) {
        $response['success'] = true;
    }

} elseif (isset($_GET['all'])) {
    
     // MARK ALL AS READ BASED ON ROLE
   
    switch ($auth_role) {
        case 'admin':
            $sql = "
                UPDATE notifications 
                SET is_read = 1 
                WHERE type IN (
                    'bill_paid', 'cash_payment', 'paypal_payment', 'complaint',
                    'agreement_admin', 'unit_added_admin', 'monthly_due_admin'
                )
            ";
            break;

        case 'employee':
            $sql = "
                UPDATE notifications 
                SET is_read = 1 
                WHERE type IN (
                    'bill_paid', 'cash_payment', 'paypal_payment','complaint',
                    'agreement_employee', 'unit_added_employee', 'monthly_due_employee'
                )
            ";
            break;

        case 'renter':
            $sql = "
                UPDATE notifications 
                SET is_read = 1 
                WHERE user_id = $user_id 
                AND type IN (
                    'bill_created', 'agreement_renter', 'monthly_due_renter',
                    'cash_payment', 'paypal_payment', 'complaint'
                )
            ";
            break;

        default:
            // Unknown or unauthenticated role
            $sql = "";
            break;
    }

    if (!empty($sql) && mysqli_query($con, $sql)) {
        $response['success'] = true;
    }
}


  //RESPONSE HANDLING

if ($isAjax) {
    echo json_encode($response);
    exit;
} else {
    // Redirect if accessed directly
    $redirect = $_SERVER['HTTP_REFERER'] ?? 'all_notifications.php';
    header("Location: $redirect");
    exit;
}
?>
