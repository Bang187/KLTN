<?php
$loggedIn = !empty($_SESSION['login']); // chỉ cần true/false
$username = $_SESSION['username'] ?? '';
?>
<nav class="topnav">
  <img class="logo" src="img/logo.png" alt="" width="100" height="100">
  <h2>TOURNAPRO</h2>
  <ul>
    <li><a href="index.php">Trang chủ</a></li>
    <li><a href="index.php?page=tournaments_list">Giải đang theo dõi</a></li>
    <li><a href="index.php?page=team">Đội bóng</a></li>
    <li><a href="?page=about">Về chúng tôi</a></li>
    <li><a href="contact.php">Liên hệ</a></li>
    <li><a href="news.php">Tin tức</a></li>

    <?php if ($loggedIn): ?>
      <li>
        <a href="dashboard.php">
          <span style="font-size:14px;opacity:.9">Chào mừng, <?= htmlspecialchars($username) ?> quay lại</span>
        </a>
      </li>
    <?php else: ?>
      <li><a href="index.php?page=login">Đăng nhập</a></li>
    <?php endif; ?>
  </ul>
</nav>
