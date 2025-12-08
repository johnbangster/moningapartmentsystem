<?php
require 'admin/config/dbcon.php';

// Fetch available units
$units = $con->query("SELECT * FROM units WHERE status='available'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Booking Page</title>
<link rel="stylesheet" href="assets/css/bootstrap5.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Book a Unit</h2>
    <form id="booking_form">
        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Contact</label>
            <input type="text" name="contact" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Select Unit</label>
            <select name="unit_id" class="form-control" required>
                <option value="">Select...</option>
                <?php while($row = $units->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" data-name="<?= $row['unit_name'] ?>">
                        <?= $row['unit_name'] ?> - ₱<?= number_format($row['price'], 2) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Amount (PHP)</label>
            <input type="number" name="amount" class="form-control" required>
        </div>
        <button type="button" id="gcashPayBtn" class="btn btn-success">Pay with GCash</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('gcashPayBtn');
    btn.addEventListener('click', () => {
        const formData = new FormData(document.getElementById('booking_form'));
        fetch('gcash_create_payment.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success && data.checkout_url) window.location.href = data.checkout_url;
            else alert(data.message || "GCash error");
        })
        .catch(err => {
            console.error(err);
            alert("Network error");
        });
    });

    // auto-fill unit_name for PHP
    document.querySelector('select[name="unit_id"]').addEventListener('change', function() {
        const selected = this.selectedOptions[0];
        if(selected) {
            let hidden = document.querySelector('input[name="unit_name_hidden"]');
            if(!hidden) {
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'unit_name';
                document.getElementById('booking_form').appendChild(hidden);
            }
            hidden.value = selected.dataset.name;
        }
    });
});
</script>
</body>
</html>
