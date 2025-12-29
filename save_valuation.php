<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = trim($_POST['action']);
    $id = (int)$_POST['id'];
    $ref_number = trim($_POST['ref_number']);
    $valuation_date = trim($_POST['valuation_date']);
    $requestor_name = trim($_POST['requestor_name']);
    // $requestor_name_2 = trim($_POST['requestor_name_2']);
    $requestor_contact_2 = trim($_POST['requestor_contact_2']);
    $bank_id = trim($_POST['bank_id']);
    $buyer = trim($_POST['buyer']);
    $seller = trim($_POST['seller']);
    $place_of_asset = trim($_POST['place_of_asset']);
    $car_company = trim($_POST['car_company']);
    $car_company_select = trim($_POST['car_company_select']);
    $vehicle_type = trim($_POST['vehicle_type']);
    $car_model = trim($_POST['car_model']);
    $registration_number = trim($_POST['registration_number']);
    $vehicle_color = trim($_POST['vehicle_color']);
    $year_of_manufacture = (int)$_POST['year_of_manufacture'];
    $date_of_registration = trim($_POST['date_of_registration']);
    $chassis_number = trim($_POST['chassis_number']);
    $engine_number = trim($_POST['engine_number']);
    $odometer_reading_value = trim($_POST['odometer_reading_value']);
    $odometer_unit = trim($_POST['odometer_unit']);
    $odometer_reading = $odometer_reading_value . ' ' . $odometer_unit;
    $transmission_type = trim($_POST['transmission_type']);
    $features = trim($_POST['features']);
    $special_note = trim($_POST['special_note']);
    $engine_transmission = trim($_POST['engine_transmission']);
    $body_paint = trim($_POST['body_paint']);
    $tyres = trim($_POST['tyres']);
    $valuation_amount = (float)$_POST['valuation_amount'];
    $forced_sale_valuation_amount = !empty($_POST['forced_sale_valuation_amount']) 
    ? (float)$_POST['forced_sale_valuation_amount'] 
    : null;

    // Fallback: Set buyer to requestor_name if empty
    if (empty($buyer)) {
        $buyer = $requestor_name;
    }

    // Validation
    $errors = [];
    if (empty($ref_number)) $errors[] = 'REF Number is required.';
    if (empty($valuation_date)) $errors[] = 'Valuation Date is required.';
    if (empty($requestor_name)) $errors[] = 'Requestor Name is required.';
    if (empty($bank_id)) $errors[] = 'Bank Name is required.';
    if (empty($buyer)) $errors[] = 'Buyer is required.';
    // if (empty($seller)) $errors[] = 'Seller is required.';
    if (empty($place_of_asset)) $errors[] = 'Place of Asset is required.';
    if (empty($car_company_select)) $errors[] = 'Car Company is required.';
    if (empty($vehicle_type)) $errors[] = 'Vehicle Type is required.';
    if (empty($car_model)) $errors[] = 'Car Model is required.';
    if (empty($registration_number)) $errors[] = 'Registration Number is required.';
    if (empty($year_of_manufacture)) $errors[] = 'Year of Manufacture is required.';
    if (empty($date_of_registration)) $errors[] = 'Date of Registration is required.';
    if (empty($chassis_number)) $errors[] = 'Chassis Number is required.';
    if (empty($engine_number)) $errors[] = 'Engine Number is required.';
    if (empty($odometer_reading_value)) $errors[] = 'Odometer Reading is required.';
    if (empty($transmission_type)) $errors[] = 'Transmission Type is required.';
    if (empty($features)) $errors[] = 'Features are required.';
    if (empty($engine_transmission)) $errors[] = 'Engine & Transmission is required.';
    if (empty($body_paint)) $errors[] = 'Body & Paint is required.';
    if (empty($tyres)) $errors[] = 'Tyres is required.';
    if ($valuation_amount <= 0) $errors[] = 'Valuation Amount must be greater than zero.';

    // Date validation
    $today = date('Y-m-d');
    if ($valuation_date > $today) $errors[] = 'Valuation Date cannot be in the future.';
    if ($date_of_registration > $today) $errors[] = 'Date of Registration cannot be in the future.';
    if ($year_of_manufacture > (int)$today) $errors[] = 'Year of Manufacture cannot be in the future.';

    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        header('Location: valuations.php?action=' . $action . '&id=' . $id);
        exit;
    }

    // Use car_company_select if not "new", otherwise use car_company
    $final_car_company = $car_company_select === 'new' ? $car_company : $car_company_select;

    // Insert new requestor if not exists
    $stmt = $pdo->prepare("INSERT IGNORE INTO requestors (name) VALUES (?)");
    $stmt->execute([$requestor_name]);
    // if ($requestor_name_2) {
    //     $stmt->execute([$requestor_name_2]);
    // }

    // Insert new bank if not exists
    $stmt = $pdo->prepare("INSERT IGNORE INTO banks (name) VALUES (?)");
    $stmt->execute([$bank_name]);

    // Insert new place if not exists
    $stmt = $pdo->prepare("INSERT IGNORE INTO places (name) VALUES (?)");
    $stmt->execute([$place_of_asset]);

    // Insert new car company and model if not exists
    $stmt = $pdo->prepare("INSERT IGNORE INTO car_models (car_company, car_model) VALUES (?, ?)");
    $stmt->execute([$final_car_company, $car_model]);

    // Insert new vehicle type if not exists
    $stmt = $pdo->prepare("INSERT IGNORE INTO vehicle_types (type) VALUES (?)");
    $stmt->execute([$vehicle_type]);

    // Insert or update valuation
    if ($action == 'add') {
        error_log("Executing INSERT with values: " . print_r([
            $ref_number, $valuation_date, $requestor_name, $requestor_contact_2,
            $bank_id, $buyer, $seller, $place_of_asset, $final_car_company, $vehicle_type, $car_model,
            $registration_number, $vehicle_color, $year_of_manufacture, $date_of_registration, $chassis_number,
            $engine_number, $odometer_reading, $transmission_type, $features, $special_note, $engine_transmission,
            $body_paint, $tyres, $valuation_amount,$forced_sale_valuation_amount 
        ], true));
        $stmt = $pdo->prepare("
            INSERT INTO valuations (
                ref_number, valuation_date, requestor_name, requestor_contact_2,
                bank_id, buyer, seller, place_of_asset, car_company, vehicle_type, car_model,
                registration_number, vehicle_color, year_of_manufacture, date_of_registration, chassis_number,
                engine_number, odometer_reading, transmission_type, features, special_note, engine_transmission,
                body_paint, tyres, valuation_amount,forced_sale_valuation_amount 
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        try {
            $success = $stmt->execute([
                $ref_number, $valuation_date, $requestor_name, $requestor_contact_2 ?: null,
                $bank_id, $buyer, $seller ?: null, $place_of_asset, $final_car_company, $vehicle_type, $car_model,
                $registration_number, $vehicle_color ?: null, $year_of_manufacture, $date_of_registration, $chassis_number,
                $engine_number, $odometer_reading, $transmission_type, $features, $special_note ?: null, $engine_transmission,
                $body_paint, $tyres, $valuation_amount, $forced_sale_valuation_amount ?: null
            ]);
        } catch (PDOException $e) {
            error_log("INSERT failed: " . $e->getMessage());
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
            header('Location: valuations.php?action=add');
            exit;
        }

        if ($success) {
            // Increment ref_number
            $new_ref_number = (int)$settings['ref_number'] + 1;
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
            $stmt->execute(['ref_number', $new_ref_number, $new_ref_number]);

            $_SESSION['success'] = 'Valuation added successfully.';
            header('Location: valuations.php');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to add valuation.';
            header('Location: valuations.php?action=add');
            exit;
        }
    } else if ($action == 'edit' && $id > 0) {
        $stmt = $pdo->prepare("
            UPDATE valuations SET
                valuation_date = ?, requestor_name = ?, requestor_contact_2 = ?,
                bank_id = ?, buyer = ?, seller = ?, place_of_asset = ?, car_company = ?, vehicle_type = ?,
                car_model = ?, registration_number = ?, vehicle_color = ?, year_of_manufacture = ?, date_of_registration = ?,
                chassis_number = ?, engine_number = ?, odometer_reading = ?, transmission_type = ?, features = ?, special_note = ?,
                engine_transmission = ?, body_paint = ?, tyres = ?, valuation_amount = ?, forced_sale_valuation_amount = ?
            WHERE id = ?
        ");
        try {
            $success = $stmt->execute([
                $valuation_date, $requestor_name, $requestor_contact_2 ?: null,
                $bank_id, $buyer, $seller ?: null, $place_of_asset, $final_car_company, $vehicle_type,
                $car_model, $registration_number, $vehicle_color ?: null, $year_of_manufacture, $date_of_registration,
                $chassis_number, $engine_number, $odometer_reading, $transmission_type, $features, $special_note ?: null,
                $engine_transmission, $body_paint, $tyres, $valuation_amount, $forced_sale_valuation_amount, $id
            ]);
        } catch (PDOException $e) {
            error_log("UPDATE failed: " . $e->getMessage());
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
            header('Location: valuations.php?action=edit&id=' . $id);
            exit;
        }

        if ($success) {
            $_SESSION['success'] = 'Valuation updated successfully.';
            header('Location: valuations.php');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to update valuation.';
            header('Location: valuations.php?action=edit&id=' . $id);
            exit;
        }
    }
} else {
    header('Location: valuations.php');
    exit;
}
?>