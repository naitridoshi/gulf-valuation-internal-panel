<?php

include 'includes/config.php';
include 'includes/functions.php';

require_once 'vendor/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['id'])) {
    die('No valuation ID provided.');
}

$valuation_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM valuations WHERE id = ?");
$stmt->execute([$valuation_id]);
$valuation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$valuation) {
    die('Valuation not found.');
}

$report_disclaimer = array_key_exists('report_disclaimer', $settings) ? nl2br(htmlspecialchars($settings['report_disclaimer'])) : '';
$company_signature = array_key_exists('company_signature', $settings) ? htmlspecialchars($settings['company_signature']) : '';

// (Optional, unused) logo block left as-is
$logo_html = '';
if (!empty($settings['logo'])) {
    if (filter_var($settings['logo'], FILTER_VALIDATE_URL)) {
        $logo_html = '';
    } else {
        $logo_src = str_replace('\\', '/', __DIR__) . '/' . $settings['logo'];
        if (!file_exists($logo_src)) {
            $logo_html = '';
        } else {
            $logo_html = '<img src="' . htmlspecialchars($logo_src) . '" alt="Company Logo">';
        }
    }
}

// Load the HTML template
$template_path = __DIR__ . '/assets/valuation_template.html';
if (!file_exists($template_path)) {
    die('Valuation template not found.');
}
$template_html = file_get_contents($template_path);

// Prepare data for replacement
if (!empty($settings['valuation_statement'])) {
    $valuation_statement_html = htmlspecialchars($settings['valuation_statement']);
} else {
    $valuation_statement_html = 'Based on produced documents, age, maintenance and observed condition of the vehicle/equipment, we are of the opinion that the present forced market value for the above vehicle/equipment with the existing specifications on "as is where is conditions" is approximately';
}

// Prepare ref_number parts for header
$ref_number_full = $valuation['ref_number'];
$last_slash_pos = strrpos($ref_number_full, '/');
if ($last_slash_pos !== false) {
    $ref_prefix = substr($ref_number_full, 0, $last_slash_pos + 1);
    $ref_suffix = substr($ref_number_full, $last_slash_pos + 1);
} else {
    $ref_prefix = $ref_number_full;
    $ref_suffix = '';
}

$replacements = [
    '{{ref_number}}' => htmlspecialchars($ref_number_full),
    '{{ref_prefix}}' => htmlspecialchars($ref_prefix),
    '{{ref_suffix}}' => htmlspecialchars($ref_suffix),
    '{{valuation_date}}' => date('d/m/Y', strtotime($valuation['valuation_date'])),
    '{{requestor_name}}' => htmlspecialchars($valuation['requestor_name']),
    '{{requestor_contact}}' => htmlspecialchars($valuation['requestor_contact_2']),
    '{{seller}}' => htmlspecialchars($valuation['seller']),
    '{{place_of_asset}}' => htmlspecialchars($valuation['place_of_asset']),
    '{{car_company}}' => htmlspecialchars($valuation['car_company']),
    '{{vehicle_type}}' => htmlspecialchars($valuation['vehicle_type']),
    '{{car_model}}' => htmlspecialchars($valuation['car_model']),
    '{{registration_number}}' => htmlspecialchars($valuation['registration_number']),
    '{{year_of_manufacture}}' => htmlspecialchars($valuation['year_of_manufacture']),
    '{{date_of_registration}}' => htmlspecialchars($valuation['date_of_registration']),
    '{{chassis_number}}' => htmlspecialchars($valuation['chassis_number']),
    '{{engine_number}}' => htmlspecialchars($valuation['engine_number']),
    '{{odometer_reading}}' => htmlspecialchars($valuation['odometer_reading']),
    '{{transmission_type}}' => htmlspecialchars($valuation['transmission_type'] ?? ''),
    '{{features}}' => nl2br(htmlspecialchars($valuation['features'])),
    '{{special_note}}' => nl2br(htmlspecialchars($valuation['special_note'])),
    '{{engine_transmission}}' => htmlspecialchars($valuation['engine_transmission']),
    '{{body_paint}}' => htmlspecialchars($valuation['body_paint']),
    '{{tyres}}' => htmlspecialchars($valuation['tyres']),
    '{{valuation_amount}}' => number_format($valuation['valuation_amount'], 3),
    '{{valuation_amount_words}}' => htmlspecialchars(numberToWords($valuation['valuation_amount'])),
    '{{forced_value}}' => number_format($valuation['valuation_amount'] * 0.8, 3),
    '{{forced_value_words}}' => htmlspecialchars(numberToWords($valuation['valuation_amount'] * 0.8)),
    '{{valuation_id}}' => htmlspecialchars($valuation['id']),
    '{{bank_name}}' => htmlspecialchars($valuation['bank_name']),
    '{{valuation_statement_html}}' => $valuation_statement_html,
];

// Replace placeholders
$html = strtr($template_html, $replacements);

/**
 * NEW: if GD is missing, remove all <img> tags so Dompdf won’t try to process images.
 * This avoids the "GD extension is required" error while keeping the rest of the report intact.
 */
$hasGd = extension_loaded('gd');
if (!$hasGd) {
    // Strip <img ...> tags entirely.
    $html = preg_replace('/<img[^>]*>/i', '', $html);
    // Optionally, you could leave a placeholder box for layout:
    // $html = preg_replace('/<img[^>]*>/i', '<div style="height:20px;"></div>', $html);
}

if (isset($_GET['debug'])) {
    header('Content-Type: text/html');
    echo $html;
    exit;
}

try {
    $options = new Options();
    $options->set('chroot', realpath(__DIR__));
    // CHANGED: be explicit; images are already removed when GD is missing
    $options->set('isRemoteEnabled', false);

    $dompdf = new Dompdf($options);
    $dompdf->setBasePath(realpath(__DIR__ . '/assets'));
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    if (isset($_GET['debug_pdf'])) {
        header('Content-Type: application/pdf');
        echo $dompdf->output();
        exit;
    }
    if (isset($_GET['debug_html'])) {
        header('Content-Type: text/html');
        echo $html;
        exit;
    }

    $dompdf->stream("valuation_" . $valuation['ref_number'] . ".pdf", ["Attachment" => true]);
} catch (Exception $e) {
    echo 'Error generating PDF: ' . $e->getMessage();
    if (isset($html)) {
        echo "<hr><h3>HTML Output:</h3>";
        echo $html;
    }
}
