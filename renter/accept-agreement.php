<?php
session_start();
require('../admin/config/dbcon.php');

if (!isset($_SESSION['auth_user']['renter_id'])) {
    header("Location: ../login.php");
    exit();
}

$renter_id = intval($_SESSION['auth_user']['renter_id']);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Agreement ID is required.");
}
$agreement_id = intval($_GET['id']);

$sql = "
    SELECT  
        a.*,  
        u.name AS unit_name,
        CONCAT(r.first_name, ' ', r.last_name) AS renter_fullname,
        r.address AS renter_address,
        b.name AS branch_name,
        b.address AS branch_address,
        bl.due_date AS next_due_date
    FROM rental_agreements a
    JOIN units u ON u.id = a.unit_id
    JOIN renters r ON r.id = a.renter_id
    LEFT JOIN branch b ON b.id = u.branch_id
    LEFT JOIN bills bl 
           ON bl.renter_id = a.renter_id 
          AND bl.unit_id = a.unit_id
          AND bl.status = 'open'
    WHERE a.id = ? AND a.renter_id = ?
    ORDER BY bl.due_date ASC
    LIMIT 1
";

$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $agreement_id, $renter_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Agreement not found.");
}

$agreement = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept'])) {
    $update_sql = "
        UPDATE rental_agreements 
        SET status = 'accepted' 
        WHERE id = ? AND renter_id = ?
    ";
    $update_stmt = $con->prepare($update_sql);
    $update_stmt->bind_param("ii", $agreement_id, $renter_id);
    if ($update_stmt->execute()) {
        header("Location: my_agreement.php?accepted=1");
        exit();
    } else {
        echo "Error updating agreement: " . $con->error;
    }
    $update_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lease Agreement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
       /* GENERAL STYLES */
        body { 
            background-color: #f4f6f9; 
            font-family: Arial, sans-serif;
            font-size: 15px;
            line-height: 1.4;
            color: #333;
        }

        .container { max-width: 900px; }

        .card { border-radius: 6px; }

        .agreement-header {
            padding: 20px 25px;
            border-bottom: 1px solid #ddd;
        }

        .agreement-header h4 {
            font-weight: 600;
        }

        /* ON-SCREEN HEADER */
        .on-screen-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .brand-logo { 
            width: 40px; 
            height: 40px; 
            object-fit: contain; 
            margin-bottom: 10px; 
        }

        .agreement-body {
            background: #fff;
            padding: 40px;
            border-radius: 0 0 6px 6px;
            font-family: "Courier New", Courier, monospace; /* typewriter font */
        }

        .agreement-summary p,
        .terms-box p,
        .agreement-body p {
            margin-bottom: 10px;
        }

        .terms-box { 
            margin-top: 10px; 
        }

        .page-break { page-break-before: always; }

        .badge { font-size: 0.9rem; }

        .card-footer { padding: 15px 25px; }

        /* PRINT STYLES */
        @media print {
            body * { visibility: hidden; }
            #agreementPrint, #agreementPrint * { visibility: visible; }
            #agreementPrint { 
                position: absolute; 
                left:0; 
                top:0; 
                width:100%; 
                padding: 25px; 
                font-family: "Courier New", Courier, monospace; /* typewriter font */
                font-size: 14px;
                line-height: 1.3; 
                color: #000;
            }

            /* Hide on-screen elements */
            .on-screen-header, .card-footer, .btn { display: none !important; }

            /* Logo smaller for print */
            .brand-logo { 
                width: 25px !important; 
                height: 25px !important; 
            }

            .page-break { page-break-before: always !important; }
        }

        /* PRINT HEADER (for popup print & PDF) */
        .print-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .print-header img.brand-logo {
            width: 25px;
            height: 25px;
            object-fit: contain;
            margin-bottom: 8px;
        }
        .print-header h5 {
            margin: 2px 0;
        }
        .print-header hr {
            width: 40%;
            margin: 10px auto;
            border: 0;
            border-top: 1px solid #777;
        }

        /* Force smaller logo in print */
        @media print {
            #agreementPrint .print-logo  {
                width: 25px !important;
                height: 25px !important;
                object-fit: contain;
            }
        }

    </style>
