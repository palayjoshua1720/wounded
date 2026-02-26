<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Manufacturer;
use App\Models\Clinic;
use App\Models\Orders;
use App\Models\UsageLog;
use App\Models\IVR;
use App\Models\Invoice;
use App\Models\Returns;

class AdminDashboardController extends Controller
{
    public function getStats()
    {
        $thirtyDaysAgo = now()->subDays(30);
        $today = today();

        $countNew  = fn($query) => $query->clone()->where('created_at', '>=', $thirtyDaysAgo)->count();
        $countToday = fn($query) => $query->clone()->whereDate('created_at', $today)->count();

        $stats = [
            'brands' => [
                'total'     => Brand::withoutTrashed()->count(),
                'active'    => Brand::withoutTrashed()->where('brand_status', 0)->count(),
                'inactive'  => Brand::withoutTrashed()->where('brand_status', 1)->count(),
                'archived'  => Brand::where('brand_status', 2)->count(),
                'new'       => $countNew(Brand::withoutTrashed()),
                'today'     => $countToday(Brand::withoutTrashed()),
            ],
            'manufacturers' => [
                'total'     => Manufacturer::withoutTrashed()->count(),
                'active'    => Manufacturer::withoutTrashed()->where('manufacturer_status', 0)->count(),
                'inactive'  => Manufacturer::withoutTrashed()->where('manufacturer_status', 1)->count(),
                'archived'  => Manufacturer::where('manufacturer_status', 2)->count(),
                'new'       => $countNew(Manufacturer::withoutTrashed()),
                'today'     => $countToday(Manufacturer::withoutTrashed()),
            ],
            'clinics' => [
                'total'     => Clinic::withoutTrashed()->count(),
                'active'    => Clinic::withoutTrashed()->where('clinic_status', 0)->count(),
                'inactive'  => Clinic::withoutTrashed()->where('clinic_status', 1)->count(),
                'archived'  => Clinic::where('clinic_status', 2)->count(),
                'new'       => $countNew(Clinic::withoutTrashed()),
                'today'     => $countToday(Clinic::withoutTrashed()),
            ],
            'orders' => [
                'total'       => Orders::count(),
                'pending'     => Orders::where('order_status', 0)->count(),
                'processing'  => Orders::where('order_status', 1)->count(),
                'shipped'     => Orders::where('order_status', 2)->count(),
                'delivered'   => Orders::where('order_status', 3)->count(),
                'cancelled'   => Orders::where('order_status', 4)->count(),
                'new'         => Orders::where('created_at', '>=', $thirtyDaysAgo)->count(),
                'today'       => Orders::whereDate('created_at', $today)->count(),
            ],
        ];

        return response()->json([
            'success'   => true,
            'data'      => [
                'brands'        => array_map('intval', $stats['brands']),
                'manufacturers' => array_map('intval', $stats['manufacturers']),
                'clinics'       => array_map('intval', $stats['clinics']),
                'orders'        => array_map('intval', $stats['orders']),
            ]
        ]);
    }


