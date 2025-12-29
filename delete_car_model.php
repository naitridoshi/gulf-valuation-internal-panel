<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'No car model ID provided.';
    header('Location: manage_car_models.php');
    exit;
}

$car_id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM car_models WHERE id = ?");
if ($stmt->execute([$car_id])) {
    $_SESSION['success'] = 'Car model deleted successfully.';
} else {
    $_SESSION['error'] = 'Failed to delete car model.';
}

header('Location: manage_car_models.php');
exit;
?>