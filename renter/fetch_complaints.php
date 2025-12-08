<?php
session_start();
require('../admin/config/dbcon.php');

// $renter_id = $_SESSION['renter_id'];
$renter_id = $_SESSION['auth_user']['user_id'];


$complaints = mysqli_query($con, "SELECT * FROM complaints WHERE renter_id = $renter_id ORDER BY created_at DESC");

if ($complaints && mysqli_num_rows($complaints) > 0):
    $i = 1;
    while ($row = mysqli_fetch_assoc($complaints)):
?>
<tr>
    <td><?= $i++ ?></td>
    <td><?= ucfirst($row['complaint_type']) ?></td>
    <td><?= htmlspecialchars($row['remarks']) ?></td>
    <td class="status-<?= strtolower($row['status']) ?>"><?= ucfirst($row['status']) ?></td>
    <td><?= date("F j, Y g:i A", strtotime($row['created_at'])) ?></td>
    <td>
        <?php
        $cid = $row['id'];
        $replies = mysqli_query($con, "SELECT * FROM complaint_replies WHERE complaint_id = $cid ORDER BY created_at ASC");
        if ($replies && mysqli_num_rows($replies) > 0): ?>
            <div style="padding:10px; border:1px solid #ccc; background:#f9f9f9; max-height:150px; overflow:auto;">
                <?php while ($rep = mysqli_fetch_assoc($replies)): ?>
                    <p>
                        <strong><?= htmlspecialchars($rep['admin_name']) ?>:</strong><br>
                        <?= htmlspecialchars($rep['reply']) ?><br>
                        <small><em><?= date("F j, Y g:i A", strtotime($rep['created_at'])) ?></em></small>
                    </p>
                    <hr>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <em>No replies yet</em>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="6">No complaints submitted yet.</td></tr>
<?php endif; ?>
