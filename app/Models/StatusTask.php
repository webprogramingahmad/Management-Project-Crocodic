<?php

namespace App\Models;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class StatusTask extends Model
{
    use HasFactory, Notifiable;

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
}
