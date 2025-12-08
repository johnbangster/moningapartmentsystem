<?php
session_start();
include('authentication.php');
require_once('config/code.php');
require('includes/header.php');
?>

<div class="container-fluid px-4">
    <div class="col-md">
        <h1 class="mt-4">BILL</h1>
    </div>

    <div class="row">
        <div class="card border-0 shadow mb-4">
            <div class="card-body">

                <!-- Generate Bill Section -->
                <div class="d-flex justify-content-end mb-3 flex-wrap">
                    <?php
                    $occupied_units = mysqli_query($con, "
                        SELECT u.id, u.name, CONCAT(r.first_name, ' ', r.last_name) AS renter_name
                        FROM units u
                        INNER JOIN renters r ON r.unit_id = u.id
                        WHERE LOWER(r.status) IN ('occupied','active')
                        ORDER BY r.last_name ASC, r.first_name ASC
                    ");

                    ?>
                    <form action="automated_bill.php" method="POST" class="d-flex flex-wrap gap-2 align-items-stretch">
                        <label class="mb-0 d-flex align-items-center me-1">Select Renter:</label>
                        <select name="unit_id" class="form-select form-select-sm" required>
                            <option value="">--Select Renter--</option>
                            <?php
                            if (mysqli_num_rows($occupied_units) > 0) {
                                while ($u = mysqli_fetch_assoc($occupied_units)) {
                                    echo '<option value="' . $u['id'] . '">' .
                                        htmlspecialchars($u['name']) . ' — ' .
                                        htmlspecialchars($u['renter_name']) .
                                        '</option>';
                                }
                            } else {
                                echo '<option disabled>No occupied units found</option>';
                            }
                            ?>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center">
                            <i class="fa-solid fa-plus me-1"></i> Generate Bill
                        </button>
                    </form>
                </div>

                <!-- Filters -->
                <?php
                $monthFilter = isset($_GET['month']) ? $_GET['month'] : '';
                $renterFilter = isset($_GET['renter']) ? $_GET['renter'] : '';
                $limit = 5;
                $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
                if ($page < 1) $page = 1;
                $offset = ($page - 1) * $limit;

                $month_query = mysqli_query($con, "
                    SELECT DISTINCT DATE_FORMAT(due_date, '%M %Y') AS month 
                    FROM bills ORDER BY due_date DESC
                ");

                $renter_query = mysqli_query($con, "
                    SELECT id, CONCAT(first_name, ' ', last_name) AS full_name
                    FROM renters
                    ORDER BY last_name ASC, first_name ASC
                ");

                $whereClause = "WHERE b.status IN ('open', 'awaiting_confirmation')";
                if (!empty($monthFilter)) {
                    $whereClause .= " AND DATE_FORMAT(b.due_date, '%M %Y') = '" . mysqli_real_escape_string($con, $monthFilter) . "'";
                }
                if (!empty($renterFilter)) {
                    $whereClause .= " AND b.renter_id = '" . intval($renterFilter) . "'";
                }

                $query = mysqli_query($con, "
                    SELECT b.*,  b.carry_balance, r.first_name, r.last_name, u.name AS unit_name
                    FROM bills b
                    INNER JOIN renters r ON b.renter_id = r.id
                    INNER JOIN units u ON b.unit_id = u.id
                    $whereClause
                    ORDER BY b.id DESC
                    LIMIT $limit OFFSET $offset
                ");
                ?>

                <div class="d-flex justify-content-between flex-wrap mb-3 ">
                    <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
                        <div class="d-flex align-items-center ">
                            <label class="me-2 mb-0">Filter by Month:</label>
                            <select name="month" class="form-select w-auto me-2" onchange="this.form.submit()">
                                <option value="">All</option>
                                <?php while ($m = mysqli_fetch_assoc($month_query)) {
                                    $val = $m['month'];
                                    $selected = ($monthFilter == $val) ? 'selected' : '';
                                    echo "<option value='$val' $selected>$val</option>";
                                } ?>
                            </select>
                        </div>

                        <div class="d-flex align-items-center ">
                            <label class="me-2 mb-0 ms-2">Filter by Renter:</label>
                            <select name="renter" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All</option>
                                <?php while ($r = mysqli_fetch_assoc($renter_query)) {
                                    $selected = ($renterFilter == $r['id']) ? 'selected' : '';
                                    echo "<option value='{$r['id']}' $selected>{$r['full_name']}</option>";
                                } ?>
                            </select>
                            <!-- <a href="billing.php" class="btn btn-secondary shadow-none ms-2"> -->
                        </div>
                          <button class="btn btn-secondary">
                               </i>Reset 
                            </button>
                    </form>
                </div>

                <!-- Bills Table -->
                <div class="table-responsive-md" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>Reference ID</th>
                                <th>Unit</th>
                                <th>Renter</th>
                                <th>Month</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Note</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_assoc($query)) {
                                    $noteColor = '#555';
                                    if (strpos($row['remarks'], 'Overpayment') !== false) $noteColor = 'green';
                                    elseif (strpos($row['remarks'], 'Unpaid') !== false) $noteColor = 'red';

                                    $badgeClass = 'bg-danger';
                                    if ($row['status'] == 'paid') $badgeClass = 'bg-success';
                                    elseif ($row['status'] == 'partial') $badgeClass = 'bg-warning text-dark';
                                    elseif ($row['status'] == 'overpaid') $badgeClass = 'bg-info text-dark';
                                    elseif ($row['status'] == 'awaiting_confirmation') $badgeClass = 'bg-warning text-dark'; // yellow for awaiting cash


                                    echo "<tr>
                                        <td>{$row['reference_id']}</td>
                                        <td>{$row['unit_name']}</td>
                                        <td>{$row['first_name']} {$row['last_name']}</td>
                                        <td>" . date('F', strtotime(datetime: $row['due_date'])) . "</td>
                                        <td>" . date('M d, Y', strtotime($row['due_date'])) . "</td>
                                        <td>₱" . number_format($row['total_amount'], 2) . "</td>
                                        <td><span class='badge $badgeClass'>" . htmlspecialchars($row['status']) . "</span></td>
                                        <td style='font-size:0.9em; color:$noteColor'>{$row['remarks']}</td>

                                        <td>";

                                    // Only show edit and pay buttons for open bills // 
                                    if (in_array($row['status'], ['open', 'awaiting_confirmation'])) {
                                        if ($_SESSION['auth_role'] == 'admin') {
                                            echo "<button class='btn btn-sm btn-primary editBtn' 
                                                data-id='{$row['id']}'
                                                data-total='{$row['total_amount']}'
                                                data-status='{$row['status']}'
                                                data-reference='{$row['reference_id']}'
                                                data-carry_balance='{$row['carry_balance']}'
                                                data-remarks='" . htmlspecialchars($row['remarks'], ENT_QUOTES) . "'
                                                data-bs-toggle='modal' data-bs-target='#editModal'>
                                                <i class='fa-solid fa-pen-to-square'></i>
                                            </button> ";
                                        }

                                        echo "<button class='btn btn-sm btn-success payBtn' 
                                                data-id='{$row['id']}'
                                                data-renter='{$row['renter_id']}'
                                                data-total='{$row['total_amount']}'
                                                data-reference='{$row['reference_id']}'
                                                data-bs-toggle='modal' data-bs-target='#payModal'>
                                                <i class='fas fa-money-bill-wave'></i>
                                                
                                            </button>";
                                    } else {
                                        echo "<button class='btn btn-sm btn-secondary' disabled><i class='fas fa-money-bill-wave'></i></button>";
                                    }

                                    echo "</td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center'>No open bills found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php
                $count_result = mysqli_query($con, "
                    SELECT COUNT(*) AS total 
                    FROM bills b 
                    JOIN renters r ON b.renter_id = r.id
                    $whereClause
                ");
                $total_rows = mysqli_fetch_assoc($count_result)['total'];
                $total_pages = ceil($total_rows / $limit);

                $prev_page = $page - 1;
                $next_page = $page + 1;
                ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-end">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $prev_page . ($monthFilter ? "&month=$monthFilter" : '') . ($renterFilter ? "&renter=$renterFilter" : '') ?>">Prev</a>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">Page <?= $page ?> of <?= $total_pages ?></span>
                        </li>
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $next_page . ($monthFilter ? "&month=$monthFilter" : '') . ($renterFilter ? "&renter=$renterFilter" : '') ?>">Next</a>
                        </li>
                    </ul>
                </nav>

            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
            <form method="POST" action="edit_bill.php">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Bill</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="bill_id" id="bill_id">
                    <div class="mb-3">
                        <label>Reference Number</label>
                        <input type="text" name="reference_number" id="reference_number" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Total Amount (₱)</label>
                        <input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" id="status" class="form-control">
                            <?php
                            $statuses = [
                                'open' => 'Open',
                                'paid' => 'Paid',
                                'overpaid' => 'Overpaid',
                                'partial' => 'Partial',
                                'awaiting_confirmation' => 'Awaiting confirmation'
                            ];
                            foreach ($statuses as $key => $label) {
                                // Leave selected blank here; we'll set it via JS with trim()
                                echo "<option value='$key'>$label</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Remarks:</label>
                        <textarea name="remarks" id="remarks" class="form-control" placeholder=""></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="update_bill" class="btn btn-success">Update Bill</button>
                </div>
            </div>
        </form>
    </div>
</div>



<!-- Cash Payment Modal -->
<div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="cash_topay.php" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cash Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <script>
                        document.getElementById('payModal').addEventListener('shown.bs.modal', function () {
                            console.log('Bill ID Sent:', document.getElementById('pay_bill_id').value);
                        });
                    </script>

                    <input type="hidden" name="bill_id" id="pay_bill_id">
                    <input type="hidden" name="renter_id" id="pay_renter_id">

                    <div class="mb-3">
                        <label>Reference Number</label>
                        <input type="text" name="reference_number" id="pay_reference" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Total Bill (₱)</label>
                        <input type="text" id="pay_total_display" class="form-control" readonly>
                        <input type="hidden" name="total_amount" id="pay_total_amount">
                    </div>

                    <div class="mb-3">
                        <label>Amount to Pay (Cash):</label>
                        <input type="number" name="amount_paid" class="form-control" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label>Remarks:</label>
                        <textarea name="remarks" class="form-control" placeholder="Optional notes (e.g., partial for October)"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" name="cash_pay" class="btn btn-success">Confirm Payment</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php 
require('includes/footer.php'); 
include('includes/scripts.php');
?>


<script>
// Use JS to populate modal fields reliably
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        const billId = btn.dataset.id;
        const total = btn.dataset.total;
        const status = btn.dataset.status.trim(); // Trim whitespace
        const reference = btn.dataset.reference;
        const remarks = btn.dataset.remarks || ''; // optional

        document.getElementById('bill_id').value = billId;
        document.getElementById('total_amount').value = total;
        document.getElementById('reference_number').value = reference;
        document.getElementById('remarks').value = remarks;

        // Set status select reliably
        const statusSelect = document.getElementById('status');
        for (let i = 0; i < statusSelect.options.length; i++) {
            if (statusSelect.options[i].value === status) {
                statusSelect.selectedIndex = i;
                break;
            }
        }
    });
});
// document.querySelectorAll('.editBtn').forEach(btn => {
//     btn.addEventListener('click', () => {
//         document.getElementById('bill_id').value = btn.dataset.id;
//         document.getElementById('total_amount').value = btn.dataset.total;
//         document.getElementById('status').value = btn.dataset.status;
//         document.getElementById('reference_number').value = btn.dataset.reference; // Set reference number
        
//     });
// });



document.querySelectorAll('.payBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        const billId = btn.dataset.id;
        const reference = btn.dataset.reference;
        const totalAmount = parseFloat(btn.dataset.total);
        const renterId = btn.dataset.renter;

        //Fill hidden modal inputs properly
        document.getElementById('pay_bill_id').value = billId;
        document.getElementById('pay_reference').value = reference;
        document.getElementById('pay_total_amount').value = totalAmount;
        document.getElementById('pay_total_display').value = '₱' + totalAmount.toLocaleString();
        document.getElementById('pay_renter_id').value = renterId;

        // Fix: Now reference input field exists
        const amountInput = document.querySelector('#payModal input[name="amount_paid"]');

        if (amountInput) {
            amountInput.addEventListener('input', function() {
                if (parseFloat(this.value) < 0) this.value = 0;
            });
        }
    });
});
</script>


