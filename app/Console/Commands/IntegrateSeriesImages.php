<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class IntegrateSeriesImages extends Command
{
    protected $signature = 'series:integrate-images';

    protected $description = 'Copy staging product images into public/series and update Series records (SG-001)';

    public function handle(): int
    {
        require_once base_path('scripts/integrate_series_images.php');

        runSeriesImagesIntegration();

        return self::SUCCESS;
    }
}
