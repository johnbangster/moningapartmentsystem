<?php
session_start();
include('config/dbcon.php');
date_default_timezone_set(timezoneId: 'Asia/Manila');


$searchResults = null;
$data = null;


if(isset($_GET['search']) && !empty($_GET['search'])){
    $search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';

    // $search = mysqli_real_escape_string($con, $_GET['search']);
    
    $searchQuery = "
        SELECT 
            r.id AS renter_id, r.first_name, r.middle_name, r.last_name, r.email, r.contacts,
            a.id AS agreement_id, a.term_months, a.monthly_rent, a.start_date, a.end_date,
            u.id AS unit_id, u.name AS unit_name, u.area, u.price, u.status AS unit_status, u.address AS unit_address, u.description AS unit_description,
            t.type_name AS unit_type, t.description AS type_description,
            b.name AS branch_name, b.address AS branch_address
        FROM units u
        LEFT JOIN rental_agreements a ON a.unit_id = u.id
        LEFT JOIN renters r ON a.renter_id = r.id
        LEFT JOIN unit_type t ON u.unit_type_id = t.id
        LEFT JOIN branch b ON u.branch_id = b.id
        WHERE 
            (r.first_name LIKE '%$search%'
            OR r.last_name LIKE '%$search%'
            OR r.email LIKE '%$search%'
            OR r.contacts LIKE '%$search%'
            OR u.name LIKE '%$search%'
            OR t.type_name LIKE '%$search%'
            OR u.status LIKE '%$search%'
            OR b.name LIKE '%$search%'
            OR u.address LIKE '%$search%')
    ";

    //Restrict employee to their branch only
    if($_SESSION['auth_role'] == 'employee'){
        $user_branch = $_SESSION['branch_id'];
        $searchQuery .= " AND u.branch_id = '$user_branch'";
    }

    $searchQuery .= " ORDER BY u.id ASC";

    
    $searchResults = mysqli_query($con, $searchQuery);
    if(!$searchResults){
        die("SQL Error: ".mysqli_error($con));
    }
}

