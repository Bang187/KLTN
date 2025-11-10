<?php
// view/updatetourna.php
error_reporting(E_ALL);
require_once __DIR__ . '/../control/controltourna.php';

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) die('Thiếu id');
$id  = (int)$_GET['id'];

$ctr  = new cTourna();
$data = $ctr->loadConfigData($id);
$T    = $data['tourna'];        // tournament + rule hiện có (nếu có)
$LOCs = $data['locations'];

if (!$T) die('Không tìm thấy giải');

$flash = null;
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_config'])) {
    // Lưu tất cả config (bao gồm đăng ký trực tuyến)
    $flash = $ctr->saveConfig($id, $_POST);
    // load lại dữ liệu sau khi lưu
    $data = $ctr->loadConfigData($id);
    $T    = $data['tourna'];
    $LOCs = $data['locations'];
}

// Giá trị mặc định khi chưa có rule
$format     = $T['ruletype']    ?: 'knockout';
$team_count = $T['team_count']  ?? '';
$rr_rounds  = $T['rr_rounds']   ?? 1;
$pointwin   = $T['pointwin']    ?? 3;
$pointdraw  = $T['pointdraw']   ?? 1;
$pointloss  = $T['pointloss']   ?? 0;
$tiebreak   = $T['tiebreak_rule'] ?? 'GD,GF,H2H';
//
$hy_group_count  = $T['hy_group_count']  ?? '';  // số bảng
$hy_take_1st     = $T['hy_take_1st']     ?? '';  // số đội nhất bảng vào KO (tổng tất cả bảng)
$hy_take_2nd     = $T['hy_take_2nd']     ?? '';  // số đội nhì bảng vào KO
$hy_take_3rd     = $T['hy_take_3rd']     ?? '';  
$hy_take_4th     = $T['hy_take_4th']     ?? ''; 


