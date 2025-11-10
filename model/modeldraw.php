<?php
include_once(__DIR__ . '/modelconnect.php');
class mDraw {
  //     public function ensureSlots($idTourna, $teamCount){
  //   $p=new mconnect(); $c=$p->moketnoi();
  //   if(!$c) return false;
  //   $q="SELECT COUNT(*) c FROM draw_slot WHERE id_tourna=?"; 
  //   $stm=mysqli_prepare($c,$q); mysqli_stmt_bind_param($stm,"i",$idTourna);
  //   mysqli_stmt_execute($stm); $res=mysqli_stmt_get_result($stm);
  //   $row=mysqli_fetch_assoc($res); $have=(int)$row['c']; mysqli_stmt_close($stm);
  //   if($have==0){
  //     $stm=mysqli_prepare($c,"INSERT INTO draw_slot(id_tourna,slot_no) VALUES (?,?)");
  //     for($i=1;$i<=$teamCount;$i++){ mysqli_stmt_bind_param($stm,"ii",$idTourna,$i); mysqli_stmt_execute($stm); }
  //     mysqli_stmt_close($stm);
  //   }
  //   $p->dongketnoi($c); return true;
  // }
public function ensureSlots($idTourna, $teamCount){
    $p = new mconnect();
    $c = $p->moketnoi();
    if(!$c) return false;

    // Nên có unique để tránh trùng (xem mục 3)
    $sql = "INSERT IGNORE INTO draw_slot (id_tourna, slot_no) VALUES (?, ?)";
    $ins = mysqli_prepare($c, $sql);

    for ($i = 1; $i <= (int)$teamCount; $i++) {
        mysqli_stmt_bind_param($ins, "ii", $idTourna, $i);
        if (!mysqli_stmt_execute($ins)) {
            // debug nếu cần:
            // error_log('ensureSlots error: '.mysqli_error($c));
        }
    }
    mysqli_stmt_close($ins);
    $p->dongketnoi($c);
    return true;
}


  public function loadSlots($idTourna){
    $p=new mconnect(); $c=$p->moketnoi(); $rows=[];
    if($c){
      $sql="SELECT s.slot_no,s.id_team,t.teamName
            FROM draw_slot s LEFT JOIN team t ON s.id_team=t.id_team
            WHERE s.id_tourna=? ORDER BY s.slot_no";
      $stm=mysqli_prepare($c,$sql); mysqli_stmt_bind_param($stm,"i",$idTourna);
      mysqli_stmt_execute($stm); $res=mysqli_stmt_get_result($stm);
      while($r=$res->fetch_assoc()) $rows[]=$r;
      mysqli_stmt_close($stm); $p->dongketnoi($c);
    }
    return $rows;
  }

