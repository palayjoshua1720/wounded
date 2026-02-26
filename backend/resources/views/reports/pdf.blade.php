<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportType }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            background: #2563eb;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 12px;
            opacity: 0.9;
        }
        .content {
            padding: 0 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .filters {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .filters table {
            width: 100%;
        }
        .filters td {
            padding: 3px 0;
        }
        .filters td:first-child {
            font-weight: bold;
            width: 120px;
        }
        .summary {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .summary-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            padding: 15px;
            flex: 1;
            text-align: center;
        }
        .summary-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        .summary-card .label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background: #f3f4f6;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #e5e7eb;
        }
        table.data-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        table.data-table tr:nth-child(even) {
            background: #f9fafb;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 20px;
            background: #f3f4f6;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-submitted { background: #fef3c7; color: #92400e; }
        .status-acknowledged { background: #dbeafe; color: #1e40af; }
        .status-shipped { background: #e0e7ff; color: #3730a3; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-available { background: #d1fae5; color: #065f46; }
        .status-used { background: #dbeafe; color: #1e40af; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-eligible { background: #d1fae5; color: #065f46; }
        .status-not_eligible { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>WoundMed Inc.</h1>
        <p>Medical Device Management System</p>
    </div>

    <div class="content">
        <div class="section">
            <h2 style="font-size: 20px; margin-bottom: 5px;">{{ $reportType }}</h2>
            <p style="color: #6b7280;">{{ $dateRange }}</p>
        </div>

        <div class="section">
            <div class="section-title">Filters Applied</div>
            <div class="filters">
                <table>
                    <tr>
                        <td>Date Range:</td>
                        <td>{{ $filters['dateRange'] }}</td>
                    </tr>
                    <tr>
                        <td>Clinic:</td>
                        <td>{{ $filters['clinic'] }}</td>
                    </tr>
                    @if(isset($filters['brand']))
                    <tr>
                        <td>Brand:</td>
                        <td>{{ $filters['brand'] }}</td>
                    </tr>
                    @endif
                    @if(isset($filters['manufacturer']))
                    <tr>
                        <td>Manufacturer:</td>
                        <td>{{ $filters['manufacturer'] }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        @if(isset($data['summary']))
        <div class="section">
            <div class="section-title">Summary</div>
            <div class="summary">
                @foreach($data['summary'] as $key => $value)
                <div class="summary-card">
                    <div class="value">{{ is_numeric($value) ? number_format($value) : $value }}</div>
                    <div class="label">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(isset($data['status_breakdown']) && count($data['status_breakdown']) > 0)
        <div class="section">
            <div class="section-title">Order Status Breakdown</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['status_breakdown'] as $status)
                    <tr>
                        <td><span class="status-badge status-{{ $status['status'] }}">{{ ucfirst($status['status']) }}</span></td>
                        <td>{{ $status['count'] }}</td>
                        <td>{{ $status['percentage'] }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($filters['clinic'] === 'All Clinics' && isset($data['top_clinics']) && count($data['top_clinics']) > 0)
        <div class="section">
            <div class="section-title">Top Clinics by Orders</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Clinic</th>
                        <th>Order Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['top_clinics'] as $clinic)
                    <tr>
                        <td>{{ $clinic['clinic_name'] }}</td>
                        <td>{{ $clinic['count'] }}</td>
                        <td>{{ $clinic['percentage'] }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(isset($filters['brand']) && $filters['brand'] === 'All Brands' && isset($data['brand_distribution']) && count($data['brand_distribution']) > 0)
        <div class="section">
            <div class="section-title">Brand Distribution</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Brand</th>
                        <th>Item Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['brand_distribution'] as $brand)
                    <tr>
                        <td>{{ $brand['brand_name'] }}</td>
                        <td>{{ $brand['count'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($filters['clinic'] !== 'All Clinics' && isset($data['top_products']) && count($data['top_products']) > 0)
        <div class="section">
            <div class="section-title">Top Products for {{ $filters['clinic'] }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['top_products'] as $product)
                    <tr>
                        <td>{{ $product['product_name'] }}</td>
                        <td>{{ $product['count'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(isset($filters['brand']) && $filters['brand'] !== 'All Brands' && isset($data['size_distribution']) && count($data['size_distribution']) > 0)
        <div class="section">
            <div class="section-title">Size Distribution for {{ $filters['brand'] }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Size</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['size_distribution'] as $size)
                    <tr>
                        <td>{{ $size['size_name'] }}</td>
                        <td>{{ $size['count'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(isset($data['orders']))
        <div class="section">
            <div class="section-title">Orders</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order Code</th>
                        <th>Date</th>
                        <th>Clinic</th>
                        <th>Items</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['orders'] as $order)
                    <tr>
                        <td>{{ $order['order_code'] }}</td>
                        <td>{{ $order['ordered_at'] }}</td>
                        <td>{{ $order['clinic_name'] }}</td>
                        <td>{{ $order['items_count'] }}</td>
                        <td>${{ number_format($order['total_amount'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(isset($data['inventory']))
        <div class="section">
            <div class="section-title">Inventory</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Serial Number</th>
                        <th>Product</th>
                        <th>Clinic</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['inventory'] as $item)
                    <tr>
                        <td>{{ $item['serial_number'] }}</td>
                        <td>{{ $item['product_name'] }}</td>
                        <td>{{ $item['clinic_name'] }}</td>
                        <td><span class="status-badge status-{{ strtolower(str_replace(' ', '_', $item['status'])) }}">{{ $item['status'] }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(isset($data['usage_logs']))
        <div class="section">
            <div class="section-title">Usage Logs</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Usage ID</th>
                        <th>Date</th>
                        <th>Clinic</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Patient ID</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['usage_logs'] as $log)
                    <tr>
                        <td>{{ $log['usage_id'] }}</td>
                        <td>{{ $log['used_at'] }}</td>
                        <td>{{ $log['clinic_name'] }}</td>
                        <td>{{ $log['product_name'] }}</td>
                        <td>{{ $log['quantity_used'] }}</td>
                        <td>{{ $log['patient_id'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(isset($data['invoices']))
        <div class="section">
            <div class="section-title">Invoices</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice Number</th>
                        <th>Date</th>
                        <th>Clinic</th>
                        <th>Total Amount</th>
                        <th>Paid Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['invoices'] as $invoice)
                    <tr>
                        <td>{{ $invoice['invoice_number'] }}</td>
                        <td>{{ $invoice['invoice_date'] }}</td>
                        <td>{{ $invoice['clinic_name'] }}</td>
                        <td>${{ number_format($invoice['amount'], 2) }}</td>
                        <td>${{ number_format($invoice['paid_amount'] ?? 0, 2) }}</td>
                        <td><span class="status-badge status-{{ $invoice['status'] }}">{{ ucfirst($invoice['status']) }}</span></td>
                    </tr>
                    @if(isset($invoice['line_items']) && count($invoice['line_items']) > 0)
                    <tr style="background-color: #f3f4f6;">
                        <td colspan="6" style="padding: 4px 12px; font-size: 10px; font-weight: bold; color: #374151;">
                            Items:
                        </td>
                    </tr>
                    @foreach($invoice['line_items'] as $item)
                    <tr style="background-color: #f9fafb;">
                        <td colspan="2" style="padding: 2px 12px; font-size: 10px; padding-left: 30px;">{{ $item['description'] }}</td>
                        <td style="padding: 2px 12px; font-size: 10px; text-align: center;">{{ $item['quantity'] }}</td>
                        <td style="padding: 2px 12px; font-size: 10px; text-align: right;">${{ number_format($item['unit_price'], 2) }}</td>
                        <td style="padding: 2px 12px; font-size: 10px; text-align: right;">${{ number_format($item['amount'], 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(isset($data['requests']))
        <div class="section">
            <div class="section-title">IVR Requests</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>IVR ID</th>
                        <th>Date</th>
                        <th>Clinic</th>
                        <th>Manufacturer</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['requests'] as $ivr)
                    <tr>
                        <td>{{ $ivr['ivr_id'] }}</td>
                        <td>{{ $ivr['created_at'] }}</td>
                        <td>{{ $ivr['clinic_name'] }}</td>
                        <td>{{ $ivr['manufacturer_name'] }}</td>
                        <td><span class="status-badge status-{{ $ivr['eligibility_status'] }}">{{ ucwords(str_replace('_', ' ', $ivr['eligibility_status'])) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="footer">
        Generated on {{ $generatedAt }} | WoundMed Inc. Report Center
    </div>
</body>
</html>
