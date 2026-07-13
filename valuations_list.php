<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $records_per_page;

// Build search condition
$search_condition = '';
$search_params = [];
if ($search !== '') {
    $search_condition = "WHERE (ref_number LIKE ? OR buyer LIKE ?)";
    $search_params = ["%$search%", "%$search%"];
}

// Get total records
$count_sql = "SELECT COUNT(*) as total FROM valuations $search_condition";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($search_params);
$total_records = $count_stmt->fetch()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch valuations for the current page
$data_sql = "SELECT id, ref_number, buyer, valuation_date, valuation_amount
     FROM valuations
     $search_condition
     ORDER BY created_at DESC,
              CAST(SUBSTRING_INDEX(ref_number, '/', -1) AS UNSIGNED) DESC
     LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($data_sql);
$param_offset = 1;
foreach ($search_params as $val) {
    $stmt->bindValue($param_offset++, $val, PDO::PARAM_STR);
}
$stmt->bindValue($param_offset++, (int)$records_per_page, PDO::PARAM_INT);
$stmt->bindValue($param_offset,   (int)$offset,           PDO::PARAM_INT);
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">All Valuations</h3>
                    </div>
                    <div class="card-body">
                        <!-- Search bar -->
                        <form method="GET" action="valuations_list.php" class="mb-3">
                            <div class="input-group">
                                <input type="text" name="search" id="valuation_search" class="form-control"
                                       placeholder="Search by REF number or Buyer name…"
                                       value="<?php echo htmlspecialchars($search); ?>"
                                       autocomplete="off">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                                    <?php if ($search): ?>
                                        <a href="valuations_list.php" class="btn btn-secondary">Clear</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>REF Number</th>
                                    <th>Buyer</th>
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
                                            <td><?php echo htmlspecialchars($val['buyer'] ?? ''); ?></td>
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
                                <?php
                                $page_base = 'valuations_list.php?page=';
                                $search_suffix = $search !== '' ? '&search=' . urlencode($search) : '';
                                ?>
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo $page_base . ($page - 1) . $search_suffix; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo $page_base . $i . $search_suffix; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo $page_base . ($page + 1) . $search_suffix; ?>">Next</a>
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
