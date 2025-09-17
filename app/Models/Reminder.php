<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Tappable;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',       // Add this
        'task',
        'reminder_date',
        'timezone',
        'frequency',
        'description',
        'status',
    ];
}
