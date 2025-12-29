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

$stmt = $pdo->prepare("SELECT * FROM banks WHERE id = ?");
$stmt->execute([$bank_id]);
$bank = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bank) {
    $_SESSION['error'] = 'Bank not found.';
    header('Location: manage_banks.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);

    if (empty($name)) {
        $_SESSION['error'] = 'Bank name is required.';
        header('Location: edit_bank.php?id=' . $bank_id);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE banks SET name = ? WHERE id = ?");
    if ($stmt->execute([$name, $bank_id])) {
        $_SESSION['success'] = 'Bank updated successfully.';
        header('Location: manage_banks.php');
        exit;
    } else {
        $_SESSION['error'] = 'Failed to update bank.';
        header('Location: edit_bank.php?id=' . $bank_id);
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
                        <h1 class="m-0">Edit Bank</h1>
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
                        <h3 class="card-title">Edit Bank Details</h3>
                    </div>
                    <div class="card-body">
                        <form action="edit_bank.php?id=<?php echo $bank_id; ?>" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Bank Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($bank['name']); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Bank</button>
                            <a href="manage_banks.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
<?php include 'includes/footer.php'; ?>