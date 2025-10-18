<?php
// view/updatetourna.php
error_reporting(0);
require_once __DIR__ . '/../control/controltourna.php';

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) die('Thiếu id');
$id = (int)$_GET['id'];


$ctr  = new cTourna();
$data = $ctr->loadConfigData((int)$_GET['id']);
$T    = $data['tourna'];        // tournament + rule hiện có (nếu có)
$LOCs = $data['locations'];

if (!$T) die('Không tìm thấy giải');

$flash = null;
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_config'])) {
    $flash = $ctr->saveConfig($id, $_POST);
    // load lại dữ liệu sau khi lưu
    $data = $ctr->loadConfigData($id);
    $T    = $data['tourna'];
    $LOCs = $data['locations'];
}

// Giá trị mặc định khi chưa có rule
$format = $T['ruletype'] ?: 'knockout';
$team_count = $T['team_count'] ?? '';
$rr_rounds  = $T['rr_rounds'] ?? 1;
$pointwin   = $T['pointwin']  ?? 3;
$pointdraw  = $T['pointdraw'] ?? 1;
$pointloss  = $T['pointloss'] ?? 0;
$tiebreak   = $T['tiebreak_rule'] ?? 'GD,GF,H2H';
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Cấu hình giải - <?php echo htmlspecialchars($T['tournaName']); ?></title>
<style>
body{font-family:Arial,Helvetica,sans-serif}
.wrap{max-width:980px;margin:16px auto}
.nav{display:flex;gap:10px;padding:8px;background:#f3f3f3;border:1px solid #ddd}
.nav a{text-decoration:none;color:#333;padding:6px 10px;border:1px solid #ccc;border-radius:4px}
.nav a.active{background:#2563eb;color:#fff;border-color:#2563eb}
table{width:100%;border-collapse:collapse}
td{padding:8px;vertical-align:middle}
td:first-child{width:240px;font-weight:bold}
tr:nth-child(odd){background:#fafafa}
.actions{margin-top:10px;display:flex;gap:8px}
input[type=number]{width:120px}
select{min-width:180px}
.flash{padding:10px;margin:10px 0;border:1px solid #ccc;background:#ffffe0}
.hidden{display:none}
  table{width:100%;border-collapse:collapse;table-layout:fixed}    
  td{padding:8px;vertical-align:middle}
  td:first-child{width:240px;font-weight:bold}
  select,input[type=text]{width:100%;max-width:480px}
</style>
<script>
function toggleRR(){
  var fmt=document.getElementById('format').value;
  document.querySelectorAll('.rr-only').forEach(el=>el.style.display=(fmt==='roundrobin')?'':'none');
}
function toggleLocation(){
  var mode=document.querySelector('input[name="location_mode"]:checked').value;
  document.getElementById('loc-existing').style.display = (mode==='existing') ? '' : 'none';
  document.getElementById('loc-new').style.display      = (mode==='new') ? '' : 'none';
}
</script>
</head>
<body onload="toggleRR();toggleLocation();">
<div class="nav">
  <a class="active" href="updatetourna.php?id=<?php echo $id;?>">Cấu hình</a>
  <a href="?page=addteam&id=<?= $id ?>">Đội tham gia</a>
  <a href="dashboard.php?page=draw&id_tourna=<?= $id_tourna ?>&team_count=<?= $teamCount ?>">Kết quả bốc thăm</a>
  <a href="schedule.php?id=<?php echo $id;?>">Lịch thi đấu</a>
  <a href="dashboard.php?page=rank&id_tourna=<?= $id_tourna ?>">Thống kê - xếp hạng</a>
</div>

<div class="wrap">
  <h2>Cấu hình mùa giải: <?php echo htmlspecialchars($T['tournaName']); ?></h2>

  <?php if ($flash): ?>
    <div class="flash"><?php echo htmlspecialchars($flash['message']); ?></div>
  <?php endif; ?>

  <form method="post">
    <table border="1">
      <tr>
        <td>Thể thức thi đấu</td>
        <td>
          <select name="format" id="format" onchange="toggleRR()">
            <option value="roundrobin" <?php echo $format==='roundrobin'?'selected':''; ?>>Vòng tròn</option>
            <option value="knockout"   <?php echo $format==='knockout'?'selected':''; ?>>Loại trực tiếp</option>
          </select>
        </td>
      </tr>
      <tr>
        <td>Số đội tham gia</td>
        <td><input type="number" name="team_count" min="2" value="<?php echo htmlspecialchars($team_count); ?>"></td>
      </tr>

      <tr class="rr-only">
        <td>Số lượt đá vòng tròn</td>
        <td>
          <select name="rr_rounds">
            <?php foreach([1,2] as $opt): ?>
              <option value="<?php echo $opt; ?>" <?php echo ($rr_rounds==$opt)?'selected':''; ?>>
                <?php echo $opt; ?> lượt
              </option>
            <?php endforeach; ?>
          </select>
        </td>
      </tr>
      <tr class="rr-only">
        <td>Điểm thắng</td>
        <td><input type="number" name="pointwin" min="0" value="<?php echo (int)$pointwin; ?>"></td>
      </tr>
      <tr class="rr-only">
        <td>Điểm hòa</td>
        <td><input type="number" name="pointdraw" min="0" value="<?php echo (int)$pointdraw; ?>"></td>
      </tr>
      <tr class="rr-only">
        <td>Điểm thua</td>
        <td><input type="number" name="pointloss" min="0" value="<?php echo (int)$pointloss; ?>"></td>
      </tr>
      <tr class="rr-only">
        <td>Luật tie-break (ưu tiên)</td>
        <td><input type="text" name="tiebreak_rule" value="<?php echo htmlspecialchars($tiebreak); ?>" style="width:260px"></td>
      </tr>

      <tr>
        <td>Địa điểm thi đấu</td>
        <td>
          <label><input type="radio" name="location_mode" value="existing" checked onclick="toggleLocation()"> Chọn sẵn</label>
          &nbsp;&nbsp;
          <label><input type="radio" name="location_mode" value="new" onclick="toggleLocation()"> Thêm mới</label>

          <div id="loc-existing" style="margin-top:8px">
            <select name="id_local">
              <option value="">-- Chưa chọn --</option>
                <?php foreach ($LOCs as $lc): ?>
                <option value="<?= (int)$lc['id_local'] ?>"
                        <?= ($T['id_local'] == $lc['id_local']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($lc['localname'] . (!empty($lc['address']) ? " ({$lc['address']})" : '')) ?>
                </option>
                <?php endforeach; ?>
            </select>
          </div>

          <div id="loc-new" class="hidden" style="margin-top:8px">
            <input type="text" name="localname" placeholder="Tên địa điểm" style="width:260px">
            <input type="text" name="address" placeholder="Địa chỉ (tuỳ chọn)" style="width:360px">
          </div>
        </td>
      </tr>
        <script>
function toggleLocation(){
  var mode = document.querySelector('input[name="location_mode"]:checked').value;
  document.getElementById('loc-existing').style.display = (mode==='existing') ? 'block' : 'none';
  document.getElementById('loc-new').style.display      = (mode==='new') ? 'block' : 'none';
}
document.querySelectorAll('input[name="location_mode"]').forEach(r=>{
  r.addEventListener('change', toggleLocation);
});
window.addEventListener('load', toggleLocation);
</script>
    </table>

    <div class="actions">
      <button type="submit" name="save_config">Lưu</button>
      <button type="reset">Nhập lại</button>
    </div>
  </form>
</div>
</body>
</html>