</head>
<body>
<div class="container mt-5 mb-5">
    <div class="card shadow border-0">
        <div class="agreement-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0"><i class="fa-solid fa-file-contract me-2"></i>CONTRACT OF LEASE</h4>
                <small><?= htmlspecialchars($agreement['unit_name']) ?></small>
            </div>
            <div>
                <button onclick="agreementPrint()" class="btn btn-light btn-sm me-2">
                    <i class="fa-solid fa-print me-1"></i> Print
                </button>
                <!-- <button onclick="downloadPDF(<?= $agreement_id ?>)" class="btn btn-warning btn-sm">
                    <i class="fa-solid fa-file-pdf me-1"></i> Download PDF
                </button> -->
                <a href="download_agreement.php?id=<?= $agreement_id ?>" class="btn btn-warning btn-sm">
                    <i class="fa-solid fa-file-pdf me-1"></i> Download PDF
                </a>

            </div>
        </div>

        <div class="agreement-body" id="agreementPrint">
            <div class="text-center mb-4 on-screen-header">
                <img src="../images/logo.png" class="brand-logo mb-2">
                <h5 class="fw-bold">Moning's Rental Services</h5>
                <h5>Address: 1438-B M.J.Cuenco Ave, Brgy Mabolo, Cebu City</h5>
                <h2 class="text-muted mb-0">CONTRACT OF LEASE</h2>
                <hr class="w-25 mx-auto">
            </div>

            <div class="agreement-summary">
                <p>This Contract of Lease is made and executed by and between:</p>
                <p>LESSOR: <b>OWNER'S NAME</b>, of legal age, Filipino, with address at 1438-B M.J.Cuenco Avenue Cebu City (hereinafter referred to as the 'Lessor');</p>
                <p>LESSEE: <b><?= htmlspecialchars($agreement['renter_fullname']) ?></b>, of legal age, Filipino, with address at <b><?= htmlspecialchars($agreement['renter_address']) ?></b> (hereinafter referred to as the 'Lessee');</p>
                <p>WITNESSETH: That the Lessor hereby agrees to lease unto the Lessee and the Lessee hereby agrees to lease from the Lessor the premises <b><?= htmlspecialchars($agreement['unit_name']) ?> / <?= htmlspecialchars($agreement['branch_name']) ?></b> located at <b><?= htmlspecialchars($agreement['branch_address']) ?></b> (the 'Leased Premises') under the following terms and conditions:</p>
                <p><strong>1. Lease Term:</strong> The term of this lease shall be for period of <b><?= $agreement['term_months'] ?></b> months / years commencing on <strong><?= $agreement['start_date'] ?></strong> and ending on <strong><?= $agreement['end_date'] ?></strong>, unless earlier terminated for cause.</p>
                <p><strong>2.Monthly Rent:</strong> The monthly rental shall be <b>₱<?= number_format($agreement['monthly_rent'], 2) ?></b> payable every <b><?= date('j', strtotime($agreement['next_due_date'])) ?></b> of the month.</p>
                <p><strong>3. Advance Payment:</strong> The Lessee shall pay advance rent equivalent to 1 month upon signing this contract, which shall be applied to the last month/s of the lease.</p>
                <p><strong>4. Deposit:</strong> The Lessee shall pay deposit the amount of <b>₱<?= number_format($agreement['monthly_rent'], 2) ?></b> upon signing this contract, which shall answer for unpaid rentals, damages, or breach of contract.</p>
                <!-- <p><strong>Unit:</strong> <?= htmlspecialchars($agreement['unit_name']) ?></p> -->
                <!-- <p><strong>Monthly Rent:</strong> ₱<?= number_format($agreement['monthly_rent'], 2) ?></p> -->
                <!-- <p><strong>Deposit:</strong> ₱<?= number_format($agreement['deposit'], 2) ?></p> -->
                <p><strong>Status:</strong> <span class="badge <?= $agreement['status'] === 'accepted' ? 'bg-success' : 'bg-secondary' ?>"><?= ucfirst($agreement['status']) ?></span></p>
            </div>

            <h6 class="fw-bold text-secondary mb-2 page-break"><i class="fa-solid fa-scale-balanced me-1"></i> Terms & Conditions</h6>
            <div class="terms-box">
                <?= nl2br(htmlspecialchars($agreement['term_conditions'])) ?>
            </div>
        </div>

        <div class="card-footer bg-light text-end">
            <?php if ($agreement['status'] !== 'accepted'): ?>
                <form method="post" class="d-inline">
                    <button type="submit" name="accept" class="btn btn-success">
                        <i class="fa-solid fa-check me-1"></i> Accept Agreement
                    </button>
                </form>
                <a href="my_agreement.php" class="btn btn-secondary">
                    <i class="fa-solid fa-xmark me-1"></i> Cancel
                </a>
            <?php else: ?>
                <div class="alert alert-success text-start mb-0">
                    <i class="fa-solid fa-circle-check me-1"></i> You have already accepted this agreement.
                </div>
                <a href="my_agreement.php" class="btn btn-danger btn-sm mt-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/3.0.1/jspdf.umd.min.js"></script>

