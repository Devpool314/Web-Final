<?php

session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$email = $_SESSION['user']['email'];
$success = $error = null;

// Lấy thông tin từ CSDL
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Cập nhật ảnh đại diện
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_avatar'])) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $file = $_FILES['avatar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $new_name = uniqid('avatar_') . '.' . $ext;
            $upload_path = 'uploads/' . $new_name;
            if (!is_dir('uploads')) mkdir('uploads');
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE email = ?");
                $stmt->bind_param("ss", $upload_path, $email);
                $stmt->execute();
                $user['avatar'] = $upload_path;
                $_SESSION['user']['avatar'] = $upload_path; // Cập nhật session ngay lập tức
                    $avatar_url = $upload_path; // <-- Thêm dòng này
                $success = "✅ Ảnh đại diện đã cập nhật.";
            } else $error = "❌ Tải ảnh thất bại.";
        } else $error = "❌ Chỉ hỗ trợ JPG hoặc PNG.";
    }
}

// Cập nhật tên hiển thị
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_name'])) {
    $newName = trim($_POST['display_name']);
    if (!empty($newName)) {
        $stmt = $conn->prepare("UPDATE users SET display_name = ? WHERE email = ?");
        $stmt->bind_param("ss", $newName, $email);
        $stmt->execute();
        $_SESSION['user']['name'] = $newName;
        $user['display_name'] = $newName;
        $success = "✅ Đã cập nhật tên hiển thị.";
    } else {
        $error = "⚠️ Tên hiển thị không được để trống.";
    }
}

// Đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (password_verify($current, $user['password'])) {
        if ($new === $confirm && strlen($new) >= 6) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed, $email);
            $stmt->execute();
            $success = "✅ Mật khẩu đã thay đổi thành công.";
        } else $error = "⚠️ Mật khẩu mới không khớp hoặc quá ngắn.";
    } else {
        $error = "❌ Mật khẩu hiện tại không đúng.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ cá nhân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .avatar { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 2px solid #ccc; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card p-4 mx-auto shadow" style="max-width: 600px;">
        <h3 class="mb-3 text-center">👤 Hồ sơ người dùng</h3>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="text-center mb-3">
            <img src="<?= htmlspecialchars($user['avatar'] ?? 'default-avatar.png') ?>?v=<?= time() ?>" class="avatar" alt="Avatar">
        </div>

        <!-- Cập nhật ảnh đại diện -->
        <form method="POST" enctype="multipart/form-data" class="mb-4">
            <div class="input-group">
                <input type="file" name="avatar" accept="image/*" class="form-control" required>
                <button class="btn btn-outline-primary" type="submit" name="update_avatar">Cập nhật ảnh</button>
            </div>
        </form>

        <!-- Cập nhật tên -->
        <form method="POST" class="mb-4">
            <label class="form-label">Tên hiển thị</label>
            <div class="input-group">
                <input type="text" name="display_name" value="<?= htmlspecialchars($user['display_name']) ?>" class="form-control" required>
                <button class="btn btn-outline-success" type="submit" name="update_name">Lưu</button>
            </div>
        </form>

        <!-- Đổi mật khẩu -->
        <form method="POST" class="mb-4">
            <label class="form-label">Đổi mật khẩu</label>
            <input type="password" name="current_password" class="form-control mb-2" placeholder="Mật khẩu hiện tại" required>
            <input type="password" name="new_password" class="form-control mb-2" placeholder="Mật khẩu mới (≥6 ký tự)" required>
            <input type="password" name="confirm_password" class="form-control mb-2" placeholder="Xác nhận mật khẩu mới" required>
            <button type="submit" name="change_password" class="btn btn-outline-warning">Đổi mật khẩu</button>
        </form>

        <div class="text-center">
            <a href="home.php" class="btn btn-secondary">← Quay lại trang chủ</a>
        </div>
    </div>
</div>

<?php if (isset($avatar_url)): ?>
<script>
localStorage.setItem('user_avatar_<?php echo $_SESSION['user']['email']; ?>', '<?php echo addslashes($avatar_url); ?>');
</script>
<?php endif; ?>

</body>
</html>