<?php

namespace App\Template;

class OtherProductOrderNotificationEmail
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
                $productType = $escape($item['product_type'] ?? '—');
                $quantity    = (int) ($item['quantity'] ?? 0);
                $price       = number_format((float) ($item['price'] ?? 0), 2);
                $subtotal    = number_format((float) ($item['subtotal'] ?? 0), 2);

                $otherItemsHtml .= "
                <tr style='border-bottom:1px solid #f1f5f9;'>
                    <td style='padding:10px 0;color:#0f172a;font-weight:500;'>{$productName}</td>
                    <td style='padding:10px 0;text-align:center;color:#0f172a;'>{$quantity}</td>
                    <td style='padding:10px 0;color:#475569;'>{$productType}</td>
                    <td style='padding:10px 0;text-align:right;color:#0f172a;'>\${$price}</td>
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
            <body style='font-family:Arial,sans-serif;background:#f1f5f9;margin:0;padding:0;'>
            <div style='max-width:580px;margin:40px auto;background:#fff;border-radius:10px;border:1px solid #e2e8f0;overflow:hidden;'>

            <!-- Header -->
            <div style='padding:22px 28px 18px;border-bottom:1px solid #e2e8f0;'>
                <p style='font-size:12px;letter-spacing:0.08em;color:#94a3b8;margin:0 0 4px;text-transform:uppercase;'>Woundmed Inc.</p>
                <h2 style='font-size:18px;font-weight:600;color:#0f172a;margin:0;'>New Other Product Order</h2>
            </div>

            <!-- Body -->
            <div style='padding:20px 28px 0;'>
                <p style='font-size:14px;color:#334155;margin:0 0 18px;line-height:1.6;'>
                Hello <strong style='color:#0f172a;'>{$manufacturerName}</strong>,<br>
                A new other product order has been placed in the system.
                </p>

                <!-- Info Cards -->
                <table width='100%' cellpadding='0' cellspacing='8' style='margin-bottom:20px;'>
                <tr>
                    <td width='50%' style='background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;padding:10px 14px;vertical-align:top;'>
                    <p style='font-size:12px;color:#94a3b8;margin:0 0 3px;letter-spacing:0.04em;'>Order code</p>
                    <p style='font-size:14px;font-weight:600;color:#0f172a;margin:0;letter-spacing:0.02em;'>{$orderCode}</p>
                    </td>
                    <td width='50%' style='background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;padding:10px 14px;vertical-align:top;'>
                    <p style='font-size:12px;color:#94a3b8;margin:0 0 3px;letter-spacing:0.04em;'>Tracking number</p>
                    <p style='font-size:14px;font-weight:600;color:#0f172a;margin:0;letter-spacing:0.02em;'>{$trackingNumber}</p>
                    </td>
                </tr>
                <tr>
                    <td style='background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;padding:10px 14px;vertical-align:top;'>
                    <p style='font-size:12px;color:#94a3b8;margin:0 0 3px;letter-spacing:0.04em;'>Ordering clinic</p>
                    <p style='font-size:14px;font-weight:600;color:#0f172a;margin:0;'>{$clinic}</p>
                    </td>
                    <td style='background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;padding:10px 14px;vertical-align:top;'>
                    <p style='font-size:12px;color:#94a3b8;margin:0 0 3px;letter-spacing:0.04em;'>Clinician</p>
                    <p style='font-size:14px;font-weight:600;color:#0f172a;margin:0;'>{$clinician}</p>
                    </td>
                </tr>
                <tr>
                    <td colspan='2' style='background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;padding:10px 14px;'>
                    <p style='font-size:12px;color:#94a3b8;margin:0 0 3px;letter-spacing:0.04em;'>Patient name</p>
                    <p style='font-size:14px;font-weight:600;color:#0f172a;margin:0;'>{$patient}</p>
                    </td>
                </tr>
                </table>

                <!-- Other Products Table -->
                <p style='font-size:12px;font-weight:700;color:#64748b;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.08em;'>Other products</p>
                <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;font-size:13px;margin-bottom:24px;'>
                <thead>
                    <tr style='border-bottom:1px solid #e2e8f0;'>
                    <th style='text-align:left;padding:8px 0;color:#94a3b8;font-weight:500;font-size:13px;width:38%;'>Product</th>
                    <th style='text-align:center;padding:8px 0;color:#94a3b8;font-weight:500;font-size:13px;width:14%;'>Qty</th>
                    <th style='text-align:left;padding:8px 0;color:#94a3b8;font-weight:500;font-size:13px;width:28%;'>Product type</th>
                    <th style='text-align:right;padding:8px 0;color:#94a3b8;font-weight:500;font-size:13px;width:20%;'>Price</th>
                    </tr>
                </thead>
                <tbody>
                    {$otherItemsHtml}
                </tbody>
                <tfoot>
                    <tr>
                    <td colspan='3' style='padding:12px 0 4px;text-align:right;color:#64748b;font-size:13px;font-weight:500;'>Total</td>
                    <td style='padding:12px 0 4px;text-align:right;font-size:16px;font-weight:700;color:#0f172a;'>\${$otherSubtotal}</td>
                    </tr>
                </tfoot>
                </table>
            </div>

            <!-- Footer -->
            <div style='padding:14px 28px;border-top:1px solid #e2e8f0;background:#fff;'>
                <p style='font-size:11px;color:#94a3b8;margin:0;text-align:center;'>
                This is an automated notification from the WOUNDMED INC. system.
                <span>
                &copy; {$year} WOUNDMED INC.
                </span>
                </p>
            </div>

            </div>
            </body>
        ";

        return $html;
    }

}
