<?php
$id = $_GET['id'];
echo "<h2>❌ Payment Failed</h2>";
echo "<p>Your reservation ID #$id is still pending.</p>";
echo "<a href='reserve.php'>Try Again</a>";
?>
