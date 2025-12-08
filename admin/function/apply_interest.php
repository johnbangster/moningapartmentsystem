<?php
require('../config/dbcon.php');

    $con->query("UPDATE billings SET interest=interest+(total_amount*0.01), status='overdue'
    WHERE status='unpaid' AND due_date < CURDATE()");

?>
