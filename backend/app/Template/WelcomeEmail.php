<?php

namespace App\Template;

class WelcomeEmail
{
    public static function getTemplate(string $userName, string $email, string $resetUrl): string
    {
        return '
            <body style="font-family: Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 0;">
              <div style="max-width: 500px; margin: 40px auto; background: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                <h2 style="color: #1e293b; font-size: 20px; margin-bottom: 16px;">Welcome to WOUNDMED INC.!</h2>
                <p style="color: #475569; font-size: 14px; line-height: 1.6;">Hello ' . htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') . ',</p>
                <p style="color: #475569; font-size: 14px; line-height: 1.6;">
                  Your account has been successfully created. To get started, please set your password by clicking the button below.
                </p>

                <div style="background: #f1f5f9; padding: 16px; border-radius: 8px; margin: 20px 0;">
                  <p style="color: #1e293b; font-size: 14px; margin: 0;">
                    <strong>Email:</strong> <span style="color: #2563eb;">' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</span>
                  </p>
                </div>

                <a href="' . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . '"
                   style="display: inline-block; margin: 20px 0; padding: 12px 20px; background: #2563eb; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">
                   Set Your Password
                </a>

                <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; margin: 20px 0; border-radius: 4px;">
                  <p style="color: #92400e; font-size: 13px; margin: 0; line-height: 1.5;">
                    <strong>Important:</strong><br/>
                    This link will expire in 60 minutes. If the link has expired, please use the "Forgot Password" option on the login page to receive a new one.
                  </p>
                </div>

                <p style="color: #475569; font-size: 14px; line-height: 1.6;">
                  If you did not expect this email, please ignore it or contact our support team.
                </p>

                <p style="font-size: 12px; color: #94a3b8; margin-top: 30px; text-align: center;">
                  This is an automated message. Please do not reply to this email.<br />&copy; ' . date('Y') . ' WOUNDMED INC.
                </p>
              </div>
            </body>
        ';
    }
}
