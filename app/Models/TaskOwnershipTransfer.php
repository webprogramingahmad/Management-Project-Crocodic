<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TaskOwnershipTransfer extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_task',
        'from_user_id',
        'to_user_id',
        'performed_by',
        'source',
        'request_id',
        'reason',
        'task_status_at_transfer',
        'was_overdue_at_transfer',
        'timer_reset_at',
        'previous_running_started_at',
        'previous_revision_deadline_at',
    ];

    protected function casts(): array
    {
        return [
            'was_overdue_at_transfer' => 'boolean',
            'timer_reset_at' => 'datetime',
            'previous_running_started_at' => 'datetime',
            'previous_revision_deadline_at' => 'datetime',
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

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function request()
    {
        return $this->belongsTo(TaskOwnershipTransferRequest::class, 'request_id');
    }
}
