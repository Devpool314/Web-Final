<?php
session_start();
require_once 'db.php';


require 'vendor/autoload.php'; // PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (!empty($email)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();

            // Tạo token và thời gian hết hạn
            $token = bin2hex(random_bytes(16));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Cập nhật vào CSDL
            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $stmt->bind_param("sss", $token, $expires, $email);
            $stmt->execute();

            // Gửi email
            $resetLink = "http://localhost/note_app/reset_password.php?token=$token";

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'nobanh0660@gmail.com';
                $mail->Password = 'adgu uqwj fhqq bhgn'; // App Password (không phải mật khẩu Gmail)
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465; 

                $mail->setFrom('nohaimai610@hotmail.com', 'Note App');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Đặt lại mật khẩu';
                $mail->Body = "Nhấn vào liên kết sau để đặt lại mật khẩu: <a href='$resetLink'>$resetLink</a>";

                $mail->send();
                $success = "✅ Email đặt lại mật khẩu đã được gửi. Vui lòng kiểm tra hộp thư.";
            } catch (Exception $e) {
                $error = "❌ Không thể gửi email: {$mail->ErrorInfo}";
            }
        } else {
            $error = "⚠️ Email không tồn tại trong hệ thống.";
        }
    } else {
        $error = "⚠️ Vui lòng nhập email.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
        <h3 class="text-center mb-4">Quên mật khẩu</h3>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Nhập email của bạn..." required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Gửi email xác nhận</button>
        </form>
        <p class="mt-3 text-center">
            <a href="index.php">Quay lại đăng nhập</a>
        </p>
    </div>
</body>
</html>
