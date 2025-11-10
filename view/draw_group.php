<?php
// view/draw_group.php
error_reporting(E_ALL);
require_once __DIR__ . '/../control/controltourna.php';

$id_tourna = 0;
if (isset($_GET['id_tourna']) && ctype_digit($_GET['id_tourna'])) {
    $id_tourna = (int)$_GET['id_tourna'];
} elseif (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $id_tourna = (int)$_GET['id'];
}

if ($id_tourna <= 0) { echo "<p>Thiếu id_tourna</p>"; return; }

$ctr = new cTourna();

$flash = null;
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_groups'])) {
  $flash = $ctr->saveGroupAssignments($id_tourna, $_POST);
  // tránh F5 gửi lại form
  header("Location: dashboard.php?page=draw_group&id_tourna={$id_tourna}&saved=".($flash['success']?1:0));
  exit;
}

// Load data (đồng thời đảm bảo đã init group/slot)
$vm = $ctr->loadGroupScreenData($id_tourna);
if (isset($vm['err'])) { echo '<p>'.$vm['err'].'</p>'; return; }
$tour    = $vm['tourna'];
$groups  = $vm['groups'];
$approved= $vm['approved'];
$used    = $vm['used'];
?>
<style>
:root{
  --card:#fff; --line:#e5e7eb; --muted:#6b7280; --text:#111827; --bg:#f8fafc;
  --primary:#2563eb; --radius:12px; --shadow:0 6px 20px rgba(0,0,0,.06)
}
.wrap{max-width:1100px;margin:16px auto}
.nav{display:flex;gap:8px;margin-bottom:12px}
.nav a{padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;text-decoration:none;color:#374151}
.nav a.active{background:var(--primary);color:#fff;border-color:var(--primary)}
.grid{display:grid;gap:16px}
@media(min-width:1000px){ .grid{grid-template-columns:1fr 1fr} }
.card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden}
.card h3{margin:0;padding:12px 14px;background:#e5f0ff;border-bottom:1px solid #d9e2ff}
.list{padding:10px 14px}
.row{display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px dashed #eef2f7}
.row:last-child{border-bottom:none}
.slotno{width:42px;font-weight:600;color:#374151}
.select{min-width:260px;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;background:#fff}
.actions{margin-top:14px;display:flex;gap:8px}
.btn{border:none;border-radius:9px;padding:10px 14px;cursor:pointer}
.btn-primary{background:var(--primary);color:#fff}
.btn-ghost{background:#f3f4f6;color:#111827;border:1px solid #d1d5db}
.flash{margin:10px 0;padding:10px 12px;border-radius:8px;border:1px solid #a7f3d0;background:#ecfdf5;color:#065f46}
.flash.error{border-color:#fecaca;background:#fef2f2;color:#991b1b}
.note{color:var(--muted);font-size:13px;padding:6px 2px}
</style>

<div class="wrap">
  <div class="nav">
    <a href="dashboard.php?page=update_tourna&id=<?= $id_tourna ?>">Cấu hình</a>
    <a href="dashboard.php?page=regulation&id_tourna=<?= $id_tourna ?>">Điều lệ</a>
    <a href="dashboard.php?page=addteam&id=<?= $id_tourna ?>">Đội tham gia</a>
    <a class="active" href="dashboard.php?page=draw_group&id_tourna=<?= $id_tourna ?>">Chia bảng</a>
    <a href="dashboard.php?page=draw&id_tourna=<?= $id_tourna ?>">KO/RR</a>
    <a href="dashboard.php?page=schedule&id=<?= $id_tourna ?>">Lịch thi đấu</a>
    <a href="dashboard.php?page=rank&id_tourna=<?= $id_tourna ?>">Thống kê - xếp hạng</a>
  </div>

  <h2>Chia bảng – <?= htmlspecialchars($tour['tournaName'] ?? '') ?></h2>
  <div class="note">
    Số bảng: <b><?= (int)($tour['hy_group_count'] ?? 4) ?></b>. Tổng đội cấu hình: <b><?= (int)($tour['team_count'] ?? 0) ?></b>.
    Chọn đội cho từng slot. Một đội chỉ xuất hiện ở <b>một</b> bảng.
  </div>

  <?php if (!empty($_GET['saved'])): ?>
    <div class="flash">Đã lưu phân bổ đội.</div>
  <?php endif; ?>

  <form method="post">
    <div class="grid">
      <?php foreach ($groups as $g): ?>
        <div class="card">
          <h3>Bảng <?= htmlspecialchars($g['label']) ?></h3>
          <div class="list">
            <?php if (empty($g['slots'])): ?>
              <div class="row"><em>Chưa có slot.</em></div>
            <?php else: foreach ($g['slots'] as $s):
              $cur = (int)($s['id_team'] ?? 0);
            ?>
              <div class="row">
                <div class="slotno"><?= (int)$s['slot_no'] ?></div>
                <div style="flex:1"></div>
                <select class="select" name="gs_<?= (int)$g['id_group'] ?>_<?= (int)$s['slot_no'] ?>">
                  <option value="">-- Chưa chọn --</option>
                  <?php foreach ($approved as $t):
                    $tid = (int)$t['id_team'];
                    $selected = ($tid === $cur);
                    $disabled = (isset($used[$tid]) && !$selected);
                  ?>
                    <option value="<?= $tid ?>" <?= $selected?'selected':'' ?> <?= $disabled?'disabled':'' ?>>
                      <?= htmlspecialchars($t['teamName']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="actions">
      <button type="submit" name="save_groups" class="btn btn-primary">💾 Lưu phân bổ</button>
      <a class="btn btn-ghost" href="dashboard.php?page=draw_group&id_tourna=<?= $id_tourna ?>">Tải lại</a>
      <a class="btn btn-primary"href="dashboard.php?page=gen_group_schedule&id_tourna=<?= (int)$id_tourna ?>">
  🔁 Sinh lịch vòng bảng
</a>
    </div>
  </form>
</div>