    public function recentActivity()
    {
        $since = now()->subDays(2);
        $events = collect();

        // Usage Logs
        if (class_exists(UsageLog::class)) {
            UsageLog::with(['clinic', 'patient.clinic'])
                ->where('logged_at', '>=', $since)
                ->orderBy('logged_at', 'desc')
                ->take(5)
                ->get()
                ->each(function ($log) use ($events) {
                    $clinicName = $log->clinic?->clinic_name
                        ?? $log->patient?->clinic?->clinic_name
                        ?? ($log->patient_id ? "Clinic (patient {$log->patient_id})" : 'No patient linked');

                    $events->push([
                        'id'        => 'usage_' . $log->graft_log_id,
                        'type'      => 'usage',
                        'action'    => 'Usage log recorded',
                        'clinic'    => $clinicName,
                        'timestamp' => $log->logged_at ? $log->logged_at->format('Y-m-d H:i:s') : null,
                    ]);
                });
        }

        // Orders
        Orders::with('clinic')
            ->where('ordered_at', '>=', $since)
            ->orderBy('ordered_at', 'desc')
            ->take(5)
            ->get()
            ->each(function ($o) use ($events) {
                $events->push([
                    'id'        => 'order_' . $o->order_id,
                    'type'      => 'order',
                    'action'    => 'New order placed',
                    'clinic'    => $o->clinic?->name ?? 'Unknown Clinic',
                    'timestamp' => $o->ordered_at ? $o->ordered_at->format('Y-m-d H:i:s') : null,
                ]);
            });

        // IVR
        if (class_exists(IVR::class)) {
            IVR::with('clinic')
                ->where('updated_at', '>=', $since)
                ->latest('updated_at')
                ->take(5)
                ->get()
                ->each(function ($ivr) use ($events) {
                    $action = match ($ivr->status ?? 'submitted') {
                        'approved' => 'IVR approved',
                        default    => 'IVR submitted',
                    };

                    $events->push([
                        'id'        => 'ivr_' . $ivr->id,
                        'type'      => 'ivr',
                        'action'    => $action,
                        'clinic'    => $ivr->clinic?->name ?? 'Unknown Clinic',
                        'timestamp' => $ivr->updated_at?->format('Y-m-d H:i:s'),
                    ]);
                });
        }

        // Invoices
        if (class_exists(Invoice::class)) {
            Invoice::with('clinic')
                ->where('created_at', '>=', $since)
                ->latest('created_at')
                ->take(5)
                ->get()
                ->each(function ($invoice) use ($events) {
                    $action = match (true) {
                        $invoice->paid_date !== null    => 'Invoice paid',
                        $invoice->status === 'overdue'  => 'Invoice overdue',
                        $invoice->status === 'partial'  => 'Partial payment received',
                        default                         => 'Invoice created',
                    };

                    $events->push([
                        'id'        => 'invoice_' . $invoice->id,
                        'type'      => 'invoice',
                        'action'    => $action,
                        'clinic'    => $invoice->clinic?->name ?? 'Unknown Clinic',
                        'time'      => $invoice->created_at?->diffForHumans() ?? 'Unknown time',
                        'timestamp' => $invoice->created_at,
                    ]);
                });
        }

        // Returns (processed returns)
        if (class_exists(Returns::class)) {
            Returns::with(['usageLog.patient.clinic'])
                ->where('returned_at', '>=', $since)
                ->latest('returned_at')
                ->take(15)
                ->get()
                ->each(function ($return) use ($events) {
                    // Optional: customize action based on data (e.g. reason)
                    $action = 'Return processed';
                    if ($return->reason) {
                        $action .= ' (' . ucfirst($return->reason) . ')';
                    }

                    $clinicName = $return->usageLog?->patient?->clinic?->name ?? 'Unknown Clinic';

                    $events->push([
                        'id'        => 'return_' . $return->return_id,
                        'type'      => 'return',
                        'action'    => $action,
                        'clinic'    => $clinicName,
                        'time'      => $return->returned_at?->diffForHumans() ?? 'Unknown time',
                        'timestamp' => $return->returned_at,
                    ]);
                });
        }

        // Sort & limit final output
        $activities = $events
            ->sortByDesc('timestamp')
            ->take(10)
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data'    => $activities
        ]);
    }

    public function getSystemAlerts()
    {
        $alerts = collect();

        // 1. Overdue / unpaid invoices (high priority)
        $overdue = Invoice::where('due_date', '<=', now())
            ->whereNotIn('status', ['paid'])
            ->selectRaw('COUNT(*) as count, MIN(due_date) as oldest_due')
            ->first();

        if ($overdue?->count > 0) {
            $oldestDue = $overdue->oldest_due ? \Carbon\Carbon::parse($overdue->oldest_due) : null;
            $alerts->push([
                'type'     => 'error',
                'message'  => "{$overdue->count} overdue or unpaid invoices",
                'detail'   => $oldestDue
                    ? "Oldest due: " . $oldestDue->format('M d, Y') . " (" . $oldestDue->diffForHumans() . ")"
                    : 'Overdue invoices exist',
                'priority' => 'high',
            ]);
        }

        // 2. IVRs awaiting eligibility review (high priority)
        $pendingIVRs = IVR::where('eligibility_status', 0)
            ->whereNull('deleted_at')
            ->selectRaw('COUNT(*) as count, MIN(submitted_at) as oldest')
            ->first();

        if ($pendingIVRs?->count > 0) {
            $oldest = $pendingIVRs->oldest ? \Carbon\Carbon::parse($pendingIVRs->oldest) : null;
            $alerts->push([
                'type'     => 'warning',
                'message'  => "{$pendingIVRs->count} IVRs awaiting eligibility review",
                'detail'   => $oldest
                    ? "Oldest submitted: " . $oldest->format('M d, Y') . " (" . $oldest->diffForHumans() . ")"
                    : 'Pending IVRs exist',
                'priority' => 'high',
            ]);
        }

        // 3. Expired / overdue grafts (highest priority - needs immediate attention)
        $overdueGrafts = UsageLog::where('expired_at', '<', now())
            ->where('log_status', 0) // still considered "active" / not disposed
            ->selectRaw('COUNT(*) as count, MAX(expired_at) as most_recent_expired')
            ->first();

        if ($overdueGrafts?->count > 0) {
            $mostRecent = $overdueGrafts->most_recent_expired
                ? \Carbon\Carbon::parse($overdueGrafts->most_recent_expired)
                : null;

            $alerts->push([
                'type'     => 'error',
                'message'  => "{$overdueGrafts->count} grafts have already expired (overdue)",
                'detail'   => $mostRecent
                    ? "Most recent expiry: " . $mostRecent->format('M d, Y') . " (" . $mostRecent->diffForHumans() . ")"
                    : 'Expired grafts exist',
                'priority' => 'high',
            ]);
        }

        // 4. Grafts expiring soon (next 30 days) - proactive reminder
        $expiringSoon = UsageLog::where('expired_at', '>=', now())
            ->where('expired_at', '<=', now()->addDays(30))
            ->where('log_status', 0)
            ->selectRaw('COUNT(*) as count, MIN(expired_at) as soonest')
            ->first();

        if ($expiringSoon?->count > 0) {
            $soonest = $expiringSoon->soonest
                ? \Carbon\Carbon::parse($expiringSoon->soonest)
                : null;

            $alerts->push([
                'type'     => 'warning',
                'message'  => "{$expiringSoon->count} grafts expiring within the next 30 days",
                'detail'   => $soonest
                    ? "Earliest expiry: " . $soonest->format('M d, Y') . " (" . $soonest->diffForHumans() . ")"
                    : 'Upcoming expirations',
                'priority' => 'medium',
            ]);
        }

        // 5. Recent returns to review (medium priority)
        $recentReturns = Returns::where('returned_at', '>=', now()->subDays(14))
            ->selectRaw('COUNT(*) as count, MAX(returned_at) as latest')
            ->first();

        if ($recentReturns?->count > 0) {
            $latest = $recentReturns->latest ? \Carbon\Carbon::parse($recentReturns->latest) : null;
            $alerts->push([
                'type'     => 'info',
                'message'  => "{$recentReturns->count} recent returns to review",
                'detail'   => $latest
                    ? "Latest return: " . $latest->format('M d, Y') . " (" . $latest->diffForHumans() . ")"
                    : 'Recent returns exist',
                'priority' => 'medium',
            ]);
        }

        // Sort: high priority first, then medium → low
        $alerts = $alerts->sortByDesc(function ($alert) {
            return match ($alert['priority'] ?? 'low') {
                'high'   => 3,
                'medium' => 2,
                default  => 1,
            };
        })->take(8);

        return response()->json([
            'success' => true,
            'data'    => $alerts->values()->all(),
        ]);
    }
}
