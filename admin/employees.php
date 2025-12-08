<?php
session_start();
require 'config/dbcon.php';
require('includes/header.php');


// Fetch employees with branch name
$sql = "SELECT e.id, e.first_name, e.last_name, e.middle_name, e.contact, e.email, e.address, 
               e.status, b.name AS branch_name
        FROM users e
        LEFT JOIN branch b ON e.branch_id = b.id
        WHERE e.role='employee'
        ORDER BY e.id DESC";

$result = mysqli_query($con, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employees</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>
</head>
<body>

<div class="container fluid px-4 mt-5">
    <h2>Employee List</h2>

    <div class="row">

        <div class="text-start mb-3">
            <!-- <a href="index.php" 
            class="btn btn-secondary btn-m shadow-none" 
            style="display: inline-flex; align-items: center;">
            <i class="fa-solid fa-arrow-left me-1"></i> Back 
            </a> -->

            <a href="add_employee.php" class="btn btn-success  shadow-none btn-sm mb-0"> 
                 Add 
                 <!-- <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#addEmployee">
                    <i class="fa-regular fa-square-plus"></i> Add
                </button> -->
            </a>
        </div>
    </div>
    
    <?php if(isset($_SESSION['success'])): ?>
        <script>
            alertify.success("<?= $_SESSION['success']; ?>");
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <script>
            alertify.error("<?= $_SESSION['error']; ?>");
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Address</th>
                <th>Branch</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result && mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr <?= $row['status'] === 'Inactive' ? 'class="table-secondary"' : '' ?>>
                        <td><?= $row['id']; ?></td>
                        <td>
                            <?= htmlspecialchars($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'].' ' : '') . $row['last_name']); ?>
                            <?php if($row['status'] === 'Inactive'): ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['contact']); ?></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td><?= htmlspecialchars($row['address']); ?></td>
                        <td><?= htmlspecialchars($row['branch_name']); ?></td>
                        <td><?= $row['status']; ?></td>
                        <td>
                            <?php if($row['status'] === 'Active'): ?>
                                <button class="btn btn-sm btn-info editBtn" 
                                        data-id="<?= $row['id']; ?>"
                                        data-first="<?= htmlspecialchars($row['first_name']); ?>"
                                        data-middle="<?= htmlspecialchars($row['middle_name']); ?>"
                                        data-last="<?= htmlspecialchars($row['last_name']); ?>"
                                        data-contact="<?= htmlspecialchars($row['contact']); ?>"
                                        data-email="<?= htmlspecialchars($row['email']); ?>"
                                        data-address="<?= htmlspecialchars($row['address']); ?>"
                                        data-branch="<?= htmlspecialchars($row['branch_name']); ?>"
                                        data-status="<?= $row['status']; ?>"
                                >Edit</button>
                                <button class="btn btn-sm btn-success viewBtn"
                                    data-id="<?= $row['id']; ?>"
                                    data-first="<?= htmlspecialchars($row['first_name']); ?>"
                                    data-middle="<?= htmlspecialchars($row['middle_name']); ?>"
                                    data-last="<?= htmlspecialchars($row['last_name']); ?>"
                                    data-contact="<?= htmlspecialchars($row['contact']); ?>"
                                    data-email="<?= htmlspecialchars($row['email']); ?>"
                                    data-address="<?= htmlspecialchars($row['address']); ?>"
                                    data-branch="<?= htmlspecialchars($row['branch_name']); ?>"
                                    data-status="<?= $row['status']; ?>"
                                >View</button>


                                <button class="btn btn-sm btn-danger deleteBtn" data-id="<?= $row['id']; ?>">Remove</button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-success reactivateBtn" data-id="<?= $row['id']; ?>">Reactivate</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center">No employees found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>



<!-- <nav aria-label="Page navigation">
    <ul class="pagination justify-content-end">
        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
            <a class="page-link" href="">Prev</a>
        </li>
        <li class="page-item disabled">
            <span class="page-link"></span>
        </li>
        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
            <a class="page-link" href="">Next</a>
        </li>
    </ul>
</nav> -->



<!-- Edit Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editEmployeeForm" action="function/update_employee.php" method="POST">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editEmployeeLabel">Edit Employee</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="status" id="edit_status">

                <div class="mb-3">
                    <label>First Name</label>
                    <input type="text" name="first_name" id="edit_first" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" id="edit_middle" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Last Name</label>
                    <input type="text" name="last_name" id="edit_last" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Contact</label>
                    <input type="text" name="contact" id="edit_contact" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Address</label>
                    <textarea name="address" id="edit_address" class="form-control" required></textarea>
                </div>
                <div class="mb-3">
                    <label>Branch</label>
                    <select name="branch_id" id="edit_branch" class="form-control" required>
                        <option value="">-- Select Branch --</option>
                        <?php
                        $branches = mysqli_query($con, "SELECT id, name FROM branch WHERE status='Active'");
                        while($b = mysqli_fetch_assoc($branches)) {
                            echo "<option value='{$b['id']}'>{$b['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="update_employee" id="updateBtn" class="btn btn-success">Update</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
    </form>
  </div>
</div>

<div class="modal fade" id="addEmployee" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        
        <form action="functions/insert_renter.php" method="POST" id="employeeForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel"><i class="fa-solid fa-house-circle-check"></i> Add Employee</h5>
                </div>
                <div class="modal-body">
                    <?php 
                        $branches = mysqli_query($con, "SELECT id, name 
                        FROM branch WHERE status = 'Active'");
                   ?>
                    <div class="row">
                        <!-- First Name -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">First Name</label>
                            <input type="text" id="fname" name="first_name" class="form-control shadow-none" required pattern="[A-Za-z ]+" title="Only letters and spaces allowed.">
                            <div class="invalid-feedback">First name must contain only letters, spaces, or period.</div>

                        </div>
                        <!-- Last Name -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Last Name</label>
                            <input type="text" id="lname" name="last_name" class="form-control shadow-none" required pattern="[A-Za-z ]+" title="Only letters and spaces allowed.">
                            <div class="invalid-feedback">Last name must contain only letters, spaces, or period.</div>

                        </div>
                        <!-- Middle Name -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">M.I</label>
                            <input type="text" id="middle_name" name="middle_name" class="form-control shadow-none">
                            <div class="invalid-feedback">middle name must contain only letters, spaces, or period.</div>

                        </div>
                        <!-- Contact -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Contact Number</label>
                            <input type="text" id="contact" name="contact" class="form-control shadow-none" required pattern="\d{11}" maxlength="11" title="Must be exactly 11 digits (e.g. 639XXXXXXXXX)">
                            <div class="invalid-feedback">Use PH format: 09XXXXXXXXX or +639XXXXXXXXX.</div>

                        </div>
                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" id="email" name="email" class="form-control shadow-none" required>
                            <div class="invalid-feedback">Enter a valid email address.</div>

                        </div>
                        <!-- Address -->
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Address</label>
                            <textarea name="address" class="form-control shadow-none" rows="1" required></textarea>
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
                       
                        <!-- Move-in Date -->
                        <!-- <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Date Started</label>
                            <input type="date" id="move_in_date" name="move_in_date" class="form-control shadow-none" required>
                        </div> -->

                        <!-- Password -->
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Password (Default: emp123)</label>
                            <!-- <input type="password" name="password" class="form-control shadow-none" value="emp123" readonly required> -->
                        </div>
                       
                       
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn text-secondary shadow-none" data-bs-dismiss="modal">CANCEL</button>
                    <button type="submit"name="add_employee" class="btn btn-success text-white shadow-none">ADD</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- View Employee Modal -->
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-labelledby="viewEmployeeLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="viewEmployeeLabel">
          <i class="fa-solid fa-user"></i> Employee Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3">
              <h6><i class="fa-solid fa-id-badge me-2"></i>ID</h6>
              <p id="view_id" class="fw-bold mb-0"></p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3">
              <h6><i class="fa-solid fa-user-tie me-2"></i>Name</h6>
              <p id="view_name" class="fw-bold mb-0"></p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3">
              <h6><i class="fa-solid fa-phone me-2"></i>Contact</h6>
              <p id="view_contact" class="fw-bold mb-0"></p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3">
              <h6><i class="fa-solid fa-envelope me-2"></i>Email</h6>
              <p id="view_email" class="fw-bold mb-0"></p>
            </div>
          </div>
          <div class="col-12">
            <div class="card border-0 shadow-sm p-3">
              <h6><i class="fa-solid fa-map-marker-alt me-2"></i>Address</h6>
              <p id="view_address" class="fw-bold mb-0"></p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3">
              <h6><i class="fa-solid fa-building me-2"></i>Branch</h6>
              <p id="view_branch" class="fw-bold mb-0"></p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3">
              <h6><i class="fa-solid fa-circle-info me-2"></i>Status</h6>
              <p id="view_status" class="fw-bold mb-0">
                <span id="view_status_badge" class="badge"></span>
              </p>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary shadow-none" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



        


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

    <script>
        document.querySelectorAll(".viewBtn").forEach(button => {
            button.addEventListener("click", function() {
                // Set basic fields
                document.getElementById("view_id").textContent = this.dataset.id;
                document.getElementById("view_name").textContent =
                    this.dataset.first + " " + (this.dataset.middle ? this.dataset.middle + " " : "") + this.dataset.last;
                document.getElementById("view_contact").textContent = this.dataset.contact;
                document.getElementById("view_email").textContent = this.dataset.email;
                document.getElementById("view_address").textContent = this.dataset.address;
                document.getElementById("view_branch").textContent = this.dataset.branch;

                // Status badge
                const statusBadge = document.getElementById("view_status_badge");
                const status = this.dataset.status;
                statusBadge.textContent = status;
                statusBadge.className = "badge"; // reset
                if(status === "Active") statusBadge.classList.add("bg-success");
                else if(status === "Inactive") statusBadge.classList.add("bg-danger");
                else statusBadge.classList.add("bg-secondary");

                // Show modal
                new bootstrap.Modal(document.getElementById('viewEmployeeModal')).show();
            });
        });


        document.addEventListener("DOMContentLoaded", function() {
        // Edit Button
        document.querySelectorAll(".editBtn").forEach(button => {
            button.addEventListener("click", function() {
                const status = this.dataset.status;
                document.getElementById("edit_id").value = this.dataset.id;
                document.getElementById("edit_first").value = this.dataset.first;
                document.getElementById("edit_middle").value = this.dataset.middle;
                document.getElementById("edit_last").value = this.dataset.last;
                document.getElementById("edit_contact").value = this.dataset.contact;
                document.getElementById("edit_email").value = this.dataset.email;
                document.getElementById("edit_address").value = this.dataset.address;
                document.getElementById("edit_status").value = status;

                let branchSelect = document.getElementById("edit_branch");
                for (let i = 0; i < branchSelect.options.length; i++) {
                    if (branchSelect.options[i].text === this.dataset.branch) {
                        branchSelect.selectedIndex = i;
                        break;
                    }
                }

                // Disable form if inactive
                const formFields = document.getElementById("editEmployeeForm").querySelectorAll("input, textarea, select");
                if(status === 'Inactive') {
                    formFields.forEach(el => el.disabled = true);
                    document.getElementById("updateBtn").classList.add("d-none");
                } else {
                    formFields.forEach(el => el.disabled = false);
                    document.getElementById("updateBtn").classList.remove("d-none");
                }

                new bootstrap.Modal(document.getElementById('editEmployeeModal')).show();
            });
        });

        // Reactivate Button in table
        document.querySelectorAll(".reactivateBtn").forEach(button => {
            button.addEventListener("click", function() {
                const id = this.dataset.id;
                alertify.confirm('Reactivate Employee', 'Are you sure you want to reactivate this employee?',
                    function(){ window.location.href = 'function/reactive_employee.php?id=' + id; },
                    function(){ /* cancel */ });
            });
        });

        //delte button
        document.querySelectorAll(".deleteBtn").forEach(button => {
            button.addEventListener("click", function() {
                const id = this.dataset.id;
                alertify.confirm('Delete Employee', 'Are you sure you want to delete this employee?',
                    function() {
                        // Redirect to your delete PHP file
                        window.location.href = 'function/delete_employee.php?id=' + id;
                    },
                    function() {
                        // Cancelled
                    }
                );
            });
        });

    });

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
