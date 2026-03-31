<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workshop Reminder</title>
</head>
<body>
    <p>Hello {{ $recipientName }},</p>

    <p>This is a reminder for your upcoming Internal Academy workshop.</p>

    <p><strong>Workshop:</strong> {{ $workshop->title }}</p>
    <p><strong>Starts:</strong> {{ $workshop->starts_at->format('Y-m-d H:i') }}</p>
    <p><strong>Ends:</strong> {{ $workshop->ends_at->format('Y-m-d H:i') }}</p>

    <p>See you there.</p>
</body>
</html>
