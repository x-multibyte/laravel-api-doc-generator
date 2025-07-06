<?php

namespace XMultibyte\ApiDoc\Console\Commands;

use Illuminate\Support\Facades\File;

class CleanDocsCommand extends BaseCommand
{
    protected $signature = 'api-docs:clean 
                            {--backups : Clean backup files}
                            {--cache : Clean cached documentation}
                            {--generated : Clean generated files}
                            {--all : Clean all documentation files}
                            {--older-than= : Clean files older than specified days}
                            {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Clean up API documentation files and cache';

    public function handle()
    {
        $this->displayHeader('API Documentation Cleaner');

        $cleanBackups = $this->option('backups');
        $cleanCache = $this->option('cache');
        $cleanGenerated = $this->option('generated');
        $cleanAll = $this->option('all');
        $olderThan = $this->option('older-than');
        $dryRun = $this->option('dry-run');

        if (!$cleanBackups && !$cleanCache && !$cleanGenerated && !$cleanAll) {
            $this->displayError('Please specify what to clean: --backups, --cache, --generated, or --all');
            return 1;
        }

        if ($dryRun) {
            $this->displayWarning('DRY RUN MODE - No files will actually be deleted');
            $this->line('');
        }

        $deletedFiles = [];
        $deletedSize = 0;

        // Clean backup files
        if ($cleanBackups || $cleanAll) {
            $result = $this->cleanBackupFiles($olderThan, $dryRun);
            $deletedFiles = array_merge($deletedFiles, $result['files']);
            $deletedSize += $result['size'];
        }

        // Clean cache files
        if ($cleanCache || $cleanAll) {
            $result = $this->cleanCacheFiles($olderThan, $dryRun);
            $deletedFiles = array_merge($deletedFiles, $result['files']);
            $deletedSize += $result['size'];
        }

        // Clean generated files
        if ($cleanGenerated || $cleanAll) {
            $result = $this->cleanGeneratedFiles($olderThan, $dryRun);
            $deletedFiles = array_merge($deletedFiles, $result['files']);
            $deletedSize += $result['size'];
        }

        // Display results
        $this->displayResults($deletedFiles, $deletedSize, $dryRun);

        return 0;
    }

    protected function cleanBackupFiles($olderThan, $dryRun)
    {
        $this->displayInfo('Cleaning backup files...');
        
        $backupDir = storage_path('api-docs/backups');
        $deletedFiles = [];
        $deletedSize = 0;

        if (!File::exists($backupDir)) {
            $this->displayWarning('No backup directory found');
            return ['files' => [], 'size' => 0];
        }

        $files = File::files($backupDir);
        $cutoffTime = $olderThan ? now()->subDays((int)$olderThan) : null;

        foreach ($files as $file) {
            $filePath = $file->getPathname();
            $fileName = $file->getFilename();
            
            // Check if file matches backup pattern
            if (!preg_match('/^openapi_backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.(json|yaml)$/', $fileName)) {
                continue;
            }

            // Check age if specified
            if ($cutoffTime && File::lastModified($filePath) > $cutoffTime->timestamp) {
                continue;
            }

            $fileSize = File::size($filePath);
            
            if (!$dryRun) {
                File::delete($filePath);
            }
            
            $deletedFiles[] = $filePath;
            $deletedSize += $fileSize;
            
            $this->line("  🗑️  {$fileName} (" . $this->formatBytes($fileSize) . ")");
        }

        return ['files' => $deletedFiles, 'size' => $deletedSize];
    }

    protected function cleanCacheFiles($olderThan, $dryRun)
    {
        $this->displayInfo('Cleaning cache files...');
        
        $cacheDir = storage_path('api-docs/cache');
        $deletedFiles = [];
        $deletedSize = 0;

        if (!File::exists($cacheDir)) {
            $this->displayWarning('No cache directory found');
            return ['files' => [], 'size' => 0];
        }

        $files = File::allFiles($cacheDir);
        $cutoffTime = $olderThan ? now()->subDays((int)$olderThan) : null;

        foreach ($files as $file) {
            $filePath = $file->getPathname();
            $fileName = $file->getFilename();
            
            // Check age if specified
            if ($cutoffTime && File::lastModified($filePath) > $cutoffTime->timestamp) {
                continue;
            }

            $fileSize = File::size($filePath);
            
            if (!$dryRun) {
                File::delete($filePath);
            }
            
            $deletedFiles[] = $filePath;
            $deletedSize += $fileSize;
            
            $this->line("  🗑️  {$fileName} (" . $this->formatBytes($fileSize) . ")");
        }

        // Remove empty directories
        if (!$dryRun && File::exists($cacheDir)) {
            $directories = File::directories($cacheDir);
            foreach ($directories as $dir) {
                if (count(File::allFiles($dir)) === 0) {
                    File::deleteDirectory($dir);
                }
            }
        }

        return ['files' => $deletedFiles, 'size' => $deletedSize];
    }

    protected function cleanGeneratedFiles($olderThan, $dryRun)
    {
        $this->displayInfo('Cleaning generated files...');
        
        $docsDir = storage_path('api-docs');
        $deletedFiles = [];
        $deletedSize = 0;

        if (!File::exists($docsDir)) {
            $this->displayWarning('No documentation directory found');
            return ['files' => [], 'size' => 0];
        }

        $patterns = [
            'openapi.json',
            'openapi.yaml',
            'imported_openapi.json',
            'imported_openapi.yaml',
            'api-docs-*.json',
            'api-docs-*.yaml'
        ];

        $cutoffTime = $olderThan ? now()->subDays((int)$olderThan) : null;

        foreach ($patterns as $pattern) {
            $files = File::glob($docsDir . '/' . $pattern);
            
            foreach ($files as $filePath) {
                $fileName = basename($filePath);
                
                // Check age if specified
                if ($cutoffTime && File::lastModified($filePath) > $cutoffTime->timestamp) {
                    continue;
                }

                $fileSize = File::size($filePath);
                
                if (!$dryRun) {
                    File::delete($filePath);
                }
                
                $deletedFiles[] = $filePath;
                $deletedSize += $fileSize;
                
                $this->line("  🗑️  {$fileName} (" . $this->formatBytes($fileSize) . ")");
            }
        }

        return ['files' => $deletedFiles, 'size' => $deletedSize];
    }

    protected function displayResults($deletedFiles, $deletedSize, $dryRun)
    {
        $this->line('');
        
        if (empty($deletedFiles)) {
            $this->displayInfo('No files found to clean');
            return;
        }

        $fileCount = count($deletedFiles);
        $sizeFormatted = $this->formatBytes($deletedSize);
        
        if ($dryRun) {
            $this->displayWarning("Would delete {$fileCount} files ({$sizeFormatted})");
        } else {
            $this->displaySuccess("Deleted {$fileCount} files ({$sizeFormatted})");
        }

        $this->line('');
        $this->line('<fg=white;options=bold>Cleanup Summary:</>');
        $this->line("  📊 Files processed: {$fileCount}");
        $this->line("  💾 Space freed: {$sizeFormatted}");
        
        if ($dryRun) {
            $this->line('');
            $this->displayInfo('Run without --dry-run to actually delete the files');
        }
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
