<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Jobs\SendSMSJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'managedUser' => new User([
                'is_admin' => false,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $plainPassword = Str::password(8, letters: true, numbers: true, symbols: false);
        $data['password'] = Hash::make($plainPassword);

        $user = User::create($data);
        $url=url('login');
        $message="Habari {$user->name}, akaunti yako ya uchaguzi imefunguliwa. Barua pepe: {$user->email}. Nenosiri lako ni: {$plainPassword} na kiungo cha kuingia: {$url}";

        SendSMSJob::dispatch($user, $message)->delay(Carbon::now()->addSeconds(3));

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'managedUser' => $user,
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (! filled($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }
}
