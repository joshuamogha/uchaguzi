<?php

namespace App\Http\Middleware;

use App\Models\Election;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVotingSessionIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Election|null $election */
        $election = $request->route('election');

        if (! $request->session()->get('verified_for_voting')) {
            return redirect()->route('vote.verify.form')
                ->withErrors(['token' => 'Please verify your voting token to continue.']);
        }

        if ($election && (int) $request->session()->get('voting_election_id') !== $election->id) {
            return redirect()->route('vote.verify.form')
                ->withErrors(['token' => 'Your verification session does not match this election.']);
        }

        return $next($request);
    }
}
