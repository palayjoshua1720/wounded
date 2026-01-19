<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Manufacturer;
use App\Models\Clinic;
use App\Models\Orders;
use App\Models\Payment;
use App\Models\UsageLog;
use App\Models\IvrRequest;
use App\Models\ReturnRequest;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getStats()
    {
        $stats = Cache::remember('dashboard_global_stats_v6', now()->addMinutes(10), function () {
            $thirtyDaysAgo = now()->subDays(30);
            $today = today();

            $countNew   = fn($query) => $query->clone()->where('created_at', '>=', $thirtyDaysAgo)->count();
            $countToday = fn($query) => $query->clone()->whereDate('created_at', $today)->count();

            return [
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
        });

        return response()->json([
            'success'   => true,
            'data'      => [
                'brands'        => array_map('intval', $stats['brands']),
                'manufacturers' => array_map('intval', $stats['manufacturers']),
                'clinics'       => array_map('intval', $stats['clinics']),
                'orders'        => array_map('intval', $stats['orders']),
            ],
            'cached_at' => now()->toDateTimeString(),
        ]);
    }

    public function recentActivity()
    {
        $activities = Cache::remember('admin_dashboard_activity_v4', now()->addMinutes(5), function () {
            $since = now()->subHours(48);
            $events = collect();

            // 1. New Orders
            Orders::with('clinic')
                ->where('created_at', '>=', $since)
                ->latest()
                ->take(20)
                ->get()
                ->each(fn($o) => $events->push([
                    'id'        => 'order_' . $o->id,
                    'type'      => 'order',
                    'action'    => 'New order submitted',
                    'clinic'    => $o->clinic?->name ?? 'Unknown Clinic',
                    'time'      => $o->created_at->diffForHumans(),
                    'timestamp' => $o->created_at,
                ]));

            // 2. Payments
            // Payment::with('clinic')
            //     ->where('status', 'completed')
            //     ->where('created_at', '>=', $since)
            //     ->latest()
            //     ->take(10)
            //     ->get()
            //     ->each(fn($p) => $events->push([
            //         'id'        => 'payment_' . $p->id,
            //         'type'      => 'payment',
            //         'action'    => 'Payment received',
            //         'clinic'    => $p->clinic?->name ?? 'Unknown Clinic',
            //         'time'      => $p->created_at->diffForHumans(),
            //         'timestamp' => $p->created_at,
            //     ]));

            // 3. Usage Logs
            if (class_exists(UsageLog::class)) {
                UsageLog::with('clinic')
                    ->where('created_at', '>=', $since)
                    ->latest()
                    ->take(10)
                    ->get()
                    ->each(fn($log) => $events->push([
                        'id'        => 'usage_' . $log->id,
                        'type'      => 'usage',
                        'action'    => 'Usage log uploaded',
                        'clinic'    => $log->clinic?->name ?? 'Unknown Clinic',
                        'time'      => $log->created_at->diffForHumans(),
                        'timestamp' => $log->created_at,
                    ]));
            }

            // 4. IVR Approvals
            // if (class_exists(IvrRequest::class)) {
            //     IvrRequest::with('clinic')
            //         ->where('status', 'approved')
            //         ->where('updated_at', '>=', $since)
            //         ->latest('updated_at')
            //         ->take(8)
            //         ->get()
            //         ->each(fn($ivr) => $events->push([
            //             'id'        => 'ivr_' . $ivr->id,
            //             'type'      => 'ivr',
            //             'action'    => 'IVR approved',
            //             'clinic'    => $ivr->clinic?->name ?? 'Unknown Clinic',
            //             'time'      => $ivr->updated_at->diffForHumans(),
            //             'timestamp' => $ivr->updated_at,
            //         ]));
            // }

            // 5. Returns
            // if (class_exists(ReturnRequest::class)) {
            //     ReturnRequest::with(['order.clinic'])
            //         ->where('status', 'processed')
            //         ->where('updated_at', '>=', $since)
            //         ->latest('updated_at')
            //         ->take(8)
            //         ->get()
            //         ->each(fn($r) => $events->push([
            //             'id'        => 'return_' . $r->id,
            //             'type'      => 'return',
            //             'action'    => 'Return processed',
            //             'clinic'    => $r->order?->clinic?->name ?? 'Unknown Clinic',
            //             'time'      => $r->updated_at->diffForHumans(),
            //             'timestamp' => $r->updated_at,
            //         ]));
            // }

            return $events
                ->sortByDesc('timestamp')
                ->take(10)
                ->values()
                ->all();
        });

        echo '<pre>';
        print_r($activities);

        return response()->json([
            'success' => true,
            'data'    => $activities
        ]);
    }
}
