<?php
session_start();
require('authentication.php');
require_once('config/code.php');
require('includes/header.php');
?>

<div class="container-fluid px-4">
    <div class="col-md">
        <h1 class="mt-4">All Transactions</h1>
        <?php include('message.php'); ?>
    </div>
<div class="row">
    <div class="card border-0 shadow mb-4">
        <div class="card-body">

            <?php
            // Filters
            $monthFilter  = isset($_GET['month']) ? $_GET['month'] : '';
            $renterFilter = isset($_GET['renter_id']) ? (int)$_GET['renter_id'] : 0;

            $limit  = 10;
            $page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $offset = ($page - 1) * $limit;

            // Month dropdown
            $month_query = mysqli_query($con, "
                SELECT DISTINCT DATE_FORMAT(due_date, '%M %Y') AS month
                FROM bills
                ORDER BY due_date DESC
            ");

            // Renter dropdown
            $renter_query = mysqli_query($con, "
                SELECT id, first_name, last_name 
                FROM renters 
                ORDER BY first_name ASC
            ");
            ?>

            <!-- Filters -->
            <form method="GET" class="mb-3 d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center">
                    <label class="me-2 mb-0">Filter by Month:</label>
                    <select name="month" class="form-select w-auto">
                        <option value="">All</option>
                        <?php while ($m = mysqli_fetch_assoc($month_query)):
                            $val = $m['month'];
                            $selected = ($monthFilter == $val) ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($val) ?>" <?= $selected ?>><?= htmlspecialchars($val) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="d-flex align-items-center">
                    <label class="me-2 mb-0">Filter by Renter:</label>
                    <select name="renter_id" class="form-select w-auto">
                        <option value="">All</option>
                        <?php while ($r = mysqli_fetch_assoc($renter_query)):
                            $rid = $r['id'];
                            $rname = $r['first_name'] . ' ' . $r['last_name'];
                            $selected = ($renterFilter == $rid) ? 'selected' : '';
                        ?>
                            <option value="<?= (int)$rid ?>" <?= $selected ?>><?= htmlspecialchars($rname) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Apply</button>
            </form>

            <?php
            // Build filter
            $filter = "WHERE 1=1";

            if ($monthFilter) {
                $ts = strtotime($monthFilter);
                if ($ts !== false) {
                    $monthNum = date('m', $ts);
                    $yearNum  = date('Y', $ts);
                    $filter  .= " AND DATE_FORMAT(b.due_date, '%m') = '$monthNum' AND DATE_FORMAT(b.due_date, '%Y') = '$yearNum'";
                }
            }

            if ($renterFilter) {
                $filter .= " AND b.renter_id = " . intval($renterFilter);
            }

            // Main query
            $query_sql = "
                SELECT 
                    b.*,
                    r.first_name,
                    r.last_name,
                    COALESCE(MAX(p.remarks), '') AS remarks,
                    COALESCE(SUM(p.amount),0) AS total_paid,
                    MAX(p.payment_date) AS last_payment
                FROM bills b
                INNER JOIN renters r ON b.renter_id = r.id
                LEFT JOIN payments p ON p.bill_id = b.id
                $filter
                GROUP BY b.id
                HAVING 
                    b.status IN ('paid','overpaid')
                    OR (b.status = 'partial' AND (total_paid > 0 OR b.carry_balance <> 0))
                ORDER BY 
                    last_payment DESC,
                    b.id DESC
                LIMIT $limit OFFSET $offset
            ";

            $query = mysqli_query($con, $query_sql);
            if (!$query) {
                die("SQL Error (Main Query): " . mysqli_error($con));
            }

            if (mysqli_num_rows($query) > 0):
            ?>

            <div class="table-responsive-md" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Ref No.</th>
                            <th>Renter Name</th>
                            <th>Balance</th>
                            <th>Over Payment</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($query)):

                            $bill_id     = $row['id'];
                            $fullname    = ucwords($row['first_name'] . ' ' . $row['last_name']);
                            $total_amount= floatval($row['total_amount']);
                            $total_paid  = floatval($row['total_paid']);
                            $carry_balance = floatval($row['carry_balance']);
                            $dbStatus    = strtolower(trim($row['status']));

                            // Calculate actual balance considering carry balance
                            if ($dbStatus === 'paid') {
                                $balance = 0;
                            } elseif ($dbStatus === 'overpaid') {
                                $balance = 0;
                            } else {
                                $balance = $total_amount - $total_paid;
                                if ($carry_balance > 0) {
                                    $balance = max(0, $balance - $carry_balance);
                                }
                            }

                            // Status badges
                            switch ($dbStatus) {
                                case 'paid':
                                    $statusBadge = '<span class="badge bg-primary">Paid</span>';
                                    break;
                                case 'overpaid':
                                    $statusBadge = '<span class="badge bg-success">Overpaid</span>';
                                    break;
                                case 'partial':
                                    $statusBadge = '<span class="badge bg-warning text-dark">Partial</span>';
                                    break;
                                case 'awaiting_confirmation':
                                    $statusBadge = '<span class="badge bg-warning text-dark">Awaiting confirmation</span>';
                                    break;
                                default:
                                    $statusBadge = '<span class="badge bg-secondary">Open</span>';
                                    break;
                            }

                            $due_date = !empty($row['due_date']) ? date('M d, Y', strtotime($row['due_date'])) : '—';

                            // Enable Cash-to-Pay only if there's balance to pay
                            $canPay = ($dbStatus != 'paid' && $dbStatus != 'overpaid' && $balance > 0);

                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['reference_id']); ?></strong></td>
                            <td><?= htmlspecialchars($fullname); ?></td>

                            <!-- BALANCE -->
                            <td class="text-center <?= ($balance > 0) ? 'text-dark' : 'text-primary' ?>">
                                <?= ($balance > 0) ? '₱'.number_format($balance,2) : 'No Balance'; ?>
                            </td>

                            <!-- CARRY BALANCE -->
                            <td class="text-center <?= ($carry_balance > 0) ? 'text-dark fw-bold' : 'text-muted' ?>">
                                <?= ($carry_balance > 0) ? '₱'.number_format($carry_balance,2) : '—'; ?>
                            </td>

                            <td class="text-center"><?= $statusBadge; ?></td>
                            <td><?= $due_date; ?></td>
                            <td><strong><?= htmlspecialchars($row['remarks']); ?></strong></td>
                            <td>
                                <a href="view_bill.php?id=<?= $bill_id; ?>" class="btn btn-sm btn-primary">View</a>
                                <?php if ($canPay): ?>
                                    <a href="cash_topay.php?id=<?= $bill_id; ?>" class="btn btn-sm btn-warning" title="Pay">
                                        <i class="fa-solid fa-money-bill-wave fa-bounce" style="color: #ffffff;"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="generate_receipt.php?id=<?= $bill_id; ?>" target="_blank" class="btn btn-sm btn-success" title="Receipt">
                                        <i class="fa-solid fa-receipt fa-lg"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php else: ?>
                <div class="alert alert-info mt-4">No transactions found.</div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php
            $count_sql = "
                SELECT COUNT(*) AS total FROM (
                    SELECT b.id, COALESCE(SUM(p.amount),0) AS total_paid, b.carry_balance, b.status
                    FROM bills b
                    LEFT JOIN payments p ON p.bill_id = b.id
                    $filter
                    GROUP BY b.id
                    HAVING 
                        b.status IN ('paid','overpaid')
                        OR (b.status = 'partial' AND (total_paid > 0 OR b.carry_balance <> 0))
                ) AS filtered_bills
            ";

            $count_result = mysqli_query($con, $count_sql);
            if (!$count_result) {
                die("SQL Error (Pagination count): " . mysqli_error($con));
            }

            $total_rows = mysqli_fetch_assoc($count_result)['total'] ?? 0;
            $total_pages = ($total_rows > 0) ? ceil($total_rows / $limit) : 1;
            $prev_page = $page - 1;
            $next_page = $page + 1;
            ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-end">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $prev_page . ($monthFilter ? "&month=" . urlencode($monthFilter) : '') . ($renterFilter ? "&renter_id=$renterFilter" : '') ?>">Prev</a>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link">Page <?= $page ?> of <?= $total_pages ?></span>
                    </li>
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $next_page . ($monthFilter ? "&month=" . urlencode($monthFilter) : '') . ($renterFilter ? "&renter_id=$renterFilter" : '') ?>">Next</a>
                    </li>
                </ul>
            </nav>

        </div>
    </div>
</div>


</div>

<?php 
require('includes/footer.php'); 
include('includes/scripts.php');
?>
