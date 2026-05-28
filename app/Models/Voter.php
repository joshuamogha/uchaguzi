<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voter extends Model
{
    use HasFactory;

    protected $fillable = [
        'election_id',
        'member_id',
        'phone_number',
        'token_hash',
        'pin_hash',
        'is_eligible',
        'has_voted',
        'verified_at',
        'voted_at',
        'token_used_at',
    ];

    protected function casts(): array
    {
        return [
            'is_eligible' => 'boolean',
            'has_voted' => 'boolean',
            'verified_at' => 'datetime',
            'voted_at' => 'datetime',
            'token_used_at' => 'datetime',
        ];
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function otpVerifications(): HasMany
    {
        return $this->hasMany(OtpVerification::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ElectionLog::class);
    }

    public function requiresPin(): bool
    {
        return filled($this->pin_hash);
    }
}
