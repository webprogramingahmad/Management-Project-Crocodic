<?php

namespace App\Models;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class StatusTask extends Model
{
    use HasFactory, Notifiable;

    /**
     * @var array<string, list<string>>
     */
    private const LEGACY_LABELS = [
        'todo' => ['To Do', 'To do'],
        'progress' => ['In progress'],
        'review' => ['Review'],
        'revision' => ['Revision'],
        'complete' => ['Complete'],
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'status_tasks';

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
        return match (strtolower($this->class ?? $this->status)) {
            'todo', 'to do' => '#EA4949',
            'progress', 'in progress' => '#FFB42E',
            'review' => '#6FAEC9',
            'revision' => '#C2410C',
            'complete' => '#7DB546',
            default => '#6c757d',
        };
    }

    public function getTextColorAttribute()
    {
        return '#fff';
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
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
