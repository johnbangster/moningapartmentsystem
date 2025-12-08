<?php

    include('authentication.php');
    require_once ('config/code.php');
    require ('includes/header.php');
?>


<div class="container-fluid px-4">
    <h1 class="mt-4">BILL</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">ADMIN DASHBOARD</li>
    </ol>

    <div class="row">
        <div class="card border-0 shadow mb-4">
            <div class="card-body">

                <div class="d-flex justify-content-between mb-3">

                    <a href="bill.php" class="btn btn-dark rounded-pill shadow-none btn-sm">
                        <i class="fa-solid fa-plus"></i> NEW BILL
                    </a>
                </div>

                <!-- 
                <div class="table-responsive-md" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Renter</th>
                                <th>Month</th>
                                <th>Due Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            
                            $filter_month = isset($_GET['filter_month']) ? $_GET['filter_month'] : '';

                            // $query = mysqli_query($con, "
                            //     SELECT b.*, r.first_name, r.last_name 
                            //     FROM bills b 
                            //     JOIN renters r ON b.renter_id = r.id
                            //     ORDER BY b.billing_month DESC
                            // ");

                            $query_sql = "
                                SELECT b.*, r.first_name, r.last_name 
                                FROM bills b 
                                JOIN renters r ON b.renter_id = r.id
                            ";

                            if (!empty($filter_month)) {
                                $query_sql .= " WHERE b.billing_month = '" . mysqli_real_escape_string($con, $filter_month) . "'";
                            }

                            $query_sql .= " ORDER BY b.billing_month DESC";
                            $query = mysqli_query($con, $query_sql);


                            if (mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_assoc($query)) {
                                    echo "<tr>
                                        <td>{$row['first_name']} {$row['last_name']}</td>
                                        <td>" . date('M', strtotime($row['due_date'])) . "</td>

                                        <td>" . date('M d, Y', strtotime($row['due_date'])) . "</td>

                                        <td>₱" . number_format($row['total_amount'], 2) . "</td>
                                        <td><span class='badge ".($row['status'] == 'paid' ? 'bg-success' : 'bg-danger')."'>{$row['status']}</span></td>
                                        <td>
                                            <button class='btn btn-sm btn-primary editBtn' 
                                                data-id='{$row['id']}'
                                                data-total='{$row['total_amount']}'
                                                data-status='{$row['status']}'
                                                data-bs-toggle='modal' data-bs-target='#editModal'>
                                                Edit
                                            </button>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>No bills found.</td></tr>";
                            }
                        ?>
                        </tbody>
                    </table>
                </div>  -->

                <?php
                // Setup month filter
                $monthFilter = isset($_GET['month']) ? $_GET['month'] : '';
                $limit = 10; // rows per page
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $limit;

                // Month filter options from database
                $month_query = mysqli_query($con, "SELECT DISTINCT DATE_FORMAT(due_date, '%M, %Y') AS month FROM bills ORDER BY due_date DESC");
            ?>

            <!-- Month Filter Dropdown -->
            <form method="GET" class="mb-3 d-flex justify-content-end align-items-center">
                <label class="me-2 mb-0">Filter by Month:</label>
                <select name="month" class="form-select w-auto me-2" onchange="this.form.submit()">
                    <option value="">All</option>
                    <?php while ($m = mysqli_fetch_assoc($month_query)) {
                        $val = $m['month'];
                        $selected = ($monthFilter == $val) ? 'selected' : '';
                        echo "<option value='$val' $selected>" . date('M, Y', strtotime($val)) . "</option>";
                    } ?>
                </select>
            </form>

            <div class="table-responsive-md" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Renter</th>
                            <th>Month</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $filter = $monthFilter ? "WHERE DATE_FORMAT(b.due_date, '%m') = '$monthFilter'" : "";
                        $query = mysqli_query($con, "
                            SELECT b.*, r.first_name, r.last_name 
                            FROM bills b 
                            JOIN renters r ON b.renter_id = r.id
                            $filter
                            ORDER BY b.due_date DESC
                            LIMIT $limit OFFSET $offset
                        ");

                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) {
                                echo "<tr>
                                        <td>{$row['first_name']} {$row['last_name']}</td>
                                        <td>" . date('M', strtotime($row['due_date'])) . "</td>
                                        <td>{$row['due_date']}</td>
                                        <td>₱" . number_format($row['total_amount'], 2) . "</td>
                                        <td><span class='badge ".($row['status'] == 'paid' ? 'bg-success' : 'bg-danger')."'>{$row['status']}</span></td>
                                        <td>
                                            <button class='btn btn-sm btn-primary editBtn' 
                                                data-id='{$row['id']}'
                                                data-total='{$row['total_amount']}'
                                                data-status='{$row['status']}'
                                                data-bs-toggle='modal' data-bs-target='#editModal'><i class='fa-solid fa-pen-to-square'></i> 
                                            </button>
                                            <button class='btn btn-sm btn-warning'><i class='fas fa-file-invoice fa-lg'></i></button>
                                            <button class='btn btn-sm btn-success'><i class='fas fa-money-bill-wave'></i></button>
                                        </td>
                                       
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>No bills found.</td></tr>";
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
                    " . ($filter ? $filter : "")
                );
                $total_rows = mysqli_fetch_assoc($count_result)['total'];
                $total_pages = ceil($total_rows / $limit);
            ?>

            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-end">
                    <?php for ($i = 1; $i <= $total_pages; $i++): 
                        $active = ($i == $page) ? 'active' : '';
                        $link = "?page=$i" . ($monthFilter ? "&month=$monthFilter" : "");
                    ?>
                        <li class="page-item <?= $active ?>"><a class="page-link" href="<?= $link ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                </ul>
            </nav>


                        </div>
                    </div>
                </div>
            </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="update_bill.php">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Bill</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="bill_id" id="bill_id">
                        <div class="mb-3">
                            <label>Total Amount (₱)</label>
                            <input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="unpaid">Unpaid</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="update_bill" class="btn btn-success">Update Bill</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
        
    <!-- Agreement modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="update_bill.php">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Bill</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="bill_id" id="bill_id">
                        <div class="mb-3">
                            <label>Total Amount (₱)</label>
                            <input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="unpaid">Unpaid</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="update_bill" class="btn btn-success">Update Bill</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php 
        require('includes/footer.php'); 
        include('includes/scripts.php');
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.editBtn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('bill_id').value = btn.dataset.id;
                document.getElementById('total_amount').value = btn.dataset.total;
                document.getElementById('status').value = btn.dataset.status;
            });
        });
    </script>













