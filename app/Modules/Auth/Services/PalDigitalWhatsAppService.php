<?php

namespace App\Modules\Auth\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Send OTP via Pal Digital WhatsApp API campaign (Meta authentication template).
 *
 * POST /api/v1/integrations/campaigns/{campaign_id}/trigger
 */
class PalDigitalWhatsAppService
{
    public function __construct(
        private PhoneNormalizer $phoneNormalizer,
    ) {}

    public function isConfigured(): bool
    {
        $cfg = config('services.pal_digital', []);
        $key = trim((string) ($cfg['integration_key'] ?? ''));
        $campaignId = trim((string) ($cfg['campaign_id'] ?? ''));

        return $key !== '' && $campaignId !== '';
    }

    /**
     * @return array{success: bool, message: ?string}
     */
    public function sendVerificationOtp(string $destinationPhone, string $otp, ?string $contactName = null): array
    {
        if (! preg_match('/^\d{6}$/', $otp)) {
            return ['success' => false, 'message' => 'Invalid OTP format.'];
        }

        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'WhatsApp OTP is not configured. Set PAL_DIGITAL_INTEGRATION_KEY and PAL_DIGITAL_CAMPAIGN_ID in .env.',
            ];
        }

        $e164 = $this->phoneNormalizer->toE164($destinationPhone);
        if ($e164 === null) {
            return ['success' => false, 'message' => 'Invalid destination phone for WhatsApp.'];
        }

        $campaignId = trim((string) config('services.pal_digital.campaign_id'));
        $baseUrl = rtrim((string) config('services.pal_digital.base_url', 'https://wa.paldigital.in'), '/');
        $url = $baseUrl.'/api/v1/integrations/campaigns/'.$campaignId.'/trigger';

        $payload = [
            'to_phone_e164' => $e164,
            'name' => trim((string) ($contactName ?: config('services.pal_digital.default_contact_name', 'MMHC User'))),
            'body_parameters' => [
                [
                    'type' => 'text',
                    'text' => $otp,
                ],
            ],
        ];

        if (config('services.pal_digital.include_button_parameters', true)) {
            // Meta authentication templates (copy-code button) require the same OTP in button params.
            $payload['button_parameters'] = [
                [
                    'type' => 'text',
                    'text' => $otp,
                ],
            ];
        }

        try {
            $client = Http::timeout(25)->acceptJson();

            $caBundle = config('services.pal_digital.ca_bundle');
            if (is_string($caBundle) && $caBundle !== '' && is_file($caBundle)) {
                $client = $client->withOptions(['verify' => $caBundle]);
            } elseif (! config('services.pal_digital.http_verify', true) && app()->isLocal()) {
                $client = $client->withoutVerifying();
            }

            $response = $client
                ->withHeaders([
                    'X-Integration-Key' => trim((string) config('services.pal_digital.integration_key')),
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $payload);

            if ($response->successful()) {
                $json = $response->json();
                if (is_array($json) && ($json['success'] ?? true) === false) {
                    return [
                        'success' => false,
                        'message' => $this->formatErrorMessage($json) ?? 'WhatsApp provider rejected the message.',
                    ];
                }

                if (config('app.debug')) {
                    Log::info('Pal Digital WhatsApp: accepted', [
                        'to' => $e164,
                        'status' => $response->status(),
                        'body' => Str::limit($response->body(), 500),
                    ]);
                }

                return ['success' => true, 'message' => null];
            }

            Log::warning('Pal Digital WhatsApp: non-success HTTP', [
                'status' => $response->status(),
                'body' => Str::limit($response->body(), 800),
            ]);

            $json = $response->json();

            return [
                'success' => false,
                'message' => $this->formatErrorMessage(is_array($json) ? $json : null)
                    ?? 'WhatsApp delivery failed (HTTP '.$response->status().'). Check PAL_DIGITAL_INTEGRATION_KEY and campaign ID.',
            ];
        } catch (\Throwable $e) {
            Log::error('Pal Digital WhatsApp: request exception', [
                'message' => $e->getMessage(),
            ]);

            $hint = config('app.debug') ? ' '.$e->getMessage() : '';

            return ['success' => false, 'message' => 'Could not reach WhatsApp provider.'.$hint];
        }
    }

    public function normalizeToE164(string $raw): ?string
    {
        return $this->phoneNormalizer->toE164($raw);
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    private function formatErrorMessage(?array $json): ?string
    {
        if ($json === null) {
            return null;
        }

        foreach (['message', 'error', 'detail'] as $key) {
            if (isset($json[$key]) && is_string($json[$key]) && $json[$key] !== '') {
                return $json[$key];
            }
        }

        if (isset($json['error']) && is_array($json['error'])) {
            $msg = $json['error']['message'] ?? null;
            if (is_string($msg) && $msg !== '') {
                return $msg;
            }
        }

        return null;
    }
}
