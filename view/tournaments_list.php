
    <?php
//
    if (isset($_SESSION['flash_message'])) {
        echo "<script>alert('" . $_SESSION['flash_message'] . "');</script>";
        unset($_SESSION['flash_message']);
    }//
    include_once('control/controltourna.php');
    include_once('control/controlfollowtourna.php');//
    
    $controller = new cTourna();
    $followCtrl = new cFollow();//

    // Nếu người dùng bấm nút Theo dõi
    if (isset($_POST['action']) && $_POST['action'] === 'follow' && isset($_POST['idtourna'])) {
        if (!isset($_SESSION['id_user'])) {
            echo "<script>alert('Vui lòng đăng nhập để theo dõi giải đấu!');</script>";
        } else {
            $id_user = $_SESSION['id_user'];
            $id_tourna = (int)$_POST['idtourna'];
            $res = $followCtrl->toggleFollow($id_user, $id_tourna);
            if ($res === true) {
                echo "<script>alert('Đã theo dõi giải đấu này!');</script>";
            } elseif ($res === 'unfollowed') {
                echo "<script>alert('Đã bỏ theo dõi giải đấu này.');</script>";
            } else {
                echo "<script>alert('Có lỗi xảy ra khi theo dõi.');</script>";
            }
            // 🔁 Sau khi xử lý, redirect lại chính trang
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
        }
    }
    if(isset($_REQUEST["btnSearch"])){
      $kq = $controller->showTournamentByName($_REQUEST["keyword"]);
    }else{
      $kq = $controller->showAllTournaments();
    }
    $BASE = rtrim(dirname($_SERVER['PHP_SELF']), '/');

    // nếu $kq là mysqli_result như trước
    if ($kq && $kq->num_rows > 0) {
        while ($row = $kq->fetch_assoc()) {
    $id = $row['idtourna'];
    //follow
    $isFollowed = false;
    if (isset($_SESSION['id_user'])) {
        $isFollowed = $followCtrl->isFollowing($_SESSION['id_user'], $id);
    }//
    $rawBanner = trim($row['banner'] ?? '');
    $rawLogo   = trim($row['logo']   ?? '');
    $bannerSrc = $rawBanner === ''
        ? "$BASE/img/giaidau/banner_macdinh.jpg"
        : (preg_match('~^(https?://|/)~i', $rawBanner) ? $rawBanner
           : (str_starts_with($rawBanner, 'img/') ? "$BASE/$rawBanner" : "$BASE/img/giaidau/$rawBanner"));

    $logoSrc = $rawLogo === ''
        ? "$BASE/img/giaidau/logo_macdinh.png"
        : (preg_match('~^(https?://|/)~i', $rawLogo) ? $rawLogo
           : (str_starts_with($rawLogo, 'img/') ? "$BASE/$rawLogo" : "$BASE/img/giaidau/$rawLogo"));

    $title  = !empty($row['tournaName']) ? $row['tournaName'] : (!empty($row['name']) ? $row['name'] : 'Không tên');
    $start  = !empty($row['startdate']) ? date('d-m-Y', strtotime($row['startdate'])) : '';
    $end    = !empty($row['enddate'])   ? date('d-m-Y', strtotime($row['enddate']))   : '';
    $dateText = $start ? ('Từ ' . $start . ($end ? ' đến ' . $end : '')) : '';

    
            ?>
            <div class="col-lg-3 col-md-6">
              <div class="t-card card h-100">
                <div class="card-banner">
                  <a href="view/tourna_detail.php?id=<?= urlencode($id) ?>">
                    <img src="<?= htmlspecialchars($bannerSrc) ?>" alt="banner"
                        onerror="this.onerror=null;this.src='<?= $BASE ?>/img/giaidau/banner_macdinh.jpg';">
                  </a>
                  <div class="logo-circle">
                    <img src="<?= htmlspecialchars($logoSrc) ?>" alt="logo"
                    onerror="this.onerror=null;this.src='<?= $BASE ?>/img/giaidau/logo_macdinh.png';">
                  </div>
                </div>

                <div class="card-body">
                  <div class="card-title"><?= $title ?></div>
                  <?php if($dateText): ?>
                    <div class="card-meta"><i class="bi bi-calendar3"></i> <?= htmlspecialchars($dateText) ?></div>
                  <?php endif; ?>
                </div>

                <div class="card-footer">
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="idtourna" value="<?= $id ?>">
                    <input type="hidden" name="action" value="follow">
                    <button type="submit" class="btn btn-follow <?= $isFollowed ? 'btn-danger' : 'btn-primary' ?>">
                        <?= $isFollowed ? 'Hủy theo dõi' : 'Theo dõi' ?>
                    </button>
                  </form>
                </div>
              </div>
            </div>
        <?php
        } // end while
    } else {
        echo '<div class="col-12"><p class="text-center text-muted">Không có giải đấu để hiển thị.</p></div>';
    }
    ?>
