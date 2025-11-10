<?php
error_reporting(0);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        /* --- Bảng danh sách bên trái --- */
.table-container {
    flex: 2;
    background: white;
    border-radius: 12px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    padding: 15px;
}
        h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }

        th, td {
            padding: 14px 18px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f9fbfd;
        }

        tr:hover {
            background-color: #eef6ff;
            transition: 0.2s;
        }

        td img {
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Cột thao tác */
        td a {
            text-decoration: none;
            margin-right: 10px;
            transition: 0.2s;
        }

        td a i {
            font-size: 18px;
            padding: 6px;
            border-radius: 50%;
            transition: 0.3s;
        }

        td a i.fa-edit {
            color: #0d6efd;
            background-color: rgba(13,110,253,0.1);
        }

        td a i.fa-edit:hover {
            background-color: #0d6efd;
            color: white;
            transform: scale(1.1);
        }

        td a i.fa-trash {
            color: #dc3545;
            background-color: rgba(220,53,69,0.1);
        }

        td a i.fa-trash:hover {
            background-color: #dc3545;
            color: white;
            transform: scale(1.1);
        }

        /* Căn giữa nội dung */
        th:nth-child(1), td:nth-child(1),
        th:nth-child(4), td:nth-child(4),
        th:nth-child(5), td:nth-child(5) {
            text-align: center;
        }
        
        /*Thêm bản tin */
        .header-actions {
            display: flex;
            justify-content: flex-end; /* đẩy nút qua bên phải */
            margin: 20px 0;
            }

            .btn-add {
            background: #3498db;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px; /* khoảng cách giữa icon và chữ */
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            }

            .btn-add i {
            font-size: 16px;
            }

            .btn-add:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.25);
            }
    </style>
</head>
<body>

<h1>Quản lý người dùng</h1>

<?php
include_once("control/controluser.php");
$p = new cUser();
if(isset($_REQUEST["btnSearch"])){
    $tblUser = $p->getUserByName($_REQUEST["keyword"]);
}else{
$tblUser = $p->getAllUser();
}
$dem = 1;
if (is_int($tblUser)) {
    if ($tblUser == -2) {
        echo "❌ Lỗi kết nối cơ sở dữ liệu!";
    } elseif ($tblUser == -1) {
        echo "⚠️ Không có người dùng nào!";
    }
} else {
    echo "<table>
    <tr>
        <th>STT</th>
        <th>Họ và Tên</th>
        <th>Tài khoản</th>
        <th>Email</th>
        <th>Số điện thoại</th>
        <th>Ngày tạo</th>
        <th>Vai trò</th>
        <th>Ảnh đại diện</th>
        <th>Thao tác</th>
    </tr>";
    while($row = $tblUser->fetch_assoc()){
        echo "<tr>";
        echo "<td>".$dem."</td>";
        echo "<td>".$row['FullName']."</td>";
        echo "<td>".$row['username']."</td>";
        echo "<td>".$row['email']."</td>";
        echo "<td>".$row['phone']."</td>";
        echo "<td>" . date("d/m/Y", strtotime($row['created_at'])) . "</td>";
        echo "<td>".$row['RoleName']."</td>";
        echo '<td><img src="img/avatar/'.$row['avatar'].'" alt="Hình ảnh" style="width:100px; height=110px; object-fit:cover;"></td>';
        echo '<td>
            <a href="?page=edit_user&id=' . $row['id_user'] . '" title="Sửa">
                <i class="fa fa-edit"></i>
            </a>
            <a href="?page=delete_user&id=' . $row['id_user'] . '" 
               onclick="return confirm(\'Bạn có chắc muốn xóa người dùng này không?\');" title="Xóa">
                <i class="fa fa-trash"></i>
            </a>
        </td>';
        $dem++;
    }
    echo "</table>";
}
?>

</body>
</html>