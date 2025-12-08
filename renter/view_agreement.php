<?php
session_start();
require ('../admin/config/dbcon.php');


if (!isset($_GET['agreement_id']) || !is_numeric($_GET['agreement_id'])) {
    echo "<div class='alert alert-danger'>Invalid agreement ID.</div>";
    exit;
}

$agreement_id = intval($_GET['agreement_id']);

if (isset($_GET['agreement_id']) && is_numeric($_GET['agreement_id'])) {
    $agreement_id = $_GET['agreement_id'];

    $query = "SELECT 
                ra.*, 
                r.first_name, r.last_name, r.middle_name, r.email, r.contacts, 
                u.name AS unit_name
              FROM rental_agreements ra
              JOIN renters r ON ra.renter_id = r.id
              JOIN units u ON ra.unit_id = u.id
              WHERE ra.id = $agreement_id";

    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $agreement = mysqli_fetch_assoc($result);
        // Display agreement details here
    } else {
        echo "Agreement not found.";
    }
} else {
    echo "Invalid agreement ID.";
}

$result = mysqli_query($con, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<div class='alert alert-warning'>Agreement not found.</div>";
    exit;
}

$agreement = mysqli_fetch_assoc($result);
$current_date = date('Y-m-d');

// Check expiration
$is_expired = ($current_date > $agreement['end_date']);


            

$data = mysqli_fetch_assoc($result);

