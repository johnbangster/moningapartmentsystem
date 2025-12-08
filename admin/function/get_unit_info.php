<?php
require_once('../config/dbcon.php');

if (isset($_GET['renter_id'])) {
    $renter_id = $_GET['renter_id'];

    $stmt = $con->prepare("
        SELECT u.name, u.price, r.lease_term, r.move_in_date 
        FROM renters r 
        JOIN units u ON r.unit_id = u.id 
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $renter_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        echo json_encode([
            'success' => true,
            'unit_name' => $result['name'],
            'price' => (float)$result['price'],
            'lease_term' => (int)$result['lease_term'],
            'move_in_date' => $result['move_in_date']
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
}

// if (isset($_GET['renter_id'])) {
//   $stmt = $con->prepare(
//     "SELECT u.name, u.price FROM renters r JOIN units u ON r.unit_id=u.id 
//      WHERE r.id=?"
//   );
//   $stmt->bind_param("i", $_GET['renter_id']);
//   $stmt->execute();
//   $j = $stmt->get_result()->fetch_assoc();
//   echo $j ? json_encode(['success'=>true,'unit_name'=>$j['name'],'price'=>$j['price']])
//           : json_encode(['success'=>false]);
// }

// if (isset($_GET['renter_id'])) {
//     $renter_id = $_GET['renter_id'];

//     $stmt = $con->prepare("SELECT u.name, u.price 
//                            FROM renters r 
//                            JOIN units u ON r.unit_id = u.id 
//                            WHERE r.id = ?");
//     $stmt->bind_param("i", $renter_id);
//     $stmt->execute();
//     $res = $stmt->get_result();

//     if ($res->num_rows > 0) {
//         $unit = $res->fetch_assoc();
//         echo json_encode([
//             'success' => true,
//             'unit_name' => $unit['name'],
//             'price' => $unit['price']
//         ]);
//     } else {
//         echo json_encode(['success' => false]);
//     }
// }

// if (isset($_POST['generate_bill'])) {
//     $renter_id = $_POST['renter_id'];
//     $renter = getRenterById($renter_id);
//     $unit = getUnitById($renter['unit_id']);

//     generateMonthlyBills($renter, $unit, $con);
//     echo "Monthly bills created successfully!";
// }

?>
