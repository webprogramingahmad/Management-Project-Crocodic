<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
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
        'nik',
        'email',
        'tgl_lahir',
        'tgl_masuk',
        'link_tele',
        'no_telp',
        'alamat',
        'id_divisi',
        'id_role',
        'id_status_sdm',
        'id_activity_status_sdm',
        'id_graduate',
        'password',
        'avatar'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tgl_lahir' => 'date',
            'tgl_masuk' => 'date'
        ];
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'id_divisi');
    }

    public function statussdm()
    {
        return $this->belongsTo(Statussdm::class, 'id_status_sdm');
    }

    public function activityStatussdm()
    {
        return $this->belongsTo(Statussdm::class, 'id_activity_status_sdm');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role');
    }

    public function graduate()
    {
        return $this->belongsTo(LastGraduate::class, 'id_graduate');
    }
    public function administrations()
    {
        return $this->hasMany(Administration::class, 'id_user');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'id_user');
    }

    public function scopeFilter(Builder $query, $filters)
    {
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user', 'user_id', 'project_id');
    }

    public function directedProjects()
    {
        return $this->hasMany(Project::class, 'id_director');
    }
}
