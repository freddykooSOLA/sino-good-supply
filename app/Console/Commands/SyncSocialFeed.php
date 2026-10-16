<?php

namespace App\Console\Commands;

use App\Services\SocialFeedService;
use Illuminate\Console\Command;
use Throwable;

class SyncSocialFeed extends Command
{
    protected $signature = 'social:sync';

    protected $description = 'Sync Facebook, Instagram, and YouTube feeds for the homepage.';

    public function handle(SocialFeedService $feed): int
    {
        try {
            $posts = $feed->syncAll();
            $this->info('Synced '.count($posts).' social posts.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
