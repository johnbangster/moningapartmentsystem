<?php
    include('authentication.php');
    require_once ('config/code.php');
    require ('includes/header.php');
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


<div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">E-Reciept</h5>
                <button class="btn btn-success btn-sm " onclick="ereceiptPrint()"><i class="fa-solid fa-print"></i> 
                    Print
                </button>
            </div>
            <div class="card-body">
            <div class="container-fluid px-4">
                

                <?php include('message.php'); ?>

                <div class="card-body" >
                    <?php
                    if (isset($_GET['bill_id']) && is_numeric($_GET['bill_id'])) {
                        $bill_id = intval($_GET['bill_id']);

                        // Fetch bill + renter info
                        $query = "
                            SELECT b.*, r.first_name, r.last_name, r.email, r.contacts 
                            FROM bills b 
                            JOIN renters r ON b.renter_id = r.id 
                            WHERE b.id = $bill_id
                            LIMIT 1
                        ";
                        $result = mysqli_query($con, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $data = mysqli_fetch_assoc($result);
                            ?>

                            <table style="width: 100%; margin-bottom: 20px;" id="ereceiptPrint">
                                <tbody>
                                    <tr>
                                        <td style="text-align: center;" colspan="2">
                                            <!-- <h4 style="font-size: 23px; line-height: 30px; margin:2px; padding:0;">Official Receipt</h4> -->
                                            <h4 class="mt-4" style="font-size: 23px; line-height: 30px; margin:2px; padding:0;">Monings Rental Services</h4>
                                            <p style="font-size: 14px; line-height: 20px; margin:2px;">1438-B M.J.Cuenco Avenue Mabolo, Cebu City</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <h5>Renter Details</h5>
                                            <p>Name: <?= htmlspecialchars($data['first_name'] . ' ' . $data['last_name']) ?></p>
                                            <p>Contact: <?= htmlspecialchars($data['contacts']) ?></p>
                                            <p>Email: <?= htmlspecialchars($data['email']) ?></p>
                                        </td>
                                        <td align="right">
                                            <h5>Receipt Details</h5>
                                            <p>Reference #: <?= htmlspecialchars($data['reference_id']) ?></p>
                                            <p>Bill Date: <?= date('d M Y', strtotime($data['payment_date'] ?? $data['created_at'])) ?></p>
                                            <p>Due Date: <?= htmlspecialchars($data['due_date']) ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="padding-top: 20px;">
                                            <h5>Billing Summary</h5>
                                            <p>Unit Price:₱ <?= number_format($data['total_amount'], 2) ?></p>
                                            <p>Add-ons:₱ <?= number_format($data['addon_total'], 2) ?></p>
                                            <p>Late Fee:₱ <?= number_format($data['late_fee'], 2) ?></p>
                                            <h5>Total Amount Paid: <strong>₱<?= number_format($data['total_amount'], 2) ?></strong></h5>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <!-- <div class="mt-4 text-end">
                                <button onclick="window.print()" class="btn btn-primary">Print Receipt</button> -->
                                <!-- <button class="btn btn-success btn-sm " onclick="ereceiptPrint()"><i class="fa-solid fa-print"></i> Print</button>
                            </div>  -->
                        <?php
                        } else {
                            echo "<h5 class='text-danger'>No bill found with this ID.</h5>";
                        }
                    } else {
                        ?>
                        <div class="text-center py-5">
                            <h5>No valid bill ID provided!</h5>
                            <div>
                                <!-- <a href="transaction.php" class="btn btn-primary mt-4 w-25">Go back to Transactions</a> -->
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                </div>
                </div>
                <div class="card-footer text-end">
                    <a href="transaction.php" class="btn btn-danger btn-sm float-end">Back</a>
                    <!-- <a href="index.php" class="btn btn-secondary">Back to Dashboard</a> -->
                </div>
        </div>
    </div>

<?php 
    require('includes/footer.php'); 
    include('includes/scripts.php');
?>


<!--for pdf vd link-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" integrity="sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/3.0.1/jspdf.umd.min.js" integrity="sha512-ad3j5/L4h648YM/KObaUfjCsZRBP9sAOmpjaT2BDx6u9aBrKFp7SbeHykruy83rxfmG42+5QqeL/ngcojglbJw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    function ereceiptPrint(){
        var divContents  = document.getElementById("ereceiptPrint").innerHTML;
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

        var elementHTML = document.querySelector("#ereceiptPrint");
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

