<?php
session_start();
require_once 'db.php';

// Lấy token từ URL
$token = $_GET['token'] ?? '';
$error = null;
$success = null;
$show_form = false;

if (!empty($token)) {
    // Kiểm tra token và hạn sử dụng
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > UTC_TIMESTAMP()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        $show_form = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_pass = $_POST['password'] ?? '';
            $confirm_pass = $_POST['confirm_password'] ?? '';

            if (strlen($new_pass) < 6) {
                $error = "⚠️ Mật khẩu phải có ít nhất 6 ký tự.";
            } elseif ($new_pass !== $confirm_pass) {
                $error = "⚠️ Mật khẩu xác nhận không khớp.";
            } else {
                // Cập nhật mật khẩu mới
                $hash = password_hash($new_pass, PASSWORD_BCRYPT);
                $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
                $update->bind_param("si", $hash, $user['id']);
                $update->execute();

                $success = "✅ Mật khẩu đã được đặt lại thành công. Bạn có thể <a href='index.php'>đăng nhập</a> ngay.";
                $show_form = false;
            }
        }
    } else {
        $error = "❌ Token không hợp lệ hoặc đã hết hạn. Vui lòng yêu cầu lại đặt lại mật khẩu.";
    }
} else {
    $error = "❌ Không tìm thấy token trong liên kết.";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt lại mật khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card p-4 shadow" style="width: 100%; max-width: 450px;">
        <h3 class="text-center mb-4">Đặt lại mật khẩu</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($show_form): ?>
            <form method="POST" novalidate>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Mật khẩu mới" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="confirm_password" class="form-control" placeholder="Xác nhận mật khẩu" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Đặt lại mật khẩu</button>
            </form>
        <?php endif; ?>

        <p class="mt-3 text-center">
            <a href="index.php">← Quay lại đăng nhập</a>
        </p>
    </div>
</body>
</html>
