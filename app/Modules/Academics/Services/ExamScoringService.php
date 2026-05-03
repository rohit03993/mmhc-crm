<?php

namespace App\Modules\Academics\Services;

use App\Modules\Academics\Models\AcademicExam;
use App\Modules\Academics\Models\AcademicExamAttempt;
use App\Modules\Academics\Models\AcademicExamAttemptAnswer;
use App\Modules\Academics\Models\AcademicExamQuestion;

class ExamScoringService
{
    /**
     * Auto-score all question types on a submitted attempt; returns total points earned.
     */
    public function scoreSubmittedAttempt(AcademicExamAttempt $attempt): float
    {
        $attempt->loadMissing(['exam.questions.options', 'answers']);
        $exam = $attempt->exam;
        $total = 0.0;

        foreach ($exam->questions as $question) {
            $answer = $attempt->answers->firstWhere('question_id', $question->id);
            if ($this->isAnswerCorrect($question, $answer)) {
                $total += (float) $question->points;
            }
        }

        return round($total, 2);
    }

    public function isAnswerCorrect(AcademicExamQuestion $question, ?AcademicExamAttemptAnswer $answer): bool
    {
        if (! $answer) {
            return false;
        }

        if ($question->question_type === AcademicExamQuestion::TYPE_MCQ_MULTI) {
            $correctIds = $question->options->where('is_correct', true)->pluck('id')->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
            $selected = collect($answer->selected_option_ids ?? [])->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();

            return $correctIds === $selected && count($correctIds) > 0;
        }

        if (! $answer->option_id) {
            return false;
        }
        $option = $question->options->firstWhere('id', (int) $answer->option_id);

        return $option && $option->is_correct;
    }

    public function maxPointsForExam(AcademicExam $exam): float
    {
        return round((float) $exam->questions()->sum('points'), 2);
    }
}
