<?php
session_start();
require 'config/dbcon.php';

header('Content-Type: application/json');

$response = [
    'unread' => 0,
    'html'   => '<li><span class="dropdown-item text-muted">No notifications</span></li>'
];

if (isset($_SESSION['auth_user'])) {
    $user_id   = intval($_SESSION['auth_user']['user_id']);
    $auth_role = $_SESSION['auth_role'] ?? '';

    //NOTIFICATION FILTERS BY ROLE
     
    switch ($auth_role) {
        case 'admin':
            // Admin sees all payment & management notifications
            $notif_types = "'bill_paid', 'cash_payment', 'paypal_payment', 'complaint', 
                            'agreement_admin', 'unit_added_admin', 'monthly_due_admin','reservation_admin'";
            break;

        case 'employee':
            // Employee sees assigned payment and management notifications
            $notif_types = "'bill_paid', 'cash_payment', 'paypal_payment','complaint',
                            'agreement_employee', 'unit_added_employee', 'monthly_due_employee','reservation_employee'";
            break;

        case 'renter':
            // Renter sees their own bill + payment + due date + agreement
            $notif_types = "'bill_created', 'agreement_renter', 'monthly_due_renter', 
                            'bill_paid', 'cash_payment', 'paypal_payment','complaint' ";
            break;

        default:
            echo json_encode($response);
            exit;
    }

    // COUNT UNREAD NOTIFICATIONS
  
    if ($auth_role === 'renter') {
        $count_sql = "
            SELECT COUNT(*) AS total 
            FROM notifications 
            WHERE user_id = $user_id 
              AND type IN ($notif_types)
              AND is_read = 0
        ";
    } else {
        $count_sql = "
            SELECT COUNT(*) AS total 
            FROM notifications 
            WHERE type IN ($notif_types)
              AND is_read = 0
        ";
    }

    $count_res = mysqli_query($con, $count_sql);
    if ($row = mysqli_fetch_assoc($count_res)) {
        $response['unread'] = (int)$row['total'];
    }

    // FETCH RECENT NOTIFICATIONS (LIMIT 5)
  
    if ($auth_role === 'renter') {
        $notif_sql = "
            SELECT * FROM notifications 
            WHERE user_id = $user_id 
              AND type IN ($notif_types)
            ORDER BY created_at DESC LIMIT 5
        ";
    } else {
        $notif_sql = "
            SELECT * FROM notifications 
            WHERE type IN ($notif_types)
            ORDER BY created_at DESC LIMIT 5
        ";
    }

    $notif_res = mysqli_query($con, $notif_sql);
    if ($notif_res && mysqli_num_rows($notif_res) > 0) {
        $html = '';
        while ($row = mysqli_fetch_assoc($notif_res)) {
            $isUnread = $row['is_read'] == 0;
            $readClass = $isUnread ? 'fw-bold text-dark' : 'text-muted';
            $newBadge  = $isUnread ? '<span class="badge bg-success ms-2">New</span>' : '';
            $createdAt = date("M d, Y h:i A", strtotime($row['created_at']));

            // Optional: customize payment messages for renters
            if (in_array($row['type'], ['bill_paid', 'cash_payment', 'paypal_payment', 'complaint'])) {
                $icon = '';
            } elseif (strpos($row['type'], 'agreement') !== false) {
                $icon = '';
            } elseif (strpos($row['type'], 'monthly_due') !== false) {
                $icon = '';
            } elseif (strpos($row['type'], 'unit_added') !== false) {
                $icon = '';
            } else {
                $icon = '';
            }

            $html .= '
                <li>
                    <a class="dropdown-item d-block ' . $readClass . '" 
                       href="mark_read.php?id=' . $row['id'] . '">
                        ' . $icon . ' ' . htmlspecialchars($row['message']) . '
                        <br><small class="text-muted">' . $createdAt . '</small>
                        ' . $newBadge . '
                    </a>
                </li>';
        }

        $html .= '
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-center text-primary" href="mark_read.php?all=1">Mark all as read</a></li>
            <li><a class="dropdown-item text-center" href="all_notifications.php">View All</a></li>';

        $response['html'] = $html;
    }
}

echo json_encode($response);
?>
