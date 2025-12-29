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
?>
