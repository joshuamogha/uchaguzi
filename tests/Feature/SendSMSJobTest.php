<?php

use App\Jobs\SendSMSJob;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('sms job formats phone number to start with 255 before sending', function () {
    Http::fake([
        'https://api.sprintsmsservice.com/api/SendSMS*' => Http::response([
            'status' => 'S',
            'description' => 'Queued',
        ], 200),
    ]);

    $user = new User([
        'name' => 'SMS User',
        'email' => 'sms@example.com',
        'phone_number' => '0712345678',
    ]);

    $job = new SendSMSJob($user, 'Test message');
    $job->handle();

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'phonenumber=255712345678');
    });
});

test('sms job keeps valid 255 phone numbers normalized', function () {
    Http::fake([
        'https://api.sprintsmsservice.com/api/SendSMS*' => Http::response([
            'status' => 'S',
            'description' => 'Queued',
        ], 200),
    ]);

    $user = new User([
        'name' => 'SMS User',
        'email' => 'sms@example.com',
        'phone_number' => '+255 712 345 678',
    ]);

    $job = new SendSMSJob($user, 'Test message');
    $job->handle();

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'phonenumber=255712345678');
    });
});
