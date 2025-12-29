<?php
include 'includes/config.php';

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

if (!$valuation) {
    $_SESSION['error'] = 'Valuation not found.';
    header('Location: valuations_list.php');
    exit;
}

// Calculate amounts
$amount_before_tax = floatval($valuation['valuation_amount']);
$vat_amount = $amount_before_tax * 0.05;
$total_amount = $amount_before_tax + $vat_amount;

// Format amounts
$amount_before_tax_formatted = number_format($amount_before_tax, 3);
$vat_amount_formatted = number_format($vat_amount, 3);
$total_amount_formatted = number_format($total_amount, 3);

// Get amount in words (you'll need to implement this function)
function convertNumberToWords($number) {
    $ones = array('', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE', 'TEN', 
                  'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN', 'SEVENTEEN', 
                  'EIGHTEEN', 'NINETEEN');
    $tens = array('', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY');
    
    $num = number_format($number, 3, '.', '');
    $num_arr = explode('.', $num);
    $num_int = (int)$num_arr[0];
    $num_dec = isset($num_arr[1]) ? (int)$num_arr[1] : 0;
    
    if ($num_int == 0) {
        $int_words = 'ZERO';
    } else {
        $int_words = '';
        if ($num_int >= 1000) {
            $thousands = floor($num_int / 1000);
            if ($thousands < 20) {
                $int_words .= $ones[$thousands] . ' THOUSAND ';
            } else {
                $int_words .= $tens[floor($thousands / 10)] . ' ' . $ones[$thousands % 10] . ' THOUSAND ';
            }
            $num_int %= 1000;
        }
        
        if ($num_int >= 100) {
            $int_words .= $ones[floor($num_int / 100)] . ' HUNDRED ';
            $num_int %= 100;
        }
        
        if ($num_int >= 20) {
            $int_words .= $tens[floor($num_int / 10)] . ' ' . $ones[$num_int % 10];
        } elseif ($num_int > 0) {
            $int_words .= $ones[$num_int];
        }
    }
    
    $dec_words = '';
    if ($num_dec > 0) {
        $dec_words = 'AND Bzs ' . $ones[floor($num_dec / 100)] . ' ' . $tens[floor(($num_dec % 100) / 10)] . ' ' . $ones[$num_dec % 10];
    }
    
    return trim($int_words) . ' ' . trim($dec_words) . ' ONLY.';
}

$total_amount_words = convertNumberToWords($total_amount);

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

// Load template
$template = file_get_contents('assets/invoice_template.html');

// Replace placeholders
$template = str_replace('{{customer_name}}', htmlspecialchars($valuation['requestor_name']), $template);
$template = str_replace('{{ref_number}}', htmlspecialchars($valuation['ref_number']), $template);
$template = str_replace('{{invoice_date}}', date('d/m/Y', strtotime($valuation['valuation_date'])), $template);
$template = str_replace('{{car_company}}', htmlspecialchars($valuation['car_company']), $template);
$template = str_replace('{{vehicle_type}}', htmlspecialchars($valuation['vehicle_type']), $template);
$template = str_replace('{{car_model}}', htmlspecialchars($valuation['car_model']), $template);
$template = str_replace('{{registration_number}}', htmlspecialchars($valuation['registration_number']), $template);
$template = str_replace('{{vehicle_color}}', htmlspecialchars($valuation['vehicle_color']), $template);
$template = str_replace('{{bank_name}}', htmlspecialchars($bank_name), $template);
$template = str_replace('{{amount_before_tax}}', $amount_before_tax_formatted, $template);
$template = str_replace('{{vat_amount}}', $vat_amount_formatted, $template);
$template = str_replace('{{total_amount}}', $total_amount_formatted, $template);
$template = str_replace('{{total_amount_words}}', $total_amount_words, $template);

echo $template;
