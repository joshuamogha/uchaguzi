<?php

namespace App\Models;

use App\Enums\ElectionStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Election extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'church_group_id',
        'title',
        'description',
        'start_at',
        'end_at',
        'status',
        'public_results_enabled',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'status' => ElectionStatus::class,
            'public_results_enabled' => 'boolean',
        ];
    }

    public function churchGroup(): BelongsTo
    {
        return $this->belongsTo(ChurchGroup::class);
    }

    public function contests(): HasMany
    {
        return $this->hasMany(ElectionContest::class)->orderBy('sort_order')->orderBy('name');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class)->orderBy('sort_order')->orderBy('name');
    }

    public function manualTallies(): HasMany
    {
        return $this->hasMany(CandidateManualTally::class);
    }

    public function voters(): HasMany
    {
        return $this->hasMany(Voter::class);
    }

    public function ballots(): HasMany
    {
        return $this->hasMany(Ballot::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ElectionLog::class);
    }

    public function isOpenForVoting(?CarbonInterface $now = null): bool
    {
        $now ??= now();

        return $this->status === ElectionStatus::Active
            && $now->betweenIncluded($this->start_at, $this->end_at);
    }

    public function canShowPublicResults(): bool
    {
        return $this->public_results_enabled
            && ($this->status === ElectionStatus::Closed || now()->greaterThan($this->end_at));
    }
}
