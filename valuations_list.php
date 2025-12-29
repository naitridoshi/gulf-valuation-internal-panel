<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get total records
$stmt = $pdo->query("SELECT COUNT(*) as total FROM valuations");
$total_records = $stmt->fetch()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch valuations for the current page
$stmt = $pdo->prepare("SELECT id, ref_number, valuation_date, valuation_amount FROM valuations ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', (int)$records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$valuations = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                        <h1 class="m-0">Valuations List</h1>
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
                        <h3 class="card-title">All Valuations</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>REF Number</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($valuations)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No valuations found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($valuations as $val): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($val['ref_number']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($val['valuation_date'])); ?></td>
                                            <td>R.O. <?php echo number_format($val['valuation_amount'], 3); ?></td>
                                            <td><a href="valuation_report.php?id=<?php echo $val['id']; ?>" class="btn btn-sm btn-info">View Report</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <!-- Pagination -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mt-3">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="valuations_list.php?page=<?php echo $page - 1; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="valuations_list.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="valuations_list.php?page=<?php echo $page + 1; ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </section>
    </div>
<?php include 'includes/footer.php'; ?>