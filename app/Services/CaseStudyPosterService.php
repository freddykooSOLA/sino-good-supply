<?php

namespace App\Services;

use App\Models\CaseStudy;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CaseStudyPosterService
{
    public function generateForCase(CaseStudy $case): bool
    {
        if (! $case->isUploadedVideo() || ! $case->video_path) {
            return false;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($case->video_path)) {
            return false;
        }

        $videoPath = $disk->path($case->video_path);
        $duration = $this->probeDuration($videoPath);
        $timestamp = $this->pickTimestamp($duration);

        $base = 'case-posters/'.Str::slug(pathinfo($case->video_path, PATHINFO_FILENAME)).'-'.$case->id;
        $jpgRelative = $base.'.jpg';
        $webpRelative = $base.'.webp';
        $jpgAbsolute = $disk->path($jpgRelative);
        $webpAbsolute = $disk->path($webpRelative);

        $disk->makeDirectory('case-posters');

        $ffmpeg = $this->ffmpegBinary();
        $result = Process::timeout(120)->run([
            $ffmpeg,
            '-hide_banner',
            '-loglevel',
            'error',
            '-ss',
            (string) $timestamp,
            '-i',
            $videoPath,
            '-frames:v',
            '1',
            '-vf',
            'scale=1920:-2',
            '-q:v',
            '2',
            $jpgAbsolute,
        ]);

        if (! $result->successful() || ! is_file($jpgAbsolute)) {
            return false;
        }

        Process::timeout(60)->run([
            $ffmpeg,
            '-hide_banner',
            '-loglevel',
            'error',
            '-i',
            $jpgAbsolute,
            '-quality',
            '85',
            $webpAbsolute,
        ]);

        if ($case->poster_path && $case->poster_path !== $jpgRelative) {
            $disk->delete($case->poster_path);
        }
        if ($case->poster_webp_path && $case->poster_webp_path !== $webpRelative) {
            $disk->delete($case->poster_webp_path);
        }

        $case->forceFill([
            'poster_path' => $jpgRelative,
            'poster_webp_path' => is_file($webpAbsolute) ? $webpRelative : null,
        ])->saveQuietly();

        return true;
    }

    protected function probeDuration(string $videoPath): float
    {
        $ffprobe = str_replace('ffmpeg', 'ffprobe', $this->ffmpegBinary());
        $result = Process::timeout(30)->run([
            $ffprobe,
            '-v',
            'error',
            '-show_entries',
            'format=duration',
            '-of',
            'default=noprint_wrappers=1:nokey=1',
            $videoPath,
        ]);

        return max(1.0, (float) trim($result->output()));
    }

    protected function pickTimestamp(float $duration): float
    {
        $candidates = [
            $duration * 0.25,
            $duration * 0.32,
            $duration * 0.4,
        ];

        return max(0.5, $candidates[1] ?? $duration * 0.3);
    }

    protected function ffmpegBinary(): string
    {
        $configured = config('services.ffmpeg.path', 'ffmpeg');
        if (filled($configured)) {
            return (string) $configured;
        }

        return 'ffmpeg';
    }
}
