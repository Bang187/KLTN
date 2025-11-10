<?php
error_reporting(0);
include_once("control/controluser.php");
$p = new cUser();
$id = $_SESSION["id_user"] ?? null;

if (!$id) {
    echo "<p style='color:red;'>Thiếu ID người dùng!</p>";
    exit;
}

$tblUser = $p->get01User($id);
if ($tblUser && $tblUser != -1 && $tblUser != -2) {
    $row = $tblUser->fetch_assoc();
} else {
    echo "<p style='color:red;'>Không tìm thấy thông tin tài khoản!</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đổi mật khẩu</title>
<style>
body {
  font-family: 'Segoe UI', sans-serif;
  background: #f9fafb;
  margin: 0;
  padding: 0;
}

.account-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
  padding: 40px 0;
}

.account-card {
  background: #fff;
  border-radius: 16px;
  padding: 30px 40px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  width: 100%;
  max-width: 550px;
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
  gap: 18px;
}

.account-form label {
  font-weight: 600;
  color: #374151;
  margin-bottom: 5px;
}

.account-form input {
  width: 100%;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid #d1d5db;
  background: #f9fafb;
  transition: all 0.2s ease;
}

.account-form input:focus {
  border-color: #2563eb;
  background: #fff;
  outline: none;
  box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}

.btn-group {
  display: flex;
  justify-content: flex-end;
  margin-top: 10px;
}

.btn-save {
  background: #2563eb;
  color: #fff;
  padding: 10px 22px;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
}
.btn-save:hover { background: #1d4ed8; }

@media (max-width: 600px) {
  .account-card {
    padding: 25px 20px;
  }
}
</style>
</head>
<body>

<section class="account-container">

  <div class="account-card">
    <h2>Đổi mật khẩu</h2>
    <form class="account-form" method="post">
        <label for="oldpass">Mật khẩu hiện tại</label>
        <input type="password" name="oldpass" required>

        <label for="newpass">Mật khẩu mới</label>
        <input type="password" name="newpass" required>

        <label for="confirm">Xác nhận mật khẩu mới</label>
        <input type="password" name="confirm" required>

        <div class="btn-group">
            <button type="submit" name="btn-save" class="btn-save">Lưu thay đổi</button>
        </div>
    </form>
  </div>
</section>

<?php
if (isset($_POST["btn-save"])) {
    $oldpass = $_POST["oldpass"];
    $newpass = $_POST["newpass"];
    $confirm = $_POST["confirm"];

    // Lấy mật khẩu cũ từ DB
    $hash = $row['password'];

    if (md5($oldpass) !== $hash) {
        echo "<script>alert('Mật khẩu hiện tại không đúng!');</script>";
    } elseif ($newpass !== $confirm) {
        echo "<script>alert('Mật khẩu mới và xác nhận không khớp!');</script>";
    } else {
        $newhash = md5($newpass);
        $kq = $p->updatePassword($id, $newhash);

        if ($kq) {
            echo "<script>alert('Đổi mật khẩu thành công!'); window.location='dashboard.php?page=change_password';</script>";
            exit();
        } else {
            echo "<script>alert('Đổi mật khẩu thất bại!');</script>";
        }
    }
}
?>

</body>
</html>