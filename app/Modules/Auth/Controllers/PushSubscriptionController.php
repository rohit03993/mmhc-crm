<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\WebPushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PushSubscriptionController extends Controller
{
    public function vapidPublicKey(WebPushService $webPush)
    {
        if (! $webPush->isConfigured()) {
            return response()->json([
                'success' => false,
                'enabled' => false,
                'message' => 'Web Push is not configured on this server.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'enabled' => true,
            'publicKey' => $webPush->publicKey(),
        ]);
    }

    public function store(Request $request, WebPushService $webPush)
    {
        $validator = Validator::make($request->all(), [
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'contentEncoding' => 'nullable|string|max:32',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid subscription.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $webPush->subscribe((int) Auth::id(), $request->all(), $request->userAgent());

            return response()->json(['success' => true]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Could not save push subscription.'], 500);
        }
    }

    public function destroy(Request $request, WebPushService $webPush)
    {
        $endpoint = (string) $request->input('endpoint', '');
        if ($endpoint === '') {
            return response()->json(['success' => false, 'message' => 'Endpoint required.'], 422);
        }

        $webPush->unsubscribe((int) Auth::id(), $endpoint);

        return response()->json(['success' => true]);
    }
}
