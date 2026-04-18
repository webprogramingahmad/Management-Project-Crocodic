<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class Project extends Model
{
    use HasFactory, Notifiable;

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
        'name',
        'start_date',
        'end_date',
        'id_difficulty',
        'id_status',
        'description',
        'id_director',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function status()
    {
        return $this->belongsTo(StatusProject::class, 'id_status');
    }

    public function difficulty()
    {
        return $this->belongsTo(ProjectDifficulty::class, 'id_difficulty');
    }

    public function director()
    {
        return $this->belongsTo(User::class, 'id_director');
    }

    public function sdms()
    {
        return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'id_project');
    }

    /** Status project yang boleh dipakai untuk create & transfer task: Running + Maintenance. */
    public static function taskAllowedStatusClasses(): array
    {
        return ['running', 'maintenance'];
    }

    public function scopeWhereAllowsTaskCreation(Builder $query): Builder
    {
        return $query->whereHas('status', fn ($q) => $q->whereIn('class', self::taskAllowedStatusClasses()));
    }

    public function allowsTaskCreation(): bool
    {
        if (! $this->relationLoaded('status')) {
            $this->load('status');
        }

        return in_array($this->status?->class, self::taskAllowedStatusClasses(), true);
    }

    /** Validasi `id_project` untuk create/transfer task (Running atau Maintenance). */
    public static function ruleExistsIdForTaskCreation(): \Illuminate\Validation\Rules\Exists
    {
        $statusIds = StatusProject::query()
            ->whereIn('class', self::taskAllowedStatusClasses())
            ->pluck('id')
            ->all();

        if ($statusIds === []) {
            return Rule::exists('projects', 'id')->whereRaw('0 = 1');
        }

        return Rule::exists('projects', 'id')->where(fn ($q) => $q->whereIn('id_status', $statusIds));
    }
}
