<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
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
                $offset = ($page - 1) * $records_per_page;

                // Get total records
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM valuations");
                $total_records = $stmt->fetch()['total'];
                $total_pages = ceil($total_records / $records_per_page);

                // Fetch valuations for the current page
                $stmt = $pdo->prepare("SELECT id, ref_number, valuation_date, valuation_amount FROM valuations ORDER BY valuation_date DESC LIMIT :limit OFFSET :offset");
                $stmt->bindValue(':limit', (int)$records_per_page, PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
                $stmt->execute();
                $valuations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Valuations List</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>REF Number</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
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
                                            <td>
                                                <a href="valuations.php?action=edit&id=<?php echo $val['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <a href="valuations.php?action=delete&id=<?php echo $val['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this valuation?');">Delete</a>
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
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="valuations.php?page=<?php echo $page - 1; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="valuations.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="valuations.php?page=<?php echo $page + 1; ?>">Next</a>
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
                                    <input type="text" name="requestor_name" id="requestor_name" class="form-control" list="requestor_list" placeholder="Enter applicant name" value="<?php echo $action == 'edit' ? htmlspecialchars($valuation['requestor_name']) : ''; ?>" required>
                                    <datalist id="requestor_list">
                                        <?php foreach ($requestors as $requestor): ?>
                                            <option value="<?php echo htmlspecialchars($requestor); ?>">
                                        <?php endforeach; ?>
                                    </datalist>
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
                                    <input type="text" name="buyer" id="buyer" class="form-control" value="<?php echo $action == 'edit' ? htmlspecialchars($valuation['buyer']) : ''; ?>" required>
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
                                    <label for="car_model" class="form-label">Model <span class="text-danger">*</span></label>
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
                                        Forced Market Value (R.O.)
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
            <?php elseif ($action == 'delete' && $id > 0): ?>
                <?php
                $stmt = $pdo->prepare("DELETE FROM valuations WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $_SESSION['success'] = 'Valuation deleted successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to delete valuation.';
                }
                header('Location: valuations.php');
                exit;
                ?>
            <?php endif; ?>
        </div>
    </section>
</div>
<script src="assets/js/validate.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var select = document.getElementById('car_company_select');
    select.addEventListener('change', function() {
        if (this.value === 'new') {
            var newMake = prompt('Enter new vehicle make:');
            if (newMake && newMake.trim() !== '') {
                // Check if already exists
                var exists = false;
                for (var i = 0; i < select.options.length; i++) {
                    if (select.options[i].value.toLowerCase() === newMake.trim().toLowerCase()) {
                        exists = true;
                        break;
                    }
                }
                if (!exists) {
                    var option = document.createElement('option');
                    option.value = newMake.trim();
                    option.text = newMake.trim();
                    select.add(option, select.options.length - 1); // before 'Add New Make'
                }
                select.value = newMake.trim();
            } else {
                select.value = '';
            }
        }
    });
});
</script>
<?php include 'includes/footer.php'; ?>
