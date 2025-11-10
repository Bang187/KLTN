<?php
require_once __DIR__ . '/modelconnect.php';
require_once __DIR__ . '/modelgroup.php';
require_once __DIR__ . '/modeltourna.php';
require_once __DIR__ . '/modelrank.php';

class mSchedule {
  // Đọc thứ tự slot đã bốc thăm (1..N) -> [id_team]
  private function loadDrawOrder(mysqli $c, int $idTourna): array {
    $teams = [];
    $sql = "SELECT slot_no, id_team FROM draw_slot WHERE id_tourna=? ORDER BY slot_no";
    $stm = mysqli_prepare($c,$sql);
    mysqli_stmt_bind_param($stm,"i",$idTourna);
    mysqli_stmt_execute($stm);
    $res = mysqli_stmt_get_result($stm);
    while($r = $res->fetch_assoc()){
      if (!empty($r['id_team'])) $teams[] = (int)$r['id_team'];
    }
    mysqli_stmt_close($stm);
    return $teams;
  }

  // Xóa lịch cũ của 1 giải
  private function purgeOld(mysqli $c, int $idTourna): void {
    $stm = mysqli_prepare($c, "DELETE FROM `match` WHERE id_tourna=?");
    mysqli_stmt_bind_param($stm,"i",$idTourna);
    mysqli_stmt_execute($stm);
    mysqli_stmt_close($stm);
  }

  // Tạo 1 trận
  private function insertMatch(mysqli $c, int $idTourna, int $round, ?int $homeId, ?int $awayId, ?string $homePH=null, ?string $awayPH=null): int {
    $sql = "INSERT INTO `match`(id_tourna, round_no, home_team_id, away_team_id, home_placeholder, away_placeholder)
            VALUES (?,?,?,?,?,?)";
    $stm = mysqli_prepare($c,$sql);
    mysqli_stmt_bind_param($stm,"iiisss",$idTourna,$round,$homeId,$awayId,$homePH,$awayPH);
    mysqli_stmt_execute($stm);
    $id = mysqli_insert_id($c);
    mysqli_stmt_close($stm);
    return $id;
  }

  // Sinh giải loại trực tiếp từ draw_slot: 1-2, 3-4, ...; rồi vòng 2 ghép "Thắng trận x"
  public function generateKnockout(int $idTourna): bool {
    $p = new mconnect(); 
    $c = $p->moketnoi(); 
    if(!$c) return false;

    mysqli_begin_transaction($c);
    try {
      $this->purgeOld($c, $idTourna);

      $order = $this->loadDrawOrder($c, $idTourna);   // [teamId1, teamId2, ...]
      $n = count($order);
      if ($n < 2) { mysqli_rollback($c); $p->dongketnoi($c); return false; }

      // --- Vòng 1
      $round = 1;
      $roundMatchIds = []; // lưu id trận của vòng hiện tại
      for ($i=0; $i<$n; $i+=2) {
        $home = $order[$i]   ?? null;
        $away = $order[$i+1] ?? null;
        $mid = $this->insertMatch($c, $idTourna, $round, $home, $away);
        $roundMatchIds[] = $mid;
      }

      // --- Các vòng tiếp theo
      $current = $roundMatchIds;
      $round++;
      while (count($current) > 1) {
        $next = [];
        for ($i=0; $i<count($current); $i+=2) {
          $m1 = $current[$i];
          $m2 = $current[$i+1] ?? null;

          $homePH = "Thắng trận " . $m1;
          if ($m2 === null) {
            // số trận lẻ: cho 1 bye (ít gặp nếu N là lũy thừa 2)
            $mid = $this->insertMatch($c, $idTourna, $round, null, null, $homePH, "BYE");
          } else {
            $awayPH = "Thắng trận " . $m2;
            $mid = $this->insertMatch($c, $idTourna, $round, null, null, $homePH, $awayPH);
          }
          $next[] = $mid;
        }
        $current = $next;
        $round++;
      }

      mysqli_commit($c);
      $p->dongketnoi($c);
      return true;

    } catch (\Throwable $e){
      mysqli_rollback($c);
      $p->dongketnoi($c);
      return false;
    }
  }

  // Tải lịch: group theo round
  // public function loadSchedule(int $idTourna): array {
  //   $p = new mconnect(); 
  //   $c = $p->moketnoi(); 
  //   $data = [];
  //   if(!$c) return $data;

