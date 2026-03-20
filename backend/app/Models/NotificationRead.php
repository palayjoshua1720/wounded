<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationRead extends Model
{
    protected $table = 'woundmed_notification_reads';

    protected $fillable = [
        'user_id',
        'notification_id',
        'read_at',
    ];

    /**
     * Get the user that read the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
