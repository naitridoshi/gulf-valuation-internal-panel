<?php

include 'includes/config.php';
include 'includes/functions.php';

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

// Fetch bank name using bank_id
$bank_name = '';
if (!empty($valuation['bank_id'])) {
    $stmt_bank = $pdo->prepare("SELECT name FROM banks WHERE id = ? LIMIT 1");
    $stmt_bank->execute([$valuation['bank_id']]);
    $bank_row = $stmt_bank->fetch(PDO::FETCH_ASSOC);
    if ($bank_row) {
        $bank_name = $bank_row['name'];
    }
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

// Add a <base> tag to the head for correct relative path resolution
$template_html = str_replace('</head>', '    <base href="assets/"></head>', $template_html);

// Conditionally remove the forced sale value block if the amount is zero or not set
if (empty($valuation['forced_sale_valuation_amount'])) {
    $pattern = '#\s*<div class="valuation-box">\s*<div>\s*<span class="amount-box">Forced Sale Value RO: \{\{forced_value\}\}<\/span>\s*<span class="amount-box">Rials Omani \{\{forced_value_words\}\}<\/span>\s*<\/div>\s*<\/div>#s';
    $template_html = preg_replace($pattern, '', $template_html);
}

// Prepare data for replacement
if (!empty($settings['valuation_statement'])) {
    $valuation_statement_html = htmlspecialchars($settings['valuation_statement']);
} else {
    $valuation_statement_html = 'Based on our produced documents, age and observedcondition of the vehicle/equipment , we are of the opinion that the present market value of the above vehicle/equipment with the existing specification on “as is where is conditions” is approximately';
}

// Prepare ref_number parts for header
$ref_number_full = $valuation['ref_number'];
$last_slash_pos = strrpos($ref_number_full, '/');
if ($last_slash_pos !== false) {
    $ref_prefix = substr($ref_number_full, 0, $last_slash_pos + 1);
} else {
    $ref_prefix = $ref_number_full;
}

$replacements = [
    '{{top_gap}}' => '60pt',
    '{{ref_number}}' => htmlspecialchars($ref_prefix),
    '{{ref_prefix}}' => htmlspecialchars($ref_prefix),
    '{{complete_ref_number}}' => htmlspecialchars($ref_number_full),
    '{{ref_suffix}}' => '', // Hidden for printing
    '{{valuation_date}}' => date('d/m/Y', strtotime($valuation['valuation_date'])),
    '{{requestor_name}}' => htmlspecialchars($valuation['requestor_name']),
    '{{requestor_contact}}' => htmlspecialchars($valuation['requestor_contact_2']),
    '{{seller}}' => htmlspecialchars($valuation['seller']),
    '{{place_of_asset}}' => htmlspecialchars($valuation['place_of_asset']),
    '{{car_company}}' => htmlspecialchars($valuation['car_company']),
    '{{bank_name}}' => htmlspecialchars($bank_name),
    '{{vehicle_type}}' => htmlspecialchars($valuation['vehicle_type']),
    '{{car_model}}' => htmlspecialchars($valuation['car_model']),
    '{{registration_number}}' => htmlspecialchars($valuation['registration_number']),
    '{{vehicle_color}}' => htmlspecialchars($valuation['vehicle_color'] ?? ''),
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
    '{{forced_value}}' => number_format($valuation['forced_sale_valuation_amount'], 3),
    '{{forced_value_words}}' => htmlspecialchars(numberToWords($valuation['forced_sale_valuation_amount'])),
    '{{valuation_id}}' => htmlspecialchars($valuation['id']),
    '{{valuation_statement_html}}' => $valuation_statement_html,
];

// Replace placeholders
$html = strtr($template_html, $replacements);

// Output the HTML for printing
header('Content-Type: text/html');
echo $html;
