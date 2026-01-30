<?php

namespace App\Services;

use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Services\EmailService;

class VerificationCodeService
{
    protected $emailService;

    public function __construct()
    {
        $this->emailService = new EmailService();
    }

    /**
     * Generate and send a verification code to the user's email
     */
    public function sendVerificationCode(User $user)
    {
        // Generate a 6-digit verification code
        $code = $this->generateCode();
        
        // Set expiration time (10 minutes from now)
        $expiresAt = now()->addMinutes(10);
        
        // Delete any existing unexpired codes for this user
        EmailVerificationCode::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->where('used', false)
            ->delete();
        
        // Create new verification code record
        $verificationCode = EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => $expiresAt,
            'used' => false,
        ]);
        
        // Send the verification code via email
        $this->sendCodeEmail($user, $code);
        
        return $verificationCode;
    }

    /**
     * Generate a 6-digit numeric code
     */
    private function generateCode()
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send the verification code to the user's email
     */
    private function sendCodeEmail(User $user, string $code)
    {
        $emailParams = [
            'to' => $user->email,
            'from' => 'noreply@woundmed.com',
            'from_name' => 'WoundMed Security',
            'subject' => 'Your Login Verification Code',
            'body' => $this->getVerificationEmailTemplate($user, $code),
        ];

        $this->emailService->send_email(
            $emailParams,
            'Verification code sent successfully',
            'Failed to send verification code'
        );
    }

    /**
     * Get the email template for verification code
     */
    private function getVerificationEmailTemplate(User $user, string $code)
    {
        $firstName = $user->first_name ?? 'User';
        
        return "
            <html>
            <head>
                <style>
                    body { 
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                        background-color: #f5f7fa; 
                        margin: 0; 
                        padding: 0; 
                        -webkit-font-smoothing: antialiased; 
                        -moz-osx-font-smoothing: grayscale;
                    }
                    .container { 
                        max-width: 600px; 
                        margin: 30px auto; 
                        background: #ffffff;
                        border-radius: 12px;
                        overflow: hidden;
                        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
                    }
                    .header { 
                        background: linear-gradient(135deg, #1e40af, #3b82f6);
                        padding: 30px 20px;
                        text-align: center;
                        color: white;
                    } 
                    .header h2 { 
                        margin: 0; 
                        font-size: 24px;
                        font-weight: 600;
                    }
                    .content { 
                        padding: 40px 30px;
                        background: #f6f3f3;
                    }
                    .greeting { 
                        font-size: 18px;
                        font-weight: 600;
                        color: #1f2937;
                        margin: 0 0 20px 0;
                    }
                    .message { 
                        font-size: 16px;
                        color: #4b5563;
                        line-height: 1.6;
                        margin: 0 0 30px 0;
                    }
                    .code-container { 
                        background: #f8fafc;
                        border: 2px dashed #cbd5e1;
                        border-radius: 12px;
                        padding: 25px;
                        text-align: center;
                        margin: 30px 0;
                    }
                    .code { 
                        font-size: 32px; 
                        font-weight: 700; 
                        letter-spacing: 8px; 
                        color: #000000;
                        text-align: center;
                        text-transform: uppercase;
                        font-family: monospace;
                        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
                    }
                    .code-label { 
                        display: block;
                        font-size: 14px;
                        color: #64748b;
                        margin-bottom: 10px;
                        font-weight: 500;
                    }
                    .instructions { 
                        font-size: 14px;
                        color: #64748b;
                        line-height: 1.6;
                        margin: 30px 0 0 0;
                        padding-top: 20px;
                        border-top: 1px solid #e2e8f0;
                    }
                    .footer { 
                        background: #f8fafc;
                        padding: 20px 30px;
                        text-align: center;
                        border-top: 1px solid #e2e8f0;
                        font-size: 13px;
                        color: #64748b;
                    }
                    .security-note { 
                        font-size: 12px;
                        color: #ef4444;
                        font-style: italic;
                        margin-top: 10px;
                    }
                    @media (max-width: 600px) {
                        .container { margin: 15px auto; }
                        .content { padding: 25px 20px; }
                        .code { font-size: 24px; letter-spacing: 5px; }
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'> 
                        <h2>WoundMed Security</h2>
                        <p>Account Verification</p>
                    </div>
                    <div class='content'>
                        <p class='greeting'>Hello {$firstName},</p>
                        <p class='message'>A login request was made for your WoundMed account. Use the verification code below to securely access your account.</p>
                        
                        <div class='code-container'>
                            <span class='code-label'>Your Verification Code</span>
                            <div class='code'>{$code}</div>
                        </div>
                        
                        <p class='message'><strong>Note:</strong> This code will expire in 10 minutes for security purposes.</p>
                        
                        <p class='instructions'>If you did not request this code, please disregard this email. For security reasons, please never share this code with anyone.</p>
                        <p class='security-note'>For your security, this email was automatically generated. Please do not reply.</p>
                    </div>
                    <div class='footer'>
                        <p>WoundMed Inc. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Verify the provided code for the user
     */
    public function verifyCode(User $user, string $code)
    {
        $verificationCode = EmailVerificationCode::where('user_id', $user->id)
            ->where('code', $code)
            ->valid()
            ->first();

        if (!$verificationCode) {
            return false;
        }

        // Mark the code as used
        $verificationCode->update(['used' => true]);

        return true;
    }
}