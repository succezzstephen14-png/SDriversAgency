<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AssignmentActivity extends Model
{
    protected $fillable = [
        'user_id',
        'entry_type',
        'status',
        'rating',
        'message',
    ];

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
