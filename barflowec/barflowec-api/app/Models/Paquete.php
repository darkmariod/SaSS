<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paquete extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'guests_min',
        'guests_max',
        'price',
        'status',
    ];
}
