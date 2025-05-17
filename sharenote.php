<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Chia sẻ ghi chú</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .share-list { margin-top: 20px; }
    .share-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #eee; }
    .share-item:last-child { border-bottom: none; }
    .share-actions button { margin-left: 8px; }
  </style>
</head>
<body class="bg-light">
<div class="container py-4">
  <h3>Chia sẻ ghi chú</h3>
  <div id="noteInfo" class="mb-3"></div>
  <form id="shareForm" class="row g-2">
    <div class="col-auto">
      <input type="email" id="shareEmail" class="form-control" placeholder="Email người nhận" required>
    </div>
    <div class="col-auto">
      <select id="sharePermission" class="form-select">
        <option value="read">Chỉ đọc</option>
        <option value="edit">Chỉnh sửa</option>
      </select>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary">Chia sẻ</button>
    </div>
  </form>
  <div class="share-list mt-4">
    <h5>Đã chia sẻ với:</h5>
    <div id="sharedUsers"></div>
  </div>
  <a href="home.php" class="btn btn-secondary mt-4">← Quay lại ghi chú</a>
</div>
<script>
const userEmail = "<?php echo $_SESSION['user']['email']; ?>";
const noteId = sessionStorage.getItem('shareNoteId');
let notes = JSON.parse(localStorage.getItem('notes')) || [];
let note = notes.find(n => n.id == noteId);

if (!note) {
  document.getElementById('noteInfo').innerHTML = '<div class="alert alert-danger">Không tìm thấy ghi chú!</div>';
  document.getElementById('shareForm').style.display = 'none';
} else {
  document.getElementById('noteInfo').innerHTML = `
    <div class="card p-2">
      <div><b>Tiêu đề:</b> ${note.title || '(Không có tiêu đề)'}</div>
      <div><b>Nội dung:</b> ${note.content ? note.content.substring(0, 100) + '...' : '(Trống)'}</div>
    </div>
  `;
  renderSharedUsers();
}

function renderSharedUsers() {
  const sharedDiv = document.getElementById('sharedUsers');
  sharedDiv.innerHTML = '';
  (note.shared || []).forEach((item, idx) => {
    const div = document.createElement('div');
    div.className = 'share-item';
    div.innerHTML = `
      <span>
        <b>${item.email}</b>
        <span class="badge bg-${item.permission === 'edit' ? 'success' : 'secondary'} ms-2">
          ${item.permission === 'edit' ? 'Chỉnh sửa' : 'Chỉ đọc'}
        </span>
      </span>
      <span class="share-actions">
        <button class="btn btn-sm btn-outline-primary" onclick="editShare(${idx})">Sửa quyền</button>
        <button class="btn btn-sm btn-outline-danger" onclick="removeShare(${idx})">Thu hồi</button>
      </span>
    `;
    sharedDiv.appendChild(div);
  });
}

document.getElementById('shareForm').onsubmit = function(e) {
  e.preventDefault();
  const email = document.getElementById('shareEmail').value.trim();
  const permission = document.getElementById('sharePermission').value;
  if (!email || email === userEmail) {
    alert('Email không hợp lệ hoặc là email của bạn!');
    return;
  }
  note.shared = note.shared || [];
  if (note.shared.some(s => s.email === email)) {
    alert('Email này đã được chia sẻ!');
    return;
  }
  note.shared.push({ email, permission });
  saveAndRender();
  this.reset();
};

window.editShare = function(idx) {
  const newPerm = prompt('Nhập quyền mới (read/edit):', note.shared[idx].permission);
  if (newPerm === 'read' || newPerm === 'edit') {
    note.shared[idx].permission = newPerm;
    saveAndRender();
  } else if (newPerm !== null) {
    alert('Chỉ chấp nhận "read" hoặc "edit".');
  }
};

window.removeShare = function(idx) {
  if (confirm('Thu hồi quyền chia sẻ với người này?')) {
    note.shared.splice(idx, 1);
    saveAndRender();
  }
};

function saveAndRender() {
  localStorage.setItem('notes', JSON.stringify(notes));
  renderSharedUsers();
}
</script>
</body>
</html>