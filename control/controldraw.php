<?php
// require_once __DIR__ . '/../model/modeldraw.php';
// require_once __DIR__ . '/../model/modelteam.php';

// class cDraw {
//   public function screen($idTourna, $teamCount){
//     $m = new mDraw();
//     // tạo slot lần đầu
//     $m->ensureSlots($idTourna, $teamCount);

//     // lưu nếu submit
//     if($_SERVER['REQUEST_METHOD'] === 'POST'){
//       $slots = $m->loadSlots($idTourna);
//       $map = [];
//       foreach($slots as $r){
//         $key = 'slot_'.$r['slot_no'];
//         if(isset($_POST[$key])){
//           $val = $_POST[$key];
//           $map[$r['slot_no']] = ($val === '') ? null : (int)$val;
//         }
//       }
//       $m->saveSlots($idTourna,$map);
//         echo '<script>location.href="dashboard.php?page=draw&id_tourna='.$idTourna.'&team_count='.$teamCount.'&saved=1";</script>';
//         exit;
//     }

//     // load để hiển thị
//     $slots = $m->loadSlots($idTourna);
//     $mt   = new mteam(); 
//     $approved = $mt->getApprovedTeamsByTourna($idTourna);// trả về id_team, teamName 

//     // đưa dữ liệu sang view
//     include __DIR__ . '/../view/draw_result.php';
//   }
// }
// Cách 2
// require_once __DIR__ . '/../model/modeldraw.php';
// require_once __DIR__ . '/../model/modelteam.php';
// require_once __DIR__ . '/../model/modeltourna.php';

// class cDraw {
//   public function screen($idTourna, $teamCountParamFromUrl = null){
//     $m  = new mDraw();
//     $mt = new mteam();
//     $mT = new mTourna();

//     // Lấy team_count “chuẩn” từ DB, không tin param URL
//     $teamCount = $mT-> countRegisteredTeams($idTourna);
//     if ($teamCount === null || $teamCount <= 0) $teamCount = 0;

//     // tạo slot nếu thiếu (không xoá slot thừa khi giảm)
//     $m->ensureSlots($idTourna, $teamCount);

//     // Lưu nếu submit
//     if($_SERVER['REQUEST_METHOD'] === 'POST'){
//       $slots = $m->loadSlots($idTourna);
//       $map = [];
//       foreach($slots as $r){
//         $key = 'slot_'.$r['slot_no'];
//         if(isset($_POST[$key])){
//           $val = $_POST[$key];
//           $map[$r['slot_no']] = ($val === '') ? null : (int)$val;
//         }
//       }
//       $m->saveSlots($idTourna,$map);
//       echo '<script>location.href="dashboard.php?page=draw&id_tourna='.$idTourna.'&saved=1";</script>';
//       exit;
//     }

//     // Load để hiển thị
//     $slots    = $m->loadSlots($idTourna);       // số dòng = số slot hiện có
//     $approved = $mt->getApprovedTeamsByTourna($idTourna);

//     // đưa dữ liệu sang view
//     include __DIR__ . '/../view/draw_result.php';
//   }
// }
// Cách 3:
require_once __DIR__ . '/../model/modeltourna.php';
require_once __DIR__ . '/../model/modeldraw.php';
require_once __DIR__ . '/../model/modelteam.php';
require_once __DIR__ . '/../model/modeltournateam.php';

class cDraw {
  public function screen(int $idTourna) {
    $md = new mDraw();
    $mt = new mTourna();

    // team_count chuẩn; nếu chưa đặt thì fallback = số đội đã duyệt
    $teamCount = (int)$mt->getTeamCount($idTourna);
    if ($teamCount <= 0) $teamCount = (int)$mt->countApprovedTeams($idTourna);

    // Chỉ ensure slot khi > 0
    if ($teamCount > 0) $md->ensureSlots($idTourna, $teamCount);

    // Lưu slot (bảng bên phải)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'save_slots') {
      $map = [];
      foreach ($md->loadSlots($idTourna) as $r) {
        $k = 'slot_' . (int)$r['slot_no'];
        if (array_key_exists($k, $_POST)) {
          $val = $_POST[$k];
          $map[(int)$r['slot_no']] = ($val === '' ? null : (int)$val);
        }
      }
      $md->saveSlots($idTourna, $map);
      header("Location: dashboard.php?page=draw&id_tourna={$idTourna}&msg=slots_saved");
      exit;
    }

    // Nạp dữ liệu tươi cho view
    $slots    = $md->loadSlots($idTourna);
   // $approved = $mt->selectApprovedTeams($idTourna); // <- dùng hàm có sẵn của bạn
    $approved = (new mTourna())->selectApprovedTeams($idTourna);
    $tourna   = $mt->getById($idTourna);

    include __DIR__ . '/../view/draw_result.php';
  }

  // Lưu seed (bảng trái)
  public function saveSeeds(int $idTourna) {
    if (!empty($_POST['seed'])) {
      $mTT = new mtournateam();
      foreach ($_POST['seed'] as $teamId => $seedVal) {
        $seed = (trim((string)$seedVal) === '' ? null : (int)$seedVal);
        $mTT->setSeed($idTourna, (int)$teamId, $seed);
      }
    }
    header("Location: dashboard.php?page=draw&id_tourna={$idTourna}&msg=seed_saved");
    exit;
  }

  // Xếp theo seed (không seed cũng hoạt động – tự rót các đội còn lại)
  public function placeSeeded(int $idTourna) {
    $ok = (new mDraw())->placeSeededDraw($idTourna);
    header("Location: dashboard.php?page=draw&id_tourna={$idTourna}&msg=" . ($ok ? 'seeded_ok' : 'seeded_fail'));
    exit;
  }
}
