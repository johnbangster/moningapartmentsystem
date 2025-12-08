<?php

require('../config/dbcon.php');
// session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Manila');
// Read and decode data safely

// Read and decode input JSON
$data = json_decode(file_get_contents("php://input"), true);
$errors = [];
$status = 422;

foreach ($data as $unitId => $unit) {
    $errors[$unitId] = [];

    if (!is_numeric($unit['unit_price'])) {
        $errors[$unitId]['unit_price'] = "Unit price must be numeric.";
    }
    if (!is_numeric($unit['electricity_bill'])) {
        $errors[$unitId]['electricity_bill'] = "Electricity bill must be numeric.";
    }
    if (!is_numeric($unit['water_bill'])) {
        $errors[$unitId]['water_bill'] = "Water bill must be numeric.";
    }

    if (!empty($unit['charges'])) {
        foreach ($unit['charges'] as $index => $charge) {
            if (empty($charge['name'])) {
                $errors[$unitId]["charge_name_$index"] = "Charge name cannot be empty.";
            }
            if (!is_numeric($charge['charge'])) {
                $errors[$unitId]["charge_amount_$index"] = "Charge amount must be numeric.";
            }
        }
    }

    if (empty($errors[$unitId])) unset($errors[$unitId]);
}

if (empty($errors)) {
    $status = 200;

    foreach ($data as $unitId => $unit) {
        $con->begin_transaction();  // Use transactions for safety

        try {
            $unit_charge = intval($unit['unit_price']);
            $electricity_bill = intval($unit['electricity_bill']);
            $water_bill = intval($unit['water_bill']);
            $total = $unit_charge + $electricity_bill + $water_bill;

            $reference_id = md5(uniqid('', true));
            $start_period = date("Y-m-01");
            $end_period = date("Y-m-t");

            $stmt = $con->prepare("INSERT INTO bills (reference_id, unit_id, unit_charge, electricity_bill, water_bill, start_period, end_period)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siissss", $reference_id, $unitId, $unit_charge, $electricity_bill, $water_bill, $start_period, $end_period);
            $stmt->execute();
            $bill_id = $stmt->insert_id;

            // Handle additional charges
            if (!empty($unit['charges'])) {
                foreach ($unit['charges'] as $charge) {
                    $charge_name = $charge['name'];
                    $charge_amount = intval($charge['charge']);
                    $total += $charge_amount;

                    $stmt_charge = $con->prepare("INSERT INTO additional_charges (bill_id, name, charge) VALUES (?, ?, ?)");
                    $stmt_charge->bind_param("isi", $bill_id, $charge_name, $charge_amount);
                    $stmt_charge->execute();
                }
            }

            // Update total bill amount
            $stmt_update = $con->prepare("UPDATE bills SET total_amount = ? WHERE id = ?");
            $stmt_update->bind_param("ii", $total, $bill_id);
            $stmt_update->execute();

            // Assign bill to renters
            $stmt_renters = $con->prepare("SELECT id, contact FROM renters WHERE unit_id = ?");
            $stmt_renters->bind_param("i", $unitId);
            $stmt_renters->execute();
            $result_renters = $stmt_renters->get_result();

            while ($renter = $result_renters->fetch_assoc()) {
                $stmt_renter_bill = $con->prepare("INSERT INTO renter_bills (renter_id, bill_id) VALUES (?, ?)");
                $stmt_renter_bill->bind_param("ii", $renter['id'], $bill_id);
                $stmt_renter_bill->execute();

                // Notify renter (use your existing SMS function)
                // sendAMessage($renter['contact'], "Your bill for $start_period to $end_period is ₱$total.00. Please pay on time.");
            }

            $con->commit();

        } catch (Exception $e) {
            $con->rollback();
            $status = 500;
            break;
        }
    }
}

echo json_encode([
    "status" => $status,
    "errors" => $errors
]);


// foreach ($data as $unitKey => $unit) {
//     // Copy data to errors
//     $errors[$unitKey] = $unit;
//     foreach ($errors[$unitKey] as $subKey => $_) {
//         $errors[$unitKey][$subKey] = "";
//     }
    
//     // Unset unnecessary values for errors
//     unset($errors[$unitKey]["unit_price"]);
//     unset($errors[$unitKey]["charges"]);

//     //($data[$unitKey] as $key => $value)
//     foreach ($unit as $key => $value) {

//         if (!str_contains($key, "name") && !is_numeric($value)) {
//             $errors[$unitKey][$key] = "Value should only contain digits.";
//         }

