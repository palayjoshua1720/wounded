<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\UsageLog;
use App\Models\Brand;
use App\Models\Invoice;
use App\Models\IVR;
use App\Models\Returns;

class ClinicDashboardController extends Controller
{
    public function orderOverview()
    {
        $user = Auth::user();
        $clinicId = $user->clinic_id ?? null;

        if (!$clinicId) {
            return response()->json(['error' => 'Clinic not associated with user'], 403);
        }

        $ordersQuery = Orders::where('clinic_id', $clinicId);

        $totalOrders = (clone $ordersQuery)->count();

        $thisMonthOrders = (clone $ordersQuery)
            ->whereMonth('ordered_at', Carbon::now()->month)
            ->whereYear('ordered_at', Carbon::now()->year)
            ->count();

        $pending   = (clone $ordersQuery)->whereIn('order_status', [0, 1])->count();
        $shipped   = (clone $ordersQuery)->where('order_status', 2)->count();
        $delivered = (clone $ordersQuery)->where('order_status', 3)->count();

        $recentOrders = $ordersQuery
            ->with([
                'patient',
                'manufacturer'
            ])
            ->orderBy('ordered_at', 'desc')
            ->take(8)
            ->get()
            ->map(function ($order) {
                $productNames = [];
                if (is_array($order->items) && !empty($order->items)) {
                    foreach ($order->items as $item) {
                        if (!empty($item['name'])) {
                            $productNames[] = $item['name'];
                        } elseif (!empty($item['product_name'])) {
                            $productNames[] = $item['product_name'];
                        } elseif (!empty($item['description'])) {
                            $productNames[] = $item['description'];
                        } elseif (!empty($item['type']) && !empty($item['size'])) {
                            $productNames[] = $item['type'] . ' (' . $item['size'] . ')';
                        }

                        // Brand name
                        if (!empty($item['brand_id'])) {
                            $brand = Brand::find($item['brand_id']);
                            if ($brand) {
                                $brandNames[] = $brand->name ?? $brand->brand_name ?? 'Brand #' . $item['brand_id'];
                            } else {
                                $brandNames[] = 'Brand ID ' . $item['brand_id'];
                            }
                        }
                    }
                }

                $displayProduct = !empty($productNames)
                    ? implode(', ', $productNames)
                    : 'Order #' . ($order->order_number ?? $order->order_code ?? $order->order_id);

                $displayBrands = !empty($brandNames)
                    ? implode(', ', array_unique($brandNames))
                    : '—';

                if (strlen($displayBrands) > 60) {
                    $displayBrands = substr($displayBrands, 0, 57) . '...';
                }
                return [
                    'id'             => $order->order_id,
                    'product'        => $displayProduct,
                    'patient_name'   => $order->patient->patient_name,
                    'status'         => $this->mapStatusToString($order->order_status),
                    'ordered_at'     => $order->ordered_at?->toISOString(),
                    'manufacturer'   => $order->manufacturer->manufacturer_name,
                    'brands'         => $displayBrands,
                ];
            });


        return response()->json([
            'data' => [
                'orders' => [
                    'total'     => $totalOrders,
                    'this_month' => $thisMonthOrders,
                    'pending'   => $pending,
                    'shipped'   => $shipped,
                    'delivered' => $delivered,
                ],
                'recent_orders'   => $recentOrders,
            ]
        ]);
    }

    private function mapStatusToString(int $status): string
    {
        return match ($status) {
            0 => 'Submitted',
            1 => 'Acknowledged',
            2 => 'Shipped',
            3 => 'Delivered',
            default => 'Unknown',
        };
    }

    public function getSystemAlerts()
    {
        $user = Auth::user();
        $clinicId = $user->clinic_id ?? null;

        if (!$clinicId) {
            return response()->json(['success' => false, 'message' => 'Clinic not associated'], 403);
        }

        $alerts = collect();

        // 1. Overdue / unpaid invoices for this clinic (high priority)
        $overdue = Invoice::where('clinic_id', $clinicId)
            ->where('due_date', '<=', now())
            ->whereNotIn('status', ['paid'])
            ->selectRaw('COUNT(*) as count, MIN(due_date) as oldest_due')
            ->first();

        if ($overdue?->count > 0) {
            $oldestDue = $overdue->oldest_due ? Carbon::parse($overdue->oldest_due) : null;
            $alerts->push([
                'type'     => 'error',
                'message'  => "{$overdue->count} overdue or unpaid invoices",
                'detail'   => $oldestDue
                    ? "Oldest due: " . $oldestDue->format('M d, Y') . " (" . $oldestDue->diffForHumans() . ")"
                    : 'Overdue invoices exist',
                'priority' => 'high',
                'timestamp' => $oldestDue?->toISOString(),
            ]);
        }

        // 2. IVRs awaiting eligibility review for this clinic (high priority)
        $pendingIVRs = IVR::whereHas('clinic', function ($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId);
        })
            ->where('eligibility_status', 0)
            ->whereNull('deleted_at')
            ->selectRaw('COUNT(*) as count, MIN(submitted_at) as oldest')
            ->first();

