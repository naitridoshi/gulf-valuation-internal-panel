<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    if ($action == 'update') {
        $username = trim($_POST['username']);
        if (empty($username)) {
            $_SESSION['error'] = 'Username is required.';
            header('Location: manage_users.php');
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $user_id]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Username already exists.';
            header('Location: manage_users.php');
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
        if ($stmt->execute([$username, $user_id])) {
            $_SESSION['success'] = 'Username updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update username.';
        }
    } elseif ($action == 'reset_password') {
        $new_password = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$new_password, $user_id])) {
            $_SESSION['success'] = 'Password reset to "password123" successfully.';
        } else {
            $_SESSION['error'] = 'Failed to reset password.';
        }
    }

    header('Location: manage_users.php');
    exit;
} else {
    header('Location: manage_users.php');
    exit;
}
?>