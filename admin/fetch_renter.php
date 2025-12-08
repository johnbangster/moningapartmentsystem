<?php
require('config/code.php');

$filter_name = isset($_POST['renter_name']) ? trim($_POST['renter_name']) : '';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

$limit = 5;
$offset = ($page - 1) * $limit;

$where = "";
if (!empty($filter_name)) {
    $safe_name = mysqli_real_escape_string($con, $filter_name);
    $where = "WHERE r.first_name LIKE '%$safe_name%' 
              OR r.middle_name LIKE '%$safe_name%' 
              OR r.last_name LIKE '%$safe_name%'";
}

// Count total renters
$count_query = "SELECT COUNT(*) AS total FROM renters r $where";
$count_result = mysqli_query($con, $count_query);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

$query = "
    SELECT 
        r.*, 
        u.name AS unit_name,
        ra.id AS agreement_id
    FROM renters r
    JOIN units u ON r.unit_id = u.id
    LEFT JOIN rental_agreements ra ON r.id = ra.renter_id AND r.unit_id = ra.unit_id
    $where
    ORDER BY r.created_at ASC
    LIMIT $limit OFFSET $offset
";

$result = mysqli_query($con, $query);
$i = $offset + 1;

$output = '';

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $output .= "<tr>
            <td>{$i}</td>
            <td>
                <span class='badge rounded-pill bg-light text-dark'>First Name: {$row['first_name']}</span><br>
                <span class='badge rounded-pill bg-light text-dark'>Last Name: {$row['last_name']}</span><br>
                <span class='badge rounded-pill bg-light text-dark'>Middle Name: {$row['middle_name']}</span>
            </td>
            <td>{$row['contacts']}</td>
            <td>{$row['email']}</td>
            <td>{$row['unit_name']}</td>
            <td>{$row['status']}</td>
            <td>
                <button class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#editModal{$row['id']}'>
                    <i class='fa-solid fa-pen-to-square'></i>
                </button>
                <button type='button' onclick=\"renter_images({$row['id']}, '{$row['first_name']} {$row['last_name']}')\" 
                        class='btn btn-info shadow-none' data-bs-toggle='modal' data-bs-target='#renter-images'>
                    <i class='fa-solid fa-images'></i>
                </button>
                <a href='view_agreement.php?agreement_id={$row['agreement_id']}' class='btn btn-success'>
                    <i class='fa-solid fa-file-contract'></i>
                </a>
                <button onclick='remove_renter({$row['id']})' class='btn btn-danger'>
                    <i class='fa-solid fa-trash-can'></i>
                </button>
            </td>
        </tr>";
        $i++;
    }
} else {
    $output .= "<tr><td colspan='7' class='text-center text-danger fw-bold'>No renters found.</td></tr>";
}

$output .= "<tr>
                <td colspan='7'>
                    <nav aria-label='Page navigation'>
                        <ul class='pagination justify-content-end'>";
                            for ($p = 1; $p <= $total_pages; $p++) {
                                $active = ($p == $page) ? 'active' : '';
                                $output .= "<li class='page-item $active'>
                                    <a class='page-link page-link-ajax' href='#' data-page='$p'>$p</a>
                                </li>";
                            }
                        $output .= "  </ul>
                    </nav>
                </td>
            </tr>";
        echo $output;
?>
