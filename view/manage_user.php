<?php
error_reporting(E_ALL); 
include_once("control/controluser.php");
$p = new cUser();
$id = $_REQUEST["id"] ?? ($_SESSION["id_user"] ?? null);

if ($id) {
    $tblUser = $p->get01User($id);
    if ($tblUser && $tblUser != -1 && $tblUser != -2) {
        $row = $tblUser->fetch_assoc();
    } else {
        echo "<p style='color:red;'>Không tìm thấy thông tin tài khoản!</p>";
        exit;
    }
} else {
    echo "<p style='color:red;'>Thiếu ID người dùng!</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản lý tài khoản</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
  font-family: 'Segoe UI', sans-serif;
  background: #f9fafb;
  margin: 0;
  padding: 0;
}

/* --- Bố cục 2 cột --- */
.account-container {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  gap: 30px;
  padding: 50px 20px;
  flex-wrap: wrap;
}

/* --- Thông tin bên trái --- */
.account-card {
  background: #fff;
  border-radius: 16px;
  padding: 30px 40px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  width: 100%;
  max-width: 500px;
}

.account-card h2 {
  text-align: center;
  color: #1f2937;
  font-size: 22px;
  margin-bottom: 20px;
  border-bottom: 2px solid #2563eb;
  padding-bottom: 8px;
}

.account-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
}

.account-form label {
  flex: 0 0 140px;
  font-weight: 600;
  color: #374151;
  text-align: right;
}

.account-form input {
  flex: 1;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid #d1d5db;
  background: #f9fafb;
  transition: all 0.2s ease;
}

input[readonly] {
  background-color: #f3f4f6;
  color: #555;
  cursor: not-allowed;
}

.btn-group {
  display: flex;
  justify-content: flex-end;
  margin-top: 10px;
}

.btn-save {
  background: #2563eb;
  color: #fff;
  padding: 10px 20px;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
}
.btn-save:hover { background: #1d4ed8; }

/* --- Avatar bên phải --- */
.account-avatar {
  background: #fff;
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  width: 100%;
  max-width: 300px;
  text-align: center;
}

.avatar-img {
  width: 180px;
  height: 180px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #2563eb;
  margin-bottom: 20px;
}

.avatar-input {
  margin-bottom: 10px;
  display: block;
  width: 100%;
}

@media (max-width: 768px) {
  .account-container {
    flex-direction: column;
    align-items: center;
  }
  .account-form label {
    text-align: left;
  }
}

/* --- Modal popup --- */
.modal {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.4);
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.modal-content {
  background: #fff;
  padding: 25px 30px;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 4px 25px rgba(0,0,0,0.2);
  animation: fadeIn 0.25s ease;
}

.modal-content h3 {
  text-align: center;
  color: #1f2937;
  margin-bottom: 20px;
}

.modal-content input {
  width: 100%;
  padding: 10px;
  margin-top: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
}

.modal-btns {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.btn-close {
  background: #9ca3af;
  color: #fff;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
}
.btn-close:hover { background: #6b7280; }

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

</style>
</head>
<body>

<section class="account-container">

  <!-- Bên trái: Thông tin cá nhân -->
  <div class="account-card">
    <h2>Thông tin cá nhân</h2>
    <form class="account-form">
      <div class="form-row">
        <label>Họ và tên</label>
        <input type="text" value="<?= htmlspecialchars($row['FullName']) ?>" readonly>
      </div>

      <div class="form-row">
        <label>Email</label>
        <input type="email" value="<?= htmlspecialchars($row['email']) ?>" readonly>
      </div>

      <div class="form-row">
        <label>Tên đăng nhập</label>
        <input type="text" value="<?= htmlspecialchars($row['username']) ?>" readonly>
      </div>

      <div class="form-row">
        <label>Số điện thoại</label>
        <input type="tel" value="<?= htmlspecialchars($row['phone'] ?? '') ?>" readonly>
      </div>

      <div class="form-row">
        <label>Ngày tạo</label>
        <input type="text" 
            value="<?= isset($row['created_at']) ? date('d/m/Y', strtotime($row['created_at'])) : 'N/A' ?>" 
            readonly>
      </div>

      <div class="btn-group">
        <button type="button" class="btn-save" onclick="openModal()">Sửa thông tin</button>
      </div>
    </form>
  </div>

  <!-- Bên phải: ảnh đại diện -->
  <div class="account-avatar">
    <h2>Ảnh đại diện</h2>
    <img src="<?= !empty($row['avatar']) ? 'img/avatar/' . htmlspecialchars($row['avatar']) : 'img/avatar/default_avaplayer.jpg' ?>" 
         alt="Avatar người dùng"
         class="avatar-img">
    <form method="post" enctype="multipart/form-data">
      <input type="file" name="favatar" accept="image/*" class="avatar-input">
      <button type="submit" name="btn-upload-ava" class="btn-save"><i class="fa-solid fa-floppy-disk"></i></button>
    </form>
  </div>

</section>

<!-- Form popup chỉnh sửa -->
<div class="modal" id="editModal">
  <div class="modal-content">
    <h3>Chỉnh sửa thông tin</h3>
    <form method="post">
        <label for="username">Tên đăng nhập</label>
        <input type="text" name="username" value="<?= htmlspecialchars($row['username']) ?>">

        <label for="fullname">Họ và tên</label>
        <input type="text" name="fullname" value="<?= htmlspecialchars($row['FullName']) ?>">

        <label for="email">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>">

        <label for="phone">Số điện thoại</label>
        <input type="tel" name="phone" value="<?= htmlspecialchars($row['phone'] ?? '') ?>">

        <div class="modal-btns">
            <button type="button" class="btn-close" onclick="closeModal()">Hủy</button>
            <button type="submit" name="btn-save" class="btn-save">Lưu thay đổi</button>
        </div>
    </form>
  </div>
</div>
<?php
        if(isset($_REQUEST["btn-upload-ava"])){
            $avatar = $row['avatar'];
            $fileavatar = $_FILES["favatar"];
            $kq = $p->uploadImageAva($id, $fileavatar, $avatar);
            if($kq){
                echo "<script>alert('Cập nhật thành công!'); window.location='dashboard.php?page=man_user';</script>";
                exit();
            }else{
                echo "<script>alert('Cập nhật thất bại!')</script>";
            }
        }
    ?>
<?php
        if (isset($_POST["btn-save"])) {
            $username = $_POST["username"];
            $fullname = $_POST["fullname"];
            $email = $_POST["email"];
            $phone = $_POST["phone"];
            
            $kq = $p->editUser($id, $username, $fullname, $email, $phone);
            if ($kq) {
                echo "<script>alert('Cập nhật thành công!'); window.location='dashboard.php?page=man_user';</script>";
                exit();
            } else {
                echo "<script>alert('Cập nhật thất bại!');</script>";
            }
        }
    ?>
<script>
function openModal() {
  document.getElementById('editModal').style.display = 'flex';
}
function closeModal() {
  document.getElementById('editModal').style.display = 'none';
}
</script>

</body>
</html>