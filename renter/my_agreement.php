<?php
session_start();
require '../admin/config/dbcon.php';
require ('includes/header.php');


if (!isset($_SESSION['auth_user']['user_id'])) {
    die("Access denied. Please log in as renter.");
}

$renter_id = intval($_SESSION['auth_user']['user_id']);

// Map user_id → renter_id
$sql_renter = mysqli_query($con, "SELECT id FROM renters WHERE user_id = $renter_id");
if ($r = mysqli_fetch_assoc($sql_renter)) {
    $renter_id = $r['id'];
} else {
    die("No renter profile found for this user.");
}

// Fetch agreements
$res = mysqli_query($con, "
  SELECT a.*, u.name
  FROM rental_agreements a
  JOIN units u ON u.id = a.unit_id
  WHERE a.renter_id = $renter_id
  ORDER BY a.created_at DESC
");

if (!$res) {
  die("Query Error: " . mysqli_error($con));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Lease Agreements</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">My Lease Agreements</h3>
  </div>

  <?php if (isset($_GET['accepted'])): ?>
    <div class="alert alert-success">Agreement successfully accepted!</div>
  <?php endif; ?>

  <div class="card shadow-sm border-0">
    <div class="card-body">
      <table id="agreementsTable" class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Unit</th>
            <th>Term</th>
            <th>Period</th>
            <th>Status</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; while ($row = mysqli_fetch_assoc($res)): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= $row['term_months'] ?> months</td>
              <td><?= $row['start_date'] ?> to <?= $row['end_date'] ?></td>
              <td>
                <span class="badge bg-<?= $row['status'] === 'accepted' ? 'success' : 'secondary' ?>">
                  <?= ucfirst($row['status']) ?>
                </span>
              </td>
              <td class="text-center">
                <a href="accept-agreement.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">
                  <i class="fa-solid fa-eye me-1"></i> View & Accept
                </a>
                <!-- <a href="../generate_pdf.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" target="_blank">
                  <i class="fa-solid fa-file-pdf me-1"></i> PDF
                </a> -->
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script>
  $(document).ready(function () {
    $('#agreementsTable').DataTable();
  });
</script>

</body>
</html>