        if ($pendingIVRs?->count > 0) {
            $oldest = $pendingIVRs->oldest ? Carbon::parse($pendingIVRs->oldest) : null;
            $alerts->push([
                'type'     => 'warning',
                'message'  => "{$pendingIVRs->count} IVRs awaiting eligibility review",
                'detail'   => $oldest
                    ? "Oldest submitted: " . $oldest->format('M d, Y') . " (" . $oldest->diffForHumans() . ")"
                    : 'Pending IVRs exist',
                'priority' => 'high',
                'timestamp' => $oldest?->toISOString(),
            ]);
        }

        // 3. Expired / overdue grafts for this clinic (highest priority)
        $overdueGrafts = UsageLog::whereHas('patient', function ($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId);
        })
            ->where('expired_at', '<', now())
            ->where('log_status', 0) // still active / not disposed
            ->selectRaw('COUNT(*) as count, MAX(expired_at) as most_recent_expired')
            ->first();

        if ($overdueGrafts?->count > 0) {
            $mostRecent = $overdueGrafts->most_recent_expired
                ? Carbon::parse($overdueGrafts->most_recent_expired)
                : null;
            $alerts->push([
                'type'     => 'error',
                'message'  => "{$overdueGrafts->count} grafts have already expired",
                'detail'   => $mostRecent
                    ? "Most recent expiry: " . $mostRecent->format('M d, Y') . " (" . $mostRecent->diffForHumans() . ")"
                    : 'Expired grafts exist',
                'priority' => 'high',
                'timestamp' => $mostRecent?->toISOString(),
            ]);
        }

        // 4. Grafts expiring soon (next 30 days) - proactive reminder
        $expiringSoon = UsageLog::whereHas('patient', function ($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId);
        })
            ->where('expired_at', '>=', now())
            ->where('expired_at', '<=', now()->addDays(30))
            ->where('log_status', 0)
            ->selectRaw('COUNT(*) as count, MIN(expired_at) as soonest')
            ->first();

        if ($expiringSoon?->count > 0) {
            $soonest = $expiringSoon->soonest
                ? Carbon::parse($expiringSoon->soonest)
                : null;
            $alerts->push([
                'type'     => 'warning',
                'message'  => "{$expiringSoon->count} grafts expiring within the next 30 days",
                'detail'   => $soonest
                    ? "Earliest expiry: " . $soonest->format('M d, Y') . " (" . $soonest->diffForHumans() . ")"
                    : 'Upcoming expirations',
                'priority' => 'medium',
                'timestamp' => $soonest?->toISOString(),
            ]);
        }

        // 5. Recent returns to review for this clinic (medium priority)
        $recentReturns = Returns::whereHas('usageLog.patient', function ($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId);
        })
            ->where('returned_at', '>=', now()->subDays(14))
            ->selectRaw('COUNT(*) as count, MAX(returned_at) as latest')
            ->first();

        if ($recentReturns?->count > 0) {
            $latest = $recentReturns->latest ? Carbon::parse($recentReturns->latest) : null;
            $alerts->push([
                'type'     => 'info',
                'message'  => "{$recentReturns->count} recent returns to review",
                'detail'   => $latest
                    ? "Latest return: " . $latest->format('M d, Y') . " (" . $latest->diffForHumans() . ")"
                    : 'Recent returns exist',
                'priority' => 'medium',
                'timestamp' => $latest?->toISOString(),
            ]);
        }

        // Sort: high priority first, then medium → low
        $alerts = $alerts->sortByDesc(function ($alert) {
            return match ($alert['priority'] ?? 'low') {
                'high'   => 3,
                'medium' => 2,
                default  => 1,
            };
        })->take(8)->values();

        return response()->json([
            'success' => true,
            'data'    => $alerts->all(),
        ]);
    }
}
