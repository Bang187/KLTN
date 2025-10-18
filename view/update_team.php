<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once("control/controlteam.php");
$p = new cteam();

$idteam = $_REQUEST["id"];
$tblTeam = $p->get01Team($idteam);

// --- JOIN users để lấy thêm thông tin quản lý ---
include_once("model/modelconnect.php");
$conn = (new mconnect())->moketnoi();
$sql = "SELECT t.teamName, t.logo, t.id_user, u.FullName, u.email, u.phone 
        FROM team t 
        JOIN users u ON t.id_user = u.id_user 
        WHERE t.id_team = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idteam);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  $ten = $row["teamName"];
  $logo = $row["logo"];
  $id_user = $row["id_user"];
  $fullname = $row["FullName"];
  $email = $row["email"];
  $phone = $row["phone"];
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cấu hình đội</title>
    <style>.container {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  padding: 40px;
  margin: 40px auto;
  max-width: 900px;
  gap: 40px;
}

/* ============================= */
/* 🔹 FORM BÊN TRÁI */
.form_addteam {
  flex: 1;
}

.form_addteam h1 {
  text-align: center;
  margin-bottom: 25px;
  font-size: 26px;
  color: #333;
  font-weight: 700;
}

.form_addteam table {
  width: 100%;
  border-collapse: collapse;
}

.form_addteam td {
  padding: 10px 0;
  vertical-align: middle;
}

.form_addteam label {
  font-weight: 600;
  color: #444;
  display: inline-block;
  min-width: 150px;
}

.form_addteam input[type="text"],
.form_addteam input[type="email"],
.form_addteam input[type="tel"],
.form_addteam input[type="file"] {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 10px;
  outline: none;
  transition: border 0.3s, box-shadow 0.3s;
  font-size: 15px;
}

.form_addteam input:focus {
  border-color: #007bff;
  box-shadow: 0 0 4px rgba(0, 123, 255, 0.3);
}

.form_addteam input[readonly] {
  background-color: #f9f9f9;
  color: #555;
  cursor: not-allowed;
}

/* ============================= */
/* 🔹 NÚT */
.btn-group {
  text-align: center;
  margin-top: 25px;
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

/* ============================= */
/* 🔹 HÌNH BÊN PHẢI */
.form_right {
  flex: 0 0 35%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.form_right img {
  max-width: 100%;
  max-height: 300px;
  border-radius: 12px;
  object-fit: cover;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  border: 2px solid #eee;
}

/* ============================= */
/* 🔹 RESPONSIVE */
@media (max-width: 768px) {
  .container {
    flex-direction: column;
    align-items: center;
    padding: 25px;
  }

  .form_addteam, .form_right {
    width: 100%;
  }

  .form_right {
    margin-top: 25px;
  }

  .form_addteam label {
    min-width: 120px;
  }
}
        .nav{display:flex;gap:10px;padding:8px;background:#f3f3f3;border:1px solid #ddd}
        .nav a{text-decoration:none;color:#333;padding:6px 10px;border:1px solid #ccc;border-radius:4px}
        .nav a.active{background:#2563eb;color:#fff;border-color:#2563eb}
    </style>
</head>
<body>
    <div class="nav">
        <a href="dashboard.php?page=update_team&id=<?php echo $idteam;?>">Cấu hình</a>
        <a href="dashboard.php?page=dash_team_member&id=<?php echo $idteam;?>">Thành viên</a>

    </div>
    <div class="container">
    <div class="form_addteam">
      <h1>Thông tin đội bóng</h1>
      <form action="" method="post" enctype="multipart/form-data">
        <table>
          <tr>
            <td><label for="tendoimoi">Tên đội bóng:</label></td>
            <td><input type="text" name="tendoimoi" id="tendoimoi" value="<?= htmlspecialchars($ten) ?>" required></td>
          </tr>
          <tr>
            <td><label for="logomoi">Logo đội:</label></td>
            <td><input type="file" name="logomoi" id="logomoi" accept="image/*"></td>
          </tr>
          <tr>
            <td><label for="fullname">Tên quản lý:</label></td>
            <td><input type="text" id="fullname" value="<?= htmlspecialchars($fullname) ?>" readonly></td>
          </tr>
          <tr>
            <td><label for="email">Email:</label></td>
            <td><input type="email" id="email" value="<?= htmlspecialchars($email) ?>" readonly></td>
          </tr>
          <tr>
            <td><label for="phone">Số điện thoại:</label></td>
            <td><input type="tel" id="phone" value="<?= htmlspecialchars($phone) ?>" readonly></td>
          </tr>
        </table>

        <div class="btn-group">
          <input type="submit" value="Lưu đội" name="btnsave">
          <input type="reset" value="Nhập lại">
        </div>
      </form>
    </div>
    <div class="form_right">
      <img src="img/doibong/<?= htmlspecialchars($logo) ?>" alt="Logo đội bóng">
    </div>
  </div>

  <?php
  if (isset($_REQUEST["btnsave"])) {
      include_once("control/controlteam.php");
      $tendoimoi = $_REQUEST["tendoimoi"];
      $logomoi = $_FILES["logomoi"];
      $p = new cteam();
      $tblSP = $p->edit01Team($idteam, $tendoimoi, $logomoi, $logo, $id_user);
      if ($tblSP) {
          echo "<script>alert('Cập nhật thành công!')</script>";
          header("refresh:0;url='dashboard.php?page=man_team'");
      } else {
          echo "<script>alert('Cập nhật thất bại!')</script>";
      }
  }
  ?>
</body>
</html>