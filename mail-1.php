
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

function cleanInput(string $value): string
{
    return trim($value);
}

$name    = cleanInput($_POST['full_name'] ?? '');
$mobile  = cleanInput($_POST['mobile'] ?? '');
$email   = cleanInput($_POST['email'] ?? '');
$query   = cleanInput($_POST['query'] ?? '');

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
    exit('Please enter valid form details.');
}

$mail = new PHPMailer(true);

try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;

    // Replace these with your actual Gmail credentials.
    $mail->Username   = 'pythondolphin@gmail.com';
    $mail->Password   = 'gitg bxgk kxts wghm';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';

    // Sender must match the authenticated Gmail account.
    $mail->setFrom(
        'info@feleadershipawards.com',
        'FE Leadership Awards Website'
    );





    // Recipients - each will receive a separate email
    $recipients = [
        'shivam@1solutions.biz'
    ];

    // Reply directly to the person who submitted the form
    $mail->addReplyTo($email, $name);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'New Contact Form Enquiry - FE Leadership Awards';

    $mail->Body = '
        <h2>New Contact Form Enquiry</h2>
        <p><strong>Name:</strong> ' .
        nl2br(htmlspecialchars($name, ENT_QUOTES, 'UTF-8')) . '</p>
        <p><strong>Mobile:</strong> ' .
        nl2br(htmlspecialchars($mobile, ENT_QUOTES, 'UTF-8')) . '</p>
        <p><strong>Email:</strong> ' .
        htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Query / Feedback:</strong><br>' .
        nl2br(htmlspecialchars($query, ENT_QUOTES, 'UTF-8')) . '</p>
    ';

    $mail->AltBody =
        "New Contact Form Enquiry\n\n" .
        "Name: $name\n" .
        "Mobile: $mobile\n" .
        "Email: $email\n\n" .
        "Query / Feedback:\n$query";

    // Send separate emails to each recipient
    foreach ($recipients as $recipient) {
        $mail->clearAddresses();
        $mail->addAddress($recipient);
        $mail->send();
    }



    /*
    $mail->addAddress(
        'shivam@1solutions.biz',
        'FE Leadership Awards'
    );

    $mail->addAddress(
        'sonu@1solutions.biz',
        'FE Leadership Awards'
    );

    $mail->addAddress(
        'pythondolphin@gmail.com',
        'FE Leadership Awards'
    );

    // Reply directly to the person who submitted the form.
    $mail->addReplyTo($email, $name);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'New Contact Form Enquiry - FE Leadership Awards';

    $mail->Body = '
        <h2>New Contact Form Enquiry</h2>
        <p><strong>Name:</strong> ' .
        nl2br(htmlspecialchars($name, ENT_QUOTES, 'UTF-8')) . '</p>
        <p><strong>Mobile:</strong> ' .
        nl2br(htmlspecialchars($mobile, ENT_QUOTES, 'UTF-8')) . '</p>
        <p><strong>Email:</strong> ' .
        htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Query / Feedback:</strong><br>' .
        nl2br(htmlspecialchars($query, ENT_QUOTES, 'UTF-8')) . '</p>
    ';

    $mail->AltBody =
        "New Contact Form Enquiry\n\n" .
        "Name: $name\n" .
        "Mobile: $mobile\n" .
        "Email: $email\n\n" .
        "Query / Feedback:\n$query";

    $mail->send();
    */

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your message has been sent successfully.'
    ]);
    exit;

} catch (Exception $e) {

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => false,
        'message' => 'Sorry, your message could not be sent. Please try again later.'
    ]);
    exit;

}
