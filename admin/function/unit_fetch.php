<?php
include('config/dbcon.php'); 

$query = mysqli_query($con, "
    SELECT u.*, ut.type_name, b.name AS branch_name
    FROM units u
    LEFT JOIN unit_type ut ON u.unit_type_id = ut.id
    LEFT JOIN branch b ON u.branch_id = b.id
    WHERE u.removed = 0
    ORDER BY u.id DESC
");

$i = 1;
$data = "";

while($row = mysqli_fetch_assoc($query)){
    // Status badge
    switch($row['status']){
        case 'Available': $status_badge = "<span class='badge bg-success'>Available</span>"; break;
        case 'Occupied': $status_badge = "<span class='badge bg-danger'>Occupied</span>"; break;
        case 'Under Maintenance': $status_badge = "<span class='badge bg-warning text-dark'>Under Maintenance</span>"; break;
        default: $status_badge = "<span class='badge bg-secondary'>Inactive</span>";
    }

    $data .= "
    <tr class='align-middle'>
        <td>{$i}</td>
        <td>{$row['name']}</td>
        <td>{$row['area']} sq. ft.</td>
        <td>₱{$row['price']}</td>
        <td>{$row['branch_name']}</td>
        <td>{$row['type_name']}</td>
        <td>{$status_badge}</td>
        <td>
            <button type='button' class='btn btn-primary shadow-none btn-sm editUnitBtn' data-id='{$row['id']}' data-bs-toggle='modal' data-bs-target='#edit-unit'>
                <i class='fa-solid fa-pen-to-square'></i>
            </button>
            <button type='button' class='btn btn-info shadow-none btn-sm' onclick=\"unit_images({$row['id']},'{$row['name']}')\" data-bs-toggle='modal' data-bs-target='#unit-images'>
                <i class='fa-solid fa-images'></i>
            </button>
            <button type='button' class='btn btn-danger shadow-none btn-sm' onclick='remove_unit({$row['id']})'>
                <i class='fa-solid fa-trash-can'></i>
            </button>
        </td>
    </tr>
    ";
    $i++;
}

echo $data;
