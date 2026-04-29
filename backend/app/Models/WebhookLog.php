<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = ['provider', 'event_id', 'event_type', 'payload'];

    protected $casts = ['payload' => 'array'];
}
