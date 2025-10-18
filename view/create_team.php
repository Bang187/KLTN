<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once("control/controlteam.php");

$p = new cteam();

if (isset($_POST["btnsave"])) {
    $teamName = $_POST["tendoi"];
    $logo = $_FILES["logo"];
    $id_user = $_SESSION['id_user'] ?? 0; // id của người quản lý đang đăng nhập

    // Gọi hàm thêm đội trong controlteam
    $result = $p->addTeam($teamName, $logo, $id_user);

    if ($result) {
        echo "<script>alert('Thêm đội bóng thành công!');</script>";
        echo "<script>window.location.href = 'dashboard.php?page=man_team';</script>";
    } else {
        echo "<script>alert('Thêm đội bóng thất bại!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thêm đội bóng</title>
  <style>

    .form_addteam {
      width: 600px;
      margin: 0 auto;
      background: white;
      /* border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
      padding: 30px; */
    }

    h1 {
      text-align: center;
      color: #333;
      margin-bottom: 30px;
    }

    table {
      width: 100%;
    }

    label {
      display: inline-block;
      width: 140px;
      font-weight: bold;
      color: #444;
      margin-bottom: 8px;
    }

    input[type="text"], input[type="file"] {
      width: calc(100% - 150px);
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    .manager-info {
      background: #f1f3f6;
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 25px;
    }

    .manager-info p {
      margin: 5px 0;
      color: #333;
    }

    .btn-group {
      text-align: center;
      margin-top: 20px;
    }

    input[type="submit"], input[type="reset"] {
      background: #007bff;
      border: none;
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      margin: 0 10px;
    }

    input[type="reset"] {
      background: #6c757d;
    }

    input[type="submit"]:hover {
      background: #0056b3;
    }

    input[type="reset"]:hover {
      background: #5a6268;
    }
  </style>
</head>
<body>

  <div class="form_addteam">
    <h1>Thêm đội bóng mới</h1>
    <!-- FORM THÊM ĐỘI -->
    <form action="" method="post" enctype="multipart/form-data">
      <table>
        <tr>
          <td><label for="tendoi">Tên đội bóng:</label></td>
          <td><input type="text" name="tendoi" id="tendoi" required></td>
        </tr>
        <tr>
          <td><label for="logo">Logo đội:</label></td>
          <td><input type="file" name="logo" id="logo" accept="image/*" required></td>
        </tr>
      </table>

      <div class="btn-group">
        <input type="submit" value="Lưu đội" name="btnsave">
        <input type="reset" value="Nhập lại">
      </div>
    </form>
  </div>

</body>
</html>