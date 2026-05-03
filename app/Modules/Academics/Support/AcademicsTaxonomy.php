<?php

namespace App\Modules\Academics\Support;

class AcademicsTaxonomy
{
    /** @return array<string, string> */
    public static function teachingMethods(): array
    {
        return config('academics-taxonomy.teaching_methods', []);
    }

    /** @return array<string, string> */
    public static function assessmentTypes(): array
    {
        return config('academics-taxonomy.assessment_types', []);
    }

    /** @return array<string, string> */
    public static function assignmentTypes(): array
    {
        return config('academics-taxonomy.assignment_types', []);
    }

    /** @param  array<int, string>|null  $keys */
    public static function teachingMethodLabels(?array $keys): string
    {
        if (empty($keys)) {
            return '—';
        }
        $map = self::teachingMethods();
        $labels = [];
        foreach ($keys as $k) {
            if (isset($map[$k])) {
                $labels[] = $map[$k];
            }
        }

        return $labels === [] ? '—' : implode(', ', $labels);
    }

    /** @param  array<int, string>|null  $keys */
    public static function assessmentTypeLabels(?array $keys): string
    {
        if (empty($keys)) {
            return '—';
        }
        $map = self::assessmentTypes();
        $labels = [];
        foreach ($keys as $k) {
            if (isset($map[$k])) {
                $labels[] = $map[$k];
            }
        }

        return $labels === [] ? '—' : implode(', ', $labels);
    }

    public static function assignmentTypeLabel(?string $key): string
    {
        if ($key === null || $key === '') {
            return '—';
        }

        return self::assignmentTypes()[$key] ?? $key;
    }

    /** @param  list<string>  $allowed */
    public static function filterKeys(?array $input, array $allowed): array
    {
        if (empty($input)) {
            return [];
        }

        return array_values(array_unique(array_intersect($input, $allowed)));
    }
}
