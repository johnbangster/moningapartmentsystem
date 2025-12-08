
<!-- Always remember the auth user role/type if using session auth_role -->
<?php
session_start();
require ('../admin/config/dbcon.php');
require ('../admin/config/code.php');
require ('includes/header.php');


if (!isset($_SESSION['auth_user']['user_id'])) {
    die("Access denied. Please log in as renter.");
}

// $renter_id = $_SESSION['renter_id'];
$renter_id = $_SESSION['auth_user']['user_id'];



$success = '';
$error = '';

// $renter_id = $_SESSION['renter_id'];
// $renter_id = $_SESSION['auth_user']['user_id'];

$complaints = mysqli_query($con, "SELECT * FROM complaints WHERE renter_id = $renter_id ORDER BY created_at DESC");

// if (isset($_POST['submit_complaint'])) {
//     $type = $_POST['complaint_type'];
//     $remarks = trim($_POST['remarks']);

//     // Require remarks if type is "others"
//     if ($type === 'others' && empty($remarks)) {
//         $error = "Please provide specific details in Remarks.";
//     } else {
//         $stmt = mysqli_prepare($con, "INSERT INTO complaints (renter_id, complaint_type, remarks, created_by) VALUES (?, ?, ?, 'renter')");
//         mysqli_stmt_bind_param($stmt, "iss", $renter_id, $type, $remarks);

//         if (mysqli_stmt_execute($stmt)) {
//             $success = "Complaint submitted successfully.";
//         } else {
//             $error = "Error: " . mysqli_error($con);
//         }

//         mysqli_stmt_close($stmt);
//     }
// }


?>

    <!-- <script>
        function toggleRemarks(select) {
            var remarksField = document.getElementById('remarks');
            if (select.value === 'others') {
                remarksField.setAttribute('required', true);
            } else {
                remarksField.removeAttribute('required');
            }
        }
    </script> -->

<?php if ($success): ?>
    <p class="success"><?= $success ?></p>
<?php elseif ($error): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>


<div class="container-fluid px-4">
    <h1 class="mt-4">COMPLAINTS</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">COMPLAINTS</li>
    </ol> 
    <div class="row">
        <div class="card border-0 shadow mb-4">
            <div class="card-body">
                <div class="text-end mb-4">
                    <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#add-complaint">
                        <i class="fa-regular fa-square-plus"></i> Create
                    </button>
                     <!--id="team-data"-->
                    <div class="row">
                    </div>
                </div>  
                <div id="complaint-table">
                    <div class="table-responsive-lg" style="height:450px; overflow-y: scroll;">
                        <table class="table table-hover border text-center">
                            <thead>
                                <tr class="bg-dark text-light">
                                <th>#</th>
                                <th>Complaint Type</th>
                                <th>Remarks</th>
                                <th>Status</th>
                                <th>Date Submitted</th>
                                <th>Replies</th>
                                </tr>
                            </thead>
                            <tbody id="">
                                <?php if ($complaints && mysqli_num_rows($complaints) > 0): ?>
                                    <?php $i = 1; while ($row = mysqli_fetch_assoc($complaints)): ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= ucfirst($row['complaint_type']) ?></td>
                                            <td><?= htmlspecialchars($row['remarks']) ?></td>
                                            <td class="status-<?= strtolower($row['status']) ?>">
                                                <?= ucfirst($row['status']) ?>
                                            </td>
                                            <td><?= date("F j, Y g:i A", strtotime($row['created_at'])) ?></td>
                                            <td>
                                                <?php
                                                $cid = $row['id'];
                                                $replies = mysqli_query($con, "SELECT * FROM complaint_replies WHERE complaint_id = $cid ORDER BY created_at ASC");

                                                if ($replies && mysqli_num_rows($replies) > 0): ?>
                                                    <div style="padding:10px; border:1px solid #ccc; background:#f9f9f9; max-height:150px; overflow:auto;">
                                                        <?php while ($rep = mysqli_fetch_assoc($replies)): ?>
                                                            <p>
                                                                <strong><?= htmlspecialchars($rep['admin_name']) ?>:</strong><br>
                                                                <?= htmlspecialchars($rep['reply']) ?><br>
                                                                <small><em><?= date("F j, Y g:i A", strtotime($rep['created_at'])) ?></em></small>
                                                            </p>
                                                            <hr>
                                                        <?php endwhile; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <em>No replies yet</em>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6">No complaints submitted yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add-complaint" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="back/submit_complaint.php" method="POST" id="add_complaint_form" autocomplete="off">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel"><i class="fa-solid fa-house-circle-check"></i> Complaint</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <label>Complaint Type</label>
                            <select name="complaint_type" onchange="toggleRemarks(this)" class="form-control" required>
                                <option value="">-- Select Type --</option>
                                <option value="plumbing">Plumbing</option>
                                <option value="electrical">Electrical</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="others">Others</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label" style="font-weight: 500;">Remarks</label>
                            <textarea name="remarks" id="remarks" placeholder="Describe the issue..." class="form-control shadow-none" rows="1" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn text-secondary shadow-none" data-bs-dismiss="modal">CANCEL</button>
                    <button type="submit" class="btn custom-bg text-white shadow-none">SAVE</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- <div class="modal fade" id="add-complaint" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="back/submit_complaint.php" method="POST" id="add_complaint_form" autocomplete="off">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel"><i class="fa-solid fa-house-circle-check"></i> Complaint</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <label>Complaint Type</label>
                            <select name="complaint_type" onchange="toggleRemarks(this)" class="form-control" required>
                                <option value="">-- Select Type --</option>
                                <option value="plumbing">Plumbing</option>
                                <option value="electrical">Electrical</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="others">Others</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                         <label class="form-label" style="font-weight: 500;">Remarks</label>
                         <textarea name="remarks" id="remarks" placeholder="Describe the issue..." class="form-control shadow-none" rows="1" required ></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn text-secondary shadow-none" data-bs-dismiss="modal">CANCEL</button>
                    <button type="submit" name="submit_complaint" class="btn custom-bg text-white shadow-none">SAVE</button>
                </div>
            </div>
        </form>
    </div>