  //   $sql = "SELECT m.*, 
  //                  th.teamName AS home_name, 
  //                  ta.teamName AS away_name
  //           FROM `match` m
  //           LEFT JOIN team th ON th.id_team = m.home_team_id
  //           LEFT JOIN team ta ON ta.id_team = m.away_team_id
  //           WHERE m.id_tourna=?
  //           ORDER BY m.round_no, m.id_match";
  //   $stm = mysqli_prepare($c,$sql);
  //   mysqli_stmt_bind_param($stm,"i",$idTourna);
  //   mysqli_stmt_execute($stm);
  //   $res = mysqli_stmt_get_result($stm);
  //   while($r = $res->fetch_assoc()){
  //     $data[(int)$r['round_no']][] = $r;
  //   }
  //   mysqli_stmt_close($stm);
  //   $p->dongketnoi($c);
  //   return $data;
  // }
public function loadSchedule(int $idTourna): array {
  $p = new mconnect(); 
  $c = $p->moketnoi(); 
  $data = [];
  if(!$c) return $data;

  $sql = "SELECT 
            m.id_match, m.id_tourna, m.round_no,
            m.id_group,                           -- giữ nguyên cột gốc
            COALESCE(m.id_group, 0) AS _gid,      -- alias an toàn
            m.leg_no, m.home_team_id, m.away_team_id,
            m.home_placeholder, m.away_placeholder,
            m.kickoff_date, m.kickoff_time, m.location_id, m.pitch_label, m.venue,
            m.home_score, m.away_score, m.status,
            th.teamName AS home_name, 
            ta.teamName AS away_name
          FROM `match` m
          LEFT JOIN team th ON th.id_team = m.home_team_id
          LEFT JOIN team ta ON ta.id_team = m.away_team_id
          WHERE m.id_tourna=?
          ORDER BY m.round_no, m.id_match";
  $stm = mysqli_prepare($c,$sql);
  mysqli_stmt_bind_param($stm,"i",$idTourna);
  mysqli_stmt_execute($stm);
  $res = mysqli_stmt_get_result($stm);
  while($r = $res->fetch_assoc()){
    // gom theo round
    $data[(int)$r['round_no']][] = $r;
  }
  mysqli_stmt_close($stm);
  $p->dongketnoi($c);
  return $data;
}


  // Cập nhật ngày/giờ/sân (nút “Lịch”)
  public function updateKickoff(int $idMatch, ?string $date, ?string $time, ?string $venue): bool {
    $p = new mconnect(); $c = $p->moketnoi(); if(!$c) return false;
    $sql = "UPDATE `match` SET kickoff_date=?, kickoff_time=?, venue=? WHERE id_match=?";
    $stm = mysqli_prepare($c,$sql);
    mysqli_stmt_bind_param($stm,"sssi",$date,$time,$venue,$idMatch);
    $ok = mysqli_stmt_execute($stm);
    mysqli_stmt_close($stm);
    $p->dongketnoi($c);
    return $ok;
  }

  // Nhập kết quả 
  public function updateScore(int $idMatch, int $hs, int $as, string $status='played'): bool {
    $p = new mconnect(); $c=$p->moketnoi(); if(!$c) return false;
    $sql = "UPDATE `match` SET home_score=?, away_score=?, status=? WHERE id_match=?";
    $stm = mysqli_prepare($c,$sql);
    mysqli_stmt_bind_param($stm,"iisi",$hs,$as,$status,$idMatch);
    $ok = mysqli_stmt_execute($stm);
    mysqli_stmt_close($stm);
    $p->dongketnoi($c);
    return $ok;
  }
  public function loadLocations(): array {
    $p = new mconnect(); $c = $p->moketnoi(); $rows=[];
    if(!$c) return $rows;
    $res = $c->query("SELECT id_local, localName FROM location ORDER BY localName");
    while($r = $res->fetch_assoc()) $rows[] = $r;
    $p->dongketnoi($c);
    return $rows;
  }

