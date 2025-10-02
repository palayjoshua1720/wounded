<?php

namespace App\Template;

class ForgotPasswordEmail
{
    public static function getTemplate(string $resetLink): string
    {
        return '
            <body style="font-family: Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 0;">
              <div style="max-width: 500px; margin: 40px auto; background: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                <h2 style="color: #1e293b; font-size: 20px; margin-bottom: 16px;">Password Reset Request</h2>
                <p style="color: #475569; font-size: 14px; line-height: 1.6;">Hello,</p>
                <p style="color: #475569; font-size: 14px; line-height: 1.6;">
                  We received a request to reset your account password. If this was you, click the button below to continue:
                </p>
                <a href="' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '" 
                   style="display: inline-block; margin: 20px 0; padding: 12px 20px; background: #2563eb; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">
                   Reset Password
                </a>
                <p style="color: #475569; font-size: 14px; line-height: 1.6;">
                  If you did not request this, you can safely ignore this email.
                </p>
                <p style="font-size: 12px; color: #94a3b8; margin-top: 30px; text-align: center;">
                  For security, this link will expire in 60 minutes.<br />&copy; ' . date('Y') . ' WOUNDMED INC.
                </p>
              </div>
            </body>
        ';
    }
}
