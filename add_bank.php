<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);

    if (empty($name)) {
        $_SESSION['error'] = 'Bank name is required.';
        header('Location: add_bank.php');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO banks (name) VALUES (?)");
    if ($stmt->execute([$name])) {
        $_SESSION['success'] = 'Bank added successfully.';
        header('Location: manage_banks.php');
        exit;
    } else {
        $_SESSION['error'] = 'Failed to add bank.';
        header('Location: add_bank.php');
        exit;
    }
}

$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);

include 'includes/header.php';
include 'includes/sidebar.php';
?>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Add Bank</h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Add New Bank</h3>
                    </div>
                    <div class="card-body">
                        <form action="add_bank.php" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Bank Name</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Bank</button>
                            <a href="manage_banks.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
<?php include 'includes/footer.php'; ?>// add_bank.php
<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);

    if (empty($name)) {
        $_SESSION['error'] = 'Bank name is required.';
        header('Location: add_bank.php');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO banks (name) VALUES (?)");
    if ($stmt->execute([$name])) {
        $_SESSION['success'] = 'Bank added successfully.';
        header('Location: manage_banks.php');
        exit;
    } else {
        $_SESSION['error'] = 'Failed to add bank.';
        header('Location: add_bank.php');
        exit;
    }
}

$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);

include 'includes/header.php';
include 'includes/sidebar.php';
?>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Add Bank</h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Add New Bank</h3>
                    </div>
                    <div class="card-body">
                        <form action="add_bank.php" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Bank Name</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Bank</button>
                            <a href="manage_banks.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
<?php include 'includes/footer.php'; ?>