?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="container-fluid px-4">
    <div class="card mt-4 shadow-sm">
        <div class="card-header">
            <h4 class="mb-0">Agreement</h4>
        </div>
        <div class="card-body">
            
                <div class="float-end">
                    <a href="renter.php" class="btn btn-secondary mt-3">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back
                    </a>
                </div>

                <div id="LeaseAgreement" class="container fluid mt-4" >
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0" style="text-align: center;">Rental Agreement Details</h5>
                        </div>
                        
                        <div class="card-body">

                            <?php if ($is_expired): ?>
                                <div class="alert alert-danger">
                                    <strong>Expired:</strong> This rental agreement expired on <strong><?= date("F j, Y", strtotime($agreement['end_date'])) ?></strong>.
                                </div>
                            <?php endif; ?>

                            <p><strong>Renter:</strong> <?= $agreement['first_name'] . ' ' . $agreement['middle_name'] . ' ' . $agreement['last_name'] ?></p>
                            <p><strong>Contact:</strong> <?= $agreement['contacts'] ?> | <?= $agreement['email'] ?></p>
                            <p><strong>Unit:</strong> <?= $agreement['unit_name'] ?></p>
                            <p><strong>Lease Term:</strong> <?= $agreement['term_months'] ?> months</p>
                            <p><strong>Start Date:</strong> <?= date("F j, Y", strtotime($agreement['start_date'])) ?></p>
                            <p><strong>End Date:</strong> <?= date("F j, Y", strtotime($agreement['end_date'])) ?></p>
                            <p><strong>Monthly Rent:</strong> ₱<?= number_format($agreement['monthly_rent'], 2) ?></p>
                            <p><strong>Deposit:</strong> ₱<?= number_format($agreement['deposit'], 2) ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?= 
                                    $agreement['status'] == 'accepted' ? 'success' : 
                                    ($agreement['status'] == 'rejected' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($agreement['status']) ?>
                                </span>
                            </p>
                            <hr>
                            <h6>Agreement Terms:</h6>
                            <p><?= nl2br($agreement['term_conditions']) ?></p>

                            <?php if (!empty($agreement['signature_path'])): ?>
                                <hr>
                                <h6>Signature:</h6>
                                <img src="<?= $agreement['signature_path'] ?>" alt="Signature" style="max-width: 300px;">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-end">
                    <buton class="btn btn-success btn-sm " onclick="LeaseAgreement()"><i class="fa-solid fa-print"></i> Print</buton>
                    <buton class="btn btn-info btn-sm mx-4" onclick="downloadPDF('<?= $agreement_id ?>')"><i class="fa-solid fa-download"></i> Download PDF</buton>
                </div>
        </div>
    </div>
</div>



       



<!-- <--for pdf vd link-->

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" integrity="sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/3.0.1/jspdf.umd.min.js" integrity="sha512-ad3j5/L4h648YM/KObaUfjCsZRBP9sAOmpjaT2BDx6u9aBrKFp7SbeHykruy83rxfmG42+5QqeL/ngcojglbJw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>




<script>
    function LeaseAgreement(){
        var divContents  = document.getElementById("LeaseAgreement").innerHTML;
        var a = window.open('','');
        a.document.write("<html><title>Moning's Rental Services</title>");
        a.document.write('<body style="font-family: fangsong;">');
        a.document.write(divContents);
        a.document.write('</body></html>');
        a.document.close();
        a.print();
    }

    window.jsPDF = window.jspdf.jsPDF;
    var docPDF = new jsPDF();

    function downloadPDF($agreement_id){

        var elementHTML = document.querySelector("#LeaseAgreement");
        docPDF.html(elementHTML,{
            callback: function() {
                docPDF.save($agreement_id+'.pdf');
            },
            x: 15,
            y: 15,
            width: 170,
            windowWidth: 650
        });

    }

</script>




<!-- <div class="container mt-4">
    <div class="card shadow-lg rounded-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fa-solid fa-file-contract me-2"></i>Rental Agreement Details</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label><strong>Renter Name:</strong></label>
                    <p><?= htmlspecialchars($data['first_name'] . ' ' . $data['middle_name'] . ' ' . $data['last_name']) ?></p>
                </div>
                <div class="col-md-6">
                    <label><strong>Email / Contact:</strong></label>
                    <p><?= htmlspecialchars($data['email']) ?> / <?= htmlspecialchars($data['contacts']) ?></p>
                </div>
                <div class="col-md-6">
                    <label><strong>Unit Name:</strong></label>
                    <p><?= htmlspecialchars($data['unit_name']) ?></p>
                </div>
                <div class="col-md-6">
                    <label><strong>Lease Term:</strong></label>
                    <p><?= $data['term_months'] ?> months</p>
                </div>
                <div class="col-md-6">
                    <label><strong>Start Date:</strong></label>
                    <p><?= date('F d, Y', strtotime($data['start_date'])) ?></p>
                </div>
                <div class="col-md-6">
                    <label><strong>End Date:</strong></label>
                    <p><?= date('F d, Y', strtotime($data['end_date'])) ?></p>
                </div>
                <div class="col-md-6">
                    <label><strong>Monthly Rent:</strong></label>
                    <p>₱<?= number_format($data['monthly_rent'], 2) ?></p>
                </div>
                <div class="col-md-6">
                    <label><strong>Deposit:</strong></label>
                    <p>₱<?= number_format($data['deposit'], 2) ?></p>
                </div>
                <div class="col-md-6">
                    <label><strong>Status:</strong></label>
                    <span class="badge bg-<?= 
                        $data['status'] == 'accepted' ? 'success' : 
                        ($data['status'] == 'rejected' ? 'danger' : 'secondary') ?>">
                        <?= ucfirst($data['status']) ?>
                    </span>
                </div>
            </div>

            <div class="mb-4">
                <label><strong>Agreement Terms & Conditions:</strong></label>
                <div class="border p-3 bg-light rounded">
                    <?= nl2br(htmlspecialchars($data['term_conditions'])) ?>
                </div>
            </div>

            <?php if (!empty($data['signature_path'])): ?>
                <div class="mb-4">
                    <label><strong>Renter Signature:</strong></label><br>
                    <img src="<?= htmlspecialchars($data['signature_path']) ?>" alt="Signature" style="max-width: 300px; border: 1px solid #ccc;">
                </div>
            <?php endif; ?>

            <a href="renter.php" class="btn btn-secondary mt-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Agreements
            </a>
        </div>
    </div>
</div> -->

<!-- <div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>Rental Agreement Details</h4>
        </div>
        <div class="card-body">
            <h5 class="mb-3">Renter Information</h5>
            <p><strong>Name:</strong> <?= $data['first_name'] . " " . $data['middle_name'] . " " . $data['last_name'] ?></p>
            <p><strong>Contact:</strong> <?= $data['contacts'] ?></p>
            <p><strong>Email:</strong> <?= $data['email'] ?></p>

            <h5 class="mt-4 mb-3">Unit Information</h5>
            
            <p><strong>Unit Name:</strong> <?= $data['unit_name'] ?> 

            <h5 class="mt-4 mb-3">Agreement Details</h5>
            <p><strong>Lease Term:</strong> <?= $data['term_months'] ?> months</p>
            <p><strong>Start Date:</strong> <?= $data['start_date'] ?></p>
            <p><strong>End Date:</strong> <?= $data['end_date'] ?></p>
            <p><strong>Monthly Rent:</strong> ₱<?= number_format($data['monthly_rent'], 2) ?></p>
            <p><strong>Deposit:</strong> ₱<?= number_format($data['deposit'], 2) ?></p>
            <p><strong>Status:</strong> <span class="badge bg-<?= 
                $data['status'] == 'accepted' ? 'success' : 
                ($data['status'] == 'pending' ? 'warning' : 'danger') ?>">
                <?= ucfirst($data['status']) ?></span></p>

            <h5 class="mt-4 mb-3">Terms and Conditions</h5>
            <div class="border p-3 bg-light">
                <?= nl2br(htmlspecialchars($data['term_conditions'])) ?>
            </div>

            <?php if ($data['signature_path']): ?>
                <h5 class="mt-4">Digital Signature</h5>
                <img src="<?= $data['signature_path'] ?>" alt="Signature" style="max-width: 300px;">
            <?php endif; ?>

            <div class="mt-4">
                <a href="generate_agreement_pdf.php?agreement_id=<?= $agreement_id ?>" class="btn btn-secondary">
                    <i class="fa-solid fa-download"></i> Download PDF
                </a>
                <a href="javascript:window.print()" class="btn btn-outline-primary">
                    <i class="fa-solid fa-print"></i> Print
                </a>
            </div>
        </div>
    </div>
</div> -->

<?php require('includes/footer.php'); ?>



<!-- (<?= ucfirst($data['unit_type']) ?>)</p> -->

<!-- generate_agreement_pdf.php?agreement_id=
  ?= $agreement_id -->