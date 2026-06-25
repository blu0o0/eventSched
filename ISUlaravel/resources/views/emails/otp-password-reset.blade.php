<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; text-align: center;">
        <h2 style="color: #2d8659;">Password Reset</h2>
        <p>Hello {{ $user->name }},</p>
        <p>Your password reset code is:</p>
        <div style="font-size: 36px; font-weight: bold; color: #2d8659; letter-spacing: 8px; padding: 20px; background: #e8f5e9; border-radius: 8px; margin: 20px 0;">
            {{ $otp }}
        </div>
        <p>This code will expire in 5 minutes.</p>
        <p style="color: #6c757d; font-size: 12px;">If you did not request a password reset, please ignore this email.</p>
        <hr>
        <p style="color: #6c757d; font-size: 12px;">ISU Event Scheduling Reservation System</p>
    </div>
</body>
</html>