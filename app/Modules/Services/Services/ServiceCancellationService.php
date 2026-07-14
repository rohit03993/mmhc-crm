<?php

namespace App\Modules\Services\Services;

use App\Models\Core\User;
use App\Modules\Services\Models\DailyService;
use App\Modules\Services\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class ServiceCancellationService
{
    /**
     * Patient cancel: only pending / pending_approval.
     */
    public function cancelByPatient(ServiceRequest $serviceRequest, User $patient, ?string $reason = null): ServiceRequest
    {
        if (! $serviceRequest->canBeCancelledByPatient($patient)) {
            throw new InvalidArgumentException(
                'This request cannot be cancelled. Only pending bookings waiting for staff acceptance can be cancelled.'
            );
        }

        return $this->applyCancellation($serviceRequest, $patient, $reason, 'patient');
    }

    /**
     * Assigned nurse/caregiver cancel: only before visit starts (pending_approval / assigned).
     * Admin cannot cancel.
     */
    public function cancelByStaff(ServiceRequest $serviceRequest, User $staff, ?string $reason = null): ServiceRequest
    {
        if (! $serviceRequest->canBeCancelledByStaff($staff)) {
            throw new InvalidArgumentException(
                'This booking cannot be cancelled. Staff may cancel only before the visit starts.'
            );
        }

        return $this->applyCancellation($serviceRequest, $staff, $reason, 'staff');
    }

    /**
     * Admin marks a refund as paid outside the app (Razorpay / UPI / bank).
     */
    public function markRefunded(
        ServiceRequest $serviceRequest,
        User $admin,
        ?string $reference = null,
        ?string $note = null
    ): ServiceRequest {
        if (! $admin->isAdmin()) {
            throw new InvalidArgumentException('Only admins can mark refunds.');
        }

        if (! $serviceRequest->isRefundDue()) {
            throw new InvalidArgumentException('This booking is not waiting for a refund.');
        }

        $reference = $reference !== null ? trim($reference) : null;
        $note = $note !== null ? trim($note) : null;

        $serviceRequest->update([
            'payment_status' => 'refunded',
            'refunded_at' => now(),
            'refunded_by' => $admin->id,
            'refund_reference' => $reference !== '' ? $reference : null,
            'refund_note' => $note !== '' ? $note : null,
        ]);

        Log::info('Visit refund marked manually by admin', [
            'service_request_id' => $serviceRequest->id,
            'admin_id' => $admin->id,
            'refund_amount' => $serviceRequest->refund_amount,
        ]);

        return $serviceRequest->fresh();
    }

    /**
     * @param  'patient'|'staff'  $actorType
     */
    private function applyCancellation(
        ServiceRequest $serviceRequest,
        User $actor,
        ?string $reason,
        string $actorType
    ): ServiceRequest {
        $reason = $reason !== null ? trim($reason) : null;
        if ($reason === '') {
            $reason = null;
        }

        try {
            DB::beginTransaction();

            $previousStatus = $serviceRequest->status;
            $previousStaffId = $serviceRequest->assigned_staff_id;
            $markRefundDue = $serviceRequest->shouldQueueManualRefundOnCancel();
            $refundAmount = $markRefundDue
                ? round((float) ($serviceRequest->total_amount ?: $serviceRequest->prepaid_amount), 2)
                : null;

            $payload = [
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $actor->id,
                'cancellation_reason' => $reason,
                'assigned_staff_id' => null,
                'assigned_at' => null,
            ];

            if ($markRefundDue) {
                $payload['refund_due_at'] = now();
                $payload['refund_amount'] = $refundAmount;
            }

            $serviceRequest->update($payload);

            DailyService::where('service_request_id', $serviceRequest->id)
                ->whereIn('status', ['pending', 'scheduled', 'in_progress'])
                ->delete();

            DB::commit();

            Log::info('Service request cancelled', [
                'service_request_id' => $serviceRequest->id,
                'actor_id' => $actor->id,
                'actor_type' => $actorType,
                'previous_status' => $previousStatus,
                'refund_due' => $markRefundDue,
                'refund_amount' => $refundAmount,
            ]);

            try {
                app(\App\Modules\Auth\Services\AppNotificationService::class)
                    ->notifyBookingCancelled(
                        $serviceRequest->fresh(['patient', 'serviceType']) ?? $serviceRequest,
                        $previousStaffId
                    );
            } catch (\Throwable $e) {
                report($e);
            }

            return $serviceRequest->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Service request cancellation failed: '.$e->getMessage(), [
                'service_request_id' => $serviceRequest->id,
                'actor_id' => $actor->id,
                'actor_type' => $actorType,
                'error' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
