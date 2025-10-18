<?php
$page = $_GET['page'] ?? '';
$isLoginPage = ($page === 'login');
?>
<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.3/font/bootstrap-icons.min.css">
    <title>Trang chủ</title>
    <link rel="stylesheet" href="css/style_index.css?v=9.0">
</head>
<body class="<?= $isLoginPage ? 'login-page' : '' ?>">
    <header> 
    <img src="img/banner.jpg" alt="" width="100%" height="150px">
    </header>
    <!-- <nav>
        <img class="logo" src="img/logo.png" alt="" width="100px" height="100px"> 
        <h2>TOUNAPRO</h2>
        <ul>
            <li><a href="index.php">Trang chủ</a></li>
            <li><a href="tourna-follow.php">Giải đang theo dõi</a></li>
            <li><a href="?page=team">Đội bóng</a></li>    
            <li><a href="?page=about">Về chúng tôi</a></li>
            <li><a href="contact.php">Liên hệ</a></li>
            <li><a href="news.php">Tin tức</a></li>
            <li><a href="?page=login">Đăng nhập</a></li>
        </ul>
        
    </nav> -->
    <?php include_once('view/partials/nav.php'); ?>
    <section class="hero-section">
    <div class="hero-content">
        <h1>HỆ THỐNG QUẢN LÝ GIẢI ĐẤU CHUYÊN NGHIỆP</h1>
        <form action="index.php" method="get">
            <?php if (isset($_REQUEST["page"])) { ?>
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>">
            <?php } ?>
            <input type="text" name="keyword" placeholder="Nhập từ khóa..." size="150"> 
            <button type="submit" name="btnSearch"><i class="fa fa-search"></i></button>
        </form>
    </div>
</section>
<article class="container my-5">
  <div class="row g-4">
        <?php
                if(isset($_REQUEST["page"])){
                    $p = $_REQUEST["page"];
                    switch($p){
                        case 'team': include_once("view/teams.php"); break;
                        case 'login': include_once("view/login.php"); break;
                        case 'register': include_once("view/register.php"); break;
                        case 'about': include_once("view/about.php"); break;
                        case 'detail_team' : include_once("view/team_detail.php"); break; 
                        case 'detail_tourna' : include_once("view/tourna_detail.php"); break;
                        
                    }
                }else{
                    include_once("view/tournaments_list.php");
                }
            ?>
  </div>
</article>
<footer class="bg-dark text-white pt-5 pb-3">
  <div class="container">
    <div class="row">
    
      <div class="col-md-4 mb-4">
        <img src="img/logo.png" alt="TOUNAPRO" class="img-fluid mb-2" style="max-width:130px;">
        <p class="big mb-0">
          TOURNAPRO — Hệ thống quản lý giải đấu chuyên nghiệp. Cập nhật lịch thi đấu, bảng xếp hạng và quản lý đội bóng.
        </p>
      </div>

      
      <div class="col-md-3 mb-4">
        <h6 class="fw-bold">Liên kết</h6>
        <ul>
        <li><a href="about.php" >Về chúng tôi</a></li>  <br>
        <li><a href="contact.php" >Liên hệ</a></li>
        <li><a href="terms.php" >Điều khoản sử dụng</a></li>
        <li><a href="privacy.php" >Chính sách bảo mật</a></li>
        </ul>
      </div>

      <!-- contact -->
      <div class="col-md-3 mb-4">
        
        <p>Địa chỉ: 12 Nguyễn Văn Bảo, Phường 1, Gò Vấp, Hồ Chí Minh </p>
        <p>Email: <a href="congbang180703@gmail.com" >congbang180703@gmail.com</a></p>
        <p>Hotline: <span class="fw-bold">0376 583 553 </span></p>
      </div>

    <div class="row">
      <div class="col-12">
        <p class="mb-2 mb-md-0 ">©2025 TOURNAPRO. All rights reserved.</p>

        <div class="d-flex gap-2">
          <a class="btn btn-outline-light btn-sm" href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a class="btn btn-outline-light btn-sm" href="#" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
          <a class="btn btn-outline-light btn-sm" href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
        </div>
      </div>
    </div>
  </div>
</footer>

</body>

</html>
<?php
