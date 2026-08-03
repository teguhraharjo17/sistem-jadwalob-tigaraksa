<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'role_id',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Relationship: User belongs to a Role
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Cek apakah user memiliki role tertentu
     */
    public function hasRole($role)
    {
        return $this->role && $this->role->name === $role;
    }

    /**
     * Cek apakah user memiliki hak akses menu tertentu
     */
    public function hasPermission($permission)
    {
        return $this->role && is_array($this->role->permissions) && in_array($permission, $this->role->permissions);
    }
}
