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

    // Lấy danh sách slot hiện có cho giải này
    $exist = [];
    $sql = "SELECT slot_no FROM draw_slot WHERE id_tourna=? ORDER BY slot_no";
    $stm = mysqli_prepare($c, $sql);
    mysqli_stmt_bind_param($stm, "i", $idTourna);
    mysqli_stmt_execute($stm);
    $res = mysqli_stmt_get_result($stm);
    while($r = $res->fetch_assoc()){
        $exist[(int)$r['slot_no']] = true;
    }
    mysqli_stmt_close($stm);

    // Bổ sung các slot thiếu từ 1..teamCount
    $ins = mysqli_prepare($c, "INSERT INTO draw_slot(id_tourna, slot_no) VALUES (?, ?)");
    for($i = 1; $i <= $teamCount; $i++){
        if (empty($exist[$i])) {
            mysqli_stmt_bind_param($ins, "ii", $idTourna, $i);
            mysqli_stmt_execute($ins);
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
    $sql="UPDATE draw_slot SET id_team=? WHERE id_tourna=? AND slot_no=?";
    $stm=mysqli_prepare($c,$sql);
    foreach($map as $slot=>$idTeam){
      if($idTeam === '' || $idTeam === null) $idTeam = null;
      mysqli_stmt_bind_param($stm,"iii",$idTeam,$idTourna,$slot);
      $ok = $ok && @mysqli_stmt_execute($stm);
    }
    mysqli_stmt_close($stm); $p->dongketnoi($c); return $ok;
  }

}

?>