<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class TaskDifficulty extends Model
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
        'difficulty',
        'class',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class, 'id_difficulty');
    }

    public function getBackgroundColorAttribute()
    {
        return match (strtolower($this->difficulty)) {
            'low' => '#6FAEC9',
            'medium' => '#FFB42E',
            'high' => '#EA4949',
            default => '#6c757d', // secondary/abu-abu
        };
    }

    public function getTextColorAttribute()
    {
        return '#fff';
    }
}
