<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Điều chỉnh tin tức</title>
    <link rel="stylesheet" href="css/styleSuaSP.css">
    <style>
.form-container {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    gap: 40px;
    background: #fff;
    padding: 40px 60px;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    max-width: 1100px;
    margin: 0 auto;
}
form {
    display: flex;
    justify-content: space-between; /* tách 2 phần trái phải */
    align-items: flex-start;
    width: 100%;
}
.form-left {
    flex: 1;
    margin-right: 40px; 
}

.form-right {
    flex: 0 0 350px; 
    width: 380px;
    display: flex;
    justify-content: center;
    align-items: center;
}

#tieude {
    text-align: left;
    font-size: 24px;
    color: #2c3e50;
    margin-bottom: 25px;
}

label {
    display: block;
    font-weight: 600;
    color: #34495e;
    margin-bottom: 6px;
}

input[type="text"],
input[type="file"],
textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    outline: none;
    transition: 0.3s;
    font-size: 15px;
    box-sizing: border-box;
    background: #fafafa;
}

textarea {
    height: 150px;
    resize: vertical;
}

input[type="text"]:focus,
textarea:focus {
    border-color: #3498db;
    background: #fff;
    box-shadow: 0 0 6px rgba(52, 152, 219, 0.3);
}

.btn-group {
    text-align: left;
    padding-top: 20px;
}

input[type="submit"],
input[type="reset"] {
    background: #3498db;
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 15px;
    transition: 0.3s;
    margin-right: 10px;
}

input[type="reset"] {
    background: #e74c3c;
}

input[type="submit"]:hover {
    background: #2980b9;
}

input[type="reset"]:hover {
    background: #c0392b;
}

.product-img {
    width: 100%;
    height: auto;
    max-height: 280px;
    border-radius: 12px;
    object-fit: cover;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    transition: transform 0.3s ease;
}

.product-img:hover {
    transform: scale(1.05);
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
/*Quay lại */
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
    </style>
</head>
<body>
    <?php
        include_once("control/controlnews.php");
        $p = new cnews();
        $id = $_REQUEST["id"];
        $tblnews = $p->get01News($id);
        if($tblnews){
            while($row = $tblnews->fetch_assoc()){
                $title = $row["title"];
                $content = $row["content"];
                $img_news = $row["img_news"];
            }
        }else{
                echo "<script>alert('Mã tin tức không tồn tại!')</script>";
                header("refresh:0;url='admin.php?page=manage_news'");
        }
    ?>
    <div class="form-container">
    <form action="" method="post" enctype="multipart/form-data">
        <div class="form-left">
            <button type="button" onclick="history.back()" class="btn-back">
            <i class="fa fa-arrow-left"></i> Quay lại
        </button>
            <h2 id="tieude">Sửa tin tức</h2>
            <table>
                <tr>
                    <td>
                        <label for="title"><b>Tiêu đề:</b></label>
                        <input type="text" name="title" id="title" value="<?php if(isset($title)) echo $title?>" required>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="content"><b>Nội dung:</b></label>
                        <textarea name="content" id="content" rows="6" required><?php if(isset($content)) echo htmlspecialchars($content); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="img"><b>Hình ảnh mới:</b></label>
                        <input type="file" name="img" id="img_news">
                    </td>
                </tr>
                <tr>
                    <td class="btn-group">
                         <button type="submit" name="btnsua" id="btnsua" title="Cập nhật">
                            <i class="fa fa-save"></i> Cập nhật
                        </button>
                        <button type="reset" name="btnnhaplai" id="btnnhaplai" title="Nhập lại">
                            <i class="fa fa-undo"></i> Nhập lại
                        </button>
                    </td>
                </tr>
            </table>
        </div>
        <div class="form-right">
            <img src="img/news/<?php if(isset($img_news)) echo $img_news?>" alt="Hình ảnh" class="product-img">
        </div>
    </form>
</div>
<?php
        include_once("control/controlnews.php");
        $p = new cnews();
        if(isset($_POST["btnsua"])){
            $title_new = $_POST["title"];
            $content_new = $_POST["content"];
            $img = $_FILES["img"];
            
            $kq = $p->editnews($id, $title_new, $content_new, $img, $img_news);
            if($kq){
                echo "<script>
                    alert('Cập nhật thành công!');
                    window.location.href = 'admin.php?page=manage_news';
                </script>";
                exit();
            }else{
                echo "<script>alert('Cập nhật thất bại!')</script>";
            }
        }
    ?>
</body>
</html>