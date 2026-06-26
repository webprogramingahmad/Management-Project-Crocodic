<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Task extends Model
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
        'id_user',
        'id_project',
        'description',
        'id_status',
        'id_difficulty',
        'created_by',
        'running_started_at',
        'running_review_at',
        'revision_deadline_at',
        'revision_hours',
    ];

    protected function casts(): array
    {
        return [
            'running_started_at' => 'datetime',
            'running_review_at' => 'datetime',
            'revision_deadline_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'id_project');
    }

    public function difficulty()
    {
        return $this->belongsTo(TaskDifficulty::class, 'id_difficulty');
    }

    public function status()
    {
        return $this->belongsTo(StatusTask::class, 'id_status', 'id', 'status_tasks');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function revisionCycles()
    {
        return $this->hasMany(TaskRevisionCycle::class, 'id_task')->orderBy('cycle_number');
    }

    public function photos()
    {
        return $this->hasMany(TaskPhoto::class, 'id_task')->latest();
    }

    public function submissions()
    {
        return $this->hasMany(TaskSubmission::class, 'id_task')->orderBy('cycle_number');
    }

    public function ownershipTransferRequests()
    {
        return $this->hasMany(TaskOwnershipTransferRequest::class, 'id_task');
    }

    public function pendingOwnershipTransferRequest()
    {
        return $this->hasOne(TaskOwnershipTransferRequest::class, 'id_task')
            ->where('status', TaskOwnershipTransferRequest::STATUS_PENDING);
    }

    public function ownershipTransfers()
    {
        return $this->hasMany(TaskOwnershipTransfer::class, 'id_task');
    }

    /**
     * Task difficulty "Stand By" hanya memicu status SDM; tidak ditampilkan di board / dashboard / notifikasi.
     */
    public function scopeExcludingStandByDifficulty(Builder $query): Builder
    {
        return $query->whereDoesntHave('difficulty', function ($q) {
            $q->where('difficulty', 'Stand By');
        });
    }
}
