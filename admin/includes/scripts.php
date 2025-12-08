<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
<script src="assets/demo/chart-area-demo.js"></script>
<script src="assets/demo/chart-bar-demo.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
<script src="js/datatables-simple-demo.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    
function deleteRenter(renterId) {
    Swal.fire({
        title: "Are you sure?",
        text: "This renter will be deleted permanently.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete!"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "function/delete_renter.php",
                type: "POST",
                data: { id: renterId },
                dataType: "json", // ensures jQuery parses the JSON
                success: function(data) {
                    if (data.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Deleted!",
                            text: data.message,
                            timer: 1200,
                            showConfirmButton: false
                        });

                        // Remove row immediately
                        const row = document.getElementById('renterRow' + renterId);
                        if (row) row.remove();

                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: data.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: "error",
                        title: "Server Error!",
                        text: `AJAX failed: ${xhr.responseText}`
                    });
                }
            });
        }
    });
}


</script>
</body>

</html>
