<?php
// reservations.php
session_start();
require 'config/dbcon.php';
require 'config/code.php';
require '../function_booking.php';
include('includes/header.php');


// Fetch all reservations
$reservations = mysqli_query($con, "
    SELECT r.*, u.name AS unit_name 
    FROM reservations r 
    JOIN units u ON r.unit_id = u.id
    ORDER BY r.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Reservations</title>
    <link rel="stylesheet" href="assets/css/bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4">All Reservations</h2>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Unit</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Move-in Date</th>
                <th>Payment Method</th>
                <th>Payment Status</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($reservations)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['unit_name']) ?></td>
                <td><?= htmlspecialchars($row['fname'] . ' ' . $row['lname']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= htmlspecialchars($row['move_in_date']) ?></td>
                <td><?= htmlspecialchars($row['payment_method']) ?></td>
                <td><?= htmlspecialchars($row['payment_status']) ?></td>
                <td>₱ <?= number_format($row['amount_paid'], 2) ?></td>
               <td>
                    <?php 
                    if(in_array($row['payment_method'], ['cash', 'gcash', 'paypal'])): ?>
                        <div class="d-flex flex-wrap gap-2">
                        <?php if(strtolower($row['payment_method']) === 'cash' && $row['payment_status'] === 'pending'): ?>
                            <!-- Pay in Cash button -->
                            <button class="btn btn-success btn-sm payCashBtn" data-id="<?= $row['id'] ?>">
                                Pay in Cash
                            </button>
                        <?php elseif($row['payment_status'] === 'paid'): ?>
                            <!-- Create Renter button -->
                            <button class="btn btn-primary btn-sm createRenterBtn" data-id="<?= $row['id'] ?>">
                                Add Renter
                            </button>
                            <a href="bookReceipt_pdf.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-info btn-sm">
                                Receipt
                            </a>
                        <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <span class="text-muted">N/A</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="receiptModalLabel">Receipt</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="receiptContent">
        <!-- Receipt content will be loaded here -->
        <div class="text-center">Loading...</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="printReceipt()">Print</button>
      </div>
    </div>
  </div>
</div>


<script>

// Handle Pay in Cash
document.querySelectorAll('.payCashBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        const reservationId = btn.dataset.id;

        Swal.fire({
            title: 'Confirm Cash Payment?',
            text: 'Mark this reservation as paid in cash.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Pay'
        }).then(result => {
            if(result.isConfirmed){
                fetch('bookcash_payment.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({reservation_id: reservationId})
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success){
                        Swal.fire('Success', data.message, 'success').then(() => {
                            location.reload(); // Refresh to show Create Renter button
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Server error.', 'error'));
            }
        });
    });
});

// Handle Create Renter
document.querySelectorAll('.createRenterBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        const reservationId = btn.dataset.id;
        window.location.href = `renter.php`;

        // window.location.href = `renter.php?reservation_id=${reservationId}`;
    });
});

// Handle View Receipt
document.querySelectorAll('.viewReceiptBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        const reservationId = btn.dataset.id;
        const receiptContent = document.getElementById('receiptContent');

        receiptContent.innerHTML = '<div class="text-center">Loading...</div>';

        fetch(`bookReceipt_pdf.php?id=${reservationId}&ajax=1`) // Pass ajax=1 to return partial HTML
        .then(res => res.text())
        .then(data => {
            receiptContent.innerHTML = data;
            const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
            receiptModal.show();
        })
        .catch(() => {
            receiptContent.innerHTML = '<div class="text-danger">Failed to load receipt.</div>';
        });
    });
});

// Print receipt from modal
function printReceipt(){
    const printContents = document.getElementById('receiptContent').innerHTML;
    const originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload(); // Reload to restore page
}


// Handle "Pay in Cash" button click
// document.querySelectorAll('.payCashBtn').forEach(btn => {
//     btn.addEventListener('click', () => {
//         const reservationId = btn.getAttribute('data-id');

//         Swal.fire({
//             title: 'Confirm Cash Payment?',
//             text: 'This will mark the reservation as paid.',
//             icon: 'question',
//             showCancelButton: true,
//             confirmButtonText: 'Yes, Pay Now'
//         }).then(result => {
//             if(result.isConfirmed){
//                 fetch('bookcash_payment.php', {
//                     method: 'POST',
//                     headers: {'Content-Type':'application/json'},
//                     body: JSON.stringify({reservation_id: reservationId})
//                 })
//                 .then(res => res.json())
//                 .then(data => {
//                     if(data.success){
//                         Swal.fire('Success', data.message, 'success')
//                         .then(() => location.reload());
//                     } else {
//                         Swal.fire('Error', data.message, 'error');
//                     }
//                 })
//                 .catch(() => Swal.fire('Error', 'Server error.', 'error'));
//             }
//         });
//     });
// });



   
</script>

</body>
</html>
