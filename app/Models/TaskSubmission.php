<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TaskSubmission extends Model
{
    public const TYPE_WORK = 'work';

    public const TYPE_REVISION = 'revision';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_task',
        'type',
        'cycle_number',
        'notes',
        'links',
        'submitted_by',
    ];

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

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function photos()
    {
        return $this->hasMany(TaskPhoto::class, 'submission_id');
    }
}
