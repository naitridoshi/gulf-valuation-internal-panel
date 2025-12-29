<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Fetch all valuations
$stmt = $pdo->query("SELECT v.*, b.name AS bank_name FROM valuations v LEFT JOIN banks b ON v.bank_id = b.id ORDER BY v.valuation_date DESC");
$valuations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=valuations.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, array(
    'REF Number',
    'Valuation Date',
    'Requestor Name',
    'Mobile Number',
    'Seller',
    'Place of Asset',
    'Car Company',
    'Bank Name',
    'Vehicle Type',
    'Car Model',
    'Registration Number',
    'Vehicle Color',
    'Year of Manufacture',
    'Date of Registration',
    'Chassis Number',
    'Engine Number',
    'Odometer Reading',
    'Transmission Type',
    'Features',
    'Special Note',
    'Engine Transmission',
    'Body Paint',
    'Tyres',
    'Valuation Amount',
    'Forced Sale Valuation Amount',
));

// Loop through the valuations and output them
foreach ($valuations as $valuation) {
    fputcsv($output, array(
        $valuation['ref_number'] ?? '',
        date('d/m/Y', strtotime($valuation['valuation_date'])),
        $valuation['requestor_name'] ?? '',
        $valuation['requestor_contact_2'] ?? '',
        $valuation['seller'] ?? '',
        $valuation['place_of_asset'] ?? '',
        $valuation['car_company'] ?? '',
        $valuation['bank_name'] ?? '',
        $valuation['vehicle_type'] ?? '',
        $valuation['car_model'] ?? '',
        $valuation['registration_number'] ?? '',
        $valuation['vehicle_color'] ?? '',
        $valuation['year_of_manufacture'] ?? '',
        $valuation['date_of_registration'] ?? '',
        $valuation['chassis_number'] ?? '',
        $valuation['engine_number'] ?? '',
        $valuation['odometer_reading'] ?? '',
        $valuation['transmission_type'] ?? '',
        $valuation['features'] ?? '',
        $valuation['special_note'] ?? '',
        $valuation['engine_transmission'] ?? '',
        $valuation['body_paint'] ?? '',
        $valuation['tyres'] ?? '',
        number_format($valuation['valuation_amount'] ?? 0, 3),
        number_format($valuation['forced_sale_valuation_amount'] ?? 0, 3),
    ));
}

fclose($output);
exit;
?>
