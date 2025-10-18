<?php
$id_tourna = isset($id_tourna) ? (int)$id_tourna : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
?>
<style>
  :root{
    --border:#e5e7eb; --muted:#6b7280; --text:#111827; --bg:#f8fafc; --card:#ffffff;
    --primary:#111827; --primary-600:#0b1220;
  }

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

  .page-card{
    background:var(--card); border:1px solid var(--border); border-radius:14px;
    box-shadow:0 2px 10px rgba(0,0,0,.05); padding:16px
  }
  .page-title{ margin:0 0 12px 0; font-size:22px; color:var(--text) }

  .hstack{ display:flex; gap:10px; align-items:center; margin:10px 0 16px }
  .btn{
    padding:8px 12px; border:1px solid var(--border); border-radius:10px;
    background:#fff; cursor:pointer
  }
  .btn.primary{ background:var(--primary); color:#fff; border-color:var(--primary) }
  .btn.primary:hover{ background:var(--primary-600) }

  .alert{ padding:8px 10px; border-radius:10px; display:inline-block }
  .alert.ok{ background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0 }
  .alert.info{ background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe }
  .badge-err{ padding:8px 10px; border-radius:10px; background:#FEF2F2; color:#991B1B; border:1px solid #FECACA }

  .round-card{ margin-top:14px; border:1px solid var(--border); border-radius:12px; overflow:hidden }
  .round-header{ padding:10px 14px; background:#22c55e; color:#fff; font-weight:600 }
  .table-wrap{ overflow-x:auto }
  .table{ width:100%; border-collapse:separate; border-spacing:0 }
  .table th,.table td{ padding:12px 14px; border-bottom:1px solid #eef2f7; vertical-align:middle }
  .table thead th{ background:#f9fafb; text-align:left; color:#374151; font-weight:600 }
  .table tbody tr:nth-child(odd){ background:#fcfdff }
  .table tbody tr:hover{ background:#f5f7fb }

  .muted{ color:var(--muted) }

  .kickoff-form, .score-form{ display:flex; gap:8px; align-items:center; flex-wrap:wrap }
  .kickoff-form input[type="date"],
  .kickoff-form input[type="time"],
  .kickoff-form select,
  .kickoff-form input[type="text"]{
    padding:8px 10px; border:1px solid var(--border); border-radius:8px; background:#fff
  }
  .kickoff-form input[type="date"]{ width:150px }
  .kickoff-form input[type="time"]{ width:110px }
  .kickoff-form select{ min-width:160px }
  .kickoff-form input[name="pitch_label"]{ width:130px }
  .kickoff-form input[name="venue"]{ width:170px }
  .kickoff-form .btn{ padding:8px 12px }

  .score-form input{
    width:70px; text-align:center; padding:8px 10px; border:1px solid var(--border); border-radius:8px
  }
  .btn {
    background: green; color: #fff; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer;
  }
  .btnpoint {
    display:inline-block; padding:6px 10px; background:#2563eb; color:#fff;
    border-radius:8px; text-decoration:none
  }
.status-pill{
  display:inline-flex; align-items:center; gap:8px;
  padding:6px 14px; border-radius:9999px; font-weight:600;
  position:relative; border:1px solid transparent;
  line-height:1; user-select:none;
}
.status-pill::after{
  content:""; position:absolute; right:4px; top:50%;
  width:16px; height:16px; border-radius:50%;
  background:#fff; transform:translateY(-50%);
  box-shadow:0 0 0 1px rgba(0,0,0,.06);
}

/* trạng thái */
.status-played   { background:#22c55e; color:#fff; border-color:#16a34a; }
.status-scheduled{ background:#ef4444; color:#fff; border-color:#dc2626; }
.status-canceled { background:#9ca3af; color:#fff; border-color:#6b7280; }
/* Status pills */
.status-pill{
  display:inline-flex; align-items:center; gap:8px;
  padding:6px 12px; border-radius:9999px; font-weight:200;
  position:relative; border:1px solid transparent;
  line-height:1; user-select:none; font-size:14px
}
.status-pill::after{
  content:""; position:absolute; right:4px; top:50%;
  width:16px; height:16px; border-radius:50%;
  background:#fff; transform:translateY(-50%);
  box-shadow:0 0 0 1px rgba(0,0,0,.06);
}

/* trạng thái */
.status-played   { background:#22c55e; color:#fff; border-color:#16a34a; }
.status-scheduled{ background:#ef4444; color:#fff; border-color:#dc2626; }
.status-canceled { background:#9ca3af; color:#fff; border-color:#6b7280; }

.score-pill{
  display:inline-block; min-width:64px; text-align:center;
  padding:6px 10px; border-radius:9999px;
  background:#f3f4f6; border:1px solid #e5e7eb; font-weight:600;
}

</style>


<div class="nav">
  <a href="dashboard.php?page=update_tourna&id=<?= $id_tourna ?>">Cấu hình</a>
  <a href="dashboard.php?page=addteam&id=<?= $id_tourna ?>">Đội tham gia</a>
  <a href="dashboard.php?page=draw&id_tourna=<?= $id_tourna ?>">Kết quả bốc thăm</a>
  <a class="active" href="dashboard.php?page=schedule&id=<?= $id_tourna ?>">Lịch thi đấu</a>
  <a href="dashboard.php?page=rank&id_tourna=<?= $id_tourna ?>">Thống kê - xếp hạng</a>
</div>
<div class="page-card">
  <h2 class="page-title">Lịch thi đấu</h2>


  <div class="hstack">
<a class="btn primary" href="dashboard.php?page=schedule&id=<?= $id_tourna ?>&generate=1">+ Sinh cặp đấu (Knockout)</a>
  <?php if(isset($_GET['genok'])): ?><span class="alert ok">Đã sinh cặp đấu.</span><?php endif; ?>
  <?php if(isset($_GET['saved'])): ?><span class="alert info">Đã lưu lịch.</span><?php endif; ?>
  <?php if(isset($_GET['conflict'])): ?>
    <span class="badge-err">Khung giờ/sân bị trùng! Vui lòng chọn thời điểm khác.</span>
    <script>
      alert(decodeURIComponent("<?= isset($_GET['msg'])? $_GET['msg'] : 'Khung giờ/sân bị trùng! Vui lòng chọn thời điểm khác.' ?>"));
    </script>
  <?php endif; ?>
  </div>

  <?php if(empty($rounds)): ?>
    <p class="muted">Chưa có trận nào. Hãy bấm “Sinh cặp đấu”.</p>
  <?php else: ?>
    <?php foreach($rounds as $roundNo => $matches): ?>
      <div class="round-card">
        <div class="round-header">Vòng <?= (int)$roundNo ?></div>
        <div class="table-wrap">
          <table class="table">
            <thead>
  <tr>
    <th style="width:70px">Mã</th>
    <th style="width:110px">Ngày</th>
    <th style="width:90px">Giờ</th>
    <th>Chủ nhà</th>
    <th class="muted" style="width:60px">Tỉ số</th>
    <th>Khách</th>
    <th style="width:120px">Sân</th>
    <th style="width:120px">Trạng thái</th>
    <th style="width:520px">Phân lịch</th>
    <th style="width:160px">Hành động</th>
  </tr>
            </thead>
<tbody>
<?php foreach($matches as $m): ?>
  <tr>
    <td><?= (int)$m['id_match'] ?></td>
    <td><?= $m['kickoff_date'] ? date('d/m/Y', strtotime($m['kickoff_date'])) : '' ?></td>
    <td><?= $m['kickoff_time'] ? substr($m['kickoff_time'], 0, 5) : '' ?></td>
    <td><?= $m['home_name'] ? htmlspecialchars($m['home_name']) : '<span class="muted">'.htmlspecialchars($m['home_placeholder']).'</span>' ?></td>
    <td class="muted">
        <?php
    // coi là có tỉ số nếu đã đá hoặc DB đã có giá trị score khác NULL
    $hasScore = ($m['status'] === 'played') ||
                ($m['home_score'] !== null && $m['away_score'] !== null);

    if ($hasScore) {
      $hs = (int)$m['home_score'];
      $as = (int)$m['away_score'];
      echo '<span class="score-pill">'.$hs.' : '.$as.'</span>';
    } else {
      echo '<span class="muted">vs</span>';
    }
  ?>
    </td>
    <td><?= $m['away_name'] ? htmlspecialchars($m['away_name']) : '<span class="muted">'.htmlspecialchars($m['away_placeholder']).'</span>' ?></td>

    <!-- Sân: ưu tiên pitch_label, fallback venue -->
    <td>
      <?= htmlspecialchars($m['pitch_label'] ?: ($m['venue'] ?? '')) ?>
    </td>

    <td>
  <?php $st = $m['status'] ?? 'scheduled'; ?>
  <span class="status-pill
              <?= $st==='played'    ? 'status-played'    : '' ?>
              <?= $st==='scheduled' ? 'status-scheduled' : '' ?>
              <?= $st==='canceled'  ? 'status-canceled'  : '' ?>">
    <?= $st==='played' ? 'Đã đá' : ($st==='canceled' ? 'Hủy' : 'Chưa đá') ?>
  </span>
</td>

    <td>
      <form method="post" class="kickoff-form">
        <input type="hidden" name="id_match" value="<?= (int)$m['id_match'] ?>">
        <input type="date" name="kickoff_date" value="<?= $m['kickoff_date'] ?? '' ?>">
        <input type="time" name="kickoff_time" value="<?= $m['kickoff_time'] ?? '' ?>">

        <!-- địa điểm ẩn: lấy từ giải -->
        <input type="hidden" name="location_id" value="<?= isset($tourna['location_id']) ? (int)$tourna['location_id'] : (int)($m['location_id'] ?? 0) ?>">

        <input type="text" name="pitch_label" placeholder="Sân (vd: Sân 1)" value="<?= htmlspecialchars($m['pitch_label'] ?? '') ?>">
        <input type="text" name="venue" placeholder="Ghi chú" value="<?= htmlspecialchars($m['venue'] ?? '') ?>">
        <button class="btn" name="update_kickoff" value="1">Lưu</button>
      </form>
    </td>

    <td>
      <a class="btnpoint" href="dashboard.php?page=match_stats&id_match=<?= (int)$m['id_match'] ?>&id=<?= $id_tourna ?>">Nhập kết quả </a>
    </td>
  </tr>
<?php endforeach; ?>
</tbody>
          </table>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>