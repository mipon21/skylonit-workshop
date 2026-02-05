<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExtractInvoiceBackground extends Command
{
    protected $signature = 'invoice:extract-background {--svg= : Path to SVG file}';
    protected $description = 'Extract background image from SVG invoice template';

    public function handle(): int
    {
        $svgPath = $this->option('svg') ?? base_path('Copy of Copy of Skylon-IT Agreement For FlashBDTopUp.svg');

        if (!file_exists($svgPath)) {
            $this->error("SVG file not found: {$svgPath}");
            return 1;
        }

        $content = file_get_contents($svgPath);
        preg_match_all('/data:image\/(\w+);base64,([A-Za-z0-9+\/=]+)/', $content, $matches);

        if (empty($matches[0])) {
            $this->error('No embedded images found in SVG.');
            return 1;
        }

        $dir = public_path('images');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $maxSize = 0;
        $maxData = null;
        $ext = 'jpg';
        foreach ($matches[2] as $i => $b64) {
            $decoded = base64_decode($b64, true);
            if ($decoded && strlen($decoded) > $maxSize) {
                $maxSize = strlen($decoded);
                $maxData = $decoded;
                $ext = $matches[1][$i] === 'jpeg' ? 'jpg' : $matches[1][$i];
            }
        }

        if (!$maxData) {
            $this->error('Failed to decode any image.');
            return 1;
        }

        $outPath = public_path('images/invoice-background.jpg');
        file_put_contents($outPath, $maxData);
        $this->info("Extracted background to: {$outPath} ({$maxSize} bytes)");
        return 0;
    }
}
