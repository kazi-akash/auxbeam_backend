<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to Auxbeam Bangladesh</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1a365d; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .btn { display: inline-block; padding: 12px 24px; background: #1a365d; color: white; text-decoration: none; border-radius: 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Auxbeam Bangladesh!</h1>
        </div>

        <div class="content">
            <p>Hi {{ $user->name }},</p>

            <p>Welcome to Auxbeam Bangladesh! We're excited to have you as part of our community.</p>

            <p>At Auxbeam Bangladesh, you'll find premium automotive LED lighting — from off-road light bars and headlights to RGB rock lights and switch panels.</p>

            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $shopUrl }}" class="btn">Start Shopping</a>
            </p>

            <p>If you have any questions, feel free to reach out to our support team.</p>

            <p>Happy Shopping!<br>The Auxbeam Bangladesh Team</p>
        </div>

        <div class="footer">
            <p>Auxbeam Bangladesh - Your Premium LED Lighting Destination</p>
        </div>
    </div>
</body>
</html>
