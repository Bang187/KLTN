<?php
require_once __DIR__ . '/modelconnect.php';

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
  public function loadSchedule(int $idTourna): array {
    $p = new mconnect(); 
    $c = $p->moketnoi(); 
    $data = [];
    if(!$c) return $data;

    $sql = "SELECT m.*, 
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

}
