<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SystemResetService;
use Illuminate\Support\Facades\Log;

class SystemController extends Controller
{
    protected $resetService;

    public function __construct(SystemResetService $resetService)
    {
        $this->resetService = $resetService;
    }

    /**
     * Show system reset page with warnings
     */
    public function showResetPage()
    {
        $stats = $this->resetService->getSystemStats();
        
        return view('auth::admin.system-reset', compact('stats'));
    }

    /**
     * Execute system reset
     */
    public function resetSystem(Request $request)
    {
        // Triple confirmation check
        $confirmation1 = $request->input('confirmation_1');
        $confirmation2 = $request->input('confirmation_2');
        $confirmationText = $request->input('confirmation_text');

        if ($confirmation1 !== 'yes' || $confirmation2 !== 'yes' || strtolower(trim($confirmationText)) !== 'reset') {
            return redirect()->back()
                ->with('error', 'All confirmation fields must be filled correctly. Reset cancelled.')
                ->withInput();
        }

        try {
            $result = $this->resetService->resetSystemData();

            if ($result['success']) {
                Log::info('System reset executed via admin panel', [
                    'admin_id' => Auth::id(),
                    'admin_email' => Auth::user()->email,
                    'stats' => $result,
                ]);

                return redirect()->route('admin.dashboard')
                    ->with('success', 'System reset completed successfully! All user data has been deleted except admin account.')
                    ->with('reset_stats', $result);
            } else {
                return redirect()->back()
                    ->with('error', 'Reset failed: ' . ($result['error'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error('System reset failed', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Reset failed: ' . $e->getMessage());
        }
    }
}

