<?php
include('authentication.php');
include('includes/header.php');
include('config/code.php');
require '../function_booking.php';
require('send_sms.php');

?>

<div class="container-fluid px-4">
    <div class="col-md">
        <h3 class="mt-4">Monings Rental System</h3>
        <?php include('message.php'); ?>
    </div>
</div>


<div class="container-fluid px-4">
    <!-- <h4 class="mt-4"></h4> -->
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard Analytics</li>
    </ol>
    <div class="row">
        <div class="col-xl-3 col-md-6 ">
            <div class="card border-secondary mb-3  mb-4">
                <div class="text-secondary card-body text-secondary">Total Renter
                    <h5 class=" text-secondary fw-bold mb-0 ">
                    <?= getCount('renters'); ?>
                </h5>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-secondary stretched-link" href="renter.php">View All Renters</a>
                    <div class="small text-secondary"><i class="fas fa-angle-right"></i></div>
                
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-secondary  text-secondary mb-4">
                <div class="card-body ">Total Units
                    <h5 class="fw-bold mb-0">
                    <?= getCount('units'); ?>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-secondary stretched-link" href="units.php">View All Units</a>
                    <div class="small text-secondary"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-secondary text-secondary mb-4">
                <div class="card-body">Total Inquiries
                    <h5 class="fw-bold mb-0">
                    <?= getCount('user_query'); ?>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-secondary stretched-link" href="inquiry.php">View  All Inquiries</a>
                    <div class="small text-secondary"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-secondary  text-secondary mb-4">
                <div class="card-body ">Total Bookings
                    <h5 class="fw-bold mb-0">
                    <?= getCount('reservations'); ?>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-secondary stretched-link" href="reservation.php">View All Bookings</a>
                    <div class="small text-secondary"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

    </div>
    
</div>


