<?php
require_once 'db.php';
session_start();
if (!isset($_GET['token'])) {
    die("Thiếu mã xác minh.");
}

$token = $_GET['token'];

// Kiểm tra token và cập nhật
$stmt = $conn->prepare("SELECT * FROM users WHERE verify_token=? AND is_verified=0");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Cập nhật trạng thái xác minh
    $update = $conn->prepare("UPDATE users SET is_verified=1, verify_token=NULL WHERE email=?");
    $update->bind_param("s", $user['email']);
    $update->execute();

    // Cập nhật session nếu đang đăng nhập
    if (isset($_SESSION['user']) && $_SESSION['user']['email'] === $user['email']) {
        $_SESSION['user']['is_verified'] = 1;
    }

    echo "Tài khoản đã được xác minh! <a href='index.php'>Quay lại</a>";
} else {
    echo "Token không hợp lệ hoặc tài khoản đã được xác minh.";
}
?>
