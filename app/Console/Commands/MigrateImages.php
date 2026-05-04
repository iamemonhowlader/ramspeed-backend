<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MigrateImages extends Command
{
    protected $signature = 'app:migrate-images';
    protected $description = 'Migrate product images from legacy project to Laravel storage';

    public function handle()
    {
        $sourcePath = 'D:/herd/Ramspeed/ramspeed-exiting-rowphp/shop-onmi-admin/uploads/product_images';
        $destPath = storage_path('app/public/product_images');

        if (!File::exists($sourcePath)) {
            $this->error("Source path does not exist: {$sourcePath}");
            return;
        }

        if (!File::exists($destPath)) {
            File::makeDirectory($destPath, 0755, true);
        }

        $files = File::files($sourcePath);
        $count = count($files);

        $this->info("Copying {$count} files...");
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($files as $file) {
            File::copy($file->getPathname(), $destPath . '/' . $file->getFilename());
            $bar->advance();
        }

        $bar->finish();
        $this->info("\nDone! Images migrated to {$destPath}");
        
        $this->call('storage:link');
    }
}
