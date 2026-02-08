<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\Invoice;
use App\Models\ProjectLink;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClearStorageUploadsCommand extends Command
{
    protected $signature = 'storage:clear-uploads
                            {--invoices : Clear generated invoice PDFs only}
                            {--apk : Clear APK/AAB uploads only}
                            {--documents : Clear project document uploads only}
                            {--all : Clear all (invoices, APK, project documents)}
                            {--force : Skip confirmation}';

    protected $description = 'Clear uploaded files (APK/AAB, project docs) and/or generated invoice PDFs to free disk space.';

    public function handle(): int
    {
        $clearInvoices = $this->option('invoices');
        $clearApk = $this->option('apk');
        $clearDocuments = $this->option('documents');
        $clearAll = $this->option('all');

        if ($clearAll) {
            $clearInvoices = $clearApk = $clearDocuments = true;
        }

        if (! $clearInvoices && ! $clearApk && ! $clearDocuments) {
            $this->error('Specify at least one of: --invoices, --apk, --documents, or --all');
            return self::FAILURE;
        }

        $actions = array_filter([
            $clearInvoices ? 'generated invoice PDFs' : null,
            $clearApk ? 'APK/AAB uploads' : null,
            $clearDocuments ? 'project document uploads' : null,
        ]);

        if (! $this->option('force') && ! $this->confirm('Clear: ' . implode(', ', $actions) . '. Continue?', true)) {
            return self::SUCCESS;
        }

        $freed = 0;

        if ($clearInvoices) {
            $freed += $this->clearInvoices();
        }
        if ($clearApk) {
            $freed += $this->clearApkDownloads();
        }
        if ($clearDocuments) {
            $freed += $this->clearProjectDocuments();
        }

        $this->info(sprintf('Done. Freed approximately %s MB.', round($freed / 1024 / 1024, 2)));

        return self::SUCCESS;
    }

    private function clearInvoices(): int
    {
        $disk = Storage::disk('local');
        $bytes = 0;
        $files = $disk->exists('invoices') ? $disk->files('invoices') : [];
        foreach ($files as $path) {
            $bytes += $disk->size($path);
            $disk->delete($path);
        }
        Invoice::query()->whereNotNull('file_path')->update(['file_path' => null]);
        $this->info(sprintf('Cleared %d invoice PDF(s), %s MB.', count($files), round($bytes / 1024 / 1024, 2)));
        return $bytes;
    }

    private function clearApkDownloads(): int
    {
        $disk = Storage::disk('local');
        $bytes = 0;
        $dirs = $disk->exists('apk-downloads') ? $disk->directories('apk-downloads') : [];
        foreach ($dirs as $dir) {
            $files = $disk->allFiles($dir);
            foreach ($files as $path) {
                $bytes += $disk->size($path);
                $disk->delete($path);
            }
        }
        ProjectLink::query()
            ->where('link_type', ProjectLink::TYPE_APK)
            ->whereNotNull('file_path')
            ->update(['file_path' => null, 'file_name' => null]);
        $this->info(sprintf('Cleared APK/AAB uploads, %s MB.', round($bytes / 1024 / 1024, 2)));
        return $bytes;
    }

    private function clearProjectDocuments(): int
    {
        $bytes = 0;
        $dirs = Storage::disk('local')->directories('project-documents');
        foreach ($dirs as $dir) {
            $files = Storage::disk('local')->allFiles($dir);
            foreach ($files as $path) {
                $bytes += Storage::disk('local')->size($path);
                Storage::disk('local')->delete($path);
            }
        }
        // Document.file_path is NOT NULL; remove records whose files we deleted
        $deleted = Document::query()->where('file_path', 'like', 'project-documents/%')->delete();
        $this->info(sprintf('Cleared project document uploads (%d record(s) removed), %s MB.', $deleted, round($bytes / 1024 / 1024, 2)));
        return $bytes;
    }
}
