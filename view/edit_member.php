<?php
include_once("control/controlteammember.php");
$p = new cteamMember();

if (!isset($_GET['id'])) {
    die("Thiếu ID thành viên!");
}

$id_member = $_GET['id'];
$tblMember = $p->get01Member($id_member);

if ($tblMember === -2) {
    die("Không thể kết nối cơ sở dữ liệu!");
} elseif ($tblMember === -1) {
    die("Không tìm thấy thành viên!");
}

$member = $tblMember->fetch_assoc();    

// Khi người dùng bấm nút "Cập nhật"
if (isset($_POST['btnsave'])) {
    $FullName = $_POST['FullName'];
    $position = $_POST['position'];
    $age = $_POST['age'];
    $status = $_POST['status'];
    $roleInTeam = $_POST['roleInTeam'];
    $phone = $_POST['phone'];
    $id_team = $member['id_team'];
    $id_player = $_POST['id_player'];

    $result = $p->edit01TeamMember($id_member,$FullName, $position, $age, $phone, $status, $roleInTeam, $id_team, $id_player);

    if ($result) {
        echo "<script>
            alert('Cập nhật thành công!');
            window.location.href='dashboard.php?page=dash_team_member&id=$id_team';
        </script>";
        ;
    } else {
        echo "<script>alert('Cập nhật thất bại!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa thành viên</title>
    <style>
        .container {
            width: 500px;
            margin: 50px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px 30px;
        }

        h1 {
            text-align: center;
            color: #0078ff;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        input, select {
            width: 100%;
            padding: 8px;
            margin-top: 4px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn-group {
            text-align: center;
            margin-top: 20px;
        }

        .btn-group input {
            padding: 10px 20px;
            margin: 0 5px;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-save {
            background-color: #0078ff;
        }

        .btn-cancel {
            background-color: #aaa;
        }

        .btn-group input:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Chỉnh sửa thành viên</h1>
    <form action="" method="post">
        <label>Tên cầu thủ:</label>
        <input type="text" name="FullName" value="<?= htmlspecialchars($member['FullName']) ?>" required>

        <label>Vị trí chơi:</label>
        <input type="text" name="position" value="<?= htmlspecialchars($member['position']) ?>" required>

        <label>Tuổi:</label>
        <input type="number" name="age" value="<?= htmlspecialchars($member['age']) ?>" required>

        <label>Chức vụ trong đội:</label>
        <select name="roleInTeam">
            <option value="Thành viên" <?= ($member['roleInTeam']=='Cầu thủ'?'selected':'') ?>>Thành viên</option>
            <option value="Đội trưởng" <?= ($member['roleInTeam']=='Đội trưởng'?'selected':'') ?>>Đội trưởng</option>
        </select>
        <label>Số điện thoại:</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($member['phone']) ?>" required>
        <label>Tình trạng:</label>
        <select name="status" value="<?= htmlspecialchars($member['status']) ?>">
            <option value="Action" <?= ($member['status']=='Đang thi đấu'?'selected':'') ?>>Hoạt động</option>
            <option value="Shutdown" <?= ($member['status']=='Dự bị'?'selected':'') ?>>Tạm nghỉ</option>
        </select>

        <div class="btn-group">
            <input type="submit" name="btnsave" value="Lưu" class="btn-save">
            <input type="button" value="Hủy" class="btn-cancel" onclick="window.location.href='dashboard.php?page=dash_team_member&id=<?= $member['id_team'] ?>'">
        </div>
    </form>
</div>

</body>
</html>