<div class="container-fluid px-4">
    <!-- <h1 class="mt-4">Transaction Today</h1> -->
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Today's Transactions</li>
    </ol>
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card border-secondary  text-secondary mb-4">
                <div class="card-body">Payment Online(Paypal)
                    <h5 class="fw-bold mb-0">
                    <?php
                            global $con;
                            $todayDate = date('Y-m-d');
                            $paypalQuery = mysqli_query($con, "
                                SELECT SUM(amount) AS total_paypal 
                                FROM payments 
                                WHERE DATE(payment_date) = '$todayDate' 
                                AND payment_type = 'paypal'
                            ");

                            if ($paypalQuery) {
                                $paypal = mysqli_fetch_assoc($paypalQuery);
                                $totalPaypal = $paypal['total_paypal'] ?? 0;
                                echo '₱' . number_format($totalPaypal, 2);
                            } else {
                                echo "Error!";
                            }
                        ?>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="medium text-secondary stretched-link" href=""></a>
                    <div class="medium text-secondary"><i class="fab fa-cc-paypal fa-lg" style="color: #929292ff;"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-secondary text-secondary mb-4">
                <div class="card-body">Cash Payments
                    <h5  class="fw-bold mb-0">
                        <?php
                            $cashQuery = mysqli_query($con, "
                                SELECT SUM(amount) AS total_cash 
                                FROM payments 
                                WHERE DATE(payment_date) = '$todayDate' 
                                AND payment_type = 'cash'
                            ");

                            if ($cashQuery) {
                                $cash = mysqli_fetch_assoc($cashQuery);
                                $totalCash = $cash['total_cash'] ?? 0;
                                echo '₱' . number_format($totalCash, 2);
                            } else {
                                echo "Error!";
                            }
                        ?>
                    </h5>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="medium text-secondary stretched-link" href=""></a>
                    <div class="medium text-secondary"><i class="fas fa-money-bill-wave fa-lg" style="color: #929292ff;"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-secondary  text-secondary mb-4">
                <div class="card-body"> Total Amount Recieved
                    <h5  class="fw-bold mb-0">
                        <?php
                            $todayDate = date('Y-m-d');
                            $query = mysqli_query($con, "
                                SELECT SUM(amount) AS total_received 
                                FROM payments 
                                WHERE DATE(payment_date) = '$todayDate'
                            ");

                            if ($query) {
                                $row = mysqli_fetch_assoc($query);
                                $total = $row['total_received'] ?? 0;
                                echo '₱' . number_format($total, 2);
                            } else {
                                echo "Error fetching data!";
                            }
                        ?>
                    </h5>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="medium text-secondary stretched-link" href="transaction.php">View  All Transactions</a>
                    <div class="medium text-secondary"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
       
    </div>

    <!-- <div class="col-xl-12 col-md-12">
        <figure class="highcharts-figure" id="front" style="width: 50%;">
            <div class="row">
                <div id="container"></div>
            </div>
            <p class="highcharts-description">
            </p>
        </figure>
    </div> -->

    <div class="container-fluid px-4">
    <div class="row">
        <!-- Highcharts Column -->
        <div class="col-xl-8 col-md-12">
            <figure class="highcharts-figure" id="front">
                <div id="container"></div>
            </figure>
        </div>

        <!-- Due Date Table -->
        <div class="col-xl-4 col-md-12">
    <div class="card border-secondary mb-4">
        <div class="card-header bg-secondary text-white">
            Due Today / Past Due
        </div>
        <div class="card-body">
            <?php
            $today = date('Y-m-d');
            $threeDays = date('Y-m-d', strtotime('+3 days'));

            // Fetch all open bills
            $smsQuery = mysqli_query($con, "
                SELECT b.id, b.due_date, b.total_amount, b.sms_sent, b.penalty_weeks,
                    r.contacts, r.first_name, r.last_name,
                    u.name AS unit_name
                FROM bills b
                JOIN renters r ON r.id = b.renter_id
                JOIN units u ON u.id = b.unit_id
                WHERE b.status = 'open'
            ");

            while ($row = mysqli_fetch_assoc($smsQuery)) {

                $due_date = $row['due_date'];
                $contact = preg_replace('/\D/', '', $row['contacts']);
                if (substr($contact, 0, 1) == "0") {
                    $contact = "63" . substr($contact, 1);
                }

                $bill_id = $row['id'];
                $total_amount = $row['total_amount'];
                $unit = $row['unit_name'];
                $name = $row['first_name'] . " " . $row['last_name'];
                $weeks_penalized = intval($row['penalty_weeks']);
                $sms_sent = $row['sms_sent'];

                $daysLate = (strtotime($today) - strtotime($due_date)) / 86400;

                //PENALTY NOTICE — SEND ONCE PER NEW PENALTY WEEK
               
                if ($daysLate > 7) {

                    $weeksLate = floor($daysLate / 7);

                    if ($weeksLate > $weeks_penalized) {

                        $new_penalties = ($weeksLate - $weeks_penalized) * 100;
                        $updated_amount = $total_amount + $new_penalties;

                        mysqli_query($con, "
                            UPDATE bills 
                            SET total_amount = '$updated_amount',
                                penalty_weeks = '$weeksLate'
                            WHERE id = '$bill_id'
                        ");

                        $message =
                            "NOTICE: Your rent for unit $unit is overdue by $weeksLate week(s). " .
                            "Added penalty: ₱$new_penalties. " .
                            "New balance: ₱" . number_format($updated_amount, 2);

                        $response = sendSms($contact, $message);
                        error_log("Penalty SMS response: " . json_encode($response));

                        //NOTHING to update in sms_sent (penalty SMS repeats each new week) 
                    }
                }

                //PRE-DUE REMINDER — SEND ONLY ONCE Condition: due in 3 days + sms_sent = 'none'
               
                if ($due_date == $threeDays && $sms_sent == 'none') {

                    $message =
                        "Reminder: Your rent for unit $unit is due in 3 days (" .
                        date('M d, Y', strtotime($due_date)) . "). Please prepare payment.";

                    $response = sendSms($contact, $message);
                    error_log("Reminder SMS response: " . json_encode($response));

                    mysqli_query($con, "UPDATE bills SET sms_sent = 'reminder' WHERE id = $bill_id");
                }

                //DUE TODAY NOTICE — SEND ONLY ONCE. Condition: due today + sms_sent is 'none' OR 'reminder'
                if ($due_date == $today && in_array($sms_sent, ['none', 'reminder'])) {

                    $message =
                        "NOTICE: Your rent for unit $unit is DUE TODAY (" .
                        date('M d, Y', strtotime($due_date)) .
                        "). Kindly settle as soon as possible.";

                    $response = sendSms($contact, $message);
                    error_log("Due Today SMS response: " . json_encode($response));

                    mysqli_query($con, "UPDATE bills SET sms_sent = 'due' WHERE id = $bill_id");
                }
            }



          
            // DISPLAY LIST OF DUE BILLS
          
            $dueQuery = mysqli_query($con, "
                SELECT b.id, r.first_name, r.last_name, u.name AS unit_name, b.due_date
                FROM bills b
                JOIN renters r ON r.id = b.renter_id
                JOIN units u ON u.id = b.unit_id
                WHERE b.status = 'open' AND b.due_date <= '$today'
                ORDER BY b.due_date ASC
            ");

            if (!$dueQuery) {
                echo '<p class="text-danger">Query Error: ' . mysqli_error($con) . '</p>';
            } else {
                if (mysqli_num_rows($dueQuery) > 0) {
                    echo '<ul class="list-group">';
                    while ($row = mysqli_fetch_assoc($dueQuery)) {

                        $dueClass = ($row['due_date'] < $today) ? 'text-danger' : 'text-warning';

                        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                        echo '<div>';
                        echo '<strong class="' . $dueClass . '">' .
                                htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) .
                             '</strong><br>';
                        echo '<small>' . htmlspecialchars($row['unit_name']) . '</small>';
                        echo '</div>';
                        echo '<span class="' . $dueClass . '">' .
                                date('M d, Y', strtotime($row['due_date'])) .
                             '</span>';
                        echo '</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p class="text-success">No due bills today!</p>';
                }
            }
            ?>
        </div>
    </div>
</div>

    
</div>

<?php


$year = date('Y'); 

// Monthly PayPal
$paypalData = array_fill(1, 12, 0);
$paypalQuery = mysqli_query($con, "
    SELECT MONTH(payment_date) AS month, SUM(amount) AS total_paypal
    FROM payments
    WHERE YEAR(payment_date) = '$year' AND payment_type = 'paypal'
    GROUP BY MONTH(payment_date)
");
while ($row = mysqli_fetch_assoc($paypalQuery)) {
    $paypalData[(int)$row['month']] = (float)$row['total_paypal'];
}

// Monthly Cash
$cashData = array_fill(1, 12, 0);
$cashQuery = mysqli_query($con, "
    SELECT MONTH(payment_date) AS month, SUM(amount) AS total_cash
    FROM payments
    WHERE YEAR(payment_date) = '$year' AND payment_type = 'cash'
    GROUP BY MONTH(payment_date)
");
while ($row = mysqli_fetch_assoc($cashQuery)) {
    $cashData[(int)$row['month']] = (float)$row['total_cash'];
}

// Monthly Total (All Payments)
$totalData = array_fill(1, 12, 0);
$totalQuery = mysqli_query($con, "
    SELECT MONTH(payment_date) AS month, SUM(amount) AS total
    FROM payments
    WHERE YEAR(payment_date) = '$year'
    GROUP BY MONTH(payment_date)
");
while ($row = mysqli_fetch_assoc($totalQuery)) {
    $totalData[(int)$row['month']] = (float)$row['total'];
}

// Month Labels
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul','Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
?>



<script>    

//  Highcharts.chart('container', {
//   chart: {
//     type: 'column',
//   },
//   title: {
//     text: 'Estimated Paid and Un-paid Bills of 2025'
//   },
//   subtitle: {
//     text:
//       'Source Payment: Cash / Online Payment (Paypal)' +//Source: <a target="_blank" 
//       '' //href="https://www.indexmundi.com/agriculture/?commodity=corn">indexmundi</a>
//   },
//   xAxis: {
//     categories: ['Mabolo', 'Solinea', 'I.T Park', 'Makati', 'City Clou'],
//     crosshair: true,
//     accessibility: {
//       description: 'Branch'
//     }
//   },
//   yAxis: {
//     min: 0,
//     title: {
//       text: ''//sample 1000 metric tons (MT)
//     }
//   },
//   tooltip: {
//     valueSuffix: ' (₱)'
//   },
//   plotOptions: {
//     column: {
//       pointPadding: 0.2,
//       borderWidth: 0
//     }
//   },
//   series: [
//     {
//       name: 'Paid',
//       data: [387749, 280000, 129000, 64300, 54000]
//     },
//     {
//       name: 'Un-Paid',
//       data: [45321, 140000, 10000, 140500, 19500]
//     },
//     {
//         name: 'Paypal',
//         data: [12000, 15000, 17000, 19000, 14000]
//     }, 
//     {
//         name: 'Cash',
//         data: [8000, 9000, 10000, 12000, 9500]
//     }
//   ]
// });

Highcharts.chart('container', {
    chart: { type: 'column' },
    title: { text: 'Monthly Payments Overview (<?= $year ?>)' },
    subtitle: { text: 'PayPal, Cash & Total Payments' },
    xAxis: { categories: <?= json_encode($months); ?> },
    yAxis: {
        title: { text: 'Amount (₱)' },
        min: 10000,
        // tickInterval: 10000,
        labels: {
        formatter: function () {
            if (this.value >= 1000000) return '₱' + (this.value / 1000000) + 'M';
            if (this.value >= 1000) return '₱' + (this.value / 1000) + 'k';
            return '₱' + this.value;
        }
    }
    },
    tooltip: { valuePrefix: '₱' },
    series: [
        { name: 'PayPal', data: <?= json_encode(array_values($paypalData)); ?> },
        { name: 'Cash', data: <?= json_encode(array_values($cashData)); ?> },
        { name: 'Total Received', data: <?= json_encode(array_values($totalData)); ?> }
    ]
});

</script>

<?php
    include('includes/footer.php');
    include('includes/scripts.php');
?>

