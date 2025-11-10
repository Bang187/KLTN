<?php
include_once(__DIR__ . '/../model/modeltourna.php');
include_once(__DIR__ . '/../model/modelrule.php');
include_once(__DIR__ . '/../model/modellocal.php');
include_once(__DIR__ . '/../model/modelgroup.php');

include_once ('controluploadtourna.php');
class cTourna {
    public function showAllTournaments() {
        $model = new mTourna();
        $result = $model->selectallTournament();
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }
    public function showTournamentByName($keyword){
        $p = new mTourna();
        $result = $p->selectTournamentByName($keyword);
       if ($result) {
            return $result;
        } else {
            return false;
        }
    }
    public function getByUser($idOrg) {
        $m = new mTourna();
        return $m->selectByUser($idOrg);
    }
    public function createTourna(string $name, ?string $startDate, ?string $endDate)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $idOrg = (int)($_SESSION['id_org'] ?? 0);
        if ($idOrg <= 0) {
            throw new Exception('Thiếu quyền: id_org không tồn tại.');
        }

        // Cho phép bỏ trống ngày
        $startDate = $startDate ?: null;
        $endDate   = $endDate   ?: null;

        // Ảnh mặc định (đặt sẵn file)
        $defaultLogo   = '../img/giaidau/logo_macdinh.png';
        $defaultBanner = '../img/giaidau/banner_macdinh.jpg';

    $m = new mTourna();
    $newId = $m->insertTourna($name, $idOrg, $startDate, $endDate, $defaultLogo, $defaultBanner);
    if (!$newId) return false;

    // B2) Upload ảnh (nếu có) → thư mục theo giải
    $subFolder = 'tournaments/' . (int)$newId . '/images';
    $logoPath   = cUploadTourna::saveImageOrDefault('hinhlogo',   $defaultLogo,   $subFolder);
    $bannerPath = cUploadTourna::saveImageOrDefault('hinhbanner', $defaultBanner, $subFolder);

    // B3) UPDATE đường dẫn ảnh
    $m->updateTournaImages($newId, $logoPath, $bannerPath);

    return $newId;
    }
    // Tạo điều lệ
// public function saveRegulation(int $id_tourna, array $post, array $files)
// {
//     // Map field
//     $fee_type   = (isset($post['fee_type']) && $post['fee_type'] === 'PAID') ? 'PAID' : 'FREE';
//     $fee_amount = ($fee_type === 'PAID') ? (int)($post['fee_amount'] ?? 0) : null;

//     $summary = $post['regulation_summary'] ?? null;

//     // reg_open/reg_close (bạn đã có sẵn 2 cột này)
//     // $reg_open  = !empty($post['regis_open_at'])  ? $post['regis_open_at']  : null;
//     // $reg_close = !empty($post['regis_close_at']) ? $post['regis_close_at'] : null;

//     $m = new mTourna();
//     $ok = $m->updateRegulationFields($id_tourna, $fee_type, $fee_amount, $summary);

