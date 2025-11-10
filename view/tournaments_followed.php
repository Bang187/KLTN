<?php
include_once('control/controltourna.php');
include_once('control/controlfollowtourna.php');

$controller = new cTourna();
$followCtrl = new cFollow();

// ⚠️ Hiển thị thông báo flash (nếu có)
if (isset($_SESSION['flash_message'])) {
    echo "<script>alert('" . $_SESSION['flash_message'] . "');</script>";
    unset($_SESSION['flash_message']);
}

// ✅ Xử lý nút Theo dõi / Hủy theo dõi
if (isset($_POST['action']) && $_POST['action'] === 'follow' && isset($_POST['idtourna'])) {
    if (!isset($_SESSION['id_user'])) {
        $_SESSION['flash_message'] = "Vui lòng đăng nhập để theo dõi giải đấu!";
    } else {
        $id_user = $_SESSION['id_user'];
        $id_tourna = (int)$_POST['idtourna'];
        $res = $followCtrl->toggleFollow($id_user, $id_tourna);
        if ($res === true) {
            $_SESSION['flash_message'] = "Đã theo dõi giải đấu này!";
        } elseif ($res === 'unfollowed') {
            $_SESSION['flash_message'] = "Đã bỏ theo dõi giải đấu này.";
        } else {
            $_SESSION['flash_message'] = "Có lỗi xảy ra khi thao tác theo dõi.";
        }
    }
    // 🔁 Redirect để tránh lỗi reload POST
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Vui lòng đăng nhập để xem các giải đã theo dõi!');window.location.href='?page=login';</script>";
    exit;
}

// 📋 Lấy danh sách giải mà người dùng đã theo dõi
$tournaments = [];
if (isset($_SESSION['id_user'])) {
    $tournaments = $followCtrl->getFollowedTournaments($_SESSION['id_user']);
}
?>

<div class="row g-4">
<?php if (!empty($tournaments)) {
    $BASE = rtrim(dirname($_SERVER['PHP_SELF']), '/');
    foreach ($tournaments as $row) {
        $id = $row['idtourna'];

        // Kiểm tra đang theo dõi chưa
        $isFollowed = false;
        if (isset($_SESSION['id_user'])) {
            $isFollowed = $followCtrl->isFollowing($_SESSION['id_user'], $id);
        }

        $rawBanner = trim($row['banner'] ?? '');
        $rawLogo   = trim($row['logo'] ?? '');
        $bannerSrc = $rawBanner === ''
            ? "$BASE/img/giaidau/banner_macdinh.jpg"
            : (preg_match('~^(https?://|/)~i', $rawBanner) ? $rawBanner
               : (str_starts_with($rawBanner, 'img/') ? "$BASE/$rawBanner" : "$BASE/img/giaidau/$rawBanner"));

        $logoSrc = $rawLogo === ''
            ? "$BASE/img/giaidau/logo_macdinh.png"
            : (preg_match('~^(https?://|/)~i', $rawLogo) ? $rawLogo
               : (str_starts_with($rawLogo, 'img/') ? "$BASE/$rawLogo" : "$BASE/img/giaidau/$rawLogo"));

        $title = htmlspecialchars($row['tournaName']);
        $start = !empty($row['startdate']) ? date('d-m-Y', strtotime($row['startdate'])) : '';
        $end   = !empty($row['enddate'])   ? date('d-m-Y', strtotime($row['enddate']))   : '';
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
                    <?php if ($dateText): ?>
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
    <?php } 
} else { ?>
    <div class="col-12">
        <p class="text-center text-muted">Bạn chưa theo dõi giải đấu nào.</p>
    </div>
<?php } ?>
</div>