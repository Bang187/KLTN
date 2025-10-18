<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once("control/controlteam.php");

$p = new cteam();
$idteam = $_REQUEST["id"];
$tblTeam = $p->get01Team($idteam);

if ($row = $tblTeam->fetch_assoc()) {
    $ten = $row["teamName"];
    $logo = $row["logo"];
    $id_user = $row["id_user"];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thêm đội bóng</title>
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: #f8f9fa;
    }

    .container {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      padding: 40px;
      margin: 40px auto;
      max-width: 900px;
      gap: 30px;
    }

    .form_addteam {
      width: 60%;
    }

    .form_addteam h1 {
      text-align: center;
      margin-bottom: 25px;
      font-size: 26px;
      color: #333;
    }

    .form_addteam table {
      width: 100%;
      border-collapse: collapse;
    }

    .form_addteam td {
      padding: 10px 0;
    }

    .form_addteam label {
      font-weight: 600;
      color: #444;
      display: inline-block;
      min-width: 130px;
    }

    .form_addteam input[type="text"],
    .form_addteam input[type="file"] {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 10px;
      outline: none;
      transition: border 0.3s;
    }

    .form_addteam input[type="text"]:focus {
      border-color: #007bff;
    }

    .btn-group {
      text-align: center;
      margin-top: 20px;
    }

    .btn-group input[type="submit"],
    .btn-group input[type="reset"] {
      padding: 10px 25px;
      margin: 0 8px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-group input[type="submit"] {
      background-color: #007bff;
      color: white;
    }

    .btn-group input[type="submit"]:hover {
      background-color: #0056b3;
    }

    .btn-group input[type="reset"] {
      background-color: #e0e0e0;
    }

    .btn-group input[type="reset"]:hover {
      background-color: #c6c6c6;
    }

    .form_right {
      width: 35%;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .form_right img {
      max-width: 100%;
      max-height: 300px;
      border-radius: 12px;
      object-fit: cover;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      border: 2px solid #eee;
    }

    @media (max-width: 768px) {
      .container {
        flex-direction: column;
        align-items: center;
      }

      .form_addteam,
      .form_right {
        width: 100%;
      }

      .form_right {
        margin-top: 25px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="form_addteam">
      <h1>Sửa đội bóng </h1>
      <!-- FORM THÊM ĐỘI -->
      <form action="" method="post" enctype="multipart/form-data">
        <table>
          <tr>
            <td><label for="tendoimoi">Tên đội bóng:</label></td>
            <td><input type="text" name="tendoimoi" id="tendoimoi" value="<?php if(isset($ten)) echo $ten;?>" required></td>
          </tr>
          <tr>
            <td><label for="logomoi">Logo đội:</label></td>
            <td><input type="file" name="logomoi" id="logomoi" accept="image/*"></td>
          </tr>
        </table>

        <div class="btn-group">
          <input type="submit" value="Lưu đội" name="btnsave">
          <input type="reset" value="Nhập lại">
        </div>
      </form>
    </div>

    <div class="form_right">
      <img src="img/doibong/<?php if(isset($logo)) echo $logo;?>" alt="Hình ảnh đội bóng">
    </div>
  </div>

  <?php
  if(isset($_REQUEST["btnsave"])) {
      include_once("control/controlteam.php");
      $tendoimoi = $_REQUEST["tendoimoi"];
      $logomoi = $_FILES["logomoi"];
      $p = new cteam();
      $tblSP = $p->edit01Team($idteam, $tendoimoi, $logomoi, $logo, $id_user);

      if($tblSP){
          echo "<script>alert('Cập nhật thành công!')</script>";
          header("refresh:0;url='dashboard.php?page=man_team'");
      } else {
          echo "<script>alert('Cập nhật thất bại!')</script>";
      }
  }
  ?>
</body>
</html>