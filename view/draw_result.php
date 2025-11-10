<?php
// --------- Lấy tham số an toàn ----------
$id_tourna = isset($id_tourna) ? (int)$id_tourna
           : (isset($idTourna)  ? (int)$idTourna
           : (isset($_GET['id_tourna']) ? (int)$_GET['id_tourna']
           : (isset($_GET['id']) ? (int)$_GET['id'] : 0)));

$teamCount = isset($teamCount) ? (int)$teamCount
           : (isset($tourna['team_count']) ? (int)$tourna['team_count'] : 0);

// $approved có thể là mysqli_result. Đưa về mảng cho dễ render.
$approvedTeams = [];
if (isset($approved)) {
  if ($approved instanceof mysqli_result) {
    while ($r = $approved->fetch_assoc()) $approvedTeams[] = $r; // id_team, teamName, (seed?)...
  } elseif (is_array($approved)) {
    $approvedTeams = $approved;
  }
}
?>
<style>
/* --------- Style gọn gàng --------- */
:root{
  --card:#fff; --line:#e5e7eb; --muted:#6b7280; --text:#111827; --bg:#f5f7fb;
  --primary:#2563eb; --radius:12px; --shadow:0 6px 20px rgba(0,0,0,.06);
}
.nav{display:flex;gap:10px;padding:10px;background:#f3f4f6;border:1px solid var(--line);border-radius:10px;margin-bottom:14px}
.nav a{display:inline-block;text-decoration:none;color:#374151;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px}
.nav a.active{background:var(--primary);color:#fff;border-color:var(--primary)}

.draw-grid{display:grid;grid-template-columns:1fr;gap:16px}
@media(min-width:960px){ .draw-grid{grid-template-columns: 1fr 1.2fr;} }

.card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);padding:16px}
.card h3{margin:0 0 12px 0;font-size:20px;color:var(--text)}

.table{width:100%;border-collapse:separate;border-spacing:0}
.table th,.table td{padding:10px 12px;border-bottom:1px solid #eef2f7}
.table thead th{background:#f9fafb;font-weight:600;color:#374151;border-top:1px solid #eef2f7}
.table tbody tr:nth-child(odd){background:#fcfdff}
.table-wrap{overflow-x:auto;border-radius:10px;border:1px solid #eef2f7}

.select{min-width:260px;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;background:#fff}
.input{width:90px;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px}

.btn{display:inline-block;border:none;border-radius:9px;padding:10px 14px;cursor:pointer}
.btn-primary{background:var(--primary);color:#fff}
.btn-ghost{background:#f3f4f6;color:#111827;border:1px solid #d1d5db}
.btn + .btn{margin-left:8px}
.actions{margin-top:10px}

.flash{margin-top:12px;padding:10px 12px;border-radius:8px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;display:inline-block}
.flash.error{background:#fef2f2;color:#991b1b;border-color:#fecaca}
</style>

<!-- Tabs -->
<div class="nav">
  <a href="dashboard.php?page=update_tourna&id=<?= $id_tourna ?>">Cấu hình</a>
  <a href="dashboard.php?page=regulation&id_tourna=<?php echo $id; ?>">Điều lệ</a>
  <a href="dashboard.php?page=addteam&id=<?= $id_tourna ?>">Đội tham gia</a>
  <a class="active" href="dashboard.php?page=draw&id_tourna=<?= $id_tourna ?>">Kết quả bốc thăm</a>
  <a href="dashboard.php?page=schedule&id=<?= $id_tourna ?>">Lịch thi đấu</a>
  <a href="dashboard.php?page=rank&id_tourna=<?= $id_tourna ?>">Thống kê - xếp hạng</a>
</div>

<?php
// Flash message đơn giản
if (!empty($_GET['msg'])) {
  $msg = $_GET['msg'];
  $text = $msg==='seeded_ok' ? 'Đã xếp slot theo hạt giống.'
        : ($msg==='seed_saved' ? 'Đã lưu seed.'
        : ($msg==='slots_saved' ? 'Đã lưu kết quả bốc thăm.' : ''));
  if ($text) echo '<div class="flash">'.$text.'</div>';
}
?>

<div class="draw-grid">
  <!-- Cột trái: Seed & Xếp -->
  <div class="card">
    <h3>Hạt giống</h3>
    <div class="table-wrap">
      <form method="post" action="dashboard.php?page=draw_save_seed&id_tourna=<?= (int)$id_tourna ?>">
        <table class="table">
          <thead><tr><th>Đội</th><th style="width:120px;text-align:left">Seed</th></tr></thead>
          <tbody>
          <?php if (empty($approvedTeams)): ?>
            <tr><td colspan="2"><em>Chưa có đội đã duyệt.</em></td></tr>
          <?php else: foreach ($approvedTeams as $t): ?>
            <tr>
              <td><?= htmlspecialchars($t['teamName'] ?? '') ?></td>
              <td>
                <input class="input" type="number" min="1"
                       name="seed[<?= (int)($t['id_team'] ?? 0) ?>]"
                       value="<?= htmlspecialchars($t['seed'] ?? '') ?>">
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
        <div class="actions">
          <button type="submit" class="btn btn-ghost"> Lưu seed</button>
          <a class="btn btn-ghost"
             href="dashboard.php?page=draw&id_tourna=<?= (int)$id_tourna ?>">Tải lại</a>
        </div>
      </form>
    </div>

    <form method="post" action="dashboard.php?page=draw_place_seeded&id_tourna=<?= (int)$id_tourna ?>" class="actions">
      <button type="submit" class="btn btn-primary"> Xếp bốc thăm </button>
    </form>

  </div>

  <!-- Cột phải: Slot & lưu -->
  <div class="card">
    <h3>Slot → Đội</h3>
    <div class="table-wrap">
      <form method="post" action="dashboard.php?page=draw&id_tourna=<?= (int)$id_tourna ?>">
        <input type="hidden" name="act" value="save_slots">
        <table class="table">
          <thead>
            <tr><th style="width:80px">Slot</th><th>Chọn đội</th></tr>
          </thead>
          <tbody>
          <?php
          // Chuẩn bị map "đội đã dùng" để disable option trùng (ngoại trừ slot đang chọn)
          $usedTeams = [];
          if (!empty($slots)) {
            foreach ($slots as $r) if (!empty($r['id_team'])) $usedTeams[(int)$r['id_team']] = true;
          }
          // Render từng slot
          if (!empty($slots)):
            foreach ($slots as $row):
              $slotNo = (int)($row['slot_no'] ?? 0);
              $current = isset($row['id_team']) ? (int)$row['id_team'] : null;
          ?>
            <tr>
              <td><?= $slotNo ?></td>
              <td>
                <select name="slot_<?= $slotNo ?>" class="select">
                  <option value="">-- Chưa chọn --</option>
                  <?php foreach ($approvedTeams as $t):
                    $tid = (int)($t['id_team'] ?? 0);
                    $selected = ($current === $tid);
                    $disabled = (!empty($usedTeams[$tid]) && !$selected);
                  ?>
                    <option value="<?= $tid ?>"
                            <?= $selected ? 'selected' : '' ?>
                            <?= $disabled ? 'disabled' : '' ?>>
                      <?= htmlspecialchars($t['teamName'] ?? '') ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
            </tr>
          <?php
            endforeach;
          else:
          ?>
            <tr><td colspan="2"><em>Chưa có slot. Kiểm tra lại cấu hình số đội.</em></td></tr>
          <?php endif; ?>
          </tbody>
        </table>

        <div class="actions">
          <button type="submit" class="btn btn-primary">💾 Lưu kết quả</button>
          <a class="btn btn-ghost"
             href="dashboard.php?page=draw&id_tourna=<?= (int)$id_tourna ?>">Tải lại</a>
        </div>
      </form>
    </div>

    <?php if (!empty($_GET['saved'])): ?>
      <div class="flash">Đã lưu kết quả bốc thăm.</div>
    <?php endif; ?>
  </div>
<a class="btn btn-ghost" 
   href="dashboard.php?page=draw_group&id_tourna=<?= (int)$id_tourna ?>&team_count=<?= (int)$teamCount ?>">
  Bốc thăm vòng bảng
</a>
</div>
