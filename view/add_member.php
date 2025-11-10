<?php
error_reporting(0);
if (empty($_GET["id_team"])) {
    echo "Thiếu mã đội bóng!";
    exit;
} 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm thành viên</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* ======= Form tìm kiếm ======= */
        .search-box {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 60%;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            margin: 0 auto 30px auto; /* 🟢 Thêm dòng này để căn giữa ngang */
        }

        .search-box input[type="text"] {
            width: 80%;
            padding: 10px 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-box input[type="text"]:focus {
            border-color: #28a745;
            box-shadow: 0 0 6px rgba(40,167,69,0.3);
        }

        .search-box button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-box button:hover {
            background-color: #218838;
            transform: scale(1.05);
        }

        /* ======= Form hiển thị người dùng ======= */
        form.user-info {
            background: #fff;
            border-radius: 12px;
            padding: 20px 30px;
            width: 60%;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: all 0.3s ease;
            margin: 0 auto 20px auto; /* 🟢 Thêm dòng này để căn giữa ngang */
        }

        form.user-info:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        }

        form.user-info table {
            width: 100%;
        }

        td {
            padding: 8px 5px;
            vertical-align: middle;
        }

        label {
            font-weight: 600;
            color: #333;
        }

        input[type="text"] {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background: #f9f9f9;
            outline: none;
            font-size: 15px;
        }

        input[type="text"]:focus {
            border-color: #28a745;
            background: #fff;
        }

        /* ======= Nút thêm thành viên ======= */
        .add-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #28a745;
            color: white;
            border: none;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .add-btn:hover {
            background-color: #218838;
            transform: scale(1.1);
        }

        /* Thông báo rỗng */
        .no-result {
            font-size: 16px;
            color: #777;
            margin-top: 10px;
        }
        /* ======= Nút quay lại ======= */
        .back-btn i {
            font-size: 18px;
            color: white;
        }
        .back-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            background-color: #5a6268;
            transform: translateX(-3px);
        }
    </style>
</head>
<body class="body_add_member">
    <div class="back-btn-container">
        <a href="?page=dash_team_member&id=<?php echo $_GET['id_team']; ?>" class="back-btn">
            <i class="fas fa-rotate-left"></i> Quay lại
        </a>
    </div>
    <!-- FORM TÌM KIẾM -->
    <form action="dashboard.php" method="get" class="search-box">
        <?php if (isset($_REQUEST["page"])) { ?>
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>">
        <?php } ?>
        <input type="hidden" name="id_team" value="<?php echo $_GET['id_team']; ?>">
        <input type="text" name="sdt" placeholder="🔍 Nhập số điện thoại để tìm thành viên...">
        <button type="submit" name="btnSearch">Tìm</button>
    </form>

    <?php
    include_once("control/controluser.php");
    $p = new cUser();
    $tblUser = null;
    if (isset($_REQUEST["btnSearch"])) {
        $tblUser = $p->getUserByPhone($_REQUEST["sdt"]);
    }

    if ($tblUser === null) {
        // chưa tìm kiếm
    } elseif ($tblUser == -2) {
        echo "<div class='no-result'>Không thể kết nối tới cơ sở dữ liệu!</div>";
    } elseif ($tblUser == -1) {
        echo "<div class='no-result'>Không có người dùng nào phù hợp!</div>";
    } else {
        while ($row = $tblUser->fetch_assoc()) {
            echo "<form method='get' action='dashboard.php' class='user-info'>";
            echo "<input type='hidden' name='page' value='add_member'>";
            echo "<input type='hidden' name='id_user' value='".$row["id_user"]."'>";
            echo "<input type='hidden' name='id_team' value='" . $_GET["id_team"] . "'>";
            echo "<table>
                    <tr>
                        <td><label>Họ và tên:</label></td>
                        <td><input type='text' value='".$row["FullName"]."' readonly></td>
                    </tr>
                    <tr>
                        <td><label>Số điện thoại:</label></td>
                        <td><input type='text' value='".$row["phone"]."' readonly></td>
                    </tr>
                    <tr>
                        <td><label>Email:</label></td>
                        <td><input type='text' value='".$row["email"]."' readonly></td>
                    </tr>
                    <tr>
                        <td colspan='2' style='text-align:center;'>
                            <button type='submit' name='btnAdd' class='add-btn'>+</button>
                        </td>
                    </tr>
                  </table>";
            echo "</form>";
        }
    }

    if (isset($_GET['btnAdd'])) { 
        include_once("control/controlteammember.php"); 
        $team = new cteamMember(); 
        $id_user = $_GET['id_user']; 
        $id_team = $_GET['id_team']; 
        $kq = $team->addMember($id_user, $id_team);
        if ($kq) { 
            echo "<script>alert('Thêm thành công!');</script>"; 
            header("refresh:0;url='dashboard.php?page=dash_team_member&id=" . $id_team . "'");
        } else { 
            echo "<script>alert('Thêm thất bại hoặc người này đã là thành viên!');</script>"; 
        } 
    }
    ?>
</body>
</html>