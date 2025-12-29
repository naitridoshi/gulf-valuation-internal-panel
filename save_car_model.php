<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = trim($_POST['action']);
    $id = (int)$_POST['id'];
    $car_company = trim($_POST['car_company']);
    $car_model = trim($_POST['car_model']);

    if (empty($car_company) || empty($car_model)) {
        $_SESSION['error'] = 'Car company and model are required.';
        header('Location: manage_car_models.php?action=' . $action . '&id=' . $id);
        exit;
    }

    if ($action == 'add') {
        $stmt = $pdo->prepare("INSERT INTO car_models (car_company, car_model) VALUES (?, ?)");
        if ($stmt->execute([$car_company, $car_model])) {
            $_SESSION['success'] = 'Car model added successfully.';
            header('Location: manage_car_models.php');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to add car model.';
            header('Location: manage_car_models.php?action=add');
            exit;
        }
    } else if ($action == 'edit' && $id > 0) {
        $stmt = $pdo->prepare("UPDATE car_models SET car_company = ?, car_model = ? WHERE id = ?");
        if ($stmt->execute([$car_company, $car_model, $id])) {
            $_SESSION['success'] = 'Car model updated successfully.';
            header('Location: manage_car_models.php');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to update car model.';
            header('Location: manage_car_models.php?action=edit&id=' . $id);
            exit;
        }
    }
} else {
    header('Location: manage_car_models.php');
    exit;
}
?>