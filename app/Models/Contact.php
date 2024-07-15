<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'job_title',
        'company_id',
        'user_id',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'linkedin',
        'twitter',
        'facebook',
        'photo',
        'birthday',
        'notes',
        'status',
        'source',
        'custom_fields',
        'last_contacted_at',
    ];

    protected $casts = [
        'birthday' => 'date',
        'last_contacted_at' => 'datetime',
        'custom_fields' => 'array',
    ];

    protected $appends = ['full_name', 'initials'];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class)->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->latest();
    }

    // Accessors
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => trim("{$this->first_name} {$this->last_name}")
        );
    }

    protected function initials(): Attribute
    {
        return Attribute::make(
            get: fn() => strtoupper(
                substr($this->first_name, 0, 1) . substr($this->last_name ?? '', 0, 1)
            )
        );
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return null;
    }

    public function getFullAddressAttribute(): ?string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);
        return !empty($parts) ? implode(', ', $parts) : null;
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhereHas('company', fn($q) => $q->where('name', 'like', "%{$search}%"));
        });
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByGroup($query, $groupId)
    {
        return $query->whereHas('groups', fn($q) => $q->where('groups.id', $groupId));
    }

    public function scopeRecentlyContacted($query)
    {
        return $query->whereNotNull('last_contacted_at')
                     ->orderBy('last_contacted_at', 'desc');
    }

    public function scopeNotContactedRecently($query, $days = 30)
    {
        return $query->where(function ($q) use ($days) {
            $q->whereNull('last_contacted_at')
              ->orWhere('last_contacted_at', '<', now()->subDays($days));
        });
    }

    // Methods
    public function markAsContacted(): void
    {
        $this->update(['last_contacted_at' => now()]);
    }

    public function logActivity(string $type, string $title, ?string $description = null): Activity
    {
        return $this->activities()->create([
            'user_id' => auth()->id(),
            'type' => $type,
            'title' => $title,
            'description' => $description,
        ]);
    }

    // Check for potential duplicates
    public static function findDuplicates(string $email = null, string $phone = null)
    {
        return static::query()
            ->when($email, fn($q) => $q->where('email', $email))
            ->when($phone, fn($q) => $q->orWhere('phone', $phone)->orWhere('mobile', $phone))
            ->get();
    }
}