  // Trả về ['ok'=>true] hoặc ['ok'=>false, 'error'=>'conflict'] nếu trùng lịch (vi phạm UNIQUE)
  public function updateKickoffFull(int $idMatch, ?string $date, ?string $time, ?int $locationId, ?string $pitchLabel, ?string $venue): array {
    $p = new mconnect(); $c = $p->moketnoi(); if(!$c) return ['ok'=>false,'error'=>'db'];
    $sql = "UPDATE `match`
            SET kickoff_date=?, kickoff_time=?, location_id=?, pitch_label=?, venue=?
            WHERE id_match=?";
    $stm = mysqli_prepare($c,$sql);
    mysqli_stmt_bind_param($stm,"ssissi",$date,$time,$locationId,$pitchLabel,$venue,$idMatch);

    $ok = @mysqli_stmt_execute($stm);
    $errno = mysqli_errno($c);
    mysqli_stmt_close($stm);
    $p->dongketnoi($c);

    if(!$ok && $errno==1062) return ['ok'=>false,'error'=>'conflict']; // UNIQUE violated
    return ['ok'=>$ok];
  }

  // Nhập tỉ số + điền đội thắng vào vòng kế theo placeholder "Thắng trận {id_match}"
  public function setResultAndPropagate(int $idMatch, int $homeScore, int $awayScore): bool {
    $p = new mconnect(); $c = $p->moketnoi(); if(!$c) return false;
    mysqli_begin_transaction($c);
    try{
      // 1) Cập nhật trận hiện tại
      $sql="UPDATE `match` SET home_score=?, away_score=?, status='played' WHERE id_match=?";
      $stm = mysqli_prepare($c,$sql);
      mysqli_stmt_bind_param($stm,"iii",$homeScore,$awayScore,$idMatch);
      mysqli_stmt_execute($stm);
      mysqli_stmt_close($stm);

      // 2) xác định đội thắng
      $winTeamId = null;
      $res = $c->query("SELECT home_team_id, away_team_id FROM `match` WHERE id_match=".$idMatch." FOR UPDATE");
      if($row = $res->fetch_assoc()){
        if ($homeScore > $awayScore) $winTeamId = (int)$row['home_team_id'];
        elseif ($awayScore > $homeScore) $winTeamId = (int)$row['away_team_id'];
        else $winTeamId = null; // hoà: tuỳ bạn xử lý thêm (pen, extra…)
      }

      if($winTeamId){
        // 3) Điền vào trận vòng sau nếu có placeholder trỏ tới trận hiện tại
        $ph = "Thắng trận ".$idMatch;

        // 3a) nếu placeholder đang ở vị trí chủ nhà
        $sql="UPDATE `match`
              SET home_team_id=?, home_placeholder=NULL
              WHERE home_placeholder=? LIMIT 1";
        $stm = mysqli_prepare($c,$sql);
        mysqli_stmt_bind_param($stm,"is",$winTeamId,$ph);
        mysqli_stmt_execute($stm);
        mysqli_stmt_close($stm);

        // 3b) nếu placeholder ở vị trí khách
        $sql="UPDATE `match`
              SET away_team_id=?, away_placeholder=NULL
              WHERE away_placeholder=? LIMIT 1";
        $stm = mysqli_prepare($c,$sql);
        mysqli_stmt_bind_param($stm,"is",$winTeamId,$ph);
        mysqli_stmt_execute($stm);
        mysqli_stmt_close($stm);
      }

      mysqli_commit($c);
      $p->dongketnoi($c);
      return true;

    }catch(\Throwable $e){
      mysqli_rollback($c);
      $p->dongketnoi($c);
      return false;
    }
  }
public function advanceByes(int $idTourna): int {
  $p = new mconnect(); 
  $c = $p->moketnoi(); 
  if (!$c) return 0;

  $advanced = 0;

  // tìm các trận chưa played, một bên có đội thật, bên kia NULL hoặc placeholder 'BYE'
  $sql = "SELECT id_match, home_team_id, away_team_id, home_placeholder, away_placeholder
          FROM `match`
          WHERE id_tourna=?
            AND (status IS NULL OR status='scheduled')
            AND (
                 (home_team_id IS NOT NULL AND (away_team_id IS NULL OR away_placeholder='BYE'))
              OR (away_team_id IS NOT NULL AND (home_team_id IS NULL OR home_placeholder='BYE'))
            )";

  $stm = mysqli_prepare($c,$sql);
  mysqli_stmt_bind_param($stm,"i",$idTourna);
  mysqli_stmt_execute($stm);
  $res = mysqli_stmt_get_result($stm);

  $rows=[];
  while($r = $res->fetch_assoc()) $rows[] = $r;
  mysqli_stmt_close($stm);
  $p->dongketnoi($c);

  // gọi setResultAndPropagate cho từng trận BYE
  foreach ($rows as $r){
    $mid = (int)$r['id_match'];
    $homeHas = !empty($r['home_team_id']);
    $awayHas = !empty($r['away_team_id']);

    if ($homeHas && !$awayHas) {
      // chủ nhà thắng BYE
      $this->setResultAndPropagate($mid, 1, 0);
      $advanced++;
    } elseif ($awayHas && !$homeHas) {
      // khách thắng BYE
      $this->setResultAndPropagate($mid, 0, 1);
      $advanced++;
    } elseif ($homeHas && $awayHas) {
      // không phải BYE
    }
  }

  return $advanced;
}
//  Thể thức chỉ vòng tròn
// ---- Helpers dùng chung ----

// Có cột trong bảng không? (để biết có leg_no hay không)
private function columnExists(mysqli $c, string $table, string $col): bool {
  $rs = $c->query("SHOW COLUMNS FROM `{$table}` LIKE '{$col}'");
  return $rs && $rs->num_rows > 0;
}

// Insert trận: tự nhận biết có cột leg_no hay không
private function insertMatchFlex(
  mysqli $c,
  int $idTourna, int $round,
  ?int $homeId, ?int $awayId,
  ?string $homePH=null, ?string $awayPH=null,
  ?int $legNo=null
): int {
  static $hasLeg = null;
  if ($hasLeg === null) $hasLeg = $this->columnExists($c,'match','leg_no');

  if ($hasLeg) {
    $sql = "INSERT INTO `match`(id_tourna, round_no, leg_no, home_team_id, away_team_id, home_placeholder, away_placeholder)
            VALUES (?,?,?,?,?,?,?)";
    $stm = mysqli_prepare($c,$sql);
    mysqli_stmt_bind_param($stm,"iiiiiss",$idTourna,$round,$legNo,$homeId,$awayId,$homePH,$awayPH);
  } else {
    $sql = "INSERT INTO `match`(id_tourna, round_no, home_team_id, away_team_id, home_placeholder, away_placeholder)
            VALUES (?,?,?,?,?,?)";
    $stm = mysqli_prepare($c,$sql);
    mysqli_stmt_bind_param($stm,"iiiiss",$idTourna,$round,$homeId,$awayId,$homePH,$awayPH);
  }
  mysqli_stmt_execute($stm);
  $id = mysqli_insert_id($c);
  mysqli_stmt_close($stm);
  return $id;
}

// Lấy danh sách đội đã duyệt (fallback nếu không dùng draw_slot)
private function loadApprovedTeams(mysqli $c, int $idTourna): array {
  $ids = [];
  $sql = "SELECT id_team
          FROM tournament_team
          WHERE id_tourna=? AND reg_status='approved'
          ORDER BY id_team";
  $stm = mysqli_prepare($c,$sql);
  mysqli_stmt_bind_param($stm,"i",$idTourna);
  mysqli_stmt_execute($stm);
  $res = mysqli_stmt_get_result($stm);
  while($r = $res->fetch_assoc()) $ids[] = (int)$r['id_team'];
  mysqli_stmt_close($stm);
  return $ids;
}

// Ưu tiên thứ tự draw_slot nếu có; nếu không, dùng danh sách đội đã duyệt
private function loadOrderForScheduling(mysqli $c, int $idTourna): array {
  $order = $this->loadDrawOrder($c, $idTourna); // đã có sẵn ở file của bạn
  if (count($order) === 0) $order = $this->loadApprovedTeams($c, $idTourna);
  return $order;
}

/**
 * Sinh lịch Vòng tròn (Round-Robin):
 *  - $double = false: 1 lượt (mỗi cặp gặp 1 lần)
 *  - $double = true : 2 lượt (lượt về đảo sân, round_no nối tiếp)
 *  - Nếu số đội lẻ -> thêm BYE (0) để xoay vòng, không tạo trận chứa BYE
 *  - Gán leg_no=1 cho lượt đi, leg_no=2 cho lượt về (nếu DB có cột leg_no)
 */
public function generateRoundRobin(int $idTourna, bool $double=false): bool {
  $p = new mconnect();
  $c = $p->moketnoi(); 
  if (!$c) return false;

  mysqli_begin_transaction($c);
  try {
    // Xoá lịch cũ của giải
    $this->purgeOld($c, $idTourna);

    // Lấy thứ tự đội để xoay vòng
    $teams = $this->loadOrderForScheduling($c, $idTourna);
    $n = count($teams);
    if ($n < 2) { mysqli_rollback($c); $p->dongketnoi($c); return false; }

    // Nếu lẻ, thêm BYE (0)
    if ($n % 2 === 1) { $teams[] = 0; $n++; }

    // Thuật toán "circle method"
    $fixed = $teams[0];
    $rest  = array_slice($teams, 1); // n-1 phần tử
    $R = $n - 1;                     // số vòng lượt đi
    $round = 1;

    for ($r = 0; $r < $R; $r++) {
      // Tạo cặp cho vòng $round
      $left  = [$fixed, ...array_slice($rest, 0, intdiv($n-2,2))];
      $right = array_reverse(array_slice($rest, intdiv($n-2,2)));

      foreach ($right as $i => $B) {
        $A = $left[$i];

        if ($A === 0 || $B === 0) continue; // bỏ BYE

        // Cân bằng sân: vòng chẵn đảo nhà/khách
        $home = ($r % 2 === 0) ? $A : $B;
        $away = ($r % 2 === 0) ? $B : $A;

        $this->insertMatchFlex($c, $idTourna, $round, $home, $away, null, null, 1);
      }

      // Xoay mảng rest sang phải 1 bước
      $last = array_pop($rest);
      array_unshift($rest, $last);

      $round++;
    }

    // Lượt về (nếu có): đảo sân, round_no tiếp tục tăng
    if ($double) {
      $startRound = $R + 1;

      // Lấy lại trận lượt đi để đảo sân
      $sql = "SELECT round_no, home_team_id, away_team_id
              FROM `match`
              WHERE id_tourna=?
              ORDER BY round_no, id_match";
      $stm = mysqli_prepare($c,$sql);
      mysqli_stmt_bind_param($stm,"i",$idTourna);
      mysqli_stmt_execute($stm);
      $res = mysqli_stmt_get_result($stm);
      $byRound = [];
      while($r = $res->fetch_assoc()){
        $byRound[(int)$r['round_no']][] = $r;
      }
      mysqli_stmt_close($stm);

      $rr = $startRound;
      for ($r=1; $r <= $R; $r++) {
        if (empty($byRound[$r])) { $rr++; continue; }
        foreach ($byRound[$r] as $m) {
          $home = (int)$m['away_team_id']; // đảo sân
          $away = (int)$m['home_team_id'];
          $this->insertMatchFlex($c, $idTourna, $rr, $home, $away, null, null, 2);
        }
        $rr++;
      }
    }

    mysqli_commit($c);
    $p->dongketnoi($c);
    return true;

  } catch (\Throwable $e) {
    mysqli_rollback($c);
    $p->dongketnoi($c);
    return false;
  }
}
// vòng bảng
  // Xoá lịch vòng bảng của giải (không đụng KO)
  public function deleteGroupStage(int $idTourna): bool {
    $c = (new mConnect())->moKetNoi(); if(!$c) return false;
    $st = $c->prepare("DELETE FROM `match` WHERE id_tourna=? AND id_group IS NOT NULL");
    $st->bind_param('i',$idTourna);
    $ok = $st->execute();
    $st->close(); $c->close();
    return $ok;
  }

