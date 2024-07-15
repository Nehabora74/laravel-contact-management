<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'user_id',
        'type',
        'title',
        'description',
        'scheduled_at',
        'completed_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getIconAttribute(): string
    {
        return match($this->type) {
            'call' => '📞',
            'email' => '✉️',
            'meeting' => '📅',
            'note' => '📝',
            'task' => '✅',
            default => '📌',
        };
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeUpcoming($query)
    {
        return $query->whereNotNull('scheduled_at')
                     ->whereNull('completed_at')
                     ->where('scheduled_at', '>=', now())
                     ->orderBy('scheduled_at');
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('scheduled_at')
                     ->whereNull('completed_at')
                     ->where('scheduled_at', '<', now());
    }

    public function markAsCompleted(): void
    {
        $this->update(['completed_at' => now()]);
    }
}
