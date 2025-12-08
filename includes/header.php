
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <!-- SweetAlert2 CSS and JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php require('includes/links.php');?>
    <title><?php echo $settings_r['site_title']; ?> - Home</title>

    <link rel="stylesheet" href="../assets/css/custom.css">
    <style>
        .availability-form{
            margin-top: -50px;
            z-index: 2;
            position: relative;
        }
        @media screen and(max-width: 575px) {
            .availability-form{
            margin-top: 25px;
            padding: 0 35px;
          
            }
        }

        @media print {
        body::before {
            content: "EXPIRED";
            color: red;
            font-size: 100px;
            position: fixed;
            top: 30%;
            left: 10%;
            transform: rotate(-45deg);
            opacity: 0.3;
    }
}
    </style>

</head>

     <?php require('includes/navbar.php'); ?>
