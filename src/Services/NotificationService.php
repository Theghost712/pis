<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class NotificationService
{
    private array $config;
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/config.php';
        $this->mailer = new PHPMailer(true);
        $this->setupMailer();
    }

    private function setupMailer(): void
    {
        $mailConfig = $this->config['mail'];
        
        $this->mailer->isSMTP();
        $this->mailer->Host = $mailConfig['host'];
        $this->mailer->Port = $mailConfig['port'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $mailConfig['username'];
        $this->mailer->Password = $mailConfig['password'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        
        $this->mailer->setFrom($mailConfig['from'], $mailConfig['from_name']);
        $this->mailer->isHTML(true);
        $this->mailer->CharSet = 'UTF-8';
    }

    public function sendVerificationEmail(string $email, string $name, int $userId): bool
    {
        $security = new \App\Core\Security();
        $token = $security->generateJWT([
            'user_id' => $userId,
            'type' => 'email_verification',
            'exp' => time() + 86400 // 24 hours
        ]);
        
        $verificationUrl = $this->config['app']['url'] . '/verify-email?token=' . urlencode($token);
        
        $subject = 'Verify Your Email Address';
        $body = $this->getEmailTemplate('verification', [
            'name' => $name,
            'verification_url' => $verificationUrl,
            'app_name' => $this->config['app']['name']
        ]);
        
        return $this->sendEmail($email, $name, $subject, $body);
    }

    public function sendPasswordResetEmail(string $email, string $name, string $token): bool
    {
        $resetUrl = $this->config['app']['url'] . '/reset-password?token=' . urlencode($token);
        
        $subject = 'Reset Your Password';
        $body = $this->getEmailTemplate('password_reset', [
            'name' => $name,
            'reset_url' => $resetUrl,
            'app_name' => $this->config['app']['name']
        ]);
        
        return $this->sendEmail($email, $name, $subject, $body);
    }

    public function sendConsentNotification(string $email, string $name, array $consentData): bool
    {
        $subject = 'New Consent Granted';
        $body = $this->getEmailTemplate('consent_notification', [
            'name' => $name,
            'patient_name' => $consentData['patient_name'],
            'scope' => $consentData['scope'],
            'expires_at' => $consentData['expires_at'],
            'app_name' => $this->config['app']['name']
        ]);
        
        return $this->sendEmail($email, $name, $subject, $body);
    }

    public function sendRecordAccessNotification(string $email, string $name, array $accessData): bool
    {
        $subject = 'Your Medical Records Were Accessed';
        $body = $this->getEmailTemplate('record_access', [
            'name' => $name,
            'provider_name' => $accessData['provider_name'],
            'record_type' => $accessData['record_type'],
            'access_time' => $accessData['access_time'],
            'app_name' => $this->config['app']['name']
        ]);
        
        return $this->sendEmail($email, $name, $subject, $body);
    }

    public function send(int $userId, string $subject, string $message, string $type = 'general'): bool
    {
        $notificationModel = new \App\Models\Notification();
        $notificationModel->create([
            'user_id' => $userId,
            'type' => $type,
            'subject' => $subject,
            'message' => $message,
        ]);
        return true;
    }

    private function sendEmail(string $email, string $name, string $subject, string $body): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    private function getEmailTemplate(string $template, array $data): string
    {
        $templates = [
            'verification' => '
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #0d6efd; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                        .content { padding: 30px; background-color: #f8f9fa; border-radius: 0 0 5px 5px; }
                        .button { display: inline-block; padding: 12px 24px; background-color: #0d6efd; color: white; text-decoration: none; border-radius: 5px; }
                        .footer { margin-top: 20px; text-align: center; color: #6c757d; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <h2>{{app_name}}</h2>
                        </div>
                        <div class="content">
                            <h3>Verify Your Email Address</h3>
                            <p>Hello {{name}},</p>
                            <p>Thank you for registering with {{app_name}}. Please click the button below to verify your email address:</p>
                            <p style="text-align: center;">
                                <a href="{{verification_url}}" class="button">Verify Email</a>
                            </p>
                            <p>If the button doesn\'t work, you can copy and paste this link into your browser:</p>
                            <p><a href="{{verification_url}}">{{verification_url}}</a></p>
                            <p>This link will expire in 24 hours.</p>
                        </div>
                        <div class="footer">
                            <p>&copy; {{app_name}}. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ',
            'password_reset' => '
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #0d6efd; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                        .content { padding: 30px; background-color: #f8f9fa; border-radius: 0 0 5px 5px; }
                        .button { display: inline-block; padding: 12px 24px; background-color: #0d6efd; color: white; text-decoration: none; border-radius: 5px; }
                        .footer { margin-top: 20px; text-align: center; color: #6c757d; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <h2>{{app_name}}</h2>
                        </div>
                        <div class="content">
                            <h3>Reset Your Password</h3>
                            <p>Hello {{name}},</p>
                            <p>We received a request to reset your password for {{app_name}}. Click the button below to create a new password:</p>
                            <p style="text-align: center;">
                                <a href="{{reset_url}}" class="button">Reset Password</a>
                            </p>
                            <p>If the button doesn\'t work, you can copy and paste this link into your browser:</p>
                            <p><a href="{{reset_url}}">{{reset_url}}</a></p>
                            <p>This link will expire in 1 hour.</p>
                            <p>If you didn\'t request a password reset, please ignore this email.</p>
                        </div>
                        <div class="footer">
                            <p>&copy; {{app_name}}. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ',
            'consent_notification' => '
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #198754; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                        .content { padding: 30px; background-color: #f8f9fa; border-radius: 0 0 5px 5px; }
                        .footer { margin-top: 20px; text-align: center; color: #6c757d; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <h2>{{app_name}}</h2>
                        </div>
                        <div class="content">
                            <h3>New Consent Granted</h3>
                            <p>Hello {{name}},</p>
                            <p><strong>{{patient_name}}</strong> has granted you consent to access their medical records.</p>
                            <p><strong>Access Scope:</strong> {{scope}}</p>
                            <p><strong>Expires At:</strong> {{expires_at}}</p>
                            <p>You can now view this patient\'s medical records through the system.</p>
                        </div>
                        <div class="footer">
                            <p>&copy; {{app_name}}. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ',
            'record_access' => '
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #ffc107; color: #333; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                        .content { padding: 30px; background-color: #f8f9fa; border-radius: 0 0 5px 5px; }
                        .footer { margin-top: 20px; text-align: center; color: #6c757d; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <h2>{{app_name}}</h2>
                        </div>
                        <div class="content">
                            <h3>Medical Records Accessed</h3>
                            <p>Hello {{name}},</p>
                            <p><strong>{{provider_name}}</strong> accessed your medical records on {{access_time}}.</p>
                            <p><strong>Record Type:</strong> {{record_type}}</p>
                            <p>If you did not authorize this access, please contact the system administrator immediately.</p>
                        </div>
                        <div class="footer">
                            <p>&copy; {{app_name}}. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            '
        ];

        $html = $templates[$template] ?? '';
        foreach ($data as $key => $value) {
            $html = str_replace('{{' . $key . '}}', htmlspecialchars($value), $html);
        }
        return $html;
    }
}