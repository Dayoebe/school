<?php

namespace App\Livewire\Cbt\Exam;

use Livewire\Component;

class EnhancedTimer extends Component
{
    // Public properties (passed from parent)
    public $timeRemaining;
    public $estimatedDuration;
    public $questionCount;
    public $currentQuestionIndex;
    
    // Optional properties
    public $showWarnings = true;
    public $showToasts = true;
    public $autoSubmitOnExpiry = true;

    /**
     * Mount the component with initial values
     */
    public function mount(
        $timeRemaining, 
        $estimatedDuration = 60, 
        $questionCount = 0, 
        $currentQuestionIndex = 0
    ) {
        $this->timeRemaining = $timeRemaining;
        $this->estimatedDuration = $estimatedDuration;
        $this->questionCount = $questionCount;
        $this->currentQuestionIndex = $currentQuestionIndex;
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.cbt.exam.enhanced-timer');
    }

    /**
     * Update time remaining (called from parent component)
     */
    public function updateTime($seconds)
    {
        $this->timeRemaining = $seconds;
    }

    /**
     * Update current question index (called from parent component)
     */
    public function updateQuestionIndex($index)
    {
        $this->currentQuestionIndex = $index;
    }

    /**
     * Get formatted time string
     */
    public function getFormattedTimeProperty()
    {
        $hours = floor($this->timeRemaining / 3600);
        $minutes = floor(($this->timeRemaining % 3600) / 60);
        $seconds = $this->timeRemaining % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Get time percentage remaining
     */
    public function getPercentageProperty()
    {
        $totalSeconds = $this->estimatedDuration * 60;
        return $totalSeconds > 0 ? ($this->timeRemaining / $totalSeconds) * 100 : 0;
    }

    /**
     * Check if time is critical (less than 5%)
     */
    public function getIsCriticalProperty()
    {
        return $this->percentage <= 5;
    }

    /**
     * Check if time is low (less than 10%)
     */
    public function getIsLowProperty()
    {
        return $this->percentage <= 10 && $this->percentage > 5;
    }

    /**
     * Check if time is warning (less than 25%)
     */
    public function getIsWarningProperty()
    {
        return $this->percentage <= 25 && $this->percentage > 10;
    }

    /**
     * Get average time per remaining question
     */
    public function getAvgTimePerQuestionProperty()
    {
        $remaining = $this->questionCount - $this->currentQuestionIndex;
        if ($remaining <= 0) {
            return 0;
        }
        return floor($this->timeRemaining / $remaining);
    }

    /**
     * Get estimated finish time
     */
    public function getEstimatedFinishTimeProperty()
    {
        return now()->addSeconds($this->timeRemaining)->format('g:i A');
    }

    /**
     * Get pace status (ahead, on-track, behind)
     */
    public function getPaceStatusProperty()
    {
        $totalSeconds = $this->estimatedDuration * 60;
        $expectedProgress = ($this->currentQuestionIndex / $this->questionCount) * 100;
        $actualProgress = (($totalSeconds - $this->timeRemaining) / $totalSeconds) * 100;
        
        $difference = $actualProgress - $expectedProgress;
        
        if ($difference < -10) {
            return 'ahead'; // Using less time than expected
        } elseif ($difference > 10) {
            return 'behind'; // Using more time than expected
        }
        return 'on-track';
    }
}