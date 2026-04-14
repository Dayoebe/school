<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBT Result Print</title>
    <style>
        body {
            color: #17202a;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 24px;
            background: #f4f6f8;
        }

        .page {
            background: #fff;
            margin: 0 auto;
            max-width: 960px;
            padding: 32px;
        }

        .header {
            border-bottom: 2px solid #d9e2ec;
            margin-bottom: 24px;
            padding-bottom: 16px;
        }

        .title {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 8px;
        }

        .muted {
            color: #52606d;
            font-size: 14px;
        }

        .meta-grid,
        .stats-grid {
            display: grid;
            gap: 12px;
        }

        .meta-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-bottom: 20px;
        }

        .stats-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin: 20px 0 28px;
        }

        .card {
            background: #f8fafc;
            border: 1px solid #d9e2ec;
            border-radius: 12px;
            padding: 14px 16px;
        }

        .card-label {
            color: #52606d;
            font-size: 12px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .card-value {
            font-size: 18px;
            font-weight: 700;
        }

        .question {
            border: 1px solid #d9e2ec;
            border-radius: 12px;
            margin-bottom: 16px;
            overflow: hidden;
        }

        .question-header {
            align-items: center;
            background: #f8fafc;
            border-bottom: 1px solid #d9e2ec;
            display: flex;
            justify-content: space-between;
            padding: 12px 16px;
        }

        .question-body {
            padding: 16px;
        }

        .badge {
            border-radius: 999px;
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 10px;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-neutral {
            background: #e2e8f0;
            color: #334155;
        }

        .option {
            border: 1px solid #d9e2ec;
            border-radius: 10px;
            margin-top: 10px;
            padding: 12px 14px;
        }

        .option.correct {
            background: #f0fdf4;
            border-color: #86efac;
        }

        .option.user-wrong {
            background: #fef2f2;
            border-color: #fca5a5;
        }

        .question-text,
        .option-text,
        .answer-text {
            line-height: 1.6;
        }

        .toolbar {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-bottom: 16px;
        }

        .toolbar button {
            background: #1d4ed8;
            border: none;
            border-radius: 8px;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
            padding: 10px 14px;
        }

        .toolbar button.secondary {
            background: #475569;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .page {
                box-shadow: none;
                max-width: none;
                padding: 0;
            }

            .toolbar {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .meta-grid,
            .stats-grid {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
        }
    </style>
    <script>
        window.triggerCbtPrint = function () {
            if (window.__cbtPrintTriggered) {
                return;
            }

            window.__cbtPrintTriggered = true;
            window.print();
        };

        window.MathJax = window.MathJax || {
            tex: {
                inlineMath: [['$', '$'], ['\\(', '\\)']],
                displayMath: [['$$', '$$'], ['\\[', '\\]']]
            },
            svg: {
                fontCache: 'global'
            },
            startup: {
                pageReady: function () {
                    return MathJax.startup.defaultPageReady().then(function () {
                        setTimeout(function () {
                            window.triggerCbtPrint();
                        }, 300);
                    });
                }
            }
        };

        window.addEventListener('load', function () {
            setTimeout(function () {
                window.triggerCbtPrint();
            }, 1200);
        }, { once: true });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js" async></script>
</head>
<body>
    <div class="page">
        <div class="toolbar">
            <button type="button" onclick="window.print()">Print Result</button>
            <button type="button" class="secondary" onclick="window.close()">Close</button>
        </div>

        <div class="header">
            <p class="muted">{{ $school?->name ?? 'School' }}</p>
            <h1 class="title">{{ $assessment->title }}</h1>
            <p class="muted">CBT result slip for {{ $student->name }}</p>
        </div>

        <div class="meta-grid">
            <div class="card">
                <div class="card-label">Student</div>
                <div class="card-value">{{ $student->name }}</div>
                <div class="muted">{{ $student->email }}</div>
            </div>
            <div class="card">
                <div class="card-label">Class / Subject</div>
                <div class="card-value">{{ $assessment->course?->name ?? 'Not assigned' }}</div>
                <div class="muted">{{ $assessment->lesson?->name ?? 'No subject' }}</div>
            </div>
            <div class="card">
                <div class="card-label">Attempt</div>
                <div class="card-value">#{{ $attempt['attempt_number'] }}</div>
                <div class="muted">{{ $attempt['submitted_at']?->format('M d, Y h:i A') }}</div>
            </div>
            <div class="card">
                <div class="card-label">Pass Mark</div>
                <div class="card-value">{{ $assessment->pass_percentage }}%</div>
                <div class="muted">Printed {{ now()->format('M d, Y h:i A') }}</div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="card">
                <div class="card-label">Score</div>
                <div class="card-value">{{ $attempt['percentage'] }}%</div>
            </div>
            <div class="card">
                <div class="card-label">Points</div>
                <div class="card-value">{{ $attempt['total_points'] }} / {{ $attempt['max_points'] }}</div>
            </div>
            <div class="card">
                <div class="card-label">Correct</div>
                <div class="card-value">{{ $attempt['correct_answers'] }} / {{ $attempt['total_questions'] }}</div>
            </div>
            <div class="card">
                <div class="card-label">Status</div>
                <div class="card-value">
                    <span class="badge {{ $attempt['passed'] ? 'badge-success' : 'badge-danger' }}">
                        {{ $attempt['passed'] ? 'PASSED' : 'FAILED' }}
                    </span>
                </div>
            </div>
        </div>

        <div>
            @foreach($assessment->questions as $question)
                @php
                    $answer = $attempt['answers']->get($question->id);
                    $correctAnswers = $question->correct_answers ?? [];
                    $isAnswered = $answer !== null;
                @endphp

                <div class="question">
                    <div class="question-header">
                        <strong>Question {{ $loop->iteration }}</strong>
                        @if(!$isAnswered)
                            <span class="badge badge-neutral">No Answer</span>
                        @elseif($answer->is_correct)
                            <span class="badge badge-success">Correct</span>
                        @else
                            <span class="badge badge-danger">Incorrect</span>
                        @endif
                    </div>

                    <div class="question-body">
                        @if($question->explanation)
                            <div class="card" style="margin-top: 12px;">
                                <div class="card-label">Instruction</div>
                                <div class="answer-text">{!! \App\Support\SafeHtml::math($question->explanation) !!}</div>
                            </div>
                        @endif

                        <div class="question-text">{!! \App\Support\SafeHtml::math($question->question_text) !!}</div>

                        @if($question->has_question_media)
                            <div class="card" style="margin-top: 12px;">
                                <div class="card-label">Question File</div>
                                @if($question->question_media_is_image)
                                    <img src="{{ $question->question_media_url }}"
                                        alt="{{ $question->question_media_original_name ?: 'Question image' }}"
                                        style="max-width: 100%; max-height: 420px; border: 1px solid #d9e2ec; border-radius: 10px; background: #fff; object-fit: contain;">
                                @else
                                    <div class="answer-text">
                                        <a href="{{ $question->question_media_url }}" target="_blank" rel="noopener noreferrer">
                                            {{ $question->question_media_original_name ?: 'Open question file' }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if(in_array($question->question_type, ['multiple_choice', 'true_false'], true))
                            @php
                                $options = $question->question_type === 'true_false'
                                    ? [0 => 'True', 1 => 'False']
                                    : ($question->options ?? []);
                            @endphp

                            @foreach($options as $index => $option)
                                @php
                                    $isUserAnswer = $isAnswered && (int) $answer->answer === (int) $index;
                                    $isCorrectAnswer = in_array((int) $index, array_map('intval', $correctAnswers), true);
                                    $optionClass = $isCorrectAnswer ? 'correct' : ($isUserAnswer ? 'user-wrong' : '');
                                @endphp
                                <div class="option {{ $optionClass }}">
                                    <strong>{{ chr(65 + $loop->index) }}.</strong>
                                    <span class="option-text">
                                        @if($question->question_type === 'true_false')
                                            {{ $option }}
                                        @else
                                            {!! \App\Support\SafeHtml::math($option) !!}
                                        @endif
                                    </span>
                                    @if($isUserAnswer)
                                        <span class="badge badge-neutral" style="margin-left: 8px;">Student Answer</span>
                                    @endif
                                    @if($isCorrectAnswer)
                                        <span class="badge badge-success" style="margin-left: 8px;">Correct Answer</span>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="card" style="margin-top: 12px;">
                                <div class="card-label">Student Answer</div>
                                <div class="answer-text">
                                    {{ $isAnswered ? ($answer->answer ?: 'No answer submitted') : 'No answer submitted' }}
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>
