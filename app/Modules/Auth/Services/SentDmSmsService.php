<?php

namespace App\Modules\Auth\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Sends SMS via Sent.dm v3 messages API (template-based OTP).
 *
 * @see https://docs.sent.dm/reference/api/messages/SentDmServicesEndpointsCustomerAPIv3MessagesSendMessageV3Endpoint
 */
class SentDmSmsService
{
    public function isConfigured(): bool
    {
        $cfg = config('services.sent_dm', []);
        $key = trim((string) ($cfg['api_key'] ?? ''));
        $tid = trim((string) ($cfg['template_id'] ?? ''));

        return $key !== '' && $tid !== '';
    }

    /**
     * @param  array<string, string>  $parameters  Keys must match the Sent template (e.g. code, otp).
     * @return array{success: bool, message: ?string}
     */
    public function sendTemplateSms(string $toE164, array $parameters): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'SMS is not configured. Set SENT_DM_API_KEY and SENT_DM_TEMPLATE_ID in .env.',
            ];
        }

        $normalizedTo = $this->normalizeToE164($toE164);
        if ($normalizedTo === null) {
            return ['success' => false, 'message' => 'Invalid destination phone for SMS.'];
        }

        $apiKey = trim((string) config('services.sent_dm.api_key'));
        $templateId = trim((string) config('services.sent_dm.template_id'));
        $baseUrl = rtrim((string) config('services.sent_dm.base_url', 'https://api.sent.dm/v3/messages'), '/');

        $payload = [
            'to' => [$normalizedTo],
            'channel' => ['sms'],
            'template' => [
                'id' => $templateId,
                'parameters' => $parameters,
            ],
        ];
        if (config('services.sent_dm.sandbox', false)) {
            $payload['sandbox'] = true;
        }

        try {
            $client = Http::timeout(20)->acceptJson();

            $caBundle = config('services.sent_dm.ca_bundle');
            if (is_string($caBundle) && $caBundle !== '' && is_file($caBundle)) {
                $client = $client->withOptions(['verify' => $caBundle]);
            } elseif (! config('services.sent_dm.http_verify', true) && app()->isLocal()) {
                $client = $client->withoutVerifying();
            }

            $response = $client
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                    'Idempotency-Key' => (string) Str::uuid(),
                ])
                ->post($baseUrl, $payload);

            $status = $response->status();
            $json = $response->json();

            if ($response->successful()) {
                if (! is_array($json)) {
                    Log::warning('Sent.dm SMS: success HTTP but body is not JSON', [
                        'status' => $status,
                        'body_preview' => Str::limit($response->body(), 500),
                    ]);

                    return [
                        'success' => false,
                        'message' => 'SMS provider returned an unexpected response (HTTP '.$status.'). Check storage/logs.',
                    ];
                }
                if (($json['success'] ?? false) !== true) {
                    return [
                        'success' => false,
                        'message' => $this->formatSentError($json)
                            ?? 'SMS was not accepted by the provider (HTTP '.$status.').',
                    ];
                }

                return ['success' => true, 'message' => null];
            }

            Log::warning('Sent.dm SMS: non-success HTTP', [
                'status' => $status,
                'body' => $response->body(),
            ]);

            $fromBody = $this->formatSentError(is_array($json) ? $json : null);

            return [
                'success' => false,
                'message' => $fromBody
                    ?? 'SMS failed (HTTP '.$status.'). Check SENT_DM_API_KEY, SENT_DM_TEMPLATE_ID, account balance, and that SENT_DM_OTP_PARAMETER_NAME matches your Sent template.',
            ];
        } catch (\Throwable $e) {
            Log::error('Sent.dm SMS: request exception', [
                'message' => $e->getMessage(),
            ]);

            $hint = config('app.debug') ? ' '.$e->getMessage() : '';

            return ['success' => false, 'message' => 'Could not reach SMS provider.'.$hint];
        }
    }

    /**
     * Normalize Indian and common inputs to E.164 for Sent `to` (e.g. +919876543210).
     */
    public function normalizeToE164(string $raw): ?string
    {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            return null;
        }
        if (str_starts_with($trimmed, '+')) {
            $digits = preg_replace('/\D+/', '', substr($trimmed, 1));
            if ($digits === '' || strlen($digits) < 10) {
                return null;
            }

            return '+'.$digits;
        }

        $digits = preg_replace('/\D+/', '', $trimmed);
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return '+'.$digits;
        }
        if (strlen($digits) === 10 && preg_match('/^[6-9]/', $digits)) {
            return '+91'.$digits;
        }
        if (strlen($digits) > 10) {
            $last = substr($digits, -10);
            if (strlen($last) === 10 && preg_match('/^[6-9]/', $last)) {
                return '+91'.$last;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    private function formatSentError(?array $json): ?string
    {
        if ($json === null) {
            return null;
        }
        $msg = $json['error']['message'] ?? $json['message'] ?? null;
        if (! is_string($msg)) {
            $msg = '';
        }
        $details = $json['error']['details'] ?? null;
        $detailStr = $this->formatSentErrorDetails($details);
        if ($msg !== '' && $detailStr !== '') {
            return $msg.' — '.$detailStr;
        }
        if ($msg !== '') {
            return $msg;
        }
        if ($detailStr !== '') {
            return $detailStr;
        }
        $code = $json['error']['code'] ?? null;
        if (is_string($code) && $code !== '') {
            return 'SMS provider error: '.$code;
        }

        return null;
    }

    /**
     * @param  mixed  $details
     */
    private function formatSentErrorDetails(mixed $details): string
    {
        if (! is_array($details) || $details === []) {
            return '';
        }
        $parts = [];
        foreach ($details as $field => $messages) {
            if (is_array($messages)) {
                $parts[] = $field.': '.implode(', ', array_map('strval', $messages));
            } elseif (is_string($messages) && $messages !== '') {
                $parts[] = $field.': '.$messages;
            }
        }

        return implode(' | ', $parts);
    }
}
