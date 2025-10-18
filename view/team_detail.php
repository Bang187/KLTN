
<?php
error_reporting(0);
include_once("../control/controlteam.php");
$p = new cteam();
$id = $_REQUEST["id"];
$tblTeam = $p->getTeamDetails($id);

if ($tblTeam == -1 || $tblTeam == -2) {
    echo "<p>Không tìm thấy đội bóng</p>";
    exit;
}

$teamName = "";
$logo = "";
$manager_name = "";
$manager_email = "";
$members = [];

while ($row = $tblTeam->fetch_assoc()) {
    $teamName = $row["teamName"];
    $logo = $row["logo"];
    $manager_name = $row["manager_name"];
    $manager_email = $row["manager_email"];

    if (!empty($row["id_player"])) {
        $members[] = [
            "name" => $row["player_name"],
            "position" => $row["position"],
            "role" => $row["roleInTeam"],
            "age" => $row["age"],
            "status" => $row["status"],
            "ava" => $row["avatar"] ?: 'default.jpg'
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Team Detail</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../css/style_team_detail.css?v=1.0">
</head>
<body>
  <!-- HEADER -->
  <header class="hero-header">
    <nav class="navbar">
      <div class="logo d-flex align-items-center gap-2">
        <img src="../img/doibong/<?php echo $logo; ?>" alt="<?php echo $teamName; ?>" 
            style="height:60px; width:60px; border-radius:50%; object-fit:cover;">
    <!--   <span style="font-weight:700; font-size:20px; color:white;"><?php echo strtoupper($teamName); ?></span>-->
        </div>
    <!--  <ul class="nav-links">
        <li><a href="index.php">HOME</a></li>
        <li><a href="#">MATCHES</a></li>
        <li><a href="#">PLAYERS</a></li>
        <li><a href="#">BLOG</a></li>
        <li><a href="#">CONTACT</a></li>
      </ul>-->
    </nav>

    <div class="hero-content">
      <h1><?php echo strtoupper($teamName); ?></h1>
      <div class="buttons">
        <a href="#" class="btn btn-primary">THÊM THÀNH VIÊN</a>
        <a href="../index.php?page=team" class="btn btn-secondary">QUAY LẠI</a>
      </div>
    </div>
  </header>
<!-- THÔNG TIN QUẢN LÝ -->
  <section class="team-info-section">
    <div class="container">
      <div class="info-cards">
        <div class="info-card">
          <i class="fa fa-id-card fa-2x"></i>
          <p>Quản lý: <span class="highlight"><?php echo $manager_name; ?></span></p>
        </div>
        <div class="info-card">
          <i class="fa fa-envelope fa-2x"></i>
          <p>Email: <span class="highlight"><?php echo $manager_email; ?></span></p>
        </div>
      </div>
      <div class="join-btn">
        <a href="#" class="btn-join">GIA NHẬP ĐỘI</a>
      </div>
    </div>
  </section>

<!-- DANH SÁCH CẦU THỦ -->
<section class="member-section">
  <h2>Danh sách thành viên</h2>

  <div class="carousel-container">
    <button class="nav-btn prev-btn">&#10094;</button>
    <div class="member-list" id="memberList">
      <?php foreach ($members as $m) { ?>
      <div class="member-card">
        <img src="../img/default_avaplayer.jpg">
        <div class="member-info">
          <h3><?php echo htmlspecialchars($m['name']); ?></h3>
          <p><strong>Vị trí:</strong> <?php echo htmlspecialchars($m['position']); ?></p>
          <p><strong>Tuổi:</strong> <?php echo htmlspecialchars($m['age']); ?></p>
          <p><strong>Vai trò:</strong> <span class="role"><?php echo htmlspecialchars($m['role']); ?></span></p>
          <p><strong>Trạng thái:</strong>
            <span class="status <?php echo ($m['status'] == 'Hoạt động') ? 'active' : 'inactive'; ?>">
              <?php echo htmlspecialchars($m['status']); ?>
            </span>
          </p>
        </div>
      </div>
      <?php } ?>
    </div>
    <button class="nav-btn next-btn">&#10095;</button>
  </div>
</section>
<script>
  const list = document.getElementById('memberList');
  const next = document.querySelector('.next-btn');
  const prev = document.querySelector('.prev-btn');

  let scrollPosition = 0;
  const cardWidth = list.querySelector('.member-card').offsetWidth + 20; // width + margin

  next.addEventListener('click', () => {
    if (scrollPosition < list.scrollWidth - list.clientWidth) {
      scrollPosition += cardWidth * 4;
      list.style.transform = `translateX(-${scrollPosition}px)`;
    }
  });

  prev.addEventListener('click', () => {
    if (scrollPosition > 0) {
      scrollPosition -= cardWidth * 4;
      list.style.transform = `translateX(-${scrollPosition}px)`;
    }
  });
</script>
</body>
</html>