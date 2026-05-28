<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CommunityRequest;
use App\Models\Community;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function index(): View
    {
        return view('admin.communities.index', [
            'communities' => Community::query()->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.communities.form', [
            'community' => new Community(['is_active' => true]),
        ]);
    }

    public function store(CommunityRequest $request): RedirectResponse
    {
        Community::create($request->validated());

        return redirect()->route('admin.communities.index')->with('success', 'Community created successfully.');
    }

    public function edit(Community $community): View
    {
        return view('admin.communities.form', compact('community'));
    }

    public function update(CommunityRequest $request, Community $community): RedirectResponse
    {
        $community->update($request->validated());

        return redirect()->route('admin.communities.index')->with('success', 'Community updated successfully.');
    }

    public function destroy(Community $community): RedirectResponse
    {
        $community->delete();

        return redirect()->route('admin.communities.index')->with('success', 'Community deleted successfully.');
    }
}
