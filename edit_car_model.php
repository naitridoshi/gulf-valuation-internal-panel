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

$stmt = $pdo->prepare("SELECT * FROM car_models WHERE id = ?");
$stmt->execute([$car_id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    $_SESSION['error'] = 'Car model not found.';
    header('Location: manage_car_models.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $car_company = trim($_POST['car_company']);
    $car_model = trim($_POST['car_model']);

    if (empty($car_company) || empty($car_model)) {
        $_SESSION['error'] = 'Car company and model are required.';
        header('Location: edit_car_model.php?id=' . $car_id);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE car_models SET car_company = ?, car_model = ? WHERE id = ?");
    if ($stmt->execute([$car_company, $car_model, $car_id])) {
        $_SESSION['success'] = 'Car model updated successfully.';
        header('Location: manage_car_models.php');
        exit;
    } else {
        $_SESSION['error'] = 'Failed to update car model.';
        header('Location: edit_car_model.php?id=' . $car_id);
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
                        <h1 class="m-0">Edit Car Model</h1>
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
                        <h3 class="card-title">Edit Car Model Details</h3>
                    </div>
                    <div class="card-body">
                        <form action="edit_car_model.php?id=<?php echo $car_id; ?>" method="POST">
                            <div class="mb-3">
                                <label for="car_company" class="form-label">Car Company</label>
                                <input type="text" name="car_company" id="car_company" class="form-control" value="<?php echo htmlspecialchars($car['car_company']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="car_model" class="form-label">Car Model</label>
                                <input type="text" name="car_model" id="car_model" class="form-control" value="<?php echo htmlspecialchars($car['car_model']); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Car Model</button>
                            <a href="manage_car_models.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
<?php include 'includes/footer.php'; ?>