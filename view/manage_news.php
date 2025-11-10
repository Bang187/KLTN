<?php
error_reporting(0);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tin tức</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
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
        /* Thumbnail phóng to ảnh*/
        .thumb {
            width: 100px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .thumb:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        /* Modal nền mờ */
        .image-modal {
            display: none; /* ẩn mặc định */
            position: fixed;
            z-index: 999;
            padding-top: 60px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.8);
        }

        /* Ảnh trong modal */
        .image-modal .modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(255,255,255,0.2);
            animation: zoomIn 0.3s ease;
        }

        /* Hiệu ứng zoom */
        @keyframes zoomIn {
            from { transform: scale(0.7); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        /* Nút đóng */
        .image-modal .close-btn {
            position: absolute;
            top: 25px;
            right: 35px;
            color: #fff;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .image-modal .close-btn:hover {
            color: #ff4d4f;
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

<h1>Quản lý tin tức</h1>
<div class="header-actions">
  <a href="admin.php?page=create_news" class="btn-add">
    <i class="fa fa-plus"></i> Thêm bản tin
  </a>
</div>
<?php
include_once("control/controlnews.php");
$p = new cnews();
if(isset($_REQUEST["btnSearch"])){
    $tblnews = $p->getNewsByName($_REQUEST["keyword"]);
}else{
    $tblnews = $p->getAllNews();
}
$dem = 1;
if($tblnews == -2){
    echo "Lỗi kết nối!";
} elseif($tblnews == -1){
    echo "Không có tin tức";
} else {
    echo "<table>
    <tr>
        <th>STT</th>
        <th>Tiêu đề</th>
        <th>Nội dung</th>
        <th>Ngày tạo</th>
        <th>Hình ảnh</th>
        <th>Thao tác</th>
    </tr>";
    while($row = $tblnews->fetch_assoc()){
        echo "<tr>";
        echo "<td>".$dem."</td>";
        echo "<td>".$row['title']."</td>";
        $content = strip_tags($row['content']);
        if (strlen($content) > 200) {
            $content = substr($content, 0, 200) . '...';
        }
        echo "<td>".$content."</td>";
        echo "<td>" . date("d/m/Y", strtotime($row['create_at'])) . "</td>";
        echo '<td>
        <img src="img/news/'.$row['img_news'].'" alt="Hình ảnh" 
                class="thumb" onclick="openImageModal(\'img/news/'.$row['img_news'].'\')">
        </td>';
        echo '<td>
            <a href="?page=edit_news&id=' . $row['id_news'] . '" title="Sửa">
                <i class="fa fa-edit"></i>
            </a>
            <a href="?page=delete_news&id=' . $row['id_news'] . '" 
               onclick="return confirm(\'Bạn có chắc muốn xóa sản phẩm này không?\');" title="Xóa">
                <i class="fa fa-trash"></i>
            </a>
        </td>';
        $dem++;
    }
    echo "</table>";
}
?>
<!-- Modal phóng to ảnh -->
<div id="imageModal" class="image-modal">
    <span class="close-btn" onclick="closeImageModal()">&times;</span>
    <img class="modal-content" id="modalImage">
</div>

</body>
<script>
function openImageModal(src) {
    const modal = document.getElementById("imageModal");
    const modalImg = document.getElementById("modalImage");
    modal.style.display = "block";
    modalImg.src = src;
}

function closeImageModal() {
    document.getElementById("imageModal").style.display = "none";
}

// Đóng modal khi click ngoài ảnh
window.onclick = function(event) {
    const modal = document.getElementById("imageModal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
}
</script>
</html>