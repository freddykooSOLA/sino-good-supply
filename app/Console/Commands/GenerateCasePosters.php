<?php

namespace App\Console\Commands;

use App\Models\CaseStudy;
use App\Services\CaseStudyPosterService;
use Illuminate\Console\Command;

class GenerateCasePosters extends Command
{
    protected $signature = 'cases:generate-posters {--id= : Case study ID}';

    protected $description = 'Generate poster JPG/WebP frames for uploaded case study videos.';

    public function handle(CaseStudyPosterService $posters): int
    {
        $query = CaseStudy::query()->where('video_type', CaseStudy::VIDEO_UPLOAD)->whereNotNull('video_path');
        if ($this->option('id')) {
            $query->whereKey($this->option('id'));
        }

        $count = 0;
        foreach ($query->get() as $case) {
            if ($posters->generateForCase($case)) {
                $count++;
                $this->info('Poster generated for case #'.$case->id);
            } else {
                $this->warn('Skipped case #'.$case->id);
            }
        }

        $this->info('Done. Generated '.$count.' poster(s).');

        return self::SUCCESS;
    }
}
