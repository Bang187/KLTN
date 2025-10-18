<?php
require_once __DIR__ . '/../model/modelschedule.php';
require_once __DIR__ . '/../model/modeltourna.php';

class cSchedule {
  public function screen(int $idTourna){
    $m  = new mSchedule();
    $mt = new mTourna();

    // Lấy thông tin giải (để lấy location mặc định nếu cần lưu kèm)
    $tourna = $mt->getById($idTourna); // nên trả về ít nhất: id_tourna, (location_id hoặc id_local)

    // --- Sinh cặp đấu từ kết quả bốc thăm
    if (isset($_GET['generate']) && $_GET['generate'] === '1') {
      $m->generateKnockout($idTourna);
      $m->advanceByes($idTourna);
      $this->redir('dashboard.php?page=schedule&id='.$idTourna.'&genok=1');
    }

    // --- Cập nhật phân lịch (Ngày / Giờ / Sân + ghi chú)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_kickoff'])) {
      $mid   = (int)($_POST['id_match'] ?? 0);
      $date  = $_POST['kickoff_date'] ?: null;
      $time  = $_POST['kickoff_time'] ?: null;

      // Chuẩn hoá sân: trim và gộp khoảng trắng
      $pitch = isset($_POST['pitch_label']) ? preg_replace('/\s+/', ' ', trim($_POST['pitch_label'])) : '';
      $ven   = $_POST['venue'] ?: null;

      // Validate: bắt buộc đủ Ngày, Giờ, Sân để UNIQUE làm việc chuẩn
      if ($pitch === '' || !$date || !$time) {
        $msg = rawurlencode('Vui lòng nhập đầy đủ: Ngày, Giờ và Sân (ví dụ: "Sân 1").');
        $this->redir('dashboard.php?page=schedule&id='.$idTourna.'&conflict=1&msg='.$msg);
      }

      // Địa điểm mặc định của giải (không dùng trong UNIQUE nữa, nhưng lưu kèm để tra cứu)
      $loc = null;
      if (isset($tourna['location_id'])) $loc = (int)$tourna['location_id'];
      elseif (isset($tourna['id_local']))  $loc = (int)$tourna['id_local'];

      // Lưu; nếu vi phạm UNIQUE (id_tourna, pitch_label, kickoff_date, kickoff_time) -> trả về 'conflict'
      $rs = $m->updateKickoffFull($mid, $date, $time, $loc, $pitch, $ven);
      if (!$rs['ok'] && (($rs['error'] ?? '') === 'conflict')) {
        $msg = rawurlencode('Khung giờ bị trùng trên cùng sân. Chọn thời điểm khác hoặc đổi sân.');
        $this->redir('dashboard.php?page=schedule&id='.$idTourna.'&conflict=1&msg='.$msg);
      } else {
        $this->redir('dashboard.php?page=schedule&id='.$idTourna.'&saved=1');
      }
    }

    // Tải lịch để hiển thị
    $rounds = $m->loadSchedule($idTourna);

    // View không còn dùng dropdown địa điểm
    $locations = [];

    include __DIR__ . '/../view/schedule.php';
  }

  private function redir(string $url): void {
    if (!headers_sent()) {
      header('Location: '.$url);
      exit;
    }
    echo '<script>location.href="'.htmlspecialchars($url, ENT_QUOTES).'";</script>';
    exit;
  }
}
?>