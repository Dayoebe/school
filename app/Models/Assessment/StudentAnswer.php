<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class StudentAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assessment_id',
        'question_id',
        'attempt_number',
        'answer',
        'points_earned',
        'is_correct',
        'time_spent_seconds',
        'submitted_at',
        'question_order',
        'exam_data'
    ];

    protected $casts = [
        'exam_data' => 'array',
        'submitted_at' => 'datetime',
        'question_order' => 'array'
    ];

    /**
     * Auto-grade the answer
     * The answer is ALREADY in original position (mapped in CbtExamInterface)
     */
    public function autoGrade()
    {
        try {
            $question = $this->relationLoaded('question') ? $this->question : null;
            $userAnswer = $this->answer; // Already in ORIGINAL position

            $questionType = $question?->question_type ?? data_get($this->exam_data, 'question_type');
            $points = (float) ($question?->points ?? data_get($this->exam_data, 'question_points', 0));
            $originalCorrectAnswers = $question?->correct_answers ?? data_get($this->exam_data, 'original_correct_answers', []);

            if ($questionType === null) {
                $question = $this->question()->select(['id', 'question_type', 'correct_answers', 'points'])->first();
                $questionType = $question?->question_type;
                $points = (float) ($question?->points ?? $points);
                $originalCorrectAnswers = $question?->correct_answers ?? $originalCorrectAnswers;
            }

            if ($questionType === null) {
                $this->is_correct = false;
                $this->points_earned = 0;
                $this->save();
                return false;
            }

            if (is_string($originalCorrectAnswers)) {
                $originalCorrectAnswers = json_decode($originalCorrectAnswers, true) ?? [];
            }
            if (!is_array($originalCorrectAnswers)) {
                $originalCorrectAnswers = [$originalCorrectAnswers];
            }
            $originalCorrectAnswers = array_map('intval', $originalCorrectAnswers);

            // Grade based on question type
            if ($questionType === 'multiple_choice') {
                $this->gradeMultipleChoice($userAnswer, $originalCorrectAnswers, $points);
            } elseif ($questionType === 'true_false') {
                $this->gradeTrueFalse($userAnswer, $originalCorrectAnswers, $points);
            } else {
                $this->is_correct = false;
                $this->points_earned = 0;
            }

            $this->save();

            return true;

        } catch (\Exception $e) {
            \Log::error('Auto-grade failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->is_correct = false;
            $this->points_earned = 0;
            $this->save();
            return false;
        }
    }

    /**
     * Grade multiple choice - answer is ALREADY mapped to original position
     */
    protected function gradeMultipleChoice($userAnswer, $originalCorrectAnswers, float $points): void
    {
        // Simple comparison - userAnswer is already in original position
        $this->is_correct = in_array((int) $userAnswer, $originalCorrectAnswers);
        $this->points_earned = $this->is_correct ? $points : 0;
    }

    /**
     * Grade true/false
     */
    protected function gradeTrueFalse($userAnswer, $originalCorrectAnswers, float $points): void
    {
        $correctAnswer = (int) ($originalCorrectAnswers[0] ?? 0);
        $userAnswer = (int) $userAnswer;

        $this->is_correct = $userAnswer === $correctAnswer;
        $this->points_earned = $this->is_correct ? $points : 0;
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Format answer for display
     */
    public function getFormattedAnswerAttribute()
    {
        $answer = data_get($this->exam_data, 'user_original_answer', $this->answer);
        $questionType = $this->relationLoaded('question')
            ? $this->question?->question_type
            : data_get($this->exam_data, 'question_type');

        if ($questionType === 'multiple_choice') {
            $options = ['A', 'B', 'C', 'D', 'E', 'F'];
            return $options[(int) $answer] ?? 'Unknown';
        } elseif ($questionType === 'true_false') {
            return (int) $answer === 0 ? 'True' : 'False';
        }

        return (string) $answer;
    }
}
