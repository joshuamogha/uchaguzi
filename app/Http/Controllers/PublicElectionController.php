<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Services\ResultService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PublicElectionController extends Controller
{
    public function __construct(
        private readonly ResultService $resultService,
    ) {
    }

    public function home(): View
    {
        return view('public.home', [
            'elections' => Election::query()->with('churchGroup')->latest()->get(),
        ]);
    }

    public function candidates(Election $election): View
    {
        return view('public.candidates', [
            'election' => $election->load(['contests.candidates']),
        ]);
    }

    public function results(Election $election): View
    {
        abort_unless($election->canShowPublicResults() || Auth::check(), 403);

        return view('public.results', [
            'election' => $election,
            'summary' => $this->resultService->summary($election),
            'contestResults' => $this->resultService->contestResults($election),
        ]);
    }
}
