<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta http-equiv="refresh" content="30">
        <!-- <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" /> original -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="For rent" content="Apartment for Rent, Condo unit for rent , Room for rent, Cebu fo rent" />
        <meta name="ASUY" content="For Rent" />
        <title>Moning Rental System</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <link href="css/custom.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
        <!-- SweetAlert2 CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

        



    <style>
    .payment-box {
        max-width: 450px;
        margin: 20px auto;
        background: #ffffff;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        text-align: center;
    }
    .payment-box h5 {
        font-weight: 600;
        font-size: 1.3rem;
        margin-bottom: 15px;
    }
    .payment-box select {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 1rem;
        margin-bottom: 15px;
    }
    #paypal-button-container {
        margin-top: 10px;
        display: flex;
        justify-content: center;
    }
    #totalAmountLabel {
        font-weight: 600;
        font-size: 1.2rem;
        margin-bottom: 15px;
        color: #1a1a1a;
    }
    </style>

</head>
<?php
    require ('../admin/config/dbcon.php');
?>
<script>
    function loadNotifications() {
        fetch('fetch_notifications.php') // ✅ now fetching renter-specific file
            .then(response => response.json())
            .then(data => {
                // Update badge
                const badge = document.getElementById('notifCount');
                if (data.unread > 0) {
                    badge.innerText = data.unread;
                    badge.style.display = 'inline-block';
                } else {
                    badge.innerText = '';
                    badge.style.display = 'none';
                }

                // Update dropdown list
                document.getElementById('notifList').innerHTML = data.html;
            })
            .catch(err => console.error('Fetch error:', err));
    }

    // Run on page load + every 10 seconds
    document.addEventListener("DOMContentLoaded", function() {
        loadNotifications();
        setInterval(loadNotifications, 10000);
    });
</script>

<!-- <script>
      function loadNotifications() {
            fetch('../admin/fetch_notifications.php')
                .then(response => response.json())
                .then(data => {
                    // Update badge (hide if 0)
                    const badge = document.getElementById('notifCount');
                    if (data.unread > 0) {
                        badge.innerText = data.unread;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.innerText = '';
                        badge.style.display = 'none';
                    }

                    // Update dropdown list
                    document.getElementById('notifList').innerHTML = data.html;
                })
                .catch(err => console.error('Fetch error:', err));
        }

        // Run on page load + every 10 seconds
        document.addEventListener("DOMContentLoaded", function() {
            loadNotifications();
            setInterval(loadNotifications, 10000);
        });
</script> -->

<body class="sb-nav-fixed">

 <?php include('includes/navbar_top.php'); ?>
    
 <div id="layoutSidenav">

     <?php include('includes/sidebar.php'); ?>

        <div id="layoutSidenav_content">
                <main>


