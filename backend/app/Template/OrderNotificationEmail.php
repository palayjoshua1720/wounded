<?php

namespace App\Template;

class OrderNotificationEmail
{
    public static function getTemplate(array $data): string
    {
        $orderCode          = htmlspecialchars($data['order_code'], ENT_QUOTES, 'UTF-8');
        $trackingNumber     = htmlspecialchars($data['tracking_number'], ENT_QUOTES, 'UTF-8');
        $clinic             = htmlspecialchars($data['clinic_name'], ENT_QUOTES, 'UTF-8');
        $clinician          = htmlspecialchars($data['clinician_name'], ENT_QUOTES, 'UTF-8');
        $patient            = htmlspecialchars($data['patient_name'], ENT_QUOTES, 'UTF-8');
        $manufacturerName   = htmlspecialchars($data['manufacturer_name'], ENT_QUOTES, 'UTF-8');
        $orderLink          = htmlspecialchars($data['order_link'], ENT_QUOTES, 'UTF-8');

        $itemsHtml = '';
        foreach ($data['items'] as $item) {
            $itemsHtml .= '
                <tr>
                    <td style="padding: 8px; border: 1px solid #e2e8f0;">' . htmlspecialchars($item['brand_name']) . '</td>
                    <td style="padding: 8px; border: 1px solid #e2e8f0;">' . htmlspecialchars($item['size_name']) . '</td>
                    <td style="padding: 8px; border: 1px solid #e2e8f0;">' . htmlspecialchars($item['quantity']) . '</td>
                    <td style="padding: 8px; border: 1px solid #e2e8f0;">$' . number_format($item['subtotal'], 2) . '</td>
                </tr>
            ';
        }

        return '
            <body style="font-family: Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 0;">
                <div style="max-width: 600px; margin: 40px auto; background: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">

                    <h2 style="color: #1e293b; font-size: 20px; margin-bottom: 8px;">
                        New Order Notification
                    </h2>
                    <p style="color: #475569; font-size: 14px; margin: 0 0 16px 0;">
                        <strong>Manufacturer:</strong> ' . $manufacturerName . '
                    </p>

                    <p style="color: #475569; font-size: 14px; line-height: 1.6;">
                        Hello,<br>
                        A new order has been created in the WoundMed System.
                    </p>

                    <p style="color: #0f172a; font-size: 14px; margin: 16px 0;">
                        <strong>Order Code:</strong> ' . $orderCode . '<br>
                        <strong>Tracking Number:</strong> ' . $trackingNumber . '<br>
                        <strong>Ordering Clinic:</strong> ' . $clinic . '<br>
                        <strong>Ordering Clinician:</strong> ' . $clinician . '<br>
                        <strong>Patient Name:</strong> ' . $patient . '
                    </p>

                    <h3 style="color: #1e293b; margin-top: 20px;">Order Items</h3>

                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin-top: 10px;">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding: 8px; background:#f1f5f9; border:1px solid #e2e8f0;">Brand</th>
                                <th style="text-align:left; padding: 8px; background:#f1f5f9; border:1px solid #e2e8f0;">Graft Size</th>
                                <th style="text-align:left; padding: 8px; background:#f1f5f9; border:1px solid #e2e8f0;">Qty</th>
                                <th style="text-align:left; padding: 8px; background:#f1f5f9; border:1px solid #e2e8f0;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ' . $itemsHtml . '
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="padding: 10px; text-align: right; background:#f8fafc; border:1px solid #e2e8f0; font-weight:bold;">
                                    Total Order Amount:
                                </td>
                                <td style="padding: 10px; background:#f8fafc; border:1px solid #e2e8f0; font-weight:bold;">
                                    $' . number_format($data['total_asp'], 2) . '
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <p style="color: #475569; font-size: 13px; margin-top: 20px; line-height: 1.6; background:#f1f5f9; padding:10px 12px; border-radius:6px; border-left:4px solid #2563eb;">
                        <strong>Note:</strong> Please review this order and update the order status accordingly (Acknowledged, Shipped, or Delivered) in the WoundMed system.
                    </p>

                    <a href="' . $orderLink . '" 
                    style="display: inline-block; padding: 12px 20px; background: #2563eb; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">
                        View Order Details
                    </a>

                    <p style="font-size: 12px; color: #94a3b8; margin-top: 30px; text-align: center;">
                        This is an automated notification from the WoundMed System.<br>
                        &copy; ' . date('Y') . ' WOUNDMED INC.
                    </p>

                </div>
            </body>
        ';
    }
}
