<?php
session_start();
if ($_SESSION['login'] !== true || $_SESSION['ID_role']  != 1) {
    echo "<script>alert('Bạn cần đăng nhập để truy cập trang này!');</script>";
    echo "<script>window.location.href = 'index.php?page=login';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    :root{
      --bg:#f6f7fb;
      --card:#ffffff;
      --text:#111827;
      --muted:#6b7280;
      --primary:#2563eb;
      --sidebar:#0f172a;      
      --sidebar-tex:#e5e7eb;
      --sidebar-w:260px;
      --radius:14px;
      --shadow:0 10px 30px rgba(0,0,0,.08);
    }
    html,body{height:100%}
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,Arial,sans-serif;background:var(--bg);color:var(--text)}


    .layout{
      display:grid;
      grid-template-columns: var(--sidebar-w) 1fr;
      grid-template-rows: 64px 1fr;
      grid-template-areas:
        "sidebar topbar"
        "sidebar content";
      min-height:100vh;
    }


    .sidebar{
      grid-area:sidebar;
      background:var(--sidebar);
      color:var(--sidebar-tex);
      padding:16px 12px;
      position:sticky; top:0; height:100vh;
    }
    .brand{
      display:flex; align-items:center; gap:10px;
      padding:10px 12px; margin-bottom:12px;
      font-weight:800; letter-spacing:.4px; color:#fff;
    }
    .brand i{font-size:20px}
    .menu{list-style:none; margin-top:8px}
    .menu li{margin:4px 0}
    .menu a{
      display:flex; align-items:center; gap:12px;
      text-decoration:none; color:var(--sidebar-tex);
      padding:12px 12px; border-radius:10px; font-weight:600;
      transition:background .15s ease, transform .04s ease;
    }
    .menu a:hover{background:rgba(255,255,255,.08); transform:translateX(2px)}
    .menu a.active{background:rgba(37,99,235,.25)}
    .menu i{width:20px; text-align:center}


    .topbar{
      grid-area:topbar;
      background:var(--card);
      box-shadow:var(--shadow);
      display:flex; align-items:center; justify-content:space-between;
      padding:0 16px; z-index:2;
    }
    .topbar .left{display:flex; align-items:center; gap:10px}
    .hamburger{display:none; border:0; background:transparent; font-size:20px}
    .search input{
      border:1px solid #e5e7eb; border-radius:10px; padding:8px 12px; width:260px;
      outline:none;
    }
    .user{
      display:flex; align-items:center; gap:10px;
      color:var(--muted); font-weight:600;
    }
    .avatar{
      width:34px; height:34px; border-radius:50%;
      background:#dbeafe; display:inline-block;
    }

    .content{
      grid-area:content;
      padding:20px;
    }
    .breadcrumbs{
      color:var(--muted); font-size:14px; margin-bottom:12px;
    }
    .grid{
      display:grid; gap:16px;
      grid-template-columns: repeat(12, minmax(0,1fr));
    }
    .card{
      grid-column: span 4 / span 4;
      background:var(--card); border-radius:var(--radius); box-shadow:var(--shadow);
      padding:16px;
    }
    .card h3{font-size:16px; margin-bottom:6px}
    .stat{font-size:28px; font-weight:800}
    .muted{color:var(--muted); font-size:12px}

    .panel{
      grid-column: span 12 / span 12;
      background:var(--card); border-radius:var(--radius); box-shadow:var(--shadow);
      padding:18px; margin-top:10px;
    }

    /* ========= Responsive ========= */
    @media (max-width: 1000px){
      .card{grid-column: span 6 / span 6;}
      .search input{width:200px}
    }
    @media (max-width: 780px){
      .layout{
        grid-template-columns: 1fr;
        grid-template-rows: 56px auto;
        grid-template-areas:
          "topbar"
          "content";
      }
      .sidebar{position:fixed; inset:0 auto 0 0; width:var(--sidebar-w); transform:translateX(-100%); transition:transform .2s ease; z-index:3}
      .sidebar.open{transform:translateX(0)}
      .hamburger{display:inline-block; color:var(--text)}
      .card{grid-column: span 12 / span 12;}
    }
    .btn-primary{
      background:var(--primary); color:#fff; border:0; padding:10px 14px; border-radius:10px; cursor:pointer;
    }
  </style>
</head>
<body>

  <div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="brand"><i class="fa-solid fa-shield-halved"></i> Admin</div>
      <ul class="menu">
        <li><a href="manage_accounts.php" class="active"><i class="fa-solid fa-user-gear"></i> Quản lý tài khoản</a></li>
        <li><a href="manage_news.php"><i class="fa-solid fa-newspaper"></i> Quản lý tin tức</a></li>
        <li><a href="analytics.php"><i class="fa-solid fa-chart-column"></i> Thống kê</a></li>
        <li><a href="view/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a></li>
      </ul>
    </aside>

    <!-- Topbar -->
    <header class="topbar">
      <div class="left">
        <button class="hamburger" id="btnToggle"><i class="fa-solid fa-bars"></i></button>
        <strong>Dashboard</strong>
      </div>
      <div class="right" style="display:flex;align-items:center;gap:16px;">
        <div class="search"><input type="text" placeholder="Tìm nhanh..."></div>
        <?php
            if (isset($_SESSION['username'])) {
                echo '<div class="user"><span class="avatar"></span> ' . htmlspecialchars($_SESSION['username']) . '</div>';
            }
        ?>
        
      </div>
    </header>

    <!-- Content -->
    <main class="content">
      <div class="breadcrumbs">Trang chủ / Dashboard</div>

      <!-- Thống kê nhanh -->
      <section class="grid">
        <div class="card">
          <h3>Tài khoản</h3>
          <div class="stat">100</div>
          <div class="muted">Tổng số người dùng</div>
        </div>
        <div class="card">
          <h3>Tin tức</h3>
          <div class="stat">3</div>
          <div class="muted">Bài viết hiện có</div>
        </div>
        <div class="card">
          <h3>Lượt truy cập</h3>
          <div class="stat">12.4K</div>
          <div class="muted">Trong 30 ngày</div>
        </div>
        <div class="card">
          <h3>Tổng số đội</h3>
          <div class="stat">30</div>
          <div class="muted">Đội bóng</div>

        </div>
        <div class="card">
          <h3>Tổng Giải đấu</h3>
          <div class="stat">10</div>
          <div class="muted">Giải đấu</div>
        
        </div>
      </section>

      <!-- Khu vực nội dung chính -->
      <section class="panel">
        <h3 style="margin-bottom:10px;">Nội dung</h3>
        <p class="muted">abc</p>
        <div style="margin-top:14px;">
          <a href="manage_accounts.php" class="btn-primary"><i class="fa-solid fa-user-gear"></i> Đi đến Quản lý tài khoản</a>
        </div>
      </section>
    </main>
  </div>


</body>
</html>