//     // Upload file điều lệ (tùy chọn)
//     if (isset($files['reg_file']) && $files['reg_file']['error'] === UPLOAD_ERR_OK) {
//         $subFolder = 'tournaments/' . (int)$id_tourna . '/regulations';
//         $doc = cUploadTourna::saveDoc('reg_file', $subFolder);
//         if ($doc) {
//             if (session_status() === PHP_SESSION_NONE) session_start();
//             $uploaded_by = $_SESSION['user_id'] ?? null;
//             $m->insertTournamentFile(
//                 $id_tourna, $doc['file_name'], $doc['file_path'],
//                 $doc['mime_type'], $doc['file_size'],
//                 1, 1, $uploaded_by
//             );
//         }
//     }
//     return $ok;
// }
// controltourna.php
public function saveRegulation(int $id, array $post, array $files = [])
{
    require_once __DIR__ . '/controluploadtourna.php'; // dùng cUploadTourna::saveDoc()

    // 1) Lệ phí + tóm tắt
    $feeType = ($post['fee_type'] ?? 'FREE') === 'PAID' ? 'PAID' : 'FREE';

    $amount  = null;
    if ($feeType === 'PAID') {
        $raw = trim($post['fee_amount'] ?? '');
        $raw = str_replace(['.',',',' '], '', $raw); // 900.000 -> 900000
        if ($raw !== '' && is_numeric($raw)) $amount = (float)$raw;
    }

    $summary = trim($post['regulation_summary'] ?? '');

    $m   = new mTourna();
    $ok1 = $m->updateRegulationFields($id, $feeType, $amount, $summary);

    // 2) Nếu có file PDF/Word thì lưu
    $ok2 = true; // mặc định không có file vẫn coi là OK
    $doc = cUploadTourna::saveDoc('reg_file', 'regulations/'.$id); // lưu vào /uploads/regulations/<id>/
    if ($doc) {
        // Cần có hàm insertRegulationFile(...) trong model (đoạn dưới)
        $ok2 = $m->insertRegulationFile(
            $id,
            $doc['file_name'],
            $doc['file_path'],
            $doc['mime_type'],
            $doc['file_size']
        );
    }

    // 3) Flash + quay lại trang điều lệ
    if (session_status()===PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = [
        'type' => ($ok1 && $ok2) ? 'success' : 'error',
        'text' => ($ok1 && $ok2) ? 'Đã lưu Điều lệ & Lệ phí.' : 'Lưu thất bại (dữ liệu hoặc tệp không hợp lệ).'
    ];

    header('Location: dashboard.php?page=regulation&id_tourna='.$id);
    exit;
}


//
public function countApprovedTeams(int $idTourna): int {
    return (new mTourna())->countApprovedTeams($idTourna);
}
public function getRegulationFiles(int $idTourna): array {
    return (new mTourna())->selectRegulationFiles($idTourna);
}
    public function loadConfigData($id) {
        $mT = new mTourna();
        $mL = new mLocation();
        return [
            'tourna'    => $mT->getDetail($id),
            'locations' => $mL->listAll()
        ];
    }
    private function resolveRuleId(string $format, $rr_rounds): int {
    // Bảng rule của bạn: 1=knockout, 2=RR-1lượt, 3=RR-2lượt, 4=hybrid
    if ($format === 'hybrid') return 4;
    if ($format === 'roundrobin') return ((int)$rr_rounds === 2) ? 3 : 2;
    return 1; // knockout
}
    public function saveConfig($id, $post) {
        // 1) xử lý location
        $idLocal = null;
        if (isset($post['location_mode']) && $post['location_mode'] === 'new') {
            $name = trim($post['localname'] ?? '');
            $addr = trim($post['address'] ?? '');
            if ($name !== '') {
                $idLocal = (new mLocation())->create($name, $addr);
            }
        } else {
            $id_local = $post['id_local'] ?? '';
            if ($id_local !== '' && ctype_digit((string)$id_local)) $idLocal = (int)$id_local;
        }

        // 2) xử lý rule
        $rs     = new mRuleSet();
        $format = $post['format'] ?? 'knockout';

        // rr-like (dùng cho cả roundrobin & hybrid)
        $rr = max(1, (int)($post['rr_rounds'] ?? 1));
        $pw = max(0, (int)($post['pointwin']  ?? 3));
        $pd = max(0, (int)($post['pointdraw'] ?? 1));
        $pl = max(0, (int)($post['pointloss'] ?? 0));
        $tie= trim($post['tiebreak_rule'] ?? 'GD,GF,H2H');

        if ($format === 'roundrobin') {
            $name   = "Vòng tròn {$rr} lượt ({$pw}-{$pd}-{$pl})";
            $idRule = $rs->findOrCreate('roundrobin', $rr, $pw, $pd, $pl, $tie, $name);
        } elseif ($format === 'hybrid') {
            // Lấy thêm thông số chia bảng
            $hy_group_count = isset($post['hy_group_count']) ? (int)$post['hy_group_count'] : null;
            $hy_take_1st    = isset($post['hy_take_1st'])    ? (int)$post['hy_take_1st']    : null;
            $hy_take_2nd    = isset($post['hy_take_2nd'])    ? (int)$post['hy_take_2nd']    : null;
            $hy_take_3rd    = isset($post['hy_take_3rd'])    ? (int)$post['hy_take_3rd']    : null;
            $hy_take_4th    = isset($post['hy_take_4th'])    ? (int)$post['hy_take_4th']    : null;

            $name   = "Hybrid {$rr} lượt ({$pw}-{$pd}-{$pl}) - G{$hy_group_count}";
            // CHÚ Ý: cập nhật findOrCreate để nhận thêm 5 tham số hy_*
            $idRule = $rs->findOrCreate('hybrid', $rr, $pw, $pd, $pl, $tie, $name,
                                        $hy_group_count, $hy_take_1st, $hy_take_2nd, $hy_take_3rd, $hy_take_4th);
        } else {
            $idRule = $rs->findOrCreate('knockout', null, null, null, null, null, 'Knock-out mặc định');
        }

        // 3) team count
        $teamCount = null;
        if (isset($post['team_count']) && $post['team_count'] !== '') {
            $teamCount = max(2, (int)$post['team_count']);
        }

        // 4) update tournament
        $okBase = (new mTourna())->updateConfig($id, $teamCount, $idRule, $idLocal);
        //5)
        $allow = isset($post['allow_online_reg']) ? 1 : 0;

    // datetime-local gửi dạng "YYYY-mm-ddTHH:ii", DB DATETIME chấp nhận "YYYY-mm-dd HH:ii:ss"
    $open  = !empty($post['regis_open_at'])  ? str_replace('T',' ', $post['regis_open_at']).':00'  : null;
    $close = !empty($post['regis_close_at']) ? str_replace('T',' ', $post['regis_close_at']).':00' : null;

    // nếu tắt công tắc → clear thời gian
    if ($allow !== 1) { $open = null; $close = null; }

    // validate nhẹ: open < close
    if ($open && $close && $open > $close) {
        return ['success'=>false,'message'=>'Thời gian mở phải trước hạn chót đăng ký'];
    }

    $okReg = (new mTourna())->updateRegistrationSettings($id, $allow, $open, $close);

    $ok = $okBase && $okReg;
    return ['success'=>$ok, 'message'=>$ok ? 'Lưu cấu hình thành công' : 'Lưu thất bại'];
    }
    public function deleteTourna($idTourna) {
        $m = new mTourna();
        return $m->deleteTourna($idTourna);
    }
    public function editTourna($idTourna, $name) {
        $m = new mTourna();
        return $m->editTourna($idTourna, $name);
    }
    private function getAuth(): array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    // Chuẩn hoá key về chữ thường để tránh lệch ID_role vs id_role
    $S = array_change_key_case($_SESSION, CASE_LOWER);

    $id_user = (int)($S['id_user'] ?? $S['iduser'] ?? $S['user_id'] ?? 0);
    $role    = (int)($S['id_role'] ?? $S['idrole'] ?? $S['role'] ?? 0);

    return ['id_user' => $id_user, 'role' => $role];
}
    // Kiểm tra quyền
    public function requireTeamManager(): array {
    $a = $this->getAuth();
    if ($a['id_user'] <= 0) {
        return ['err' => 'Bạn cần đăng nhập tài khoản Quản lý đội.'];
    }
    if ($a['role'] !== 3) {
        return ['err' => 'Chỉ tài khoản Quản lý đội mới được đăng ký.'];
    }
    return $a;
}
    // Chọn đội để đk
    public function showRegisterTeamScreen(int $idTourna) {
    $auth = $this->requireTeamManager();
    if (isset($auth['err'])) { $msg = $auth['err']; include __DIR__.'/../view/register_tourna.php'; return; }

    // lấy danh sách đội của user CHƯA đăng ký giải này
    $m = new mTourna();
    $teams = $m->listUserTeamsNotInTournament($auth['id_user'], $idTourna);

    // info giải để hiển thị
    $tourna = $m->getTournamentById($idTourna);

    include __DIR__.'/../view/register_tourna.php';
}
    // Xử lý đăng ký
    public function submitRegisterTeam(int $tournaId, int $teamId) {
    $auth = $this->requireTeamManager();
    if (isset($auth['err'])) return ['err'=>$auth['err']];

    $m = new mTourna();
    $t = $m->getTournamentById($tournaId);
    if (!$t) return ['err'=>'Giải không tồn tại'];

    // kiểm tra cửa sổ đăng ký
    $now = date('Y-m-d H:i:s');
    if ((int)($t['allow_online_reg'] ?? 0) !== 1) return ['err'=>'Giải chưa mở đăng ký trực tuyến'];
    if (!empty($t['regis_open_at'])  && $now < $t['regis_open_at'])  return ['err'=>'Chưa đến thời gian mở đăng ký'];
    if (!empty($t['regis_close_at']) && $now > $t['regis_close_at']) return ['err'=>'Đã quá hạn đăng ký'];

    // team có thuộc user không?
    if (!$m->isTeamOwnedByUser($teamId, $auth['id_user'])) return ['err'=>'Bạn không sở hữu đội này'];

    // đã đăng ký chưa?
    if ($m->isTeamRegistered($tournaId, $teamId)) return ['err'=>'Đội đã đăng ký giải này'];

    // check slot
    $reg = $m->countRegisteredTeams($tournaId);
    $max = (int)($t['team_count'] ?? 0);
    if ($max > 0 && $reg >= $max) return ['err'=>'Số lượng đội đã đủ'];

    // ghi dữ liệu
    $ok = $m->insertTournamentTeam($tournaId, $teamId, 'online', 'pending');
    return $ok ? ['ok'=>'Đã gửi đăng ký. Chờ BTC duyệt.'] : ['err'=>'Lỗi ghi nhận đăng ký'];
}



    public function addTeamScreen(int $id_tourna){
        $m = new mTourna();
        $tourna = $m->getById($id_tourna);                 // lấy team_count, tên giải...
        $teamCount = (int)($tourna['team_count'] ?? 0);
        // ... nếu cần, load thêm danh sách đội/đăng ký ở đây ...
        include __DIR__ . '/../view/addteam.php';           // truyền $tourna, $teamCount, $id_tourna vào view
    }
    public function getTournamentDetails(int $id) {
    $m = new mTourna();
    return $m->selectTournamentDetails($id);
}
// Cập nhật cài đặt đăng ký trực tuyến  
    public function updateRegistrationSettings($id, $post){
    $allow = isset($post['allow_online_reg']) ? 1 : 0;
    $open  = !empty($post['regis_open_at'])  ? $post['regis_open_at']  : null;
    $close = !empty($post['regis_close_at']) ? $post['regis_close_at'] : null;

    $m = new mTourna();
    return $m->updateRegistrationSettings($id, $allow, $open, $close);
}
// 
    public function getTournaById($id){
    $m = new mTourna(); return $m->getTournamentById($id);
}

