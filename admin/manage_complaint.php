<?php
session_start();
require 'config/dbcon.php';

// Restrict access
if (!isset($_SESSION['auth_role']) || !in_array($_SESSION['auth_role'], ['admin', 'employee', 'renter'])) {
    die("Access denied");
}

$success = '';
$error   = '';

$auth_role = $_SESSION['auth_role'];
$auth_user = $_SESSION['auth_user'] ?? [];
$user_id   = intval($auth_user['user_id'] ?? 0);
$first_name = $auth_user['first_name'] ?? '';
$last_name  = $auth_user['last_name'] ?? '';
$username   = $auth_user['username'] ?? '';
$full_name  = trim($first_name . ' ' . $last_name) ?: $username;

//Function: create notification
function createNotification($con, $user_id, $message, $type = 'complaint') {
    $stmt = mysqli_prepare($con, "
        INSERT INTO notifications (user_id, message, type, is_read, created_at)
        VALUES (?, ?, ?, 0, NOW())
    ");
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $message, $type);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}


  //HANDLE REPLY SUBMISSION
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reply'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $reply        = trim($_POST['reply']);

    if (!empty($reply)) {
        $stmt = mysqli_prepare(
            $con,
            "INSERT INTO complaint_replies (complaint_id, reply, replied_by, admin_name, created_at) 
             VALUES (?, ?, ?, ?, NOW())"
        );
        mysqli_stmt_bind_param($stmt, "isss", $complaint_id, $reply, $auth_role, $full_name);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Reply submitted successfully.";

            // Get complaint details for notification
            $res = mysqli_query($con, "SELECT renter_id FROM complaints WHERE id = $complaint_id");
            $complaint = mysqli_fetch_assoc($res);
            $renter_id = intval($complaint['renter_id']);

            //Notify relevant users
            if (in_array($auth_role, ['admin', 'employee'])) {
                // Notify renter that admin/employee replied
                $msg = "$full_name replied to your complaint.";
                createNotification($con, $renter_id, $msg, 'complaint');
            } elseif ($auth_role === 'renter') {
                // Notify admin/employee that renter replied
                $msg = "$full_name (Renter) replied to a complaint.";
                createNotification($con, null, $msg, 'complaint'); // NULL = all admins/employees can see
            }
        } else {
            $error = "Error: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = "Reply cannot be empty.";
    }
}

//HANDLE STATUS UPDATE

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $new_status   = $_POST['status'];

    // Validate status
    if (in_array($new_status, ['ongoing', 'resolved'])) {
        $stmt = mysqli_prepare($con, "UPDATE complaints SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $new_status, $complaint_id);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Complaint status updated to " . ucfirst($new_status) . ".";

            // Fetch the renter who submitted this complaint
            // $res = mysqli_query($con, "SELECT renter_id FROM complaints WHERE id = $complaint_id");
            // if ($res && mysqli_num_rows($res) > 0) {
            //     $complaint = mysqli_fetch_assoc($res);
            //     $renter_id = intval($complaint['renter_id']);

            //     // Notify ONLY the renter
            //     $msg = "Your complaint status has been updated to '" . ucfirst($new_status) . "'.";
            //     createNotification($con, $renter_id, $msg, 'complaint');
            // }
        } else {
            $error = "Error updating status: " . mysqli_error($con);
        }

        mysqli_stmt_close($stmt);
    } else {
        $error = "Invalid status value.";
    }
}


// FETCH COMPLAINTS

$sql = "SELECT c.*, 
               CONCAT(u.first_name, ' ', u.last_name) AS renter_name, 
               u.contact
        FROM complaints c
        JOIN users u ON u.id = c.renter_id
        ORDER BY c.created_at DESC";
$result = mysqli_query($con, $sql);

include('includes/header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Complaints</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Complaints</li>
    </ol>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive" style="max-height: 550px; overflow-y:auto;">
                <table class="table table-hover text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Renter</th>
                            <th>Contact</th>
                            <th>Complaint Type</th>
                            <th>Remarks</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Replies</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['renter_name']) ?></td>
                                <td><?= htmlspecialchars($row['contact']) ?></td>
                                <td><?= ucfirst($row['complaint_type']) ?></td>
                                <td><?= htmlspecialchars($row['remarks']) ?></td>
                                <td>
                                    <?php if ($auth_role !== 'renter'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="complaint_id" value="<?= $row['id'] ?>">
                                            <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                                                <option value="ongoing"  <?= $row['status'] === 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                                <option value="resolved" <?= $row['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    <?php else: ?>
                                        <?= ucfirst($row['status']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= date("F j, Y g:i A", strtotime($row['created_at'])) ?></td>
                                <td>
                                    <?php
                                    $cid = $row['id'];
                                    $replies = mysqli_query(
                                        $con,
                                        "SELECT * FROM complaint_replies 
                                         WHERE complaint_id = $cid 
                                         ORDER BY created_at ASC"
                                    );
                                    if ($replies && mysqli_num_rows($replies) > 0): ?>
                                        <div style="padding:10px; border:1px solid #ccc; background:#f9f9f9; max-height:150px; overflow:auto;">
                                            <?php while ($rep = mysqli_fetch_assoc($replies)): ?>
                                                <p>
                                                    <strong><?= htmlspecialchars($rep['admin_name']) ?> (<?= ucfirst($rep['replied_by']) ?>):</strong><br>
                                                    <?= nl2br(htmlspecialchars($rep['reply'])) ?><br>
                                                    <small><em><?= date("F j, Y g:i A", strtotime($rep['created_at'])) ?></em></small>
                                                </p>
                                                <hr>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php else: ?>
                                        <em>No replies yet</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary reply-btn" 
                                            data-id="<?= $row['id'] ?>" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#replyModal">
                                        Reply
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="9">No complaints found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="replyForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Reply</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="complaint_id" id="complaintId">
                    <textarea name="reply" class="form-control" placeholder="Type your reply..." rows="3" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit_reply" class="btn btn-primary">Send Reply</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.reply-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('complaintId').value = this.dataset.id;
    });
});
</script>

<?php include('includes/footer.php'); ?>
