<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CandidateRequest;
use App\Models\Election;
use App\Models\ElectionContest;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CandidateController extends Controller
{
    public function index(Election $election): View
    {
        $this->authorize('view', $election);

        return view('admin.elections.candidates.index', [
            'election' => $election,
            'contests' => $election->contests()->with('candidates.member')->get(),
        ]);
    }

    public function create(Election $election, ElectionContest $contest): View
    {
        $this->authorize('update', $election);

        return view('admin.elections.candidates.form', [
            'election' => $election,
            'contest' => $contest,
            'candidate' => $contest->candidates()->make([
                'election_id' => $election->id,
                'is_active' => true,
                'sort_order' => ($contest->candidates()->max('sort_order') ?? 0) + 1,
            ]),
            'members' => Member::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(CandidateRequest $request, Election $election, ElectionContest $contest): RedirectResponse
    {
        $this->authorize('update', $election);

        $data = $request->validated();
        $data['election_id'] = $election->id;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('candidates', 'public');
        }

        $contest->candidates()->create($data);

        return redirect()->route('admin.elections.candidates.index', $election)->with('success', 'Candidate added successfully.');
    }

    public function edit(Election $election, ElectionContest $contest, \App\Models\Candidate $candidate): View
    {
        $this->authorize('update', $election);

        return view('admin.elections.candidates.form', [
            'election' => $election,
            'contest' => $contest,
            'candidate' => $candidate,
            'members' => Member::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(CandidateRequest $request, Election $election, ElectionContest $contest, \App\Models\Candidate $candidate): RedirectResponse
    {
        $this->authorize('update', $election);

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($candidate->photo) {
                Storage::disk('public')->delete($candidate->photo);
            }

            $data['photo'] = $request->file('photo')->store('candidates', 'public');
        }

        $candidate->update($data);

        return redirect()->route('admin.elections.candidates.index', $election)->with('success', 'Candidate updated successfully.');
    }

    public function destroy(Election $election, ElectionContest $contest, \App\Models\Candidate $candidate): RedirectResponse
    {
        $this->authorize('update', $election);

        if ($candidate->photo) {
            Storage::disk('public')->delete($candidate->photo);
        }

        $candidate->delete();

        return redirect()->route('admin.elections.candidates.index', $election)->with('success', 'Candidate removed successfully.');
    }
}
