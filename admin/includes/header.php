<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>

        <title>Dashboard - Monings Rental System</title>

        <!-- Alertify CSS -->
        <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
        <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>


        <!-- Bootstrap 5 Bundle JS (includes Popper.js needed for dropdowns) -->
        <link rel="stylesheet" href="../assets/css/bootstrap5.min.css">
         <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
        
        <!-- Bootstrap 5 CSS -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/custom.css" rel="stylesheet" />

        <link href="css/styles.css" rel="stylesheet" />

        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

        <link href="https://fonts.googleapis.com/css2?family=Merienda:wght@300..900&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
        <?php require('scripts.php');?>

        <!--bar graph-->
        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.js"></script>
        <script src="https://code.highcharts.com/modules/export-data.js"></script>
        <script src="https://code.highcharts.com/modules/accessibility.js"></script>
        <script src="https://code.highcharts.com/themes/adaptive.js"></script>
        
    </head>
    <style>
        :root
        {
          --teal: #2ec1ac;
          --teal_hover: #279e8c;
        }

        *{
          font-family: "Poppins", sans-serif;
        }
          
        .h-font{
          font-family: "Merienda", cursive;
        }
          
        /* remove arrow number */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
        }
        /* Firefox */
        input[type=number] {
        -moz-appearance: textfield;
        }

        .custom-bg{
          background-color: var(--teal);
          border: var(--teal);
        }

        .custom-bg:hover{
          background-color: var(--teal_hover);
          border-color: var(--teal_hover);
        }
          
        .h-line{
          width: 150px;
          margin: 0 auto;
          height: 1.7px;
        }

        .custom-alert{
        position: fixed;
        top: 25px;
        right: 25px;
        }

          * {
    font-family:
      -apple-system,
      BlinkMacSystemFont,
      "Segoe UI",
      Roboto,
      Helvetica,
      Arial,
      "Apple Color Emoji",
      "Segoe UI Emoji",
      "Segoe UI Symbol",
      sans-serif;
  }

 

   .highcharts-figure {
        width: 100%;        /* Fit the figure to its parent div */
        margin: 0;
    }

    #container {
        width: 100%;        /* Make the chart fit inside the div */
        height: 200px;      /* You can adjust this height */
    }


  /* .highcharts-figure,
  .highcharts-data-table table {
    min-width: 310px;
    max-width: 800px;
    margin: 1em auto;
  } */

  #container {
    height: 400px;
  }

  .highcharts-data-table table {
    font-family: Verdana, sans-serif;
    border-collapse: collapse;
    border: 1px solid var(--highcharts-neutral-color-10, #e6e6e6);
    margin: 10px auto;
    text-align: center;
    width: 100%;
    max-width: 500px;
  }

  .highcharts-data-table caption {
    padding: 1em 0;
    font-size: 1.2em;
    color: var(--highcharts-neutral-color-60, #666);
  }

  .highcharts-data-table th {
    font-weight: 600;
    padding: 0.5em;
  }

  .highcharts-data-table td,
  .highcharts-data-table th,
  .highcharts-data-table caption {
    padding: 0.5em;
  }

  .highcharts-data-table thead tr,
  .highcharts-data-table tbody tr:nth-child(even) {
    background: var(--highcharts-neutral-color-3, #f7f7f7);
  }

  .highcharts-description {
    margin: 0.3rem 10px;
  }

  /* Custom scrollbar for Due Date table */
.card-body::-webkit-scrollbar {
    width: 6px;  /* width of the scrollbar */
}

.card-body::-webkit-scrollbar-track {
    background: #f1f1f1;  /* track color */
    border-radius: 3px;
}

.card-body::-webkit-scrollbar-thumb {
    background: #6c757d;  /* thumb color */
    border-radius: 3px;
}

.card-body::-webkit-scrollbar-thumb:hover {
    background: #495057;  /* thumb color on hover */
}

.card-body {
    scrollbar-width: thin;
    scrollbar-color: #6c757d #f1f1f1;
}

/* Extend the Adaptive theme */
:root,
.highcharts-light {
  --highcharts-color-0: black;
}

@media (prefers-color-scheme: dark) {
  :root {
    --highcharts-color-0: white;
  }
}

.highcharts-dark {
  --highcharts-color-0: white;
}


.is-valid {
    border: 2px solid #28a745 !important;
}
.is-invalid {
    border: 2px solid #dc3545 !important;
}
</style>

  <script>
    function loadNotifications() {
        fetch('fetch_notifications.php')
            .then(response => response.json())
            .then(data => {
                // Update badge count
                const badge = document.getElementById('notifCount');
                if (badge) {
                    if (data.unread > 0) {
                        badge.innerText = data.unread;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.innerText = '';
                        badge.style.display = 'none';
                    }
                }

                // Update dropdown list (HTML returned from fetch_notifications.php)
                const notifList = document.getElementById('notifList');
                if (notifList) {
                    notifList.innerHTML = data.html || '<li class="dropdown-item text-muted">No new notifications</li>';
                }
            })
            .catch(err => console.error('Fetch error:', err));
    }

    // MARK SINGLE AS READ
    function markAsRead(id) {
        fetch(`mark_read.php?id=${id}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    loadNotifications(); // Refresh after marking read
                }
            })
            .catch(err => console.error('Mark read error:', err));
    }

    // MARK ALL AS READ
    function markAllAsRead() {
        fetch('mark_read.php?all=1', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    loadNotifications();
                }
            })
            .catch(err => console.error('Mark all read error:', err));
    }

    // Auto-load on page load and refresh every 10 seconds
    document.addEventListener('DOMContentLoaded', () => {
        loadNotifications();
        setInterval(loadNotifications, 10000);
    });
</script>

  
   
<body class="sb-nav-fixed bg-light">
    <?php include('includes/navbar-top.php'); ?>

        <!--<?php include('message.php'); ?>-->

    <div id="layoutSidenav">

        <?php include('includes/sidebar.php'); ?>

        <div id="layoutSidenav_content">
          <main>


    