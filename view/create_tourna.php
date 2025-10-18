<style>
  form input[type="text"],
  form input[type="date"],
  form input[type="number"],
  form input[type="file"],
  form select {
    width: 100%;
    max-width: 400px;
    padding: 6px 8px;
    margin-top: 4px;
    margin-bottom: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 14px;
  }

  form input[type="submit"],
  form input[type="reset"] {
    padding: 8px 16px;
    margin-right: 8px;
    border: none;
    border-radius: 4px;
    background-color: #4CAF50;
    color: white;
    font-size: 14px;
    cursor: pointer;
  }

  form input[type="submit"]:hover,
  form input[type="reset"]:hover {
    background-color: #45a049;
  }

  table {
    width: 100%;
    max-width: 720px;
    border-collapse: collapse;
    margin-top: 12px;
  }

  table td {
    padding: 8px;
    vertical-align: top;
  }

  table tr:nth-child(even) {
    background-color: #f9f9f9;
  }

  table tr:hover {
    background-color: #f1f1f1;
  }
</style>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../control/controltourna.php';

// Chỉ cho BTC tạo giải
if (empty($_SESSION['id_org'])) {
  echo "<script>alert('Bạn không có quyền tạo giải (thiếu id_org). Hãy đăng nhập tài khoản BTC.'); history.back();</script>";
  exit;
}

$old = ['tengiaidau'=>'','startdate'=>'','enddate'=>''];  // giữ lại giá trị khi lỗi

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnThem'])) {
    $name  = trim($_POST['tengiaidau'] ?? '');
    $start = $_POST['startdate'] ?? null;
    $end   = $_POST['enddate'] ?? null;

    $old['tengiaidau'] = $name;
    $old['startdate']  = $start;
    $old['enddate']    = $end;

    // Validate
    if ($name === '') {
        echo "<script>alert('Tên giải đấu là bắt buộc.');</script>";
    } elseif (!empty($start) && !empty($end) && $start > $end) {
        echo "<script>alert('Ngày bắt đầu phải trước hoặc bằng ngày kết thúc.');</script>";
    } else {
        try {
            $ctl = new cTourna();
            // Controller sẽ tự lấy id_org từ session và xử lý upload mặc định
            $newId = $ctl->createTourna($name, $start ?: null, $end ?: null);

            if ($newId) {
                echo "<script>alert('Tạo giải thành công!'); window.location='dashboard.php?page=man_tourna';</script>";
                exit;
            } else {
                echo "<script>alert('Tạo giải thất bại. Vui lòng thử lại.');</script>";
            }
        } catch (Throwable $e) {
            $msg = addslashes($e->getMessage());
            echo "<script>alert('Lỗi: {$msg}');</script>";
        }
    }
}
?>

<!-- FORM -->
<form action="" method="post" enctype="multipart/form-data" style="max-width:720px;">
  <h2 style="margin:6px 0 12px;">Tạo giải đấu</h2>
  <table>
    <tr>
      <td>
        <label for="tengiaidau">Tên giải đấu (*)</label><br>
        <input type="text" name="tengiaidau" required
               value="<?= htmlspecialchars($old['tengiaidau']) ?>">
      </td>
    </tr>
    <tr>
      <td>
        <label for="startdate">Ngày bắt đầu</label><br>
        <input type="date" name="startdate"
               value="<?= htmlspecialchars($old['startdate']) ?>">
      </td>
    </tr>
    <tr>
      <td>
        <label for="enddate">Ngày kết thúc</label><br>
        <input type="date" name="enddate"
               value="<?= htmlspecialchars($old['enddate']) ?>">
      </td>
    </tr>
    <tr>
      <td>
        <label for="logo">Logo (tùy chọn)</label><br>
        <input type="file" name="hinhlogo" accept=".jpg,.jpeg,.png,.gif,.webp">
      </td>
    </tr>
    <tr>
      <td>
        <label for="banner">Banner (tùy chọn)</label><br>
        <input type="file" name="hinhbanner" accept=".jpg,.jpeg,.png,.gif,.webp">
      </td>
    </tr>
    <tr>
      <td style="padding-top:10px;">
        <input type="submit" value="Tạo mới" name="btnThem">
        <input type="reset"  value="Nhập lại">
      </td>
    </tr>
  </table>
</form>
