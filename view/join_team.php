<?php
session_start();
include_once("../control/controljointeam.php");

if (!isset($_SESSION['id_user'])) {
    echo "<script>
            alert('Vui lòng đăng nhập trước khi gia nhập đội!');
            window.location.href='../index.php?page=login';
          </script>";
    exit;
}

$id_team = $_GET['id_team'] ?? null;
if (!$id_team) {
    echo "<script>alert('Thiếu thông tin đội bóng!'); history.back();</script>";
    exit;
}

// Xử lý khi bấm nút GỬI YÊU CẦU
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = $_SESSION['id_user'];
    $message = $_POST['message'] ?? null;

    $p = new cJoinTeam();
    $result = $p->cSendJoinRequest($id_team, $id_user, $message);

    switch ($result) {
        case 1:
            echo "<script>alert('Yêu cầu gia nhập đội đã được gửi đến quản lý!'); 
                  window.location.href='../index.php?page=team';</script>";
            break;
        case -1:
            echo "<script>alert('Bạn đã gửi yêu cầu trước đó, vui lòng chờ duyệt!'); history.back();</script>";
            break;
        case -3:
            echo "<script>alert('Bạn đã là thành viên của đội này, không thể gửi yêu cầu nữa!'); history.back();</script>";
            break;
        case -4:
            echo "<script>alert('Bạn hiện đang thuộc một đội khác, không thể gia nhập thêm đội!'); history.back();</script>";
            break;
        case 0:
            echo "<script>alert('Có lỗi khi gửi yêu cầu, vui lòng thử lại!'); history.back();</script>";
            break;
        default:
            echo "<script>alert('Lỗi kết nối cơ sở dữ liệu!'); history.back();</script>";
            break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Gửi yêu cầu gia nhập đội</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #1d3557, #457b9d);
      color: white;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .join-box {
      background: rgba(255, 255, 255, 0.1);
      padding: 30px;
      border-radius: 15px;
      text-align: center;
      width: 400px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    .join-box h2 {
      margin-bottom: 20px;
    }
    textarea {
      width: 100%;
      height: 100px;
      border: none;
      border-radius: 10px;
      padding: 10px;
      resize: none;
      font-size: 15px;
    }
    button {
      margin-top: 20px;
      background: #f4a261;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 10px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }
    button:hover {
      background: #e76f51;
    }
    a {
      color: #f1faee;
      text-decoration: none;
      display: inline-block;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="join-box">
    <h2>Gửi yêu cầu gia nhập đội</h2>
    <form method="POST">
      <textarea name="message" placeholder="Nhập ghi chú (nếu có)..."></textarea><br>
      <button type="submit">GỬI YÊU CẦU</button>
    </form>
    <a href="../index.php?page=team"><i class="fa fa-arrow-left"></i> Quay lại</a>
  </div>
</body>
</html>