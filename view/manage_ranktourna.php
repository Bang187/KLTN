<?php
error_reporting(0);
include_once(__DIR__ . '/../model/modelrank.php');
include_once(__DIR__ . '/../model/modeltourna.php');

// view/manage_tournarank.php
$tournaId = isset($_GET['id_tourna']) ? (int)$_GET['id_tourna'] : 0;

if ($tournaId <= 0) { echo "<p>Thiếu ID giải.</p>"; return; }

/* Nếu các file cũ dùng $id_tourna thì gán thêm biến tương thích */
$id_tourna = $tournaId;
$mt = new mTourna();
$td = $mt->getDetail($tournaId); // đã có ruletype, rr_rounds, pointwin/pointdraw/pointloss
$ruleType = strtolower($td['ruletype'] ?? '');



/* teamCount nếu cần truyền qua trang bốc thăm */
$teamCount = isset($_GET['team_count']) ? (int)$_GET['team_count'] : 0;

$mr = new mRank();
// Kiểm tra có trận vòng bảng / KO không
$hasGroup = $mr->hasGroupMatches($tournaId);
$hasKO    = $mr->hasKoMatches($tournaId);

$overview    = $mr->getOverviewByTournament($tournaId); // trả về array các chỉ số

// 2) BXH: chỉ hiển thị khi stage round-robin/group tồn tại
$leagueStages= $mr->getLeagueStages($tournaId); // mảng stage_type in ('round_robin','group')
$standings   = !empty($leagueStages) ? $mr->getStandingsLive($tournaId) : [];
$koStage     = $mr->getKnockoutStage($tournaId);
$bracket     = $koStage ? $mr->getBracketNodes($koStage['id_stage']) : [];
if (!empty($leagueStages)) {
  // ví dụ lấy BXH của stage đầu tiên
  $standings = $mr->getStandingsLive($tournaId, $leagueStages[0]['id_stage']);
}

// 3) Cây đấu: tìm stage knockout → lấy danh sách node để vẽ
$koStage = $mr->getKnockoutStage($tournaId);
$bracket = [];
if ($koStage) {
  $bracket = $mr->getBracketNodes($koStage['id_stage']); // trả về mảng theo ROUND
}
if (empty($koStage) || empty($bracket)) {
  $fallback = $mr->getBracketFromMatches($tournaId);
  if (!empty($fallback)) {
    $koStage = $koStage ?: ['name' => 'Loại trực tiếp'];
    $bracket = $fallback;
  }
}
?>

<style>
/* Cards tổng quan */
  .nav{
    display:flex; gap:10px; padding:10px; background:#f7f7f9;
    border:1px solid var(--border); border-radius:10px; margin-bottom:16px
  }
  .nav a{
    text-decoration:none; color:#374151; padding:8px 12px; background:#fff;
    border:1px solid var(--border); border-radius:8px
  }
    .nav a.active{
        background:#2563eb;color:#fff;border-color:#2563eb
    }
.kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(160px,1fr));gap:14px;margin:10px 0}
.kpi{background: #a0b3edff;;border:1px solid #eee;border-radius:12px;padding:14px}
.kpi .num{font-size:28px;font-weight:700}
.kpi .label{font-size:13px;color:#777}

/* Bảng BXH */
.table{width:100%;border-collapse:collapse;margin-top:12px;table-layout: fixed}
.table th,.table td{width:auto;border:1px solid #e5e5e5;padding:8px}
.table th{background:#fafafa;text-align:left}


/* Bracket đơn giản dạng cột */
.bracket{display:grid;grid-auto-flow:column;grid-auto-columns:240px;gap:24px;overflow:auto;padding:8px 0}
.round-col{display:flex;flex-direction:column;gap:16px}
.node{border:1px solid #e5e5e5;border-radius:10px;padding:10px;background:#fff}
.node .title{font-size:12px;color:#888;margin-bottom:6px}
.team{display:flex;justify-content:space-between;padding:4px 6px;border-radius:8px}
.team + .team{margin-top:6px}
.team.win{font-weight:600}

</style>
<div class="nav">
  <a href="dashboard.php?page=update_tourna&id=<?= $id_tourna ?>">Cấu hình</a>
  <a href="dashboard.php?page=regulation&id_tourna=<?php echo $id; ?>">Điều lệ</a>
  <a href="dashboard.php?page=addteam&id_tourna=<?= $id_tourna ?>">Đội tham gia</a>
  <a href="dashboard.php?page=draw&id_tourna=<?= $id_tourna ?>&team_count=<?= $teamCount ?>">Kết quả bốc thăm</a>
  <a href="dashboard.php?page=schedule&id=<?= $id_tourna ?>">Lịch thi đấu</a>
  <a class="active" href="dashboard.php?page=rank&id_tourna=<?= $id_tourna ?>">Thống kê - xếp hạng</a>
</div>
<h2>Thống kê & BXH</h2>

<!-- 1) Tổng quan -->
<section>
  <h3>Tổng quan giải</h3>
  <div class="kpi-grid">
    <div class="kpi"><div class="num"><?= $overview['num_teams'] ?? 0 ?></div><div class="label">Đội tham dự</div></div>
    <div class="kpi"><div class="num"><?= $overview['num_matches_played'] ?? 0 ?></div><div class="label">Trận đã đấu</div></div>
    <div class="kpi"><div class="num"><?= $overview['total_goals'] ?? 0 ?></div><div class="label">Tổng bàn thắng</div></div>
    <div class="kpi"><div class="num"><?= $overview['goals_per_match'] ?? '0.00' ?></div><div class="label">Bàn/trận</div></div>
  </div>
</section>

<!-- 2) BXH (chỉ hiển thị khi có stage vòng tròn) -->
<?php if ($ruleType === 'roundrobin'): ?>
  <?php
    // lấy BXH live
    $standings = (new mRank())->getStandingsLive($tournaId);
  ?>
  <section>
    <h3>BXH (Vòng tròn<?= (int)($td['rr_rounds'] ?? 1) === 2 ? ' · 2 lượt' : '' ?>)</h3>
    <table class="table">
      <thead>
        <tr>
          <th>Hạng</th><th>Đội</th><th>Tr</th><th>T</th><th>H</th><th>B</th>
          <th>GF</th><th>GA</th><th>GD</th><th>Điểm</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($standings as $i => $row): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= htmlspecialchars($row['team_name']) ?></td>
          <td><?= $row['p'] ?></td>
          <td><?= $row['w'] ?></td>
          <td><?= $row['d'] ?></td>
          <td><?= $row['l'] ?></td>
          <td><?= $row['gf'] ?></td>
          <td><?= $row['ga'] ?></td>
          <td><?= $row['gd'] ?></td>
          <td><strong><?= $row['pts'] ?></strong></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>
<?php endif; ?>

<!-- 3) Cây đấu -->
<?php if ($hasKO): ?> 
  <h3>Nhánh thi đấu (<?= htmlspecialchars($koStage['name']) ?>)</h3>

  <style>
  .bracket{display:grid;grid-auto-flow:column;grid-auto-columns:260px;gap:20px;overflow:auto;padding:8px 0}
  .round-col{display:flex;flex-direction:column;gap:14px}
  .node{border:1px solid #e5e5e5;border-radius:10px;padding:10px;background:#fff}
  .node .title{font-size:12px;color:#888;margin-bottom:6px}
  .team{display:flex;justify-content:space-between;padding:6px 8px;border-radius:8px;background:#f9fafb}
  .team + .team{margin-top:6px}
  .team.win{font-weight:600}
  </style>

  <?php if (empty($bracket)): ?>
    <p>Chưa có nhánh đấu do chưa có lịch thi đấu.</p>
  <?php else: ?>
<div class="bracket">
  <?php foreach ($bracket as $roundNo => $nodes): ?>
    <div class="round-col">
      <?php foreach ($nodes as $n): ?>
        <div class="node">
          <div class="title">
            <?= htmlspecialchars($n['round_title'] ?? ('Vòng KO '.$roundNo)) ?>
            <?= ' · Trận #'.(int)($n['_seq'] ?? 0) ?>
            <?= !empty($n['kickoff_date']) ? ' · '.htmlspecialchars($n['kickoff_date']) : '' ?>
          </div>
          <div class="team <?= !empty($n['home_win']) ? 'win' : '' ?>">
            <span><?= htmlspecialchars($n['home_label']) ?></span>
            <strong><?= isset($n['home_score']) ? (int)$n['home_score'] : '' ?></strong>
          </div>
          <div class="team <?= !empty($n['away_win']) ? 'win' : '' ?>">
            <span><?= htmlspecialchars($n['away_label']) ?></span>
            <strong><?= isset($n['away_score']) ? (int)$n['away_score'] : '' ?></strong>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</div>  
  <?php endif; ?>
<?php endif; ?>
<?php if ($hasGroup): ?>
  <h3>BXH vòng bảng</h3>
  <?php $all = (new mRank())->getAllGroupStandings($tournaId); ?>
  <?php foreach ($all as $label => $rows): ?>
    <h4>Bảng <?= htmlspecialchars($label) ?></h4>
    <table class="table">
      <thead>
        <tr><th>Hạng</th><th>Đội</th><th>Tr</th><th>T</th><th>H</th><th>B</th>
            <th>GF</th><th>GA</th><th>GD</th><th>Điểm</th></tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $i=>$r): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= htmlspecialchars($r['teamName']) ?></td>
          <td><?= $r['p'] ?></td><td><?= $r['w'] ?></td><td><?= $r['d'] ?></td><td><?= $r['l'] ?></td>
          <td><?= $r['gf'] ?></td><td><?= $r['ga'] ?></td><td><?= $r['gd'] ?></td>
          <td><strong><?= $r['pts'] ?></strong></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endforeach; ?>
<?php endif; ?>