// Giá trị cho phần đăng ký trực tuyến
$allow_online_reg = !empty($T['allow_online_reg']) ? 1 : 0;
$regis_open_at    = !empty($T['regis_open_at'])  ? date('Y-m-d\TH:i', strtotime($T['regis_open_at']))  : '';
$regis_close_at   = !empty($T['regis_close_at']) ? date('Y-m-d\TH:i', strtotime($T['regis_close_at'])) : '';
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
  table{width:100%;border-collapse:collapse;table-layout:fixed}
  td{padding:8px;vertical-align:middle}
  td:first-child{width:240px;font-weight:bold}
  tr:nth-child(odd){background:#fafafa}
  .actions{margin-top:10px;display:flex;gap:8px}
  input[type=number]{width:120px}
  select,input[type=text]{width:100%;max-width:480px}
  .flash{padding:10px;margin:10px 0;border:1px solid #ccc;background:#ffffe0}
  .hidden{display:none}
  /* công tắc đẹp nhẹ */
  .switch{position:relative;display:inline-block;width:54px;height:28px;vertical-align:middle}
  .switch input{display:none}
  .slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#ccc;transition:.3s;border-radius:28px}
  .slider:before{position:absolute;content:"";height:22px;width:22px;left:3px;bottom:3px;background:white;transition:.3s;border-radius:50%}
  .switch input:checked + .slider{background:#16a34a}
  .switch input:checked + .slider:before{transform:translateX(26px)}
</style>
<script>
function toggleFormatBlocks(){
  var fmt = document.getElementById('format').value;
  // Các dòng áp dụng cho round robin & hybrid
  document.querySelectorAll('.rr-like').forEach(el=>{
    el.style.display = (fmt==='roundrobin' || fmt==='hybrid') ? '' : 'none';
  });
  // Khối cấu hình riêng của hybrid
  document.getElementById('hybrid-block').style.display = (fmt==='hybrid') ? '' : 'none';
}
function toggleLocation(){
  var mode=document.querySelector('input[name="location_mode"]:checked').value;
  document.getElementById('loc-existing').style.display = (mode==='existing') ? 'block' : 'none';
  document.getElementById('loc-new').style.display      = (mode==='new') ? 'block' : 'none';
}
function toggleRegTime(){
  var on = document.getElementById('allowReg').checked;
  document.querySelectorAll('tr.regtime').forEach(tr=>{
    tr.style.display = on ? '' : 'none';
    tr.querySelectorAll('input').forEach(inp=>{
      inp.disabled = !on;         // không gửi khi tắt
      // KHÔNG xoá value tại đây để không làm mất dữ liệu hiển thị lại sau load
    });
  });
}
window.addEventListener('load', function(){
  toggleFormatBlocks();
  toggleLocation();
  toggleRegTime();
  document.getElementById('allowReg').addEventListener('change', toggleRegTime);
  document.getElementById('format').addEventListener('change', toggleFormatBlocks);
});
</script>


</head>
<body>
<div class="nav">
  <a class="active" href="updatetourna.php?id=<?php echo $id;?>">Cấu hình</a>
  <a href="dashboard.php?page=regulation&id_tourna=<?php echo $id; ?>">Điều lệ</a>
  <a href="?page=addteam&id=<?php echo $id; ?>">Đội tham gia</a>
  <a href="dashboard.php?page=draw&id_tourna=<?php echo $id; ?>&team_count=<?php echo (int)$team_count; ?>">Kết quả bốc thăm</a>
  <a href="schedule.php?id=<?php echo $id;?>">Lịch thi đấu</a>
  <a href="dashboard.php?page=rank&id_tourna=<?php echo $id; ?>">Thống kê - xếp hạng</a>
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
          <select name="format" id="format" onchange="toggleFormatBlocks()">
            <option value="roundrobin" <?php echo $format==='roundrobin'?'selected':''; ?>>Vòng tròn</option>
            <option value="knockout"   <?php echo $format==='knockout'?'selected':''; ?>>Loại trực tiếp</option>
            <option value="hybrid"   <?php echo $format==='hybrid'?'selected':''; ?>>Hỗn hợp (chia bảng)</option>
          </select>
        </td>
      </tr>

      <tr>
        <td>Số đội tham gia</td>
        <td><input type="number" name="team_count" min="2" value="<?php echo htmlspecialchars($team_count); ?>"></td>
      </tr>

      <!-- CẤU HÌNH ĐĂNG KÝ TRỰC TUYẾN -->
      <tr>
        <td>Đăng ký trực tuyến</td>
        <td>
          <label class="switch">
            <input type="checkbox" id="allowReg" name="allow_online_reg" value="1" <?php echo $allow_online_reg?'checked':''; ?>
            onchange="toggleRegTime()" >
            <span class="slider round"></span>
          </label>
        </td>
      </tr>
      <tr class="regtime">
        <td>Mở đăng ký từ</td>
        <td>
          <input type="datetime-local" name="regis_open_at" value="<?php echo $regis_open_at; ?>">
          <small>Để trống = mở ngay khi bật</small>
        </td>
      </tr>

      <tr class="regtime">
        <td>Hạn chót đăng ký</td>
        <td>
          <input type="datetime-local" name="regis_close_at" value="<?php echo $regis_close_at; ?>">
          <small>Để trống = không hạn chót</small>
        </td>
      </tr>
      <!-- HẾT: ĐĂNG KÝ TRỰC TUYẾN -->

      <tr class="rr-like">
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

      <tr class="rr-like">
        <td>Điểm thắng</td>
        <td><input type="number" name="pointwin" min="0" value="<?php echo (int)$pointwin; ?>"></td>
      </tr>
      <tr class="rr-like">
        <td>Điểm hòa</td>
        <td><input type="number" name="pointdraw" min="0" value="<?php echo (int)$pointdraw; ?>"></td>
      </tr>
      <tr class="rr-like">
        <td>Điểm thua</td>
        <td><input type="number" name="pointloss" min="0" value="<?php echo (int)$pointloss; ?>"></td>
      </tr>
      <tr class="rr-like">
        <td>Luật tie-break (ưu tiên)</td>
        <td><input type="text" name="tiebreak_rule" value="<?php echo htmlspecialchars($tiebreak); ?>" style="max-width:260px"></td>
      </tr>
    <!-- HYBRID BLOCK: hiện khi chọn Hỗn hợp (chia bảng) -->
<tr>
  <td colspan="2" style="background:#e5e7eb;font-weight:bold;border-top:2px solid #d1d5db">
    CẤU HÌNH BẢNG ĐẤU VÀ VÒNG KNOCKOUT
  </td>
</tr>
<tbody id="hybrid-block" style="display:none">
  <tr>
    <td>Số bảng</td>
    <td><input type="number" min="1" name="hy_group_count"
               value="<?php echo htmlspecialchars($hy_group_count); ?>" style="width:120px"></td>
  </tr>
  <tr>
    <td>Số đội nhất bảng vào KO (tổng)</td>
    <td><input type="number" min="0" name="hy_take_1st"
               value="<?php echo htmlspecialchars($hy_take_1st); ?>" style="width:120px">
      <small>Ví dụ có 4 bảng, lấy 4 đội nhất ⇒ nhập 4</small>
    </td>
  </tr>
  <tr>
    <td>Số đội nhì bảng vào KO (tổng)</td>
    <td><input type="number" min="0" name="hy_take_2nd"
               value="<?php echo htmlspecialchars($hy_take_2nd); ?>" style="width:120px"></td>
  </tr>
  <tr>
    <td>Số đội hạng 3 vào KO (tổng)</td>
    <td><input type="number" min="0" name="hy_take_3rd"
               value="<?php echo htmlspecialchars($hy_take_3rd); ?>" style="width:120px"></td>
  </tr>
  <tr>
    <td>Số đội hạng 4 vào KO (tổng)</td>
    <td><input type="number" min="0" name="hy_take_4th"
               value="<?php echo htmlspecialchars($hy_take_4th); ?>" style="width:120px"></td>
  </tr>
</tbody>

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
            <input type="text" name="localname" placeholder="Tên địa điểm" style="max-width:260px">
            <input type="text" name="address" placeholder="Địa chỉ (tuỳ chọn)" style="max-width:360px">
          </div>
        </td>
      </tr>
    </table>

    <div class="actions">
      <button type="submit" name="save_config">Lưu</button>
      <button type="reset">Nhập lại</button>
    </div>
  </form>
</div>
</body>
</html>
