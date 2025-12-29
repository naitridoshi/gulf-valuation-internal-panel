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
                    <h1 class="m-0">Manage Banks</h1>
                </div>
                <div class="col-sm-6">
                    <a href="manage_banks.php?action=add" class="btn btn-primary float-sm-right">Add New Bank</a>
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
                $stmt = $pdo->query("SELECT id, name FROM banks ORDER BY name");
                $banks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Banks List</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($banks)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No banks found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($banks as $bank): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($bank['id']); ?></td>
                                            <td><?php echo htmlspecialchars($bank['name']); ?></td>
                                            <td>
                                                <a href="manage_banks.php?action=edit&id=<?php echo $bank['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <a href="manage_banks.php?action=delete&id=<?php echo $bank['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this bank?');">Delete</a>
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
                $bank = [];
                if ($action == 'edit' && $id > 0) {
                    $stmt = $pdo->prepare("SELECT * FROM banks WHERE id = ?");
                    $stmt->execute([$id]);
                    $bank = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$bank) {
                        $_SESSION['error'] = 'Bank not found.';
                        header('Location: manage_banks.php');
                        exit;
                    }
                }
                ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?php echo $action == 'add' ? 'Add New Bank' : 'Edit Bank'; ?></h3>
                    </div>
                    <div class="card-body">
                        <form id="bankForm" action="save_bank.php" method="POST">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="mb-3">
                                <label for="name" class="form-label">Bank Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" value="<?php echo $action == 'edit' ? htmlspecialchars($bank['name']) : ''; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Bank</button>
                            <a href="manage_banks.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            <?php elseif ($action == 'delete' && $id > 0): ?>
                <?php
                $stmt = $pdo->prepare("DELETE FROM banks WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $_SESSION['success'] = 'Bank deleted successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to delete bank.';
                }
                header('Location: manage_banks.php');
                exit;
                ?>
            <?php endif; ?>
        </div>
    </section>
</div>
<script src="assets/js/validate.js"></script>
<?php include 'includes/footer.php'; ?>