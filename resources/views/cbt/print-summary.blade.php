<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportType === 'subject' ? 'CBT Subject Result Print' : 'CBT Class Result Print' }}</title>
    <style>
        body {
            margin: 0;
            padding: 24px;
            background: #eef2f7;
            color: #17202a;
            font-family: Arial, sans-serif;
        }

        .page {
            max-width: 1120px;
            margin: 0 auto;
            background: #ffffff;
            padding: 32px;
        }

        .toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 16px;
        }

        .toolbar button {
            border: none;
            border-radius: 8px;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
            padding: 10px 14px;
        }

        .toolbar .primary {
            background: #1d4ed8;
        }

        .toolbar .secondary {
            background: #475569;
        }

        .header {
            border-bottom: 2px solid #d9e2ec;
            padding-bottom: 18px;
            margin-bottom: 24px;
        }

        .eyebrow {
            display: inline-block;
            border-radius: 999px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            padding: 7px 12px;
            text-transform: uppercase;
        }

        .title {
            margin: 12px 0 8px;
            font-size: 30px;
            font-weight: 700;
        }

        .subtitle {
            margin: 0;
            color: #52606d;
            font-size: 14px;
            line-height: 1.6;
        }

        .meta-grid,
        .stats-grid {
            display: grid;
            gap: 12px;
        }

        .meta-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 20px;
        }

        .stats-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin: 24px 0;
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
            font-weight: 700;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .card-value {
            font-size: 18px;
            font-weight: 700;
        }

        .table-wrap {
            border: 1px solid #d9e2ec;
            border-radius: 14px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #eff6ff;
        }

        th,
        td {
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 14px;
            text-align: left;
            vertical-align: top;
            font-size: 14px;
        }

        th {
            color: #334155;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 700;
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

        .muted {
            color: #64748b;
            font-size: 13px;
        }

        .empty {
            padding: 32px;
            text-align: center;
            color: #52606d;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .page {
                max-width: none;
                padding: 0;
            }

            .toolbar {
                display: none;
            }
        }

        @media (max-width: 900px) {
            .meta-grid,
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 12px;
            }

            .page {
                padding: 18px;
            }

            .meta-grid,
            .stats-grid {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }

            th,
            td {
                padding: 10px 8px;
                font-size: 12px;
            }
        }
    </style>
    <script>
        window.triggerCbtPrint = function () {
            if (window.__cbtSummaryPrintTriggered) {
                return;
            }

            window.__cbtSummaryPrintTriggered = true;
            window.print();
        };

        window.addEventListener('load', function () {
            setTimeout(function () {
                window.triggerCbtPrint();
            }, 500);
        }, { once: true });
    </script>
</head>
<body>
    <div class="page">
        <div class="toolbar">
            <button type="button" class="secondary" onclick="window.close()">Close</button>
            <button type="button" class="primary" onclick="window.triggerCbtPrint()">Print</button>
        </div>

        <header class="header">
            <span class="eyebrow">{{ $reportType === 'subject' ? 'Subject Result Sheet' : 'Class Result Sheet' }}</span>
            <h1 class="title">{{ $assessment->title }}</h1>
            <p class="subtitle">
                {{ $school?->name ?? 'School' }} · {{ $assessment->course?->name ?? 'No class assigned' }}
                @if($assessment->lesson?->name)
                    · {{ $assessment->lesson->name }}
                @endif
            </p>

            <div class="meta-grid">
                <div class="card">
                    <div class="card-label">Prepared By</div>
                    <div class="card-value">{{ $viewer->name }}</div>
                </div>
                <div class="card">
                    <div class="card-label">Generated At</div>
                    <div class="card-value">{{ now()->format('M d, Y h:i A') }}</div>
                </div>
                <div class="card">
                    <div class="card-label">Pass Mark</div>
                    <div class="card-value">{{ $assessment->pass_percentage }}%</div>
                </div>
                <div class="card">
                    <div class="card-label">Duration</div>
                    <div class="card-value">{{ $assessment->formatted_duration }}</div>
                </div>
                <div class="card">
                    <div class="card-label">Max Attempts</div>
                    <div class="card-value">{{ $assessment->formatted_max_attempts }}</div>
                </div>
                <div class="card">
                    <div class="card-label">Results Status</div>
                    <div class="card-value">{{ $assessment->results_published_at ? 'Published' : 'Internal only' }}</div>
                </div>
            </div>
        </header>

        <section class="stats-grid">
            <div class="card">
                <div class="card-label">Students In Class</div>
                <div class="card-value">{{ $metrics['students_in_class'] }}</div>
            </div>
            <div class="card">
                <div class="card-label">Submitted</div>
                <div class="card-value">{{ $metrics['submitted_count'] }}</div>
            </div>
            <div class="card">
                <div class="card-label">Not Submitted</div>
                <div class="card-value">{{ $metrics['not_submitted_count'] }}</div>
            </div>
            <div class="card">
                <div class="card-label">Participation</div>
                <div class="card-value">{{ $metrics['participation_rate'] }}%</div>
            </div>
            <div class="card">
                <div class="card-label">Passed</div>
                <div class="card-value">{{ $metrics['pass_count'] }}</div>
            </div>
            <div class="card">
                <div class="card-label">Failed</div>
                <div class="card-value">{{ $metrics['fail_count'] }}</div>
            </div>
            <div class="card">
                <div class="card-label">Average Score</div>
                <div class="card-value">{{ $metrics['average_percentage'] }}%</div>
            </div>
            <div class="card">
                <div class="card-label">Top Score</div>
                <div class="card-value">{{ $metrics['top_percentage'] }}%</div>
            </div>
        </section>

        <section class="table-wrap">
            @if($rows->isEmpty())
                <div class="empty">
                    No students or submitted CBT attempts were found for this assessment in the current academic year.
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Student</th>
                            <th>Admission No</th>
                            <th>Attempts</th>
                            <th class="text-right">Best Score</th>
                            <th class="text-right">Points</th>
                            <th>Latest Submission</th>
                            <th>Status</th>
                            <th>Eligibility</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            @php($bestAttempt = $row['best_attempt'])
                            @php($latestAttempt = $row['latest_attempt'])
                            <tr>
                                <td>{{ $row['rank'] ?? '-' }}</td>
                                <td>
                                    <div>{{ $row['student']->name }}</div>
                                    <div class="muted">{{ $row['student']->email }}</div>
                                </td>
                                <td>{{ $row['admission_number'] ?: '-' }}</td>
                                <td>{{ $row['attempt_count'] }}</td>
                                <td class="text-right">{{ $bestAttempt['percentage'] ?? '0' }}%</td>
                                <td class="text-right">
                                    @if($bestAttempt)
                                        {{ $bestAttempt['total_points'] }}/{{ $bestAttempt['max_points'] }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($latestAttempt && $latestAttempt['submitted_at'])
                                        {{ $latestAttempt['submitted_at']->format('M d, Y h:i A') }}
                                    @else
                                        <span class="muted">Not submitted</span>
                                    @endif
                                </td>
                                <td>
                                    @if($bestAttempt)
                                        <span class="badge {{ $bestAttempt['passed'] ? 'badge-success' : 'badge-danger' }}">
                                            {{ $bestAttempt['passed'] ? 'Passed' : 'Failed' }}
                                        </span>
                                    @else
                                        <span class="badge badge-neutral">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $row['is_eligible'] ? 'badge-success' : 'badge-danger' }}">
                                        {{ $row['is_eligible'] ? 'Eligible' : 'Blocked' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    </div>
</body>
</html>
