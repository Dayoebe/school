<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'assessment_id',
        'question_text',
        'question_type',
        'options',
        'correct_answers',
        'points',
        'explanation',
        'is_required',
        'time_limit',
        'order',
        'difficulty_level',
        'tags'
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answers' => 'array',
        'is_required' => 'boolean',
        'tags' => 'array',
        'points' => 'decimal:2'
    ];

    const QUESTION_TYPES = [
        'multiple_choice' => 'Multiple Choice',
        'true_false' => 'True/False',
        'short_answer' => 'Short Answer',
        'essay' => 'Essay',
        'fill_blank' => 'Fill in the Blank',
        'matching' => 'Matching',
        'ordering' => 'Ordering',
        'drag_drop' => 'Drag & Drop',
        'qna_topic' => 'Q&A Topic',
        'project_criteria' => 'Project Criteria',
        'assignment_question' => 'Assignment Question'
    ];

    const DIFFICULTY_LEVELS = [
        'easy' => 'Easy',
        'medium' => 'Medium',
        'hard' => 'Hard',
        'expert' => 'Expert'
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($question) {
            if (!$question->order) {
                $maxOrder = static::where('assessment_id', $question->assessment_id)->max('order') ?? 0;
                $question->order = $maxOrder + 1;
            }
        });

        static::deleting(function ($question) {
            static::where('assessment_id', $question->assessment_id)
                ->where('order', '>', $question->order)
                ->decrement('order');
        });
    }

    public function getFormattedOptionsAttribute()
    {
        if (!is_array($this->options)) {
            return [];
        }

        $formatted = [];
        foreach ($this->options as $index => $option) {
            $formatted[] = [
                'index' => $index,
                'letter' => chr(65 + $index),
                'text' => $option,
                'is_correct' => in_array($index, $this->correct_answers ?? [])
            ];
        }

        return $formatted;
    }

    public function getStatsAttribute()
    {
        $totalAnswers = $this->studentAnswers()->count();
        $correctAnswers = $this->studentAnswers()->where('is_correct', true)->count();

        return [
            'total_answers' => $totalAnswers,
            'correct_answers' => $correctAnswers,
            'accuracy_rate' => $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 1) : 0,
            'difficulty_actual' => $this->calculateActualDifficulty()
        ];
    }

    protected function calculateActualDifficulty()
    {
        $stats = $this->stats;

        if ($stats['total_answers'] < 5) {
            return 'insufficient_data';
        }

        $accuracy = $stats['accuracy_rate'];

        if ($accuracy >= 80) return 'easy';
        if ($accuracy >= 60) return 'medium';
        if ($accuracy >= 40) return 'hard';
        return 'very_hard';
    }

    public function getQuestionTypeLabelAttribute()
    {
        return self::QUESTION_TYPES[$this->question_type] ?? ucfirst($this->question_type);
    }

    public function getDifficultyLabelAttribute()
    {
        return self::DIFFICULTY_LEVELS[$this->difficulty_level] ?? ucfirst($this->difficulty_level);
    }

    public function hasMultipleCorrectAnswers()
    {
        if ($this->question_type !== 'multiple_choice') {
            return false;
        }

        $correctAnswers = is_string($this->correct_answers)
            ? json_decode($this->correct_answers, true)
            : $this->correct_answers;

        return is_array($correctAnswers) && count($correctAnswers) > 1;
    }

    public function isCorrectAnswer($answer)
    {
        if ($answer === null || $answer === '' || $answer === 'null' || $answer === []) {
            return false;
        }

        $correctAnswers = $this->correct_answers;
        if (is_string($correctAnswers)) {
            $correctAnswers = json_decode($correctAnswers, true);
        }
        
        if (!is_array($correctAnswers) || empty($correctAnswers)) {
            return false;
        }

        $correctAnswers = array_map('intval', $correctAnswers);

        switch ($this->question_type) {
            case 'multiple_choice':
                return $this->checkMultipleChoiceAnswer($answer, $correctAnswers);
            
            case 'true_false':
                return $this->checkTrueFalseAnswer($answer, $correctAnswers);
            
            case 'short_answer':
            case 'fill_blank':
                return $this->checkTextAnswer($answer, $correctAnswers);
            
            default:
                $userAnswer = is_array($answer) ? array_map('intval', $answer) : intval($answer);
                
                if (is_array($userAnswer)) {
                    sort($userAnswer);
                    sort($correctAnswers);
                    return $userAnswer === $correctAnswers;
                }
                
                return in_array($userAnswer, $correctAnswers);
        }
    }

    protected function checkMultipleChoiceAnswer($answer, $correctAnswers)
    {
        if ($answer === null || $answer === '' || $answer === 'null') {
            return false;
        }

        if (is_array($answer)) {
            if (empty($answer)) {
                return false;
            }

            $userAnswers = array_map('intval', $answer);
            sort($userAnswers);
            sort($correctAnswers);
            
            return $userAnswers === $correctAnswers;
        }
        
        $userAnswer = intval($answer);
        return in_array($userAnswer, $correctAnswers);
    }

    protected function checkTrueFalseAnswer($answer, $correctAnswers)
    {
        if ($answer === null || $answer === '' || $answer === 'null') {
            return false;
        }

        $userAnswer = intval($answer);
        return in_array($userAnswer, $correctAnswers);
    }

    protected function checkTextAnswer($answer, $correctAnswers)
    {
        if ($answer === null || $answer === '' || $answer === 'null') {
            return false;
        }

        $userAnswer = is_array($answer) ? implode(' ', $answer) : $answer;
        $userAnswer = strtolower(trim($userAnswer));
        
        foreach ($correctAnswers as $correctAnswer) {
            $correctAnswer = strtolower(trim($correctAnswer));
            if ($userAnswer === $correctAnswer) {
                return true;
            }
        }
        
        return false;
    }

    public function calculatePartialCredit($answer)
    {
        if ($answer === null || $answer === '' || $answer === 'null' || $answer === []) {
            return 0;
        }

        if ($this->isCorrectAnswer($answer) === true) {
            return $this->points;
        }

        if ($this->question_type === 'multiple_choice' && $this->hasMultipleCorrectAnswers()) {
            $correctAnswers = is_string($this->correct_answers)
                ? json_decode($this->correct_answers, true)
                : $this->correct_answers;

            $correctAnswers = array_map('intval', $correctAnswers);
            
            if (!is_array($answer)) {
                $answer = [$answer];
            }
            $answer = array_map('intval', $answer);

            if (empty($answer)) {
                return 0;
            }

            $correctCount = count(array_intersect($answer, $correctAnswers));
            $totalCorrect = count($correctAnswers);
            $incorrectCount = count(array_diff($answer, $correctAnswers));

            $score = max(0, ($correctCount - $incorrectCount) / $totalCorrect);
            return $this->points * $score;
        }

        return 0;
    }
}