<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = trim($_POST['action']);
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);

    if (empty($name)) {
        $_SESSION['error'] = 'Bank name is required.';
        header('Location: manage_banks.php?action=' . $action . '&id=' . $id);
        exit;
    }

    if ($action == 'add') {
        $stmt = $pdo->prepare("INSERT INTO banks (name) VALUES (?)");
        if ($stmt->execute([$name])) {
            $_SESSION['success'] = 'Bank added successfully.';
            header('Location: manage_banks.php');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to add bank.';
            header('Location: manage_banks.php?action=add');
            exit;
        }
    } else if ($action == 'edit' && $id > 0) {
        $stmt = $pdo->prepare("UPDATE banks SET name = ? WHERE id = ?");
        if ($stmt->execute([$name, $id])) {
            $_SESSION['success'] = 'Bank updated successfully.';
            header('Location: manage_banks.php');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to update bank.';
            header('Location: manage_banks.php?action=edit&id=' . $id);
            exit;
        }
    }
} else {
    header('Location: manage_banks.php');
    exit;
}
?>