<?php
include_once(__DIR__ . "/../model/modeljointeam.php");

class cJoinTeam {
    // Gửi yêu cầu gia nhập đội
    public function cSendJoinRequest($id_team, $id_user, $message = null) {
        $p = new mJoinTeam();

        // 🧩 B1. Kiểm tra nếu user đã là thành viên đội
        $isMember = $p->checkIsTeamMember($id_team, $id_user);
        if ($isMember === false) return -2; // lỗi kết nối
        if ($isMember->num_rows > 0) return -3; // đã là thành viên

        // 🔹 B2. Kiểm tra xem người chơi có đang thuộc đội khác không
        $active = $p->checkPlayerActiveTeam($id_user);
        if ($active === false) return -2; // lỗi kết nối

        // ⚙️ Nếu đang ở đội nào đó, kiểm tra có phải là đội này không
        $hasOtherTeam = false;
        while ($row = $active->fetch_assoc()) {
            if ($row['id_team'] != $id_team) {
                $hasOtherTeam = true;
                break;
            }
        }
    if ($hasOtherTeam) return -4; // đang ở đội khác

        // 🧩 B2. Kiểm tra đã gửi yêu cầu chưa
        $check = $p->checkExistingRequest($id_team, $id_user);
        if ($check === false) return -2;
        if ($check->num_rows > 0) return -1; // đã có yêu cầu pending

        // 🧩 B3. Thêm yêu cầu mới
        $insert = $p->insertJoinRequest($id_team, $id_user, $message);
        return $insert ? 1 : 0;
    }
    // Lấy danh sách các yêu cầu chờ
    public function cGetPendingRequests($id_manager) {
        $p = new mJoinTeam();
        return $p->getPendingRequestsByManager($id_manager);
    }

    // Duyệt yêu cầu
    public function cApproveRequest($id_request) {
        $p = new mJoinTeam();
        $result = $p->approveRequest($id_request);
        return $result ? 1 : 0;
    }

    // Từ chối yêu cầu
    public function cRejectRequest($id_request) {
        $p = new mJoinTeam();
        $result = $p->rejectRequest($id_request);
        return $result ? 1 : 0;
    }
    // Lấy danh sách đội mà user đã tham gia
    public function getTeamsByUser($id_user) {  
        $p = new mJoinTeam();
        return $p->selectTeamsByUser($id_user);
    }
}
?>