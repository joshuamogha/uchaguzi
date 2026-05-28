<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MemberRequest;
use App\Models\Community;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(): View
    {
        return view('admin.members.index', [
            'members' => Member::query()->with('community')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.members.form', [
            'member' => new Member(['is_active' => true]),
            'communities' => Community::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(MemberRequest $request): RedirectResponse
    {
        Member::create($request->validated());

        return redirect()->route('admin.members.index')->with('success', 'Member created successfully.');
    }

    public function edit(Member $member): View
    {
        return view('admin.members.form', [
            'member' => $member,
            'communities' => Community::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(MemberRequest $request, Member $member): RedirectResponse
    {
        $member->update($request->validated());

        return redirect()->route('admin.members.index')->with('success', 'Member updated successfully.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        $member->delete();

        return redirect()->route('admin.members.index')->with('success', 'Member deleted successfully.');
    }
}
