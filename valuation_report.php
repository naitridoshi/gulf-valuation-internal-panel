<?php
include 'includes/config.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'No valuation ID provided.';
    header('Location: valuations_list.php');
    exit;
}

$valuation_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM valuations WHERE id = ?");
$stmt->execute([$valuation_id]);
$valuation = $stmt->fetch(PDO::FETCH_ASSOC);

// Get bank name from bank_id
$bank_name = '';
if (!empty($valuation['bank_id'])) {
    $stmt_bank = $pdo->prepare("SELECT name FROM banks WHERE id = ? LIMIT 1");
    $stmt_bank->execute([$valuation['bank_id']]);
    $bank_row = $stmt_bank->fetch(PDO::FETCH_ASSOC);
    if ($bank_row) {
        $bank_name = $bank_row['name'];
    }
}

if (!$valuation) {
    $_SESSION['error'] = 'Valuation not found.';
    header('Location: valuations_list.php');    
    exit;
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Valuation Report</h1>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Valuation Details</h3>
                    <div class="card-tools">
                        <button class="btn btn-primary no-print" onclick="printReport()">Print Report</button>
                        <a href="export_pdf.php?id=<?php echo $valuation_id; ?>" class="btn btn-success no-print disabled">Export to PDF</a>
                        <a href="print_invoice.php?id=<?php echo $valuation_id; ?>" class="btn btn-warning no-print">Print Invoice</a>
                    </div>
                </div>
                <div class="card-body printable">
                    <div style="margin-bottom: 20px;">
                        <strong>REF: <?php echo htmlspecialchars($valuation['ref_number']); ?></strong>
                        <span style="float: right;">Date: <?php echo date('d/m/Y', strtotime($valuation['valuation_date'])); ?></span>
                    </div>
                        <p><strong>WE THE UNDERSIGNED AT THE REQUEST OF:</strong> <?php echo htmlspecialchars($valuation['requestor_name'] . ($valuation['requestor_contact_2'] ? ' / ' . $valuation['requestor_contact_2'] : '')); ?></p>
                    <p><strong>TO CARRY OUT THE VALUATION OF:</strong> <?php echo htmlspecialchars($valuation['car_company']); ?></p>
                    <p><strong>TO AVAIL FINANCE FROM:</strong> <?php echo htmlspecialchars($bank_name); ?></p>
                    <h4 style="color: white; background: darkblue; padding: 5px; text-align: center;">WE NOW REPORT AS FOLLOWS:</h4>
                    <table class="table">
                        <tr>
                            <td><strong>Name of the Applicant (Buyer)</strong></td>
                            <td><?php echo htmlspecialchars($valuation['buyer']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Seller</strong></td>
                            <td><?php echo htmlspecialchars($valuation['seller']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Place of Asset</strong></td>
                            <td><?php echo htmlspecialchars($valuation['place_of_asset']); ?></td>
                        </tr>
                    </table>
                    <h4>DETAILS OF VEHICLE</h4>
                    <table class="table">
                        <tr>
                            <td><strong>Make</strong></td>
                            <td><?php echo htmlspecialchars($valuation['car_company']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Type</strong></td>
                            <td><?php echo htmlspecialchars($valuation['vehicle_type']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Model</strong></td>
                            <td><?php echo htmlspecialchars($valuation['car_model']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Registration Number</strong></td>
                            <td><?php echo htmlspecialchars($valuation['registration_number']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Vehicle Color</strong></td>
                            <td><?php echo htmlspecialchars($valuation['vehicle_color']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Year of Manufacture</strong></td>
                            <td><?php echo htmlspecialchars($valuation['year_of_manufacture']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Date of Registration</strong></td>
                            <td><?php echo date('d/m/Y', strtotime($valuation['date_of_registration'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Chassis Number</strong></td>
                            <td><?php echo htmlspecialchars($valuation['chassis_number']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Engine Number</strong></td>
                            <td><?php echo htmlspecialchars($valuation['engine_number']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Odometer Reading</strong></td>
                            <td><?php echo htmlspecialchars($valuation['odometer_reading']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Transmission Type</strong></td>
                            <td><?php echo htmlspecialchars($valuation['transmission_type'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Features</strong></td>
                            <td><?php echo nl2br(htmlspecialchars($valuation['features'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Special Note (if any)</strong></td>
                            <td><?php echo nl2br(htmlspecialchars($valuation['special_note'])); ?></td>
                        </tr>
                    </table>
                    <h4>OBSERVATION</h4>
                    <table class="table">
                        <tr>
                            <td><strong>Engine & Transmission</strong></td>
                            <td><?php echo htmlspecialchars($valuation['engine_transmission']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Body & Paint</strong></td>
                            <td><?php echo htmlspecialchars($valuation['body_paint']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tyres</strong></td>
                            <td><?php echo htmlspecialchars($valuation['tyres']); ?></td>
                        </tr>
                    </table>
                    <p>Based on our observation, age, maintenance and performance of the vehicle, we are of the opinion that the present market value of the above vehicle with the existing specification on “as is where is conditions” is approximately<br>
                    <strong>R.O. <?php echo number_format($valuation['valuation_amount'], 3); ?> (Rials Omani <?php echo numberToWords($valuation['valuation_amount']); ?>)</strong></p>
                    <p><strong>FORCED MARKET VALUE: R.O. <?php echo number_format($valuation['forced_sale_valuation_amount'], 3); ?> (Rials Omani <?php echo numberToWords($valuation['forced_sale_valuation_amount']); ?>)</strong></p>
                    <p><?php echo nl2br(htmlspecialchars($settings['valuation_footer'] ?? '')); ?></p>
                    <p><strong><?php echo htmlspecialchars(substr($valuation['ref_number'], strrpos($valuation['ref_number'], '/') + 1)); ?></strong></p>
                    <h4>TAX INVOICE</h4>
                    <table class="table">
                        <tr>
                            <td><strong>Invoice Number</strong></td>
                            <td><?php echo htmlspecialchars($valuation['ref_number']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Date</strong></td>
                            <td><?php echo date('d/m/Y', strtotime($valuation['valuation_date'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Description</strong></td>
                            <td>Valuation Services for <?php echo htmlspecialchars($valuation['car_company']); ?> <?php echo htmlspecialchars($valuation['car_model']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Amount</strong></td>
                            <td>R.O. <?php echo number_format($valuation['valuation_amount'], 3); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
function printReport() {
    var valuationId = <?php echo json_encode($valuation_id); ?>;
    var printUrl = 'print_valuation.php?id=' + valuationId;

    var iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.top = '-100px';
    iframe.style.left = '-100px';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    iframe.src = printUrl;

    document.body.appendChild(iframe);

    iframe.onload = function() {
        try {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        } catch (e) {
            console.error('Printing failed', e);
            alert('Could not open print dialog. Please check your browser settings.');
        }
    };
}
</script>
<?php include 'includes/footer.php'; ?>
