<?php 
session_start();
require 'config/dbcon.php';
require('includes/header.php');


// Fetch branches for dropdown
$branches = mysqli_query($con, "SELECT id, name FROM branch WHERE status = 'Active'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Employee</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- AlertifyJS CSS -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>
</head>

<body>
    <div class="container fluid px-4 mt-4">
        <h2>Add New Employee</h2>
        <div class="row">
            <div class="card border-0 shadow mb-4">
                <div class="card-body">
                    <!-- Employee Form -->
                    <form id="employeeForm" action="function/insert_employee.php" method="POST" novalidate>
                        <div class="row mb-3">
                            <div class="col">
                                <label>First Name</label>
                                <input type="text" name="first_name" class="form-control" required>
                                <div class="invalid-feedback">First name must contain only letters, spaces, or period.</div>
                            </div>
                            <div class="col">
                                <label>Last Name</label>
                                <input type="text" name="last_name" class="form-control" required>
                                <div class="invalid-feedback">Last name must contain only letters, spaces, or period.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" class="form-control">
                            <div class="invalid-feedback">Middle name must contain only letters, spaces, or period.</div>
                        </div>

                        <div class="mb-3">
                            <label>Contact</label>
                            <input type="text" name="contact" class="form-control" required>
                            <div class="invalid-feedback">Use PH format: 09XXXXXXXXX or +639XXXXXXXXX.</div>
                        </div>

                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                            <div class="invalid-feedback">Enter a valid email address.</div>
                        </div>

                        <div class="mb-3">
                            <label>Address</label>
                            <textarea name="address" class="form-control" required></textarea>
                            <div class="invalid-feedback">Address is required.</div>
                        </div>

                        <div class="mb-3">
                            <label>Assign Branch</label>
                            <select name="branch_id" class="form-control" required>
                                <option value="">-- Select Branch --</option>
                                <?php while ($b = mysqli_fetch_assoc($branches)): ?>
                                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <div class="invalid-feedback">Please select a branch.</div>
                        </div>

                        <button type="submit" name="add_employee" class="btn btn-primary">Add Employee</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- AlertifyJS -->
     <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    
    <script>
    <?php if(isset($_SESSION['success'])): ?>
        alertify.set('notifier','position', 'top-right');
        alertify.success("<?= addslashes($_SESSION['success']); ?>");
        setTimeout(function(){ window.location.href = "employees.php"; }, 2000);
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        alertify.set('notifier','position', 'top-right');
        alertify.error("<?= addslashes($_SESSION['error']); ?>");
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    </script>

    <!-- Client-Side Validation -->
    <script>

        document.getElementById("employeeForm").addEventListener("submit", function(e) {
        let valid = true;

        let firstName   = document.querySelector("[name='first_name']");
        let lastName    = document.querySelector("[name='last_name']");
        let middleName  = document.querySelector("[name='middle_name']");
        let contact     = document.querySelector("[name='contact']");
        let email       = document.querySelector("[name='email']");
        let address     = document.querySelector("[name='address']");
        let branch      = document.querySelector("[name='branch_id']");

        let nameRegex   = /^[a-zA-Z\s.]+$/;
        let contactRegex= /^(09\d{9}|\+639\d{9})$/;
        let emailRegex  = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        // Reset states
        [firstName,lastName,middleName,contact,email,address,branch].forEach(el=>{
            el.classList.remove("is-invalid","is-valid");
        });

        // First Name
        if (!nameRegex.test(firstName.value.trim())) {
            firstName.classList.add("is-invalid"); valid = false;
        } else firstName.classList.add("is-valid");

        // Last Name
        if (!nameRegex.test(lastName.value.trim())) {
            lastName.classList.add("is-invalid"); valid = false;
        } else lastName.classList.add("is-valid");

        // Middle Name (optional)
        if (middleName.value.trim() !== "" && !nameRegex.test(middleName.value.trim())) {
            middleName.classList.add("is-invalid"); valid = false;
        } else if(middleName.value.trim() !== "") {
            middleName.classList.add("is-valid");
        }

        // Contact
        if (!contactRegex.test(contact.value.trim())) {
            contact.classList.add("is-invalid"); valid = false;
        } else contact.classList.add("is-valid");

        // Email
        if (!emailRegex.test(email.value.trim())) {
            email.classList.add("is-invalid"); valid = false;
        } else email.classList.add("is-valid");

        // Address
        if (address.value.trim() === "") {
            address.classList.add("is-invalid"); valid = false;
        } else address.classList.add("is-valid");

        // Branch
        if (branch.value === "") {
            branch.classList.add("is-invalid"); valid = false;
        } else branch.classList.add("is-valid");

        if (!valid) {
            e.preventDefault();
            alertify.error("Please fix the errors before submitting.");
        }
    });
    </script>
</body>
</html>
