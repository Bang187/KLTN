<style>
.news-detail {
    max-width: 850px;
    background: #fff;
    margin: 0 auto;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    padding: 40px 50px;
    animation: fadeIn 0.5s ease;
    min-height: auto;
}

/* Hiệu ứng xuất hiện */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Tiêu đề */
.news-detail>h1 {
    font-size: 28px;
    color: #333;
    text-align: center;
    margin-bottom: 25px;
    line-height: 1.3;
}

/* Ảnh chính */
.news-detail img {
    display: block;
    width: 100%;
    max-height: 420px;
    object-fit: cover;
    border-radius: 12px;
    margin: 0 auto 25px auto;
}

/* Ngày đăng */
.news-detail .date {
    text-align: right;
    color: #777;
    font-style: italic;
    font-size: 15px;
    margin-bottom: 25px;
}

/* Nội dung tin */
.news-detail .content {
    font-size: 17px;
    color: #444;
    line-height: 1.7;
    text-align: justify;
    white-space: pre-wrap; /* Giữ nguyên xuống dòng & khoảng cách như trong CSDL */
}

/* Nút quay lại */
.btn-back {
    display: inline-flex;
    align-items: center;
    background: linear-gradient(135deg, #6c757d, #495057);
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 15px;
    margin-top: 30px;
    transition: all 0.3s ease;
}

.btn-back:hover {
    background: linear-gradient(135deg, #495057, #343a40);
    transform: translateY(-2px);
}

.btn-back::before {
    content: "←";
    margin-right: 8px;
    font-size: 16px;
}
/* Bao bọc riêng phần chi tiết tin tức */
.news-wrapper {
    max-height: 80vh; /* Giới hạn chiều cao tối đa chiếm 80% màn hình */
    overflow-y: auto; /* Tạo thanh cuộn dọc nếu nội dung quá dài */
    padding-right: 10px; /* Để thanh cuộn không đè chữ */
}

/* Ẩn thanh cuộn xấu (tuỳ chọn, cho đẹp hơn trên Chrome) */
.news-wrapper::-webkit-scrollbar {
    width: 8px;
}
.news-wrapper::-webkit-scrollbar-thumb {
    background-color: rgba(150, 150, 150, 0.5);
    border-radius: 4px;
}
.news-wrapper::-webkit-scrollbar-thumb:hover {
    background-color: rgba(120, 120, 120, 0.8);
}
</style>
<?php
include_once("control/controlnews.php");
$p = new cnews();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $news = $p->get01News($id);

    if ($row = $news->fetch_assoc()) {
        echo '<div class="news-wrapper">'; 
        echo '<div class="news-detail">';
        echo '<button onclick="history.back()" class="btn-back">Quay lại</button>';
        echo '<h1>' . $row['title'] . '</h1>';
        echo '<img src="img/news/' . $row['img_news'] . '" alt="Ảnh bản tin">';
        echo '<p class="date">' . date("d/m/Y", strtotime($row['create_at'])) . '</p>';
        echo '<div class="content">' . nl2br($row['content']) . '</div>';
        echo '<button onclick="history.back()" class="btn-back">Quay lại</button>';
        echo '</div>';
        echo '</div>';
    } else {
        echo "<p>Không tìm thấy bản tin.</p>";
    }
}
?>