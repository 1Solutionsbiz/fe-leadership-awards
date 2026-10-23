<?php

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Clean input
function cleanInput(string $value): string
{
    return trim($value);
}

$name   = cleanInput($_POST['full_name'] ?? '');
$mobile = cleanInput($_POST['mobile'] ?? '');
$email  = cleanInput($_POST['email'] ?? '');
$query  = cleanInput($_POST['query'] ?? '');

// Validate form fields
if (
    $name === '' ||
    $email === '' ||
    $query === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($name) > 100 ||
    strlen($mobile) > 25 ||
    strlen($email) > 254 ||
    strlen($query) > 5000
) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Please enter valid form details.'
    ]);
    exit;
}

// Recipients: each will receive a separate email
$recipients = [
    'shivam@1solutions.biz'
];

// Email details
$subject = 'New Contact Form Enquiry - FE Leadership Awards';

$safeName   = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$safeMobile = htmlspecialchars($mobile, ENT_QUOTES, 'UTF-8');
$safeEmail  = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$safeQuery  = nl2br(htmlspecialchars($query, ENT_QUOTES, 'UTF-8'));

// HTML email body
$htmlMessage = '
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <h2>New Contact Form Enquiry</h2>

    <p><strong>Name:</strong> ' . $safeName . '</p>

    <p><strong>Mobile:</strong> ' . $safeMobile . '</p>

    <p><strong>Email:</strong> ' . $safeEmail . '</p>

    <p><strong>Query / Feedback:</strong><br>' . $safeQuery . '</p>
</body>
</html>
';

// Email headers
$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/html; charset=UTF-8';

// Use an email address on your own domain as the sender.
// Your hosting provider may require this address to exist.
$headers[] = 'From: FE Leadership Awards <info@feleadershipawards.com>';

// Set Reply-To to the form submitter's email
$headers[] = 'Reply-To: ' . $email;

$headersString = implode("\r\n", $headers);

try {
    $allSent = true;

    // Send an individual email to each recipient
    foreach ($recipients as $recipient) {
        $sent = mail(
            $recipient,
            '=?UTF-8?B?' . base64_encode($subject) . '?=',
            $htmlMessage,
            $headersString
        );

        if (!$sent) {
            $allSent = false;
            error_log(
                'Contact form mail() failed for recipient: ' . $recipient
            );
        }
    }

    if (!$allSent) {
        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Email could not be sent. Please try again later.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your message has been sent successfully.'
    ]);
    exit;

} catch (Throwable $e) {
    error_log('Contact form error: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Sorry, your message could not be sent. Please try again later.'
    ]);
    exit;
}
