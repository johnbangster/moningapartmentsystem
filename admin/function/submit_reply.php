<?php
session_start();
require '../config/dbcon.php'; // Adjust path if necessary

// Check admin access
if (!isset($_SESSION['auth_role']) || !in_array($_SESSION['auth_role'], ['admin', 'employee'])) {
    http_response_code(403);
    exit("Unauthorized access");
}

// Validate input
$cid    = isset($_POST['complaint_id']) ? intval($_POST['complaint_id']) : 0;
$reply  = isset($_POST['reply']) ? trim($_POST['reply']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : null; // optional

$admin_name = $_SESSION['auth_role'] ?? $_SESSION['admin_name'];
$replied_by = $_SESSION['auth_role'];

$allowed_statuses = ['open', 'ongoing', 'resolved'];

if ($cid > 0 && $reply !== '') {

    // Insert the reply
    $stmt = mysqli_prepare($con, "INSERT INTO complaint_replies (complaint_id, reply, replied_by, admin_name) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isss", $cid, $reply, $replied_by, $admin_name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Optional: update status if provided
    if ($status && in_array($status, $allowed_statuses)) {
        $stmt2 = mysqli_prepare($con, "UPDATE complaints SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt2, "si", $status, $cid);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
    }

    // Return reply block as HTML
    echo '
    <div class="reply-entry mb-2">
        <strong>' . htmlspecialchars($admin_name) . ':</strong><br>
        ' . nl2br(htmlspecialchars($reply)) . '<br>
        <small class="text-muted"><em>' . date("F j, Y") . '</em></small>
    </div>';

} else {
    http_response_code(400);
    echo "Missing required data.";
}
