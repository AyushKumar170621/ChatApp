<?php
session_start();
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendResetEmail($email, $token) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username = 'ayushkumar20021922@gmail.com'; // SMTP username
        $mail->Password = 'qoxqwxvdwkgcpzkq'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 80;

        // Recipients
        $mail->setFrom('ayushkumar20021922@gmail.com', 'Mailer');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset';
        $mail->Body = 'Click on the following link to reset your password: <a href="http://localhost/ChatApp/vendor-front/reset_password_form.php?token=' . $token . '">Reset Password</a>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Generate a token and save it in the database with the email
    $token = bin2hex(random_bytes(50));

    // Here you should save the token in your database with the email
    // saveToken($email, $token);

    if (sendResetEmail($email, $token)) {
        $_SESSION['message'] = 'A password reset link has been sent to your email.';
    } else {
        $_SESSION['message'] = 'Failed to send reset link. Please try again.';
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
</head>
<body>
    <form method="POST" action="forgot_password.php">
        <input type="email" name="email" required placeholder="Enter your email" />
        <button type="submit">Send Reset Link</button>
    </form>
    <?php
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
    ?>
</body>
</html>