<!-- // document.querySelectorAll('.payBtn').forEach(btn => {
//     btn.addEventListener('click', () => {
//         const billId = btn.dataset.id;
//         const reference = btn.dataset.reference;
//         const totalAmount = parseFloat(btn.dataset.total);
//         const renterId = btn.dataset.renter;

//         // Fill modal fields
//         document.getElementById('pay_bill_id').value = billId;
//         document.getElementById('pay_reference').value = reference;
//         document.getElementById('pay_total_amount').value = totalAmount;
//         document.getElementById('pay_total_display').value = '₱' + totalAmount.toLocaleString();
//         document.getElementById('pay_renter_id').value = renterId;

//         // Fetch current balance via AJAX (optional, if balance is stored separately)
//         // For simplicity, we'll calculate from modal total and existing amount_paid
//         const row = btn.closest('tr');
//         let currentPaid = parseFloat(row.querySelector('td:nth-child(6)').dataset.paid || 0);
//         let currentBalance = totalAmount - currentPaid;

//         // Set max value in input
//         // const amountInput = document.querySelector('#payModal input[name="amount_paid"]');
//         // amountInput.value = currentBalance.toFixed(2);
//         // amountInput.max = currentBalance.toFixed(2);

//         // Optional: show remaining balance dynamically
//         amountInput.addEventListener('input', function() {
//             let entered = parseFloat(this.value) || 0;
//             if (entered > currentBalance) this.value = currentBalance.toFixed(2);
//         });
//     });
// }); -->


