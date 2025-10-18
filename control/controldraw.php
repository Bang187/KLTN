<?php
require_once __DIR__ . '/../model/modeldraw.php';
require_once __DIR__ . '/../model/modelteam.php';

class cDraw {
  public function screen($idTourna, $teamCount){
    $m = new mDraw();
    // tạo slot lần đầu
    $m->ensureSlots($idTourna, $teamCount);

    // lưu nếu submit
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
      $slots = $m->loadSlots($idTourna);
      $map = [];
      foreach($slots as $r){
        $key = 'slot_'.$r['slot_no'];
        if(isset($_POST[$key])){
          $val = $_POST[$key];
          $map[$r['slot_no']] = ($val === '') ? null : (int)$val;
        }
      }
      $m->saveSlots($idTourna,$map);
        echo '<script>location.href="dashboard.php?page=draw&id_tourna='.$idTourna.'&team_count='.$teamCount.'&saved=1";</script>';
        exit;
    }

    // load để hiển thị
    $slots = $m->loadSlots($idTourna);
    $mt   = new mteam(); 
    $approved = $mt->getApprovedTeamsByTourna($idTourna);// trả về id_team, teamName 

    // đưa dữ liệu sang view
    include __DIR__ . '/../view/draw_result.php';
  }
}
