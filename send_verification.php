<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function send_verification_email($to_email, $token) {
    // ✅ Đường dẫn xác nhận
    $verify_link = "http://localhost/note_app/verify.php?token=" . urlencode($token);

    $mail = new PHPMailer(true);

    try {
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';           // SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'nobanh0660@gmail.com';     // Email của bạn
        $mail->Password   = 'adgu uqwj fhqq bhgn';        // App password (không dùng mật khẩu Gmail thường)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Người gửi & người nhận
        $mail->setFrom('nohaimai610@hotmail.com', 'Note App');
        $mail->addAddress($to_email); // ✅ Gửi đúng email người đăng ký

        // Nội dung
        $mail->isHTML(true);
        $mail->Subject = 'Xác nhận tài khoản của bạn';
        $mail->Body    = "
            <p>Chào bạn,</p>
            <p>Vui lòng bấm vào liên kết sau để xác minh email:</p>
            <p><a href='$verify_link'>$verify_link</a></p>
            <p>Nếu bạn không đăng ký tài khoản, hãy bỏ qua email này.</p>
        ";
        $mail->AltBody = "Vui lòng truy cập đường dẫn sau để xác minh email: $verify_link";

        $mail->send();
        // echo 'Đã gửi email xác minh';
        return true;

    } catch (Exception $e) {
        error_log("Lỗi gửi email: {$mail->ErrorInfo}");
        return false;
    }
}
?>