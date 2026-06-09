<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('admin can create and update users', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Ballot Clerk',
            'email' => 'clerk@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_admin' => 0,
            'is_active' => 1,
        ])
        ->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'clerk@example.com')->firstOrFail();

    expect($user->is_admin)->toBeFalse();
    expect($user->is_active)->toBeTrue();
    expect(Hash::check('password123', $user->password))->toBeTrue();

    $this->actingAs($admin)
        ->put(route('admin.users.update', $user), [
            'name' => 'Ballot Clerk Updated',
            'email' => 'clerk@example.com',
            'password' => '',
            'password_confirmation' => '',
            'is_admin' => 0,
            'is_active' => 0,
        ])
        ->assertRedirect(route('admin.users.index'));

    $user->refresh();

    expect($user->name)->toBe('Ballot Clerk Updated');
    expect($user->is_active)->toBeFalse();
});

test('inactive users cannot log in', function () {
    $user = User::factory()->create([
        'email' => 'inactive@example.com',
        'password' => Hash::make('password123'),
        'is_admin' => false,
        'is_active' => false,
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('admin cannot deactivate their own account', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => '',
            'password_confirmation' => '',
            'is_admin' => 1,
            'is_active' => 0,
        ])
        ->assertSessionHasErrors('is_active');
});