  public function saveSlots($idTourna, $map){
    $p=new mconnect(); $c=$p->moketnoi(); if(!$c) return false; $ok=true;
    // $sql="UPDATE draw_slot SET id_team=? WHERE id_tourna=? AND slot_no=?";
    // $stm=mysqli_prepare($c,$sql);
    // foreach($map as $slot=>$idTeam){
    //   if($idTeam === '' || $idTeam === null) $idTeam = null;
    //   mysqli_stmt_bind_param($stm,"iii",$idTeam,$idTourna,$slot);
    //   $ok = $ok && @mysqli_stmt_execute($stm);
    // }
    // mysqli_stmt_close($stm); $p->dongketnoi($c); return $ok;
    $stmtSet = mysqli_prepare($c, "UPDATE draw_slot SET id_team=? WHERE id_tourna=? AND slot_no=?");
    $stmtNull = mysqli_prepare($c, "UPDATE draw_slot SET id_team=NULL WHERE id_tourna=? AND slot_no=?");

    foreach($map as $slot=>$idTeam){
        if ($idTeam === '' || $idTeam === null) {
            mysqli_stmt_bind_param($stmtNull, "ii", $idTourna, $slot);
            $ok = $ok && mysqli_stmt_execute($stmtNull);
        } else {
            $idTeam = (int)$idTeam;
            mysqli_stmt_bind_param($stmtSet, "iii", $idTeam, $idTourna, $slot);
            $ok = $ok && mysqli_stmt_execute($stmtSet);
        }
    }

    mysqli_stmt_close($stmtSet);
    mysqli_stmt_close($stmtNull);
    $p->dongketnoi($c);
    return $ok;

  }
// Phân hạt giống
// modeldraw.php
public function placeSeededDraw(int $idTourna, bool $shuffleNoSeed = false): bool {
    $p = new mconnect(); $c = $p->moketnoi(); if(!$c) return false;

    // 1) Lấy team_count; nếu rỗng → đếm đội approved
    $N = 0;
    if ($rs = $c->query("SELECT team_count FROM tournament WHERE idtourna=".(int)$idTourna)) {
        if ($row = $rs->fetch_assoc()) $N = (int)$row['team_count'];
    }

    $sql = "SELECT tt.id_team, COALESCE(tt.seed,NULL) AS seed, t.teamName
            FROM tournament_team tt
            JOIN team t ON t.id_team = tt.id_team
            WHERE tt.id_tourna=? AND tt.reg_status='approved'
            ORDER BY t.teamName";
    $st = $c->prepare($sql);
    $st->bind_param('i',$idTourna);
    $st->execute();
    $rs = $st->get_result();

    $approved = []; $bySeed = []; $noSeed = [];
    while ($r=$rs->fetch_assoc()){
        $approved[] = $r;
        if (!empty($r['seed'])) $bySeed[(int)$r['seed']][] = (int)$r['id_team'];
        else $noSeed[] = (int)$r['id_team'];
    }
    $st->close();

    if ($N <= 0) $N = count($approved);         // fallback
    if ($N <= 1) { $p->dongketnoi($c); return false; }

    // 2) Bảo đảm slot 1..N có sẵn
    $this->ensureSlots($idTourna, $N);

    // 3) Mapping slot theo seed cho N=8/16; N khác → sẽ lấp tuần tự
    $slotAssign = array_fill(1, $N, null);
    $map8  = [1,8,5,4,3,6,7,2];
    $map16 = [1,16,9,8,5,12,13,4,3,14,11,6,7,10,15,2];
    $slotsBySeed = ($N===8) ? $map8 : (($N===16) ? $map16 : []);

    // 3a) Gán đội có seed vào slot theo mapping
    if ($slotsBySeed) {
        foreach ($slotsBySeed as $seedIdx => $slotNo) { // seedIdx 0..7/15
            $seed = $seedIdx + 1;
            if (!empty($bySeed[$seed])) {
                shuffle($bySeed[$seed]);                // nếu trùng seed → random
                $slotAssign[$slotNo] = array_shift($bySeed[$seed]);
            }
        }
    }

    // 3b) Gom đội còn lại (seed dư & không seed), optional random
    $left = [];
    foreach ($bySeed as $arr) foreach ($arr as $tid) $left[] = $tid;
    foreach ($noSeed as $tid) $left[] = $tid;
    if ($shuffleNoSeed) shuffle($left);

    // 3c) Lấp các slot còn trống
    foreach ($slotAssign as $i => $val) {
        if ($val !== null) continue;
        $tid = array_shift($left);
        if (!$tid) break;
        $slotAssign[$i] = $tid;
    }

    // 4) Ghi vào draw_slot
    $c->query("UPDATE draw_slot SET id_team=NULL WHERE id_tourna=".(int)$idTourna);
    $stmt = $c->prepare("UPDATE draw_slot SET id_team=? WHERE id_tourna=? AND slot_no=?");
    foreach ($slotAssign as $slotNo => $tid) {
        if ($tid === null) continue;
        $stmt->bind_param('iii',$tid,$idTourna,$slotNo);
        $stmt->execute();
    }
    $stmt->close();

    $p->dongketnoi($c);
    return true;
}


}

?>