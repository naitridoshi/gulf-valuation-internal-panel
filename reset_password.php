<?php
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);

    if (empty($username)) {
        $_SESSION['error'] = 'Please enter a username.';
        header('Location: forgot_password.php');
        exit;
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $new_password = 'password123';
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
        if ($update_stmt->execute([$hashed_password, $username])) {
            $_SESSION['success'] = 'Password has been reset to "password @123". Please login.';
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to reset password. Please try again.';
            header('Location: forgot_password.php');
            exit;
        }
    } else {
        $_SESSION['error'] = 'Username not found.';
        header('Location: forgot_password.php');
        exit;
    }
} else {
    header('Location: forgot_password.php');
    exit;
}
?>