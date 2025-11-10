<?php
// view/register_tourna.php
$ROOT = rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/'); // /KltN
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$detailUrl = $ROOT . "/view/tourna_detail.php?id=" . $id;  // fallback
?>
<div style="max-width:900px;margin:24px auto;background:#fff;padding:16px;border-radius:8px">
  <?php if (!empty($msg)): ?>
    <p><?= htmlspecialchars($msg) ?></p>

    <!-- Nút quay lại ưu tiên history, fallback về detailUrl -->
    <p>
      <a href="<?= $detailUrl ?>"
         onclick="if (document.referrer && document.referrer.indexOf(location.origin)===0) { history.back(); return false; }">
        Quay lại chi tiết giải
      </a>
    </p>

  <?php else: ?>
    <h3>Đăng ký tham gia: <?= htmlspecialchars($tourna['tournaName'] ?? ('Giải #'.$id)) ?></h3>

    <?php if (empty($teams)): ?>
      <p>Bạn chưa có đội nào (hoặc tất cả đội của bạn đã đăng ký/được duyệt). Hãy tạo đội trước.</p>
      <p><a href="<?= $ROOT ?>/index.php?page=team">Về trang đội của tôi</a></p>
      <p>
        <a href="<?= $detailUrl ?>"
           onclick="if (document.referrer && document.referrer.indexOf(location.origin)===0) { history.back(); return false; }">
          Quay lại chi tiết giải
        </a>
      </p>
    <?php else: ?>
      <form method="post" action="">
        <label>Chọn đội để đăng ký:</label><br>
        <select name="team_id" required style="min-width:260px">
          <?php foreach ($teams as $t): ?>
            <option value="<?= (int)$t['id_team'] ?>"><?= htmlspecialchars($t['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" name="doRegister">Gửi đăng ký</button>
      </form>

      <p style="margin-top:10px">
        <a href="<?= $detailUrl ?>"
           onclick="if (document.referrer && document.referrer.indexOf(location.origin)===0) { history.back(); return false; }">
          Quay lại chi tiết giải
        </a>
      </p>
    <?php endif; ?>
  <?php endif; ?>
</div>
