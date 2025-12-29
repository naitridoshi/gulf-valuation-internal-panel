<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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
                    <h1 class="m-0">Manage Car Models</h1>
                </div>
                <div class="col-sm-6">
                    <a href="manage_car_models.php?action=add" class="btn btn-primary float-sm-right">Add New Car Model</a>
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

            <?php if ($action == 'list'): ?>
                <?php
                // Removed created_at from SELECT
                $stmt = $pdo->query("SELECT id, car_company, car_model FROM car_models ORDER BY car_company");
                $car_models = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Car Models List</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Car Company</th>
                                    <th>Car Model</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($car_models)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No car models found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($car_models as $car): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($car['id']); ?></td>
                                            <td><?php echo htmlspecialchars($car['car_company']); ?></td>
                                            <td><?php echo htmlspecialchars($car['car_model']); ?></td>
                                            <td>
                                                <a href="manage_car_models.php?action=edit&id=<?php echo $car['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <a href="manage_car_models.php?action=delete&id=<?php echo $car['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this car model?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php elseif ($action == 'add' || $action == 'edit'): ?>
                <?php
                $car = [];
                if ($action == 'edit' && $id > 0) {
                    $stmt = $pdo->prepare("SELECT * FROM car_models WHERE id = ?");
                    $stmt->execute([$id]);
                    $car = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$car) {
                        $_SESSION['error'] = 'Car model not found.';
                        header('Location: manage_car_models.php');
                        exit;
                    }
                }
                ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?php echo $action == 'add' ? 'Add New Car Model' : 'Edit Car Model'; ?></h3>
                    </div>
                    <div class="card-body">
                        <form id="carModelForm" action="save_car_model.php" method="POST">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="mb-3">
                                <label for="car_company" class="form-label">Car Company <span class="text-danger">*</span></label>
                                <input type="text" name="car_company" id="car_company" class="form-control" value="<?php echo $action == 'edit' ? htmlspecialchars($car['car_company']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="car_model" class="form-label">Car Model <span class="text-danger">*</span></label>
                                <input type="text" name="car_model" id="car_model" class="form-control" value="<?php echo $action == 'edit' ? htmlspecialchars($car['car_model']) : ''; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Car Model</button>
                            <a href="manage_car_models.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            <?php elseif ($action == 'delete' && $id > 0): ?>
                <?php
                $stmt = $pdo->prepare("DELETE FROM car_models WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $_SESSION['success'] = 'Car model deleted successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to delete car model.';
                }
                header('Location: manage_car_models.php');
                exit;
                ?>
            <?php endif; ?>
        </div>
    </section>
</div>
<script src="assets/js/validate.js"></script>
<?php include 'includes/footer.php'; ?>