<?php
$codigo = '7654321';
$totalC = '1234';
$filler = '0';
$base12 = $codigo . $totalC . $filler;
echo 'Base12: ' . $base12 . PHP_EOL;
echo 'Length: ' . strlen($base12) . PHP_EOL;

// Calcular DV
$sum = 0;
for ($i = 0; $i < 12; $i++) {
    $d = (int)$base12[$i];
    $sum += ($i % 2 === 0) ? $d : 3 * $d;
}
$mod = $sum % 10;
$dv = ($mod === 0) ? 0 : 10 - $mod;
$ean13 = $base12 . $dv;
echo 'EAN13: ' . $ean13 . PHP_EOL;
echo 'Length: ' . strlen($ean13) . PHP_EOL;
echo 'Is numeric: ' . (is_numeric($ean13) ? 'yes' : 'no') . PHP_EOL;
echo 'Matches regex: ' . (preg_match('/^\d{13}$/', $ean13) ? 'yes' : 'no') . PHP_EOL;