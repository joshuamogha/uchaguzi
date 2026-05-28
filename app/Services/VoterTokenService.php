<?php

namespace App\Services;

use App\Models\Voter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VoterTokenService
{
    public function issueCredentials(Voter $voter, bool $generatePin = true): array
    {
        $plainToken = Str::random(48);
        $plainPin = $generatePin ? (string) random_int(100000, 999999) : null;

        $voter->forceFill([
            'token_hash' => hash('sha256', $plainToken),
            'pin_hash' => $plainPin ? Hash::make($plainPin) : null,
        ])->save();

        return [
            'token' => $plainToken,
            'pin' => $plainPin,
        ];
    }

    public function findByPlainToken(string $plainToken): ?Voter
    {
        return Voter::query()
            ->with(['election', 'member'])
            ->where('token_hash', hash('sha256', $plainToken))
            ->first();
    }

    public function verifyPin(Voter $voter, ?string $pin): bool
    {
        if (! $voter->requiresPin()) {
            return true;
        }

        if (! filled($pin)) {
            return false;
        }

        return Hash::check((string) $pin, $voter->pin_hash);
    }
}