//         if (empty(($errors[$unitKey][$key]))) {
//             unset($errors[$unitKey][$key]);
//         }
//     }

//     // Unset unit if no errors
//     if (count($errors[$unitKey]) < 1) {
//         unset($errors[$unitKey]);
//     }
// }

// if (count($errors) < 1) {
//     $status = 200;

//     foreach ($data as $unitId => $unit) {
        
//         // prepare bill data
//         $electricity_bill = intval($unit['electricity_bill']);
//         $water_bill = intval($unit['water_bill']);
//         $start_period = date("Y-m-01");
//         $end_period = date("Y-m-t");
//         $unit_charge = intval($unit['unit_price']);
//         $total = intval($unit_charge) + intval($electricity_bill) + intval($water_bill);
//         // foreach (['charges', 'unit_price', 'water_bill', 'electricity_bill'] as $key) {
//         //     unset($data[$unitId][$key]);
//         // }
//         // $sql = "INSERT INTO bills (reference_id, unit_id, unit_charge, electricity_bill, water_bill, start_period, end_period)
//         //         VALUES ('" . md5(uniqid('', true)) . "', '$unitId', '$unit_charge', '$electricity_bill', '$water_bill', '$start_period', '$end_period')";
        
//         // insert bill
//         $ref = md5(uniqid('', true));
//         $sql = "INSERT INTO bills (reference_id, unit_id, unit_charge, electricity_bill, water_bill, start_period, end_period)
//                 VALUES ('$ref', '$unitId', '$unit_charge', '$electricity_bill', '$water_bill', '$start_period', '$end_period')";
//         $res = $con->query($sql);

//         if (!$res) {
//             echo json_encode([
//                 "status" => 500,
//                 "message" => "MySQL Error: " . $con->error
//             ]);
//             exit;
//         }
        
//         $bill_id = $con->insert_id;

//         // handle additional charges if exist
//         if (!empty($unit['charges'])) {
//             foreach ($unit['charges'] as $charge) {
//                 $name = $charge['name'];
//                 $charge_amount = intval($charge['charge']);
//                 $total += $charge_amount;

//                 $sql = "INSERT INTO additional_charges (bill_id, `name`, charge) VALUES ($bill_id, '$name', $charge_amount)";
//                 $res = $con->query($sql);
//                 if (!$res) {
//                     $status = 500;
//                     break 2;
//                 }
//             }
//         }

//         // update total
//         $sql = "UPDATE bills SET total_amount = $total WHERE id = $bill_id";
//         $con->query($sql);

//         // assign bill to renters
//         $sql = "SELECT id, contact FROM renters WHERE unit_id = $unitId";
//         $renters = $con->query($sql)->fetch_all(MYSQLI_ASSOC);
//         foreach ($renters as $renter) {
//             if (isset($renter['contact'])) {
//                 $sql = "INSERT INTO renter_bills (renter_id, bill_id) VALUES ('".$renter['id']."', $bill_id)";
//                 $con->query($sql);
//                 sendAMessage($renter['contact'], "Your bill for $start_period to $end_period is ₱$total.00. Please pay on time.");
//             }
//         }
//     }
// }

   

        // if ($res) {
        //     $bill_id = $con->insert_id;

        //     if (count($data[$unitId]) > 0) {
        //         $vals = array_values($data[$unitId]);
        //         $charges = [];
        //         $i = 0;
                
        //         while ($i < count($vals)) {
        //             $charges[] = [$vals[$i], $vals[++$i]];
        //             $i++;
        //         }
    
        //         foreach ($charges as [$name, $charge]) {
        //             $total += intval($charge);
        //             $sql = "INSERT INTO additional_charges (bill_id, `name`, charge) VALUES ($bill_id, '$name', '$charge')";
        //             $con->query($sql);
        //         }
        //     }

        //     $sql = "UPDATE bills SET `total_amount` = $total WHERE id = $bill_id";
        //     $con->query($sql);
        //     $sql = "SELECT id, contact FROM renters WHERE unit_id = $unitId";
        //     $renters = $con->query($sql)->fetch_all(MYSQLI_ASSOC);
        //     foreach ($renters as $renter) {
        //         if (isset($renter['contact'])) {
        //             $sql = "INSERT INTO renter_bills (renter_id, bill_id) VALUES ('".$renter['id']."', ".intval($bill_id).")";
        //             $res = $con->query($sql);
        //             sendAMessage($renter['contact'], "Your bill for $start_period to $end_period is P$total.00. Please make sure to pay to avoid penalties.");
        //         }
        //     }
        // }
    

    

