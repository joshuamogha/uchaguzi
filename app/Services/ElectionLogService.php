<?php

namespace App\Services;

use App\Models\Election;
use App\Models\ElectionLog;
use App\Models\User;
use App\Models\Voter;
use Illuminate\Http\Request;

class ElectionLogService
{
    public function log(
        string $action,
        ?string $description = null,
        ?Election $election = null,
        ?Voter $voter = null,
        ?User $user = null,
        ?Request $request = null,
    ): ElectionLog {
        return ElectionLog::create([
            'election_id' => $election?->id,
            'voter_id' => $voter?->id,
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
