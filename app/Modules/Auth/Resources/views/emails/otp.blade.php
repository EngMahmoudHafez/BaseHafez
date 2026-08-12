<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @php
        $locale = app()->getLocale() ?: config('app.locale', 'ar');
    @endphp
    <title>{{ trans('messages.Your OTP Code', [], $locale) }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
        }
        .otp-box {
            background-color: #f8f9fa;
            border: 2px dashed #3498db;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #3498db;
            letter-spacing: 5px;
            margin: 10px 0;
        }
        .message {
            color: #555;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ trans('messages.Welcome', [], $locale) }} {{ $userName }}!</h1>
        </div>

        <div class="message">
            <p>{{ trans('messages.Your OTP code is', [], $locale) }}:</p>
        </div>

        <div class="otp-box">
            <div class="otp-code">{{ $otpCode }}</div>
        </div>

        <div class="message">
            <p>
                {{ trans('messages.Please use this code to verify your email address. This code will expire in 5 minutes.', [], $locale) }}
            </p>
        </div>

        <div class="warning">
            <strong>{{ trans('messages.Important', [], $locale) }}:</strong>
            {{ trans('messages.Do not share this code with anyone.', [], $locale) }}
        </div>

        <div class="footer">
            <p>{{ trans('messages.This is an automated message, please do not reply.', [], $locale) }}</p>
            <p>
                &copy; {{ date('Y') }} {{ config('app.name') }}. {{ trans('messages.All rights reserved.', [], $locale) }}
            </p>
        </div>
    </div>
</body>
</html>
