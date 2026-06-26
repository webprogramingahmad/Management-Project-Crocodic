<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class TaskRevisionCycle extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'task_revision_cycles';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_task',
        'cycle_number',
        'entered_revision_at',
        'exited_revision_at',
        'deadline_at',
        'revision_hours',
        'notes',
        'links',
    ];

    public function photos()
    {
        return $this->hasMany(TaskPhoto::class, 'id_revision_cycle');
    }

    protected function casts(): array
    {
        return [
            'entered_revision_at' => 'datetime',
            'exited_revision_at' => 'datetime',
            'deadline_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'id_task');
    }
}

