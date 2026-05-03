<?php

namespace App\Modules\Academics\Services;

use App\Modules\Academics\Models\Assignment;

class ChecklistScoreService
{
    /**
     * @param  array<string|int, mixed>  $answers
     * @return array{earned: float, possible: float, normalized_answers: array<string, bool>}
     */
    public function score(Assignment $assignment, array $answers): array
    {
        $items = $assignment->normalizedChecklistItems();
        $possible = array_sum(array_map(fn ($i) => $i['points'], $items));
        $earned = 0.0;
        $normalized = [];
        foreach ($items as $idx => $item) {
            $key = (string) $idx;
            $checked = ! empty($answers[$key]) || ! empty($answers[$idx]);
            $normalized[$key] = $checked;
            if ($checked) {
                $earned += $item['points'];
            }
        }

        return [
            'earned' => round($earned, 2),
            'possible' => round($possible, 2),
            'normalized_answers' => $normalized,
        ];
    }
}