  // Sinh lịch round-robin cho MỘT bảng
  public function generateOneGroup(int $idTourna, int $idGroup, array $teamIds, ?int $defaultLoc = null, int $rounds = 1): void {
    $c = (new mConnect())->moKetNoi(); if(!$c) return;

    // n đội, nếu lẻ thì thêm BYE=0
    $order = array_values($teamIds);
    $n = count($order);
    if ($n % 2 === 1) { $order[] = 0; $n++; }
    if ($n < 2) { $c->close(); return; }

    $half  = $n/2;
    $arr   = $order;
    $round = 1;

    for ($loop=0; $loop<$rounds; $loop++) {
      $A = $arr;
      for ($i=0; $i<$n-1; $i++) {
        for ($j=0; $j<$half; $j++) {
          $home = $A[$j];
          $away = $A[$n-1-$j];
          if ($home==0 || $away==0) continue; // BYE

          // đổi sân nhẹ cho cân bằng
          if ($j % 2 == 1) { $tmp=$home; $home=$away; $away=$tmp; }

          // dùng HÀM CŨ để chèn
          $matchId = $this->insertMatch($c, $idTourna, $round, $home, $away, null, null);

          // gắn id_group + default status/location nếu bạn muốn
          $upd = $c->prepare("UPDATE `match` SET id_group=?, status='scheduled'".($defaultLoc?' ,location_id=?':'')." WHERE id_match=?");
          if ($defaultLoc) { $upd->bind_param('iii', $idGroup, $defaultLoc, $matchId); }
          else { $upd->bind_param('ii', $idGroup, $matchId); }
          $upd->execute(); $upd->close();
        }
        $round++;

        // xoay mảng (circle method)
        $fixed = $A[0];
        $tail  = array_slice($A,1);
        array_unshift($tail, array_pop($tail));
        $A = array_merge([$fixed], $tail);
      }

      // lượt về (nếu rounds=2): đảo sân toàn bộ – cách đơn giản là lặp lần 2 và đổi home/away ở chỗ trên,
      // hoặc đổi $j%2 logic. Ở đây mình để tùy biến qua tham số $rounds.
    }

    $c->close();
  }

