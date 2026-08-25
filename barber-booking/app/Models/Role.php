<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Rol propio de la aplicación, apuntado por users.role_id.
 *
 * Convive con los roles de Spatie (tablas con prefijo `spatie_`), que son los
 * que resuelven los permisos del panel. Este modelo se usa al aprovisionar una
 * barbería nueva.
 */
class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
