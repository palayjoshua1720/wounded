<?php

namespace App\Template;

class WelcomeEmail
{
    public static function getTemplate(string $userName, string $email, string $password, string $loginUrl): string
    {
        return '
            <body style="font-family: Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 0;">
              <div style="max-width: 500px; margin: 40px auto; background: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                <h2 style="color: #1e293b; font-size: 20px; margin-bottom: 16px;">Welcome to WoundMed!</h2>
                <p style="color: #475569; font-size: 14px; line-height: 1.6;">Hello ' . htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') . ',</p>
                <p style="color: #475569; font-size: 14px; line-height: 1.6;">
                  Your account has been successfully created. Below are your login credentials:
                </p>
                
                <div style="background: #f1f5f9; padding: 16px; border-radius: 8px; margin: 20px 0;">
                  <p style="color: #1e293b; font-size: 14px; margin: 0 0 8px 0;">
                    <strong>Email:</strong> <span style="color: #2563eb;">' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</span>
                  </p>
                  <p style="color: #1e293b; font-size: 14px; margin: 0;">
                    <strong>Password:</strong> <span style="color: #2563eb; font-family: monospace;">' . htmlspecialchars($password, ENT_QUOTES, 'UTF-8') . '</span>
                  </p>
                </div>
                
                <p style="color: #475569; font-size: 14px; line-height: 1.6;">
                  Click the button below to access your account:
                </p>
                
                <a href="' . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . '" 
                   style="display: inline-block; margin: 20px 0; padding: 12px 20px; background: #2563eb; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">
                   Login to Your Account
                </a>
                
                <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; margin: 20px 0; border-radius: 4px;">
                  <p style="color: #92400e; font-size: 13px; margin: 0; line-height: 1.5;">
                    <strong>⚠️ Important Security Notice:</strong><br/>
                    For your security, please change your password after your first login. You can do this by going to Settings > Security.
                  </p>
                </div>
                
                <p style="color: #475569; font-size: 14px; line-height: 1.6;">
                  If you have any questions or need assistance, please contact our support team.
                </p>
                
                <p style="font-size: 12px; color: #94a3b8; margin-top: 30px; text-align: center;">
                  This is an automated message. Please do not reply to this email.<br />&copy; ' . date('Y') . ' WOUNDMED INC.
                </p>
              </div>
            </body>
        ';
    }
}
