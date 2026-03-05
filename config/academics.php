<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Topic completion threshold (percentage)
    |--------------------------------------------------------------------------
    | When this percentage of eligible students have submitted an assignment,
    | the linked topic is automatically marked as completed.
    */
    'completion_threshold' => (int) env('ACADEMICS_COMPLETION_THRESHOLD', 70),
];
