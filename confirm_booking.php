<?php
require 'function_booking.php';
require 'admin/config/dbcon.php';

cleanup_expired_cash_reservations($con);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <!-- <script src="https://www.paypal.com/sdk/js?client-id=AUnkUncPlt01Sw9zgVJLZB_lkdVB1DF2_1_Nz3xYOHSixwfev0wL061GoGMNqEEbg2Gle_dRJcCCOcJY&currency=PHP&disable-funding=card"></script> -->
    <!-- <script src="https://www.paypal.com/sdk/js?client-id=Ab-W1WHCrsBe68cL4bNydaxSyqk4VpR88F_uZYB5J-S-CJJLGpy-3t88rYTXUco_U3NGgqplr0girCnE&currency=PHP&disable-funding=card"></script> -->

    <script src="https://www.paypal.com/sdk/js?client-id=AUnkUncPlt01Sw9zgVJLZB_lkdVB1DF2_1_Nz3xYOHSixwfev0wL061GoGMNqEEbg2Gle_dRJcCCOcJY&currency=PHP&disable-funding=card"></script>

    <?php require('includes/links.php'); ?>
    <title><?php echo $settings_r['site_title']; ?> - Confirm Booking</title>
</head>
<body class="bg-light">

<?php
// Redirect if no ID or shutdown mode
if(!isset($_GET['id']) || $settings_r['shutdown'] == true){
    header("Location: units.php");
    exit;
}

$data = filteration($_GET);

// Fetch unit only if available
// $unit_res = select("SELECT * FROM `units` WHERE `id`=? AND `status`=? AND `removed`=?", [$data['id'], 'Available', 0], 'isi');
$unit_res = select(
    "SELECT * FROM `units` WHERE `id`=? AND `removed`=? AND (`status`='Available' OR `status`='Reserved')",
    [$data['id'], 0],
    'ii'
);

if(mysqli_num_rows($unit_res) == 0){
    header("Location: units.php"); // Unit not available
    exit;
}

if(mysqli_num_rows($unit_res) == 0){
    header("Location: units.php"); // Unit not available
    exit;
}

$unit_data = mysqli_fetch_assoc($unit_res);

$_SESSION['unit'] = [
    "id" => $unit_data['id'],
    "name" => $unit_data['name'],
    "price" => $unit_data['price'],
    "payment" => null,
    "available" => true,
];

$formatted_price = number_format($unit_data['price'], 2);

// Fetch thumbnail
$unit_thumb = UNITS_IMG_PATH . "Thumbnails.jpg"; // default
$thumb_q = mysqli_query($con, "SELECT * FROM `unit_images` WHERE `unit_id`='{$unit_data['id']}' AND `thumb`='1'");
if(mysqli_num_rows($thumb_q) > 0){
    $thumb_res = mysqli_fetch_assoc($thumb_q);
    $unit_thumb = UNITS_IMG_PATH . $thumb_res['image'];
}
?>

