<?php

namespace App\Modules\Payments\Services;

use Illuminate\Support\Facades\Http;

class RazorpayXService
{
    public function isEnabled(): bool
    {
        return (bool) config('payments.razorpayx.enabled')
            && ! empty(config('payments.razorpayx.key_id'))
            && ! empty(config('payments.razorpayx.key_secret'));
    }

    public function createUpiPayout(array $payload): array
    {
        $contact = $this->createContact($payload['staff_name'], $payload['staff_phone'], $payload['staff_email']);
        $fundAccount = $this->createVpaFundAccount($contact['id'], $payload['staff_upi']);

        $payoutPayload = [
            'account_number' => '7878780080316316',
            'fund_account_id' => $fundAccount['id'],
            'amount' => (int) round(((float) $payload['amount']) * 100),
            'currency' => 'INR',
            'mode' => 'UPI',
            'purpose' => 'payout',
            'queue_if_low_balance' => true,
            'reference_id' => $payload['reference_id'],
            'narration' => $payload['narration'],
            'notes' => $payload['notes'] ?? [],
        ];

        return $this->request('post', '/payouts', $payoutPayload);
    }

    private function createContact(string $name, ?string $phone, ?string $email): array
    {
        return $this->request('post', '/contacts', [
            'name' => $name,
            'type' => 'employee',
            'reference_id' => 'staff_'.time(),
            'email' => $email,
            'contact' => $phone,
        ]);
    }

    private function createVpaFundAccount(string $contactId, string $upiId): array
    {
        return $this->request('post', '/fund_accounts', [
            'contact_id' => $contactId,
            'account_type' => 'vpa',
            'vpa' => [
                'address' => $upiId,
            ],
        ]);
    }

    private function request(string $method, string $uri, array $payload): array
    {
        $baseUrl = rtrim((string) config('payments.razorpayx.base_url'), '/');
        $verifySsl = (bool) config('payments.razorpayx.verify_ssl', true);
        $response = Http::withBasicAuth(
            (string) config('payments.razorpayx.key_id'),
            (string) config('payments.razorpayx.key_secret')
        )->withOptions([
            'verify' => $verifySsl,
        ])->acceptJson()->$method($baseUrl.$uri, $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('RazorpayX API failed: '.$response->status().' '.$response->body());
        }

        return $response->json();
    }
}
