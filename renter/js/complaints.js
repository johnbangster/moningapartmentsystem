document.getElementById("add_complaint_form").addEventListener("submit", function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch("renter_submit_complaint.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById("add-renter"));
        modal.hide();

        // Clear form
        document.getElementById("add_complaint_form").reset();

        // Refresh complaint table
        fetch("fetch_complaints.php")
        .then(res => res.text())
        .then(html => {
            document.querySelector("#complaint-table tbody").innerHTML = html;
        });

        alert("Complaint submitted successfully.");
    })
    .catch(err => {
        console.error("Error:", err);
        alert("Something went wrong.");
    });
});