public function registerTournament($tournaId, $teamId, $userId){
    $m = new mTourna();
    $t = $m->getTournamentById($tournaId);
    if (!$t) return ['err'=>'Giải không tồn tại'];

    // tính trạng thái nếu bạn CHƯA dùng cột status
    $now = date('Y-m-d H:i:s');
    $status = 1; // upcoming
    if (!empty($t['startdate']) && !empty($t['enddate'])) {
        if ($now < $t['startdate']) $status = 1;
        elseif ($now > $t['enddate']) $status = 3;
        else $status = 2;
    } else if (isset($t['status'])) {
        $status = (int)$t['status'];
    }

    if ((int)$t['allow_online_reg'] !== 1) return ['err'=>'Giải chưa mở đăng ký trực tuyến'];
    if ($status !== 1) return ['err'=>'Chỉ cho đăng ký khi giải sắp diễn ra'];

    if (!empty($t['regis_open_at']) && $now < $t['regis_open_at'])  return ['err'=>'Chưa đến thời gian mở đăng ký'];
    if (!empty($t['regis_close_at']) && $now > $t['regis_close_at']) return ['err'=>'Đã quá hạn đăng ký'];

    // kiểm tra quyền sở hữu đội
    $mt = new mTeam();
    if (!$mt->isTeamOwnedByUser($teamId, $userId)) return ['err'=>'Bạn không sở hữu đội này'];

    // đã đăng ký chưa
    if ($m->isTeamRegistered($tournaId, $teamId)) return ['err'=>'Đội đã đăng ký giải này'];

    // không vượt team_count
    $registered = $m->countRegisteredTeams($tournaId);
    if (!empty($t['team_count']) && $registered >= (int)$t['team_count']) return ['err'=>'Số lượng đội đã đủ'];

    $ok = $m->insertTournamentTeam($tournaId, $teamId, 'online', 'pending');
    return $ok ? ['ok'=>'Đã gửi đăng ký. Chờ BTC duyệt.'] : ['err'=>'Lỗi ghi nhận đăng ký'];
}
// detail giải
public function getTournamentFullDetails(int $id) {
    return (new mTourna())->selectTournamentFullDetails($id);
}

