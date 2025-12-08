<?php
session_start();
require 'config/dbcon.php';

if (!isset($_SESSION['auth_user'])) {
    header("Location: login.php");
    exit;
}

$user_id   = intval($_SESSION['auth_user']['user_id']);
$auth_role = $_SESSION['auth_role'] ?? '';


 //FETCH NOTIFICATIONS BASED ON USER ROLE (updated with renter PayPal & cash notifications)

switch ($auth_role) {
    case 'admin':
        $sql = "
            SELECT * FROM notifications 
            WHERE type IN (
                'bill_paid', 'cash_payment', 'paypal_payment', 'complaint', 
                'agreement_admin', 'unit_added_admin', 'monthly_due_admin'
            )
            ORDER BY created_at DESC
        ";
        break;

    case 'employee':
        $sql = "
            SELECT * FROM notifications 
            WHERE type IN (
                'bill_paid', 'cash_payment', 'paypal_payment',
                'agreement_employee', 'unit_added_employee', 'monthly_due_employee'
            )
            ORDER BY created_at DESC
        ";
        break;

    case 'renter':
        $sql = "
            SELECT * FROM notifications 
            WHERE user_id = $user_id 
            AND type IN (
                'bill_created', 'agreement_renter', 'monthly_due_renter',
                'cash_payment', 'paypal_payment'
            )
            ORDER BY created_at DESC
        ";
        break;

    default:
        header("Location: login.php");
        exit;
}

$result = mysqli_query($con, $sql);

//MARK ALL AS READ
if (isset($_POST['mark_read'])) {
    switch ($auth_role) {
        case 'admin':
            $update_sql = "
                UPDATE notifications 
                SET is_read = 1 
                WHERE type IN (
                    'bill_paid', 'cash_payment', 'paypal_payment', 'complaint', 
                    'agreement_admin', 'unit_added_admin', 'monthly_due_admin'
                )
            ";
            break;

        case 'employee':
            $update_sql = "
                UPDATE notifications 
                SET is_read = 1 
                WHERE type IN (
                    'bill_paid', 'cash_payment', 'paypal_payment',
                    'agreement_employee', 'unit_added_employee', 'monthly_due_employee'
                )
            ";
            break;

        case 'renter':
            $update_sql = "
                UPDATE notifications 
                SET is_read = 1 
                WHERE user_id = $user_id 
                AND type IN (
                    'bill_created', 'agreement_renter', 'monthly_due_renter',
                    'cash_payment', 'paypal_payment'
                )
            ";
            break;
    }

    mysqli_query($con, $update_sql);
    header("Location: all_notifications.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <?= ucfirst($auth_role) ?> Notifications
                </h5>
                <form method="post" class="m-0">
                    <button type="submit" name="mark_read" class="btn btn-success btn-sm">
                        Mark All as Read
                    </button>
                </form>
            </div>

            <div class="card-body">
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <ul class="list-group">
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php
                                $isUnread = $row['is_read'] == 0;
                                $badgeClass = $isUnread ? 'bg-danger' : 'bg-secondary';
                                $badgeText  = $isUnread ? 'New' : 'Read';
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="<?= $isUnread ? 'fw-bold text-dark' : 'text-muted' ?>">
                                        <?= htmlspecialchars($row['message']) ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= date("M d, Y h:i A", strtotime($row['created_at'])) ?>
                                    </small>
                                </div>
                                <a href="mark_read.php?id=<?= $row['id'] ?>" 
                                   class="badge <?= $badgeClass ?> text-decoration-none"><?= $badgeText ?></a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No notifications found.</p>
                <?php endif; ?>
            </div>

            <div class="card-footer text-end">
                <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
