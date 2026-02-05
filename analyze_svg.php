<?php
$content = file_get_contents(__DIR__ . '/Copy of Copy of Skylon-IT Agreement For FlashBDTopUp.svg');
echo "Length: " . strlen($content) . "\n";
echo "\nFirst 2000 chars:\n";
echo substr($content, 0, 2000) . "\n\n";
echo "Has <text: " . (strpos($content, '<text') !== false ? 'YES' : 'NO') . "\n";
echo "Has <image: " . (strpos($content, '<image') !== false ? 'YES' : 'NO') . "\n";
echo "Has data:image: " . (strpos($content, 'data:image') !== false ? 'YES' : 'NO') . "\n";
echo "Has base64: " . (strpos($content, 'base64') !== false ? 'YES' : 'NO') . "\n";
$idx = strpos($content, 'data:');
if ($idx !== false) {
    echo "\nData URL at $idx: " . substr($content, $idx, 80) . "\n";
}
