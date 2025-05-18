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
        $mail->Password   = 'adgu uqwj fhqq bhgn';        // App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';                    // Thêm charset UTF-8

        // Người gửi & người nhận
        $mail->setFrom('nobanh0660@gmail.com', '=?UTF-8?B?'.base64_encode('Note App').'?='); // Sửa email người gửi
        $mail->addAddress($to_email);

        // Nội dung
        $mail->isHTML(true);
        $mail->Subject = '=?UTF-8?B?'.base64_encode('Xác nhận tài khoản Note App của bạn').'?=';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #2196f3;'>Xác nhận tài khoản Note App</h2>
                <p>Xin chào,</p>
                <p>Cảm ơn bạn đã đăng ký tài khoản tại Note App. Để hoàn tất quá trình đăng ký, vui lòng bấm vào nút bên dưới để xác minh địa chỉ email của bạn:</p>
                <p style='text-align: center;'>
                    <a href='$verify_link' 
                       style='display: inline-block; 
                              background-color: #2196f3; 
                              color: white; 
                              padding: 12px 24px; 
                              text-decoration: none; 
                              border-radius: 4px;
                              margin: 20px 0;'>
                        Xác nhận tài khoản
                    </a>
                </p>
                <p>Hoặc bạn có thể copy và dán đường dẫn sau vào trình duyệt:</p>
                <p style='background: #f5f5f5; padding: 10px; word-break: break-all;'>$verify_link</p>
                <p><small style='color: #666;'>Nếu bạn không đăng ký tài khoản này, vui lòng bỏ qua email này.</small></p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='color: #666; font-size: 12px; text-align: center;'>Email này được gửi tự động, vui lòng không trả lời.</p>
            </div>
        ";
        $mail->AltBody = "Xin chào,\n\nVui lòng truy cập đường dẫn sau để xác minh email của bạn:\n$verify_link\n\nNếu bạn không đăng ký tài khoản này, vui lòng bỏ qua email này.";

        $mail->send();
        // echo 'Đã gửi email xác minh';
        return true;

    } catch (Exception $e) {
        error_log("Lỗi gửi email: {$mail->ErrorInfo}");
        return false;
    }
}
?>
