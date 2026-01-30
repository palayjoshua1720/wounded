<?php

namespace App\Template;

class ForgotPasswordEmail
{
    public static function getTemplate(string $resetLink, string $userName = ''): string
    {
        $greetingName = $userName ?: 'User';
            
        return '
            <html>
            <head>
                <style>
                    body { 
                        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; 
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
                    .button-container { 
                        text-align: center;
                        margin: 30px 0;
                    }
                    .reset-button { 
                        display: inline-block;
                        padding: 15px 30px;
                        background: linear-gradient(135deg, #1e40af, #3b82f6);
                        color: white !important;
                        text-decoration: none !important;
                        border-radius: 8px;
                        font-weight: 600;
                        font-size: 16px;
                        text-align: center;
                        transition: all 0.3s ease;
                        box-shadow: 0 4px 6px rgba(30, 64, 175, 0.2);
                    }
                    .reset-button:hover { 
                        background: linear-gradient(135deg, #1d4ed8, #2563eb);
                        transform: translateY(-2px);
                        box-shadow: 0 6px 12px rgba(30, 64, 175, 0.3);
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
                        .reset-button { padding: 12px 20px; }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h2>Password Reset Request</h2>
                        <p>Account Security</p>
                    </div>
                    <div class="content">
                        <p class="greeting">Hello ' . htmlspecialchars($greetingName, ENT_QUOTES, 'UTF-8') . ',</p>
                        <p class="message">We received a request to reset your account password. If this was you, click the button below to continue:</p>
                            
                        <div class="button-container">
                            <a href="' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '" class="reset-button">
                                Reset Password
                            </a>
                        </div>
                            
                        <p class="message"><strong>Note:</strong> For security, this link will expire in 60 minutes.</p>
                            
                        <p class="instructions">If you did not request this, you can safely ignore this email. For security reasons, please never share password reset links with anyone.</p>
                        <p class="security-note">For your security, this email was automatically generated. Please do not reply.</p>
                    </div>
                    <div class="footer">
                        <p>WoundMed Inc. All rights reserved.</p> 
                    </div>
                </div>
            </body>
            </html>
        ';
    }
}
