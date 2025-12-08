<?php
session_start();
require('../admin/config/dbcon.php');
require('includes/header.php');

date_default_timezone_set(timezoneId: 'Asia/Manila');


// Logged-in renter ID
$user_id = (int)($_SESSION['auth_user']['user_id'] ?? 0);

// Map user_id -> renter_id
$renter_id = null;
$stmt = mysqli_prepare($con, "SELECT id FROM renters WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $renter_id);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$renter_id) die("No renter profile found.");

// Show only open/awaiting bills if ?open=1
$only_unpaid = isset($_GET['open']) ? true : false;

// Query bills
$query = "SELECT * FROM bills WHERE renter_id = ?";
$params = [$renter_id];
$types = "i";

if ($only_unpaid) {
    $query .= " AND status IN ('open','awaiting_confirmation')";
}

$query .= " ORDER BY due_date ASC";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Bills</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sandbox -->
    <!-- Replace YOUR_SANDBOX_CLIENT_ID with your sandbox client id -->
    <!-- <script src="https://www.paypal.com/sdk/js?client-id=Ab-W1WHCrsBe68cL4bNydaxSyqk4VpR88F_uZYB5J-S-CJJLGpy-3t88rYTXUco_U3NGgqplr0girCnE&currency=PHP"></script> -->
    <script src="https://www.paypal.com/sdk/js?client-id=AUnkUncPlt01Sw9zgVJLZB_lkdVB1DF2_1_Nz3xYOHSixwfev0wL061GoGMNqEEbg2Gle_dRJcCCOcJY&currency=PHP&disable-funding=card"></script>

    <!-- <script src="https://www.paypal.com/sdk/js?=sb&currency=PHP"></script> -->
</head>
<body class="container py-4">
<div class="container-fluid shadow p-4">
    <h2 class="mb-3">My Bills</h2>

    <!-- Filter -->
    <form method="get" class="mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="open" id="open" value="1" <?= $only_unpaid ? 'checked' : '' ?>>
            <label class="form-check-label" for="open">Show only Open / Awaiting bills / Partial</label>
        </div>
        <button type="submit" class="btn btn-primary mt-2">Apply Filter</button>
        <a href="bills.php" class="btn btn-secondary mt-2">Clear</a>
    </form>

    <!-- Bills Table -->
    <form id="billForm">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Reference</th>
                    <th>Billing Month</th>
                    <th>Due Date</th>
                    <th>Total Amount</th>
                    <th>Balance</th>
                    <th>Overpaid</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($bill = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <?php if ($bill['status'] == 'open' || $bill['status'] == 'awaiting_confirmation'): ?>
                                <input type="checkbox" class="bill-checkbox" name="bill_ids[]" data-amount="<?= $bill['balance'] > 0 ? $bill['balance'] : $bill['total_amount'] ?>" value="<?= $bill['id'] ?>">
                            <?php elseif ($bill['status'] == 'partial'): ?>
                                <input type="checkbox" class="bill-checkbox" name="bill_ids[]" data-amount="<?= $bill['balance'] ?>" value="<?= $bill['id'] ?>">
                            <?php else: ?> <!-- paid or others -->
                                <input type="checkbox" disabled>
                            <?php endif; ?>
                        </td>

                        <td><?= htmlspecialchars($bill['reference_id']) ?></td>
                        <td><?= htmlspecialchars(date('F', strtotime($bill['due_date']))) ?></td>
                        <td><?= htmlspecialchars(date('M-d-Y', strtotime($bill['due_date']))) ?></td>
                        <td><?= number_format($bill['total_amount'],2) ?></td>
                        <td><?= number_format($bill['balance'],2) ?></td>
                        <td><?= number_format($bill['carry_balance'],2) ?></td>

                        <td>
                            <?php if($bill['status']=='paid'): ?>
                                <span class="badge bg-success">Paid</span>
                            <?php elseif($bill['status']=='awaiting_confirmation'): ?>
                                <span class="badge bg-warning text-dark">Awaiting Confirmation</span>
                            <?php elseif($bill['status']=='open'): ?>
                                <span class="badge bg-danger">Open</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($bill['status']) ?></span>
                            <?php endif; ?>
                        </td>

                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">No bills found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- <h5>Total Selected Amount: ₱<span id="totalAmount">0.00</span></h5> -->
    </form>

    <!-- Payment Options -->
    <div class="payment-box">
        <h5>Payment Options</h5>
        <!-- <p id="totalAmountLabel">Total Selected Amount: ₱<span id="totalAmount">0.00</span></p> -->
        <h5>Total Selected Amount: ₱<span id="totalAmount">0.00</span></h5>

        <select id="paymentMethod">
            <option disabled selected>-- Select Payment Method --</option>
            <option value="cash">Cash Payment</option>
            <option value="paypal">PayPal</option>
        </select>

        <div id="paymentActions"></div>
    </div>
    <!-- <div class="my-4">
        <label for="paymentMethod" class="form-label fw-bold">Select Payment Method:</label>
        <select id="paymentMethod" class="form-select w-auto d-inline-block">
            <option value="" selected disabled>-- Choose Option --</option>
            <option value="cash">Cash</option>
            <option value="paypal">PayPal</option>
        </select>
        <div id="paymentActions" class="mt-3"  style="max-width: 400px; height: 40;"></div>
        <div id="paypal-button-container"></div>
    </div> -->
</div>



<!-- <script>

// Calculate total dynamically
const checkboxes = document.querySelectorAll('.bill-checkbox');
const totalDisplay = document.getElementById('totalAmount');

function updateTotal() {
    let total = 0;
    checkboxes.forEach(cb => {
        if(cb.checked) total += parseFloat(cb.dataset.amount);
    });
    totalDisplay.innerText = total.toFixed(2);
}
checkboxes.forEach(cb => cb.addEventListener('change', updateTotal));

// Payment option handler
const paymentSelect = document.getElementById('paymentMethod');
const paymentActions = document.getElementById('paymentActions');

paymentSelect.addEventListener('change', () => {
    paymentActions.innerHTML = '';
    if(paymentSelect.value === 'cash'){
        paymentActions.innerHTML = `<button id="cashPaymentBtn" class="btn btn-success">Confirm Cash Payment</button>`;
        attachCashHandler();
    }
    if(paymentSelect.value === 'paypal'){
        paymentActions.innerHTML = `<div id="paypal-button-container"></div>`;
        renderPayPalButton();
    }
});

// Cash Payment
function attachCashHandler(){
    document.getElementById('cashPaymentBtn').addEventListener('click', () => {
        const selected = [...document.querySelectorAll('.bill-checkbox:checked')];
        if(selected.length === 0){
            Swal.fire("Please select at least one bill.");
            return;
        }

        // Build bill IDs and amounts arrays
        const billIds = selected.map(cb => cb.value);
        const amounts = selected.map(cb => parseFloat(cb.dataset.amount));

        Swal.fire({
            title: "Confirm Cash Payment?",
            text: `You will settle ₱${amounts.reduce((a,b)=>a+b,0).toFixed(2)} at the office.`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes, Confirm"
        }).then(result => {
            if(result.isConfirmed){
                fetch('cash_to_pay.php',{
                    method:'POST',
                    headers:{ 'Content-Type':'application/json' },
                    body: JSON.stringify({ bill_ids: billIds, amounts: amounts })
                }).then(res=>res.json()).then(resp=>{
                    if(resp.success){
                        Swal.fire("Success!","Cash payment recorded.\nReference: "+resp.reference_number,"success")
                        .then(()=> location.reload());
                    }else{
                        Swal.fire("Error","Something went wrong.","error");
                    }
                });
            }
        });
    });
}

// PayPal Payment
function renderPayPalButton(){
    paypal.Buttons({
        style:{ layout:"vertical", color:"blue", shape:"rect", label:"pay" },
        createOrder: function(data, actions){
            const selected = [...document.querySelectorAll('.bill-checkbox:checked')];
            if(selected.length === 0){ Swal.fire("Please select at least one bill."); return; }http://localhost/moningsrental/admin/manage_complaint.php
            const total = selected.reduce((sum, cb)=> sum + parseFloat(cb.dataset.amount), 0);

            return actions.order.create({
                purchase_units:[{ amount:{ value: total.toFixed(2), currency_code:"PHP" } }]
            });
        },
        onApprove: function(data, actions){
            return actions.order.capture().then(function(details){
                const selected = [...document.querySelectorAll('.bill-checkbox:checked')];
                const billIds = selected.map(cb => cb.value);
                const amounts = selected.map(cb => parseFloat(cb.dataset.amount));
                const total = amounts.reduce((a,b)=>a+b,0);

                fetch('paypal_checkout.php',{
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({
                        bill_ids: billIds,
                        amounts: amounts,
                        payer_name: details.payer.name.given_name,
                        transaction_id: details.id,
                        total_amount: total
                    })
                }).then(res=>res.json()).then(resp=>{
                    if(resp.success){
                        Swal.fire("Success!","Payment successful.","success").then(()=> location.reload());
                    }else{
                        Swal.fire("Error","Something went wrong.","error");
                    }
                });
            });
        }
    }).render('#paypal-button-container');
}
</script> -->

<script>

    const checkboxes = document.querySelectorAll('.bill-checkbox');
    const totalDisplay = document.getElementById('totalAmount');
    const paymentSelect = document.getElementById('paymentMethod');
    const paymentActions = document.getElementById('paymentActions');

    // Update total amount whenever checkboxes change
    function updateTotal() {
        let total = 0;
        checkboxes.forEach(cb => { if(cb.checked) total += parseFloat(cb.dataset.amount); });
        totalDisplay.innerText = total.toFixed(2);
    }
    checkboxes.forEach(cb => cb.addEventListener('change', updateTotal));

    // Get selected bills as objects {id, amount}
    function getSelectedBills() {
        const selected = [...document.querySelectorAll('.bill-checkbox:checked')];
        return selected.map(cb => ({id: parseInt(cb.value), amount: parseFloat(cb.dataset.amount)}));
    }

    // Handle payment method selection
    paymentSelect.addEventListener('change', () => {
        paymentActions.innerHTML = '';
        if(paymentSelect.value === 'cash'){
            paymentActions.innerHTML = `<button id="cashPaymentBtn" class="btn btn-success">Confirm Cash Payment</button>`;
            attachCashHandler();
        } 
        else if(paymentSelect.value === 'paypal'){
            paymentActions.innerHTML = `<div id="paypal-button-container" style="max-width: 400px;"></div>`;
            renderPayPalButton();
        }
    });

    // Cash payment handler
    function attachCashHandler(){
        document.getElementById('cashPaymentBtn').addEventListener('click', () => {
            const bills = getSelectedBills();
            if(bills.length === 0){
                Swal.fire("Please select at least one bill.");
                return;
            }
            const total = bills.reduce((sum,b)=>sum+b.amount,0);
            Swal.fire({
                title: "Confirm Cash Payment?",
                text: `You will settle ₱${total.toFixed(2)} at the office.`,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Yes, Confirm"
            }).then(result => {
                if(result.isConfirmed){
                    fetch('cash_to_pay.php',{
                        method:'POST',
                        headers:{'Content-Type':'application/json'},
                        body: JSON.stringify({
                            bill_ids: bills.map(b=>b.id),
                            amounts: bills.map(b=>b.amount)
                        })
                    }).then(res=>res.json()).then(resp=>{
                        if(resp.success){
                            Swal.fire("Success!", "Cash payment recorded.\nReference: "+resp.reference_number, "success")
                                .then(()=> location.reload());
                        } else {
                            Swal.fire("Error", resp.message || "Something went wrong.", "error");
                        }
                    });
                }
            });
        });
    }

    // PayPal payment
    // function renderPayPalButton(){
    //     const bills = getSelectedBills();
    //     if(bills.length === 0){
    //         Swal.fire("Please select at least one bill.");
    //         return;
    //     }
    //     const total = bills.reduce((sum,b)=>sum+b.amount,0);

    //     if(typeof paypal === "undefined"){
    //         console.error("PayPal SDK not loaded!");
    //         Swal.fire("PayPal SDK not loaded! Check your client-id.");
    //         return;
    //     }
    //     paypal.Buttons({
    //         fundingSource: paypal.FUNDING.PAYPAL,
    //         style: { layout:'vertical', color:'blue', shape:'rect', label:'paypal', height: 40},
    //         createOrder: (data, actions) => {
    //             return actions.order.create({
    //                 purchase_units: [{
    //                     amount: { value: total.toFixed(2), currency_code: "PHP" }
    //                 }]
    //             });
    //         },

    //         onApprove: (data, actions) => {
    //             return actions.order.capture().then(details => {
    //                 fetch('paypal_checkout.php',{
    //                     method:'POST',
    //                     headers:{'Content-Type':'application/json'},
    //                     body: JSON.stringify({
    //                         bill_ids: bills.map(b=>b.id),
    //                         amounts: bills.map(b=>b.amount),
    //                         total_amount: total,
    //                         payer_name: details.payer.name.given_name + " " + details.payer.name.surname,
    //                         transaction_id: details.id
    //                     })
    //                 }).then(res => res.json()).then(resp => {
    //                     if(resp.success){
    //                         Swal.fire({
    //                             icon:'success',
    //                             title:'Payment Successful',
    //                             text:'PayPal payment recorded!',
    //                             timer:1500,
    //                             showConfirmButton:false
    //                         }).then(()=> location.reload());
    //                     } else {
    //                         Swal.fire({icon:'error', title:'Error', text: resp.message || "Payment failed"});
    //                     }
    //                 }).catch(err => {
    //                     console.error(err);
    //                     Swal.fire({icon:'error', title:'Error', text:'Could not record payment'});
    //                 });
    //             });
    //         },
    //         onError: (err) => {
    //             console.error(err);
    //             Swal.fire({icon:'error', title:'PayPal Error', text: err.toString()});
    //         }
    //     }).render('#paypal-button-container');
    // }

    function renderPayPalButton(){
    const bills = getSelectedBills();
    if(bills.length === 0){
        Swal.fire("Please select at least one bill.");
        return;
    }
    const total = bills.reduce((sum,b)=>sum+b.amount,0);

    paypal.Buttons({
        style: {
            layout: 'vertical',
            shape: 'rect',
            color: 'blue',     // You can use 'gold', 'blue', 'silver', 'black'
            label: 'paypal',
            tagline: false,    // Removes "Pay with PayPal" text
            height: 48         // Commercial size
        },
        funding: {
            disallowed: [ paypal.FUNDING.CARD ] // Hides "Debit/Credit Card"
        },
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: { value: total.toFixed(2), currency_code:"PHP" }
                }]
            });
        },
        // onApprove: function(data, actions) {
        //     return actions.order.capture().then(details => {
        //         Swal.fire({
        //             icon: 'success',
        //             title: 'Payment Successful',
        //             text: 'Thank you, ' + details.payer.name.given_name + '!',
        //             timer: 1500,
        //             showConfirmButton: false
        //         }).then(() => location.reload());
        //     });
        // }

        onApprove: (data, actions) => {
            return actions.order.capture().then(details => {
                fetch('paypal_checkout.php',{
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({
                        bill_ids: bills.map(b=>b.id),
                        amounts: bills.map(b=>b.amount),
                        total_amount: total,
                        payer_name: details.payer.name.given_name + " " + details.payer.name.surname,
                        transaction_id: details.id
                    })
                }).then(res => res.json()).then(resp => {
                    if(resp.success){
                        Swal.fire({
                            icon:'success',
                            title:'Payment Successful',
                            text:'PayPal payment recorded!',
                            timer:1500,
                            showConfirmButton:false
                        }).then(()=> location.reload());
                    } else {
                        Swal.fire({icon:'error', title:'Error', text: resp.message || "Payment failed"});
                    }
                }).catch(err => {
                    console.error(err);
                    Swal.fire({icon:'error', title:'Error', text:'Could not record payment'});
                });
            });
        },
        onError: (err) => {
            console.error(err);
            Swal.fire({icon:'error', title:'PayPal Error', text: err.toString()});
        }
    }).render('#paypal-button-container');
}
    
</script>


</body>
</html>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