<script>
function agreementPrint() {
    let content = document.getElementById("agreementPrint").innerHTML;

    let printWindow = window.open('', '', 'width=900,height=700');

    printWindow.document.write(`
        <html>
        <head>
            <title>Print Agreement</title>
            <style>
                body { margin: 30px; font-family: Arial, sans-serif; font-size: 15px; line-height: 1.3; }
                p { margin-bottom: 10px; }
                .page-break { page-break-before: always; }
                hr { width: 40%; margin: 12px auto; border: 0; border-top: 1px solid #777; }
            </style>
        </head>
        <body>
          

            ${content}
        </body>
        </html>
    `);

    printWindow.document.close();
    setTimeout(() => { printWindow.print(); printWindow.close(); }, 500);
}

// function downloadPDF(agreement_id) {
//     const elementHTML = document.querySelector("#agreementPrint");

//     // Clone the element to avoid affecting UI
//     const clone = elementHTML.cloneNode(true);

//     // Force all logos inside clone to small size
//     clone.querySelectorAll(".brand-logo").forEach(img => {
//         img.style.width = "25px";
//         img.style.height = "25px";
//     });

//     // Force typewriter font and smaller font size for PDF
//     clone.style.fontFamily = '"Courier New", Courier, monospace';
//     clone.style.fontSize = "11px";  // smaller font to fit page
//     clone.style.lineHeight = "1.1";

//     const { jsPDF } = window.jspdf;
//     const docPDF = new jsPDF('p', 'mm', 'a4');

//     docPDF.html(clone, {
//         callback: function(pdf) {
//             pdf.save("Agreement-" + agreement_id + ".pdf");
//         },
//         x: 10,
//         y: 10,
//         width: 190,                  // almost full width of A4 page
//         windowWidth: clone.scrollWidth,
//         html2canvas: {
//             scale: 2,                 // higher scale for better resolution
//             logging: true,
//             letterRendering: true,
//             useCORS: true
//         }
//     });
// }





// function downloadPDF(agreement_id) {
//     const elementHTML = document.querySelector("#agreementPrint");
//     const docPDF = new jsPDF();
//     docPDF.html(elementHTML, {
//         callback: function() { docPDF.save("Agreement-" + agreement_id + ".pdf"); },
//         x: 15,
//         y: 15,
//         width: 170,
//         windowWidth: 650
//     });
// }
</script>
</body>
</html>
