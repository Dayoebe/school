<?php

namespace App\Support\Cbt;

use App\Models\Assessment\Assessment;
use App\Models\Assessment\Question;

class CbtExamPayloadBuilder
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function build(Assessment $assessment, int $userId, int $attemptNumber): array
    {
        $assessment->loadMissing('questions');

        $questionCollection = $assessment->questions;

        if ($assessment->shuffle_questions) {
            $questionCollection = $questionCollection
                ->sortBy(fn (Question $question) => $this->deterministicHash(
                    'question-order',
                    (int) $question->id,
                    (int) $assessment->id,
                    $attemptNumber,
                    $userId
                ))
                ->values();
        }

        return $questionCollection
            ->map(function (Question $question) use ($assessment, $userId, $attemptNumber): array {
                $payload = $question->toArray();
                $payload['options'] = is_string($payload['options'] ?? null)
                    ? json_decode($payload['options'], true) ?? []
                    : ($payload['options'] ?? []);

                if (
                    $assessment->shuffle_options
                    && !empty($payload['options'])
                    && ($payload['question_type'] ?? '') === 'multiple_choice'
                ) {
                    $indexedOptions = [];

                    foreach ($payload['options'] as $optionIndex => $optionText) {
                        $indexedOptions[] = [
                            'orig_idx' => (int) $optionIndex,
                            'text' => $optionText,
                            'weight' => $this->deterministicHash(
                                'option-order:' . (int) $question->id,
                                (int) $optionIndex,
                                (int) $assessment->id,
                                $attemptNumber,
                                $userId
                            ),
                        ];
                    }

                    usort($indexedOptions, fn (array $left, array $right) => strcmp($left['weight'], $right['weight']));

                    $newOptions = [];
                    $displayToOriginal = [];

                    foreach ($indexedOptions as $newIndex => $indexedOption) {
                        $newOptions[$newIndex] = $indexedOption['text'];
                        $displayToOriginal[$newIndex] = $indexedOption['orig_idx'];
                    }

                    $payload['options'] = $newOptions;
                    $payload['display_to_original_map'] = $displayToOriginal;
                    $payload['was_shuffled'] = true;
                } else {
                    $payload['display_to_original_map'] = null;
                    $payload['was_shuffled'] = false;
                }

                $payload['points'] = (float) ($payload['points'] ?? 1);
                unset($payload['correct_answers'], $payload['explanation']);

                return $payload;
            })
            ->values()
            ->all();
    }

    public function mapDisplayToOriginalAnswer(array $questionPayload, int $displayAnswer): int
    {
        if (
            !empty($questionPayload['was_shuffled'])
            && isset($questionPayload['display_to_original_map'][$displayAnswer])
        ) {
            return (int) $questionPayload['display_to_original_map'][$displayAnswer];
        }

        return $displayAnswer;
    }

    private function deterministicHash(
        string $scope,
        int $entityId,
        int $assessmentId,
        int $attemptNumber,
        int $userId
    ): string {
        return hash('sha256', implode('|', [
            $scope,
            (string) $entityId,
            (string) $assessmentId,
            (string) $attemptNumber,
            (string) $userId,
        ]));
    }
}
