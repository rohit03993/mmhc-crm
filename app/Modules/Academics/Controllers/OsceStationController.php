<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academics\Models\OsceSession;
use App\Modules\Academics\Models\OsceStation;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OsceStationController extends Controller
{
    public function __construct(
        private OsceSessionController $sessionGate
    ) {}

    /** @return list<array{label: string, points: float}> */
    protected function parseChecklistItemsFromRaw(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        $out = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $points = 1.0;
            $label = $line;
            if (preg_match('/^(.+?)\s*\|\s*([\d.]+)\s*$/u', $line, $m)) {
                $label = trim($m[1]);
                $points = (float) $m[2];
            }
            if ($label !== '') {
                $out[] = ['label' => $label, 'points' => max(0.0, $points)];
            }
        }

        return $out;
    }

    protected function authorizeSession(OsceSession $session): void
    {
        $this->sessionGate->authorizeStaffSession($session);
    }

    public function create(OsceSession $session)
    {
        $this->authorizeSession($session);

        return view('academics::osce.stations.create', compact('session'));
    }

    public function store(Request $request, OsceSession $session)
    {
        $this->authorizeSession($session);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'time_limit_seconds' => ['nullable', 'integer', 'min:30', 'max:7200'],
            'checklist_items_raw' => ['nullable', 'string', 'max:20000'],
        ]);
        $items = $this->parseChecklistItemsFromRaw($request->input('checklist_items_raw'));
        if ($items === []) {
            throw ValidationException::withMessages([
                'checklist_items_raw' => 'Add at least one checklist line for this station.',
            ]);
        }
        $nextOrder = (int) ($session->stations()->max('sort_order') ?? -1) + 1;
        OsceStation::create([
            'osce_session_id' => $session->id,
            'sort_order' => $nextOrder,
            'name' => $validated['name'],
            'instructions' => $validated['instructions'] ?? null,
            'time_limit_seconds' => $validated['time_limit_seconds'] ?? null,
            'checklist_items' => $items,
        ]);

        return redirect()->route('academics.osce.show', $session)->with('success', 'Station added.');
    }

    public function edit(OsceSession $session, OsceStation $station)
    {
        $this->authorizeSession($session);
        if ((int) $station->osce_session_id !== (int) $session->id) {
            abort(404);
        }

        return view('academics::osce.stations.edit', compact('session', 'station'));
    }

    public function update(Request $request, OsceSession $session, OsceStation $station)
    {
        $this->authorizeSession($session);
        if ((int) $station->osce_session_id !== (int) $session->id) {
            abort(404);
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'time_limit_seconds' => ['nullable', 'integer', 'min:30', 'max:7200'],
            'checklist_items_raw' => ['nullable', 'string', 'max:20000'],
        ]);
        $items = $this->parseChecklistItemsFromRaw($request->input('checklist_items_raw'));
        if ($items === []) {
            throw ValidationException::withMessages([
                'checklist_items_raw' => 'Add at least one checklist line for this station.',
            ]);
        }
        $station->update([
            'name' => $validated['name'],
            'instructions' => $validated['instructions'] ?? null,
            'time_limit_seconds' => $validated['time_limit_seconds'] ?? null,
            'checklist_items' => $items,
        ]);

        return redirect()->route('academics.osce.show', $session)->with('success', 'Station updated.');
    }

    public function destroy(OsceSession $session, OsceStation $station)
    {
        $this->authorizeSession($session);
        if ((int) $station->osce_session_id !== (int) $session->id) {
            abort(404);
        }
        $station->delete();

        return redirect()->route('academics.osce.show', $session)->with('success', 'Station removed.');
    }
}
