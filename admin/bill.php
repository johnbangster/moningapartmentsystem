<?php
require('config/dbcon.php');
?>

<!-- HTML UI -->
 <form action="generate_full_bill.php" method="POST">
    <label>Renter</label>
    <select name="renter_id" id="renter-select" required>
        <option value="">-- Select Renter --</option>
        <?php
        $res = $con->query("SELECT id, CONCAT(first_name, ' ', last_name) AS full_name FROM renters");
        while ($r = $res->fetch_assoc()) {
            echo "<option value='{$r['id']}'>{$r['full_name']}</option>";
        }
        ?>
    </select>

    <div id="unit-info" style="margin-top: 10px; display: none;">
        <label>Unit</label>
        <input type="text" id="unit_name" class="form-control" readonly>

        <label>Monthly Rent</label>
        <input type="text" id="unit_price" class="form-control" readonly>

        <label>Lease Term (months)</label>
        <input type="text" id="lease_term" class="form-control" readonly>

        <label>Total Lease Amount</label>
        <input type="text" id="total_lease" class="form-control" readonly>

        <label>Due Date (same as Move-in)</label>
        <input type="date" id="due_date" class="form-control" readonly>

        
        <div class="mb-3">
            <label>Optional Add-ons (e.g. Internet, Water)</label>
            <div id="addonsContainer">
                <div class="row mb-2">
                    <div class="col">
                        <input type="text" name="addons[name][]" class="form-control" placeholder="Add-on name (e.g. Internet)" />
                    </div>
                    <div class="col">
                        <input type="number" name="addons[value][]" class="form-control" placeholder="Amount" step="0.01" />
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-secondary" onclick="addAddon()">Add More</button>
        </div>

    </div>

    <button type="submit">Generate Bill</button>
</form>



<script>
    document.getElementById('renter-select').addEventListener('change', function () {
        const renterId = this.value;
        if (!renterId) {
            document.getElementById('unit-info').style.display = 'none';
            return;
        }

        fetch('function/get_unit_info.php?renter_id=' + renterId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('unit-info').style.display = 'block';
                    document.getElementById('unit_name').value = data.unit_name;
                    document.getElementById('unit_price').value = data.price;
                    document.getElementById('lease_term').value = data.lease_term;
                    document.getElementById('total_lease').value = (data.price * data.lease_term).toFixed(2);
                    document.getElementById('due_date').value = data.move_in_date;
                } else {
                    document.getElementById('unit-info').style.display = 'none';
                    alert('Renter info not found.');
                }
            });
    });

    
    function addAddon() {
        const html = `
        <div class="row mb-2">
            <div class="col"><input type="text" name="addons[name][]" class="form-control" placeholder="Add-on name" /></div>
            <div class="col"><input type="number" name="addons[value][]" class="form-control" placeholder="Amount" step="0.01" /></div>
        </div>`;
        document.getElementById('addonsContainer').insertAdjacentHTML('beforeend', html);
    }

</script>




