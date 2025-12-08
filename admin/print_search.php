<?php
session_start();
include('config/dbcon.php');

if(!isset($_GET['search']) || empty($_GET['search'])){
    die("<h3>No search provided.</h3>");
}
$search = mysqli_real_escape_string($con, $_GET['search']);

$query = "
    SELECT 
    r.id AS renter_id, r.first_name, r.middle_name, r.last_name, r.email, r.contact,
    u.id AS unit_id, u.name AS unit_name, u.status AS unit_status,
    b.name AS branch_name, b.address AS branch_address,
    e.id AS employee_id, e.first_name AS employee_first, e.last_name AS employee_last
    FROM units u
    LEFT JOIN rental_agreements a ON a.unit_id = u.id
    LEFT JOIN users r ON a.renter_id = r.id AND r.role = 'renter'
    LEFT JOIN branch b ON u.branch_id = b.id
    LEFT JOIN users e ON e.branch_id = u.branch_id AND e.role = 'employee'
    WHERE r.first_name LIKE '%$search%'
    OR r.last_name LIKE '%$search%'
    OR r.email LIKE '%$search%'
    OR r.contact LIKE '%$search%'
    OR u.name LIKE '%$search%'

";
$results = mysqli_query($con, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Print Search Results</title>
    <style>
        body { font-family: Arial; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px;}
        th, td { border: 1px solid #000; padding: 6px; font-size: 12px; }
        th { background: #ddd; }
        .header { text-align: center; margin-bottom: 10px; }
        .logo { width: 70px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">🖨 Print Now</button>
</div>

<div class="header">
    <img src="images/logo.png" class="logo"><br>
    <h3>Monings Rental Services</h3>
    <small>1438-B M.J.Cuenco Ave, Brgy Mabolo, Cebu City</small><br>
    <small>Generated on: <?= date("F d, Y"); ?></small>
</div>

<h4>Search Results for: <?= htmlspecialchars($_GET['search']); ?></h4>

<table>
<tr>
    <th>Unit ID</th>
    <th>Unit Name</th>
    <th>Unit Type</th>
    <th>Status</th>
    <th>Branch</th>
    <th>Assigned Employee</th>
    <th>Unit Price</th>
</tr>
<?php while($row = mysqli_fetch_assoc($results)): ?>
<tr>
    <td><?= $row['unit_id']; ?></td>
    <td><?= $row['unit_name']; ?></td>
    <td><?= $row['unit_type'] ?: '-'; ?></td>
    <td><?= $row['unit_status'] ?: '-'; ?></td>
    <td><?= $row['branch_name'] ?: '-'; ?></td>
    <td><?= $row['employee_assigned'] ?: '-'; ?></td>
    <td>₱<?= number_format($row['price'],2); ?></td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
