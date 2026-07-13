<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $delete_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($delete_id > 0) {
        $stmt = $pdo->prepare("SELECT ref_number FROM valuations WHERE id = ?");
        $stmt->execute([$delete_id]);
        $ref_number_to_delete = $stmt->fetchColumn();

        $stmt = $pdo->prepare("DELETE FROM valuations WHERE id = ?");
        if ($stmt->execute([$delete_id])) {
            if ($ref_number_to_delete) {
                $ref_parts = explode('/', $ref_number_to_delete);
                $seq_part = end($ref_parts);
                $year_part = count($ref_parts) >= 2 ? $ref_parts[count($ref_parts) - 2] : '';
                $current_year = date('y');

                if (ctype_digit($seq_part) && $year_part === $current_year) {
                    $seq_value = (int)$seq_part;
                    if ($seq_value > 0) {
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM valuations WHERE ref_number = ?");
                        $stmt->execute([$ref_number_to_delete]);
                        $ref_exists = (int)$stmt->fetchColumn() > 0;

                        if (!$ref_exists) {
                            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
                            $stmt->execute(['ref_number', $seq_value, $seq_value]);
                        }
                    }
                }
            }

            $_SESSION['success'] = 'Valuation deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete valuation.';
        }
    } else {
        $_SESSION['error'] = 'Invalid valuation id.';
    }

    header('Location: valuations.php');
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch data for dropdowns
$requestors = $pdo->query("SELECT name FROM requestors ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$banks = $pdo->query("SELECT id, name FROM banks ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$car_companies = $pdo->query("SELECT DISTINCT car_company FROM car_models ORDER BY car_company")->fetchAll(PDO::FETCH_COLUMN);
$car_models = $pdo->query("SELECT DISTINCT car_model FROM car_models ORDER BY car_model")->fetchAll(PDO::FETCH_COLUMN);
$places = $pdo->query("SELECT name FROM places ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$vehicle_types = $pdo->query("SELECT type FROM vehicle_types ORDER BY type")->fetchAll(PDO::FETCH_COLUMN);
$past_buyers = $pdo->query("SELECT DISTINCT buyer FROM valuations WHERE buyer IS NOT NULL AND buyer != '' ORDER BY buyer")->fetchAll(PDO::FETCH_COLUMN);

// Generate REF Number for add action
$year = date('y');
$ref_number_seq = $settings['ref_number'] ?? 1;
$ref_number = ($settings['ref_prefix'] ?? 'GAS/VAL/') . $year . '/' . sprintf("%04d", $ref_number_seq);

// Generate years for manufacture
$current_year = date('Y');
$years = range($current_year, $current_year - 50);

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
                    <h1 class="m-0">Valuations</h1>
                </div>
                <div class="col-sm-6 d-flex justify-content-end">
                    <?php if ($action == 'list'): ?>
                        <a href="export_csv.php" class="btn btn-primary mr-2">Export to CSV</a>
                    <?php endif; ?>
                    <?php if ($action == 'add'): ?>
                        <a href="valuations.php?action=add" class="btn btn-primary disabled" tabindex="-1" aria-disabled="true">Add New Valuation</a>
                    <?php else: ?>
                        <a href="valuations.php?action=add" class="btn btn-primary">Add New Valuation</a>
                    <?php endif; ?>
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
                $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM valuations $search_condition");
                $count_stmt->execute($search_params);
                $total_records = $count_stmt->fetch()['total'];
                $total_pages = ceil($total_records / $records_per_page);

                // Fetch valuations for the current page
                $list_sql = "SELECT id, ref_number, buyer, valuation_date, valuation_amount
                     FROM valuations
                     $search_condition
                     ORDER BY valuation_date DESC,
                              CAST(SUBSTRING_INDEX(ref_number, '/', -1) AS UNSIGNED) DESC
                     LIMIT ? OFFSET ?";
                $stmt = $pdo->prepare($list_sql);
                $param_offset = 1;
                foreach ($search_params as $val) {
                    $stmt->bindValue($param_offset++, $val, PDO::PARAM_STR);
                }
                $stmt->bindValue($param_offset++, (int)$records_per_page, PDO::PARAM_INT);
                $stmt->bindValue($param_offset,   (int)$offset,            PDO::PARAM_INT);
                $stmt->execute();
                $valuations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Valuations List</h3>
                    </div>
                    <div class="card-body">
                        <!-- Search bar -->
                        <form method="GET" action="valuations.php" class="mb-3">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search by REF number or Buyer name…"
                                       value="<?php echo htmlspecialchars($search); ?>"
                                       autocomplete="off">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                                    <?php if ($search): ?>
                                        <a href="valuations.php" class="btn btn-secondary">Clear</a>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($valuations)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center"><?php echo $search ? 'No valuations match your search.' : 'No valuations found.'; ?></td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($valuations as $val): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($val['ref_number']); ?></td>
                                            <td><?php echo htmlspecialchars($val['buyer'] ?? ''); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($val['valuation_date'])); ?></td>
                                            <td>R.O. <?php echo number_format($val['valuation_amount'], 3); ?></td>
                                            <td>
                                                <a href="valuations.php?action=edit&id=<?php echo $val['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <form method="POST" action="valuations.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this valuation?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $val['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                                <a href="valuation_report.php?id=<?php echo $val['id']; ?>" class="btn btn-sm btn-info ">View Report</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <!-- Pagination -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mt-3">
                                <?php
                                $page_base = 'valuations.php?page=';
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
            <?php elseif ($action == 'add' || $action == 'edit'): ?>
                <?php
                $valuation = [];
                if ($action == 'edit' && $id > 0) {
                    $stmt = $pdo->prepare("SELECT * FROM valuations WHERE id = ?");
                    $stmt->execute([$id]);
                    $valuation = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$valuation) {
                        $_SESSION['error'] = 'Valuation not found.';
                        header('Location: valuations.php');
                        exit;
                    }
                }

                // Generate REF Number for add action
                if ($action == 'add') {
                    $year = date('y');
                    $ref_number_seq = $settings['ref_number'] ?? 1;
                    $ref_number = ($settings['ref_prefix'] ?? 'GAS/VAL/') . $year . '/' . sprintf("%04d", $ref_number_seq);
                } else {
                    $ref_number = $valuation['ref_number'];
                }
                ?>
                <div class="card">
                    <div class="card-body">
                        <div id="formErrors" class="alert alert-danger d-none"></div>
                        <form id="valuationForm" action="save_valuation.php" method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="ref_number" class="form-label">REF Number <span class="text-danger">*</span></label>
                                    <input type="text" name="ref_number" id="ref_number" class="form-control" value="<?php echo htmlspecialchars($ref_number); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="valuation_date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="valuation_date" id="valuation_date" class="form-control" value="<?php echo $action == 'edit' ? $valuation['valuation_date'] : date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            <hr class="mb-3">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="requestor_name" class="form-label">WE THE UNDERSIGNED AT THE REQUEST OF <span class="text-danger">*</span></label>
                                    <div class="autocomplete-wrap" style="position:relative;">
                                        <input type="text" name="requestor_name" id="requestor_name" class="form-control" placeholder="Enter applicant name" value="<?php echo $action == 'edit' ? htmlspecialchars($valuation['requestor_name']) : ''; ?>" required autocomplete="off">
                                        <div id="requestor_dropdown" class="ac-dropdown" style="display:none;"></div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="requestor_contact_2" class="form-label">Requestor Contact</label>
                                    <input type="text" name="requestor_contact_2" id="requestor_contact_2" class="form-control" placeholder="Enter contact number" value="<?php echo $action == 'edit' ? htmlspecialchars($valuation['requestor_contact_2']) : ''; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="bank_name" class="form-label">TO AVAIL FINANCE FROM <span class="text-danger">*</span></label>
                                    <select name="bank_id" id="bank_id" class="form-control" required>
                                        <option value="">Select Bank</option>
                                        <?php foreach ($banks as $bank): ?>
                                            <option value="<?php echo htmlspecialchars($bank['id']); ?>" <?php echo $action == 'edit' && isset($valuation['bank_id']) && $valuation['bank_id'] == $bank['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($bank['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                            <label for="place_of_asset" class="form-label">Place of Asset <span class="text-danger">*</span></label>
                                    <input type="text" name="place_of_asset" id="place_of_asset" class="form-control" list="place_list" placeholder="Enter or select place of asset" value="<?php echo $action == 'edit' ? htmlspecialchars($valuation['place_of_asset']) : ''; ?>" required>
                                    <datalist id="place_list">
                                        <?php foreach ($places as $place): ?>
                                            <option value="<?php echo htmlspecialchars($place); ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>

                            </div>
                            <h4 class="mt-4">WE NOW REPORT AS FOLLOWS:</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="buyer" class="form-label">Name of the Applicant (Buyer) <span class="text-danger">*</span></label>
                                    <div class="autocomplete-wrap" style="position:relative;">
                                        <input type="text" name="buyer" id="buyer" class="form-control" placeholder="Search or enter buyer name" value="<?php echo $action == 'edit' ? htmlspecialchars($valuation['buyer'] ?? $valuation['requestor_name']) : ''; ?>" required autocomplete="off">
                                        <div id="buyer_dropdown" class="ac-dropdown" style="display:none; position:absolute; z-index:1000; background:#fff; border:1px solid #ccc; width:100%; max-height:200px; overflow-y:auto;"></div>
                                    </div>
                                    <small class="form-text text-muted">Auto-filled from applicant name — override if different.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="seller" class="form-label">Seller</label>
                                    <input type="text" name="seller" id="seller" class="form-control" placeholder="Enter seller name" value="<?php echo $action == 'edit' ? htmlspecialchars($valuation['seller']) : ''; ?>">
                                </div>
                            </div>
                            <h4 class="mt-4">DETAILS OF VEHICLE</h4>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="car_company_select" class="form-label">Make <span class="text-danger">*</span></label>
                                    <select name="car_company_select" id="car_company_select" class="form-control form-control-sm" style="min-width: 0; width: 100%;" required>
                                        <option value="">Select Make</option>
                                        <?php foreach ($car_companies as $company): ?>
                                            <option value="<?php echo htmlspecialchars($company); ?>" <?php echo $action == 'edit' && $valuation['car_company'] == $company ? 'selected' : ''; ?>><?php echo htmlspecialchars($company); ?></option>
                                        <?php endforeach; ?>
                                        <option value="new">Add New Make</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="vehicle_type" class="form-label">Type <span class="text-danger">*</span></label>
                                    <input type="text" name="vehicle_type" id="vehicle_type" class="form-control form-control-sm" style="min-width: 0; width: 100%;" list="vehicle_type_list" placeholder="Enter or select vehicle type" value="<?php echo $action == 'edit' ? htmlspecialchars($valuation['vehicle_type']) : ''; ?>" required>
                                    <datalist id="vehicle_type_list">
                                        <?php foreach ($vehicle_types as $type): ?>
                                            <option value="<?php echo htmlspecialchars($type); ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="car_model" class="form-label">Model Year <span class="text-danger">*</span></label>
                                    <input type="text" name="car_model" id="car_model" class="form-control form-control-sm" style="min-width: 0; width: 100%;" list="car_model_list" placeholder="Enter or select vehicle model" value="<?php echo $action == 'edit' ? htmlspecialchars($valuation['car_model']) : ''; ?>" required>
                                    <datalist id="car_model_list">
                                        <?php foreach ($car_models as $model): ?>
                                            <option value="<?php echo htmlspecialchars($model); ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="registration_number" class="form-label">Registration Number <span class="text-danger">*</span></label>
                                    <input type="text" name="registration_number" id="registration_number" class="form-control form-control-sm" style="min-width: 0; width: 100%;" placeholder="Enter registration number" value="<?php echo $action == 'edit' ? htmlspecialchars($valuation['registration_number']) : ''; ?>" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="vehicle_color" class="form-label">Vehicle Color</label>
                                    <input type="text" name="vehicle_color" id="vehicle_color" class="form-control form-control-sm" style="min-width: 0; width: 100%;" placeholder="Enter vehicle color" value="<?php echo $action == 'edit' ? htmlspecialchars($valuation['vehicle_color'] ?? '') : ''; ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="chassis_number" class="form-label">Chassis Number <span class="text-danger">*</span></label>
                                    <input type="text" name="chassis_number" id="chassis_number" class="form-control form-control-sm" style="min-width: 0; width: 100%;" placeholder="Enter chassis number" value="<?php echo $action == 'edit' ? htmlspecialchars($valuation['chassis_number']) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                 <div class="col-md-4 mb-3">
                                    <label for="engine_number" class="form-label">Engine Number <span class="text-danger">*</span></label>
                                    <input type="text" name="engine_number" id="engine_number" class="form-control form-control-sm" style="min-width: 0; width: 100%;" placeholder="Enter engine number" value="<?php echo $action == 'edit' ? htmlspecialchars($valuation['engine_number']) : ''; ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="year_of_manufacture" class="form-label">Year of Manufacture <span class="text-danger">*</span></label>
                                    <select name="year_of_manufacture" id="year_of_manufacture" class="form-control form-control-sm" style="min-width: 0; width: 100%;" required>
                                        <option value="">Select Year</option>
                                        <?php foreach ($years as $year): ?>
                                            <option value="<?php echo $year; ?>" <?php echo $action == 'edit' && $valuation['year_of_manufacture'] == $year ? 'selected' : ''; ?>><?php echo $year; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="date_of_registration" class="form-label">Date of Registration <span class="text-danger">*</span></label>
                                    <input type="date" name="date_of_registration" id="date_of_registration" class="form-control form-control-sm" style="min-width: 0; width: 100%;" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $action == 'edit' ? htmlspecialchars($valuation['date_of_registration']) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="odometer_reading" class="form-label">Odometer Reading <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="odometer_reading_value" id="odometer_reading_value" class="form-control form-control-sm" placeholder="Enter reading" value="<?php 
                                            if ($action == 'edit' && !empty($valuation['odometer_reading'])) {
                                                $reading = htmlspecialchars($valuation['odometer_reading']);
                                                echo preg_replace('/\s*(KM|MILES|NOT WORKING)\s*$/i', '', $reading);
                                            }
                                        ?>" required>
                                        <select name="odometer_unit" id="odometer_unit" class="form-control form-control-sm" style="max-width: 140px;" required>
                                            <option value="KM" <?php 
                                                if ($action == 'edit' && !empty($valuation['odometer_reading']) && stripos($valuation['odometer_reading'], 'KM') !== false) echo 'selected';
                                                elseif ($action == 'add') echo 'selected';
                                            ?>>KM</option>
                                            <option value="MILES" <?php echo $action == 'edit' && !empty($valuation['odometer_reading']) && stripos($valuation['odometer_reading'], 'MILES') !== false ? 'selected' : ''; ?>>MILES</option>
                                            <option value="HOURS" <?php echo $action == 'edit' && !empty($valuation['odometer_reading']) && stripos($valuation['odometer_reading'], 'HOURS') !== false ? 'selected' : ''; ?>>HOURS</option>
                                            <option value="NOT WORKING" <?php echo $action == 'edit' && !empty($valuation['odometer_reading']) && stripos($valuation['odometer_reading'], 'NOT WORKING') !== false ? 'selected' : ''; ?>>NOT WORKING</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="transmission_type" class="form-label">Transmission Type <span class="text-danger">*</span></label>
                                    <select name="transmission_type" id="transmission_type" class="form-control form-control-sm" style="min-width: 0; width: 100%;" required>
                                        <option value="">Select Transmission Type</option>
                                        <option value="AUTOMATIC" <?php echo $action == 'edit' && isset($valuation['transmission_type']) && $valuation['transmission_type'] == 'AUTOMATIC' ? 'selected' : ''; ?>>AUTOMATIC</option>
                                        <option value="MANUAL" <?php echo $action == 'edit' && isset($valuation['transmission_type']) && $valuation['transmission_type'] == 'MANUAL' ? 'selected' : ''; ?>>MANUAL</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="features" class="form-label">Features <span class="text-danger">*</span></label>
                                    <textarea name="features" id="features" class="form-control" style="min-width: 0; width: 100%; height: 120px; resize: vertical;" rows="6" placeholder="Enter vehicle features" required><?php echo $action == 'edit' ? htmlspecialchars($valuation['features']) : 'FULL OPTION- CD, MP3, POWER WINDOW, SENSOR, BLUETOOTH, SUNROOF, LEATHER SEATS, NAVIGATOR, AIR BAGS & ALLOY WHEELS'; ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="special_note" class="form-label">Special Note (if any)</label>
                                    <textarea name="special_note" id="special_note" class="form-control" style="min-width: 0; width: 100%; height: 120px; resize: vertical;" rows="6" placeholder="Enter any special notes"><?php echo $action == 'edit' ? htmlspecialchars($valuation['special_note']) : 'REFURBISHED, PROCURED FROM UAE, VALUATION BASED ON OMAN DEALER PRICING.'; ?></textarea>
                                </div>
                            </div>
                            <h4 class="mt-4">OBSERVATION</h4>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="engine_transmission" class="form-label">Engine & Transmission <span class="text-danger">*</span></label>
                                    <input type="text" name="engine_transmission" id="engine_transmission" class="form-control form-control-sm" style="min-width: 0; width: 100%;" value="<?php echo $action == 'edit' ? htmlspecialchars($valuation['engine_transmission']) : 'OK'; ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="body_paint" class="form-label">Body & Paint <span class="text-danger">*</span></label>
                                    <input type="text" name="body_paint" id="body_paint" class="form-control form-control-sm" style="min-width: 0; width: 100%;" value="<?php echo $action == 'edit' ? htmlspecialchars($valuation['body_paint']) : 'OK'; ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="tyres" class="form-label">Tyres <span class="text-danger">*</span></label>
                                    <input type="text" name="tyres" id="tyres" class="form-control form-control-sm" style="min-width: 0; width: 100%;" value="<?php echo $action == 'edit' ? htmlspecialchars($valuation['tyres']) : 'OK'; ?>" required>
                                </div>
                            </div>
                            <h4 class="mt-4">Valuation Statement</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="valuation_amount" class="form-label">Valuation Amount (R.O.) <span class="text-danger">*</span></label>
                                    <input type="number" name="valuation_amount" id="valuation_amount" class="form-control" step="0.001" min="0.001" placeholder="Enter valuation amount (e.g., 6250)" value="<?php echo $action == 'edit' ? number_format($valuation['valuation_amount'], 3, '.', '') : ''; ?>" required>
                                    <small id="amount_in_words" class="form-text text-muted"></small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="forced_sale_valuation_amount" class="form-label">
                                        Constrained Realization Circumstances (FMV) (R.O.)
                                    </label>
                                    <input 
                                        type="number" 
                                        name="forced_sale_valuation_amount" 
                                        id="forced_sale_valuation_amount" 
                                        class="form-control" 
                                        step="0.001" 
                                        placeholder="Enter Forced Sale valuation amount (e.g., 6000)" 
                                        value="<?php echo ($action == 'edit' && !empty($valuation['forced_sale_valuation_amount'])) 
                                            ? number_format($valuation['forced_sale_valuation_amount'], 3, '.', '') 
                                            : ''; ?>">
                                    <small id="forced_sale_valuation_amount_in_words" class="form-text text-muted"></small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="invoice_amount" class="form-label">Invoice Amount (R.O.) <span class="text-danger">*</span></label>
                                    <input type="number" name="invoice_amount" id="invoice_amount" class="form-control" step="0.001" min="0.001" placeholder="Enter invoice amount (e.g., 100.000)" value="<?php echo ($action == 'edit' && isset($valuation['invoice_amount'])) ? number_format($valuation['invoice_amount'], 3, '.', '') : ''; ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="invoice_vat_display" class="form-label">VAT 5% (R.O.)</label>
                                    <input type="text" id="invoice_vat_display" class="form-control" value="<?php echo ($action == 'edit' && isset($valuation['invoice_vat'])) ? number_format($valuation['invoice_vat'], 3, '.', '') : ''; ?>" disabled>
                                    <input type="hidden" name="invoice_vat" id="invoice_vat" value="<?php echo ($action == 'edit' && isset($valuation['invoice_vat'])) ? number_format($valuation['invoice_vat'], 3, '.', '') : ''; ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="invoice_total_display" class="form-label">Total Invoice Amount (R.O.)</label>
                                    <input type="text" id="invoice_total_display" class="form-control" value="<?php echo ($action == 'edit' && isset($valuation['invoice_total'])) ? number_format($valuation['invoice_total'], 3, '.', '') : ''; ?>" disabled>
                                    <input type="hidden" name="invoice_total" id="invoice_total" value="<?php echo ($action == 'edit' && isset($valuation['invoice_total'])) ? number_format($valuation['invoice_total'], 3, '.', '') : ''; ?>">
                                </div>
                            </div>
                            <h4 class="mt-4"><?php echo htmlspecialchars($settings['company_signature'] ?? 'For GULF ADJUSTERS, SURVEYORS & SERVICES LLC'); ?></h4>
                            <div class="mb-3">
                                <p><?php echo nl2br(htmlspecialchars($settings['valuation_statement'] ?? 'Based on our observation, age, maintenance and performance of the vehicle, we are of the opinion that the present market value of the above vehicle with the existing specification on “as is where is conditions” is approximately')); ?></p>
                                <p><?php echo nl2br(htmlspecialchars($settings['report_disclaimer'] ?? 'This report is true to the best of our knowledge and is issued without prejudice subject to the valuation as per condition of the vehicle at the date, place and time of our inspection.')); ?></p>
                                <p><?php echo nl2br(htmlspecialchars($settings['valuation_footer'] ?? "Authorized Signatory\nNote: Refer Basis of Valuation, Specific Assumptions, Saving Clauses forming part of this valuation refer overleaf")); ?></p>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg">Save Valuation</button>
                            <a href="valuations.php" class="btn btn-secondary btn-lg">Cancel</a>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
<script>
/* ── Autocomplete dropdown styles ───────────────────────────────────── */
(function(){
    var s = document.createElement('style');
    s.textContent = [
        '.ac-dropdown { position:absolute; z-index:9999; background:#fff;',
        '  border:1px solid #aaa; border-top:none; width:100%;',
        '  max-height:220px; overflow-y:auto; border-radius:0 0 4px 4px;',
        '  box-shadow:0 4px 10px rgba(0,0,0,.15); }',
        '.ac-item { padding:8px 12px; cursor:pointer; font-size:14px; }',
        '.ac-item:hover, .ac-item.ac-active { background:#e9f0ff; color:#003399; }'
    ].join('\n');
    document.head.appendChild(s);
})();

document.addEventListener('DOMContentLoaded', function() {

    // ── Data arrays from PHP ───────────────────────────────────────────────
    var REQUESTORS  = <?php echo json_encode(array_values($requestors)); ?>;
    var PAST_BUYERS = <?php echo json_encode(array_values($past_buyers)); ?>;

    // ── Generic autocomplete builder ───────────────────────────────────────
    function makeAutocomplete(inputEl, dropEl, dataArr, onSelect) {
        var activeIdx = -1;

        function show(items) {
            dropEl.innerHTML = '';
            activeIdx = -1;
            if (!items.length) { dropEl.style.display = 'none'; return; }
            items.forEach(function(item, i) {
                var d = document.createElement('div');
                d.className = 'ac-item';
                d.textContent = item;
                d.addEventListener('mousedown', function(e) {
                    e.preventDefault(); // keep focus on input
                    inputEl.value = item;
                    dropEl.style.display = 'none';
                    if (onSelect) onSelect(item);
                    inputEl.dispatchEvent(new Event('change'));
                    inputEl.classList.remove('is-invalid');
                    inputEl.classList.add('is-valid');
                });
                dropEl.appendChild(d);
            });
            dropEl.style.display = 'block';
        }

        inputEl.addEventListener('input', function() {
            var q = this.value.trim().toLowerCase();
            if (!q) {
                // show all when field is focused and empty
                show(dataArr.slice(0, 20));
                return;
            }
            var matches = dataArr.filter(function(v) {
                return v.toLowerCase().indexOf(q) !== -1;
            });
            show(matches.slice(0, 20));
        });

        inputEl.addEventListener('focus', function() {
            var q = this.value.trim().toLowerCase();
            var items = q
                ? dataArr.filter(function(v){ return v.toLowerCase().indexOf(q) !== -1; }).slice(0,20)
                : dataArr.slice(0, 20);
            show(items);
        });

        // Keyboard navigation
        inputEl.addEventListener('keydown', function(e) {
            var items = dropEl.querySelectorAll('.ac-item');
            if (!items.length) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIdx = Math.min(activeIdx + 1, items.length - 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIdx = Math.max(activeIdx - 1, 0);
            } else if (e.key === 'Enter' && activeIdx >= 0) {
                e.preventDefault();
                items[activeIdx].dispatchEvent(new MouseEvent('mousedown'));
                return;
            } else if (e.key === 'Escape') {
                dropEl.style.display = 'none';
                activeIdx = -1;
                return;
            }
            items.forEach(function(el, i) {
                el.classList.toggle('ac-active', i === activeIdx);
                if (i === activeIdx) el.scrollIntoView({ block: 'nearest' });
            });
        });

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!inputEl.contains(e.target) && !dropEl.contains(e.target)) {
                dropEl.style.display = 'none';
                activeIdx = -1;
            }
        });
    }

    // ── Requestor autocomplete ─────────────────────────────────────────────
    var requestorEl  = document.getElementById('requestor_name');
    var requestorDrop= document.getElementById('requestor_dropdown');
    var buyerEl      = document.getElementById('buyer');
    var buyerDrop    = document.getElementById('buyer_dropdown');

    if (requestorEl && requestorDrop) {
        makeAutocomplete(requestorEl, requestorDrop, REQUESTORS);
    }

    // ── Buyer autocomplete + auto-fill from requestor ──────────────────────
    if (buyerEl && buyerDrop) {
        makeAutocomplete(buyerEl, buyerDrop, PAST_BUYERS);
    }

    if (requestorEl && buyerEl) {
        // In add mode, buyer starts empty — always sync until user picks from buyer dropdown
        var buyerManual = buyerEl.value !== '' && <?php echo $action === 'edit' ? 'true' : 'false'; ?>;

        function syncBuyer() {
            if (!buyerManual) {
                buyerEl.value = requestorEl.value;
                buyerEl.classList.remove('is-invalid');
                if (buyerEl.value.trim()) buyerEl.classList.add('is-valid');
            }
        }
        syncBuyer();
        requestorEl.addEventListener('input',  syncBuyer);
        requestorEl.addEventListener('change', syncBuyer);

        // If user types directly in buyer field (different from requestor), stop syncing
        buyerEl.addEventListener('input', function() {
            if (buyerEl.value !== requestorEl.value) {
                buyerManual = true;
            } else {
                buyerManual = false;
            }
        });
    }

    // ── Invoice amount calculator ──────────────────────────────────────────
    var invoiceAmount    = document.getElementById('invoice_amount');
    var invoiceVatDisp   = document.getElementById('invoice_vat_display');
    var invoiceVat       = document.getElementById('invoice_vat');
    var invoiceTotalDisp = document.getElementById('invoice_total_display');
    var invoiceTotal     = document.getElementById('invoice_total');
    if (invoiceAmount && invoiceVatDisp && invoiceVat && invoiceTotalDisp && invoiceTotal) {
        var calcInvoice = function() {
            var amt = parseFloat(invoiceAmount.value);
            if (isNaN(amt)) {
                invoiceVatDisp.value = invoiceVat.value = invoiceTotalDisp.value = invoiceTotal.value = '';
                return;
            }
            var vat   = (amt * 0.05).toFixed(3);
            var total = (amt + parseFloat(vat)).toFixed(3);
            invoiceVatDisp.value = invoiceVat.value = vat;
            invoiceTotalDisp.value = invoiceTotal.value = total;
        };
        if (!invoiceVatDisp.value && invoiceAmount.value) calcInvoice();
        invoiceAmount.addEventListener('input', calcInvoice);
    }

    // ── Live valuation amount in words ────────────────────────────────────
    var valuationAmount = document.getElementById('valuation_amount');
    var amountInWords   = document.getElementById('amount_in_words');
    if (valuationAmount && amountInWords) {
        valuationAmount.addEventListener('input', function() {
            var v = parseFloat(this.value);
            amountInWords.textContent = v > 0 ? '(Rials Omani ' + numberToWords(v) + ')' : '';
        });
    }

    // ── Add-new car make prompt ────────────────────────────────────────────
    var carSelect = document.getElementById('car_company_select');
    if (carSelect) {
        carSelect.addEventListener('change', function() {
            if (this.value === 'new') {
                var newMake = prompt('Enter new vehicle make:');
                if (newMake && newMake.trim()) {
                    var exists = Array.from(carSelect.options).some(function(o) {
                        return o.value.toLowerCase() === newMake.trim().toLowerCase();
                    });
                    if (!exists) {
                        var opt = document.createElement('option');
                        opt.value = opt.text = newMake.trim();
                        carSelect.add(opt, carSelect.options.length - 1);
                    }
                    carSelect.value = newMake.trim();
                } else {
                    carSelect.value = '';
                }
            }
        });
    }

    // ── AJAX form submission ───────────────────────────────────────────────
    var form       = document.getElementById('valuationForm');
    var formErrors = document.getElementById('formErrors');
    if (!form) return;

    var requiredFields = form.querySelectorAll('input[required], select[required], textarea[required]');

    function validateField(field) {
        var valid = field.type === 'number'
            ? (field.value !== '' && parseFloat(field.value) > 0)
            : field.tagName === 'SELECT'
                ? field.value !== ''
                : field.value.trim() !== '';
        field.classList.toggle('is-invalid', !valid);
        field.classList.toggle('is-valid',   valid);
        return valid;
    }

    requiredFields.forEach(function(f) {
        ['input','change','blur'].forEach(function(ev) {
            f.addEventListener(ev, function() { validateField(f); });
        });
        validateField(f); // run on load for edit mode
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var allValid = true;
        requiredFields.forEach(function(f) { if (!validateField(f)) allValid = false; });
        if (!allValid) {
            formErrors.innerHTML = '<strong>Please fill in all required fields before saving.</strong>';
            formErrors.classList.remove('d-none');
            formErrors.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            return;
        }
        var btn = form.querySelector('button[type="submit"]');
        var origText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Saving…';
        formErrors.classList.add('d-none');

        var saveUrl = <?php
            $base = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
            $base = rtrim($base, '/');
            echo json_encode($base . '/save_valuation.php');
        ?>;
        fetch(saveUrl, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form)
        })
        .then(function(r) {
            // Capture raw text first so we can diagnose non-JSON responses
            return r.text().then(function(text) {
                try {
                    return JSON.parse(text);
                } catch(e) {
                    // Server returned non-JSON (PHP error, redirect HTML, etc.)
                    console.error('Server response was not JSON:', text);
                    throw new Error('Server returned invalid response: ' + text.substring(0, 300));
                }
            });
        })
        .then(function(data) {
            if (data.success) {
                window.location.href = data.redirect || 'valuations.php';
            } else {
                var msgs = Array.isArray(data.errors) ? data.errors : ['An unknown error occurred.'];
                formErrors.innerHTML = msgs.map(function(m) { return '<div>' + m + '</div>'; }).join('');
                formErrors.classList.remove('d-none');
                formErrors.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                btn.disabled = false;
                btn.textContent = origText;
            }
        })
        .catch(function(err) {
            var msg = err && err.message ? err.message : 'Unknown error';
            formErrors.innerHTML = '<div><strong>Save failed:</strong> ' + msg + '</div>';
            formErrors.classList.remove('d-none');
            formErrors.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            btn.disabled = false;
            btn.textContent = origText;
        });
    });

    // ── Number to words ───────────────────────────────────────────────────
    function numberToWords(number) {
        var ones = ['Zero','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
                    'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen',
                    'Seventeen','Eighteen','Nineteen'];
        var tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
        var thousands = ['','Thousand','Million','Billion'];
        if (number == 0) return 'Zero';
        number = parseFloat(number);
        var whole = Math.floor(number);
        var decimal = Math.round((number - whole) * 1000);
        var words = [], wholeWords = '';
        if (whole > 0) {
            var chunks = String(whole).padStart(Math.ceil(String(whole).length/3)*3,'0').match(/.{1,3}/g).reverse();
            chunks.forEach(function(chunk, i) {
                chunk = parseInt(chunk);
                if (!chunk) return;
                var cw = [];
                if (chunk >= 100) { cw.push(ones[Math.floor(chunk/100)]+' Hundred'); chunk %= 100; }
                if (chunk >= 20)  { cw.push(tens[Math.floor(chunk/10)]); chunk %= 10; }
                if (chunk > 0)    { cw.push(ones[chunk]); }
                if (cw.length) words.push(cw.join(' ') + (thousands[i] ? ' '+thousands[i] : ''));
            });
            wholeWords = words.reverse().join(' ');
        } else { wholeWords = 'Zero'; }
        return wholeWords + (decimal > 0 ? ' and '+numberToWords(decimal)+' Baizas' : '') + ' Only';
    }
});
</script>
<?php include 'includes/footer.php'; ?>
