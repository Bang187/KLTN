<?php
// view/tourna_detail.php
error_reporting(0);
include_once("control/controltourna.php");

$id = $_REQUEST["id"] ?? null;
if (!$id) { echo "<p>Thiếu tham số id</p>"; exit; }

$c = new cTourna();

$tbl = method_exists($c, 'getTournamentDetails') ? $c->getTournamentDetails($id) : -1;

if ($tbl == -1 || $tbl == -2 || !$tbl || $tbl->num_rows == 0) {
  echo "<p>Không tìm thấy giải đấu</p>";
  exit;
}

$row = $tbl->fetch_assoc();

// Base path tính theo file đang chạy (view/tourna_detail.php)
$BASE = rtrim(dirname($_SERVER['PHP_SELF']), '/');  

// ----- Lấy dữ liệu an toàn & format -----
$title   = $row['tournaName'] ?? ($row['name'] ?? 'Giải đấu');
$start   = !empty($row['startdate']) ? date('d-m-Y', strtotime($row['startdate'])) : '';
$end     = !empty($row['enddate'])   ? date('d-m-Y', strtotime($row['enddate']))   : '';
$status  = $row['status']   ?? '';           // ví dụ: Sắp diễn ra/Đang diễn ra/Đã kết thúc
$location= $row['location'] ?? ($row['address'] ?? '');
$teamCnt = $row['team_count'] ?? ($row['teamCount'] ?? '');
$desc    = $row['description'] ?? ($row['note'] ?? '');

$rawBanner = trim($row['banner'] ?? '');
$rawLogo   = trim($row['logo']   ?? '');

// Banner
$bannerSrc = $rawBanner === ''
  ? "$BASE/../img/giaidau/banner_macdinh.jpg"
  : (preg_match('~^(https?://|/)~i', $rawBanner) ? $rawBanner
     : (str_starts_with($rawBanner, 'img/') ? "$BASE/../$rawBanner" : "$BASE/../img/giaidau/$rawBanner"));

// Logo
$logoSrc = $rawLogo === ''
  ? "$BASE/../img/giaidau/logo_macdinh.png"
  : (preg_match('~^(https?://|/)~i', $rawLogo) ? $rawLogo
     : (str_starts_with($rawLogo, 'img/') ? "$BASE/../$rawLogo" : "$BASE/../img/giaidau/$rawLogo"));

