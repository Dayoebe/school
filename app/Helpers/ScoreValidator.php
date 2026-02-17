<?php

namespace App\Helpers;

class ScoreValidator
{
    /**
     * Validate and sanitize a CA score (0-10)
     */
    public static function validateCA($score)
    {
        // Handle null/empty
        if ($score === null || $score === '') {
            return null;
        }

        // Convert to integer (removes decimals)
        $score = filter_var($score, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        if ($score === null) {
            throw new \Exception("Score must be a whole number between 0 and 10.");
        }

        if ($score < 0) {
            throw new \Exception("Score cannot be negative.");
        }

        if ($score > 10) {
            throw new \Exception("CA score cannot exceed 10.");
        }

        return $score;
    }

    /**
     * Validate and sanitize exam score (0-60)
     */
    public static function validateExam($score)
    {
        // Handle null/empty
        if ($score === null || $score === '') {
            return null;
        }

        // Convert to integer
        $score = filter_var($score, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        if ($score === null) {
            throw new \Exception("Score must be a whole number between 0 and 60.");
        }

        if ($score < 0) {
            throw new \Exception("Score cannot be negative.");
        }

        if ($score > 60) {
            throw new \Exception("Exam score cannot exceed 60.");
        }

        return $score;
    }

    /**
     * Calculate total score and validate
     */
    public static function calculateTotal($ca1, $ca2, $ca3, $ca4, $exam)
    {
        $ca1 = self::validateCA($ca1);
        $ca2 = self::validateCA($ca2);
        $ca3 = self::validateCA($ca3);
        $ca4 = self::validateCA($ca4);
        $exam = self::validateExam($exam);

        $total = ($ca1 ?? 0) + ($ca2 ?? 0) + ($ca3 ?? 0) + ($ca4 ?? 0) + ($exam ?? 0);

        if ($total > 100) {
            throw new \Exception("Total score cannot exceed 100. Current total: {$total}");
        }

        return $total;
    }

    /**
     * Validate attendance scores
     */
    public static function validateAttendance($present, $absent)
    {
        if ($present !== null && $present < 0) {
            throw new \Exception("Present days cannot be negative.");
        }

        if ($absent !== null && $absent < 0) {
            throw new \Exception("Absent days cannot be negative.");
        }

        // Optional: Check for unrealistic values
        if ($present !== null && $present > 365) {
            throw new \Exception("Present days seems unrealistic (max 365).");
        }

        if ($absent !== null && $absent > 365) {
            throw new \Exception("Absent days seems unrealistic (max 365).");
        }

        return true;
    }

    /**
     * Validate trait/activity scores (1-5)
     */
    public static function validateTrait($score)
    {
        if ($score === null || $score === '') {
            return null;
        }

        $score = filter_var($score, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        if ($score === null) {
            throw new \Exception("Trait score must be a whole number between 1 and 5.");
        }

        if ($score < 1 || $score > 5) {
            throw new \Exception("Trait score must be between 1 and 5.");
        }

        return $score;
    }

    /**
     * Calculate grade from total score
     */
    public static function calculateGrade($totalScore)
    {
        return match (true) {
            $totalScore >= 75 => 'A1',
            $totalScore >= 70 => 'B2',
            $totalScore >= 65 => 'B3',
            $totalScore >= 60 => 'C4',
            $totalScore >= 55 => 'C5',
            $totalScore >= 50 => 'C6',
            $totalScore >= 45 => 'D7',
            $totalScore >= 40 => 'E8',
            default => 'F9',
        };
    }

    /**
     * Get default comment based on score
     */
    public static function getDefaultComment($totalScore)
    {
        return match (true) {
            $totalScore >= 75 => 'Distinction',
            $totalScore >= 70 => 'Very good',
            $totalScore >= 65 => 'Good',
            $totalScore >= 60 => 'Credit',
            $totalScore >= 55 => 'Credit',
            $totalScore >= 50 => 'Credit',
            $totalScore >= 45 => 'Pass',
            $totalScore >= 40 => 'Pass',
            default => 'Fail',
        };
    }
}