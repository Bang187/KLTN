<?php
include_once(__DIR__ . '/../model/modeltourna.php');
include_once(__DIR__ . '/../model/modelrule.php');
include_once(__DIR__ . '/../model/modellocal.php');

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

        // Dùng helper upload
        $logoPath   = cUploadTourna::saveUploadOrDefault('hinhlogo',   $defaultLogo);
        $bannerPath = cUploadTourna::saveUploadOrDefault('hinhbanner', $defaultBanner);

        // Insert DB
        $m = new mTourna();
        return $m->insertTourna($name, $idOrg, $startDate, $endDate, $logoPath, $bannerPath);
    }
    public function loadConfigData($id) {
        $mT = new mTourna();
        $mL = new mLocation();
        return [
            'tourna'    => $mT->getDetail($id),
            'locations' => $mL->listAll()
        ];
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
        $rs = new mRuleSet();
        $format = $post['format'] ?? 'knockout';
        if ($format === 'roundrobin') {
            $rr = max(1, (int)($post['rr_rounds'] ?? 1));
            $pw = max(0, (int)($post['pointwin']  ?? 3));
            $pd = max(0, (int)($post['pointdraw'] ?? 1));
            $pl = max(0, (int)($post['pointloss'] ?? 0));
            $tie= trim($post['tiebreak_rule'] ?? 'GD,GF,H2H');
            $name = "Vòng tròn {$rr} lượt ({$pw}-{$pd}-{$pl})";
            $idRule = $rs->findOrCreate('roundrobin', $rr, $pw, $pd, $pl, $tie, $name);
        } else {
            $idRule = $rs->findOrCreate('knockout', null, null, null, null, null, 'Knock-out mặc định');
        }

        // 3) team count
        $teamCount = null;
        if (isset($post['team_count']) && $post['team_count'] !== '') {
            $teamCount = max(2, (int)$post['team_count']);
        }

        // 4) update tournament
        $ok = (new mTourna())->updateConfig($id, $teamCount, $idRule, $idLocal);
        return ['success'=>$ok, 'message'=>$ok ? 'Lưu cấu hình thành công' : 'Lưu thất bại'];
    }
    public function deleteTourna($idTourna) {
        $m = new mTourna();
        return $m->deleteTourna($idTourna);
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

}
?>