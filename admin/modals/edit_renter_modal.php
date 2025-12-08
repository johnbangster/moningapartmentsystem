
<div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="function/update_renter.php" method="POST">
      <input type="hidden" name="renter_id" value="<?= $row['id'] ?>">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Renter</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">First Name</label>
              <input type="text" name="fname" class="form-control" value="<?= $row['first_name'] ?>" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Last Name</label>
              <input type="text" name="lname" class="form-control" value="<?= $row['last_name'] ?>" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">M.I</label>
              <input type="text" name="middle_name" class="form-control" value="<?= $row['middle_name'] ?>">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Contact</label>
              <input type="text" name="contact" class="form-control" value="<?= $row['contacts'] ?>" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" value="<?= $row['email'] ?>" required>
            </div>

            <div class="col-12 mb-3">
              <label class="form-label">Address</label>
              <textarea name="address" class="form-control" required><?= $row['address'] ?></textarea>
            </div>

            <!-- STATUS BADGE -->
            <div class="col-md-6 mb-3">
              <label class="form-label">Status</label><br>
              <?php 
                $status = trim($row['status']);
                $badgeClass = ($status === 'Active') ? 'bg-success' : 'bg-danger';
              ?>
              <span id="statusBadge<?= $row['id'] ?>" 
                    class="badge <?= $badgeClass ?> px-3 py-2 text-uppercase">
                <?= htmlspecialchars($row['status']) ?>
              </span>
              <input type="hidden" id="statusInput<?= $row['id'] ?>" name="status" value="<?= htmlspecialchars($row['status']) ?>">
            </div>
          </div>
        </div>

         <!-- Unit -->
        <div class="col-md-6 mb-2">
            <label class="form-label fw-bold">House Name</label>
            <select name="unit_id" class="form-control shadow-none" required>
                <option selected disabled>-- Select Unit --</option>
                <?php
                // Select units with status 'available' and not removed
                $unitRes = mysqli_query($con, "SELECT id, name FROM units WHERE status = 'available' AND removed = 0");
                while ($unit = mysqli_fetch_assoc($unitRes)) {
                    $selected = ($unit['id'] == $row['unit_id']) ? 'selected' : '';
                    echo '<option value="' . $unit['id'] . '" ' . $selected . '>' . $unit['name'] . '</option>';
                }
                ?>
             </select>
        </div>

        <div class="modal-footer d-flex justify-content-between">
          <div>
            <!-- BOTH BUTTONS ALWAYS SHOWN -->
            <button type="button" 
                    onclick="activateRenter(<?= $row['id'] ?>)" 
                    class="btn btn-success me-2">
              <i class="bi bi-person-check"></i> Activate
            </button>
            <button type="button" 
                    onclick="deActivateRenter(<?= $row['id'] ?>)" 
                    class="btn btn-danger">
              <i class="bi bi-person-dash"></i> De-Activate
            </button>
          </div>

          <div>
            <button type="submit" name="update_renter" class="btn btn-primary">
              <i class="bi bi-save"></i> Save Changes
            </button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-circle"></i> Cancel
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
//Activate renter
function activateRenter(renterId) {
  Swal.fire({
    title: "Activate renter?",
    text: "This renter will be marked as 'Active'.",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#28a745",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Yes, activate"
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "function/reactivate_renter.php",
        type: "POST",
        data: { id: renterId },
        dataType: "json", //ensures response is parsed as JSON
        success: function(data) {
          if (data.success) {
            Swal.fire({
              icon: "success",
              title: "Activated!",
              text: data.message,
              timer: 1500,
              showConfirmButton: false
            });

            // Update badge instantly
            const badge = $("#statusBadge" + renterId);
            badge.removeClass("bg-danger").addClass("bg-success").text("Active");
            $("#statusInput" + renterId).val("Active");

          } else {
            Swal.fire("Error!", data.message, "error");
          }
        },
        error: function(xhr, status, error) {
          console.error(xhr.responseText);
          Swal.fire("Server Error!", "Invalid JSON or server issue.", "error");
        }
      });
    }
  });
}

//Deactivate renter
function deActivateRenter(renterId) {
  Swal.fire({
    title: "Deactivate renter?",
    text: "This renter will be marked as 'De-Activate'.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Yes, deactivate"
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "function/deactivate_renter.php",
        type: "POST",
        data: { id: renterId },
        dataType: "json", //ensures response is parsed as JSON
        success: function(data) {
          if (data.success) {
            Swal.fire({
              icon: "success",
              title: "Deactivated!",
              text: data.message,
              timer: 1500,
              showConfirmButton: false
            });

            // Update badge instantly
            const badge = $("#statusBadge" + renterId);
            badge.removeClass("bg-success").addClass("bg-danger").text("De-Activate");
            $("#statusInput" + renterId).val("De-Activate");

          } else {
            Swal.fire("Error!", data.message, "error");
          }
        },
        error: function(xhr, status, error) {
          console.error(xhr.responseText);
          Swal.fire("Server Error!", "Invalid JSON or server issue.", "error");
        }
      });
    }
  });
}
</script>