<div class="container">
    <div class="row">
        <!-- Page header -->
        <div class="col-12 my-5 mb-4 px-4">
            <h2 class="fw-bold">CONFIRM BOOKING</h2>
            <div style="font-size: 14px;">
                <a href="index.php" class="text-secondary text-decoration-none">HOME</a>
                <span class="text-secondary"> > </span>
                <a href="units.php" class="text-secondary text-decoration-none">UNITS</a>
            </div>
        </div>

        <!-- Unit Info -->
        <div class="col-lg-7 col-md-12 px-4">
            <div class="cardd p-3 shadow-sm rounded">
                <img src="<?= $unit_thumb ?>" class="img-fluid rounded mb-3">
                <h5><?= $unit_data['name'] ?></h5>
                <h5>₱ <?= $formatted_price ?> per month</h5>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="col-lg-5 col-md-12 px-4">
            <div class="card mb-4 border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <form id="booking_form">
                        <input type="hidden" name="unit_id" value="<?= $unit_data['id'] ?>">
                        <input type="hidden" name="payment_type" value="full_payment">
                        <input type="hidden" name="unit_name" value="<?= $unit_data['name'] ?>">
                        <input type="hidden" name="amount" value="<?= $unit_data['price'] ?>">

                        <h6 class="mb-3">BOOKING DETAILS</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input name="fname" type="text" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input name="lname" type="text" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input name="email" type="email" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone number</label>
                                <input name="contact" type="text" id="contact" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Move-in</label>
                                <input id="movInDate" name="move_in" type="date" class="form-control shadow-none" required>
                            </div>

                            <!-- Payment Options -->
                            <div class="payment-box mt-4">
                                <h5>Payment Options</h5>
                                <h5>Amount to pay ₱ <?= $formatted_price ?></h5>
                                <label><input type="checkbox" id="cashCheckbox" name="payment" value="cash"> Cash</label>
                                <label><input type="checkbox" id="paypalCheckbox" name="payment" value="paypal"> PayPal</label>
                                <label><input type="checkbox" id="gcashCheckbox" name="payment" value="gcash"> G-Cash</label>
                                <div id="paymentActions" class="mt-3"></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require('includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<!-- PayPal SDK -->
<!-- <script src="https://www.paypal.com/sdk/js?client-id=AUnkUncPlt01Sw9zgVJLZB_lkdVB1DF2_1_Nz3xYOHSixwfev0wL061GoGMNqEEbg2Gle_dRJcCCOcJY&currency=PHP&disable-funding=card"></script> -->
<!-- <script src="https://www.paypal.com/sdk/js?client-id=AUnkUncPlt01Sw9zgVJLZB_lkdVB1DF2_1_Nz3xYOHSixwfev0wL061GoGMNqEEbg2Gle_dRJcCCOcJY&currency=PHP&disable-funding=card"></script> -->


<script>
const cashCheckbox = document.getElementById('cashCheckbox');
const paypalCheckbox = document.getElementById('paypalCheckbox');
const gcashCheckbox = document.getElementById('gcashCheckbox');
const paymentActions = document.getElementById('paymentActions');
const startDateInput = document.getElementById('movInDate');
const today = new Date().toISOString().split('T')[0];
const form = document.getElementById('booking_form'); //reference form

startDateInput.min = today;

let reservationId = null;
let amountToPay = <?= $unit_data['price'] ?>;

// Only one checkbox at a time
// cashCheckbox.addEventListener('change', () => { if(cashCheckbox.checked) { paypalCheckbox.checked=false; gcashCheckbox.checked=false; } updatePaymentOptions(); });
// paypalCheckbox.addEventListener('change', () => { if(paypalCheckbox.checked) { cashCheckbox.checked=false; gcashCheckbox.checked=false; } updatePaymentOptions(); });
// gcashCheckbox.addEventListener('change', () => { if(gcashCheckbox.checked) { cashCheckbox.checked=false; paypalCheckbox.checked=false; } updatePaymentOptions(); });

// One checkbox only
[cashCheckbox, paypalCheckbox, gcashCheckbox].forEach(cb => {
    cb.addEventListener('change', () => {
        [cashCheckbox, paypalCheckbox, gcashCheckbox].forEach(o => { if(o !== cb) o.checked = false; });
        updatePaymentOptions();
    });
});


// Update payment options UI

function updatePaymentOptions(){
    paymentActions.innerHTML = '';

    if(cashCheckbox.checked){
        paymentActions.innerHTML = `<button id="cashPaymentBtn" class="btn btn-success col-md-12 mt-4">Confirm Cash Payment</button>`;
        attachCashHandler();
    } 
    else if(paypalCheckbox.checked){
        // Only render PayPal once
        if(!paypalRendered){
            paymentActions.innerHTML = `<div id="paypal-button-container" style="max-width:400px;"></div>`;
            renderPayPalButton();
            paypalRendered = true;
        }
    } 
    else if(gcashCheckbox.checked){
        paymentActions.innerHTML = `<button id="gcashPayBtn" class="btn btn-primary col-md-12 mt-4">Pay using GCash</button>`;
        attachGcashHandler();
    }
}

// function updatePaymentOptions(){
//     paymentActions.innerHTML = '';
//     if(cashCheckbox.checked){
//         paymentActions.innerHTML = `<button id="cashPaymentBtn" class="btn btn-success col-md-12 mt-4">Confirm Cash Payment</button>`;
//         attachCashHandler();
//     } else if(paypalCheckbox.checked){
//         paymentActions.innerHTML = `<div id="paypal-button-container" style="max-width:400px;"></div>`;
//         renderPayPalButton();
//     } else if(gcashCheckbox.checked){
//         paymentActions.innerHTML = `<button id="gcashPayBtn" class="btn btn-primary col-md-12 mt-4">Pay using GCash</button>`;
//         attachGcashHandler();
//     }
// }

// fixed validation
function validateForm(){
    const required = ['fname','lname','email','contact','move_in'];
    for(const name of required){
        if(!form[name].value.trim()){
            Swal.fire("Incomplete Form", "Please fill out all required fields first!", "warning");
            return false;
        }
    }
    return true;
}


let paypalRendered = false;

function updatePaymentOptions(){
    paymentActions.innerHTML = '';

    if(cashCheckbox.checked){
        paymentActions.innerHTML = `<button id="cashPaymentBtn" class="btn btn-success col-md-12 mt-4">Confirm Cash Payment</button>`;
        attachCashHandler();
    } else if(paypalCheckbox.checked){
        // Add container dynamically
        paymentActions.innerHTML = `<div id="paypal-button-container" style="max-width:400px;"></div>`;
        renderPayPalButton();
    } else if(gcashCheckbox.checked){
        paymentActions.innerHTML = `<button id="gcashPayBtn" class="btn btn-primary col-md-12 mt-4">Pay using GCash</button>`;
        attachGcashHandler();
    }
}

function validateForm() {
    const required = ['fname','lname','email','contact','move_in'];
    for (const name of required) {
        if (!form[name].value.trim()) {
            Swal.fire("Incomplete Form", "Please fill out all required fields first!", "warning");
            return false;
        }
    }
    return true;
}


// Render PayPal button dynamically
paypalCheckbox.addEventListener('change', () => {
    if (paypalCheckbox.checked) {
        paymentActions.innerHTML = `<div id="paypal-button-container" style="max-width:400px;"></div>`;
        renderPayPalButton();
    } else {
        paymentActions.innerHTML = '';
    }
});

paypalCheckbox.addEventListener('change', () => {
    if (paypalCheckbox.checked) {
        paymentActions.innerHTML = `<div id="paypal-button-container" style="max-width:400px;"></div>`;
        renderPayPalButton();
    } else {
        paymentActions.innerHTML = '';
    }
});

function renderPayPalButton() {
    paypal.Buttons({
        style: { color: 'blue', shape: 'pill', label: 'paypal' },

        // Create order
        createOrder: async function(data, actions) {
            if (!validateForm()) return;

            let resData;
            try {
                // Save reservation first
                const res = await fetch('save_reservation.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        unit_id: form.unit_id.value,
                        fname: form.fname.value,
                        lname: form.lname.value,
                        email: form.email.value,
                        contact: form.contact.value,
                        move_in: form.move_in.value,
                        payment_type: 'full_payment',
                        amount: amountToPay
                    })
                });

                // Handle response text
                const textResponse = await res.text();  // Read the raw response body first

                // Check if the response was successful
                if (!res.ok) {
                    throw new Error(`Server returned status ${res.status}: ${textResponse}`);
                }

                // Now try parsing it as JSON
                try {
                    resData = JSON.parse(textResponse);
                } catch (parseErr) {
                    console.error('Invalid JSON from server:', textResponse);
                    throw new Error("Server returned invalid JSON");
                }

            } catch(err) {
                Swal.fire("Server Error", "Failed to create reservation. Please try again.", "error");
                console.error(err);
                throw err; // stop PayPal flow
            }

            if (!resData.success) {
                Swal.fire("Error", resData.message || "Failed to create reservation.", "error");
                throw new Error(resData.message);
            }

            // Store reservation ID for payment update
            reservationId = resData.reservation_id;

            // Create PayPal order
            return actions.order.create({
                purchase_units: [{
                    amount: { value: amountToPay.toFixed(2) },
                    description: "Unit " + form.unit_name.value
                }]
            });
        },

        // Capture payment after approval
        onApprove: async function(data, actions) {
            try {
                const details = await actions.order.capture();

                // Update reservation payment
                const res = await fetch('update_reservation.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        reservation_id: reservationId,
                        payer_name: details.payer.name.given_name + ' ' + details.payer.name.surname,
                        payer_email: details.payer.email_address,
                        payment_status: 'paid',
                        payment_method: 'paypal',
                        transaction_id: details.id,
                        amount_paid: amountToPay
                    })
                });

                // Handle response text
                const textResponse = await res.text(); // Read the raw response body first

                // Parse JSON
                let updateData;
                try {
                    updateData = JSON.parse(textResponse);
                } catch (parseErr) {
                    console.error('Invalid JSON from update_reservation_payment.php:', textResponse);
                    throw new Error("Failed to parse server response.");
                }

                if (updateData.success) {
                    Swal.fire("Payment Successful", "Your booking has been confirmed!", "success")
                        .then(() => window.location.href = 'booking_success.php?id=' + reservationId);
                } else {
                    Swal.fire("Error", updateData.message || "Payment succeeded but reservation update failed.", "error");
                }

            } catch(err) {
                console.error(err);
                Swal.fire("Payment Error", "Something went wrong: " + err.message, "error");
            }
        },

        onError: function(err) {
            console.error(err);
            Swal.fire("Payment Error", "PayPal error: " + err.message || err, "error");
        }

    }).render('#paypal-button-container');
}

