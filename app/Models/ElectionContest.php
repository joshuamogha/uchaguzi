<?php

namespace App\Models;

use App\Enums\ContestType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectionContest extends Model
{
    use HasFactory;

    protected $fillable = [
        'election_id',
        'community_id',
        'name',
        'contest_type',
        'min_selections',
        'max_selections',
        'required_selections',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'contest_type' => ContestType::class,
            'is_active' => 'boolean',
        ];
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class)->orderBy('sort_order')->orderBy('name');
    }

    public function selections(): HasMany
    {
        return $this->hasMany(BallotSelection::class);
    }
}
