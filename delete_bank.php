<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'No bank ID provided.';
    header('Location: manage_banks.php');
    exit;
}

$bank_id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM banks WHERE id = ?");
if ($stmt->execute([$bank_id])) {
    $_SESSION['success'] = 'Bank deleted successfully.';
} else {
    $_SESSION['error'] = 'Failed to delete bank.';
}

header('Location: manage_banks.php');
exit;
?>