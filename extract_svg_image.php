<?php
$svgPath = __DIR__ . '/Copy of Copy of Skylon-IT Agreement For FlashBDTopUp.svg';
$content = file_get_contents($svgPath);

preg_match_all('/data:image\/(\w+);base64,([A-Za-z0-9+\/=]+)/', $content, $matches);

$dir = __DIR__ . '/public/images/invoice-parts';
if (!is_dir($dir)) mkdir($dir, 0755, true);

echo "Found " . count($matches[0]) . " images\n";

foreach ($matches[0] as $i => $full) {
    $ext = $matches[1][$i];
    $b64 = $matches[2][$i];
    $decoded = base64_decode($b64, true);
    if ($decoded === false) {
        echo "  img" . ($i+1) . ": decode FAILED\n";
        continue;
    }
    $ext = ($ext === 'jpeg') ? 'jpg' : $ext;
    $out = "$dir/img" . ($i+1) . ".$ext";
    file_put_contents($out, $decoded);
    echo "  img" . ($i+1) . ".$ext: " . strlen($decoded) . " bytes\n";
}

// Also copy largest to main invoice-background for template use
$maxSize = 0;
$maxFile = null;
foreach (glob("$dir/*") as $f) {
    $s = filesize($f);
    if ($s > $maxSize) {
        $maxSize = $s;
        $maxFile = $f;
    }
}
if ($maxFile) {
    copy($maxFile, __DIR__ . '/public/images/invoice-background.jpg');
    echo "\nMain background: " . basename($maxFile) . " (" . $maxSize . " bytes)\n";
}
