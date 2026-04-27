<?php

namespace App\Template;

class FollowupOrderNotificationEmail
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
        
        $year = date('Y');

        $itemsHtml = '';
        foreach ($data['items'] as $item) {
            $itemsHtml .= '
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:10px 0;color:#0f172a;font-weight:500;">' . htmlspecialchars($item['brand_name']) . '</td>
                    <td style="padding:10px 0;color:#475569;">' . htmlspecialchars($item['size_name']) . '</td>
                    <td style="padding:10px 0;text-align:center;color:#0f172a;">' . htmlspecialchars($item['quantity']) . '</td>
                    <td style="padding:10px 0;text-align:right;color:#0f172a;">$' . number_format($item['subtotal'], 2) . '</td>
                </tr>
            ';
        }

        return '
            <body style="font-family:Arial,sans-serif;background:#f1f5f9;margin:0;padding:0;">
            <div style="max-width:580px;margin:40px auto;background:#fff;border-radius:10px;border:1px solid #e2e8f0;overflow:hidden;">

            <!-- Header -->
            <div style="padding:22px 28px 18px;border-bottom:1px solid #e2e8f0;">
                <p style="font-size:12px;letter-spacing:0.08em;color:#94a3b8;margin:0 0 4px;text-transform:uppercase;">Woundmed Inc.</p>
                <h2 style="font-size:18px;font-weight:600;color:#0f172a;margin:0;">
                Follow-up: Action needed
                &nbsp;<span style="font-size:11px;font-weight:600;background:#fef3c7;color:#92400e;padding:3px 8px;border-radius:20px;letter-spacing:0.04em;">REMINDER</span>
                </h2>
            </div>

            <!-- Body -->
            <div style="padding:20px 28px 0;">
                <p style="font-size:14px;color:#334155;margin:0 0 18px;line-height:1.6;">
                Hello <strong style="color:#0f172a;">' . $manufacturerName . '</strong>,<br>
                This is a follow-up reminder for an order previously submitted in the system. Please review and take the necessary action as soon as possible.
                </p>

                <!-- Info Cards -->
                <table width="100%" cellpadding="0" cellspacing="8" style="margin-bottom:20px;">
                <tr>
                    <td width="50%" style="background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;padding:10px 14px;vertical-align:top;">
                    <p style="font-size:12px;color:#94a3b8;margin:0 0 3px;letter-spacing:0.04em;">Order code</p>
                    <p style="font-size:14px;font-weight:600;color:#0f172a;margin:0;letter-spacing:0.02em;">' . $orderCode . '</p>
                    </td>
                    <td width="50%" style="background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;padding:10px 14px;vertical-align:top;">
                    <p style="font-size:12px;color:#94a3b8;margin:0 0 3px;letter-spacing:0.04em;">Tracking number</p>
                    <p style="font-size:14px;font-weight:600;color:#0f172a;margin:0;letter-spacing:0.02em;">' . $trackingNumber . '</p>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;padding:10px 14px;vertical-align:top;">
                    <p style="font-size:12px;color:#94a3b8;margin:0 0 3px;letter-spacing:0.04em;">Ordering clinic</p>
                    <p style="font-size:14px;font-weight:600;color:#0f172a;margin:0;">' . $clinic . '</p>
                    </td>
                    <td style="background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;padding:10px 14px;vertical-align:top;">
                    <p style="font-size:12px;color:#94a3b8;margin:0 0 3px;letter-spacing:0.04em;">Clinician</p>
                    <p style="font-size:14px;font-weight:600;color:#0f172a;margin:0;">' . $clinician . '</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;padding:10px 14px;">
                    <p style="font-size:12px;color:#94a3b8;margin:0 0 3px;letter-spacing:0.04em;">Patient name</p>
                    <p style="font-size:14px;font-weight:600;color:#0f172a;margin:0;">' . $patient . '</p>
                    </td>
                </tr>
                </table>

                <!-- Order Items -->
                <p style="font-size:12px;font-weight:700;color:#64748b;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.08em;">Order items (grafts)</p>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:13px;margin-bottom:20px;">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0;">
                    <th style="text-align:left;padding:8px 0;color:#94a3b8;font-weight:500;font-size:13px;width:38%;">Brand</th>
                    <th style="text-align:left;padding:8px 0;color:#94a3b8;font-weight:500;font-size:13px;width:28%;">Graft size</th>
                    <th style="text-align:center;padding:8px 0;color:#94a3b8;font-weight:500;font-size:13px;width:14%;">Qty</th>
                    <th style="text-align:right;padding:8px 0;color:#94a3b8;font-weight:500;font-size:13px;width:20%;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    ' . $itemsHtml . '
                </tbody>
                <tfoot>
                    <tr>
                    <td colspan="3" style="padding:12px 0 4px;text-align:right;color:#64748b;font-size:13px;font-weight:500;">Total</td>
                    <td style="padding:12px 0 4px;text-align:right;font-size:16px;font-weight:700;color:#0f172a;">$' . number_format($data['total_asp'], 2) . '</td>
                    </tr>
                </tfoot>
                </table>
            </div>

            <!-- Reminder Note + CTA -->
            <div style="padding:16px 28px;border-top:1px solid #e2e8f0;background:#fffbeb;">
                <table cellpadding="0" cellspacing="0" style="margin-bottom:14px;width:100%;">
                <tr>
                    <td width="3" style="background:#f59e0b;border-radius:2px;padding:0;">&nbsp;</td>
                    <td style="padding:0 0 0 10px;font-size:12px;color:#475569;line-height:1.7;">
                    <strong style="color:#334155;">Reminder:</strong> This order is still awaiting your action. Please update the status &mdash;
                    <strong style="color:#334155;">Acknowledged</strong>, <strong style="color:#334155;">Shipped</strong>, or <strong style="color:#334155;">Delivered</strong>
                    &mdash; in the WOUNDMED INC. system as soon as possible.
                    </td>
                </tr>
                </table>
                <a href="' . $orderLink . '" style="display:inline-block;padding:9px 18px;background:#0f172a;color:#fff;text-decoration:none;border-radius:6px;font-size:13px;font-weight:600;">
                View order details
                </a>
            </div>

            <!-- Footer -->
            <div style="padding:14px 28px;border-top:1px solid #e2e8f0;background:#fff;">
                <p style="font-size:11px;color:#94a3b8;margin:0;text-align:center;">
                This is an automated notification from the WOUNDMED INC. system.
                <span>
                &copy; ' . $year . ' WOUNDMED INC.
                </span>
                </p>
            </div>

            </div>
            </body>
        ';
    }
}
