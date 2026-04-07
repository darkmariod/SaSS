<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = ['name'];

    // Un rol tiene muchos usuarios
    public function users()
    {
        return $this->hasMany(User::class, 'rol_id');
    }
}
