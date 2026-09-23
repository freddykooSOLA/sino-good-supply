<?php

namespace App\Console\Commands;

use App\Services\FacebookFeedService;
use Illuminate\Console\Command;
use Throwable;

class SyncFacebookFeed extends Command
{
    protected $signature = 'facebook:sync';

    protected $description = 'Fetch the latest Facebook Page posts for the homepage.';

    public function handle(FacebookFeedService $feed): int
    {
        try {
            $posts = $feed->sync();
            $this->info('Synced '.count($posts).' Facebook posts.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $feed->recordError($e->getMessage());
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
