<?php

namespace App\Modules\Services\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Services\Models\ServiceRequest;
use App\Modules\Services\Services\ServiceCancellationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminVisitRefundController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'due');

        $query = ServiceRequest::with([
            'patient',
            'serviceType',
            'cancelledByUser',
            'refundedByUser',
        ])->where('status', 'cancelled');

        if ($status === 'refunded') {
            $query->refundedVisits()->orderByDesc('refunded_at');
        } else {
            $status = 'due';
            $query->refundDue()->orderByDesc('refund_due_at');
        }

        $refunds = $query->paginate(15)->withQueryString();

        $stats = [
            'due_count' => ServiceRequest::refundDue()->count(),
            'due_amount' => (float) ServiceRequest::refundDue()->sum('refund_amount'),
            'refunded_count' => ServiceRequest::refundedVisits()->count(),
            'refunded_amount' => (float) ServiceRequest::refundedVisits()->sum('refund_amount'),
        ];

        return view('services::admin.refunds.index', compact('refunds', 'stats', 'status'));
    }

    public function markRefunded(Request $request, ServiceRequest $serviceRequest)
    {
        $validator = Validator::make($request->all(), [
            'refund_reference' => 'nullable|string|max:191',
            'refund_note' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            app(ServiceCancellationService::class)->markRefunded(
                $serviceRequest,
                Auth::user(),
                $request->input('refund_reference'),
                $request->input('refund_note')
            );

            return redirect()
                ->route('admin.visit-refunds', ['status' => 'due'])
                ->with('success', 'Refund marked as paid. Remember this only updates CRM — send the money in Razorpay/UPI/bank separately.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->with('error', 'Could not mark this refund. Please try again.');
        }
    }
}
