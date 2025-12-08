 <?php
include('authentication.php');
include('includes/header.php');
include('config/code.php');

// Get totals for current year (grouped by month)
$currentYear = date("Y");

$sql = "
    SELECT MONTH(payment_date) AS month_num,
           SUM(amount) AS total
    FROM payments
    WHERE YEAR(payment_date) = '$currentYear'
    GROUP BY YEAR(payment_date), MONTH(payment_date)
    ORDER BY YEAR(payment_date), MONTH(payment_date)
";
$result = mysqli_query($con, $sql);

// Prepare monthly data
$months = [];
$totals = [];

// Fill all 12 months (default 0)
for ($i = 1; $i <= 12; $i++) {
    $months[$i] = date("F", mktime(0, 0, 0, $i, 1));
    $totals[$i] = 0;
}

// Replace with actual totals from DB
while ($row = mysqli_fetch_assoc($result)) {
    $monthNum = (int)$row['month_num'];
    $totals[$monthNum] = (float)$row['total'];
}

// Grand total
$grandTotal = array_sum($totals);
$grandTotalFormatted = '₱' . number_format($grandTotal, 2);

// Add "Grand Total" as a 13th bar
$months[] = "Grand Total";
$totals[] = $grandTotal;

// Encode for JS
$months_json = json_encode(array_values($months));
$totals_json = json_encode(array_values($totals));
?>

<div class="container-fluid px-4">
    <div class="row mt-3">
        <div class="col-xl-12">
            <div class="alert alert-info fw-bold">
                Total Paid Amount Recieved (<?= $currentYear ?>): <?= $grandTotalFormatted ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12 col-md-8">
            <canvas id="bar-chart" height="100"></canvas>
        </div>
    </div>
</div>

<script>
const months = <?= $months_json ?>;
const totals = <?= $totals_json ?>;

new Chart(document.getElementById("bar-chart"), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: "Total Paid (₱)",
            backgroundColor: months.map(m => m === "Grand Total" ? "#c45850" : "#3e95cd"),
            data: totals
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            title: {
                display: true,
                text: 'Monthly Paid Transactions + Grand Total (<?= $currentYear ?>)'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let value = context.raw || 0;
                        return '₱' + Number(value).toLocaleString(undefined, { minimumFractionDigits: 2 });
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₱' + Number(value).toLocaleString(undefined, { minimumFractionDigits: 2 });
                    }
                }
            }
        }
    }
});
</script>


<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
