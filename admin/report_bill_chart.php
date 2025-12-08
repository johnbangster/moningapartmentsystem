<?php
require('config/dbcon.php');   // adjust path as needed
include('authentication.php');
include('includes/header.php');
?>

<!-- Highcharts Column -->
 <div class="container-fluid px-4 mt-5">
  <div class="row">
          <div class="col-xl-12 col-md-12">
              <figure class="highcharts-figure" id="front">
                  <div id="container"></div>
              </figure>
          </div>
    </div>
 </div>

<?php
$year = date('Y'); //change this to any year like 2024

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
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
?>



<script>    

Highcharts.chart('container', {
    chart: { type: 'column' },
    title: { text: 'Monthly Payments Overview (<?= $year ?>)' },
    subtitle: { text: 'PayPal, Cash & Total Payments' },
    xAxis: { categories: <?= json_encode($months); ?> },
    yAxis: {
        title: { text: 'Amount (₱)' },
        min: 0
    },
    tooltip: { valuePrefix: '₱' },
    series: [
        { name: 'PayPal', data: <?= json_encode(array_values($paypalData)); ?> },
        { name: 'Cash', data: <?= json_encode(array_values($cashData)); ?> },
        { name: 'Total Received', data: <?= json_encode(array_values($totalData)); ?> }
    ]
});

</script>

