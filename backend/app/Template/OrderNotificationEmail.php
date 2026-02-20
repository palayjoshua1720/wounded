<?php

namespace App\Template;

class OrderNotificationEmail
{
    public static function getTemplate(array $data): string
    {
        $escape = fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

        $orderCode        = $escape($data['order_code'] ?? '');
        $trackingNumber   = $escape($data['tracking_number'] ?? '');
        $clinic           = $escape($data['clinic_name'] ?? '');
        $clinician        = $escape($data['clinician_name'] ?? '');
        $patient          = $escape($data['patient_name'] ?? '');
        $manufacturerName = $escape($data['manufacturer_name'] ?? '');
        $orderLink        = $escape($data['order_link'] ?? '');

        /*
        |--------------------------------------------------------------------------
        | GRAFT ITEMS
        |--------------------------------------------------------------------------
        */
        $itemsHtml = '';
        foreach ($data['items'] ?? [] as $item) {
            $brandName = $escape($item['brand_name'] ?? '—');
            $sizeName  = $escape($item['size_name'] ?? '—');
            $quantity  = (int) ($item['quantity'] ?? 0);
            $subtotal  = number_format((float) ($item['subtotal'] ?? 0), 2);

            $itemsHtml .= "
            <tr>
                <td style='padding:8px;border:1px solid #e2e8f0;'>{$brandName}</td>
                <td style='padding:8px;border:1px solid #e2e8f0;'>{$sizeName}</td>
                <td style='padding:8px;border:1px solid #e2e8f0;text-align:center;'>{$quantity}</td>
                <td style='padding:8px;border:1px solid #e2e8f0;text-align:right;'>\${$subtotal}</td>
            </tr>";
        }

        $graftSubtotal = number_format((float) ($data['graft_subtotal'] ?? 0), 2);

        /*
        |--------------------------------------------------------------------------
        | ADDITIONAL PRODUCTS
        |--------------------------------------------------------------------------
        */
        $otherItemsHtml = '';
        $hasOtherItems  = !empty($data['other_product_items']);

        if ($hasOtherItems) {
            foreach ($data['other_product_items'] as $item) {
                $productName = $escape($item['product_name'] ?? '—');
                $quantity    = (int) ($item['quantity'] ?? 0);
                $price       = number_format((float) ($item['price'] ?? 0), 2);
                $subtotal    = number_format((float) ($item['subtotal'] ?? 0), 2);

                $otherItemsHtml .= "
                <tr>
                    <td style='padding:8px;border:1px solid #e2e8f0;'>{$productName}</td>
                    <td style='padding:8px;border:1px solid #e2e8f0;text-align:center;'>{$quantity}</td>
                    <td style='padding:8px;border:1px solid #e2e8f0;text-align:right;'>\${$price}</td>
                    <td style='padding:8px;border:1px solid #e2e8f0;text-align:right;'>\${$subtotal}</td>
                </tr>";
            }
        }

        $otherSubtotal = number_format((float) ($data['other_subtotal'] ?? 0), 2);
        $totalAsp      = number_format((float) ($data['total_asp'] ?? 0), 2);
        $year          = date('Y');

        /*
        |--------------------------------------------------------------------------
        | BUILD EMAIL BODY
        |--------------------------------------------------------------------------
        */

        $html = "
            <body style='font-family:Arial,sans-serif;background:#f8fafc;margin:0;padding:0;'>
            <div style='max-width:600px;margin:40px auto;background:#fff;padding:30px;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.1);'>

            <h2 style='color:#1e293b;font-size:20px;margin-bottom:8px;'>New Order Notification</h2>
            <p style='color:#475569;font-size:14px;margin:0 0 16px 0;'>
            <strong>Manufacturer:</strong> {$manufacturerName}
            </p>

            <p style='color:#475569;font-size:14px;line-height:1.6;'>
            Hello,<br>
            A new order has been created in the WoundMed System.
            </p>

            <p style='color:#0f172a;font-size:14px;margin:16px 0;'>
            <strong>Order Code:</strong> {$orderCode}<br>
            <strong>Tracking Number:</strong> {$trackingNumber}<br>
            <strong>Ordering Clinic:</strong> {$clinic}<br>
            <strong>Ordering Clinician:</strong> {$clinician}<br>
            <strong>Patient Name:</strong> {$patient}
            </p>

            <h3 style='margin-top:20px;'>Order Items (Grafts)</h3>

            <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;margin-top:10px;'>
            <thead>
            <tr>
            <th style='text-align:left;padding:8px;background:#f1f5f9;border:1px solid #e2e8f0;'>Brand</th>
            <th style='text-align:left;padding:8px;background:#f1f5f9;border:1px solid #e2e8f0;'>Graft Size</th>
            <th style='text-align:center;padding:8px;background:#f1f5f9;border:1px solid #e2e8f0;'>Qty</th>
            <th style='text-align:right;padding:8px;background:#f1f5f9;border:1px solid #e2e8f0;'>Subtotal</th>
            </tr>
            </thead>
            <tbody>
            {$itemsHtml}
            </tbody>
            <tfoot>
            <tr>
            <td colspan='3' style='padding:10px;text-align:right;background:#f8fafc;border:1px solid #e2e8f0;font-weight:bold;'>Graft Subtotal:</td>
            <td style='padding:10px;background:#f8fafc;border:1px solid #e2e8f0;font-weight:bold;text-align:right;'>\${$graftSubtotal}</td>
            </tr>
            </tfoot>
            </table>
        ";

        if ($hasOtherItems) {
            $html .= "
            <h3 style='margin-top:32px;margin-bottom:12px;'>Additional Products</h3>

            <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;'>
            <thead>
            <tr>
            <th style='text-align:left;padding:8px;background:#f1f5f9;border:1px solid #e2e8f0;'>Product</th>
            <th style='text-align:center;padding:8px;background:#f1f5f9;border:1px solid #e2e8f0;'>Qty</th>
            <th style='text-align:right;padding:8px;background:#f1f5f9;border:1px solid #e2e8f0;'>Unit Price</th>
            <th style='text-align:right;padding:8px;background:#f1f5f9;border:1px solid #e2e8f0;'>Subtotal</th>
            </tr>
            </thead>
            <tbody>
            {$otherItemsHtml}
            </tbody>
            <tfoot>
            <tr>
            <td colspan='3' style='padding:10px;text-align:right;background:#f8fafc;border:1px solid #e2e8f0;font-weight:bold;'>Additional Subtotal:</td>
            <td style='padding:10px;background:#f8fafc;border:1px solid #e2e8f0;font-weight:bold;text-align:right;'>\${$otherSubtotal}</td>
            </tr>
            </tfoot>
            </table>";
        }

        $html .= "
            <div style='margin-top:24px;padding:16px;background:#eff6ff;border-radius:8px;text-align:right;'>
            <strong style='font-size:18px;color:#1e40af;'>
            Total Order Amount: \${$totalAsp}
            </strong>
            </div>

            <p style='color:#475569;font-size:13px;margin-top:20px;line-height:1.6;background:#f1f5f9;padding:10px 12px;border-radius:6px;border-left:4px solid #2563eb;'>
            <strong>Note:</strong> Please review this order and update the status accordingly (Acknowledged, Shipped, or Delivered) in the WoundMed system.
            </p>

            <a href='{$orderLink}' style='display:inline-block;padding:12px 20px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;margin-top:20px;'>
            View Order Details
            </a>

            <p style='font-size:12px;color:#94a3b8;margin-top:30px;text-align:center;'>
            This is an automated notification from the WoundMed System.<br>
            © {$year} WOUNDMED INC.
            </p>

            </div>
            </body>
        ";

        return $html;
    }

}
