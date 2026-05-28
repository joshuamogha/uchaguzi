<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChurchGroupRequest;
use App\Models\ChurchGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ChurchGroupController extends Controller
{
    public function index(): View
    {
        return view('admin.church-groups.index', [
            'groups' => ChurchGroup::query()->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.church-groups.form', [
            'group' => new ChurchGroup(['is_active' => true]),
        ]);
    }

    public function store(ChurchGroupRequest $request): RedirectResponse
    {
        ChurchGroup::create($request->validated());

        return redirect()->route('admin.church-groups.index')->with('success', 'Church group created successfully.');
    }

    public function edit(ChurchGroup $churchGroup): View
    {
        return view('admin.church-groups.form', [
            'group' => $churchGroup,
        ]);
    }

    public function update(ChurchGroupRequest $request, ChurchGroup $churchGroup): RedirectResponse
    {
        $churchGroup->update($request->validated());

        return redirect()->route('admin.church-groups.index')->with('success', 'Church group updated successfully.');
    }

    public function destroy(ChurchGroup $churchGroup): RedirectResponse
    {
        $churchGroup->delete();

        return redirect()->route('admin.church-groups.index')->with('success', 'Church group deleted successfully.');
    }
}
