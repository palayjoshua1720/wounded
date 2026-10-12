<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Orders;
use App\Models\UsageLog;
use App\Models\IVR;
use App\Models\Invoice;
use App\Models\Returns;
use App\Models\NotificationRead;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotificationController extends Controller
{
    /**
     * Fetch notification history – mirrors AdminDashboardController::recentActivity
     * but keeps ALL records (no 2-day window) and supports date-range + pagination.
     */
    public function fetchNotif(Request $request)
    {
        $perPage   = (int) $request->input('per_page', 20);
        $page      = (int) $request->input('page', 1);
        $search    = $request->input('search');
        $type      = $request->input('type');       // order, usage, ivr, invoice, return
        $startDate = $request->input('start_date');  // Y-m-d
        $endDate   = $request->input('end_date');    // Y-m-d
        $clinicId  = $request->input('clinic_id');

        $since = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $until = $endDate   ? Carbon::parse($endDate)->endOfDay()     : null;

        $userId = $request->user()?->id;
        $readIds = NotificationRead::where('user_id', $userId)
            ->pluck('notification_id')
            ->toArray();

        $events = collect();

        // ── Usage Logs ─────────────────────────────────────────────
        if (!$clinicId && (!$type || $type === 'usage')) {
            if (class_exists(UsageLog::class)) {
                $query = UsageLog::with(['clinic', 'patient.clinic', 'graftSize'])
                    ->orderBy('logged_at', 'desc');

                if ($since) $query->where('logged_at', '>=', $since);
                if ($until) $query->where('logged_at', '<=', $until);

                $query->get()->each(function ($log) use ($events, $readIds) {
                    $clinicName = $log->clinic?->clinic_name
                        ?? $log->patient?->clinic?->clinic_name
                        ?? ($log->patient_id ? "Clinic (patient {$log->patient_id})" : 'No patient linked');

                    $notifId = 'usage_' . $log->graft_log_id;

                    $events->push([
                        'id'          => $notifId,
                        'type'        => 'usage',
                        'title'       => 'Usage log recorded',
                        'message'     => "Serial {$log->serial_number} logged at {$clinicName}",
                        'clinic'      => $clinicName,
                        'patient'     => $log->patient?->patient_name ?? null,
                        'serial'      => $log->serial_number ?? null,
                        'status'      => match ((int) $log->log_status) {
                            0 => 'Used',
                            1 => 'Unused',
                            2 => 'Expired',
                            3 => 'Returned',
                            default => 'Unknown',
                        },
                        'created_at'  => $log->logged_at?->toIso8601String(),
                        'is_read'     => in_array($notifId, $readIds),
                    ]);
                });
            }
        }

        // ── Orders ─────────────────────────────────────────────────
        if (!$type || $type === 'order') {
            $query = Orders::with(['clinic', 'patient', 'manufacturer'])
                ->orderBy('ordered_at', 'desc');

            if ($clinicId) $query->where('clinic_id', $clinicId);
            if ($since) $query->where('ordered_at', '>=', $since);
            if ($until) $query->where('ordered_at', '<=', $until);

            $query->get()->each(function ($o) use ($events, $readIds) {
                $productNames = [];
                $brandNames   = [];
                if (is_array($o->items) && !empty($o->items)) {
                    foreach ($o->items as $item) {
                        if (!empty($item['name'])) {
                            $productNames[] = $item['name'];
                        } elseif (!empty($item['product_name'])) {
                            $productNames[] = $item['product_name'];
                        } elseif (!empty($item['description'])) {
                            $productNames[] = $item['description'];
                        } elseif (!empty($item['type']) && !empty($item['size'])) {
                            $productNames[] = $item['type'] . ' (' . $item['size'] . ')';
                        }

                        if (!empty($item['brand_id'])) {
                            $brand = Brand::find($item['brand_id']);
                            if ($brand) {
                                $brandNames[] = $brand->name ?? $brand->brand_name ?? 'Brand #' . $item['brand_id'];
                            }
                        }
                    }
                }

                $displayProduct = !empty($productNames) ? implode(', ', $productNames) : null;
                $displayBrands  = !empty($brandNames) ? implode(', ', array_unique($brandNames)) : null;

                $statusLabel = match ((int) $o->order_status) {
                    0 => 'Submitted',
                    1 => 'Acknowledged',
                    2 => 'Shipped',
                    3 => 'Delivered',
                    4 => 'Cancelled',
                    default => 'Unknown',
                };

                $notifId = 'order_' . $o->order_id;

                $events->push([
                    'id'           => $notifId,
                    'type'         => 'order',
                    'title'        => 'New order placed',
                    'message'      => 'Order from ' . ($o->clinic?->clinic_name ?? 'Unknown Clinic'),
                    'clinic'       => $o->clinic?->clinic_name ?? 'Unknown Clinic',
                    'patient'      => $o->patient?->patient_name ?? null,
                    'detail'       => $displayProduct,
                    'brands'       => $displayBrands,
                    'manufacturer' => $o->manufacturer?->manufacturer_name ?? null,
                    'status'       => $statusLabel,
                    'created_at'   => $o->ordered_at?->toIso8601String(),
                    'is_read'      => in_array($notifId, $readIds),
                ]);
            });
        }

        // ── IVR ────────────────────────────────────────────────────
        if (!$type || $type === 'ivr') {
            if (class_exists(IVR::class)) {
                $query = IVR::with(['clinic', 'patient', 'brand', 'manufacturer'])
                    ->latest('updated_at');

                if ($clinicId) $query->where('clinic_id', $clinicId);
                if ($since) $query->where('updated_at', '>=', $since);
                if ($until) $query->where('updated_at', '<=', $until);

                $query->get()->each(function ($ivr) use ($events, $readIds) {
                    $clinicName = $ivr->clinic?->clinic_name ?? 'Unknown Clinic';

                    $action = match (true) {
                        $ivr->created_at->diffInMinutes($ivr->updated_at) <= 2
                            && $ivr->eligibility_status === 0 => 'IVR submitted',
                        $ivr->eligibility_status === 0 => 'IVR eligibility still pending',
                        $ivr->eligibility_status === 1 => 'IVR marked as Eligible',
                        $ivr->eligibility_status === 2 => 'IVR marked as Not Eligible',
                        default => 'IVR record updated',
                    };

                    $statusLabel = match ((int) $ivr->eligibility_status) {
                        0 => 'Pending',
                        1 => 'Eligible',
                        2 => 'Not Eligible',
                        default => 'Unknown',
                    };

                    $notifId = 'ivr_' . $ivr->ivr_id;

                    $events->push([
                        'id'           => $notifId,
                        'type'         => 'ivr',
                        'title'        => $action,
                        'message'      => "{$action} • {$clinicName}",
                        'clinic'       => $clinicName,
                        'patient'      => $ivr->patient?->patient_name ?? null,
                        'detail'       => $ivr->ivr_number ? "IVR {$ivr->ivr_number}" : null,
                        'brands'       => $ivr->brand?->name ?? $ivr->brand?->brand_name ?? null,
                        'manufacturer' => $ivr->manufacturer?->manufacturer_name ?? null,
                        'status'       => $statusLabel,
                        'created_at'   => $ivr->updated_at?->toIso8601String(),
                        'is_read'      => in_array($notifId, $readIds),
                    ]);
                });
            }
        }

        // ── Invoices ───────────────────────────────────────────────
        if (!$clinicId && (!$type || $type === 'invoice')) {
            if (class_exists(Invoice::class)) {
                $query = Invoice::with('clinic')
                    ->latest('created_at');

                if ($since) $query->where('created_at', '>=', $since);
                if ($until) $query->where('created_at', '<=', $until);

                $query->get()->each(function ($invoice) use ($events, $readIds) {
                    $action = match (true) {
                        $invoice->status === 'pending_review' => 'Invoice pending review',
                        $invoice->status === 'pending' => 'Invoice payment pending',
                        $invoice->status === 'paid'     => 'Invoice paid',
                        $invoice->status === 'overdue'  => 'Invoice overdue',
                        $invoice->status === 'cancelled'  => 'Invoice cancelled',
                        default                         => 'Invoice created',
                    };

                    $statusLabel = ucwords(str_replace('_', ' ', $invoice->status ?? 'pending'));

                    $notifId = 'invoice_' . $invoice->id;

                    $events->push([
                        'id'         => $notifId,
                        'type'       => 'invoice',
                        'title'      => $action,
                        'message'    => ($invoice->invoice_number ? "Inv #{$invoice->invoice_number}" : 'Invoice') . ' • ' . ($invoice->clinic?->clinic_name ?? 'Unknown Clinic'),
                        'clinic'     => $invoice->clinic?->clinic_name ?? 'Unknown Clinic',
                        'detail'     => $invoice->invoice_number ? "Inv #{$invoice->invoice_number}" : null,
                        'amount'     => $invoice->amount ? '$' . number_format($invoice->amount, 2) : null,
                        'status'     => $statusLabel,
                        'created_at' => $invoice->created_at?->toIso8601String(),
                        'is_read'    => in_array($notifId, $readIds),
                    ]);
                });
            }
        }

        // ── Returns ────────────────────────────────────────────────
        if (!$clinicId && (!$type || $type === 'return')) {
            if (class_exists(Returns::class)) {
                $query = Returns::with(['usageLog.patient.clinic', 'brand', 'graftSize'])
                    ->latest('returned_at');

                if ($since) $query->where('returned_at', '>=', $since);
                if ($until) $query->where('returned_at', '<=', $until);

                $query->get()->each(function ($return) use ($events, $readIds) {
                    $clinicName = $return->usageLog?->patient?->clinic?->clinic_name ?? 'Unknown Clinic';

                    $notifId = 'return_' . $return->return_id;

                    $events->push([
                        'id'         => $notifId,
                        'type'       => 'return',
                        'title'      => 'Return processed',
                        'message'    => "Return processed • {$clinicName}",
                        'clinic'     => $clinicName,
                        'patient'    => $return->usageLog?->patient?->patient_name ?? null,
                        'detail'     => $return->ocr_serial_number ?? null,
                        'brands'     => $return->brand?->name ?? $return->brand?->brand_name ?? null,
                        'status'     => $return->reason ? ucfirst($return->reason) : 'Returned',
                        'created_at' => $return->returned_at?->toIso8601String(),
                        'is_read'    => in_array($notifId, $readIds),
                    ]);
                });
            }
        }

        // ── Search filter ──────────────────────────────────────────
        if ($search) {
            $q = strtolower($search);
            $events = $events->filter(function ($e) use ($q) {
                return str_contains(strtolower($e['title'] ?? ''), $q)
                    || str_contains(strtolower($e['message'] ?? ''), $q)
                    || str_contains(strtolower($e['clinic'] ?? ''), $q)
                    || str_contains(strtolower($e['patient'] ?? ''), $q)
                    || str_contains(strtolower($e['detail'] ?? ''), $q);
            });
        }

        // ── Sort & paginate ────────────────────────────────────────
        $sorted = $events->sortByDesc('created_at')->values();
        $total  = $sorted->count();
        $items  = $sorted->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'last_page'    => (int) ceil($total / $perPage),
                'unread_count' => $events->where('is_read', false)->count(),
            ]
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        NotificationRead::updateOrCreate([
            'user_id' => $request->user()?->id,
            'notification_id' => $id,
        ], [
            'read_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        $ids = $request->input('ids', []);
        $userId = $request->user()?->id;

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No IDs provided'], 400);
        }

        foreach ($ids as $id) {
            NotificationRead::updateOrCreate([
                'user_id' => $userId,
                'notification_id' => $id,
            ], [
                'read_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get notification statistics - counts across ALL data (not just current page)
     */
    public function stats(Request $request)
    {
        $clinicId = $request->input('clinic_id');
        $userId = $request->user()?->id;
        
        $readIds = NotificationRead::where('user_id', $userId)
            ->pluck('notification_id')
            ->toArray();

        $events = collect();
        $todayStart = Carbon::today()->startOfDay();
        $weekStart = Carbon::now()->subDays(7)->startOfDay();

        // ── Usage Logs ─────────────────────────────────────────────
        if (!$clinicId) {
            if (class_exists(UsageLog::class)) {
                $query = UsageLog::with(['clinic', 'patient.clinic']);
                
                $query->get()->each(function ($log) use (&$events, $readIds) {
                    $notifId = 'usage_' . $log->graft_log_id;

                    $events->push([
                        'id'          => $notifId,
                        'created_at'  => $log->logged_at?->toIso8601String(),
                        'is_read'     => in_array($notifId, $readIds),
                    ]);
                });
            }
        }

        // ── Orders ─────────────────────────────────────────────────
        $query = Orders::with(['clinic']);
        if ($clinicId) $query->where('clinic_id', $clinicId);
        
        $query->get()->each(function ($o) use (&$events, $readIds) {
            $notifId = 'order_' . $o->order_id;
            
            $events->push([
                'id'          => $notifId,
                'created_at'  => $o->ordered_at?->toIso8601String(),
                'is_read'     => in_array($notifId, $readIds),
            ]);
        });

        // ── IVR ────────────────────────────────────────────────────
        if (class_exists(IVR::class)) {
            $query = IVR::with(['clinic']);
            if ($clinicId) $query->where('clinic_id', $clinicId);
            
            $query->get()->each(function ($ivr) use (&$events, $readIds) {
                $notifId = 'ivr_' . $ivr->ivr_id;
                
                $events->push([
                    'id'          => $notifId,
                    'created_at'  => $ivr->updated_at?->toIso8601String(),
                    'is_read'     => in_array($notifId, $readIds),
                ]);
            });
        }

        // ── Invoices ───────────────────────────────────────────────
        if (!$clinicId && class_exists(Invoice::class)) {
            Invoice::with('clinic')->get()->each(function ($invoice) use (&$events, $readIds) {
                $notifId = 'invoice_' . $invoice->id;
                
                $events->push([
                    'id'          => $notifId,
                    'created_at'  => $invoice->created_at?->toIso8601String(),
                    'is_read'     => in_array($notifId, $readIds),
                ]);
            });
        }

        // ── Returns ────────────────────────────────────────────────
        if (!$clinicId && class_exists(Returns::class)) {
            Returns::with(['usageLog.patient.clinic'])->get()->each(function ($return) use (&$events, $readIds) {
                $notifId = 'return_' . $return->return_id;
                
                $events->push([
                    'id'          => $notifId,
                    'created_at'  => $return->returned_at?->toIso8601String(),
                    'is_read'     => in_array($notifId, $readIds),
                ]);
            });
        }

        $total = $events->count();
        $unread = $events->where('is_read', false)->count();
        
        // Count notifications from the last 24 hours
        $today = $events->filter(function ($e) use ($todayStart) {
            if (!$e['created_at']) return false;
            $date = Carbon::parse($e['created_at']);
            return $date->gte($todayStart);
        })->count();

        // Count notifications from the last 7 days
        $week = $events->filter(function ($e) use ($weekStart) {
            if (!$e['created_at']) return false;
            $date = Carbon::parse($e['created_at']);
            return $date->gte($weekStart);
        })->count();

        return response()->json([
            'success' => true,
            'total' => $total,
            'unread' => $unread,
            'today' => $today,
            'week' => $week,
        ]);
    }
}