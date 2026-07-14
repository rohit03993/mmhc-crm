<?php

namespace App\Modules\Auth\Services;

use App\Models\Core\User;
use App\Modules\Auth\Models\UserNotification;
use App\Modules\Services\Models\ServiceRequest;
use Illuminate\Support\Facades\Log;

class AppNotificationService
{
    public function notify(
        User|int $user,
        string $type,
        string $title,
        ?string $body = null,
        ?string $actionUrl = null,
        array $meta = []
    ): ?UserNotification {
        try {
            $userId = $user instanceof User ? $user->id : (int) $user;
            if ($userId <= 0) {
                return null;
            }

            $notification = UserNotification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'action_url' => $actionUrl,
                'meta' => $meta ?: null,
            ]);

            try {
                app(WebPushService::class)->sendToUser(
                    $userId,
                    $title,
                    $body,
                    $actionUrl,
                    ['type' => $type]
                );
            } catch (\Throwable $e) {
                Log::warning('Web Push send failed: '.$e->getMessage(), [
                    'user_id' => $userId,
                    'type' => $type,
                ]);
            }

            return $notification;
        } catch (\Throwable $e) {
            Log::warning('Failed to create user notification: '.$e->getMessage(), [
                'type' => $type,
                'title' => $title,
            ]);

            return null;
        }
    }

    public function notifyBookingCreated(ServiceRequest $serviceRequest): void
    {
        $serviceRequest->loadMissing(['patient:id,name', 'serviceType:id,name']);
        $staffId = $serviceRequest->assigned_staff_id;
        if (! $staffId) {
            return;
        }

        $patientName = $serviceRequest->patient->name ?? 'A patient';
        $serviceName = $serviceRequest->serviceType->name ?? 'service';

        $this->notify(
            $staffId,
            UserNotification::TYPE_BOOKING_CREATED,
            'New booking request',
            "{$patientName} requested {$serviceName} (#{$serviceRequest->id}). Accept or reject on your dashboard.",
            route('staff.dashboard'),
            ['service_request_id' => $serviceRequest->id]
        );
    }

    public function notifyBookingCancelled(ServiceRequest $serviceRequest, ?int $staffId = null): void
    {
        $serviceRequest->loadMissing(['patient:id,name', 'serviceType:id,name']);
        $staffId = $staffId ?: $serviceRequest->assigned_staff_id;
        if (! $staffId) {
            return;
        }

        $patientName = $serviceRequest->patient->name ?? 'A patient';
        $serviceName = $serviceRequest->serviceType->name ?? 'service';

        $this->notify(
            $staffId,
            UserNotification::TYPE_BOOKING_CANCELLED,
            'Booking cancelled',
            "{$patientName} cancelled {$serviceName} (#{$serviceRequest->id}).",
            route('staff.dashboard'),
            ['service_request_id' => $serviceRequest->id]
        );
    }

    public function notifyBookingAccepted(ServiceRequest $serviceRequest): void
    {
        $serviceRequest->loadMissing(['assignedStaff:id,name', 'serviceType:id,name']);
        if (! $serviceRequest->patient_id) {
            return;
        }

        $staffName = $serviceRequest->assignedStaff->name ?? 'Staff';
        $serviceName = $serviceRequest->serviceType->name ?? 'service';

        $this->notify(
            $serviceRequest->patient_id,
            UserNotification::TYPE_BOOKING_ACCEPTED,
            'Booking accepted',
            "{$staffName} accepted your {$serviceName} request (#{$serviceRequest->id}).",
            route('services.show', $serviceRequest),
            ['service_request_id' => $serviceRequest->id]
        );
    }

    public function notifyBookingRejected(ServiceRequest $serviceRequest, ?string $reason = null): void
    {
        if (! $serviceRequest->patient_id) {
            return;
        }

        $serviceRequest->loadMissing(['serviceType:id,name']);
        $serviceName = $serviceRequest->serviceType->name ?? 'service';
        $body = "Your {$serviceName} request (#{$serviceRequest->id}) was declined by staff.";
        if ($reason) {
            $body .= ' Reason: '.$reason;
        }

        $this->notify(
            $serviceRequest->patient_id,
            UserNotification::TYPE_BOOKING_REJECTED,
            'Booking declined',
            $body,
            route('services.show', $serviceRequest),
            ['service_request_id' => $serviceRequest->id]
        );
    }

    public function notifyStaffAssigned(ServiceRequest $serviceRequest): void
    {
        $serviceRequest->loadMissing(['patient:id,name', 'serviceType:id,name']);
        $staffId = $serviceRequest->assigned_staff_id;
        if (! $staffId) {
            return;
        }

        $patientName = $serviceRequest->patient->name ?? 'A patient';
        $serviceName = $serviceRequest->serviceType->name ?? 'service';

        $this->notify(
            $staffId,
            UserNotification::TYPE_STAFF_ASSIGNED,
            'New service assignment',
            "You were assigned to {$serviceName} for {$patientName} (#{$serviceRequest->id}).",
            route('staff.service-details', $serviceRequest),
            ['service_request_id' => $serviceRequest->id]
        );
    }
}
