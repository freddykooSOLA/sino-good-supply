<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WatermarkSetting extends Model
{
    public const PATTERN_CENTERED = 'centered';

    public const PATTERN_TILED = 'tiled';

    protected $fillable = [
        'image_path',
        'size',
        'opacity',
        'pattern',
        'spacing',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'opacity' => 'integer',
            'spacing' => 'integer',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'image_path' => null,
            'size' => 140,
            'opacity' => 18,
            'pattern' => self::PATTERN_TILED,
            'spacing' => 90,
        ]);
    }

    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    public function imageAbsolutePath(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        $path = Storage::disk('public')->path($this->image_path);

        return is_file($path) ? $path : null;
    }

    public function isTiled(): bool
    {
        return $this->pattern === self::PATTERN_TILED;
    }

    public function frontendConfig(): array
    {
        return [
            'image' => $this->imageUrl(),
            'size' => max(24, (int) $this->size),
            'opacity' => max(0, min(100, (int) $this->opacity)) / 100,
            'pattern' => $this->isTiled() ? self::PATTERN_TILED : self::PATTERN_CENTERED,
            'spacing' => max(0, (int) $this->spacing),
            'text' => 'SINO GOOD',
        ];
    }
}
