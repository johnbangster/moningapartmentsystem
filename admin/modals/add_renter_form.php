<div class="modal fade" id="addRenterModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="addRenterForm" method="POST" action="functions/insert_renter.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Renter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3"><label>First name</label><input name="first_name" class="form-control" required></div>
        <div class="mb-3"><label>Last name</label><input name="last_name" class="form-control" required></div>
        <div class="mb-3"><label>Contact (11 digits)</label><input name="contacts" class="form-control" required></div>
        <div class="mb-3"><label>Email</label><input name="email" type="email" class="form-control" required></div>
        <div class="mb-3"><label>Unit ID</label><input name="unit_id" class="form-control" required></div>
        <div class="mb-3"><label>Move-in date</label><input name="move_in_date" type="date" class="form-control" required></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" type="submit">Add Renter</button>
      </div>
    </form>
  </div>
</div>