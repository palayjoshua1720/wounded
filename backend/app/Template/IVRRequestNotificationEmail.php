<?php

namespace App\Template;

class IVRRequestNotificationEmail
{
    public static function getTemplate(array $data): string
    {
        $ivrNumber        = htmlspecialchars($data['ivr_number'], ENT_QUOTES, 'UTF-8');
        $patientName      = htmlspecialchars($data['patient_name'], ENT_QUOTES, 'UTF-8');
        $clinicName       = htmlspecialchars($data['clinic_name'], ENT_QUOTES, 'UTF-8');
        $brandName        = htmlspecialchars($data['brand_name'], ENT_QUOTES, 'UTF-8');
        $manufacturerName = htmlspecialchars($data['manufacturer_name'], ENT_QUOTES, 'UTF-8');
        $eligibilityLabel = htmlspecialchars($data['eligibility_label'], ENT_QUOTES, 'UTF-8');
        $fileUrl          = isset($data['file_url']) ? htmlspecialchars($data['file_url'], ENT_QUOTES, 'UTF-8') : null;
        $ivrLink          = htmlspecialchars($data['ivr_link'], ENT_QUOTES, 'UTF-8');

        return '
            <body style="font-family: Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 0;">
                <div style="max-width: 600px; margin: 40px auto; background: #ffffff; padding: 30px; 
                            border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">

                    <h2 style="color: #1e293b; font-size: 20px; margin-bottom: 8px;">
                        New IVR Request Notification
                    </h2>

                    <p style="color: #475569; font-size: 14px; margin-bottom: 16px;">
                        <strong>Manufacturer:</strong> ' . $manufacturerName . '
                    </p>

                    <p style="color: #475569; font-size: 14px; line-height: 1.6;">
                        Hello,<br>
                        A new IVR request has been submitted in the WoundMed system.
                    </p>

                    <p style="color: #0f172a; font-size: 14px; margin: 16px 0;">
                        <strong>IVR Number:</strong> ' . $ivrNumber . '<br>
                        <strong>Patient Name:</strong> ' . $patientName . '<br>
                        <strong>Clinic:</strong> ' . $clinicName . '<br>
                        <strong>Brand:</strong> ' . $brandName . '<br>
                        <strong>Eligibility Status:</strong> ' . $eligibilityLabel . '
                    </p>

                    ' . ($fileUrl ? '
                        <div style="margin: 20px 0;">
                            <a href="' . $fileUrl . '" 
                                style="display: inline-block; padding: 12px 20px; background: #16a34a; color: #ffffff;
                                    text-decoration: none; border-radius: 6px; font-weight: bold;">
                                Download Attached IVR File
                            </a>
                        </div>
                    ' : '') . '

                    <!-- MAGIC LINK BUTTON -->
                    <div style="margin: 25px 0;">
                        <a href="' . $ivrLink . '" 
                            style="display: inline-block; padding: 12px 20px; background: #2563eb; color: #ffffff;
                                text-decoration: none; border-radius: 6px; font-weight: bold;">
                            View IVR Request Details
                        </a>
                    </div>

                    <p style="color: #475569; font-size: 13px; margin-top: 20px; line-height: 1.6;
                            background:#f1f5f9; padding:10px 12px; border-radius:6px; border-left:4px solid #2563eb;">
                        <strong>Note:</strong> Please review this IVR request and update the eligibility
                        status accordingly (Pending, Eligible, Not Eligible). You may access the request
                        directly using the secure link above.
                    </p>

                    <p style="font-size: 12px; color: #94a3b8; margin-top: 30px; text-align: center;">
                        This is an automated notification from the WoundMed System.<br>
                        &copy; ' . date('Y') . ' WOUNDMED INC.
                    </p>

                </div>
            </body>
        ';
    }

}
