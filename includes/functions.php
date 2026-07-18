<?php
function numberToWords($number, $isDecimal = false) {
    $ones = ['Zero', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
             'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    $thousands = ['', 'Thousand', 'Million', 'Billion'];

    if ($number == 0) return $isDecimal ? 'Zero' : 'Zero Only';

    $number = floatval($number);
    $whole = floor($number);
    $decimal = round(($number - $whole) * 1000);

    $words = [];

    if ($whole > 0) {
        $wholeStr = str_pad($whole, ceil(strlen($whole) / 3) * 3, '0', STR_PAD_LEFT);
        $chunks = [];
        $len = strlen($wholeStr);
        for ($i = 0; $i < $len; $i += 3) {
            $chunks[] = substr($wholeStr, $i, 3);
        }

        $chunkCount = count($chunks);
        for ($i = 0; $i < $chunkCount; $i++) {
            $chunk = intval($chunks[$i]);
            if ($chunk == 0) continue;

            $chunkWords = [];

            if ($chunk >= 100) {
                $chunkWords[] = $ones[intval($chunk / 100)] . ' Hundred';
                $chunk %= 100;
            }

            if ($chunk >= 20) {
                $chunkWords[] = $tens[intval($chunk / 10)];
                $chunk %= 10;
            }

            if ($chunk > 0) {
                $chunkWords[] = $ones[$chunk];
            }

            if (!empty($chunkWords)) {
                $words[] = implode(' ', $chunkWords) . ($thousands[$chunkCount - $i - 1] ? ' ' . $thousands[$chunkCount - $i - 1] : '');
            }
        }
    } else {
        $words[] = 'Zero';
    }

    $wholeWords = implode(' ', $words);

    $decimalWords = '';
    if ($decimal > 0) {
        $decimalWords = ' and ' . numberToWords($decimal, true) . ' Baizas';
    }

    return $wholeWords . $decimalWords . ($isDecimal ? '' : ' Only');
}

function get_next_ref_seq($pdo, $settings, $year = null) {
    if ($year === null) {
        $year = date('y');
    }
    $prefix = $settings['ref_prefix'] ?? 'GAS/VAL/';
    
    // Fetch all existing reference numbers for this prefix and year
    $like_pattern = $prefix . $year . '/%';
    $stmt = $pdo->prepare("SELECT ref_number FROM valuations WHERE ref_number LIKE ?");
    $stmt->execute([$like_pattern]);
    $existing_refs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $existing_seqs = [];
    foreach ($existing_refs as $ref) {
        $parts = explode('/', $ref);
        $seq_part = end($parts);
        if (ctype_digit($seq_part)) {
            $existing_seqs[] = (int)$seq_part;
        }
    }
    
    // Get starting sequence from settings (default to 1)
    $start_seq = isset($settings['ref_number']) ? (int)$settings['ref_number'] : 1;
    
    // Find the first unused sequence number starting from $start_seq
    $next_seq = $start_seq;
    while (in_array($next_seq, $existing_seqs)) {
        $next_seq++;
    }
    
    return $next_seq;
}
?>
