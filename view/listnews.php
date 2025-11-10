<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Danh sách bản tin</title>
  <style>

    .titlenews {
      text-align: center;
      margin-bottom: 30px;
      color: #222;
      font-size: 28px;
      font-weight: 600;
    }

    .news-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 25px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .news-card {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      transition: all 0.3s ease;
      text-align: center;
      padding-bottom: 15px;
    }

    .news-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .news-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }

    .news-title {
      font-size: 18px;
      font-weight: bold;
      color: #333;
      margin: 15px 10px 5px;
      line-height: 1.4;
    }

    .news-date {
      color: #777;
      font-size: 14px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <h2 class="titlenews">📰 Danh sách bản tin</h2>

  <div class="news-container">
    <?php
      include_once("control/controlnews.php");
      $p = new cnews();
      if(isset($_REQUEST["btnSearch"])){
      $tblnews = $p->getNewsByName($_REQUEST["keyword"]);
      }else{
      $tblnews = $p->getAllNews();
      }
      if ($tblnews && $tblnews instanceof mysqli_result) {
          while ($row = $tblnews->fetch_assoc()) {
              echo '<a href="index.php?page=view_news_detail&id=' . $row['id_news'] . '" class="news-link">';
              echo '<div class="news-card">';
              echo '<div class="news-title">'.$row['title'].'</div>';
              echo '<img src="img/news/'.$row['img_news'].'" alt="Hình ảnh">';
              echo '<div class="news-date">'.date("d/m/Y", strtotime($row['create_at'])).'</div>';
              echo '</div>';
              echo '</a>';
          }
      } else {
          echo "<p style='text-align:center;color:red;'>Không có bản tin này!</p>";
      }
    ?>
  </div>
</body>
</html>