<?php
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) die('Thiếu id giải');
$idTourna = (int)$_GET['id'];

include_once(__DIR__.'/../model/modeltournateam.php');
include_once(__DIR__.'/../control/controlteam.php');

$mTT = new mtournateam();
$cTeam = new cTeam(); // class controlteam của bạn có getAllTeams(), getTeamByName()

$flash = null;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action = $_POST['action'] ?? '';
    if ($action==='register') {
        $keyword = trim($_POST['team_name'] ?? '');
        if ($keyword==='') {
            $flash = 'Tên đội không được rỗng';
        } else {
            $res = $cTeam->getTeamByName($keyword); // mysqli_result | -1 | -2
            if ($res instanceof mysqli_result && $res->num_rows>0) {
                $row = $res->fetch_assoc();                  // lấy kết quả đầu tiên
                $ok  = $mTT->register($idTourna, (int)$row['id_team']);
                $flash = $ok ? 'Đã thêm vào danh sách chờ duyệt' : 'Đội đã tồn tại trong giải hoặc lỗi CSDL';
            } elseif ($res===-1) {
                $flash = 'Không tìm thấy đội phù hợp';
            } else {
                $flash = 'Lỗi kết nối CSDL';
            }
        }
    } elseif ($action==='setstatus') {
        $ok = $mTT->updateStatus((int)$_POST['id_tournateam'], $_POST['status'] ?? 'pending');
        $flash = $ok ? 'Cập nhật trạng thái thành công' : 'Cập nhật thất bại';
    }
}

// Lấy datalist (toàn bộ đội) & danh sách đã đăng ký
$allTeams = $cTeam->getAllTeams();           // mysqli_result | -1 | -2
$registered = $mTT->listByTournament($idTourna); // mysqli_result | false
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Đội tham gia giải</title>
<style>
body{font-family:Arial,Helvetica,sans-serif}
.wrap{max-width:1100px;margin:16px auto}
.nav{display:flex;gap:10px;padding:8px;background:#f3f3f3;border:1px solid #ddd}
.nav a{text-decoration:none;color:#333;padding:6px 10px;border:1px solid #ccc;border-radius:4px}
.nav a.active{background:#2563eb;color:#fff;border-color:#2563eb}
h2{margin:8px 0 12px}
.flash{padding:10px;margin:10px 0;border:1px solid #ccc;background:#ffffe0}
.form-inline{display:flex;gap:8px;margin:10px 0}
input[type=text]{padding:6px 8px;min-width:360px}
button{padding:6px 12px;cursor:pointer}
.table{width:100%;border-collapse:collapse;margin-top:12px}
.table th,.table td{border:1px solid #ddd;padding:8px}
.badge{padding:2px 8px;border-radius:12px;border:1px solid #ccc;font-size:12px}
.badge.pending{background:#eef5ff;border-color:#8bb4ff}
.badge.approved{background:#eaffea;border-color:#7ac77a}
.badge.rejected{background:#ffecec;border-color:#ff9f9f}
</style>
</head>
<body>
<?php $id = $idTourna; 
            $teamCount = isset($teamCount) ? (int)$teamCount
           : (isset($tourna['team_count']) ? (int)$tourna['team_count'] : 0);
// dùng chung biến cho nav ?>

<div class="nav">
  <a href="?page=update_tourna&id=<?= $id ?>">Cấu hình</a>
  <a class="active" href="?page=addteam&id=<?= $id ?>">Đội tham gia</a>
  <a href="dashboard.php?page=draw&id_tourna=<?= $id_tourna ?>&team_count=<?= $teamCount ?>">Kết quả bốc thăm</a>
  <a href="?page=schedule&id=<?= $id ?>">Lịch thi đấu</a>
  <a href="?page=rank&id_tourna=<?= $id_tourna ?>">Thống kê - xếp hạng</a>
</div>
<div class="wrap">
  <h2>DANH SÁCH ĐỘI THAM GIA GIẢI</h2>

  <?php if ($flash): ?><div class="flash"><?= htmlspecialchars($flash) ?></div><?php endif; ?>

  <!-- Form đăng ký đội: luôn hiển thị, không JS -->
  <form method="post" class="form-inline">
    <input type="hidden" name="action" value="register">
    <input list="teamlist" name="team_name" placeholder="Nhập tên đội (có gợi ý từ danh sách)...">
    <datalist id="teamlist">
      <?php if ($allTeams instanceof mysqli_result && $allTeams->num_rows>0): ?>
        <?php while($t = $allTeams->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($t['teamName']) ?>"></option>
        <?php endwhile; ?>
      <?php endif; ?>
    </datalist>
    <button type="submit">Đăng ký đội tham gia</button>
  </form>

  <!-- Bảng danh sách đã đăng ký -->
  <table class="table">
    <thead>
      <tr>
        <th>Đội</th>
        <th>Trạng thái</th>
        <th>Cập nhật</th>
      </tr>
    </thead>
    <tbody>
    <?php if ($registered instanceof mysqli_result && $registered->num_rows>0): ?>
      <?php while($r = $registered->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($r['teamName']) ?></td>
          <td><span class="badge <?= htmlspecialchars($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span></td>
          <td>
            <form method="post" style="display:flex;gap:6px;align-items:center">
              <input type="hidden" name="action" value="setstatus">
              <input type="hidden" name="id_tournateam" value="<?= (int)$r['id_tournateam'] ?>">
              <select name="status">
                <option value="pending"  <?= $r['status']==='pending'?'selected':''; ?>>Đang duyệt</option>
                <option value="approved" <?= $r['status']==='approved'?'selected':''; ?>>Chấp nhận</option>
                <option value="rejected" <?= $r['status']==='rejected'?'selected':''; ?>>Từ chối</option>
              </select>
              <button type="submit">Lưu</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="3">Chưa có đội nào đăng ký.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
