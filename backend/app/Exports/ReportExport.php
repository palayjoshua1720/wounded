<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $reportType;
    protected $filters;
    protected $data;

    public function __construct(string $reportType, array $filters, array $data)
    {
        $this->reportType = $reportType;
        $this->filters = $filters;
        $this->data = $data;
    }

    public function array(): array
    {
        $exportData = [];

        // Add report metadata
        $exportData[] = ['WoundMed Inc. - ' . $this->getReportTitle()];
        $exportData[] = ['Generated: ' . now()->format('Y-m-d H:i:s')];
        $exportData[] = [''];
        $exportData[] = ['Filters Applied:'];
        $exportData[] = ['Date Range:', $this->filters['dateRange']];
        $exportData[] = ['Clinic:', $this->filters['clinic']];
        if (isset($this->filters['brand'])) {
            $exportData[] = ['Brand:', $this->filters['brand']];
        }
        if (isset($this->filters['manufacturer'])) {
            $exportData[] = ['Manufacturer:', $this->filters['manufacturer']];
        }
        $exportData[] = [''];

        // Add summary section
        if (isset($this->data['summary'])) {
            $exportData[] = ['Summary:'];
            foreach ($this->data['summary'] as $key => $value) {
                $exportData[] = [ucwords(str_replace('_', ' ', $key)), $value];
            }
            $exportData[] = [''];
        }

        // Add main data based on report type
        $exportData = array_merge($exportData, $this->getMainData());

        return $exportData;
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return $this->getReportTitle();
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A4:A8')->getFont()->setBold(true);

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    private function getReportTitle(): string
    {
        $titles = [
            'orders' => 'Orders Report',
            'inventory' => 'Inventory Report',
            'usage' => 'Usage Report',
            'invoice' => 'Invoice Report',
            'ivr' => 'IVR Report',
            'returns' => 'Returns Report',
        ];

        return $titles[$this->reportType] ?? 'Report';
    }

    private function getMainData(): array
    {
        switch ($this->reportType) {
            case 'orders':
                return $this->getOrdersData();
            case 'inventory':
                return $this->getInventoryData();
            case 'usage':
                return $this->getUsageData();
            case 'invoice':
                return $this->getInvoiceData();
            case 'ivr':
                return $this->getIVRData();
            default:
                return [];
        }
    }

    private function getOrdersData(): array
    {
        $data = [];

        // Order Status Breakdown (always shown)
        if (isset($this->data['status_breakdown']) && count($this->data['status_breakdown']) > 0) {
            $data[] = ['Order Status Breakdown:'];
            $data[] = ['Status', 'Count', 'Percentage'];
            foreach ($this->data['status_breakdown'] as $status) {
                $data[] = [
                    $status['status'],
                    $status['count'],
                    $status['percentage'] . '%',
                ];
            }
            $data[] = [''];
        }

        // Top Clinics (shown when "All Clinics" selected)
        if ($this->filters['clinic'] === 'All Clinics' && isset($this->data['top_clinics']) && count($this->data['top_clinics']) > 0) {
            $data[] = ['Top Clinics by Orders:'];
            $data[] = ['Clinic', 'Order Count', 'Percentage'];
            foreach ($this->data['top_clinics'] as $clinic) {
                $data[] = [
                    $clinic['clinic_name'],
                    $clinic['count'],
                    $clinic['percentage'] . '%',
                ];
            }
            $data[] = [''];
        }

        // Brand Distribution (shown when "All Brands" selected)
        if (isset($this->filters['brand']) && $this->filters['brand'] === 'All Brands' && isset($this->data['brand_distribution']) && count($this->data['brand_distribution']) > 0) {
            $data[] = ['Brand Distribution:'];
            $data[] = ['Brand', 'Item Count'];
            foreach ($this->data['brand_distribution'] as $brand) {
                $data[] = [
                    $brand['brand_name'],
                    $brand['count'],
                ];
            }
            $data[] = [''];
        }

        // Top Products (shown when specific clinic selected)
        if ($this->filters['clinic'] !== 'All Clinics' && isset($this->data['top_products']) && count($this->data['top_products']) > 0) {
            $data[] = ['Top Products for ' . $this->filters['clinic'] . ':'];
            $data[] = ['Product', 'Count'];
            foreach ($this->data['top_products'] as $product) {
                $data[] = [
                    $product['product_name'],
                    $product['count'],
                ];
            }
            $data[] = [''];
        }

        // Size Distribution (shown when specific brand selected)
        if (isset($this->filters['brand']) && $this->filters['brand'] !== 'All Brands' && isset($this->data['size_distribution']) && count($this->data['size_distribution']) > 0) {
            $data[] = ['Size Distribution for ' . $this->filters['brand'] . ':'];
            $data[] = ['Size', 'Count'];
            foreach ($this->data['size_distribution'] as $size) {
                $data[] = [
                    $size['size_name'],
                    $size['count'],
                ];
            }
            $data[] = [''];
        }

        // Orders List section
        $data[] = ['Orders List:'];
        $data[] = ['Order Code', 'Date', 'Clinic', 'Items', 'Total Amount'];

        if (isset($this->data['orders'])) {
            foreach ($this->data['orders'] as $order) {
                $data[] = [
                    $order['order_code'],
                    $order['ordered_at'],
                    $order['clinic_name'],
                    $order['items_count'],
                    $order['total_amount'],
                ];
            }
        }

        return $data;
    }

    private function getInventoryData(): array
    {
        $data = [['Inventory List:']];
        $data[] = ['Serial Number', 'Product', 'Clinic', 'Status', 'Date Added'];

        if (isset($this->data['inventory'])) {
            foreach ($this->data['inventory'] as $item) {
                $data[] = [
                    $item['serial_number'],
                    $item['product_name'],
                    $item['clinic_name'],
                    $item['status'],
                    $item['created_at'],
                ];
            }
        }

        return $data;
    }

    private function getUsageData(): array
    {
        $data = [['Usage Logs:']];
        $data[] = ['Usage ID', 'Date', 'Clinic', 'Product', 'Quantity', 'Patient ID'];

        if (isset($this->data['usage_logs'])) {
            foreach ($this->data['usage_logs'] as $log) {
                $data[] = [
                    $log['usage_id'],
                    $log['used_at'],
                    $log['clinic_name'],
                    $log['product_name'],
                    $log['quantity_used'],
                    $log['patient_id'],
                ];
            }
        }

        return $data;
    }

    private function getInvoiceData(): array
    {
        $data = [['Invoices:']];
        $data[] = ['Invoice Number', 'Date', 'Clinic', 'Total Amount', 'Paid Amount', 'Status'];

        if (isset($this->data['invoices'])) {
            foreach ($this->data['invoices'] as $invoice) {
                $data[] = [
                    $invoice['invoice_number'],
                    $invoice['invoice_date'],
                    $invoice['clinic_name'],
                    $invoice['amount'],
                    $invoice['paid_amount'] ?? 0,
                    $invoice['status'],
                ];
                
                // Add line items if present
                if (isset($invoice['line_items']) && count($invoice['line_items']) > 0) {
                    $data[] = ['', 'Line Items:', '', '', '', ''];
                    $data[] = ['', 'Description', 'Quantity', 'Unit Price', 'Amount', ''];
                    foreach ($invoice['line_items'] as $item) {
                        $data[] = [
                            '',
                            $item['description'],
                            $item['quantity'],
                            $item['unit_price'],
                            $item['amount'],
                            '',
                        ];
                    }
                    $data[] = ['', '', '', 'Line Items Total:', $invoice['line_items_total'] ?? 0, ''];
                    $data[] = ['', '', '', '', '', '']; // Empty row for spacing
                }
            }
        }

        return $data;
    }

    private function getIVRData(): array
    {
        $data = [['IVR Requests:']];
        $data[] = ['IVR ID', 'Date', 'Clinic', 'Manufacturer', 'Status'];

        if (isset($this->data['requests'])) {
            foreach ($this->data['requests'] as $ivr) {
                $data[] = [
                    $ivr['ivr_id'],
                    $ivr['created_at'],
                    $ivr['clinic_name'],
                    $ivr['manufacturer_name'],
                    $ivr['eligibility_status'],
                ];
            }
        }

        return $data;
    }
}