</div> -->

<div class="position-fixed top-0 start-50 translate-end-x p-3" style="z-index: 9999">
  <div id="toastSuccess" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        Complaint submitted successfully.
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>

  <div id="toastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        An error occurred while submitting your complaint.
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>



<?php
    include('includes/footer.php');
    include('includes/scripts.php');
?>

<script>

document.getElementById("add_complaint_form").addEventListener("submit", function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append("submit_complaint", "1"); 

    fetch("back/submit_complaint.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById("add-complaint"));
            modal.hide();

            // Reset form
            document.getElementById("add_complaint_form").reset();

            // Refresh only the complaints table body
            fetch("fetch_complaints.php")
            .then(res => res.text())
            .then(html => {
                document.querySelector("#complaint-table tbody").innerHTML = html;
            });

            // SweetAlert2 success
            Swal.fire({
                icon: "success",
                title: "Complaint Submitted",
                text: data.message,
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });

        } else {
            Swal.fire({
                icon: "error",
                title: "Submission Failed",
                text: data.message
            });
        }
    })
    .catch(err => {
        console.error("Error:", err);
        Swal.fire({
            icon: "error",
            title: "Server Error",
            text: "Something went wrong. Please try again later."
        });
    });
});

function toggleRemarks(select) {
    var remarksField = document.getElementById('remarks');
    if (select.value === 'others') {
        remarksField.setAttribute('required', true);
    } else {
        remarksField.removeAttribute('required');
    }
}
    
// document.getElementById("add_complaint_form").addEventListener("submit", function(e) {
//     e.preventDefault();

//     const formData = new FormData(this);

//     fetch("back/submit_complaint.php", {
//         method: "POST",
//         body: formData
//     })
//     .then(res => res.json())
//     .then(data => {
//         if (data.status === "success") {
//             // Close modal
//             const modal = bootstrap.Modal.getInstance(document.getElementById("add-complaint"));
//             modal.hide();

//             // Reset form
//             document.getElementById("add_complaint_form").reset();

//             //Refresh only the complaints table body
//             fetch("fetch_complaints.php")
//             .then(res => res.text())
//             .then(html => {
//                 document.querySelector("#complaint-table tbody").innerHTML = html;
//             });

//             // SweetAlert2 success
//             Swal.fire({
//                 icon: "success",
//                 title: "Complaint Submitted",
//                 text: data.message,
//                 timer: 5000,
//                 timerProgressBar: true,
//                 showConfirmButton: false
//             });

//         } else {
//             // SweetAlert2 error
//             Swal.fire({
//                 icon: "error",
//                 title: "Submission Failed",
//                 text: data.message
//             });
//         }
//     })
//     .catch(err => {
//         console.error("Error:", err);
//         Swal.fire({
//             icon: "error",
//             title: "Server Error",
//             timer: 5000,
//             timerProgressBar: true,
//             text: "Something went wrong. Please try again later."
//         });
//     });
// });

// function toggleRemarks(select) {
//     var remarksField = document.getElementById('remarks');
//     if (select.value === 'others') {
//         remarksField.setAttribute('required', true);
//     } else {
//         remarksField.removeAttribute('required');
//     }
// }
</script>



<!-- <script>

        document.getElementById("add_complaint_form").addEventListener("submit", function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch("back/submit_complaint.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                //  Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById("add-complaint"));
                modal.hide();

                // // Reset form
                document.getElementById("add_complaint_form").reset();

                // // // Refresh complaints table
                // fetch("complaint.php")
                // // .then(res => res.text())
                // .then(html => {
                //     document.querySelector("#complaint-table").innerHTML = html;
                // });

                // Show success toast
                const toast = new bootstrap.Toast(document.getElementById('toastSuccess'));
                toast.show();

            } else {
                // Show error toast
                document.querySelector("#toastError .toast-body").innerText = data.message;
                const toast = new bootstrap.Toast(document.getElementById('toastError'));
                toast.show();
            }
        })
        .catch(err => {
            console.error("Error:", err);
            const toast = new bootstrap.Toast(document.getElementById('toastError'));
            toast.show();
        });
    });
</script> -->


<!-- <script>
    document.getElementById("add_complaint_form").addEventListener("submit", function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch("", {
            method: "POST",
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error("Failed");
            return res.text();
        })
        .then(data => {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById("add-renter"));
            modal.hide();

            // Reset form
            document.getElementById("add_complaint_form").reset();

            // Refresh table
            fetch("fetch_complaints.php")
            .then(res => res.text())
            .then(html => {
                document.querySelector("#complaint-table tbody").innerHTML = html;
            });

            // Show success toast
            const toast = new bootstrap.Toast(document.getElementById('toastSuccess'));
            toast.show();
        })
        .catch(err => {
            console.error("Error:", err);

            // Show error toast
            const toast = new bootstrap.Toast(document.getElementById('toastError'));
            toast.show();
        });
    });
</script> -->





