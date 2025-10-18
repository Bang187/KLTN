<?php
$idMatch = (int)$_GET['id_match'];
$tournaId = isset($_GET['id']) ? (int)$_GET['id'] : (int)($match['id_tourna'] ?? 0);
?>
<style>
.score-box{display:flex;justify-content:center;gap:18px;margin:10px 0}
.score{font-size:48px;font-weight:700;border:1px solid #e5e7eb;padding:6px 18px;border-radius:10px}
.teamname{font-size:26px;color:#ef4444;font-weight:600}
.col2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.card{border:1px solid #e5e7eb;border-radius:12px;padding:12px}
.tbl{width:100%;border-collapse:collapse}
.tbl th,.tbl td{border-bottom:1px solid #f1f5f9;padding:8px}
.btn{padding:8px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;cursor:pointer}
.btn.primary{background:#2563eb;color:#fff;border-color:#2563eb}
.btn.danger{background:#ef4444;color:#fff;border-color:#ef4444}
.select,input[type=number]{padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px}
</style>

<h2>Nhập kết quả trận</h2>

<div class="score-box">
  <div class="teamname"><?= htmlspecialchars($match['home_name'] ?? 'Đội nhà') ?></div>
  <div class="score"><?= (int)$match['home_score'] ?></div>
  <div class="score"><?= (int)$match['away_score'] ?></div>
  <div class="teamname"><?= htmlspecialchars($match['away_name'] ?? 'Đội khách') ?></div>
</div>

<div class="col2">
  <div class="card">
    <h3>Ghi bàn — Đội nhà</h3>
    <form method="post">
      <input type="hidden" name="id_match" value="<?= $idMatch ?>">
      <select class="select" name="home_member_id" required>
        <option value="">Chọn cầu thủ</option>
        <?php foreach($homeMembers as $mbr): ?>
          <option value="<?= (int)$mbr['id_member'] ?>"><?= htmlspecialchars($mbr['fullname']) ?></option>
        <?php endforeach; ?>
      </select>
      <input class="select" type="number" min="0" max="120" name="home_minute" placeholder="Phút" required>
      <select class="select" name="home_goal_type">
        <option value="goal">Bàn thường</option>
        <option value="penalty_goal">Penalty</option>
        <option value="own_goal">Phản lưới</option>
      </select>
      <button class="btn primary" name="add_goal_home" value="1">Thêm</button>
    </form>
  </div>

  <div class="card">
    <h3>Ghi bàn — Đội khách</h3>
    <form method="post">
      <input type="hidden" name="id_match" value="<?= $idMatch ?>">
      <select class="select" name="away_member_id" required>
        <option value="">Chọn cầu thủ</option>
        <?php foreach($awayMembers as $mbr): ?>
          <option value="<?= (int)$mbr['id_member'] ?>"><?= htmlspecialchars($mbr['fullname']) ?></option>
        <?php endforeach; ?>
      </select>
      <input class="select" type="number" min="0" max="120" name="away_minute" placeholder="Phút" required>
      <select class="select" name="away_goal_type">
        <option value="goal">Bàn thường</option>
        <option value="penalty_goal">Penalty</option>
        <option value="own_goal">Phản lưới</option>
      </select>
      <button class="btn primary" name="add_goal_away" value="1">Thêm</button>
    </form>
  </div>
</div>

<div class="card" style="margin-top:16px">
  <h3>Danh sách bàn thắng</h3>
  <table class="tbl">
    <thead><tr><th>Phút</th><th>Đội</th><th>Cầu thủ</th><th>Loại</th><th></th></tr></thead>
    <tbody>
      <?php foreach($events as $ev): ?>
        <tr>
          <td><?= (int)$ev['minute'] ?></td>
          <td><?= $ev['team_side']==='home'?'Đội nhà':'Đội khách' ?></td>
          <td><?= htmlspecialchars($ev['fullname']) ?></td>
          <td><?= $ev['event_type']==='penalty_goal'?'Penalty':($ev['event_type']==='own_goal'?'Phản lưới':'Thường') ?></td>
          <td>
            <form method="post" onsubmit="return confirm('Xóa sự kiện?')">
              <input type="hidden" name="id_match" value="<?= $idMatch ?>">
              <input type="hidden" name="id_event" value="<?= (int)$ev['id_event'] ?>">
              <button class="btn danger" name="del_event" value="1">Xóa</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<form method="post" style="margin-top:12px">
  <input type="hidden" name="id_match" value="<?= $idMatch ?>">
  <button class="btn primary" name="finalize_match" value="1">Lưu & kết thúc trận</button>
  <a class="btn" href="dashboard.php?page=schedule&id=<?= $tournaId ?>">Quay về lịch</a>
</form>