// Chuỗi ngày
$dateText = $start ? ('Từ ' . $start . ($end ? ' đến ' . $end : '')) : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Bạn có thể tạo file riêng: ../css/style_tourna_detail.css và bỏ style inline này đi -->
  <style>
    :root { --radius: 20px; }
    *{ box-sizing:border-box }
    body{ margin:0; font-family:system-ui, -apple-system, Segoe UI, Roboto, Arial; color:#222; background:#f5f7fb }
    .hero-header{
      position:relative; min-height:320px; display:flex; align-items:flex-end; padding:24px;
      background:#111 url('<?= htmlspecialchars($bannerSrc) ?>') center/cover no-repeat;
      border-bottom-left-radius: var(--radius); border-bottom-right-radius: var(--radius);
      overflow:hidden;
    }
    .hero-header::after{
      content:""; position:absolute; inset:0; background:linear-gradient(180deg,rgba(0,0,0,.25),rgba(0,0,0,.65));
    }
    .hero-inner{ position:relative; z-index:2; display:flex; gap:16px; align-items:center; width:100%; }
    /* .logo{
      width:90px; height:90px; border-radius:50%; overflow:hidden; flex:0 0 90px; border:3px solid rgba(255,255,255,.8);
      background:#fff;
    } */
    .logo img{ width:100%; height:100%; object-fit:cover }
    .title-wrap{ color:#fff; flex:1 }
    .title-wrap h1{ margin:0 0 6px; font-size:28px; letter-spacing:.2px }
    .title-wrap .meta{ opacity:.95; font-size:14px; display:flex; gap:12px; flex-wrap:wrap }
    .badge{
      display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,.12); color:#fff;
      padding:6px 10px; border-radius:999px; border:1px solid rgba(255,255,255,.25);
    }
    .page-actions{ display:flex; gap:10px }
    .btn{
      display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:12px; border:none; cursor:pointer;
      background:#ffd400; color:#222; font-weight:600; text-decoration:none;
    }
    .btn.secondary{ background:#ffffffd9 }
    .container{ max-width:1100px; margin:28px auto; padding:0 16px }
    .grid{
      display:grid; grid-template-columns:repeat(12,1fr); gap:16px;
    }
    .card{
      background:#fff; border-radius:16px; box-shadow:0 6px 18px rgba(14,30,37,.06);
      padding:18px;
    }
    .section-title{ margin:0 0 12px; font-size:18px }
    .info-list{ display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px }
    .info-item{ display:flex; gap:10px; align-items:flex-start; padding:10px; border:1px dashed #e9edf3; border-radius:12px; background:#fafbfd }
    .info-item i{ opacity:.8; margin-top:2px }
    .desc{ white-space:pre-line; line-height:1.6; color:#444 }
    @media (max-width: 768px){
      .info-list{ grid-template-columns:1fr }
      .hero-header{ min-height:260px }
      .title-wrap h1{ font-size:22px }
    }
  </style>
</head>
<body>

  <!-- HERO -->
  <header class="hero-header">
    <div class="hero-inner">
      <div class="logo">
        <img src="<?= htmlspecialchars($logoSrc) ?>" alt="logo"
             onerror="this.onerror=null;this.src='<?= $BASE ?>/../img/giaidau/logo_macdinh.png';">
      </div>
      <div class="title-wrap">
        <h1><?= htmlspecialchars(mb_strtoupper($title)) ?></h1>
        <div class="meta">
          <?php if ($dateText): ?>
            <span class="badge"><i class="fa fa-calendar"></i> <?= htmlspecialchars($dateText) ?></span>
          <?php endif; ?>
          <?php if ($status): ?>
            <span class="badge"><i class="fa fa-circle-dot"></i> <?= htmlspecialchars($status) ?></span>
          <?php endif; ?>
          <?php if ($teamCnt !== '' && $teamCnt !== null): ?>
            <span class="badge"><i class="fa fa-users"></i> <?= (int)$teamCnt ?> đội</span>
          <?php endif; ?>
          <?php if ($location): ?>
            <span class="badge"><i class="fa fa-location-dot"></i> <?= htmlspecialchars($location) ?></span>
          <?php endif; ?>
        </div>
      </div>
      <div class="page-actions">
        <!-- Tuỳ chỉnh đường dẫn theo phân quyền/quy trình của bạn -->
        <a class="btn" href="#"><i class="fa fa-plus"></i> ĐĂNG KÝ ĐỘI</a>
        <a class="btn secondary" href="../index.php?page=tourna"><i class="fa fa-arrow-left"></i> QUAY LẠI</a>
      </div>
    </div>
  </header>

  <!-- NỘI DUNG -->
  <main class="container">
    <div class="grid">
      <!-- Cột trái -->
      <section class="card" style="grid-column: span 8;">
        <h2 class="section-title">Giới thiệu / Thông tin giải</h2>
        <?php if ($desc): ?>
          <div class="desc"><?= nl2br(htmlspecialchars($desc)) ?></div>
        <?php else: ?>
          <div class="desc">Chưa có mô tả cho giải đấu này.</div>
        <?php endif; ?>
      </section>

      <!-- Cột phải -->
      <aside class="card" style="grid-column: span 4;">
        <h2 class="section-title">Thông tin nhanh</h2>
        <div class="info-list">
          <?php if ($dateText): ?>
          <div class="info-item"><i class="fa fa-calendar-days"></i>
            <div><strong>Thời gian</strong><br><?= htmlspecialchars($dateText) ?></div>
          </div>
          <?php endif; ?>

          <?php if ($status): ?>
          <div class="info-item"><i class="fa fa-flag-checkered"></i>
            <div><strong>Trạng thái</strong><br><?= htmlspecialchars($status) ?></div>
          </div>
          <?php endif; ?>

          <?php if ($location): ?>
          <div class="info-item"><i class="fa fa-location-dot"></i>
            <div><strong>Địa điểm</strong><br><?= htmlspecialchars($location) ?></div>
          </div>
          <?php endif; ?>

          <?php if ($teamCnt !== '' && $teamCnt !== null): ?>
          <div class="info-item"><i class="fa fa-people-group"></i>
            <div><strong>Số đội dự kiến</strong><br><?= (int)$teamCnt ?> đội</div>
          </div>
          <?php endif; ?>
        </div>
      </aside>
    </div>

    <!-- (Tuỳ chọn) Khu vực luật lệ/cách tính điểm nếu CSDL có -->
    <?php if (!empty($row['rule_type']) || !empty($row['point_win']) || !empty($row['point_draw']) || !empty($row['point_loss'])): ?>
    <section class="card" style="margin-top:16px;">
      <h2 class="section-title">Luật thi đấu</h2>
      <div class="info-list" style="grid-template-columns: repeat(3, minmax(0,1fr));">
        <?php if (!empty($row['rule_type'])): ?>
          <div class="info-item"><i class="fa fa-chess"></i>
            <div><strong>Thể thức</strong><br><?= htmlspecialchars($row['rule_type']) ?></div>
          </div>
        <?php endif; ?>
        <?php if (isset($row['point_win'])): ?>
          <div class="info-item"><i class="fa fa-trophy"></i>
            <div><strong>Thắng</strong><br><?= (int)$row['point_win'] ?> điểm</div>
          </div>
        <?php endif; ?>
        <?php if (isset($row['point_draw'])): ?>
          <div class="info-item"><i class="fa fa-scale-balanced"></i>
            <div><strong>Hoà</strong><br><?= (int)$row['point_draw'] ?> điểm</div>
          </div>
        <?php endif; ?>
        <?php if (isset($row['point_loss'])): ?>
          <div class="info-item"><i class="fa fa-circle-xmark"></i>
            <div><strong>Thua</strong><br><?= (int)$row['point_loss'] ?> điểm</div>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php endif; ?>
  </main>

</body>
</html>
