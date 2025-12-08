<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
Swal.fire({
  icon: 'error',
  title: 'Payment Failed',
  text: 'Your transaction was cancelled or not completed.',
  confirmButtonText: 'OK'
}).then(() => {
  window.location.href = 'renter_bookings.php';
});
</script>

</body>
</html>
