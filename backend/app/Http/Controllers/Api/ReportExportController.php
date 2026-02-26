<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Orders;
use App\Models\Clinic;
use App\Models\Brand;
use App\Models\Manufacturer;
use App\Models\Invoice;
use App\Models\IVR;
use App\Models\UsageLog;
use App\Models\Returns;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportExportController extends Controller
{
    /**
     * Export report as PDF
     */
    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|string|in:orders,inventory,usage,invoices,ivr',
            'date_range' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'clinic_id' => 'nullable|string',
            'brand_id' => 'nullable|string',
            'manufacturer_id' => 'nullable|string',
        ]);

        $reportData = $this->getReportData($validated);
        $filters = $this->getFilterLabels($validated);

        $pdf = PDF::loadView('reports.pdf', [
            'reportType' => $this->getReportTypeName($validated['report_type']),
            'dateRange' => $filters['dateRange'],
            'filters' => $filters,
            'data' => $reportData,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ]);

        $filename = $validated['report_type'] . '_report_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export report as Excel
     */
    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|string|in:orders,inventory,usage,invoices,ivr',
            'date_range' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'clinic_id' => 'nullable|string',
            'brand_id' => 'nullable|string',
            'manufacturer_id' => 'nullable|string',
        ]);

        $reportData = $this->getReportData($validated);
        $filters = $this->getFilterLabels($validated);

        $export = new \App\Exports\ReportExport(
            $validated['report_type'],
            $filters,
            $reportData
        );

        $filename = $validated['report_type'] . '_report_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download($export, $filename);
    }

    /**
     * Get report data based on type and filters
     */
    private function getReportData(array $filters): array
    {
        $reportType = $filters['report_type'];
        $dateRange = $this->getDateRange($filters);

        switch ($reportType) {
            case 'orders':
                return $this->getOrdersReport($dateRange, $filters);
            case 'inventory':
                return $this->getInventoryReport($dateRange, $filters);
            case 'usage':
                return $this->getUsageReport($dateRange, $filters);
            case 'invoices':
                return $this->getInvoiceReport($dateRange, $filters);
            case 'ivr':
                return $this->getIVRReport($dateRange, $filters);
            default:
                return [];
        }
    }

    /**
     * Get date range based on filter
     */
    private function getDateRange(array $filters): array
    {
        $dateRange = $filters['date_range'];
        $endDate = now();

        switch ($dateRange) {
            case 'last_7_days':
                $startDate = now()->subDays(7);
                break;
            case 'last_30_days':
                $startDate = now()->subDays(30);
                break;
            case 'last_year':
                $startDate = now()->subYear();
                break;
            case 'custom':
                $startDate = isset($filters['start_date']) ? Carbon::parse($filters['start_date']) : now()->subDays(7);
                $endDate = isset($filters['end_date']) ? Carbon::parse($filters['end_date']) : now();
                break;
            default:
                $startDate = now()->subDays(7);
        }

        return [
            'start' => $startDate->startOfDay(),
            'end' => $endDate->endOfDay(),
        ];
    }

    /**
     * Get orders report data
     */
    private function getOrdersReport(array $dateRange, array $filters): array
    {
        $query = Orders::query()
            ->whereBetween('ordered_at', [$dateRange['start'], $dateRange['end']]);

        if (!empty($filters['clinic_id']) && $filters['clinic_id'] !== 'all') {
            $query->where('clinic_id', $filters['clinic_id']);
        }

        if (!empty($filters['brand_id']) && $filters['brand_id'] !== 'all') {
            $query->whereHas('brand', function ($q) use ($filters) {
                $q->where('brand_id', $filters['brand_id']);
            });
        }

        $orders = $query->with(['clinic'])->get();

        $totalOrders = $orders->count();
        $totalItems = $orders->sum(function ($order) {
            $items = is_array($order->items) ? $order->items : [];
            return collect($items)->sum('quantity');
        });
        // Calculate total value from items (using 'asp' field for price)
        $totalValue = $orders->sum(function ($order) {
            $items = is_array($order->items) ? $order->items : [];
            return collect($items)->sum(function ($item) {
                $price = $item['asp'] ?? $item['price'] ?? $item['unit_price'] ?? 0;
                $quantity = $item['quantity'] ?? 1;
                return $price * $quantity;
            });
        });

        // Pre-load brands lookup for efficiency
        $brands = Brand::all()->keyBy('brand_id')->map(fn($b) => $b->brand_name);

        // Calculate all order items for analysis
        $allItems = $orders->flatMap(function ($order) use ($brands) {
            $items = is_array($order->items) ? $order->items : [];
            return collect($items)->map(function ($item) use ($order, $brands) {
                $brandId = $item['brand_id'] ?? $item['brandId'] ?? null;
                $brandName = $item['brand_name'] ?? null;
                
                // Look up brand name from database if not in item data
                if (empty($brandName) && $brandId) {
                    $brandName = $brands->get($brandId) ?? 'Unknown Brand';
                }
                
                return [
                    'product_name' => $item['product_name'] ?? $item['graft_size'] ?? 'Unknown Product',
                    'product_id' => $item['product_id'] ?? $item['productId'] ?? $item['id'] ?? null,
                    'brand_id' => $brandId,
                    'brand_name' => $brandName ?? 'Unknown Brand',
                    'size_id' => $item['graft_size_id'] ?? $item['graftSizeId'] ?? $item['size_id'] ?? $item['sizeId'] ?? null,
                    'size_name' => $item['graft_size_name'] ?? $item['graftSizeName'] ?? $item['size_name'] ?? $item['sizeName'] ?? null,
                    'quantity' => $item['quantity'] ?? 0,
                    'order_id' => $order->order_id,
                    'clinic_id' => $order->clinic_id,
                    'clinic_name' => $order->clinic->clinic_name ?? 'Unknown Clinic',
                ];
            });
        });

        // Order Status Breakdown (always shown)
        $statusMap = [
            0 => 'submitted',
            1 => 'acknowledged',
            2 => 'shipped',
            3 => 'delivered',
            4 => 'cancelled',
        ];
        
        $statusBreakdown = $orders->groupBy('order_status')
            ->map(function ($group) use ($totalOrders, $statusMap) {
                $statusValue = $group->first()->order_status;
                $statusLabel = $statusMap[$statusValue] ?? 'unknown';
                return [
                    'status' => $statusLabel,
                    'count' => $group->count(),
                    'percentage' => $totalOrders > 0 ? round(($group->count() / $totalOrders) * 100, 1) : 0,
                ];
            })->values();

        // Top Clinics (shown when "All Clinics" selected)
        $topClinics = $orders->groupBy('clinic_id')
            ->map(function ($group) use ($totalOrders) {
                return [
                    'clinic_id' => $group->first()->clinic_id,
                    'clinic_name' => $group->first()->clinic->clinic_name ?? 'Unknown Clinic',
                    'count' => $group->count(),
                    'percentage' => $totalOrders > 0 ? round(($group->count() / $totalOrders) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values();

        // Brand Distribution (shown when "All Brands" selected)
        $brandDistribution = $allItems->filter(fn($item) => !empty($item['brand_id']))
            ->groupBy('brand_id')
            ->map(function ($group) {
                return [
                    'brand_id' => $group->first()['brand_id'],
                    'brand_name' => $group->first()['brand_name'] ?? 'Unknown Brand',
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();

        // Top Products (shown when specific clinic selected)
        $topProducts = $allItems->filter(fn($item) => !empty($item['product_id']))
            ->groupBy('product_id')
            ->map(function ($group) {
                return [
                    'product_id' => $group->first()['product_id'],
                    'product_name' => $group->first()['product_name'],
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values();

        // Size Distribution (shown when specific brand selected)
        $sizeDistribution = $allItems->filter(fn($item) => !empty($item['size_id']))
            ->groupBy('size_id')
            ->map(function ($group) {
                return [
                    'size_id' => $group->first()['size_id'],
                    'size_name' => $group->first()['size_name'] ?? 'Unknown Size',
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();

        return [
            'summary' => [
                'total_orders' => $totalOrders,
                'total_items' => $totalItems,
                'total_value' => $totalValue,
            ],
            'status_breakdown' => $statusBreakdown,
            'top_clinics' => $topClinics,
            'brand_distribution' => $brandDistribution,
            'top_products' => $topProducts,
            'size_distribution' => $sizeDistribution,
            'orders' => $orders->map(function ($order) {
                $items = is_array($order->items) ? $order->items : [];
                // Calculate order total from items (using 'asp' field for price)
                $orderTotal = collect($items)->sum(function ($item) {
                    $price = $item['asp'] ?? $item['price'] ?? $item['unit_price'] ?? 0;
                    $quantity = $item['quantity'] ?? 1;
                    return $price * $quantity;
                });
                return [
                    'order_code' => $order->order_code,
                    'ordered_at' => $order->ordered_at->format('Y-m-d'),
                    'clinic_name' => $order->clinic->clinic_name ?? 'Unknown',
                    'items_count' => collect($items)->sum('quantity'),
                    'total_amount' => $orderTotal,
                ];
            }),
        ];
    }

    /**
     * Get inventory report data
     */
    private function getInventoryReport(array $dateRange, array $filters): array
    {
        // Inventory is based on usage logs with their status
        $query = UsageLog::query()
            ->whereBetween('logged_at', [$dateRange['start'], $dateRange['end']]);

        $usageLogs = $query->with(['patient.clinic', 'graftSize.brand'])->get();

        // Filter by clinic if specified
        if (!empty($filters['clinic_id']) && $filters['clinic_id'] !== 'all') {
            $usageLogs = $usageLogs->filter(function ($log) use ($filters) {
                return $log->patient?->clinic_id == $filters['clinic_id'];
            });
        }

        // Filter by brand if specified
        if (!empty($filters['brand_id']) && $filters['brand_id'] !== 'all') {
            $usageLogs = $usageLogs->filter(function ($log) use ($filters) {
                return $log->graftSize?->brand_id == $filters['brand_id'];
            });
        }

        $totalItems = $usageLogs->count();
        // log_status: 0 = expected, 1 = delivered, 2 = used, 3 = partially_used, 4 = reassigned, 5 = unused, 6 = expired
        $usedItems = $usageLogs->where('log_status', 2)->count();
        $inUseItems = $usageLogs->where('log_status', 1)->count();

        // Status breakdown with labels
        $statusMap = [
            0 => 'Expected',
            1 => 'Delivered',
            2 => 'Used',
            3 => 'Partially Used',
            4 => 'Reassigned',
            5 => 'Unused',
            6 => 'Expired',
        ];
        
        $statusBreakdown = $usageLogs->groupBy('log_status')
            ->map(function ($group) use ($totalItems, $statusMap) {
                $statusValue = $group->first()->log_status;
                $statusLabel = $statusMap[$statusValue] ?? 'Unknown';
                return [
                    'status' => $statusLabel,
                    'count' => $group->count(),
                    'percentage' => $totalItems > 0 ? round(($group->count() / $totalItems) * 100, 1) : 0,
                ];
            })->values();

        // Top clinics (when All Clinics selected)
        $topClinics = $usageLogs->groupBy(fn($log) => $log->patient?->clinic_id)
            ->filter()
            ->map(function ($group) use ($totalItems) {
                return [
                    'clinic_name' => $group->first()->patient?->clinic?->clinic_name ?? 'Unknown Clinic',
                    'count' => $group->count(),
                    'percentage' => $totalItems > 0 ? round(($group->count() / $totalItems) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values();

        // Brand distribution (when All Brands selected)
        $brandDistribution = $usageLogs->groupBy(fn($log) => $log->graftSize?->brand_id)
            ->filter()
            ->map(function ($group) {
                return [
                    'brand_name' => $group->first()->graftSize?->brand?->brand_name ?? 'Unknown Brand',
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();

        return [
            'summary' => [
                'total_items' => $totalItems,
                'used_items' => $usedItems,
                'in_use_items' => $inUseItems,
            ],
            'status_breakdown' => $statusBreakdown,
            'top_clinics' => $topClinics,
            'brand_distribution' => $brandDistribution,
            'inventory' => $usageLogs->map(function ($log) {
                return [
                    'serial_number' => $log->serial_number,
                    'product_name' => $log->graftSize?->size ?? 'N/A',
                    'clinic_name' => $log->patient?->clinic?->clinic_name ?? 'Unknown Clinic',
                    'status' => $this->getInventoryStatusLabel($log->log_status),
                    'created_at' => $log->logged_at?->format('Y-m-d'),
                ];
            }),
        ];
    }

    /**
     * Get usage report data
     */
    private function getUsageReport(array $dateRange, array $filters): array
    {
        $query = UsageLog::query()
            ->whereBetween('date_of_service', [$dateRange['start'], $dateRange['end']]);

        $usageLogs = $query->with(['patient.clinic', 'graftSize.brand'])->get();

        // Filter by clinic if specified
        if (!empty($filters['clinic_id']) && $filters['clinic_id'] !== 'all') {
            $usageLogs = $usageLogs->filter(function ($log) use ($filters) {
                return $log->patient?->clinic_id == $filters['clinic_id'];
            });
        }

        // Filter by brand if specified
        if (!empty($filters['brand_id']) && $filters['brand_id'] !== 'all') {
            $usageLogs = $usageLogs->filter(function ($log) use ($filters) {
                return $log->graftSize?->brand_id == $filters['brand_id'];
            });
        }

        $totalUsage = $usageLogs->count();
        $totalQuantity = $usageLogs->sum('quantity_used');

        // Top products
        $topProducts = $usageLogs->groupBy('graft_size_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'product_name' => $first->graftSize?->size ?? 'Unknown',
                    'total_quantity' => $group->sum('quantity_used'),
                    'usage_count' => $group->count(),
                ];
            })
            ->sortByDesc('total_quantity')
            ->take(10)
            ->values();

        // Top clinics (when All Clinics selected)
        $topClinics = $usageLogs->groupBy(fn($log) => $log->patient?->clinic_id)
            ->filter()
            ->map(function ($group) use ($totalUsage) {
                return [
                    'clinic_name' => $group->first()->patient?->clinic?->clinic_name ?? 'Unknown Clinic',
                    'count' => $group->count(),
                    'percentage' => $totalUsage > 0 ? round(($group->count() / $totalUsage) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values();

        // Brand distribution (when All Brands selected)
        $brandDistribution = $usageLogs->groupBy(fn($log) => $log->graftSize?->brand_id)
            ->filter()
            ->map(function ($group) {
                return [
                    'brand_name' => $group->first()->graftSize?->brand?->brand_name ?? 'Unknown Brand',
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();

        return [
            'summary' => [
                'total_usage_records' => $totalUsage,
                'total_quantity_used' => $totalQuantity,
            ],
            'top_products' => $topProducts,
            'top_clinics' => $topClinics,
            'brand_distribution' => $brandDistribution,
            'usage_logs' => $usageLogs->map(function ($log) {
                return [
                    'usage_id' => $log->graft_log_id,
                    'used_at' => $log->date_of_service?->format('Y-m-d'),
                    'clinic_name' => $log->patient?->clinic?->clinic_name ?? 'Unknown Clinic',
                    'product_name' => $log->graftSize?->size ?? 'Unknown',
                    'quantity_used' => $log->quantity_used,
                    'patient_id' => $log->patient_id,
                ];
            }),
        ];
    }

    /**
     * Get invoice report data
     */
    private function getInvoiceReport(array $dateRange, array $filters): array
    {
        $query = Invoice::query()
            ->whereBetween('invoice_date', [$dateRange['start'], $dateRange['end']]);

        if (!empty($filters['clinic_id']) && $filters['clinic_id'] !== 'all') {
            $query->where('clinic_id', $filters['clinic_id']);
        }

        $invoices = $query->with('clinic')->get();

        $totalInvoices = $invoices->count();
        $totalAmount = $invoices->sum('amount');
        // Use paid_amount column for paid amount (handles partial payments)
        $paidAmount = $invoices->sum('paid_amount') ?? 0;
        // Pending amount is total - paid
        $pendingAmount = $totalAmount - $paidAmount;

        // Status breakdown
        $statusBreakdown = $invoices->groupBy('status')
            ->map(function ($group) use ($totalInvoices) {
                return [
                    'status' => ucfirst($group->first()->status),
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                    'percentage' => $totalInvoices > 0 ? round(($group->count() / $totalInvoices) * 100, 1) : 0,
                ];
            })->values();

        // Top clinics (when All Clinics selected)
        $topClinics = $invoices->groupBy('clinic_id')
            ->map(function ($group) use ($totalAmount) {
                $clinicTotal = $group->sum('amount');
                return [
                    'clinic_name' => $group->first()->clinic->clinic_name ?? 'Unknown Clinic',
                    'count' => $group->count(),
                    'total_amount' => $clinicTotal,
                    'percentage' => $totalAmount > 0 ? round(($clinicTotal / $totalAmount) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('total_amount')
            ->take(5)
            ->values();

        // Calculate line items totals for detailed breakdown
        $allLineItems = $invoices->flatMap(function ($invoice) {
            $lineItems = is_array($invoice->line_items) ? $invoice->line_items : [];
            return collect($lineItems)->map(function ($item) use ($invoice) {
                return [
                    'invoice_number' => $invoice->invoice_number,
                    'description' => $item['description'] ?? $item['product_name'] ?? 'Unknown Item',
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? $item['price'] ?? 0,
                    'amount' => ($item['quantity'] ?? 1) * ($item['unit_price'] ?? $item['price'] ?? 0),
                ];
            });
        });

        return [
            'summary' => [
                'total_invoices' => $totalInvoices,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'pending_amount' => $pendingAmount,
            ],
            'status_breakdown' => $statusBreakdown,
            'top_clinics' => $topClinics,
            'line_items_total' => $allLineItems->sum('amount'),
            'invoices' => $invoices->map(function ($invoice) {
                $lineItems = is_array($invoice->line_items) ? $invoice->line_items : [];
                $lineItemsTotal = collect($lineItems)->sum(function ($item) {
                    return ($item['quantity'] ?? 1) * ($item['unit_price'] ?? $item['price'] ?? 0);
                });
                return [
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                    'clinic_name' => $invoice->clinic->clinic_name ?? 'Unknown',
                    'amount' => $invoice->amount,
                    'paid_amount' => $invoice->paid_amount ?? 0,
                    'status' => ucfirst($invoice->status),
                    'line_items' => collect($lineItems)->map(function ($item) {
                        return [
                            'description' => $item['description'] ?? $item['product_name'] ?? 'Unknown Item',
                            'quantity' => $item['quantity'] ?? 1,
                            'unit_price' => $item['unit_price'] ?? $item['price'] ?? 0,
                            'amount' => ($item['quantity'] ?? 1) * ($item['unit_price'] ?? $item['price'] ?? 0),
                        ];
                    })->values(),
                    'line_items_total' => $lineItemsTotal,
                ];
            }),
        ];
    }

    /**
     * Get IVR report data
     */
    private function getIVRReport(array $dateRange, array $filters): array
    {
        $query = IVR::query()
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);

        if (!empty($filters['clinic_id']) && $filters['clinic_id'] !== 'all') {
            $query->where('clinic_id', $filters['clinic_id']);
        }

        if (!empty($filters['manufacturer_id']) && $filters['manufacturer_id'] !== 'all') {
            $query->where('manufacturer_id', $filters['manufacturer_id']);
        }

        $ivrRequests = $query->with(['clinic', 'manufacturer'])->get();

        $totalRequests = $ivrRequests->count();
        $eligibleRequests = $ivrRequests->where('eligibility_status', 'eligible')->count();
        $notEligibleRequests = $ivrRequests->where('eligibility_status', 'not_eligible')->count();

        // Eligibility status breakdown
        $statusBreakdown = $ivrRequests->groupBy('eligibility_status')
            ->map(function ($group) use ($totalRequests) {
                $status = $group->first()->eligibility_status;
                return [
                    'status' => $status === 'eligible' ? 'Eligible' : 'Not Eligible',
                    'count' => $group->count(),
                    'percentage' => $totalRequests > 0 ? round(($group->count() / $totalRequests) * 100, 1) : 0,
                ];
            })->values();

        // Top clinics (when All Clinics selected)
        $topClinics = $ivrRequests->groupBy('clinic_id')
            ->map(function ($group) use ($totalRequests) {
                return [
                    'clinic_name' => $group->first()->clinic->clinic_name ?? 'Unknown',
                    'count' => $group->count(),
                    'percentage' => $totalRequests > 0 ? round(($group->count() / $totalRequests) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values();

        // Manufacturer distribution (when All Manufacturers selected)
        $manufacturerDistribution = $ivrRequests->groupBy('manufacturer_id')
            ->map(function ($group) {
                return [
                    'manufacturer_name' => $group->first()->manufacturer->manufacturer_name ?? 'Unknown',
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();

        return [
            'summary' => [
                'total_requests' => $totalRequests,
                'eligible' => $eligibleRequests,
                'not_eligible' => $notEligibleRequests,
            ],
            'status_breakdown' => $statusBreakdown,
            'top_clinics' => $topClinics,
            'manufacturer_distribution' => $manufacturerDistribution,
            'requests' => $ivrRequests->map(function ($ivr) {
                return [
                    'ivr_id' => $ivr->ivr_id,
                    'created_at' => $ivr->created_at->format('Y-m-d'),
                    'clinic_name' => $ivr->clinic->clinic_name ?? 'Unknown',
                    'manufacturer_name' => $ivr->manufacturer->manufacturer_name ?? 'Unknown',
                    'serial_number' => $ivr->serial_number,
                    'eligibility_status' => $ivr->eligibility_status === 'eligible' ? 'Eligible' : 'Not Eligible',
                ];
            }),
        ];
    }

    /**
     * Get inventory status label
     */
    private function getInventoryStatusLabel(int $status): string
    {
        $labels = [
            1 => 'Available',
            2 => 'Used',
            3 => 'Partially Used',
            4 => 'Expired',
            5 => 'Returned',
        ];

        return $labels[$status] ?? 'Unknown';
    }

    /**
     * Get report type name
     */
    private function getReportTypeName(string $type): string
    {
        $names = [
            'orders' => 'Orders Report',
            'inventory' => 'Inventory Report',
            'usage' => 'Usage Report',
            'invoice' => 'Invoice Report',
            'ivr' => 'IVR Report',
            'returns' => 'Returns Report',
        ];

        return $names[$type] ?? 'Report';
    }

    /**
     * Get filter labels for display
     */
    private function getFilterLabels(array $filters): array
    {
        $clinicName = 'All Clinics';
        if (!empty($filters['clinic_id']) && $filters['clinic_id'] !== 'all') {
            $clinic = Clinic::find($filters['clinic_id']);
            $clinicName = $clinic ? $clinic->clinic_name : 'Unknown Clinic';
        }

        // Get actual date range
        $dateRange = $this->getDateRange($filters);
        $dateRangeLabel = $dateRange['start']->format('M d, Y') . ' - ' . $dateRange['end']->format('M d, Y');

        $result = [
            'dateRange' => $dateRangeLabel,
            'clinic' => $clinicName,
        ];

        // Only include brand for reports that support it (not IVR, not Invoices)
        if ($filters['report_type'] !== 'ivr' && $filters['report_type'] !== 'invoices') {
            $brandName = 'All Brands';
            if (!empty($filters['brand_id']) && $filters['brand_id'] !== 'all') {
                $brand = Brand::find($filters['brand_id']);
                $brandName = $brand ? $brand->brand_name : 'Unknown Brand';
            }
            $result['brand'] = $brandName;
        }

        // Only include manufacturer for IVR report
        if ($filters['report_type'] === 'ivr') {
            $manufacturerName = 'All Manufacturers';
            if (!empty($filters['manufacturer_id']) && $filters['manufacturer_id'] !== 'all') {
                $manufacturer = Manufacturer::find($filters['manufacturer_id']);
                $manufacturerName = $manufacturer ? $manufacturer->manufacturer_name : 'Unknown Manufacturer';
            }
            $result['manufacturer'] = $manufacturerName;
        }

        return $result;
    }
}
