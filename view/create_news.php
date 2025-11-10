<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm bản tin</title>
    <style>
/* Khung chính */
#khung {
    background: #fff;
    width: 520px;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    padding: 30px 40px;
    animation: fadeIn 0.4s ease;
    margin: 60px auto;             /* 👈 Căn giữa ngang */
}

/* Hiệu ứng mở */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Tiêu đề */
#tieude {
    text-align: center;
    font-size: 24px;
    margin-bottom: 25px;
    color: #333;
    letter-spacing: 1px;
}

/* Bảng nội dung */
table {
    width: 100%;
}

td {
    padding: 12px 0;
}

/* Nhãn */
label {
    font-weight: 600;
    color: #444;
    display: block;
    margin-bottom: 6px;
}

/* Input, textarea, file */
input[type="text"],
textarea,
input[type="file"] {
    width: 100%;
    padding: 10px 12px;
    border: 1.5px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    transition: all 0.25s ease;
}

input[type="text"]:focus,
textarea:focus,
input[type="file"]:focus {
    border-color: #4a90e2;
    outline: none;
    box-shadow: 0 0 6px rgba(74, 144, 226, 0.3);
}

/* Textarea */
textarea {
    resize: vertical;
    min-height: 100px;
}

/* Nút thêm */

#btnthem:hover {
    background: linear-gradient(135deg, #357abd, #2c5fa6);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Responsive */
@media (max-width: 600px) {
    #khung {
        width: 90%;
        padding: 25px;
    }

    #tieude {
        font-size: 22px;
    }
}
/*quay lại */
.btn-back {
    background-color: gray;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: 0.3s;
}

.btn-back:hover {
    background-color: black;
}
/*Nút save và reset */
.btn-group button {
    background: #3498db;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 15px;
    transition: 0.3s;
    margin-right: 10px;
    display: inline-flex;
    align-items: center;
    gap: 8px; /* khoảng cách giữa icon và chữ */
}

.btn-group button[type="reset"] {
    background: #e74c3c;
}

.btn-group button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.btn-group button[type="submit"]:hover {
    background: #2980b9;
}

.btn-group button[type="reset"]:hover {
    background: #c0392b;
}

.btn-group i {
    font-size: 16px;
}
/* Gom 2 nút lại cùng hàng */
.btn-group {
    display: flex;
    justify-content: center;   /* Căn giữa ngang */
    gap: 12px;                 /* Khoảng cách giữa 2 nút */
    margin-top: 15px;
}

/* Đảm bảo nút trông đồng đều */
.btn-group button {
    flex: 0 0 auto; /* Giữ kích thước tự nhiên, không kéo giãn */
}
    </style>
</head>
<body>
    <div id="khung">
    <form action="" method="post" enctype="multipart/form-data">
            <table>
                <button type="button" onclick="history.back()" class="btn-back">
            <i class="fa fa-arrow-left"></i> Quay lại
        </button>
                <h2 id="tieude">Bản tin mới</h2>
                    <tr>
                        <td>
                            <label for="title"><b>Tiêu đề:</b></label>
                            <input type="text" name="title" id="title" required>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="content"><b>Nội dung:</b></label>
                            <textarea name="content" id="content" required></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="img_news"><b>Hình ảnh:</b></label>
                            <input type="file" name="img_news" id="img_news" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="btn-group">
                            <button type="submit" name="btnthem" id="btnthem" title="Cập nhật">
                                <i class="fa fa-save"></i> Thêm mới
                            </button>
                            <button type="reset" name="btnnhaplai" id="btnnhaplai" title="Nhập lại">
                                <i class="fa fa-undo"></i> Nhập lại
                            </button>
                        </td>
                    </tr>
            </table>
    </form>
</div>
    <?php
        if(isset($_REQUEST["btnthem"])){
            include_once("control/controlnews.php");
            $p = new cnews();
            $title = $_REQUEST["title"];
            $content = $_REQUEST["content"];
            $img_news = $_FILES["img_news"];
            $kq = $p->addNews($title, $content, $img_news);
            if($kq){
                echo "<script>
                        alert('Thêm bản tin thành công!');
                        window.location.href = 'admin.php?page=manage_news';
                    </script>";
            }else{
                echo "<script>alert('Thêm bản tin Thất bại!')</script>";
            }
        }
    ?>
</body>
</html>