<?php
$id = isset($_GET['id_tourna']) ? (int)$_GET['id_tourna'] : 0;

include_once(__DIR__ . '/../control/controltourna.php');
$c = new cTourna();

$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);

$ok = false; $notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSaveReg'])) {
    $ok = $c->saveRegulation($id, $_POST, $_FILES);   // trả về true/false
    $_SESSION['flash'] = [
        'type' => $ok ? 'success' : 'error',
        'text' => $ok ? 'Đã lưu Điều lệ & Lệ phí.' : 'Lưu thất bại. Vui lòng kiểm tra lại.'
    ];
    header('Location: dashboard.php?page=regulation&id_tourna='.$id);
    exit;
}

// Prefill
$tourna     = $c->getTournaById($id);
$fee_type   = $tourna['fee_type'] ?? 'FREE';
$fee_amount = $tourna['fee_amount'] ?? '';
$summary    = $tourna['regulation_summary'] ?? '';
?>
<style>
  .nav{display:flex;gap:10px;padding:8px;background:#f3f3f3;border:1px solid #ddd}
  .nav a{text-decoration:none;color:#333;padding:6px 10px;border:1px solid #ccc;border-radius:4px}
  .nav a.active{background:#2563eb;color:#fff;border-color:#2563eb}
  :root { --card:#ffffff; --bg:#f6f7fb; --primary:#2563eb; --text:#222; --muted:#6b7280; --border:#e5e7eb; }
  body { background: var(--bg); }
  .wrap { max-width: 900px; margin: 20px auto; }
  .card {
    background: var(--card); border:1px solid var(--border); border-radius:14px;
    box-shadow: 0 8px 24px rgba(0,0,0,.04); padding:22px;
  }
  .title { margin:0 0 16px; font-size:20px; color:var(--text); }
  .grid { display:grid; grid-template-columns: 1fr 1fr; gap:16px; }
  .field { display:flex; flex-direction:column; gap:8px; }
  .label { font-weight:600; color:var(--text); }
  .muted { color: var(--muted); font-size:13px; }
  select, input[type="number"], textarea, input[type="file"]{
    width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:10px; background:#fff;
    font: inherit;
  }
  textarea { min-height:160px; resize:vertical; }
  .actions { margin-top:16px; display:flex; gap:10px; }
  .btn {
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    padding:10px 16px; border-radius:12px; border:1px solid transparent; cursor:pointer; font-weight:600;
  }
  .btn-primary { background:var(--primary); color:#fff; }
  .btn-secondary { background:#fff; color:var(--text); border-color:var(--border); }
  .alert {
    margin-bottom:16px; padding:12px 14px; border-radius:10px; border:1px solid;
  }
  .alert-success { background:#ecfdf5; color:#065f46; border-color:#a7f3d0; }
  .alert-error   { background:#fef2f2; color:#991b1b; border-color:#fecaca; }
  .hidden{ display:none; }
</style>
<div class="nav">
  <a href="?page=updatetourna?id=<?php echo $id;?>">Cấu hình</a>
  <a class="active" href="dashboard.php?page=regulation&id_tourna=<?php echo $id; ?>">Điều lệ</a>
  <a href="?page=addteam&id=<?php echo $id; ?>">Đội tham gia</a>
  <a href="dashboard.php?page=draw&id_tourna=<?php echo $id; ?>&team_count=<?php echo (int)$team_count; ?>">Kết quả bốc thăm</a>
  <a href="schedule.php?id=<?php echo $id;?>">Lịch thi đấu</a>
  <a href="dashboard.php?page=rank&id_tourna=<?php echo $id; ?>">Thống kê - xếp hạng</a>
</div>
<div class="wrap">
  <?php if ($flash): ?>
    <div class="alert <?= $flash['type']==='success'?'alert-success':'alert-error' ?>">
      <?= htmlspecialchars($flash['text']) ?>
    </div>
  <?php endif; ?>

  <form class="card" method="post" enctype="multipart/form-data">
    <h2 class="title">Điều lệ & Lệ phí</h2>

    <div class="grid">
      <div class="field">
        <label class="label">Lệ phí</label>
        <select name="fee_type" id="fee_type">
          <option value="FREE" <?= $fee_type==='FREE'?'selected':''; ?>>Miễn phí</option>
          <option value="PAID" <?= $fee_type==='PAID'?'selected':''; ?>>Có phí</option>
        </select>
        <small class="muted">Chọn “Có phí” để nhập số tiền.</small>
      </div>

      <div class="field" id="amount_wrap">
        <label class="label">Số tiền (đ)</label>
        <input type="number" name="fee_amount" min="0" step="1000"
               value="<?= htmlspecialchars($fee_amount) ?>"
               placeholder="Ví dụ: 900000">
      </div>
    </div>

    <div class="field" style="margin-top:12px">
      <label class="label">Điều lệ (tóm tắt)</label>
      <textarea name="regulation_summary" placeholder="Đối tượng, yêu cầu, quy định, hạn đóng lệ phí, liên hệ BTC..."><?= htmlspecialchars($summary) ?></textarea>
    </div>

    <div class="field" style="margin-top:12px">
      <label class="label">Tệp điều lệ (PDF/Word)</label>
      <input type="file" name="reg_file" accept=".pdf,.doc,.docx">
      <small class="muted">Tối đa 10MB. Chỉ .pdf, .doc, .docx</small>
    </div>

    <div class="actions">
      <button type="submit" class="btn btn-primary" name="btnSaveReg">Lưu</button>
      <a class="btn btn-secondary" href="dashboard.php?page=regulation&id_tourna=<?= (int)$id ?>">Tải lại</a>
    </div>
  </form>
</div>

<script>
  const feeSel = document.getElementById('fee_type');
  const amtWrap = document.getElementById('amount_wrap');
  function toggleAmount(){ amtWrap.classList.toggle('hidden', feeSel.value !== 'PAID'); }
  toggleAmount(); feeSel.addEventListener('change', toggleAmount);
</script>