//GCash Handler fixed
function attachGcashHandler(){
    const btn = document.getElementById('gcashPayBtn');
    if(!btn) return;

    btn.addEventListener('click', (e)=>{
        e.preventDefault();
        if(!validateForm()) return;

        const formData = new FormData(form);

        fetch('gcash_booking.php', {method:'POST', body: formData})
        .then(res => res.json())
        .then(data => {
            if(data.success){
                Swal.fire({
                    title:"Proceed to GCash?",
                    text:"You’ll be redirected to GCash checkout.",
                    icon:"question",
                    showCancelButton:true,
                    confirmButtonText:"Proceed"
                }).then(result => {
                    if(result.isConfirmed){
                        fetch('gcash_create_payment.php',{method:'POST',body:formData})
                        .then(res=>res.json())
                        .then(pay=>{
                            if(pay.success && pay.checkout_url){
                                window.location.href=pay.checkout_url;
                            } else {
                                Swal.fire("Error", pay.message || "GCash error", "error");
                            }
                        })
                        .catch(()=>Swal.fire("Error","Network error","error"));
                    }
                });
            } else {
                Swal.fire("Error", data.message, "error");
            }
        })
        .catch(()=>Swal.fire("Error","Server error","error"));
    });
}

function attachCashHandler() {
    const btn = document.getElementById('cashPaymentBtn');
    if (!btn) return;

    // Remove old listeners
    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);

    newBtn.addEventListener('click', (e) => {
        e.preventDefault(); // prevent default form submission

        const form = document.getElementById('booking_form');

        // Validate required fields
        const fname = form.fname.value.trim();
        const lname = form.lname.value.trim();
        const email = form.email.value.trim();
        const contact = form.contact.value.trim();
        const move_in = form.move_in.value.trim();

        if (!fname || !lname || !email || !contact || !move_in) {
            Swal.fire("Incomplete Form", "Please fill out all required fields before reserving.", "warning");
            return;
        }

        const formData = new FormData(form);

        // SweetAlert confirmation
        Swal.fire({
            title: "Confirm Cash Reservation?",
            text: "You have 3 days to pay in cash. After 3 days, reservation expires automatically.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes, Reserve"
        }).then(result => {
            if(result.isConfirmed){
                fetch('book_cash.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.success){
                        Swal.fire("Success", data.message, "success")
                        .then(() => window.location.href = "thank_you.php");
                    } else {
                        Swal.fire("Error", data.message, "error")
                        .then(() => window.location.href = "units.php");
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire("Error", "Server error.", "error");
                });
            }
        });
    });
}





</script>

</body>
</html>
