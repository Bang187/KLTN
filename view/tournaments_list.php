
    <?php
    include_once('control/controltourna.php');
    
    $controller = new cTourna();
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
                  <a href="<?=$BASE?>/index.php?page=detail_tourna&id=<?=$id?>">
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
                  <a href="#" class="btn btn-follow">Theo dõi</a>
                </div>
              </div>
            </div>
        <?php
        } // end while
    } else {
        echo '<div class="col-12"><p class="text-center text-muted">Không có giải đấu để hiển thị.</p></div>';
    }
    ?>