// ---Handle View Renter ---
if(isset($_GET['renter_id']) && is_numeric($_GET['renter_id'])){
    $renter_id = intval($_GET['renter_id']);
    
    $query = "SELECT 
                r.id AS renter_id, r.first_name, r.middle_name, r.last_name, r.email, r.contacts,
                a.id AS agreement_id, a.term_months, a.monthly_rent, a.start_date, a.end_date,
                u.name AS unit_name, u.area, u.price, u.status, u.address, u.description,
                t.type_name AS unit_type, t.description AS type_description,
                b.name AS branch_name, b.address AS branch_address
              FROM renters r
              LEFT JOIN rental_agreements a ON a.renter_id = r.id
              LEFT JOIN units u ON a.unit_id = u.id
              LEFT JOIN unit_type t ON u.unit_type_id = t.id
              LEFT JOIN branch b ON u.branch_id = b.id
              WHERE r.id = '$renter_id'
              LIMIT 1";
    
    $result = mysqli_query($con, $query);
    if($result && mysqli_num_rows($result) > 0){
        $data = mysqli_fetch_assoc($result);
    } else {
        echo "<div class='alert alert-warning text-center mt-5'>Renter not found.</div>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Renter Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<style>
/* PRINT STYLE */
@media print {

    body {
        font-family: Arial, sans-serif !important;
        font-size: 13px;
        margin: 20px;
    }

    /* Give spacing between sections */
    .section {
        margin-bottom: 25px;
    }

    /* Table formatting */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th, td {
        border: 1px solid #444;
        padding: 8px 10px;
        text-align: left;
    }

    th {
        background: #f0f0f0 !important;
        font-weight: bold;
    }

    table, tr, td, th {
        page-break-inside: avoid !important;
    }

    /* HEADER */
    .header-print {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .header-print img {
        height: 90px;
    }

    /* NEW — FORCE billing table on page 2 */
    .billing-section {
        page-break-before: always;
    }
}
</style>

<body class="bg-light">

    <?php include('includes/header.php'); ?>


<div class="container my-5">

    

    <!--Search Form -->
    <form method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" required class="form-control" placeholder="Search renter name, email, or phone" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button class="btn btn-primary">Search</button>
        </div>
    </form>
    <?php if($searchResults && mysqli_num_rows($searchResults) > 0): ?>
        <div class="mb-4">
            <a href="export_excel_search.php?search=<?= urlencode($_GET['search']); ?>" class="btn btn-sm btn-success">
                Excel
            </a>
            <a href="export_search_pdf.php?search=<?= urlencode($_GET['search']); ?>" class="btn btn-sm btn-danger">
                PDF
            </a>
             <!-- <a href="print_search.php?search=<?= urlencode($_GET['search']); ?>" class="btn btn-sm btn-info">
                Print
            </a> -->
        </div>
    <?php endif; ?>


    <?php if(isset($_GET['renter_id'])): 
        $renter_id = intval($_GET['renter_id']);
    ?>
        <div class="mb-4">
            <a href="export_excel.php?renter_id=<?= $renter_id; ?>" class="btn btn-sm btn-success">
                Excel
            </a>
            <a href="export_renter_pdf.php?renter_id=<?= $renter_id; ?>" class="btn btn-sm btn-danger">
                PDF
            </a>
            <!-- <a href="export_renter_csv.php?renter_id=<?= $renter_id; ?>" class="btn btn-sm btn-success">
                Export to CSV
            </a> -->
            <a class="btn btn-sm btn-info" onclick="rentInfo()">
                Print
            </a>
        </div>
    <?php endif; ?>


    <!--Search Results Table -->
    <?php if($searchResults !== null): ?>
        <?php if(mysqli_num_rows($searchResults) > 0): ?>
            <div class="card mb-4 shadow">
                <div class="card-header bg-info text-white"><h5>Search Results</h5></div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-striped mb-0">
    <thead class="table-dark">
        <tr>
            <th>Unit ID</th>
            <th>Unit Name</th>
            <th>Unit Type</th>
            <th>Status</th>
            <th>Branch</th>
            <th>Renter Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Agreement</th>
            <th>View</th>
        </tr>
    </thead>
    <tbody>
    <?php while($row = mysqli_fetch_assoc($searchResults)): ?>
        <tr>
            <td><?= $row['unit_id']; ?></td>
            <td><?= $row['unit_name']; ?></td>
            <td><?= $row['unit_type'] ?: 'N/A'; ?></td>
            <td><?= $row['unit_status'] ?: 'N/A'; ?></td>
            <td><?= $row['branch_address'] ?: 'N/A'; ?></td>
            <td><?= $row['first_name'] ? $row['first_name'].' '.$row['middle_name'].' '.$row['last_name'] : '<span class="text-danger">No Renter</span>'; ?></td>
            <td><?= $row['email'] ?: '-'; ?></td>
            <td><?= $row['contacts'] ?: '-'; ?></td>
            <td><?= $row['agreement_id'] ? $row['term_months'].' mos' : '<span class="text-danger">No Agreement</span>'; ?></td>
            <td>
                <?php if($row['renter_id']): ?>
                    <a href="renter_report.php?renter_id=<?= $row['renter_id']; ?>" class="btn btn-sm btn-info">View</a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">No results found for: <strong><?= htmlspecialchars($_GET['search']); ?></strong></div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- View Renter Details -->
    <?php if($data): 
        // Fetch bills
        $billQuery = "SELECT * FROM bills WHERE renter_id='$renter_id' ORDER BY due_date ASC";
        $bill_run = mysqli_query($con, $billQuery);
    ?>
        <div class="container my-4 p-4 bg-white shadow-sm rounded" id="rentInfo">
            <div class="d-flex align-items-center mb-4">
                <img src="images/logo.png" alt="Logo" style="height:100px; margin-right:15px;">
                <div>
                    <h3 class="fw-bold mb-0">Monings Rental Services</h3>
                    <small class="text-muted">Address: 1438-B M.J.Cuenco Ave, Brgy Mabolo, Cebu City</small><br>
                    <small class="text-muted">Generated on <?= date("F d, Y"); ?></small>
                </div>
            </div>

            <div class="row g-4">
                <!-- Renter Info -->
                <div class="col-md-4">
                    <h5>Renter Information</h5>
                    <p><strong>Full Name:</strong> <?= $data['first_name'].' '.$data['middle_name'].' '.$data['last_name']; ?></p>
                    <p><strong>Email:</strong> <?= $data['email']; ?></p>
                    <p><strong>Contact:</strong> <?= $data['contacts']; ?></p>
                </div>

                <!-- Lease Info -->
                <div class="col-md-4">
                    <h5>Lease Agreement</h5>
                    <?php if($data['agreement_id']): ?>
                        <p><strong>Term:</strong> <?= $data['term_months']; ?> months</p>
                        <p><strong>Monthly Rent:</strong> ₱<?= number_format($data['monthly_rent'],2); ?></p>
                        <p><strong>Start:</strong> <?= date('F d, Y', strtotime($data['start_date'])); ?></p>
                        <p><strong>End:</strong> <?= date('F d, Y', strtotime($data['end_date'])); ?></p>
                        <span class="badge bg-success">ACTIVE</span>
                    <?php else: ?>
                        <div class="alert alert-warning">No lease agreement found.</div>
                    <?php endif; ?>
                </div>

                <!-- Unit Info -->
                <div class="col-md-4">
                    <h5>Unit Information</h5>
                    <?php if($data['unit_name']): ?>
                        <p><strong>Unit Name:</strong> <?= $data['unit_name']; ?></p>
                        <p><strong>Unit Type:</strong> <?= $data['unit_type']; ?> (<?= $data['type_description']; ?>)</p>
                        <p><strong>Area:</strong> <?= $data['area']; ?> sqm</p>
                        <p><strong>Price:</strong> ₱<?= number_format($data['price'],2); ?></p>
                        <p><strong>Branch:</strong> <?= $data['branch_address']; ?></p>
                    <?php else: ?>
                        <div class="alert alert-warning">No unit assigned.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bills Table -->
             <div class="mt-4 billing-section">
                <h5>Billing</h5>
                <?php if($bill_run && mysqli_num_rows($bill_run) > 0): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Reference #</th>
                                <th>Month</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Status</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php while($bill = mysqli_fetch_assoc($bill_run)): ?>
                                <tr>
                                    <td><?= $bill['reference_id']; ?></td>
                                    <td><?= date('F', strtotime($bill['due_date'])); ?></td>
                                    <td><?= date('M d, Y', strtotime($bill['due_date'])); ?></td>
                                    <td>₱<?= number_format($bill['total_amount'],2); ?></td>
                                    <td>
                                        <span class="badge 
                                            <?= $bill['status'] == 'paid' ? 'bg-success' : 
                                            ($bill['status'] == 'partial' ? 'bg-warning' : 'bg-info'); ?>">
                                            <?= strtoupper($bill['status']); ?>
                                        </span>
                                    </td>

                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">No billing records found.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function rentInfo() {
    var content = document.getElementById("rentInfo").innerHTML;
    var printWindow = window.open('', '', 'width=900,height=700');

    printWindow.document.write(`
        <html>
        <head>
            <title>Monings Rental Services</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 25px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 15px;
                }
                th, td {
                    border: 1px solid #333;
                    padding: 10px;
                    font-size: 13px;
                }
                th {
                    background: #e9ecef;
                }
                h3 {
                    margin-bottom: 4px;
                }
                .header-print {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                    margin-bottom: 20px;
                }
                .header-print img {
                    height: 90px;
                }
            </style>
        </head>
        <body>
            ${content}
        </body>
        </html>
    `);

    printWindow.document.close();

    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 500);
}

    // function rentInfo(){
    //     var divContents =  document.getElementById("rentInfo").innerHTML;
    //     var a = window.open('','');
    //     // a.document.write('<html><titlte>Monings Rental Services</title>');
    //     a.document.write('<body style="font-family: fansong;">');
    //     a.document.write(divContents);
    //     a.document.write('</body></html>');
    //     a.document.close();
    //     a.print();

    // }
</script>
</body>
</html>
