<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ref_prefix = trim($_POST['ref_prefix']);
    $timezone = $_POST['timezone'];
    $company_name = trim($_POST['company_name']);
    $logo = trim($_POST['logo']);
    $valuation_statement = trim($_POST['valuation_statement']);
    $report_disclaimer = trim($_POST['report_disclaimer']);
    $company_signature = trim($_POST['company_signature']);
    $valuation_footer = trim($_POST['valuation_footer']);

    if (empty($ref_prefix) || empty($timezone) || empty($company_name) || empty($logo) ||
        empty($valuation_statement) || empty($report_disclaimer) || empty($company_signature) ||
        empty($valuation_footer)) {
        $_SESSION['error'] = 'All fields are required.';
        header('Location: config_page.php');
        exit;
    }

    if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
        $_SESSION['error'] = 'Invalid timezone.';
        header('Location: config_page.php');
        exit;
    }

    $updates = [
        ['key' => 'ref_prefix', 'value' => $ref_prefix],
        ['key' => 'timezone', 'value' => $timezone],
        ['key' => 'company_name', 'value' => $company_name],
        ['key' => 'logo', 'value' => $logo],
        ['key' => 'valuation_statement', 'value' => $valuation_statement],
        ['key' => 'report_disclaimer', 'value' => $report_disclaimer],
        ['key' => 'company_signature', 'value' => $company_signature],
        ['key' => 'valuation_footer', 'value' => $valuation_footer]
    ];

    $success = true;
    foreach ($updates as $update) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
        if (!$stmt->execute([$update['key'], $update['value'], $update['value']])) {
            $success = false;
        }
    }

    if ($success) {
        $_SESSION['success'] = 'Settings updated successfully.';
    } else {
        $_SESSION['error'] = 'Failed to update settings.';
    }
    header('Location: config_page.php');
    exit;
} else {
    header('Location: config_page.php');
    exit;
}
?>