  // Sinh lịch CHO TOÀN BỘ các bảng trong 1 giải
  public function generateAllGroups(int $idTourna): array {
    $mg = new mGroup();

    $groups = $mg->listGroups($idTourna);
    if (empty($groups)) return ['ok'=>false,'msg'=>'Chưa có bảng nào'];

    // Lấy sân mặc định của giải (nếu có) để set luôn location_id
    $mt = new mTourna();
    $t  = $mt->getTournamentById($idTourna);
    $defaultLoc = isset($t['id_local']) ? (int)$t['id_local'] : null;

    // Xoá lịch vòng bảng cũ để sinh lại
    $this->deleteGroupStage($idTourna);

    foreach ($groups as $g) {
      $rows = $mg->listTeamsInGroup((int)$g['id_group']); // slot_no, id_team
      $teamIds = [];
      foreach ($rows as $r) if (!empty($r['id_team'])) $teamIds[] = (int)$r['id_team'];

      $this->generateOneGroup($idTourna, (int)$g['id_group'], $teamIds, $defaultLoc, 1/*1 lượt; đổi 2 nếu muốn đi-về*/);
    }
    return ['ok'=>true,'msg'=>'Đã sinh lịch vòng bảng'];
  }
// Thêm trong class mSchedule

// Insert trận KO với placeholder home/away
private function insertKoWithPH(mysqli $c, int $idTourna, int $round, string $homePH, string $awayPH): int {
  $sql = "INSERT INTO `match`(id_tourna, round_no, home_placeholder, away_placeholder)
          VALUES (?,?,?,?)";
  $st = $c->prepare($sql);
  $st->bind_param('iiss', $idTourna, $round, $homePH, $awayPH);
  $st->execute();
  $id = $c->insert_id;
  $st->close();
  return $id;
}

// Tạo playoff từ các bảng bằng placeholder (A1 vs B2 ...)
// Hỗ trợ: 2 bảng -> Bán kết; 4 bảng -> Tứ kết; 8 bảng -> Vòng 1/8
private function generatePlayoffFromGroups(int $idTourna): array {
  $mg = new mGroup();
  $groups = $mg->listGroupsSimple($idTourna);   // id_group, label, sort_order
  if (count($groups) < 2) return ['ok'=>false,'msg'=>'Cần >=2 bảng để tạo playoff'];

  // map label theo sort_order: A,B,C,…
  usort($groups, fn($a,$b)=>$a['sort_order']<=>$b['sort_order']);
  $labels = array_column($groups,'label');       // ['A','B','C','D',...]

  // pattern bắt cặp theo số bảng
  $n = count($labels);
  $pairs = [];

  if ($n == 2) {                 // 2 bảng: tạo bán kết + CK
    // SF: A1-B2, B1-A2
    $pairs = [
      ['Nhất bảng '.$labels[0], 'Nhì bảng '.$labels[1]],
      ['Nhất bảng '.$labels[1], 'Nhì bảng '.$labels[0]],
    ];
    $stage = 'Bán kết';
  }
  elseif ($n == 4) {             // 4 bảng: tạo tứ kết
    // QF: A1-B2, B1-A2, C1-D2, D1-C2
    $pairs = [
      ['Nhất bảng A', 'Nhì bảng B'],
      ['Nhất bảng B', 'Nhì bảng A'],
      ['Nhất bảng C', 'Nhì bảng D'],
      ['Nhất bảng D', 'Nhì bảng C'],
    ];
    $stage = 'Tứ kết';
  }
  elseif ($n == 8) {             // 8 bảng: vòng 1/8
    $stage = 'Vòng 1/8';
    $pairs = [
      ['Nhất bảng A','Nhì bảng H'],
      ['Nhất bảng B','Nhì bảng G'],
      ['Nhất bảng C','Nhì bảng F'],
      ['Nhất bảng D','Nhì bảng E'],
      ['Nhất bảng E','Nhì bảng D'],
      ['Nhất bảng F','Nhì bảng C'],
      ['Nhất bảng G','Nhì bảng B'],
      ['Nhất bảng H','Nhì bảng A'],
    ];
  } else {
    return ['ok'=>false,'msg'=>"Chưa có pattern playoff cho {$n} bảng"];
  }

  $p = new mConnect(); $c = $p->moKetNoi(); if(!$c) return ['ok'=>false,'msg'=>'DB'];
  mysqli_begin_transaction($c);
  try{
    // playoff bắt đầu từ round kế tiếp round lớn nhất của vòng bảng
    $startRound = $mg->maxGroupRoundNo($idTourna) + 1;

    // vòng đầu tiên
    $createdIds = [];
    foreach ($pairs as $pr) {
      $mid = $this->insertKoWithPH($c, $idTourna, $startRound, $pr[0], $pr[1]);
      $createdIds[] = $mid;
    }

    // các vòng sau (bán kết/chung kết) ghép "Thắng trận x"
    $current = $createdIds; $round = $startRound + 1;
    while (count($current) > 1) {
      $next = [];
      for ($i=0; $i<count($current); $i+=2) {
        $m1 = $current[$i];
        $m2 = $current[$i+1] ?? null;
        $ph1 = "Thắng trận ".$m1;
        $ph2 = $m2 ? ("Thắng trận ".$m2) : "BYE";
        $next[] = $this->insertKoWithPH($c, $idTourna, $round, $ph1, $ph2);
      }
      $current = $next;
      $round++;
    }

    mysqli_commit($c); $p->dongKetNoi($c);
    return ['ok'=>true,'msg'=>'Đã sinh playoff (placeholder)'];

  }catch(\Throwable $e){
    mysqli_rollback($c); $p->dongKetNoi($c);
    return ['ok'=>false,'msg'=>'TX error'];
  }
}

//  sinh vòng bảng + sinh playoff 
public function generateGroupsAndPlayoff(int $idTourna): array {
  $g = $this->generateAllGroups($idTourna);
  if (empty($g['ok'])) return $g;
  return $this->generatePlayoffFromGroups($idTourna);
}
  // Điền đội vào playoff từ BXH bảng (thay placeholder "Nhất bảng X", "Nhì bảng Y")
public function resolvePlayoffFromStandings(int $idTourna): array {
    $mr = new mRank();
    $all = $mr->getAllGroupStandings($idTourna); // ['A'=>rows,'B'=>rows,...]
    if (empty($all)) return ['ok'=>false,'msg'=>'Chưa có BXH bảng.'];

    $map=[];
    foreach ($all as $label=>$rows){
        if (!empty($rows[0])) $map["Nhất bảng ".$label] = (int)$rows[0]['id_team'];
        if (!empty($rows[1])) $map["Nhì bảng ".$label]  = (int)$rows[1]['id_team'];
    }
    if (empty($map)) return ['ok'=>false,'msg'=>'Thiếu dữ liệu Nhất/Nhì.'];

    $c=(new mConnect())->moKetNoi(); if(!$c) return ['ok'=>false,'msg'=>'DB'];
    $aff=0;
    foreach ($map as $ph=>$tid){
        $st=$c->prepare("UPDATE `match` SET home_team_id=?, home_placeholder=NULL WHERE id_tourna=? AND home_placeholder=?");
        $st->bind_param('iis',$tid,$idTourna,$ph); $st->execute(); $aff += $st->affected_rows; $st->close();
        $st=$c->prepare("UPDATE `match` SET away_team_id=?, away_placeholder=NULL WHERE id_tourna=? AND away_placeholder=?");
        $st->bind_param('iis',$tid,$idTourna,$ph); $st->execute(); $aff += $st->affected_rows; $st->close();
    }
    $c->close();
    return ['ok'=>true,'msg'=>"Đã khóa & điền $aff vị trí playoff"];
}


}
