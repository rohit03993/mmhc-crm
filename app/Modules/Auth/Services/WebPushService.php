<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    public function isConfigured(): bool
    {
        if (! config('webpush.enabled')) {
            return false;
        }

        if (! class_exists(WebPush::class)) {
            return false;
        }

        $public = (string) config('webpush.vapid.public_key');
        $private = (string) config('webpush.vapid.private_key');

        return $public !== '' && $private !== '';
    }

    public function publicKey(): ?string
    {
        $key = (string) config('webpush.vapid.public_key');

        return $key !== '' ? $key : null;
    }

    /**
     * Store or refresh a browser push subscription for the user.
     */
    public function subscribe(int $userId, array $payload, ?string $userAgent = null): PushSubscription
    {
        $endpoint = (string) ($payload['endpoint'] ?? '');
        $keys = $payload['keys'] ?? [];
        $publicKey = (string) ($keys['p256dh'] ?? '');
        $authToken = (string) ($keys['auth'] ?? '');
        $encoding = (string) ($payload['contentEncoding'] ?? $payload['content_encoding'] ?? 'aesgcm');

        if ($endpoint === '' || $publicKey === '' || $authToken === '') {
            throw new \InvalidArgumentException('Invalid push subscription payload.');
        }

        return PushSubscription::updateOrCreate(
            [
                'endpoint_hash' => PushSubscription::hashEndpoint($endpoint),
            ],
            [
                'user_id' => $userId,
                'endpoint' => $endpoint,
                'public_key' => $publicKey,
                'auth_token' => $authToken,
                'content_encoding' => $encoding ?: 'aesgcm',
                'user_agent' => $userAgent ? mb_substr($userAgent, 0, 255) : null,
                'last_used_at' => now(),
            ]
        );
    }

    public function unsubscribe(int $userId, string $endpoint): void
    {
        PushSubscription::query()
            ->where('user_id', $userId)
            ->where('endpoint_hash', PushSubscription::hashEndpoint($endpoint))
            ->delete();
    }

    /**
     * Send a Web Push to all browser subscriptions for a user.
     */
    public function sendToUser(int $userId, string $title, ?string $body = null, ?string $url = null, array $extra = []): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $subs = PushSubscription::query()->where('user_id', $userId)->get();
        if ($subs->isEmpty()) {
            return;
        }

        try {
            $webPush = $this->client();
        } catch (\Throwable $e) {
            Log::warning('WebPush client init failed: '.$e->getMessage());

            return;
        }

        $payload = json_encode(array_filter([
            'title' => $title,
            'body' => $body,
            'url' => $url ?: url('/dashboard'),
            'icon' => url('/icons/icon-192.png'),
            'badge' => url('/icons/icon-192.png'),
        ] + $extra));

        foreach ($subs as $sub) {
            try {
                $subscription = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                    'contentEncoding' => $sub->content_encoding ?: 'aesgcm',
                ]);
                $webPush->queueNotification($subscription, $payload);
            } catch (\Throwable $e) {
                Log::warning('WebPush queue failed', [
                    'subscription_id' => $sub->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            if ($report->isSuccess()) {
                PushSubscription::query()
                    ->where('endpoint', $endpoint)
                    ->update(['last_used_at' => now()]);
                continue;
            }

            $code = $report->getResponse()?->getStatusCode();
            // Gone / not found — drop stale subscription
            if (in_array($code, [404, 410], true)) {
                PushSubscription::query()->where('endpoint', $endpoint)->delete();
            } else {
                Log::warning('WebPush delivery failed', [
                    'endpoint' => $endpoint,
                    'reason' => $report->getReason(),
                    'status' => $code,
                ]);
            }
        }
    }

    private function client(): WebPush
    {
        $subject = (string) config('webpush.vapid.subject');
        if ($subject !== '' && ! str_starts_with($subject, 'mailto:') && ! str_starts_with($subject, 'https:')) {
            $subject = 'mailto:'.$subject;
        }

        return new WebPush([
            'VAPID' => [
                'subject' => $subject ?: 'mailto:admin@themmhc.com',
                'publicKey' => (string) config('webpush.vapid.public_key'),
                'privateKey' => (string) config('webpush.vapid.private_key'),
            ],
        ], [
            'TTL' => (int) config('webpush.ttl', 86400),
        ]);
    }
}
