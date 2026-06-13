<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class SendSMSJob implements ShouldQueue
{
    use Queueable;

      public int $tries = 3;

    public int $timeout = 15;

    public array $backoff = [5, 30, 120];

    /**
     * Create a new job instance.
     */
    public function __construct(public User $user,public string  $message)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $apiId = config('services.sms.api_id');
        $password = config('services.sms.api_password');
        $senderId = config('services.sms.sender_id');

        $response = Http::timeout($this->timeout)
            ->retry($this->tries, 200)
            ->get('https://api.sprintsmsservice.com/api/SendSMS', [
                'api_id' => $apiId,
                'api_password' => $password,
                'sms_type' => 'T',
                'encoding' => 'T',
                'sender_id' => $senderId,
                'phonenumber' => $this->formatPhoneNumber($this->user->phone_number),
                'textmessage' => $this->message,
            ]);

        if (! $response->successful()) {
            return [
                'success' => false,
                'error' => 'HTTP failure: '.$response->status(),
            ];
        }

        $json = $response->json();
        if (! is_array($json)) {
            return [
                'success' => false,
                'error' => 'Invalid provider response',
            ];
        }

        if (($json['status'] ?? null) !== 'S') {
            return [
                'success' => false,
                'error' => 'Provider failure: '.($json['description'] ?? 'Unknown error'),
            ];
        }

        return [
            'success' => true,
            'provider_response' => $json,
        ];
    }

    private function formatPhoneNumber(?string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phoneNumber) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '255')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '255'.substr($digits, 1);
        }

        if (str_starts_with($digits, '7') && strlen($digits) === 9) {
            return '255'.$digits;
        }

        return $digits;
    }
}