public function getApprovedTeams(int $idTourna) {
    return (new mTourna())->selectApprovedTeams($idTourna);
}

public function getMatches(int $id, string $type, int $limit=10){
    return (new mTourna())->selectMatches($id,$type,$limit);
}

public function getQuickStats(int $idTourna): array {
    return (new mTourna())->selectQuickStats($idTourna);
}
public function getBracket(int $id){
    return (new mTourna())->selectBracketByRounds($id);
}
// chia bảng
  public function loadGroupScreenData(int $idTourna): array {
    $mg = new mGroup();

    $tour = $mg->getTournaWithRule($idTourna);
    if (!$tour) return ['err'=>'Không tìm thấy giải'];

    // Số bảng lấy theo rule; fallback 4
    $G = (int)($tour['hy_group_count'] ?? 0);
    if ($G <= 0) $G = 4;

    // Khởi tạo nếu chưa có
    if ($mg->countGroups($idTourna) === 0) {
      $teamCount = (int)($tour['team_count'] ?? 0);
      $mg->createGroupsAndSlots($idTourna, $G, $teamCount);
    }

    $groups   = $mg->listGroupsWithSlots($idTourna);
    $approved = $mg->listApprovedTeams($idTourna);

    // đội đã dùng (để disable option trùng trong view)
    $used = [];
    foreach ($groups as $g) foreach ($g['slots'] as $s)
      if (!empty($s['id_team'])) $used[(int)$s['id_team']] = true;

    return [
      'tourna'   => $tour,
      'groups'   => $groups,
      'approved' => $approved,
      'used'     => $used,
    ];
  }

  /** Lưu phân bổ đội theo POST */
  public function saveGroupAssignments(int $idTourna, array $post): array {
    $mg = new mGroup();

    // Parse tất cả key gs_{gid}_{slot}
    $assign = [];   // [gid][slot] = id_team|null
    $seen   = [];   // phát hiện trùng đội
    $dup    = false;

    foreach ($post as $k=>$v) {
      if (strpos($k,'gs_')===0) {
        $parts = explode('_',$k); // gs, gid, slot
        if (count($parts)===3) {
          $gid  = (int)$parts[1];
          $slot = (int)$parts[2];
          $tid  = ($v===''? null : (int)$v);
          $assign[$gid][$slot] = $tid;
          if ($tid) {
            if (isset($seen[$tid])) $dup = true;
            $seen[$tid] = true;
          }
        }
      }
    }

    if ($dup) return ['success'=>false, 'message'=>'Một đội được chọn ở nhiều bảng. Hãy kiểm tra lại.'];

    // Ghi DB
    $ok = $mg->clearAssignments($idTourna);
    if ($ok) {
      foreach ($assign as $gid=>$slots) {
        foreach ($slots as $slot=>$tid) {
          if ($tid) $ok = $mg->setAssignment((int)$gid,(int)$slot,(int)$tid) && $ok;
        }
      }
    }

    return ['success'=>$ok, 'message'=>$ok?'Đã lưu phân bổ đội':'Lưu thất bại'];
  }
}
?>