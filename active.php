<?php
session_start();
include 'db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Kiểm tra token trong cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT * FROM users WHERE verify_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        // Cập nhật trạng thái người dùng
        $update = $conn->prepare("UPDATE users SET is_verified = 1 WHERE verify_token = ?");
        $update->bind_param("s", $token);
        $update->execute();

        // Đăng nhập người dùng và chuyển hướng
        $_SESSION['user'] = [
            'email' => $user['email'],
            'name' => $user['display_name']
        ];
        echo "Tài khoản của bạn đã được kích hoạt. Bạn sẽ được chuyển hướng đến trang chủ.";
        header("Location: home.php");
        exit;
    } else {
        echo "Token không hợp lệ hoặc đã hết hạn.";
    }
}
?>
