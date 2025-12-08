<?php
session_start();
require_once('../admin/config/dbcon.php');

header('Content-Type: application/json');

$response = ['unread' => 0, 'html' => ''];

// Make sure renter is logged in
if (!isset($_SESSION['auth_user']) || $_SESSION['auth_role'] !== 'renter') {
    echo json_encode($response);
    exit;
}

$user_id = intval($_SESSION['auth_user']['user_id']);

// Count unread notifications for this renter
$count_sql = "
    SELECT COUNT(*) AS total 
    FROM notifications 
    WHERE user_id = $user_id 
      AND type IN ('bill_paid', 'cash_payment', 'paypal_payment', 'complaint')
      AND is_read = 0
";
$count_result = mysqli_query($con, $count_sql);
if ($count_row = mysqli_fetch_assoc($count_result)) {
    $response['unread'] = $count_row['total'];
}

// Fetch latest 5 notifications
$list_sql = "
    SELECT * FROM notifications 
    WHERE user_id = $user_id 
      AND type IN ('bill_paid', 'cash_payment', 'paypal_payment', 'complaint')
    ORDER BY created_at DESC 
    LIMIT 5
";
$list_result = mysqli_query($con, $list_sql);

if (mysqli_num_rows($list_result) > 0) {
    while ($row = mysqli_fetch_assoc($list_result)) {
        $icon = '';
        if ($row['type'] === 'paypal_payment') $icon = '';
        if ($row['type'] === 'cash_payment')   $icon = '';
        if ($row['type'] === 'complaint')      $icon = '';
        if ($row['type'] === 'bill_paid')      $icon = '';

        $response['html'] .= "
            <li class='dropdown-item d-flex justify-content-between align-items-center'>
                <span>{$icon} {$row['message']}</span>
                <small class='text-muted'>" . date('M d, Y h:i A', strtotime($row['created_at'])) . "</small>
            </li>
        ";
    }
} else {
    $response['html'] = "<li class='dropdown-item text-muted'>No new notifications</li>";
}

echo json_encode($response);
exit;
?>
