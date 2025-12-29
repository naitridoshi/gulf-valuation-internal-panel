<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
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
                        <h1 class="m-0">Configuration</h1>
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
                        <h3 class="card-title">Project Settings</h3>
                    </div>
                    <div class="card-body">
                        <form action="update_config.php" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ref_prefix" class="form-label">Reference Prefix</label>
                                    <input type="text" name="ref_prefix" id="ref_prefix" class="form-control" value="<?php echo htmlspecialchars($settings['ref_prefix'] ?? 'GAS/VAL/'); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <select name="timezone" id="timezone" class="form-control" required>
                                        <?php
                                        $timezones = DateTimeZone::listIdentifiers();
                                        foreach ($timezones as $tz) {
                                            $selected = ($settings['timezone'] == $tz) ? 'selected' : '';
                                            echo "<option value='$tz' $selected>$tz</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="company_name" class="form-label">Company Name</label>
                                    <input type="text" name="company_name" id="company_name" class="form-control" value="<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="logo" class="form-label">Logo URL</label>
                                    <input type="text" name="logo" id="logo" class="form-control" value="<?php echo htmlspecialchars($settings['logo'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="valuation_statement" class="form-label">Valuation Statement</label>
                                <textarea name="valuation_statement" id="valuation_statement" class="form-control" rows="4" required><?php echo htmlspecialchars($settings['valuation_statement'] ?? 'Based on our observation, age, maintenance and performance of the vehicle, we are of the opinion that the present market value of the above vehicle with the existing specification on “as is where is conditions” is approximately'); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="report_disclaimer" class="form-label">Report Disclaimer</label>
                                <textarea name="report_disclaimer" id="report_disclaimer" class="form-control" rows="4" required><?php echo htmlspecialchars($settings['report_disclaimer'] ?? 'This report is true to the best of our knowledge and is issued without prejudice subject to the valuation as per condition of the vehicle at the date, place and time of our inspection.'); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="company_signature" class="form-label">Company Signature</label>
                                <input type="text" name="company_signature" id="company_signature" class="form-control" value="<?php echo htmlspecialchars($settings['company_signature'] ?? 'For GULF ADJUSTERS, SURVEYORS & SERVICES LLC'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="valuation_footer" class="form-label">Valuation Footer</label>
                                <textarea name="valuation_footer" id="valuation_footer" class="form-control" rows="4" required><?php echo htmlspecialchars($settings['valuation_footer'] ?? "Authorized Signatory\nNote: Refer Basis of Valuation, Specific Assumptions, Saving Clauses forming part of this valuation refer overleaf"); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg">Save Settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
<?php include 'includes/footer.php'; ?>