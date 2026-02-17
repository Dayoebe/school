<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// CbtAnswer.php  
class CbtAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'cbt_result_id', 'question_id', 'selected_answer', 'text_answer',
        'is_correct', 'points_awarded', 'time_spent_seconds', 'answered_at',
        'flagged_for_review', 'answer_sequence'
    ];

    protected $casts = [
        'selected_answer' => 'array',
        'is_correct' => 'boolean',
        'points_awarded' => 'decimal:2',
        'answered_at' => 'datetime',
        'flagged_for_review' => 'boolean',
    ];

    // Relationships
    public function result()
    {
        return $this->belongsTo(CbtResult::class, 'cbt_result_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    // Methods
    public function checkCorrectness()
    {
        $question = $this->question;
        
        if (!$question) {
            return false;
        }

        $userAnswer = $this->selected_answer ?? $this->text_answer;
        $this->is_correct = $question->isCorrectAnswer($userAnswer);
        $this->points_awarded = $this->is_correct ? $question->points : 0;
        
        return $this->save();
    }
}