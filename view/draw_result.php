<?php
$id_tourna = isset($id_tourna) ? (int)$id_tourna
           : (isset($idTourna)  ? (int)$idTourna
           : (isset($_GET['id_tourna']) ? (int)$_GET['id_tourna']
           : (isset($_GET['id']) ? (int)$_GET['id'] : 0)));

$teamCount = isset($teamCount) ? (int)$teamCount
           : (isset($tourna['team_count']) ? (int)$tourna['team_count'] : 0);
?>
<style>
.nav{display:flex;gap:10px;padding:8px;background:#f3f3f3;border:1px solid #ddd}
.nav a{text-decoration:none;color:#333;padding:6px 10px;border:1px solid #ccc;border-radius:4px}
.nav a.active{background:#2563eb;color:#fff;border-color:#2563eb}

  .draw-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.04);padding:16px}
  .draw-title{margin:0 0 12px 0;font-size:22px;color:#111827}

  .table-draw{width:100%;border-collapse:separate;border-spacing:0}
  .table-draw th,.table-draw td{padding:12px 14px;border-bottom:1px solid #eef2f7}
  .table-draw thead th{background:#f9fafb;font-weight:600;color:#374151;border-top:1px solid #eef2f7}
  .table-draw tbody tr:nth-child(odd){background:#fcfdff}
  .table-draw tbody tr:hover{background:#f3f4f6}
  .table-draw th:first-child,.table-draw td:first-child{border-left:1px solid #eef2f7}
  .table-draw th:last-child,.table-draw td:last-child{border-right:1px solid #eef2f7}
  .table-wrap{overflow-x:auto;border-radius:10px}

  select.draw-select{min-width:280px;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;background:#fff}
  button.btn-save{margin-top:12px;padding:10px 16px;border:none;border-radius:8px;background:#111827;color:#fff;cursor:pointer}
  button.btn-save:hover{opacity:.9}

  .flash{margin-top:12px;padding:10px 12px;border-radius:8px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;display:inline-block}

</style>
<div class="nav">
  <a href="dashboard.php?page=update_tourna&id=<?= $id_tourna ?>">Cấu hình</a>
  <a href="dashboard.php?page=addteam&id=<?= $id_tourna ?>">Đội tham gia</a>
  <a class="active" href="dashboard.php?page=draw&id_tourna=<?= $id_tourna ?>&team_count=<?= $teamCount ?>">Kết quả bốc thăm</a>
  <a href="dashboard.php?page=schedule&id=<?= $id_tourna ?>">Lịch thi đấu</a>
  <a href="dashboard.php?page=rank&id_tourna=<?= $id_tourna ?>">Thống kê - xếp hạng</a>
</div>
<?php
// $slots: [slot_no, id_team, teamName], $approved: [id_team, teamName]
$usedTeams = [];
foreach($slots as $r){
  if(!empty($r['id_team'])) $usedTeams[] = (int)$r['id_team'];
}
?>
<div class="draw-card">
  <h2 class="draw-title">Kết quả bốc thăm</h2>

  <form method="post">
    <div class="table-wrap">
      <table class="table-draw">
        <thead>
          <tr>
            <th style="width:120px">Slot</th>
            <th>Chọn đội</th>
          </tr>
        </thead>
        <tbody>
        <?php
          // $slots: [slot_no, id_team, teamName], $approved: [id_team, teamName]
          $usedTeams = [];
          foreach ($slots as $r) {
            if (!empty($r['id_team'])) $usedTeams[] = (int)$r['id_team'];
          }

          foreach ($slots as $row):
            $i = (int)$row['slot_no'];
            $currentSlotTeam = $row['id_team'] ?? null;
        ?>
          <tr>
            <td><?= $i ?></td>
            <td>
              <select name="slot_<?= $i ?>" class="draw-select">
                <option value="">-- Chưa chọn --</option>
                <?php foreach ($approved as $t):
                  $isSelected = ($currentSlotTeam == $t['id_team']);
                  $isUsed = in_array((int)$t['id_team'], $usedTeams, true) && !$isSelected;
                ?>
                  <option value="<?= (int)$t['id_team'] ?>"
                          <?= $isSelected ? 'selected' : '' ?>
                          <?= $isUsed ? 'disabled' : '' ?>>
                      <?= htmlspecialchars($t['teamName']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <button type="submit" class="btn-save">Lưu kết quả</button>
  </form>

  <?php if (isset($_GET['saved'])): ?>
    <div class="flash">Đã lưu kết quả bốc thăm.</div>
  <?php endif; ?>
</div>