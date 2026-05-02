<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class StatusProject extends Model
{
    use HasFactory, Notifiable;

    /**
     * @var array<string, list<string>>
     */
    private const LEGACY_LABELS = [
        'todo' => ['To do', 'To Do', 'Todo'],
        'running' => ['Running'],
        'maintenance' => ['Maintenance'],
        'completed' => ['Completed', 'Complete'],
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'status',
        'class'
    ];

    public function getBackgroundColorAttribute()
    {
        $raw = strtolower(($this->status ?? '') . ' ' . ($this->class ?? ''));

        if (str_contains($raw, 'maintenance')) {
            return '#E0E0E0';
        }

        if (str_contains($raw, 'running')) {
            return '#038C8C';
        }

        if (str_contains($raw, 'complete') || str_contains($raw, 'finish')) {
            return '#7DB546';
        }

        if (str_contains($raw, 'to do') || str_contains($raw, 'todo') || str_contains($raw, 'not')) {
            return '#EA4949';
        }

        return '#6c757d';
    }

    public function getTextColorAttribute()
    {
        $raw = strtolower(($this->status ?? '') . ' ' . ($this->class ?? ''));

        if (str_contains($raw, 'maintenance')) {
            return '#000000';
        }

        return '#fff';
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * @return list<string>
     */
    public static function legacyLabelsForClass(string $class): array
    {
        return self::LEGACY_LABELS[strtolower($class)] ?? [];
    }

    public function scopeWhereClassOrLegacy(Builder $query, string $class): Builder
    {
        $labels = self::legacyLabelsForClass($class);

        return $query->where(function (Builder $q) use ($class, $labels): void {
            $q->where('class', strtolower($class));
            if ($labels !== []) {
                $q->orWhereIn('status', $labels);
            }
        });
    }

    public static function firstByClass(string $class): ?self
    {
        return self::query()->whereClassOrLegacy($class)->first();
    }
}
