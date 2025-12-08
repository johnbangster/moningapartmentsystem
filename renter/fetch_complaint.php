<?php
session_start();
require ('../admin/config/dbcon.php');

if (!isset($_SESSION['auth_user']['user_id'])) {
    die("Access denied.");
}

$renter_id = $_SESSION['auth_user']['user_id'];
$complaints = mysqli_query($con, "SELECT * FROM complaints WHERE renter_id = $renter_id ORDER BY created_at DESC");

if ($complaints && mysqli_num_rows($complaints) > 0) {
    $i = 1;
    while ($row = mysqli_fetch_assoc($complaints)) {
        echo "<tr>";
        echo "<td>" . $i++ . "</td>";
        echo "<td>" . ucfirst($row['complaint_type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['remarks']) . "</td>";
        echo "<td class='status-" . strtolower($row['status']) . "'>" . ucfirst($row['status']) . "</td>";
        echo "<td>" . date("F j, Y g:i A", strtotime($row['created_at'])) . "</td>";

        // Replies
        $cid = $row['id'];
        $replies = mysqli_query($con, "SELECT * FROM complaint_replies WHERE complaint_id = $cid ORDER BY created_at ASC");

        echo "<td>";
        if ($replies && mysqli_num_rows($replies) > 0) {
            echo "<div style='padding:10px; border:1px solid #ccc; background:#f9f9f9; max-height:150px; overflow:auto;'>";
            while ($rep = mysqli_fetch_assoc($replies)) {
                echo "<p>
                        <strong>" . htmlspecialchars($rep['admin_name']) . ":</strong><br>" .
                        htmlspecialchars($rep['reply']) . "<br>
                        <small><em>" . date("F j, Y g:i A", strtotime($rep['created_at'])) . "</em></small>
                      </p><hr>";
            }
            echo "</div>";
        } else {
            echo "<em>No replies yet</em>";
        }
        echo "</td>";

        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No complaints submitted yet.</td></tr>";
}
