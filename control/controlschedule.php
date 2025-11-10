<?php
require_once __DIR__ . '/../model/modelschedule.php';
require_once __DIR__ . '/../model/modeltourna.php';
require_once __DIR__ . '/../model/modelgroup.php';

class cSchedule {
  public function screen(int $idTourna){
    $m  = new mSchedule();
    $mt = new mTourna();

    // Lấy thông tin giải (để lấy location mặc định nếu cần lưu kèm)
    $tourna = $mt->getById($idTourna); // nên trả về ít nhất: id_tourna, (location_id hoặc id_local)

    // --- Sinh cặp đấu từ kết quả bốc thăm
    // if (isset($_GET['generate']) && $_GET['generate'] === 'auto') {
    //   // Lấy rule từ tournament (mTourna->getDetail đã trả về ruletype, rr_rounds)
    //   $tourna = $mt->getDetail($idTourna);

    //   $type = strtolower($tourna['ruletype'] ?? '');
    //   if ($type === 'knockout') {
    //     $m->generateKnockout($idTourna);
    //     $m->advanceByes($idTourna);
    //   } elseif ($type === 'roundrobin') {
    //     $double = ((int)($tourna['rr_rounds'] ?? 1) >= 2);
    //     $m->generateRoundRobin($idTourna, $double); // <-- HÀM MỚI ở modelschedule
    //   } else {
    //     $this->redir('dashboard.php?page=schedule&id='.$idTourna.'&conflict=1&msg='.rawurlencode('Chưa cấu hình thể thức.'));
    //   }

    //   $this->redir('dashboard.php?page=schedule&id='.$idTourna.'&genok=1');
    // }
    if (isset($_GET['generate']) && $_GET['generate'] === 'auto') {
    // Ưu tiên sinh theo bảng (nếu có bảng)
    $mg = new mGroup();
    $hasGroups = method_exists($mg,'listGroups') && count($mg->listGroups($idTourna)) > 0;

    if ($hasGroups) {
        $m->generateGroupsAndPlayoff($idTourna);   // ✅ sinh vòng bảng + playoff
    } else {
        // fallback: KO hoặc RR không bảng
        $tourna = $mt->getDetail($idTourna);
        $type = strtolower($tourna['ruletype'] ?? '');
        if ($type === 'knockout') {
            $m->generateKnockout($idTourna);
            $m->advanceByes($idTourna);
        } else {
            $double = ((int)($tourna['rr_rounds'] ?? 1) >= 2);
            $m->generateRoundRobin($idTourna, $double);
        }
    }
    $this->redir('dashboard.php?page=schedule&id='.$idTourna.'&genok=1');
}
    if (isset($_GET['resolve']) && $_GET['resolve']==='groups') {
    $rs = $m->resolvePlayoffFromStandings($idTourna);
    $this->redir('dashboard.php?page=schedule&id='.$idTourna.'&scoreok=1&msg='.rawurlencode($rs['msg'] ?? ''));
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
    // --- Gắn STT cho từng trận theo từng vòng & tạo map id_match -> STT
$idToSeq = [];              // [id_match] => seq toàn cục
$seq     = 1;
ksort($rounds);             // đảm bảo vòng tăng dần
foreach ($rounds as $rnd => &$list) {
    foreach ($list as &$row) {
        $row['_seq'] = $seq;                 // STT hiển thị
        $idToSeq[(int)$row['id_match']] = $seq;
        $seq++;
    }
}
unset($list, $row); // tránh reference leak
// Tạo tiêu đề vòng đẹp (Vòng bảng vs KO)
// --- Nhận diện ruletype + ngưỡng vòng bảng (nếu có)
$detail        = $mt->getDetail($idTourna);                 // có ruletype, rr_rounds...
$ruleType      = strtolower($detail['ruletype'] ?? '');
$mg            = new mGroup();
$maxGroupRound = method_exists($mg,'maxGroupRoundNo') ? (int)$mg->maxGroupRoundNo($idTourna) : 0;

$roundTitles = [];

// Nhãn KO theo SỐ TRẬN của vòng (matches in round)
$koLabelByMatches = function(int $matches): string {
    return match ($matches) {
        1   => 'Chung kết',
        2   => 'Bán kết',
        4   => 'Tứ kết',
        8   => 'Vòng 1/8',
        16  => 'Vòng 1/16',
        32  => 'Vòng 1/32',
        default => 'Play-off',
    };
};

foreach ($rounds as $rnd => $list) {
    // có trận thuộc bảng?
    $hasGroup = false;
    foreach ($list as $row) {
        $gid = isset($row['_gid']) ? (int)$row['_gid']
                                   : (isset($row['id_group']) ? (int)$row['id_group'] : 0);
        if ($gid > 0) { $hasGroup = true; break; }
    }

    if ($hasGroup) {                        // Vòng bảng
        $roundTitles[$rnd] = 'Vòng '.$rnd;
        continue;
    }

    // Không có id_group:
    // - RR không bảng  -> 'Vòng n'
    // - KO thuần hoặc playoff sau vòng bảng -> nhãn KO theo số trận
    if ($ruleType === 'roundrobin' && $maxGroupRound === 0) {
        $roundTitles[$rnd] = 'Vòng '.$rnd;                 // RR (không bảng)
    } else {
        $roundTitles[$rnd] = $koLabelByMatches(count($list)); // KO
    }
}

// --- Helper thay "Thắng trận {id_match}" -> "Thắng trận {STT}"
$prettyPlaceholder = function (?string $ph) use ($idToSeq) {
    if (!$ph) return null;
    return preg_replace_callback('/Thắng trận\s+(\d+)/u', function($m) use ($idToSeq) {
        $mid = (int)$m[1];
        $seq = $idToSeq[$mid] ?? $mid; // fallback nếu chưa có
        return 'Thắng trận '.$seq;
    }, $ph);
};
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
  // Vòng bảng
    // Sinh lịch vòng bảng cho cả giải
    public function generateGroupStage(int $idTourna): array {
        $mg = new mGroup();
        $ms = new mSchedule();
        $mt = new mTourna();

        // Lấy default location của giải (nếu có để set sẵn)
        $t = $mt->getTournamentById($idTourna);
        $defaultLoc = isset($t['id_local']) ? (int)$t['id_local'] : null;

        // Xoá lịch vòng bảng cũ để sinh lại
        $ms->deleteGroupStage($idTourna);

        $groups = $mg->listGroups($idTourna);
        if (empty($groups)) return ['ok'=>false, 'msg'=>'Chưa có bảng nào.'];

        foreach ($groups as $g) {
            $teams = $mg->listTeamsInGroup((int)$g['id_group']); // theo slot
            // Lấy danh sách id_team theo slot
            $order = [];
            foreach ($teams as $row) {
                if (!empty($row['id_team'])) $order[] = (int)$row['id_team'];
            }
            // Nếu số đội lẻ -> thêm BYE (0)
            $n = count($order);
            $hasBye = false;
            if ($n % 2 === 1) { $order[] = 0; $n++; $hasBye = true; }

            if ($n < 2) continue;

            // Thuật toán "circle method"
            $half = $n / 2;
            $arr  = $order;
            $round = 1;

            $roundsToGenerate = 1; // 1 lượt (home/away 1)
            // Nếu muốn 2 lượt trong bảng, đổi =2 và lặp thêm lượt đảo sân.

            for ($r=0; $r<$roundsToGenerate; $r++) {
                $A = $arr;
                for ($i=0; $i<$n-1; $i++) {
                    for ($j=0; $j<$half; $j++) {
                        $home = $A[$j];
                        $away = $A[$n-1-$j];
                        if ($home==0 || $away==0) continue; // BYE

                        // Đảo sân đơn giản cho cân bằng (tuỳ chọn)
                        if ($j % 2 == 1) { $tmp = $home; $home = $away; $away = $tmp; }

                        $ms->insertMatch([
                            'id_tourna'     => $idTourna,
                            'id_group'      => (int)$g['id_group'],
                            'round_no'      => $round,
                            'home_team_id'  => $home,
                            'away_team_id'  => $away,
                            'kickoff_date'  => null,        // để BTC tự xếp ngày/giờ sau
                            'location_id'   => $defaultLoc, // set sẵn sân mặc định nếu có
                            'pitch_label'   => null
                        ]);
                    }
                    $round++;

                    // Rotate (giữ nguyên phần tử 0)
                    $fixed = $A[0];
                    $tail  = array_slice($A, 1);
                    array_unshift($tail, array_pop($tail));
                    $A = array_merge([$fixed], $tail);
                }

                // lượt 2 (nếu cần): đảo sân các cặp vừa sinh
                // có thể sinh bằng cách lặp lại toàn bộ và hoán vị home/away.
            }
        }

        return ['ok'=>true, 'msg'=>'Đã sinh lịch vòng bảng'];
    }
    public function genGroupSchedule(int $idTourna): array {
    $ms = new mSchedule();
    return $ms->generateGroupsAndPlayoff($idTourna);
  }
}
?>