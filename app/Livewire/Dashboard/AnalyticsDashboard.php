<?php

namespace App\Livewire\Dashboard;

use App\Models\AdmissionRegistration;
use App\Models\BroadcastMessage;
use App\Models\ContactMessage;
use App\Models\FeeInvoiceRecord;
use App\Models\GalleryItem;
use App\Models\MediaAsset;
use App\Models\Notice;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AnalyticsDashboard extends Component
{
    public int $months = 6;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read analytics dashboard'), 403);
    }

    protected function monthLabels(): array
    {
        $period = CarbonPeriod::create(
            now()->startOfMonth()->subMonths(max(1, $this->months) - 1),
            '1 month',
            now()->startOfMonth()
        );

        return collect($period)
            ->map(fn (Carbon $month): string => $month->format('Y-m'))
            ->values()
            ->all();
    }

    protected function formatMonth(string $month): string
    {
        return Carbon::createFromFormat('Y-m', $month)->format('M Y');
    }

    protected function countsByMonth(Collection $rows, string $dateColumn, array $monthKeys): array
    {
        $counts = array_fill_keys($monthKeys, 0);

        foreach ($rows as $row) {
            $date = data_get($row, $dateColumn);
            if (!$date) {
                continue;
            }

            $monthKey = Carbon::parse($date)->format('Y-m');
            if (array_key_exists($monthKey, $counts)) {
                $counts[$monthKey]++;
            }
        }

        return $counts;
    }

    protected function admissionsMetrics(array $monthKeys): array
    {
        $admissions = AdmissionRegistration::query()
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m', $monthKeys[0])->startOfMonth(),
                Carbon::createFromFormat('Y-m', end($monthKeys))->endOfMonth(),
            ])
            ->get(['id', 'status', 'created_at']);

        $monthly = $this->countsByMonth($admissions, 'created_at', $monthKeys);

        $total = AdmissionRegistration::query()->count();
        $pending = AdmissionRegistration::query()->where('status', 'pending')->count();
        $approved = AdmissionRegistration::query()->where('status', 'approved')->count();
        $enrolled = AdmissionRegistration::query()->whereNotNull('enrolled_at')->count();

        $conversionRate = $total > 0 ? round(($enrolled / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'enrolled' => $enrolled,
            'conversion_rate' => $conversionRate,
            'monthly' => $monthly,
        ];
    }

    protected function inquiryMetrics(array $monthKeys): array
    {
        $messages = ContactMessage::query()
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m', $monthKeys[0])->startOfMonth(),
                Carbon::createFromFormat('Y-m', end($monthKeys))->endOfMonth(),
            ])
            ->get(['id', 'status', 'created_at']);

        $monthly = $this->countsByMonth($messages, 'created_at', $monthKeys);

        $total = ContactMessage::query()->count();
        $new = ContactMessage::query()->where('status', 'new')->count();
        $inProgress = ContactMessage::query()->where('status', 'in_progress')->count();
        $resolved = ContactMessage::query()->where('status', 'resolved')->count();

        $responseRate = $total > 0 ? round(($resolved / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'new' => $new,
            'in_progress' => $inProgress,
            'resolved' => $resolved,
            'response_rate' => $responseRate,
            'monthly' => $monthly,
        ];
    }

    protected function feeMetrics(array $monthKeys): array
    {
        $baseQuery = FeeInvoiceRecord::query()
            ->whereHas('feeInvoice.user', function ($query): void {
                $query->where('school_id', auth()->user()?->school_id);
            });

        $allRows = (clone $baseQuery)
            ->select(['amount', 'fine', 'paid', 'waiver'])
            ->get();

        $billable = (int) $allRows->sum(fn (FeeInvoiceRecord $row): int => (int) $row->getRawOriginal('amount') + (int) $row->getRawOriginal('fine'));
        $paid = (int) $allRows->sum(fn (FeeInvoiceRecord $row): int => (int) $row->getRawOriginal('paid'));
        $waiver = (int) $allRows->sum(fn (FeeInvoiceRecord $row): int => (int) $row->getRawOriginal('waiver'));
        $outstanding = max(0, $billable - $paid - $waiver);

        $rowsForPeriod = (clone $baseQuery)
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m', $monthKeys[0])->startOfMonth(),
                Carbon::createFromFormat('Y-m', end($monthKeys))->endOfMonth(),
            ])
            ->get(['paid', 'created_at']);

        $monthly = array_fill_keys($monthKeys, 0);
        foreach ($rowsForPeriod as $row) {
            $monthKey = Carbon::parse($row->created_at)->format('Y-m');
            if (array_key_exists($monthKey, $monthly)) {
                $monthly[$monthKey] += (int) $row->getRawOriginal('paid');
            }
        }

        return [
            'billable' => $billable,
            'paid' => $paid,
            'waiver' => $waiver,
            'outstanding' => $outstanding,
            'monthly_collected' => $monthly,
        ];
    }

    protected function engagementMetrics(array $monthKeys): array
    {
        $broadcasts = BroadcastMessage::query()
            ->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m', $monthKeys[0])->startOfMonth(),
                Carbon::createFromFormat('Y-m', end($monthKeys))->endOfMonth(),
            ])
            ->get(['id', 'created_at']);

        $monthlyBroadcasts = $this->countsByMonth($broadcasts, 'created_at', $monthKeys);

        $activeNotices = Notice::query()
            ->where('active', 1)
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('stop_date', '>=', now()->toDateString())
            ->count();

        $portalRecipients = DB::table('broadcast_message_recipients')
            ->whereNotNull('portal_delivered_at')
            ->where('school_id', auth()->user()?->school_id)
            ->count();

        return [
            'notices_total' => Notice::query()->count(),
            'active_notices' => $activeNotices,
            'broadcasts_total' => BroadcastMessage::query()->count(),
            'portal_reach' => $portalRecipients,
            'gallery_items' => GalleryItem::query()->count(),
            'media_assets' => MediaAsset::query()->where('school_id', auth()->user()?->school_id)->count(),
            'monthly_broadcasts' => $monthlyBroadcasts,
        ];
    }

    protected function maxFromSeries(array $series): int
    {
        $max = 0;
        foreach ($series as $value) {
            $max = max($max, (int) $value);
        }

        return max(1, $max);
    }

    public function render()
    {
        $monthKeys = $this->monthLabels();

        $admissions = $this->admissionsMetrics($monthKeys);
        $inquiries = $this->inquiryMetrics($monthKeys);
        $fees = $this->feeMetrics($monthKeys);
        $engagement = $this->engagementMetrics($monthKeys);

        $labels = collect($monthKeys)->map(fn (string $key): string => $this->formatMonth($key))->all();

        return view('livewire.dashboard.analytics-dashboard', [
            'monthKeys' => $monthKeys,
            'monthLabels' => $labels,
            'admissions' => $admissions,
            'inquiries' => $inquiries,
            'fees' => $fees,
            'engagement' => $engagement,
            'admissionsMax' => $this->maxFromSeries($admissions['monthly']),
            'inquiriesMax' => $this->maxFromSeries($inquiries['monthly']),
            'feeMax' => $this->maxFromSeries($fees['monthly_collected']),
            'engagementMax' => $this->maxFromSeries($engagement['monthly_broadcasts']),
        ])
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('analytics.index'), 'text' => 'Analytics Dashboard', 'active' => true],
                ],
            ])
            ->title('Analytics Dashboard');
    }
}
