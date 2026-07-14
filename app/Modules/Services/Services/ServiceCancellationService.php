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
     * Clears staff assignment drafts and non-final daily rows.
     *
     * @throws InvalidArgumentException
     */
    public function cancelByPatient(ServiceRequest $serviceRequest, User $patient, ?string $reason = null): ServiceRequest
    {
        if (! $serviceRequest->canBeCancelledByPatient($patient)) {
            throw new InvalidArgumentException(
                'This request cannot be cancelled. Only pending bookings waiting for staff acceptance can be cancelled.'
            );
        }

        $reason = $reason !== null ? trim($reason) : null;
        if ($reason === '') {
            $reason = null;
        }

        try {
            DB::beginTransaction();

            $previousStatus = $serviceRequest->status;
            $previousStaffId = $serviceRequest->assigned_staff_id;

            $serviceRequest->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $patient->id,
                'cancellation_reason' => $reason,
                // Free staff from pending approval / loose assignment drafts
                'assigned_staff_id' => null,
                'assigned_at' => null,
            ]);

            DailyService::where('service_request_id', $serviceRequest->id)
                ->whereIn('status', ['pending', 'scheduled', 'in_progress'])
                ->delete();

            DB::commit();

            Log::info('Service request cancelled by patient', [
                'service_request_id' => $serviceRequest->id,
                'patient_id' => $patient->id,
                'previous_status' => $previousStatus,
            ]);

            try {
                app(\App\Modules\Auth\Services\AppNotificationService::class)
                    ->notifyBookingCancelled($serviceRequest->fresh(['patient', 'serviceType']) ?? $serviceRequest, $previousStaffId);
            } catch (\Throwable $e) {
                report($e);
            }

            return $serviceRequest->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Service request cancellation failed: '.$e->getMessage(), [
                'service_request_id' => $serviceRequest->id,
                'patient_id' => $patient->id,
                'error' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
